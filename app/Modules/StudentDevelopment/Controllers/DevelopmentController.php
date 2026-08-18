<?php

namespace App\Modules\StudentDevelopment\Controllers;

use App\Core\Controller;
use App\Modules\StudentDevelopment\Models\DevelopmentModel;
use App\Models\KelasModel;
use App\Models\SubjectModel;

class DevelopmentController extends Controller {
    protected $model;

    public function __construct() {
        parent::__construct();
        require_login();
        $this->model = new DevelopmentModel();
    }

    /**
     * Dashboard View
     */
    public function index() {
        $role = auth_get_role();
        $userId = auth_get_user_id();

        $data = [
            'title' => 'Pemantauan Perkembangan Santri',
            'role' => $role,
            'user' => $_SESSION['user']['nama'] ?? 'Pengguna',
            'categories' => $this->model->getCategories(),
        ];

        // 1. Admin & BK (Akses Global)
        if ($role === 'admin') {
            $page = (int)($_GET['page'] ?? 1);
            if ($page < 1) $page = 1;
            $limit = 15;
            $offset = ($page - 1) * $limit;

            $filters = [
                'kelas_id' => $_GET['kelas_id'] ?? '',
                'type' => $_GET['type'] ?? '',
                'category_id' => $_GET['category_id'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'q' => $_GET['q'] ?? '',
            ];

            $data['observations'] = $this->model->getObservations($filters, $limit, $offset);
            $total = $this->model->countObservations($filters);
            $data['total_pages'] = ceil($total / $limit);
            $data['page'] = $page;
            $data['filters'] = $filters;

            $kelasModel = new KelasModel();
            $data['kelas_list'] = $kelasModel->findAllActive();
            $data['classes_stats'] = $this->model->getClassesWithStats();

            $this->view('layouts/header', $data);
            $this->view('StudentDevelopment/Views/dashboard', $data);
            $this->view('layouts/footer', $data);
            return;
        }

        // 2. Guru / Wali Kelas (Akses Terbatas)
        // Check if teacher is also a Wali Kelas in active year
        $waliKelasClasses = auth_get_wali_kelas_kelas();
        $data['is_wali_kelas'] = !empty($waliKelasClasses);
        $data['wali_kelas_classes'] = $waliKelasClasses;

        // Guru View - observations they made
        $guruFilters = ['teacher_id' => $userId];
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $data['my_observations'] = $this->model->getObservations($guruFilters, $limit, $offset);
        $myTotal = $this->model->countObservations($guruFilters);
        $data['my_total_pages'] = ceil($myTotal / $limit);
        $data['my_page'] = $page;

        // Wali Kelas View - statistics of their class
        if ($data['is_wali_kelas']) {
            $classStudents = [];
            foreach ($waliKelasClasses as $kls) {
                $classStudents[$kls['id']] = $this->model->getClassStudentsWithStats($kls['id']);
            }
            $data['class_students'] = $classStudents;
        }

        $this->view('layouts/header', $data);
        $this->view('StudentDevelopment/Views/dashboard', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Input Observasi View
     */
    public function observe() {
        $userId = auth_get_user_id();
        $preselectedStudent = $_GET['student_id'] ?? '';
        $scheduleContext = $this->model->getContextualSchedule($userId);

        $subjectModel = new SubjectModel();
        $kelasModel = new KelasModel();

        $data = [
            'title' => 'Catat Observasi Santri',
            'students' => $this->model->getStudentsForSelect(),
            'categories' => $this->model->getCategories(),
            'preselected_student' => $preselectedStudent,
            'schedule_context' => $scheduleContext,
            'subjects' => $subjectModel->findAll(),
            'kelas_list' => $kelasModel->findAllActive(),
            'action' => url('/student-development/observe/store'),
        ];

        $this->view('layouts/header', $data);
        $this->view('StudentDevelopment/Views/observe', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Store Observation
     */
    public function storeObservation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/student-development');
        }

        csrf_validate_token();

        $targetType = $_POST['target_type'] ?? 'student'; // 'student' or 'class'
        $studentId = ($targetType === 'student' && !empty($_POST['student_id'])) ? (int)$_POST['student_id'] : null;
        $kelasId = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
        $type = $_POST['type'] ?? '';
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $context = trim($_POST['context'] ?? '');
        $subjectId = !empty($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;
        $observationDate = $_POST['observation_date'] ?? date('Y-m-d');

        if (($targetType === 'student' && !$studentId) || ($targetType === 'class' && !$kelasId) || !$type || !$categoryId || empty($content)) {
            add_flash('Semua field wajib (Target Observasi, Tipe, Kategori, dan Catatan) harus diisi.', 'error');
            $this->redirect('/student-development/observe');
        }

        $userId = auth_get_user_id();

        $insertData = [
            'student_id' => $studentId,
            'teacher_id' => $userId,
            'type' => $type,
            'category_id' => $categoryId,
            'content' => $content,
            'context' => $context,
            'kelas_id' => $kelasId,
            'subject_id' => $subjectId,
            'observation_date' => $observationDate,
        ];

        $result = $this->model->storeObservation($insertData);

        if ($result) {
            add_flash('Catatan observasi berhasil disimpan.', 'success');
            // If page requested to redirect back to a profile (only if studentId is set)
            if ($studentId && !empty($_POST['redirect_student_profile'])) {
                $this->redirect('/student-development/student?id=' . $studentId);
            }
            $this->redirect('/student-development');
        } else {
            add_flash('Gagal menyimpan observasi. Silakan coba lagi.', 'error');
            $this->redirect('/student-development/observe');
        }
    }

    /**
     * Student Profile timeline and metrics
     */
    public function studentProfile() {
        $studentId = (int)($_GET['id'] ?? 0);
        if (!$studentId) {
            add_flash('ID Santri tidak valid.', 'error');
            $this->redirect('/student-development');
        }

        $profile = $this->model->getStudentProfile($studentId);
        if (!$profile) {
            add_flash('Profil santri tidak ditemukan atau sudah dinonaktifkan.', 'error');
            $this->redirect('/student-development');
        }

        // Authorization check: 
        // Guru can only view profile if they created at least one observation for this student
        // OR if they are the Wali Kelas for this student
        // Admin / BK can see everything
        $role = auth_get_role();
        $userId = auth_get_user_id();
        $isAuthorized = ($role === 'admin');

        if (!$isAuthorized) {
            // Check if user is Wali Kelas for this student's class
            $waliClasses = auth_get_wali_kelas_kelas();
            foreach ($waliClasses as $kls) {
                if ($kls['id'] == $profile['kelas_id']) {
                    $isAuthorized = true;
                    break;
                }
            }
        }

        if (!$isAuthorized) {
            // Check if guru has recorded any observation for this student
            $myObsCount = $this->model->countObservations([
                'student_id' => $studentId,
                'teacher_id' => $userId
            ]);
            if ($myObsCount > 0) {
                $isAuthorized = true;
            }
        }

        // For Guru who are not Wali Kelas & have no records yet, but they want to view the profile 
        // when they click to record an observation. Let's allow view if they are just reading, but 
        // restrict showing other teacher's comments if strict privacy is needed.
        // Let's allow view, but filter out other teacher's details if they are just a regular teacher with no relationship.
        // To be safe and respect user requirements: Wali kelas and Admin see everything. 
        // Regular teachers only see their own recorded observations in the timeline.
        $timeline = $this->model->getStudentTimeline($studentId);
        if ($role !== 'admin') {
            $isWaliKelasThisStudent = false;
            $waliClasses = auth_get_wali_kelas_kelas();
            foreach ($waliClasses as $kls) {
                if ($kls['id'] == $profile['kelas_id']) {
                    $isWaliKelasThisStudent = true;
                    break;
                }
            }

            if (!$isWaliKelasThisStudent) {
                // Regular teacher: Filter timeline to only show their own observations
                $timeline = array_filter($timeline, function($obs) use ($userId) {
                    return $obs['teacher_id'] == $userId;
                });
            }
        }

        $stats = $this->model->getStudentStats($studentId);
        $distribution = $this->model->getStudentCategoryDistribution($studentId);

        $data = [
            'title' => 'Profil Perkembangan: ' . htmlspecialchars($profile['nama']),
            'profile' => $profile,
            'stats' => $stats,
            'distribution' => $distribution,
            'timeline' => $timeline,
            'role' => $role,
            'userId' => $userId,
        ];

        $this->view('layouts/header', $data);
        $this->view('StudentDevelopment/Views/student_profile', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Update Context
     */
    public function updateContext() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/student-development');
        }

        csrf_validate_token();

        $obsId = (int)($_POST['observation_id'] ?? 0);
        $context = trim($_POST['context'] ?? '');
        $studentId = (int)($_POST['student_id'] ?? 0);

        if (!$obsId || empty($context)) {
            add_flash('Isi konteks tidak boleh kosong.', 'error');
            $this->redirect('/student-development/student?id=' . $studentId);
        }

        // Authorization check: User must be Admin, BK, Wali Kelas of the student, or the creator of the observation
        $obs = $this->model->getObservationById($obsId);
        if (!$obs) {
            add_flash('Catatan observasi tidak ditemukan.', 'error');
            $this->redirect('/student-development');
        }

        $role = auth_get_role();
        $userId = auth_get_user_id();
        $isAuthorized = ($role === 'admin' || $obs['teacher_id'] == $userId);

        if (!$isAuthorized) {
            // Check if Wali Kelas
            $waliClasses = auth_get_wali_kelas_kelas();
            foreach ($waliClasses as $kls) {
                if ($kls['id'] == $obs['kelas_id']) {
                    $isAuthorized = true;
                    break;
                }
            }
        }

        if ($isAuthorized) {
            $this->model->updateContext($obsId, $context);
            add_flash('Konteks berhasil ditambahkan/diperbarui.', 'success');
        } else {
            add_flash('Anda tidak memiliki otorisasi untuk menambah konteks pada catatan ini.', 'error');
        }

        $this->redirect('/student-development/student?id=' . $studentId);
    }

    /**
     * Add Response
     */
    public function addResponse() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/student-development');
        }

        csrf_validate_token();

        $obsId = (int)($_POST['observation_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $studentId = (int)($_POST['student_id'] ?? 0);

        if (!$obsId || empty($content)) {
            add_flash('Respon tidak boleh kosong.', 'error');
            $this->redirect('/student-development/student?id=' . $studentId);
        }

        $obs = $this->model->getObservationById($obsId);
        if (!$obs) {
            add_flash('Catatan observasi tidak ditemukan.', 'error');
            $this->redirect('/student-development');
        }

        // Authorization check: Admin, BK, or Wali Kelas of the student
        $role = auth_get_role();
        $userId = auth_get_user_id();
        $isAuthorized = ($role === 'admin');

        if (!$isAuthorized) {
            $waliClasses = auth_get_wali_kelas_kelas();
            foreach ($waliClasses as $kls) {
                if ($kls['id'] == $obs['kelas_id']) {
                    $isAuthorized = true;
                    break;
                }
            }
        }

        if ($isAuthorized) {
            $this->model->addResponse($obsId, $userId, $content);
            add_flash('Respon berhasil disimpan.', 'success');
        } else {
            add_flash('Hanya wali kelas atau admin yang dapat memberikan respon formal.', 'error');
        }

        $this->redirect('/student-development/student?id=' . $studentId);
    }

    /**
     * Categories settings page (Admin only)
     */
    public function categories() {
        require_admin();

        $data = [
            'title' => 'Pengaturan Kategori Observasi',
            'categories' => $this->model->getCategories(),
            'role' => auth_get_role(),
            'user' => $_SESSION['user']['nama'] ?? 'Admin',
        ];

        $this->view('layouts/header', $data);
        $this->view('StudentDevelopment/Views/categories', $data);
        $this->view('layouts/footer', $data);
    }

    public function storeCategory() {
        require_admin();
        csrf_validate_token();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $color = trim($_POST['color'] ?? '#64748b');

        if (empty($name)) {
            add_flash('Nama kategori wajib diisi.', 'error');
            $this->redirect('/student-development/categories');
        }

        $result = $this->model->storeCategory(['name' => $name, 'description' => $description, 'color' => $color]);

        if ($result) {
            add_flash('Kategori baru berhasil ditambahkan.', 'success');
        } else {
            add_flash('Gagal menambahkan kategori. Nama kategori mungkin sudah terdaftar.', 'error');
        }
        $this->redirect('/student-development/categories');
    }

    public function updateCategory() {
        require_admin();
        csrf_validate_token();

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $color = trim($_POST['color'] ?? '#64748b');

        if (!$id || empty($name)) {
            add_flash('Nama kategori wajib diisi.', 'error');
            $this->redirect('/student-development/categories');
        }

        $result = $this->model->updateCategory($id, ['name' => $name, 'description' => $description, 'color' => $color]);

        if ($result) {
            add_flash('Kategori berhasil diperbarui.', 'success');
        } else {
            add_flash('Gagal memperbarui kategori.', 'error');
        }
        $this->redirect('/student-development/categories');
    }

    public function deleteCategory() {
        require_admin();
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            add_flash('ID kategori tidak valid.', 'error');
            $this->redirect('/student-development/categories');
        }

        try {
            $result = $this->model->deleteCategory($id);
            if ($result) {
                add_flash('Kategori berhasil dihapus.', 'success');
            } else {
                add_flash('Gagal menghapus kategori.', 'error');
            }
        } catch (\Exception $e) {
            add_flash('Kategori tidak dapat dihapus karena masih digunakan oleh beberapa catatan observasi.', 'error');
        }

        $this->redirect('/student-development/categories');
    }

    /**
     * AJAX endpoint to get subjects list for a specific class
     */
    public function getClassSubjects() {
        header('Content-Type: application/json');
        $kelasId = (int)($_GET['kelas_id'] ?? 0);
        if (!$kelasId) {
            echo json_encode([]);
            exit;
        }

        $subjects = $this->model->getSubjectsByClass($kelasId);
        echo json_encode($subjects);
        exit;
    }

    public function deleteObservation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/student-development');
        }

        csrf_validate_token();

        $obsId = (int)($_POST['observation_id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);

        if (!$obsId) {
            add_flash('ID Catatan tidak valid.', 'error');
            $this->redirect('/student-development');
        }

        $obs = $this->model->getObservationById($obsId);
        if (!$obs) {
            add_flash('Catatan observasi tidak ditemukan.', 'error');
            $this->redirect('/student-development');
        }

        $role = auth_get_role();
        $userId = auth_get_user_id();
        $isAuthorized = ($role === 'admin' || $obs['teacher_id'] == $userId);

        if ($isAuthorized) {
            $this->model->deleteObservation($obsId);
            add_flash('Catatan observasi berhasil dihapus.', 'success');
        } else {
            add_flash('Anda tidak memiliki otorisasi untuk menghapus catatan ini.', 'error');
        }

        if ($studentId) {
            $this->redirect('/student-development/student?id=' . $studentId);
        } else {
            $this->redirect('/student-development');
        }
    }
}
