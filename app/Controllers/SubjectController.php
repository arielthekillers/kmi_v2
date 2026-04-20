<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SubjectModel;

class SubjectController extends Controller {
    protected $subjectModel;

    public function __construct() {
        parent::__construct();
        $this->subjectModel = new SubjectModel();
    }


    public function index() {
        require_admin();

        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;

        // Perform search/pagination logic
        // This logic is moved from view to Controller/Model
        if (!empty($search)) {
            $total = $this->subjectModel->countSearch($search);
            $offset = ($page - 1) * $limit;
            $displayPelajaran = $this->subjectModel->search($search, $limit, $offset);
        } else {
            // For now, if no search, getAll then paginate in PHP OR implemented getAll paginated in Model
            // Since SubjectModel::getAll returns all, let's paginate in PHP to keep it simple or implement paginate in Model.
            // Let's implement full pagination in controller using getAll
            // Actually, better to fetch all and slice array if dataset is small, or use SQL pagination.
            // Subjects are few (usually < 50), so fetching all is fine.
            
            $allSubjects = $this->subjectModel->getAll();
            
            // Filter in PHP if needed (though search is handled above)
            // If empty search, we just paginate all
            
            $total = count($allSubjects);
            $offset = ($page - 1) * $limit;
            $displayPelajaran = array_slice($allSubjects, $offset, $limit);
        }

        $totalPages = ceil($total / $limit);
        $page = max(1, min($page, $totalPages));
        
        // Pass data to view
        renderHeader("Master Pelajaran");
        $this->view('subjects/index', [
            'displayPelajaran' => $displayPelajaran,
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
            'offset' => $offset,
            'limit' => $limit,
            'search' => $search
        ]);
        renderFooter();
    }

    public function store() {
        require_admin();
        csrf_validate_token();

        $id = $_POST['id'] ?? '';
        $data = [
            'nama' => htmlspecialchars($_POST['nama'] ?? ''),
            'skala' => htmlspecialchars($_POST['skala'] ?? '80-30')
        ];

        try {
            if (!empty($id)) {
                $this->subjectModel->update($id, $data);
                $msg = 'Data pelajaran berhasil diperbarui.';
            } else {
                $this->subjectModel->create($data);
                $msg = 'Pelajaran baru berhasil ditambahkan.';
            }
            add_flash($msg, 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan data pelajaran: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/subjects');
    }

    public function delete() {
        require_admin();
        
        $id = $_GET['id'] ?? '';
        if (!empty($id)) {
            try {
                $this->subjectModel->delete($id);
                add_flash('Data pelajaran berhasil dihapus.', 'success');
            } catch (\Exception $e) {
                add_flash('Gagal menghapus data pelajaran: ' . $e->getMessage(), 'error');
            }
        }
        
        $this->redirect('/subjects');
    }
}
