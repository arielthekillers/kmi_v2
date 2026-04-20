<?php

namespace App\Core;

use PDO;

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    protected $academic_year_id;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Fetch active academic year
        $stmt = $this->db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
        $year = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->academic_year_id = $year ? $year['id'] : null;
    }


    public function findAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
