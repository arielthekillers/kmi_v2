<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ScheduleModel;
use App\Models\KelasModel;
use App\Models\SubjectModel;
use App\Models\TeacherModel;

class ScheduleController extends Controller {
    public function index() {
        require_admin(); // Ensure admin access

        $scheduleModel = new ScheduleModel();
        $kelasModel = new KelasModel();
        $subjectModel = new SubjectModel();
        $teacherModel = new TeacherModel();

        // Check if models exist, otherwise fallback (migration in progress)
        // Ideally we assume models exist. If not, we might need to query raw SQL 
        // but let's assume Migration 11, 12, 13 are done properly.

        // Fetch Data for Dropdowns
        // Classes
        $classes = $kelasModel->findAllActive();
        $kelasData = [];
        foreach ($classes as $c) {
            $kelasData[$c['id']] = $c;
        }
        // Sor Classes
         try {
            uasort($kelasData, function ($a, $b) {
                $t = strnatcmp($a['tingkat'] ?? '', $b['tingkat'] ?? '');
                if ($t === 0) return strnatcmp($a['abjad'] ?? '', $b['abjad'] ?? '');
                return $t;
            });
        } catch (\Throwable $e) {}


        // Subjects
        $subjects = $subjectModel->findAll();
        $pelajaranData = [];
        foreach ($subjects as $s) {
            $pelajaranData[$s['id']] = $s;
        }
        // Sort Subjects
        usort($subjects, function($a, $b) {
            return strnatcmp($a['nama'], $b['nama']);
        });

        // Teachers (Filter only pengajar and admin)
        // TeacherModel might return all, we filter manually or assume TeacherModel handles it?
        // TeacherModel currently returns findAll users. Let's filter in PHP for now to be safe or verify Model.
        // Looking at TeacherModel code (if I could see it), it likely returns all. 
        // Let's use custom query if needed, but for now let's query users table directly via Model query if available, 
        // BUT TeacherModel usually wraps `users` table.
        // Let's assume findAll returns all users and we filter role.
        $allTeachers = $teacherModel->findAll();
        $pengajarData = [];
        $teacherOptions = [];
        foreach ($allTeachers as $t) {
            if (in_array($t['role'], ['pengajar', 'admin'])) {
                // Keep all in pengajarData for historical display mapping
                $pengajarData[$t['id']] = $t;
                
                // Only active teachers should appear in dropdowns
                if (!isset($t['is_active']) || $t['is_active'] == 1) {
                    $teacherOptions[] = $t;
                }
            }
        }
        usort($teacherOptions, function($a, $b) {
            return strnatcmp($a['nama'], $b['nama']);
        });

        // Filter Logic
        $selectedKelasId = $_GET['kelas_id'] ?? '';
        $selectedPengajarId = $_GET['pengajar_id'] ?? '';

        if ($selectedPengajarId) {
            $selectedKelasId = '';
        }

        $viewTitle = '';
        $currentJadwal = [];
        $teacherSchedule = [];

        $primaryJadwal = [];
        $replacementJadwal = [];

        if ($selectedKelasId && isset($kelasData[$selectedKelasId])) {
            $k = $kelasData[$selectedKelasId];
            $viewTitle = 'Jadwal Kelas ' . $k['tingkat'] . '-' . $k['abjad'];
            $currentJadwal = $scheduleModel->getByClass($selectedKelasId);
            $primaryJadwal = $scheduleModel->getPrimaryScheduleByClass($selectedKelasId);
            $replacementJadwal = $scheduleModel->getReplacementScheduleByClass($selectedKelasId);
        }

        if ($selectedPengajarId && isset($pengajarData[$selectedPengajarId])) {
            $p = $pengajarData[$selectedPengajarId];
            $viewTitle = 'Jadwal Mengajar: ' . $p['nama'];
            $teacherSchedule = $scheduleModel->getByTeacher($selectedPengajarId);
        }

        $days = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $hours = range(1, 7);

        $this->view('schedule/index', [
            'kelasData' => $kelasData, // Associative for lookup
            'subjectOptions' => $subjects, // Sorted array for dropdowns
            'pelajaranData' => $pelajaranData, // Associative for lookup
            'teacherOptions' => $teacherOptions, // Sorted array for dropdowns
            'pengajarData' => $pengajarData, // Associative for lookup
            'selectedKelasId' => $selectedKelasId,
            'selectedPengajarId' => $selectedPengajarId,
            'viewTitle' => $viewTitle,
            'currentJadwal' => $currentJadwal,
            'primaryJadwal' => $primaryJadwal,
            'replacementJadwal' => $replacementJadwal,
            'teacherSchedule' => $teacherSchedule,
            'days' => $days,
            'hours' => $hours
        ]);
    }

