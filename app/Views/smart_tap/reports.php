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
            <button type="button" onclick="exportTableToExcel('recapTable', 'Rekap_Kedisiplinan_Smart_Tap')" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition">
                <i class="ri-file-excel-2-line"></i> Ekspor Excel (.xls)
            </button>
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

    <!-- KPI Summary Cards (Berdasarkan Kewajiban Sesi & Realisasi Presensi) -->
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

    <!-- Data Table Container -->
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
                        Periode: <?= date('d M Y', strtotime($startDate)) ?> s/d <?= date('d M Y', strtotime($endDate)) ?> &bull; Klik judul kolom untuk mengurutkan data (sort).
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

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="recapTable">
                <thead>
                    <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 select-none">
                        <th class="py-3 px-3 text-center w-12 cursor-pointer hover:bg-gray-100" onclick="sortTable(0, 'int')">
                            No <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3 px-4 cursor-pointer hover:bg-gray-100" onclick="sortTable(1, 'str')">
                            Nama Lengkap & NIP/NIS <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3 px-3 cursor-pointer hover:bg-gray-100" onclick="sortTable(2, 'str')">
                            Peran / Kelas <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3 px-3 text-center cursor-pointer hover:bg-gray-100 bg-indigo-50/40 text-indigo-900" onclick="sortTable(3, 'int')" title="Total sesi yang wajib dihadiri dalam periode ini">
                            Kewajiban <i class="ri-arrow-up-down-line text-[10px] text-indigo-400"></i>
                        </th>
                        <th class="py-3 px-3 text-center cursor-pointer hover:bg-gray-100 font-extrabold text-emerald-800" onclick="sortTable(4, 'int')" title="Total sesi hadir (Tepat Waktu + Terlambat)">
                            Total Hadir <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3 px-3 text-center cursor-pointer hover:bg-gray-100 text-teal-700" onclick="sortTable(5, 'int')">
                            Tepat <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3 px-3 text-center cursor-pointer hover:bg-gray-100 text-amber-700" onclick="sortTable(6, 'int')">
                            Telat <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3 px-3 text-center cursor-pointer hover:bg-gray-100 text-blue-700" onclick="sortTable(7, 'int')">
                            Izin <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3 px-3 text-center cursor-pointer hover:bg-gray-100 text-rose-700 bg-rose-50/30" onclick="sortTable(8, 'int')" title="Kewajiban dikurangi Hadir & Izin">
                            Alpa <i class="ri-arrow-up-down-line text-[10px] text-rose-400"></i>
                        </th>
                        <th class="py-3 px-4 text-center cursor-pointer hover:bg-gray-100 w-36" onclick="sortTable(9, 'float')" title="Persentase kehadiran fisik (Hadir / Kewajiban)">
                            % Hadir <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                        <th class="py-3 px-4 text-center cursor-pointer hover:bg-gray-100" onclick="sortTable(10, 'str')">
                            Status Kedisiplinan <i class="ri-arrow-up-down-line text-[10px] text-gray-400"></i>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($recap)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-12 text-gray-400">
                                <i class="ri-inbox-line text-3xl mb-1 block"></i>
                                Tidak ada data santri atau pengajar pada rentang tanggal atau filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($recap as $row): 
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
                        ?>
                            <tr class="recap-row hover:bg-gray-50/80 transition" data-filter="<?= $filterTagsStr ?>" data-search="<?= strtolower(htmlspecialchars(($row['name'] ?? '') . ' ' . ($row['identifier'] ?? '') . ' ' . ($row['subinfo'] ?? ''))) ?>">
                                <td class="py-3 px-3 text-xs font-medium text-gray-400 text-center row-num" data-val="<?= $no ?>"><?= $no++ ?></td>
                                
                                <!-- Nama Lengkap & NIP/NIS -->
                                <td class="py-3 px-4" data-val="<?= htmlspecialchars($row['name'] ?? '') ?>">
                                    <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                        <?= htmlspecialchars($row['name'] ?: 'Tanpa Nama') ?>
                                        <?php if ($statusKey === 'perfect'): ?>
                                            <i class="ri-star-fill text-amber-400 text-xs" title="Presensi Sempurna 100%"></i>
                                        <?php elseif ($alpa > 0): ?>
                                            <i class="ri-close-circle-fill text-rose-500 text-xs" title="Ada Alpa: <?= $alpa ?>x"></i>
                                        <?php elseif ($telat > 0): ?>
                                            <i class="ri-alarm-warning-fill text-amber-500 text-xs" title="Ada Terlambat: <?= $telat ?>x"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] text-gray-400 font-mono tracking-tight"><?= htmlspecialchars($row['identifier'] ?: '-') ?></div>
                                </td>

                                <!-- Peran / Kelas -->
                                <td class="py-3 px-3" data-val="<?= htmlspecialchars($row['subinfo'] ?? '') ?>">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?= ($row['person_type'] ?? '') == 'student' ? 'bg-indigo-50 text-indigo-700' : 'bg-purple-50 text-purple-700' ?>">
                                        <?= htmlspecialchars($row['subinfo'] ?: '-') ?>
                                    </span>
                                </td>

                                <!-- Kewajiban Sesi -->
                                <td class="py-3 px-3 text-center bg-indigo-50/30" data-val="<?= $kewajiban ?>">
                                    <?php if ($kewajiban > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold text-indigo-950 bg-indigo-100/70">
                                            <?= $kewajiban ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic font-mono">Bebas</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Total Hadir -->
                                <td class="py-3 px-3 text-center font-extrabold" data-val="<?= $hadir ?>">
                                    <?php if ($hadir > 0): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-extrabold text-emerald-800 bg-emerald-100/70">
                                            <?= $hadir ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-300">0</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Tepat Waktu -->
                                <td class="py-3 px-3 text-center" data-val="<?= $tepat ?>">
                                    <?php if ($tepat > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200/60">
                                            <?= $tepat ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-300">0</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Terlambat -->
                                <td class="py-3 px-3 text-center" data-val="<?= $telat ?>">
                                    <?php if ($telat > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <?= $telat ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-300">0</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Izin / Dispensasi -->
                                <td class="py-3 px-3 text-center" data-val="<?= $izin ?>">
                                    <?php if ($izin > 0): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 cursor-help" title="<?= htmlspecialchars($permReasons ?: 'Dispensasi Resmi') ?>">
                                            <i class="ri-file-text-line"></i> <?= $izin ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-300">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Alpa / Tidak Hadir -->
                                <td class="py-3 px-3 text-center bg-rose-50/20" data-val="<?= $alpa ?>">
                                    <?php if ($alpa > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-300">
                                            <?= $alpa ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-300">0</span>
                                    <?php endif; ?>
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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
let currentFilterTag = 'all';
let currentSortCol = null;
let currentSortDir = 'asc';

function filterStatus(tag) {
    currentFilterTag = tag;

    // Update active tab styles
    document.querySelectorAll('.status-tab').forEach(tab => {
        tab.className = 'status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition';
    });
    const activeTab = document.getElementById('tab-' + tag);
    if (activeTab) {
        activeTab.className = 'status-tab px-3 py-1 rounded-lg bg-white text-gray-900 font-bold shadow-xs transition';
    }

    applyTableFilters();
}

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
        currentSortDir = (type === 'str') ? 'asc' : 'desc'; // Angka default desc (terbesar di atas)
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

    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));

    // Re-number visible rows
    applyTableFilters();
}

// Client-Side Excel Export (.xls HTML Table)
function exportTableToExcel(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let html = `
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <!--[if gte mso 9]>
        <xml>
        <x:ExcelWorkbook>
        <x:ExcelWorksheets>
            <x:ExcelWorksheet>
                <x:Name>Rekap Kedisiplinan</x:Name>
                <x:WorksheetOptions>
                    <x:DisplayGridlines/>
                </x:WorksheetOptions>
            </x:ExcelWorksheet>
        </x:ExcelWorksheets>
        </x:ExcelWorkbook>
        </xml>
        <![endif]-->
        <meta http-equiv="content-type" content="text/plain; charset=UTF-8"/>
        <style>
            table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; }
            th { background-color: #1e3a8a; color: #ffffff; border: 1px solid #cbd5e1; padding: 8px; text-align: center; }
            td { border: 1px solid #cbd5e1; padding: 6px; }
            .num { text-align: center; }
        </style>
    </head>
    <body>
        <h2>REKAPITULASI & KEDISIPLINAN SMART TAP</h2>
        <p>Periode: <?= htmlspecialchars($startDate) ?> s/d <?= htmlspecialchars($endDate) ?></p>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>NIP / NIS</th>
                    <th>Kategori / Peran</th>
                    <th>Kewajiban Sesi</th>
                    <th>Total Hadir</th>
                    <th>Tepat Waktu</th>
                    <th>Terlambat</th>
                    <th>Izin / Sakit</th>
                    <th>Alpa</th>
                    <th>% Kehadiran</th>
                    <th>Status Kedisiplinan</th>
                </tr>
            </thead>
            <tbody>
    `;

    const visibleRows = Array.from(table.querySelectorAll('tbody tr.recap-row')).filter(r => r.style.display !== 'none');
    let idx = 1;
    visibleRows.forEach(row => {
        const cells = row.children;
        const nameDiv = cells[1].querySelector('div:first-child');
        const identDiv = cells[1].querySelector('div:last-child');
        const name = nameDiv ? nameDiv.innerText.trim() : '';
        const ident = identDiv ? identDiv.innerText.trim() : '';
        const role = cells[2].innerText.trim();
        const kewajiban = cells[3].getAttribute('data-val') || '0';
        const hadir = cells[4].getAttribute('data-val') || '0';
        const tepat = cells[5].getAttribute('data-val') || '0';
        const telat = cells[6].getAttribute('data-val') || '0';
        const izin = cells[7].getAttribute('data-val') || '0';
        const alpa = cells[8].getAttribute('data-val') || '0';
        const pct = (cells[9].getAttribute('data-val') || '0') + '%';
        const status = cells[10].innerText.trim();

        html += `
            <tr>
                <td class="num">${idx++}</td>
                <td>${name}</td>
                <td style="mso-number-format:'\\@'">${ident}</td>
                <td>${role}</td>
                <td class="num">${kewajiban}</td>
                <td class="num">${hadir}</td>
                <td class="num">${tepat}</td>
                <td class="num">${telat}</td>
                <td class="num">${izin}</td>
                <td class="num">${alpa}</td>
                <td class="num">${pct}</td>
                <td>${status}</td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    </body>
    </html>
    `;

    const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${filename}_<?= date('Ymd_His') ?>.xls`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>

<?php
renderFooter();
?>
