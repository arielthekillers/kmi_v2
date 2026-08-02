<?php
$filter = $filter ?? 'month';
$stats = $stats ?? [];
$summary = $stats['summary'] ?? [];
$prevSummary = $stats['prevSummary'] ?? [];
$topAbsentees = $stats['topAbsentees'] ?? [];
$topSubstitutes = $stats['topSubstitutes'] ?? [];
$topSubjects = $stats['topSubjects'] ?? [];
$chartData = $stats['chartData'] ?? [];

$totalLeaves = $summary['total_leaves'] ?? 0;
$totalSlots = $summary['total_slots'] ?? 0;
$totalSubstituted = $summary['total_substituted'] ?? 0;
$totalEmpty = $summary['total_empty'] ?? 0;

$emptyPercentage = $totalSlots > 0 ? round(($totalEmpty / $totalSlots) * 100) : 0;
$subPercentage = $totalSlots > 0 ? round(($totalSubstituted / $totalSlots) * 100) : 0;

// Calculate Trends
function getTrendHtml($current, $prev, $inverseGood = false) {
    if ($prev == 0) {
        if ($current > 0) return '<span class="text-xs font-medium text-amber-500"><i class="ri-arrow-right-up-line"></i> Baru</span>';
        return '<span class="text-xs font-medium text-gray-400">-</span>';
    }
    
    $diff = $current - $prev;
    $percentage = round(($diff / $prev) * 100);
    
    if ($diff > 0) {
        $color = $inverseGood ? 'text-red-500' : 'text-emerald-500';
        $icon = 'ri-arrow-right-up-line';
        return '<span class="text-xs font-medium ' . $color . '"><i class="' . $icon . '"></i> +' . $percentage . '%</span>';
    } elseif ($diff < 0) {
        $color = $inverseGood ? 'text-emerald-500' : 'text-red-500';
        $icon = 'ri-arrow-right-down-line';
        return '<span class="text-xs font-medium ' . $color . '"><i class="' . $icon . '"></i> ' . $percentage . '%</span>';
    } else {
        return '<span class="text-xs font-medium text-gray-400">Tetap</span>';
    }
}

$leavesTrend = getTrendHtml($totalLeaves, $prevSummary['total_leaves'] ?? 0, true); // true = higher is worse
$slotsTrend = getTrendHtml($totalSlots, $prevSummary['total_slots'] ?? 0, true);

// Prepare chart labels and data
$chartLabels = [];
$chartValues = [];
$daysOrder = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Ahad'];
$dayCounts = array_fill_keys(array_values($daysOrder), 0);

foreach ($chartData as $row) {
    if (isset($daysOrder[$row['day_name']])) {
        $indoName = $daysOrder[$row['day_name']];
        $dayCounts[$indoName] = $row['leave_count'];
    }
}
$chartLabels = json_encode(array_keys($dayCounts));
$chartValues = json_encode(array_values($dayCounts));

