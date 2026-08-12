<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AttendanceModel;
use App\Models\StudentAttendanceModel;
use App\Models\TanqihModel;
use App\Models\KelasModel;
use App\Models\TeacherLeaveModel;
use App\Models\AcademicYearModel;

class WeeklyReportController extends Controller {
    public function index() {
        if (!is_logged_in() || (auth_get_role() !== 'admin' && !auth_is_pbm())) {
            redirect('/?error=Unauthorized');
        }

        $weekOptions = $this->generateWeeksList();
        
        $this->view('weekly_report/index', [
            'week_options' => $weekOptions,
            'title' => 'Laporan Mingguan'
        ]);
    }

    private function generateWeeksList() {
        $dayOfWeek = date('w'); // 0 (Sun) to 6 (Sat)
        
        // If it's Friday (5), the current week's Thursday was yesterday.
        // If it's Saturday (6), the current week's Thursday is +5 days.
        // For Sun (0) to Thu (4), the current week's Thursday is + (4 - $dayOfWeek) days.
        if ($dayOfWeek == 5) {
            $currentThu = date('Y-m-d', strtotime('-1 day'));
        } elseif ($dayOfWeek == 6) {
            $currentThu = date('Y-m-d', strtotime('+5 days'));
        } else {
            $diff = 4 - $dayOfWeek;
            $currentThu = date('Y-m-d', strtotime("+$diff days"));
        }
        
        $bulanId = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $groupedOptions = [];
        $thu = $currentThu;
        
        // Generate options for the past 24 weeks (approx 6 months)
        for ($i = 0; $i < 24; $i++) {
            $sat = date('Y-m-d', strtotime('-5 days', strtotime($thu)));
            $month = (int)date('n', strtotime($thu));
            $year = (int)date('Y', strtotime($thu));
            
            $monthKey = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
            $monthLabel = $bulanId[$month] . ' ' . $year;
            
            if (!isset($groupedOptions[$monthKey])) {
                $groupedOptions[$monthKey] = [
                    'label' => $monthLabel,
                    'weeks' => []
                ];
            }
            
            $firstThu = date('Y-m-d', strtotime('first Thursday of ' . date('F Y', strtotime($thu))));
            $diffDays = (strtotime($thu) - strtotime($firstThu)) / 86400;
            $weekNum = round($diffDays / 7) + 1;
            
            $label = "Minggu ke-$weekNum";
            if ($i === 0) {
                $label .= " (Minggu Ini)";
            } elseif ($i === 1) {
                $label .= " (Minggu Lalu)";
            }
            
            $groupedOptions[$monthKey]['weeks'][] = [
                'value' => $sat . '|' . $thu,
                'label' => $label
            ];
            
            $thu = date('Y-m-d', strtotime('-7 days', strtotime($thu)));
        }
        
        return $groupedOptions;
    }

    public function printTeacherAttendance() {
        if (!is_logged_in() || (auth_get_role() !== 'admin' && !auth_is_pbm())) {
            die('Unauthorized');
        }

        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        if (!$start || !$end) die('Periode tidak valid');

        $attendanceModel = new AttendanceModel();
        // We need to build the report for teachers.
        // It's better to fetch from AttendanceModel using getReportStats, then aggregate.
        $rawLogs = $attendanceModel->getReportStats($start, $end);
        
        // However, we also need expected hours (Jumlah Jam Mengajar).
        // Since we don't have a direct query for weekly expected hours, we can infer from the schedules table.
        // I will write a custom query in the controller to get expected schedules per teacher for the given days.
        $db = \App\Core\Database::getInstance()->getConnection();
        $ayId = $_SESSION['academic_year_id'] ?? null;
        if (!$ayId) {
            $ayModel = new \App\Models\AcademicYearModel();
            $activeAy = $ayModel->getActive();
            $ayId = $activeAy['id'] ?? null;
        }
        
        $schedulesSql = "SELECT s.*, u.nama as teacher_nama FROM schedules s JOIN users u ON s.teacher_id = u.id WHERE s.academic_year_id = ?";
        $stmt = $db->prepare($schedulesSql);
        $stmt->execute([$ayId]);
        $schedules = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Map English day to Indonesian
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];

        // Determine which days are in the range
        $daysInRange = [];
        $currentDate = $start;
        
        // Cap the effective end date to today to avoid calculating future expected schedules
        $today = date('Y-m-d');
        $effectiveEnd = (strtotime($end) > strtotime($today)) ? $today : $end;
        
