<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AttendanceModel;
use App\Models\StudentAttendanceModel;
use App\Models\TanqihModel;
use App\Models\KelasModel;
use App\Models\TeacherLeaveModel;
use App\Models\AcademicYearModel;

class WeeklyReportController extends Controller {
    public function index() {
        if (!is_logged_in() || (auth_get_role() !== 'admin' && !auth_is_pbm())) {
            redirect('/?error=Unauthorized');
        }

        $today = date('Y-m-d');
        // If today is Friday or Saturday, maybe the last week's report is wanted. Let's just default to last Thursday
        $thursday = date('Y-m-d', strtotime('last Thursday', strtotime($today . ' +1 day')));
        $saturday = date('Y-m-d', strtotime('-5 days', strtotime($thursday)));
        
        $this->view('weekly_report/index', [
            'default_start' => $saturday,
            'default_end' => $thursday,
            'title' => 'Laporan Mingguan'
        ]);
    }

    public function printTeacherAttendance() {
        if (!is_logged_in() || (auth_get_role() !== 'admin' && !auth_is_pbm())) {
            die('Unauthorized');
        }

        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        if (!$start || !$end) die('Periode tidak valid');

        $attendanceModel = new AttendanceModel();
        // We need to build the report for teachers.
        // It's better to fetch from AttendanceModel using getReportStats, then aggregate.
        $rawLogs = $attendanceModel->getReportStats($start, $end);
        
        // However, we also need expected hours (Jumlah Jam Mengajar).
        // Since we don't have a direct query for weekly expected hours, we can infer from the schedules table.
        // I will write a custom query in the controller to get expected schedules per teacher for the given days.
        $db = \App\Core\Database::getInstance()->getConnection();
        $ayId = $_SESSION['academic_year_id'] ?? null;
        
        $schedulesSql = "SELECT s.*, u.nama as teacher_nama FROM schedules s JOIN users u ON s.teacher_id = u.id WHERE s.academic_year_id = ?";
        $stmt = $db->prepare($schedulesSql);
        $stmt->execute([$ayId]);
        $schedules = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Map English day to Indonesian
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];

        // Determine which days are in the range
        $daysInRange = [];
        $currentDate = $start;
        while (strtotime($currentDate) <= strtotime($end)) {
            $dayNameEnglish = date('D', strtotime($currentDate));
            $daysInRange[] = [
                'date' => $currentDate,
                'dayName' => $dayMap[$dayNameEnglish] ?? ''
            ];
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        $report = [];
        $substitutions = []; // For top substitute teachers
        
        // Init expected
        foreach ($schedules as $sched) {
            $tid = $sched['teacher_id'];
            $tname = $sched['teacher_nama'];
            if (!$tid) continue;
            
            if (!isset($report[$tid])) {
                $report[$tid] = [
                    'nama' => $tname,
                    'expected' => 0,
                    'hadir' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'alfa' => 0
                ];
            }
            
            // Check if this schedule falls on a day in our range
            foreach ($daysInRange as $dayObj) {
                if ($sched['day'] === $dayObj['dayName']) {
                    $report[$tid]['expected']++;
                }
            }
        }

        // Now process raw logs (actual attendance)
        foreach ($rawLogs as $log) {
            $tid = $log['teacher_id'];
            if (!$tid) continue;
            
            // If the teacher has no expected schedule (maybe old data or badal for someone else? Wait, badal is substitute_teacher_id)
            if (!isset($report[$tid])) {
                $report[$tid] = [
                    'nama' => $log['teacher_nama'],
                    'expected' => 0,
                    'hadir' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'alfa' => 0
                ];
            }

            if ($log['status'] === 'hadir') {
                $report[$tid]['hadir']++;
            } elseif ($log['status'] === 'substitute') {
                // The original teacher is replaced. Is it considered Izin or Sakit?
                // The note usually contains "Izin" or "Sakit" if they used TeacherLeave.
                // We should check TeacherLeaveModel or just default to Izin if they are substituted.
                // Wait, if it's 'substitute', the teacher is absent. 
                // Let's assume it's Izin for now unless we can parse 'sakit' from note.
                if (stripos($log['note'], 'sakit') !== false) {
                    $report[$tid]['sakit']++;
                } else {
                    $report[$tid]['izin']++;
                }
                
                // Track substitute stats
                $subId = $log['substitute_teacher_id'];
                if ($subId) {
                    if (!isset($substitutions[$subId])) {
                        $substitutions[$subId] = ['nama' => $log['subst_nama'], 'count' => 0];
                    }
                    $substitutions[$subId]['count']++;
                }
            } elseif ($log['status'] === 'alpha') {
                $report[$tid]['alfa']++;
            }
        }

        // Calculate %
        foreach ($report as $tid => &$data) {
            if ($data['expected'] > 0) {
                // Some alpha might be because they didn't log.
                // Wait, if expected > 0 and no log exists, it means they haven't been logged yet.
                // Should we assume ALFA for missing logs in the past date range?
                // The system has attendance_logs. If it's missing, it's missing. We can calculate Alfa = Expected - (Hadir + Sakit + Izin).
                // Actually, let's just use:
                $data['alfa'] = max(0, $data['expected'] - ($data['hadir'] + $data['sakit'] + $data['izin']));
                
                $data['pct_s'] = round(($data['sakit'] / $data['expected']) * 100, 2);
                $data['pct_i'] = round(($data['izin'] / $data['expected']) * 100, 2);
                $data['pct_a'] = round(($data['alfa'] / $data['expected']) * 100, 2);
                $data['pct_hadir'] = round(($data['hadir'] / $data['expected']) * 100, 2);
            } else {
                $data['pct_s'] = $data['pct_i'] = $data['pct_a'] = $data['pct_hadir'] = 0;
            }
        }
        
        // Remove teachers with 0 expected if they don't teach this week
        $report = array_filter($report, fn($r) => $r['expected'] > 0);

        // Sort by nama
        usort($report, fn($a, $b) => strcmp($a['nama'], $b['nama']));

        // Sort substitutions by count descending
        usort($substitutions, fn($a, $b) => $b['count'] <=> $a['count']);
        
        // Keep top 10 substitutions
        $topSubstitutions = array_slice($substitutions, 0, 10);

        $this->view('weekly_report/print_teacher', [
            'start' => $start,
            'end' => $end,
            'report' => $report,
            'topSubstitutions' => $topSubstitutions,
            'title' => 'Laporan Mingguan Kehadiran Guru'
        ]);
    }

