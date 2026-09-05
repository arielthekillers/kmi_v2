<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class KelasModel extends Model {
    protected $table = 'kelas';

    protected function getActiveYearId() {
        $year = $this->db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch();
        return $year ? (int)$year['id'] : 0;
    }

    /**
     * @return array[]
     */
    public function findAllActive() {
        $yearId = $this->getActiveYearId();
        $stmt = $this->db->prepare("SELECT * FROM kelas WHERE academic_year_id = ? ORDER BY tingkat ASC, abjad ASC");
        $stmt->execute([$yearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllGrouped() {
        $yearId = $this->getActiveYearId();

        $stmt = $this->db->prepare("
            SELECT k.*, 
                   CASE 
                       WHEN tp1.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u1.nama)
                       WHEN tp1.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u1.nama)
                       ELSE u1.nama
                   END as wali_kelas_1,
                   CASE 
                       WHEN tp2.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u2.nama)
                       WHEN tp2.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u2.nama)
                       ELSE u2.nama
                   END as wali_kelas_2,
                   (SELECT COUNT(*) 
                    FROM student_enrollments se 
                    JOIN students s ON se.student_id = s.id
                    WHERE se.kelas_id = k.id AND se.status IN ('Active', 'Graduated') AND s.deleted_at IS NULL) as jumlah_murid
            FROM kelas k
            LEFT JOIN users u1 ON k.teacher_id = u1.id
            LEFT JOIN teacher_profiles tp1 ON u1.id = tp1.user_id
            LEFT JOIN users u2 ON k.teacher_id_2 = u2.id
            LEFT JOIN teacher_profiles tp2 ON u2.id = tp2.user_id
            WHERE k.academic_year_id = ?
            ORDER BY tingkat ASC, abjad ASC
        ");
        $stmt->execute([$yearId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format combined wali_kelas display
        foreach ($rows as &$r) {
            $w1 = $r['wali_kelas_1'] ?? null;
            $w2 = $r['wali_kelas_2'] ?? null;
            if ($w1 && $w2) {
                $r['wali_kelas'] = $w1 . ' & ' . $w2;
            } elseif ($w1) {
                $r['wali_kelas'] = $w1;
            } elseif ($w2) {
                $r['wali_kelas'] = $w2;
            } else {
                $r['wali_kelas'] = null;
            }
        }
        unset($r);

        // Group by Tingkat
        $groupedKelas = [];
        foreach ($rows as $k) {
            $tingkat = $k['tingkat'] ?? 'Lainnya';
            $groupedKelas[$tingkat][] = $k;
        }

        // Sort Keys (Levels) naturally (1, 2, 10, etc.)
        uksort($groupedKelas, 'strnatcmp');
        
        return $groupedKelas;
    }

    public function create($data) {
        $yearId = $this->getActiveYearId();

        $stmt = $this->db->prepare("INSERT INTO kelas (tingkat, abjad, location, teacher_id, teacher_id_2, academic_year_id, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['tingkat'], 
            $data['abjad'], 
            $data['location'] ?? null, 
            !empty($data['teacher_id']) ? $data['teacher_id'] : null,
            !empty($data['teacher_id_2']) ? $data['teacher_id_2'] : null,
            $yearId,
            $data['gender'] ?? 'Pa'
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE kelas SET tingkat = ?, abjad = ?, location = ?, teacher_id = ?, teacher_id_2 = ?, gender = ? WHERE id = ?");
        return $stmt->execute([
            $data['tingkat'], 
            $data['abjad'], 
            $data['location'] ?? null, 
            !empty($data['teacher_id']) ? $data['teacher_id'] : null,
            !empty($data['teacher_id_2']) ? $data['teacher_id_2'] : null,
            $data['gender'] ?? 'Pa',
            $id
        ]);
    }

    public function find($id) {
        $stmt = $this->db->prepare("
            SELECT k.*, 
                   CASE 
                       WHEN tp1.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u1.nama)
                       WHEN tp1.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u1.nama)
                       ELSE u1.nama
                   END as wali_kelas_1,
                   CASE 
                       WHEN tp2.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u2.nama)
                       WHEN tp2.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u2.nama)
                       ELSE u2.nama
                   END as wali_kelas_2
            FROM kelas k 
            LEFT JOIN users u1 ON k.teacher_id = u1.id 
            LEFT JOIN teacher_profiles tp1 ON u1.id = tp1.user_id 
            LEFT JOIN users u2 ON k.teacher_id_2 = u2.id 
            LEFT JOIN teacher_profiles tp2 ON u2.id = tp2.user_id 
            WHERE k.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            $w1 = $res['wali_kelas_1'] ?? null;
            $w2 = $res['wali_kelas_2'] ?? null;
            if ($w1 && $w2) {
                $res['wali_kelas'] = $w1 . ' & ' . $w2;
            } elseif ($w1) {
                $res['wali_kelas'] = $w1;
            } elseif ($w2) {
                $res['wali_kelas'] = $w2;
            } else {
                $res['wali_kelas'] = null;
            }
        }
        return $res;
    }

    public function getStudentsWithDetails($id) {
        $yearId = $this->getActiveYearId();
        $stmt = $this->db->prepare("
            SELECT s.*, se.status as enrollment_status 
            FROM students s 
            INNER JOIN student_enrollments se ON s.id = se.student_id 
            WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status IN ('Active', 'Graduated') AND s.deleted_at IS NULL
            ORDER BY s.nama ASC
        ");
        $stmt->execute([$id, $yearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getScheduleWithDetails($id) {
        $yearId = $this->getActiveYearId();
        $stmt = $this->db->prepare("
            SELECT sch.*, sub.nama as subject_name, u.nama as teacher_name 
            FROM schedules sch 
            INNER JOIN (
                SELECT MAX(id) as max_id 
                FROM schedules 
                WHERE kelas_id = ? AND academic_year_id = ? 
                GROUP BY day, hour
            ) latest ON sch.id = latest.max_id
            LEFT JOIN subjects sub ON sch.subject_id = sub.id 
            LEFT JOIN users u ON sch.teacher_id = u.id 
            ORDER BY sch.day ASC, sch.hour ASC
        ");
        $stmt->execute([$id, $yearId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $schedule = [];
        foreach ($rows as $row) {
            $schedule[$row['day']][$row['hour']] = $row;
        }
        return $schedule;
    }
}
