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
                   u.nama as wali_kelas,
                   (SELECT COUNT(*) 
                    FROM student_enrollments se 
                    WHERE se.kelas_id = k.id AND se.status = 'Active') as jumlah_murid
            FROM kelas k
            LEFT JOIN users u ON k.teacher_id = u.id
            WHERE k.academic_year_id = ?
            ORDER BY tingkat ASC, abjad ASC
        ");
        $stmt->execute([$yearId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        $stmt = $this->db->prepare("INSERT INTO kelas (tingkat, abjad, location, teacher_id, academic_year_id) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['tingkat'], 
            $data['abjad'], 
            $data['location'] ?? null, 
            !empty($data['teacher_id']) ? $data['teacher_id'] : null,
            $yearId
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE kelas SET tingkat = ?, abjad = ?, location = ?, teacher_id = ? WHERE id = ?");
        return $stmt->execute([
            $data['tingkat'], 
            $data['abjad'], 
            $data['location'] ?? null, 
            !empty($data['teacher_id']) ? $data['teacher_id'] : null,
            $id
        ]);
    }

    public function find($id) {
        $stmt = $this->db->prepare("
            SELECT k.*, u.nama as wali_kelas 
            FROM kelas k 
            LEFT JOIN users u ON k.teacher_id = u.id 
            WHERE k.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudentsWithDetails($id) {
        $yearId = $this->getActiveYearId();
        $stmt = $this->db->prepare("
            SELECT s.*, se.status as enrollment_status 
            FROM students s 
            INNER JOIN student_enrollments se ON s.id = se.student_id 
            WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status = 'Active' 
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
            LEFT JOIN subjects sub ON sch.subject_id = sub.id 
            LEFT JOIN users u ON sch.teacher_id = u.id 
            WHERE sch.kelas_id = ? AND sch.academic_year_id = ? 
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
