<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\TeacherModel;

class TeacherController extends Controller {
    protected $teacherModel;

    public function __construct() {
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

        renderHeader("Master Pengajar");
        $this->view('teachers/index', [
            'displayPengajar' => $displayPengajar,
            'totalData' => $totalData,
            'totalPages' => $totalPages,
            'page' => $page,
            'offset' => $offset,
            'perPage' => $limit,
            'search' => $search
        ]);
        renderFooter();
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

        $this->redirect('/teachers');
    }

    public function delete() {
        require_admin();
        $id = $_GET['id'] ?? '';
        if (!empty($id)) {
            try {
                $this->teacherModel->delete($id);
                add_flash('Data pengajar berhasil dihapus.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal menghapus data pengajar: ' . $e->getMessage(), 'error');
            }
        }
        $this->redirect('/teachers');
    }
}
