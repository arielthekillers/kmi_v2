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
        $status = $_GET['status'] ?? 'Active';
        
        $model = new Student();
        $kelas = $model->getKelasList();
        
        $students = [];
        $totalItems = 0;
        $isSearching = !empty($q) || !empty($kelas_id) || $status !== 'Active';

        if ($isSearching) {
            $filters = ['q' => $q, 'kelas_id' => $kelas_id, 'status' => $status];
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
            'selected_status' => $status,
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
        $history = $model->getHistory($id);
        
        $data = [
            'title' => 'Edit Data Santri',
            'kelas' => $kelas,
            'student' => $student,
            'history' => $history,
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
            add_flash('Data santri berhasil dipindahkan ke tempat sampah.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menghapus santri: ' . $e->getMessage(), 'error');
        }
        $this->redirect('/students');
    }

    public function trash() {
        require_admin();

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $q = $_GET['q'] ?? '';
        
        $model = new Student();
        
        $filters = ['q' => $q];
        $students = $model->getTrash($filters, $limit, $offset);
        $totalItems = $model->countTrash($filters);
        
        $totalPages = ceil($totalItems / $limit);
        
        $data = [
            'title' => 'Tempat Sampah Santri',
            'students' => $students,
            'q' => $q,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/trash', $data);
        $this->view('layouts/footer', $data);
    }

    public function restore() {
        require_admin();
        $id = $_GET['id'] ?? null;
        
        $model = new Student();
        try {
            $model->restore($id);
            add_flash('Data santri berhasil dipulihkan.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal memulihkan santri: ' . $e->getMessage(), 'error');
        }
        $this->redirect('/students/trash');
    }

    public function forceDelete() {
        require_admin();
        $id = $_GET['id'] ?? null;
        
        $model = new Student();
        try {
            $model->forceDelete($id);
            add_flash('Data santri berhasil dihapus secara permanen.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menghapus santri secara permanen: ' . $e->getMessage(), 'error');
        }
        $this->redirect('/students/trash');
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
            foreach ($students as &$s) {
                $history = $model->getHistory($s['id']);
                $s['is_promoted'] = false;
                $s['promoted_to'] = '';
                
                if (!empty($history)) {
                    $latest = $history[0];
                    if ($latest['academic_year_id'] > $this->currentYear['id']) {
                        $s['is_promoted'] = true;
                        $s['promoted_to'] = 'Kelas ' . $latest['tingkat'] . '-' . $latest['abjad'] . ' (' . $latest['year_name'] . ')';
                    }
                }
            }
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

    public function export() {
        require_admin();

        $kelasIds = $_POST['export_kelas'] ?? [];
        $fields = $_POST['export_fields'] ?? [];

        if (empty($kelasIds)) {
            add_flash('Pilih minimal satu kelas untuk di-export.', 'error');
            $this->redirect('/students');
        }
        if (empty($fields)) {
            add_flash('Pilih minimal satu data untuk di-export.', 'error');
            $this->redirect('/students');
        }

        $model = new Student();
        $students = [];
        
        foreach ($kelasIds as $kId) {
            if ($kId === 'all') continue;
            $studentsInClass = $model->getAll(['kelas_id' => $kId], 10000, 0);
            $students = array_merge($students, $studentsInClass);
        }

        $allFieldsMap = [
            'nis' => 'NIS',
            'nisn' => 'NISN',
            'nik' => 'NIK',
            'nama' => 'Nama Lengkap',
            'gender' => 'Jenis Kelamin',
            'kelas' => 'Kelas',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'tahun_masuk' => 'Tahun Masuk',
            'alamat' => 'Alamat Lengkap',
            'provinsi' => 'Provinsi',
            'kabupaten' => 'Kabupaten/Kota',
            'kecamatan' => 'Kecamatan',
            'kelurahan' => 'Kelurahan/Desa',
            'rt_rw' => 'RT/RW',
            'kode_pos' => 'Kode Pos',
            'nama_kk' => 'Nama Kepala Keluarga',
            'nama_wali' => 'Nama Wali',
            'pekerjaan_ayah' => 'Pekerjaan Ayah',
            'no_hp_ayah' => 'No HP Ayah',
            'nama_ibu' => 'Nama Ibu',
            'pekerjaan_ibu' => 'Pekerjaan Ibu',
            'no_hp_ibu' => 'No HP Ibu'
        ];

        // Filter valid fields
        $selectedHeaders = [];
        foreach ($fields as $field) {
            if (isset($allFieldsMap[$field]) && $field !== 'all') {
                $selectedHeaders[$field] = $allFieldsMap[$field];
            }
        }

        $filename = "Data_Santri_" . date('Ymd_His') . ".xls";

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        
        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"></head>';
        $html .= '<body>';
        $html .= '<table border="1">';
        
        // Headers
        $html .= '<tr>';
        foreach ($selectedHeaders as $label) {
            $html .= '<th style="background-color: #4CAF50; color: white;">' . htmlspecialchars($label) . '</th>';
        }
        $html .= '</tr>';

        // Data
        foreach ($students as $student) {
            $html .= '<tr>';
            foreach ($selectedHeaders as $key => $label) {
                $val = '';
                if ($key === 'kelas') {
                    $val = ($student['tingkat'] ?? '') . ' ' . ($student['abjad'] ?? '');
                } else if ($key === 'gender') {
                    $val = ($student['gender'] === 'L') ? 'Laki-laki' : 'Perempuan';
                } else {
                    $val = $student[$key] ?? '';
                }
                
                // Fields that should be treated as text to prevent losing leading zeros or scientific notation
                $textFields = ['nis', 'nisn', 'nik', 'kode_pos', 'no_hp_ayah', 'no_hp_ibu'];
                
                if (in_array($key, $textFields)) {
                    $html .= '<td style="mso-number-format:\'\@\';">' . htmlspecialchars($val) . '</td>';
                } else {
                    $html .= '<td>' . htmlspecialchars($val) . '</td>';
                }
            }
            $html .= '</tr>';
        }
        
        $html .= '</table></body></html>';
        
        fwrite($output, $html);
        fclose($output);
        exit;
    }

    public function processPromotion() {
        require_admin();
        
        $sourceKelasId = $_POST['source_kelas_id'] ?? null;
        $targetKelasId = $_POST['target_kelas_id'] ?? null;
        $targetYearId = $_POST['target_year_id'] ?? null;
        $studentIds = $_POST['student_ids'] ?? [];
        $mutationType = $_POST['mutation_type'] ?? 'kenaikan';

        if (empty($studentIds)) {
            add_flash('Pilih minimal satu santri.', 'error');
            $this->redirect('/students/promote?kelas_id=' . $sourceKelasId);
        }

        if (($mutationType === 'kenaikan' || $mutationType === 'perpindahan') && (!$targetKelasId || !$targetYearId)) {
            add_flash('Data target mutasi tidak lengkap.', 'error');
            $this->redirect('/students/promote?kelas_id=' . $sourceKelasId);
        }

        $model = new Student();
        $successCount = 0;

        foreach ($studentIds as $studentId) {
            try {
                if ($mutationType === 'kenaikan' || $mutationType === 'perpindahan') {
                    // Prevent backend double mutation for 'kenaikan'
                    if ($mutationType === 'kenaikan') {
                        $exists = $model->getHistory($studentId);
                        $alreadyPromoted = false;
                        foreach ($exists as $h) {
                            if ($h['academic_year_id'] == $targetYearId) {
                                $alreadyPromoted = true;
                                break;
                            }
                        }
                        if ($alreadyPromoted) continue;
                    }
                    
                    $model->enroll($studentId, $targetKelasId, $targetYearId);
                } else if ($mutationType === 'kelulusan') {
                    // Prevent if already graduated
                    $history = $model->getHistory($studentId);
                    if (!empty($history) && $history[0]['status'] === 'Graduated') {
                        continue;
                    }
                    
                    // Graduate them in the current academic year
                    $model->updateEnrollmentStatus($studentId, $this->currentYear['id'], 'Graduated');
                }
                $successCount++;
            } catch (\Exception $e) {
                // Log error if needed
            }
        }

        $msg = $mutationType === 'kelulusan' ? 'meluluskan' : 'memproses mutasi';
        if (function_exists('log_activity') && $successCount > 0) {
            $logAction = $mutationType === 'kelulusan' ? "Meluluskan $successCount santri dari Kelas ID: $sourceKelasId" : "Memutasi/Menaikkan $successCount santri dari Kelas ID: $sourceKelasId ke Kelas ID: $targetKelasId (TA ID: $targetYearId)";
            log_activity($logAction);
        }
        add_flash("Berhasil $msg $successCount santri.", 'success');
        $this->redirect('/students/promote?kelas_id=' . $sourceKelasId);
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

    public function apiGetKelas() {
        header('Content-Type: application/json');
        $yearId = $_GET['year_id'] ?? null;
        if (!$yearId) {
            echo json_encode([]);
            exit;
        }
        $model = new Student();
        $kelas = $model->getKelasByYear($yearId);
        echo json_encode($kelas);
        exit;
    }

    /**
     * View Student Academic History Timeline
     */
    public function history() {
        require_admin();
        $id = $_GET['id'] ?? null;

        $model = new Student();
        $student = $model->find($id);
        
        if (!$student) {
            add_flash('Data santri tidak ditemukan.', 'error');
            $this->redirect('/students');
        }

        $history = $model->getHistory($id);
        $kelas = $model->getKelasList();
        
        $yearModel = new \App\Models\AcademicYearModel();
        $allYears = $yearModel->getAll();

        $data = [
            'title' => 'Riwayat Akademik Santri',
            'student' => $student,
            'history' => $history,
            'kelas' => $kelas,
            'allYears' => $allYears,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'admin'
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/history', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Update Student Enrollment Status (Lulus, Keluar, Pindah)
     */
    public function updateStatus() {
        require_admin();
        $studentId = $_POST['student_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$studentId || !in_array($status, ['Graduated', 'Out'])) {
            add_flash('Data status tidak valid.', 'error');
            $this->redirect('/students');
        }

        $model = new Student();
        try {
            $model->updateEnrollmentStatus($studentId, $this->currentYear['id'], $status);
            add_flash("Status akademik santri berhasil diperbarui menjadi '$status'.", 'success');
        } catch (\Exception $e) {
            add_flash('Gagal memperbarui status akademik: ' . $e->getMessage(), 'error');
        }
        $this->redirect("/students/history?id=$studentId");
    }

    /**
     * Re-enroll Inactive/Returned Student into a Class
     */
    public function reEnroll() {
        require_admin();
        $studentId = $_POST['student_id'] ?? null;
        $kelasId = $_POST['kelas_id'] ?? null;
        $yearId = $_POST['academic_year_id'] ?? null;

        if (!$studentId || !$kelasId || !$yearId) {
            add_flash('Data pendaftaran kembali tidak lengkap.', 'error');
            $this->redirect('/students');
        }

        $model = new Student();
        try {
            $model->reEnroll($studentId, $kelasId, $yearId);
            add_flash('Santri berhasil didaftarkan masuk kembali ke kelas.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal mendaftarkan kembali santri: ' . $e->getMessage(), 'error');
        }
        $this->redirect("/students/history?id=$studentId");
    }
    public function rollbackHistory() {
        require_admin();
        $enrollmentId = $_POST['enrollment_id'] ?? null;
        $studentId = $_POST['student_id'] ?? null;

        if (!$enrollmentId || !$studentId) {
            add_flash('Data tidak valid.', 'error');
            $this->redirect('/students');
        }

        $model = new Student();
        try {
            $model->rollbackEnrollment($enrollmentId);
            add_flash('Riwayat mutasi berhasil dihapus dan dibatalkan.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal membatalkan mutasi: ' . $e->getMessage(), 'error');
        }
        $this->redirect("/students/history?id=$studentId");
    }
}
