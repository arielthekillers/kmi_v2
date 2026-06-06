<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SubjectModel extends Model {
    protected $table = 'subjects';

    public function getAll() {
        // Fetch all, ordered by name (legacy behavior)
        // Also supports search/pagination in Controller if needed, but Model can just return basic query builder or raw results
        // For standard getAll, let's return all.
        $stmt = $this->db->query("SELECT * FROM subjects ORDER BY nama ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function searchFiltered($filters, $limit, $offset) {
        $sql = "SELECT * FROM subjects WHERE 1=1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND nama LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
        }
        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }
        if (isset($filters['is_special']) && $filters['is_special'] !== '') {
            $sql .= " AND is_special = ?";
            $params[] = $filters['is_special'];
        }
        
        $sql .= " ORDER BY category ASC, urutan ASC, nama ASC LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        
        $idx = 1;
        foreach ($params as $val) {
            $stmt->bindValue($idx++, $val);
        }
        
        $stmt->bindValue($idx++, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue($idx++, (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFiltered($filters) {
        $sql = "SELECT COUNT(*) as total FROM subjects WHERE 1=1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND nama LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
        }
        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }
        if (isset($filters['is_special']) && $filters['is_special'] !== '') {
            $sql .= " AND is_special = ?";
            $params[] = $filters['is_special'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO subjects (nama, nama_ar, category, urutan, skala, is_special) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['nama'], 
            $data['nama_ar'] ?? null, 
            $data['category'] ?? null, 
            $data['urutan'] ?? 0, 
            $data['skala'], 
            $data['is_special'] ?? 0
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE subjects SET nama = ?, nama_ar = ?, category = ?, urutan = ?, skala = ?, is_special = ? WHERE id = ?");
        return $stmt->execute([
            $data['nama'], 
            $data['nama_ar'] ?? null, 
            $data['category'] ?? null, 
            $data['urutan'] ?? 0, 
            $data['skala'], 
            $data['is_special'] ?? 0, 
            $id
        ]);
    }

    public function getSpecialSubjects() {
        $stmt = $this->db->query("SELECT * FROM subjects WHERE is_special = 1 ORDER BY nama ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
