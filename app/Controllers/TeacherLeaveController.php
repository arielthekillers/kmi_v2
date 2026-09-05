<?php

namespace App\Controllers;

use App\Models\TeacherLeaveModel;
use App\Models\TeachingSubstitutionModel;
use App\Models\TeacherAssistantModel;
use App\Models\TeacherModel;
use App\Models\ScheduleModel;
use App\Models\SubjectModel;
use App\Models\TanqihModel;
use PDO;
use App\Core\Controller;

class TeacherLeaveController extends Controller {
    private $leaveModel;
    private $subModel;
    private $assistantModel;
    private $teacherModel;
    private $scheduleModel;
    private $tanqihModel;

    public function __construct() {
        require_login();
        if (auth_get_role() !== 'admin' && !auth_is_pbm()) {
            $this->redirect('/');
        }
        $this->leaveModel = new TeacherLeaveModel();
        $this->subModel = new TeachingSubstitutionModel();
        $this->assistantModel = new TeacherAssistantModel();
        $this->teacherModel = new TeacherModel();
        $this->scheduleModel = new ScheduleModel();
        $this->tanqihModel = new TanqihModel();
    }

    public function index() {
        $academicYearId = $this->leaveModel->getAcademicYearId();
        
        $tab = $_GET['tab'] ?? 'leaves';
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $selectedYearVal = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        
        $data = [];
        $monthLeaves = [];

        if ($tab === 'substitutes') {
            $stmt = $this->subModel->query("
                SELECT ts.substitute_teacher_id, u.nama as substitute_name, COUNT(*) as total_jam,
                       GROUP_CONCAT(CONCAT(ts.hour, '|', sub.nama, '|', k.tingkat, k.abjad) SEPARATOR '||') as details
                FROM teaching_substitutions ts
                JOIN teacher_leaves l ON ts.leave_id = l.id
                JOIN users u ON ts.substitute_teacher_id = u.id
                JOIN subjects sub ON ts.subject_id = sub.id
                JOIN kelas k ON ts.kelas_id = k.id
                WHERE l.date = ? AND l.academic_year_id = ? AND ts.substitute_teacher_id IS NOT NULL
                GROUP BY ts.substitute_teacher_id, u.nama
                ORDER BY u.nama ASC
            ", [$date, $academicYearId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $startDate = sprintf('%04d-%02d-01', $selectedYearVal, $selectedMonth);
            $endDate = date('Y-m-t', strtotime($startDate));

            $stmt = $this->leaveModel->query("
                SELECT l.*, u.nama as teacher_name,
                       (SELECT COUNT(*) FROM teaching_substitutions ts WHERE ts.leave_id = l.id AND ts.substitute_teacher_id IS NULL) as empty_slots,
                       (SELECT COUNT(*) FROM teaching_substitutions ts WHERE ts.leave_id = l.id) as total_slots
                FROM teacher_leaves l 
                JOIN users u ON l.teacher_id = u.id 
                WHERE l.academic_year_id = ? AND l.date >= ? AND l.date <= ?
                ORDER BY l.date ASC, u.nama ASC
            ", [$academicYearId, $startDate, $endDate]);
            $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($leaves)) {
                $leaveIds = array_column($leaves, 'id');
                $inQuery = implode(',', array_fill(0, count($leaveIds), '?'));
                $stmtSubs = $this->leaveModel->query("
                    SELECT ts.*, sub.nama as subject_name, k.tingkat, k.abjad, u.nama as sub_name
                    FROM teaching_substitutions ts
                    LEFT JOIN subjects sub ON ts.subject_id = sub.id
                    LEFT JOIN kelas k ON ts.kelas_id = k.id
                    LEFT JOIN users u ON ts.substitute_teacher_id = u.id
                    WHERE ts.leave_id IN ($inQuery)
                    ORDER BY ts.hour ASC
                ", $leaveIds);
                $allSubs = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);
                
                $subsByLeave = [];
                foreach ($allSubs as $sub) {
                    $subsByLeave[$sub['leave_id']][] = $sub;
                }
                
                foreach ($leaves as &$leave) {
                    $leave['details'] = $subsByLeave[$leave['id']] ?? [];
                }
                unset($leave); // break reference
            }
            
            foreach ($leaves as $leave) {
                $monthLeaves[$leave['date']][] = $leave;
            }
        }
        
        // Calendar metadata
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYearVal);
        $firstDayOfMonth = sprintf('%04d-%02d-01', $selectedYearVal, $selectedMonth);
        $firstDayOfWeek = date('w', strtotime($firstDayOfMonth)); // 0 = Sunday, 6 = Saturday
        // Pesantren week starts on Saturday (Sabtu = 0, Minggu = 1, Senin = 2, ..., Jumat = 6)
        $firstDayOffset = ($firstDayOfWeek + 1) % 7;
        
        $prevMonth = $selectedMonth - 1;
        $prevYear = $selectedYearVal;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $nextMonth = $selectedMonth + 1;
        $nextYear = $selectedYearVal;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }
        $teachers = $this->teacherModel->getAll();

