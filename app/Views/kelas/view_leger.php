<!-- e:\xampp\htdocs\kmi_v2\app\Views\kelas\view_leger.php -->
<?php
// Calculate statistics per subject (columns)
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

// Calculate the class overall average (average of all student averages)
$totalAvgSum = 0;
$avgCount = 0;
foreach ($studentScores as $studentId => $scores) {
    if ($scores['count'] > 0) {
        $totalAvgSum += $scores['avg'];
        $avgCount++;
    }
}
$classOverallAvg = $avgCount > 0 ? $totalAvgSum / $avgCount : 0;

$typeMap = [
    'UUPT' => 'Ulangan Umum Pertengahan Tahun',
    'UPT' => 'Ujian Pertengahan Tahun',
    'UUAT' => 'Ulangan Umum Akhir Tahun',
    'UAT' => 'Ujian Akhir Tahun'
];
$sessionType = '';
if (!empty($sessions)) {
    foreach ($sessions as $s) {
        if ($s['id'] == $selected_session_id) {
            $sessionType = $s['type'];
            break;
        }
    }
}
$sessionName = $typeMap[$sessionType] ?? '';
?>

<style>
    /* Fix flex child expansion to allow horizontal scroll on desktop */
    main .flex-1 {
        min-width: 0;
    }
    .leger-body {
        color: #000;
        font-size: 11px;
    }
    .leger-table {
        border-collapse: collapse;
        width: 100%;
        min-width: 1300px;
        border: 2px solid #000;
    }
    .print-only {
        display: none !important;
    }
    .leger-table th, .leger-table td {
        border: 1px solid #000;
        padding: 3px 4px;
        text-align: center;
        vertical-align: middle;
    }
    .leger-table th {
        font-weight: bold;
        background-color: #f9f9f9;
        font-size: 10px;
    }
    .leger-table td.student-name {
        text-align: left;
        font-weight: 600;
        font-size: 11px;
        white-space: nowrap;
    }
    .rotated-header {
        height: 135px;
        white-space: nowrap;
        vertical-align: bottom;
        padding-bottom: 5px;
    }
    .rotated-header-container {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        display: inline-block;
        width: 25px;
        margin: 0 auto;
        font-weight: bold;
    }
    @media print {
        body {
            background-color: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        /* Hide layout elements */
        nav,
        aside,
        footer,
        main > div.mb-8,
        .no-print {
            display: none !important;
        }
        /* Make content area full width */
        main {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        main > div.flex, 
        main > div.flex-col {
            display: block !important;
            gap: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .print-area {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
            overflow: visible !important;
        }
        .leger-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: auto !important;
        }
        .leger-table th, 
        .leger-table td {
            padding: 2px 3px !important;
            font-size: 8px !important;
        }
        .leger-table td.student-name {
            font-size: 9px !important;
            white-space: nowrap;
        }
        .rotated-header {
            height: 110px !important;
        }
        .rotated-header-container {
            width: 18px !important;
            font-size: 8px !important;
        }
        @page {
            size: A4 landscape;
            margin: 0.5cm;
        }
        .print-only {
            display: flex !important;
        }
    }
</style>

<!-- Action Header (Hidden in Print) -->
<div class="no-print mb-6 flex justify-between items-center bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
    <div class="flex items-center gap-3">
        <a href="<?= url('/classes/detail?id=' . $kelas['id'] . '&tab=raport&session_id=' . $selected_session_id) ?>" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
        <div>
            <h4 class="text-sm font-bold text-gray-800">Pratinjau Rekap Nilai</h4>
            <p class="text-xs text-gray-400">Kelas <?= htmlspecialchars($kelas['tingkat'] . '-' . $kelas['abjad']) ?> | <?= htmlspecialchars($sessionName) ?></p>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="<?= url('/classes/export-leger?id=' . $kelas['id'] . '&session_id=' . $selected_session_id) ?>" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
            <i class="ri-file-excel-line text-sm"></i> Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
            <i class="ri-printer-line text-sm"></i> Cetak Rekap Nilai (PDF)
        </button>
    </div>
</div>

<!-- Print Container (Landscape A4 Layout) -->
<div class="print-area leger-body bg-white p-4 shadow-md rounded-2xl border border-gray-100 mb-12 overflow-x-auto">
    <!-- Header info -->
    <div class="text-center mb-4">
        <h2 class="text-base font-bold uppercase tracking-wide">REKAPITULASI NILAI SANTRI</h2>
        <p class="text-xs font-semibold uppercase">KELAS: <?= htmlspecialchars($kelas['tingkat'] . '-' . $kelas['abjad']) ?> | SESI: <?= htmlspecialchars($sessionName) ?> | TAHUN AJARAN: <?= htmlspecialchars($this->currentYear['name'] ?? '') ?></p>
        <p class="text-[10px] text-gray-500 mt-0.5">Wali Kelas: <?= htmlspecialchars($kelas['wali_kelas'] ?? '-') ?></p>
    </div>

    <!-- The Matrix Table -->
    <table class="leger-table">
        <thead>
            <tr>
                <th class="w-8" rowspan="2">No</th>
                <th class="w-16" rowspan="2">STBK</th>
                <th class="w-48 text-left px-3" rowspan="2">NAMA</th>
                <!-- Subjects columns header (rotated) -->
                <?php foreach ($leger['exams'] as $exam): ?>
                    <th class="rotated-header w-8" rowspan="2">
                        <div class="rotated-header-container">
                            <?= htmlspecialchars($exam['subject_name']) ?>
                        </div>
                    </th>
                <?php endforeach; ?>
                <!-- Stat totals headers -->
                <th class="w-12" rowspan="2">JUMLAH</th>
                <th class="w-12" rowspan="2">RATA-RATA</th>
                <th class="w-10" rowspan="2">RANKING</th>
                <!-- Behaviors -->
                <th class="w-36" colspan="3">NILAI PERILAKU</th>
                <!-- Attendance -->
                <th class="w-30" colspan="3">ABSENSI</th>
            </tr>
            <tr>
                <th class="w-12 text-[8px]">SULUK</th>
                <th class="w-12 text-[8px]">MUWATHOBAH</th>
                <th class="w-12 text-[8px]">NADHOFAH</th>
                <th class="w-10 text-[8px]">SAKIT</th>
                <th class="w-10 text-[8px]">IZIN</th>
                <th class="w-10 text-[8px]">ALPA</th>
            </tr>
        </thead>
        <tbody>
            <!-- Student rows -->
            <?php foreach ($leger['students'] as $idx => $s): 
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
            ?>
                <tr class="hover:bg-gray-50/50">
                    <td><?= $idx + 1 ?></td>
                    <td class="font-mono text-xs"><?= htmlspecialchars($s['nis']) ?></td>
                    <td class="student-name px-2"><?= htmlspecialchars($s['nama']) ?></td>
                    <!-- Subject Grades -->
                    <?php foreach ($leger['exams'] as $exam): 
                        $grade = $leger['grades'][$studentId][$exam['exam_id']] ?? null;
                        $hasGrade = ($exam['status'] === 'selesai' && $grade && $grade['score_final'] !== null);
                        $scoreNum = $hasGrade ? round($grade['score_final']) : '-';
                    ?>
                        <td class="<?= $hasGrade ? 'font-medium' : 'text-gray-300' ?>"><?= $scoreNum ?></td>
                    <?php endforeach; ?>
                    <!-- Totals & Averages -->
                    <td class="font-bold bg-gray-50/50"><?= $scores['total'] ?></td>
                    <td class="font-bold bg-gray-50/50 text-indigo-600"><?= $scores['count'] > 0 ? number_format($scores['avg'], 2) : '-' ?></td>
                    <td class="font-black bg-indigo-50/30 text-indigo-700"><?= $rankings[$studentId] ?></td>
                    <!-- Behaviors -->
                    <td class="font-semibold"><?= $sVal ?></td>
                    <td class="font-semibold"><?= $mVal ?></td>
                    <td class="font-semibold"><?= $nVal ?></td>
                    <!-- Absences -->
                    <td><?= $sakitVal ?></td>
                    <td><?= $izinVal ?></td>
                    <td><?= $alpaVal ?></td>
                </tr>
            <?php endforeach; ?>

            <!-- STATISTIK JUMLAH (Row Sums) -->
            <tr class="bg-gray-50/80 font-bold">
                <td colspan="3" class="text-right pr-3 uppercase text-[9px] tracking-wider">Jumlah</td>
                <?php foreach ($leger['exams'] as $exam): ?>
                    <td><?= $subjectStats[$exam['exam_id']]['sum'] ?></td>
                <?php endforeach; ?>
                <!-- Student average/ranking column totals -->
                <td colspan="9" class="bg-white" rowspan="4">
                    <!-- Overall class average box (similar to the big 58,62 centered in sheet) -->
                    <div class="flex flex-col items-center justify-center py-2 h-full">
                        <span class="text-[8px] text-gray-400 font-bold uppercase tracking-wider">RATA-RATA KELAS</span>
                        <span class="text-2xl font-black text-indigo-700 mt-1"><?= number_format($classOverallAvg, 2) ?></span>
                    </div>
                </td>
            </tr>

            <!-- STATISTIK MINIMAL -->
            <tr class="bg-gray-50/80 font-bold">
                <td colspan="3" class="text-right pr-3 uppercase text-[9px] tracking-wider">Nilai Minimal</td>
                <?php foreach ($leger['exams'] as $exam): ?>
                    <td><?= $subjectStats[$exam['exam_id']]['min'] ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- STATISTIK MAKSIMAL -->
            <tr class="bg-gray-50/80 font-bold">
                <td colspan="3" class="text-right pr-3 uppercase text-[9px] tracking-wider">Nilai Maksimal</td>
                <?php foreach ($leger['exams'] as $exam): ?>
                    <td><?= $subjectStats[$exam['exam_id']]['max'] ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- STATISTIK RATA-RATA -->
            <tr class="bg-gray-50/80 font-bold">
                <td colspan="3" class="text-right pr-3 uppercase text-[9px] tracking-wider">Rata-rata</td>
                <?php foreach ($leger['exams'] as $exam): ?>
                    <td><?= $subjectStats[$exam['exam_id']]['avg'] > 0 ? number_format($subjectStats[$exam['exam_id']]['avg'], 2) : '-' ?></td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>

    <!-- Signatures -->
    <div class="mt-8 flex justify-between text-xs font-bold text-center px-8 print-only">
        <div class="w-1/3">
            <p>&nbsp;</p>
            <div class="h-12"></div>
            <p>&nbsp;</p>
        </div>
        <div class="w-1/3">
            <p>Wali Kelas</p>
            <div class="h-12"></div>
            <p class="border-b border-dashed border-black inline-block px-6"><?= htmlspecialchars($kelas['wali_kelas'] ?? '') ?></p>
        </div>
        <div class="w-1/3">
            <p>Pimpinan Pondok Pesantren Darussalam Bogor</p>
            <div class="h-12"></div>
            <p class="border-b border-dashed border-black inline-block px-6">Kiai Muhammad Abu Jihad Lillah, S.H.I., M.Pd.</p>
        </div>
    </div>
</div>
