<!-- Header & Back Button -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
        <a href="<?= url('/classes/detail?id=' . $kelas['id'] . '&tab=raport&session_id=' . $selected_session_id) ?>" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors text-gray-600">
            <i class="ri-arrow-left-line text-lg"></i>
        </a>
        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
            <i class="ri-user-line text-lg"></i>
        </div>
        Raport Santri
    </h3>
    <div class="flex items-center gap-2">
        <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 uppercase tracking-widest">
            <?php
            $typeMap = [
                'UUPT' => 'Ulangan Umum Pertengahan Tahun',
                'UPT' => 'Ujian Pertengahan Tahun',
                'UUAT' => 'Ulangan Umum Akhir Tahun',
                'UAT' => 'Ujian Akhir Tahun'
            ];
            $currentSession = null;
            foreach ($sessions as $s) {
                if ($s['id'] == $selected_session_id) {
                    $currentSession = $s;
                    break;
                }
            }
            if ($currentSession) {
                $sessionName = $typeMap[$currentSession['type']] ?? $currentSession['type'];
                echo htmlspecialchars($currentSession['type'] . ' - ' . $sessionName);
            }
            ?>
        </span>
        <a href="<?= url('/classes/detail?id=' . $kelas['id'] . '&tab=raport_arab&student_id=' . $student['student_id'] . '&session_id=' . $selected_session_id) ?>" 
           class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
            <i class="ri-printer-line text-sm"></i> Cetak Rapor Arab
        </a>
    </div>
</div>

<!-- Student Info Card -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h4 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($student['nama']) ?></h4>
            <div class="text-sm text-gray-500 mt-1 flex items-center gap-4">
                <span><i class="ri-barcode-box-line mr-1 text-gray-400"></i> NIS: <span class="font-mono text-gray-700"><?= htmlspecialchars($student['nis']) ?></span></span>
                <span><i class="ri-community-line mr-1 text-gray-400"></i> Kelas: <span class="font-bold text-gray-700"><?= htmlspecialchars($kelas['tingkat'] . '-' . $kelas['abjad']) ?></span></span>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Nilai Rata-rata</p>
            <?php 
                $studentTotal = 0;
                $studentCount = 0;
                foreach ($leger['exams'] as $exam) {
                    if ($exam['status'] === 'selesai') {
                        $grade = $leger['grades'][$student['student_id']][$exam['exam_id']] ?? null;
                        if ($grade && $grade['score_final'] !== null) {
                            $studentTotal += round($grade['score_final']);
                            $studentCount++;
                        }
                    }
                }
                $avg = $studentCount > 0 ? $studentTotal / $studentCount : null;
            ?>
            <span class="text-2xl font-black <?= $avg !== null ? 'text-indigo-600' : 'text-gray-300' ?>">
                <?= $avg !== null ? number_format($avg, 2) : '-' ?>
            </span>
        </div>
    </div>
</div>

<!-- Grades Table -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
            <i class="ri-file-list-3-line text-indigo-500"></i>
            Rincian Nilai Mata Pelajaran
        </h3>
        <span class="text-xs text-gray-400 italic">*Nilai ditampilkan jika status koreksian sudah Selesai.</span>
    </div>

    <!-- Desktop View Table -->
    <div class="overflow-x-auto hidden md:block">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest w-16">No</th>
                    <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">Mata Pelajaran</th>
                    <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest w-32">Nilai Akhir</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-[13px]">
                <?php 
                $count = 1;
                foreach ($leger['exams'] as $exam): 
                    $grade = $leger['grades'][$student['student_id']][$exam['exam_id']] ?? null;
                    
                    $skala = $exam['skala'] ?? '80-30';
                    list($max_val, $min_val) = explode('-', $skala);

                    if ($exam['status'] === 'selesai' && $grade && $grade['score_final'] !== null) {
                        $val = round($grade['score_final']);
                        $textColor = $val < $min_val ? 'text-red-500 font-bold' : 'text-gray-900 font-bold';
                        $display = $val;
                    } else {
                        $display = '-';
                        $textColor = 'text-gray-400 font-medium';
                    }
                ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-400"><?= $count++ ?></td>
                        <td class="px-6 py-4 font-bold text-gray-700"><?= htmlspecialchars($exam['subject_name']) ?></td>
                        <td class="px-6 py-4 text-center text-base <?= $textColor ?>">
                            <?= $display ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($leger['exams'])): ?>
                    <tr><td colspan="3" class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada jadwal mata pelajaran untuk sesi ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile View Cards -->
    <div class="grid grid-cols-1 divide-y divide-gray-150 md:hidden bg-white">
        <?php 
        $count = 1;
        foreach ($leger['exams'] as $exam): 
            $grade = $leger['grades'][$student['student_id']][$exam['exam_id']] ?? null;
            
            $skala = $exam['skala'] ?? '80-30';
            list($max_val, $min_val) = explode('-', $skala);

            if ($exam['status'] === 'selesai' && $grade && $grade['score_final'] !== null) {
                $val = round($grade['score_final']);
                $isFailing = $val < $min_val;
                $scoreBg = $isFailing ? 'bg-red-50 text-red-700 border-red-150' : 'bg-indigo-50 text-indigo-700 border-indigo-150';
                $display = $val;
            } else {
                $isFailing = false;
                $scoreBg = 'bg-gray-50 text-gray-400 border-gray-150';
                $display = '-';
            }
        ?>
            <div class="p-4 flex items-center justify-between hover:bg-gray-50/20 transition-colors">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-400 font-mono text-[9px] flex items-center justify-center shrink-0">
                            <?= $count++ ?>
                        </span>
                        <h4 class="font-bold text-gray-800 text-sm truncate">
                            <?= htmlspecialchars($exam['subject_name']) ?>
                        </h4>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1 ml-7 flex items-center gap-1">
                        <span>KKM/Skala:</span>
                        <span class="font-mono bg-gray-100 px-1 py-0.5 rounded text-gray-600"><?= $min_val ?> - <?= $max_val ?></span>
                    </p>
                </div>
                
                <div class="shrink-0 text-right">
                    <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl text-base font-black border <?= $scoreBg ?> shadow-sm">
                        <?= $display ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($leger['exams'])): ?>
            <div class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada jadwal mata pelajaran untuk sesi ini.</div>
        <?php endif; ?>
    </div>
</div>
