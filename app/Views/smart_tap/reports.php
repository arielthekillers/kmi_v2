<?php
renderHeader("Smart Tap – Rekapitulasi & Laporan Kedisiplinan");
$summary = $summary ?? [
    'total_target' => count($recap),
    'total_sesi_terselenggara' => 0,
    'sum_kewajiban_all' => 0,
    'sum_hadir_all' => 0,
    'sum_ontime_all' => 0,
    'sum_late_all' => 0,
    'sum_izin_all' => 0,
    'sum_alpa_all' => 0,
    'percent_presence_aggregate' => 0,
    'total_peserta_disiplin' => 0,
    'total_peserta_warning' => 0,
    'total_peserta_late' => 0,
    'total_peserta_alpa' => 0,
    'total_peserta_izin' => 0
];

// Siapkan map modal JSON untuk akses cepat tanpa AJAX
$modalDataMap = [];
foreach ($recap as $r) {
    $pKey = $r['participant_key'] ?? ($r['person_type'] . '_' . $r['person_id']);
    $modalDataMap[$pKey] = [
        'name' => $r['name'],
        'identifier' => $r['identifier'] ?: '-',
        'subinfo' => $r['subinfo'] ?: '-',
        'person_type' => $r['person_type'],
        'gender' => $r['gender'] ?? 'L',
        'kewajiban' => (int)($r['kewajiban'] ?? 0),
        'total_attendance' => (int)($r['total_attendance'] ?? 0),
        'count_on_time' => (int)($r['count_on_time'] ?? 0),
        'count_late' => (int)($r['count_late'] ?? 0),
        'count_permission' => (int)($r['count_permission'] ?? 0),
        'permission_reasons' => $r['permission_reasons'] ?: '-',
        'count_alpa' => (int)($r['count_alpa'] ?? 0),
        'percent_presence' => (float)($r['percent_presence'] ?? 0),
        'percent_compliance' => (float)($r['percent_compliance'] ?? 0),
        'status_key' => $r['status_key'] ?? 'disiplin',
        'status_label' => $r['status_label'] ?? 'Disiplin',
        'badge_color' => $r['badge_color'] ?? 'emerald',
        'sessions' => $r['sessions_detail'] ?? []
    ];
}
?>

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-file-chart-line text-indigo-600"></i> Rekapitulasi & Kedisiplinan Smart Tap
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Evaluasi tingkat kedisiplinan dan kepatuhan presensi berdasarkan kewajiban sesi, kehadiran aktual, ketepatan waktu, dan dispensasi resmi.
            </p>
        </div>

        <div class="flex items-center gap-2 print:hidden">
            <!-- Server-Side Compliant Excel Export (.xls XML Spreadsheet) -->
            <a href="<?= url('/smart-tap/reports/export?' . http_build_query($_GET)) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition" title="Unduh File Excel Resmi (.xls)">
                <i class="ri-file-excel-2-line"></i> Ekspor Excel
            </a>
            <!-- CSV Download -->
            <a href="<?= url('/smart-tap/reports/export?' . http_build_query(array_merge($_GET, ['format' => 'csv']))) ?>" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-xl border border-emerald-200 transition" title="Unduh File CSV Ringan">
                <i class="ri-file-text-line"></i> CSV
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                <i class="ri-printer-line"></i> Cetak / PDF
            </button>
        </div>
    </div>

    <!-- Compact Filter Bar (1 Baris) -->
    <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-gray-100 print:hidden">
        <form method="GET" action="<?= url('/smart-tap/reports') ?>" class="flex flex-col md:flex-row md:items-center gap-2.5 sm:gap-3">
            <!-- Tanggal Mulai s/d Sampai -->
            <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                <span class="text-xs font-semibold text-gray-500 flex items-center gap-1 shrink-0">
                    <i class="ri-calendar-2-line text-indigo-500"></i> Periode:
                </span>
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="bg-transparent text-xs text-gray-800 border-none p-0 focus:ring-0 focus:outline-none">
                <span class="text-xs text-gray-400 font-medium">s/d</span>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="bg-transparent text-xs text-gray-800 border-none p-0 focus:ring-0 focus:outline-none">
            </div>

            <!-- Kategori Peserta -->
            <div class="w-full md:w-48 shrink-0">
                <select name="target_type" class="w-full text-xs font-medium text-gray-700 rounded-xl border border-gray-200 bg-gray-50 py-2 px-3 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all" <?= $targetType == 'all' ? 'selected' : '' ?>>Semua (Santri & Guru)</option>
                    <option value="students" <?= $targetType == 'students' ? 'selected' : '' ?>>Santri Saja</option>
                    <option value="teachers" <?= $targetType == 'teachers' ? 'selected' : '' ?>>Pengajar Saja</option>
                </select>
            </div>

            <!-- Event Tertentu -->
            <div class="w-full md:flex-1">
                <select name="event_id" class="w-full text-xs font-medium text-gray-700 rounded-xl border border-gray-200 bg-gray-50 py-2 px-3 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Kegiatan / Event</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= $ev['id'] ?>" <?= $eventId == $ev['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ev['name']) ?> (<?= $ev['target_type'] == 'teachers' ? 'Guru' : ($ev['target_type'] == 'students' ? 'Santri' : 'Semua') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="flex items-center gap-2 shrink-0 md:ml-auto">
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
                <a href="<?= url('/smart-tap/reports') ?>" title="Reset Filter" class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                    <i class="ri-refresh-line sm:mr-1"></i> <span class="hidden sm:inline">Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- 1. Total Sesi Wajib -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Beban</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base">
                    <i class="ri-calendar-check-line"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900"><?= number_format($summary['sum_kewajiban_all'] ?? 0) ?></div>
                <div class="text-[11px] text-gray-400 mt-0.5"><?= $summary['total_sesi_terselenggara'] ?? 0 ?> sesi &bull; <?= $summary['total_target'] ?? count($recap) ?> peserta</div>
            </div>
        </div>

        <!-- 2. Realisasi Hadir -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Hadir</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="ri-user-received-2-line"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600"><?= number_format($summary['sum_hadir_all'] ?? 0) ?></div>
                <div class="text-[11px] text-emerald-700 font-semibold mt-0.5"><?= $summary['percent_presence_aggregate'] ?? 0 ?>% Kehadiran</div>
            </div>
        </div>

        <!-- 3. Tepat Waktu -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tepat Waktu</span>
                <span class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base">
                    <i class="ri-checkbox-circle-line"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-teal-600"><?= number_format($summary['sum_ontime_all'] ?? 0) ?></div>
                <?php 
                $pctTepatAgg = ($summary['sum_hadir_all'] ?? 0) > 0 ? round((($summary['sum_ontime_all'] ?? 0) / $summary['sum_hadir_all']) * 100, 1) : 0;
                ?>
                <div class="text-[11px] text-teal-700 font-medium mt-0.5"><?= $pctTepatAgg ?>% dari hadir</div>
            </div>
        </div>

        <!-- 4. Terlambat -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Terlambat</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                    <i class="ri-time-line"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-amber-600"><?= number_format($summary['sum_late_all'] ?? 0) ?></div>
                <?php 
                $pctTelatAgg = ($summary['sum_hadir_all'] ?? 0) > 0 ? round((($summary['sum_late_all'] ?? 0) / $summary['sum_hadir_all']) * 100, 1) : 0;
                ?>
                <div class="text-[11px] text-amber-700 font-medium mt-0.5"><?= $pctTelatAgg ?>% dari hadir</div>
            </div>
        </div>

        <!-- 5. Izin / Sakit -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Izin / Sakit</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                    <i class="ri-survey-line"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-blue-600"><?= number_format($summary['sum_izin_all'] ?? 0) ?></div>
                <div class="text-[11px] text-blue-700 font-medium mt-0.5"><?= $summary['total_peserta_izin'] ?? 0 ?> orang berizin</div>
            </div>
        </div>

        <!-- 6. Tidak Hadir (Alpa) -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tidak Hadir</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base">
                    <i class="ri-close-circle-line"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-rose-600"><?= number_format($summary['sum_alpa_all'] ?? 0) ?></div>
                <div class="text-[11px] text-rose-700 font-medium mt-0.5"><?= $summary['total_peserta_alpa'] ?? 0 ?> orang alpa</div>
            </div>
        </div>
    </div>

    <!-- Data Table Container (Clean & Airly Design) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Table Toolbar & Filters -->
        <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    <i class="ri-list-check text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        Daftar Akumulasi Kehadiran & Evaluasi Kedisiplinan
                        <span class="text-xs font-normal text-gray-500">(<span id="visibleCount"><?= count($recap) ?></span> terdata)</span>
                    </h3>
                    <p class="text-xs text-gray-500">
                        Periode: <?= date('d M Y', strtotime($startDate)) ?> s/d <?= date('d M Y', strtotime($endDate)) ?> &bull; Klik nama peserta atau tombol aksi untuk membuka detail sesi.
                    </p>
                </div>
            </div>

            <!-- Quick Filter Tabs & Search -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 print:hidden">
                <div class="relative w-full sm:w-60">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="tableSearch" placeholder="Cari nama / NIS / NIP..." class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="inline-flex p-1 bg-gray-100 rounded-xl text-xs font-medium text-gray-600">
                    <button type="button" onclick="filterStatus('all')" id="tab-all" class="status-tab px-3 py-1 rounded-lg bg-white text-gray-900 font-bold shadow-xs transition">
                        Semua
                    </button>
                    <button type="button" onclick="filterStatus('disiplin')" id="tab-disiplin" class="status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition">
                        Tertib (<?= $summary['total_peserta_disiplin'] ?? 0 ?>)
                    </button>
                    <button type="button" onclick="filterStatus('warning')" id="tab-warning" class="status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition">
                        Perlu Perhatian (<?= $summary['total_peserta_warning'] ?? 0 ?>)
                    </button>
                    <button type="button" onclick="filterStatus('late')" id="tab-late" class="status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition">
                        Telat (<?= $summary['total_peserta_late'] ?? 0 ?>)
                    </button>
                    <button type="button" onclick="filterStatus('alpa')" id="tab-alpa" class="status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition">
                        Ada Alpa (<?= $summary['total_peserta_alpa'] ?? 0 ?>)
                    </button>
                    <button type="button" onclick="filterStatus('permission')" id="tab-permission" class="status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition">
                        Izin (<?= $summary['total_peserta_izin'] ?? 0 ?>)
                    </button>
                </div>
            </div>
        </div>

        <!-- Clean, Spacious Main Table (Consolidated Columns) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="recapTable">
                <thead>
                    <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 select-none">
                        <th class="py-3.5 px-3 text-center w-12 cursor-pointer hover:bg-gray-100" onclick="sortTable(0, 'int')">
                            No <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3.5 px-4 cursor-pointer hover:bg-gray-100 min-w-[200px]" onclick="sortTable(1, 'str')">
                            Nama Lengkap & NIP/NIS <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3.5 px-3 cursor-pointer hover:bg-gray-100" onclick="sortTable(2, 'str')">
                            Peran / Kelas <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3.5 px-3 text-center cursor-pointer hover:bg-gray-100 bg-indigo-50/40 text-indigo-900" onclick="sortTable(3, 'int')" title="Total sesi yang wajib dihadiri dalam periode ini">
                            Kewajiban <i class="ri-arrow-up-down-line text-[10px] text-indigo-400"></i>
                        </th>
                        <th class="py-3.5 px-4 text-center cursor-pointer hover:bg-gray-100 font-extrabold text-emerald-800" onclick="sortTable(4, 'int')" title="Total kehadiran dan rincian tepat/telat">
                            Realisasi Hadir <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3.5 px-3 text-center cursor-pointer hover:bg-gray-100" onclick="sortTable(5, 'int')" title="Jumlah sesi alpa atau izin">
                            Izin &amp; Alpa <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3.5 px-4 text-center cursor-pointer hover:bg-gray-100 w-36" onclick="sortTable(6, 'float')" title="Persentase kehadiran fisik (Hadir / Kewajiban)">
                            % Hadir <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3.5 px-4 text-center cursor-pointer hover:bg-gray-100" onclick="sortTable(7, 'str')">
                            Status Kedisiplinan <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3.5 px-3 text-center w-20 print:hidden">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($recap)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-12 text-gray-400">
                                <i class="ri-inbox-line text-3xl mb-1 block"></i>
                                Tidak ada data santri atau pengajar pada rentang tanggal atau filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($recap as $row): 
                            $pKey = $row['participant_key'] ?? ($row['person_type'] . '_' . $row['person_id']);
                            $kewajiban = (int)($row['kewajiban'] ?? 0);
                            $hadir = (int)($row['total_attendance'] ?? 0);
                            $tepat = (int)($row['count_on_time'] ?? 0);
                            $telat = (int)($row['count_late'] ?? 0);
                            $izin = (int)($row['count_permission'] ?? 0);
                            $alpa = (int)($row['count_alpa'] ?? 0);
                            $pctHadir = (float)($row['percent_presence'] ?? 0);
                            $pctKepatuhan = (float)($row['percent_compliance'] ?? 0);
                            $statusKey = $row['status_key'] ?? 'disiplin';
                            $statusLabel = $row['status_label'] ?? 'Disiplin';
                            $badgeColor = $row['badge_color'] ?? 'emerald';
                            $permReasons = $row['permission_reasons'] ?? '';

                            // Tag data-filter
                            $filterTags = [];
                            if ($kewajiban === 0) {
                                $filterTags[] = 'exempted';
                            }
                            if ($pctKepatuhan >= 85 && $kewajiban > 0) {
                                $filterTags[] = 'disiplin';
                            } else if ($kewajiban > 0) {
                                $filterTags[] = 'warning';
                            }
                            if ($telat > 0) {
                                $filterTags[] = 'late';
                            }
                            if ($alpa > 0) {
                                $filterTags[] = 'alpa';
                            }
                            if ($izin > 0) {
                                $filterTags[] = 'permission';
                            }
                            $filterTagsStr = implode(' ', $filterTags);

                            // Avatar initials
                            $initial = strtoupper(mb_substr($row['name'] ?: 'U', 0, 1));
                        ?>
                            <tr class="recap-row hover:bg-indigo-50/30 transition group" data-filter="<?= $filterTagsStr ?>" data-search="<?= strtolower(htmlspecialchars(($row['name'] ?? '') . ' ' . ($row['identifier'] ?? '') . ' ' . ($row['subinfo'] ?? ''))) ?>">
                                <td class="py-3 px-3 text-xs font-medium text-gray-400 text-center row-num" data-val="<?= $no ?>"><?= $no++ ?></td>
                                
                                <!-- Nama Lengkap & NIP/NIS (CLICKABLE TO OPEN MODAL) -->
                                <td class="py-3 px-4 cursor-pointer" onclick="openDetailModal('<?= $pKey ?>')" data-val="<?= htmlspecialchars($row['name'] ?? '') ?>" title="Klik untuk melihat detail presensi &amp; riwayat sesi">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-700 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 group-hover:ring-2 group-hover:ring-indigo-300 transition-all">
                                            <?= $initial ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-gray-900 group-hover:text-indigo-600 transition flex items-center gap-1.5 truncate">
                                                <?= htmlspecialchars($row['name'] ?: 'Tanpa Nama') ?>
                                                <?php if ($statusKey === 'perfect'): ?>
                                                    <i class="ri-star-fill text-amber-400 text-xs shrink-0" title="Presensi Sempurna 100%"></i>
                                                <?php elseif ($alpa > 0): ?>
                                                    <i class="ri-close-circle-fill text-rose-500 text-xs shrink-0" title="Ada Alpa: <?= $alpa ?>x"></i>
                                                <?php elseif ($telat > 0): ?>
                                                    <i class="ri-alarm-warning-fill text-amber-500 text-xs shrink-0" title="Ada Terlambat: <?= $telat ?>x"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[11px] text-gray-400 font-mono tracking-tight flex items-center gap-2">
                                                <span><?= htmlspecialchars($row['identifier'] ?: '-') ?></span>
                                                <span class="text-[10px] text-indigo-500 font-sans opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-0.5">
                                                    <i class="ri-arrow-right-up-line"></i> detail
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Peran / Kelas -->
                                <td class="py-3 px-3" data-val="<?= htmlspecialchars($row['subinfo'] ?? '') ?>">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?= ($row['person_type'] ?? '') == 'student' ? 'bg-indigo-50 text-indigo-700' : 'bg-purple-50 text-purple-700' ?>">
                                        <?= htmlspecialchars($row['subinfo'] ?: '-') ?>
                                    </span>
                                </td>

                                <!-- Kewajiban Sesi -->
                                <td class="py-3 px-3 text-center bg-indigo-50/20" data-val="<?= $kewajiban ?>">
                                    <?php if ($kewajiban > 0): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold text-indigo-950 bg-indigo-100/70">
                                            <?= $kewajiban ?> Sesi
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic font-mono">Bebas</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Realisasi Hadir (Consolidated: Hadir + Tepat + Telat) -->
                                <td class="py-3 px-4 text-center" data-val="<?= $hadir ?>">
                                    <?php if ($hadir > 0): ?>
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                                <?= $hadir ?>x Hadir
                                            </span>
                                            <div class="text-[10px] text-gray-500 font-medium mt-0.5 flex items-center gap-1.5">
                                                <span class="text-teal-700"><?= $tepat ?> tepat</span>
                                                <span class="text-gray-300">&bull;</span>
                                                <span class="<?= $telat > 0 ? 'text-amber-700 font-bold' : 'text-gray-400' ?>"><?= $telat ?> telat</span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-300 font-mono">0 Hadir</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Izin & Alpa (Consolidated) -->
                                <td class="py-3 px-3 text-center" data-val="<?= $alpa * 100 + $izin ?>">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <?php if ($alpa > 0): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-300" title="Tidak Hadir tanpa keterangan: <?= $alpa ?> sesi">
                                                <?= $alpa ?>x Alpa
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($izin > 0): ?>
                                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 cursor-help" title="<?= htmlspecialchars($permReasons ?: 'Dispensasi Resmi') ?>">
                                                <i class="ri-file-text-line text-[10px]"></i> <?= $izin ?>x Izin
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($alpa === 0 && $izin === 0): ?>
                                            <span class="text-xs text-gray-300">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- % Kehadiran & Visual Progress Bar -->
                                <td class="py-3 px-4 text-center" data-val="<?= $pctHadir ?>">
                                    <?php if ($kewajiban > 0): ?>
                                        <div class="flex items-center justify-between text-xs font-bold mb-1">
                                            <span class="<?= $pctHadir >= 85 ? 'text-emerald-700' : ($pctHadir >= 70 ? 'text-amber-700' : 'text-rose-700') ?>">
                                                <?= number_format($pctHadir, 1) ?>%
                                            </span>
                                            <span class="text-[10px] text-gray-400 font-mono"><?= $hadir ?>/<?= $kewajiban ?></span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-full rounded-full <?= $pctHadir >= 85 ? 'bg-emerald-500' : ($pctHadir >= 70 ? 'bg-amber-500' : 'bg-rose-500') ?>" style="width: <?= min(100, $pctHadir) ?>%"></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Bebas Tugas</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status / Ketertiban Badge -->
                                <td class="py-3 px-4 text-center" data-val="<?= htmlspecialchars($statusLabel) ?>">
                                    <?php if ($statusKey === 'exempted'): ?>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-full border border-gray-200">
                                            <i class="ri-shield-user-line"></i> Bebas Tugas
                                        </span>
                                    <?php elseif ($statusKey === 'perfect'): ?>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-300 shadow-xs">
                                            <i class="ri-star-fill text-amber-500"></i> Sangat Disiplin
                                        </span>
                                    <?php elseif ($statusKey === 'disiplin' || $statusKey === 'disiplin_late'): ?>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-teal-800 bg-teal-50 px-2.5 py-1 rounded-full border border-teal-200">
                                            <i class="ri-checkbox-circle-fill text-teal-600"></i> <?= htmlspecialchars($statusLabel) ?>
                                        </span>
                                    <?php elseif ($statusKey === 'warning'): ?>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                                            <i class="ri-alert-line text-amber-600"></i> <?= htmlspecialchars($statusLabel) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-black text-rose-800 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-300">
                                            <i class="ri-close-circle-fill text-rose-600"></i> <?= htmlspecialchars($statusLabel) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Aksi (Buka Modal Detail) -->
                                <td class="py-3 px-3 text-center print:hidden">
                                    <button type="button" onclick="openDetailModal('<?= $pKey ?>')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 transition" title="Lihat Rekap &amp; Riwayat Sesi">
                                        <i class="ri-eye-line"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Pop-Up Detail Presensi & Evaluasi Peserta -->
<div id="modalParticipantDetail" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" onclick="closeDetailModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-3xl border border-gray-100 flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="p-5 sm:p-6 border-b border-gray-100 flex items-start justify-between bg-gradient-to-r from-gray-50 via-white to-indigo-50/40">
                <div class="flex items-center gap-4">
                    <div id="modalAvatar" class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white text-lg font-bold flex items-center justify-center shadow-sm shrink-0">
                        U
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-lg font-bold text-gray-900" id="modalName">-</h3>
                            <span id="modalStatusBadge" class="text-xs font-bold px-2.5 py-0.5 rounded-full">-</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-2 font-mono">
                            <span id="modalIdentifier">-</span>
                            <span>&bull;</span>
                            <span id="modalSubinfo" class="font-sans font-medium text-gray-600">-</span>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-xl bg-white hover:bg-gray-100 text-gray-400 hover:text-gray-700 flex items-center justify-center border border-gray-200 transition shrink-0">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-6">
                <!-- KPI Breakdown Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-100 text-center">
                        <div class="text-[11px] font-semibold text-gray-500 uppercase">Kewajiban</div>
                        <div class="text-xl font-black text-gray-900 mt-1" id="modalKewajiban">0</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">Sesi Wajib</div>
                    </div>
                    <div class="bg-emerald-50/60 p-3.5 rounded-xl border border-emerald-100 text-center">
                        <div class="text-[11px] font-semibold text-emerald-700 uppercase">Tepat Waktu</div>
                        <div class="text-xl font-black text-emerald-700 mt-1" id="modalOnTime">0</div>
                        <div class="text-[10px] text-emerald-600 mt-0.5">Sesi Tepat</div>
                    </div>
                    <div class="bg-amber-50/60 p-3.5 rounded-xl border border-amber-100 text-center">
                        <div class="text-[11px] font-semibold text-amber-700 uppercase">Terlambat</div>
                        <div class="text-xl font-black text-amber-700 mt-1" id="modalLate">0</div>
                        <div class="text-[10px] text-amber-600 mt-0.5">Sesi Telat</div>
                    </div>
                    <div class="bg-blue-50/60 p-3.5 rounded-xl border border-blue-100 text-center">
                        <div class="text-[11px] font-semibold text-blue-700 uppercase">Izin / Sakit</div>
                        <div class="text-xl font-black text-blue-700 mt-1" id="modalPermission">0</div>
                        <div class="text-[10px] text-blue-600 mt-0.5">Dispensasi</div>
                    </div>
                    <div class="bg-rose-50/60 p-3.5 rounded-xl border border-rose-100 text-center col-span-2 sm:col-span-1">
                        <div class="text-[11px] font-semibold text-rose-700 uppercase">Alpa</div>
                        <div class="text-xl font-black text-rose-700 mt-1" id="modalAlpa">0</div>
                        <div class="text-[10px] text-rose-600 mt-0.5">Tanpa Keterangan</div>
                    </div>
                </div>

                <!-- Progress & Compliance Rate -->
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="w-full sm:w-1/2">
                        <div class="flex justify-between text-xs font-bold text-gray-700 mb-1">
                            <span>Tingkat Kehadiran Fisik</span>
                            <span id="modalPresenceText">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div id="modalPresenceBar" class="h-full rounded-full bg-emerald-500 transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="w-full sm:w-1/2">
                        <div class="flex justify-between text-xs font-bold text-gray-700 mb-1">
                            <span>Tingkat Kepatuhan (Hadir + Izin)</span>
                            <span id="modalComplianceText">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div id="modalComplianceBar" class="h-full rounded-full bg-indigo-500 transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- Detail Riwayat Sesi Periode Ini -->
                <div>
                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i class="ri-history-line text-indigo-600"></i> Riwayat Presensi Per Sesi (Periode Ini)
                        </span>
                        <span id="modalSessionsCount" class="text-[11px] font-normal text-gray-500">0 sesi</span>
                    </h4>
                    <div class="border border-gray-100 rounded-xl overflow-hidden shadow-xs">
                        <div class="max-h-64 overflow-y-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase sticky top-0 border-b border-gray-100 select-none">
                                    <tr>
                                        <th class="py-2.5 px-3 text-center w-10">No</th>
                                        <th class="py-2.5 px-3">Hari &amp; Tanggal</th>
                                        <th class="py-2.5 px-3">Kegiatan / Sesi</th>
                                        <th class="py-2.5 px-3 text-center">Waktu Tap</th>
                                        <th class="py-2.5 px-3 text-center">Status</th>
                                        <th class="py-2.5 px-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="modalSessionsTbody" class="divide-y divide-gray-100">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                <div class="text-[11px] text-gray-500">
                    <i class="ri-information-line"></i> Data presensi tercatat otomatis melalui RFID Smart Tap &amp; Dispensasi resmi.
                </div>
                <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold rounded-xl border border-gray-200 transition">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// Dictionary data lengkap peserta untuk pop-up modal
const participantDetails = <?= json_encode($modalDataMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

let currentFilterTag = 'all';
let currentSortCol = null;
let currentSortDir = 'asc';

// Open Participant Detail Modal
function openDetailModal(key) {
    const data = participantDetails[key];
    if (!data) return;

    // Header info
    document.getElementById('modalAvatar').innerText = (data.name || 'U').charAt(0).toUpperCase();
    document.getElementById('modalName').innerText = data.name || 'Tanpa Nama';
    document.getElementById('modalIdentifier').innerText = data.identifier || '-';
    document.getElementById('modalSubinfo').innerText = data.subinfo || '-';

    // Status Badge
    const badge = document.getElementById('modalStatusBadge');
    badge.innerText = data.status_label || 'Disiplin';
    badge.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full ';
    if (data.status_key === 'perfect') {
        badge.className += 'bg-emerald-100 text-emerald-800 border border-emerald-300';
    } else if (data.status_key === 'disiplin' || data.status_key === 'disiplin_late') {
        badge.className += 'bg-teal-50 text-teal-800 border border-teal-200';
    } else if (data.status_key === 'warning') {
        badge.className += 'bg-amber-50 text-amber-800 border border-amber-200';
    } else if (data.status_key === 'exempted') {
        badge.className += 'bg-gray-100 text-gray-700 border border-gray-200';
    } else {
        badge.className += 'bg-rose-50 text-rose-800 border border-rose-200';
    }

    // KPI Numbers
    document.getElementById('modalKewajiban').innerText = data.kewajiban;
    document.getElementById('modalOnTime').innerText = data.count_on_time;
    document.getElementById('modalLate').innerText = data.count_late;
    document.getElementById('modalPermission').innerText = data.count_permission;
    document.getElementById('modalAlpa').innerText = data.count_alpa;

    // Progress Bars
    const pctPres = Math.min(100, Math.max(0, data.percent_presence));
    const pctComp = Math.min(100, Math.max(0, data.percent_compliance));
    document.getElementById('modalPresenceText').innerText = pctPres + '% (' + data.total_attendance + '/' + data.kewajiban + ')';
    document.getElementById('modalPresenceBar').style.width = pctPres + '%';
    document.getElementById('modalComplianceText').innerText = pctComp + '%';
    document.getElementById('modalComplianceBar').style.width = pctComp + '%';

    // Sessions Table
    const tbody = document.getElementById('modalSessionsTbody');
    tbody.innerHTML = '';
    const sessions = data.sessions || [];
    document.getElementById('modalSessionsCount').innerText = sessions.length + ' sesi wajib';

    if (sessions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-6 text-gray-400">
                    Tidak ada sesi wajib yang terjadwal untuk peserta ini pada periode tersebut.
                </td>
            </tr>
        `;
    } else {
        sessions.forEach((s, idx) => {
            let statusPill = '';
            if (s.status === 'on_time') {
                statusPill = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200"><i class="ri-checkbox-circle-fill mr-1"></i> Tepat Waktu</span>';
            } else if (s.status === 'late') {
                statusPill = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"><i class="ri-time-fill mr-1"></i> Terlambat</span>';
            } else if (s.status === 'permission') {
                statusPill = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="ri-file-text-line mr-1"></i> Izin / Sakit</span>';
            } else {
                statusPill = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200"><i class="ri-close-circle-fill mr-1"></i> Alpa</span>';
            }

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50/80 transition';
            tr.innerHTML = `
                <td class="py-2.5 px-3 text-center text-gray-400 font-mono">${idx + 1}</td>
                <td class="py-2.5 px-3 font-medium text-gray-800">
                    <div>${s.day_name || '-'}</div>
                    <div class="text-[10px] text-gray-400 font-mono">${s.date_formatted || s.date}</div>
                </td>
                <td class="py-2.5 px-3 font-semibold text-gray-900">${s.event_name || '-'}</td>
                <td class="py-2.5 px-3 text-center font-mono text-gray-600">${s.tap_time !== '-' ? s.tap_time + ' WIB' : '-'}</td>
                <td class="py-2.5 px-3 text-center">${statusPill}</td>
                <td class="py-2.5 px-3 text-gray-500 text-[11px]">${s.note || '-'}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    const modal = document.getElementById('modalParticipantDetail');
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Close Modal
function closeDetailModal() {
    const modal = document.getElementById('modalParticipantDetail');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeDetailModal();
    }
});

// Filter by Status Tab
function filterStatus(tag) {
    currentFilterTag = tag;

    document.querySelectorAll('.status-tab').forEach(tab => {
        tab.className = 'status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition';
    });
    const activeTab = document.getElementById('tab-' + tag);
    if (activeTab) {
        activeTab.className = 'status-tab px-3 py-1 rounded-lg bg-white text-gray-900 font-bold shadow-xs transition';
    }

    applyTableFilters();
}

// Search and Filter Application
function applyTableFilters() {
    const searchQuery = (document.getElementById('tableSearch')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.recap-row');
    let visibleCount = 0;
    let num = 1;

    rows.forEach(row => {
        const rowFilters = (row.getAttribute('data-filter') || '').split(' ');
        const rowSearch = row.getAttribute('data-search') || '';

        const matchesTag = (currentFilterTag === 'all') || rowFilters.includes(currentFilterTag);
        const matchesSearch = !searchQuery || rowSearch.includes(searchQuery);

        if (matchesTag && matchesSearch) {
            row.style.display = '';
            visibleCount++;
            const numCell = row.querySelector('.row-num');
            if (numCell) numCell.innerText = num++;
        } else {
            row.style.display = 'none';
        }
    });

    const countElem = document.getElementById('visibleCount');
    if (countElem) {
        countElem.innerText = visibleCount;
    }
}

document.getElementById('tableSearch')?.addEventListener('input', applyTableFilters);

// Interactive Table Column Sorter
function sortTable(columnIndex, type) {
    const table = document.getElementById('recapTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('.recap-row'));
    if (!rows.length) return;

    if (currentSortCol === columnIndex) {
        currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortCol = columnIndex;
        currentSortDir = (type === 'str') ? 'asc' : 'desc';
    }

    rows.sort((a, b) => {
        const cellA = a.children[columnIndex];
        const cellB = b.children[columnIndex];
        let valA = cellA.getAttribute('data-val') ?? cellA.innerText.trim();
        let valB = cellB.getAttribute('data-val') ?? cellB.innerText.trim();

        if (type === 'int' || type === 'float') {
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
            return currentSortDir === 'asc' ? valA - valB : valB - valA;
        } else {
            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();
            return currentSortDir === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
        }
    });

    rows.forEach(row => tbody.appendChild(row));
    applyTableFilters();
}
</script>

<?php
renderFooter();
?>