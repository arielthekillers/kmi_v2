<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\KelasModel;

class KelasController extends Controller {
    protected $kelasModel;

    public function __construct() {
        parent::__construct();
        $this->kelasModel = new KelasModel();
    }


    public function index() {
        require_admin();
        
        $db = \App\Core\Database::getInstance();
        $teachers = $db->query("SELECT id, nama FROM users WHERE role = 'pengajar' ORDER BY nama ASC")->fetchAll();
        $groupedKelas = $this->kelasModel->getAllGrouped();
        
        renderHeader("Master Kelas");
        $this->view('kelas/index', [
            'groupedKelas' => $groupedKelas,
            'teachers' => $teachers
        ]);
        renderFooter();
    }

    public function store() {
        require_admin();
        csrf_validate_token();

        $id = $_POST['id'] ?? '';
        $data = [
            'tingkat' => htmlspecialchars($_POST['tingkat'] ?? ''),
            'abjad' => htmlspecialchars($_POST['abjad'] ?? ''),
            'location' => htmlspecialchars($_POST['location'] ?? ''),
            'teacher_id' => $_POST['teacher_id'] ?? null
        ];

        try {
            if (!empty($id)) {
                $this->kelasModel->update($id, $data);
                $msg = 'Data kelas berhasil diperbarui.';
            } else {
                $this->kelasModel->create($data);
                $msg = 'Kelas baru berhasil ditambahkan.';
            }
            add_flash($msg, 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan data kelas: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/classes');
    }

    public function detail() {
        require_login();
        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/classes');

        $kelas = $this->kelasModel->find($id);
        if (!$kelas) {
            add_flash('Kelas tidak ditemukan.', 'error');
            $this->redirect('/classes');
        }

        $role = auth_get_role();
        $userId = auth_get_user_id();

        // Check permission: Must be admin OR Wali Kelas for this class
        if ($role !== 'admin' && $kelas['teacher_id'] != $userId) {
            add_flash('Anda tidak memiliki akses ke halaman ini.', 'error');
            $this->redirect('/');
        }

        $tab = $_GET['tab'] ?? 'overview';
        $data = [
            'title' => "Detail Kelas " . $kelas['tingkat'] . "-" . $kelas['abjad'],
            'kelas' => $kelas,
            'tab' => $tab,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $role
        ];

        // Fetch Tab-specific data
        if ($tab === 'santri') {
            $data['students'] = $this->kelasModel->getStudentsWithDetails($id);
        } elseif ($tab === 'jadwal') {
            $data['schedule'] = $this->kelasModel->getScheduleWithDetails($id);
        } elseif ($tab === 'nilai') {
            $gradeModel = new \App\Models\GradeModel();
            $sessions = $gradeModel->getSessions($this->currentYear['id']);
            $activeSession = $gradeModel->getActiveSession($this->currentYear['id']);
            
            $sessionId = $_GET['session_id'] ?? ($activeSession ? $activeSession['id'] : '');
            
            $filters = ['kelas' => $id];
            if (!empty($sessionId)) {
                $filters['exam_session_id'] = $sessionId;
            }
            
            $data['sessions'] = $sessions;
            $data['selected_session_id'] = $sessionId;
            $data['exams'] = $gradeModel->getAllExams($filters);
        } elseif ($tab === 'view_nilai') {
            $examId = $_GET['exam_id'] ?? null;
            if (!$examId) $this->redirect('/classes/detail?id=' . $id . '&tab=nilai');

            $gradeModel = new \App\Models\GradeModel();
            $exam = $gradeModel->getExamById($examId);
            if (!$exam || $exam['kelas_id'] != $id) {
                add_flash('Data nilai tidak ditemukan.', 'error');
                $this->redirect('/classes/detail?id=' . $id . '&tab=nilai');
            }

            $students = $gradeModel->getGrades($examId, $exam['kelas_id'], $exam['academic_year_id']);
            usort($students, function ($a, $b) {
                return strnatcasecmp($a['nama'] ?? '', $b['nama'] ?? '');
            });

            $data['exam'] = $exam;
            $data['students'] = $students;
        } elseif ($tab === 'raport') {
            $gradeModel = new \App\Models\GradeModel();
            $sessions = $gradeModel->getSessions($this->currentYear['id']);
            $activeSession = $gradeModel->getActiveSession($this->currentYear['id']);
            
            $sessionId = $_GET['session_id'] ?? ($activeSession ? $activeSession['id'] : '');
            
            $data['sessions'] = $sessions;
            $data['selected_session_id'] = $sessionId;
            
            if ($sessionId) {
                $data['leger'] = $gradeModel->getClassLeger($id, $sessionId, $this->currentYear['id']);
            } else {
                $data['leger'] = null;
            }
        } elseif ($tab === 'raport_detail') {
            $studentId = $_GET['student_id'] ?? null;
            $sessionId = $_GET['session_id'] ?? null;
            
            if (!$studentId || !$sessionId) {
                $this->redirect('/classes/detail?id=' . $id . '&tab=raport');
            }

            $gradeModel = new \App\Models\GradeModel();
            $sessions = $gradeModel->getSessions($this->currentYear['id']);
            $data['sessions'] = $sessions;
            $data['selected_session_id'] = $sessionId;

            $leger = $gradeModel->getClassLeger($id, $sessionId, $this->currentYear['id']);
            
            $currentStudent = null;
            foreach ($leger['students'] as $s) {
                if ($s['student_id'] == $studentId) {
                    $currentStudent = $s;
                    break;
                }
            }

            if (!$currentStudent) {
                add_flash('Data santri tidak ditemukan.', 'error');
                $this->redirect('/classes/detail?id=' . $id . '&tab=raport&session_id=' . $sessionId);
            }

            $data['student'] = $currentStudent;
            $data['leger'] = $leger;
        }

        renderHeader($data['title']);
        $this->view('kelas/detail', $data);
        renderFooter();
    }

    public function delete() {
        require_admin();
        
        $id = $_GET['id'] ?? '';
        if (!empty($id)) {
            try {
                $this->kelasModel->delete($id);
                add_flash('Data kelas berhasil dihapus.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal menghapus data kelas: ' . $e->getMessage(), 'error');
            }
        }
        
        $this->redirect('/classes');
    }
}
