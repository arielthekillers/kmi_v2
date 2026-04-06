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

        $data = [
            'title' => 'Data Kelas',
            'classes' => Kelas::all(),
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
                'jumlah_murid' => $_POST['jumlah_murid'] ?? 0
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
