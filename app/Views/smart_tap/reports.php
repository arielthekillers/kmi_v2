<?php
renderHeader("Smart Tap – Rekapitulasi & Laporan Kedisiplinan");
$summary = $summary ?? [
    'total_target' => count($recap),
    'total_hadir' => 0,
    'total_ontime' => 0,
    'total_late' => 0,
    'total_izin' => 0,
    'total_tidak_hadir' => 0,
    'percent_presence' => 0,
    'sum_taps_ontime' => 0,
    'sum_taps_late' => 0,
    'sum_taps_total' => 0
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
                Laporan akumulasi kehadiran, keterlambatan, dispensasi/izin, dan evaluasi ketertiban santri & pengajar.
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
                    <option value="">Semua Kegiatan / Event</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= $ev['id'] ?>" <?= $eventId == $ev['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ev['name']) ?> (<?= $ev['target_type'] == 'teachers' ? 'Guru' : ($ev['target_type'] == 'students' ? 'Santri' : 'Semua') ?>)
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

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- 1. Wajib Hadir (Target) -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Wajib Hadir</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base">
                    <i class="ri-team-line"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900"><?= $summary['total_target'] ?></div>
                <div class="text-[11px] text-gray-400 mt-0.5">Target Terdata</div>
            </div>
        </div>

        <!-- 2. Total Hadir -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Hadir</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="ri-user-received-2-line"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600"><?= $summary['total_hadir'] ?></div>
                <div class="text-[11px] text-emerald-700 font-medium mt-0.5"><?= $summary['percent_presence'] ?>% Kehadiran</div>
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
                <div class="text-2xl sm:text-3xl font-extrabold text-teal-600"><?= $summary['total_ontime'] ?></div>
                <div class="text-[11px] text-gray-400 mt-0.5"><?= $summary['sum_taps_ontime'] ?>x tap tepat</div>
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
                <div class="text-2xl sm:text-3xl font-extrabold text-amber-600"><?= $summary['total_late'] ?></div>
                <div class="text-[11px] text-gray-400 mt-0.5"><?= $summary['sum_taps_late'] ?>x tap telat</div>
            </div>
        </div>

        <!-- 5. Izin / Dispensasi -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Izin / Sakit</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                    <i class="ri-survey-line"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-blue-600"><?= $summary['total_izin'] ?></div>
                <div class="text-[11px] text-gray-400 mt-0.5">Dispensasi Resmi</div>
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
                <div class="text-2xl sm:text-3xl font-extrabold text-rose-600"><?= $summary['total_tidak_hadir'] ?></div>
                <div class="text-[11px] text-gray-400 mt-0.5">Alpa / Ghaib</div>
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
                    <h3 class="text-base font-bold text-gray-900">
                        Daftar Akumulasi Kehadiran (<span id="visibleCount"><?= count($recap) ?></span> terdata)
                    </h3>
                    <p class="text-xs text-gray-500">
                        Periode: <?= date('d M Y', strtotime($startDate)) ?> s/d <?= date('d M Y', strtotime($endDate)) ?>
                    </p>
                </div>
            </div>

            <!-- Quick Filter Tabs & Search -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 print:hidden">
                <div class="relative w-full sm:w-64">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="tableSearch" placeholder="Cari nama / NIS / NIP..." class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="inline-flex p-1 bg-gray-100 rounded-xl text-xs font-medium text-gray-600">
                    <button type="button" onclick="filterStatus('all')" id="tab-all" class="status-tab px-3 py-1 rounded-lg bg-white text-gray-900 font-bold shadow-xs transition">
                        Semua
                    </button>
                    <button type="button" onclick="filterStatus('ontime')" id="tab-ontime" class="status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition">
                        Tepat (<?= $summary['total_ontime'] ?>)
                    </button>
                    <button type="button" onclick="filterStatus('late')" id="tab-late" class="status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition">
                        Telat (<?= $summary['total_late'] ?>)
                    </button>
                    <button type="button" onclick="filterStatus('permission')" id="tab-permission" class="status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition">
                        Izin (<?= $summary['total_izin'] ?>)
                    </button>
                    <button type="button" onclick="filterStatus('absent')" id="tab-absent" class="status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition">
                        Alpa (<?= $summary['total_tidak_hadir'] ?>)
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="recapTable">
                <thead>
                    <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="py-3 px-4 text-center w-12">No</th>
                        <th class="py-3 px-4">Nama Lengkap & NIP/NIS</th>
                        <th class="py-3 px-4">Kategori / Peran</th>
                        <th class="py-3 px-4 text-center">Tepat Waktu</th>
                        <th class="py-3 px-4 text-center">Terlambat</th>
                        <th class="py-3 px-4 text-center">Dispensasi / Izin</th>
                        <th class="py-3 px-4 text-center">Total Hadir</th>
                        <th class="py-3 px-4 text-center">Status / Ketertiban</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($recap)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-400">
                                <i class="ri-inbox-line text-3xl mb-1 block"></i>
                                Tidak ada data santri atau pengajar pada rentang tanggal atau filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($recap as $row): 
                            $totalLate = ($row['count_late'] ?? 0) + ($row['count_very_late'] ?? 0);
                            $status = $row['status'] ?? 'absent';
                        ?>
                            <tr class="recap-row hover:bg-gray-50/80 transition" data-status="<?= $status ?>" data-search="<?= strtolower(htmlspecialchars(($row['name'] ?? '') . ' ' . ($row['identifier'] ?? '') . ' ' . ($row['subinfo'] ?? ''))) ?>">
                                <td class="py-3.5 px-4 text-xs font-medium text-gray-400 text-center row-num"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                        <?= htmlspecialchars($row['name'] ?: 'Tanpa Nama') ?>
                                        <?php if ($status === 'ontime'): ?>
                                            <i class="ri-shield-check-fill text-emerald-500 text-xs" title="Hadir Tepat Waktu"></i>
                                        <?php elseif ($status === 'late'): ?>
                                            <i class="ri-alarm-warning-fill text-amber-500 text-xs" title="Pernah Terlambat"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] text-gray-400 font-mono tracking-tight"><?= htmlspecialchars($row['identifier'] ?: '-') ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= ($row['person_type'] ?? '') == 'student' ? 'bg-indigo-50 text-indigo-700' : 'bg-purple-50 text-purple-700' ?>">
                                        <?= htmlspecialchars($row['subinfo'] ?: '-') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($row['count_on_time'] > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                            <?= $row['count_on_time'] ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-300">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($totalLate > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            <?= $totalLate ?>x
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-300">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (!empty($row['has_permission'])): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700" title="<?= htmlspecialchars($row['permission_reason'] ?: 'Izin Resmi') ?>">
                                            <i class="ri-file-text-line"></i> <?= ucfirst(htmlspecialchars($row['permission_type'] ?: 'Izin')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-extrabold text-gray-900">
                                    <?php if ($row['total_attendance'] > 0): ?>
                                        <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg"><?= $row['total_attendance'] ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($status === 'ontime'): ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                                            <i class="ri-star-fill text-amber-400"></i> Disiplin
                                        </span>
                                    <?php elseif ($status === 'late'): ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">
                                            <i class="ri-time-line"></i> Telat (<?= $totalLate ?>x)
                                        </span>
                                    <?php elseif ($status === 'permission'): ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                                            <i class="ri-survey-line"></i> <?= ucfirst(htmlspecialchars($row['permission_type'] ?: 'Izin')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-700 bg-rose-50 px-3 py-1 rounded-full border border-rose-200">
                                            <i class="ri-close-circle-line"></i> Tidak Hadir (Alpa)
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
let currentStatusFilter = 'all';

function filterStatus(status) {
    currentStatusFilter = status;

    // Update active tab styles
    document.querySelectorAll('.status-tab').forEach(tab => {
        tab.className = 'status-tab px-3 py-1 rounded-lg text-gray-600 hover:text-gray-900 transition';
    });
    const activeTab = document.getElementById('tab-' + status);
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
        const rowStatus = row.getAttribute('data-status');
        const rowSearch = row.getAttribute('data-search') || '';

        const matchesStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);
        const matchesSearch = !searchQuery || rowSearch.includes(searchQuery);

        if (matchesStatus && matchesSearch) {
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
</script>

<?php
renderFooter();
?>
