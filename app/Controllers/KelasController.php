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
        $teachers = $db->query("
            SELECT u.id, 
                   CASE 
                       WHEN tp.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u.nama)
                       WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                       ELSE u.nama
                   END as nama
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'pengajar' 
            ORDER BY u.nama ASC
        ")->fetchAll();
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
        require_login();
        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/classes');

        $kelas = $this->kelasModel->find($id);
        if (!$kelas) {
            add_flash('Kelas tidak ditemukan.', 'error');
            $this->redirect('/classes');
        }

        $role = auth_get_role();
        $userId = auth_get_user_id();

        // Check permission: Must be admin OR Wali Kelas for this class
        if ($role !== 'admin' && $kelas['teacher_id'] != $userId) {
            add_flash('Anda tidak memiliki akses ke halaman ini.', 'error');
            $this->redirect('/');
        }

        $tab = $_GET['tab'] ?? 'overview';
        $data = [
            'title' => "Detail Kelas " . $kelas['tingkat'] . "-" . $kelas['abjad'],
            'kelas' => $kelas,
            'tab' => $tab,
            'user' => $_SESSION['nama'] ?? 'User',
            'role' => $role
        ];

        // Fetch Tab-specific data
        if ($tab === 'santri') {
            $data['students'] = $this->kelasModel->getStudentsWithDetails($id);
        } elseif ($tab === 'jadwal') {
            $data['schedule'] = $this->kelasModel->getScheduleWithDetails($id);
        } elseif ($tab === 'nilai') {
            $gradeModel = new \App\Models\GradeModel();
            $sessions = $gradeModel->getSessions($this->currentYear['id']);
            $activeSession = $gradeModel->getActiveSession($this->currentYear['id']);
            
            $sessionId = $_GET['session_id'] ?? ($activeSession ? $activeSession['id'] : '');
            
            $filters = ['kelas' => $id];
            if (!empty($sessionId)) {
                $filters['exam_session_id'] = $sessionId;
            }
            
            $data['sessions'] = $sessions;
            $data['selected_session_id'] = $sessionId;
            $data['exams'] = $gradeModel->getAllExams($filters);
        } elseif ($tab === 'view_nilai') {
            $examId = $_GET['exam_id'] ?? null;
            if (!$examId) $this->redirect('/classes/detail?id=' . $id . '&tab=nilai');

            $gradeModel = new \App\Models\GradeModel();
            $exam = $gradeModel->getExamById($examId);
            if (!$exam || $exam['kelas_id'] != $id) {
                add_flash('Data nilai tidak ditemukan.', 'error');
                $this->redirect('/classes/detail?id=' . $id . '&tab=nilai');
            }

            $students = $gradeModel->getGrades($examId, $exam['kelas_id'], $exam['academic_year_id']);
            usort($students, function ($a, $b) {
                return strnatcasecmp($a['nama'] ?? '', $b['nama'] ?? '');
            });

            $data['exam'] = $exam;
            $data['students'] = $students;
        } elseif ($tab === 'raport') {
            $gradeModel = new \App\Models\GradeModel();
            $sessions = $gradeModel->getSessions($this->currentYear['id']);
            $activeSession = $gradeModel->getActiveSession($this->currentYear['id']);
            
            $sessionId = $_GET['session_id'] ?? ($activeSession ? $activeSession['id'] : '');
            
            $data['sessions'] = $sessions;
            $data['selected_session_id'] = $sessionId;
            
            if ($sessionId) {
                $data['leger'] = $gradeModel->getClassLeger($id, $sessionId, $this->currentYear['id']);
            } else {
                $data['leger'] = null;
            }
        } elseif ($tab === 'raport_detail') {
            $studentId = $_GET['student_id'] ?? null;
            $sessionId = $_GET['session_id'] ?? null;
            
            if (!$studentId || !$sessionId) {
                $this->redirect('/classes/detail?id=' . $id . '&tab=raport');
            }

            $gradeModel = new \App\Models\GradeModel();
            $sessions = $gradeModel->getSessions($this->currentYear['id']);
            $data['sessions'] = $sessions;
            $data['selected_session_id'] = $sessionId;

            $leger = $gradeModel->getClassLeger($id, $sessionId, $this->currentYear['id']);
            
            $currentStudent = null;
            foreach ($leger['students'] as $s) {
                if ($s['student_id'] == $studentId) {
                    $currentStudent = $s;
                    break;
                }
            }

            if (!$currentStudent) {
                add_flash('Data santri tidak ditemukan.', 'error');
                $this->redirect('/classes/detail?id=' . $id . '&tab=raport&session_id=' . $sessionId);
            }

            $data['student'] = $currentStudent;
            $data['leger'] = $leger;
        } elseif ($tab === 'perilaku') {
            $gradeModel = new \App\Models\GradeModel();
            $sessions = $gradeModel->getSessions($this->currentYear['id']);
            $activeSession = $gradeModel->getActiveSession($this->currentYear['id']);
            
            $sessionId = $_GET['session_id'] ?? ($activeSession ? $activeSession['id'] : '');
            
            $data['sessions'] = $sessions;
            $data['selected_session_id'] = $sessionId;
            $data['students'] = $this->kelasModel->getStudentsWithDetails($id);
            
            $behaviors = [];
            if ($sessionId) {
                $db = \App\Core\Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT student_id, suluk, muwathobah, nadhofah 
                    FROM student_behaviors 
                    WHERE academic_year_id = ? AND exam_session_id = ?
                ");
                $stmt->execute([$this->currentYear['id'], $sessionId]);
                $behaviorsList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($behaviorsList as $row) {
                    $behaviors[$row['student_id']] = $row;
                }
            }
            $data['behaviors'] = $behaviors;
        } elseif ($tab === 'raport_arab') {
            $studentId = $_GET['student_id'] ?? null;
            $sessionId = $_GET['session_id'] ?? null;
            
            if (!$studentId || !$sessionId) {
                $this->redirect('/classes/detail?id=' . $id . '&tab=raport');
            }

            $gradeModel = new \App\Models\GradeModel();
            $sessions = $gradeModel->getSessions($this->currentYear['id']);
            $data['sessions'] = $sessions;
            $data['selected_session_id'] = $sessionId;

            $leger = $gradeModel->getClassLeger($id, $sessionId, $this->currentYear['id']);
            
            $currentStudent = null;
            foreach ($leger['students'] as $s) {
                if ($s['student_id'] == $studentId) {
                    $currentStudent = $s;
                    break;
                }
            }

            if (!$currentStudent) {
                add_flash('Data santri tidak ditemukan.', 'error');
                $this->redirect('/classes/detail?id=' . $id . '&tab=raport&session_id=' . $sessionId);
            }

            // Fetch behavior grades
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT suluk, muwathobah, nadhofah 
                FROM student_behaviors 
                WHERE student_id = ? AND academic_year_id = ? AND exam_session_id = ?
            ");
            $stmt->execute([$studentId, $this->currentYear['id'], $sessionId]);
            $behavior = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Fetch class averages for each exam in this session
            // To display in the 'المعدلة' (Class Average) column
            $examAverages = [];
            foreach ($leger['exams'] as $exam) {
                if ($exam['status'] === 'selesai') {
                    $stmtAvg = $db->prepare("
                        SELECT AVG(score_final) as average_score 
                        FROM grades 
                        WHERE exam_id = ? AND score_final IS NOT NULL
                    ");
                    $stmtAvg->execute([$exam['exam_id']]);
                    $examAverages[$exam['exam_id']] = $stmtAvg->fetchColumn() ?: 0;
                } else {
                    $examAverages[$exam['exam_id']] = null;
                }
            }

            $data['student'] = $currentStudent;
            $data['leger'] = $leger;
            $data['behavior'] = $behavior;
            $data['examAverages'] = $examAverages;
        } elseif ($tab === 'leger') {
            $sessionId = $_GET['session_id'] ?? null;
            if (!$sessionId) {
                $this->redirect('/classes/detail?id=' . $id . '&tab=raport');
            }

            $gradeModel = new \App\Models\GradeModel();
            $sessions = $gradeModel->getSessions($this->currentYear['id']);
            $data['sessions'] = $sessions;
            $data['selected_session_id'] = $sessionId;

            $leger = $gradeModel->getClassLeger($id, $sessionId, $this->currentYear['id']);
            
            // Fetch behaviors
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT student_id, suluk, muwathobah, nadhofah 
                FROM student_behaviors 
                WHERE academic_year_id = ? AND exam_session_id = ?
            ");
            $stmt->execute([$this->currentYear['id'], $sessionId]);
            $behaviorsList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $behaviors = [];
            foreach ($behaviorsList as $row) {
                $behaviors[$row['student_id']] = $row;
            }

            // Fetch student attendance logs (aggregate Sakit, Izin, Alpa)
            $stmtAtt = $db->prepare("
                SELECT student_id, 
                       SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) as sakit,
                       SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as izin,
                       SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as alpa
                FROM attendance
                GROUP BY student_id
            ");
            $stmtAtt->execute();
            $attendanceList = $stmtAtt->fetchAll(\PDO::FETCH_ASSOC);
            $attendance = [];
            foreach ($attendanceList as $row) {
                $attendance[$row['student_id']] = $row;
            }

            // Fetch class averages for each exam in this session
            $examAverages = [];
            foreach ($leger['exams'] as $exam) {
                if ($exam['status'] === 'selesai') {
                    $stmtAvg = $db->prepare("
                        SELECT AVG(score_final) as average_score 
                        FROM grades 
                        WHERE exam_id = ? AND score_final IS NOT NULL
                    ");
                    $stmtAvg->execute([$exam['exam_id']]);
                    $examAverages[$exam['exam_id']] = $stmtAvg->fetchColumn() ?: 0;
                } else {
                    $examAverages[$exam['exam_id']] = null;
                }
            }

            // Calculate student rankings and totals
            $studentScores = [];
            foreach ($leger['students'] as $s) {
                $studentId = $s['student_id'];
                $totalScore = 0;
                $examCount = 0;
                foreach ($leger['exams'] as $exam) {
                    if ($exam['status'] === 'selesai') {
                        $grade = $leger['grades'][$studentId][$exam['exam_id']] ?? null;
                        if ($grade && $grade['score_final'] !== null) {
                            $totalScore += round($grade['score_final']);
                            $examCount++;
                        }
                    }
                }
                $avgScore = $examCount > 0 ? $totalScore / $examCount : 0;
                $studentScores[$studentId] = [
                    'total' => $totalScore,
                    'avg' => $avgScore,
                    'count' => $examCount
                ];
            }

            // Sort students by total score descending to assign rankings
            uasort($studentScores, function ($a, $b) {
                return $b['total'] <=> $a['total'];
            });

            $rankings = [];
            $rank = 1;
            $prevScore = -1;
            $sameRankCount = 0;
            foreach ($studentScores as $studentId => $scores) {
                if ($scores['total'] < $prevScore) {
                    $rank += $sameRankCount;
                    $sameRankCount = 1;
                } else {
                    $sameRankCount++;
                }
                $rankings[$studentId] = $rank;
                $prevScore = $scores['total'];
            }

            $data['leger'] = $leger;
            $data['behaviors'] = $behaviors;
            $data['attendance'] = $attendance;
            $data['studentScores'] = $studentScores;
            $data['rankings'] = $rankings;
            $data['examAverages'] = $examAverages;
        }

        renderHeader($data['title']);
        $this->view('kelas/detail', $data);
        renderFooter();
    }

    public function savePerilaku() {
        require_login();
        csrf_validate_token();

        $classId = $_POST['class_id'] ?? '';
        $sessionId = $_POST['session_id'] ?? '';

        if (empty($classId) || empty($sessionId)) {
            add_flash('Sesi ujian dan kelas tidak valid.', 'error');
            $this->redirect('/classes');
        }

        // Check permission: Must be admin OR Wali Kelas for this class
        $kelas = $this->kelasModel->find($classId);
        $role = auth_get_role();
        $userId = auth_get_user_id();
        if ($role !== 'admin' && $kelas['teacher_id'] != $userId) {
            add_flash('Anda tidak memiliki akses untuk melakukan tindakan ini.', 'error');
            $this->redirect('/');
        }

        $suluk = $_POST['suluk'] ?? [];
        $muwathobah = $_POST['muwathobah'] ?? [];
        $nadhofah = $_POST['nadhofah'] ?? [];
        $studentIds = $_POST['student_id'] ?? [];

        $db = \App\Core\Database::getInstance()->getConnection();
        try {
            $db->beginTransaction();
            
            $sql = "INSERT INTO student_behaviors (student_id, academic_year_id, exam_session_id, suluk, muwathobah, nadhofah) 
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        suluk = VALUES(suluk), 
                        muwathobah = VALUES(muwathobah),
                        nadhofah = VALUES(nadhofah)";
            
            $stmt = $db->prepare($sql);
            
            foreach ($studentIds as $studentId) {
                $sScore = isset($suluk[$studentId]) && $suluk[$studentId] !== '' ? (int)$suluk[$studentId] : null;
                $mScore = isset($muwathobah[$studentId]) && $muwathobah[$studentId] !== '' ? (int)$muwathobah[$studentId] : null;
                $nScore = isset($nadhofah[$studentId]) && $nadhofah[$studentId] !== '' ? (int)$nadhofah[$studentId] : null;
                
                $stmt->execute([
                    $studentId, 
                    $this->currentYear['id'], 
                    $sessionId, 
                    $sScore, 
                    $mScore, 
                    $nScore
                ]);
            }
            
            $db->commit();
            add_flash('Nilai perilaku santri berhasil disimpan.', 'success');
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            add_flash('Gagal menyimpan nilai perilaku: ' . $e->getMessage(), 'error');
        }

        $this->redirect('/classes/detail?id=' . $classId . '&tab=perilaku&session_id=' . $sessionId);
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
