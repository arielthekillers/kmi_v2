<?php

namespace App\Modules\Subjects\Models;

use App\Core\Database;

class Subject {
    public static function all() {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM subjects ORDER BY nama ASC")->fetchAll();
    }

    public static function create($data) {
        $db = Database::getInstance();
        $db->query("INSERT INTO subjects (nama) VALUES (?)", [
            $data['nama']
        ]);
        return $db->getConnection()->lastInsertId();
    }

    public static function find($id) {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM subjects WHERE id = ?", [$id])->fetch();
    }

    public static function update($id, $data) {
        $db = Database::getInstance();
        $db->query("UPDATE subjects SET nama = ? WHERE id = ?", [
            $data['nama'],
            $id
        ]);
    }

    public static function delete($id) {
        $db = Database::getInstance();
        $db->query("DELETE FROM subjects WHERE id = ?", [$id]);
    }
}
