<?php
// Prepare Data
$id = $exam['id'];
$isFinished = ($exam['status'] ?? '') === 'selesai';
$userRole = auth_get_role();
$isReadOnly = $isFinished;
$canEditSkorMaks = ($userRole === 'admin' && !$isFinished);
$canEditScores = ($userRole !== 'admin' && !$isFinished);

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


    <!-- Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4 flex flex-col md:flex-row md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($exam['mapel_nama']) ?></h1>
            <div class="mt-1 flex items-center text-sm text-gray-500 gap-4">
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">
                    Kelas <?= htmlspecialchars($exam['tingkat']) ?>-<?= htmlspecialchars($exam['abjad']) ?>
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <?= htmlspecialchars($exam['pengajar_nama'] ?? 'Unknown') ?>
                </span>
            <?php if ($isFinished): ?>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-bold uppercase tracking-wide">
                    Selesai
                </span>
            <?php endif; ?>
            <?php if ($isReadOnly && !$isFinished && $userRole === 'admin'): ?>
                 <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full font-bold uppercase tracking-wide">
                    View Only (Admin)
                </span>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-2 text-center bg-gray-50 p-2 rounded-lg border border-gray-100 mb-4">
        <div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Skor Max (Soal)</div>
            <div class="flex justify-center mt-1">
                <input type="number" name="skor_maks" id="skor_maks_input" value="<?= $skor_maks ?>" 
                    <?= !$canEditSkorMaks ? 'disabled' : '' ?>
                    oninput="updateConfig()"
                    class="w-20 text-center font-bold text-lg text-gray-900 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-1 border">
            </div>
        </div>
        <div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Nilai Max</div>
            <div class="font-bold text-lg text-indigo-600"><?= $max_val ?></div>
        </div>
        <div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Nilai Min</div>
            <div class="font-bold text-lg text-red-500"><?= $min_val ?></div>
        </div>
    </div>
</div>

<!-- Hidden Config for JS -->
<input type="hidden" id="nilai_maks" value="<?= $js_nilai_maks ?>">
<input type="hidden" id="nilai_min" value="<?= $js_nilai_min ?>">

<!-- Student Table -->

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider w-12">No</th>
                    <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">Nama Santri</th>
                    <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider w-24 sm:w-32 text-center">Skor</th>
                    <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider w-16 sm:w-20 text-center">Nilai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php 
                $totalNilai = 0;
                $countNilai = 0;
                foreach ($students as $i => $row): 
                    if (is_numeric($row['nilai'])) {
                        $totalNilai += $row['nilai'];
                        $countNilai++;
                    }
                ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 font-mono">
                            <?= $i + 1 ?>
                            <input type="hidden" name="student_id[]" value="<?= $row['student_id'] ?>">
                        </td>
                        <td class="px-3 py-2 text-sm font-medium text-gray-900 leading-tight">
                            <?= htmlspecialchars(preg_replace('/^Siswa\s+.*?\s*No\s+(\d+)/i', 'No. $1', $row['nama'])) ?>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <input type="text" name="skor[]" value="<?= $row['skor'] ?>"
                                <?= !$canEditScores ? 'disabled' : '' ?>
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm p-1 border transition-shadow text-center disabled:bg-gray-100 disabled:text-gray-500"
                                placeholder="<?= !$canEditScores ? '-' : '...' ?>" oninput="calculateRow(this)">
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <input type="text" readonly tabindex="-1" value="<?= is_numeric($row['nilai']) ? round($row['nilai']) : '' ?>"
                                class="nilai-output bg-transparent text-gray-900 font-bold block w-full border-none text-sm p-0 text-center">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <div class="text-[10px] text-gray-500 uppercase">
                * <b>-</b> = Absen, <b>0</b> = Salah
            </div>
            <div class="text-xs font-semibold text-gray-900">
                Rata-rata: <span id="rataRataDisplay" class="text-indigo-600 text-sm ml-1"><?= $countNilai > 0 ? round($totalNilai / $countNilai, 2) : 0 ?></span>
            </div>
        </div>
    </div>

    <!-- Sticky Save Button -->
    <?php if (!$isReadOnly): ?>
    <div class="fixed bottom-0 inset-x-0 pb-6 px-4 pointer-events-none">
        <div class="max-w-4xl mx-auto flex justify-end gap-3 pointer-events-auto">
                <button type="submit" name="action" value="save" onclick="return confirm('Apakah Anda yakin ingin menyimpan <?= $userRole === 'admin' ? 'konfigurasi' : 'draft nilai' ?> ini?');" class="bg-white hover:bg-gray-50 text-indigo-600 font-bold py-3 px-6 rounded-full shadow-lg border border-indigo-100 transition-transform transform hover:-translate-y-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <?= $userRole === 'admin' ? 'Simpan Update' : 'Simpan Draft' ?>
                </button>
                <?php if ($canEditScores): ?>
                <button type="submit" name="action" value="finish" onclick="return validateFinish()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-full shadow-xl transition-transform transform hover:-translate-y-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Selesai Diperiksa
                </button>
                <?php endif; ?>
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
        const output = row.querySelector('.nilai-output');
        const nilaiMaks = parseFloat(document.getElementById('nilai_maks').value) || 100;
        const nilaiMin = parseFloat(document.getElementById('nilai_min').value) || 0;
        const skorMaks = overrideSkorMaks || parseFloat(document.getElementById('skor_maks_input').value) || 100;
        
        let valStr = input.value.trim();

        // CASE 1: Absent (-)
        if (valStr === '-') {
            output.value = '0'; 
            window.updateAverage();
            return;
        }

        // CASE 2: Empty
        if (valStr === '') {
            output.value = '';
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
            output.value = Math.round(nilaiMin);
            window.updateAverage();
            return;
        }

        // CASE 5: Normal Calculation
        let nilai = Math.round((skor / skorMaks) * nilaiMaks);

        // Clamping
        if (nilai < nilaiMin) nilai = nilaiMin;
        if (nilai > nilaiMaks) nilai = nilaiMaks;

        output.value = Math.round(nilai);
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

</script>

<?php renderFooter(); ?>
