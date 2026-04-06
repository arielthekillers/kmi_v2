<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\GradeModel;
use App\Models\KelasModel;
use App\Models\SubjectModel;
use App\Models\TeacherModel;

class GradeController extends Controller {
    
    public function index() {
        require_login();
        
        $gradeModel = new GradeModel();
        $kelasModel = new KelasModel();
        $subjectModel = new SubjectModel();
        $teacherModel = new TeacherModel();

        // Data for Filters
        $kelas = $kelasModel->findAll();
        // Sort Kelas
         try {
            uasort($kelas, function ($a, $b) {
                $t = strnatcmp($a['tingkat'] ?? '', $b['tingkat'] ?? '');
                if ($t === 0) return strnatcmp($a['abjad'] ?? '', $b['abjad'] ?? '');
                return $t;
            });
        } catch (\Throwable $e) {}

        $allSubjects = $subjectModel->findAll();
        usort($allSubjects, function($a, $b) { return strnatcmp($a['nama'], $b['nama']); });
        
        $allTeachers = $teacherModel->findAll(); // Or filter logic
        $pengajar = [];
        foreach ($allTeachers as $t) {
             if (in_array($t['role'], ['pengajar', 'admin'])) {
                 $pengajar[] = $t;
             }
        }
        usort($pengajar, function($a, $b) { return strnatcmp($a['nama'], $b['nama']); });

        // Filter Params
        $filters = [
            'kelas' => $_GET['kelas'] ?? '',
            'pelajaran' => $_GET['pelajaran'] ?? '',
            'pengajar' => $_GET['pengajar'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        // Role Constraints
        $userRole = auth_get_role();
        $userId = auth_get_user_id();

        if ($userRole === 'pengajar' && $userId) {
            $filters['pengajar'] = $userId;
        }

        $exams = $gradeModel->getAllExams($filters);

        // Stats
        $stats = [
            'total' => count($exams),
            'selesai' => 0,
            'proses' => 0,
            'belum' => 0
        ];
        foreach ($exams as $e) {
            $st = $e['status'] ?? 'belum';
            if ($st === 'selesai') $stats['selesai']++;
            elseif ($st === 'proses') $stats['proses']++;
            else $stats['belum']++;
        }

        $this->view('grades/index', [
            'exams' => $exams,
            'kelas' => $kelas,
            'pelajaran' => $allSubjects,
            'pengajar' => $pengajar,
            'filters' => $filters,
            'stats' => $stats
        ]);
    }

    public function create() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_token();
            
            $data = [
                'subject_id' => $_POST['id_pelajaran'] ?? null,
                'kelas_id' => $_POST['id_kelas'] ?? null,
                'teacher_id' => $_POST['id_pengajar'] ?? null
            ];

            if ($data['subject_id'] && $data['kelas_id'] && $data['teacher_id']) {
                $model = new GradeModel();
                try {
                    $model->createExam($data);
                    add_flash('success', 'Data koreksi berhasil ditambahkan.');
                } catch (\Exception $e) {
                    add_flash('error', 'Gagal: ' . $e->getMessage());
                }
            } else {
                add_flash('error', 'Semua field harus diisi.');
            }
            redirect('/grades');
        }
    }

    public function edit() {
        require_login();

        $id = $_GET['id'] ?? null;
        if (!$id) redirect('/grades');

        $model = new GradeModel();
        $exam = $model->getExamById($id);

        if (!$exam) {
            add_flash('error', 'Data koreksi tidak ditemukan.');
            redirect('/grades');
        }

        // Access Check (Ideally verify teacher ownership if not admin)
        // Legacy didn't strictly block viewing if I recall, but let's be safe.
        // Actually legacy nilai.php didn't check teacher ownership explicitly in the snippet I saw, 
        // but let's allow it for now as per legacy.

        $students = $model->getGrades($id, $exam['kelas_id']);

        $this->view('grades/edit', [
            'exam' => $exam,
            'students' => $students
        ]);
    }

    public function update() {
        require_login();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/grades');
        csrf_validate_token();

        $id = $_POST['id'] ?? null;
        if (!$id) redirect('/grades');

        $model = new GradeModel();
        $exam = $model->getExamById($id);
        if (!$exam) redirect('/grades');

        $studentIds = $_POST['student_id'] ?? [];
        $skors = $_POST['skor'] ?? [];
        $action = $_POST['action'] ?? 'save';

        // Validation for Finish
        $allFilled = true;
        foreach ($skors as $s) {
            if (trim($s) === '') {
                $allFilled = false;
                break;
            }
        }

        $newStatus = 'proses';
        if ($action === 'finish') {
            if (!$allFilled) {
                add_flash('error', 'Gagal menyelesaikan: Masih ada nilai kosong. Disimpan sebagai draft.');
            } else {
                $newStatus = 'selesai';
            }
        }

        try {
            $model->saveGrades($id, $exam, $studentIds, $skors, $newStatus);
            if ($action === 'finish' && $allFilled) {
                add_flash('success', 'Koreksi selesai.'); // Redirecting to list might be better for "Finish"
                redirect('/grades');
            } else {
                add_flash('success', 'Draft nilai tersimpan.');
                redirect('/grades/edit?id=' . $id);
            }
        } catch (\Exception $e) {
            add_flash('error', 'Gagal menyimpan: ' . $e->getMessage());
            redirect('/grades/edit?id=' . $id);
        }
    }

    public function delete() {
        require_admin();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $model = new GradeModel();
            $model->deleteExam($id);
            add_flash('success', 'Data koreksi dihapus.');
        }
        redirect('/grades');
    }

    public function unlock() {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_token();
            $id = $_POST['id'] ?? null;
            if ($id) {
                $model = new GradeModel();
                $model->unlockExam($id);
                add_flash('success', 'Akes koreksi dibuka kembali.');
            }
        }
        redirect('/grades'); // Or back to where they were?
    }
}
