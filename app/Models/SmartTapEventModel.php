<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SmartTapEventModel extends Model
{
    protected $table = 'smart_tap_events';

    public function getAllEvents()
    {
        $stmt = $this->db->query("SELECT * FROM smart_tap_events ORDER BY open_time ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveEventsForToday($dayOfWeek, $dateStr, $deviceId = null)
    {
        // $dayOfWeek: 1 (Mon) - 7 (Sun)
        $deviceFilter = "";
        $params = [$dayOfWeek, $dateStr];

        if (!empty($deviceId)) {
            $deviceFilter = "AND (allow_any_device = 1 OR FIND_IN_SET(?, allowed_devices) > 0)";
            $params[] = $deviceId;
        }

        $sql = "SELECT * FROM smart_tap_events 
                WHERE is_active = 1 
                AND (
                    (event_type = 'daily' AND FIND_IN_SET(?, days_of_week) > 0)
                    OR 
                    (event_type = 'one_off' AND event_date = ?)
                )
                $deviceFilter
                ORDER BY open_time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM smart_tap_events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createEvent($data)
    {
        $sql = "INSERT INTO smart_tap_events (
            name, category, event_type, event_date, days_of_week, target_type, target_classes,
            open_time, start_time, close_time, after_close_action, allow_any_device,
            allowed_devices, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['category'] ?? 'sholat',
            $data['event_type'] ?? 'daily',
            !empty($data['event_date']) ? $data['event_date'] : null,
            $data['days_of_week'] ?? '1,2,3,4,5,6,7',
            $data['target_type'] ?? 'all',
            !empty($data['target_classes']) ? (is_array($data['target_classes']) ? implode(',', $data['target_classes']) : $data['target_classes']) : null,
            $data['open_time'],
            $data['start_time'],
            $data['close_time'],
            $data['after_close_action'] ?? 'reject',
            isset($data['allow_any_device']) ? (int)$data['allow_any_device'] : 1,
            !empty($data['allowed_devices']) ? (is_array($data['allowed_devices']) ? implode(',', $data['allowed_devices']) : $data['allowed_devices']) : null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);
        return $this->db->lastInsertId();
    }

    public function updateEvent($id, $data)
    {
        $sql = "UPDATE smart_tap_events SET 
            name = ?, category = ?, event_type = ?, event_date = ?, days_of_week = ?, target_type = ?, target_classes = ?,
            open_time = ?, start_time = ?, close_time = ?, after_close_action = ?, allow_any_device = ?,
            allowed_devices = ?, is_active = ?
            WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['category'] ?? 'sholat',
            $data['event_type'] ?? 'daily',
            !empty($data['event_date']) ? $data['event_date'] : null,
            $data['days_of_week'] ?? '1,2,3,4,5,6,7',
            $data['target_type'] ?? 'all',
            !empty($data['target_classes']) ? (is_array($data['target_classes']) ? implode(',', $data['target_classes']) : $data['target_classes']) : null,
            $data['open_time'],
            $data['start_time'],
            $data['close_time'],
            $data['after_close_action'] ?? 'reject',
            isset($data['allow_any_device']) ? (int)$data['allow_any_device'] : 1,
            !empty($data['allowed_devices']) ? (is_array($data['allowed_devices']) ? implode(',', $data['allowed_devices']) : $data['allowed_devices']) : null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $id
        ]);
    }

    public function deleteEvent($id)
    {
        $stmt = $this->db->prepare("DELETE FROM smart_tap_events WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

