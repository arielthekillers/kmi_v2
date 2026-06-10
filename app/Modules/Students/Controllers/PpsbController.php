<?php

namespace App\Modules\Students\Controllers;

use App\Core\Controller;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\PpsbRegistration;

class PpsbController extends Controller {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Public Registration Form
     */
    public function register() {
        $data = [
            'title' => 'Pendaftaran Santri Baru (PPSB)',
            'user' => $_SESSION['nama'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ];

        // Public view (uses layout header & footer, but without login enforcement)
        $this->view('layouts/header', $data);
        $this->view('Students/Views/ppsb_register', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Store Public Registration
     */
    public function storePublic() {
        $data = $_POST;
        
        // Basic Validation
        if (empty($data['nama']) || empty($data['gender']) || empty($data['tempat_lahir']) || empty($data['tanggal_lahir']) || empty($data['alamat']) || empty($data['nama_wali']) || empty($data['no_hp_wali'])) {
            add_flash('Semua field wajib diisi.', 'error');
            $this->redirect('/ppsb/daftar');
        }

        $model = new PpsbRegistration();
        try {
            $regNo = $model->generateRegNo();
            $data['registration_no'] = $regNo;
            $data['status'] = 'Pending';
            
            $model->create($data);
            
            add_flash('Pendaftaran berhasil! Simpan kartu pendaftaran Anda.', 'success');
            $this->redirect("/ppsb/success?reg_no=" . urlencode($regNo));
        } catch (\Exception $e) {
            add_flash('Gagal mendaftar: ' . $e->getMessage(), 'error');
            $this->redirect('/ppsb/daftar');
        }
    }

    /**
     * Public Success/Receipt Page
     */
    public function success() {
        $regNo = $_GET['reg_no'] ?? null;
        if (!$regNo) {
            add_flash('Data pendaftaran tidak valid.', 'error');
            $this->redirect('/ppsb/daftar');
        }

        $model = new PpsbRegistration();
        $registration = $model->findByRegNo($regNo);

        if (!$registration) {
            add_flash('Data pendaftaran tidak ditemukan.', 'error');
            $this->redirect('/ppsb/daftar');
        }

        $data = [
            'title' => 'Registrasi Sukses — PPSB',
            'registration' => $registration,
            'user' => $_SESSION['nama'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/ppsb_success', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Admin Registrations Index
     */
    public function adminIndex() {
        require_admin();

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;

        $limit = 10;
        $offset = ($page - 1) * $limit;

        $q = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? '';

        $model = new PpsbRegistration();
        $filters = ['q' => $q, 'status' => $status];
        
        $registrations = $model->getAll($filters, $limit, $offset);
        $totalItems = $model->countAll($filters);
        $totalPages = ceil($totalItems / $limit);

        // Need classes list for enrollment modal
        $studentModel = new Student();
        $kelas = $studentModel->getKelasList();
        
        // Suggest next NIS
        $nextNis = $studentModel->generateNextNis($this->currentYear['name'] ?? null);

        $data = [
            'title' => 'Master Pendaftaran PPSB',
            'registrations' => $registrations,
            'kelas' => $kelas,
            'nextNis' => $nextNis,
            'q' => $q,
            'selected_status' => $status,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'admin',
            'count_pending' => $model->countAll(['status' => 'Pending']),
            'count_passed' => $model->countAll(['status' => 'Passed']),
            'count_enrolled' => $model->countAll(['status' => 'Enrolled']),
            'count_failed' => $model->countAll(['status' => 'Failed'])
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/ppsb_admin', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Admin Update Status (Passed/Failed)
     */
    public function updateStatus() {
        require_admin();

        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$id || !in_array($status, ['Passed', 'Failed', 'Pending'])) {
            add_flash('Aksi tidak valid.', 'error');
            $this->redirect('/admin/ppsb');
        }

        $model = new PpsbRegistration();
        try {
            $model->update($id, ['status' => $status]);
            add_flash("Status pendaftar berhasil diperbarui menjadi '$status'.", 'success');
        } catch (\Exception $e) {
            add_flash('Gagal memperbarui status: ' . $e->getMessage(), 'error');
        }
        $this->redirect('/admin/ppsb');
    }

    /**
     * Admin Place/Enroll approved registration into Class
     */
    public function enroll() {
        require_admin();

        $id = $_POST['id'] ?? null;
        $kelasId = $_POST['kelas_id'] ?? null;
        $nis = $_POST['nis'] ?? null;

        if (!$id || !$kelasId || !$nis) {
            add_flash('Data penempatan kelas tidak lengkap.', 'error');
            $this->redirect('/admin/ppsb');
        }

        $model = new PpsbRegistration();
        $reg = $model->find($id);

        if (!$reg) {
            add_flash('Data pendaftar tidak ditemukan.', 'error');
            $this->redirect('/admin/ppsb');
        }

        if ($reg['status'] !== 'Passed') {
            add_flash('Hanya pendaftar yang LULUS yang dapat ditempatkan di kelas.', 'error');
            $this->redirect('/admin/ppsb');
        }

        $studentModel = new Student();
        
        // Start transaction
        $studentModel->beginTransaction();
        try {
            // Check if NIS already exists
            $existing = $studentModel->findByNis($nis);
            if ($existing) {
                throw new \Exception("Nomor Induk Santri (NIS) '$nis' sudah terdaftar.");
            }

            // Create Student Record
            $studentData = [
                'nis' => $nis,
                'nama' => $reg['nama'],
                'gender' => $reg['gender'],
                'tempat_lahir' => $reg['tempat_lahir'],
                'tanggal_lahir' => $reg['tanggal_lahir'],
                'nama_wali' => $reg['nama_wali'],
                'alamat' => $reg['alamat'],
                'tahun_masuk' => date('Y'),
                'kelas_id' => $kelasId // Passed to trigger enroll in Model
            ];
            
            $studentId = $studentModel->create($studentData);

            // Update registration with student_id and set status to Enrolled
            $model->update($id, [
                'status' => 'Enrolled',
                'student_id' => $studentId
            ]);

            $studentModel->commit();
            add_flash("Santri '" . $reg['nama'] . "' berhasil ditempatkan di kelas.", 'success');
        } catch (\Exception $e) {
            $studentModel->rollBack();
            add_flash('Gagal menempatkan kelas: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/admin/ppsb');
    }

    /**
     * Admin Delete Registration
     */
    public function delete() {
        require_admin();
        $id = $_GET['id'] ?? null;

        $model = new PpsbRegistration();
        try {
            $model->delete($id);
            add_flash('Data pendaftaran berhasil dihapus.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menghapus pendaftaran: ' . $e->getMessage(), 'error');
        }
        $this->redirect('/admin/ppsb');
    }
}
