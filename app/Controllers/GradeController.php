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
        $ayModel = new \App\Models\AcademicYearModel();

        // Academic Years list for filter
        $academicYears = $ayModel->getAll();

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

        // Active Session Context
        $activeSession = $gradeModel->getActiveSession($this->currentYear['id']);
        $allSessions = $gradeModel->getSessions($this->currentYear['id']);

        // Role Constraints
        $userRole = auth_get_role();
        $userId = auth_get_user_id();

        // Stats and Progress per Class (Academic Session wide)
        $progressFilters = [
            'academic_year_id' => $this->currentYear['id'],
            'exam_session_id' => ($activeSession['id'] ?? '')
        ];
        if ($userRole === 'pengajar' && $userId && !auth_is_panitia()) {
            $progressFilters['pengajar'] = $userId;
        }
        $allExamsForStats = $gradeModel->getAllExams($progressFilters);

        $classProgress = [];
        foreach ($allExamsForStats as $exam) {
            $klsId = $exam['kelas_id'];
            if (!isset($classProgress[$klsId])) {
                $classProgress[$klsId] = [
                    'total' => 0,
                    'selesai' => 0
                ];
            }
            $classProgress[$klsId]['total']++;
            if (($exam['status'] ?? '') === 'selesai') {
                $classProgress[$klsId]['selesai']++;
            }
        }

        // Decorate and filter out classes with no exams (0/0 correction)
        foreach ($kelas as $key => &$k) {
            $kId = $k['id'];
            $totalExams = $classProgress[$kId]['total'] ?? 0;
            if ($totalExams === 0) {
                unset($kelas[$key]);
            } else {
                $k['total_exams'] = $totalExams;
                $k['selesai_exams'] = $classProgress[$kId]['selesai'] ?? 0;
            }
        }
        unset($k);

        // Determine active class by default if none selected, using the filtered list
        $activeKelasId = $_GET['kelas'] ?? '';
        if (empty($activeKelasId) && !empty($kelas)) {
            $firstKelas = reset($kelas);
            $activeKelasId = $firstKelas['id'];
        }

        // Filter Params
        $filters = [
            'academic_year_id' => $this->currentYear['id'],
            'exam_session_id' => ($activeSession['id'] ?? ''),
            'kelas' => $activeKelasId,
            'pelajaran' => $_GET['pelajaran'] ?? '',
            'pengajar' => $_GET['pengajar'] ?? '',
            'status' => $_GET['status'] ?? '',
            'has_oral' => $_GET['has_oral'] ?? ''
        ];

        if ($userRole === 'pengajar' && $userId && !auth_is_panitia()) {
            $filters['pengajar'] = $userId;
        }

        $exams = $gradeModel->getAllExams($filters);

        // Teaching Assignments Map for dynamic 'Add Koreksi'
        $scheduleModel = new \App\Models\ScheduleModel();
        $teachingMap = $scheduleModel->getAllAssignments($this->currentYear['id']);

        // Special subjects (is_special = 1) for the toggle
        $subjectModel2 = new \App\Models\SubjectModel();
        $specialSubjects = $subjectModel2->getSpecialSubjects();

        $this->view('grades/index', [
            'exams'          => $exams,
            'kelas'          => $kelas,
            'pelajaran'      => $allSubjects,
            'pengajar'       => $pengajar,
            'teachingMap'    => $teachingMap,
            'specialSubjects'=> $specialSubjects,
            'academicYears'  => $academicYears,
            'allSessions'    => $allSessions,
            'activeSession'  => $activeSession,
            'filters'        => $filters
        ]);
    }

    public function create() {
        if (!auth_can_manage_grades()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/grades');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_token();
            
            $data = [
                'subject_id' => $_POST['id_pelajaran'] ?? null,
                'kelas_id' => $_POST['id_kelas'] ?? null,
                'teacher_id' => $_POST['id_pengajar'] ?? null,
                'skor_maks' => (int)($_POST['skor_maks'] ?? 100),
                'has_oral' => (int)($_POST['has_oral'] ?? 0)
            ];

            if ($data['subject_id'] && $data['kelas_id'] && $data['teacher_id']) {
                $model = new GradeModel();
                try {
                    $examId = $model->createExam($data);
                    log_activity("Membuat form koreksi ujian (Pelajaran ID: {$data['subject_id']}, Kelas ID: {$data['kelas_id']})");
                    add_flash('Data koreksi berhasil ditambahkan. Silakan lengkapi nomor bayanat.', 'success');
                    $this->redirect('/grades?kelas=' . $data['kelas_id']);
                } catch (\Exception $e) {
                    add_flash('Gagal: ' . $e->getMessage(), 'error');
                }
            } else {
                add_flash('Semua field harus diisi.', 'error');
            }
            if (!empty($data['kelas_id'])) {
                redirect('/grades?kelas=' . $data['kelas_id']);
            } else {
                redirect('/grades');
            }
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

        $students = $model->getGrades($id, $exam['kelas_id'], $exam['academic_year_id']);
        
        $isPanitia = auth_is_panitia($exam['exam_session_id'] ?? null);
        $isExaminer = (isset($exam['teacher_id']) && $exam['teacher_id'] == auth_get_user_id());

        // Sorting for Students
        if ($isExaminer) {
            // Examiner sorts by bayanat (Requirement 2)
            // Handle NULL/empty bayanat by putting them at the end
            usort($students, function ($a, $b) {
                $aBay = ($a['no_bayanat'] !== null && $a['no_bayanat'] !== '') ? (int)$a['no_bayanat'] : 999999;
                $bBay = ($b['no_bayanat'] !== null && $b['no_bayanat'] !== '') ? (int)$b['no_bayanat'] : 999999;
                if ($aBay === $bBay) {
                    return strnatcasecmp($a['nama'] ?? '', $b['nama'] ?? '');
                }
                return $aBay <=> $bBay;
            });
        } else {
            // Admin/Panitia/Others sort by name
            usort($students, function ($a, $b) {
                return strnatcasecmp($a['nama'] ?? '', $b['nama'] ?? '');
            });
        }

        $this->view('grades/edit', [
            'exam' => $exam,
            'students' => $students,
            'isPanitia' => $isPanitia
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
        $isPanitia = auth_is_panitia($exam['exam_session_id']);
        $isAdmin = ($userRole === 'admin');
        $sessionOpen = (isset($exam['session_is_open']) && $exam['session_is_open'] == 1);

        // Teacher Restriction: Cannot update if session is closed
        if (!$isAdmin && !$isPanitia && !$sessionOpen) {
            add_flash('Sesi input nilai untuk ujian ini sedang ditutup oleh Panitia.', 'error');
            redirect('/grades/edit?id=' . $id);
        }

        $newStatus = $exam['status'] ?? 'proses';
        $skorMaksPost = $_POST['skor_maks'] ?? null;
        $studentIds = $_POST['student_id'] ?? [];
        $noBayanats = $_POST['no_bayanat'] ?? [];
        $action = $_POST['action'] ?? 'save';

        $isExaminer = (isset($exam['teacher_id']) && $exam['teacher_id'] == auth_get_user_id());

        if ($isAdmin || $isPanitia) {
            // Admin updates skor_maks and no_bayanat mapping
            if ($skorMaksPost !== null && is_numeric($skorMaksPost)) {
                $exam['skor_maks'] = (float)$skorMaksPost;
            }
            // Only force 'save' action if not the examiner (who might be clicking 'finish')
            if (!$isExaminer) {
                $action = 'save';
            }
        }

        // Always process scores if provided (Requirement 3 fix)
        $skors = $_POST['skor'] ?? [];
        $skorsLisan = $_POST['skor_lisan'] ?? [];
        
        $examType = (int)($exam['has_oral'] ?? 0);
        $allFilled = true;
        $validateOral = ($isAdmin || $isPanitia);
        
        if ($examType == 0) {
            // Tulis only: check $skors
            if (empty($skors)) {
                $allFilled = false;
            } else {
                foreach ($skors as $s) {
                    if (trim($s) === '') {
                        $allFilled = false;
                        break;
                    }
                }
            }
        } elseif ($examType == 2) {
            // Lisan only: check $skorsLisan (only if user is allowed to edit/see oral scores)
            if ($validateOral) {
                if (empty($skorsLisan)) {
                    $allFilled = false;
                } else {
                    foreach ($skorsLisan as $s) {
                        if (trim($s) === '') {
                            $allFilled = false;
                            break;
                        }
                    }
                }
            }
        } elseif ($examType == 1) {
            // Tulis & Lisan: check both if admin/panitia, otherwise check only tulis
            if ($validateOral) {
                if (empty($skors) || empty($skorsLisan)) {
                    $allFilled = false;
                } else {
                    for ($i = 0; $i < count($studentIds); $i++) {
                        $sVal = isset($skors[$i]) ? trim($skors[$i]) : '';
                        $oVal = isset($skorsLisan[$i]) ? trim($skorsLisan[$i]) : '';
                        if ($sVal === '' || $oVal === '') {
                            $allFilled = false;
                            break;
                        }
                    }
                }
            } else {
                // Only validate tulis
                if (empty($skors)) {
                    $allFilled = false;
                } else {
                    foreach ($skors as $s) {
                        if (trim($s) === '') {
                            $allFilled = false;
                            break;
                        }
                    }
                }
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
        try {
            $saveData = [
                'skor_lisan' => ($isAdmin || $isPanitia) ? ($skorsLisan) : [],
                'nilai' => $_POST['nilai'] ?? []
            ];
            if ($isAdmin || $isPanitia) {
                $hasOralPost = $_POST['has_oral'] ?? null;
                if ($hasOralPost !== null) {
                    $saveData['has_oral'] = (int)$hasOralPost;
                }
            }
            $model->saveGrades($id, $exam['subject_id'], $exam['skor_maks'], $exam['skala'] ?? '80-30', $studentIds, $skors, $newStatus, $noBayanats, $saveData);
            $logMsg = ($newStatus === 'selesai') ? "Menyelesaikan koreksi ujian (ID: {$id}, Pelajaran ID: {$exam['subject_id']}, Kelas ID: {$exam['kelas_id']})" : "Menyimpan draf nilai ujian (ID: {$id}, Pelajaran ID: {$exam['subject_id']}, Kelas ID: {$exam['kelas_id']})";
            log_activity($logMsg);
            if ($userRole !== 'admin' && $action === 'finish' && $allFilled) {
                add_flash('Koreksi selesai.', 'success');
            } else {
                $msg = ($userRole === 'admin') ? 'Konfigurasi & Bayanat berhasil diupdate.' : 'Draft nilai tersimpan.';
                add_flash($msg, 'success');
            }
            redirect('/grades?kelas=' . $exam['kelas_id']);
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan: ' . $e->getMessage(), 'error');
            redirect('/grades/edit?id=' . $id);
        }
    }

    public function delete() {
        if (!auth_can_manage_grades()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/grades');
        }
        $id = $_GET['id'] ?? null;
        $kelasId = null;
        if ($id) {
            $model = new GradeModel();
            $exam = $model->getExamById($id);
            if ($exam) {
                $kelasId = $exam['kelas_id'];
                $model->deleteExam($id);
                log_activity("Menghapus data koreksi ujian ke tong sampah (ID: {$id}, Pelajaran ID: {$exam['subject_id']}, Kelas ID: {$exam['kelas_id']})");
                add_flash('Data koreksi dihapus.', 'success');
            }
        }
        if ($kelasId) {
            redirect('/grades?kelas=' . $kelasId);
        } else {
            redirect('/grades');
        }
    }

    public function unlock() {
        if (!auth_can_manage_grades()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/grades');
        }
        $kelasId = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_token();
            $id = $_POST['id'] ?? null;
            if ($id) {
                $model = new GradeModel();
                $exam = $model->getExamById($id);
                if ($exam) {
                    $kelasId = $exam['kelas_id'];
                    $model->unlockExam($id);
                    log_activity("Membuka kembali akses koreksi ujian (ID: {$id}, Pelajaran ID: {$exam['subject_id']}, Kelas ID: {$exam['kelas_id']})");
                    add_flash('Akes koreksi dibuka kembali.', 'success');
                }
            }
        }
        if ($kelasId) {
            redirect('/grades?kelas=' . $kelasId);
        } else {
            redirect('/grades');
        }
    }

    public function trash() {
        require_login();
        if (!auth_can_manage_grades()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/grades');
        }

        $model = new GradeModel();
        $deletedExams = $model->getDeletedExams($this->currentYear['id']);
        
        $this->view('grades/trash', [
            'title' => 'Tong Sampah - Koreksi Ujian',
            'deletedExams' => $deletedExams
        ]);
    }

    public function restore() {
        if (!auth_can_manage_grades()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/grades');
        }
        $id = $_GET['id'] ?? null;
        if ($id) {
            $model = new GradeModel();
            $model->restoreExam($id);
            log_activity("Memulihkan data koreksi ujian dari tong sampah (ID: {$id})");
            add_flash('Data koreksi berhasil dikembalikan.', 'success');
        }
        $this->redirect('/grades/trash');
    }

    public function force_delete() {
        if (!auth_can_manage_grades()) {
            add_flash('Akses ditolak.', 'error');
            $this->redirect('/grades');
        }
        $id = $_GET['id'] ?? null;
        if ($id) {
            $model = new GradeModel();
            $model->hardDeleteExam($id);
            log_activity("Menghapus secara permanen data koreksi ujian beserta nilainya (ID: {$id})");
            add_flash('Data koreksi dihapus permanen beserta nilainya.', 'success');
        }
        $this->redirect('/grades/trash');
    }
}
