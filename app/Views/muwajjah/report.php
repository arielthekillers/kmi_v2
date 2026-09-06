<?php renderHeader("Laporan Absensi Muwajjah"); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header Section -->
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate flex items-center gap-2">
                <i class="ri-file-chart-line text-indigo-600"></i> Rekap Absensi Muwajjah
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Statistik dan persentase kehadiran pendampingan Belajar Malam (Muwajjah).
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-2">
            <a href="<?= url('/muwajjah') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="ri-arrow-left-line mr-2"></i> Kembali ke Form Absensi
            </a>
        </div>
    </div>

    <!-- Filter Rentang Tanggal -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form method="GET" action="<?= url('/muwajjah/report') ?>" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label for="start_date" class="text-xs font-semibold text-gray-600">Dari:</label>
                    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>"
                           class="rounded-lg border-gray-300 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 text-xs font-medium text-gray-800 px-3 py-1.5 border">
                </div>
                <div class="flex items-center gap-2">
                    <label for="end_date" class="text-xs font-semibold text-gray-600">Sampai:</label>
                    <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>"
                           class="rounded-lg border-gray-300 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 text-xs font-medium text-gray-800 px-3 py-1.5 border">
                </div>
                <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy) ?>">
                <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-2xs transition-colors">
                    <i class="ri-filter-3-line mr-1"></i> Filter
                </button>
            </div>

            <div class="text-xs font-medium text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">
                Total Hari Muwajjah Efektif: <span class="font-bold text-indigo-700"><?= $reportData['effective_days'] ?> Hari</span>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <?php
    $effectiveDays = $reportData['effective_days'];
    $waliStats = $reportData['wali_stats'];
    $totalWali = count($waliStats);
    
    $avgCompliance = 0;
    $topCount = 0;
    $lowCount = 0;

    if ($totalWali > 0) {
        $sumRate = array_sum(array_column($waliStats, 'compliance_rate'));
        $avgCompliance = round($sumRate / $totalWali, 1);

        foreach ($waliStats as $w) {
            if ($w['compliance_rate'] >= 90) $topCount++;
            if ($w['compliance_rate'] < 75) $lowCount++;
        }
    }
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                <i class="ri-calendar-check-line text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Muwajjah Efektif</p>
                <h4 class="text-xl font-bold text-gray-900"><?= $effectiveDays ?> <span class="text-xs font-normal text-gray-400">Malam</span></h4>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-green-50 rounded-xl text-green-600">
                <i class="ri-pie-chart-line text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Rata-rata Kehadiran</p>
                <h4 class="text-xl font-bold text-gray-900"><?= $avgCompliance ?>%</h4>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                <i class="ri-medal-line text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Kehadiran Tinggi (≥90%)</p>
                <h4 class="text-xl font-bold text-emerald-600"><?= $topCount ?> <span class="text-xs font-normal text-gray-400">Wali Kelas</span></h4>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-rose-50 rounded-xl text-rose-600">
                <i class="ri-alert-line text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Perlu Perhatian (<75%)</p>
                <h4 class="text-xl font-bold text-rose-600"><?= $lowCount ?> <span class="text-xs font-normal text-gray-400">Wali Kelas</span></h4>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-user-star-line"></i> Data Absensi Individu Wali Kelas
            </h3>
            <span class="text-xs text-gray-500 font-medium"><?= count($waliStats) ?> Wali Kelas Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider w-12">No</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">Wali Kelas</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-600 uppercase tracking-wider">
                            <a href="<?= url('/muwajjah/report?start_date=' . $startDate . '&end_date=' . $endDate . '&sort_by=kelas') ?>" 
                               class="hover:text-indigo-600 inline-flex items-center gap-1 <?= $sortBy === 'kelas' ? 'text-indigo-600 font-extrabold' : '' ?>" title="Klik untuk mengurutkan berdasarkan kelas">
                                Kelas <?= $sortBy === 'kelas' ? '<i class="ri-arrow-down-s-line"></i>' : '' ?>
                            </a>
                        </th>
                        <th scope="col" class="px-3 py-3 text-center font-bold text-gray-600 uppercase tracking-wider">Hadir</th>
                        <th scope="col" class="px-3 py-3 text-center font-bold text-gray-600 uppercase tracking-wider">Tidak Hadir</th>
                        <th scope="col" class="px-3 py-3 text-center font-bold text-gray-600 uppercase tracking-wider">Izin / Sakit</th>
                        <th scope="col" class="px-4 py-3 text-center font-bold text-gray-600 uppercase tracking-wider">
                            <a href="<?= url('/muwajjah/report?start_date=' . $startDate . '&end_date=' . $endDate . '&sort_by=rate') ?>" 
                               class="hover:text-indigo-600 inline-flex items-center gap-1 <?= $sortBy === 'rate' ? 'text-indigo-600 font-extrabold' : '' ?>" title="Klik untuk mengurutkan berdasarkan persentase kehadiran">
                                % Kehadiran <?= $sortBy === 'rate' ? '<i class="ri-arrow-down-s-line"></i>' : '' ?>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($waliStats)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400 italic">
                                Belum ada data Wali Kelas yang terpetakan untuk tahun ajaran aktif ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($waliStats as $w): 
                            $rate = $w['compliance_rate'];
                            $badgeColor = 'bg-red-50 text-red-700 border-red-200';
                            $progressColor = 'bg-red-500';

                            if ($rate >= 90) {
                                $badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                $progressColor = 'bg-emerald-500';
                            } elseif ($rate >= 75) {
                                $badgeColor = 'bg-blue-50 text-blue-700 border-blue-200';
                                $progressColor = 'bg-blue-500';
                            } elseif ($rate >= 60) {
                                $badgeColor = 'bg-amber-50 text-amber-700 border-amber-200';
                                $progressColor = 'bg-amber-500';
                            }
                        ?>
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-4 py-3 text-gray-500 font-mono"><?= $no++ ?></td>
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    <?php
                                    $prefix = ($w['gender'] === 'Perempuan') ? 'Al-Ustadzah ' : 'Al-Ustadz ';
                                    $formattedNama = (strpos($w['teacher_name'], 'Ustadz') === false) ? $prefix . $w['teacher_name'] : $w['teacher_name'];
                                    echo htmlspecialchars($formattedNama);
                                    ?>
                                </td>
                                <td class="px-4 py-3 text-indigo-700 font-semibold">
                                    <?= htmlspecialchars($w['class_names'] ?? '-') ?>
                                </td>
                                <td class="px-3 py-3 text-center text-green-700 font-bold bg-green-50/30"><?= $w['hadir'] ?></td>
                                <td class="px-3 py-3 text-center text-red-700 font-bold bg-red-50/30"><?= $w['alfa'] ?></td>
                                <td class="px-3 py-3 text-center text-blue-700 font-bold bg-blue-50/30"><?= $w['izin'] ?></td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 overflow-hidden hidden sm:block">
                                            <div class="h-2 rounded-full <?= $progressColor ?>" style="width: <?= min(100, max(0, $rate)) ?>%"></div>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold border <?= $badgeColor ?>">
                                            <?= $rate ?>%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Explanation Box (Di bawah tabel) -->
    <div class="mb-6 bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-600 leading-relaxed">
        <span class="font-bold text-slate-800 flex items-center gap-1 mb-1">
            <i class="ri-information-fill text-indigo-500"></i> Catatan Perhitungan Hari Efektif & Libur Otomatis:
        </span>
        <ul class="list-disc pl-5 space-y-0.5">
            <li><strong>Malam Kamis & Malam Jumat</strong> otomatis diabaikan dari rekap (Libur Rutin).</li>
            <li>Hari aktif lainnya (Sabtu - Rabu) yang <strong>tidak ada 1 pun data absensi</strong> yang diinput oleh petugas otomatis terdeteksi sebagai <strong>Libur Kegiatan Pondok</strong>.</li>
            <li>Persentase Kehadiran dihitung dari: <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-indigo-700 font-mono">Total Hadir / Total Hari Muwajjah Efektif * 100%</code>.</li>
        </ul>
    </div>

</main>

<?php renderFooter(); ?>