        // Build list of dates in the month that have NO KBM at all (Friday or full-day dispensation for all classes)
        $noKbmDates = [];
        $kelasModel = new \App\Models\KelasModel();
        $totalActiveKelas = count($kelasModel->findAllActive());
        $activityModel = new \App\Models\ActivityModel();
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDateStr = sprintf('%04d-%02d-%02d', $selectedYearVal, $selectedMonth, $day);
            $timestamp = strtotime($currentDateStr);
            
            // Friday is always weekly holiday (no KBM)
            if (date('w', $timestamp) == 5) {
                $noKbmDates[$currentDateStr] = 'Libur Mingguan (Jumat)';
                continue;
            }
            
            // Check full day dispensations for all classes
            $disps = $activityModel->getDispensationsByDate($currentDateStr);
            foreach ($disps as $disp) {
                if ($disp['is_full_day'] && count($disp['kelas_ids']) >= $totalActiveKelas) {
                    $noKbmDates[$currentDateStr] = $disp['name'];
                    break;
                }
            }
        }
        
        $this->view('layouts/header', ['title' => 'Izin Mengajar']);
        $this->view('leaves/index', [
            'tab' => $tab,
            'date' => $date,
            'data' => $data,
            'monthLeaves' => $monthLeaves,
            'selectedMonth' => $selectedMonth,
            'selectedYearVal' => $selectedYearVal,
            'daysInMonth' => $daysInMonth,
            'firstDayOffset' => $firstDayOffset,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'teachers' => $teachers,
            'noKbmDates' => $noKbmDates
        ]);
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $id = $_POST['id'] ?? '';
        if ($id) {
            $stmt = $this->subModel->query("
                SELECT ts.hour, ts.kelas_id, l.date, l.academic_year_id 
                FROM teaching_substitutions ts
                JOIN teacher_leaves l ON ts.leave_id = l.id
                WHERE ts.leave_id = ? AND ts.substitute_teacher_id IS NOT NULL
            ", [$id]);
            $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($subs as $sub) {
                $this->tanqihModel->query("DELETE FROM tanqih WHERE date = ? AND kelas_id = ? AND hour = ? AND academic_year_id = ? AND status = 'justified'", [$sub['date'], $sub['kelas_id'], $sub['hour'], $sub['academic_year_id']]);
            }

            $this->subModel->query("DELETE FROM teaching_substitutions WHERE leave_id = ?", [$id]);
            $this->leaveModel->query("DELETE FROM teacher_leaves WHERE id = ?", [$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data berhasil dihapus.'];
        }
        
        $redirectUrl = '/leaves';
        if (!empty($_POST['date'])) {
            $dateParts = explode('-', $_POST['date']);
            if (count($dateParts) >= 2) {
                $redirectUrl .= '?month=' . $dateParts[1] . '&year=' . $dateParts[0];
            }
        }
        
        $this->redirect($redirectUrl);
    }



    public function getSchedules() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $datesStr = $_POST['date'] ?? '';
        $teacherId = $_POST['teacher_id'] ?? '';
        
        if (empty($datesStr) || empty($teacherId)) {
            echo json_encode([]);
            exit;
        }

        $dates = array_map('trim', explode(',', $datesStr));
        $daysIndo = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $result = [];

        foreach ($dates as $date) {
            if (empty($date)) continue;
            
            $timestamp = strtotime($date);
            $dayOfWeek = date('w', $timestamp);
            $dayName = $daysIndo[$dayOfWeek];

            // Get schedules for this teacher on this day
            $stmt = $this->scheduleModel->query("
                SELECT s.*, k.tingkat, k.abjad, sub.nama as subject_name 
                FROM schedules s
                JOIN kelas k ON s.kelas_id = k.id
                JOIN subjects sub ON s.subject_id = sub.id
                WHERE s.teacher_id = ? AND s.day = ? AND s.academic_year_id = ?
                ORDER BY s.hour ASC
            ", [$teacherId, $dayName, $this->scheduleModel->getAcademicYearId()]);
            
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($schedules)) {
                $result[$date] = [
                    'dayName' => $dayName,
                    'schedules' => []
                ];
                
                // Group by hour for easy matrix rendering
                foreach ($schedules as $sch) {
                    $result[$date]['schedules'][$sch['hour']] = $sch;
                }
            }
        }

        echo json_encode($result);
        exit;
    }

    public function storeAjax() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $teacherId = $_POST['teacher_id'] ?? '';
        $startDate = $_POST['tanggal_mulai'] ?? '';
        $endDate = $_POST['tanggal_selesai'] ?? $startDate;
        $type = $_POST['type'] ?? 'izin';
        
        if (empty($teacherId) || empty($startDate)) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }
        if (empty($endDate)) {
            $endDate = $startDate;
        }
        
        $dates = [];
        $current = $startDate;
        while ($current <= $endDate) {
            $dates[] = $current;
            $current = date('Y-m-d', strtotime($current . ' +1 day'));
        }
        
        $daysIndo = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $academicYearId = $this->leaveModel->getAcademicYearId();
        $inserted = 0;
        
        foreach ($dates as $date) {
            $timestamp = strtotime($date);
            $dayOfWeek = (int)date('w', $timestamp);
            $dayName = $daysIndo[$dayOfWeek];
            
            // Check if teacher has active schedules on this day
            $stmtSch = $this->scheduleModel->query("
                SELECT s.hour, s.kelas_id, s.subject_id 
                FROM schedules s
                INNER JOIN (
                    SELECT MAX(id) as max_id 
                    FROM schedules 
                    WHERE academic_year_id = ? 
                    GROUP BY kelas_id, day, hour
                ) latest ON s.id = latest.max_id
                WHERE s.teacher_id = ? AND s.day = ?
            ", [$academicYearId, $teacherId, $dayName]);
            $schedules = $stmtSch->fetchAll(PDO::FETCH_ASSOC);
            if (empty($schedules)) continue;
            
            $activityModel = new \App\Models\ActivityModel();
            $effectiveSchedules = [];
            foreach ($schedules as $sch) {
                $eff = $activityModel->getEffectiveSchedule($date, $sch['kelas_id'], $sch['hour']);
                if (!$eff['affected']) {
                    $effectiveSchedules[] = $sch;
                }
            }
            if (empty($effectiveSchedules)) continue;
            
            // Check if leave already exists
            $stmt = $this->leaveModel->query("SELECT id FROM teacher_leaves WHERE teacher_id = ? AND date = ?", [$teacherId, $date]);
            if ($stmt->fetch()) continue;
            
            $leaveId = $this->leaveModel->create([
                'date' => $date,
                'teacher_id' => $teacherId,
                'type' => $type,
                'created_by' => $_SESSION['user_id'] ?? null,
                'status' => 'published',
                'academic_year_id' => $academicYearId
            ]);
            
            $substitutions = [];
            foreach ($effectiveSchedules as $sch) {
                // Determine default substitute if an assistant is set
                $assistantId = $this->assistantModel->getAssistantForSubject($teacherId, $sch['subject_id'], $sch['kelas_id'], $academicYearId);
                
                if ($assistantId) {
                    // Check if assistant has a regular active schedule at this hour
                    $stmtBusy1 = $this->scheduleModel->query("
                        SELECT 1 
                        FROM schedules s
                        INNER JOIN (
                            SELECT MAX(id) as max_id 
                            FROM schedules 
                            WHERE academic_year_id = ? 
                            GROUP BY kelas_id, day, hour
                        ) latest ON s.id = latest.max_id
                        WHERE s.teacher_id = ? AND s.day = ? AND s.hour = ?
                    ", [$academicYearId, $assistantId, $dayName, $sch['hour']]);
                    
                    // Check if assistant is already substituting at this hour
                    $stmtBusy2 = $this->subModel->query("
                        SELECT 1 
                        FROM teaching_substitutions ts
                        JOIN teacher_leaves tl ON ts.leave_id = tl.id
                        WHERE ts.substitute_teacher_id = ? AND tl.date = ? AND ts.hour = ?
                    ", [$assistantId, $date, $sch['hour']]);
                    
                    if ($stmtBusy1->fetchColumn() || $stmtBusy2->fetchColumn()) {
                        $assistantId = null; // Conflict found, do not auto-assign
                    }
                }
                
                $substitutions[] = [
                    'hour' => $sch['hour'],
                    'kelas_id' => $sch['kelas_id'],
                    'subject_id' => $sch['subject_id'],
                    'substitute_teacher_id' => $assistantId ?: null,
                    'note' => ''
                ];
            }
            if (!empty($substitutions)) {
                $this->subModel->createBatch($leaveId, $substitutions);
                foreach ($substitutions as $sub) {
                    if ($sub['substitute_teacher_id']) {
                        $this->tanqihModel->verify($date, $sub['kelas_id'], $sub['hour'], $_SESSION['user_id'] ?? null, 'justified');
                    }
                }
            }
            $inserted++;
        }
        
        if ($inserted == 0) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada jadwal mengajar pada tanggal yang dipilih.']);
            exit;
        }
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    public function getDailySubstitutes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $date = $_POST['date'] ?? '';
        if (!$date) {
            echo json_encode(['success' => false, 'message' => 'Tanggal tidak ditemukan']);
            exit;
        }
        
        $academicYearId = $this->leaveModel->getAcademicYearId();
        
        $stmt = $this->subModel->query("
            SELECT ts.substitute_teacher_id, u.nama as substitute_name, COUNT(*) as total_jam,
                   GROUP_CONCAT(CONCAT(ts.hour, '|', sub.nama, '|', k.tingkat, k.abjad) SEPARATOR '||') as details
            FROM teaching_substitutions ts
            JOIN teacher_leaves l ON ts.leave_id = l.id
            JOIN users u ON ts.substitute_teacher_id = u.id
            JOIN subjects sub ON ts.subject_id = sub.id
            JOIN kelas k ON ts.kelas_id = k.id
            WHERE l.date = ? AND l.academic_year_id = ? AND ts.substitute_teacher_id IS NOT NULL
            GROUP BY ts.substitute_teacher_id, u.nama
            ORDER BY u.nama ASC
        ", [$date, $academicYearId]);
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public function getLeaveDetails() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $leaveId = $_POST['id'] ?? '';
        if (!$leaveId) {
            echo json_encode(['success' => false, 'message' => 'ID Izin tidak ditemukan']);
            exit;
        }
        
        $leave = $this->leaveModel->findWithDetails($leaveId);
        if (!$leave) {
            echo json_encode(['success' => false, 'message' => 'Data Izin tidak ditemukan']);
            exit;
        }
        
        $date = $leave['date'];
        $teacherId = $leave['teacher_id'];
        
        $timestamp = strtotime($date);
        $daysIndo = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayName = $daysIndo[date('w', $timestamp)];
        
        $stmt = $this->scheduleModel->query("
            SELECT s.*, k.tingkat, k.abjad, sub.nama as subject_name 
            FROM schedules s
            JOIN kelas k ON s.kelas_id = k.id
            JOIN subjects sub ON s.subject_id = sub.id
            WHERE s.teacher_id = ? AND s.day = ? AND s.academic_year_id = ?
            ORDER BY s.hour ASC
        ", [$teacherId, $dayName, $this->scheduleModel->getAcademicYearId()]);
        
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $activityModel = new \App\Models\ActivityModel();
        $effectiveSchedules = [];
        foreach ($schedules as $sch) {
            $eff = $activityModel->getEffectiveSchedule($date, $sch['kelas_id'], $sch['hour']);
            if (!$eff['affected']) {
                $effectiveSchedules[] = $sch;
            }
        }
        $schedules = $effectiveSchedules;
        
        // Also fetch teacher name for the UI
        $stmt2 = $this->teacherModel->query("SELECT nama FROM users WHERE id = ?", [$teacherId]);
        $teacherName = $stmt2->fetchColumn();
        $leave['teacher_name'] = $teacherName;
        
        echo json_encode([
            'success' => true,
            'leave' => $leave,
            'schedules' => $schedules,
            'dayName' => $dayName
        ]);
        exit;
    }

    public function getRecommendations() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $date = $_POST['date'] ?? '';
        $hour = $_POST['hour'] ?? '';
        $subjectId = $_POST['subject_id'] ?? '';
        $kelasId = $_POST['kelas_id'] ?? '';
        $absentTeacherId = $_POST['teacher_id'] ?? '';

        $recs = $this->leaveModel->getRecommendations($date, $hour, $subjectId, $kelasId, $absentTeacherId);
        echo json_encode($recs);
        exit;
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $teacherId = $_POST['teacher_id'] ?? '';
        $subs = $_POST['subs'] ?? []; // subs[date][hour][kelas_id, subject_id, substitute_teacher_id, note]

        if (empty($teacherId) || empty($subs)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak lengkap.'];
            $this->redirect('/leaves/create');
        }

        $editLeaveId = $_POST['edit_leave_id'] ?? '';
        $substituteIds = [];
        
        $redirectDate = '';
        foreach ($subs as $date => $dailySubs) {
            if (!$redirectDate) $redirectDate = $date;
            $hasLeave = false;
            foreach ($dailySubs as $hour => $data) {
                if ($data['substitute_teacher_id'] !== 'self') {
                    $hasLeave = true;
                    break;
                }
            }
            
            if (!$hasLeave) continue; // Skip dates with no leave assigned
            
            if ($editLeaveId) {
                $leaveId = $editLeaveId;
                
                $stmt = $this->subModel->query("
                    SELECT ts.hour, ts.kelas_id, l.date, l.academic_year_id 
                    FROM teaching_substitutions ts
                    JOIN teacher_leaves l ON ts.leave_id = l.id
                    WHERE ts.leave_id = ? AND ts.substitute_teacher_id IS NOT NULL
                ", [$leaveId]);
                $oldSubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($oldSubs as $sub) {
                    $this->tanqihModel->query("DELETE FROM tanqih WHERE date = ? AND kelas_id = ? AND hour = ? AND academic_year_id = ? AND status = 'justified'", [$sub['date'], $sub['kelas_id'], $sub['hour'], $sub['academic_year_id']]);
                }

                $this->subModel->query("DELETE FROM teaching_substitutions WHERE leave_id = ?", [$leaveId]);
            } else {
                $leaveId = $this->leaveModel->create([
                    'date' => $date,
                    'teacher_id' => $teacherId,
                    'created_by' => $_SESSION['user_id'] ?? null,
                    'status' => 'published'
                ]);
            }

            $substitutions = [];
            foreach ($dailySubs as $hour => $data) {
                if ($data['substitute_teacher_id'] !== 'self') {
                    $subId = $data['substitute_teacher_id'] === '' ? null : $data['substitute_teacher_id'];
                    $substitutions[] = [
                        'hour' => $hour,
                        'kelas_id' => $data['kelas_id'],
                        'subject_id' => $data['subject_id'],
                        'substitute_teacher_id' => $subId,
                        'note' => $data['note'] ?? ''
                    ];
                    if ($subId) {
                        $substituteIds[$subId][] = $date;
                    }
                }
            }

            if (!empty($substitutions)) {
                $this->subModel->createBatch($leaveId, $substitutions);
                foreach ($substitutions as $sub) {
                    if ($sub['substitute_teacher_id']) {
                        $this->tanqihModel->verify($date, $sub['kelas_id'], $sub['hour'], $_SESSION['user_id'] ?? null, 'justified');
                    }
                }
            }
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Izin mengajar berhasil disimpan.'];
        if ($redirectDate) {
            $parts = explode('-', $redirectDate);
            if (count($parts) >= 2) {
                $this->redirect('/leaves?month=' . $parts[1] . '&year=' . $parts[0]);
            } else {
                $this->redirect('/leaves');
            }
        } else {
            $this->redirect('/leaves');
        }
    }



    public function assistants() {
        $academicYearId = $this->leaveModel->getAcademicYearId();
        $assistants = $this->assistantModel->getAll($academicYearId);
        $teachers = $this->teacherModel->getAll();
        
        $subjectModel = new SubjectModel();
        $stmt = $subjectModel->query("SELECT * FROM subjects ORDER BY nama ASC");
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('layouts/header', ['title' => 'Asisten Pengajar Tetap']);
        $this->view('leaves/assistants', [
            'assistants' => $assistants,
            'teachers' => $teachers,
            'subjects' => $subjects
        ]);
    }

    public function storeAssistant() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $academicYearId = $this->leaveModel->getAcademicYearId();
        
        $subjectId = null;
        $kelasId = null;
        
        if (!empty($_POST['subject_id'])) {
            $parts = explode('|', $_POST['subject_id']);
            if (count($parts) === 2) {
                $subjectId = $parts[0];
                $kelasId = $parts[1];
            } else {
                $subjectId = $_POST['subject_id'];
            }
        }
        
        $this->assistantModel->create([
            'academic_year_id' => $academicYearId,
            'teacher_id' => $_POST['teacher_id'],
            'assistant_id' => $_POST['assistant_id'],
            'subject_id' => $subjectId,
            'kelas_id' => $kelasId
        ]);
        
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Asisten berhasil ditambahkan.'];
        redirect('/leaves/assistants');
    }

    public function getTeacherSubjects() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $teacherId = $_POST['teacher_id'] ?? '';
        if (!$teacherId) {
            echo json_encode([]);
            exit;
        }

        $academicYearId = $this->leaveModel->getAcademicYearId();
        
        $stmt = $this->scheduleModel->query("
            SELECT DISTINCT s.subject_id, s.kelas_id, sub.nama as subject_name, CONCAT(k.tingkat, '-', k.abjad, ' ', k.gender) as kelas_name
            FROM schedules s
            JOIN subjects sub ON s.subject_id = sub.id
            JOIN kelas k ON s.kelas_id = k.id
            WHERE s.teacher_id = ? AND s.academic_year_id = ?
            ORDER BY sub.nama ASC, k.tingkat ASC, k.abjad ASC, k.gender ASC
        ", [$teacherId, $academicYearId]);
        
        $assignments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($assignments as $a) {
            $result[] = [
                'id' => $a['subject_id'] . '|' . $a['kelas_id'],
                'nama' => $a['subject_name'] . ' - Kelas ' . $a['kelas_name']
            ];
        }
        
        echo json_encode($result);
        exit;
    }

    public function deleteAssistant() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $this->assistantModel->delete($_POST['id']);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Asisten berhasil dihapus.'];
        redirect('/leaves/assistants');
    }

    public function printSubstitute() {
        $date = $_GET['date'] ?? '';
        $subId = $_GET['sub_id'] ?? '';
        if (!$date) {
            die("Data tanggal tidak lengkap.");
        }
        
        $academicYearId = $this->leaveModel->getAcademicYearId();
        
        $whereSub = $subId ? "AND ts.substitute_teacher_id = ?" : "AND ts.substitute_teacher_id IS NOT NULL AND ts.substitute_teacher_id != ''";
        $params = $subId ? [$date, $academicYearId, $subId] : [$date, $academicYearId];
        
        $stmt = $this->subModel->query("
            SELECT ts.hour, k.tingkat, k.abjad, sub.nama as subject_name, 
                   u_asli.nama as original_teacher_name, ts.note,
                   u_sub.nama as substitute_name, tp_sub.gender as sub_gender
            FROM teaching_substitutions ts
            JOIN teacher_leaves l ON ts.leave_id = l.id
            JOIN kelas k ON ts.kelas_id = k.id
            JOIN subjects sub ON ts.subject_id = sub.id
            JOIN users u_asli ON l.teacher_id = u_asli.id
            JOIN users u_sub ON ts.substitute_teacher_id = u_sub.id
            LEFT JOIN teacher_profiles tp_sub ON u_sub.id = tp_sub.user_id
            WHERE l.date = ? AND l.academic_year_id = ? $whereSub
            ORDER BY u_sub.nama ASC, ts.hour ASC
        ", $params);
        
        $allSchedules = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($allSchedules)) {
            die("Tidak ada jadwal yang digantikan pada tanggal tersebut.");
        }
        
        $grouped = [];
        foreach ($allSchedules as $sch) {
            $grouped[$sch['substitute_name']]['gender'] = $sch['sub_gender'];
            $grouped[$sch['substitute_name']]['schedules'][] = $sch;
        }
        
        $this->view('leaves/print_substitute', [
            'date' => $date,
            'groupedSchedules' => $grouped
        ]);
    }

    public function statistics() {
        $filter = $_GET['filter'] ?? 'month';
        $academicYearId = $this->leaveModel->getAcademicYearId();
        
        $stats = $this->leaveModel->getStatistics($filter, $academicYearId);
        
        $this->view('layouts/header', ['title' => 'Statistik & Evaluasi Izin']);
        $this->view('leaves/statistics', [
            'filter' => $filter,
            'stats' => $stats
        ]);
    }

    public function statisticsDetails() {
        header('Content-Type: application/json');
        
        $teacherId = $_POST['teacher_id'] ?? null;
        $type = $_POST['type'] ?? null;
        $filter = $_POST['filter'] ?? 'month';
        
        if (!$teacherId || !$type) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }
        
        $academicYearId = $this->leaveModel->getAcademicYearId();
        
        $details = [];
        if ($type === 'absentee') {
            $details = $this->leaveModel->getTeacherLeaveDetails($teacherId, $filter, $academicYearId);
        } elseif ($type === 'substitute') {
            $details = $this->leaveModel->getTeacherSubstitutionDetails($teacherId, $filter, $academicYearId);
        }
        
        echo json_encode(['success' => true, 'data' => $details]);
    }

    public function statisticsFullList() {
        header('Content-Type: application/json');
        
        $type = $_POST['type'] ?? null;
        $filter = $_POST['filter'] ?? 'month';
        
        if (!$type) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }
        
        $academicYearId = $this->leaveModel->getAcademicYearId();
        $data = $this->leaveModel->getFullStatisticsList($type, $filter, $academicYearId);
        
        echo json_encode(['success' => true, 'data' => $data]);
    }
}
