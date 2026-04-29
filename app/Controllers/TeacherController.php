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
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;

        if (!empty($search)) {
            $totalData = $this->teacherModel->countSearch($search);
            $offset = ($page - 1) * $limit;
            $displayPengajar = $this->teacherModel->search($search, $limit, $offset);
        } else {
            $allTeachers = $this->teacherModel->getAll();
            $totalData = count($allTeachers);
            $offset = ($page - 1) * $limit;
            $displayPengajar = array_slice($allTeachers, $offset, $limit);
        }

        $totalPages = ceil($totalData / $limit);
        $page = max(1, min($page, $totalPages));

        $data = [
            'title' => 'Data Pengajar',
            'displayPengajar' => $displayPengajar,
            'totalData' => $totalData,
            'totalPages' => $totalPages,
            'page' => $page,
            'offset' => $offset,
            'perPage' => $limit,
            'q' => $search,
            'is_searching' => !empty($search),
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
                add_flash('Data pengajar berhasil ditambahkan.', 'success');
            } else {
                $this->teacherModel->update($id, $data);
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
                $this->teacherModel->delete($id);
                add_flash('Data pengajar berhasil dipindahkan ke tempat sampah.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal menghapus data pengajar: ' . $e->getMessage(), 'error');
            }
        }
        $this->redirect('/teachers/trash');
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

            // Save new hashed + plain password
            $upd = $db->prepare("UPDATE users SET password = ?, password_plain = ? WHERE id = ?");
            $upd->execute([$hashedPassword, $newPassword, $id]);

            // Build WA link
            $hp = preg_replace('/[^0-9]/', '', $teacher['hp'] ?? '');
            if ($hp && substr($hp, 0, 1) === '0') $hp = '62' . substr($hp, 1);

            require_once __DIR__ . '/../../helpers/utilities.php';
            $loginUrl = url('/login');
            $waMsg  = "Assalamu'alaikum Wr. Wb.\n\nBerikut akun antum untuk login di KMI App:\n\n";
            $waMsg .= "Username: " . ($teacher['hp'] ?? '-') . "\n";
            $waMsg .= "Password: " . $newPassword . "\n\n";
            $waMsg .= "Link Login: " . $loginUrl . "\n\n";
            $waMsg .= "Mohon dijaga kerahasiaannya.\n\nSyukron";
            $waLink = $hp ? "https://wa.me/{$hp}?text=" . rawurlencode($waMsg) : null;

            // Session already started by auth helpers — just write to it
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['reset_result'] = [
                'nama'     => $teacher['nama'],
                'hp'       => $teacher['hp'] ?? '-',
                'password' => $newPassword,
                'wa_link'  => $waLink
            ];

            add_flash('Password ' . $teacher['nama'] . ' berhasil direset.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal reset password: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/teachers');
    }
}
