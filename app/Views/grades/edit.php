<?php
// Prepare Data
$id = $exam['id'];
$userRole = auth_get_role();
$userId = auth_get_user_id();
$isAdmin = ($userRole === 'admin');
$isPanitia = $isPanitia ?? false;
$isAdminOrPanitia = ($isAdmin || $isPanitia);
$isExaminer = (isset($exam['teacher_id']) && $exam['teacher_id'] == $userId);
$isFinished = ($exam['status'] === 'selesai');
$sessionOpen = (isset($exam['session_is_open']) && $exam['session_is_open'] == 1);

// Logic:
// 1. Finished exams are always read-only for everyone (must Unlock first)
// 2. Admin/Panitia can only edit Skor Maks (Configuration)
// 3. Designated Examiner can only edit Scores (subject to Session Open)
$isReadOnly = $isFinished || (!$isAdminOrPanitia && !$sessionOpen);
$canEditSkorMaks = $isAdminOrPanitia && !$isFinished;
$canEditScores = $isExaminer && $sessionOpen && !$isFinished;

// Parse Scale for JS
$skala = $exam['skala'] ?? '80-30';
list($max_val, $min_val) = explode('-', $skala);
$max_val = (int)$max_val;
$min_val = (int)$min_val;
$skor_maks = (float)($exam['skor_maks'] ?? 100);

// Locale-safe values for JS (ensure dot as decimal separator)
$js_skor_maks = number_format($skor_maks, 2, '.', '');
$js_nilai_maks = number_format($max_val, 2, '.', '');
$js_nilai_min = number_format($min_val, 2, '.', '');

