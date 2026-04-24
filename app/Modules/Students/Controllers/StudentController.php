<?php

namespace App\Modules\Students\Controllers;

use App\Core\Controller;
use App\Modules\Students\Models\Student;

class StudentController extends Controller {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        require_admin();

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $q = $_GET['q'] ?? '';
        $kelas_id = $_GET['kelas_id'] ?? '';
        
        $model = new Student();
        $kelas = $model->getKelasList();
        
        $students = [];
        $totalItems = 0;
        $isSearching = !empty($q) || !empty($kelas_id);

        if ($isSearching) {
            $filters = ['q' => $q, 'kelas_id' => $kelas_id];
            $students = $model->getAll($filters, $limit, $offset);
            $totalItems = $model->countAll($filters);
        }
        
        $totalPages = ceil($totalItems / $limit);
        
        $data = [
            'title' => 'Data Santri',
            'students' => $students,
            'kelas' => $kelas,
            'q' => $q,
            'selected_kelas' => $kelas_id,
            'is_searching' => $isSearching,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function create() {
        require_admin();

        $model = new Student();
        $kelas = $model->getKelasList();
        
        $data = [
            'title' => 'Tambah Data Santri',
            'kelas' => $kelas,
            'student' => null,
            'action' => url('/students/store'),
            'q' => $_GET['q'] ?? '',
            'selected_kelas' => $_GET['kelas_id'] ?? '',
            'page' => $_GET['page'] ?? 1,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/form', $data);
        $this->view('layouts/footer', $data);
    }

    public function edit() {
        require_admin();
        $id = $_GET['id'] ?? null;

        $model = new Student();
        $student = $model->find($id);
        
        if (!$student) {
            add_flash('Data santri tidak ditemukan.', 'error');
            $this->redirect('/students');
        }

        $kelas = $model->getKelasList();
        
        $data = [
            'title' => 'Edit Data Santri',
            'kelas' => $kelas,
            'student' => $student,
            'action' => url("/students/update"),
            'q' => $_GET['q'] ?? '',
            'selected_kelas' => $_GET['kelas_id'] ?? '',
            'page' => $_GET['page'] ?? 1,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/form', $data);
        $this->view('layouts/footer', $data);
    }

    public function store() {
        require_admin();
        $data = $_POST;
        
        $q = $_POST['q'] ?? '';
        $kelas_id = $_POST['selected_kelas'] ?? '';
        $page = $_POST['page'] ?? 1;
        unset($data['q'], $data['selected_kelas'], $data['page']);

        $model = new Student();
        try {
            $model->create($data);
            add_flash('Data santri berhasil ditambahkan.', 'success');
            $this->redirect("/students?q=" . urlencode($q) . "&kelas_id=$kelas_id&page=$page");
        } catch (\Exception $e) {
            add_flash('Gagal menambah santri: ' . $e->getMessage(), 'error');
            $this->redirect("/students/create?q=" . urlencode($q) . "&kelas_id=$kelas_id&page=$page");
        }
    }

    public function update() {
        require_admin();
        $id = $_POST['id'] ?? null;
        $data = $_POST;
        
        $q = $_POST['q'] ?? '';
        $kelas_id = $_POST['selected_kelas'] ?? '';
        $page = $_POST['page'] ?? 1;
        unset($data['q'], $data['selected_kelas'], $data['page']);

        $model = new Student();
        try {
            $model->update($id, $data);
            add_flash('Data santri berhasil diperbarui.', 'success');
            $this->redirect("/students?q=" . urlencode($q) . "&kelas_id=$kelas_id&page=$page");
        } catch (\Exception $e) {
            add_flash('Gagal memperbarui santri: ' . $e->getMessage(), 'error');
            $this->redirect("/students/edit?id=$id&q=" . urlencode($q) . "&kelas_id=$kelas_id&page=$page");
        }
    }

    public function delete() {
        require_admin();
        $id = $_GET['id'] ?? null;
        
        $model = new Student();
        try {
            $model->delete($id);
            add_flash('Data santri berhasil dihapus.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menghapus santri: ' . $e->getMessage(), 'error');
        }
        $this->redirect('/students');
    }

    public function promote() {
        require_admin();
        
        $model = new Student();
        $kelas = $model->getKelasList();
        
        $yearModel = new \App\Models\AcademicYearModel();
        $allYears = $yearModel->getAll();

        $sourceKelasId = $_GET['kelas_id'] ?? null;
        $students = [];
        if ($sourceKelasId) {
            $students = $model->getAll(['kelas_id' => $sourceKelasId]);
        }

        $data = [
            'title' => 'Promosi Naik Kelas',
            'kelas' => $kelas,
            'allYears' => $allYears,
            'students' => $students,
            'sourceKelasId' => $sourceKelasId,
            'currentYear' => $this->currentYear
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/promote', $data);
        $this->view('layouts/footer', $data);
    }

    public function processPromotion() {
        require_admin();
        
        $sourceKelasId = $_POST['source_kelas_id'] ?? null;
        $targetKelasId = $_POST['target_kelas_id'] ?? null;
        $targetYearId = $_POST['target_year_id'] ?? null;
        $studentIds = $_POST['student_ids'] ?? [];

        if (!$targetKelasId || !$targetYearId || empty($studentIds)) {
            add_flash('Data promosi tidak lengkap.', 'error');
            $this->redirect('/students/promote');
        }

        $model = new Student();
        $successCount = 0;

        foreach ($studentIds as $studentId) {
            try {
                $model->enroll($studentId, $targetKelasId, $targetYearId);
                $successCount++;
            } catch (\Exception $e) {
                // Log error if needed
            }
        }

        add_flash("Berhasil memproses promosi/pindah kelas untuk $successCount santri.", 'success');
        $this->redirect('/students');
    }

    public function apiRegions() {
        header('Content-Type: application/json');
        $type = $_GET['type'] ?? 'provinces';
        $parentId = $_GET['parent_id'] ?? null;

        $baseUrl = "https://wilayah.id/api";
        $url = "$baseUrl/$type.json";
        
        if ($parentId) {
            $url = "$baseUrl/$type/$parentId.json";
        }

        $cacheDir = __DIR__ . '/../../../Storage/cache/regions';
        if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);
        $cacheFile = $cacheDir . '/' . md5($url) . '.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400 * 7)) { // 1 week cache
            echo file_get_contents($cacheFile);
            exit;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        $content = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($content) {
            file_put_contents($cacheFile, $content);
            echo $content;
        } else {
            error_log("Wilayah.id API Curl Error: " . $curlError);
            echo json_encode(['data' => [], 'error' => $curlError]);
        }
        exit;
    }
}
