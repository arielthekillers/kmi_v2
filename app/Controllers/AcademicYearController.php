<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AcademicYearModel;

class AcademicYearController extends Controller {
    protected $yearModel;

    public function __construct() {
        parent::__construct();
        $this->yearModel = new AcademicYearModel();
    }

    public function index() {
        require_admin();
        $years = $this->yearModel->getAll();
        
        renderHeader("Manajemen Tahun Ajaran");
        $this->view('academic_years/index', [
            'years' => $years
        ]);
        renderFooter();
    }

    public function store() {
        require_admin();
        csrf_validate_token();
        
        $name = $_POST['name'] ?? '';
        if (!empty($name)) {
            $this->yearModel->create($name);
            add_flash('Tahun ajaran berhasil ditambahkan.', 'success');
        }
        $this->redirect('/academic-years');
    }

    public function setActive() {
        require_admin();
        csrf_validate_token();
        
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            if ($this->yearModel->setActive($id)) {
                add_flash('Tahun ajaran aktif berhasil diubah.', 'success');
            } else {
                add_flash('Gagal mengubah tahun ajaran aktif.', 'error');
            }
        }
        $this->redirect('/academic-years');
    }
}
