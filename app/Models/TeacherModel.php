<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TeacherModel extends Model {
    protected $table = 'users';

    private function getStatusCondition($status) {
        if ($status === 'Active') {
            return "u.is_active = 1";
        } elseif ($status === 'Inactive') {
            return "u.is_active = 0";
        }
        return "1=1"; // 'All'
    }

    public function getAll($status = 'Active') {
        $statusCond = $this->getStatusCondition($status);
        $sql = "
            SELECT u.id, 
                   u.nama as nama_raw,
                   CASE 
                       WHEN tp.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u.nama)
                       WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                       ELSE u.nama
                   END as nama,
                   u.username, u.password_plain, u.is_active, tp.phone as hp, tp.nip
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar' AND u.deleted_at IS NULL AND $statusCond
            ORDER BY u.nama ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getForExport($status = 'Active') {
        $statusCond = $this->getStatusCondition($status);
        $sql = "
            SELECT 
                CASE 
                    WHEN tp.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u.nama)
                    WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                    ELSE u.nama
                END as nama,
                tp.nip,
                tp.gender,
                tp.birth_place,
                tp.birth_date,
                tp.address,
                tp.phone as hp,
                tp.education,
                tp.year_graduated,
                tp.father_name,
                tp.mother_name
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar' AND u.deleted_at IS NULL AND $statusCond
            ORDER BY u.nama ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function search($keyword, $limit, $offset, $status = 'Active') {
        $statusCond = $this->getStatusCondition($status);
        $sql = "
            SELECT u.id, 
                   u.nama as nama_raw,
                   CASE 
                       WHEN tp.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u.nama)
                       WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                       ELSE u.nama
                   END as nama,
                   u.username, u.password_plain, u.is_active, tp.phone as hp, tp.nip
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar' AND u.deleted_at IS NULL AND $statusCond
            AND (u.nama LIKE ? OR tp.phone LIKE ?)
            ORDER BY u.nama ASC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($sql);
        $like = "%$keyword%";
        $stmt->bindValue(1, $like);
        $stmt->bindValue(2, $like);
        $stmt->bindValue(3, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(4, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSearch($keyword, $status = 'Active') {
        $statusCond = $this->getStatusCondition($status);
        $sql = "
            SELECT COUNT(*) as total
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar' AND u.deleted_at IS NULL AND $statusCond
            AND (u.nama LIKE ? OR tp.phone LIKE ?)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$keyword%", "%$keyword%"]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function create($data) {
        $this->db->beginTransaction();
        try {
            // 1. Insert User
            $stmt = $this->db->prepare("INSERT INTO users (username, password, password_plain, nama, role) VALUES (?, ?, ?, ?, 'pengajar')");
            $stmt->execute([
                $data['username'],
                $data['password'],
                $data['password_plain'],
                $data['nama']
            ]);
            $userId = $this->db->lastInsertId();

            // 2. Insert Profile
            $stmtProfile = $this->db->prepare("INSERT INTO teacher_profiles (user_id, phone) VALUES (?, ?)");
            $stmtProfile->execute([$userId, $data['hp']]);

            $this->db->commit();
            return $userId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $data) {
        $this->db->beginTransaction();
        try {
            // Update User
            $fields = ['nama = ?'];
            $params = [$data['nama']];
            
            if (isset($data['username']) && !empty($data['username'])) {
                $fields[] = 'username = ?';
                $params[] = $data['username'];
            }

            // If password changed (not empty)
            if (!empty($data['password'])) {
                $fields[] = 'password = ?';
                $params[] = $data['password'];
                $fields[] = 'password_plain = ?';
                $params[] = $data['password_plain'];
            }
            
            // Update User
            $params[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Update Profile (HP)
            // Check if profile exists
            $check = $this->db->prepare("SELECT id FROM teacher_profiles WHERE user_id = ?");
            $check->execute([$id]);
            if ($check->fetch()) {
                $stmtProfile = $this->db->prepare("UPDATE teacher_profiles SET phone = ? WHERE user_id = ?");
                $stmtProfile->execute([$data['hp'], $id]);
            } else {
                $stmtProfile = $this->db->prepare("INSERT INTO teacher_profiles (user_id, phone) VALUES (?, ?)");
                $stmtProfile->execute([$id, $data['hp']]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getTrash($keyword, $limit, $offset) {
        $sql = "
            SELECT u.id, u.nama, u.username, u.password_plain, tp.phone as hp, tp.nip, u.deleted_at
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar' AND u.deleted_at IS NOT NULL
            AND (u.nama LIKE ? OR tp.phone LIKE ?)
            ORDER BY u.deleted_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($sql);
        $like = "%$keyword%";
        $stmt->bindValue(1, $like);
        $stmt->bindValue(2, $like);
        $stmt->bindValue(3, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(4, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countTrash($keyword) {
        $sql = "
            SELECT COUNT(*) as total
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar' AND u.deleted_at IS NOT NULL
            AND (u.nama LIKE ? OR tp.phone LIKE ?)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$keyword%", "%$keyword%"]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function restore($id) {
        $stmt = $this->db->prepare("UPDATE users SET deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function forceDelete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getTeacherDetail($id) {
        $sql = "
            SELECT u.id, 
                   u.nama as nama_raw,
                   CASE 
                       WHEN tp.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u.nama)
                       WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                       ELSE u.nama
                   END as nama,
                   u.username, u.password_plain, u.is_active, 
                   tp.phone as hp, tp.nip, tp.gender, tp.birth_place, 
                   tp.birth_date, tp.address, tp.education, 
                   tp.year_graduated, tp.father_name, tp.mother_name
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.id = ? AND u.role = 'pengajar' AND u.deleted_at IS NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTeachingHistory($teacherId) {
        $sql = "
            SELECT ay.name as academic_year_name, k.tingkat, k.abjad, sub.nama as subject_name
            FROM schedules s
            JOIN academic_years ay ON s.academic_year_id = ay.id
            JOIN kelas k ON s.kelas_id = k.id
            JOIN subjects sub ON s.subject_id = sub.id
            WHERE s.teacher_id = ?
            GROUP BY s.academic_year_id, s.kelas_id, s.subject_id
            ORDER BY ay.name DESC, k.tingkat ASC, k.abjad ASC, sub.nama ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
