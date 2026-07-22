<?php

namespace App\Modules\Students\Models;

use App\Core\Database;

class PpsbRegistration {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = [], $limit = null, $offset = 0) {
        $filtering = $this->applyFilters($filters);
        
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDir = strtoupper($filters['dir'] ?? 'DESC');
        
        $allowedSortFields = ['created_at', 'nama', 'registration_no', 'status'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        if ($sortDir !== 'ASC' && $sortDir !== 'DESC') {
            $sortDir = 'DESC';
        }

        $orderBy = "p.$sortField $sortDir";
        
        $sql = "SELECT p.*, s.nis, s.nama as student_name 
                FROM ppsb_registrations p 
                LEFT JOIN students s ON p.student_id = s.id
                WHERE p.deleted_at IS NULL
                AND {$filtering['where']}
                ORDER BY $orderBy";
        
        $params = $filtering['params'];

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function countAll($filters = []) {
        $filtering = $this->applyFilters($filters);
        $sql = "SELECT COUNT(*) FROM ppsb_registrations p 
                WHERE p.deleted_at IS NULL
                AND {$filtering['where']}";
        return (int)$this->db->query($sql, $filtering['params'])->fetchColumn();
    }

    private function applyFilters($filters) {
        $where = "1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $where .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $where .= " AND (p.nama LIKE ? OR p.registration_no LIKE ? OR p.nama_wali LIKE ?)";
            $q = "%" . trim($filters['q']) . "%";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        return ['where' => $where, 'params' => $params];
    }

    public function find($id) {
        return $this->db->query("
            SELECT p.*, s.nis, s.nama as student_name 
            FROM ppsb_registrations p
            LEFT JOIN students s ON p.student_id = s.id
            WHERE p.id = ? AND p.deleted_at IS NULL
        ", [$id])->fetch();
    }

    public function findByRegNo($regNo) {
        return $this->db->query("SELECT * FROM ppsb_registrations WHERE registration_no = ? AND deleted_at IS NULL", [$regNo])->fetch();
    }

    public function generateRegNo() {
        $year = date('Y');
        $prefix = "REG-$year-";
        
        $stmt = $this->db->query("SELECT registration_no FROM ppsb_registrations WHERE registration_no LIKE '$prefix%' ORDER BY id DESC LIMIT 1");
        $lastRegNo = $stmt->fetchColumn();

        if ($lastRegNo) {
            $seq = (int)substr($lastRegNo, -4);
            return $prefix . str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            return $prefix . '0001';
        }
    }

    public function create($data) {
        if (empty($data['registration_no'])) {
            $data['registration_no'] = $this->generateRegNo();
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO ppsb_registrations ($columns) VALUES ($placeholders)";
        
        $this->db->query($sql, array_values($data));
        return (int)$this->db->getConnection()->lastInsertId();
    }

    public function update($id, $data) {
        $columns = [];
        $params = [];
        foreach ($data as $key => $value) {
            $columns[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;

        $sql = "UPDATE ppsb_registrations SET " . implode(', ', $columns) . " WHERE id = ?";
        return $this->db->query($sql, $params);
    }

    public function delete($id) {
        return $this->db->query("UPDATE ppsb_registrations SET deleted_at = NOW() WHERE id = ?", [$id]);
    }
    
    public function forceDelete($id) {
        return $this->db->query("DELETE FROM ppsb_registrations WHERE id = ?", [$id]);
    }
}
