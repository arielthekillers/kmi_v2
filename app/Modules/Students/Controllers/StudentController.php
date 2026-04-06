<?php

namespace App\Modules\Students\Controllers;

use App\Core\Controller;
use App\Modules\Students\Models\Student;

class StudentController extends Controller {

    public function index() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $model = new Student();
        $students = $model->getAll();
        
        $data = [
            'title' => 'Data Santri',
            'students' => $students,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function create() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $model = new Student();
        $kelas = $model->getKelasList();
        
        $data = [
            'title' => 'Tambah Data Santri',
            'kelas' => $kelas,
            'action' => 'store',
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('Students/Views/form', $data);
        $this->view('layouts/footer', $data);
    }

    public function store() {
        $data = $_POST;
        // Validation logic here...
        
        $model = new Student();
        try {
            $model->create($data);
            // Flash message?
            $this->redirect('/students');
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
