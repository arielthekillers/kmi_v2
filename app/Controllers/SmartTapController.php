<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SmartTapEventModel;
use App\Models\SmartTapDeviceModel;
use App\Models\SmartTapCardModel;
use App\Models\SmartTapPermissionModel;
use App\Models\SmartTapLogModel;
use App\Models\SmartTapOfficerModel;
use App\Models\SmartTapExemptionModel;
use App\Models\KelasModel;

class SmartTapController extends Controller
{
    private $eventModel;
    private $deviceModel;
    private $cardModel;
    private $permissionModel;
    private $logModel;
    private $officerModel;
    private $exemptionModel;
    private $kelasModel;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database::getInstance()->getConnection();
        $this->eventModel = new SmartTapEventModel();
        $this->deviceModel = new SmartTapDeviceModel();
        $this->cardModel = new SmartTapCardModel();
        $this->permissionModel = new SmartTapPermissionModel();
        $this->logModel = new SmartTapLogModel();
        $this->officerModel = new SmartTapOfficerModel();
        $this->exemptionModel = new SmartTapExemptionModel();
        $this->kelasModel = new KelasModel();
    }

    /**
     * Dashboard Realtime Monitoring
     */
    public function live()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak. Modul Smart Tap hanya dapat diakses oleh Admin atau Petugas Smart Tap.', 'error');
            $this->redirect('/');
        }

        $today = date('Y-m-d');
        $dayOfWeek = date('N');
        $events = $this->eventModel->getActiveEventsForToday($dayOfWeek, $today);

        // Mode 'all' (default): tampilkan seluruh sesi aktif hari ini secara bersamaan
        // atau spesifik ID event jika difilter
        $selectedEventId = isset($_GET['event_id']) ? ($_GET['event_id'] === 'all' ? 'all' : (int)$_GET['event_id']) : 'all';

        // Rekap ringkas kehadiran per event hari ini
        $eventsWithStats = $this->logModel->getTodayEventsSummaryStats($events, $today);

        $totalAllTaps = 0;
        $totalAllOnTime = 0;
        $totalAllLate = 0;
        foreach ($eventsWithStats as $evSt) {
            $totalAllTaps += (int)($evSt['total_hadir'] ?? 0);
            $totalAllOnTime += (int)($evSt['on_time'] ?? 0);
            $totalAllLate += (int)($evSt['late'] ?? 0);
        }

        $filterId = ($selectedEventId === 'all') ? null : (int)$selectedEventId;
        $recentLogs = $this->logModel->getRecentLogs($filterId, $today, 50);

        $this->view('smart_tap/live', [
            'events' => $events,
            'eventsWithStats' => $eventsWithStats,
            'selectedEventId' => $selectedEventId,
            'totalAllTaps' => $totalAllTaps,
            'totalAllOnTime' => $totalAllOnTime,
            'totalAllLate' => $totalAllLate,
            'recentLogs' => $recentLogs,
            'today' => $today
        ]);
    }

    /**
     * Rekapitulasi & Laporan Kedisiplinan
     */
    /**
     * Cari ID event yang paling relevan dengan waktu server saat ini
     */
    private function findCurrentEventId(array $events): int
    {
        if (empty($events)) return 0;
        $currentTime = date('H:i:s');

        // Pass 1: Sesi yang sedang buka resmi saat ini
        foreach ($events as $ev) {
            $open = $ev['open_time'] ?? '00:00:00';
            $close = $ev['close_time'] ?? '23:59:59';
            if ($currentTime >= $open && $currentTime <= $close) {
                return (int)$ev['id'];
            }
        }

        // Pass 2: Sesi yang baru saja selesai & mengizinkan very_late
        $recentVeryLateId = 0;
        foreach ($events as $ev) {
            $close = $ev['close_time'] ?? '23:59:59';
            $allowsVeryLate = ($ev['after_close_action'] ?? '') === 'very_late';
            if ($currentTime > $close && $allowsVeryLate) {
                $recentVeryLateId = (int)$ev['id'];
            }
        }
        if ($recentVeryLateId > 0) {
            return $recentVeryLateId;
        }

        // Pass 3: Sesi berikutnya hari ini
        foreach ($events as $ev) {
            $open = $ev['open_time'] ?? '00:00:00';
            if ($currentTime < $open) {
                return (int)$ev['id'];
            }
        }

        return (int)$events[0]['id'];
    }

    /**
     * Pindahkan catatan presensi dari satu event ke event lain jika terjadi salah pilih sesi
     */
    public function reassignLogs()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak. Modul Smart Tap hanya dapat diakses oleh Admin atau Petugas Smart Tap.', 'error');
            $this->redirect('/');
        }

        $fromEventId = (int)($_POST['from_event_id'] ?? 0);
        $toEventId = (int)($_POST['to_event_id'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');

        if ($fromEventId > 0 && $toEventId > 0 && $fromEventId !== $toEventId) {
            $count = $this->logModel->reassignEventLogs($fromEventId, $toEventId, $date);
            add_flash("Berhasil memindahkan {$count} data presensi ke sesi tujuan.", 'success');
        } else {
            add_flash('Sesi asal dan sesi tujuan harus berbeda.', 'error');
        }

        $this->redirect('/smart-tap/live?event_id=' . ($toEventId ?: $fromEventId));
    }

    public function reports()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak. Modul Smart Tap hanya dapat diakses oleh Admin atau Petugas Smart Tap.', 'error');
            $this->redirect('/');
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $targetType = $_GET['target_type'] ?? 'all';
        $eventId = !empty($_GET['event_id']) ? (int)$_GET['event_id'] : null;

        $events = $this->eventModel->getAllEvents();
        $recapData = $this->logModel->getComprehensiveRecapReport($startDate, $endDate, $targetType, $eventId);
        $summary = $recapData['summary'];
        $recap = $recapData['rows'];

        $this->view('smart_tap/reports', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'targetType' => $targetType,
            'eventId' => $eventId,
            'events' => $events,
            'recap' => $recap,
            'summary' => $summary
        ]);
    }

    /**
     * Manajemen Perizinan (Izin / Sakit)
     */
    public function permissions()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak. Modul Smart Tap hanya dapat diakses oleh Admin atau Petugas Smart Tap.', 'error');
            $this->redirect('/');
        }

        $tab = $_GET['tab'] ?? 'permissions';

        $filters = [
            'person_type' => $_GET['person_type'] ?? null,
            'permission_type' => $_GET['permission_type'] ?? null,
            'date' => $_GET['date'] ?? null
        ];

        $permissions = $this->permissionModel->getPermissions($filters);
        $exemptions = $this->exemptionModel->getAll();
        $events = $this->eventModel->getAllEvents();
        $classes = $this->kelasModel->findAllActive();

        // Get all active teachers for modal dropdown
        $teachers = (new \App\Models\TeacherModel())->getAll('Active');

        $this->view('smart_tap/permissions', [
            'tab' => $tab,
            'permissions' => $permissions,
            'exemptions' => $exemptions,
            'teachers' => $teachers,
            'events' => $events,
            'classes' => $classes,
            'filters' => $filters
        ]);
    }

    public function storePermission()
    {
        require_login();

        $personType = $_POST['person_type'] ?? 'student';
        $personId = (int)($_POST['person_id'] ?? 0);
        $permType = $_POST['permission_type'] ?? 'izin';
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? $startDate;
        $eventIds = isset($_POST['event_ids']) ? (is_array($_POST['event_ids']) ? implode(',', $_POST['event_ids']) : $_POST['event_ids']) : null;
        $reason = $_POST['reason'] ?? '';

        if (!$personId) {
            $_SESSION['error'] = "Pilih santri atau pengajar terlebih dahulu.";
            $this->redirect('/smart-tap/permissions');
        }

        $userId = $_SESSION['user_id'] ?? null;

        $this->permissionModel->createPermission([
            'person_type' => $personType,
            'person_id' => $personId,
            'permission_type' => $permType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'event_ids' => $eventIds,
            'reason' => $reason,
            'created_by' => $userId
        ]);

        $_SESSION['success'] = "Data perizinan berhasil disimpan.";
        $this->redirect('/smart-tap/permissions');
    }

    public function deletePermission()
    {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->permissionModel->deletePermission($id);
            $_SESSION['success'] = "Data perizinan berhasil dihapus.";
        }
        $this->redirect('/smart-tap/permissions');
    }

    public function storeExemption()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/smart-tap/permissions?tab=exemptions');
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $category = trim($_POST['category'] ?? 'Guru Senior');
        $scope = trim($_POST['scope'] ?? 'all');
        if (!in_array($scope, ['all', 'sholat', 'custom'])) {
            $scope = 'all';
        }

        $eventIds = null;
        if ($scope === 'custom') {
            $postedEventIds = $_POST['event_ids'] ?? [];
            if (is_array($postedEventIds)) {
                $eventIds = implode(',', array_filter(array_map('intval', $postedEventIds)));
            } elseif (is_string($postedEventIds)) {
                $eventIds = trim($postedEventIds);
            }
        }

        $reason = trim($_POST['reason'] ?? '');

        if (!$userId) {
            $_SESSION['error'] = "Pilih guru terlebih dahulu.";
            $this->redirect('/smart-tap/permissions?tab=exemptions');
        }

        $createdBy = $_SESSION['user_id'] ?? null;
        $this->exemptionModel->addExemption($userId, $category, $scope, $eventIds, $reason, $createdBy);

        $_SESSION['success'] = "Guru berhasil ditetapkan sebagai Bebas Smart Tap (Dispensasi Tetap).";
        $this->redirect('/smart-tap/permissions?tab=exemptions');
    }

    public function deleteExemption()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/smart-tap/permissions?tab=exemptions');
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->exemptionModel->deleteExemption($id);
            $_SESSION['success'] = "Dispensasi guru berhasil dihapus. Guru kembali dikenakan kewajiban absensi.";
        }
        $this->redirect('/smart-tap/permissions?tab=exemptions');
    }

    /**
     * Master Event / Jenis Absensi
     */
    public function events()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak. Modul Smart Tap hanya dapat diakses oleh Admin atau Petugas Smart Tap.', 'error');
            $this->redirect('/');
        }

        $events = $this->eventModel->getAllEvents();
        $classes = $this->kelasModel->findAllActive();
        $devices = $this->deviceModel->getActiveDevices();
        $todayHolidayInfo = $this->eventModel->getAcademicHolidayInfo(date('Y-m-d'));

        // Ambil daftar guru / pengajar aktif untuk pilihan target personil
        $db = $this->db ?? \App\Core\Database::getInstance()->getConnection();
        $stmtT = $db->query("
            SELECT u.id, u.nama, u.username, tp.nip, tp.gender,
                   CASE 
                       WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                       ELSE CONCAT('Al-Ustadz ', u.nama)
                   END as nama_gelar
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar' AND u.deleted_at IS NULL AND u.is_active = 1
            ORDER BY u.nama ASC
        ");
        $teachers = $stmtT ? $stmtT->fetchAll(\PDO::FETCH_ASSOC) : [];

        $this->view('smart_tap/events', [
            'events' => $events,
            'classes' => $classes,
            'teachers' => $teachers,
            'devices' => $devices,
            'todayHolidayInfo' => $todayHolidayInfo
        ]);
    }

    public function storeEvent()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/smart-tap/events');
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $eventType = $_POST['event_type'] ?? 'daily';
        $name = trim($_POST['name'] ?? '');
        $nameLower = strtolower($name);

        $category = $_POST['category'] ?? null;
        if (empty($category)) {
            if ($eventType === 'one_off') {
                $category = 'incidental';
            } elseif (strpos($nameLower, 'sholat') !== false) {
                $category = 'sholat';
            } elseif (strpos($nameLower, 'kantor') !== false || strpos($nameLower, 'office') !== false || strpos($nameLower, 'piket') !== false) {
                $category = 'office';
            } else {
                $category = ($eventType === 'daily') ? 'sholat' : 'incidental';
            }
        }

        $data = [
            'name' => $name,
            'category' => $category,
            'event_type' => $eventType,
            'event_date' => !empty($_POST['event_date']) ? $_POST['event_date'] : null,
            'days_of_week' => !empty($_POST['days_of_week']) ? (is_array($_POST['days_of_week']) ? implode(',', $_POST['days_of_week']) : $_POST['days_of_week']) : '1,2,3,4,5,6,7',
            'target_type' => $_POST['target_type'] ?? 'all',
            'target_classes' => !empty($_POST['target_classes']) ? (is_array($_POST['target_classes']) ? implode(',', $_POST['target_classes']) : $_POST['target_classes']) : null,
            'target_teachers' => !empty($_POST['target_teachers']) ? (is_array($_POST['target_teachers']) ? implode(',', $_POST['target_teachers']) : $_POST['target_teachers']) : null,
            'target_gender' => in_array($_POST['target_gender'] ?? '', ['all', 'L', 'P']) ? $_POST['target_gender'] : 'all',
            'open_time' => $_POST['open_time'] ?? '04:00',
            'start_time' => $_POST['start_time'] ?? '04:30',
            'close_time' => $_POST['close_time'] ?? '05:00',
            'after_close_action' => $_POST['after_close_action'] ?? 'reject',
            'allow_any_device' => (isset($_POST['allow_any_device']) && $_POST['allow_any_device'] == '1') ? 1 : 0,
            'allowed_devices' => !empty($_POST['allowed_devices']) ? (is_array($_POST['allowed_devices']) ? implode(',', $_POST['allowed_devices']) : $_POST['allowed_devices']) : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sync_academic_calendar' => isset($_POST['sync_academic_calendar']) ? (int)$_POST['sync_academic_calendar'] : (($category === 'office') ? 1 : 0)
        ];

        if (empty($data['name'])) {
            $_SESSION['error'] = "Nama kegiatan/event harus diisi.";
            $this->redirect('/smart-tap/events');
        }

        if ($id) {
            $this->eventModel->updateEvent($id, $data);
            $_SESSION['success'] = "Pengaturan event berhasil diperbarui.";
        } else {
            $this->eventModel->createEvent($data);
            $_SESSION['success'] = "Event absensi baru berhasil dibuat.";
        }

        $this->redirect('/smart-tap/events');
    }

    public function updateParticipants()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/smart-tap/events');
        }

        $id = (int)($_POST['id'] ?? 0);
        $targetTeachers = !empty($_POST['target_teachers']) 
            ? (is_array($_POST['target_teachers']) ? implode(',', $_POST['target_teachers']) : $_POST['target_teachers']) 
            : null;
        $targetClasses = !empty($_POST['target_classes']) 
            ? (is_array($_POST['target_classes']) ? implode(',', $_POST['target_classes']) : $_POST['target_classes']) 
            : null;

        if ($id) {
            $db = $this->db ?? \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE smart_tap_events SET target_teachers = ?, target_classes = ? WHERE id = ?");
            $stmt->execute([$targetTeachers, $targetClasses, $id]);
            $_SESSION['success'] = "Daftar peserta wajib absensi berhasil diperbarui.";
        }
        $this->redirect('/smart-tap/events');
    }

    public function deleteEvent()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/smart-tap/events');
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->eventModel->deleteEvent($id);
            $_SESSION['success'] = "Event absensi berhasil dihapus.";
        }
        $this->redirect('/smart-tap/events');
    }

    /**
     * Pairing Kartu RFID
     */
    public function cards()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak. Modul Smart Tap hanya dapat diakses oleh Admin atau Petugas Smart Tap.', 'error');
            $this->redirect('/');
        }

        $tab = $_GET['tab'] ?? 'students';
        $search = trim($_GET['search'] ?? '');
        $statusCard = $_GET['has_card'] ?? '';
        $kelasId = !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50;

        $students = [];
        $teachers = [];
        $totalStudents = 0;

        if ($tab === 'students') {
            $students = $this->cardModel->getStudentsList($search, $statusCard, $page, $limit, $kelasId);
            $totalStudents = $this->cardModel->getStudentsCount($search, $statusCard, $kelasId);
        } else {
            $teachers = $this->cardModel->getTeachersList($search, $statusCard);
        }

        $stats = $this->cardModel->getCardStats();
        $classes = $this->kelasModel->findAllActive();

        $this->view('smart_tap/cards', [
            'tab' => $tab,
            'search' => $search,
            'statusCard' => $statusCard,
            'kelasId' => $kelasId,
            'students' => $students,
            'teachers' => $teachers,
            'stats' => $stats,
            'classes' => $classes,
            'page' => $page,
            'totalPages' => ceil($totalStudents / $limit),
            'totalStudents' => $totalStudents
        ]);
    }

    public function pairCard()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            header('Content-Type: application/json', true, 403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            exit;
        }
        header('Content-Type: application/json');

        $personType = $_POST['person_type'] ?? '';
        $personId = (int)($_POST['person_id'] ?? 0);
        $cardUid = trim($_POST['card_uid'] ?? '');

        if (!$personId || !in_array($personType, ['student', 'teacher'])) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
            exit;
        }

        try {
            $this->cardModel->pairCard($personType, $personId, $cardUid);
            echo json_encode(['success' => true, 'message' => 'Kartu RFID berhasil dihubungkan!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function unpairCard()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            header('Content-Type: application/json', true, 403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            exit;
        }
        header('Content-Type: application/json');

        $personType = $_POST['person_type'] ?? '';
        $personId = (int)($_POST['person_id'] ?? 0);

        $this->cardModel->unpairCard($personType, $personId);
        echo json_encode(['success' => true, 'message' => 'Kartu RFID berhasil dilepas.']);
        exit;
    }

    /**
     * Terminal (Device)
     */
    public function devices()
    {
        require_login();

        $devices = $this->deviceModel->getAllDevices();
        $this->view('smart_tap/devices', [
            'devices' => $devices
        ]);
    }

    public function storeDevice()
    {
        require_login();

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $data = [
            'device_code' => strtoupper(trim($_POST['device_code'] ?? '')),
            'device_name' => trim($_POST['device_name'] ?? ''),
            'pin_code' => trim($_POST['pin_code'] ?? '1234'),
            'location' => trim($_POST['location'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if (empty($data['device_code']) || empty($data['device_name'])) {
            $_SESSION['error'] = "Kode dan nama perangkat harus diisi.";
            $this->redirect('/smart-tap/devices');
        }

        if ($id) {
            $this->deviceModel->updateDevice($id, $data);
            $_SESSION['success'] = "Data perangkat berhasil diupdate.";
        } else {
            $this->deviceModel->createDevice($data);
            $_SESSION['success'] = "Perangkat Terminal baru berhasil didaftarkan.";
        }

        $this->redirect('/smart-tap/devices');
    }

    public function regenerateDeviceToken()
    {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->deviceModel->regenerateToken($id);
            $_SESSION['success'] = "Token API perangkat berhasil diganti.";
        }
        $this->redirect('/smart-tap/devices');
    }

    public function startSetup()
    {
        require_login();
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Device ID tidak valid']);
            exit;
        }

        $device = $this->deviceModel->getDeviceById($id);
        if (!$device) {
            echo json_encode(['success' => false, 'message' => 'Perangkat tidak ditemukan']);
            exit;
        }

        $status = $this->deviceModel->startSetup($id);
        echo json_encode([
            'success' => true,
            'device' => [
                'id' => $device['id'],
                'code' => $device['device_code'],
                'name' => $device['device_name'],
                'location' => $device['location'] ?? ''
            ],
            'status' => $status
        ]);
        exit;
    }

    public function setupStatus()
    {
        require_login();
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Device ID tidak valid']);
            exit;
        }

        $status = $this->deviceModel->getSetupStatus($id);
        if (!$status) {
            echo json_encode(['success' => false, 'message' => 'Perangkat tidak ditemukan']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'status' => $status
        ]);
        exit;
    }

    public function cancelSetup()
    {
        require_login();
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id) {
            $this->deviceModel->cancelSetup($id);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    public function generatePairingCode()
    {
        require_login();
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Device ID tidak valid']);
            exit;
        }

        $res = $this->deviceModel->generatePairingCode($id);
        echo json_encode([
            'success' => true,
            'pairing_code' => $res['code'],
            'expires_at' => $res['expires_at'],
            'message' => 'Kode pairing berhasil digenerate!'
        ]);
        exit;
    }

    public function unpairDevice()
    {
        require_login();
        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id) {
            $this->deviceModel->unpairDevice($id);
            $_SESSION['success'] = "Hubungan Terminal berhasil diputus (Unpaired). Mesin harus dipairing ulang untuk dapat sync.";
        }
        $this->redirect('/smart-tap/devices');
    }

    public function deleteDevice()
    {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->deviceModel->deleteDevice($id);
            $_SESSION['success'] = "Perangkat berhasil dihapus.";
        }
        $this->redirect('/smart-tap/devices');
    }

        public function searchPerson()
    {
        require_login();
        header('Content-Type: application/json');

        $q = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? 'student';

        if (strlen($q) < 2) {
            echo json_encode([]);
            exit;
        }

        $db = \App\Core\Database::getInstance()->getConnection();
        $academicYearId = $this->cardModel->getAcademicYearId();

        if ($type === 'student') {
            $stmt = $db->prepare("
                SELECT s.id, s.nama, CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, '')) as subinfo, s.nis
                FROM student_enrollments se
                INNER JOIN students s ON se.student_id = s.id
                LEFT JOIN kelas k ON se.kelas_id = k.id
                WHERE se.academic_year_id = ? AND se.status = 'Active' 
                  AND s.deleted_at IS NULL AND (s.nama LIKE ? OR s.nis LIKE ?)
                ORDER BY s.nama ASC
                LIMIT 15
            ");
            $stmt->execute([$academicYearId, "%$q%", "%$q%"]);
            echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } else {
            $stmt = $db->prepare("
                SELECT u.id, u.nama, 'Pengajar / Guru' as subinfo, u.username as nis
                FROM users u
                WHERE u.deleted_at IS NULL AND u.is_active = 1 AND u.role = 'pengajar' AND (u.nama LIKE ? OR u.username LIKE ?)
                ORDER BY u.nama ASC
                LIMIT 15
            ");
            $stmt->execute(["%$q%", "%$q%"]);
            echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }
        exit;
    }

    /**
     * Manajemen Petugas Smart Tap (Khusus Admin)
     */
    public function officers()
    {
        require_admin();

        $officers = $this->officerModel->getAllOfficers();
        $teachers = $this->officerModel->getAllEligibleTeachers();
        $officerUserIds = array_column($officers, 'user_id');

        $this->view('smart_tap/officers', [
            'officers' => $officers,
            'teachers' => $teachers,
            'officerUserIds' => $officerUserIds
        ]);
    }

    /**
     * Simpan / Update Penugasan Petugas Smart Tap (Khusus Admin)
     */
    public function updateOfficers()
    {
        require_admin();
        csrf_validate_token();

        $userIds = $_POST['user_ids'] ?? [];
        if (!is_array($userIds)) {
            $userIds = [];
        }

        try {
            $this->officerModel->updateOfficers($userIds, auth_get_user_id());
            add_flash('Daftar petugas Smart Tap berhasil diperbarui.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal memperbarui petugas Smart Tap: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/smart-tap/officers');
    }

    /**
     * Hapus Petugas Tertentu (Khusus Admin)
     */
    public function deleteOfficer()
    {
        require_admin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->officerModel->removeOfficer($id);
            add_flash('Petugas Smart Tap berhasil dihapus.', 'success');
        }
        $this->redirect('/smart-tap/officers');
    }

    /**
     * Unduh Aplikasi Kiosk Client (.exe atau ZIP)
     */
    public function downloadClient()
    {
        require_login();
        if (!auth_is_smart_tap_officer()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/');
        }

        $downloadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'downloads';
        
        // Prioritas unduhan: file EXE langsung atau zip installer
        $files = [
            'SmartTapKiosk-Windows.zip' => 'SmartTapKiosk-Windows.zip',
            'SmartTapKiosk.exe' => 'SmartTapKiosk.exe',
            'smart-tap-update.zip' => 'smart-tap-update.zip'
        ];

        $targetFile = null;
        $downloadName = 'SmartTapKiosk-Windows.zip';

        // Cek file mana yang ada
        foreach ($files as $filename => $asName) {
            $path = $downloadDir . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($path)) {
                $targetFile = $path;
                $downloadName = $asName;
                break;
            }
        }

        if (!$targetFile || !file_exists($targetFile)) {
            add_flash('File installer Smart Tap Client belum tersedia di server. Silakan hubungi Administrator sistem.', 'error');
            $this->redirect('/smart-tap/devices');
        }

        $mimeType = (substr($downloadName, -4) === '.zip') ? 'application/zip' : 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($targetFile));
        
        // Bersihkan output buffer sebelum kirim file
        if (ob_get_level()) {
            ob_end_clean();
        }
        readfile($targetFile);
        exit;
    }

    /**
     * Smart Tap PWA Tablet Kiosk (Standalone Mobile/Tablet Terminal)
     */
    public function kiosk()
    {
        $this->view('smart_tap/kiosk');
    }

    /**
     * Web App Manifest for PWA Kiosk
     */
    public function kioskManifest()
    {
        header('Content-Type: application/manifest+json; charset=utf-8');
        echo json_encode([
            'name' => 'Smart Tap Kiosk - Pondok Modern Darussalam Bogor',
            'short_name' => 'Smart Tap',
            'description' => 'Terminal Presensi Mandiri Santri & Pengajar Smart Tap',
            'start_url' => url('/smart-tap/kiosk'),
            'display' => 'standalone',
            'orientation' => 'landscape',
            'background_color' => '#0b0f19',
            'theme_color' => '#4f46e5',
            'icons' => [
                [
                    'src' => url('/img/kmi.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => url('/img/kmi.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
