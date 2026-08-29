<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TeacherModel;

class TeacherController extends Controller {
    protected $teacherModel;

    public function __construct() {
        parent::__construct();
        $this->teacherModel = new TeacherModel();
    }


    public function index() {
        require_admin();

        $search = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? 'Active';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;

        if (!empty($search)) {
            $totalData = $this->teacherModel->countSearch($search, $status);
            $offset = ($page - 1) * $limit;
            $displayPengajar = $this->teacherModel->search($search, $limit, $offset, $status);
        } else {
            $allTeachers = $this->teacherModel->getAll($status);
            $totalData = count($allTeachers);
            $offset = ($page - 1) * $limit;
            $displayPengajar = array_slice($allTeachers, $offset, $limit);
        }

        $totalPages = ceil($totalData / $limit);
        $page = max(1, min($page, max(1, $totalPages)));

        $data = [
            'title' => 'Data Pengajar',
            'displayPengajar' => $displayPengajar,
            'totalData' => $totalData,
            'totalPages' => $totalPages,
            'page' => $page,
            'offset' => $offset,
            'perPage' => $limit,
            'q' => $search,
            'status' => $status,
            'is_searching' => true,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('teachers/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function store() {
        require_admin();
        csrf_validate_token();

        $id = $_POST['id'] ?? '';
        $isNew = empty($id);
        
        $nama = htmlspecialchars($_POST['nama'] ?? '');
        $hp = htmlspecialchars($_POST['hp'] ?? '');
        // username set to HP for now, clean it
        $username = preg_replace('/[^0-9]/', '', $hp);
        if ($username && substr($username, 0, 1) === '0') {
            $username = '62' . substr($username, 1);
        }
        if (empty($username)) {
            // fallback if no HP, use random or name?
            // Legacy uses HP as username. If empty, maybe error?
            // Let's use name lowercased + random if HP is empty to ensure uniqueness
            if (empty($hp)) {
                $username = strtolower(str_replace(' ', '', $nama)) . rand(100, 999);
            }
        }

        // Check uniqueness of HP/username
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $checkId = $isNew ? 0 : $id;
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $checkId]);
            if ($stmt->fetch()) {
                add_flash('Nomor HP / Username sudah terdaftar pada pengguna lain.', 'error');
                $this->redirect('/teachers');
            }
        } catch (\Exception $e) {
            // Ignore and let it be caught during insert/update
        }
        
        $passwordInput = $_POST['password'] ?? '';
        $passwordHash = null;
        $passwordPlain = null;
        
