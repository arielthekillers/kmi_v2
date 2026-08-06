<?php

namespace App\Modules\Students\Controllers;

use App\Core\Controller;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\PpsbRegistration;

class PpsbController extends Controller {
    public function __construct() {
        parent::__construct();
    }

    private function calculateCompleteness($reg) {
        $fieldsToCheck = [
            'registration_no', 'nama', 'gender', 'tempat_lahir', 'tanggal_lahir', 
            'alamat', 'nama_wali', 'no_hp_wali', 'nik', 'nisn', 'provinsi', 
            'kabupaten', 'kecamatan', 'kelurahan', 'rt_rw', 'kode_pos', 
            'nama_kk', 'pekerjaan_ayah', 'no_hp_ayah', 'nama_ibu', 'pekerjaan_ibu', 'no_hp_ibu'
        ];
        
        $filled = 0;
        $total = count($fieldsToCheck);
        foreach ($fieldsToCheck as $f) {
            if (!empty($reg[$f]) && $reg[$f] !== '-' && $reg[$f] !== 'null') {
                $filled++;
            }
        }
        return round(($filled / $total) * 100);
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
        $sort = $_GET['sort'] ?? 'created_at';
        $dir = $_GET['dir'] ?? 'desc';

        $model = new PpsbRegistration();
        $filters = ['q' => $q, 'status' => $status, 'sort' => $sort, 'dir' => $dir];
        
        $registrations = $model->getAll($filters, $limit, $offset);
        
        foreach ($registrations as &$reg) {
            $reg['completeness'] = $this->calculateCompleteness($reg);
        }
        
        $totalItems = $model->countAll($filters);
        $totalPages = ceil($totalItems / $limit);

        // Determine next academic year for placement
        $yearModel = new \App\Models\AcademicYearModel();
        $allYears = $yearModel->getAll();
        $nextYear = null;
        foreach (array_reverse($allYears) as $y) {
            if ($y['name'] > ($this->currentYear['name'] ?? '')) {
                $nextYear = $y;
                break;
            }
        }
        if (!$nextYear) {
            $nextYear = $this->currentYear; // fallback to current if next doesn't exist
        }

        // Need classes list for enrollment modal for the next year
        $studentModel = new Student();
        $kelas = $studentModel->getKelasByYear($nextYear['id'] ?? null);
        
        // Suggest next NIS
        $nextNis = $studentModel->generateNextNis($nextYear['name'] ?? null);

        $data = [
            'title' => 'Master Pendaftaran PPSB',
            'registrations' => $registrations,
            'kelas' => $kelas,
            'nextNis' => $nextNis,
            'targetYear' => $nextYear,
            'q' => $q,
            'selected_status' => $status,
            'sort' => $sort,
            'dir' => $dir,
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
     * Admin Statistics Page
     */
    public function statistics() {
        require_admin();

        $model = new PpsbRegistration();
        $all = $model->getAll([], 100000);
        
        $stats = [
            'total' => count($all),
            'laki_laki' => 0,
            'perempuan' => 0,
            'usia_tertua_l' => null,
            'usia_termuda_l' => null,
            'usia_tertua_p' => null,
            'usia_termuda_p' => null,
            'completeness_25' => 0,
            'completeness_50' => 0,
            'completeness_75' => 0,
            'completeness_100' => 0,
            'kabupaten_terbanyak' => []
        ];

        $today = new \DateTime();
        $usia_l = [];
        $usia_p = [];
        $kabupatenCounts = [];

        foreach ($all as $r) {
            if ($r['gender'] === 'L') {
                $stats['laki_laki']++;
                if (!empty($r['tanggal_lahir'])) {
                    try { $usia_l[] = $today->diff(new \DateTime($r['tanggal_lahir']))->y; } catch (\Exception $e) {}
                }
            } else {
                $stats['perempuan']++;
                if (!empty($r['tanggal_lahir'])) {
                    try { $usia_p[] = $today->diff(new \DateTime($r['tanggal_lahir']))->y; } catch (\Exception $e) {}
                }
            }

            $comp = $this->calculateCompleteness($r);
            if ($comp <= 25) $stats['completeness_25']++;
            elseif ($comp <= 50) $stats['completeness_50']++;
            elseif ($comp <= 75) $stats['completeness_75']++;
            else $stats['completeness_100']++;

            if (!empty($r['kabupaten'])) {
                $kab = strtoupper(trim($r['kabupaten']));
                if (!isset($kabupatenCounts[$kab])) $kabupatenCounts[$kab] = 0;
                $kabupatenCounts[$kab]++;
            }
        }

        if (!empty($usia_l)) {
            $stats['usia_tertua_l'] = max($usia_l);
            $stats['usia_termuda_l'] = min($usia_l);
        }
        if (!empty($usia_p)) {
            $stats['usia_tertua_p'] = max($usia_p);
            $stats['usia_termuda_p'] = min($usia_p);
        }

        arsort($kabupatenCounts);
        $stats['kabupaten_terbanyak'] = array_slice($kabupatenCounts, 0, 6, true);

        $data = [
            'title' => 'Statistik Pendaftaran (PPSB)',
            'stats' => $stats,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'admin',
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/ppsb_statistics', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Admin Edit Registration
     */
    public function edit() {
        require_admin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            add_flash('Data pendaftaran tidak valid.', 'error');
            $this->redirect('/admin/ppsb');
        }
        
        $model = new PpsbRegistration();
        $registration = $model->find($id);
        if (!$registration) {
            add_flash('Data pendaftaran tidak ditemukan.', 'error');
            $this->redirect('/admin/ppsb');
        }

        $data = [
            'title' => 'Lengkapi Data PPSB',
            'registration' => $registration,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'admin',
            'return_url' => $_GET['return'] ?? '/admin/ppsb'
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/ppsb_edit', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Admin Update Registration Data
     */
    public function updateData() {
        require_admin();
        $id = $_POST['id'] ?? null;
        $returnUrl = $_POST['return_url'] ?? '/admin/ppsb';

        if (!$id) {
            add_flash('ID Pendaftaran tidak valid.', 'error');
            $this->redirect($returnUrl);
        }

        $data = $_POST;
        unset($data['id']);
        unset($data['return_url']);
        unset($data['csrf_token']);

        $model = new PpsbRegistration();
        try {
            $model->update($id, $data);
            add_flash('Data pendaftaran berhasil diperbarui.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal memperbarui data: ' . $e->getMessage(), 'error');
        }
        
        // redirect is smart enough to handle paths like /admin/ppsb?page=3
        $this->redirect($returnUrl);
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
     * Admin Bulk Action (Passed, Failed, Delete, Pending)
     */
    public function bulkAction() {
        require_admin();
        $action = $_POST['action'] ?? null;
        $ids = $_POST['selected_ids'] ?? [];
        $returnUrl = $_POST['return_url'] ?? '/admin/ppsb';

        if (empty($ids) || !$action) {
            add_flash('Tidak ada data atau aksi yang dipilih.', 'error');
            $this->redirect($returnUrl);
        }

        $model = new PpsbRegistration();
        $success = 0;
        foreach ($ids as $id) {
            try {
                if ($action === 'ForceDelete') {
                    $model->forceDelete($id);
                    $success++;
                } elseif ($action === 'Delete') {
                    $model->delete($id);
                    $success++;
                } elseif (in_array($action, ['Passed', 'Failed', 'Pending'])) {
                    $model->update($id, ['status' => $action]);
                    $success++;
                }
            } catch (\Exception $e) {
                // skip failed updates silently in bulk
            }
        }

        add_flash("Berhasil memproses $success data pendaftar.", 'success');
        $this->redirect($returnUrl);
    }

    /**
     * Admin Place/Enroll approved registration into Class
     */
    public function enroll() {
        require_admin();

        $id = $_POST['id'] ?? null;
        $kelasId = $_POST['kelas_id'] ?? null;
        $nis = $_POST['nis'] ?? null;
        $returnUrl = $_POST['return_url'] ?? '/admin/ppsb';

        if (!$id || !$kelasId || !$nis) {
            add_flash('Data penempatan kelas tidak lengkap.', 'error');
            $this->redirect($returnUrl);
        }

        $model = new PpsbRegistration();
        $reg = $model->find($id);

        if (!$reg) {
            add_flash('Data pendaftar tidak ditemukan.', 'error');
            $this->redirect($returnUrl);
        }

        if ($reg['status'] !== 'Passed') {
            add_flash('Hanya pendaftar yang LULUS yang dapat ditempatkan di kelas.', 'error');
            $this->redirect($returnUrl);
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
                'nama' => $reg['nama'] ?? null,
                'nik' => $reg['nik'] ?? null,
                'nisn' => $reg['nisn'] ?? null,
                'gender' => $reg['gender'] ?? null,
                'tempat_lahir' => $reg['tempat_lahir'] ?? null,
                'tanggal_lahir' => $reg['tanggal_lahir'] ?? null,
                'alamat' => $reg['alamat'] ?? null,
                'rt_rw' => $reg['rt_rw'] ?? null,
                'kelurahan' => $reg['kelurahan'] ?? null,
                'desa_id' => $reg['desa_id'] ?? null,
                'kecamatan' => $reg['kecamatan'] ?? null,
                'kec_id' => $reg['kec_id'] ?? null,
                'kabupaten' => $reg['kabupaten'] ?? null,
                'kab_id' => $reg['kab_id'] ?? null,
                'provinsi' => $reg['provinsi'] ?? null,
                'prov_id' => $reg['prov_id'] ?? null,
                'kode_pos' => $reg['kode_pos'] ?? null,
                'nama_kk' => $reg['nama_kk'] ?? null,
                'nama_wali' => $reg['nama_wali'] ?? null,
                'pekerjaan_ayah' => $reg['pekerjaan_ayah'] ?? null,
                'no_hp_ayah' => $reg['no_hp_ayah'] ?? null,
                'nama_ibu' => $reg['nama_ibu'] ?? null,
                'pekerjaan_ibu' => $reg['pekerjaan_ibu'] ?? null,
                'no_hp_ibu' => $reg['no_hp_ibu'] ?? null,
                'tahun_masuk' => date('Y'),
                'kelas_id' => $kelasId, // Passed to trigger enroll in Model
                'academic_year_id' => $_POST['academic_year_id'] ?? null
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

        $this->redirect($returnUrl);
    }

    /**
     * Admin Cancel Enrollment
     */
    public function cancelEnroll() {
        require_admin();

        $id = $_POST['id'] ?? null;
        $returnUrl = $_POST['return_url'] ?? '/admin/ppsb';

        if (!$id) {
            add_flash('Data tidak ditemukan.', 'error');
            $this->redirect($returnUrl);
        }

        $model = new PpsbRegistration();
        $reg = $model->find($id);

        if (!$reg || $reg['status'] !== 'Enrolled' || empty($reg['student_id'])) {
            add_flash('Data pendaftar tidak valid atau belum ditempatkan di kelas.', 'error');
            $this->redirect($returnUrl);
        }

        try {
            $studentModel = new Student();
            $studentModel->delete($reg['student_id']);

            $model->update($id, [
                'status' => 'Passed',
                'student_id' => null
            ]);

            add_flash('Penempatan kelas berhasil dibatalkan. Data santri yang terkait telah dihapus.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal membatalkan penempatan kelas: ' . $e->getMessage(), 'error');
        }

        $this->redirect($returnUrl);
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

    /**
     * Import CSV file
     */
    public function importCsv() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                add_flash('Gagal mengunggah file.', 'error');
                $this->redirect('/admin/ppsb');
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'csv') {
                add_flash('Format file tidak didukung. Harap unggah file CSV.', 'error');
                $this->redirect('/admin/ppsb');
            }

            if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
                $headerFound = false;
                $header = null;
                
                // Scan up to 10 rows to find the main header
                for ($i = 0; $i < 10; $i++) {
                    $row = fgetcsv($handle, 10000, ",");
                    if ($row === FALSE) break;
                    
                    if (count($row) === 1 && strpos($row[0], ';') !== false) {
                        $row = explode(';', $row[0]);
                    }
                    
                    $col1 = strtoupper(trim($row[1] ?? ''));
                    $col2 = strtoupper(trim($row[2] ?? ''));
                    $col11 = strtoupper(trim($row[11] ?? ''));
                    
                    if (($col1 === 'NO. INDUK' || $col1 === 'NO. STANBUK') && $col2 === 'NAMA' && $col11 === 'HP AYAH/IBU') {
                        $headerFound = true;
                        $header = $row;
                        break;
                    }
                }

                if (!$headerFound) {
                    fclose($handle);
                    add_flash('Format CSV tidak sesuai! Pastikan file memiliki kolom NO. INDUK (atau NO. STANBUK), NAMA, dan HP AYAH/IBU pada susunan yang benar.', 'error');
                    $this->redirect('/admin/ppsb');
                    return; // Prevent further execution
                }

                // Skip the sub-header row (e.g., AYAH, IBU)
                fgetcsv($handle, 10000, ",");

                $ppsbModel = new PpsbRegistration();
                
                $inserted = 0;
                $updated = 0;

                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    if (count($data) === 1 && strpos($data[0], ';') !== false) {
                        // Fallback to semicolon if Excel exports it that way
                        $data = explode(';', $data[0]);
                    }

                    if (empty($data[1]) || trim($data[1]) === '') {
                        continue;
                    }

                    $nis = trim($data[1]);
                    
                    // Split HP
                    $hp = trim($data[11] ?? '');
                    $hpAyah = $hp;
                    $hpIbu = '';
                    if (strpos($hp, '/') !== false) {
                        $parts = explode('/', $hp);
                        $hpAyah = trim($parts[0]);
                        $hpIbu = trim($parts[1] ?? '');
                    } else if (!empty($hp)) {
                        $hpIbu = $hpAyah; // Jika hanya 1 nomor, isi untuk keduanya
                    }

                    // Format Date
                    $tanggalLahir = trim($data[6] ?? '');
                    if ($tanggalLahir && strpos($tanggalLahir, '-') !== false) {
                        $dateParts = explode('-', $tanggalLahir);
                        if (count($dateParts) === 3) {
                            $tanggalLahir = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                        }
                    } else {
                        $tanggalLahir = null;
                    }

                    $ppsbData = [
                        'registration_no' => $nis,
                        'nama' => trim($data[2] ?? ''),
                        'nisn' => trim($data[3] ?? '') !== 'null' ? trim($data[3] ?? '') : null,
                        'nik' => trim($data[4] ?? '') !== '0' ? trim($data[4] ?? '') : null,
                        'tempat_lahir' => trim($data[5] ?? ''),
                        'tanggal_lahir' => $tanggalLahir,
                        'gender' => trim($data[7] ?? '') === 'L' ? 'L' : 'P',
                        'alamat' => trim($data[12] ?? ''),
                        'rt_rw' => trim($data[13] ?? '') . '/' . trim($data[14] ?? ''),
                        'kelurahan' => trim($data[15] ?? ''),
                        'kecamatan' => trim($data[16] ?? ''),
                        'kabupaten' => trim($data[17] ?? ''),
                        'provinsi' => trim($data[18] ?? ''),
                        'nama_wali' => trim($data[22] ?? ''), // NAMA AYAH
                        'pekerjaan_ayah' => trim($data[28] ?? ''), // PEKERJAAN AYAH
                        'no_hp_ayah' => $hpAyah,
                        'nama_ibu' => trim($data[23] ?? ''), // NAMA IBU
                        'pekerjaan_ibu' => trim($data[29] ?? ''), // PEKERJAAN IBU
                        'no_hp_ibu' => $hpIbu,
                        'no_hp_wali' => $hpAyah ?: $hpIbu, // Fallback
                        'status' => 'Pending'
                    ];
                    
                    foreach ($ppsbData as $k => $v) {
                        if ($v === 'null' || $v === 'Invalid date') {
                            // Khusus tanggal_lahir harus beneran NULL kalau kosong/invalid
                            if ($k === 'tanggal_lahir') {
                                $ppsbData[$k] = null;
                            } else {
                                $ppsbData[$k] = '';
                            }
                        }
                    }

                    try {
                        $existing = $ppsbModel->findByRegNoWithTrashed($nis);

                        if ($existing) {
                            // Pertahankan status yang lama agar tidak revert ke Pending secara paksa jika sudah Passed/Enrolled
                            unset($ppsbData['status']);
                            
                            // Restore record jika sebelumnya dihapus (soft delete)
                            $ppsbData['deleted_at'] = null;
                            
                            $ppsbModel->update($existing['id'], $ppsbData);
                            $updated++;
                        } else {
                            $ppsbModel->create($ppsbData);
                            $inserted++;
                        }
                    } catch (\Exception $e) {
                        // skip on error
                    }
                }
                fclose($handle);

                add_flash("Import CSV Selesai! $inserted data pendaftar baru ditambahkan, $updated data diperbarui.", 'success');
            } else {
                add_flash('Gagal membaca file CSV.', 'error');
            }
        }
        
        $this->redirect('/admin/ppsb');
    }
}
