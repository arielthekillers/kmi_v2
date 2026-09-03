<?php renderHeader($title); ?>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 6mm 8mm;
    }
    nav, header, footer, aside, #sidebar, .no-print {
        display: none !important;
    }
    body {
        background: white !important;
        color: #111827 !important;
        font-size: 11px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    main {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .shadow, .shadow-sm, .shadow-md {
        box-shadow: none !important;
    }
    .border {
        border-color: #e5e7eb !important;
    }

    /* Force 4 summary cards into 1 row when printing */
    .summary-cards-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 0.4rem !important;
        margin-bottom: 0.75rem !important;
    }
    .summary-cards-grid .p-5 {
        padding: 0.4rem 0.5rem !important;
    }
    .summary-cards-grid .p-3 {
        padding: 0.25rem !important;
        border-radius: 0.375rem !important;
    }
    .summary-cards-grid svg {
        width: 1rem !important;
        height: 1rem !important;
    }
    .summary-cards-grid .ml-5 {
        margin-left: 0.4rem !important;
    }
    .summary-cards-grid dt {
        font-size: 0.65rem !important;
        line-height: 0.85rem !important;
    }
    .summary-cards-grid dd {
        font-size: 0.95rem !important;
        line-height: 1.15rem !important;
    }

    /* Compact table cells so all columns including STATUS fit within printable page width */
    table {
        width: 100% !important;
        table-layout: auto !important;
        font-size: 10px !important;
    }
    th, td {
        padding: 0.3rem 0.35rem !important;
    }
    th.px-6, td.px-6 {
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
    }
    th.py-3, td.py-4 {
        padding-top: 0.3rem !important;
        padding-bottom: 0.3rem !important;
    }
    .w-16 {
        width: 2.25rem !important;
    }
    .mr-2 {
        margin-right: 0.2rem !important;
    }
    .text-xs {
        font-size: 9px !important;
    }
}
</style>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Laporan Tanqih Idad
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Rekapitulasi kesiapan mengajar guru per periode.
            </p>
        </div>
        <div class="no-print">
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 shadow-sm transition-all flex items-center gap-2">
                <i class="ri-printer-line"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Filter Date & Presets -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6 no-print">
        <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Mulai Tanggal</label>
                <input type="date" name="start" value="<?= $startDate ?>" max="<?= date('Y-m-d') ?>" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2 border">
            </div>
            <div class="w-full md:flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end" value="<?= $endDate ?>" max="<?= date('Y-m-d') ?>" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2 border">
            </div>
            <div class="w-full md:w-auto flex items-center gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 w-full md:w-auto shadow-sm">
                    Tampilkan Laporan
                </button>
            </div>
        </form>

        <!-- Presets -->
        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2 text-xs">
            <span class="text-gray-400 font-medium">Periode Cepat:</span>
            <button type="button" onclick="setQuickDate('today')" class="px-2.5 py-1 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 rounded text-gray-600 font-medium transition-colors">Hari Ini</button>
            <button type="button" onclick="setQuickDate('week')" class="px-2.5 py-1 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 rounded text-gray-600 font-medium transition-colors">Minggu Ini</button>
            <button type="button" onclick="setQuickDate('month')" class="px-2.5 py-1 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 rounded text-gray-600 font-medium transition-colors">Bulan Ini</button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6 summary-cards-grid">
        <!-- Total Jadwal -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Jadwal</dt>
                            <dd class="text-lg font-medium text-gray-900"><?= number_format($globalStats['total_jadwal']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Terverifikasi -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Terverifikasi</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                <?= number_format($globalStats['total_verified']) ?>
                                <span class="text-xs text-gray-400 font-normal ml-1">
                                    (<?= $globalStats['total_jadwal'] > 0 ? round(($globalStats['total_verified'] / $globalStats['total_jadwal']) * 100) : 0 ?>%)
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Justifikasi -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Justifikasi</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                <?= number_format($globalStats['total_justified']) ?>
                                <span class="text-xs text-gray-400 font-normal ml-1">
                                    (<?= $globalStats['total_jadwal'] > 0 ? round(($globalStats['total_justified'] / $globalStats['total_jadwal']) * 100) : 0 ?>%)
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Belum Diverifikasi -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Belum Diverifikasi</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                <?= number_format($globalStats['total_belum']) ?>
                                <span class="text-xs text-gray-400 font-normal ml-1">
                                    (<?= $globalStats['total_jadwal'] > 0 ? round(($globalStats['total_belum'] / $globalStats['total_jadwal']) * 100) : 0 ?>%)
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white shadow overflow-hidden rounded-lg border border-gray-200">
        <!-- Live Search Bar -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-3 no-print">
            <div class="relative w-full sm:w-72">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" id="teacherSearchInput" onkeyup="filterTeachersTable()" placeholder="Cari nama pengajar..." class="block w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="text-xs text-gray-500 font-medium">
                Menampilkan <span id="visibleTeachersCount" class="font-bold text-gray-800"><?= count($report) ?></span> pengajar
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <?php
                $sort = $sort ?? '';
                $order = $order ?? 'desc';
                
                $nextKepatuhanOrder = ($sort === 'kepatuhan' && $order === 'desc') || empty($sort) ? 'asc' : 'desc';
                $nextStatusOrder = ($sort === 'status' && $order === 'desc') || empty($sort) ? 'asc' : 'desc';
                $nextNamaOrder = ($sort === 'nama' && $order === 'asc') ? 'desc' : 'asc';
                $nextJadwalOrder = ($sort === 'jadwal' && $order === 'desc') ? 'asc' : 'desc';
                $nextVerifiedOrder = ($sort === 'verified' && $order === 'desc') ? 'asc' : 'desc';
                $nextJustifiedOrder = ($sort === 'justified' && $order === 'desc') ? 'asc' : 'desc';
                $nextBelumOrder = ($sort === 'belum' && $order === 'desc') ? 'asc' : 'desc';
                ?>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="<?= url('/tanqih/report?start=' . $startDate . '&end=' . $endDate . '&sort=nama&order=' . $nextNamaOrder) ?>" class="hover:text-indigo-650 flex items-center gap-1.5 group select-none">
                                Nama Pengajar
                                <?php if ($sort === 'nama'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-sm text-indigo-600 font-bold"></i>
                                <?php else: ?>
                                    <i class="ri-expand-up-down-line text-xs text-gray-300 group-hover:text-gray-400"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Total Jadwal">
                            <a href="<?= url('/tanqih/report?start=' . $startDate . '&end=' . $endDate . '&sort=jadwal&order=' . $nextJadwalOrder) ?>" class="inline-flex items-center justify-center gap-1 group">
                                <i class="ri-calendar-event-fill text-indigo-500 text-lg"></i>
                                <?php if ($sort === 'jadwal'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-xs text-indigo-600 font-bold"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Terverifikasi">
                            <a href="<?= url('/tanqih/report?start=' . $startDate . '&end=' . $endDate . '&sort=verified&order=' . $nextVerifiedOrder) ?>" class="inline-flex items-center justify-center gap-1 group">
                                <i class="ri-checkbox-circle-fill text-green-500 text-lg"></i>
                                <?php if ($sort === 'verified'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-xs text-green-600 font-bold"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Justifikasi">
                            <a href="<?= url('/tanqih/report?start=' . $startDate . '&end=' . $endDate . '&sort=justified&order=' . $nextJustifiedOrder) ?>" class="inline-flex items-center justify-center gap-1 group">
                                <i class="ri-alert-fill text-yellow-500 text-lg"></i>
                                <?php if ($sort === 'justified'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-xs text-yellow-600 font-bold"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Belum Diverifikasi">
                            <a href="<?= url('/tanqih/report?start=' . $startDate . '&end=' . $endDate . '&sort=belum&order=' . $nextBelumOrder) ?>" class="inline-flex items-center justify-center gap-1 group">
                                <i class="ri-close-circle-fill text-red-500 text-lg"></i>
                                <?php if ($sort === 'belum'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-xs text-red-600 font-bold"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="<?= url('/tanqih/report?start=' . $startDate . '&end=' . $endDate . '&sort=kepatuhan&order=' . $nextKepatuhanOrder) ?>" class="hover:text-indigo-650 flex items-center justify-center gap-1.5 group select-none">
                                Kepatuhan (%)
                                <?php if ($sort === 'kepatuhan'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-sm text-indigo-600 font-bold"></i>
                                <?php else: ?>
                                    <i class="ri-expand-up-down-line text-xs text-gray-300 group-hover:text-gray-400"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="<?= url('/tanqih/report?start=' . $startDate . '&end=' . $endDate . '&sort=status&order=' . $nextStatusOrder) ?>" class="hover:text-indigo-650 flex items-center gap-1.5 group select-none">
                                Status
                                <?php if ($sort === 'status'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-sm text-indigo-600 font-bold"></i>
                                <?php else: ?>
                                    <i class="ri-expand-up-down-line text-xs text-gray-300 group-hover:text-gray-400"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody id="reportTableBody" class="bg-white divide-y divide-gray-200">
                    <?php if (empty($report)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 text-sm">
                                Tidak ada data jadwal pada rentang tanggal ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($report as $r): 
                            // Kepatuhan hanya dihitung dari slot 'verified' (hadir dikonfirmasi)
                            // 'justified' hanya penanda beralasan, tidak masuk perhitungan
                            $pct = $r['expected'] > 0 ? round(($r['verified_real'] / $r['expected']) * 100) : 0;
                            $belum = $r['expected'] - $r['verified_real'] - $r['justified'];
                            
                            // Color Coding
                            if ($pct >= 75) {
                                $badgeColor = 'bg-green-100 text-green-800';
                                $barColor = 'bg-emerald-500';
                                $statusText = 'Sangat Baik';
                            } elseif ($pct >= 50) {
                                $badgeColor = 'bg-blue-100 text-blue-800';
                                $barColor = 'bg-blue-500';
                                $statusText = 'Baik';
                            } elseif ($pct >= 25) {
                                $badgeColor = 'bg-yellow-100 text-yellow-800';
                                $barColor = 'bg-amber-400';
                                $statusText = 'Cukup';
                            } else {
                                $badgeColor = 'bg-red-100 text-red-800';
                                $barColor = 'bg-red-500';
                                $statusText = 'Perlu Perhatian';
                            }
                        ?>
                        <tr class="hover:bg-gray-50 teacher-row" data-name="<?= htmlspecialchars($r['name']) ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <button type="button" 
                                        onclick='openTeacherModal(<?= json_encode([
                                            "name" => $r["name"],
                                            "expected" => $r["expected"],
                                            "verified" => $r["verified_real"],
                                            "justified" => $r["justified"],
                                            "unverified" => $belum,
                                            "pct" => $pct,
                                            "badge_color" => $badgeColor,
                                            "status_text" => $statusText,
                                            "subjects" => array_values($r["subjects"] ?? []),
                                            "details" => $r["details"] ?? []
                                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                        class="text-indigo-600 font-semibold hover:text-indigo-800 transition-colors cursor-pointer text-left">
                                    <?= htmlspecialchars($r['name']) ?>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center font-medium">
                                <?= $r['expected'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-center font-semibold">
                                <?= $r['verified_real'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 text-center font-semibold">
                                <?= $r['justified'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-center font-semibold">
                                <?= $belum ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center">
                                    <span class="text-sm font-bold text-gray-700 mr-2 w-10 text-right"><?= $pct ?>%</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="<?= $barColor ?> h-1.5 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeColor ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Detail Pengajar -->
<div id="teacherDetailModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeTeacherModal()" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl sm:align-middle">
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-gray-100 pb-4 mb-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="text-xl font-bold text-gray-900" id="modalTeacherName">Nama Pengajar</h3>
                        <span id="modalStatusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">Status</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Laporan Tanqih Idad periode <?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?></p>
                </div>
                <button type="button" onclick="closeTeacherModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 text-center">
                    <span class="text-xs font-medium text-gray-500 block">Total Jadwal</span>
                    <span id="modalStatTotal" class="text-lg font-bold text-gray-800">-</span>
                </div>
                <div class="bg-green-50 border border-green-100 rounded-xl p-3 text-center">
                    <span class="text-xs font-medium text-green-600 block">Terverifikasi</span>
                    <span id="modalStatVerified" class="text-lg font-bold text-green-700">-</span>
                </div>
                <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-3 text-center">
                    <span class="text-xs font-medium text-yellow-600 block">Justifikasi</span>
                    <span id="modalStatJustified" class="text-lg font-bold text-yellow-700">-</span>
                </div>
                <div class="bg-red-50 border border-red-100 rounded-xl p-3 text-center">
                    <span class="text-xs font-medium text-red-600 block">Belum Diverifikasi</span>
                    <span id="modalStatUnverified" class="text-lg font-bold text-red-700">-</span>
                </div>
            </div>

            <!-- Daftar Pelajaran Yang Diampu -->
            <div class="mb-5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="ri-book-read-line text-indigo-500 text-sm"></i> Daftar Mata Pelajaran &amp; Kelas
                </h4>
                <div id="modalSubjectsList" class="flex flex-wrap gap-2">
                    <!-- Pills injected via JS -->
                </div>
            </div>

            <!-- Detail Sesi Mengajar Table -->
            <div>
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="ri-time-line text-indigo-500 text-sm"></i> Rincian Sesi Mengajar per Tanggal
                </h4>
                <div class="max-h-72 overflow-y-auto border border-gray-200 rounded-xl shadow-inner">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-600">Tanggal &amp; Hari</th>
                                <th class="px-4 py-2.5 text-center font-semibold text-gray-600">Jam</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-600">Kelas</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-600">Mata Pelajaran</th>
                                <th class="px-4 py-2.5 text-center font-semibold text-gray-600">Status Tanqih</th>
                            </tr>
                        </thead>
                        <tbody id="modalDetailsTable" class="bg-white divide-y divide-gray-100">
                            <!-- Rows injected via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-5 text-right">
                <button type="button" onclick="closeTeacherModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openTeacherModal(data) {
    document.getElementById('modalTeacherName').textContent = data.name;
    const badge = document.getElementById('modalStatusBadge');
    badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + data.badge_color;
    badge.textContent = data.status_text + ' (' + data.pct + '%)';

    document.getElementById('modalStatTotal').textContent = data.expected;
    document.getElementById('modalStatVerified').textContent = data.verified;
    document.getElementById('modalStatJustified').textContent = data.justified;
    document.getElementById('modalStatUnverified').textContent = data.unverified;

    // Render Subjects list
    const subjectsContainer = document.getElementById('modalSubjectsList');
    subjectsContainer.innerHTML = '';
    if (!data.subjects || data.subjects.length === 0) {
        subjectsContainer.innerHTML = '<span class="text-xs text-gray-400 italic">Tidak ada daftar pelajaran.</span>';
    } else {
        data.subjects.forEach(sub => {
            const pill = document.createElement('span');
            pill.className = 'px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold flex items-center gap-1.5';
            pill.innerHTML = `<i class="ri-book-open-line text-indigo-500"></i> ${escapeHtml(sub.subject)} <span class="text-indigo-400 font-normal">(${escapeHtml(sub.kelas)})</span>`;
            subjectsContainer.appendChild(pill);
        });
    }

    // Render Details table
    const tableBody = document.getElementById('modalDetailsTable');
    tableBody.innerHTML = '';
    if (!data.details || data.details.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 italic">Belum ada rincian sesi mengajar.</td></tr>';
    } else {
        data.details.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';

            let statusBadge = '';
            if (item.status === 'verified') {
                const verifierInfo = item.verifier_name ? `oleh ${escapeHtml(item.verifier_name)}` : '';
                statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-green-100 text-green-800" title="${verifierInfo}"><i class="ri-checkbox-circle-fill mr-1 text-green-600"></i> Terverifikasi</span>`;
                if (item.verifier_name) {
                    statusBadge += `<span class="block text-[10px] text-gray-400 font-normal mt-0.5">${escapeHtml(item.verifier_name)}</span>`;
                }
            } else if (item.status === 'justified') {
                statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-yellow-100 text-yellow-800"><i class="ri-alert-fill mr-1 text-yellow-600"></i> Justifikasi</span>`;
            } else {
                statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-800"><i class="ri-close-circle-fill mr-1 text-red-600"></i> Belum Diverifikasi</span>`;
            }

            // Format Date
            let dateStr = item.date;
            try {
                const d = new Date(item.date + 'T00:00:00');
                dateStr = item.day + ', ' + d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            } catch(e) {}

            tr.innerHTML = `
                <td class="px-4 py-2.5 whitespace-nowrap font-medium text-gray-800">${dateStr}</td>
                <td class="px-4 py-2.5 text-center font-bold text-indigo-600">Jam ${item.hour}</td>
                <td class="px-4 py-2.5 whitespace-nowrap text-gray-700 font-medium">${escapeHtml(item.kelas)}</td>
                <td class="px-4 py-2.5 text-gray-900 font-semibold">${escapeHtml(item.subject)}</td>
                <td class="px-4 py-2.5 text-center">${statusBadge}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    document.getElementById('teacherDetailModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeTeacherModal() {
    document.getElementById('teacherDetailModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeTeacherModal();
});

function setQuickDate(type) {
    const today = new Date();
    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    let start = formatDate(today);
    let end = formatDate(today);

    if (type === 'week') {
        const current = new Date();
        const w = current.getDay(); // 0 (Sun) to 6 (Sat)
        const daysToSub = (w === 6) ? 0 : (w + 1);
        const sat = new Date();
        sat.setDate(current.getDate() - daysToSub);
        start = formatDate(sat);
        end = formatDate(today);
    } else if (type === 'month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        start = formatDate(firstDay);
        end = formatDate(today);
    }

    const startInput = document.querySelector('input[name="start"]');
    const endInput = document.querySelector('input[name="end"]');
    if (startInput) startInput.value = start;
    if (endInput) endInput.value = end;

    const form = startInput ? startInput.closest('form') : null;
    if (form) form.submit();
}

function filterTeachersTable() {
    const input = document.getElementById('teacherSearchInput');
    if (!input) return;
    const query = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#reportTableBody tr.teacher-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.dataset.name ? row.dataset.name.toLowerCase() : row.textContent.toLowerCase();
        if (name.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countSpan = document.getElementById('visibleTeachersCount');
    if (countSpan) countSpan.textContent = visibleCount;
}
</script>

<?php renderFooter(); ?>
