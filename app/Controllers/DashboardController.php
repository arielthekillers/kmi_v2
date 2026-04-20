<?php

namespace App\Controllers;

use App\Core\Controller;
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
            'pengajar' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pengajar'")->fetchColumn(),
            'santri' => $pdo->prepare("SELECT COUNT(*) FROM student_enrollments WHERE academic_year_id = ? AND status = 'Active'")
        ];
        
        $stats['kelas']->execute([$yearId]);
        $stats['kelas'] = $stats['kelas']->fetchColumn();
        
        $stats['santri']->execute([$yearId]);
        $stats['santri'] = $stats['santri']->fetchColumn();

        // 4. Koreksi Stats
        $koreksiSql = "SELECT COUNT(*) as total, SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) as selesaicount FROM exams";
        if ($role === 'pengajar' && $userId) {
            $stmt = $pdo->prepare($koreksiSql . " WHERE teacher_id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $pdo->query($koreksiSql);
        }
        $kRes = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalKoreksi = $kRes['total'] ?? 0;
        $finishedKoreksi = $kRes['selesaicount'] ?? 0;
        $correctionPercent = $totalKoreksi > 0 ? round(($finishedKoreksi / $totalKoreksi) * 100) : 0;

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
            'role' => $role,
            'userId' => $userId
        ]);
    }
}
