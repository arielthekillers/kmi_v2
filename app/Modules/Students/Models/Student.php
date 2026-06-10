<?php

namespace App\Modules\Students\Models;

use App\Core\Database;

class Student {
    protected $db;
    protected $academic_year_id;

    public function __construct() {
        $this->db = Database::getInstance();
        $year = $this->db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch();
        $this->academic_year_id = $year ? (int)$year['id'] : null;
    }

    public function getAll($filters = [], $limit = null, $offset = 0) {
        $status = $filters['status'] ?? 'Active';
        $hasJoin = ($status !== 'Unassigned');
        $filtering = $this->applyFilters($filters, $hasJoin);
        
        $sql = "";
        $params = [];

        if ($status === 'Active') {
            $sql = "SELECT s.*, se.kelas_id, se.status as enrollment_status, k.tingkat, k.abjad 
                    FROM students s 
                    INNER JOIN student_enrollments se ON s.id = se.student_id
                    LEFT JOIN kelas k ON se.kelas_id = k.id
                    WHERE se.academic_year_id = ? AND se.status = 'Active'
                    AND s.deleted_at IS NULL
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
        } elseif ($status === 'Unassigned') {
            $sql = "SELECT s.*, NULL as kelas_id, 'Unassigned' as enrollment_status, NULL as tingkat, NULL as abjad 
                    FROM students s 
                    WHERE s.deleted_at IS NULL
                    AND s.id NOT IN (SELECT student_id FROM student_enrollments WHERE academic_year_id = ?)
                    AND (
                        NOT EXISTS (SELECT 1 FROM student_enrollments WHERE student_id = s.id)
                        OR
                        (SELECT status FROM student_enrollments WHERE student_id = s.id ORDER BY id DESC LIMIT 1) NOT IN ('Graduated', 'Out')
                    )
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
        } elseif ($status === 'Graduated') {
            $sql = "SELECT s.*, se.kelas_id, se.status as enrollment_status, k.tingkat, k.abjad 
                    FROM students s 
                    INNER JOIN student_enrollments se ON s.id = se.student_id
                    LEFT JOIN kelas k ON se.kelas_id = k.id
                    WHERE se.academic_year_id = ? AND se.status = 'Graduated'
                    AND s.deleted_at IS NULL
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
        } elseif ($status === 'Out') {
            $sql = "SELECT s.*, se.kelas_id, se.status as enrollment_status, k.tingkat, k.abjad 
                    FROM students s 
                    INNER JOIN student_enrollments se ON s.id = se.student_id
                    LEFT JOIN kelas k ON se.kelas_id = k.id
                    WHERE se.academic_year_id = ? AND se.status = 'Out'
                    AND s.deleted_at IS NULL
                    AND s.id NOT IN (SELECT student_id FROM student_enrollments WHERE academic_year_id = ? AND status = 'Active')
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
            $params[] = $this->academic_year_id;
        } else { // 'All'
            $sql = "SELECT s.*, se.kelas_id, se.status as enrollment_status, k.tingkat, k.abjad 
                    FROM students s 
                    LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                    LEFT JOIN kelas k ON se.kelas_id = k.id
                    WHERE s.deleted_at IS NULL
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
        }

        $params = array_merge($params, $filtering['params']);
        $sql .= " ORDER BY s.nama ASC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function countAll($filters = []) {
        $status = $filters['status'] ?? 'Active';
        $hasJoin = ($status !== 'Unassigned');
        $filtering = $this->applyFilters($filters, $hasJoin);
        
        $sql = "";
        $params = [];

        if ($status === 'Active') {
            $sql = "SELECT COUNT(*) 
                    FROM students s 
                    INNER JOIN student_enrollments se ON s.id = se.student_id
                    WHERE se.academic_year_id = ? AND se.status = 'Active'
                    AND s.deleted_at IS NULL
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
        } elseif ($status === 'Unassigned') {
            $sql = "SELECT COUNT(*) 
                    FROM students s 
                    WHERE s.deleted_at IS NULL
                    AND s.id NOT IN (SELECT student_id FROM student_enrollments WHERE academic_year_id = ?)
                    AND (
                        NOT EXISTS (SELECT 1 FROM student_enrollments WHERE student_id = s.id)
                        OR
                        (SELECT status FROM student_enrollments WHERE student_id = s.id ORDER BY id DESC LIMIT 1) NOT IN ('Graduated', 'Out')
                    )
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
        } elseif ($status === 'Graduated') {
            $sql = "SELECT COUNT(*) 
                    FROM students s 
                    INNER JOIN student_enrollments se ON s.id = se.student_id
                    WHERE se.academic_year_id = ? AND se.status = 'Graduated'
                    AND s.deleted_at IS NULL
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
        } elseif ($status === 'Out') {
            $sql = "SELECT COUNT(*) 
                    FROM students s 
                    INNER JOIN student_enrollments se ON s.id = se.student_id
                    WHERE se.academic_year_id = ? AND se.status = 'Out'
                    AND s.deleted_at IS NULL
                    AND s.id NOT IN (SELECT student_id FROM student_enrollments WHERE academic_year_id = ? AND status = 'Active')
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
            $params[] = $this->academic_year_id;
        } else { // 'All'
            $sql = "SELECT COUNT(*) 
                    FROM students s 
                    LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                    WHERE s.deleted_at IS NULL
                    AND {$filtering['where']}";
            $params[] = $this->academic_year_id;
        }

        $params = array_merge($params, $filtering['params']);
        return (int)$this->db->query($sql, $params)->fetchColumn();
    }

