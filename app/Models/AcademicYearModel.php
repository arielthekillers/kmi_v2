<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class AcademicYearModel extends Model {
    protected $table = 'academic_years';

    public function getActive() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $year = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$year) {
            // Fallback to latest if none active
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY name DESC LIMIT 1");
            $stmt->execute();
            $year = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return $year;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY name DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setActive($id) {
        $this->db->beginTransaction();
        try {
            $this->db->exec("UPDATE {$this->table} SET is_active = 0");
            $stmt = $this->db->prepare("UPDATE {$this->table} SET is_active = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function create($name) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name) VALUES (?)");
        return $stmt->execute([$name]);
    }
}