?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-4">
            <a href="<?= url('/leaves') ?>" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 transition-all shadow-sm">
                <i class="ri-arrow-left-line text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl text-gray-900 font-bold">Statistik & Evaluasi</h1>
                <p class="mt-1 text-sm text-gray-500">Rekapitulasi data izin mengajar dan kinerja guru pengganti.</p>
            </div>
        </div>
        
        <div>
            <form id="filterForm" action="<?= url('/leaves/statistics') ?>" method="GET" class="flex gap-2">
                <select name="filter" class="border border-gray-300 rounded-lg text-sm px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-300 transition-shadow bg-white shadow-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="today" <?= $filter === 'today' ? 'selected' : '' ?>>Hari Ini</option>
                    <option value="week" <?= $filter === 'week' ? 'selected' : '' ?>>Minggu Ini</option>
                    <option value="month" <?= $filter === 'month' ? 'selected' : '' ?>>Bulan Ini</option>
                    <option value="year" <?= $filter === 'year' ? 'selected' : '' ?>>Tahun Ajaran Ini</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Top Row: Chart & Summary Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-4">
        
        <!-- Chart Section (Left) -->
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="ri-bar-chart-2-line text-indigo-500"></i> Frekuensi Izin Hari
                </h3>
            </div>
            <div class="p-4 flex-1 flex flex-col justify-center">
                <div class="h-48 w-full relative">
                    <canvas id="dayChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Summary Cards (Right 2x2 Grid) -->
        <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Hari Izin</p>
                        <h3 class="text-3xl font-bold text-gray-900 flex items-end gap-2">
                            <?= $totalLeaves ?>
                            <?php if($filter != 'year'): ?>
                                <span class="mb-1"><?= $leavesTrend ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i class="ri-calendar-check-line text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Jam Izin</p>
                        <h3 class="text-3xl font-bold text-gray-900 flex items-end gap-2">
                            <?= $totalSlots ?> <span class="text-sm font-normal text-gray-500 mb-1">JP</span>
                            <?php if($filter != 'year'): ?>
                                <span class="mb-1"><?= $slotsTrend ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="ri-time-line text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-100">
                    <div class="h-full bg-emerald-500 transition-all duration-1000" style="width: <?= $subPercentage ?>%"></div>
                </div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Jam Tergantikan</p>
                        <h3 class="text-3xl font-bold text-emerald-600"><?= $totalSubstituted ?> <span class="text-sm font-normal text-gray-500">JP</span></h3>
                        <p class="text-xs text-emerald-600 mt-1 font-medium"><?= $subPercentage ?>% tertangani</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="ri-shield-check-line text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-100">
                    <div class="h-full bg-red-500 transition-all duration-1000" style="width: <?= $emptyPercentage ?>%"></div>
                </div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Jam Kosong</p>
                        <h3 class="text-3xl font-bold text-red-600"><?= $totalEmpty ?> <span class="text-sm font-normal text-gray-500">JP</span></h3>
                        <p class="text-xs text-red-600 mt-1 font-medium"><?= $emptyPercentage ?>% terbengkalai</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-600">
                        <i class="ri-alert-line text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- Top Absentees -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="ri-logout-circle-r-line text-red-500"></i> Total Hari Izin Terbanyak
                </h3>
            </div>
            <div class="p-2 flex-1">
                <?php if (empty($topAbsentees)): ?>
                    <div class="text-center text-gray-500 py-8">Belum ada data izin.</div>
                <?php else: ?>
                    <ul class="space-y-1">
                        <?php foreach ($topAbsentees as $index => $row): ?>
                            <li>
                                <button onclick="showDetails(<?= $row['id'] ?>, 'absentee', '<?= htmlspecialchars($row['nama']) ?>')" class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-gray-50 rounded-xl transition-colors group">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600 border border-gray-200 group-hover:bg-red-50 group-hover:text-red-600 group-hover:border-red-200 transition-colors flex-shrink-0">
                                            <?= $index + 1 ?>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-sm font-bold text-gray-900 truncate"><?= htmlspecialchars($row['nama']) ?></p>
                                            <p class="text-[11px] text-gray-500"><?= $row['total_jam_kosong'] ?> jam pelajaran</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold bg-red-50 text-red-700 rounded-lg group-hover:bg-red-100">
                                            <?= $row['leave_count'] ?> hari
                                        </span>
                                    </div>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Substitutes -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="ri-medal-line text-amber-500"></i> Beban Mengganti Terbanyak
                </h3>
            </div>
            <div class="p-2 flex-1">
                <?php if (empty($topSubstitutes)): ?>
                    <div class="text-center text-gray-500 py-8">Belum ada guru pengganti.</div>
                <?php else: ?>
                    <ul class="space-y-1">
                        <?php foreach ($topSubstitutes as $index => $row): ?>
                            <li>
                                <button onclick="showDetails(<?= $row['teacher_id'] ?>, 'substitute', '<?= htmlspecialchars($row['nama']) ?>')" class="w-full text-left px-3 py-2 flex items-center justify-between hover:bg-gray-50 rounded-xl transition-colors group">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        <div class="w-7 h-7 rounded-full <?= $index === 0 ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-gray-100 text-gray-600 border-gray-200' ?> flex items-center justify-center text-xs font-bold border group-hover:bg-amber-50 group-hover:text-amber-600 transition-colors flex-shrink-0">
                                            <?= $index + 1 ?>
                                        </div>
                                        <p class="text-sm font-bold text-gray-900 truncate"><?= htmlspecialchars($row['nama']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold bg-indigo-50 text-indigo-700 rounded-lg group-hover:bg-indigo-100">
                                            <?= $row['substitution_count'] ?> JP
                                        </span>
                                    </div>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Subjects -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="ri-book-open-line text-blue-500"></i> Pelajaran Sering Ditinggalkan
                </h3>
            </div>
            <div class="p-3 flex-1">
                <?php if (empty($topSubjects)): ?>
                    <div class="text-center text-gray-500 py-8">Belum ada data mata pelajaran.</div>
                <?php else: ?>
                    <ul class="space-y-2">
                        <?php foreach ($topSubjects as $index => $row): ?>
                            <li class="flex items-center justify-between px-2 py-1">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold border border-blue-100 flex-shrink-0">
                                        <?= $index + 1 ?>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 truncate"><?= htmlspecialchars($row['nama']) ?></p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <span class="text-sm font-bold text-gray-600">
                                        <?= $row['abandon_count'] ?> <span class="text-xs font-normal text-gray-400">JP</span>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Modal for Details -->
<div id="detailsModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeDetailsModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center gap-3 mb-4">
                    <div id="modalIcon" class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-indigo-100">
                        <i class="ri-information-line text-indigo-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modalTitle">Detail Riwayat</h3>
                </div>
                <div class="w-full">
                    <!-- Loader -->
                    <div id="modalLoader" class="flex justify-center py-4">
                        <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    
                    <!-- Content -->
                    <div id="modalContent" class="hidden overflow-hidden rounded-lg border border-gray-200">
                        <ul id="detailsList" class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
                            <!-- Dynamic content -->
                        </ul>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeDetailsModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Chart.js Initialization
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dayChart').getContext('2d');
    const labels = <?= $chartLabels ?>;
    const data = <?= $chartValues ?>;
    
    // Find the max value to scale the chart
    const maxVal = Math.max(...data);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Izin',
                data: data,
                backgroundColor: data.map(v => v === maxVal && maxVal > 0 ? 'rgba(239, 68, 68, 0.8)' : 'rgba(99, 102, 241, 0.8)'), // Highlight max day in red
                borderRadius: 6,
                barThickness: 32
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' absen izin';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 } // whole numbers only
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});

