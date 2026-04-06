<?php

namespace App\Modules\Auth\Models;

use App\Core\Database;

class User {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUsername($username) {
        $stmt = $this->db->query("SELECT * FROM users WHERE username = ?", [$username]);
        return $stmt->fetch();
    }

    public static function all($role = null) {
        $db = Database::getInstance();
        if ($role) {
            return $db->query("SELECT * FROM users WHERE role = ? ORDER BY nama ASC", [$role])->fetchAll();
        }
        return $db->query("SELECT * FROM users ORDER BY nama ASC")->fetchAll();
    }

    public static function create($data) {
        $db = Database::getInstance();
        $db->query("INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)", [
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['nama'],
            $data['role']
        ]);
    }

    public static function delete($id) {
        $db = Database::getInstance();
        $db->query("DELETE FROM users WHERE id = ?", [$id]);
    }

    public static function find($id) {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();
    }
}
