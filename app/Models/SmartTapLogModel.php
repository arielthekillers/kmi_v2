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

        // 3.5. Cek apakah ada Waiver / Pengecualian Presensi (Udzur Cuaca / Libur Perpulangan)
        $waiverModel = new \App\Models\SmartTapWaiverModel();
        $activeWaiver = $waiverModel->getActiveWaiver($eventId, $event['category'] ?? null, $date);

        // 4. Build Attendance Lists
        $listHadir = [];
        $listTerlambat = [];
        $listIzin = [];
        $listWaived = [];
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
            } elseif ($activeWaiver) {
                // Sesi ini terkena Udzur Cuaca / Libur: TIDAK dianggap Ghaib!
                $listWaived[] = array_merge($t, [
                    'waiver_title' => $activeWaiver['title'],
                    'waiver_reason' => $activeWaiver['reason_type']
                ]);
            } else {
                $listGhaib[] = $t;
            }
        }

        $isWaived = !empty($activeWaiver);
        return [
            'event' => $event,
            'date' => $date,
            'is_waived' => $isWaived,
            'waiver' => $activeWaiver,
            'stats' => [
                'total_wajib' => count($allTargets),
                'total_hadir_ontime' => count($listHadir),
                'total_terlambat' => count($listTerlambat),
                'total_izin' => count($listIzin),
                'total_waived' => count($listWaived),
                'total_ghaib' => count($listGhaib),
                'is_waived' => $isWaived,
                'waiver_title' => $activeWaiver['title'] ?? null,
                'waiver_reason' => $activeWaiver['reason_type'] ?? null,
                'percent_presence' => $isWaived ? 100.0 : (count($allTargets) > 0 ? round(((count($listHadir) + count($listTerlambat)) / count($allTargets)) * 100, 1) : 0)
            ],
            'hadir' => $listHadir,
            'terlambat' => $listTerlambat,
            'izin' => $listIzin,
            'waived' => $listWaived,
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
     * Rekapitulasi Komprehensif & Evaluasi Kedisiplinan:
     * Menghitung Kewajiban Kehadiran (Target Sesi) per individu berdasarkan sesi terselenggara,
     * status pengecualian (exemption), target spesifik, dan gender.
     * Mengkalkulasi kehadiran (Tepat & Terlambat), izin resmi, serta alpa secara presisi.
     */
    public function getComprehensiveRecapReport($startDate, $endDate, $targetType = 'all', $eventId = null, $classId = null)
    {
        // 1. Ambil detail event terpilih (jika spesifik) atau semua event aktif
        $event = null;
        if (!empty($eventId)) {
            $stmtEv = $this->db->prepare("SELECT * FROM smart_tap_events WHERE id = ?");
            $stmtEv->execute([(int)$eventId]);
            $event = $stmtEv->fetch(PDO::FETCH_ASSOC);
        }

        $sqlAllEv = "SELECT * FROM smart_tap_events WHERE is_active = 1";
        if (!empty($eventId)) {
            $sqlAllEv .= " AND id = " . (int)$eventId;
        }
        $eventsList = $this->db->query($sqlAllEv)->fetchAll(PDO::FETCH_ASSOC);
        $eventMap = [];
        foreach ($eventsList as $ev) {
            $eventMap[(int)$ev['id']] = $ev;
        }

        // Tentukan target tipe efektif:
        $effectiveTarget = $targetType;
        if ($event) {
            if ($event['target_type'] === 'teachers') {
                $effectiveTarget = 'teachers';
            } elseif ($event['target_type'] === 'students') {
                $effectiveTarget = 'students';
            }
        }

        // 2. Ambil sesi yang terselenggara (memiliki log absensi masuk) pada rentang tanggal & event
        $whereSess = ["DATE(l.tapped_at) BETWEEN ? AND ?"];
        $paramSess = [$startDate, $endDate];
        if (!empty($eventId)) {
            $whereSess[] = "l.event_id = ?";
            $paramSess[] = (int)$eventId;
        }
        if ($effectiveTarget === 'teachers') {
            $whereSess[] = "l.person_type = 'teacher'";
        } elseif ($effectiveTarget === 'students') {
            $whereSess[] = "l.person_type = 'student'";
        }
        $whereSessStr = implode(" AND ", $whereSess);

        $sqlSessions = "SELECT l.event_id, DATE(l.tapped_at) as session_date, COUNT(*) as tap_count
                        FROM smart_tap_logs l
                        WHERE $whereSessStr
                        GROUP BY l.event_id, DATE(l.tapped_at)
                        ORDER BY session_date ASC, l.event_id ASC";
        $stmtSess = $this->db->prepare($sqlSessions);
        $stmtSess->execute($paramSess);
        $sessionsHeld = $stmtSess->fetchAll(PDO::FETCH_ASSOC);

        // Tambahkan sesi hari ini jika sedang aktif / terjadwal hari ini (meski belum ada yang tap)
        $today = date('Y-m-d');
        if ($today >= $startDate && $today <= $endDate) {
            $todayDow = (int)date('N');
            foreach ($eventsList as $ev) {
                $dows = array_map('intval', explode(',', $ev['days_of_week']));
                if (in_array($todayDow, $dows)) {
                    $alreadyIn = false;
                    foreach ($sessionsHeld as $sh) {
                        if ($sh['session_date'] === $today && (int)$sh['event_id'] === (int)$ev['id']) {
                            $alreadyIn = true;
                            break;
                        }
                    }
                    if (!$alreadyIn) {
                        $sessionsHeld[] = [
                            'event_id' => (int)$ev['id'],
                            'session_date' => $today,
                            'tap_count' => 0
                        ];
                    }
                }
            }
        }

        // 3. Ambil data pengecualian (exemptions)
        $allExemptions = $this->db->query("SELECT * FROM smart_tap_exemptions")->fetchAll(PDO::FETCH_ASSOC);
        $exemptionByTeacher = [];
        foreach ($allExemptions as $ex) {
            $exemptionByTeacher[(int)$ex['user_id']][] = $ex;
        }

        // 4. Ambil data perizinan & dispensasi yang beririsan dengan rentang tanggal
        $sqlPerms = "SELECT person_type, person_id, permission_type, reason, event_ids, start_date, end_date
                     FROM smart_tap_permissions
                     WHERE start_date <= ? AND end_date >= ?";
        $stmtPerms = $this->db->prepare($sqlPerms);
        $stmtPerms->execute([$endDate, $startDate]);
        $rawPerms = $stmtPerms->fetchAll(PDO::FETCH_ASSOC);
        $permMap = [];
        foreach ($rawPerms as $p) {
            $key = $p['person_type'] . '_' . $p['person_id'];
            $permMap[$key][] = $p;
        }

        // 5. Ambil data log aktual dalam rentang tanggal
        $whereL = ["DATE(l.tapped_at) BETWEEN ? AND ?"];
        $paramsL = [$startDate, $endDate];
        if (!empty($eventId)) {
            $whereL[] = "l.event_id = ?";
            $paramsL[] = (int)$eventId;
        }
        if ($effectiveTarget !== 'all') {
            $whereL[] = "l.person_type = ?";
            $paramsL[] = $effectiveTarget === 'students' ? 'student' : 'teacher';
        }
        $whereClauseL = implode(" AND ", $whereL);

        $sqlLogs = "SELECT l.person_type, l.person_id, l.event_id, DATE(l.tapped_at) as tap_date, l.status
                    FROM smart_tap_logs l
                    WHERE $whereClauseL";
        $stmtLogs = $this->db->prepare($sqlLogs);
        $stmtLogs->execute($paramsL);
        $rawLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        // Petakan log: person_key => [ 'date_eventId' => best_status ]
        $logMap = [];
        foreach ($rawLogs as $l) {
            $key = $l['person_type'] . '_' . $l['person_id'];
            $sessKey = $l['tap_date'] . '_' . $l['event_id'];
            if (!isset($logMap[$key][$sessKey]) || $l['status'] === 'on_time') {
                $logMap[$key][$sessKey] = $l['status'];
            }
        }

        // 6. Siapkan Target Baseline Peserta
        $participants = [];

        // Pengajar
        if (in_array($effectiveTarget, ['all', 'teachers'])) {
            $sqlTeachers = "SELECT u.id, u.username as identifier, u.nama as name, 'teacher' as person_type,
                                   'Pengajar / Guru' as subinfo, tp.nip, tp.gender
                            FROM users u
                            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                            WHERE u.role = 'pengajar' AND u.deleted_at IS NULL AND u.is_active = 1
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
                    'gender' => $t['gender'] ?? 'L',
                    'kelas_id' => null
                ];
            }
        }

        // Santri
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

            $sqlStudents = "SELECT s.id, s.nis as identifier, s.nama as name, 'student' as person_type,
                                   CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, '')) as subinfo,
                                   s.gender, se.kelas_id
                            FROM students s
                            INNER JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                            LEFT JOIN kelas k ON se.kelas_id = k.id
                            WHERE s.deleted_at IS NULL $classFilter
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
                    'gender' => $s['gender'] ?? 'L',
                    'kelas_id' => (int)$s['kelas_id']
                ];
            }
        }

        // 7. Hitung Akumulasi Kewajiban, Kehadiran, dan Kedisiplinan per Individu
        $rows = [];
        $summary = [
            'total_target' => count($participants),
            'total_sesi_terselenggara' => count($sessionsHeld),
            'sum_kewajiban_all' => 0,
            'sum_hadir_all' => 0,
            'sum_ontime_all' => 0,
            'sum_late_all' => 0,
            'sum_izin_all' => 0,
            'sum_alpa_all' => 0,
            'percent_presence_aggregate' => 0,
            'total_peserta_disiplin' => 0,
            'total_peserta_warning' => 0,
            'total_peserta_late' => 0,
            'total_peserta_alpa' => 0,
            'total_peserta_izin' => 0
        ];

        foreach ($participants as $key => $p) {
            $pType = $p['person_type'];
            $pId = (int)$p['person_id'];
            $gender = $p['gender'] ?? 'L';
            $pClassId = (int)($p['kelas_id'] ?? 0);

            $kewajiban = 0;
            $countOnTime = 0;
            $countLate = 0;
            $countPermission = 0;
            $countAlpa = 0;
            $permReasons = [];

            foreach ($sessionsHeld as $sess) {
                $evId = (int)$sess['event_id'];
                $sessDate = $sess['session_date'];
                if (!isset($eventMap[$evId])) continue;
                $ev = $eventMap[$evId];

                // Cek apakah peserta memiliki kewajiban hadir di sesi ini
                $isObligated = false;
                if ($pType === 'teacher') {
                    if (in_array($ev['target_type'], ['all', 'teachers'])) {
                        // Cek pengecualian (exemption)
                        $isExempt = false;
                        if (isset($exemptionByTeacher[$pId])) {
                            foreach ($exemptionByTeacher[$pId] as $ex) {
                                if ($ex['scope'] === 'all') { $isExempt = true; break; }
                                if ($ex['scope'] === ($ev['category'] ?? '')) { $isExempt = true; break; }
                                if ($ex['scope'] === 'custom' && !empty($ex['event_ids'])) {
                                    $evIds = array_map('intval', explode(',', $ex['event_ids']));
                                    if (in_array($evId, $evIds)) { $isExempt = true; break; }
                                }
                            }
                        }

                        if (!$isExempt) {
                            $isObligated = true;
                            // Target guru spesifik
                            if (!empty($ev['target_teachers'])) {
                                $allowedT = array_map('intval', explode(',', $ev['target_teachers']));
                                if (!in_array($pId, $allowedT)) $isObligated = false;
                            }
                            // Gender filter
                            if ($isObligated && !empty($ev['target_gender']) && $ev['target_gender'] !== 'all') {
                                $isMale = in_array($gender, ['L', 'Laki-laki', null]);
                                if ($ev['target_gender'] === 'L' && !$isMale) $isObligated = false;
                                if ($ev['target_gender'] === 'P' && $isMale) $isObligated = false;
                            }
                        }
                    }
                } elseif ($pType === 'student') {
                    if (in_array($ev['target_type'], ['all', 'students'])) {
                        $classOk = true;
                        if (!empty($ev['target_classes'])) {
                            $allowedC = array_map('intval', explode(',', $ev['target_classes']));
                            if (!in_array($pClassId, $allowedC)) $classOk = false;
                        }
                        $genderOk = true;
                        if (!empty($ev['target_gender']) && $ev['target_gender'] !== 'all') {
                            if ($gender !== $ev['target_gender']) $genderOk = false;
                        }
                        if ($classOk && $genderOk) $isObligated = true;
                    }
                }

                if (!$isObligated) continue;

                $kewajiban++;

                // Evaluasi status presensi di sesi ini
                $sessKey = $sessDate . '_' . $evId;
                if (isset($logMap[$key][$sessKey])) {
                    $st = $logMap[$key][$sessKey];
                    if ($st === 'on_time') {
                        $countOnTime++;
                    } else {
                        $countLate++;
                    }
                } else {
                    // Cek apakah ada perizinan resmi di tanggal & event ini
                    $hasPerm = false;
                    $pReason = '';
                    if (isset($permMap[$key])) {
                        foreach ($permMap[$key] as $perm) {
                            if ($sessDate >= $perm['start_date'] && $sessDate <= $perm['end_date']) {
                                if (empty($perm['event_ids']) || in_array((string)$evId, explode(',', $perm['event_ids']))) {
                                    $hasPerm = true;
                                    $pReason = ucfirst($perm['permission_type']) . (!empty($perm['reason']) ? ': ' . $perm['reason'] : '');
                                    break;
                                }
                            }
                        }
                    }

                    if ($hasPerm) {
                        $countPermission++;
                        if (!in_array($pReason, $permReasons)) $permReasons[] = $pReason;
                    } else {
                        $countAlpa++;
                    }
                }
            }

            $totalHadir = $countOnTime + $countLate;
            $percentKehadiran = $kewajiban > 0 ? round(($totalHadir / $kewajiban) * 100, 1) : 100.0;
            $percentKepatuhan = $kewajiban > 0 ? round((($totalHadir + $countPermission) / $kewajiban) * 100, 1) : 100.0;
            $percentTepatWaktu = $totalHadir > 0 ? round(($countOnTime / $totalHadir) * 100, 1) : 0;

            // Tentukan status kedisiplinan objektif
            $statusDisiplin = 'disiplin';
            $statusLabel = 'Disiplin';
            $badgeColor = 'emerald';

            if ($kewajiban === 0) {
                $statusDisiplin = 'exempted';
                $statusLabel = 'Bebas Tugas';
                $badgeColor = 'gray';
            } elseif ($totalHadir === 0 && $countPermission === 0) {
                $statusDisiplin = 'alpa_total';
                $statusLabel = 'Tidak Hadir (' . $countAlpa . 'x Alpa)';
                $badgeColor = 'rose';
            } elseif ($percentKepatuhan >= 90 && $countLate === 0 && $countAlpa === 0) {
                $statusDisiplin = 'perfect';
                $statusLabel = 'Sangat Disiplin (100%)';
                $badgeColor = 'emerald';
            } elseif ($percentKepatuhan >= 85) {
                if ($countLate > 0) {
                    $statusDisiplin = 'disiplin_late';
                    $statusLabel = 'Tertib (Telat ' . $countLate . 'x)';
                    $badgeColor = 'teal';
                } else {
                    $statusDisiplin = 'disiplin';
                    $statusLabel = 'Disiplin (' . $percentKehadiran . '%)';
                    $badgeColor = 'emerald';
                }
            } elseif ($percentKepatuhan >= 70) {
                $statusDisiplin = 'warning';
                $statusLabel = 'Cukup (' . $countAlpa . 'x Alpa' . ($countLate > 0 ? ', ' . $countLate . 'x Telat' : '') . ')';
                $badgeColor = 'amber';
            } else {
                $statusDisiplin = 'poor';
                $statusLabel = 'Kurang Tertib (' . $countAlpa . 'x Alpa)';
                $badgeColor = 'rose';
            }

            // Summary counters
            $summary['sum_kewajiban_all'] += $kewajiban;
            $summary['sum_hadir_all'] += $totalHadir;
            $summary['sum_ontime_all'] += $countOnTime;
            $summary['sum_late_all'] += $countLate;
            $summary['sum_izin_all'] += $countPermission;
            $summary['sum_alpa_all'] += $countAlpa;

            if ($percentKepatuhan >= 85 && $kewajiban > 0) $summary['total_peserta_disiplin']++;
            if ($percentKepatuhan < 85 && $kewajiban > 0) $summary['total_peserta_warning']++;
            if ($countLate > 0) $summary['total_peserta_late']++;
            if ($countAlpa > 0) $summary['total_peserta_alpa']++;
            if ($countPermission > 0) $summary['total_peserta_izin']++;

            $rows[] = [
                'person_type' => $pType,
                'person_id' => $pId,
                'name' => $p['name'],
                'identifier' => $p['identifier'],
                'subinfo' => $p['subinfo'],
                'kewajiban' => $kewajiban,
                'total_attendance' => $totalHadir,
                'count_on_time' => $countOnTime,
                'count_late' => $countLate,
                'count_permission' => $countPermission,
                'permission_reasons' => implode('; ', $permReasons),
                'count_alpa' => $countAlpa,
                'percent_presence' => $percentKehadiran,
                'percent_compliance' => $percentKepatuhan,
                'percent_ontime' => $percentTepatWaktu,
                'status' => ($statusDisiplin === 'exempted' ? 'waived' : ($countAlpa > 0 && $totalHadir === 0 ? 'absent' : ($countLate > 0 ? 'late' : ($countPermission > 0 && $totalHadir === 0 ? 'permission' : 'ontime')))),
                'status_key' => $statusDisiplin,
                'status_label' => $statusLabel,
                'badge_color' => $badgeColor
            ];
        }

        // Summary Aggregates
        if ($summary['sum_kewajiban_all'] > 0) {
            $summary['percent_presence_aggregate'] = round(($summary['sum_hadir_all'] / $summary['sum_kewajiban_all']) * 100, 1);
        } else {
            $summary['percent_presence_aggregate'] = 100.0;
        }

        // Backward compatibility keys for existing views/reports
        $summary['total_hadir'] = $summary['sum_hadir_all'];
        $summary['total_ontime'] = $summary['sum_ontime_all'];
        $summary['total_late'] = $summary['sum_late_all'];
        $summary['total_izin'] = $summary['sum_izin_all'];
        $summary['total_tidak_hadir'] = $summary['sum_alpa_all'];
        $summary['percent_presence'] = $summary['percent_presence_aggregate'];
        $summary['sum_taps_ontime'] = $summary['sum_ontime_all'];
        $summary['sum_taps_late'] = $summary['sum_late_all'];
        $summary['sum_taps_total'] = $summary['sum_hadir_all'];

        // Urutkan default:
        // 1. Peserta dengan kewajiban > 0 didahulukan
        // 2. Peserta dengan % Kepatuhan terendah / Alpa terbanyak di atas (agar kedisiplinan langsung terlihat)
        // 3. Nama ASC
        usort($rows, function ($a, $b) {
            $hasObligA = $a['kewajiban'] > 0 ? 1 : 0;
            $hasObligB = $b['kewajiban'] > 0 ? 1 : 0;
            if ($hasObligA !== $hasObligB) {
                return $hasObligB <=> $hasObligA;
            }

            if ($a['kewajiban'] > 0) {
                if ($a['percent_compliance'] !== $b['percent_compliance']) {
                    return $a['percent_compliance'] <=> $b['percent_compliance'];
                }
                if ($a['count_alpa'] !== $b['count_alpa']) {
                    return $b['count_alpa'] <=> $a['count_alpa'];
                }
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
