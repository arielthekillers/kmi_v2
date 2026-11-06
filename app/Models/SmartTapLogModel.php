<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SmartTapLogModel extends Model
{
    protected $table = 'smart_tap_logs';

    /**
     * Batch sync logs sent from offline Mini PC kiosk.
     * Uses log_uuid for idempotency.
     */
    public function syncBatchLogs(array $logs, $deviceId = null)
    {
        if (empty($logs)) {
            return ['synced' => 0, 'skipped' => 0, 'errors' => []];
        }

        $synced = 0;
        $skipped = 0;
        $errors = [];

        $sql = "INSERT INTO smart_tap_logs (
            log_uuid, event_id, device_id, person_type, person_id, card_uid,
            tapped_at, status, note, synced_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            synced_at = NOW()";

        $stmt = $this->db->prepare($sql);

        foreach ($logs as $log) {
            try {
                $logUuid = !empty($log['log_uuid']) ? $log['log_uuid'] : md5(($log['event_id'] ?? 0) . '_' . ($log['card_uid'] ?? '') . '_' . ($log['tapped_at'] ?? ''));
                $eventId = (int)($log['event_id'] ?? 0);
                $devId = !empty($log['device_id']) ? (int)$log['device_id'] : $deviceId;
                $personType = in_array($log['person_type'] ?? '', ['student', 'teacher']) ? $log['person_type'] : 'student';
                $personId = (int)($log['person_id'] ?? 0);
                $cardUid = trim($log['card_uid'] ?? '');
                $tappedAt = !empty($log['tapped_at']) ? $log['tapped_at'] : date('Y-m-d H:i:s');
                $status = in_array($log['status'] ?? '', ['on_time', 'late', 'very_late', 'rejected']) ? $log['status'] : 'on_time';
                $note = $log['note'] ?? null;

                $stmt->execute([
                    $logUuid,
                    $eventId,
                    $devId,
                    $personType,
                    $personId,
                    $cardUid,
                    $tappedAt,
                    $status,
                    $note
                ]);

                if ($stmt->rowCount() > 0) {
                    $synced++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error on log {$log['card_uid']}: " . $e->getMessage();
            }
        }

        return [
            'synced' => $synced,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Get recent logs for live stream
     */
    public function getRecentLogs($eventId = null, $date = null, $limit = 30)
    {
        $date = $date ?: date('Y-m-d');
        $where = ["DATE(l.tapped_at) = ?"];
        $params = [$date];

        if ($eventId) {
            $where[] = "l.event_id = ?";
            $params[] = $eventId;
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT l.*,
                CASE 
                    WHEN l.person_type = 'student' THEN s.nama
                    WHEN l.person_type = 'teacher' THEN u.nama
                    ELSE 'Tidak Diketahui'
                END as person_name,
                CASE 
                    WHEN l.person_type = 'student' THEN s.nis
                    WHEN l.person_type = 'teacher' THEN u.username
                    ELSE '-'
                END as person_identifier,
                CASE 
                    WHEN l.person_type = 'student' THEN CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, ''))
                    WHEN l.person_type = 'teacher' THEN 'Pengajar / Guru'
                    ELSE '-'
                END as person_subinfo,
                d.device_name,
                e.name as event_name,
                COALESCE(e.category, 'sholat') as event_category
                FROM smart_tap_logs l
                LEFT JOIN smart_tap_events e ON l.event_id = e.id
                LEFT JOIN smart_tap_devices d ON l.device_id = d.id
                LEFT JOIN students s ON l.person_type = 'student' AND l.person_id = s.id
                LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                LEFT JOIN kelas k ON se.kelas_id = k.id
                LEFT JOIN users u ON l.person_type = 'teacher' AND l.person_id = u.id
                WHERE $whereClause
                ORDER BY l.tapped_at DESC
                LIMIT $limit";

        $execParams = array_merge([$this->academic_year_id], $params);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($execParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hitung ringkasan statistik (hadir, tepat waktu, telat) per event hari ini
     */
    public function getTodayEventsSummaryStats(array $events, $date = null)
    {
        $date = $date ?: date('Y-m-d');
        $sql = "SELECT event_id,
                       COUNT(*) as total_hadir,
                       SUM(CASE WHEN status = 'on_time' THEN 1 ELSE 0 END) as on_time,
                       SUM(CASE WHEN status IN ('late', 'very_late') THEN 1 ELSE 0 END) as late
                FROM smart_tap_logs
                WHERE DATE(tapped_at) = ?
                GROUP BY event_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lookup = [];
        foreach ($rows as $r) {
            $lookup[$r['event_id']] = [
                'total_hadir' => (int)$r['total_hadir'],
                'on_time' => (int)$r['on_time'],
                'late' => (int)$r['late']
            ];
        }

        $result = [];
        foreach ($events as $ev) {
            $id = $ev['id'];
            $st = $lookup[$id] ?? ['total_hadir' => 0, 'on_time' => 0, 'late' => 0];
            $result[$id] = array_merge($ev, $st);
        }
        return $result;
    }

    /**
     * Get live statistics and breakdown for a specific event today
     */
    public function getEventLiveStatus($eventId, $date = null)
    {
        $date = $date ?: date('Y-m-d');

        // Fetch event info
        $evStmt = $this->db->prepare("SELECT * FROM smart_tap_events WHERE id = ?");
        $evStmt->execute([$eventId]);
        $event = $evStmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) return null;

        // Khusus Hari Jumat untuk Sholat Dzuhur:
        // Jika hari ini hari Jumat (day 5), Sholat Dzuhur otomatis dialihkan khusus Jamaah Putri ('P')
        // karena Jamaah Putra melaksanakan Sholat Jumat.
        $dayOfWeek = (int)date('N', strtotime($date));
        if ($dayOfWeek === 5 && ($event['category'] ?? '') === 'sholat' && stripos($event['name'], 'Dzuhur') !== false && ($event['target_gender'] ?? 'all') === 'all') {
            $event['target_gender'] = 'P';
        }

        // 1. Target Audience
        $targetStudents = [];
        $targetTeachers = [];

        if (in_array($event['target_type'], ['all', 'students'])) {
            $classFilter = "";
            $pClass = [];
            if (!empty($event['target_classes'])) {
                $classes = explode(',', $event['target_classes']);
                $placeholders = implode(',', array_fill(0, count($classes), '?'));
                $classFilter = "AND se.kelas_id IN ($placeholders)";
                $pClass = $classes;
            }

            $genderFilterS = "";
            if (!empty($event['target_gender']) && $event['target_gender'] !== 'all') {
                $genderFilterS = " AND s.gender = " . $this->db->quote($event['target_gender']) . " ";
            }

            $sqlS = "SELECT s.id, s.nis, s.nama, s.rfid_uid, 'student' as person_type,
                            CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, '')) as subinfo,
                            se.kelas_id
                     FROM students s
                     INNER JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                     LEFT JOIN kelas k ON se.kelas_id = k.id
                     WHERE s.deleted_at IS NULL $classFilter $genderFilterS
                     ORDER BY k.tingkat ASC, k.abjad ASC, s.nama ASC";

            $stmtS = $this->db->prepare($sqlS);
            $stmtS->execute(array_merge([$this->academic_year_id], $pClass));
            $targetStudents = $stmtS->fetchAll(PDO::FETCH_ASSOC);
        }

        if (in_array($event['target_type'], ['all', 'teachers'])) {
            $exemptionModel = new \App\Models\SmartTapExemptionModel();
            $exemptedUserIds = $exemptionModel->getExemptedUserIds($eventId, $event['category'] ?? null);

            $exemptionFilter = "";
            if (!empty($exemptedUserIds)) {
                $exemptionFilter = " AND u.id NOT IN (" . implode(',', array_map('intval', $exemptedUserIds)) . ") ";
            }

            $genderFilterT = "";
            if (!empty($event['target_gender']) && $event['target_gender'] !== 'all') {
                if ($event['target_gender'] === 'L') {
                    $genderFilterT = " AND (tp.gender = 'Laki-laki' OR tp.gender = 'L' OR tp.gender IS NULL) ";
                } elseif ($event['target_gender'] === 'P') {
                    $genderFilterT = " AND (tp.gender = 'Perempuan' OR tp.gender = 'P') ";
                }
            }

            $targetTeacherFilter = "";
            if (!empty($event['target_teachers'])) {
                $teacherIds = array_filter(array_map('intval', explode(',', $event['target_teachers'])));
                if (!empty($teacherIds)) {
                    $targetTeacherFilter = " AND u.id IN (" . implode(',', $teacherIds) . ") ";
                }
            }

            $sqlT = "SELECT u.id, u.username as nis, u.nama, u.rfid_uid, 'teacher' as person_type,
                            'Pengajar / Guru' as subinfo, NULL as kelas_id
                     FROM users u
                     LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                     WHERE u.role = 'pengajar' AND u.deleted_at IS NULL AND u.is_active = 1
                     $exemptionFilter $genderFilterT $targetTeacherFilter
                     ORDER BY u.nama ASC";
            $targetTeachers = $this->db->query($sqlT)->fetchAll(PDO::FETCH_ASSOC);
        }

        // 2. Fetch all logs for this event & date
        $sqlL = "SELECT person_type, person_id, card_uid, tapped_at, status 
                 FROM smart_tap_logs 
                 WHERE event_id = ? AND DATE(tapped_at) = ? AND status != 'rejected'";
        $stmtL = $this->db->prepare($sqlL);
        $stmtL->execute([$eventId, $date]);
        $allLogs = $stmtL->fetchAll(PDO::FETCH_ASSOC);

        $loggedMap = [];
        foreach ($allLogs as $l) {
            $key = $l['person_type'] . '_' . $l['person_id'];
            if (!isset($loggedMap[$key])) {
                $loggedMap[$key] = $l;
            }
        }

        // 3. Fetch active permissions for this date & event
        $sqlP = "SELECT person_type, person_id, permission_type, reason, event_ids
                 FROM smart_tap_permissions
                 WHERE ? BETWEEN start_date AND end_date";
        $stmtP = $this->db->prepare($sqlP);
        $stmtP->execute([$date]);
        $allPerms = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        $permMap = [];
        foreach ($allPerms as $p) {
            if (empty($p['event_ids']) || in_array($eventId, explode(',', $p['event_ids']))) {
                $key = $p['person_type'] . '_' . $p['person_id'];
                $permMap[$key] = $p;
            }
        }

        // 4. Build Attendance Lists
        $listHadir = [];
        $listTerlambat = [];
        $listIzin = [];
        $listGhaib = [];

        $allTargets = array_merge($targetStudents, $targetTeachers);
        foreach ($allTargets as $t) {
            $key = $t['person_type'] . '_' . $t['id'];

            if (isset($loggedMap[$key])) {
                $logData = $loggedMap[$key];
                $item = array_merge($t, [
                    'tapped_at' => $logData['tapped_at'],
                    'status' => $logData['status']
                ]);

                if ($logData['status'] === 'on_time') {
                    $listHadir[] = $item;
                } else {
                    $listTerlambat[] = $item;
                }
            } elseif (isset($permMap[$key])) {
                $perm = $permMap[$key];
                $listIzin[] = array_merge($t, [
                    'permission_type' => $perm['permission_type'],
                    'reason' => $perm['reason']
                ]);
            } else {
                $listGhaib[] = $t;
            }
        }

        return [
            'event' => $event,
            'date' => $date,
            'stats' => [
                'total_wajib' => count($allTargets),
                'total_hadir_ontime' => count($listHadir),
                'total_terlambat' => count($listTerlambat),
                'total_izin' => count($listIzin),
                'total_ghaib' => count($listGhaib),
                'percent_presence' => count($allTargets) > 0 ? round(((count($listHadir) + count($listTerlambat)) / count($allTargets)) * 100, 1) : 0
            ],
            'hadir' => $listHadir,
            'terlambat' => $listTerlambat,
            'izin' => $listIzin,
            'ghaib' => $listGhaib
        ];
    }

    /**
     * Discipline & Attendance Recap across a date range
     */
    public function getRecapReport($startDate, $endDate, $targetType = 'all', $eventId = null, $classId = null)
    {
        $res = $this->getComprehensiveRecapReport($startDate, $endDate, $targetType, $eventId, $classId);
        return $res['rows'];
    }

    /**
     * Rekapitulasi Komprehensif: Menggabungkan Target Baseline (Pengajar/Santri),
     * Log Absensi Masuk (Tepat Waktu & Terlambat), serta Dispensasi/Izin Resmi.
     * Menghasilkan KPI Summary Cards & Tabel Lengkap (termasuk yang Tidak Hadir / Alpa).
     */
    public function getComprehensiveRecapReport($startDate, $endDate, $targetType = 'all', $eventId = null, $classId = null)
    {
        // 1. Ambil detail event jika event_id dipilih
        $event = null;
        if (!empty($eventId)) {
            $stmtEv = $this->db->prepare("SELECT * FROM smart_tap_events WHERE id = ?");
            $stmtEv->execute([(int)$eventId]);
            $event = $stmtEv->fetch(PDO::FETCH_ASSOC);
        }

        // Tentukan target tipe efektif:
        $effectiveTarget = $targetType;
        if ($event) {
            if ($event['target_type'] === 'teachers') {
                $effectiveTarget = 'teachers';
            } elseif ($event['target_type'] === 'students') {
                $effectiveTarget = 'students';
            }

            // Khusus Hari Jumat untuk Sholat Dzuhur jika filter rentang 1 hari pada hari Jumat:
            if (($event['category'] ?? '') === 'sholat' && stripos($event['name'], 'Dzuhur') !== false && ($event['target_gender'] ?? 'all') === 'all') {
                if ($startDate === $endDate && (int)date('N', strtotime($startDate)) === 5) {
                    $event['target_gender'] = 'P';
                }
            }
        }

        $participants = [];

        // 2. Ambil data Pengajar jika target mencakup 'all' atau 'teachers'
        if (in_array($effectiveTarget, ['all', 'teachers'])) {
            $exemptionModel = new \App\Models\SmartTapExemptionModel();
            $exemptedUserIds = $exemptionModel->getExemptedUserIds($eventId, $event['category'] ?? null);

            $exemptionFilter = "";
            if (!empty($exemptedUserIds)) {
                $exemptionFilter = " AND u.id NOT IN (" . implode(',', array_map('intval', $exemptedUserIds)) . ") ";
            }

            $genderFilterT = "";
            if ($event && !empty($event['target_gender']) && $event['target_gender'] !== 'all') {
                if ($event['target_gender'] === 'L') {
                    $genderFilterT = " AND (tp.gender = 'Laki-laki' OR tp.gender = 'L' OR tp.gender IS NULL) ";
                } elseif ($event['target_gender'] === 'P') {
                    $genderFilterT = " AND (tp.gender = 'Perempuan' OR tp.gender = 'P') ";
                }
            }

            $targetTeacherFilter = "";
            if ($event && !empty($event['target_teachers'])) {
                $teacherIds = array_filter(array_map('intval', explode(',', $event['target_teachers'])));
                if (!empty($teacherIds)) {
                    $targetTeacherFilter = " AND u.id IN (" . implode(',', $teacherIds) . ") ";
                }
            }

            $sqlTeachers = "SELECT u.id, u.username as identifier, u.nama as name, 'teacher' as person_type,
                                   'Pengajar / Guru' as subinfo, tp.nip, tp.phone
                            FROM users u
                            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                            WHERE u.role = 'pengajar' AND u.deleted_at IS NULL AND u.is_active = 1
                            $exemptionFilter $genderFilterT $targetTeacherFilter
                            ORDER BY u.nama ASC";
            $teachers = $this->db->query($sqlTeachers)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($teachers as $t) {
                $key = 'teacher_' . $t['id'];
                $participants[$key] = [
                    'person_type' => 'teacher',
                    'person_id' => (int)$t['id'],
                    'name' => $t['name'],
                    'identifier' => $t['identifier'] ?: ($t['nip'] ?: '-'),
                    'subinfo' => 'Pengajar / Guru',
                    'count_on_time' => 0,
                    'count_late' => 0,
                    'count_very_late' => 0,
                    'total_attendance' => 0,
                    'has_permission' => false,
                    'permission_type' => null,
                    'permission_reason' => null,
                    'status' => 'absent'
                ];
            }
        }

        // 3. Ambil data Santri jika target mencakup 'all' atau 'students'
        if (in_array($effectiveTarget, ['all', 'students'])) {
            $classFilter = "";
            $pClass = [];

            $targetClasses = [];
            if (!empty($classId)) {
                $targetClasses = [(int)$classId];
            } elseif ($event && !empty($event['target_classes'])) {
                $targetClasses = array_map('intval', explode(',', $event['target_classes']));
            }

            if (!empty($targetClasses)) {
                $placeholders = implode(',', array_fill(0, count($targetClasses), '?'));
                $classFilter = " AND se.kelas_id IN ($placeholders) ";
                $pClass = $targetClasses;
            }

            $genderFilterS = "";
            if ($event && !empty($event['target_gender']) && $event['target_gender'] !== 'all') {
                $genderFilterS = " AND s.gender = " . $this->db->quote($event['target_gender']) . " ";
            }

            $sqlStudents = "SELECT s.id, s.nis as identifier, s.nama as name, 'student' as person_type,
                                   CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, '')) as subinfo,
                                   se.kelas_id
                            FROM students s
                            INNER JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                            LEFT JOIN kelas k ON se.kelas_id = k.id
                            WHERE s.deleted_at IS NULL $classFilter $genderFilterS
                            ORDER BY k.tingkat ASC, k.abjad ASC, s.nama ASC";

            $stmtS = $this->db->prepare($sqlStudents);
            $stmtS->execute(array_merge([$this->academic_year_id], $pClass));
            $students = $stmtS->fetchAll(PDO::FETCH_ASSOC);

            foreach ($students as $s) {
                $key = 'student_' . $s['id'];
                $participants[$key] = [
                    'person_type' => 'student',
                    'person_id' => (int)$s['id'],
                    'name' => $s['name'],
                    'identifier' => $s['identifier'] ?: '-',
                    'subinfo' => $s['subinfo'] ?: 'Santri',
                    'count_on_time' => 0,
                    'count_late' => 0,
                    'count_very_late' => 0,
                    'total_attendance' => 0,
                    'has_permission' => false,
                    'permission_type' => null,
                    'permission_reason' => null,
                    'status' => 'absent'
                ];
            }
        }

        // 4. Ambil catatan log presensi pada rentang tanggal & event
        $whereL = ["DATE(l.tapped_at) BETWEEN ? AND ?"];
        $paramsL = [$startDate, $endDate];

        if ($effectiveTarget !== 'all') {
            $whereL[] = "l.person_type = ?";
            $paramsL[] = $effectiveTarget === 'students' ? 'student' : 'teacher';
        }

        if ($eventId) {
            $whereL[] = "l.event_id = ?";
            $paramsL[] = (int)$eventId;
        }

        $whereClauseL = implode(" AND ", $whereL);

        $sqlLogs = "SELECT l.person_type, l.person_id,
                           COUNT(CASE WHEN l.status = 'on_time' THEN 1 END) as count_on_time,
                           COUNT(CASE WHEN l.status = 'late' THEN 1 END) as count_late,
                           COUNT(CASE WHEN l.status = 'very_late' THEN 1 END) as count_very_late,
                           COUNT(CASE WHEN l.status != 'rejected' THEN 1 END) as total_attendance,
                           CASE 
                               WHEN l.person_type = 'student' THEN s.nama
                               WHEN l.person_type = 'teacher' THEN u.nama
                           END as fallback_name,
                           CASE 
                               WHEN l.person_type = 'student' THEN s.nis
                               WHEN l.person_type = 'teacher' THEN u.username
                           END as fallback_identifier,
                           CASE 
                               WHEN l.person_type = 'student' THEN CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, ''))
                               WHEN l.person_type = 'teacher' THEN 'Pengajar / Guru'
                           END as fallback_subinfo
                    FROM smart_tap_logs l
                    LEFT JOIN students s ON l.person_type = 'student' AND l.person_id = s.id
                    LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                    LEFT JOIN kelas k ON se.kelas_id = k.id
                    LEFT JOIN users u ON l.person_type = 'teacher' AND l.person_id = u.id
                    WHERE $whereClauseL
                    GROUP BY l.person_type, l.person_id";

        $stmtLogs = $this->db->prepare($sqlLogs);
        $stmtLogs->execute(array_merge([$this->academic_year_id], $paramsL));
        $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        foreach ($logs as $log) {
            $key = $log['person_type'] . '_' . $log['person_id'];
            if (!isset($participants[$key])) {
                $participants[$key] = [
                    'person_type' => $log['person_type'],
                    'person_id' => (int)$log['person_id'],
                    'name' => $log['fallback_name'] ?: 'Tanpa Nama',
                    'identifier' => $log['fallback_identifier'] ?: '-',
                    'subinfo' => $log['fallback_subinfo'] ?: '-',
                    'count_on_time' => 0,
                    'count_late' => 0,
                    'count_very_late' => 0,
                    'total_attendance' => 0,
                    'has_permission' => false,
                    'permission_type' => null,
                    'permission_reason' => null,
                    'status' => 'absent'
                ];
            }

            $participants[$key]['count_on_time'] = (int)$log['count_on_time'];
            $participants[$key]['count_late'] = (int)$log['count_late'];
            $participants[$key]['count_very_late'] = (int)$log['count_very_late'];
            $participants[$key]['total_attendance'] = (int)$log['total_attendance'];
        }

        // 5. Ambil data perizinan & dispensasi yang beririsan dengan tanggal laporan
        $sqlPerms = "SELECT person_type, person_id, permission_type, reason, event_ids, start_date, end_date
                     FROM smart_tap_permissions
                     WHERE start_date <= ? AND end_date >= ?";
        $stmtPerms = $this->db->prepare($sqlPerms);
        $stmtPerms->execute([$endDate, $startDate]);
        $perms = $stmtPerms->fetchAll(PDO::FETCH_ASSOC);

        foreach ($perms as $p) {
            if ($eventId && !empty($p['event_ids'])) {
                $allowedEvents = explode(',', $p['event_ids']);
                if (!in_array((string)$eventId, $allowedEvents)) {
                    continue;
                }
            }

            $key = $p['person_type'] . '_' . $p['person_id'];
            if (isset($participants[$key])) {
                $participants[$key]['has_permission'] = true;
                $participants[$key]['permission_type'] = $p['permission_type'];
                $participants[$key]['permission_reason'] = $p['reason'];
            }
        }

        // 6. Hitung Status Kedisiplinan & Summary KPI
        $summary = [
            'total_target' => count($participants),
            'total_hadir' => 0,
            'total_ontime' => 0,
            'total_late' => 0,
            'total_izin' => 0,
            'total_tidak_hadir' => 0,
            'percent_presence' => 0,
            'sum_taps_ontime' => 0,
            'sum_taps_late' => 0,
            'sum_taps_total' => 0
        ];

        foreach ($participants as &$p) {
            $totalLate = $p['count_late'] + $p['count_very_late'];
            $summary['sum_taps_ontime'] += $p['count_on_time'];
            $summary['sum_taps_late'] += $totalLate;
            $summary['sum_taps_total'] += $p['total_attendance'];

            if ($p['total_attendance'] > 0) {
                $summary['total_hadir']++;
                if ($totalLate == 0) {
                    $p['status'] = 'ontime';
                    $summary['total_ontime']++;
                } else {
                    $p['status'] = 'late';
                    $summary['total_late']++;
                }
            } elseif ($p['has_permission']) {
                $p['status'] = 'permission';
                $summary['total_izin']++;
            } else {
                $p['status'] = 'absent';
                $summary['total_tidak_hadir']++;
            }
        }
        unset($p);

        if ($summary['total_target'] > 0) {
            $summary['percent_presence'] = round(($summary['total_hadir'] / $summary['total_target']) * 100, 1);
        }

        // 7. Urutkan: Terlambat -> Tidak Hadir -> Izin -> Tepat Waktu, lalu Nama ASC
        $rows = array_values($participants);
        usort($rows, function ($a, $b) {
            $rank = ['late' => 1, 'absent' => 2, 'permission' => 3, 'ontime' => 4];
            $rankA = $rank[$a['status']] ?? 9;
            $rankB = $rank[$b['status']] ?? 9;

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }
            return strcmp($a['name'], $b['name']);
        });

        return [
            'summary' => $summary,
            'rows' => $rows
        ];
    }

    /**
     * Pindahkan catatan log dari satu event ke event lain untuk tanggal tertentu
     * dan otomatis sesuaikan status ketepatan waktu dengan sesi tujuan
     */
    public function reassignEventLogs($fromEventId, $toEventId, $date = null)
    {
        $date = $date ?: date('Y-m-d');

        // Ambil info jadwal event tujuan
        $eventStmt = $this->db->prepare("SELECT open_time, start_time, close_time, after_close_action FROM smart_tap_events WHERE id = ?");
        $eventStmt->execute([(int)$toEventId]);
        $targetEvent = $eventStmt->fetch(PDO::FETCH_ASSOC);

        // Ambil seluruh log yang tercatat di event asal pada tanggal tersebut
        $logsStmt = $this->db->prepare("SELECT id, tapped_at FROM smart_tap_logs WHERE event_id = ? AND DATE(tapped_at) = ?");
        $logsStmt->execute([(int)$fromEventId, $date]);
        $logs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($logs)) {
            return 0;
        }

        $updateStmt = $this->db->prepare("UPDATE smart_tap_logs SET event_id = ?, status = ? WHERE id = ?");
        $count = 0;

        foreach ($logs as $row) {
            $status = 'on_time';
            if ($targetEvent) {
                $tapTime = date('H:i:s', strtotime($row['tapped_at']));
                $startTime = $targetEvent['start_time'] ?? '00:00:00';
                $closeTime = $targetEvent['close_time'] ?? '23:59:59';
                $afterClose = $targetEvent['after_close_action'] ?? 'reject';

                if ($tapTime <= $startTime) {
                    $status = 'on_time';
                } elseif ($tapTime <= $closeTime) {
                    $status = 'late';
                } else {
                    $status = ($afterClose === 'very_late') ? 'very_late' : 'late';
                }
            }

            $updateStmt->execute([(int)$toEventId, $status, (int)$row['id']]);
            $count++;
        }

        return $count;
    }
}
