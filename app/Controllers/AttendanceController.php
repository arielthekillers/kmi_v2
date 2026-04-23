<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AttendanceModel;

class AttendanceController extends Controller {
    protected $attendanceModel;

    public function __construct() {
        parent::__construct();
        $this->attendanceModel = new AttendanceModel();
    }


    public function index() {
        require_login();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Check Permissions (Piket Keliling allowed)
        if (!auth_is_piket_keliling_today($date) && auth_get_role() !== 'admin') {
            add_flash('Anda tidak memiliki akses untuk mengelola absensi pada tanggal ini.', 'error');
            $this->redirect('/');
        }

        $schedule = $this->attendanceModel->getDailyScheduleWithAttendance($date);
        
        // Helper to get Teachers for Dropdown (Teachers active in current academic year)
        $db = \App\Core\Database::getInstance()->getConnection();
        $yearId = get_active_academic_year_id();
        $stmt = $db->prepare("
            SELECT DISTINCT u.id, u.nama 
            FROM users u 
            JOIN schedules s ON u.id = s.teacher_id 
            WHERE s.academic_year_id = ?
            ORDER BY u.nama ASC
        ");
        $stmt->execute([$yearId]);
        $teachers = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        
        $pengajarList = [];
        foreach($teachers as $id => $nama) $pengajarList[$id] = ['nama' => $nama];

        // Determine current active jam Ke-X based on settings
        $settingModel = new \App\Models\SettingModel();
        $hoursConfig = $settingModel->getTvHours();
        $currentTime = date('H:i');
        $currentHour = null;

        foreach ($hoursConfig as $row) {
            if ($row['type'] === 'jam') {
                if ($currentTime >= $row['start'] && $currentTime <= $row['end']) {
                    $currentHour = $row['value'];
                    break;
                }
            }
        }

        $this->view('attendance/index', [
            'title' => 'Absensi Pengajar',
            'selectedDate' => $date,
            'schedule' => $schedule,
            'pengajarList' => $pengajarList,
            'currentDetectedHour' => $currentHour
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        require_login();
        csrf_validate_token();

        $date = $_POST['date'] ?? '';
        $key = $_POST['key'] ?? ''; // key is kelasId|hour
        $status = $_POST['status'] ?? '';
        $teacherId = $_POST['pengajar_id'] ?? '';
        
        list($kelasId, $hour) = explode('|', $key);

        // Permission Check
        if (!auth_is_piket_keliling_today($date, $hour) && auth_get_role() !== 'admin') {
             add_flash('Access Denied: Anda tidak bertugas pada sesi ini.', 'error');
             $this->redirect('/attendance?date=' . urlencode($date));
        }

        $data = [
            'date' => $date,
            'kelas_id' => $kelasId,
            'hour' => $hour,
            'teacher_id' => $teacherId,
            'status' => $status,
            'ketepatan' => $_POST['ketepatan'] ?? null,
            'jam_datang' => $_POST['jam_datang'] ?? null,
            'pengajar_pengganti' => $_POST['pengajar_pengganti'] ?? null,
            'petugas_id' => auth_get_user_id()
        ];

        try {
            $this->attendanceModel->saveAttendance($data);
            add_flash('Absensi berhasil disimpan.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/attendance?date=' . urlencode($date) . '&jam=' . urlencode($hour));
    }

    public function report() {
        require_login();

        $date = $_GET['date'] ?? date('Y-m-d');
        $kelasId = $_GET['kelas_id'] ?? '';
        $pengajarId = $_GET['pengajar_id'] ?? '';
        $jam = $_GET['jam'] ?? '';

        $db = \App\Core\Database::getInstance()->getConnection();
        $yearId = get_active_academic_year_id();

        require_once __DIR__ . '/../../helpers/profile_helper.php';

        $logs = $this->attendanceModel->getReportStats($date, $date, $kelasId, $pengajarId, $jam);
        
        // Fetch All Classes for Active Year
        $kelasStmt = $db->prepare("SELECT * FROM kelas WHERE academic_year_id = ? ORDER BY tingkat ASC, abjad ASC");
        $kelasStmt->execute([$yearId]);
        $kelasData = $kelasStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch All Teachers active in current academic year
        $teacherStmt = $db->prepare("
            SELECT DISTINCT u.id, u.nama 
            FROM users u 
            JOIN schedules s ON u.id = s.teacher_id 
            WHERE s.academic_year_id = ?
            ORDER BY u.nama ASC
        ");
        $teacherStmt->execute([$yearId]);
        $teachers = $teacherStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch Lesson Hours Config
        $settingModel = new \App\Models\SettingModel();
        $hoursConfig = $settingModel->getTvHours();

        // Process aggregated stats
        $stats = [
            'total' => count($logs),
            'hadir' => 0,
            'tepat' => 0, 
            'terlambat' => 0,
            'tidak_hadir' => 0,
            'diganti' => 0
        ];

        // Enrich logs with 'mapel_name'
        $scheduleMap = [];
        $schStmt = $db->prepare("SELECT s.*, sub.nama as mapel_name FROM schedules s JOIN subjects sub ON s.subject_id = sub.id WHERE s.academic_year_id = ?");
        $schStmt->execute([$yearId]);
        while ($r = $schStmt->fetch(\PDO::FETCH_ASSOC)) {
            $scheduleMap[$r['kelas_id']][$r['day']][$r['hour']] = $r['mapel_name'];
        }

        $processedLogs = [];
        $dayMapInd = ['Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'];

        foreach ($logs as $log) {
            // Determine Status Logic
            $status = $log['status'];
            $isHadir = ($status === 'hadir');
            $ketepatan = null;
            $jamDatang = null;

            if ($isHadir) {
                $stats['hadir']++;
                if (strpos($log['note'] ?? '', 'Terlambat') !== false) {
                     $stats['terlambat']++;
                     $ketepatan = 'terlambat';
                     preg_match('/Terlambat: (.*)/', $log['note'], $matches); 
                     $jamDatang = $matches[1] ?? '';
                } else {
                     $stats['tepat']++;
                     $ketepatan = 'tepat_waktu';
                }
            } elseif ($status === 'tidak_hadir' || $status === 'alpha') {
                $stats['tidak_hadir']++;
            } elseif ($status === 'diganti' || $status === 'substitute') {
                $stats['diganti']++;
            }

            // Mapel
            $dayName = $dayMapInd[date('D', strtotime($log['date']))] ?? '';
            $mapelName = $scheduleMap[$log['kelas_id']][$dayName][$log['hour']] ?? '-';

            $processedLogs[] = array_merge($log, [
                'mapel_name' => $mapelName,
                'ketepatan' => $ketepatan,
                'jam_datang' => $jamDatang
            ]);
        }

        $this->view('attendance/report', [
            'title' => 'Laporan Piket Keliling',
            'logs' => $processedLogs,
            'stats' => $stats,
            'kelasData' => $kelasData,
            'pengajarData' => $teachers,
            'hoursConfig' => $hoursConfig,
            'filter' => [
                'date' => $date,
                'kelas_id' => $kelasId,
                'pengajar_id' => $pengajarId,
                'jam' => $jam
            ]
        ]);
    }
}
