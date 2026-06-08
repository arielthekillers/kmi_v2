    <!-- Detail Nilai Header & Actions -->
    <div class="mb-6 flex items-center justify-between">
        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
            <a href="<?= url('/classes/detail?id=' . $exam['kelas_id'] . '&tab=nilai') ?>" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors text-gray-600">
                <i class="ri-arrow-left-line text-lg"></i>
            </a>
            Nilai Ujian: <?= htmlspecialchars($exam['mapel_nama']) ?>
        </h3>
        <div class="flex items-center gap-2">
            <?php if ($exam['status'] === 'selesai'): ?>
                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-green-100 text-green-700 uppercase tracking-widest">
                    Verifikasi Selesai
                </span>
            <?php elseif ($exam['status'] === 'proses'): ?>
                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700 uppercase tracking-widest">
                    Sedang Proses
                </span>
            <?php else: ?>
                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-gray-100 text-gray-700 uppercase tracking-widest">
                    Belum Dikoreksi
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Student List Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span class="font-medium">Pengajar:</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($exam['pengajar_nama'] ?? 'Unknown') ?></span>
                <span class="text-gray-300">|</span>
                <span class="font-medium">Skala:</span>
                <span class="font-bold text-gray-900"><?php 
                    $skala = $exam['skala'] ?? '80-30';
                    list($max_val, $min_val) = explode('-', $skala);
                    echo $min_val . ' - ' . $max_val;
                ?></span>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right flex items-center gap-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rata-rata Kelas:</span>
                    <?php 
                        $totalNilai = 0;
                        $countNilai = 0;
                        foreach ($students as $row) {
                            if (is_numeric($row['nilai'])) {
                                $totalNilai += $row['nilai'];
                                $countNilai++;
                            }
                        }
                        $avg = $countNilai > 0 ? round($totalNilai / $countNilai, 2) : 0;
                    ?>
                    <span class="text-sm font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">
                        <?= number_format($avg, 2) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">Nama Lengkap</th>
                        <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest w-24">No. Bayanat</th>
                        <?php if ($exam['has_oral'] == 1): ?>
                            <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest w-32">Nilai Lisan</th>
                        <?php endif; ?>
                        <?php if ($exam['has_oral'] != 2): ?>
                            <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest w-32">Skor Tulis</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest w-24"><?= $exam['has_oral'] == 2 ? 'Nilai Lisan' : 'Nilai Tulis' ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-[13px]">
                    <?php foreach ($students as $i => $row): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3.5">
                                <div class="font-bold text-gray-900">
                                     <?= htmlspecialchars($row['nama']) ?>
                                </div>
                                <div class="text-[10px] text-gray-400 font-mono"><?= htmlspecialchars($row['nis']) ?></div>
                            </td>

                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="font-mono text-gray-600">
                                     <?= $row['no_bayanat'] ?: '-' ?>
                                </span>
                            </td>
                            
                            <?php if ($exam['has_oral'] == 1): ?>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="font-medium text-gray-700">
                                        <?= htmlspecialchars($row['score_oral'] ?? '-') ?>
                                    </span>
                                </td>
                            <?php endif; ?>

                            <?php if ($exam['has_oral'] != 2): ?>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="font-medium text-gray-700">
                                        <?= htmlspecialchars(is_numeric($row['skor']) ? (float)$row['skor'] : ($row['skor'] ?? '-')) ?>
                                    </span>
                                </td>
                            <?php endif; ?>
 
                            <td class="px-6 py-3.5 text-center">
                                <span class="font-bold <?= is_numeric($row['nilai']) && $row['nilai'] < $min_val ? 'text-red-500' : 'text-indigo-600' ?>">
                                    <?= is_numeric($row['nilai']) ? round($row['nilai']) : '-' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="<?= ($exam['has_oral'] == 1) ? 5 : (($exam['has_oral'] == 2) ? 3 : 4) ?>" class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada data santri.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
