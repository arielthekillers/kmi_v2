<!-- e:\xampp\htdocs\kmi_v2\app\Views\kelas\view_raport_arab.php -->
<?php
// Group exams by KMI category
$arabicExams = [];
$islamicExams = [];
$foreignExams = [];
$generalExams = [];

foreach ($leger['exams'] as $exam) {
    $cat = $exam['category'] ?? '';
    if ($cat === 'arabic') {
        $arabicExams[] = $exam;
    } elseif ($cat === 'islamic') {
        $islamicExams[] = $exam;
    } elseif ($cat === 'foreign') {
        $foreignExams[] = $exam;
    } else {
        $generalExams[] = $exam; // Default fallback to general
    }
}

// Calculate averages dynamically
$db = \App\Core\Database::getInstance()->getConnection();

// Fetch all sessions for this academic year
$stmtSessions = $db->prepare("SELECT id, type FROM exam_sessions WHERE academic_year_id = ?");
$stmtSessions->execute([$this->currentYear['id']]);
$allYrSessions = $stmtSessions->fetchAll(\PDO::FETCH_ASSOC);

$sem1SessionIds = [];
$sem2SessionIds = [];
foreach ($allYrSessions as $sOption) {
    if (in_array($sOption['type'], ['UUPT', 'UPT'])) {
        $sem1SessionIds[] = $sOption['id'];
    } else {
        $sem2SessionIds[] = $sOption['id'];
    }
}

// Student grades for Sem 1
$sem1Total = 0;
$sem1Count = 0;
$sem1AvgClassTotal = 0;
$sem1AvgClassCount = 0;

