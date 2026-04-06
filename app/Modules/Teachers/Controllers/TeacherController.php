<?php

namespace App\Modules\Teachers\Controllers;

use App\Core\Controller;
use App\Modules\Auth\Models\User;

class TeacherController extends Controller {

    public function index() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $teachers = User::all('guru');

        $data = [
            'title' => 'Data Pengajar',
            'teachers' => $teachers,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('Teachers/Views/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function store() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            User::create([
                'nama' => $_POST['nama'],
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'role' => 'guru'
            ]);
            $this->redirect('/teachers');
        }
    }

    public function delete() {
        session_start();
        if (isset($_GET['id'])) {
            User::delete($_GET['id']);
        }
        $this->redirect('/teachers');
    }
}