renderHeader("Input Nilai - " . htmlspecialchars($exam['mapel_nama']));
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24">
<form method="post" action="<?= url('/grades/update') ?>" id="gradeForm">
    <?= csrf_token_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">


    <!-- Header Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-indigo-600 px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight leading-none"><?= htmlspecialchars($exam['mapel_nama']) ?></h1>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-sm border border-white/10 uppercase">
                        Kelas <?= htmlspecialchars($exam['tingkat']) ?>-<?= htmlspecialchars($exam['abjad']) ?>
                    </span>
                    <span class="inline-flex items-center text-xs font-medium text-indigo-100 gap-1.5 px-2.5 py-0.5 rounded-full bg-white/10">
                        <i class="ri-user-star-line"></i>
                        <?= htmlspecialchars($exam['pengajar_nama'] ?? 'Unknown') ?>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/50 text-white border border-white/10 uppercase tracking-widest">
                        <?= htmlspecialchars($exam['exam_type'] ?? '-') ?>
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <?php if ($isFinished): ?>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-xs font-black bg-white text-green-600 shadow-lg border border-green-100 uppercase tracking-tighter animate-pulse">
                        <i class="ri-checkbox-circle-fill mr-1.5 text-lg"></i> TERVERIFIKASI
                    </span>
                <?php elseif (!$isFinished && $sessionOpen): ?>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-xs font-black bg-white text-indigo-600 shadow-lg border border-indigo-100 uppercase tracking-tighter">
                        <i class="ri-edit-circle-fill mr-1.5 text-lg"></i> MODE INPUT AKTIF
                    </span>
                <?php elseif (!$isFinished && !$sessionOpen): ?>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-xs font-black bg-white text-red-600 shadow-lg border border-red-100 uppercase tracking-tighter">
                        <i class="ri-lock-2-fill mr-1.5 text-lg"></i> SESI TERKUNCI
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dashboard Bar -->
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100 bg-gray-50/50">
            <!-- Skor Maks Input -->
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Skor Maks (Soal)</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Total bobot poin soal</p>
                </div>
                <div class="relative">
                    <input type="number" name="skor_maks" id="skor_maks_input" value="<?= (float)$skor_maks ?>" 
                        <?= !$canEditSkorMaks ? 'disabled' : '' ?>
                        oninput="updateConfig()"
                        class="w-24 text-right pr-3 pl-3 font-black text-2xl text-indigo-600 bg-white border-2 border-transparent focus:border-indigo-500 rounded-xl shadow-inner transition-all <?= !$canEditSkorMaks ? 'opacity-50 cursor-not-allowed' : 'hover:border-indigo-200' ?>">
                </div>
            </div>

            <!-- Nilai Maks Info -->
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Target Nilai (Max)</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Skala tertinggi rapor</p>
                </div>
                <p class="text-3xl font-black text-gray-900"><?= $max_val ?></p>
            </div>

            <!-- Nilai Min Info -->
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Base Nilai (Min)</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Skala terendah rapor</p>
                </div>
                <p class="text-3xl font-black text-red-500"><?= $min_val ?></p>
            </div>
        </div>
    </div>

    <?php if (!$isAdmin && !$isPanitia && !$sessionOpen && !$isFinished): ?>
        <div class="bg-red-50 border border-red-100 p-4 mb-6 rounded-2xl flex items-center gap-4 animate-in fade-in slide-in-from-top-2 duration-500">
            <div class="bg-red-500 text-white rounded-xl p-3 shadow-md shadow-red-200">
                <i class="ri-error-warning-fill text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-red-900 font-black uppercase tracking-tight">Akses Terbatas: Sesi Ditutup</p>
                <p class="text-xs text-red-600/80 font-medium">Panitia telah menonaktifkan input nilai. Hubungi bagian kurikulum jika butuh perbaikan.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Student List Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden mb-12">
        <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Daftar Nilai Santri</h3>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">Rata-rata Kelas</p>
                    <?php 
                        $totalNilai = 0;
                        $countNilai = 0;
                        foreach ($students as $row) {
                            if (is_numeric($row['nilai'])) {
                                $totalNilai += $row['nilai'];
                                $countNilai++;
                            }
                        }
                    ?>
                    <p id="rataRataDisplay" class="text-lg font-black text-indigo-600 leading-none">
                        <?= $countNilai > 0 ? round($totalNilai / $countNilai, 2) : 0 ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto overflow-y-visible">
            <table class="min-w-full divide-y divide-gray-100 table-fixed md:table-auto">
                <thead class="hidden md:table-header-group">
                    <tr class="bg-white">
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/30 w-24">No. Bayanat</th>
                        <?php if ($isAdminOrPanitia): ?>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Lengkap</th>
                        <?php endif; ?>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-40">Skor Peserta</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-24">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 block md:table-row-group">
                    <?php foreach ($students as $i => $row): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group flex flex-col md:table-row border-b md:border-b-0 border-gray-50 last:border-0 p-4 md:p-0">
                            <!-- No Bayanat -->
                            <td class="md:px-6 md:py-5 whitespace-nowrap text-[11px] mb-1 md:mb-0">
                                <div class="flex items-center gap-2">
                                    <span class="md:hidden not-italic text-[9px] font-black text-gray-400 uppercase tracking-tighter mr-1">No. Bayanat</span>
                                    <input type="hidden" name="student_id[]" value="<?= $row['student_id'] ?>">
                                    <?php if ($isAdminOrPanitia): ?>
                                        <input type="number" name="no_bayanat[]" value="<?= $row['no_bayanat'] ?>" 
                                            placeholder="Set #"
                                            min="1"
                                            oninput="if(this.value < 1) this.value = ''; checkDuplicateBayanat();"
                                            <?= $isFinished ? 'disabled' : '' ?>
                                            class="bayanat-input w-16 h-9 text-center font-black text-indigo-600 bg-gray-50 border border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-0 transition-all">
                                    <?php else: ?>
                                        <span class="text-lg font-black text-gray-900 bg-gray-100 px-3 py-1 rounded-lg border border-gray-200 min-w-[3rem] text-center">
                                            <?= $row['no_bayanat'] ?: '??' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Name (Admin/Panitia Only) -->
                            <?php if ($isAdminOrPanitia): ?>
                            <td class="md:px-6 md:py-5 mb-4 md:mb-0">
                                <div class="text-sm font-bold text-gray-800 tracking-tight leading-tight uppercase md:truncate md:max-w-xs lg:max-w-md">
                                    <?= htmlspecialchars($row['nama']) ?>
                                    <div class="text-[9px] text-gray-400 font-medium tracking-normal lowercase"><?= $row['nis'] ?></div>
                                </div>
                            </td>
                            <?php endif; ?>
                            
                            <!-- Input & Output Container for Mobile -->
                            <td class="md:px-6 md:py-5 flex items-center gap-3 md:table-cell">
                                <div class="flex-1 md:w-full">
                                    <label class="block md:hidden text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 ml-2">Input Skor</label>
                                    <input type="text" name="skor[]" value="<?= is_numeric($row['skor']) ? (float)$row['skor'] : $row['skor'] ?>"
                                        <?= !$canEditScores ? 'disabled' : '' ?>
                                        inputmode="decimal"
                                        oninput="this.value = this.value.replace(/[^0-9.\-]/g, ''); if(parseFloat(this.value) < 0) this.value = '0'; calculateRow(this);"
                                        class="w-full h-12 md:h-11 bg-white border-2 border-gray-100 rounded-2xl px-4 text-center font-black text-gray-900 focus:border-indigo-500 focus:ring-0 transition-all shadow-sm hover:border-gray-200 disabled:bg-gray-100/50 disabled:text-gray-400 disabled:border-transparent"
                                        placeholder="<?= !$canEditScores ? '-' : '...' ?>">
                                </div>
                                
                                <!-- Result beside input on mobile -->
                                <div class="flex flex-col items-center justify-center w-20 md:hidden bg-gray-50 rounded-2xl p-2 border border-gray-100">
                                    <label class="text-[8px] font-black text-gray-400 uppercase tracking-tighter mb-1">Nilai</label>
                                    <input type="text" readonly tabindex="-1" value="<?= is_numeric($row['nilai']) ? round($row['nilai']) : '' ?>"
                                        class="nilai-output-mobile bg-transparent text-indigo-600 font-black text-xl w-full text-center p-0 border-none pointer-events-none">
                                </div>
                            </td>

                            <!-- Hidden Nilai Column on Mobile (redundant with the one above) but keep for JS desktop compatibility -->
                            <td class="px-6 py-5 hidden md:table-cell">
                                <div class="flex flex-col items-center justify-center">
                                    <input type="text" readonly tabindex="-1" value="<?= is_numeric($row['nilai']) ? round($row['nilai']) : '' ?>"
                                        class="nilai-output bg-transparent text-gray-900 font-black text-xl w-full text-center p-0 border-none pointer-events-none transition-all group-hover:scale-110 group-hover:text-indigo-600">
                                    <div class="h-1 w-8 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-indigo-400 transition-all" style="width: <?= min(100, (is_numeric($row['nilai']) ? round($row['nilai']) : 0)) ?>%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><b>-</b> = Absen</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-400"></span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><b>0</b> = Salah Semua</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Toolbar -->
    <?php 
    // Button Logic:
    // 1. If Finished: No buttons (must Unlock from index)
    // 2. If Admin/Panitia: "Update Konfigurasi" (Skor Maks)
    // 3. If Examiner: "Simpan Draft" & "Selesai Diperiksa"
    ?>
    <?php if (!$isFinished): ?>
    <div class="fixed bottom-0 inset-x-0 pb-8 px-6 pointer-events-none z-50">
        <div class="max-w-4xl mx-auto pointer-events-auto flex items-center justify-between p-4 bg-white/80 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl ring-1 ring-black/5">
            <div class="hidden md:flex flex-col ml-4">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                    Role: <?= $isAdminOrPanitia ? 'Panitia Ujian' : ($isExaminer ? 'Pemeriksa' : 'Viewer') ?>
                </p>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full <?= $sessionOpen ? 'bg-green-500 animate-pulse' : 'bg-red-500' ?>"></span>
                    <p class="text-xs font-bold text-gray-600"><?= $sessionOpen ? 'Sesi Terbuka' : 'Sesi Terkunci' ?></p>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <?php if ($isAdminOrPanitia): ?>
                    <div id="duplicateWarning" class="hidden flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-xl text-[10px] font-bold uppercase tracking-widest border border-red-100 animate-pulse">
                        <i class="ri-error-warning-fill text-lg"></i>
                        Ada Nomor Bayanat Ganda!
                    </div>
                    <button type="submit" id="saveConfigBtn" name="action" value="save" class="w-full md:w-auto h-14 px-10 rounded-2xl bg-indigo-600 text-white font-black text-sm uppercase tracking-widest shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="ri-settings-3-line text-lg"></i>
                        Update Konfigurasi & Bayanat
                    </button>
                    <?php if (!$isExaminer): ?>
                        <div class="hidden md:flex items-center px-4 py-2 bg-gray-100 rounded-xl text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                             Nilai Santri Read-Only
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($isExaminer && $sessionOpen): ?>
                    <button type="submit" name="action" value="save" onclick="return confirm('Simpan hasil koreksi sebagai draft?');" class="flex-1 md:flex-none h-14 px-8 rounded-2xl bg-white border-2 border-gray-100 text-gray-600 font-black text-sm uppercase tracking-widest hover:bg-gray-50 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="ri-save-3-line text-lg"></i>
                        Simpan Draft
                    </button>
                    <button type="submit" name="action" value="finish" onclick="return validateFinish()" class="flex-[2] md:flex-none h-14 px-10 rounded-2xl bg-green-600 text-white font-black text-sm uppercase tracking-widest shadow-xl shadow-green-200 hover:bg-green-700 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="ri-checkbox-circle-line text-lg"></i>
                        Selesai Diperiksa
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</form>
</main>