// Modal & Ajax Logic
function showDetails(teacherId, type, teacherName) {
    const modal = document.getElementById('detailsModal');
    const title = document.getElementById('modalTitle');
    const icon = document.getElementById('modalIcon');
    const loader = document.getElementById('modalLoader');
    const content = document.getElementById('modalContent');
    const list = document.getElementById('detailsList');
    
    // Setup UI
    title.textContent = "Riwayat: " + teacherName;
    if (type === 'absentee') {
        icon.className = "flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100";
        icon.innerHTML = '<i class="ri-logout-circle-r-line text-red-600 text-xl"></i>';
    } else {
        icon.className = "flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-amber-100";
        icon.innerHTML = '<i class="ri-medal-line text-amber-600 text-xl"></i>';
    }
    
    list.innerHTML = '';
    loader.classList.remove('hidden');
    content.classList.add('hidden');
    modal.classList.remove('hidden');
    
    // Fetch data
    const filter = new URLSearchParams(window.location.search).get('filter') || 'month';
    const formData = new FormData();
    formData.append('teacher_id', teacherId);
    formData.append('type', type);
    formData.append('filter', filter);
    
    fetch('<?= url('/leaves/statistics/details') ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        loader.classList.add('hidden');
        content.classList.remove('hidden');
        
        if (res.success && res.data.length > 0) {
            let html = '';
            res.data.forEach(item => {
                const dateObj = new Date(item.date);
                const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                
                if (type === 'absentee') {
                    html += `
                    <li class="px-4 py-3 flex items-start">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">${dateStr}</p>
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">${item.reason || 'Tanpa keterangan'}</p>
                        </div>
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            ${item.jam_kosong} JP ditinggalkan
                        </span>
                    </li>`;
                } else {
                    html += `
                    <li class="px-4 py-3 flex items-start">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">${dateStr}</p>
                            <p class="text-xs text-gray-500 mt-1">${item.subject_name} • Kelas ${item.tingkat}${item.kelas_name}</p>
                        </div>
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                            Jam ke-${item.hour}
                        </span>
                    </li>`;
                }
            });
            list.innerHTML = html;
        } else {
            list.innerHTML = '<li class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada riwayat.</li>';
        }
    })
    .catch(err => {
        loader.classList.add('hidden');
        content.classList.remove('hidden');
        list.innerHTML = '<li class="px-4 py-6 text-center text-sm text-red-500">Gagal memuat data.</li>';
        console.error(err);
    });
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}
</script>

<?php renderFooter(); ?>
