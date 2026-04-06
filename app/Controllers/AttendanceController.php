<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AttendanceModel;

class AttendanceController extends Controller {
    protected $attendanceModel;

    public function __construct() {
        $this->attendanceModel = new AttendanceModel();
    }

    public function index() {
        require_login();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Check Permissions (Piket Keliling allowed)
        if (!auth_is_piket_keliling_today($date) && auth_get_role() !== 'admin') {
            add_flash('Anda tidak memiliki akses untuk mengelola absensi pada tanggal ini.', 'error');
            redirect('/');
        }

        $schedule = $this->attendanceModel->getDailyScheduleWithAttendance($date);
        
        // Helper to get Teachers for Dropdown (reusing from existing or model)
        // We can use a simpler query here or a TeacherModel
        $db = \App\Core\Database::getInstance()->getConnection();
        $teachers = $db->query("SELECT id, nama FROM users WHERE role IN ('pengajar', 'admin') ORDER BY nama ASC")->fetchAll(\PDO::FETCH_KEY_PAIR);
        $pengajarList = [];
        foreach($teachers as $id => $nama) $pengajarList[$id] = ['nama' => $nama];

        $this->view('attendance/index', [
            'title' => 'Absensi Pengajar',
            'selectedDate' => $date,
            'schedule' => $schedule,
            'pengajarList' => $pengajarList
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
        
        // Permission Check
        if (!auth_is_piket_keliling_today($date) && auth_get_role() !== 'admin') {
             add_flash('Access Denied', 'error');
             redirect('/attendance?date=' . urlencode($date));
        }

        if (!$date || !$key || !$status) {
            add_flash('Data tidak lengkap.', 'error');
            redirect('/attendance?date=' . urlencode($date));
        }

        list($kelasId, $hour) = explode('|', $key);

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

        redirect('/attendance?date=' . urlencode($date) . '&jam=' . urlencode($hour));
    }

    public function report() {
        require_login();

        $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $_GET['end'] ?? date('Y-m-d');
        $kelasId = $_GET['kelas_id'] ?? '';
        $pengajarId = $_GET['pengajar_id'] ?? '';

        $logs = $this->attendanceModel->getReportStats($startDate, $endDate, $kelasId, $pengajarId);
        
        // Fetch All Classes and Teachers for Filter Dropdowns
        $db = \App\Core\Database::getInstance()->getConnection();
        $kelasData = $db->query("SELECT * FROM kelas ORDER BY tingkat ASC, abjad ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $teachers = $db->query("SELECT id, nama FROM users WHERE role IN ('pengajar', 'admin') ORDER BY nama ASC")->fetchAll(\PDO::FETCH_ASSOC);

        // Process aggregated stats
        $stats = [
            'total' => count($logs),
            'hadir' => 0,
            'tepat' => 0, 
            'terlambat' => 0,
            'tidak_hadir' => 0,
            'diganti' => 0
        ];

        // Enrich logs with 'mapel_name' if needed (requires fetching schedule or joining)
        // For now, let's leave mapel empty or fetch if critical. The legacy fetched it via separate map.
        // We'll mimic that to show Mapel.
        $scheduleMap = [];
        $schStmt = $db->query("SELECT s.*, sub.nama as mapel_name FROM schedules s JOIN subjects sub ON s.subject_id = sub.id");
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
            } elseif ($status === 'tidak_hadir') {
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
            'filter' => [
                'start' => $startDate,
                'end' => $endDate,
                'kelas_id' => $kelasId,
                'pengajar_id' => $pengajarId
            ]
        ]);
    }
}
