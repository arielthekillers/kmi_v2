<?php

namespace App\Modules\Subjects\Controllers;

use App\Core\Controller;
use App\Modules\Subjects\Models\Subject;

class SubjectController extends Controller {

    public function index() {
        // Ensure login
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $data = [
            'title' => 'Data Pelajaran',
            'subjects' => Subject::all(),
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        $this->view('layouts/header', $data);
        $this->view('Subjects/Views/index', $data);
        $this->view('layouts/footer', $data);
    }

    public function store() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Subject::create([
                'nama' => $_POST['name']
            ]);
            // Flash message? (Simple for now)
            $this->redirect('/subjects');
        }
    }

    public function delete() {
        session_start();
        if (isset($_GET['id'])) {
            Subject::delete($_GET['id']);
        }
        $this->redirect('/subjects');
    }
}
