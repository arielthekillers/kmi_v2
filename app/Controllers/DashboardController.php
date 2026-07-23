<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AcademicCalendarModel;
use PDO;

class DashboardController extends Controller {
    public function index() {
        require_login();
        
        $db = \App\Core\Database::getInstance();
        $pdo = $db->getConnection();
        
        $role = auth_get_role();
        $userId = auth_get_user_id();

        // 1. Academic Year Info
        $year = $pdo->query("SELECT id, name FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch();
        $yearId = $year ? (int)$year['id'] : 0;
        $yearName = $year ? $year['name'] : 'Unknown';

        // 2. Date/Day Helpers
        $todayDate = date('Y-m-d');
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        $todayDay = $dayMap[date('D')] ?? '';

        // 3. Master Data Stats
        $stats = [
            'pelajaran' => $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
            'kelas' => $pdo->prepare("SELECT COUNT(*) FROM kelas WHERE academic_year_id = ?"),
            'pengajar' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pengajar' AND deleted_at IS NULL")->fetchColumn(),
            'tahun_ajaran' => $pdo->query("SELECT COUNT(*) FROM academic_years")->fetchColumn(),
            'santri' => $pdo->prepare("
                SELECT COUNT(*) 
                FROM student_enrollments se 
                INNER JOIN students s ON se.student_id = s.id 
                WHERE se.academic_year_id = ? AND se.status = 'Active' AND s.deleted_at IS NULL
            ")
        ];
        
        $stats['kelas']->execute([$yearId]);
        $stats['kelas'] = $stats['kelas']->fetchColumn();
        
        $stats['santri']->execute([$yearId]);
        $stats['santri'] = $stats['santri']->fetchColumn();

        // 4. Koreksi Stats
        $activeSessionId = 0;
        if ($yearId) {
            $sessStmt = $pdo->prepare("SELECT id FROM exam_sessions WHERE academic_year_id = ? AND is_active = 1 LIMIT 1");
            $sessStmt->execute([$yearId]);
            $activeSession = $sessStmt->fetch(PDO::FETCH_ASSOC);
            $activeSessionId = $activeSession ? (int)$activeSession['id'] : 0;
        }

        $totalKoreksi = 0;
        $finishedKoreksi = 0;
        $correctionPercent = 0;

        if ($activeSessionId > 0) {
            $koreksiSql = "SELECT COUNT(*) as total, SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) as selesaicount 
                           FROM exams 
                           WHERE exam_session_id = ? AND is_deleted = 0";
            if ($role === 'pengajar' && $userId) {
                $stmt = $pdo->prepare($koreksiSql . " AND teacher_id = ?");
                $stmt->execute([$activeSessionId, $userId]);
            } else {
                $stmt = $pdo->prepare($koreksiSql);
                $stmt->execute([$activeSessionId]);
            }
            $kRes = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalKoreksi = $kRes['total'] ?? 0;
            $finishedKoreksi = $kRes['selesaicount'] ?? 0;
            $correctionPercent = $totalKoreksi > 0 ? round(($finishedKoreksi / $totalKoreksi) * 100) : 0;
        }

        // 5. Attendance Summary (Tanqih)
        // Total Slots Today for this year
        $sqlSlots = "SELECT COUNT(*) FROM schedules WHERE day = ? AND academic_year_id = ?";
        $paramsSlots = [$todayDay, $yearId];
        if ($role === 'pengajar' && $userId) {
            $sqlSlots .= " AND teacher_id = ?";
            $paramsSlots[] = $userId;
        }
        $stmtSlots = $pdo->prepare($sqlSlots);
        $stmtSlots->execute($paramsSlots);
        $totalSlotsToday = $stmtSlots->fetchColumn();

        // Verified Slots (Tanqih)
        $sqlVerified = "
            SELECT COUNT(*) 
            FROM tanqih t
            JOIN schedules s ON t.kelas_id = s.kelas_id AND t.hour = s.hour
            WHERE t.date = ? AND s.day = ? AND s.academic_year_id = ?
        ";
        $paramsVerified = [$todayDate, $todayDay, $yearId];
        if ($role === 'pengajar' && $userId) {
            $sqlVerified .= " AND s.teacher_id = ?";
            $paramsVerified[] = $userId;
        }
        $stmtVerified = $pdo->prepare($sqlVerified);
        $stmtVerified->execute($paramsVerified);
        $verifiedCount = $stmtVerified->fetchColumn();

        $attendancePercent = $totalSlotsToday > 0 ? round(($verifiedCount / $totalSlotsToday) * 100) : 0;

        // 6. Piket Info
        $piketSyeikh = $pdo->prepare("SELECT u.nama FROM piket_schedule p JOIN users u ON p.user_id = u.id WHERE p.type = 'syeikh' AND p.day = ? AND p.academic_year_id = ?");
        $piketSyeikh->execute([$todayDay, $yearId]);
        $piketSyeikhNames = $piketSyeikh->fetchAll(PDO::FETCH_COLUMN);

        $piketKeliling = $pdo->prepare("SELECT u.nama FROM piket_schedule p JOIN users u ON p.user_id = u.id WHERE p.type = 'keliling' AND p.day = ? AND p.academic_year_id = ?");
        $piketKeliling->execute([$todayDay, $yearId]);
        $piketKelilingNames = $piketKeliling->fetchAll(PDO::FETCH_COLUMN);

        // 7. Teacher Attendance Log Stats
        $attStmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM attendance_logs WHERE date = ? AND academic_year_id = ? GROUP BY status");
        $attStmt->execute([$todayDate, $yearId]);
        $absensiStats = ['hadir' => 0, 'tidak_hadir' => 0];
        while ($row = $attStmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['status'] === 'hadir') $absensiStats['hadir'] = $row['cnt'];
            elseif ($row['status'] === 'tidak_hadir') $absensiStats['tidak_hadir'] = $row['cnt'];
        }

        // 7b. Student Attendance Stats
        $activeStudentSessionId = 0;
        if ($yearId) {
            $sessStmt = $pdo->prepare("SELECT id FROM attendance_sessions WHERE academic_year_id = ? AND is_active = 1 LIMIT 1");
            $sessStmt->execute([$yearId]);
            $activeStudentSession = $sessStmt->fetch(PDO::FETCH_ASSOC);
            $activeStudentSessionId = $activeStudentSession ? (int)$activeStudentSession['id'] : 0;
        }

        $studentAbsensiStats = ['sakit' => 0, 'izin' => 0, 'alpha' => 0];
        if ($activeStudentSessionId > 0) {
            $sAttStmt = $pdo->prepare("SELECT type, COUNT(*) as cnt FROM student_absences WHERE date = ? AND attendance_session_id = ? GROUP BY type");
            $sAttStmt->execute([$todayDate, $activeStudentSessionId]);
            while ($row = $sAttStmt->fetch(PDO::FETCH_ASSOC)) {
                $type = strtolower($row['type']);
                if (isset($studentAbsensiStats[$type])) {
                    $studentAbsensiStats[$type] = $row['cnt'];
                }
            }
        }
        $totalStudentAbsents = array_sum($studentAbsensiStats);
        // Assuming $stats['santri'] contains total active students
        $totalStudents = $stats['santri'] ?? 0;
        $studentAbsensiStats['hadir'] = max(0, $totalStudents - $totalStudentAbsents);
        $studentAbsensiStats['tidak_hadir'] = $totalStudentAbsents;

        // 8. Academic Calendar logic
        $calendarModel = new AcademicCalendarModel();
        $events = $yearId ? $calendarModel->getByYear($yearId) : [];
        $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $selectedYearVal = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYearVal);
        $firstDayOfMonth = date('w', strtotime("$selectedYearVal-$selectedMonth-01"));
        $firstDayOffset = ($firstDayOfMonth == 0) ? 6 : $firstDayOfMonth - 1;

        $monthEvents = [];
        foreach ($events as $event) {
            $start = strtotime($event['tanggal_mulai']);
            $end = empty($event['tanggal_selesai']) ? $start : strtotime($event['tanggal_selesai']);
            $monthStart = strtotime("$selectedYearVal-$selectedMonth-01");
            $monthEnd = strtotime("$selectedYearVal-$selectedMonth-$daysInMonth 23:59:59");
            if ($start <= $monthEnd && $end >= $monthStart) {
                $monthEvents[] = $event;
            }
        }

        $kategoriConfig = [
            'Akademik' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'dot' => 'bg-blue-500',   'border' => 'border-blue-200'],
            'Ujian'    => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'dot' => 'bg-red-500',    'border' => 'border-red-200'],
            'Kegiatan' => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'dot' => 'bg-green-500',  'border' => 'border-green-200'],
            'Libur'    => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200'],
            'Lainnya'  => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'dot' => 'bg-gray-400',   'border' => 'border-gray-200'],
        ];

        $bulanId = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',    '04' => 'April',
            '05' => 'Mei',     '06' => 'Juni',     '07' => 'Juli',     '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        // Pass everything to view
        $this->view('dashboard', [
            'stats' => $stats,
            'todayDay' => $todayDay,
            'yearName' => $yearName,
            'totalKoreksi' => $totalKoreksi,
            'finishedKoreksi' => $finishedKoreksi,
            'correctionPercent' => $correctionPercent,
            'totalSlotsToday' => $totalSlotsToday,
            'verifiedCount' => $verifiedCount,
            'attendancePercent' => $attendancePercent,
            'piketSyeikh' => $piketSyeikhNames,
            'piketKeliling' => $piketKelilingNames,
            'absensiStats' => $absensiStats,
            'studentAbsensiStats' => $studentAbsensiStats,
            'role' => $role,
            'userId' => $userId,
            'selectedMonth' => $selectedMonth,
            'selectedYearVal' => $selectedYearVal,
            'daysInMonth' => $daysInMonth,
            'firstDayOffset' => $firstDayOffset,
            'monthEvents' => $monthEvents,
            'kategoriConfig' => $kategoriConfig,
            'bulanId' => $bulanId
        ]);
    }
}
