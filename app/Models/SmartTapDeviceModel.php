<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SmartTapDeviceModel extends Model
{
    protected $table = 'smart_tap_devices';

    public function getAllDevices()
    {
        $stmt = $this->db->query("SELECT * FROM smart_tap_devices ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveDevices()
    {
        $stmt = $this->db->query("SELECT id, device_code, device_name, pin_code, location, last_sync_at FROM smart_tap_devices WHERE is_active = 1 ORDER BY device_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDeviceById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM smart_tap_devices WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDeviceByToken($token)
    {
        $stmt = $this->db->prepare("SELECT * FROM smart_tap_devices WHERE device_token = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createDevice($data)
    {
        $token = !empty($data['device_token']) ? $data['device_token'] : bin2hex(random_bytes(16));
        $sql = "INSERT INTO smart_tap_devices (device_code, device_name, device_token, pin_code, location, is_active)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['device_code'],
            $data['device_name'],
            $token,
            $data['pin_code'] ?? '1234',
            $data['location'] ?? null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);
        return $this->db->lastInsertId();
    }

    public function updateDevice($id, $data)
    {
        $sql = "UPDATE smart_tap_devices SET 
            device_code = ?, device_name = ?, pin_code = ?, location = ?, is_active = ?
            WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['device_code'],
            $data['device_name'],
            $data['pin_code'] ?? '1234',
            $data['location'] ?? null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $id
        ]);
    }

    public function regenerateToken($id)
    {
        $token = bin2hex(random_bytes(16));
        $stmt = $this->db->prepare("UPDATE smart_tap_devices SET device_token = ?, is_paired = 0 WHERE id = ?");
        $stmt->execute([$token, $id]);
        return $token;
    }

    public function generatePairingCode($id)
    {
        $code = (string)random_int(100000, 999999);
        $stmt = $this->db->prepare("UPDATE smart_tap_devices SET pairing_code = ?, pairing_expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?");
        $stmt->execute([$code, $id]);

        $device = $this->getDeviceById($id);
        return [
            'code' => $code,
            'expires_at' => $device['pairing_expires_at'] ?? date('Y-m-d H:i:s', time() + 900)
        ];
    }

    public function verifyAndPair($pairingCode, $ip = null)
    {
        $cleanCode = preg_replace('/\s+/', '', trim($pairingCode));
        $stmt = $this->db->prepare("SELECT * FROM smart_tap_devices WHERE pairing_code = ? AND pairing_expires_at >= NOW() AND is_active = 1 LIMIT 1");
        $stmt->execute([$cleanCode]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$device) {
            return false;
        }

        // Generate fresh device token specifically for this newly paired client machine
        $newToken = bin2hex(random_bytes(16));
        $upStmt = $this->db->prepare("UPDATE smart_tap_devices SET device_token = ?, pairing_code = NULL, pairing_expires_at = NULL, is_paired = 1, last_sync_at = NOW(), last_ip = ? WHERE id = ?");
        $upStmt->execute([$newToken, $ip, $device['id']]);

        $device['device_token'] = $newToken;
        $device['is_paired'] = 1;
        return $device;
    }

    public function unpairDevice($id)
    {
        $newToken = bin2hex(random_bytes(16)); // Invalidate previous token
        $stmt = $this->db->prepare("UPDATE smart_tap_devices SET device_token = ?, is_paired = 0, pairing_code = NULL, pairing_expires_at = NULL WHERE id = ?");
        return $stmt->execute([$newToken, $id]);
    }

    public function updateLastSync($id, $ip = null)
    {
        $stmt = $this->db->prepare("UPDATE smart_tap_devices SET last_sync_at = NOW(), last_ip = ? WHERE id = ?");
        return $stmt->execute([$ip, $id]);
    }

    public function deleteDevice($id)
    {
        $stmt = $this->db->prepare("DELETE FROM smart_tap_devices WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

