<?php
// Prepare Data
$id = $exam['id'];
$isFinished = ($exam['status'] ?? '') === 'selesai';
$userRole = auth_get_role();
$isReadOnly = $isFinished || ($userRole === 'admin');

// Parse Scale for JS
$skala = $exam['skala'] ?? '80-30';
list($max_val, $min_val) = explode('-', $skala);
$max_val = (int)$max_val;
$min_val = (int)$min_val;
$skor_maks = (float)($exam['skor_maks'] ?? 100);

renderHeader("Input Nilai - " . htmlspecialchars($exam['mapel_nama']));
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24">

    <!-- Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 flex flex-col md:flex-row md:justify-between gap-6">
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

    <div class="grid grid-cols-3 gap-4 text-center bg-gray-50 p-3 rounded-lg border border-gray-100">
        <div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Skor Max</div>
            <div class="font-bold text-lg text-gray-900"><?= $skor_maks ?></div>
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
<input type="hidden" id="skor_maks" value="<?= $skor_maks ?>">
<input type="hidden" id="nilai_maks" value="<?= $max_val ?>">
<input type="hidden" id="nilai_min" value="<?= $min_val ?>">

<!-- Form -->
<form method="post" action="<?= url('/grades/update') ?>" id="gradeForm">
    <?= csrf_token_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Santri</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skor Semifinal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            <?= $i + 1 ?>
                            <input type="hidden" name="student_id[]" value="<?= $row['student_id'] ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($row['nama']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" name="skor[]" value="<?= $row['skor'] ?>"
                                <?= $isReadOnly ? 'disabled' : '' ?>
                                class="w-full sm:w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border transition-shadow text-center disabled:bg-gray-100 disabled:text-gray-500"
                                placeholder="<?= $isReadOnly ? '-' : 'Skor' ?>" oninput="calculateRow(this)">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" readonly tabindex="-1" value="<?= $row['nilai'] ?>"
                                class="nilai-output bg-transparent text-gray-900 font-bold block w-24 border-none sm:text-sm p-0 text-center">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <div class="text-xs text-gray-500">
                * Ketik <b>-</b> untuk absen (Nilai 0). Ketik <b>0</b> untuk salah semua (Nilai Min).
            </div>
            <div class="font-medium text-gray-900">
                Rata-rata Kelas: <span id="rataRataDisplay" class="text-indigo-600 text-lg ml-2"><?= $countNilai > 0 ? round($totalNilai / $countNilai, 2) : 0 ?></span>
            </div>
        </div>
    </div>

    <!-- Sticky Save Button -->
    <?php if (!$isReadOnly): ?>
    <div class="fixed bottom-0 inset-x-0 pb-6 px-4 pointer-events-none">
        <div class="max-w-4xl mx-auto flex justify-end gap-3 pointer-events-auto">
            <button type="submit" name="action" value="save" onclick="return confirm('Apakah Anda yakin ingin menyimpan draft penilaian ini?');" class="bg-white hover:bg-gray-50 text-indigo-600 font-bold py-3 px-6 rounded-full shadow-lg border border-indigo-100 transition-transform transform hover:-translate-y-1 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                Simpan Draft
            </button>
            <button type="submit" name="action" value="finish" onclick="return validateFinish()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-full shadow-xl transition-transform transform hover:-translate-y-1 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Selesai Diperiksa
            </button>
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

    const skorMaks = parseFloat(document.getElementById('skor_maks').value) || 100;
    const nilaiMaks = parseFloat(document.getElementById('nilai_maks').value) || 100;
    const nilaiMin = parseFloat(document.getElementById('nilai_min').value) || 0;

    function calculateRow(input) {
        const row = input.closest('tr');
        const output = row.querySelector('.nilai-output');
        let valStr = input.value.trim();

        // CASE 1: Absent (-)
        if (valStr === '-') {
            output.value = '0'; // Absen = 0 strictly
            updateAverage();
            return;
        }

        // CASE 2: Empty
        if (valStr === '') {
            output.value = '';
            updateAverage();
            return;
        }

        let skor = parseFloat(valStr);

        // CASE 3: Invalid Input
        if (isNaN(skor)) {
            // output.value = ''; // Optional: Clear or keep previous?
            return;
        }

        // CASE 4: Score 0 (All Wrong) -> Get Minimum Grade
        if (skor === 0) {
            output.value = nilaiMin;
            updateAverage();
            return;
        }

        // CASE 5: Normal Calculation
        // Formula: (Score / MaxScore) * MaxGrade
        let nilai = (skor / skorMaks) * nilaiMaks;

        // Floor at MinGrade
        if (nilai < nilaiMin) nilai = nilaiMin;

        // Cap at Max
        if (nilai > nilaiMaks) nilai = nilaiMaks;

        output.value = nilai.toFixed(2);
        updateAverage();
    }

    function updateAverage() {
        const outputs = document.querySelectorAll('.nilai-output');
        let total = 0;
        let count = 0;

        outputs.forEach(el => {
            let valStr = el.value;
            if (valStr !== '') {
                // If it's a number (including 0 from absent), count it
                let val = parseFloat(valStr);
                if (!isNaN(val)) {
                    total += val;
                    count++;
                }
            }
        });

        const avg = count > 0 ? (total / count).toFixed(2) : 0;
        document.getElementById('rataRataDisplay').textContent = avg;
    }
</script>

<?php renderFooter(); ?>
