<?php renderHeader("Absensi Muwajjah (Belajar Malam)"); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ri-moon-clear-line text-indigo-600"></i> Absensi Muwajjah
                </h2>
                <p class="text-gray-500 text-sm">Catat pendampingan belajar malam mandiri santri.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= url('/muwajjah/report') ?>" class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-xl shadow-xs text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 transition-all">
                    <i class="ri-file-chart-line mr-1.5 text-indigo-600"></i> Laporan Absensi
                </a>
                <?php if ($userRole === 'admin'): ?>
                    <a href="<?= url('/piket/muwajjah') ?>" class="inline-flex items-center px-4 py-2 rounded-xl shadow-xs text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-all">
                        <i class="ri-calendar-event-line mr-1.5"></i> Jadwal Piket Malam
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Compact Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-3 mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 pl-1">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                    <i class="ri-moon-line text-xl"></i>
                </div>
                <div>
                    <?php
                    $dayNameMap = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Ahad'];
                    $dayNum = (int)date('N', strtotime($selectedDate));
                    $formattedDateStr = $dayNameMap[$dayNum] . ', ' . date('d M Y', strtotime($selectedDate));
                    
                    // Count total cards (1 card per Wali Kelas)
                    $totalItems = 0;
                    foreach ($classes as $cls) {
                        $wCount = count($cls['wali_kelas'] ?? []);
                        $totalItems += ($wCount > 0) ? $wCount : 1;
                    }
                    ?>
                    <h3 class="text-sm font-bold text-gray-900 leading-tight"><?= $formattedDateStr ?></h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Total <?= count($classes) ?> Kelas (<?= $totalItems ?> Wali Kelas)</p>
                </div>
                <?php if ($isRoutineHoliday): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">
                        Libur Rutin Malam (Kamis/Jumat)
                    </span>
                <?php endif; ?>
            </div>

            <form method="GET" action="<?= url('/muwajjah') ?>" class="flex items-center gap-2 w-full md:w-auto">
                <div class="relative flex-1 md:flex-none">
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()"
                           class="block w-full md:w-48 pl-3 pr-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none">
                </div>
                <a href="<?= url('/muwajjah?tanggal=' . date('Y-m-d')) ?>"
                   class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm flex-shrink-0">
                    Hari Ini
                </a>
            </form>
        </div>
    </div>

    <!-- Read-only Banner -->
    <?php if (!$hasAccess && !$isRoutineHoliday): ?>
        <div class="mb-6 bg-blue-50/80 border border-blue-200 p-4 rounded-2xl flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="ri-shield-user-line text-lg"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-blue-900">Mode Lihat (Read-only)</h4>
                <p class="text-[11px] text-blue-700 mt-0.5">
                    Pengisian absensi Muwajjah hanya dapat dilakukan oleh **Petugas Piket Muwajjah** yang bertugas hari ini atau **Admin**.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Grid Card: 1 Kartu per Wali Kelas (Persis Absensi Pengajar) -->
    <?php if (empty($classes)): ?>
        <div class="text-center py-12 bg-white rounded-2xl border border-gray-200 shadow-sm">
            <i class="ri-building-line text-4xl text-gray-300"></i>
            <p class="mt-2 text-sm text-gray-500 font-medium">Belum ada data kelas yang terdaftar.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php foreach ($classes as $c): 
                $waliList = $c['wali_kelas'] ?? [];
                
                // If a class has no wali kelas assigned
                if (empty($waliList)): 
            ?>
                    <div class="bg-white border-2 border-gray-200 rounded-2xl shadow-sm flex flex-col overflow-hidden">
                        <div class="p-5 flex-1">
                            <div class="flex justify-between items-start mb-4">
                                <div class="px-3 py-1 rounded-lg bg-white border border-gray-100 text-xs font-bold text-gray-700 shadow-sm flex items-center gap-1.5">
                                    <span>Kelas <?= htmlspecialchars($c['tingkat'] . '-' . $c['abjad']) ?></span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold <?= $c['gender'] === 'Pi' ? 'bg-pink-50 text-pink-700' : 'bg-blue-50 text-blue-700' ?>">
                                        <?= $c['gender'] === 'Pi' ? 'Pi' : 'Pa' ?>
                                    </span>
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 border-gray-200"></div>
                            </div>
                            <h3 class="text-base font-bold text-gray-900 mb-1 leading-tight flex items-center gap-1.5">
                                Muwajjah Malam
                            </h3>
                            <p class="text-xs text-amber-600 italic mt-2">Belum ada Wali Kelas</p>
                        </div>
                        <div class="px-5 py-4 bg-gray-50/50 border-t border-gray-100">
                            <button disabled class="w-full inline-flex justify-center items-center px-4 py-2.5 text-xs font-bold rounded-xl text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed">
                                Tidak ada Wali Kelas
                            </button>
                        </div>
                    </div>
                <?php 
                else:
                    // Render 1 Card for each Wali Kelas
                    foreach ($waliList as $wIdx => $w):
                        $key = $c['kelas_id'] . '_' . $w['teacher_id'];
                        $record = $existingAbsensi[$key] ?? null;
                        $hasRecord = !empty($record);
                        $status = $record['status'] ?? '';
                        
                        $statusBadge = '';
                        $cardBorder = 'border-gray-200';
                        $cardBg = 'bg-white';

                        if ($hasRecord) {
                            if ($status === 'hadir') {
                                $cardBorder = 'border-green-200';
                                $cardBg = 'bg-green-50/40';
                                $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 uppercase tracking-wider">Hadir</span>';
                            } elseif ($status === 'terlambat') {
                                $cardBorder = 'border-amber-200';
                                $cardBg = 'bg-amber-50/40';
                                $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">Terlambat</span>';
                            } elseif ($status === 'izin') {
                                $cardBorder = 'border-blue-200';
                                $cardBg = 'bg-blue-50/40';
                                $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">Izin / Sakit</span>';
                            } elseif ($status === 'badal') {
                                $cardBorder = 'border-purple-200';
                                $cardBg = 'bg-purple-50/40';
                                $penggantiId = $record['pengganti_id'] ?? '';
                                $pName = $teachers[$penggantiId]['nama'] ?? 'Pengganti';
                                $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 uppercase tracking-wider">Diganti: ' . htmlspecialchars($pName) . '</span>';
                            } elseif ($status === 'alfa') {
                                $cardBorder = 'border-red-200';
                                $cardBg = 'bg-red-50/40';
                                $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 uppercase tracking-wider">Alfa</span>';
                            }
                        }
                ?>
                        <div class="<?= $cardBg ?> border-2 <?= $cardBorder ?> rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group overflow-hidden">
                            <div class="p-5 flex-1">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="px-3 py-1 rounded-lg bg-white border border-gray-100 text-xs font-bold text-gray-700 shadow-sm group-hover:border-indigo-100 group-hover:text-indigo-600 transition-colors flex items-center gap-1.5">
                                        <span>Kelas <?= htmlspecialchars($c['tingkat'] . '-' . $c['abjad']) ?></span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold <?= $c['gender'] === 'Pi' ? 'bg-pink-50 text-pink-700' : 'bg-blue-50 text-blue-700' ?>">
                                            <?= $c['gender'] === 'Pi' ? 'Pi' : 'Pa' ?>
                                        </span>
                                        <?php if (count($waliList) > 1): ?>
                                            <span class="text-[10px] font-mono text-gray-400">#<?= $wIdx + 1 ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($hasRecord): ?>
                                        <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center text-white shadow-lg shadow-green-100">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-6 h-6 rounded-full border-2 border-gray-200 group-hover:border-indigo-300 transition-colors"></div>
                                    <?php endif; ?>
                                </div>

                                <h3 class="text-base font-bold text-gray-900 mb-1 leading-tight line-clamp-1" title="<?= htmlspecialchars($w['formatted_nama']) ?>">
                                    <?= htmlspecialchars($w['formatted_nama']) ?>
                                </h3>

                                <p class="text-xs text-gray-500 mb-4 flex items-center gap-1.5 font-medium">
                                    <i class="ri-user-star-line text-indigo-500"></i>
                                    <span>Wali Kelas <?= htmlspecialchars($c['tingkat'] . '-' . $c['abjad']) ?></span>
                                </p>

                                <?php if ($hasRecord): ?>
                                    <div class="mt-2">
                                        <?= $statusBadge ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="px-5 py-4 bg-gray-50/50 border-t border-gray-100">
                                <?php if ($isRoutineHoliday): ?>
                                    <button disabled 
                                            class="w-full inline-flex justify-center items-center px-4 py-2.5 text-xs font-bold rounded-xl shadow-sm text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed">
                                        <i class="ri-sun-cloudy-line mr-2 text-sm"></i> Libur Rutin Malam
                                    </button>
                                <?php else: ?>
                                    <button onclick="openSingleWaliModal(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($w), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($record), ENT_QUOTES) ?>)" 
                                            class="w-full inline-flex justify-center items-center px-4 py-2.5 text-xs font-bold rounded-xl shadow-sm text-white transition-all duration-200 <?= $hasRecord ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-100' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-100' ?>">
                                        <i class="<?= $hasRecord ? 'ri-edit-line' : 'ri-checkbox-circle-line' ?> mr-2 text-sm"></i>
                                        <?= $hasRecord ? 'Edit Absensi' : 'Catat Absensi' ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<!-- Single Wali Kelas Modal (Pop-up persis seperti Absensi Pengajar per Guru) -->
