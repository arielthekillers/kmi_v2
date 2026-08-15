<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ActivityModel extends Model {
    protected $table = 'school_activities';

    /**
     * Activity Resolver Pusat
     * Menentukan apakah suatu kelas pada tanggal dan jam tertentu terdampak oleh kegiatan sekolah.
     * Logika ini digunakan sebagai Single Source of Truth oleh semua modul (Jurnal, Absensi, Statistik).
     * 
     * @param string $date (Y-m-d)
     * @param int $kelasId
     * @param int $hour
     * @return array ['affected' => bool, 'activity_id' => int|null, 'activity_name' => string|null]
     */
    public function getEffectiveSchedule($date, $kelasId, $hour) {
        // Cek apakah hari tersebut adalah hari Jumat (Libur Mingguan)
        $dayOfWeek = date('w', strtotime($date));
        if ($dayOfWeek == 5) { // 5 adalah hari Jumat
            return [
                'affected' => true,
                'activity_id' => null,
                'activity_name' => 'Libur Mingguan (Jumat)'
            ];
        }

        // Cari kegiatan yang cocok dengan tanggal dan kelas (berkat ekspansi target)
        $stmt = $this->db->prepare("
            SELECT a.id, a.name, a.is_full_day
            FROM school_activities a
            JOIN activity_targets t ON a.id = t.activity_id
            WHERE t.kelas_id = ? 
              AND ? BETWEEN a.start_date AND a.end_date
        ");
        $stmt->execute([$kelasId, $date]);
        
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($activities as $activity) {
            // Jika kegiatan berlangsung sehari penuh (full day), jam berapapun pasti terdampak
            if ($activity['is_full_day']) {
                return [
                    'affected' => true,
                    'activity_id' => $activity['id'],
                    'activity_name' => $activity['name']
                ];
            }
            
            // Jika kegiatan hanya berlangsung pada jam tertentu, cek apakah jam saat ini termasuk
            $stmtHour = $this->db->prepare("
                SELECT 1 
                FROM activity_hours 
                WHERE activity_id = ? 
                  AND ? BETWEEN hour_start AND hour_end
                LIMIT 1
            ");
            $stmtHour->execute([$activity['id'], $hour]);
            
            if ($stmtHour->fetchColumn()) {
                return [
                    'affected' => true,
                    'activity_id' => $activity['id'],
                    'activity_name' => $activity['name']
                ];
            }
        }

        // Jika tidak ada kegiatan yang relevan, KBM berjalan normal (efektif)
        return [
            'affected' => false,
            'activity_id' => null,
            'activity_name' => null
        ];
    }

    /**
     * Menyimpan data kegiatan beserta jam dan target kelasnya dalam satu transaksi.
     */
    public function createActivity($data, $kelasIds, $academic_calendar_id = null) {
        if (!$this->academic_year_id) {
            throw new \Exception("Tidak ada tahun ajaran aktif.");
        }

        try {
            $this->db->beginTransaction();

            // 1. Insert school_activities
            $stmtAct = $this->db->prepare("
                INSERT INTO school_activities (academic_year_id, academic_calendar_id, name, type, start_date, end_date, is_full_day)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtAct->execute([
                $this->academic_year_id,
                $academic_calendar_id,
                $data['name'],
                $data['type'],
                $data['start_date'],
                $data['end_date'],
                $data['is_full_day']
            ]);
            
            $activityId = $this->db->lastInsertId();

            // 2. Insert activity_hours
            if (!$data['is_full_day'] && $data['hour_start'] > 0 && $data['hour_end'] > 0) {
                $stmtHours = $this->db->prepare("INSERT INTO activity_hours (activity_id, hour_start, hour_end) VALUES (?, ?, ?)");
                $stmtHours->execute([$activityId, $data['hour_start'], $data['hour_end']]);
            }

            // 3. Insert activity_targets
            if (!empty($kelasIds)) {
                // Menggunakan INSERT IGNORE untuk menangani perlindungan Unique Key secara diam-diam
                $stmtTarget = $this->db->prepare("INSERT IGNORE INTO activity_targets (activity_id, kelas_id) VALUES (?, ?)");
                foreach ($kelasIds as $kid) {
                    if ($kid) {
                        $stmtTarget->execute([$activityId, $kid]);
                    }
                }
            }

            $this->db->commit();
            return $activityId;

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getAllActivities() {
        $stmt = $this->db->prepare("
            SELECT * FROM school_activities 
            WHERE academic_year_id = ? 
            ORDER BY start_date DESC
        ");
        $stmt->execute([$this->academic_year_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil semua dispensasi KBM yang aktif pada tanggal tertentu beserta detail target kelas dan jamnya.
     * Menggunakan query tunggal untuk optimasi batch di memori PHP.
     * 
     * @param string $date (Y-m-d)
     * @return array
     */
    public function getDispensationsByDate($date) {
        $stmt = $this->db->prepare("
            SELECT a.id, a.name, a.is_full_day,
                   GROUP_CONCAT(DISTINCT t.kelas_id) as target_kelas_ids
            FROM school_activities a
            JOIN activity_targets t ON a.id = t.activity_id
            WHERE ? BETWEEN a.start_date AND a.end_date
            GROUP BY a.id
        ");
        $stmt->execute([$date]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($activities as $act) {
            $actId = $act['id'];
            $kelasIds = $act['target_kelas_ids'] ? array_map('intval', explode(',', $act['target_kelas_ids'])) : [];
            
            $hours = [];
            if (!$act['is_full_day']) {
                $stmtHour = $this->db->prepare("SELECT hour_start, hour_end FROM activity_hours WHERE activity_id = ?");
                $stmtHour->execute([$actId]);
                $hours = $stmtHour->fetchAll(PDO::FETCH_ASSOC);
            }

            $result[] = [
                'id' => $actId,
                'name' => $act['name'],
                'is_full_day' => (bool)$act['is_full_day'],
                'kelas_ids' => $kelasIds,
                'hours' => $hours
            ];
        }
        return $result;
    }

    /**
     * Mengambil semua dispensasi KBM yang aktif pada rentang tanggal tertentu beserta detail target kelas dan jamnya.
     * 
     * @param string $startDate (Y-m-d)
     * @param string $endDate (Y-m-d)
     * @return array [date => [dispensations]]
     */
    public function getDispensationsByRange($startDate, $endDate, $academicYearId = null) {
        $ayId = $academicYearId ?? $this->academic_year_id;
        $stmt = $this->db->prepare("
            SELECT a.id, a.name, a.is_full_day, a.start_date, a.end_date,
                   GROUP_CONCAT(DISTINCT t.kelas_id) as target_kelas_ids
            FROM school_activities a
            JOIN activity_targets t ON a.id = t.activity_id
            WHERE a.academic_year_id = ?
              AND (a.start_date <= ? AND a.end_date >= ?)
            GROUP BY a.id
        ");
        $stmt->execute([$ayId, $endDate, $startDate]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hoursMap = [];
        $actIds = array_column($activities, 'id');
        if (!empty($actIds)) {
            $inClause = implode(',', array_fill(0, count($actIds), '?'));
            $stmtHour = $this->db->prepare("SELECT activity_id, hour_start, hour_end FROM activity_hours WHERE activity_id IN ($inClause)");
            $stmtHour->execute($actIds);
            while ($row = $stmtHour->fetch(PDO::FETCH_ASSOC)) {
                $hoursMap[$row['activity_id']][] = [
                    'hour_start' => (int)$row['hour_start'],
                    'hour_end' => (int)$row['hour_end']
                ];
            }
        }

        // We can pre-process this into a day-by-day mapping for fast lookup in PHP
        $dispensationsByDate = [];
        $current = $startDate;
        while ($current <= $endDate) {
            $dispensationsByDate[$current] = [];

            foreach ($activities as $act) {
                if ($current >= $act['start_date'] && $current <= $act['end_date']) {
                    $kelasIds = $act['target_kelas_ids'] ? array_map('intval', explode(',', $act['target_kelas_ids'])) : [];
                    $dispensationsByDate[$current][] = [
                        'id' => $act['id'],
                        'name' => $act['name'],
                        'is_full_day' => (bool)$act['is_full_day'],
                        'kelas_ids' => $kelasIds,
                        'hours' => $hoursMap[$act['id']] ?? []
                    ];
                }
            }
            $current = date('Y-m-d', strtotime($current . ' +1 day'));
        }

        return $dispensationsByDate;
    }
}
