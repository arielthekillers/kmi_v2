<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SmartTapDeviceModel;
use App\Models\SmartTapEventModel;
use App\Models\SmartTapCardModel;
use App\Models\SmartTapPermissionModel;
use App\Models\SmartTapLogModel;
use App\Models\SmartTapExemptionModel;

class SmartTapApiController extends Controller
{
    private $deviceModel;
    private $eventModel;
    private $cardModel;
    private $permissionModel;
    private $logModel;
    private $exemptionModel;

    public function __construct()
    {
        parent::__construct();
        $this->deviceModel = new SmartTapDeviceModel();
        $this->eventModel = new SmartTapEventModel();
        $this->cardModel = new SmartTapCardModel();
        $this->permissionModel = new SmartTapPermissionModel();
        $this->logModel = new SmartTapLogModel();
        $this->exemptionModel = new SmartTapExemptionModel();
    }

    /**
     * POST /api/smart-tap/handshake
     * Step 2 Kiosk: Mengirim setup_code + info perangkat untuk validasi server & memicu kode 2FA di dashboard admin
     */
    public function handshake()
    {
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $setupCode = trim($data['setup_code'] ?? '');
        $hostname = trim($data['hostname'] ?? '');

        if (empty($setupCode)) {
            echo json_encode([
                'success' => false,
                'message' => 'Kode Setup tidak boleh kosong'
            ]);
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $device = $this->deviceModel->recordHandshake($setupCode, $ip, $hostname);

        if (!$device) {
            echo json_encode([
                'success' => false,
                'message' => 'Kode Setup salah, tidak aktif, atau sudah kedaluwarsa. Silakan periksa di kmi_v2.'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Handshake berhasil! Silakan lihat 6-digit Kode 2FA di Dashboard kmi_v2.',
            'server_time' => date('Y-m-d H:i:s'),
            'device' => [
                'id' => $device['id'],
                'code' => $device['device_code'],
                'name' => $device['device_name'],
                'location' => $device['location'] ?? ''
            ]
        ]);
        exit;
    }

    /**
     * POST /api/smart-tap/pair
     * Step 3 Kiosk: Memasukkan 6-digit kode 2FA + setup_code untuk verifikasi akhir dan mendapatkan device token
     */
    public function pairDevice()
    {
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $setupCode = trim($data['setup_code'] ?? '');
        $code = trim($data['pairing_code'] ?? '');

        if (empty($setupCode) || empty($code)) {
            echo json_encode([
                'success' => false,
                'message' => 'Kode Setup dan Kode Pairing 2FA harus diisi'
            ]);
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $result = $this->deviceModel->completePairing($setupCode, $code, $ip);

        if (!$result['ok']) {
            echo json_encode([
                'success' => false,
                'message' => $result['error'] ?? 'Gagal memverifikasi 2FA',
                'attempts_left' => $result['attempts_left'] ?? 0
            ]);
            exit;
        }

        $device = $result['device'];

        echo json_encode([
            'success' => true,
            'message' => 'Inisiasi dan pairing perangkat berhasil!',
            'device_token' => $device['device_token'],
            'device' => [
                'id' => $device['id'],
                'code' => $device['device_code'],
                'name' => $device['device_name'],
                'pin_code' => $device['pin_code'] ?? '1234',
                'location' => $device['location'] ?? ''
            ]
        ]);
        exit;
    }

    private function getBearerToken()
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

        if (!empty($authHeader) && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return $_GET['token'] ?? ($_POST['token'] ?? null);
    }

    private function authenticateDevice()
    {
        $token = $this->getBearerToken();
        if (empty($token)) {
            header('Content-Type: application/json', true, 401);
            echo json_encode(['error' => 'Missing device token']);
            exit;
        }

        $device = $this->deviceModel->getDeviceByToken($token);
        if (!$device) {
            header('Content-Type: application/json', true, 403);
            echo json_encode(['error' => 'Invalid or inactive device token']);
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $this->deviceModel->updateLastSync($device['id'], $ip);

        return $device;
    }

    /**
     * GET /api/smart-tap/sync-master
     * Provides all master data for the offline client to cache locally.
     */
    public function syncMaster()
    {
        header('Content-Type: application/json');
        $device = $this->authenticateDevice();

        $today = date('Y-m-d');
        $dayOfWeek = date('N'); // 1 (Mon) - 7 (Sun)

        $events = $this->eventModel->getActiveEventsForToday($dayOfWeek, $today, $device['id']);
        $permissions = $this->permissionModel->getActivePermissionsForDate($today);
        $masterCards = $this->cardModel->getAllMasterForSync();

        echo json_encode([
            'success' => true,
            'server_time' => date('Y-m-d H:i:s'),
            'device' => [
                'id' => $device['id'],
                'code' => $device['device_code'],
                'name' => $device['device_name'],
                'pin_code' => $device['pin_code'],
                'location' => $device['location']
            ],
            'today_events' => $events,
            'today_permissions' => $permissions,
            'exempted_teachers' => $this->exemptionModel->getExemptedUserIds(),
            'students' => $masterCards['students'],
            'teachers' => $masterCards['teachers'],
            'total_registered_cards' => $masterCards['total_registered_cards']
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * POST /api/smart-tap/sync-logs
     * Receives attendance logs synced from offline client.
     */
    public function syncLogs()
    {
        header('Content-Type: application/json');
        $device = $this->authenticateDevice();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data && !empty($_POST['logs'])) {
            $data = is_array($_POST['logs']) ? $_POST : json_decode($_POST['logs'], true);
        }

        $logs = $data['logs'] ?? ($data ?? []);
        if (!is_array($logs) || empty($logs)) {
            echo json_encode(['success' => false, 'message' => 'No logs provided']);
            exit;
        }

        $res = $this->logModel->syncBatchLogs($logs, $device['id']);

        echo json_encode([
            'success' => true,
            'device_id' => $device['id'],
            'synced' => $res['synced'],
            'skipped' => $res['skipped'],
            'errors' => $res['errors'],
            'server_time' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    /**
     * GET /api/smart-tap/live-data
     * Realtime polling endpoint for admin web dashboard
     */
    public function liveData()
    {
        header('Content-Type: application/json');

        $eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
        $date = $_GET['date'] ?? date('Y-m-d');

        if (!$eventId) {
            $dayOfWeek = date('N');
            $events = $this->eventModel->getActiveEventsForToday($dayOfWeek, $date);
            if (!empty($events)) {
                $eventId = $events[0]['id'];
            }
        }

        if (!$eventId) {
            echo json_encode(['success' => false, 'message' => 'No active event today']);
            exit;
        }

        $liveStatus = $this->logModel->getEventLiveStatus($eventId, $date);
        $recentLogs = $this->logModel->getRecentLogs($eventId, $date, 15);

        echo json_encode([
            'success' => true,
            'event' => $liveStatus['event'] ?? null,
            'stats' => $liveStatus['stats'] ?? null,
            'recent_logs' => $recentLogs,
            'server_time' => date('H:i:s')
        ]);
        exit;
    }

    /**
     * Ping endpoint
     */
    public function ping()
    {
        header('Content-Type: application/json');
        $device = $this->authenticateDevice();
        echo json_encode([
            'success' => true,
            'device' => $device['device_name'],
            'server_time' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    /**
     * GET /api/smart-tap/version
     * Mengembalikan info versi rilis Smart Tap Kiosk Client terbaru
     */
    public function version()
    {
        header('Content-Type: application/json');

        // Menentukan base url dinamis
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $base = $protocol . "://" . $host;
        if ($scriptDir !== '/' && $scriptDir !== '\\' && !empty($scriptDir)) {
            $base .= rtrim(str_replace('\\', '/', $scriptDir), '/');
        }

        $latestVersion = '1.2.0';
        $updateZipUrl = $base . '/api/smart-tap/download-update';
        $exeUrl = $base . '/smart-tap/download-client';

        echo json_encode([
            'success' => true,
            'latest_version' => $latestVersion,
            'release_date' => '2026-11-03',
            'release_notes' => "Pembaruan Smart Tap Kiosk Client v1.2.0:\n- Dukungan Multi-Reader USB Scanner (Windows RawInput isolasi hardware)\n- Antrean tap presensi FIFO tanpa resiko karakter tertukar\n- Tampilan notifikasi Live Card Stack (beberapa kartu sekaligus)\n- Sinkronisasi presensi kategori Kantor dengan kalender akademik", 
            'download_url' => $updateZipUrl,
            'exe_download_url' => $exeUrl,
            'mandatory' => false
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * GET /api/smart-tap/download-update
     * Mengalirkan file zip patch smart-tap-update.zip
     */
    public function downloadUpdate()
    {
        $downloadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'downloads';
        $targetFile = $downloadDir . DIRECTORY_SEPARATOR . 'smart-tap-update.zip';

        if (!file_exists($targetFile)) {
            header('HTTP/1.0 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Berkas update tidak ditemukan di server']);
            exit;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="smart-tap-update.zip"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($targetFile));

        if (ob_get_level()) {
            ob_end_clean();
        }
        readfile($targetFile);
        exit;
    }
}

