<?php

namespace App\Modules\Attendance\Models;

use App\Core\Database;

class Attendance {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getKelasList() {
        return $this->db->query("SELECT * FROM kelas ORDER BY tingkat ASC, abjad ASC")->fetchAll();
    }

    public function getStudentsByKelas($kelasId) {
        return $this->db->query(
            "SELECT * FROM students WHERE kelas_id = ? ORDER BY nama ASC", 
            [$kelasId]
        )->fetchAll();
    }

    public function getAttendanceByClassAndDate($kelasId, $date) {
        $sql = "SELECT s.id as student_id, s.nama, s.nis, a.status, a.note 
                FROM students s 
                LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ?
                WHERE s.kelas_id = ? 
                ORDER BY s.nama ASC";
        return $this->db->query($sql, [$date, $kelasId])->fetchAll();
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
