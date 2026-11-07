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

        $escape = function($str) {
            return htmlspecialchars((string)($str ?? ''), ENT_XML1, 'UTF-8');
        };

        $totalTeachers = count($teacherScheduleData);
        $totalTeachingHours = 0;
        foreach ($teacherScheduleData as $row) {
            $totalTeachingHours += $row['total_hours'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

        $xml .= '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>KMI Darussalam</Author>
  <Company>Pondok Modern Darussalam Bogor</Company>
 </DocumentProperties>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Center"/>
   <Borders/>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="Title">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="14" ss:Color="#0F172A" ss:Bold="1"/>
  </Style>
  <Style ss:ID="Subtitle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#1E3A8A" ss:Bold="1"/>
  </Style>
  <Style ss:ID="Meta">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="9" ss:Color="#64748B" ss:Italic="1"/>
  </Style>
  <Style ss:ID="Header">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#475569"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#475569"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#475569"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#475569"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#1E3A8A" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="Cell">
   <Alignment ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1E293B"/>
  </Style>
  <Style ss:ID="CellWrap">
   <Alignment ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1E293B"/>
  </Style>
  <Style ss:ID="CellCenter">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1E293B"/>
  </Style>
  <Style ss:ID="CellCenterBold">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1E293B" ss:Bold="1"/>
  </Style>
  <Style ss:ID="CellText">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1E293B"/>
   <NumberFormat ss:Format="@"/>
  </Style>
  <Style ss:ID="CellBold">
   <Alignment ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1E293B" ss:Bold="1"/>
  </Style>
  <Style ss:ID="CellSlotActive">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A7F3D0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A7F3D0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A7F3D0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A7F3D0"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="9" ss:Color="#065F46" ss:Bold="1"/>
   <Interior ss:Color="#ECFDF5" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="CellSlotEmpty">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="9" ss:Color="#94A3B8"/>
  </Style>
  <Style ss:ID="TotalRow">
   <Alignment ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#475569"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#475569"/>
   </Borders>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#0F172A" ss:Bold="1"/>
   <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>
  </Style>
 </Styles>' . "\n";

        // =========================================================================
        // TAB 1: REKAP TUGAS & BEBAN MENGAJAR (DAFTAR MAPEL & KELAS)
        // =========================================================================
        if ($format === 'all' || $format === 'rekap') {
            $xml .= '<Worksheet ss:Name="Rekap Tugas Mengajar"><Table>';
            $xml .= '<Column ss:Width="35"/><Column ss:Width="100"/><Column ss:Width="230"/><Column ss:Width="40"/><Column ss:Width="80"/><Column ss:Width="420"/><Column ss:Width="170"/>';
            $xml .= '<Row ss:Height="22"><Cell ss:MergeAcross="6" ss:StyleID="Title"><Data ss:Type="String">PONDOK MODERN DARUSSALAM BOGOR</Data></Cell></Row>';
            $xml .= '<Row ss:Height="18"><Cell ss:MergeAcross="6" ss:StyleID="Subtitle"><Data ss:Type="String">REKAPITULASI PENGAJAR &amp; BEBAN MENGAJAR (DAFTAR MAPEL &amp; KELAS)</Data></Cell></Row>';
            $xml .= '<Row ss:Height="15"><Cell ss:MergeAcross="6" ss:StyleID="Meta"><Data ss:Type="String">Tahun Ajaran: ' . $escape($academicYearName) . ' | Dicetak: ' . date('d F Y, H:i') . ' WIB</Data></Cell></Row>';
            $xml .= '<Row ss:Height="8"/>';
            $xml .= '<Row ss:Height="24">';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">NO</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">NIP</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">NAMA PENGAJAR</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">L/P</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">TOTAL BEBAN (JP)</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">DAFTAR MATA PELAJARAN &amp; KELAS YANG DIAMPU</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">HARI MENGAJAR</Data></Cell>';
            $xml .= '</Row>';

            $no = 1;
            foreach ($teacherScheduleData as $item) {
                $t = $item['teacher'];
                $summaryList = $item['summary'];
                $genderCode = ($t['gender'] === 'Perempuan') ? 'P' : (($t['gender'] === 'Laki-laki') ? 'L' : '-');

                $summaryText = '';
                if (empty($summaryList)) {
                    $summaryText = 'Belum ada jadwal mengajar';
                } else {
                    $lines = [];
                    foreach ($summaryList as $sum) {
                        $lines[] = '• ' . $sum['subject_name'] . ' kelas ' . $sum['class_name'] . ' (' . $sum['total_hours'] . ' Jam)';
                    }
                    $summaryText = implode("\n", $lines);
                }
                $daysText = !empty($item['days_active']) ? implode(', ', $item['days_active']) : '-';

                $rowHeight = max(20, count($summaryList) * 16);
                $xml .= '<Row ss:Height="' . $rowHeight . '">';
                $xml .= '<Cell ss:StyleID="CellCenter"><Data ss:Type="Number">' . $no++ . '</Data></Cell>';
                $xml .= '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . $escape($t['nip'] ?? '-') . '</Data></Cell>';
                $xml .= '<Cell ss:StyleID="CellBold"><Data ss:Type="String">' . $escape($t['nama']) . '</Data></Cell>';
                $xml .= '<Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $escape($genderCode) . '</Data></Cell>';
                $xml .= '<Cell ss:StyleID="CellCenterBold"><Data ss:Type="Number">' . $item['total_hours'] . '</Data></Cell>';
                $xml .= '<Cell ss:StyleID="CellWrap"><Data ss:Type="String">' . $escape($summaryText) . '</Data></Cell>';
                $xml .= '<Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $escape($daysText) . '</Data></Cell>';
                $xml .= '</Row>';
            }

            $xml .= '<Row ss:Height="22">';
            $xml .= '<Cell ss:MergeAcross="3" ss:StyleID="TotalRow"><Data ss:Type="String">TOTAL KESELURUHAN (' . $totalTeachers . ' PENGAJAR):</Data></Cell>';
            $xml .= '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . $totalTeachingHours . '</Data></Cell>';
            $xml .= '<Cell ss:MergeAcross="1" ss:StyleID="TotalRow"><Data ss:Type="String">Rata-rata: ' . ($totalTeachers > 0 ? round($totalTeachingHours / $totalTeachers, 1) : 0) . ' JP/Pengajar</Data></Cell>';
            $xml .= '</Row>';
            $xml .= '</Table></Worksheet>' . "\n";
        }

        // =========================================================================
        // TAB 2: MATRIKS JADWAL MINGGUAN PER PENGAJAR (HARI SABTU - KAMIS, JAM 1 S/D 7)
        // =========================================================================
        if ($format === 'all' || $format === 'matriks') {
            $xml .= '<Worksheet ss:Name="Matriks Jadwal Mingguan"><Table>';
            $xml .= '<Column ss:Width="35"/><Column ss:Width="210"/><Column ss:Width="95"/><Column ss:Width="70"/>';
            foreach ($hours as $h) {
                $xml .= '<Column ss:Width="125"/>';
            }
            $xml .= '<Column ss:Width="75"/>';

            $xml .= '<Row ss:Height="22"><Cell ss:MergeAcross="11" ss:StyleID="Title"><Data ss:Type="String">PONDOK MODERN DARUSSALAM BOGOR</Data></Cell></Row>';
            $xml .= '<Row ss:Height="18"><Cell ss:MergeAcross="11" ss:StyleID="Subtitle"><Data ss:Type="String">MATRIKS JADWAL MINGGUAN PER PENGAJAR (HARI SABTU - KAMIS, JAM 1 S/D 7)</Data></Cell></Row>';
            $xml .= '<Row ss:Height="15"><Cell ss:MergeAcross="11" ss:StyleID="Meta"><Data ss:Type="String">Tahun Ajaran: ' . $escape($academicYearName) . ' | Dicetak: ' . date('d F Y, H:i') . ' WIB</Data></Cell></Row>';
            $xml .= '<Row ss:Height="8"/>';
            $xml .= '<Row ss:Height="24">';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">NO</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">NAMA PENGAJAR</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">NIP</Data></Cell>';
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">HARI</Data></Cell>';
            foreach ($hours as $h) {
                $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">JAM KE-' . $h . '</Data></Cell>';
            }
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">TOTAL HARI</Data></Cell>';
            $xml .= '</Row>';

            $mNo = 1;
            foreach ($teacherScheduleData as $item) {
                $t = $item['teacher'];
                $grid = $item['grid'];

                foreach ($days as $day) {
                    $daySlots = $grid[$day] ?? [];
                    $dailyCount = 0;
                    foreach ($hours as $h) {
                        if (!empty($daySlots[$h])) $dailyCount++;
                    }

                    $xml .= '<Row ss:Height="24">';
                    $xml .= '<Cell ss:StyleID="CellCenter"><Data ss:Type="Number">' . $mNo . '</Data></Cell>';
                    $xml .= '<Cell ss:StyleID="CellBold"><Data ss:Type="String">' . $escape($t['nama']) . '</Data></Cell>';
                    $xml .= '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . $escape($t['nip'] ?? '-') . '</Data></Cell>';
                    $xml .= '<Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $escape($day) . '</Data></Cell>';

                    foreach ($hours as $h) {
                        $slot = $daySlots[$h] ?? null;
                        if ($slot) {
                            $slotText = $slot['subject_name'] . "\n" . 'Kelas ' . $slot['class_name'];
                            $xml .= '<Cell ss:StyleID="CellSlotActive"><Data ss:Type="String">' . $escape($slotText) . '</Data></Cell>';
                        } else {
                            $xml .= '<Cell ss:StyleID="CellSlotEmpty"><Data ss:Type="String">-</Data></Cell>';
                        }
                    }

                    $xml .= '<Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . ($dailyCount > 0 ? $dailyCount . ' JP' : '-') . '</Data></Cell>';
                    $xml .= '</Row>';
                }
                $mNo++;
            }

            $xml .= '</Table></Worksheet>' . "\n";
        }

        $xml .= '</Workbook>';

        fwrite($output, $xml);
        fclose($output);
        exit;
    }
}
