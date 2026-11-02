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

    /* ------------------------------------------------------------------
     * Wizard Inisiasi Perangkat
     *   1. Admin memulai setup  -> Kode Setup sekali pakai (XXXX-XXXX)
     *   2. Kiosk handshake      -> URL + Kode Setup, server membuat kode 2FA
     *   3. Kiosk pairing        -> Kode Setup + kode 2FA, server menerbitkan token
     * ------------------------------------------------------------------ */

    public function generatePairingCode($id)
    {
        $status = $this->startSetup($id);
        return [
            'code' => $status['setup_code'] ?? '',
            'expires_at' => $status['setup_expires_at'] ?? date('Y-m-d H:i:s', time() + 3600)
        ];
    }

    public function verifyAndPair($pairingCode, $ip = null)
    {
        return $this->findBySetupCode($pairingCode);
    }

    const SETUP_TTL_MINUTES = 60;
    const PAIRING_TTL_MINUTES = 10;
    const MAX_PAIRING_ATTEMPTS = 5;

    /** Normalisasi Kode Setup: huruf besar, buang spasi/strip. */
    public static function normalizeSetupCode($code)
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$code));
    }

    /** Format tampilan Kode Setup: XXXX-XXXX */
    public static function formatSetupCode($code)
    {
        $code = self::normalizeSetupCode($code);
        return strlen($code) === 8 ? substr($code, 0, 4) . '-' . substr($code, 4) : $code;
    }

    /**
     * Mulai (atau ulangi) inisiasi perangkat: buat Kode Setup baru dan reset status pairing.
     * Token lama tetap berlaku sampai perangkat baru berhasil dipairing.
     */
    public function startSetup($id)
    {
        // Tanpa karakter ambigu (0/O, 1/I/L)
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $check = $this->db->prepare("SELECT id FROM smart_tap_devices WHERE setup_code = ?");
            $check->execute([$code]);
        } while ($check->fetch());

        $stmt = $this->db->prepare("UPDATE smart_tap_devices SET
                setup_code = ?, setup_expires_at = DATE_ADD(NOW(), INTERVAL " . self::SETUP_TTL_MINUTES . " MINUTE),
                handshake_at = NULL, handshake_ip = NULL, handshake_hostname = NULL,
                pairing_code = NULL, pairing_expires_at = NULL, pairing_attempts = 0
            WHERE id = ?");
        $stmt->execute([$code, $id]);

        return $this->getSetupStatus($id);
    }

    /** Batalkan inisiasi yang sedang berjalan. */
    public function cancelSetup($id)
    {
        $stmt = $this->db->prepare("UPDATE smart_tap_devices SET
                setup_code = NULL, setup_expires_at = NULL,
                handshake_at = NULL, handshake_ip = NULL, handshake_hostname = NULL,
                pairing_code = NULL, pairing_expires_at = NULL, pairing_attempts = 0
            WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Status wizard untuk halaman admin (dipolling).
     * stage: idle | waiting_handshake | waiting_pairing | locked | expired | paired
     */
    public function getSetupStatus($id)
    {
        $stmt = $this->db->prepare("SELECT *,
                (setup_expires_at IS NOT NULL AND setup_expires_at >= NOW()) AS setup_valid,
                (pairing_expires_at IS NOT NULL AND pairing_expires_at >= NOW()) AS pairing_valid
            FROM smart_tap_devices WHERE id = ?");
        $stmt->execute([$id]);
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$d) {
            return null;
        }

        if (empty($d['setup_code'])) {
            $stage = !empty($d['is_paired']) ? 'paired' : 'idle';
        } elseif (!$d['setup_valid']) {
            $stage = 'expired';
        } elseif (empty($d['handshake_at'])) {
            $stage = 'waiting_handshake';
        } elseif ((int)$d['pairing_attempts'] >= self::MAX_PAIRING_ATTEMPTS) {
            $stage = 'locked';
        } elseif (!$d['pairing_valid']) {
            $stage = 'expired';
        } else {
            $stage = 'waiting_pairing';
        }

        return [
            'stage' => $stage,
            'is_paired' => (int)$d['is_paired'],
            'setup_code' => $d['setup_code'] ? self::formatSetupCode($d['setup_code']) : null,
            'setup_expires_at' => $d['setup_expires_at'],
            'handshake_at' => $d['handshake_at'],
            'handshake_ip' => $d['handshake_ip'],
            'handshake_hostname' => $d['handshake_hostname'],
            'pairing_code' => $stage === 'waiting_pairing' ? $d['pairing_code'] : null,
            'pairing_expires_at' => $d['pairing_expires_at'],
            'attempts_left' => max(0, self::MAX_PAIRING_ATTEMPTS - (int)$d['pairing_attempts']),
            'last_sync_at' => $d['last_sync_at'],
        ];
    }

    /** Cari perangkat aktif berdasarkan Kode Setup yang masih berlaku. */
    public function findBySetupCode($setupCode)
    {
        $code = self::normalizeSetupCode($setupCode);
        if (strlen($code) !== 8) {
            return false;
        }
        $stmt = $this->db->prepare("SELECT *,
                (pairing_expires_at IS NOT NULL AND pairing_expires_at >= NOW()) AS pairing_valid
            FROM smart_tap_devices
            WHERE setup_code = ? AND setup_expires_at >= NOW() AND is_active = 1 LIMIT 1");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Step 2 (kiosk): catat handshake dan buat kode 2FA 6 digit yang terikat ke perangkat ini.
     */
    public function recordHandshake($setupCode, $ip = null, $hostname = null)
    {
        $device = $this->findBySetupCode($setupCode);
        if (!$device) {
            return false;
        }

        $pairingCode = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $this->db->prepare("UPDATE smart_tap_devices SET
                handshake_at = NOW(), handshake_ip = ?, handshake_hostname = ?,
                pairing_code = ?, pairing_expires_at = DATE_ADD(NOW(), INTERVAL " . self::PAIRING_TTL_MINUTES . " MINUTE),
                pairing_attempts = 0
            WHERE id = ?");
        $stmt->execute([$ip, $hostname ? mb_substr($hostname, 0, 100) : null, $pairingCode, $device['id']]);

        return $device;
    }

    /**
     * Step 3 (kiosk): verifikasi Kode Setup + kode 2FA, lalu terbitkan device token permanen.
     * Return: ['ok' => bool, 'device' => array|null, 'error' => string|null, 'attempts_left' => int]
     */
    public function completePairing($setupCode, $pairingCode, $ip = null)
    {
        $device = $this->findBySetupCode($setupCode);
        if (!$device || empty($device['handshake_at'])) {
            return ['ok' => false, 'error' => 'Sesi inisiasi tidak ditemukan atau sudah kedaluwarsa. Ulangi dari Step 2.', 'attempts_left' => 0];
        }

        $attempts = (int)$device['pairing_attempts'];
        if ($attempts >= self::MAX_PAIRING_ATTEMPTS) {
            return ['ok' => false, 'error' => 'Terlalu banyak percobaan salah. Admin harus memulai ulang inisiasi di kmi_v2.', 'attempts_left' => 0];
        }

        if (empty($device['pairing_valid'])) {
            return ['ok' => false, 'error' => 'Kode 2FA sudah kedaluwarsa. Ulangi dari Step 2 untuk mendapatkan kode baru.', 'attempts_left' => 0];
        }

        $cleanPairing = preg_replace('/\D/', '', (string)$pairingCode);
        if (!hash_equals((string)$device['pairing_code'], $cleanPairing)) {
            $attempts++;
            $up = $this->db->prepare("UPDATE smart_tap_devices SET pairing_attempts = ? WHERE id = ?");
            $up->execute([$attempts, $device['id']]);
            $left = max(0, self::MAX_PAIRING_ATTEMPTS - $attempts);
            return [
                'ok' => false,
                'error' => $left > 0
                    ? "Kode 2FA salah. Sisa percobaan: {$left}."
                    : 'Kode 2FA salah dan sesi dikunci. Admin harus memulai ulang inisiasi di kmi_v2.',
                'attempts_left' => $left
            ];
        }

        $newToken = bin2hex(random_bytes(32));
        $up = $this->db->prepare("UPDATE smart_tap_devices SET
                device_token = ?, is_paired = 1, last_sync_at = NOW(), last_ip = ?,
                setup_code = NULL, setup_expires_at = NULL,
                pairing_code = NULL, pairing_expires_at = NULL, pairing_attempts = 0
            WHERE id = ?");
        $up->execute([$newToken, $ip, $device['id']]);

        $device['device_token'] = $newToken;
        $device['is_paired'] = 1;
        return ['ok' => true, 'device' => $device, 'error' => null, 'attempts_left' => self::MAX_PAIRING_ATTEMPTS];
    }

    public function unpairDevice($id)
    {
        $newToken = bin2hex(random_bytes(32)); // Invalidate previous token
        $stmt = $this->db->prepare("UPDATE smart_tap_devices SET device_token = ?, is_paired = 0,
                setup_code = NULL, setup_expires_at = NULL,
                handshake_at = NULL, handshake_ip = NULL, handshake_hostname = NULL,
                pairing_code = NULL, pairing_expires_at = NULL, pairing_attempts = 0
            WHERE id = ?");
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

