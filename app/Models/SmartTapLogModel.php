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
                e.name as event_name
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

            $sqlS = "SELECT s.id, s.nis, s.nama, s.rfid_uid, 'student' as person_type,
                            CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, '')) as subinfo,
                            se.kelas_id
                     FROM students s
                     INNER JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                     LEFT JOIN kelas k ON se.kelas_id = k.id
                     WHERE s.deleted_at IS NULL $classFilter
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

            $sqlT = "SELECT u.id, u.username as nis, u.nama, u.rfid_uid, 'teacher' as person_type,
                            'Pengajar / Guru' as subinfo, NULL as kelas_id
                     FROM users u
                     WHERE u.role = 'pengajar' AND u.deleted_at IS NULL AND u.is_active = 1
                     $exemptionFilter
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
        $whereL = ["DATE(l.tapped_at) BETWEEN ? AND ?"];
        $paramsL = [$startDate, $endDate];

        if ($targetType !== 'all') {
            $whereL[] = "l.person_type = ?";
            $paramsL[] = $targetType === 'students' ? 'student' : 'teacher';
        }

        if ($eventId) {
            $whereL[] = "l.event_id = ?";
            $paramsL[] = $eventId;
        }

        $whereClauseL = implode(" AND ", $whereL);

        // Aggregation per person
        $sql = "SELECT l.person_type, l.person_id,
                       CASE 
                           WHEN l.person_type = 'student' THEN s.nama
                           WHEN l.person_type = 'teacher' THEN u.nama
                       END as name,
                       CASE 
                           WHEN l.person_type = 'student' THEN s.nis
                           WHEN l.person_type = 'teacher' THEN u.username
                       END as identifier,
                       CASE 
                           WHEN l.person_type = 'student' THEN CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, ''))
                           WHEN l.person_type = 'teacher' THEN 'Pengajar / Guru'
                       END as subinfo,
                       COUNT(CASE WHEN l.status = 'on_time' THEN 1 END) as count_on_time,
                       COUNT(CASE WHEN l.status = 'late' THEN 1 END) as count_late,
                       COUNT(CASE WHEN l.status = 'very_late' THEN 1 END) as count_very_late,
                       COUNT(CASE WHEN l.status != 'rejected' THEN 1 END) as total_attendance
                FROM smart_tap_logs l
                LEFT JOIN students s ON l.person_type = 'student' AND l.person_id = s.id
                LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                LEFT JOIN kelas k ON se.kelas_id = k.id
                LEFT JOIN users u ON l.person_type = 'teacher' AND l.person_id = u.id
                WHERE $whereClauseL
                GROUP BY l.person_type, l.person_id
                ORDER BY count_late DESC, count_very_late DESC, total_attendance DESC";

        $execParams = array_merge([$this->academic_year_id], $paramsL);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($execParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