    private function applyFilters($filters, $hasEnrollmentJoin = true) {
        $where = "1=1";
        $params = [];

        if ($hasEnrollmentJoin && !empty($filters['kelas_id'])) {
            $where .= " AND se.kelas_id = ?";
            $params[] = $filters['kelas_id'];
        } elseif (!$hasEnrollmentJoin && !empty($filters['kelas_id'])) {
            $where .= " AND 1=0";
        }

        if (!empty($filters['q'])) {
            $where .= " AND (s.nama LIKE ? OR s.nis LIKE ? OR s.nik LIKE ?)";
            $q = "%" . trim($filters['q']) . "%";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        return ['where' => $where, 'params' => $params];
    }

    public function find($id) {
        return $this->db->query("
            SELECT s.*, 
                   (SELECT se.kelas_id FROM student_enrollments se WHERE se.student_id = s.id AND se.academic_year_id = ? ORDER BY se.id DESC LIMIT 1) as kelas_id,
                   (SELECT se.status FROM student_enrollments se WHERE se.student_id = s.id AND se.academic_year_id = ? ORDER BY se.id DESC LIMIT 1) as enrollment_status
            FROM students s 
            WHERE s.id = ? AND s.deleted_at IS NULL
        ", [$this->academic_year_id, $this->academic_year_id, $id])->fetch();
    }

    public function findByNis($nis, $academic_year_id = null) {
        $yearId = $academic_year_id ?? $this->academic_year_id;
        return $this->db->query("SELECT * FROM students WHERE nis = ? AND deleted_at IS NULL", [$nis])->fetch();
    }

    public function create($data) {
        $enrollData = [
            'kelas_id' => $data['kelas_id'] ?? null,
            'academic_year_id' => $data['academic_year_id'] ?? $this->academic_year_id
        ];

        unset($data['kelas_id']);
        unset($data['academic_year_id']);

        $this->db->beginTransaction();
        try {
            $existing = $this->findByNis($data['nis']);
            if ($existing) {
                $studentId = $existing['id'];
                $this->update($studentId, $data);
            } else {
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '?'));
                $sql = "INSERT INTO students ($columns) VALUES ($placeholders)";
                $this->db->query($sql, array_values($data));
                $studentId = (int)$this->db->getConnection()->lastInsertId();
            }

            if ($studentId && $enrollData['kelas_id']) {
                $this->enroll($studentId, $enrollData['kelas_id'], $enrollData['academic_year_id']);
            }

            $this->db->commit();
            return $studentId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function enroll($studentId, $kelasId, $yearId, $status = 'Active') {
        $this->db->query("UPDATE student_enrollments SET status = 'Moved', end_date = CURDATE() 
                          WHERE student_id = ? AND academic_year_id = ? AND status = 'Active'", 
                          [$studentId, $yearId]);

        return $this->db->query("INSERT INTO student_enrollments (student_id, academic_year_id, kelas_id, status, start_date) 
                          VALUES (?, ?, ?, ?, CURDATE())", 
                          [$studentId, $yearId, $kelasId, $status]);
    }

    public function getHistory($studentId) {
        return $this->db->query("
            SELECT se.*, k.tingkat, k.abjad, ay.name as year_name 
            FROM student_enrollments se
            JOIN kelas k ON se.kelas_id = k.id
            JOIN academic_years ay ON se.academic_year_id = ay.id
            WHERE se.student_id = ?
            ORDER BY se.academic_year_id DESC, se.start_date DESC
        ", [$studentId])->fetchAll();
    }

    public function update($id, $data) {
        $newKelasId = $data['kelas_id'] ?? null;
        
        // Remove non-person fields
        unset($data['id']);
        unset($data['kelas_id']);
        unset($data['academic_year_id']);

        $columns = [];
        $params = [];
        foreach ($data as $key => $value) {
            $columns[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;

        $sql = "UPDATE students SET " . implode(', ', $columns) . " WHERE id = ?";
        $this->db->query($sql, $params);

        // Update enrollment if kelas changed in current year
        if ($newKelasId) {
            $current = $this->find($id);
            if (!$current['kelas_id'] || $current['kelas_id'] != $newKelasId) {
                $this->enroll($id, $newKelasId, $this->academic_year_id);
            }
        }
        return true;
    }

    public function delete($id) {
        return $this->db->query("UPDATE students SET deleted_at = NOW() WHERE id = ?", [$id]);
    }

    public function getTrash($filters = [], $limit = null, $offset = 0) {
        $filtering = $this->applyFilters($filters);
        
        $sql = "SELECT s.*, se.kelas_id, k.tingkat, k.abjad 
                FROM students s 
                LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                LEFT JOIN kelas k ON se.kelas_id = k.id
                WHERE s.deleted_at IS NOT NULL
                AND {$filtering['where']}
                ORDER BY s.deleted_at DESC";
        
        $params = array_merge([$this->academic_year_id], $filtering['params']);

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function countTrash($filters = []) {
        $filtering = $this->applyFilters($filters);
        $sql = "SELECT COUNT(*) FROM students s 
                WHERE s.deleted_at IS NOT NULL
                AND {$filtering['where']}";
        return (int)$this->db->query($sql, $filtering['params'])->fetchColumn();
    }

    public function restore($id) {
        return $this->db->query("UPDATE students SET deleted_at = NULL WHERE id = ?", [$id]);
    }

    public function forceDelete($id) {
        $this->db->beginTransaction();
        try {
            // Update ppsb_registrations linkages to prevent constraint errors
            $this->db->query("UPDATE ppsb_registrations SET student_id = NULL, status = 'Passed' WHERE student_id = ?", [$id]);
            
            // Delete enrollments
            $this->db->query("DELETE FROM student_enrollments WHERE student_id = ?", [$id]);
            
            // Delete attendance
            $this->db->query("DELETE FROM attendance WHERE student_id = ?", [$id]);
            
            // Delete grades
            $this->db->query("DELETE FROM grades WHERE student_id = ?", [$id]);
            
            // Delete student behaviors
            $this->db->query("DELETE FROM student_behaviors WHERE student_id = ?", [$id]);
            
            // Delete student
            $this->db->query("DELETE FROM students WHERE id = ?", [$id]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    public function commit() {
        return $this->db->commit();
    }

    public function rollBack() {
        return $this->db->rollBack();
    }

    public function generateNextNis($yearName = null) {
        if (!$yearName) {
            $year = $this->db->query("SELECT name FROM academic_years WHERE is_active = 1 LIMIT 1")->fetchColumn();
            $yearName = $year ?: date('Y') . '-' . (date('Y') + 1);
        }
        
        $prefix = '';
        preg_match_all('/\d+/', $yearName, $matches);
        if (count($matches[0]) >= 2) {
            $year1 = $matches[0][0];
            $year2 = $matches[0][1];
            $prefix = substr($year1, -2) . substr($year2, -2);
        } else {
            $prefix = date('y') . (date('y') + 1);
        }
        
        $stmt = $this->db->query("SELECT nis FROM students WHERE nis LIKE '$prefix.%' ORDER BY CAST(SUBSTRING_INDEX(nis, '.', -1) AS UNSIGNED) DESC LIMIT 1");
        $maxNis = $stmt->fetchColumn();
        
        if ($maxNis) {
            $parts = explode('.', $maxNis);
            $seq = (int)end($parts);
            return $prefix . '.' . str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            return $prefix . '.1001'; // Default start sequence
        }
    }

    public function updateEnrollmentStatus($studentId, $yearId, $status) {
        return $this->db->query("UPDATE student_enrollments 
                                 SET status = ?, end_date = CURDATE() 
                                 WHERE student_id = ? AND academic_year_id = ? AND status = 'Active'",
                                 [$status, $studentId, $yearId]);
    }

    public function reEnroll($studentId, $kelasId, $yearId) {
        $this->db->beginTransaction();
        try {
            // Deactivate any currently active enrollments in the target year
            $this->db->query("UPDATE student_enrollments SET status = 'Moved', end_date = CURDATE() 
                              WHERE student_id = ? AND academic_year_id = ? AND status = 'Active'",
                              [$studentId, $yearId]);
                              
            // Insert the new active enrollment
            $this->db->query("INSERT INTO student_enrollments (student_id, academic_year_id, kelas_id, status, start_date) 
                              VALUES (?, ?, ?, 'Active', CURDATE())", 
                              [$studentId, $yearId, $kelasId]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getKelasList() {
        return $this->db->query("SELECT * FROM kelas WHERE academic_year_id = ? ORDER BY tingkat ASC, abjad ASC", [$this->academic_year_id])->fetchAll();
    }
}
