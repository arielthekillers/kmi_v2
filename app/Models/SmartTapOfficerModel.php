<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SmartTapOfficerModel extends Model
{
    protected $table = 'smart_tap_officers';

    /**
     * Get all officers with user details
     */
    public function getAllOfficers()
    {
        $sql = "SELECT sto.id, sto.user_id, sto.created_at,
                       u.nama as teacher_name, u.username, u.role, u.rfid_uid,
                       admin.nama as assigned_by_name
                FROM smart_tap_officers sto
                JOIN users u ON sto.user_id = u.id
                LEFT JOIN users admin ON sto.assigned_by = admin.id
                WHERE u.deleted_at IS NULL
                ORDER BY u.nama ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active teachers eligible to be assigned as officers
     */
    public function getAllEligibleTeachers()
    {
        $sql = "SELECT u.id, u.nama, u.username, u.rfid_uid
                FROM users u
                WHERE u.deleted_at IS NULL 
                  AND u.is_active = 1 
                  AND u.role = 'pengajar'
                ORDER BY u.nama ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if user is officer
     */
    public function isOfficer($userId)
    {
        $stmt = $this->db->prepare("SELECT id FROM smart_tap_officers WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (bool) $stmt->fetch();
    }

    /**
     * Add officer
     */
    public function addOfficer($userId, $assignedBy = null)
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO smart_tap_officers (user_id, assigned_by) VALUES (?, ?)");
        return $stmt->execute([$userId, $assignedBy]);
    }

    /**
     * Remove officer by ID
     */
    public function removeOfficer($id)
    {
        $stmt = $this->db->prepare("DELETE FROM smart_tap_officers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Remove officer by User ID
     */
    public function removeOfficerByUserId($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM smart_tap_officers WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Bulk sync / update officers list
     */
    public function updateOfficers(array $userIds, $assignedBy = null)
    {
        $this->db->beginTransaction();
        try {
            // Delete all current
            $this->db->exec("DELETE FROM smart_tap_officers");

            // Re-insert selected
            if (!empty($userIds)) {
                $stmt = $this->db->prepare("INSERT INTO smart_tap_officers (user_id, assigned_by) VALUES (?, ?)");
                foreach ($userIds as $uid) {
                    $uid = (int)$uid;
                    if ($uid > 0) {
                        $stmt->execute([$uid, $assignedBy]);
                    }
                }
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

