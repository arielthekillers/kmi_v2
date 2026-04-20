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

        // Data for Filters (Active Year Only)
        $kelas = $kelasModel->findAllActive();
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
                'teacher_id' => $_POST['id_pengajar'] ?? null,
                'skor_maks' => (int)($_POST['skor_maks'] ?? 100)
            ];

            if ($data['subject_id'] && $data['kelas_id'] && $data['teacher_id']) {
                $model = new GradeModel();
                try {
                    $model->createExam($data);
                    add_flash('Data koreksi berhasil ditambahkan.', 'success');
                } catch (\Exception $e) {
                    add_flash('Gagal: ' . $e->getMessage(), 'error');
                }
            } else {
                add_flash('Semua field harus diisi.', 'error');
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
            add_flash('Data koreksi tidak ditemukan.', 'error');
            redirect('/grades');
        }

        // Access Check (Ideally verify teacher ownership if not admin)
        // Legacy didn't strictly block viewing if I recall, but let's be safe.
        // Actually legacy nilai.php didn't check teacher ownership explicitly in the snippet I saw, 
        // but let's allow it for now as per legacy.

        $students = $model->getGrades($id, $exam['kelas_id']);
        
        // Natural Sorting for Students
        usort($students, function ($a, $b) {
            return strnatcasecmp($a['nama'] ?? '', $b['nama'] ?? '');
        });

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

        $userRole = auth_get_role();
        $newStatus = $exam['status'] ?? 'proses';
        $skorMaksPost = $_POST['skor_maks'] ?? null;

        if ($userRole === 'admin') {
            // Admin only updates skor_maks configuration
            if ($skorMaksPost !== null && is_numeric($skorMaksPost)) {
                $exam['skor_maks'] = (float)$skorMaksPost;
            }
            $studentIds = [];
            $skors = [];
            $action = 'save';
        } else {
            // Examiner updates student scores
            $studentIds = $_POST['student_id'] ?? [];
            $skors = $_POST['skor'] ?? [];
            $action = $_POST['action'] ?? 'save';
            // Use current skor_maks from database for examiners, ignore any POST attempt
            // $exam['skor_maks'] remains from getExamById

            $allFilled = true;
            foreach ($skors as $s) {
                if (trim($s) === '') {
                    $allFilled = false;
                    break;
                }
            }

            if ($action === 'finish') {
                if (!$allFilled) {
                    add_flash('Gagal menyelesaikan: Masih ada nilai kosong. Disimpan sebagai draft.', 'error');
                    $newStatus = 'proses';
                } else {
                    $newStatus = 'selesai';
                }
            } else {
                $newStatus = 'proses';
            }
        }

        try {
            $model->saveGrades($id, $exam['subject_id'], $exam['skor_maks'], $exam['skala'] ?? '80-30', $studentIds, $skors, $newStatus);
            if ($userRole !== 'admin' && $action === 'finish' && $allFilled) {
                add_flash('Koreksi selesai.', 'success');
                redirect('/grades');
            } else {
                $msg = ($userRole === 'admin') ? 'Konfigurasi skor berhasil diupdate.' : 'Draft nilai tersimpan.';
                add_flash($msg, 'success');
                redirect('/grades/edit?id=' . $id);
            }
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan: ' . $e->getMessage(), 'error');
            redirect('/grades/edit?id=' . $id);
        }
    }

    public function delete() {
        require_admin();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $model = new GradeModel();
            $model->deleteExam($id);
            add_flash('Data koreksi dihapus.', 'success');
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
                add_flash('Akes koreksi dibuka kembali.', 'success');
            }
        }
        redirect('/grades'); // Or back to where they were?
    }
}