<div id="singleWaliModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 backdrop-blur-xs transition-opacity" onclick="toggleModal('singleWaliModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg sm:my-8 sm:align-middle sm:w-full mx-auto border border-gray-100">
            <form action="<?= url('/muwajjah/store') ?>" method="POST">
                <?= csrf_token_field() ?>
                <input type="hidden" name="tanggal" value="<?= htmlspecialchars($selectedDate) ?>">
                <input type="hidden" name="attendance[0][kelas_id]" id="modal_kelas_id">
                <input type="hidden" name="attendance[0][teacher_id]" id="modal_teacher_id">

                <!-- Modal Header -->
                <div class="bg-indigo-600 px-6 py-4 text-white flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-bold" id="modal_header_title">Catat Absensi Wali Kelas</h3>
                        <p class="text-xs text-indigo-100 mt-0.5" id="modal_header_subtitle">Kelas</p>
                    </div>
                    <button type="button" onclick="toggleModal('singleWaliModal')" class="text-indigo-200 hover:text-white transition-colors">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="bg-white px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Status Kehadiran <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-indigo-50/40 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" name="attendance[0][status]" value="hadir" id="st_hadir" required onchange="updateBadalVisibility(this.value)" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs font-semibold text-gray-800 flex items-center gap-1.5">
                                        <i class="ri-checkbox-circle-line text-green-600 text-base"></i> Hadir
                                    </span>
                                </div>
                            </label>
                            <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-indigo-50/40 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" name="attendance[0][status]" value="badal" id="st_badal" required onchange="updateBadalVisibility(this.value)" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs font-semibold text-gray-800 flex items-center gap-1.5">
                                        <i class="ri-user-shared-line text-purple-600 text-base"></i> Diganti Guru Pengganti
                                    </span>
                                </div>
                            </label>
                            <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-indigo-50/40 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" name="attendance[0][status]" value="izin" id="st_izin" required onchange="updateBadalVisibility(this.value)" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs font-semibold text-gray-800 flex items-center gap-1.5">
                                        <i class="ri-user-unfollow-line text-blue-600 text-base"></i> Izin / Sakit
                                    </span>
                                </div>
                            </label>
                            <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-indigo-50/40 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" name="attendance[0][status]" value="alfa" id="st_alfa" required onchange="updateBadalVisibility(this.value)" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs font-semibold text-gray-800 flex items-center gap-1.5">
                                        <i class="ri-close-circle-line text-red-600 text-base"></i> Tidak Hadir (Alfa)
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Dropdown Guru Pengganti (Badal) -->
                    <div id="modal_field_badal" class="hidden">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Guru Pengganti (Badal) <span class="text-red-500">*</span></label>
                        <div class="w-full">
                            <select name="attendance[0][pengganti_id]" id="modal_select_badal" <?= (!$hasAccess || $isRoutineHoliday) ? 'disabled' : '' ?>
                                    class="tom-select block w-full text-xs">
                                <option value="">-- Ketik / Pilih Guru Pengganti --</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan (Opsional)</label>
                        <input type="text" name="attendance[0][catatan]" id="modal_input_catatan" placeholder="Misal: Alasan keterlambatan/izin/digantikan..." <?= (!$hasAccess || $isRoutineHoliday) ? 'disabled' : '' ?>
                               class="block w-full border border-gray-200 rounded-xl text-xs px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none">
                    </div>
                </div>

                <style>
                    #modal_field_badal .ts-wrapper {
                        width: 100% !important;
                        display: block !important;
                        padding: 0 !important;
                        border: none !important;
                        box-shadow: none !important;
                        background: transparent !important;
                    }
                    #modal_field_badal .ts-control {
                        width: 100% !important;
                        min-height: 46px !important;
                        height: 46px !important;
                        border-radius: 0.75rem !important;
                        border: 1px solid #e5e7eb !important;
                        padding: 0.75rem 1rem !important;
                        font-size: 0.75rem !important;
                        display: flex !important;
                        align-items: center !important;
                        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
                        background-color: #ffffff !important;
                    }
                    #modal_input_catatan {
                        width: 100% !important;
                        height: 46px !important;
                        border-radius: 0.75rem !important;
                        border: 1px solid #e5e7eb !important;
                        padding: 0.75rem 1rem !important;
                        font-size: 0.75rem !important;
                        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
                        background-color: #ffffff !important;
                    }
                </style>

                <!-- Modal Footer -->
                <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-100 flex flex-row items-center justify-between">
                    <div id="delete_btn_container" class="hidden">
                        <?php if ($hasAccess && !$isRoutineHoliday): ?>
                            <button type="button" onclick="deleteAbsensiRecord()" class="inline-flex justify-center items-center rounded-xl border border-red-200 px-4 py-2.5 bg-red-50 text-xs font-bold text-red-600 hover:bg-red-100 transition-all">
                                <i class="ri-delete-bin-line mr-1.5 text-sm"></i> Hapus Absensi
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-3 ml-auto">
                        <button type="button" onclick="toggleModal('singleWaliModal')" class="inline-flex justify-center rounded-xl border border-gray-300 shadow-xs px-5 py-2.5 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 transition-all">
                            Batal
                        </button>
                        <?php if ($hasAccess && !$isRoutineHoliday): ?>
                            <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent shadow-md px-5 py-2.5 bg-indigo-600 text-xs font-bold text-white hover:bg-indigo-700 focus:outline-none transition-all">
                                Simpan Absensi
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) el.classList.toggle('hidden');
}

