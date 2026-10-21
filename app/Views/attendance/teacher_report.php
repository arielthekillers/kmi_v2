<?php renderHeader($title); ?>

<style>
/* Custom scrollbar for sticky table container */
.table-scroll-container::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.table-scroll-container::-webkit-scrollbar-track {
    background: #f3f4f6;
}
.table-scroll-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}
.table-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

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
    .shadow, .shadow-sm, .shadow-md, .shadow-lg {
        box-shadow: none !important;
    }
    .border {
        border-color: #e5e7eb !important;
    }

    /* Print layout for 5 summary cards in 1 row */
    .summary-cards-grid {
        display: grid !important;
        grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
        gap: 0.35rem !important;
        margin-bottom: 0.75rem !important;
    }
    .summary-cards-grid .p-4 {
        padding: 0.35rem 0.45rem !important;
    }
    .summary-cards-grid svg, .summary-cards-grid i {
        font-size: 1rem !important;
    }
    .summary-cards-grid dt {
        font-size: 0.62rem !important;
        line-height: 0.8rem !important;
    }
    .summary-cards-grid dd {
        font-size: 0.95rem !important;
        line-height: 1.15rem !important;
    }

    table {
        width: 100% !important;
        table-layout: auto !important;
        font-size: 10px !important;
    }
    th, td {
        padding: 0.25rem 0.35rem !important;
    }
    .w-16 {
        width: 2.25rem !important;
    }

    /* Single Teacher Slip Printing Mode */
    body.print-single-slip {
        padding: 0 !important;
        margin: 0 !important;
        background: white !important;
    }
    body.print-single-slip main,
    body.print-single-slip nav,
    body.print-single-slip header,
    body.print-single-slip footer,
    body.print-single-slip .no-print {
        display: none !important;
    }
    body.print-single-slip #teacherDetailModal {
        display: block !important;
        position: static !important;
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        overflow: visible !important;
    }
    body.print-single-slip #teacherDetailModal > div {
        display: block !important;
        min-height: auto !important;
        padding: 0 !important;
    }
    body.print-single-slip #teacherDetailModal .fixed.inset-0 {
        display: none !important;
    }
    body.print-single-slip #teacherDetailModal .transform {
        transform: none !important;
        box-shadow: none !important;
        border: none !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    body.print-single-slip #teacherDetailModal .max-h-72 {
        max-height: none !important;
        overflow: visible !important;
    }
    body.print-single-slip .no-print-slip {
        display: none !important;
    }
    body.print-single-slip .print-only-slip {
        display: block !important;
    }
    body.print-single-slip #modalDetailsBody tr {
        page-break-inside: avoid;
    }
}
</style>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header Section -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600">
                    <i class="ri-user-star-line text-xl"></i>
                </span>
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Laporan Kehadiran Pengajar
                </h2>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                Rekapitulasi kepatuhan dan kedisiplinan mengajar guru berbasis hasil patroli piket keliling (Syeikh Diwan).
            </p>
        </div>
        <div class="flex items-center gap-2 no-print">
            <a href="<?= url('/attendance/report') ?>" class="px-3.5 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 shadow-sm transition-all flex items-center gap-1.5">
                <i class="ri-file-chart-line text-gray-400"></i> Laporan Piket Keliling
            </a>
            <button onclick="window.print()" class="px-3.5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-1.5">
                <i class="ri-printer-line"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Filter Date & Presets -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6 no-print">
        <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-1/3">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mulai Tanggal</label>
                <input type="date" id="inputStart" name="start" value="<?= htmlspecialchars($startDate) ?>" max="<?= date('Y-m-d') ?>" class="block w-full border-gray-300 rounded-lg shadow-sm sm:text-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="w-full md:w-1/3">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" id="inputEnd" name="end" value="<?= htmlspecialchars($endDate) ?>" max="<?= date('Y-m-d') ?>" class="block w-full border-gray-300 rounded-lg shadow-sm sm:text-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="w-full md:w-auto flex items-center gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 w-full md:w-auto shadow-sm flex items-center justify-center gap-1.5">
                    <i class="ri-filter-3-line"></i> Tampilkan
                </button>
                <a href="<?= url('/attendance/teacher-report') ?>" class="bg-white hover:bg-gray-50 text-gray-700 px-3.5 py-2 rounded-lg text-sm font-medium shadow-sm border border-gray-300">
                    Reset
                </a>
            </div>
        </form>

        <!-- Presets & Quick Search -->
        <div class="mt-3 pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-gray-400 font-medium">Periode Cepat:</span>
                <button type="button" onclick="setQuickDate('today')" class="px-2.5 py-1 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 rounded-md text-gray-600 font-medium transition-colors">Hari Ini</button>
                <button type="button" onclick="setQuickDate('week')" class="px-2.5 py-1 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 rounded-md text-gray-600 font-medium transition-colors">Minggu Ini</button>
                <button type="button" onclick="setQuickDate('month')" class="px-2.5 py-1 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 rounded-md text-gray-600 font-medium transition-colors">Bulan Ini</button>
            </div>
            <?php if ($canViewAll): ?>
            <div class="w-full sm:w-64">
                <div class="relative">
                    <i class="ri-search-line absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    <input type="text" id="teacherSearch" placeholder="Cari nama pengajar..." class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-md focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Cards (Merged into 5 High-Level Cards) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-6 summary-cards-grid">
        <!-- 1. Total Jadwal -->
        <div class="col-span-2 sm:col-span-1 bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Jadwal</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="ri-calendar-event-fill text-base"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900"><?= number_format($globalStats['total_jadwal']) ?></div>
                <div class="text-[11px] text-gray-400 mt-0.5">Sesi KBM Terjadwal</div>
            </div>
        </div>

        <!-- 2. Hadir di Kelas (Gabungan Hadir Tepat & Terlambat) -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100 bg-emerald-50/20 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Hadir di Kelas</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="ri-checkbox-circle-fill text-base"></i>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-emerald-800">
                        <?= number_format($globalStats['total_hadir_tepat'] + $globalStats['total_terlambat']) ?>
                    </span>
                    <span class="text-xs text-emerald-600/80 font-medium">
                        (<?= $globalStats['total_jadwal'] > 0 ? round((($globalStats['total_hadir_tepat'] + $globalStats['total_terlambat']) / $globalStats['total_jadwal']) * 100) : 0 ?>%)
                    </span>
                </div>
                <div class="mt-2 pt-1.5 border-t border-emerald-100 flex items-center justify-between text-[11px]">
                    <span class="text-emerald-700 font-semibold" title="Hadir Tepat Waktu">
                        <i class="ri-check-line font-bold"></i> <?= number_format($globalStats['total_hadir_tepat']) ?> Tepat
                    </span>
                    <span class="text-amber-700 font-semibold" title="Total keterlambatan: <?= number_format($globalStats['total_menit_terlambat']) ?> menit">
                        <i class="ri-time-line"></i> <?= number_format($globalStats['total_terlambat']) ?> Telat <span class="font-normal text-[10px] text-amber-600">(<?= number_format($globalStats['total_menit_terlambat']) ?>m)</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. Izin & Alpha (Gabungan Diganti / Inval & Tidak Hadir) -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-rose-100 bg-rose-50/20 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-rose-800 uppercase tracking-wider">Izin &amp; Alpha</span>
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                    <i class="ri-user-unfollow-fill text-base"></i>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-gray-900">
                        <?= number_format($globalStats['total_diganti'] + $globalStats['total_tidak_hadir']) ?>
                    </span>
                    <span class="text-xs text-gray-400 font-medium">sesi</span>
                </div>
                <div class="mt-2 pt-1.5 border-t border-rose-100 flex items-center justify-between text-[11px]">
                    <span class="text-blue-700 font-semibold" title="Diisi guru inval/badal (Sakit/Izin Resmi)">
                        <i class="ri-arrow-left-right-line"></i> <?= number_format($globalStats['total_diganti']) ?> Inval
                    </span>
                    <span class="text-rose-700 font-semibold" title="Alpha / tanpa kabar">
                        <i class="ri-close-circle-line"></i> <?= number_format($globalStats['total_tidak_hadir']) ?> Alpha
                    </span>
                </div>
            </div>
        </div>

        <!-- 4. Rata-rata Kehadiran -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100 bg-emerald-50/20 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Kehadiran</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                    %
                </div>
            </div>
            <div>
                <div class="text-2xl font-bold text-emerald-800"><?= $globalStats['avg_kehadiran'] ?>%</div>
                <div class="text-[11px] text-emerald-600/80 mt-0.5">Hadir &amp; Izin Resmi</div>
                <div class="w-full bg-emerald-100 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= $globalStats['avg_kehadiran'] ?>%"></div>
                </div>
            </div>
        </div>

        <!-- 5. Rata-rata Ketepatan Waktu -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100 bg-blue-50/20 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-blue-800 uppercase tracking-wider">Ketepatan</span>
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                    <i class="ri-timer-line text-base"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-bold text-blue-800"><?= $globalStats['avg_ketepatan'] ?>%</div>
                <div class="text-[11px] text-blue-600/80 mt-0.5">Dari sesi hadir (45m/jam)</div>
                <div class="w-full bg-blue-100 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: <?= $globalStats['avg_ketepatan'] ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <?php
    $countTotal = count($report);
    $countAttention = 0;
    $countLate = 0;
    $countPerfect = 0;

    foreach ($report as $r) {
        if ((int)$r['tidak_hadir'] > 0 || (float)$r['kehadiran_pct'] < 75) {
            $countAttention++;
        }
        if ((int)$r['terlambat'] > 0) {
            $countLate++;
        }
        if ((float)$r['kehadiran_pct'] >= 100 && ($r['ketepatan_pct'] === null || (float)$r['ketepatan_pct'] >= 100) && (int)$r['terlambat'] === 0 && (int)$r['tidak_hadir'] === 0) {
            $countPerfect++;
        }
    }
    ?>

    <!-- Main Table Card -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-bold text-gray-900">Daftar Kepatuhan Kehadiran Pengajar</h3>
                <p class="text-xs text-gray-500">
                    Periode: <span class="font-medium text-gray-700"><?= date('d M Y', strtotime($startDate)) ?></span> s/d <span class="font-medium text-gray-700"><?= date('d M Y', strtotime($endDate)) ?></span>
                </p>
            </div>
            <div class="text-xs text-gray-400 no-print flex items-center gap-1.5">
                <i class="ri-information-line text-indigo-500"></i> Klik nama pengajar untuk melihat rincian riwayat jam mengajar.
            </div>
        </div>

        <!-- Quick Filter Tabs & Search Info -->
        <div class="px-6 py-2.5 bg-gray-50/70 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs no-print">
            <div class="flex items-center gap-1.5 flex-wrap" id="statusFilterTabs">
                <button type="button" onclick="setStatusFilter('all')" data-filter="all" class="status-tab px-3 py-1 rounded-lg font-medium transition-all bg-indigo-600 text-white shadow-xs">
                    Semua <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-white/20"><?= $countTotal ?></span>
                </button>
                <button type="button" onclick="setStatusFilter('attention')" data-filter="attention" class="status-tab px-3 py-1 rounded-lg font-medium transition-all bg-white text-gray-600 border border-gray-200 hover:bg-gray-100">
                    Perlu Perhatian <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-rose-100 text-rose-700 font-bold"><?= $countAttention ?></span>
                </button>
                <button type="button" onclick="setStatusFilter('late')" data-filter="late" class="status-tab px-3 py-1 rounded-lg font-medium transition-all bg-white text-gray-600 border border-gray-200 hover:bg-gray-100">
                    Ada Terlambat <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-amber-100 text-amber-800 font-semibold"><?= $countLate ?></span>
                </button>
                <button type="button" onclick="setStatusFilter('perfect')" data-filter="perfect" class="status-tab px-3 py-1 rounded-lg font-medium transition-all bg-white text-gray-600 border border-gray-200 hover:bg-gray-100">
                    Disiplin 100% <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-semibold"><?= $countPerfect ?></span>
                </button>
            </div>
            <div id="filterCountIndicator" class="text-gray-500 font-medium text-[11px]">
                Menampilkan <span id="visibleRowCount" class="font-bold text-gray-900"><?= $countTotal ?></span> pengajar
            </div>
        </div>

        <div class="overflow-x-auto max-h-[72vh] overflow-y-auto relative table-scroll-container">
            <?php
            $sort = $sort ?? '';
            $order = $order ?? 'desc';
            
            $nextKehadiranOrder = ($sort === 'kehadiran' && $order === 'desc') ? 'asc' : 'desc';
            $nextKetepatanOrder = ($sort === 'ketepatan' && $order === 'desc') ? 'asc' : 'desc';
            $nextStatusOrder = ($sort === 'status' && $order === 'desc') ? 'asc' : 'desc';
            $nextNamaOrder = ($sort === 'nama' && $order === 'asc') ? 'desc' : 'asc';
            $nextJadwalOrder = ($sort === 'jadwal' && $order === 'desc') ? 'asc' : 'desc';
            $nextTepatOrder = ($sort === 'tepat' && $order === 'desc') ? 'asc' : 'desc';
            $nextTerlambatOrder = ($sort === 'terlambat' && $order === 'desc') ? 'asc' : 'desc';
            $nextDigantiOrder = ($sort === 'diganti' && $order === 'desc') ? 'asc' : 'desc';
            $nextTidakHadirOrder = ($sort === 'tidak_hadir' && $order === 'desc') ? 'asc' : 'desc';
            ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/95 backdrop-blur-sm sticky top-0 z-10 shadow-xs border-b border-gray-200">
                    <tr>
                        <!-- Nama Pengajar -->
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <a href="<?= url('/attendance/teacher-report?start=' . $startDate . '&end=' . $endDate . '&sort=nama&order=' . $nextNamaOrder) ?>" class="hover:text-indigo-600 flex items-center gap-1.5 group select-none">
                                Nama Pengajar
                                <?php if ($sort === 'nama'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-sm text-indigo-600 font-bold"></i>
                                <?php else: ?>
                                    <i class="ri-expand-up-down-line text-xs text-gray-300 group-hover:text-gray-400"></i>
                                <?php endif; ?>
                            </a>
                        </th>

                        <!-- Kolom Ikon 1: Total Jadwal (Ungu) -->
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider" title="Total Jadwal Sesi Mengajar">
                            <a href="<?= url('/attendance/teacher-report?start=' . $startDate . '&end=' . $endDate . '&sort=jadwal&order=' . $nextJadwalOrder) ?>" class="inline-flex items-center justify-center gap-1.5 group hover:text-indigo-600 transition-colors">
                                <i class="ri-calendar-event-fill text-indigo-500 text-sm"></i>
                                <span>Jadwal</span>
                                <?php if ($sort === 'jadwal'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-xs text-indigo-600 font-bold"></i>
                                <?php endif; ?>
                            </a>
                        </th>

                        <!-- Kolom Ikon 2: Hadir Tepat (Hijau) -->
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider" title="Hadir Tepat Waktu">
                            <a href="<?= url('/attendance/teacher-report?start=' . $startDate . '&end=' . $endDate . '&sort=tepat&order=' . $nextTepatOrder) ?>" class="inline-flex items-center justify-center gap-1.5 group hover:text-emerald-600 transition-colors">
                                <i class="ri-checkbox-circle-fill text-emerald-500 text-sm"></i>
                                <span>Tepat</span>
                                <?php if ($sort === 'tepat'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-xs text-emerald-600 font-bold"></i>
                                <?php endif; ?>
                            </a>
                        </th>

                        <!-- Kolom Ikon 3: Terlambat (Kuning) -->
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider" title="Hadir Terlambat (dengan Total Menit Keterlambatan)">
                            <a href="<?= url('/attendance/teacher-report?start=' . $startDate . '&end=' . $endDate . '&sort=terlambat&order=' . $nextTerlambatOrder) ?>" class="inline-flex items-center justify-center gap-1.5 group hover:text-amber-600 transition-colors">
                                <i class="ri-alert-fill text-amber-500 text-sm"></i>
                                <span>Telat</span>
                                <?php if ($sort === 'terlambat'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-xs text-amber-600 font-bold"></i>
                                <?php endif; ?>
                            </a>
                        </th>

                        <!-- Kolom Ikon 4: Diganti (Biru) -->
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider" title="Diganti / Di-inval Guru Lain (Sakit/Izin)">
                            <a href="<?= url('/attendance/teacher-report?start=' . $startDate . '&end=' . $endDate . '&sort=diganti&order=' . $nextDigantiOrder) ?>" class="inline-flex items-center justify-center gap-1.5 group hover:text-blue-600 transition-colors">
                                <i class="ri-arrow-left-right-fill text-blue-500 text-sm"></i>
                                <span>Inval</span>
                                <?php if ($sort === 'diganti'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-xs text-blue-600 font-bold"></i>
                                <?php endif; ?>
                            </a>
                        </th>

                        <!-- Kolom Ikon 5: Tidak Hadir (Merah) -->
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider" title="Tidak Hadir / Alpha / Tanpa Keterangan">
                            <a href="<?= url('/attendance/teacher-report?start=' . $startDate . '&end=' . $endDate . '&sort=tidak_hadir&order=' . $nextTidakHadirOrder) ?>" class="inline-flex items-center justify-center gap-1.5 group hover:text-rose-600 transition-colors">
                                <i class="ri-close-circle-fill text-rose-500 text-sm"></i>
                                <span>Alpha</span>
                                <?php if ($sort === 'tidak_hadir'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-xs text-rose-600 font-bold"></i>
                                <?php endif; ?>
                            </a>
                        </th>

                        <!-- Kehadiran (%) -->
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider" title="Persentase Kehadiran dan Izin dari Total Jadwal">
                            <a href="<?= url('/attendance/teacher-report?start=' . $startDate . '&end=' . $endDate . '&sort=kehadiran&order=' . $nextKehadiranOrder) ?>" class="hover:text-indigo-600 flex items-center justify-center gap-1.5 group select-none">
                                Kehadiran (%)
                                <?php if ($sort === 'kehadiran' || empty($sort)): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-sm text-indigo-600 font-bold"></i>
                                <?php else: ?>
                                    <i class="ri-expand-up-down-line text-xs text-gray-300 group-hover:text-gray-400"></i>
                                <?php endif; ?>
                            </a>
                        </th>

                        <!-- Ketepatan Waktu (%) -->
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider" title="Persentase Ketepatan Sesi Saat Hadir (45 Menit per Jam Pelajaran)">
                            <a href="<?= url('/attendance/teacher-report?start=' . $startDate . '&end=' . $endDate . '&sort=ketepatan&order=' . $nextKetepatanOrder) ?>" class="hover:text-indigo-600 flex items-center justify-center gap-1.5 group select-none">
                                Ketepatan (%)
                                <?php if ($sort === 'ketepatan'): ?>
                                    <i class="ri-arrow-<?= $order === 'desc' ? 'down' : 'up' ?>-s-line text-sm text-indigo-600 font-bold"></i>
                                <?php else: ?>
                                    <i class="ri-expand-up-down-line text-xs text-gray-300 group-hover:text-gray-400"></i>
                                <?php endif; ?>
                            </a>
                        </th>

                        <!-- Status -->
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <a href="<?= url('/attendance/teacher-report?start=' . $startDate . '&end=' . $endDate . '&sort=status&order=' . $nextStatusOrder) ?>" class="hover:text-indigo-600 flex items-center gap-1.5 group select-none">
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
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500 text-sm">
                                Tidak ada data jadwal atau absensi pada rentang tanggal ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($report as $r): 
                            $isAttention = ((int)$r['tidak_hadir'] > 0 || (float)$r['kehadiran_pct'] < 75) ? '1' : '0';
                            $isLate = ((int)$r['terlambat'] > 0) ? '1' : '0';
                            $isPerfect = ((float)$r['kehadiran_pct'] >= 100 && ($r['ketepatan_pct'] === null || (float)$r['ketepatan_pct'] >= 100) && (int)$r['terlambat'] === 0 && (int)$r['tidak_hadir'] === 0) ? '1' : '0';
                        ?>
                        <tr class="hover:bg-gray-50 teacher-row transition-colors" 
                            data-name="<?= htmlspecialchars(strtolower($r['name'])) ?>"
                            data-attention="<?= $isAttention ?>"
                            data-late="<?= $isLate ?>"
                            data-perfect="<?= $isPerfect ?>">
                            <!-- Nama Pengajar -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <button type="button" 
                                        onclick='openTeacherModal(<?= json_encode([
                                             "teacher_id" => $r["teacher_id"],
                                             "name" => $r["name"],
                                             "photo_url" => url(get_profile_picture_url($r["teacher_id"], $r["name"], $r["photo"])),
                                             "expected" => $r["expected"],
                                             "hadir_tepat" => $r["hadir_tepat"],
                                             "terlambat" => $r["terlambat"],
                                             "total_menit_terlambat" => $r["total_menit_terlambat"],
                                             "diganti" => $r["diganti"],
                                             "tidak_hadir" => $r["tidak_hadir"],
                                             "kehadiran_pct" => $r["kehadiran_pct"],
                                             "ketepatan_pct" => $r["ketepatan_pct"],
                                             "pct" => $r["pct"],
                                             "badge_color" => $r["badge_color"],
                                             "status_text" => $r["status_text"],
                                             "bar_color" => $r["bar_color"],
                                             "details" => $r["details"] ?? []
                                         ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                        class="flex items-center gap-3 text-indigo-600 font-semibold hover:text-indigo-900 transition-colors cursor-pointer text-left group">
                                    <img src="<?= url(get_profile_picture_url($r['teacher_id'], $r['name'], $r['photo'])) ?>" 
                                         alt="Avatar" 
                                         class="h-8 w-8 rounded-full object-cover ring-2 ring-gray-100 shadow-sm flex-shrink-0 group-hover:ring-indigo-300 transition-all">
                                    <span class="group-hover:underline"><?= htmlspecialchars($r['name']) ?></span>
                                </button>
                            </td>

                            <!-- Total Jadwal (Ungu) -->
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 text-center font-semibold">
                                <?= $r['expected'] ?>
                            </td>

                            <!-- Hadir Tepat (Hijau) -->
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-emerald-600 text-center font-bold">
                                <?= $r['hadir_tepat'] ?>
                            </td>

                            <!-- Terlambat (Kuning) -->
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-amber-600 text-center font-semibold">
                                <?= $r['terlambat'] ?>
                                <?php if ($r['total_menit_terlambat'] > 0): ?>
                                    <span class="text-[11px] text-amber-700/80 font-normal ml-0.5" title="Total <?= $r['total_menit_terlambat'] ?> menit keterlambatan">
                                        (<?= $r['total_menit_terlambat'] ?>m)
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Diganti (Biru) -->
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-600 text-center font-semibold">
                                <?= $r['diganti'] ?>
                            </td>

                            <!-- Tidak Hadir (Merah) -->
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-rose-600 text-center font-bold">
                                <?= $r['tidak_hadir'] ?>
                            </td>

                            <!-- Kehadiran (%) -->
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center">
                                    <span class="text-sm font-bold <?= $r['kehadiran_text_color'] ?> mr-2 w-10 text-right"><?= $r['kehadiran_pct'] ?>%</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2 overflow-hidden shadow-inner">
                                        <div class="<?= $r['kehadiran_bar_color'] ?> h-2 rounded-full transition-all duration-500" style="width: <?= $r['kehadiran_pct'] ?>%"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Ketepatan Waktu (%) -->
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <?php if ($r['ketepatan_pct'] !== null): ?>
                                <div class="flex items-center justify-center">
                                    <span class="text-sm font-bold <?= $r['ketepatan_text_color'] ?> mr-2 w-10 text-right"><?= $r['ketepatan_pct'] ?>%</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-2 overflow-hidden shadow-inner">
                                        <div class="<?= $r['ketepatan_bar_color'] ?> h-2 rounded-full transition-all duration-500" style="width: <?= $r['ketepatan_pct'] ?>%"></div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-xs text-gray-400 font-medium" title="Belum ada sesi kehadiran">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $r['badge_color'] ?>">
                                    <?= $r['status_text'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="noFilterResultRow" style="display: none;">
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400 text-sm">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="ri-user-search-line text-3xl text-gray-300 mb-1"></i>
                                    <span>Tidak ada pengajar yang sesuai dengan filter atau kata kunci pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Detail Riwayat Kehadiran Pengajar -->
<div id="teacherDetailModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-60" onclick="closeTeacherModal()" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl sm:align-middle">
            <!-- Print-only Official Slip Header -->
            <div class="hidden print-only-slip mb-4 text-center border-b border-gray-300 pb-3">
                <h2 class="text-base font-bold uppercase tracking-wider text-gray-900">Slip Rekapitulasi Kehadiran Pengajar</h2>
                <p class="text-xs text-gray-600">Kuliyatul Mu'allimin Al-Islamiyah (KMI) • Periode: <?= date('d/m/Y', strtotime($startDate)) ?> s/d <?= date('d/m/Y', strtotime($endDate)) ?></p>
            </div>

            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-gray-100 pb-4 mb-4">
                <div class="flex items-center gap-3.5">
                    <img id="modalTeacherAvatar" src="" alt="Avatar" class="w-12 h-12 rounded-full object-cover ring-2 ring-indigo-100 shadow-sm">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-xl font-bold text-gray-900" id="modalTeacherName">Nama Pengajar</h3>
                            <span id="modalStatusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold">Status</span>
                            <span id="modalKehadiranBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Kehadiran: 0%</span>
                            <span id="modalKetepatanBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Ketepatan: 0%</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Periode: <?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="printTeacherSlip()" class="no-print-slip px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5 shadow-xs">
                        <i class="ri-printer-line text-sm"></i> Cetak Slip
                    </button>
                    <button type="button" onclick="closeTeacherModal()" class="no-print-slip text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mini Summary in Modal (Merged into 5 Cards) -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 mb-4">
                <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-200/70 text-center">
                    <div class="text-[10px] uppercase tracking-wider font-semibold text-gray-500">Total Jadwal</div>
                    <div class="text-lg font-bold text-gray-900 mt-0.5" id="modalTotalJadwal">0</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Sesi KBM</div>
                </div>
                <div class="bg-emerald-50/50 p-2.5 rounded-lg border border-emerald-100 text-center">
                    <div class="text-[10px] uppercase tracking-wider font-semibold text-emerald-800">Hadir di Kelas</div>
                    <div class="text-lg font-bold text-emerald-800 mt-0.5" id="modalHadirTotal">0</div>
                    <div class="text-[10px] text-emerald-700 mt-0.5 font-medium">
                        <span id="modalHadirTepat">0</span> Tepat • <span id="modalTerlambat">0</span> Telat (<span id="modalMenitTerlambat">0m</span>)
                    </div>
                </div>
                <div class="bg-rose-50/50 p-2.5 rounded-lg border border-rose-100 text-center">
                    <div class="text-[10px] uppercase tracking-wider font-semibold text-rose-800">Izin &amp; Alpha</div>
                    <div class="text-lg font-bold text-gray-900 mt-0.5" id="modalAbsenTotal">0</div>
                    <div class="text-[10px] text-gray-600 mt-0.5 font-medium">
                        <span class="text-blue-700" id="modalDiganti">0</span> Inval • <span class="text-rose-700" id="modalTidakHadir">0</span> Alpha
                    </div>
                </div>
                <div class="bg-emerald-50/70 p-2.5 rounded-lg border border-emerald-200 text-center">
                    <div class="text-[10px] uppercase tracking-wider font-semibold text-emerald-800">Kehadiran</div>
                    <div class="text-lg font-bold text-emerald-800 mt-0.5" id="modalKehadiranVal">0%</div>
                    <div class="text-[10px] text-emerald-600 mt-0.5">Hadir &amp; Izin</div>
                </div>
                <div class="bg-blue-50/70 p-2.5 rounded-lg border border-blue-200 text-center">
                    <div class="text-[10px] uppercase tracking-wider font-semibold text-blue-800">Ketepatan</div>
                    <div class="text-lg font-bold text-blue-800 mt-0.5" id="modalKetepatanVal">0%</div>
                    <div class="text-[10px] text-blue-600 mt-0.5">Dari Sesi Hadir</div>
                </div>
            </div>

            <!-- Fair Calculation Note -->
            <div class="bg-indigo-50/40 border border-indigo-100 rounded-lg p-2.5 mb-4 text-xs text-indigo-900 flex items-start gap-2">
                <i class="ri-scales-3-line text-indigo-600 text-base mt-0.5 flex-shrink-0"></i>
                <div class="leading-relaxed">
                    <span class="font-bold">Penilaian Transparan:</span>
                    <ul class="list-disc list-inside mt-0.5 space-y-0.5 text-[11px] text-indigo-800">
                        <li><strong>Kehadiran</strong>: Persentase kehadiran mengajar dan izin resmi tercatat dari total jadwal KBM.</li>
                        <li><strong>Ketepatan Waktu</strong>: Persentase ketepatan dari sesi saat guru hadir di kelas. Berdasarkan durasi 45 menit per jam pelajaran (misal terlambat 15 menit = 67%).</li>
                    </ul>
                </div>
            </div>

            <!-- Details Table -->
            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div class="max-h-72 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-3.5 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider">Tanggal & Hari</th>
                                <th class="px-3.5 py-2.5 text-center font-semibold text-gray-500 uppercase tracking-wider">Jam</th>
                                <th class="px-3.5 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider">Kelas</th>
                                <th class="px-3.5 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                <th class="px-3.5 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider">Status Kehadiran</th>
                                <th class="px-3.5 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider">Petugas Piket</th>
                            </tr>
                        </thead>
                        <tbody id="modalDetailsBody" class="divide-y divide-gray-200 bg-white">
                            <!-- Populated via Javascript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="mt-5 border-t border-gray-100 pt-3 flex justify-between items-center no-print-slip">
                <button type="button" onclick="printTeacherSlip()" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm">
                    <i class="ri-printer-line text-sm"></i> Cetak Slip Pengajar
                </button>
                <button type="button" onclick="closeTeacherModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function setQuickDate(preset) {
    const today = new Date();
    let start = new Date();
    let end = new Date();

    if (preset === 'today') {
        // Hari ini
    } else if (preset === 'week') {
        // Sabtu pekan KMI
        const day = today.getDay(); // 0: Sun, 6: Sat
        const diff = (day === 6) ? 0 : (day + 1);
        start.setDate(today.getDate() - diff);
    } else if (preset === 'month') {
        // Awal bulan ini
        start = new Date(today.getFullYear(), today.getMonth(), 1);
    }

    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    document.getElementById('inputStart').value = formatDate(start);
    document.getElementById('inputEnd').value = formatDate(end);
    document.getElementById('inputStart').form.submit();
}

function openTeacherModal(data) {
    document.getElementById('modalTeacherName').textContent = data.name || 'Unknown';
    document.getElementById('modalTeacherAvatar').src = data.photo_url || '';
    
    const badge = document.getElementById('modalStatusBadge');
    badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ' + data.badge_color;
    badge.textContent = data.status_text;

    const bKehadiran = document.getElementById('modalKehadiranBadge');
    bKehadiran.textContent = 'Kehadiran: ' + (data.kehadiran_pct !== undefined ? data.kehadiran_pct : 0) + '%';

    const bKetepatan = document.getElementById('modalKetepatanBadge');
    if (data.ketepatan_pct !== null && data.ketepatan_pct !== undefined) {
        bKetepatan.textContent = 'Ketepatan: ' + data.ketepatan_pct + '%';
        bKetepatan.style.display = 'inline-flex';
    } else {
        bKetepatan.textContent = 'Ketepatan: -';
    }

    const hadirTotal = (parseInt(data.hadir_tepat) || 0) + (parseInt(data.terlambat) || 0);
    const absenTotal = (parseInt(data.diganti) || 0) + (parseInt(data.tidak_hadir) || 0);
    document.getElementById('modalHadirTotal').textContent = hadirTotal;
    document.getElementById('modalAbsenTotal').textContent = absenTotal;

    document.getElementById('modalTotalJadwal').textContent = data.expected;
    document.getElementById('modalHadirTepat').textContent = data.hadir_tepat;
    document.getElementById('modalTerlambat').textContent = data.terlambat;
    document.getElementById('modalMenitTerlambat').textContent = (data.total_menit_terlambat || 0) + 'm';
    document.getElementById('modalDiganti').textContent = data.diganti;
    document.getElementById('modalTidakHadir').textContent = data.tidak_hadir;
    document.getElementById('modalKehadiranVal').textContent = (data.kehadiran_pct !== undefined ? data.kehadiran_pct : 0) + '%';
    document.getElementById('modalKetepatanVal').textContent = (data.ketepatan_pct !== null && data.ketepatan_pct !== undefined) ? (data.ketepatan_pct + '%') : '-';

    const tbody = document.getElementById('modalDetailsBody');
    tbody.innerHTML = '';

    const details = data.details || [];
    if (details.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada rincian jam pelajaran tercatat.</td></tr>`;
    } else {
        details.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';

            // Status Badge Formatting
            let statusHtml = '';
            if (item.status_key === 'hadir_tepat') {
                statusHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Hadir Tepat</span>`;
            } else if (item.status_key === 'terlambat') {
                const infoDatang = item.jam_datang ? `(Tiba: ${item.jam_datang})` : '';
                statusHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    Terlambat ${item.menit_terlambat}m ${infoDatang}
                </span>`;
            } else if (item.status_key === 'diganti') {
                statusHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    ${item.status_label || 'Diganti'}
                </span>`;
            } else {
                statusHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    Tidak Hadir (Alpha)
                </span>`;
            }

            // Format date string
            const d = new Date(item.date);
            const formattedDate = `${item.day}, ${item.date}`;

            tr.innerHTML = `
                <td class="px-3.5 py-2.5 whitespace-nowrap text-gray-900 font-medium">${formattedDate}</td>
                <td class="px-3.5 py-2.5 whitespace-nowrap text-center text-gray-700">Jam ${item.hour} <span class="text-[10px] text-gray-400 block">${item.time_range || ''}</span></td>
                <td class="px-3.5 py-2.5 whitespace-nowrap text-gray-800 font-semibold">${item.kelas}</td>
                <td class="px-3.5 py-2.5 text-gray-700">${item.subject}</td>
                <td class="px-3.5 py-2.5">${statusHtml}</td>
                <td class="px-3.5 py-2.5 whitespace-nowrap text-gray-600">${item.petugas_name || '<span class="text-gray-400 italic">-</span>'}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.getElementById('teacherDetailModal').classList.remove('hidden');
}

function closeTeacherModal() {
    document.getElementById('teacherDetailModal').classList.add('hidden');
}

function printTeacherSlip() {
    document.body.classList.add('print-single-slip');
    window.print();
}

window.addEventListener('afterprint', () => {
    document.body.classList.remove('print-single-slip');
});

// Client-side instant filter tabs & search
let currentStatusFilter = 'all';

function setStatusFilter(filter) {
    currentStatusFilter = filter;
    document.querySelectorAll('.status-tab').forEach(tab => {
        if (tab.getAttribute('data-filter') === filter) {
            tab.className = 'status-tab px-3 py-1 rounded-lg font-medium transition-all bg-indigo-600 text-white shadow-xs';
        } else {
            tab.className = 'status-tab px-3 py-1 rounded-lg font-medium transition-all bg-white text-gray-600 border border-gray-200 hover:bg-gray-100';
        }
    });
    applyFilters();
}

function applyFilters() {
    const searchInput = document.getElementById('teacherSearch');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.teacher-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const isAttention = row.getAttribute('data-attention') === '1';
        const isLate = row.getAttribute('data-late') === '1';
        const isPerfect = row.getAttribute('data-perfect') === '1';

        let matchesStatus = true;
        if (currentStatusFilter === 'attention') matchesStatus = isAttention;
        else if (currentStatusFilter === 'late') matchesStatus = isLate;
        else if (currentStatusFilter === 'perfect') matchesStatus = isPerfect;

        const matchesSearch = !query || name.includes(query);

        if (matchesStatus && matchesSearch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noResultRow = document.getElementById('noFilterResultRow');
    if (noResultRow) {
        noResultRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }

    const countElem = document.getElementById('visibleRowCount');
    if (countElem) {
        countElem.textContent = visibleCount;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('teacherSearch');
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
});
</script>

<?php renderFooter(); ?>
