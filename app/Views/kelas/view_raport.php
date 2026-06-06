<!-- Detail Nilai Header & Actions -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
            <i class="ri-book-read-line text-lg"></i>
        </div>
        Raport Santri
    </h3>
    <div class="flex items-center gap-3">
        <?php if (!empty($selected_session_id) && $leger && !empty($leger['students'])): ?>
            <a href="<?= url('/classes/detail?id=' . $kelas['id'] . '&tab=leger&session_id=' . $selected_session_id) ?>" 
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                <i class="ri-file-chart-line text-lg"></i> Lihat Leger (Rekap Nilai)
            </a>
        <?php endif; ?>
        <form action="" method="GET" class="flex items-center">
            <input type="hidden" name="id" value="<?= $kelas['id'] ?>">
            <input type="hidden" name="tab" value="raport">
            <select name="session_id" onchange="this.form.submit()" class="text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-lg px-4 py-2 focus:border-indigo-500 focus:ring-0 shadow-sm cursor-pointer hover:border-gray-300 transition-colors">
                <option value="">Pilih Sesi Ujian...</option>
                <?php 
                $typeMap = [
                    'UUPT' => 'Ulangan Umum Pertengahan Tahun',
                    'UPT' => 'Ujian Pertengahan Tahun',
                    'UUAT' => 'Ulangan Umum Akhir Tahun',
                    'UAT' => 'Ujian Akhir Tahun'
                ];
                foreach ($sessions as $session): 
                    $sessionName = $typeMap[$session['type']] ?? $session['type'];
                ?>
                    <option value="<?= $session['id'] ?>" <?= $selected_session_id == $session['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($session['type'] . ' (' . $sessionName . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if (empty($selected_session_id)): ?>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
            <i class="ri-search-line text-2xl"></i>
        </div>
        <h4 class="text-lg font-bold text-gray-900 mb-2">Silakan Pilih Sesi Ujian</h4>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">Pilih salah satu sesi ujian dari dropdown di atas untuk melihat nilai santri pada sesi tersebut.</p>
    </div>
<?php elseif (!$leger || empty($leger['students'])): ?>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
            <i class="ri-file-warning-line text-2xl"></i>
        </div>
        <h4 class="text-lg font-bold text-gray-900 mb-2">Data Tidak Ditemukan</h4>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">Belum ada data santri yang terdaftar atau tidak ada ujian yang dijadwalkan pada sesi ini.</p>
    </div>
<?php else: ?>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6 flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center shrink-0">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
                <i class="ri-list-check text-indigo-500"></i>
                Daftar Nilai Rata-rata Santri
            </h3>
            <span class="text-xs text-gray-400 italic">*Nilai rata-rata dihitung dari mata pelajaran yang berstatus <strong>Selesai</strong>.</span>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest w-16">No</th>
                        <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">Nama Lengkap</th>
                        <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest w-40">Nilai Rata-rata</th>
                        <th class="px-6 py-3 text-right text-[9px] font-bold text-gray-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-[13px]">
                    <?php 
                    $classTotalAvg = 0;
                    $classValidStudents = 0;

                    foreach ($leger['students'] as $idx => $student): 
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
                        if ($avg !== null) {
                            $classTotalAvg += $avg;
                            $classValidStudents++;
                        }
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-400"><?= $idx + 1 ?></td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900"><?= htmlspecialchars($student['nama']) ?></div>
                                <div class="text-[10px] text-gray-400 font-mono mt-0.5">NIS: <?= htmlspecialchars($student['nis']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($avg !== null): ?>
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-md text-sm font-bold bg-indigo-50 text-indigo-700">
                                        <?= number_format($avg, 2) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-300 font-medium">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="<?= url('/classes/detail?id=' . $kelas['id'] . '&tab=raport_detail&session_id=' . $selected_session_id . '&student_id=' . $student['student_id']) ?>" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-lg transition-colors">
                                    <i class="ri-file-list-3-line"></i> Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if ($classValidStudents > 0): ?>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right text-[10px] font-bold text-gray-500 uppercase tracking-widest">
                            Rata-rata Kelas
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-md text-sm font-bold bg-indigo-100 text-indigo-800">
                                <?= number_format($classTotalAvg / $classValidStudents, 2) ?>
                            </span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
<?php endif; ?>
