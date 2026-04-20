<?php

namespace App\Modules\Classes\Models;

use App\Core\Database;

class Kelas {
    public static function all() {
        $db = Database::getInstance();
        $year = $db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch();
        $yearId = $year ? (int)$year['id'] : 0;

        return $db->query("
            SELECT k.*, 
                   u.nama as wali_kelas,
                   CONCAT(tingkat, ' ', abjad) as nama_kelas,
                   (SELECT COUNT(*) 
                    FROM student_enrollments se 
                    WHERE se.kelas_id = k.id AND se.status = 'Active') as jumlah_murid
            FROM kelas k
            LEFT JOIN users u ON k.teacher_id = u.id
            WHERE k.academic_year_id = ?
            ORDER BY tingkat ASC, abjad ASC
        ", [$yearId])->fetchAll();
    }

    public static function create($data) {
        $db = Database::getInstance();
        $year = $db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch();
        $yearId = $year ? (int)$year['id'] : 0;

        $db->query("INSERT INTO kelas (tingkat, abjad, gender, location, teacher_id, academic_year_id) VALUES (?, ?, ?, ?, ?, ?)", [
            $data['tingkat'],
            $data['abjad'],
            $data['gender'],
            $data['location'] ?? null,
            !empty($data['teacher_id']) ? $data['teacher_id'] : null,
            $yearId
        ]);
    }

    public static function delete($id) {
        $db = Database::getInstance();
        $db->query("DELETE FROM kelas WHERE id = ?", [$id]);
    }

    public static function find($id) {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM kelas WHERE id = ?", [$id])->fetch();
    }
}
