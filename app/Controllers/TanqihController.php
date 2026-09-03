<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TanqihModel;
use App\Models\ScheduleModel; // We can reuse this or query directly if specific join needed
use App\Models\TeacherModel;  // For user name if needed

class TanqihController extends Controller {
    protected $tanqihModel;

    public function __construct() {
        parent::__construct();
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

        // Fetch active academic year using helper
        $yearId = get_active_academic_year_id();

        // Determine current active jam Ke-X based on settings
        $settingModel = new \App\Models\SettingModel();
        $hoursConfig = $settingModel->getTvHours();
        $currentTime = date('H:i');
        $currentDetectedHour = null;

        foreach ($hoursConfig as $row) {
            if ($row['type'] === 'jam') {
                if ($currentTime >= $row['start'] && $currentTime <= $row['end']) {
                    $currentDetectedHour = $row['value'];
                    break;
                }
            }
        }

        // Query borrowed from asistensi.php
        $sql = "SELECT s.*, 
                       k.tingkat, k.abjad, 
                       sub.nama as mapel_nama,
                       u.nama as teacher_nama
                FROM schedules s
                JOIN kelas k ON s.kelas_id = k.id
                JOIN subjects sub ON s.subject_id = sub.id
                LEFT JOIN users u ON s.teacher_id = u.id
                WHERE s.day = ? AND s.academic_year_id = ?
                ORDER BY s.hour ASC, k.tingkat ASC, k.abjad ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$dayNameVideo, $yearId]);
        $schedulesRaw = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch Logs
        $tanqihLogs = $this->tanqihModel->getVerificationsByDate($date);

        // Fetch KBM dispensations for this date
        $activityModel = new \App\Models\ActivityModel();
        $dispensations = $activityModel->getDispensationsByDate($date);
        
        $dayOfWeek = date('w', strtotime($date));
        $isFriday = ($dayOfWeek == 5);

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

            // Check dispensation
            $isDispensation = false;
            $dispensationName = null;

            if ($isFriday) {
                $isDispensation = true;
                $dispensationName = 'Libur Mingguan (Jumat)';
            } else {
                foreach ($dispensations as $disp) {
                    if (in_array((int)$row['kelas_id'], $disp['kelas_ids'])) {
                        if ($disp['is_full_day']) {
                            $isDispensation = true;
                            $dispensationName = $disp['name'];
                            break;
                        } else {
                            foreach ($disp['hours'] as $h) {
                                if ((int)$row['hour'] >= (int)$h['hour_start'] && (int)$row['hour'] <= (int)$h['hour_end']) {
                                    $isDispensation = true;
                                    $dispensationName = $disp['name'];
                                    break 2;
                                }
                            }
                        }
                    }
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
                'subject_name' => $row['mapel_nama'],
                'is_dispensation' => $isDispensation,
                'dispensation_name' => $dispensationName
            ];
        }

        $this->view('tanqih/index', [
            'title' => 'Tanqih Idad',
            'selectedDate' => $date,
            'dayName' => $dayNameEnglish,
            'isPiketToday' => $isPiketToday,
            'canVerify' => $canVerify,
            'dailySchedule' => $dailySchedule,
            'currentDetectedHour' => $currentDetectedHour
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

        // KBM Dispensation Guard
        if ($action === 'verify') {
            $activityModel = new \App\Models\ActivityModel();
            $eff = $activityModel->getEffectiveSchedule($date, $kelasId, $hour);
            if ($eff['affected']) {
                $this->jsonOrRedirect($ajax, false, 'Tidak dapat memverifikasi sesi bebas KBM: ' . $eff['activity_name'], $date);
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
                        'status' => $status,
                        'hour' => $hour
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
        
        $today = date('Y-m-d');

        // Calculate Saturday (Sabtu) of the current KMI week (Sabtu - Kamis)
        $w = (int)date('w'); // 0 (Sun) to 6 (Sat)
        $daysToSub = ($w === 6) ? 0 : ($w + 1);
        $defaultStart = date('Y-m-d', strtotime("-{$daysToSub} days"));

        $startDate = $_GET['start'] ?? $defaultStart;
        $endDate = $_GET['end'] ?? $today;

        if ($startDate > $today) {
            $startDate = $today;
        }
        if ($endDate > $today) {
            $endDate = $today;
        }

        $ayId = $_GET['academic_year_id'] ?? get_active_academic_year_id();

        $sort = $_GET['sort'] ?? '';
        $order = $_GET['order'] ?? 'desc';

        $data = $this->tanqihModel->getReportStats($startDate, $endDate, $ayId);
        
        // Access Control: Non-admins only see their own report
        $userRole = auth_get_role();
        $userId = auth_get_user_id();

        if ($userRole !== 'admin') {
            $filteredReport = [];
            if (isset($data['report'][$userId])) {
                $filteredReport[$userId] = $data['report'][$userId];
            }
            $data['report'] = $filteredReport;
            
            // Calculate global stats for the filtered view (teacher's own stats)
            $stats = [
                'total_jadwal' => 0,
                'total_verified' => 0,
                'total_justified' => 0,
                'total_belum' => 0
            ];
            
            if (isset($filteredReport[$userId])) {
                $r = $filteredReport[$userId];
                $stats['total_jadwal'] = $r['expected'] ?? 0;
                $stats['total_verified'] = $r['verified_real'] ?? 0;
                $stats['total_justified'] = $r['justified'] ?? 0;
                // Unverified = jadwal - verified (justified tidak masuk kepatuhan, tapi bukan unverified)
                $stats['total_belum'] = max(0, $stats['total_jadwal'] - ($r['verified_real'] ?? 0) - ($r['justified'] ?? 0));
            }
            
            $data['globalStats'] = $stats;
        }

        // Precalculate compliance & status for sorting
        foreach ($data['report'] as $key => &$r) {
            $r['pct'] = $r['expected'] > 0 ? ($r['verified_real'] / $r['expected']) : 0;
            if ($r['pct'] >= 0.75) {
                $r['status_level'] = 4; // Excellent
            } elseif ($r['pct'] >= 0.50) {
                $r['status_level'] = 3; // Baik
            } elseif ($r['pct'] >= 0.25) {
                $r['status_level'] = 2; // Cukup
            } else {
                $r['status_level'] = 1; // Perlu Perhatian
            }
        }
        unset($r);

        // Perform sorting if specified
        if (!empty($sort)) {
            uasort($data['report'], function($a, $b) use ($sort, $order) {
                if ($sort === 'kepatuhan') {
                    $valA = $a['pct'];
                    $valB = $b['pct'];
                } elseif ($sort === 'status') {
                    $valA = $a['status_level'];
                    $valB = $b['status_level'];
                } elseif ($sort === 'nama') {
                    $valA = strtolower($a['name'] ?? '');
                    $valB = strtolower($b['name'] ?? '');
                } elseif ($sort === 'jadwal') {
                    $valA = $a['expected'] ?? 0;
                    $valB = $b['expected'] ?? 0;
                } elseif ($sort === 'verified') {
                    $valA = $a['verified_real'] ?? 0;
                    $valB = $b['verified_real'] ?? 0;
                } elseif ($sort === 'justified') {
                    $valA = $a['justified'] ?? 0;
                    $valB = $b['justified'] ?? 0;
                } elseif ($sort === 'belum') {
                    $valA = max(0, ($a['expected'] ?? 0) - ($a['verified_real'] ?? 0) - ($a['justified'] ?? 0));
                    $valB = max(0, ($b['expected'] ?? 0) - ($b['verified_real'] ?? 0) - ($b['justified'] ?? 0));
                } else {
                    return 0;
                }

                if ($valA == $valB) {
                    return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
                }

                if ($order === 'asc') {
                    return ($valA < $valB) ? -1 : 1;
                } else {
                    return ($valA > $valB) ? -1 : 1;
                }
            });
        }

        $this->view('tanqih/report', [
            'title' => 'Laporan Tanqih',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'report' => $data['report'],
            'globalStats' => $data['globalStats'],
            'sort' => $sort,
            'order' => $order
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