function openSingleWaliModal(kelasObj, waliObj, recordObj) {
    document.getElementById('modal_header_title').innerText = waliObj.formatted_nama;
    document.getElementById('modal_header_subtitle').innerText = `Wali Kelas ${kelasObj.tingkat}-${kelasObj.abjad}`;

    document.getElementById('modal_kelas_id').value = kelasObj.id;
    document.getElementById('modal_teacher_id').value = waliObj.teacher_id;

    const currStatus = (recordObj && recordObj.status) ? recordObj.status : '';
    const currPengganti = (recordObj && recordObj.pengganti_id) ? recordObj.pengganti_id : '';
    const currCatatan = (recordObj && recordObj.catatan) ? recordObj.catatan : '';

    // Remove any leftover delete status action input
    const delInput = document.getElementById('modal_status_delete_action');
    if (delInput) delInput.remove();

    // Check radio status (Jika belum diisi absensinya, tidak ada yang ter-select)
    const radioHadir = document.getElementById('st_hadir');
    const radioBadal = document.getElementById('st_badal');
    const radioIzin = document.getElementById('st_izin');
    const radioAlfa = document.getElementById('st_alfa');

    if (radioHadir) { radioHadir.checked = (currStatus === 'hadir'); radioHadir.required = true; }
    if (radioBadal) { radioBadal.checked = (currStatus === 'badal' || currStatus === 'diganti'); radioBadal.required = true; }
    if (radioIzin) { radioIzin.checked = (currStatus === 'izin'); radioIzin.required = true; }
    if (radioAlfa) { radioAlfa.checked = (currStatus === 'alfa'); radioAlfa.required = true; }

    // Badal dropdown (TomSelect integration)
    const selectBadal = document.getElementById('modal_select_badal');
    if (selectBadal) {
        selectBadal.value = currPengganti;
        if (selectBadal.tomselect) {
            selectBadal.tomselect.setValue(currPengganti || '');
        }
    }

    // Catatan
    const inputCatatan = document.getElementById('modal_input_catatan');
    if (inputCatatan) inputCatatan.value = currCatatan;

    // Show/Hide Delete Button
    const deleteBtnBox = document.getElementById('delete_btn_container');
    if (deleteBtnBox) {
        if (currStatus) {
            deleteBtnBox.classList.remove('hidden');
        } else {
            deleteBtnBox.classList.add('hidden');
        }
    }

    updateBadalVisibility(currStatus);
    toggleModal('singleWaliModal');
}

