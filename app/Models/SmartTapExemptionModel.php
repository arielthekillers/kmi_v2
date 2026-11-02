<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SmartTapExemptionModel extends Model
{
    protected $table = 'smart_tap_exemptions';

    public function getAll()
    {
        $sql = "SELECT e.*, 
                       u.nama, 
                       u.username,
                       tp.nip, 
                       tp.phone,
                       tp.profile_picture,
                       CASE 
                           WHEN tp.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u.nama)
                           WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                           ELSE u.nama
                       END as nama_gelar
                FROM smart_tap_exemptions e
                INNER JOIN users u ON e.user_id = u.id
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                WHERE u.deleted_at IS NULL
                ORDER BY e.created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExemptedUserIds()
    {
        $stmt = $this->db->query("SELECT user_id FROM smart_tap_exemptions");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function addExemption($userId, $category = 'Guru Senior', $reason = '', $createdBy = null)
    {
        $sql = "INSERT INTO smart_tap_exemptions (user_id, category, reason, created_by)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    category = VALUES(category),
                    reason = VALUES(reason)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            (int)$userId,
            $category ?: 'Guru Senior',
            $reason ?: null,
            $createdBy
        ]);
    }

    public function deleteExemption($id)
    {
        $stmt = $this->db->prepare("DELETE FROM smart_tap_exemptions WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function isExempt($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM smart_tap_exemptions WHERE user_id = ?");
        $stmt->execute([(int)$userId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}