        while (strtotime($currentDate) <= strtotime($effectiveEnd)) {
            $dayNameEnglish = date('D', strtotime($currentDate));
            $daysInRange[] = [
                'date' => $currentDate,
                'dayName' => $dayMap[$dayNameEnglish] ?? ''
            ];
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        $report = [];
        $substitutions = []; // For top substitute teachers
        
        // Init expected
        foreach ($schedules as $sched) {
            $tid = $sched['teacher_id'];
            $tname = $sched['teacher_nama'];
            if (!$tid) continue;
            
            if (!isset($report[$tid])) {
                $report[$tid] = [
                    'nama' => $tname,
                    'expected' => 0,
                    'hadir' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'alfa' => 0
                ];
            }
            
            // Check if this schedule falls on a day in our range
            foreach ($daysInRange as $dayObj) {
                if ($sched['day'] === $dayObj['dayName']) {
                    $report[$tid]['expected']++;
                }
            }
        }

        $processedSlots = [];

        // 1. Process TeacherLeaves (Izin Mengajar) as the PRIMARY source of truth
        // If they have an approved leave, it overrides whatever is in attendance_logs
        $leaveSql = "SELECT tl.date as leave_date, tl.teacher_id, tl.type, ts.kelas_id, ts.hour, ts.substitute_teacher_id, 
                            u2.nama as subst_nama
                     FROM teacher_leaves tl 
                     JOIN teaching_substitutions ts ON tl.id = ts.leave_id
                     LEFT JOIN users u2 ON ts.substitute_teacher_id = u2.id
                     WHERE tl.date BETWEEN ? AND ? AND tl.academic_year_id = ?";
        $leaveStmt = $db->prepare($leaveSql);
        $leaveStmt->execute([$start, $end, $ayId]);
        $teacherLeaves = $leaveStmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($teacherLeaves as $leave) {
            $tid = $leave['teacher_id'];
            if (!$tid || !isset($report[$tid])) continue;

            $slotKey = $leave['leave_date'] . '|' . $leave['kelas_id'] . '|' . $leave['hour'];
            
            // Count it as izin or sakit
            if ($leave['type'] === 'sakit') {
                $report[$tid]['sakit']++;
            } else {
                $report[$tid]['izin']++;
            }
            $processedSlots[$slotKey] = true;

            // Track substitute stats
            $subId = $leave['substitute_teacher_id'];
            if ($subId) {
                if (!isset($substitutions[$subId])) {
                    $substitutions[$subId] = ['nama' => $leave['subst_nama'], 'count' => 0];
                }
                $substitutions[$subId]['count']++;
            }
        }

        // 2. Process raw logs (actual daily attendance)
        foreach ($rawLogs as $log) {
            $tid = $log['teacher_id'];
            if (!$tid) continue;
            
            // If the teacher has no expected schedule
            if (!isset($report[$tid])) {
                $report[$tid] = [
                    'nama' => $log['teacher_nama'],
                    'expected' => 0,
                    'hadir' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'alfa' => 0
                ];
            }

            $slotKey = $log['date'] . '|' . $log['kelas_id'] . '|' . $log['hour'];
            
            // If already processed by teacher_leaves (meaning they have an approved leave), SKIP!
            // This prevents human error where Admin marks them as 'hadir' despite having an approved leave.
            if (isset($processedSlots[$slotKey])) continue;

            $processedSlots[$slotKey] = true;

            if ($log['status'] === 'hadir') {
                $report[$tid]['hadir']++;
            } elseif ($log['status'] === 'substitute') {
                if (stripos($log['note'], 'sakit') !== false) {
                    $report[$tid]['sakit']++;
                } else {
                    $report[$tid]['izin']++;
                }
                
                // Track substitute stats
                $subId = $log['substitute_teacher_id'];
                if ($subId) {
                    if (!isset($substitutions[$subId])) {
                        $substitutions[$subId] = ['nama' => $log['subst_nama'], 'count' => 0];
                    }
                    $substitutions[$subId]['count']++;
                }
            } elseif ($log['status'] === 'alpha') {
                $report[$tid]['alfa']++;
            }
        }

        // Calculate %
        foreach ($report as $tid => &$data) {
            if ($data['expected'] > 0) {
                // We assume any hour NOT explicitly marked as absent (sakit, izin, alfa) is HADIR.
                $totalAbsent = $data['sakit'] + $data['izin'] + $data['alfa'];
                $data['hadir'] = max(0, $data['expected'] - $totalAbsent);
                
                $data['pct_s'] = round(($data['sakit'] / $data['expected']) * 100, 2);
                $data['pct_i'] = round(($data['izin'] / $data['expected']) * 100, 2);
                $data['pct_a'] = round(($data['alfa'] / $data['expected']) * 100, 2);
                $data['pct_hadir'] = round(($data['hadir'] / $data['expected']) * 100, 2);
            } else {
                $data['pct_s'] = $data['pct_i'] = $data['pct_a'] = $data['pct_hadir'] = 0;
            }
        }
        
        // Remove teachers with 0 expected or 100% attendance (sakit + izin + alfa == 0)
        $report = array_filter($report, function($r) { 
            return $r['expected'] > 0 && ($r['sakit'] > 0 || $r['izin'] > 0 || $r['alfa'] > 0); 
        });

        // Sort by % Kehadiran ASC (worse attendance first), then absolute absences DESC, then by nama ASC
        usort($report, function($a, $b) { 
            // 1. Sort by % Kehadiran ASC
            if ($a['pct_hadir'] !== $b['pct_hadir']) {
                return $a['pct_hadir'] <=> $b['pct_hadir'];
            }
            
            // 2. Sort by total absolute absences DESC (more hours missed = worse)
            $absentA = $a['sakit'] + $a['izin'] + $a['alfa'];
            $absentB = $b['sakit'] + $b['izin'] + $b['alfa'];
            if ($absentA !== $absentB) {
                return $absentB <=> $absentA;
            }
            
            // 3. Fallback to Nama ASC
            return strcmp($a['nama'], $b['nama']);
        });

        // Sort substitutions by count descending
        usort($substitutions, function($a, $b) { return $b['count'] <=> $a['count']; });
        
        // Keep top 10 substitutions
        $topSubstitutions = array_slice($substitutions, 0, 10);

        $this->view('weekly_report/print_teacher', [
            'start' => $start,
            'end' => $end,
            'report' => $report,
            'topSubstitutions' => $topSubstitutions,
            'title' => 'Laporan Mingguan Kehadiran Guru'
        ]);
    }

