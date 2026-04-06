<?php

namespace App\Modules\Classes\Models;

use App\Core\Database;

class Kelas {
    public static function all() {
        $db = Database::getInstance();
        return $db->query("SELECT *, CONCAT(tingkat, ' ', abjad) as nama_kelas FROM kelas ORDER BY tingkat ASC, abjad ASC")->fetchAll();
    }

    public static function create($data) {
        $db = Database::getInstance();
        $db->query("INSERT INTO kelas (tingkat, abjad, gender, jumlah_murid) VALUES (?, ?, ?, ?)", [
            $data['tingkat'],
            $data['abjad'],
            $data['gender'],
            $data['jumlah_murid'] ?? 0
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
