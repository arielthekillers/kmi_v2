<?php
// Kategori badge config
$kategoriConfig = [
    'Akademik' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'dot' => 'bg-blue-500',   'border' => 'border-blue-200'],
    'Ujian'    => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'dot' => 'bg-red-500',    'border' => 'border-red-200'],
    'Kegiatan' => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'dot' => 'bg-green-500',  'border' => 'border-green-200'],
    'Libur'    => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200'],
    'Lainnya'  => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'dot' => 'bg-gray-400',   'border' => 'border-gray-200'],
];

$bulanId = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',    '04' => 'April',
    '05' => 'Mei',     '06' => 'Juni',     '07' => 'Juli',     '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
];

$formatTanggal = function($mulai, $selesai) use ($bulanId) {
    $d1 = date('j', strtotime($mulai));
    $m1 = $bulanId[date('m', strtotime($mulai))];
    $y1 = date('Y', strtotime($mulai));

    if (empty($selesai) || $selesai === $mulai) {
        return "<div class='whitespace-nowrap'>$d1 $m1 $y1</div>";
    }

    $d2 = date('j', strtotime($selesai));
    $m2 = $bulanId[date('m', strtotime($selesai))];
    $y2 = date('Y', strtotime($selesai));

    // For any range, force exactly 2 lines
    return "<div class='whitespace-nowrap'>$d1 $m1 $y1</div>" .
           "<div class='whitespace-nowrap text-gray-500 text-[11px]'>s/d $d2 $m2 $y2</div>";
};

