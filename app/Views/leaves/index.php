<?php
$data = $data ?? [];
$tab = $tab ?? 'leaves';
$date = $date ?? date('Y-m-d');
$noKbmDates = $noKbmDates ?? [];
?>

<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-7xl mx-auto">
<?php
$bulanId = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',    '04' => 'April',
    '05' => 'Mei',     '06' => 'Juni',     '07' => 'Juli',     '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
];
?>

    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">Izin Mengajar & Substitusi</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola izin pengajar dan guru pengganti harian.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <a href="<?= url('/leaves/statistics') ?>" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <i class="ri-bar-chart-box-line mr-2"></i> Statistik & Evaluasi
            </a>
            <a href="<?= url('/leaves/assistants') ?>" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <i class="ri-team-line mr-2"></i> Asisten Pengajar
            </a>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        
        <!-- Calendar Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
            <h2 class="text-lg font-bold text-gray-900">
                <?= $bulanId[sprintf('%02d', $selectedMonth)] ?> <?= $selectedYearVal ?>
            </h2>
            <div class="flex items-center gap-2">
                <a href="<?= url("/leaves?month={$prevMonth}&year={$prevYear}") ?>" 
                   class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                    <i class="ri-arrow-left-s-line"></i>
                </a>
                <a href="<?= url("/leaves?month=".date('m')."&year=".date('Y')) ?>" 
                   class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    Hari Ini
                </a>
                <a href="<?= url("/leaves?month={$nextMonth}&year={$nextYear}") ?>" 
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
                    $hari = ['Sabtu', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
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
                        $isToday = (date('Y-m-d') === $currentDateStr);
                        $todayClass = $isToday ? 'bg-indigo-50' : 'bg-white';
                        $todayNumberClass = $isToday ? 'bg-indigo-600 text-white rounded-full w-7 h-7 flex items-center justify-center' : 'text-gray-700 p-1';

                        echo "<div class=\"border-r border-b border-gray-100 p-1.5 flex flex-col {$todayClass} relative group/cell\">";
                        
                        // Date number (Absolute Top Right for number, but using flex justify-end)
                        echo "<div class=\"flex justify-end mb-1\">";
                        echo "<span class=\"text-sm font-medium {$todayNumberClass}\">{$day}</span>";
                        echo "</div>";
                        
                        // Add button, Substitute view, and Print view (Absolute Top Left, visible on hover)
                        $isNoKbm = isset($noKbmDates[$currentDateStr]);
                        if (!$isNoKbm) {
                            echo "<div class=\"absolute top-1.5 left-1.5 z-10 opacity-0 group-hover/cell:opacity-100 transition-opacity flex gap-0.5\">";
                            echo "<a href=\"javascript:void(0)\" onclick=\"openAddModal('{$currentDateStr}')\" class=\"text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 p-1 rounded-md transition-colors\" title=\"Tambah izin di tanggal ini\"><i class=\"ri-add-line text-[15px]\"></i></a>";
                            echo "<a href=\"javascript:void(0)\" onclick=\"openDailySubstitutesModal('{$currentDateStr}')\" class=\"text-gray-400 hover:text-teal-600 hover:bg-teal-50 p-1 rounded-md transition-colors\" title=\"Lihat Guru Pengganti Hari Ini\"><i class=\"ri-user-received-line text-[15px]\"></i></a>";
                            echo "<a href=\"" . url("/leaves/print_substitute?date={$currentDateStr}") . "\" target=\"_blank\" class=\"text-gray-400 hover:text-amber-600 hover:bg-amber-50 p-1 rounded-md transition-colors\" title=\"Cetak Jadwal Pengganti Hari Ini\"><i class=\"ri-printer-line text-[15px]\"></i></a>";
                            echo "</div>";
                        }
                        
                        // Render Events for this day
                        echo "<div class=\"flex-1 flex flex-col gap-0.5 overflow-y-auto max-h-[150px] no-scrollbar relative z-0\">";
                        
                        if ($isNoKbm) {
                            echo "<div class=\"text-[9px] font-bold text-red-600 bg-red-50 border border-red-100/50 rounded px-1.5 py-1 text-center mt-1 select-none leading-tight\" title=\"Bebas KBM: " . htmlspecialchars($noKbmDates[$currentDateStr]) . "\">";
                            echo "<i class=\"ri-close-circle-line text-[13px] mr-1 align-middle\"></i><span class=\"align-middle\">Bebas KBM</span><br>";
                            echo "<span class=\"text-[8px] font-normal text-red-500/80 truncate block mt-0.5\">" . htmlspecialchars($noKbmDates[$currentDateStr]) . "</span>";
                            echo "</div>";
                        }
                        
                        if (isset($monthLeaves[$currentDateStr])) {
                            foreach ($monthLeaves[$currentDateStr] as $leave) {
                                $hasEmpty = $leave['empty_slots'] > 0;
                                $bgClass = $hasEmpty ? 'bg-red-50 border-red-200 hover:bg-red-100' : 'bg-emerald-50 border-emerald-200 hover:bg-emerald-100';
                                $textClass = $hasEmpty ? 'text-red-700' : 'text-emerald-700';
                                
                                $filled = $leave['total_slots'] - $leave['empty_slots'];
                                $total = $leave['total_slots'];
                                $fractionText = "({$filled}/{$total})";
                                
                                $teacherName = htmlspecialchars($leave['teacher_name']);
                                $statusText = $hasEmpty ? "{$leave['empty_slots']} jam kosong" : "Terisi Penuh";
                                $titleTooltip = "{$teacherName} | Status: {$statusText} | Progress: {$filled} dari {$total} jam tergantikan";
                                
                                // Rich Tooltip Content (Hidden)
                                $tooltipId = "tooltip-" . $leave['id'];
                                echo "<div id=\"{$tooltipId}\" class=\"hidden\">";
                                echo "<div class=\"font-bold mb-2 border-b border-gray-700 pb-1.5 text-[11px] uppercase tracking-wider text-gray-300\">{$teacherName}</div>";
                                echo "<ul class=\"space-y-1.5\">";
                                if (!empty($leave['details'])) {
                                    foreach ($leave['details'] as $det) {
                                        $sub = $det['substitute_teacher_id'] ? htmlspecialchars($det['sub_name']) : "<span class=\"text-red-400 italic\">Kosong</span>";
                                        $subjectName = htmlspecialchars($det['subject_name'] ?? '');
                                        $kelas = htmlspecialchars(($det['tingkat'] ?? '') . '-' . ($det['abjad'] ?? ''));
                                        echo "<li class=\"flex items-start gap-1.5 leading-snug\"><span class=\"text-gray-400 flex-shrink-0 font-medium whitespace-nowrap\">Jam {$det['hour']} -</span> <span>{$subjectName} {$kelas} &rarr; <strong>{$sub}</strong></span></li>";
                                    }
                                } else {
                                    echo "<li class=\"text-gray-400 italic\">Tidak ada detail jadwal.</li>";
                                }
                                echo "</ul>";
                                echo "</div>";
                                
                                echo "<div class=\"relative group/event mx-0.5\">";
                                echo "<a href=\"javascript:void(0)\" onclick=\"openEditModal('{$leave['id']}')\" data-tooltip-id=\"{$tooltipId}\" class=\"tippy-target pl-1.5 pr-5 py-0.5 text-[10px] leading-tight font-medium rounded border transition-all cursor-pointer {$bgClass} flex items-center justify-between shadow-sm\">";
                                echo "<div class=\"truncate font-bold {$textClass}\">{$teacherName}</div>";
                                echo "<div class=\"text-[9px] ml-1 font-bold {$textClass} opacity-80 whitespace-nowrap\">{$fractionText}</div>";
                                echo "</a>";
                                
                                // Delete button on hover
                                echo "<form action=\"" . url("/leaves/delete") . "\" method=\"POST\" class=\"absolute top-1/2 -translate-y-1/2 right-0.5 z-20 opacity-0 group-hover/event:opacity-100 transition-opacity\" onsubmit=\"return confirm('Apakah Anda yakin ingin membatalkan izin ini?');\">
                                        <input type=\"hidden\" name=\"id\" value=\"{$leave['id']}\">
                                        <input type=\"hidden\" name=\"date\" value=\"{$currentDateStr}\">
                                        <button type=\"submit\" class=\"bg-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded-full w-4 h-4 flex items-center justify-center shadow border border-red-200\" title=\"Batalkan Izin\">
                                            <i class=\"ri-close-line text-[9px]\"></i>
                                        </button>
                                      </form>";
                                echo "</div>";
                            }
                        }
                        
                        echo "</div></div>";
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
</div>

<!-- Modal: Tambah Izin Baru -->
<div id="modal-tambah-izin" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('modal-tambah-izin').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-visible">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-2xl">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-calendar-add-line text-indigo-600"></i> Buat Izin Baru
            </h3>
            <button type="button" onclick="document.getElementById('modal-tambah-izin').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <form id="formTambahIzin" onsubmit="submitTambahIzin(event)" class="px-6 py-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Pengajar yang Izin</label>
                <select name="teacher_id" id="add_teacher_id" class="tom-select w-full" required>
                    <option value="">Pilih Pengajar...</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Izin <span class="text-xs font-normal text-gray-500">(bisa pilih rentang)</span></label>
                <input type="text" id="add_date_range" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 outline-none transition" placeholder="Pilih Tanggal...">
                <input type="hidden" id="add_tanggal_mulai" name="tanggal_mulai">
                <input type="hidden" id="add_tanggal_selesai" name="tanggal_selesai">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Izin</label>
                <select name="type" id="add_type" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 outline-none transition" required>
                    <option value="izin">Izin Kepentingan</option>
                    <option value="sakit">Sakit</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="document.getElementById('modal-tambah-izin').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btnSubmitTambah" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors flex items-center">
                    <i class="ri-save-line mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Guru Pengganti Harian -->
<div id="modal-daily-substitutes" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('modal-daily-substitutes').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50 flex-shrink-0">
            <div>
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="ri-user-received-line text-indigo-600"></i> Guru Pengganti Harian
                </h3>
                <p class="text-xs text-gray-500 mt-0.5" id="daily-sub-modal-subtitle">Memuat data...</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-daily-substitutes').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1 bg-white" id="dailySubstitutesContainer">
            <!-- Content loaded via JS -->
        </div>
    </div>
</div>

<!-- Modal: Edit Substitusi -->
<div id="modal-edit-izin" class="hidden fixed inset-0 z-[50] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('modal-edit-izin').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50 flex-shrink-0">
            <div>
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="ri-user-received-line text-indigo-600"></i> Kelola Guru Pengganti
                </h3>
                <p class="text-xs text-gray-500 mt-0.5" id="edit-modal-subtitle">Memuat data...</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-edit-izin').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1 bg-white">
            <form id="formEditSubstitusi" action="<?= url('/leaves/store') ?>" method="POST">
                <input type="hidden" name="edit_leave_id" id="edit_leave_id">
                <input type="hidden" name="teacher_id" id="edit_teacher_id">
                <input type="hidden" name="date" id="edit_date">
                
                <div id="scheduleListContainer">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                        <p class="mt-2 text-sm text-gray-500">Memuat jadwal pelajaran...</p>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0 rounded-b-2xl">
            <button type="button" onclick="document.getElementById('modal-edit-izin').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-xl transition-colors border border-gray-200 bg-white shadow-sm">Tutup</button>
            <button type="button" onclick="document.getElementById('formEditSubstitusi').submit()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
                <i class="ri-save-line"></i> Simpan Pengganti
            </button>
        </div>
    </div>
</div>

<style>
/* Custom Tippy Theme */
.tippy-box[data-theme~='translucent'] {
  background-color: rgba(17, 24, 39, 0.95);
  color: white;
  backdrop-filter: blur(8px);
  border-radius: 12px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
  padding: 8px 12px;
  border: 1px solid rgba(255,255,255,0.1);
}
.tippy-box[data-theme~='translucent'][data-placement^='top'] > .tippy-arrow::before {
  border-top-color: rgba(17, 24, 39, 0.95);
}
</style>

<!-- Modal: Rekomendasi Algoritma -->
<div id="recommendationModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeRecommendationModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="ri-search-eye-line text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Pilih Pengajar Pengganti</h3>
                    <p class="text-xs text-gray-500" id="rec-modal-subtitle">Mencari kandidat terbaik...</p>
                </div>
            </div>
            <button type="button" onclick="closeRecommendationModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        
        <div class="p-4 flex-1 flex flex-col overflow-hidden bg-gray-50">
            <div class="mb-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="text" id="teacherSearch" placeholder="Cari nama pengajar..." class="w-full pl-9 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 outline-none" onkeyup="filterTeachers()">
                </div>
            </div>

            <div class="flex gap-2 mb-3">
                <button type="button" onclick="selectTeacher('', 'Pilih Pengganti')" class="flex-1 bg-white hover:bg-gray-100 text-gray-700 text-xs font-medium py-2 rounded-lg border border-gray-200 transition-colors shadow-sm">
                    <i class="ri-close-circle-line text-gray-400 mr-1"></i> Kosongkan
                </button>
                <button type="button" onclick="selectTeacher('self', 'Mengajar Sendiri')" class="flex-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-medium py-2 rounded-lg border border-indigo-200 transition-colors shadow-sm">
                    <i class="ri-user-star-line mr-1"></i> Mengajar Sendiri
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto pr-1" id="recommendationList">
                <!-- List diisi oleh JS -->
            </div>
        </div>
    </div>
</div>

<!-- Tippy.js for Tooltips -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css" />
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/animations/shift-away.css" />

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script>
    // Inisialisasi Tippy.js
    tippy('.tippy-target', {
        content(reference) {
            const id = reference.getAttribute('data-tooltip-id');
            const template = document.getElementById(id);
            return template.innerHTML;
        },
        allowHTML: true,
        theme: 'translucent',
        animation: 'shift-away',
        interactive: false,
        placement: 'top',
        maxWidth: 350
    });

    // Inisialisasi Flatpickr
    flatpickr("#add_date_range", {
        mode: "range",
        dateFormat: "Y-m-d",
        locale: "id",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 0) {
                document.getElementById('add_tanggal_mulai').value = '';
                document.getElementById('add_tanggal_selesai').value = '';
            } else if (selectedDates.length === 1) {
                document.getElementById('add_tanggal_mulai').value = instance.formatDate(selectedDates[0], "Y-m-d");
                document.getElementById('add_tanggal_selesai').value = '';
            } else if (selectedDates.length === 2) {
                document.getElementById('add_tanggal_mulai').value = instance.formatDate(selectedDates[0], "Y-m-d");
                document.getElementById('add_tanggal_selesai').value = instance.formatDate(selectedDates[1], "Y-m-d");
            }
        }
    });

    // Inisialisasi TomSelect
    document.querySelectorAll('.tom-select').forEach((el) => {
        if (!el.tomselect) {
            new TomSelect(el, {
                create: false,
                sortField: { field: "text", direction: "asc" }
            });
        }
    });

    function openAddModal(dateStr) {
        document.getElementById('modal-tambah-izin').classList.remove('hidden');
        const fp = document.getElementById('add_date_range')._flatpickr;
        if(dateStr) {
            fp.setDate(dateStr);
            document.getElementById('add_tanggal_mulai').value = dateStr;
            document.getElementById('add_tanggal_selesai').value = dateStr;
        } else {
            fp.clear();
        }
        
        const select = document.getElementById('add_teacher_id');
        if (select.tomselect) { select.tomselect.clear(); }
    }

    function submitTambahIzin(e) {
        e.preventDefault();
        const teacher_id = document.getElementById('add_teacher_id').value;
        const mulai = document.getElementById('add_tanggal_mulai').value;
        const selesai = document.getElementById('add_tanggal_selesai').value;
        const type = document.getElementById('add_type').value;
        
        if(!teacher_id || !mulai) {
            alert("Harap isi pengajar dan tanggal!");
            return;
        }

        const btn = document.getElementById('btnSubmitTambah');
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line animate-spin mr-1"></i> Menyimpan...';

        const fd = new FormData();
        fd.append('teacher_id', teacher_id);
        fd.append('tanggal_mulai', mulai);
        fd.append('tanggal_selesai', selesai);
        fd.append('type', type);

        fetch('<?= url('/leaves/store_ajax') ?>', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if(data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menyimpan.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-save-line mr-1"></i> Simpan';
                }
            } catch (err) {
                // Server returned non-JSON (e.g. PHP Notice) but data is usually saved successfully.
                console.error("Server response:", text);
                window.location.reload();
            }
        })
        .catch(err => {
            console.error("Fetch error:", err);
            alert('Gagal terhubung ke server. Periksa koneksi Anda.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-save-line mr-1"></i> Simpan';
        });
    }

    // Modal Edit
    function openEditModal(leaveId) {
        event.preventDefault(); // Mencegah pindah halaman
        document.getElementById('modal-edit-izin').classList.remove('hidden');
        document.getElementById('scheduleListContainer').innerHTML = `
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-500">Memuat detail jadwal...</p>
            </div>
        `;
        
        const fd = new FormData();
        fd.append('id', leaveId);
        
        fetch('<?= url('/leaves/details') ?>', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('scheduleListContainer').innerHTML = `<p class="text-red-500 text-center">${data.message}</p>`;
                return;
            }
            
            const leave = data.leave;
            document.getElementById('edit_leave_id').value = leave.id;
            document.getElementById('edit_teacher_id').value = leave.teacher_id;
            document.getElementById('edit_date').value = leave.date;
            
            document.getElementById('edit-modal-subtitle').innerHTML = `Mencarikan pengganti untuk <strong>${leave.teacher_name}</strong> pada tanggal <strong>${leave.date}</strong>`;
            
            const schedules = data.schedules;
            let existingMap = {};
            if(leave.details) {
                leave.details.forEach(s => {
                    existingMap[s.hour] = { id: s.substitute_teacher_id, name: s.substitute_name || '' };
                });
            }
            
            let html = `
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Jam Ke-</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Mata Pelajaran</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-48">Guru Pengganti</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
            `;
            
            schedules.forEach(sch => {
                const hour = sch.hour;
                const existing = existingMap[hour];
                const subId = existing && existing.id !== null ? existing.id : '';
                const subName = existing && existing.id !== null ? existing.name : 'Pilih Pengganti';
                const bgClass = existing && existing.id !== null ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-gray-50 border-gray-200 text-gray-500';
                
                html += `
                <tr>
                    <td class="px-4 py-3 text-center font-bold text-gray-700">${hour}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-800">${sch.subject_name}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">${sch.tingkat}-${sch.abjad}</td>
                    <td class="px-4 py-3">
                        <input type="hidden" name="subs[${leave.date}][${hour}][kelas_id]" value="${sch.kelas_id}">
                        <input type="hidden" name="subs[${leave.date}][${hour}][subject_id]" value="${sch.subject_id}">
                        <input type="hidden" id="sub_${hour}" name="subs[${leave.date}][${hour}][substitute_teacher_id]" value="${subId}">
                        <div onclick="openRecommendation(${hour}, ${sch.subject_id}, ${sch.kelas_id}, '${sch.subject_name.replace(/'/g, "\\'")}', 'Kelas ${sch.tingkat}-${sch.abjad}')" 
                             class="cursor-pointer border rounded-lg px-3 py-2 flex items-center justify-between transition-colors ${bgClass} hover:bg-gray-100">
                            <span id="sub_display_${hour}" class="text-xs font-bold truncate mr-2">${subName}</span>
                            <i class="ri-search-line opacity-50 flex-shrink-0"></i>
                        </div>
                    </td>
                </tr>
                `;
            });
            
            if(schedules.length === 0) {
                html += `<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Tidak ada jadwal pada hari ini.</td></tr>`;
            }
            
            html += `</tbody></table></div>`;
            document.getElementById('scheduleListContainer').innerHTML = html;
        });
    }

    // Recommendation Modal logic
    let recTargetHour = null;

    function openRecommendation(hour, subjectId, kelasId, subjectName, className) {
        recTargetHour = hour;
        document.getElementById('recommendationModal').classList.remove('hidden');
        
        document.getElementById('rec-modal-subtitle').innerHTML = `Mencarikan pengganti untuk <strong>${subjectName}</strong> (${className}) pada <strong>Jam ke-${hour}</strong>`;
        
        const list = document.getElementById('recommendationList');
        list.innerHTML = `
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-500">Menganalisis jutaan kemungkinan jadwal...</p>
            </div>
        `;

        const fd = new FormData();
        fd.append('date', document.getElementById('edit_date').value);
        fd.append('hour', hour);
        fd.append('subject_id', subjectId);
        fd.append('kelas_id', kelasId);
        fd.append('teacher_id', document.getElementById('edit_teacher_id').value);

        fetch('<?= url('/leaves/recommendations') ?>', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            list.innerHTML = '';
            if (data.length === 0) {
                list.innerHTML = '<p class="text-center text-gray-500 py-4">Tidak ada pengajar yang tersedia.</p>';
                return;
            }

            data.forEach(teacher => {
                let badgeClass = 'bg-gray-100 text-gray-800 border border-gray-200';
                if (teacher.is_assistant) badgeClass = 'bg-purple-100 text-purple-800 border border-purple-200';
                else if (teacher.score >= 300) badgeClass = 'bg-green-100 text-green-800 border border-green-200';
                else if (teacher.score >= 200) badgeClass = 'bg-blue-100 text-blue-800 border border-blue-200';
                else if (teacher.score >= 100) badgeClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                else badgeClass = 'bg-red-50 text-red-700 border border-red-200'; // Libur

                const row = `
                    <div class="rec-teacher-row flex items-center justify-between p-3 mb-2 bg-white rounded-lg border border-gray-100 shadow-sm hover:border-indigo-200 transition-colors cursor-pointer" onclick="selectTeacher(${teacher.id}, '${teacher.nama.replace(/'/g, "\\'")}')">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider ${badgeClass}">${teacher.category}</span>
                                <span class="text-[10px] text-gray-500"><i class="ri-star-s-fill text-yellow-400"></i> Skor: ${teacher.score}</span>
                            </div>
                            <div class="font-bold text-gray-900 text-sm">${teacher.nama} ${teacher.is_assistant ? '<i class="ri-shield-star-fill text-purple-500"></i>' : ''}</div>
                            <div class="text-[10px] text-gray-500 truncate mt-0.5 max-w-[250px]" title="${teacher.reason.replace(/"/g, '&quot;')}">${teacher.reason}</div>
                        </div>
                        <button type="button" class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors border border-gray-200">
                            <i class="ri-arrow-right-line"></i>
                        </button>
                    </div>
                `;
                list.insertAdjacentHTML('beforeend', row);
            });
        });
    }

    function selectTeacher(id, name) {
        if(recTargetHour !== null) {
            document.getElementById(`sub_${recTargetHour}`).value = id;
            const display = document.getElementById(`sub_display_${recTargetHour}`);
            display.textContent = name;
            
            const container = display.closest('div');
            if (id === '' || id === null) {
                container.className = "cursor-pointer border rounded-lg px-3 py-2 flex items-center justify-between transition-colors bg-gray-50 border-gray-200 text-gray-500 hover:bg-gray-100";
            } else if (id === 'self') {
                container.className = "cursor-pointer border rounded-lg px-3 py-2 flex items-center justify-between transition-colors bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100";
            } else {
                container.className = "cursor-pointer border rounded-lg px-3 py-2 flex items-center justify-between transition-colors bg-indigo-50 border-indigo-200 text-indigo-700 hover:bg-indigo-100";
            }
        }
        closeRecommendationModal();
    }

    function filterTeachers() {
        const filter = document.getElementById('teacherSearch').value.toLowerCase();
        document.querySelectorAll('.rec-teacher-row').forEach(node => {
            const text = node.textContent || node.innerText;
            node.style.display = text.toLowerCase().indexOf(filter) > -1 ? "" : "none";
        });
    }

    function closeRecommendationModal() {
        document.getElementById('recommendationModal').classList.add('hidden');
        document.getElementById('teacherSearch').value = '';
    }

    function openDailySubstitutesModal(dateStr) {
        document.getElementById('modal-daily-substitutes').classList.remove('hidden');
        
        const dateObj = new Date(dateStr);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const indoDateStr = dateObj.toLocaleDateString('id-ID', options);
        
        document.getElementById('daily-sub-modal-subtitle').innerHTML = `Tanggal: <strong>${indoDateStr}</strong>`;
        const container = document.getElementById('dailySubstitutesContainer');
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-500">Memuat data guru pengganti...</p>
            </div>
        `;
        
        const fd = new FormData();
        fd.append('date', dateStr);
        
        fetch('<?= url('/leaves/daily_substitutes') ?>', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                container.innerHTML = `<p class="text-red-500 text-center">${data.message}</p>`;
                return;
            }
            
            if (data.data.length === 0) {
                container.innerHTML = `<p class="text-gray-500 text-center py-8">Tidak ada guru pengganti pada tanggal ini.</p>`;
                return;
            }
            
            let html = `
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guru Pengganti</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Ke</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
            `;
            
            data.data.forEach(sub => {
                const details = sub.details.split('||').filter(d => d.trim() !== '');
                details.forEach((detail, index) => {
                    const parts = detail.split('|');
                    if (parts.length === 3) {
                        html += `<tr class="hover:bg-gray-50">`;
                        if (index === 0) {
                            html += `<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium align-top" rowspan="${details.length}">${sub.substitute_name}</td>`;
                        }
                        html += `
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${parts[0]}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 truncate max-w-[150px]" title="${parts[1]}">${parts[1]}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                <span class="text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded">Kelas ${parts[2]}</span>
                            </td>
                        </tr>`;
                    }
                });
            });
            
            html += `</tbody></table></div>`;
            container.innerHTML = html;
        });
    }
</script>

<?php renderFooter(); ?>
