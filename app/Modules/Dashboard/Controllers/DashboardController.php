<?php

namespace App\Modules\Dashboard\Controllers;

use App\Core\Controller;

class DashboardController extends Controller {

    public function index() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        // Logic using MySQL
        $db = \App\Core\Database::getInstance();
        
        $todayDay = $this->getIndonesianDay(date('D'));
        $todayDate = date('Y-m-d');

        // 1. Syeikh Diwan
        $syeikh = $db->query("
            SELECT u.nama 
            FROM duties d 
            JOIN users u ON d.user_id = u.id 
            WHERE d.day = ? AND d.type = 'diwan'", 
            [$todayDay]
        )->fetchAll();

        // 2. Piket Keliling
        $keliling = $db->query("
            SELECT u.nama 
            FROM duties d 
            JOIN users u ON d.user_id = u.id 
            WHERE d.day = ? AND d.type = 'keliling'", 
            [$todayDay]
        )->fetchAll();

        // 3. Absensi Pengajar
        $absensi = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status != 'hadir' THEN 1 ELSE 0 END) as tidak_hadir
            FROM teacher_attendance 
            WHERE date = ?",
            [$todayDate]
        )->fetch();

        // 4. Tanqih Idad (Simplified for now - Total vs Verified)
        // Total Slots Today
        $totalSlots = $db->query("
            SELECT COUNT(*) as c 
            FROM schedules 
            WHERE day = ?", 
            [$todayDay]
        )->fetch()['c'];

        // Verified Slots Today
        $verifiedSlots = $db->query("
            SELECT COUNT(*) as c 
            FROM teaching_logs 
            WHERE date = ? AND status = 'verified'", 
            [$todayDate]
        )->fetch()['c'];

        $tanqihPercent = $totalSlots > 0 ? round(($verifiedSlots / $totalSlots) * 100) : 0;

        // 5. Koreksi Progress
        $totalSubjects = $db->query("SELECT COUNT(*) as c FROM subjects")->fetch()['c']; // This might be per-class-subject count actually
        // Better metric: Count entries in subject_progress
        // specific metric: how many done?
        $koreksiStats = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
            FROM subject_progress"
        )->fetch();
        
        $koreksiPercent = ($koreksiStats['total'] > 0) ? round(($koreksiStats['selesai'] / $koreksiStats['total']) * 100) : 0;


        // Master Data Stats
        $stats = [
            'pelajaran' => $db->query("SELECT COUNT(*) as c FROM subjects")->fetch()['c'],
            'kelas' => $db->query("SELECT COUNT(*) as c FROM kelas")->fetch()['c'],
            'pengajar' => $db->query("SELECT COUNT(*) as c FROM users WHERE role = 'guru'")->fetch()['c'],
            'santri' => $db->query("SELECT COUNT(*) as c FROM students")->fetch()['c']
        ];

        // Pass data to view
        $data = [
            'stats' => $stats,
            'todayDay' => $todayDay,
            'todayDate' => $todayDate,
            'syeikh' => $syeikh,
            'keliling' => $keliling,
            'absensi' => $absensi,
            'tanqih' => [
                'verified' => $verifiedSlots,
                'total' => $totalSlots,
                'percent' => $tanqihPercent
            ],
            'koreksi' => [
                'done' => $koreksiStats['selesai'] ?? 0,
                'total' => $koreksiStats['total'] ?? 0,
                'percent' => $koreksiPercent
            ],
            'title' => 'Dashboard KMI'
        ];
        
        // Passing user role for view logic
        $data['user_role'] = $_SESSION['role'] ?? 'user';
        $data['user_name'] = $_SESSION['nama'] ?? 'User';

        $this->view('layouts/header', $data);
        $this->view('Dashboard/Views/index', $data);
        $this->view('layouts/footer', $data);
    }

    private function getIndonesianDay($day) {
        $map = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        return $map[$day] ?? $day;
    }
}
