<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class AttendanceModel extends Model {
    protected $table = 'attendance_logs';

    /**
     * Get aggregated schedule with attendance data for a specific date
     */
    public function getDailyScheduleWithAttendance($date) {
        // 1. Fetch Schedule for the Day
        $timestamp = strtotime($date);
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        $dayName = $dayMap[date('D', $timestamp)] ?? '';

        $sql = "SELECT s.*, 
                       k.tingkat, k.abjad, 
                       sub.nama as mapel_nama,
                       u.nama as teacher_nama
                FROM schedules s
                JOIN kelas k ON s.kelas_id = k.id
                JOIN subjects sub ON s.subject_id = sub.id
                LEFT JOIN users u ON s.teacher_id = u.id
                WHERE s.day = ? AND s.academic_year_id = ?
                ORDER BY s.hour ASC, k.tingkat ASC, k.abjad ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dayName, $this->academic_year_id]);
        $schedulesRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fetch Attendance Logs for Date
        $attStmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE date = ? AND academic_year_id = ?");
        $attStmt->execute([$date, $this->academic_year_id]);
        
        $attendanceLogs = []; // Key: classId|hour
        while($row = $attStmt->fetch(PDO::FETCH_ASSOC)) {
            $attendanceLogs[$row['kelas_id'] . '|' . $row['hour']] = $row;
        }

        // 2.5 Fetch approved leaves/substitutions for the date
        $leaveStmt = $this->db->prepare("
            SELECT d.kelas_id, d.hour, u.nama as substitute_name
            FROM teaching_substitutions d
            JOIN teacher_leaves l ON d.leave_id = l.id
            LEFT JOIN users u ON d.substitute_teacher_id = u.id
            WHERE l.date = ? AND l.status = 'published'
        ");
        $leaveStmt->execute([$date]);
        $substitutions = [];
        while($row = $leaveStmt->fetch(PDO::FETCH_ASSOC)) {
            $substitutions[$row['kelas_id'] . '|' . $row['hour']] = $row['substitute_name'];
        }

        // 2.7 Fetch KBM Dispensations for the date
        $activityModel = new \App\Models\ActivityModel();
        $dispensations = $activityModel->getDispensationsByDate($date);
        
        $dayOfWeek = date('w', strtotime($date));
        $isFriday = ($dayOfWeek == 5);

        // 3. Merge
        $dailySchedule = [];
        foreach ($schedulesRaw as $row) {
            $kelasId = $row['kelas_id'];
            $hour = $row['hour'];
            $key = $kelasId . '|' . $hour;
            
            $log = $attendanceLogs[$key] ?? null;
            $attendanceData = null;

            if ($log) {
                // Parse legacy note format
                $ketepatan = ($log['status'] === 'hadir') ? 'tepat_waktu' : null;
                $jamDatang = null;
                
                if ($log['status'] === 'hadir' && $log['note'] && strpos($log['note'], 'Terlambat') !== false) {
                     $ketepatan = 'terlambat';
                     preg_match('/Terlambat: (.*)/', $log['note'], $matches); // Old format: "Terlambat: 07:15"
                     $jamDatang = $matches[1] ?? '';
                     if (!$jamDatang) {
                         // Try extracting time if format is different (e.g. from report parser)
                         preg_match('/\((\d{2}:\d{2})\)/', $log['note'], $matches2);
                         $jamDatang = $matches2[1] ?? '';
                     }
                }

                $attendanceData = [
                    'status' => $log['status'],
                    'relative_time' => $log['status'] === 'hadir' ? 'tepat_waktu' : null,
                    'ketepatan' => $ketepatan,
                    'jam_datang' => $jamDatang,
                    'note' => $log['note'],
                    'pengajar_pengganti' => $log['substitute_teacher_id'],
                    'petugas_id' => $log['petugas_id'] ?? null
                ];
            }

            // Check dispensation
            $isDispensation = false;
            $dispensationName = null;

            if ($isFriday) {
                $isDispensation = true;
                $dispensationName = 'Libur Mingguan (Jumat)';
            } else {
                foreach ($dispensations as $disp) {
                    if (in_array((int)$kelasId, $disp['kelas_ids'])) {
                        if ($disp['is_full_day']) {
                            $isDispensation = true;
                            $dispensationName = $disp['name'];
                            break;
                        } else {
                            foreach ($disp['hours'] as $h) {
                                if ((int)$hour >= (int)$h['hour_start'] && (int)$hour <= (int)$h['hour_end']) {
                                    $isDispensation = true;
                                    $dispensationName = $disp['name'];
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            $dailySchedule[] = [
                'key' => $key,
                'kelas_id' => $kelasId,
                'hour' => $hour,
                'mapel_id' => $row['subject_id'],
                'pengajar_id' => $row['teacher_id'],
                
                'kelas_name' => "Kelas " . ($row['tingkat'] ?? '?') . "-" . ($row['abjad'] ?? '?'),
                'mapel_name' => $row['mapel_nama'],
                'teacher_name' => $row['teacher_nama'],
                'substitute_name' => $substitutions[$key] ?? null,
                'absensi' => $attendanceData,
                'is_dispensation' => $isDispensation,
                'dispensation_name' => $dispensationName
            ];
        }
        
        return $dailySchedule;
    }

    public function saveAttendance($data) {
        $date = $data['date'];
        $kelasId = $data['kelas_id'];
        $hour = $data['hour'];
        $teacherId = $data['teacher_id'];
        $status = $data['status'];
        $petugasId = $data['petugas_id']; // Log who inputted it

        // Process Note / Status Logic
        $note = null;
        $substituteId = null;
        $actualStatus = $status;

        if ($status === 'hadir') {
            $ketepatan = $data['ketepatan'] ?? 'tepat_waktu';
            if ($ketepatan === 'terlambat') {
                $jamDatang = $data['jam_datang'] ?? '';
                $note = "Terlambat: $jamDatang";
            } else {
                 $note = "Tepat Waktu"; // Or empty? Legacy seemed to rely on empty for Tepat?
                 // Let's set it to empty or specific if needed. Legacy simpan_absensi didn't save "Tepat Waktu".
                 // But simpan_absensi logic was: if ($ketepatan === 'tepat_waktu') $note = null;
                 $note = null; 
            }
        } elseif ($status === 'diganti') {
            $actualStatus = 'substitute'; 
            $substituteId = $data['pengajar_pengganti'];
        } elseif ($status === 'tidak_hadir') {
            $actualStatus = 'alpha';
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (date, kelas_id, hour, teacher_id, status, substitute_teacher_id, note, petugas_id, academic_year_id, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                substitute_teacher_id = VALUES(substitute_teacher_id), 
                note = VALUES(note),
                petugas_id = VALUES(petugas_id),
                updated_at = NOW()
        ");
        return $stmt->execute([
            $date, $kelasId, $hour, $teacherId, $actualStatus, $substituteId, $note, $petugasId, $this->academic_year_id
        ]);
    }
    
    public function getReportStats($startDate, $endDate, $classId = null, $teacherId = null, $hour = null) {
        $params = [$startDate, $endDate, $this->academic_year_id];
        $sql = "SELECT al.*, 
                       k.tingkat, k.abjad,
                       u.nama as teacher_nama,
                       subst.nama as subst_nama,
                       p.nama as petugas_nama,
                       tp_u.profile_picture as teacher_pic,
                       tp_p.profile_picture as petugas_pic
                FROM {$this->table} al
                JOIN kelas k ON al.kelas_id = k.id
                LEFT JOIN users u ON al.teacher_id = u.id
                LEFT JOIN users subst ON al.substitute_teacher_id = subst.id
                LEFT JOIN users p ON al.petugas_id = p.id
                LEFT JOIN teacher_profiles tp_u ON u.id = tp_u.user_id
                LEFT JOIN teacher_profiles tp_p ON p.id = tp_p.user_id
                WHERE al.date BETWEEN ? AND ? AND al.academic_year_id = ?";
        
        if ($classId !== '' && $classId !== null) {
            $sql .= " AND al.kelas_id = ?";
            $params[] = $classId;
        }

        if ($teacherId !== '' && $teacherId !== null) {
            $sql .= " AND (al.teacher_id = ? OR al.substitute_teacher_id = ?)";
            $params[] = $teacherId;
            $params[] = $teacherId;
        }

        if ($hour !== '' && $hour !== null) {
            $sql .= " AND al.hour = ?";
            $params[] = $hour;
        }

        $sql .= " ORDER BY al.date DESC, k.tingkat ASC, k.abjad ASC, al.hour ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get aggregated teacher attendance stats per period
     */
    public function getTeacherReportStats($startDate, $endDate, $academicYearId = null) {
        $ayId = $academicYearId ?? $this->academic_year_id;

        // 1. Fetch all schedules for this academic year
        $schedulesSql = "
            SELECT s.*, u.nama as teacher_nama, sub.nama as mapel_nama, k.tingkat, k.abjad
            FROM schedules s
            JOIN users u ON s.teacher_id = u.id
            JOIN subjects sub ON s.subject_id = sub.id
            JOIN kelas k ON s.kelas_id = k.id
            WHERE s.academic_year_id = ?
            ORDER BY k.tingkat ASC, k.abjad ASC, s.hour ASC
        ";
        $stmt = $this->db->prepare($schedulesSql);
        $stmt->execute([$ayId]);
        $allSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $schedulesByDay = [];
        $teachersMap = [];
        foreach ($allSchedules as $slot) {
            $schedulesByDay[$slot['day']][] = $slot;
            $teachersMap[$slot['teacher_id']] = $slot['teacher_nama'];
        }

        // 2. Fetch teacher profiles (profile picture)
        $profileStmt = $this->db->query("SELECT user_id, profile_picture FROM teacher_profiles");
        $profiles = [];
        while ($p = $profileStmt->fetch(PDO::FETCH_ASSOC)) {
            $profiles[$p['user_id']] = $p['profile_picture'];
        }

        // 3. Fetch KBM dispensations in date range
        $activityModel = new \App\Models\ActivityModel();
        $dispensationsByDate = $activityModel->getDispensationsByRange($startDate, $endDate, $ayId);

        // 4. Fetch academic calendar events categorized as Libur (Holiday)
        $stmtCal = $this->db->prepare("
            SELECT tanggal_mulai, tanggal_selesai 
            FROM academic_calendar_events 
            WHERE academic_year_id = ? 
              AND kategori = 'Libur'
              AND (tanggal_mulai <= ? AND (tanggal_selesai >= ? OR tanggal_selesai IS NULL))
        ");
        $stmtCal->execute([$ayId, $endDate, $startDate]);
        $calendarHolidays = $stmtCal->fetchAll(PDO::FETCH_ASSOC);

        $holidays = [];
        foreach ($calendarHolidays as $cal) {
            $s = $cal['tanggal_mulai'];
            $e = !empty($cal['tanggal_selesai']) ? $cal['tanggal_selesai'] : $cal['tanggal_mulai'];
            $cur = $s;
            while ($cur <= $e) {
                $holidays[$cur] = true;
                $cur = date('Y-m-d', strtotime($cur . ' +1 day'));
            }
        }

        // 5. Fetch lesson hours config (TvHours)
        $settingModel = new \App\Models\SettingModel();
        $hoursConfig = $settingModel->getTvHours();
        $hoursMap = [];
        foreach ($hoursConfig as $h) {
            if ($h['type'] === 'jam') {
                $startSec = strtotime('2000-01-01 ' . $h['start'] . ':00');
                $endSec = strtotime('2000-01-01 ' . $h['end'] . ':00');
                $dur = ($startSec && $endSec && $endSec > $startSec) ? round(($endSec - $startSec) / 60) : 45;
                $hoursMap[$h['value']] = [
                    'start' => $h['start'],
                    'end' => $h['end'],
                    'duration' => $dur
                ];
            }
        }

        // 6. Fetch attendance logs in date range
        $attSql = "
            SELECT al.*, 
                   k.tingkat, k.abjad,
                   u.nama as teacher_nama,
                   subst.nama as subst_nama,
                   p.nama as petugas_nama
            FROM {$this->table} al
            JOIN kelas k ON al.kelas_id = k.id
            LEFT JOIN users u ON al.teacher_id = u.id
            LEFT JOIN users subst ON al.substitute_teacher_id = subst.id
            LEFT JOIN users p ON al.petugas_id = p.id
            WHERE (al.date BETWEEN ? AND ?) AND al.academic_year_id = ?
        ";
        $attStmt = $this->db->prepare($attSql);
        $attStmt->execute([$startDate, $endDate, $ayId]);
        $attendanceLogs = [];
        while ($row = $attStmt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['date'] . '|' . $row['kelas_id'] . '|' . $row['hour'];
            $attendanceLogs[$key] = $row;
        }

        // 7. Fetch approved teacher leaves (izin/sakit)
        $leaveSql = "
            SELECT tl.date as leave_date, tl.teacher_id, tl.type, ts.kelas_id, ts.hour, ts.substitute_teacher_id, 
                   u2.nama as subst_nama
            FROM teacher_leaves tl 
            JOIN teaching_substitutions ts ON tl.id = ts.leave_id
            LEFT JOIN users u2 ON ts.substitute_teacher_id = u2.id
            WHERE (tl.date BETWEEN ? AND ?) AND tl.academic_year_id = ? AND tl.status = 'published'
        ";
        $leaveStmt = $this->db->prepare($leaveSql);
        $leaveStmt->execute([$startDate, $endDate, $ayId]);
        $teacherLeaves = [];
        while ($row = $leaveStmt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['leave_date'] . '|' . $row['kelas_id'] . '|' . $row['hour'];
            $teacherLeaves[$key] = $row;
        }

        // 8. Initialize report container
        $report = [];
        foreach ($teachersMap as $tid => $name) {
            $report[$tid] = [
                'teacher_id' => $tid,
                'name' => $name,
                'photo' => $profiles[$tid] ?? null,
                'expected' => 0,
                'hadir_tepat' => 0,
                'terlambat' => 0,
                'total_menit_terlambat' => 0,
                'diganti' => 0,
                'tidak_hadir' => 0,
                'total_skor' => 0.0,
                'total_skor_ketepatan' => 0.0,
                'details' => []
            ];
        }

        // 9. Day-by-day iteration
        $today = date('Y-m-d');
        $nowTime = date('H:i');
        $effectiveEnd = (strtotime($endDate) > strtotime($today)) ? $today : $endDate;

        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];

        $cur = $startDate;
        while (strtotime($cur) <= strtotime($effectiveEnd)) {
            $dayOfWeek = date('w', strtotime($cur));
            $isFriday = ($dayOfWeek == 5);
            $isHoliday = isset($holidays[$cur]);

            if (!$isFriday && !$isHoliday) {
                $dayNameEnglish = date('D', strtotime($cur));
                $dayNameInd = $dayMap[$dayNameEnglish] ?? '';
                $daySchedules = $schedulesByDay[$dayNameInd] ?? [];
                $dayDispensations = $dispensationsByDate[$cur] ?? [];

                foreach ($daySchedules as $slot) {
                    $tid = $slot['teacher_id'];
                    if (!$tid || !isset($report[$tid])) continue;

                    // Check dispensations
                    $isDisp = false;
                    foreach ($dayDispensations as $disp) {
                        if (in_array((int)$slot['kelas_id'], $disp['kelas_ids'])) {
                            if ($disp['is_full_day']) {
                                $isDisp = true;
                                break;
                            } else {
                                foreach ($disp['hours'] as $h) {
                                    if ((int)$slot['hour'] >= (int)$h['hour_start'] && (int)$slot['hour'] <= (int)$h['hour_end']) {
                                        $isDisp = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                    if ($isDisp) continue;

                    $hourVal = (int)$slot['hour'];
                    $hourInfo = $hoursMap[$hourVal] ?? ['start' => '07:00', 'end' => '07:45', 'duration' => 45];
                    $slotKey = $cur . '|' . $slot['kelas_id'] . '|' . $hourVal;

                    // If date is today, and session has not finished yet, skip if unrecorded
                    if ($cur === $today && $nowTime < ($hourInfo['end'] ?? '23:59')) {
                        if (!isset($attendanceLogs[$slotKey]) && !isset($teacherLeaves[$slotKey])) {
                            continue;
                        }
                    }

                    $kelasName = 'Kelas ' . $slot['tingkat'] . '-' . $slot['abjad'];
                    $subjectName = $slot['mapel_nama'];

                    $report[$tid]['expected']++;

                    $log = $attendanceLogs[$slotKey] ?? null;
                    $leave = $teacherLeaves[$slotKey] ?? null;

                    if ($log) {
                        $status = $log['status'];
                        if ($status === 'hadir') {
                            $isLate = false;
                            $jamDatang = null;

                            if (!empty($log['note']) && stripos($log['note'], 'Terlambat') !== false) {
                                $isLate = true;
                                if (preg_match('/Terlambat:\s*([0-9]{1,2}:[0-9]{2})/', $log['note'], $m)) {
                                    $jamDatang = $m[1];
                                } elseif (preg_match('/\((\d{1,2}:\d{2})\)/', $log['note'], $m)) {
                                    $jamDatang = $m[1];
                                }
                            }

                            if ($isLate) {
                                $menit = 0;
                                if ($jamDatang) {
                                    $tD = strtotime("2000-01-01 $jamDatang:00");
                                    $tS = strtotime("2000-01-01 " . $hourInfo['start'] . ":00");
                                    if ($tD && $tS && $tD > $tS) {
                                        $menit = round(($tD - $tS) / 60);
                                    }
                                }
                                if ($menit <= 0) $menit = 15; // default fallback if time not explicitly formatted
                                
                                // Fair calculation: deduction proportional to minutes late
                                $duration = (!empty($hourInfo['duration']) && $hourInfo['duration'] > 0) ? $hourInfo['duration'] : 45;
                                $score = max(0.0, ($duration - $menit) / $duration);
                                $report[$tid]['terlambat']++;
                                $report[$tid]['total_menit_terlambat'] += $menit;
                                $report[$tid]['total_skor'] += $score;
                                $report[$tid]['total_skor_ketepatan'] += $score;

                                $report[$tid]['details'][] = [
                                    'date' => $cur,
                                    'day' => $dayNameInd,
                                    'hour' => $hourVal,
                                    'time_range' => $hourInfo['start'] . ' - ' . $hourInfo['end'],
                                    'kelas' => $kelasName,
                                    'subject' => $subjectName,
                                    'status_key' => 'terlambat',
                                    'status_label' => 'Terlambat (' . $menit . ' mnt)',
                                    'jam_datang' => $jamDatang,
                                    'menit_terlambat' => $menit,
                                    'petugas_name' => $log['petugas_nama'] ?? null,
                                    'note' => $log['note']
                                ];
                            } else {
                                // Hadir Tepat
                                $report[$tid]['hadir_tepat']++;
                                $report[$tid]['total_skor'] += 1.0;
                                $report[$tid]['total_skor_ketepatan'] += 1.0;

                                $report[$tid]['details'][] = [
                                    'date' => $cur,
                                    'day' => $dayNameInd,
                                    'hour' => $hourVal,
                                    'time_range' => $hourInfo['start'] . ' - ' . $hourInfo['end'],
                                    'kelas' => $kelasName,
                                    'subject' => $subjectName,
                                    'status_key' => 'hadir_tepat',
                                    'status_label' => 'Hadir Tepat',
                                    'jam_datang' => null,
                                    'menit_terlambat' => 0,
                                    'petugas_name' => $log['petugas_nama'] ?? null,
                                    'note' => $log['note']
                                ];
                            }
                        } elseif ($status === 'substitute' || $status === 'diganti') {
                            $report[$tid]['diganti']++;
                            $substName = $log['subst_nama'] ?? 'Guru Pengganti';
                            $report[$tid]['details'][] = [
                                'date' => $cur,
                                'day' => $dayNameInd,
                                'hour' => $hourVal,
                                'time_range' => $hourInfo['start'] . ' - ' . $hourInfo['end'],
                                'kelas' => $kelasName,
                                'subject' => $subjectName,
                                'status_key' => 'diganti',
                                'status_label' => 'Diganti (' . $substName . ')',
                                'substitute_name' => $substName,
                                'petugas_name' => $log['petugas_nama'] ?? null,
                                'note' => $log['note']
                            ];
                        } else {
                            // Alpha / tidak hadir
                            $report[$tid]['tidak_hadir']++;
                            $report[$tid]['details'][] = [
                                'date' => $cur,
                                'day' => $dayNameInd,
                                'hour' => $hourVal,
                                'time_range' => $hourInfo['start'] . ' - ' . $hourInfo['end'],
                                'kelas' => $kelasName,
                                'subject' => $subjectName,
                                'status_key' => 'tidak_hadir',
                                'status_label' => 'Tidak Hadir (Alpha)',
                                'petugas_name' => $log['petugas_nama'] ?? null,
                                'note' => $log['note']
                            ];
                        }
                    } elseif ($leave) {
                        // Official leave
                        $report[$tid]['diganti']++;
                        $substName = $leave['subst_nama'] ?? 'Guru Pengganti';
                        $report[$tid]['details'][] = [
                            'date' => $cur,
                            'day' => $dayNameInd,
                            'hour' => $hourVal,
                            'time_range' => $hourInfo['start'] . ' - ' . $hourInfo['end'],
                            'kelas' => $kelasName,
                            'subject' => $subjectName,
                            'status_key' => 'diganti',
                            'status_label' => 'Izin/Sakit (' . $substName . ')',
                            'substitute_name' => $substName,
                            'petugas_name' => 'Sistem Izin',
                            'note' => 'Izin resmi tercatat di sistem'
                        ];
                    } else {
                        // Unrecorded in the past
                        $report[$tid]['tidak_hadir']++;
                        $report[$tid]['details'][] = [
                            'date' => $cur,
                            'day' => $dayNameInd,
                            'hour' => $hourVal,
                            'time_range' => $hourInfo['start'] . ' - ' . $hourInfo['end'],
                            'kelas' => $kelasName,
                            'subject' => $subjectName,
                            'status_key' => 'tidak_hadir',
                            'status_label' => 'Tidak Tercatat / Alpha',
                            'petugas_name' => null,
                            'note' => 'Tidak ada catatan kehadiran pada jam ini'
                        ];
                    }
                }
            }

            $cur = date('Y-m-d', strtotime($cur . ' +1 day'));
        }

        // 10. Kehadiran and Ketepatan Waktu percentages and status badges
        $globalStats = [
            'total_jadwal' => 0,
            'total_hadir_tepat' => 0,
            'total_terlambat' => 0,
            'total_menit_terlambat' => 0,
            'total_diganti' => 0,
            'total_tidak_hadir' => 0,
            'avg_kehadiran' => 0,
            'avg_ketepatan' => 0,
            'avg_kepatuhan' => 0
        ];

        $sumKehadiran = 0;
        $countKehadiran = 0;
        $sumKetepatan = 0;
        $countKetepatan = 0;

        foreach ($report as &$r) {
            // 1. Kehadiran (%) = (hadir_tepat + terlambat + diganti) / expected
            $totalHadirIzin = $r['hadir_tepat'] + $r['terlambat'] + $r['diganti'];
            if ($r['expected'] > 0) {
                $r['kehadiran_pct'] = min(100, max(0, round(($totalHadirIzin / $r['expected']) * 100)));
                $sumKehadiran += $r['kehadiran_pct'];
                $countKehadiran++;
            } else {
                $r['kehadiran_pct'] = 0;
            }

            // 2. Ketepatan Waktu (%) = total_skor_ketepatan / (hadir_tepat + terlambat)
            $totalSesiHadir = $r['hadir_tepat'] + $r['terlambat'];
            if ($totalSesiHadir > 0) {
                $r['ketepatan_pct'] = min(100, max(0, round(($r['total_skor_ketepatan'] / $totalSesiHadir) * 100)));
                $sumKetepatan += $r['ketepatan_pct'];
                $countKetepatan++;
            } else {
                $r['ketepatan_pct'] = null; // Belum ada sesi hadir
            }

            // Kehadiran badge & bar styling
            if ($r['kehadiran_pct'] >= 85) {
                $r['kehadiran_bar_color'] = 'bg-emerald-500';
                $r['kehadiran_text_color'] = 'text-emerald-700';
            } elseif ($r['kehadiran_pct'] >= 70) {
                $r['kehadiran_bar_color'] = 'bg-blue-500';
                $r['kehadiran_text_color'] = 'text-blue-700';
            } elseif ($r['kehadiran_pct'] >= 50) {
                $r['kehadiran_bar_color'] = 'bg-amber-400';
                $r['kehadiran_text_color'] = 'text-amber-700';
            } else {
                $r['kehadiran_bar_color'] = 'bg-rose-500';
                $r['kehadiran_text_color'] = 'text-rose-700';
            }

            // Ketepatan badge & bar styling
            if ($r['ketepatan_pct'] === null) {
                $r['ketepatan_bar_color'] = 'bg-gray-300';
                $r['ketepatan_text_color'] = 'text-gray-400';
            } elseif ($r['ketepatan_pct'] >= 85) {
                $r['ketepatan_bar_color'] = 'bg-emerald-500';
                $r['ketepatan_text_color'] = 'text-emerald-700';
            } elseif ($r['ketepatan_pct'] >= 70) {
                $r['ketepatan_bar_color'] = 'bg-blue-500';
                $r['ketepatan_text_color'] = 'text-blue-700';
            } elseif ($r['ketepatan_pct'] >= 50) {
                $r['ketepatan_bar_color'] = 'bg-amber-400';
                $r['ketepatan_text_color'] = 'text-amber-700';
            } else {
                $r['ketepatan_bar_color'] = 'bg-rose-500';
                $r['ketepatan_text_color'] = 'text-rose-700';
            }

            // Overall evaluation score (composite)
            $scoreForStatus = ($r['ketepatan_pct'] !== null)
                ? round(($r['kehadiran_pct'] * 0.5) + ($r['ketepatan_pct'] * 0.5))
                : $r['kehadiran_pct'];
            $r['pct'] = $scoreForStatus; // backward compatibility

            if ($scoreForStatus >= 85) {
                $r['status_level'] = 4;
                $r['status_text'] = 'Sangat Baik';
                $r['badge_color'] = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                $r['bar_color'] = 'bg-emerald-500';
            } elseif ($scoreForStatus >= 70) {
                $r['status_level'] = 3;
                $r['status_text'] = 'Baik';
                $r['badge_color'] = 'bg-blue-50 text-blue-700 border border-blue-200';
                $r['bar_color'] = 'bg-blue-500';
            } elseif ($scoreForStatus >= 50) {
                $r['status_level'] = 2;
                $r['status_text'] = 'Cukup';
                $r['badge_color'] = 'bg-amber-50 text-amber-700 border border-amber-200';
                $r['bar_color'] = 'bg-amber-400';
            } else {
                $r['status_level'] = 1;
                $r['status_text'] = 'Perlu Perhatian';
                $r['badge_color'] = 'bg-rose-50 text-rose-700 border border-rose-200';
                $r['bar_color'] = 'bg-rose-500';
            }

            $globalStats['total_jadwal'] += $r['expected'];
            $globalStats['total_hadir_tepat'] += $r['hadir_tepat'];
            $globalStats['total_terlambat'] += $r['terlambat'];
            $globalStats['total_menit_terlambat'] += $r['total_menit_terlambat'];
            $globalStats['total_diganti'] += $r['diganti'];
            $globalStats['total_tidak_hadir'] += $r['tidak_hadir'];
        }
        unset($r);

        $globalStats['avg_kehadiran'] = $countKehadiran > 0 ? round($sumKehadiran / $countKehadiran) : 0;
        $globalStats['avg_ketepatan'] = $countKetepatan > 0 ? round($sumKetepatan / $countKetepatan) : 0;
        $globalStats['avg_kepatuhan'] = $globalStats['avg_kehadiran'];

        // Default sort: highest kehadiran first, then ketepatan, then name
        uasort($report, function($a, $b) {
            if ($a['kehadiran_pct'] !== $b['kehadiran_pct']) {
                return $b['kehadiran_pct'] <=> $a['kehadiran_pct'];
            }
            $ketA = $a['ketepatan_pct'] ?? -1;
            $ketB = $b['ketepatan_pct'] ?? -1;
            if ($ketA !== $ketB) {
                return $ketB <=> $ketA;
            }
            return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
        });

        return [
            'report' => $report,
            'globalStats' => $globalStats
        ];
    }
}