function updateBadalVisibility(val) {
    const badalBox = document.getElementById('modal_field_badal');
    const selectBadal = document.getElementById('modal_select_badal');
    if (badalBox) {
        if (val === 'badal' || val === 'diganti') {
            badalBox.classList.remove('hidden');
            if (selectBadal) {
                selectBadal.required = true;
            }
        } else {
            badalBox.classList.add('hidden');
            if (selectBadal) {
                selectBadal.required = false;
                selectBadal.value = '';
                if (selectBadal.tomselect) {
                    selectBadal.tomselect.clear();
                }
            }
        }
    }
}

function deleteAbsensiRecord() {
    if (!confirm('Apakah Anda yakin ingin menghapus / membatalkan catatan absensi ini?')) {
        return;
    }
    let statusInput = document.getElementById('modal_status_delete_action');
    if (!statusInput) {
        statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'attendance[0][status]';
        statusInput.id = 'modal_status_delete_action';
        document.querySelector('#singleWaliModal form').appendChild(statusInput);
    }
    statusInput.value = 'delete';

    // Remove required from status radios
    document.querySelectorAll('#singleWaliModal input[type="radio"]').forEach(r => r.required = false);

    document.querySelector('#singleWaliModal form').submit();
}
</script>

<?php renderFooter(); ?>
