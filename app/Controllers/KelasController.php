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
            
            $exams = $gradeModel->getAllExams($filters);
            foreach ($exams as &$exam) {
                if ($exam['status'] === 'selesai') {
                    $students = $gradeModel->getGrades($exam['id'], $id, $exam['academic_year_id']);
                    $total = 0;
                    $count = 0;
                    foreach ($students as $row) {
                        $merged = calculate_merged_grade($row['nilai'], $row['score_oral'], $exam['has_oral']);
                        if ($merged !== null) {
                            $total += $merged;
                            $count++;
                        }
                    }
                    $exam['average_score'] = $count > 0 ? ($total / $count) : 0;
                }
            }
            unset($exam);
            $data['exams'] = $exams;
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
            foreach ($students as &$studentRow) {
                $studentRow['nilai_akhir'] = calculate_merged_grade($studentRow['nilai'], $studentRow['score_oral'], $exam['has_oral']);
            }
            unset($studentRow);

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
                    $examId = $exam['exam_id'];
                    $total = 0;
                    $count = 0;
                    foreach ($leger['students'] as $s) {
                        $grade = $leger['grades'][$s['student_id']][$examId] ?? null;
                        if ($grade && $grade['score_final'] !== null) {
                            $total += round($grade['score_final']);
                            $count++;
                        }
                    }
                    $examAverages[$examId] = $count > 0 ? ($total / $count) : 0;
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
                    $examId = $exam['exam_id'];
                    $total = 0;
                    $count = 0;
                    foreach ($leger['students'] as $s) {
                        $grade = $leger['grades'][$s['student_id']][$examId] ?? null;
                        if ($grade && $grade['score_final'] !== null) {
                            $total += round($grade['score_final']);
                            $count++;
                        }
                    }
                    $examAverages[$examId] = $count > 0 ? ($total / $count) : 0;
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

    public function exportLeger() {
        require_login();
        $id = $_GET['id'] ?? null;
        $sessionId = $_GET['session_id'] ?? null;

        if (!$id || !$sessionId) {
            $this->redirect('/classes');
        }

        $kelas = $this->kelasModel->find($id);
        if (!$kelas) {
            add_flash('Kelas tidak ditemukan.', 'error');
            $this->redirect('/classes');
        }

        $role = auth_get_role();
        $userId = auth_get_user_id();

        // Check permission: Must be admin OR Wali Kelas for this class
        if ($role !== 'admin' && $kelas['teacher_id'] != $userId) {
            add_flash('Anda tidak memiliki akses untuk mengekspor data ini.', 'error');
            $this->redirect('/');
        }

        $gradeModel = new \App\Models\GradeModel();
        $leger = $gradeModel->getClassLeger($id, $sessionId, $this->currentYear['id']);

        if (empty($leger['students'])) {
            add_flash('Data leger kosong atau tidak ada santri di kelas ini.', 'error');
            $this->redirect('/classes/detail?id=' . $id . '&tab=leger&session_id=' . $sessionId);
        }

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
                $examId = $exam['exam_id'];
                $total = 0;
                $count = 0;
                foreach ($leger['students'] as $s) {
                    $grade = $leger['grades'][$s['student_id']][$examId] ?? null;
                    if ($grade && $grade['score_final'] !== null) {
                        $total += round($grade['score_final']);
                        $count++;
                    }
                }
                $examAverages[$examId] = $count > 0 ? ($total / $count) : 0;
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

        // Calculate subject stats
        $subjectStats = [];
        foreach ($leger['exams'] as $exam) {
            $examId = $exam['exam_id'];
            $scores = [];
            foreach ($leger['students'] as $s) {
                $studentId = $s['student_id'];
                $grade = $leger['grades'][$studentId][$examId] ?? null;
                if ($exam['status'] === 'selesai' && $grade && $grade['score_final'] !== null) {
                    $scores[] = round($grade['score_final']);
                }
            }
            
            $subjectStats[$examId] = [
                'sum' => !empty($scores) ? array_sum($scores) : 0,
                'min' => !empty($scores) ? min($scores) : '-',
                'max' => !empty($scores) ? max($scores) : '-',
                'avg' => !empty($scores) ? array_sum($scores) / count($scores) : 0
            ];
        }

        // Calculate the class overall average
        $totalAvgSum = 0;
        $avgCount = 0;
        foreach ($studentScores as $studentId => $scores) {
            if ($scores['count'] > 0) {
                $totalAvgSum += $scores['avg'];
                $avgCount++;
            }
        }
        $classOverallAvg = $avgCount > 0 ? $totalAvgSum / $avgCount : 0;

        // Fetch session type for displaying session name
        $stmtSession = $db->prepare("SELECT type FROM exam_sessions WHERE id = ?");
        $stmtSession->execute([$sessionId]);
        $sessionType = $stmtSession->fetchColumn() ?: '';

        $typeMap = [
            'UUPT' => 'Ulangan Umum Pertengahan Tahun',
            'UPT' => 'Ujian Pertengahan Tahun',
            'UUAT' => 'Ulangan Umum Akhir Tahun',
            'UAT' => 'Ujian Akhir Tahun'
        ];
        $sessionName = $typeMap[$sessionType] ?? $sessionType;

        $filename = "Rekap_Nilai_Kelas_" . str_replace(' ', '_', $kelas['tingkat'] . '-' . $kelas['abjad']) . "_" . date('Ymd_His') . ".xls";
        log_activity("Mengekspor rekap nilai kelas " . $kelas['tingkat'] . "-" . $kelas['abjad'] . " ke Excel (Sesi: {$sessionName})");

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head>';
        $html .= '<meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8">';
        $html .= '<style>';
        $html .= '  .title { font-family: Arial, sans-serif; font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; }';
        $html .= '  .subtitle { font-family: Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; text-transform: uppercase; }';
        $html .= '  .meta-info { font-family: Arial, sans-serif; font-size: 9pt; text-align: center; color: #555555; }';
        $html .= '  .leger-table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 9pt; width: 100%; }';
        $html .= '  .leger-table th { border: 1px solid #000000; background-color: #f2f2f2; font-weight: bold; text-align: center; vertical-align: middle; }';
        $html .= '  .leger-table td { border: 1px solid #000000; text-align: center; vertical-align: middle; }';
        $html .= '  .rotated-header { mso-rotate: 90; height: 120px; width: 30px; white-space: nowrap; }';
        $html .= '  .student-name { text-align: left; font-weight: bold; }';
        $html .= '  .number-text { mso-number-format: "\@"; }';
        $html .= '  .bold { font-weight: bold; }';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';

        // Header info
        $html .= '<div class="title">REKAPITULASI NILAI SANTRI</div>';
        $html .= '<div class="subtitle">KELAS: ' . htmlspecialchars($kelas['tingkat'] . '-' . $kelas['abjad']) . ' | SESI: ' . htmlspecialchars($sessionName) . ' | TAHUN AJARAN: ' . htmlspecialchars($this->currentYear['name'] ?? '') . '</div>';
        $html .= '<div class="meta-info">Wali Kelas: ' . htmlspecialchars($kelas['wali_kelas'] ?? '-') . '</div>';
        $html .= '<br/>';

        // Table
        $html .= '<table class="leger-table" border="1">';
        $html .= '  <thead>';
        $html .= '    <tr>';
        $html .= '      <th rowspan="2" style="width: 40px;">No</th>';
        $html .= '      <th rowspan="2" style="width: 80px;">STBK</th>';
        $html .= '      <th rowspan="2" style="width: 250px;">NAMA</th>';
        
        // Subject columns header
        foreach ($leger['exams'] as $exam) {
            $html .= '      <th class="rotated-header" rowspan="2">' . htmlspecialchars($exam['subject_name']) . '</th>';
        }
        
        $html .= '      <th rowspan="2" style="width: 70px;">JUMLAH</th>';
        $html .= '      <th rowspan="2" style="width: 80px;">RATA-RATA</th>';
        $html .= '      <th rowspan="2" style="width: 70px;">RANKING</th>';
        
        // Behaviors & Absences colspan headers
        $html .= '      <th colspan="3">NILAI PERILAKU</th>';
        $html .= '      <th colspan="3">ABSENSI</th>';
        $html .= '    </tr>';
        $html .= '    <tr>';
        $html .= '      <th style="width: 50px;">SULUK</th>';
        $html .= '      <th style="width: 50px;">MUWATHOBAH</th>';
        $html .= '      <th style="width: 50px;">NADHOFAH</th>';
        $html .= '      <th style="width: 50px;">SAKIT</th>';
        $html .= '      <th style="width: 50px;">IZIN</th>';
        $html .= '      <th style="width: 50px;">ALPA</th>';
        $html .= '    </tr>';
        $html .= '  </thead>';
        $html .= '  <tbody>';

        // Student rows
        foreach ($leger['students'] as $idx => $s) {
            $studentId = $s['student_id'];
            $scores = $studentScores[$studentId];
            
            $b = $behaviors[$studentId] ?? null;
            $sVal = $b && $b['suluk'] !== null ? $b['suluk'] : '';
            $mVal = $b && $b['muwathobah'] !== null ? $b['muwathobah'] : '';
            $nVal = $b && $b['nadhofah'] !== null ? $b['nadhofah'] : '';
            
            $att = $attendance[$studentId] ?? null;
            $sakitVal = $att ? $att['sakit'] : 0;
            $izinVal = $att ? $att['izin'] : 0;
            $alpaVal = $att ? $att['alpa'] : 0;

            $html .= '    <tr>';
            $html .= '      <td>' . ($idx + 1) . '</td>';
            $html .= '      <td class="number-text">' . htmlspecialchars($s['nis']) . '</td>';
            $html .= '      <td class="student-name">' . htmlspecialchars($s['nama']) . '</td>';
            
            // Grades
            foreach ($leger['exams'] as $exam) {
                $grade = $leger['grades'][$studentId][$exam['exam_id']] ?? null;
                $hasGrade = ($exam['status'] === 'selesai' && $grade && $grade['score_final'] !== null);
                $scoreNum = $hasGrade ? round($grade['score_final']) : '-';
                $html .= '      <td>' . $scoreNum . '</td>';
            }

            // Totals
            $html .= '      <td class="bold">' . $scores['total'] . '</td>';
            $html .= '      <td class="bold">' . ($scores['count'] > 0 ? number_format($scores['avg'], 2) : '-') . '</td>';
            $html .= '      <td class="bold">' . $rankings[$studentId] . '</td>';
            
            // Behaviors
            $html .= '      <td>' . $sVal . '</td>';
            $html .= '      <td>' . $mVal . '</td>';
            $html .= '      <td>' . $nVal . '</td>';
            
            // Absences
            $html .= '      <td>' . $sakitVal . '</td>';
            $html .= '      <td>' . $izinVal . '</td>';
            $html .= '      <td>' . $alpaVal . '</td>';
            $html .= '    </tr>';
        }

        // Statistics rows (Jumlah, Minimal, Maksimal, Rata-rata)
        // Row 1: Jumlah
        $html .= '    <tr style="font-weight: bold; background-color: #f2f2f2;">';
        $html .= '      <td colspan="3" style="text-align: right;">Jumlah</td>';
        foreach ($leger['exams'] as $exam) {
            $html .= '      <td>' . $subjectStats[$exam['exam_id']]['sum'] . '</td>';
        }
        // Class average spanned box
        $html .= '      <td colspan="9" rowspan="4" style="background-color: #ffffff; text-align: center; vertical-align: middle;">';
        $html .= '        <div style="font-size: 8pt; color: #777777; font-weight: bold;">RATA-RATA KELAS</div>';
        $html .= '        <div style="font-size: 16pt; font-weight: bold; color: #4f46e5; margin-top: 4px;">' . number_format($classOverallAvg, 2) . '</div>';
        $html .= '      </td>';
        $html .= '    </tr>';

        // Row 2: Nilai Minimal
        $html .= '    <tr style="font-weight: bold; background-color: #f2f2f2;">';
        $html .= '      <td colspan="3" style="text-align: right;">Nilai Minimal</td>';
        foreach ($leger['exams'] as $exam) {
            $html .= '      <td>' . $subjectStats[$exam['exam_id']]['min'] . '</td>';
        }
        $html .= '    </tr>';

        // Row 3: Nilai Maksimal
        $html .= '    <tr style="font-weight: bold; background-color: #f2f2f2;">';
        $html .= '      <td colspan="3" style="text-align: right;">Nilai Maksimal</td>';
        foreach ($leger['exams'] as $exam) {
            $html .= '      <td>' . $subjectStats[$exam['exam_id']]['max'] . '</td>';
        }
        $html .= '    </tr>';

        // Row 4: Rata-rata
        $html .= '    <tr style="font-weight: bold; background-color: #f2f2f2;">';
        $html .= '      <td colspan="3" style="text-align: right;">Rata-rata</td>';
        foreach ($leger['exams'] as $exam) {
            $avgVal = $subjectStats[$exam['exam_id']]['avg'] > 0 ? number_format($subjectStats[$exam['exam_id']]['avg'], 2) : '-';
            $html .= '      <td>' . $avgVal . '</td>';
        }
        $html .= '    </tr>';

        $html .= '  </tbody>';
        $html .= '</table>';

        // Signatures
        $totalCols = 12 + count($leger['exams']);
        $colSpanLeft = floor($totalCols / 3);
        $colSpanMiddle = floor($totalCols / 3);
        $colSpanRight = $totalCols - ($colSpanLeft + $colSpanMiddle);

        $html .= '<br/><br/>';
        $html .= '<table style="width: 100%; border: none; font-family: Arial, sans-serif; font-size: 10pt;">';
        $html .= '  <tr>';
        $html .= '    <td colspan="' . $colSpanLeft . '" style="border: none;">&nbsp;</td>';
        $html .= '    <td colspan="' . $colSpanMiddle . '" style="border: none; text-align: center; font-weight: bold;">Wali Kelas</td>';
        $html .= '    <td colspan="' . $colSpanRight . '" style="border: none; text-align: center; font-weight: bold;">Pimpinan Pondok Pesantren Darussalam Bogor</td>';
        $html .= '  </tr>';
        $html .= '  <tr><td colspan="' . $totalCols . '" style="border: none; height: 50px;">&nbsp;</td></tr>';
        $html .= '  <tr>';
        $html .= '    <td colspan="' . $colSpanLeft . '" style="border: none;">&nbsp;</td>';
        $html .= '    <td colspan="' . $colSpanMiddle . '" style="border: none; text-align: center; font-weight: bold; text-decoration: underline;">' . htmlspecialchars($kelas['wali_kelas'] ?? '') . '</td>';
        $html .= '    <td colspan="' . $colSpanRight . '" style="border: none; text-align: center; font-weight: bold; text-decoration: underline;">Kiai Muhammad Abu Jihad Lillah, S.H.I., M.Pd.</td>';
        $html .= '  </tr>';
        $html .= '</table>';

        $html .= '</body>';
        $html .= '</html>';

        fwrite($output, $html);
        fclose($output);
        exit;
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
