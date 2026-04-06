<?php

namespace App\Modules\Students\Models;

use App\Core\Database;

class Student {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = []) {
        $sql = "SELECT s.*, k.tingkat, k.abjad 
                FROM students s 
                LEFT JOIN kelas k ON s.kelas_id = k.id";
        
        // Add filters if needed
        $sql .= " ORDER BY s.nama ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function find($id) {
        return $this->db->query("SELECT * FROM students WHERE id = ?", [$id])->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO students (nis, nama, kelas_id, gender, tempat_lahir, tanggal_lahir, nama_wali) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['nis'], 
            $data['nama'], 
            $data['kelas_id'], 
            $data['gender'], 
            $data['tempat_lahir'] ?? null, 
            $data['tanggal_lahir'] ?? null, 
            $data['nama_wali'] ?? null
        ]);
    }
    
     public function getKelasList() {
        return $this->db->query("SELECT * FROM kelas ORDER BY tingkat ASC, abjad ASC")->fetchAll();
    }
}
