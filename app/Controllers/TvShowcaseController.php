<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class TvShowcaseController extends Controller {

    private function getTeacherProfile($teacherId, $pdo) {
        if (!$teacherId) return null;
        $stmt = $pdo->prepare("SELECT u.*, tp.*, u.nama as nama FROM users u LEFT JOIN teacher_profiles tp ON u.id = tp.user_id WHERE u.id = ?");
        $stmt->execute([$teacherId]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$teacher) return null;

        $nama = $teacher['nama'];
        $gender = $teacher['gender'] ?? '';
        
        $namaClean = preg_replace('/^(Al-Ustadz|Al-Ustadzah|Kiai|Ustadz|Ustadzah)\s+/i', '', $nama);
        $namaClean = preg_replace('/,?\s+(S\.Pd\.?I?|M\.Pd\.?I?|Lc\.?|S\.H\.?I?|M\.S\.?I?|S\.Ag\.?|BA\.?|S\.E\.?|S\.A\.B\.?)\.?$/i', '', $namaClean);
        
        $title = '';
        $badgeColor = 'gray';
        $badgeText = 'UST/USTZH';
        
        if (in_array(ucfirst(strtolower($gender)), ['Laki-laki', 'L', 'Male', 'Pria'])) {
            $title = 'Al-Ustadz'; $badgeColor = 'blue'; $badgeText = 'USTADZ'; $gender = 'Laki-laki';
        } else if (in_array(ucfirst(strtolower($gender)), ['Perempuan', 'P', 'Female', 'Wanita'])) {
            $title = 'Al-Ustadzah'; $badgeColor = 'pink'; $badgeText = 'USTADZAH'; $gender = 'Perempuan';
        }
        
        $profilePic = url('/avatar') . "?id=" . urlencode((string)$teacherId); 
        if (!empty($teacher['profile_picture']) && file_exists(__DIR__ . '/../../' . $teacher['profile_picture'])) {
             $profilePic = url('/' . $teacher['profile_picture']) . '?t=' . time();
        }
        
        return [
            'id' => $teacherId,
            'nama_lengkap' => $nama,
            'nama_display' => trim($namaClean),
            'title' => $title,
            'gender' => $gender,
            'badge_color' => $badgeColor,
            'badge_text' => $badgeText,
            'profile_picture' => $profilePic
        ];
    }

    public function index() {
        require_once __DIR__ . '/../../helpers/utilities.php'; // ensure url() is available
        header('Content-Type: text/html; charset=UTF-8');
        
        $settingModel = new \App\Models\SettingModel();
        // Allow query param override for testing (?mode=exam)
        $mode = $_GET['mode'] ?? $settingModel->get('tv_showcase_mode', 'normal');
        
        extract([]); 
        if ($mode === 'exam') {
            require_once __DIR__ . '/../Views/tvshowcase_exam.php';
        } else {
            require_once __DIR__ . '/../Views/tvshowcase.php';
        }
    }

    public function apiData() {
        require_once __DIR__ . '/../../helpers/utilities.php';
        
        $db = \App\Core\Database::getInstance();
        $pdo = $db->getConnection();

        $settingModel = new \App\Models\SettingModel();
        $mode = $_GET['mode'] ?? $settingModel->get('tv_showcase_mode', 'normal');
        
        if ($mode === 'exam') {
            return $this->apiExamData($pdo);
        }

        // Get total active students (excluding deleted)
        $yearId = $this->currentYear['id'] ?? null;
        $totalSantri = 0;
        if ($yearId) {
            $countStmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM students s 
                INNER JOIN student_enrollments se ON s.id = se.student_id 
                WHERE se.academic_year_id = ? AND se.status = 'Active' AND s.deleted_at IS NULL
            ");
            $countStmt->execute([$yearId]);
            $totalSantri = (int)$countStmt->fetchColumn();
        }

        $selectedDate = date('Y-m-d');
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa', 
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        $dayNameEnglish = date('D', strtotime($selectedDate));
        $dayNameIndo = $dayMap[$dayNameEnglish] ?? '';

        $sql = "SELECT s.*, 
                       k.tingkat, k.abjad, k.legacy_id as kelas_legacy_id,
                       sub.nama as mapel_nama,
                       u.nama as teacher_nama, u.id as teacher_user_id
                FROM schedules s
                JOIN kelas k ON s.kelas_id = k.id
                JOIN subjects sub ON s.subject_id = sub.id
                LEFT JOIN users u ON s.teacher_id = u.id
                WHERE s.day = ?
                ORDER BY s.hour ASC, k.legacy_id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dayNameIndo]);
        $schedulesRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $attStmt = $pdo->prepare("SELECT * FROM attendance_logs WHERE date = ?");
        $attStmt->execute([$selectedDate]);
        $attendanceLogs = []; 
        while($row = $attStmt->fetch(PDO::FETCH_ASSOC)) {
            $attendanceLogs[$row['kelas_id'] . '|' . $row['hour']] = $row;
        }

        $tanqStmt = $pdo->prepare("SELECT t.*, u.nama as verifier_name FROM tanqih t LEFT JOIN users u ON t.verifier_id = u.id WHERE date = ?");
        $tanqStmt->execute([$selectedDate]);
        $tanqihLogs = [];
        while($row = $tanqStmt->fetch(PDO::FETCH_ASSOC)) {
            $tanqihLogs[$row['kelas_id'] . '|' . $row['hour']] = $row;
        }

        $dailySchedule = [];
        $stats = ['total' => 0, 'verified' => 0, 'pending' => 0, 'justified' => 0];
        $verificationsList = [];

        foreach ($schedulesRaw as $row) {
            $hour = $row['hour'];
            $kelasId = $row['kelas_id'];
            $kelasName = $row['tingkat'] . '-' . $row['abjad'];
            $mapelName = $row['mapel_nama'];
            $teacherId = $row['teacher_user_id'];
            $pengajarName = $row['teacher_nama'] ?? 'Unknown';
            
            $key = $kelasId . '|' . $hour;
            
            $tanqih = $tanqihLogs[$key] ?? null;
            $isTanqihVerified = !empty($tanqih);
            
            $stats['total']++;
            if ($isTanqihVerified) {
                $stats['verified']++;
                if (($tanqih['status'] ?? '') === 'justified') $stats['justified']++;
                
                $verificationsList[] = [
                    'pengajar' => $pengajarName,
                    'pengajar_profile' => $this->getTeacherProfile($teacherId, $pdo),
                    'kelas' => $kelasName,
                    'mapel' => $mapelName,
                    'verifier' => $tanqih['verifier_name'] ?? 'Piket',
                    'timestamp' => isset($tanqih['created_at']) ? strtotime($tanqih['created_at']) : 0,
                    'time_formatted' => isset($tanqih['created_at']) ? date('H:i', strtotime($tanqih['created_at'])) : '-',
                    'status' => $tanqih['status'] ?? 'verified'
                ];
            } else {
                $stats['pending']++;
            }
            
            $att = $attendanceLogs[$key] ?? null;
            $scheduleStatus = 'pending';
            $isSubstitute = false;
            $isAbsen = !empty($att);
            
            if ($isAbsen) {
                $rawStatus = $att['status'];
                if ($rawStatus === 'substitute' || !empty($att['substitute_teacher_id'])) {
                    $scheduleStatus = 'substitute';
                    $isSubstitute = true;
                    if (!empty($att['substitute_teacher_id'])) {
                        $subProfile = $this->getTeacherProfile($att['substitute_teacher_id'], $pdo);
                        $pengajarName = $subProfile['nama_display'] . " (Pengganti)";
                        $teacherId = $att['substitute_teacher_id'];
                    }
                } elseif ($rawStatus === 'hadir') {
                    $scheduleStatus = 'verified';
                } elseif (in_array($rawStatus, ['izin', 'sakit'])) {
                    $scheduleStatus = 'justified';
                }
            }
            
            if (!isset($dailySchedule[$hour])) $dailySchedule[$hour] = [];
            
            $dailySchedule[$hour][] = [
                'kelas' => $kelasName,
                'mapel' => $mapelName,
                'pengajar' => $pengajarName,
                'pengajar_profile' => $this->getTeacherProfile($teacherId, $pdo),
                'status' => $scheduleStatus,
                'verified' => $isAbsen,
                'is_substitute' => $isSubstitute
            ];
        }

        usort($verificationsList, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        $latestVerifications = array_slice($verificationsList, 0, 10);

        $piketSyeikh = [];
        $piketKeliling = [];

        $piketStmt = $pdo->prepare("
            SELECT ps.*, u.nama, u.id as user_id 
            FROM piket_schedule ps 
            JOIN users u ON ps.user_id = u.id 
            WHERE ps.day = ?
        ");
        $piketStmt->execute([$dayNameIndo]);
        $piketRaw = $piketStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($piketRaw as $p) {
            $profile = $this->getTeacherProfile($p['user_id'], $pdo);
            if ($p['type'] === 'syeikh') {
                $piketSyeikh[] = $profile;
            } else {
                $sess = $p['session'] ?? 1;
                if (!isset($piketKeliling[$sess])) $piketKeliling[$sess] = [];
                $piketKeliling[$sess][] = $profile;
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'date' => $selectedDate,
            'day' => $dayNameIndo,
            'schedule_by_hour' => $dailySchedule,
            'stats' => $stats,
            'total_santri' => $totalSantri,
            'latest_verifications' => $latestVerifications,
            'piket' => [
                'syeikh' => $piketSyeikh,
                'keliling' => $piketKeliling
            ],
            'hours_config' => (new \App\Models\SettingModel())->getTvHours(),
            'bgm_youtube' => (new \App\Models\SettingModel())->get('tv_showcase_bgm_youtube', ''),
            'quotes' => (new \App\Models\SettingModel())->get('tv_showcase_quotes', [
                "Pancajiwa Pondok: Keikhlasan, Kesederhanaan, Berdikari, Ukhuwah Islamiyah, dan Kebebasan.",
                "Motto Pondok: Berbudi tinggi, Berbadan sehat, Berpengetahuan luas, dan Berpikiran bebas.",
                "إنّ تنفيذ التربية الخلقية والعقلية لا يكفي بمجرد الكلام، بل لا بدّ أن يكون بالقدوة الصالحة...",
                "الوعي مبعث كلّ النجاح",
                "من وعى انتبه",
                "Think globally, act locally!"
            ])
        ]);
        exit;
    }

    public function apiExamData($pdo) {
        $yearId = $this->currentYear['id'] ?? null;
        
        // 1. General Stats
        $totalSantri = 0;
        $totalKelas = 0;
        if ($yearId) {
            $countStmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM students s 
                INNER JOIN student_enrollments se ON s.id = se.student_id 
                WHERE se.academic_year_id = ? AND se.status = 'Active' AND s.deleted_at IS NULL
            ");
            $countStmt->execute([$yearId]);
            $totalSantri = (int)$countStmt->fetchColumn();
            
            $kelasStmt = $pdo->prepare("SELECT COUNT(*) FROM kelas WHERE academic_year_id = ?");
            $kelasStmt->execute([$yearId]);
            $totalKelas = (int)$kelasStmt->fetchColumn();
        }
        
        $totalPelajaran = (int)$pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
        $totalPengajar = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pengajar' AND deleted_at IS NULL")->fetchColumn();
        
        // 2. Active Session
        $activeSession = null;
        if ($yearId) {
            $sessStmt = $pdo->prepare("
                SELECT es.*, ay.name as year 
                FROM exam_sessions es 
                JOIN academic_years ay ON es.academic_year_id = ay.id 
                WHERE es.academic_year_id = ? AND es.is_active = 1 
                LIMIT 1
            ");
            $sessStmt->execute([$yearId]);
            $activeSession = $sessStmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $panitia = [];
        $correctionStats = ['total' => 0, 'belum' => 0, 'proses' => 0, 'selesai' => 0, 'percent' => 0];
        $examsList = [];
        
        if ($activeSession) {
            $sessionId = $activeSession['id'];
            
            // 3. Panitia Ujian
            $panitiaStmt = $pdo->prepare("
                SELECT u.id, u.nama, u.role
                FROM exam_committees ec 
                JOIN users u ON ec.user_id = u.id 
                WHERE ec.exam_session_id = ?
                ORDER BY u.nama ASC
            ");
            $panitiaStmt->execute([$sessionId]);
            $panitiaRaw = $panitiaStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($panitiaRaw as $p) {
                $panitia[] = $this->getTeacherProfile($p['id'], $pdo);
            }
            
            // 4. Correction Stats
            $statsStmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END) as belum,
                    SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
                FROM exams 
                WHERE exam_session_id = ? AND is_deleted = 0
            ");
            $statsStmt->execute([$sessionId]);
            $sRaw = $statsStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($sRaw && $sRaw['total'] > 0) {
                $correctionStats = [
                    'total' => (int)$sRaw['total'],
                    'belum' => (int)$sRaw['belum'],
                    'proses' => (int)$sRaw['proses'],
                    'selesai' => (int)$sRaw['selesai'],
                    'percent' => round(((int)$sRaw['selesai'] / (int)$sRaw['total']) * 100, 1)
                ];
            }
            
            // 5. Exams List (to display on carousel)
            $examsStmt = $pdo->prepare("
                SELECT e.id, sub.nama as subject_name, sub.nama_ar, k.tingkat, k.abjad, u.nama as teacher_name, u.id as teacher_id, e.status, e.has_oral
                FROM exams e 
                JOIN subjects sub ON e.subject_id = sub.id 
                JOIN kelas k ON e.kelas_id = k.id 
                LEFT JOIN users u ON e.teacher_id = u.id 
                WHERE e.exam_session_id = ? AND e.is_deleted = 0
                ORDER BY 
                    CASE WHEN e.status = 'proses' THEN 1 WHEN e.status = 'belum' THEN 2 ELSE 3 END,
                    sub.nama ASC, k.legacy_id ASC
            ");
            $examsStmt->execute([$sessionId]);
            $examsRaw = $examsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($examsRaw as $row) {
                $examsList[] = [
                    'kelas' => $row['tingkat'] . '-' . $row['abjad'],
                    'mapel' => $row['subject_name'],
                    'mapel_ar' => $row['nama_ar'],
                    'pengajar' => $row['teacher_name'] ?? 'Belum Diatur',
                    'pengajar_profile' => $this->getTeacherProfile($row['teacher_id'], $pdo),
                    'status' => $row['status'], // 'belum', 'proses', 'selesai'
                    'has_oral' => $row['has_oral']
                ];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'mode' => 'exam',
            'session' => $activeSession,
            'panitia' => $panitia,
            'correction_stats' => $correctionStats,
            'exams_list' => $examsList,
            'total_santri' => $totalSantri,
            'total_kelas' => $totalKelas,
            'total_pelajaran' => $totalPelajaran,
            'total_pengajar' => $totalPengajar,
            'bgm_youtube' => (new \App\Models\SettingModel())->get('tv_showcase_bgm_youtube', ''),
            'quotes' => (new \App\Models\SettingModel())->get('tv_showcase_quotes', [
                "Pancajiwa Pondok: Keikhlasan, Kesederhanaan, Berdikari, Ukhuwah Islamiyah, dan Kebebasan.",
                "Motto Pondok: Berbudi tinggi, Berbadan sehat, Berpengetahuan luas, dan Berpikiran bebas.",
                "إنّ تنفيذ التربية الخلقية والعقلية لا يكفي بمجرد الكلام، بل لا بدّ أن يكون بالقدوة الصالحة...",
                "الوعي مبعث كلّ النجاح",
                "من وعى انتبه",
                "Think globally, act locally!"
            ])
        ]);
        exit;
    }
}