    public function printStudentAttendance() {
        if (!is_logged_in() || (auth_get_role() !== 'admin' && !auth_is_pbm())) {
            die('Unauthorized');
        }

        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        if (!$start || !$end) die('Periode tidak valid');

        $ayId = $_SESSION['academic_year_id'] ?? null;
        $db = \App\Core\Database::getInstance()->getConnection();

        // Get all classes
        $kelasStmt = $db->prepare("SELECT * FROM kelas WHERE academic_year_id = ? ORDER BY tingkat ASC, abjad ASC");
        $kelasStmt->execute([$ayId]);
        $kelasList = $kelasStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get student count per class
        $enrStmt = $db->prepare("SELECT kelas_id, COUNT(student_id) as total FROM student_enrollments WHERE academic_year_id = ? AND status IN ('Active', 'Graduated') GROUP BY kelas_id");
        $enrStmt->execute([$ayId]);
        $enrData = $enrStmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Get absences per class in range
        $absStmt = $db->prepare("
            SELECT kelas_id, type, COUNT(student_id) as total
            FROM student_absences 
            WHERE date BETWEEN ? AND ?
            GROUP BY kelas_id, type
        ");
        $absStmt->execute([$start, $end]);
        $absRaw = $absStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $absences = [];
        foreach ($absRaw as $row) {
            $absences[$row['kelas_id']][$row['type']] = $row['total'];
        }

        $hariEfektif = 6; // Hardcoded to 6 (Sat-Thu) as per user request

        $report = [];
        foreach ($kelasList as $k) {
            $kid = $k['id'];
            $jumlahSantri = $enrData[$kid] ?? 0;
            
            if ($jumlahSantri == 0) continue;

            $sakit = $absences[$kid]['sakit'] ?? 0;
            $izin = $absences[$kid]['izin'] ?? 0;
            $alfa = $absences[$kid]['alpha'] ?? 0;

            $totalStudentDays = $jumlahSantri * $hariEfektif;
            $totalAbsences = $sakit + $izin + $alfa;
            
            $pct_s = $totalStudentDays > 0 ? round(($sakit / $totalStudentDays) * 100, 2) : 0;
            $pct_i = $totalStudentDays > 0 ? round(($izin / $totalStudentDays) * 100, 2) : 0;
            $pct_a = $totalStudentDays > 0 ? round(($alfa / $totalStudentDays) * 100, 2) : 0;
            
            $pct_hadir = $totalStudentDays > 0 ? round((($totalStudentDays - $totalAbsences) / $totalStudentDays) * 100, 2) : 0;

            $report[] = [
                'kelas' => $k['tingkat'] . ' ' . $k['abjad'],
                'hari_efektif' => $hariEfektif,
                'jumlah_santri' => $jumlahSantri,
                'alfa' => $alfa,
                'pct_a' => $pct_a,
                'izin' => $izin,
                'pct_i' => $pct_i,
                'sakit' => $sakit,
                'pct_s' => $pct_s,
                'pct_hadir' => $pct_hadir,
            ];
        }

        // Sort by class name naturally (e.g. 1 B, 1 C, 2 A)
        usort($report, function($a, $b) {
            // Extracts number and letter for better sorting
            preg_match('/(\d+)\s*(.*)/', $a['kelas'], $matchA);
            preg_match('/(\d+)\s*(.*)/', $b['kelas'], $matchB);
            
            $numA = isset($matchA[1]) ? (int)$matchA[1] : 0;
            $numB = isset($matchB[1]) ? (int)$matchB[1] : 0;
            
            if ($numA == $numB) {
                return strcmp(isset($matchA[2]) ? $matchA[2] : '', isset($matchB[2]) ? $matchB[2] : '');
            }
            return $numA <=> $numB;
        });

        // Get Top 3 Highest and Lowest
        $sortedByHadir = $report;
        usort($sortedByHadir, fn($a, $b) => $b['pct_hadir'] <=> $a['pct_hadir']);
        
        $top3 = array_slice($sortedByHadir, 0, 3);
        $bottom3 = array_slice(array_reverse($sortedByHadir), 0, 3);

        $this->view('weekly_report/print_student', [
            'start' => $start,
            'end' => $end,
            'report' => $report,
            'top3' => $top3,
            'bottom3' => $bottom3,
            'title' => 'Laporan Mingguan Kehadiran Santri'
        ]);
    }

    public function printTanqih() {
        if (!is_logged_in() || (auth_get_role() !== 'admin' && !auth_is_pbm())) {
            die('Unauthorized');
        }

        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        if (!$start || !$end) die('Periode tidak valid');

        $tanqihModel = new TanqihModel();
        $statsData = $tanqihModel->getReportStats($start, $end);
        $report = $statsData['report'];
        
        // Ensure justified is NOT counted towards verified_real
        // Wait, getReportStats already does this. It calculates pct based on verified_real.

        // Calculate pct for each and build array
        $finalReport = [];
        $stats = [
            '100' => 0,
            '76_99' => 0,
            '51_75' => 0,
            '26_50' => 0,
            '0_25' => 0
        ];

        foreach ($report as $r) {
            $pct = $r['expected'] > 0 ? round(($r['verified_real'] / $r['expected']) * 100, 2) : 0;
            
            $finalReport[] = [
                'nama' => $r['name'],
                'expected' => $r['expected'],
                'pct' => $pct
            ];

            // Calculate stats
            if ($pct == 100) $stats['100']++;
            elseif ($pct >= 76) $stats['76_99']++;
            elseif ($pct >= 51) $stats['51_75']++;
            elseif ($pct >= 26) $stats['26_50']++;
            else $stats['0_25']++;
        }

        usort($finalReport, function($a, $b) {
            if ($a['pct'] == $b['pct']) {
                return strcmp($a['nama'], $b['nama']);
            }
            return $b['pct'] <=> $a['pct']; // DESC
        });

        // Filter highest (100% or top 10)
        $highest = array_filter($finalReport, fn($r) => $r['pct'] == 100);
        if (empty($highest)) {
            $highest = array_slice($finalReport, 0, 10);
        }

        // Lowest (Top 10 lowest)
        $lowest = array_slice(array_reverse($finalReport), 0, 10);

        $this->view('weekly_report/print_tanqih', [
            'start' => $start,
            'end' => $end,
            'highest' => $highest,
            'lowest' => $lowest,
            'stats' => $stats,
            'title' => 'Laporan Mingguan Tanqih Idad'
        ]);
    }
}