    public function store() {
        require_admin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/schedule');
        }

        csrf_validate_token();

        $kelasId = $_POST['kelas_id'] ?? '';
        $mode = $_POST['mode'] ?? 'utama';
        $newSchedule = $_POST['schedule'] ?? [];

        if (empty($kelasId)) {
            add_flash('ID Kelas tidak ditemukan.', 'error');
            redirect('/schedule');
        }

        $model = new ScheduleModel();
        try {
            if ($mode === 'pengganti') {
                $model->updateBatchPengganti($kelasId, $newSchedule);
                add_flash('Jadwal pengganti berhasil ditambahkan.', 'success');
            } else {
                $model->updateBatchUtama($kelasId, $newSchedule);
                add_flash('Jadwal utama berhasil diperbarui.', 'success');
            }
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan jadwal: ' . $e->getMessage(), 'error');
        }

        redirect('/schedule?kelas_id=' . urlencode($kelasId));
    }

    public function mySchedule() {
        require_once __DIR__ . '/../../helpers/auth.php';
        require_login();
        
        $currentUserId = auth_get_user_id();
        if (!$currentUserId) {
            $this->redirect('/login');
        }

        $kelasModel = new KelasModel();
        $subjectModel = new SubjectModel();
        $scheduleModel = new ScheduleModel();

        $classes = $kelasModel->findAllActive();
        $kelasData = [];
        foreach ($classes as $c) {
            $kelasData[$c['id']] = $c;
        }

        $subjects = $subjectModel->findAll();
        $pelajaranData = [];
        foreach ($subjects as $s) {
            $pelajaranData[$s['id']] = $s;
        }

        $days = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $hours = range(1, 7);

        // getByTeacher() already returns a nested [$day][$hour] => ['mapel'=>..., 'kelas'=>...] array
        $mySchedule = $scheduleModel->getByTeacher($currentUserId);

        // Fetch teaching substitutions
        $stmt = $scheduleModel->query("
            SELECT sub.hour, tl.date, k.tingkat, k.abjad, s.nama as subject_name
            FROM teaching_substitutions sub
            JOIN teacher_leaves tl ON sub.leave_id = tl.id
            JOIN kelas k ON sub.kelas_id = k.id
            JOIN subjects s ON sub.subject_id = s.id
            WHERE sub.substitute_teacher_id = ? AND tl.status = 'published'
            AND tl.date >= CURRENT_DATE
        ", [$currentUserId]);
        $substitutions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('schedule/my_schedule', [
            'kelasData' => $kelasData,
            'pelajaranData' => $pelajaranData,
            'days' => $days,
            'hours' => $hours,
            'mySchedule' => $mySchedule,
            'substitutions' => $substitutions
        ]);
    }

    public function export() {
        require_admin();

        $format = $_GET['format'] ?? 'all'; // 'all', 'rekap', 'matriks'
        $teacherId = !empty($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : null;

        $scheduleModel = new ScheduleModel();
        $teacherScheduleData = $scheduleModel->getAllTeachersWithSchedule($this->currentYear['id'] ?? null, $teacherId);
        $academicYearName = $this->currentYear['name'] ?? '2026-2027';

        $timestamp = date('Ymd_His');
        $filename = "Jadwal_Pengajar_KMI_TA_" . preg_replace('/[^a-zA-Z0-9]/', '_', $academicYearName) . "_{$timestamp}.xls";

        log_activity("Mengekspor jadwal pengajar ke Excel (Format: {$format}, TA: {$academicYearName})");

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $days = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $hours = range(1, 7);

        $output = fopen('php://output', 'w');

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8">';
        $html .= '<style>';
        $html .= 'body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #1e293b; }';
        $html .= '.title-school { font-size: 14pt; font-weight: bold; text-align: center; text-transform: uppercase; color: #0f172a; }';
        $html .= '.title-sub { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; color: #1e3a8a; }';
        $html .= '.meta-info { font-size: 9pt; text-align: center; color: #64748b; margin-bottom: 20px; }';
        $html .= '.section-title { font-size: 11pt; font-weight: bold; background-color: #e2e8f0; color: #0f172a; padding: 8px 12px; border: 1px solid #94a3b8; }';
        $html .= '.data-table { border-collapse: collapse; width: 100%; margin-bottom: 30px; font-size: 10pt; }';
        $html .= '.data-table th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; text-align: center; vertical-align: middle; padding: 8px; border: 1px solid #64748b; }';
        $html .= '.data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: top; }';
        $html .= '.text-center { text-align: center; }';
        $html .= '.text-left { text-align: left; }';
        $html .= '.text-right { text-align: right; }';
        $html .= '.font-bold { font-weight: bold; }';
        $html .= '.number-text { mso-number-format: "\@"; text-align: center; }';
        $html .= '.total-row { background-color: #f1f5f9; font-weight: bold; }';
        $html .= '.bg-slot-active { background-color: #ecfdf5; border: 1px solid #a7f3d0; text-align: center; }';
        $html .= '.bg-slot-empty { color: #94a3b8; text-align: center; }';
        $html .= '.jp-pill { font-weight: bold; color: #1d4ed8; }';
        $html .= '</style>';
        $html .= '</head><body>';

        // Official Header
        $html .= '<div class="title-school">PONDOK MODERN DARUSSALAM BOGOR</div>';
        $html .= '<div class="title-sub">KULLIYYATUL MU\'ALLIMIN AL-ISLAMIYYAH (KMI)</div>';
        $html .= '<div class="title-sub" style="font-size: 11pt; color: #334155;">DAFTAR PENGAJAR DAN JADWAL MENGAJAR</div>';
        $html .= '<div class="meta-info">Tahun Ajaran: <b>' . htmlspecialchars($academicYearName) . '</b> | Diunduh: ' . date('d F Y, H:i') . ' WIB</div>';

        $totalTeachers = count($teacherScheduleData);
        $totalTeachingHours = 0;
        foreach ($teacherScheduleData as $row) {
            $totalTeachingHours += $row['total_hours'];
        }

        // =========================================================================
        // SECTION 1: REKAPITULASI PENGAJAR & BEBAN MENGAJAR (DAFTAR MAPEL & KELAS)
        // =========================================================================
        if ($format === 'all' || $format === 'rekap') {
            $html .= '<table class="data-table" border="1">';
            $html .= '<thead>';
            $html .= '<tr><th colspan="7" class="section-title text-left">BAGIAN 1: REKAPITULASI DAFTAR PENGAJAR & BEBAN MENGAJAR (MAPEL & KELAS)</th></tr>';
            $html .= '<tr>';
            $html .= '<th style="width: 40px;">NO</th>';
            $html .= '<th style="width: 110px;">NIP</th>';
            $html .= '<th style="width: 260px;">NAMA PENGAJAR</th>';
            $html .= '<th style="width: 50px;">L/P</th>';
            $html .= '<th style="width: 80px;">TOTAL BEBAN (JP)</th>';
            $html .= '<th style="width: 450px;">DAFTAR MATA PELAJARAN & KELAS YANG DIAMPU</th>';
            $html .= '<th style="width: 180px;">HARI MENGAJAR</th>';
            $html .= '</tr>';
            $html .= '</thead><tbody>';

            $no = 1;
            foreach ($teacherScheduleData as $item) {
                $t = $item['teacher'];
                $summaryList = $item['summary'];
                $genderCode = ($t['gender'] === 'Perempuan') ? 'P' : (($t['gender'] === 'Laki-laki') ? 'L' : '-');

                $html .= '<tr>';
                $html .= '<td class="text-center">' . $no++ . '</td>';
                $html .= '<td class="number-text">' . htmlspecialchars($t['nip'] ?? '-') . '</td>';
                $html .= '<td class="font-bold text-left">' . htmlspecialchars($t['nama']) . '</td>';
                $html .= '<td class="text-center">' . $genderCode . '</td>';
                $html .= '<td class="text-center font-bold jp-pill">' . $item['total_hours'] . ' JP</td>';

                // Daftar Mata Pelajaran & Kelas yang diampu (bullet list style)
                $html .= '<td class="text-left">';
                if (empty($summaryList)) {
                    $html .= '<span style="color: #94a3b8; font-style: italic;">Belum ada jadwal mengajar</span>';
                } else {
                    $html .= '<div style="margin: 0; padding: 0;">';
                    foreach ($summaryList as $sum) {
                        $html .= '&bull; <b>' . htmlspecialchars($sum['subject_name']) . '</b> kelas ' . htmlspecialchars($sum['class_name']) . ' <span style="color: #64748b;">(' . $sum['total_hours'] . ' Jam)</span><br/>';
                    }
                    $html .= '</div>';
                }
                $html .= '</td>';

                // Hari aktif mengajar
                $html .= '<td class="text-center">';
                if (!empty($item['days_active'])) {
                    $html .= htmlspecialchars(implode(', ', $item['days_active']));
                } else {
                    $html .= '<span style="color: #94a3b8;">-</span>';
                }
                $html .= '</td>';

                $html .= '</tr>';
            }

            // Total Summary Row
            $html .= '<tr class="total-row">';
            $html .= '<td colspan="4" class="text-right font-bold">TOTAL KESELURUHAN (' . $totalTeachers . ' PENGAJAR):</td>';
            $html .= '<td class="text-center font-bold jp-pill">' . $totalTeachingHours . ' JP</td>';
            $html .= '<td colspan="2" class="text-left" style="color: #64748b; font-size: 9pt;">Rata-rata beban: ' . ($totalTeachers > 0 ? round($totalTeachingHours / $totalTeachers, 1) : 0) . ' JP/Pengajar</td>';
            $html .= '</tr>';

            $html .= '</tbody></table>';
        }

        // =========================================================================
        // SECTION 2: MATRIKS JADWAL MINGGUAN PER PENGAJAR (HARI & JAM KE-1 S/D 7)
        // =========================================================================
        if ($format === 'all' || $format === 'matriks') {
            if ($format === 'all') {
                $html .= '<br/><br/>';
            }

            $html .= '<table class="data-table" border="1">';
            $html .= '<thead>';
            $html .= '<tr><th colspan="12" class="section-title text-left">BAGIAN 2: MATRIKS JADWAL MINGGUAN PER PENGAJAR (HARI SABTU - KAMIS, JAM 1 S/D 7)</th></tr>';
            $html .= '<tr>';
            $html .= '<th style="width: 40px;">NO</th>';
            $html .= '<th style="width: 240px;">NAMA PENGAJAR</th>';
            $html .= '<th style="width: 100px;">NIP</th>';
            $html .= '<th style="width: 80px;">HARI</th>';
            foreach ($hours as $h) {
                $html .= '<th style="width: 140px;">JAM KE-' . $h . '</th>';
            }
            $html .= '<th style="width: 70px;">TOTAL HARI</th>';
            $html .= '</tr>';
            $html .= '</thead><tbody>';

            $no = 1;
            foreach ($teacherScheduleData as $item) {
                $t = $item['teacher'];
                $grid = $item['grid'];

                $firstDay = true;
                foreach ($days as $day) {
                    $daySlots = $grid[$day] ?? [];
                    $dailyCount = 0;
                    foreach ($hours as $h) {
                        if (!empty($daySlots[$h])) {
                            $dailyCount++;
                        }
                    }

                    $html .= '<tr>';
                    if ($firstDay) {
                        $html .= '<td rowspan="' . count($days) . '" class="text-center font-bold" style="vertical-align: middle;">' . $no++ . '</td>';
                        $html .= '<td rowspan="' . count($days) . '" class="text-left font-bold" style="vertical-align: middle;">' . htmlspecialchars($t['nama']) . '<br/><span style="font-size: 8pt; font-weight: normal; color: #64748b;">Total: ' . $item['total_hours'] . ' JP</span></td>';
                        $html .= '<td rowspan="' . count($days) . '" class="number-text" style="vertical-align: middle;">' . htmlspecialchars($t['nip'] ?? '-') . '</td>';
                        $firstDay = false;
                    }

                    $html .= '<td class="text-center font-bold" style="background-color: #f8fafc;">' . $day . '</td>';

                    foreach ($hours as $h) {
                        $slot = $daySlots[$h] ?? null;
                        if ($slot) {
                            $html .= '<td class="bg-slot-active">';
                            $html .= '<div class="font-bold">' . htmlspecialchars($slot['subject_name']) . '</div>';
                            $html .= '<div style="font-size: 8pt; color: #4338ca; font-weight: bold;">Kelas ' . htmlspecialchars($slot['class_name']) . '</div>';
                            $html .= '</td>';
                        } else {
                            $html .= '<td class="bg-slot-empty">-</td>';
                        }
                    }

                    $html .= '<td class="text-center font-bold ' . ($dailyCount > 0 ? 'jp-pill' : 'text-gray-400') . '">' . ($dailyCount > 0 ? $dailyCount . ' JP' : '-') . '</td>';
                    $html .= '</tr>';
                }
            }

            $html .= '</tbody></table>';
        }

        $html .= '</body></html>';

        fwrite($output, $html);
        fclose($output);
        exit;
    }
}
