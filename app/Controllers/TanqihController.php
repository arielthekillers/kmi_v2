<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TanqihModel;
use App\Models\ScheduleModel; // We can reuse this or query directly if specific join needed
use App\Models\TeacherModel;  // For user name if needed

class TanqihController extends Controller {
    protected $tanqihModel;

    public function __construct() {
        $this->tanqihModel = new TanqihModel();
    }

    public function index() {
        require_login();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $userRole = auth_get_role();
        $userId = auth_get_user_id();

        // Check Permissions
        $canVerify = auth_is_syeikh_diwan_today($date);
        $isPiketToday = ($canVerify && $userRole === 'pengajar');

        // Fetch Schedule for the day
        // We'll use raw SQL or a specific method in ScheduleModel if available, 
        // to ensure we get exactly what the view needs (joins with subjects/classes)
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $timestamp = strtotime($date);
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa', 
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        $dayNameEnglish = date('D', $timestamp);
        $dayNameVideo = $dayMap[$dayNameEnglish] ?? '';

        // Query borrowed from asistensi.php
        $sql = "SELECT s.*, 
                       k.tingkat, k.abjad, 
                       sub.nama as mapel_nama,
                       u.nama as teacher_nama
                FROM schedules s
                JOIN kelas k ON s.kelas_id = k.id
                JOIN subjects sub ON s.subject_id = sub.id
                LEFT JOIN users u ON s.teacher_id = u.id
                WHERE s.day = ?
                ORDER BY s.hour ASC, k.tingkat ASC, k.abjad ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$dayNameVideo]);
        $schedulesRaw = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch Logs
        $tanqihLogs = $this->tanqihModel->getVerificationsByDate($date);

        $dailySchedule = [];
        foreach ($schedulesRaw as $row) {
            $pengajarId = $row['teacher_id'];

            // Visibility Filter: If normal teacher (not piket), only see own
            if (!$canVerify && $userRole === 'pengajar' && $pengajarId != $userId) {
                continue;
            }

            $key = $row['kelas_id'] . '|' . $row['hour'];
            $verificationData = $tanqihLogs[$key] ?? null;
            $isVerified = !empty($verificationData);
            
            $verifierName = 'Piket';
            if ($isVerified) {
                if ($verificationData['verifier_id'] == $userId) {
                    $verifierName = 'Anda';
                } else {
                    $verifierName = $verificationData['verifier_name'] ?? 'Piket';
                }
            }

            $dailySchedule[] = [
                'kelas_id' => $row['kelas_id'],
                'hour' => $row['hour'],
                'mapel_id' => $row['subject_id'],
                'pengajar_id' => $pengajarId,
                'is_verified' => $isVerified,
                'verification' => [
                    'status' => $verificationData['status'] ?? 'verified',
                    'timestamp' => isset($verificationData['verified_at']) ? strtotime($verificationData['verified_at']) : 0
                ],
                'verifier_name' => $verifierName,
                // Passing extra display info directly
                'kelas_name' => "Kelas " . ($row['tingkat'] ?? '?') . "-" . ($row['abjad'] ?? '?'),
                'teacher_name' => $row['teacher_nama'],
                'subject_name' => $row['mapel_nama']
            ];
        }

        $this->view('tanqih/index', [
            'title' => 'Tanqih Idad',
            'selectedDate' => $date,
            'dayName' => $dayNameEnglish,
            'isPiketToday' => $isPiketToday,
            'canVerify' => $canVerify,
            'dailySchedule' => $dailySchedule
        ]);
    }

    public function verify() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        require_login();
        csrf_validate_token();

        $date = $_POST['date'] ?? '';
        $kelasId = $_POST['kelas_id'] ?? '';
        $hour = $_POST['hour'] ?? '';
        $pengajarId = $_POST['pengajar_id'] ?? '';
        $action = $_POST['action'] ?? 'verify';
        $status = $_POST['status'] ?? 'verified'; 
        $ajax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

        if (!$date || !$kelasId || !$hour || !$pengajarId) {
            $this->jsonOrRedirect($ajax, false, 'Invalid Data', $date);
        }

        // Permissions
        $userRole = auth_get_role();
        $userId = auth_get_user_id();
        
        if (!auth_is_syeikh_diwan_today($date)) {
            $this->jsonOrRedirect($ajax, false, 'Access Denied', $date);
        }

        if ($userRole !== 'admin') {
            $now = date('H:i');
            if ($now < '06:30' || $now > '14:15') {
                 $this->jsonOrRedirect($ajax, false, 'Verifikasi hanya pukul 06:30 - 14:15.', $date);
            }
            if ((string)$userId === (string)$pengajarId) {
                 $this->jsonOrRedirect($ajax, false, 'Tidak boleh memverifikasi diri sendiri.', $date);
            }
        }

        try {
            if ($action === 'verify') {
                $this->tanqihModel->verify($date, $kelasId, $hour, $userId, $status);
                $msg = 'Berhasil diverifikasi.';
            } else {
                $this->tanqihModel->unverify($date, $kelasId, $hour);
                $msg = 'Verifikasi dibatalkan.';
            }

            if ($ajax) {
                // Return extra data for UI update
                $responseData = [
                    'success' => true,
                    'message' => $msg,
                    'action' => $action,
                    'data' => []
                ];
                if ($action === 'verify') {
                    $responseData['data'] = [
                        'verifier_name' => auth_get_display_name() ?? 'Ahlan',
                        'timestamp' => date('H:i'),
                        'status' => $status
                    ];
                }
                echo json_encode($responseData);
                exit;
            }

            add_flash($msg, 'success');
            redirect('/tanqih?date=' . urlencode($date));

        } catch (\Exception $e) {
            $this->jsonOrRedirect($ajax, false, 'DB Error: ' . $e->getMessage(), $date);
        }
    }

    public function report() {
        require_login();
        
        $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('last saturday'));
        $endDate = $_GET['end'] ?? date('Y-m-d', strtotime('next thursday'));

        $data = $this->tanqihModel->getReportStats($startDate, $endDate);
        
        // Access Control: Non-admins only see their own report
        $userRole = auth_get_role();
        $userId = auth_get_user_id();

        if ($userRole !== 'admin') {
            $filteredReport = [];
            if (isset($data['report'][$userId])) {
                $filteredReport[$userId] = $data['report'][$userId];
            }
            $data['report'] = $filteredReport;
            
            // Recalculate global stats for the filtered view? 
            // Better to hide global stats or keep context. Legacy usually hides.
            $data['globalStats'] = null; 
        }

        $this->view('tanqih/report', [
            'title' => 'Laporan Tanqih',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'report' => $data['report'],
            'globalStats' => $data['globalStats']
        ]);
    }

    private function jsonOrRedirect($ajax, $success, $msg, $date) {
        if ($ajax) {
            echo json_encode(['success' => $success, 'message' => $msg]);
            exit;
        }
        add_flash($msg, $success ? 'success' : 'error');
        redirect('/tanqih?date=' . urlencode($date));
        exit;
    }
}
