<?php
renderHeader("Smart Tap — Rekapitulasi & Laporan Kedisiplinan");
?>

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-file-chart-line text-indigo-600"></i> Rekapitulasi & Kedisiplinan Smart Tap
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Laporan akumulasi kehadiran, keterlambatan, dan evaluasi ketertiban santri & pengajar.
            </p>
        </div>

        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition print:hidden">
            <i class="ri-printer-line"></i> Cetak / PDF
        </button>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 print:hidden">
        <form method="GET" action="<?= url('/smart-tap/reports') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Sampai</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Kategori Peserta</label>
                <select name="target_type" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                    <option value="all" <?= $targetType == 'all' ? 'selected' : '' ?>>Semua (Santri & Guru)</option>
                    <option value="students" <?= $targetType == 'students' ? 'selected' : '' ?>>Santri Saja</option>
                    <option value="teachers" <?= $targetType == 'teachers' ? 'selected' : '' ?>>Pengajar Saja</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Event Tertentu</label>
                <select name="event_id" class="w-full text-sm rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                    <option value="">Semua Kegiatan/Event</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= $ev['id'] ?>" <?= $eventId == $ev['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ev['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-4 flex justify-end gap-2 pt-2">
                <a href="<?= url('/smart-tap/reports') ?>" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 font-medium rounded-xl hover:bg-gray-100 transition">
                    Reset Filter
                </a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                    <i class="ri-filter-line mr-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900">
                Data Akumulasi Kehadiran (<?= count($recap) ?> orang terdata)
            </h3>
            <span class="text-xs text-gray-500">
                Periode: <?= date('d M Y', strtotime($startDate)) ?> s/d <?= date('d M Y', strtotime($endDate)) ?>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Kategori / Kelas</th>
                        <th class="py-3 px-4 text-center">Tepat Waktu</th>
                        <th class="py-3 px-4 text-center">Terlambat</th>
                        <th class="py-3 px-4 text-center">Sangat Terlambat</th>
                        <th class="py-3 px-4 text-center">Total Hadir</th>
                        <th class="py-3 px-4 text-center">Evaluasi Kedisiplinan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($recap)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-400">
                                <i class="ri-inbox-line text-3xl mb-1 block"></i>
                                Tidak ada catatan kehadiran pada rentang tanggal atau filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($recap as $row): 
                            $totalLate = $row['count_late'] + $row['count_very_late'];
                        ?>
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-3 px-4 text-xs font-medium text-gray-400"><?= $no++ ?></td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900"><?= htmlspecialchars($row['name'] ?: 'Tanpa Nama') ?></div>
                                    <div class="text-[11px] text-gray-400"><?= htmlspecialchars($row['identifier'] ?: '-') ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $row['person_type'] == 'student' ? 'bg-indigo-50 text-indigo-700' : 'bg-purple-50 text-purple-700' ?>">
                                        <?= htmlspecialchars($row['subinfo']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-emerald-600">
                                    <?= $row['count_on_time'] ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($row['count_late'] > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            <?= $row['count_late'] ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($row['count_very_late'] > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                            <?= $row['count_very_late'] ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center font-extrabold text-gray-900">
                                    <?= $row['total_attendance'] ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($totalLate == 0): ?>
                                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                                            <i class="ri-star-fill text-amber-400"></i> Sangat Disiplin
                                        </span>
                                    <?php elseif ($totalLate <= 3): ?>
                                        <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">
                                            Baik (<?= $totalLate ?>x telat)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full">
                                            Perhatian (<?= $totalLate ?>x telat)
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
renderFooter();
?>