<script>
    function validateFinish() {
        const inputs = document.querySelectorAll('input[name="skor[]"]');
        for (let input of inputs) {
            if (input.value.trim() === '') {
                alert('Gagal: Semua kolom skor harus diisi sebelum menandai selesai.');
                input.focus();
                return false;
            }
        }
        return confirm('Apakah Anda yakin ingin menyelesaikan pemeriksaan ini? Status akan menjadi Selesai dan tidak dapat diubah lagi.');
    }

    // Robust real-time calculation logic
    window.updateConfig = function() {
        const skorMaksInput = document.getElementById('skor_maks_input');
        if (!skorMaksInput) return;
        
        let currentSkorMaks = parseFloat(skorMaksInput.value) || 100;
        
        // Recalculate all rows
        document.querySelectorAll('input[name="skor[]"]').forEach(input => {
            window.calculateRow(input, currentSkorMaks);
        });
        window.updateAverage();
    };

    window.calculateRow = function(input, overrideSkorMaks = null) {
        const row = input.closest('tr');
        const outputs = row.querySelectorAll('.nilai-output, .nilai-output-mobile');
        const progressBar = row.querySelector('.h-full.bg-indigo-400');
        const nilaiMaks = parseFloat(document.getElementById('nilai_maks').value) || 100;
        const nilaiMin = parseFloat(document.getElementById('nilai_min').value) || 0;
        const skorMaks = overrideSkorMaks || parseFloat(document.getElementById('skor_maks_input').value) || 100;
        
        let valStr = input.value.trim();

        // CASE 1: Absent (-)
        if (valStr === '-') {
            outputs.forEach(o => o.value = '0');
            if (progressBar) progressBar.style.width = '0%';
            window.updateAverage();
            return;
        }

        // CASE 2: Empty
        if (valStr === '') {
            outputs.forEach(o => o.value = '');
            if (progressBar) progressBar.style.width = '0%';
            window.updateAverage();
            return;
        }

        let skor = parseFloat(valStr.replace(',', '.')); // Robust parsing

        // CASE 3: Invalid Input
        if (isNaN(skor)) {
            return;
        }

        // CASE 4: Score 0 (All Wrong) -> Get Minimum Grade
        if (skor === 0) {
            outputs.forEach(o => o.value = Math.round(nilaiMin));
            if (progressBar) progressBar.style.width = ((nilaiMin / nilaiMaks) * 100) + '%';
            window.updateAverage();
            return;
        }

        // CASE 5: Normal Calculation
        let nilai = Math.round((skor / skorMaks) * nilaiMaks);

        // Clamping
        if (nilai < nilaiMin) nilai = nilaiMin;
        if (nilai > nilaiMaks) nilai = nilaiMaks;

        let finalNilai = Math.round(nilai);
        outputs.forEach(o => o.value = finalNilai);
        if (progressBar) progressBar.style.width = ((finalNilai / nilaiMaks) * 100) + '%';
        window.updateAverage();
    };

    window.updateAverage = function() {
        const outputs = document.querySelectorAll('.nilai-output');
        let total = 0;
        let count = 0;

        outputs.forEach(el => {
            let valStr = el.value.trim();
            if (valStr !== '') {
                let val = parseFloat(valStr);
                if (!isNaN(val)) {
                    total += val;
                    count++;
                }
            }
        });

        const avg = count > 0 ? (total / count).toFixed(2) : 0;
        const display = document.getElementById('rataRataDisplay');
        if (display) display.textContent = avg;
    };

    window.checkDuplicateBayanat = function() {
        const inputs = document.querySelectorAll('.bayanat-input');
        const warning = document.getElementById('duplicateWarning');
        const saveBtn = document.getElementById('saveConfigBtn');
        const values = [];
        let hasDuplicate = false;

        // Reset states
        inputs.forEach(input => {
            input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
            input.classList.add('border-gray-200', 'bg-gray-50', 'text-indigo-600');
        });

        inputs.forEach(input => {
            const val = input.value.trim();
            if (val !== '') {
                if (values.includes(val)) {
                    hasDuplicate = true;
                    // Highlight all inputs with this duplicate value
                    inputs.forEach(i => {
                        if (i.value.trim() === val) {
                            i.classList.remove('border-gray-200', 'bg-gray-50', 'text-indigo-600');
                            i.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
                        }
                    });
                }
                values.push(val);
            }
        });

        if (hasDuplicate) {
            warning.classList.remove('hidden');
            saveBtn.disabled = true;
            saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warning.classList.add('hidden');
            saveBtn.disabled = false;
            saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    };

</script>

<?php renderFooter(); ?>