if (!empty($sem1SessionIds)) {
    $inSem1 = implode(',', $sem1SessionIds);
    // Student score
    $stmtG = $db->prepare("
        SELECT g.score_final 
        FROM grades g
        JOIN exams e ON g.exam_id = e.id
        WHERE g.student_id = ? AND e.exam_session_id IN ($inSem1) AND e.is_deleted = 0 AND e.status = 'selesai' AND g.score_final IS NOT NULL
    ");
    $stmtG->execute([$student['student_id']]);
    $sem1Grades = $stmtG->fetchAll(\PDO::FETCH_COLUMN);
    foreach ($sem1Grades as $gVal) {
        $sem1Total += round($gVal);
        $sem1Count++;
    }

    // Class average
    $stmtAvg = $db->prepare("
        SELECT AVG(g.score_final) 
        FROM grades g
        JOIN exams e ON g.exam_id = e.id
        WHERE e.kelas_id = ? AND e.exam_session_id IN ($inSem1) AND e.is_deleted = 0 AND e.status = 'selesai' AND g.score_final IS NOT NULL
        GROUP BY e.id
    ");
    $stmtAvg->execute([$kelas['id']]);
    $sem1ClassAvgs = $stmtAvg->fetchAll(\PDO::FETCH_COLUMN);
    foreach ($sem1ClassAvgs as $avgVal) {
        $sem1AvgClassTotal += $avgVal;
        $sem1AvgClassCount++;
    }
}

// Student grades for Sem 2
$sem2Total = 0;
$sem2Count = 0;
$sem2AvgClassTotal = 0;
$sem2AvgClassCount = 0;

if (!empty($sem2SessionIds)) {
    $inSem2 = implode(',', $sem2SessionIds);
    // Student score
    $stmtG = $db->prepare("
        SELECT g.score_final 
        FROM grades g
        JOIN exams e ON g.exam_id = e.id
        WHERE g.student_id = ? AND e.exam_session_id IN ($inSem2) AND e.is_deleted = 0 AND e.status = 'selesai' AND g.score_final IS NOT NULL
    ");
    $stmtG->execute([$student['student_id']]);
    $sem2Grades = $stmtG->fetchAll(\PDO::FETCH_COLUMN);
    foreach ($sem2Grades as $gVal) {
        $sem2Total += round($gVal);
        $sem2Count++;
    }

    // Class average
    $stmtAvg = $db->prepare("
        SELECT AVG(g.score_final) 
        FROM grades g
        JOIN exams e ON g.exam_id = e.id
        WHERE e.kelas_id = ? AND e.exam_session_id IN ($inSem2) AND e.is_deleted = 0 AND e.status = 'selesai' AND g.score_final IS NOT NULL
        GROUP BY e.id
    ");
    $stmtAvg->execute([$kelas['id']]);
    $sem2ClassAvgs = $stmtAvg->fetchAll(\PDO::FETCH_COLUMN);
    foreach ($sem2ClassAvgs as $avgVal) {
        $sem2AvgClassTotal += $avgVal;
        $sem2AvgClassCount++;
    }
}

$sem1Avg = $sem1Count > 0 ? $sem1Total / $sem1Count : null;
$sem2Avg = $sem2Count > 0 ? $sem2Total / $sem2Count : null;

$sem1ClassAvg = $sem1AvgClassCount > 0 ? $sem1AvgClassTotal / $sem1AvgClassCount : null;
$sem2ClassAvg = $sem2AvgClassCount > 0 ? $sem2AvgClassTotal / $sem2AvgClassCount : null;

// Overall combined average
$overallTotal = ($sem1Avg !== null ? $sem1Avg : 0) + ($sem2Avg !== null ? $sem2Avg : 0);
$overallCount = ($sem1Avg !== null ? 1 : 0) + ($sem2Avg !== null ? 1 : 0);
$overallAvg = $overallCount > 0 ? $overallTotal / $overallCount : null;

$overallClassTotal = ($sem1ClassAvg !== null ? $sem1ClassAvg : 0) + ($sem2ClassAvg !== null ? $sem2ClassAvg : 0);
$overallClassAvg = $overallCount > 0 ? $overallClassTotal / $overallCount : null;

// Class tingkat Arabic translation mapping
$tingkatMap = [
    1 => 'الأول',
    2 => 'الثاني',
    3 => 'الثالث',
    4 => 'الرابع',
    5 => 'الخامس',
    6 => 'السادس'
];
$tingkatAr = $tingkatMap[$kelas['tingkat']] ?? $kelas['tingkat'];

// Count rows for layout alignment
$rightPanelExams = array_merge($arabicExams, $islamicExams);
$leftPanelExams = array_merge($foreignExams, $generalExams);

$rightRowsCount = count($rightPanelExams);
$leftRowsCount = count($leftPanelExams);
?>

<link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

<style>
    .raport-body {
        font-family: 'Amiri', serif;
        direction: rtl;
        color: #000;
    }
    .raport-table {
        border-collapse: collapse;
        width: 100%;
        border: 2px solid #000;
    }
    .raport-table th, .raport-table td {
        border: 1px solid #000;
        padding: 4px 6px;
        font-size: 14px;
        vertical-align: middle;
    }
    .raport-table th {
        font-weight: bold;
        background-color: #fcfcfc;
    }
    .vertical-text {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        text-align: center;
        white-space: nowrap;
        font-weight: bold;
        padding: 8px 4px !important;
        font-size: 13px;
    }
    @media print {
        body {
            background-color: #fff !important;
            margin: 0 !important;
        }
        .no-print {
            display: none !important;
        }
        .print-area {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        @page {
            size: A4 portrait;
            margin: 1.2cm 1cm 1cm 1cm;
        }
    }
</style>

<!-- Action Buttons / Header (Hidden in Print) -->
<div class="no-print mb-6 flex justify-between items-center bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
    <div class="flex items-center gap-3">
        <a href="<?= url('/classes/detail?id=' . $kelas['id'] . '&tab=raport_detail&session_id=' . $selected_session_id . '&student_id=' . $student['student_id']) ?>" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
        <h4 class="text-sm font-bold text-gray-800">Tampilan Rapor Arab (Pratinjau)</h4>
    </div>
    <button onclick="window.print()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
        <i class="ri-printer-line text-sm"></i> Cetak Rapor
    </button>
</div>

<!-- Print Container -->
<div class="print-area raport-body bg-white p-6 max-w-5xl mx-auto shadow-md rounded-2xl border border-gray-100 mb-12">
    <!-- Bismillah -->
    <div class="text-center mb-4">
        <h3 class="text-xl font-bold">بسم الله الرحمن الرحيم</h3>
    </div>

    <!-- Student Metadata -->
    <div class="flex justify-between items-center mb-4 text-base font-bold px-2">
        <div>الطالب: <span class="border-b border-dashed border-black px-2"><?= htmlspecialchars($student['nama']) ?></span></div>
        <div>الفصل: <span class="border-b border-dashed border-black px-2"><?= htmlspecialchars($tingkatAr . ' ' . $kelas['abjad']) ?></span></div>
    </div>

    <!-- Double Panel Layout (Flex / Grid) -->
    <div class="flex gap-3 w-full">
        
        <!-- ================= RIGHT PANEL ================= -->
        <div class="w-1/2">
            <table class="raport-table">
                <thead>
                    <tr>
                        <th class="w-12 text-center" rowspan="2">القسم</th>
                        <th class="text-center" rowspan="2">المواد</th>
                        <th class="text-center" colspan="3">الدرجة</th>
                    </tr>
                    <tr>
                        <th class="w-12 text-center text-xs">المعدلة</th>
                        <th class="w-12 text-center text-xs">الأرقام</th>
                        <th class="text-center text-xs">الألفاظ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- --- 1. BAHASA ARAB (اللغة العربية) --- -->
                    <?php 
                    $arCount = count($arabicExams);
                    foreach ($arabicExams as $idx => $exam):
                        $grade = $leger['grades'][$student['student_id']][$exam['exam_id']] ?? null;
                        $hasGrade = ($exam['status'] === 'selesai' && $grade && $grade['score_final'] !== null);
                        
                        $scoreNum = $hasGrade ? round($grade['score_final']) : '';
                        $scoreText = $hasGrade ? terbilang_arab($grade['score_final']) : '';
                        $avgNum = isset($examAverages[$exam['exam_id']]) ? number_format($examAverages[$exam['exam_id']], 2) : '';
                    ?>
                        <tr>
                            <?php if ($idx === 0): ?>
                                <td class="vertical-text" rowspan="<?= $arCount ?>">اللغة العربية</td>
                            <?php endif; ?>
                            <td class="font-bold"><?= htmlspecialchars($exam['nama_ar'] ?: $exam['subject_name']) ?></td>
                            <td class="text-center font-semibold text-xs"><?= $avgNum ?></td>
                            <td class="text-center font-bold"><?= $scoreNum ?></td>
                            <td class="text-center text-sm font-semibold"><?= $scoreText ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- --- 2. AQIDAH & SYARIAH (العقائد والشرائع) --- -->
                    <?php 
                    $isCount = count($islamicExams);
                    foreach ($islamicExams as $idx => $exam):
                        $grade = $leger['grades'][$student['student_id']][$exam['exam_id']] ?? null;
                        $hasGrade = ($exam['status'] === 'selesai' && $grade && $grade['score_final'] !== null);
                        
                        $scoreNum = $hasGrade ? round($grade['score_final']) : '';
                        $scoreText = $hasGrade ? terbilang_arab($grade['score_final']) : '';
                        $avgNum = isset($examAverages[$exam['exam_id']]) ? number_format($examAverages[$exam['exam_id']], 2) : '';
                    ?>
                        <tr>
                            <?php if ($idx === 0): ?>
                                <td class="vertical-text" rowspan="<?= $isCount ?>">العقائد والشرائع</td>
                            <?php endif; ?>
                            <td class="font-bold"><?= htmlspecialchars($exam['nama_ar'] ?: $exam['subject_name']) ?></td>
                            <td class="text-center font-semibold text-xs"><?= $avgNum ?></td>
                            <td class="text-center font-bold"><?= $scoreNum ?></td>
                            <td class="text-center text-sm font-semibold"><?= $scoreText ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ================= LEFT PANEL ================= -->
        <div class="w-1/2">
            <table class="raport-table">
                <thead>
                    <tr>
                        <th class="w-12 text-center" rowspan="2">القسم</th>
                        <th class="text-center" rowspan="2">المواد</th>
                        <th class="text-center" colspan="3">الدرجة</th>
                    </tr>
                    <tr>
                        <th class="w-12 text-center text-xs">المعدلة</th>
                        <th class="w-12 text-center text-xs">الأرقام</th>
                        <th class="text-center text-xs">الألفاظ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- --- 3. BAHASA ASING (الأجنبية) --- -->
                    <?php 
                    $forCount = count($foreignExams);
                    foreach ($foreignExams as $idx => $exam):
                        $grade = $leger['grades'][$student['student_id']][$exam['exam_id']] ?? null;
                        $hasGrade = ($exam['status'] === 'selesai' && $grade && $grade['score_final'] !== null);
                        
                        $scoreNum = $hasGrade ? round($grade['score_final']) : '';
                        $scoreText = $hasGrade ? terbilang_arab($grade['score_final']) : '';
                        $avgNum = isset($examAverages[$exam['exam_id']]) ? number_format($examAverages[$exam['exam_id']], 2) : '';
                    ?>
                        <tr>
                            <?php if ($idx === 0): ?>
                                <td class="vertical-text" rowspan="<?= $forCount ?>">الأجنبية</td>
                            <?php endif; ?>
                            <td class="font-bold"><?= htmlspecialchars($exam['nama_ar'] ?: $exam['subject_name']) ?></td>
                            <td class="text-center font-semibold text-xs"><?= $avgNum ?></td>
                            <td class="text-center font-bold"><?= $scoreNum ?></td>
                            <td class="text-center text-sm font-semibold"><?= $scoreText ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- --- 4. UMUM & SENI (العامة والفنون) --- -->
                    <?php 
                    $genCount = count($generalExams);
                    foreach ($generalExams as $idx => $exam):
                        $grade = $leger['grades'][$student['student_id']][$exam['exam_id']] ?? null;
                        $hasGrade = ($exam['status'] === 'selesai' && $grade && $grade['score_final'] !== null);
                        
                        $scoreNum = $hasGrade ? round($grade['score_final']) : '';
                        $scoreText = $hasGrade ? terbilang_arab($grade['score_final']) : '';
                        $avgNum = isset($examAverages[$exam['exam_id']]) ? number_format($examAverages[$exam['exam_id']], 2) : '';
                    ?>
                        <tr>
                            <?php if ($idx === 0): ?>
                                <td class="vertical-text" rowspan="<?= $genCount ?>">العامة والفنون</td>
                            <?php endif; ?>
                            <td class="font-bold"><?= htmlspecialchars($exam['nama_ar'] ?: $exam['subject_name']) ?></td>
                            <td class="text-center font-semibold text-xs"><?= $avgNum ?></td>
                            <td class="text-center font-bold"><?= $scoreNum ?></td>
                            <td class="text-center text-sm font-semibold"><?= $scoreText ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Bottom Totals & Behavior Grades (Suluk, etc.) -->
    <div class="flex gap-3 w-full mt-3">
        <!-- Right Bottom Table (Duplicated / Combined layout) -->
        <div class="w-1/2">
            <table class="raport-table">
                <tbody>
                    <!-- totals -->
                    <tr>
                        <td class="font-bold" colspan="2">مجموع الدرجات لنصف السنة (Sem. I)</td>
                        <td class="w-12 text-center font-semibold text-xs"><?= $sem1ClassAvg !== null ? number_format($sem1ClassAvg, 2) : '-' ?></td>
                        <td class="w-12 text-center font-bold"><?= $sem1Avg !== null ? round($sem1Avg) : '-' ?></td>
                        <td class="text-center text-sm font-semibold"><?= $sem1Avg !== null ? terbilang_arab($sem1Avg) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold" colspan="2">مجموع الدرجات لآخر السنة (Sem. II)</td>
                        <td class="w-12 text-center font-semibold text-xs"><?= $sem2ClassAvg !== null ? number_format($sem2ClassAvg, 2) : '-' ?></td>
                        <td class="w-12 text-center font-bold"><?= $sem2Avg !== null ? round($sem2Avg) : '-' ?></td>
                        <td class="text-center text-sm font-semibold"><?= $sem2Avg !== null ? terbilang_arab($sem2Avg) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold" colspan="2">المعدل العام (Combined Average)</td>
                        <td class="w-12 text-center font-semibold text-xs"><?= $overallClassAvg !== null ? number_format($overallClassAvg, 2) : '-' ?></td>
                        <td class="w-12 text-center font-bold"><?= $overallAvg !== null ? round($overallAvg) : '-' ?></td>
                        <td class="text-center text-sm font-semibold"><?= $overallAvg !== null ? terbilang_arab($overallAvg) : '-' ?></td>
                    </tr>
                    <!-- Behaviors -->
                    <?php
                    $sVal = $behavior && $behavior['suluk'] !== null ? $behavior['suluk'] : '';
                    $sText = $behavior && $behavior['suluk'] !== null ? terbilang_arab($behavior['suluk']) : '';
                    
                    $mVal = $behavior && $behavior['muwathobah'] !== null ? $behavior['muwathobah'] : '';
                    $mText = $behavior && $behavior['muwathobah'] !== null ? terbilang_arab($behavior['muwathobah']) : '';
                    
                    $nVal = $behavior && $behavior['nadhofah'] !== null ? $behavior['nadhofah'] : '';
                    $nText = $behavior && $behavior['nadhofah'] !== null ? terbilang_arab($behavior['nadhofah']) : '';
                    ?>
                    <tr>
                        <td class="font-bold" colspan="2">السلوك (Behavior)</td>
                        <td class="w-12 text-center">-</td>
                        <td class="w-12 text-center font-bold"><?= $sVal ?></td>
                        <td class="text-center text-sm font-semibold"><?= $sText ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold" colspan="2">المواظبة (Attendance)</td>
                        <td class="w-12 text-center">-</td>
                        <td class="w-12 text-center font-bold"><?= $mVal ?></td>
                        <td class="text-center text-sm font-semibold"><?= $mText ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold" colspan="2">النظافة (Cleanliness)</td>
                        <td class="w-12 text-center">-</td>
                        <td class="w-12 text-center font-bold"><?= $nVal ?></td>
                        <td class="text-center text-sm font-semibold"><?= $nText ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Left Bottom Table (Helper panel for signatures or blank alignments) -->
        <div class="w-1/2">
            <table class="raport-table h-full">
                <tbody>
                    <tr>
                        <td class="font-bold" colspan="2" style="height: 29px;">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="font-bold" colspan="2" style="height: 29px;">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="font-bold" colspan="2" style="height: 29px;">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="font-bold" colspan="2" style="height: 29px;">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="font-bold" colspan="2" style="height: 29px;">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="font-bold" colspan="2" style="height: 29px;">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="w-12 text-center">&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Signatures -->
    <div class="mt-8 flex justify-between text-base font-bold text-center px-4">
        <div class="w-1/3">
            <p>ولي الأمر</p>
            <div class="h-20"></div>
            <p class="border-b border-dashed border-black inline-block px-10">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
        </div>
        <div class="w-1/3">
            <p>ولي الفصل</p>
            <div class="h-20"></div>
            <p class="border-b border-dashed border-black inline-block px-6"><?= htmlspecialchars($kelas['wali_kelas'] ?? '') ?></p>
        </div>
        <div class="w-1/3">
            <p>مدير المعهد</p>
            <div class="h-20"></div>
            <p class="border-b border-dashed border-black inline-block px-6">كيامي أبو جهاد لله، M.Pd., S.H.I.</p>
        </div>
    </div>
</div>
