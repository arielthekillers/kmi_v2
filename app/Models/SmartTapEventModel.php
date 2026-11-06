<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SmartTapEventModel extends Model
{
    protected $table = 'smart_tap_events';

    public function getAllEvents()
    {
        $stmt = $this->db->query("SELECT * FROM smart_tap_events ORDER BY open_time ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Memeriksa apakah suatu tanggal merupakan hari libur akademik atau dispensasi KBM seluruh kelas:
     * 1. Hari Jumat (libur mingguan pesantren/KMI)
     * 2. Libur di kalender akademik (academic_calendar_events dengan kategori = 'Libur')
     * 3. Dispensasi KBM seharian untuk seluruh kelas (school_activities dengan is_full_day = 1 dan target seluruh kelas)
     *
     * @param string $dateStr (Format Y-m-d)
     * @param int|null $academicYearId
     * @return array [is_holiday => bool, 'type' => string|null, 'reason' => string|null]
     */
    public function getAcademicHolidayInfo($dateStr, $academicYearId = null)
    {
        $ayId = $academicYearId ?? $this->academic_year_id;

        // 1. Cek Hari Jumat
        $dayOfWeek = (int)date('N', strtotime($dateStr));
        if ($dayOfWeek === 5) {
            return [
                'is_holiday' => true,
                'type' => 'friday',
                'reason' => 'Libur Mingguan (Jumat)'
            ];
        }

        // 2. Cek Libur Kalender Akademik
        $stmtCal = $this->db->prepare("
            SELECT keterangan 
            FROM academic_calendar_events 
            WHERE academic_year_id = ? 
              AND kategori = 'Libur'
              AND (
                (tanggal_selesai IS NOT NULL AND ? >= tanggal_mulai AND ? <= tanggal_selesai)
                OR
                (tanggal_selesai IS NULL AND tanggal_mulai = ?)
              )
            LIMIT 1
        ");
        $stmtCal->execute([$ayId, $dateStr, $dateStr, $dateStr]);
        $calRow = $stmtCal->fetch(PDO::FETCH_ASSOC);
        if ($calRow) {
            return [
                'is_holiday' => true,
                'type' => 'calendar_holiday',
                'reason' => 'Kalender Akademik: ' . ($calRow['keterangan'] ?: 'Libur Resmi')
            ];
        }

        // 3. Cek Dispensasi KBM Seharian untuk Seluruh Kelas
        $stmtTotalK = $this->db->prepare("SELECT COUNT(*) FROM kelas WHERE academic_year_id = ?");
        $stmtTotalK->execute([$ayId]);
        $totalKelas = (int)$stmtTotalK->fetchColumn();

        if ($totalKelas > 0) {
            $stmtAct = $this->db->prepare("
                SELECT a.name, a.type, COUNT(DISTINCT t.kelas_id) as target_count
                FROM school_activities a
                LEFT JOIN activity_targets t ON a.id = t.activity_id
                WHERE a.academic_year_id = ?
                  AND a.is_full_day = 1
                  AND (a.start_date <= ? AND a.end_date >= ?)
                GROUP BY a.id
                HAVING target_count >= ? OR a.type = 'Libur'
                LIMIT 1
            ");
            $stmtAct->execute([$ayId, $dateStr, $dateStr, $totalKelas]);
            $actRow = $stmtAct->fetch(PDO::FETCH_ASSOC);
            if ($actRow) {
                return [
                    'is_holiday' => true,
                    'type' => 'dispensasi_kbm',
                    'reason' => 'Dispensasi KBM Seluruh Kelas: ' . $actRow['name']
                ];
            }
        }

        return [
            'is_holiday' => false,
            'type' => null,
            'reason' => null
        ];
    }

    public function getActiveEventsForToday($dayOfWeek, $dateStr, $deviceId = null)
    {
        // $dayOfWeek: 1 (Mon) - 7 (Sun)
        $deviceFilter = "";
        $params = [$dayOfWeek, $dateStr];

        if (!empty($deviceId)) {
            $deviceFilter = "AND (allow_any_device = 1 OR FIND_IN_SET(?, allowed_devices) > 0)";
            $params[] = $deviceId;
        }

        $sql = "SELECT * FROM smart_tap_events 
                WHERE is_active = 1 
                AND (
                    (event_type = 'daily' AND FIND_IN_SET(?, days_of_week) > 0)
                    OR 
                    (event_type = 'one_off' AND event_date = ?)
                )
                $deviceFilter
                ORDER BY open_time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $allEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter event yang tersinkron dengan kalender akademik / KBM jika hari ini libur
        $holidayInfo = null;
        $activeEvents = [];
        foreach ($allEvents as $ev) {
            $syncAcademic = isset($ev['sync_academic_calendar']) 
                ? (int)$ev['sync_academic_calendar'] 
                : (($ev['category'] ?? '') === 'office' ? 1 : 0);

            if ($syncAcademic) {
                if ($holidayInfo === null) {
                    $holidayInfo = $this->getAcademicHolidayInfo($dateStr);
                }
                if ($holidayInfo['is_holiday']) {
                    // Libur Jumat / Kalender Akademik / Dispensasi KBM Full Day, event ini otomatis dilewati!
                    continue;
                }
            }
            $activeEvents[] = $ev;
        }

        return $activeEvents;
    }

    public function getEventById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM smart_tap_events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createEvent($data)
    {
        $sql = "INSERT INTO smart_tap_events (
            name, category, event_type, event_date, days_of_week, target_type, target_classes,
            target_teachers, target_gender, open_time, start_time, close_time, after_close_action, allow_any_device,
            allowed_devices, is_active, sync_academic_calendar
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['category'] ?? 'sholat',
            $data['event_type'] ?? 'daily',
            !empty($data['event_date']) ? $data['event_date'] : null,
            $data['days_of_week'] ?? '1,2,3,4,5,6,7',
            $data['target_type'] ?? 'all',
            !empty($data['target_classes']) ? (is_array($data['target_classes']) ? implode(',', $data['target_classes']) : $data['target_classes']) : null,
            !empty($data['target_teachers']) ? (is_array($data['target_teachers']) ? implode(',', $data['target_teachers']) : $data['target_teachers']) : null,
            in_array($data['target_gender'] ?? '', ['all', 'L', 'P']) ? $data['target_gender'] : 'all',
            $data['open_time'],
            $data['start_time'],
            $data['close_time'],
            $data['after_close_action'] ?? 'reject',
            isset($data['allow_any_device']) ? (int)$data['allow_any_device'] : 1,
            !empty($data['allowed_devices']) ? (is_array($data['allowed_devices']) ? implode(',', $data['allowed_devices']) : $data['allowed_devices']) : null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            isset($data['sync_academic_calendar']) ? (int)$data['sync_academic_calendar'] : (($data['category'] ?? '') === 'office' ? 1 : 0)
        ]);
        return $this->db->lastInsertId();
    }

    public function updateEvent($id, $data)
    {
        $sql = "UPDATE smart_tap_events SET 
            name = ?, category = ?, event_type = ?, event_date = ?, days_of_week = ?, target_type = ?, target_classes = ?,
            target_teachers = ?, target_gender = ?, open_time = ?, start_time = ?, close_time = ?, after_close_action = ?, allow_any_device = ?,
            allowed_devices = ?, is_active = ?, sync_academic_calendar = ?
            WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['category'] ?? 'sholat',
            $data['event_type'] ?? 'daily',
            !empty($data['event_date']) ? $data['event_date'] : null,
            $data['days_of_week'] ?? '1,2,3,4,5,6,7',
            $data['target_type'] ?? 'all',
            !empty($data['target_classes']) ? (is_array($data['target_classes']) ? implode(',', $data['target_classes']) : $data['target_classes']) : null,
            !empty($data['target_teachers']) ? (is_array($data['target_teachers']) ? implode(',', $data['target_teachers']) : $data['target_teachers']) : null,
            in_array($data['target_gender'] ?? '', ['all', 'L', 'P']) ? $data['target_gender'] : 'all',
            $data['open_time'],
            $data['start_time'],
            $data['close_time'],
            $data['after_close_action'] ?? 'reject',
            isset($data['allow_any_device']) ? (int)$data['allow_any_device'] : 1,
            !empty($data['allowed_devices']) ? (is_array($data['allowed_devices']) ? implode(',', $data['allowed_devices']) : $data['allowed_devices']) : null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            isset($data['sync_academic_calendar']) ? (int)$data['sync_academic_calendar'] : (($data['category'] ?? '') === 'office' ? 1 : 0),
            $id
        ]);
    }

    public function deleteEvent($id)
    {
        // Hapus juga seluruh riwayat presensi yang terkait dengan event ini agar bersih dari database
        $stmtLogs = $this->db->prepare("DELETE FROM smart_tap_logs WHERE event_id = ?");
        $stmtLogs->execute([(int)$id]);

        $stmt = $this->db->prepare("DELETE FROM smart_tap_events WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}