    public function printStudentAttendance() {
        if (!is_logged_in() || (auth_get_role() !== 'admin' && !auth_is_pbm())) {
            die('Unauthorized');
        }

        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        if (!$start || !$end) die('Periode tidak valid');

        $ayId = $_SESSION['academic_year_id'] ?? null;
        if (!$ayId) {
            $ayModel = new \App\Models\AcademicYearModel();
            $activeAy = $ayModel->getActive();
            $ayId = $activeAy['id'] ?? null;
        }

        $db = \App\Core\Database::getInstance()->getConnection();

        // Get all classes
        $kelasStmt = $db->prepare("SELECT * FROM kelas WHERE academic_year_id = ? ORDER BY tingkat ASC, abjad ASC");
        $kelasStmt->execute([$ayId]);
        $kelasList = $kelasStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get student count per class (excluding soft-deleted students)
        $enrStmt = $db->prepare("
            SELECT se.kelas_id, COUNT(se.student_id) as total 
            FROM student_enrollments se
            JOIN students s ON se.student_id = s.id 
            WHERE se.academic_year_id = ? AND se.status = 'Active' AND s.deleted_at IS NULL 
            GROUP BY se.kelas_id
        ");
        $enrStmt->execute([$ayId]);
        $enrData = $enrStmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Get absences per class in range
        $absStmt = $db->prepare("
            SELECT kelas_id, type, COUNT(student_id) as total
            FROM student_absences 
            WHERE date BETWEEN ? AND ?
            GROUP BY kelas_id, type
        ");
        $absStmt->execute([$start, $end]);
        $absRaw = $absStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $absences = [];
        foreach ($absRaw as $row) {
            $absences[$row['kelas_id']][$row['type']] = $row['total'];
        }

        $hariEfektif = 6; // Hardcoded to 6 (Sat-Thu) as per user request

        $report = [];
        foreach ($kelasList as $k) {
            $kid = $k['id'];
            $jumlahSantri = $enrData[$kid] ?? 0;
            
            if ($jumlahSantri == 0) continue;

            $sakit = $absences[$kid]['sakit'] ?? 0;
            $izin = $absences[$kid]['izin'] ?? 0;
            $alfa = $absences[$kid]['alpha'] ?? 0;

            $totalStudentDays = $jumlahSantri * $hariEfektif;
            $totalAbsences = $sakit + $izin + $alfa;
            
            $pct_s = $totalStudentDays > 0 ? round(($sakit / $totalStudentDays) * 100, 2) : 0;
            $pct_i = $totalStudentDays > 0 ? round(($izin / $totalStudentDays) * 100, 2) : 0;
            $pct_a = $totalStudentDays > 0 ? round(($alfa / $totalStudentDays) * 100, 2) : 0;
            
            $pct_hadir = $totalStudentDays > 0 ? round((($totalStudentDays - $totalAbsences) / $totalStudentDays) * 100, 2) : 0;

            $report[] = [
                'kelas' => $k['tingkat'] . ' ' . $k['abjad'],
                'hari_efektif' => $hariEfektif,
                'jumlah_santri' => $jumlahSantri,
                'alfa' => $alfa,
                'pct_a' => $pct_a,
                'izin' => $izin,
                'pct_i' => $pct_i,
                'sakit' => $sakit,
                'pct_s' => $pct_s,
                'pct_hadir' => $pct_hadir,
            ];
        }

        // Sort by class name naturally (e.g. 1 B, 1 C, 2 A)
        usort($report, function($a, $b) {
            // Extracts number and letter for better sorting
            preg_match('/(\d+)\s*(.*)/', $a['kelas'], $matchA);
            preg_match('/(\d+)\s*(.*)/', $b['kelas'], $matchB);
            
            $numA = isset($matchA[1]) ? (int)$matchA[1] : 0;
            $numB = isset($matchB[1]) ? (int)$matchB[1] : 0;
            
            if ($numA == $numB) {
                return strcmp(isset($matchA[2]) ? $matchA[2] : '', isset($matchB[2]) ? $matchB[2] : '');
            }
            return $numA <=> $numB;
        });

        // Get Top 3 Highest and Lowest
        $sortedByHadir = $report;
        usort($sortedByHadir, function($a, $b) { return $b['pct_hadir'] <=> $a['pct_hadir']; });
        
        $top3 = array_slice($sortedByHadir, 0, 3);
        $bottom3 = array_slice(array_reverse($sortedByHadir), 0, 3);

        $this->view('weekly_report/print_student', [
            'start' => $start,
            'end' => $end,
            'report' => $report,
            'top3' => $top3,
            'bottom3' => $bottom3,
            'title' => 'Laporan Mingguan Kehadiran Santri'
        ]);
    }

    public function printTanqih() {
        if (!is_logged_in() || (auth_get_role() !== 'admin' && !auth_is_pbm())) {
            die('Unauthorized');
        }

        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        if (!$start || !$end) die('Periode tidak valid');

        $tanqihModel = new TanqihModel();
        $statsData = $tanqihModel->getReportStats($start, $end);
        $report = $statsData['report'];
        
        // Ensure justified is NOT counted towards verified_real
        // Wait, getReportStats already does this. It calculates pct based on verified_real.

        // Calculate pct for each and build array
        $finalReport = [];
        $stats = [
            '100' => 0,
            '76_99' => 0,
            '51_75' => 0,
            '26_50' => 0,
            '0_25' => 0
        ];

        foreach ($report as $r) {
            $pct = $r['expected'] > 0 ? round(($r['verified_real'] / $r['expected']) * 100, 2) : 0;
            
            $finalReport[] = [
                'nama' => $r['name'],
                'expected' => $r['expected'],
                'pct' => $pct
            ];

            // Calculate stats
            if ($pct == 100) $stats['100']++;
            elseif ($pct >= 76) $stats['76_99']++;
            elseif ($pct >= 51) $stats['51_75']++;
            elseif ($pct >= 26) $stats['26_50']++;
            else $stats['0_25']++;
        }

        usort($finalReport, function($a, $b) {
            if ($a['pct'] == $b['pct']) {
                return strcmp($a['nama'], $b['nama']);
            }
            return $b['pct'] <=> $a['pct']; // DESC
        });

        // Filter highest (100% or top 10)
        $highest = array_filter($finalReport, function($r) { return $r['pct'] == 100; });
        if (empty($highest)) {
            $highest = array_slice($finalReport, 0, 10);
        }

        // Lowest (Top 10 lowest)
        $lowest = array_slice(array_reverse($finalReport), 0, 10);

        $this->view('weekly_report/print_tanqih', [
            'start' => $start,
            'end' => $end,
            'highest' => $highest,
            'lowest' => $lowest,
            'stats' => $stats,
            'title' => 'Laporan Mingguan Tanqih Idad'
        ]);
    }
}