$isAdmin = auth_get_role() === 'admin';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-calendar-event-line text-indigo-600"></i>
                Kalender Akademik
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                <?= $activeYear ? htmlspecialchars('Tahun Ajaran ' . $activeYear['name']) : 'Tidak ada tahun ajaran aktif' ?>
            </p>
        </div>
        
        <div class="flex items-center gap-3 flex-wrap">
            <?php if ($isAdmin && $activeYearId): ?>
                <!-- View Toggles -->
                <div class="inline-flex items-center bg-gray-100/80 p-1 rounded-xl">
                    <a href="<?= url('/academic-calendar?view=list' . ($viewMode == 'month' ? '&month='.$selectedMonth.'&year='.$selectedYearVal : '')) ?>" 
                       class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all <?= $viewMode === 'list' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">
                        <i class="ri-list-check"></i> List
                    </a>
                    <a href="<?= url('/academic-calendar?view=month' . ($viewMode == 'list' ? '&month='.date('m').'&year='.date('Y') : '&month='.$selectedMonth.'&year='.$selectedYearVal)) ?>" 
                       class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all <?= $viewMode === 'month' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">
                        <i class="ri-calendar-2-line"></i> Kalender
                    </a>
                </div>

                <!-- Tambah Event Button -->
                <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm transition-colors">
                    <i class="ri-add-line"></i> Tambah Kegiatan
                </button>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if (empty($grouped) && $viewMode === 'list' && empty($monthEvents) && $viewMode === 'month'): ?>
        <!-- Empty State -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                <i class="ri-calendar-event-line text-3xl text-indigo-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-1">Belum ada kegiatan</h3>
            <p class="text-sm text-gray-400 mb-6">
                <?= $activeYearId ? 'Kalender akademik untuk tahun ajaran aktif ini masih kosong.' : 'Tidak ada tahun ajaran aktif.' ?>
            </p>
            <?php if ($isAdmin && $activeYearId): ?>
                <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                    <i class="ri-add-line"></i> Tambah Kegiatan Pertama
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        
        <?php if ($viewMode === 'list'): ?>
            <!-- Calendar Table grouped by month (Compact) -->
            <div class="space-y-6">
                <?php foreach ($grouped as $monthKey => $events): ?>
                    <?php
                        [$year, $month] = explode('-', $monthKey);
                        $monthName = ($bulanId[$month] ?? $month) . ' ' . $year;
                    ?>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <!-- Month Header -->
                        <div class="px-5 py-2.5 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100 flex items-center gap-2">
                            <i class="ri-calendar-2-line text-indigo-500 text-sm"></i>
                            <h2 class="text-sm font-bold text-indigo-800 uppercase tracking-wide"><?= $monthName ?></h2>
                            <span class="ml-auto text-xs text-indigo-400 font-medium"><?= count($events) ?> kegiatan</span>
                        </div>

                        <!-- Events Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-50">
                                <thead>
                                    <tr class="bg-gray-50/50">
                                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-40">Tanggal</th>
                                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Kategori</th>
                                        <?php if ($isAdmin): ?>
                                            <th class="px-5 py-2 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php foreach ($events as $event): ?>
                                        <?php $cfg = $kategoriConfig[$event['kategori']] ?? $kategoriConfig['Lainnya']; ?>
                                        <tr class="hover:bg-gray-50/80 transition-colors group">
                                            <td class="px-5 py-2">
                                                <div class="flex items-start gap-2">
                                                    <span class="mt-1 w-1.5 h-1.5 rounded-full flex-shrink-0 <?= $cfg['dot'] ?>"></span>
                                                    <span class="text-sm text-gray-700 font-medium leading-snug">
                                                        <?= $formatTanggal($event['tanggal_mulai'], $event['tanggal_selesai']) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-5 py-2">
                                                <span class="text-sm text-gray-800"><?= htmlspecialchars($event['keterangan']) ?></span>
                                            </td>
                                            <td class="px-5 py-2">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium <?= $cfg['bg'] ?> <?= $cfg['text'] ?> border <?= $cfg['border'] ?>">
                                                    <?= htmlspecialchars($event['kategori']) ?>
                                                </span>
                                            </td>
                                            <?php if ($isAdmin): ?>
                                                <td class="px-5 py-2 text-right">
                                                    <div class="flex items-center justify-end gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <a href="<?= url('/academic-calendar/edit?id=' . $event['id']) ?>"
                                                            class="p-1 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="Edit">
                                                            <i class="ri-edit-2-line text-sm"></i>
                                                        </a>
                                                        <button onclick="confirmDelete(<?= $event['id'] ?>, '<?= htmlspecialchars(addslashes($event['keterangan'])) ?>')"
                                                            class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Hapus">
                                                            <i class="ri-delete-bin-line text-sm"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($viewMode === 'month'): ?>
            <!-- Monthly Grid View -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                
                <!-- Calendar Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
                    <h2 class="text-lg font-bold text-gray-900">
                        <?= $bulanId[sprintf('%02d', $selectedMonth)] ?> <?= $selectedYearVal ?>
                    </h2>
                    <div class="flex items-center gap-2">
                        <a href="<?= url("/academic-calendar?view=month&month={$prevMonth}&year={$prevYear}") ?>" 
                           class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                        <a href="<?= url("/academic-calendar?view=month&month=".date('m')."&year=".date('Y')) ?>" 
                           class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            Hari Ini
                        </a>
                        <a href="<?= url("/academic-calendar?view=month&month={$nextMonth}&year={$nextYear}") ?>" 
                           class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="w-full overflow-x-auto">
                    <div class="min-w-[800px] border-l border-gray-100">
                        <!-- Days of week -->
                        <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50/50">
                            <?php 
                            $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                            foreach($hari as $h): ?>
                                <div class="px-2 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider border-r border-gray-100">
                                    <?= $h ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Days grid -->
                        <div class="grid grid-cols-7 auto-rows-[minmax(120px,_auto)]">
                            <?php 
                            // Blank days before month starts
                            for ($i = 0; $i < $firstDayOffset; $i++) {
                                echo '<div class="border-r border-b border-gray-100 bg-gray-50/30"></div>';
                            }

                            // Days of the month
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $currentDateStr = sprintf('%04d-%02d-%02d', $selectedYearVal, $selectedMonth, $day);
                                $currentTimestamp = strtotime($currentDateStr);
                                
                                $isToday = (date('Y-m-d') === $currentDateStr);
                                $todayClass = $isToday ? 'bg-indigo-50' : 'bg-white';
                                $todayNumberClass = $isToday ? 'bg-indigo-600 text-white rounded-full w-7 h-7 flex items-center justify-center' : 'text-gray-700 p-1';

                                echo "<div class=\"border-r border-b border-gray-100 p-1.5 flex flex-col {$todayClass}\">";
                                echo "<div class=\"flex justify-end mb-1\"><span class=\"text-sm font-medium {$todayNumberClass}\">{$day}</span></div>";
                                
                                // Render Events for this day
                                echo "<div class=\"flex-1 space-y-1 flex flex-col gap-0.5 overflow-y-auto max-h-[150px] no-scrollbar\">";
                                
                                foreach ($monthEvents as $event) {
                                    $start = strtotime($event['tanggal_mulai']);
                                    $end = empty($event['tanggal_selesai']) ? $start : strtotime($event['tanggal_selesai']);
                                    
                                    if ($currentTimestamp >= $start && $currentTimestamp <= $end) {
                                        $cfg = $kategoriConfig[$event['kategori']] ?? $kategoriConfig['Lainnya'];
                                        
                                        // Determine boundaries for styling continuous block
                                        $isStart = ($currentTimestamp == $start);
                                        $isEnd = ($currentTimestamp == $end);
                                        
                                        $roundedClass = '';
                                        if ($isStart && $isEnd) $roundedClass = 'rounded-md mx-0.5';
                                        elseif ($isStart) $roundedClass = 'rounded-l-md ml-0.5 -mr-1.5 z-10 relative';
                                        elseif ($isEnd) $roundedClass = 'rounded-r-md mr-0.5 -ml-1.5 z-10 relative';
                                        else $roundedClass = '-mx-1.5 z-0 relative'; // Middle of event stretches

                                        // For tooltip
                                        $tooltip = htmlspecialchars($event['keterangan'] . ' (' . $event['kategori'] . ')');

                                        echo "<div title=\"{$tooltip}\" class=\"px-2 py-1 text-[10px] leading-tight font-medium truncate {$cfg['bg']} {$cfg['text']} border-y border-transparent {$roundedClass}\">";
                                        // Only show text on the start day, or on Mondays, or on the 1st of the month
                                        if ($isStart || date('w', $currentTimestamp) == 1 || $day == 1) {
                                            echo htmlspecialchars($event['keterangan']);
                                        } else {
                                            echo "&nbsp;";
                                        }
                                        echo "</div>";
                                    }
                                }
                                
                                echo "</div></div>"; // end events container, end day cell
                            }

                            // Fill remaining grid cells to complete the last row
                            $totalCells = $firstDayOffset + $daysInMonth;
                            $remainder = $totalCells % 7;
                            if ($remainder > 0) {
                                $cellsToAdd = 7 - $remainder;
                                for ($i = 0; $i < $cellsToAdd; $i++) {
                                    echo '<div class="border-r border-b border-gray-100 bg-gray-50/30"></div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Legend (Centered at bottom) -->
    <div class="flex flex-wrap items-center justify-center gap-3 mt-8">
        <?php foreach ($kategoriConfig as $kat => $cfg): ?>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium <?= $cfg['bg'] ?> <?= $cfg['text'] ?> border <?= $cfg['border'] ?>">
                <span class="w-1.5 h-1.5 rounded-full <?= $cfg['dot'] ?>"></span>
                <?= $kat ?>
            </span>
        <?php endforeach; ?>
    </div>

</div>

<?php if ($isAdmin && $activeYearId): ?>
<!-- ============================================================ -->
<!-- Modal: Tambah Event -->
<!-- ============================================================ -->
<div id="modal-tambah" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('modal-tambah').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-calendar-add-line text-indigo-600"></i>
                Tambah Kegiatan
            </h3>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="<?= url('/academic-calendar/store') ?>" class="px-6 py-5 space-y-4">
            <?= csrf_token_field() ?>

            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama / Keterangan Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="keterangan" required placeholder="cth: Ulangan Umum Pertengahan Tahun"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
            </div>

            <!-- Tanggal -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" id="add-tanggal-mulai" required
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-gray-400 text-xs">(opsional)</span></label>
                    <input type="date" name="tanggal_selesai" id="add-tanggal-selesai"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
                </div>
            </div>

            <!-- Kategori -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($kategoriConfig as $kat => $cfg): ?>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="kategori" value="<?= $kat ?>" <?= $kat === 'Akademik' ? 'checked' : '' ?>
                                class="hidden kategori-radio">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border-2 cursor-pointer transition-all
                                kategori-option <?= $cfg['bg'] ?> <?= $cfg['text'] ?> border-transparent hover:border-current"
                                data-kat="<?= $kat ?>">
                                <span class="w-2 h-2 rounded-full <?= $cfg['dot'] ?>"></span>
                                <?= $kat ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">
                    <i class="ri-save-line mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Konfirmasi Hapus -->
<div id="modal-delete" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('modal-delete').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-delete-bin-line text-2xl text-red-500"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Kegiatan?</h3>
        <p class="text-sm text-gray-500 mb-1">Anda akan menghapus:</p>
        <p id="delete-label" class="text-sm font-semibold text-gray-800 mb-6 px-4"></p>
        <div class="flex gap-3 justify-center">
            <button onclick="document.getElementById('modal-delete').classList.add('hidden')"
                class="px-5 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-xl border border-gray-200 transition-colors">
                Batal
            </button>
            <a id="delete-link" href="#"
                class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors">
                Ya, Hapus
            </a>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, label) {
    document.getElementById('delete-label').textContent = label;
    document.getElementById('delete-link').href = '<?= url('/academic-calendar/delete') ?>?id=' + id;
    document.getElementById('modal-delete').classList.remove('hidden');
}

// Kategori radio pill selector
document.querySelectorAll('.kategori-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.kategori-option').forEach(function(opt) {
            opt.classList.remove('ring-2', 'ring-offset-1', 'ring-indigo-400', '!border-current');
            opt.style.borderColor = '';
        });
        var label = radio.closest('label').querySelector('.kategori-option');
        if (label) {
            label.style.borderColor = 'currentColor';
        }
    });
});

// Set initial selected state
document.querySelectorAll('.kategori-radio:checked').forEach(function(radio) {
    var label = radio.closest('label').querySelector('.kategori-option');
    if (label) label.style.borderColor = 'currentColor';
});

// Ensure tanggal_selesai >= tanggal_mulai
document.getElementById('add-tanggal-mulai').addEventListener('change', function() {
    var selesai = document.getElementById('add-tanggal-selesai');
    selesai.min = this.value;
    if (selesai.value && selesai.value < this.value) selesai.value = '';
});
</script>
<?php endif; ?>
