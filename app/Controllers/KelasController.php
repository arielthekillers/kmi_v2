<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\KelasModel;

class KelasController extends Controller {
    protected $kelasModel;

    public function __construct() {
        parent::__construct();
        $this->kelasModel = new KelasModel();
    }


    public function index() {
        require_admin();
        
        $db = \App\Core\Database::getInstance();
        $teachers = $db->query("SELECT id, nama FROM users WHERE role = 'pengajar' ORDER BY nama ASC")->fetchAll();
        $groupedKelas = $this->kelasModel->getAllGrouped();
        
        renderHeader("Master Kelas");
        $this->view('kelas/index', [
            'groupedKelas' => $groupedKelas,
            'teachers' => $teachers
        ]);
        renderFooter();
    }

    public function store() {
        require_admin();
        csrf_validate_token();

        $id = $_POST['id'] ?? '';
        $data = [
            'tingkat' => htmlspecialchars($_POST['tingkat'] ?? ''),
            'abjad' => htmlspecialchars($_POST['abjad'] ?? ''),
            'location' => htmlspecialchars($_POST['location'] ?? ''),
            'teacher_id' => $_POST['teacher_id'] ?? null
        ];

        try {
            if (!empty($id)) {
                $this->kelasModel->update($id, $data);
                $msg = 'Data kelas berhasil diperbarui.';
            } else {
                $this->kelasModel->create($data);
                $msg = 'Kelas baru berhasil ditambahkan.';
            }
            add_flash($msg, 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan data kelas: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/classes');
    }

    public function detail() {
        require_admin();
        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/classes');

        $kelas = $this->kelasModel->find($id);
        if (!$kelas) {
            add_flash('Kelas tidak ditemukan.', 'error');
            $this->redirect('/classes');
        }

        $tab = $_GET['tab'] ?? 'overview';
        $data = [
            'title' => "Detail Kelas " . $kelas['tingkat'] . "-" . $kelas['abjad'],
            'kelas' => $kelas,
            'tab' => $tab,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $_SESSION['role'] ?? 'user'
        ];

        // Fetch Tab-specific data
        if ($tab === 'santri') {
            $data['students'] = $this->kelasModel->getStudentsWithDetails($id);
        } elseif ($tab === 'jadwal') {
            $data['schedule'] = $this->kelasModel->getScheduleWithDetails($id);
        }

        renderHeader($data['title']);
        $this->view('kelas/detail', $data);
        renderFooter();
    }

    public function delete() {
        require_admin();
        
        $id = $_GET['id'] ?? '';
        if (!empty($id)) {
            try {
                $this->kelasModel->delete($id);
                add_flash('Data kelas berhasil dihapus.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal menghapus data kelas: ' . $e->getMessage(), 'error');
            }
        }
        
        $this->redirect('/classes');
    }
}
