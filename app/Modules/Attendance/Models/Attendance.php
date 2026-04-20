<?php

namespace App\Modules\Attendance\Models;

use App\Core\Database;

class Attendance {
    protected $db;

    protected $academic_year_id;

    public function __construct() {
        $this->db = Database::getInstance();
        $year = $this->db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch();
        $this->academic_year_id = $year ? (int)$year['id'] : null;
    }

    public function getKelasList() {
        return $this->db->query("SELECT * FROM kelas WHERE academic_year_id = ? ORDER BY tingkat ASC, abjad ASC", [$this->academic_year_id])->fetchAll();
    }

    public function getStudentsByKelas($kelasId) {
        return $this->db->query(
            "SELECT s.* FROM students s 
             INNER JOIN student_enrollments se ON s.id = se.student_id
             WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status = 'Active' 
             ORDER BY s.nama ASC", 
            [$kelasId, $this->academic_year_id]
        )->fetchAll();
    }

    public function getAttendanceByClassAndDate($kelasId, $date) {
        $sql = "SELECT s.id as student_id, s.nama, s.nis, a.status, a.note 
                FROM students s 
                INNER JOIN student_enrollments se ON s.id = se.student_id
                LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ?
                WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status = 'Active' 
                ORDER BY s.nama ASC";
        return $this->db->query($sql, [$date, $kelasId, $this->academic_year_id])->fetchAll();
    }

    public function save($data) {
        // Upsert logic (Insert or Update)
        $sql = "INSERT INTO attendance (student_id, date, status, note, created_by) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status), note = VALUES(note), created_by = VALUES(created_by)";
        
        $this->db->query($sql, [
            $data['student_id'],
            $data['date'],
            $data['status'],
            $data['note'] ?? null,
            $data['created_by']
        ]);
    }
}