        if ($isNew) {
            if (empty($passwordInput)) {
                // Generate random
                $passwordPlain = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            } else {
                $passwordPlain = $passwordInput;
            }
            $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);
        } else {
            // Update
            if (!empty($passwordInput)) {
                $passwordPlain = $passwordInput;
                $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);
            }
        }

        $data = [
            'nama' => $nama,
            'hp' => $hp,
            'username' => $username,
            'password' => $passwordHash,
            'password_plain' => $passwordPlain
        ];

        try {
            if ($isNew) {
                // Check if username exists?
                // Model handle insert?
                $this->teacherModel->create($data);
                log_activity("Menambahkan data pengajar baru: {$nama} (Username: {$username})");
                add_flash('Data pengajar berhasil ditambahkan.', 'success');
            } else {
                $this->teacherModel->update($id, $data);
                log_activity("Memperbarui data pengajar: {$nama} (ID: {$id}, Username: {$username})");
                add_flash('Data pengajar berhasil diperbarui.', 'success');
            }
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan data pengajar: ' . $e->getMessage(), 'error');
        }

        if (!$isNew && !empty($_POST['redirect_to'])) {
            $this->redirect($_POST['redirect_to']);
        } elseif (!$isNew && !empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        $this->redirect('/teachers');
    }

    public function delete() {
        require_admin();
        $id = $_GET['id'] ?? '';
        if (!empty($id)) {
            try {
                \App\Modules\Auth\Models\User::updateStatus($id, 0);
                log_activity("Menonaktifkan data pengajar (ID: {$id})");
                add_flash('Data pengajar berhasil dinonaktifkan.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal menonaktifkan data pengajar: ' . $e->getMessage(), 'error');
            }
        }
        $this->redirect('/teachers?status=Active');
    }

    public function toggleStatus() {
        require_admin();
        $id = $_GET['id'] ?? null;
        $status = isset($_GET['status']) ? (int)$_GET['status'] : 0;
        
        if ($id !== null) {
            try {
                \App\Modules\Auth\Models\User::updateStatus($id, $status);
                $statusText = $status === 1 ? "Mengaktifkan kembali" : "Menonaktifkan";
                log_activity("{$statusText} data pengajar (ID: {$id})");
                $msg = $status === 1 ? 'Data pengajar berhasil diaktifkan kembali.' : 'Data pengajar berhasil dinonaktifkan.';
                add_flash($msg, 'success');
            } catch (\Exception $e) {
                add_flash('Gagal mengubah status pengajar: ' . $e->getMessage(), 'error');
            }
        }
        
        $this->redirect('/teachers?status=' . ($status === 1 ? 'Inactive' : 'Active'));
    }

    public function trash() {
        require_admin();

        $search = $_GET['q'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        
        $offset = ($page - 1) * $limit;
        $totalData = $this->teacherModel->countTrash($search);
        $displayPengajar = $this->teacherModel->getTrash($search, $limit, $offset);

        $totalPages = ceil($totalData / $limit);
        $page = max(1, min($page, max(1, $totalPages)));

        $data = [
            'title' => 'Tempat Sampah Pengajar',
            'displayPengajar' => $displayPengajar,
            'totalData' => $totalData,
            'totalPages' => $totalPages,
            'page' => $page,
            'offset' => $offset,
            'perPage' => $limit,
            'q' => $search,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('teachers/trash', $data);
        $this->view('layouts/footer', $data);
    }

    public function restore() {
        require_admin();
        $id = $_GET['id'] ?? null;
        if (!empty($id)) {
            try {
                $this->teacherModel->restore($id);
                log_activity("Memulihkan data pengajar dari tempat sampah (ID: {$id})");
                add_flash('Data pengajar berhasil dipulihkan.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal memulihkan data pengajar: ' . $e->getMessage(), 'error');
            }
        }
        $this->redirect('/teachers/trash');
    }

    public function forceDelete() {
        require_admin();
        $id = $_GET['id'] ?? null;
        if (!empty($id)) {
            try {
                $this->teacherModel->forceDelete($id);
                log_activity("Menghapus secara permanen data pengajar (ID: {$id})");
                add_flash('Data pengajar berhasil dihapus permanen.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal menghapus data pengajar: ' . $e->getMessage(), 'error');
            }
        }
        $this->redirect('/teachers/trash');
    }

    public function resetPassword() {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/teachers');
        csrf_validate_token();

        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            add_flash('ID pengajar tidak ditemukan.', 'error');
            $this->redirect('/teachers');
        }

        // Generate random 6-digit password
        $newPassword = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            $db = \App\Core\Database::getInstance()->getConnection();

            // Fetch teacher info — hp stored in teacher_profiles.phone
            $stmt = $db->prepare("SELECT u.nama, tp.phone as hp FROM users u LEFT JOIN teacher_profiles tp ON u.id = tp.user_id WHERE u.id = ?");
            $stmt->execute([$id]);
            $teacher = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$teacher) {
                add_flash('Pengajar tidak ditemukan.', 'error');
                $this->redirect('/teachers');
            }

            // Clean and normalize phone number to get new username
            $hp = preg_replace('/[^0-9]/', '', $teacher['hp'] ?? '');
            if ($hp && substr($hp, 0, 1) === '0') $hp = '62' . substr($hp, 1);

            // Save new hashed + plain password, and sync username to match current phone number
            if ($hp) {
                $upd = $db->prepare("UPDATE users SET password = ?, password_plain = ?, username = ? WHERE id = ?");
                $upd->execute([$hashedPassword, $newPassword, $hp, $id]);
            } else {
                $upd = $db->prepare("UPDATE users SET password = ?, password_plain = ? WHERE id = ?");
                $upd->execute([$hashedPassword, $newPassword, $id]);
            }
            log_activity("Mereset password pengajar: {$teacher['nama']} (ID: {$id})");

            // Build WA link
            $hpWa = $hp;
            if (empty($hpWa)) {
                $hpWa = preg_replace('/[^0-9]/', '', $teacher['hp'] ?? '');
                if ($hpWa && substr($hpWa, 0, 1) === '0') $hpWa = '62' . substr($hpWa, 1);
            }

            require_once __DIR__ . '/../../helpers/utilities.php';
            $loginUrl = url('/login');
            $waMsg  = "Assalamu'alaikum Wr. Wb.\n\nBerikut akun antum untuk login di KMI App:\n\n";
            $waMsg .= "Username: " . ($hp ?: ($teacher['hp'] ?? '-')) . "\n";
            $waMsg .= "Password: " . $newPassword . "\n\n";
            $waMsg .= "Link Login: " . $loginUrl . "\n\n";
            $waMsg .= "Mohon dijaga kerahasiaannya.\n\nSyukron";
            $waLink = $hpWa ? "https://wa.me/{$hpWa}?text=" . rawurlencode($waMsg) : null;

            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['reset_result'] = [
                'nama'     => $teacher['nama'],
                'hp'       => $hp ?: ($teacher['hp'] ?? '-'),
                'password' => $newPassword,
                'wa_msg'   => $waMsg
            ];

            add_flash('Password ' . $teacher['nama'] . ' berhasil direset.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal reset password: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/teachers');
    }

    public function export() {
        require_admin();

        $fields = $_POST['export_fields'] ?? [];

        if (empty($fields)) {
            add_flash('Pilih minimal satu data untuk di-export.', 'error');
            $this->redirect('/teachers');
        }

        $status = $_POST['status'] ?? 'Active';
        $teachers = $this->teacherModel->getForExport($status);

        $allFieldsMap = [
            'nama' => 'Nama Lengkap',
            'nip' => 'NIP',
            'hp' => 'No. HP',
            'gender' => 'Jenis Kelamin',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'address' => 'Alamat',
            'education' => 'Pendidikan Terakhir',
            'year_graduated' => 'Tahun Lulus',
            'father_name' => 'Nama Ayah',
            'mother_name' => 'Nama Ibu'
        ];

        // Filter valid fields
        $selectedHeaders = [];
        foreach ($fields as $field) {
            if (isset($allFieldsMap[$field]) && $field !== 'all') {
                $selectedHeaders[$field] = $allFieldsMap[$field];
            }
        }

        $filename = "Data_Pengajar_" . date('Ymd_His') . ".xls";
        log_activity("Mengekspor data pengajar ke Excel (Status filter: {$status})");

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
        foreach ($teachers as $teacher) {
            $html .= '<tr>';
            foreach ($selectedHeaders as $key => $label) {
                $val = '';
                if ($key === 'gender') {
                    $val = $teacher['gender'] === 'Laki-laki' ? 'Laki-laki' : ($teacher['gender'] === 'Perempuan' ? 'Perempuan' : '');
                } else {
                    $val = $teacher[$key] ?? '';
                }
                
                // Fields that should be treated as text to prevent losing leading zeros or scientific notation
                $textFields = ['nip', 'hp'];
                
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

    public function history() {
        require_admin();

        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            add_flash('ID pengajar tidak ditemukan.', 'error');
            $this->redirect('/teachers');
        }

        $teacher = $this->teacherModel->getTeacherDetail($id);
        if (!$teacher) {
            add_flash('Pengajar tidak ditemukan.', 'error');
            $this->redirect('/teachers');
        }

        $history = $this->teacherModel->getTeachingHistory($id);

        // Group history by academic year
        $groupedHistory = [];
        foreach ($history as $h) {
            $groupedHistory[$h['academic_year_name']][] = $h;
        }

        $data = [
            'title' => 'Riwayat Mengajar: ' . $teacher['nama'],
            'teacher' => $teacher,
            'groupedHistory' => $groupedHistory,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('teachers/history', $data);
        $this->view('layouts/footer', $data);
    }

    public function shareCredentials() {
        require_admin();
        header('Content-Type: application/json');
        
        $hp = $_POST['hp'] ?? null;
        $message = $_POST['message'] ?? null;

        if (!$hp || !$message) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            return;
        }

        $hpNum = preg_replace('/[^0-9]/', '', $hp);
        if (substr($hpNum, 0, 1) === '0') $hpNum = '62' . substr($hpNum, 1);

        require_once __DIR__ . '/../../helpers/whatsapp.php';
        
        if (queue_whatsapp_message($hpNum, $message, 'System (Credential)')) {
            echo json_encode(['success' => true, 'message' => 'Pesan kredensial berhasil diproses.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memproses pesan kredensial.']);
        }
    }
}
