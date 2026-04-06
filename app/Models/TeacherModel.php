<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TeacherModel extends Model {
    protected $table = 'users';

    public function getAll() {
        // We need name (from users), phone (from profiles), password_plain (from users)
        // Join users and teacher_profiles
        // Role = 'pengajar'
        $sql = "
            SELECT u.id, u.nama, u.username, u.password_plain, tp.phone as hp, tp.nip
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar'
            ORDER BY u.nama ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function search($keyword, $limit, $offset) {
        $sql = "
            SELECT u.id, u.nama, u.username, u.password_plain, tp.phone as hp, tp.nip
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar' 
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

    public function countSearch($keyword) {
        $sql = "
            SELECT COUNT(*) as total
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar'
            AND (u.nama LIKE ? OR tp.phone LIKE ?)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$keyword%", "%$keyword%"]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function create($data) {
        $this->db->getConnection()->beginTransaction();
        try {
            // 1. Insert User
            $stmt = $this->db->prepare("INSERT INTO users (username, password, password_plain, nama, role) VALUES (?, ?, ?, ?, 'pengajar')");
            $stmt->execute([
                $data['username'],
                $data['password'],
                $data['password_plain'],
                $data['nama']
            ]);
            $userId = $this->db->getConnection()->lastInsertId();

            // 2. Insert Profile
            $stmtProfile = $this->db->prepare("INSERT INTO teacher_profiles (user_id, phone) VALUES (?, ?)");
            $stmtProfile->execute([$userId, $data['hp']]);

            $this->db->getConnection()->commit();
            return $userId;
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

    public function update($id, $data) {
        $this->db->getConnection()->beginTransaction();
        try {
            // Update User
            $fields = ['nama = ?'];
            $params = [$data['nama']];
            
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

            $this->db->getConnection()->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        // Cascading delete should handle profile via FK, but let's be safe or rely on FK
        // Schema has ON DELETE CASCADE for teacher_profiles
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
