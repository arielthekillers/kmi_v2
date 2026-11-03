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

    public function getExemptedUserIds($eventId = null, $eventCategory = null)
    {
        $all = $this->db->query("SELECT user_id, scope, event_ids FROM smart_tap_exemptions")->fetchAll(PDO::FETCH_ASSOC);
        if (!$eventId && !$eventCategory) {
            return array_column($all, 'user_id');
        }

        $userIds = [];
        foreach ($all as $row) {
            $scope = $row['scope'] ?? 'all';
            if ($scope === 'all') {
                $userIds[] = (int)$row['user_id'];
            } elseif ($scope === 'sholat' && $eventCategory === 'sholat') {
                $userIds[] = (int)$row['user_id'];
            } elseif ($scope === 'custom' && !empty($row['event_ids'])) {
                $allowedEvents = array_filter(array_map('trim', explode(',', $row['event_ids'])));
                if (in_array((string)$eventId, $allowedEvents)) {
                    $userIds[] = (int)$row['user_id'];
                }
            }
        }
        return array_values(array_unique($userIds));
    }

    public function addExemption($userId, $category = 'Guru Senior', $scope = 'all', $eventIds = null, $reason = '', $createdBy = null)
    {
        $sql = "INSERT INTO smart_tap_exemptions (user_id, category, scope, event_ids, reason, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    category = VALUES(category),
                    scope = VALUES(scope),
                    event_ids = VALUES(event_ids),
                    reason = VALUES(reason)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            (int)$userId,
            $category ?: 'Guru Senior',
            in_array($scope, ['all', 'sholat', 'custom']) ? $scope : 'all',
            $eventIds ?: null,
            $reason ?: null,
            $createdBy
        ]);
    }

    public function deleteExemption($id)
    {
        $stmt = $this->db->prepare("DELETE FROM smart_tap_exemptions WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function isExempt($userId, $eventId = null, $eventCategory = null)
    {
        $stmt = $this->db->prepare("SELECT scope, event_ids FROM smart_tap_exemptions WHERE user_id = ? LIMIT 1");
        $stmt->execute([(int)$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;

        if (!$eventId && !$eventCategory) return true;

        $scope = $row['scope'] ?? 'all';
        if ($scope === 'all') return true;
        if ($scope === 'sholat' && $eventCategory === 'sholat') return true;
        if ($scope === 'custom' && !empty($row['event_ids'])) {
            $allowedEvents = array_filter(array_map('trim', explode(',', $row['event_ids'])));
            return in_array((string)$eventId, $allowedEvents);
        }

        return false;
    }
}

