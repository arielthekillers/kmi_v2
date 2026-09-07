<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MuwajjahModel;
use App\Models\TeacherModel;
use App\Models\PiketModel;

class MuwajjahController extends Controller {

    protected $muwajjahModel;
    protected $teacherModel;
    protected $piketModel;

    public function __construct() {
        parent::__construct();
        $this->muwajjahModel = new MuwajjahModel();
        $this->teacherModel = new TeacherModel();
        $this->piketModel = new PiketModel();
    }

    /**
     * Check if current user is allowed to input attendance
     */
    private function checkAccess($dateStr) {
        require_login();
        $userRole = auth_get_role();
        $userId = auth_get_user_id();

        if ($userRole === 'admin') {
            return true;
        }

        // Check if user is on Piket Muwajjah schedule for this date
        if ($this->muwajjahModel->isUserPiketMuwajjah($userId, $dateStr)) {
            return true;
        }

        return false;
    }

    /**
     * Main page: Input Absensi Muwajjah
     */
    public function index() {
        require_login();

        $selectedDate = $_GET['tanggal'] ?? date('Y-m-d');
        $isRoutineHoliday = $this->muwajjahModel->isRoutineHoliday($selectedDate);
        $hasAccess = $this->checkAccess($selectedDate);

        $classes = $this->muwajjahModel->getClassesWithWaliKelas();
        $existingAbsensi = $this->muwajjahModel->getAbsensiByDate($selectedDate);

        // Fetch teachers list for Badal (guru pengganti) dropdown (only active teachers with role 'pengajar')
        $teachers = $this->teacherModel->getAll('Active');
        uasort($teachers, function($a, $b) { return strnatcmp($a['nama'], $b['nama']); });

        $this->view('muwajjah/index', [
            'selectedDate' => $selectedDate,
            'isRoutineHoliday' => $isRoutineHoliday,
            'hasAccess' => $hasAccess,
            'classes' => $classes,
            'existingAbsensi' => $existingAbsensi,
            'teachers' => $teachers,
            'userRole' => auth_get_role()
        ]);
    }

    /**
     * Save Attendance Batch
     */
    public function store() {
        require_login();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/muwajjah');
        csrf_validate_token();

        $dateStr = $_POST['tanggal'] ?? date('Y-m-d');

        if (!$this->checkAccess($dateStr)) {
            add_flash('Anda tidak memiliki hak akses untuk mengisi absensi Muwajjah pada tanggal tersebut.', 'error');
            redirect('/muwajjah?tanggal=' . $dateStr);
        }

        $attendanceInput = $_POST['attendance'] ?? [];
        if (empty($attendanceInput)) {
            add_flash('Tidak ada data absensi yang dikirim.', 'error');
            redirect('/muwajjah?tanggal=' . $dateStr);
        }

        try {
            $recordedBy = auth_get_user_id();
            $this->muwajjahModel->saveAbsensiBatch($dateStr, $attendanceInput, $recordedBy);
            add_flash('Absensi Muwajjah tanggal ' . date('d/m/Y', strtotime($dateStr)) . ' berhasil disimpan.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan absensi: ' . $e->getMessage(), 'error');
        }

        redirect('/muwajjah?tanggal=' . $dateStr);
    }

    /**
     * Report / Compliance Dashboard
     */
    public function report() {
        require_login();

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $sortBy = $_GET['sort_by'] ?? 'kelas';

        $reportData = $this->muwajjahModel->getComplianceReport($startDate, $endDate, $sortBy);

        $this->view('muwajjah/report', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sortBy' => $sortBy,
            'reportData' => $reportData,
            'userRole' => auth_get_role()
        ]);
    }

    /**
     * Piket Schedule for Muwajjah (Admin interface)
     */
    public function piketSchedule() {
        require_login();

        $allTeachers = $this->teacherModel->findAll();
        $teachers = [];
        foreach ($allTeachers as $t) {
            if (in_array($t['role'], ['pengajar', 'admin']) && $t['is_active'] == 1 && $t['deleted_at'] === null) {
                $teachers[$t['id']] = $t;
            }
        }
        uasort($teachers, function($a, $b) { return strnatcmp($a['nama'], $b['nama']); });

        $schedule = $this->piketModel->getSchedule('muwajjah');

        $this->view('piket/index', [
            'title' => 'Jadwal Piket Muwajjah Malam',
            'desc' => 'Daftar guru yang bertugas sebagai Piket Malam Belajar Mandiri (Muwajjah).',
            'type' => 'muwajjah',
            'actionUrl' => '/piket/muwajjah/update',
            'schedule' => $schedule,
            'teachers' => $teachers,
            'days' => ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis']
        ]);
    }

    /**
     * Update Piket Muwajjah Schedule
     */
    public function updatePiketSchedule() {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/piket/muwajjah');
        csrf_validate_token();

        $data = $_POST['piket'] ?? [];
        try {
            $this->piketModel->updateSchedule('muwajjah', $data);
            add_flash('Jadwal Piket Muwajjah berhasil disimpan.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan jadwal: ' . $e->getMessage(), 'error');
        }
        redirect('/piket/muwajjah');
    }
}
