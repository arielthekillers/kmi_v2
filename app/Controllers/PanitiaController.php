<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\GradeModel;
use App\Models\AcademicYearModel;
use App\Models\TeacherModel;

class PanitiaController extends Controller {
    protected $gradeModel;
    protected $ayModel;
    protected $teacherModel;

    public function __construct() {
        parent::__construct();
        $this->gradeModel = new GradeModel();
        $this->ayModel = new AcademicYearModel();
        $this->teacherModel = new TeacherModel();
    }

    public function index() {
        require_login();
        
        $activeAY = $this->ayModel->getActive();
        if (!$activeAY) {
            add_flash('Tahun ajaran aktif belum ditentukan.', 'error');
            redirect('/');
        }

        // Only Admin can manage sessions? Or Panitia for any session can manage others?
        // User said "Panitia same as admin". We check if user is admin OR panitia for any session.
        if (!auth_can_manage_grades()) {
            add_flash('Akses ditolak.', 'error');
            redirect('/grades');
        }

        $sessions = $this->gradeModel->getSessions($activeAY['id']);
        $allTeachers = $this->teacherModel->findAll();
        
        // Fetch committee for each session
        foreach ($sessions as &$s) {
            $s['committee'] = $this->gradeModel->getCommittee($s['id']);
            $s['committee_ids'] = array_column($s['committee'], 'id');
        }

        $this->view('panitia/index', [
            'sessions' => $sessions,
            'activeAY' => $activeAY,
            'allTeachers' => $allTeachers
        ]);
    }

    public function updateSessionStatus() {
        require_login();
        if (!auth_can_manage_grades()) redirect('/grades');
        csrf_validate_token();

        $id = $_POST['id'] ?? null;
        $isOpen = isset($_POST['is_open']) ? (int)$_POST['is_open'] : null;
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : null;

        if ($id) {
            $data = [];
            if ($isOpen !== null) $data['is_open'] = $isOpen;
            if ($isActive !== null) $data['is_active'] = $isActive;

            if ($this->gradeModel->updateSessionStatus($id, $data)) {
                add_flash('Status sesi ujian berhasil diperbarui.', 'success');
            } else {
                add_flash('Gagal memperbarui status sesi.', 'error');
            }
        }
        redirect('/grades/panitia');
    }

    public function updateCommittee() {
        require_login();
        if (!auth_can_manage_grades()) redirect('/grades');
        csrf_validate_token();

        $sessionId = $_POST['session_id'] ?? null;
        $userIds = $_POST['user_ids'] ?? [];

        if ($sessionId) {
            try {
                $this->gradeModel->updateCommittee($sessionId, $userIds);
                add_flash('Daftar panitia berhasil diperbarui.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal memperbarui panitia: ' . $e->getMessage(), 'error');
            }
        }
        redirect('/grades/panitia');
    }
}
