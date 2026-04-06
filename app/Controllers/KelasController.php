<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\KelasModel;

class KelasController extends Controller {
    protected $kelasModel;

    public function __construct() {
        $this->kelasModel = new KelasModel();
    }

    public function index() {
        // Auth check (using helper)
        require_admin();
        
        $groupedKelas = $this->kelasModel->getAllGrouped();
        
        // Render common header manually or via Layout logic?
        // For now, let's assume we use the existing global helpers in the view or layout
        // But Controller::view should header/footer if configured
        
        // We'll wrap the view call with header/footer in the view file OR
        // Controller::view calls can just include the content, and we assume layout is handled.
        // Let's stick to the current pattern: calling renderHeader/Footer inside the view (or around it).
        // BUT, better MVC approach is Layouts.
        // For this quick refactor, I'll pass the view content to a layout OR ensure the view calls helpers.
        // My extracted view relies on renderHeader being called BEFORE it? No, it relies on it being called.
        // I will make a layout wrapper in Controller::view later.
        // For now, let's emit header/footer in the Controller or View.
        
        // Let's update Controller.php to support layouts or just call helpers in the view.
        // The simplest path right now:
        renderHeader("Master Kelas");
        $this->view('kelas/index', ['groupedKelas' => $groupedKelas]);
        renderFooter();
    }

    public function store() {
        require_admin();
        csrf_validate_token();

        $id = $_POST['id'] ?? '';
        $data = [
            'tingkat' => htmlspecialchars($_POST['tingkat'] ?? ''),
            'abjad' => htmlspecialchars($_POST['abjad'] ?? ''),
            'jumlah_murid' => (int)($_POST['jumlah_murid'] ?? 0)
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
