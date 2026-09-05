<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\StudentAttendanceModel;
use App\Models\KelasModel;
use App\Models\TeacherModel;
use App\Models\AcademicYearModel;

class StudentAttendanceController extends Controller {
    protected $attendanceModel;
    protected $kelasModel;
    protected $teacherModel;
    protected $ayModel;

    public function __construct() {
        parent::__construct();
        $this->attendanceModel = new StudentAttendanceModel();
        $this->kelasModel = new KelasModel();
        $this->teacherModel = new TeacherModel();
        $this->ayModel = new AcademicYearModel();
    }

    public function index() {
        require_login();

        $activeSession = $this->attendanceModel->getActiveSession($this->currentYear['id']);
        
        if (!auth_can_manage_attendance($activeSession['id'] ?? null)) {
            add_flash('Akses ditolak: Hanya petugas absensi (Bagian Pengajaran) yang dapat mengakses halaman ini.', 'error');
            $this->redirect('/');
        }
        
        // Fetch all active classes
        $kelas = $this->kelasModel->findAllActive();
        try {
            uasort($kelas, function ($a, $b) {
                $t = strnatcmp($a['tingkat'] ?? '', $b['tingkat'] ?? '');
                if ($t === 0) return strnatcmp($a['abjad'] ?? '', $b['abjad'] ?? '');
                return $t;
            });
        } catch (\Throwable $e) {}

        // Active class ID
        $activeKelasId = $_GET['kelas'] ?? '';
        if (empty($activeKelasId) && !empty($kelas)) {
            $firstKelas = reset($kelas);
            $activeKelasId = $firstKelas['id'];
        }

        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $tab = $_GET['tab'] ?? 'input';

        $students = [];
        $summary = [];
        $schedules = [];
        $dispensations = [];
        $absentCounts = [];

        if ($activeSession) {
            $absentCounts = $this->attendanceModel->getAbsentCountPerClass($selectedDate, $activeSession['id']);
        }

        if (!empty($activeKelasId) && $activeSession) {
            $students = $this->attendanceModel->getAbsencesByClassAndDate($activeKelasId, $selectedDate, $activeSession['id']);
            $summary = $this->attendanceModel->getAbsenceSummaryByClass($activeKelasId, $activeSession['id'], $startDate, $endDate);

            // Fetch schedules for active class on selected date
            $timestamp = strtotime($selectedDate);
            $dayMap = [
                'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
                'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
            ];
            $dayName = $dayMap[date('D', $timestamp)] ?? '';

            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT s.hour, sub.nama as mapel_nama, u.nama as teacher_nama
                FROM schedules s
                INNER JOIN (
                    SELECT MAX(id) as max_id 
                    FROM schedules 
                    WHERE kelas_id = ? AND academic_year_id = ? 
                    GROUP BY day, hour
                ) latest ON s.id = latest.max_id
                JOIN subjects sub ON s.subject_id = sub.id
                LEFT JOIN users u ON s.teacher_id = u.id
                WHERE s.day = ?
                ORDER BY s.hour ASC
            ");
            $stmt->execute([$activeKelasId, $this->currentYear['id'], $dayName]);
            $schedules = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Fetch KBM dispensations for selected date
            $activityModel = new \App\Models\ActivityModel();
            $allDispensations = $activityModel->getDispensationsByDate($selectedDate);

            $dayOfWeek = date('w', $timestamp);
            if ($dayOfWeek == 5) {
                $dispensations[] = [
                    'id' => null,
                    'name' => 'Libur Mingguan (Jumat)',
                    'is_full_day' => true,
                    'kelas_ids' => [(int)$activeKelasId],
                    'hours' => []
                ];
            }

            foreach ($allDispensations as $disp) {
                if (in_array((int)$activeKelasId, $disp['kelas_ids'])) {
                    $dispensations[] = $disp;
                }
            }
        }

        $this->view('student_attendance/index', [
            'title' => 'Absensi Santri',
            'kelas' => $kelas,
            'activeKelasId' => $activeKelasId,
            'selectedDate' => $selectedDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'students' => $students,
            'summary' => $summary,
            'activeSession' => $activeSession,
            'tab' => $tab,
            'schedules' => $schedules,
            'dispensations' => $dispensations,
            'absentCounts' => $absentCounts
        ]);
    }

