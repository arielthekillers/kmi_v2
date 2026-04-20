<?php

namespace App\Modules\Classes\Controllers;

use App\Core\Controller;
use App\Modules\Classes\Models\Kelas;

class ClassController extends Controller {

    public function index() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $db = \App\Core\Database::getInstance();
        $teachers = $db->query("SELECT id, nama FROM users WHERE role = 'pengajar' ORDER BY nama ASC")->fetchAll();

        $data = [
            'title' => 'Data Kelas',
            'classes' => Kelas::all(),
            'teachers' => $teachers,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('Classes/Views/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function store() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Kelas::create([
                'tingkat' => $_POST['tingkat'],
                'abjad' => $_POST['abjad'],
                'gender' => $_POST['gender'],
                'location' => $_POST['location'] ?? null,
                'teacher_id' => $_POST['teacher_id'] ?? null
            ]);
            $this->redirect('/classes');
        }
    }

    public function delete() {
        session_start();
        if (isset($_GET['id'])) {
            Kelas::delete($_GET['id']);
        }
        $this->redirect('/classes');
    }
}