    public function store() {
        require_login();
        csrf_validate_token();

        $sessionId = $_POST['session_id'] ?? null;
        $kelasId = $_POST['kelas_id'] ?? null;
        $date = $_POST['date'] ?? null;
        $absencesData = $_POST['absences'] ?? [];

        if (!$sessionId || !$kelasId || !$date) {
            add_flash('Parameter input tidak lengkap.', 'error');
            $this->redirect('/student-attendance');
        }

        // Permission check: admin or PBM committee member of active session, and session is open
        $activeSession = $this->attendanceModel->getActiveSession($this->currentYear['id']);
        if (!$activeSession || $activeSession['id'] != $sessionId || !$activeSession['is_open']) {
            add_flash('Sesi input absensi semester ini sedang ditutup.', 'error');
            $this->redirect('/student-attendance?kelas=' . $kelasId . '&date=' . urlencode($date));
        }

        if (!auth_can_manage_attendance($sessionId)) {
            add_flash('Akses ditolak: Anda tidak memiliki wewenang untuk mengisi absensi.', 'error');
            $this->redirect('/student-attendance?kelas=' . $kelasId . '&date=' . urlencode($date));
        }

        try {
            $this->attendanceModel->saveAbsences($sessionId, $kelasId, $date, $absencesData, auth_get_user_id());
            
            // Log activity
            if (function_exists('log_activity')) {
                log_activity("Merekap absensi santri kelas ID {$kelasId} pada tanggal {$date}");
            }
            
            add_flash('Absensi santri berhasil disimpan.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan absensi: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/student-attendance?kelas=' . $kelasId . '&date=' . urlencode($date));
    }

    public function pbmIndex() {
        require_login();
        
        $activeAY = $this->ayModel->getActive();
        if (!$activeAY) {
            add_flash('Tahun ajaran aktif belum ditentukan.', 'error');
            $this->redirect('/');
        }

        if (!auth_can_manage_attendance()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/student-attendance');
        }

        $sessions = $this->attendanceModel->getSessions($activeAY['id']);
        $allTeachers = $this->teacherModel->findAll();
        usort($allTeachers, function($a, $b) { return strnatcmp($a['nama'], $b['nama']); });
        
        $activeTeachers = array_filter($allTeachers, function($t) {
            return (!isset($t['is_active']) || $t['is_active'] == 1);
        });

        // Fetch PBM committee for each session
        foreach ($sessions as &$s) {
            $s['committee'] = $this->attendanceModel->getCommittee($s['id']);
            $s['committee_ids'] = array_column($s['committee'], 'id');
        }
        unset($s);

        $this->view('student_attendance/pbm', [
            'sessions' => $sessions,
            'activeAY' => $activeAY,
            'allTeachers' => $allTeachers,
            'activeTeachers' => $activeTeachers
        ]);
    }

    public function updateSessionStatus() {
        require_login();
        if (auth_get_role() !== 'admin') {
            add_flash('Hanya Admin yang dapat mengubah status sesi.', 'error');
            $this->redirect('/student-attendance/pbm');
        }
        csrf_validate_token();

        $id = $_POST['id'] ?? null;
        $isOpen = isset($_POST['is_open']) ? (int)$_POST['is_open'] : null;
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : null;

        if ($id) {
            $data = [];
            if ($isOpen !== null) $data['is_open'] = $isOpen;
            if ($isActive !== null) $data['is_active'] = $isActive;

            if ($this->attendanceModel->updateSessionStatus($id, $data)) {
                add_flash('Status sesi semester berhasil diperbarui.', 'success');
            } else {
                add_flash('Gagal memperbarui status sesi.', 'error');
            }
        }
        $this->redirect('/student-attendance/pbm');
    }

    public function updateCommittee() {
        require_login();
        if (auth_get_role() !== 'admin') {
            add_flash('Hanya Admin yang dapat mengelola panitia PBM.', 'error');
            $this->redirect('/student-attendance/pbm');
        }
        csrf_validate_token();

        $sessionId = $_POST['session_id'] ?? null;
        $userIds = $_POST['user_ids'] ?? [];

        if ($sessionId) {
            try {
                $this->attendanceModel->updateCommittee($sessionId, $userIds);
                add_flash('Daftar panitia PBM berhasil diperbarui.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal memperbarui panitia PBM: ' . $e->getMessage(), 'error');
            }
        }
        $this->redirect('/student-attendance/pbm');
    }
}
