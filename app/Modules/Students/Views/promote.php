<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h5 class="text-lg font-bold text-gray-800 flex items-center">
                <i class="ri-git-branch-line mr-2 text-indigo-600 border border-transparent"></i>
                Mutasi Kelas Santri
            </h5>
            <a href="<?= url('/students') ?>" class="text-sm font-medium text-gray-500 hover:text-gray-700">Kembali</a>
        </div>
        <div class="p-6">
            <div class="flex flex-col lg:flex-row gap-6 mb-8">
                <!-- Source Data Card -->
                <div class="w-full lg:w-1/3 bg-white p-5 rounded-2xl shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <label class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <i class="ri-login-circle-line"></i>
                            </div>
                            Sumber Data
                        </label>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md">
                            TA: <?= htmlspecialchars($currentYear['name'] ?? 'None') ?>
                        </span>
                    </div>
                    <form action="" method="GET">
                        <select name="kelas_id" class="w-full px-4 py-3 text-sm font-bold text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all cursor-pointer hover:border-indigo-300" onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas Asal --</option>
                            <?php foreach ($kelas as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($sourceKelasId ?? '') == $k['id'] ? 'selected' : '' ?>>
                                    Kelas <?= htmlspecialchars($k['tingkat'] . '-' . $k['abjad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- Mutation Configuration Card -->
                <div class="flex-1 bg-white p-5 rounded-2xl shadow-sm border border-gray-200 relative">
                    <?php if (!($sourceKelasId ?? false)): ?>
                        <!-- Overlay when no source class is selected -->
                        <div class="absolute inset-0 bg-white/70 backdrop-blur-[2px] z-10 flex items-center justify-center rounded-2xl">
                            <span class="text-sm font-bold text-gray-500 flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-200">
                                <i class="ri-lock-line"></i> Pilih kelas asal terlebih dahulu
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="ri-settings-4-line"></i>
                        </div>
                        <span class="text-sm font-bold text-gray-900">Pengaturan Mutasi</span>
                    </div>

                    <form action="<?= url('/students/promote/store') ?>" method="POST" id="mutasiForm" onsubmit="return handleMutationSubmit(event)">
                        <?= csrf_input() ?>
                        <input type="hidden" name="source_kelas_id" value="<?= htmlspecialchars($sourceKelasId ?? '') ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Jenis Mutasi (Custom Radios) -->
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Jenis Mutasi</label>
                                <div class="flex flex-col gap-2">
                                    <?php 
                                    $isSourceKelas6 = false;
                                    $sourceTingkat = 0;
                                    if ($sourceKelasId ?? false) {
                                        foreach ($kelas as $k) {
                                            if ($k['id'] == $sourceKelasId) {
                                                $sourceTingkat = (int)$k['tingkat'];
                                                if ($sourceTingkat === 6) $isSourceKelas6 = true;
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    
                                    <?php if (!$isSourceKelas6): ?>
                                    <label class="relative flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 group">
                                        <input type="radio" name="mutation_type" value="kenaikan" checked class="peer sr-only" onchange="updateMutationUI()">
                                        <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-indigo-500 peer-checked:border-4 transition-all"></div>
                                        <span class="text-sm font-bold text-gray-700 peer-checked:text-indigo-900 transition-colors">Kenaikan Kelas</span>
                                    </label>
                                    <?php else: ?>
                                    <label class="relative flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors has-[:checked]:border-green-500 has-[:checked]:bg-green-50/50 group">
                                        <input type="radio" name="mutation_type" value="kelulusan" checked class="peer sr-only" onchange="updateMutationUI()">
                                        <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-green-500 peer-checked:border-4 transition-all"></div>
                                        <span class="text-sm font-bold text-gray-700 peer-checked:text-green-900 transition-colors">Kelulusan</span>
                                    </label>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Target Configuration -->
                            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4" id="targetContainer">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Tahun Ajaran Tujuan</label>
                                    <select name="target_year_id" id="targetYear" class="w-full px-4 py-3 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all cursor-pointer hover:border-indigo-300" onchange="fetchKelasTujuan(this.value)" required>
                                        <option value="">-- Pilih Tahun Ajaran --</option>
                                        <?php foreach ($allYears as $y): ?>
                                            <option value="<?= $y['id'] ?>" data-is-current="<?= $y['id'] == $currentYear['id'] ? '1' : '0' ?>">
                                                <?= htmlspecialchars($y['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="targetKelasContainer">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Kelas Tujuan</label>
                                    <select name="target_kelas_id" id="targetKelas" class="w-full px-4 py-3 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all cursor-pointer hover:border-indigo-300" required>
                                        <option value="">-- Pilih Kelas --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <!-- Form tag remains open here for the table below -->
                </div>
            </div>

            <?php if ($sourceKelasId ?? false): ?>

                    <!-- Mobile Select All -->
                    <?php if (!empty($students)): ?>
                    <div class="md:hidden flex items-center justify-between p-4 bg-gray-800 text-white rounded-t-xl">
                        <span class="text-xs font-bold uppercase tracking-wider">Pilih Semua Santri</span>
                        <input type="checkbox" id="checkAllMobile" class="rounded text-indigo-500 focus:ring-indigo-500 w-5 h-5" checked>
                    </div>
                    <?php endif; ?>

                    <div class="border border-gray-200 rounded-xl md:rounded-t-xl rounded-t-none overflow-hidden">
                        <table class="w-full text-left border-collapse block md:table">
                            <thead class="bg-gray-800 text-white hidden md:table-header-group">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider w-10">
                                        <input type="checkbox" id="checkAll" class="rounded text-indigo-600 focus:ring-indigo-500" checked>
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider">NIS / NISN</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider">Nama Santri</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-center">Kelas</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 block md:table-row-group">
                                <?php if (empty($students)): ?>
                                    <tr class="block md:table-row">
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic bg-gray-50 block md:table-cell">Tidak ada data santri di kelas ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $s): ?>
                                        <tr class="flex flex-wrap md:table-row border-b md:border-b-0 border-gray-100 hover:bg-gray-50 transition-colors <?= $s['is_promoted'] ? 'opacity-70 bg-gray-50' : '' ?> p-2.5 md:p-0 items-center">
                                            
                                            <!-- 1. Checkbox -->
                                            <td class="w-8 md:w-10 flex-shrink-0 md:px-6 md:py-4 flex justify-center md:table-cell">
                                                <?php if ($s['is_promoted']): ?>
                                                    <i class="ri-check-double-line text-green-500 text-lg md:text-lg" title="Sudah diproses"></i>
                                                <?php else: ?>
                                                    <input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>" class="student-check rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4" checked>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- 2. NIS (Desktop) / Second Row (Mobile) -->
                                            <td class="w-full md:w-auto order-last md:order-none pl-8 md:pl-0 md:px-6 md:py-4 mt-1.5 md:mt-0 flex items-center gap-2 md:table-cell">
                                                <div class="text-[11px] md:text-sm font-bold text-gray-500 md:text-gray-900 font-mono md:font-sans leading-none"><?= htmlspecialchars($s['nis'] ?: '-') ?></div>
                                                <div class="hidden md:block text-[11px] text-gray-400 font-mono mt-0.5"><?= htmlspecialchars($s['nisn'] ?: '-') ?></div>
                                                
                                                <!-- Mobile only inline Kelas -->
                                                <span class="md:hidden text-gray-300 text-[10px]">&bull;</span>
                                                <span class="md:hidden inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold bg-indigo-50 text-indigo-700 uppercase tracking-wider leading-none">
                                                    <?= htmlspecialchars(($s['tingkat'] ?? '') . ' ' . ($s['abjad'] ?? '')) ?>
                                                </span>
                                            </td>

                                            <!-- 3. Avatar & Name -->
                                            <td class="flex-1 min-w-0 md:px-6 md:py-4 flex items-center gap-2.5 md:table-cell">
                                                <div class="flex items-center gap-2.5 w-full">
                                                    <div class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-indigo-100 text-indigo-700 flex flex-shrink-0 items-center justify-center font-bold text-xs md:text-sm">
                                                        <?= mb_strtoupper(mb_substr($s['nama'], 0, 1)) ?>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="student-name text-[13px] md:text-sm font-bold text-gray-900 truncate leading-tight"><?= htmlspecialchars($s['nama']) ?></div>
                                                        <div class="text-[9px] md:text-[10px] font-bold text-indigo-500 uppercase tracking-wider mt-0.5 leading-none">
                                                            <?= $s['gender'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- 4. Kelas (Desktop only) -->
                                            <td class="hidden md:table-cell px-6 py-4 text-center">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 uppercase tracking-wider whitespace-nowrap">
                                                    <?= htmlspecialchars(($s['tingkat'] ?? '') . ' ' . ($s['abjad'] ?? '')) ?>
                                                </span>
                                            </td>

                                            <!-- 5. Keterangan -->
                                            <td class="w-auto flex-shrink-0 md:px-6 md:py-4 text-right md:text-left">
                                                <?php if ($s['is_promoted']): ?>
                                                    <span class="md:hidden inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-800 uppercase leading-none">Naik</span>
                                                    <span class="hidden md:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-800 border border-green-200 uppercase">
                                                        Sudah Naik Ke <?= htmlspecialchars($s['promoted_to']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="md:hidden text-[9px] text-gray-400 italic pr-1">Belum</span>
                                                    <span class="hidden md:inline text-gray-400 text-xs italic">Belum Diproses</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-8 flex justify-end">
                        <button type="submit" id="btnSubmit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 transition-all active:scale-95">
                            <i class="ri-rocket-line mr-2"></i> Proses Mutasi
                        </button>
                    </div>
                </form>
            <?php else: ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeConfirmModal()"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-50 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="ri-question-line text-indigo-600 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-base font-bold leading-6 text-gray-900">Konfirmasi Mutasi Kelas</h3>
                            <div class="mt-4">
                                <div class="mb-4 text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <div class="grid grid-cols-3 gap-2 text-xs">
                                        <div class="font-semibold text-gray-500">Dari Kelas</div>
                                        <div class="col-span-2 font-bold text-gray-800" id="confirm-source-class"></div>
                                        
                                        <div class="font-semibold text-gray-500">Tujuan</div>
                                        <div class="col-span-2 font-bold text-indigo-600" id="confirm-target-class"></div>
                                    </div>
                                </div>
                                
                                <div class="border border-gray-150 rounded-xl overflow-hidden bg-gray-50/50">
                                    <div class="px-4 py-2 border-b border-gray-150 bg-gray-50 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                        Daftar Santri Terpilih (<span id="confirm-count-total">0</span>)
                                    </div>
                                    <div id="confirm-list" class="max-h-48 overflow-y-auto px-4 py-2 text-xs divide-y divide-gray-100 font-medium text-gray-700">
                                        <!-- Dynamic list -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-2xl border-t border-gray-100">
                    <button type="button" onclick="submitMutationForm()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white focus:outline-none transition-colors cursor-pointer">
                        Ya, Proses Mutasi
                    </button>
                    <button type="button" onclick="closeConfirmModal()" class="w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors cursor-pointer">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.student-check').forEach(cb => cb.checked = this.checked);
    const mobileCheck = document.getElementById('checkAllMobile');
    if (mobileCheck) mobileCheck.checked = this.checked;
});

document.getElementById('checkAllMobile')?.addEventListener('change', function() {
    document.querySelectorAll('.student-check').forEach(cb => cb.checked = this.checked);
    const desktopCheck = document.getElementById('checkAll');
    if (desktopCheck) desktopCheck.checked = this.checked;
});

const targetYearSelect = document.getElementById('targetYear');
const targetKelasSelect = document.getElementById('targetKelas');
const targetContainer = document.getElementById('targetContainer');
const btnSubmit = document.getElementById('btnSubmit');

async function fetchKelasTujuan(yearId) {
    if (!yearId) {
        targetKelasSelect.innerHTML = '<option value="">-- Pilih Kelas Tujuan --</option>';
        return;
    }
    
    try {
        const response = await fetch(`<?= url('/api/kelas?year_id=') ?>${yearId}`);
        const data = await response.json();
        const sourceTingkat = <?= isset($sourceTingkat) ? $sourceTingkat : 0 ?>;
        
        let html = '<option value="">-- Pilih Kelas Tujuan --</option>';
        data.forEach(k => {
            if (parseInt(k.tingkat) > sourceTingkat) {
                html += `<option value="${k.id}">Kelas ${k.tingkat}-${k.abjad}</option>`;
            }
        });
        targetKelasSelect.innerHTML = html;
    } catch (e) {
        console.error("Gagal mengambil data kelas", e);
        targetKelasSelect.innerHTML = '<option value="">-- Gagal memuat kelas --</option>';
    }
}

function updateMutationUI() {
    if (!targetYearSelect) return;
    
    const type = document.querySelector('input[name="mutation_type"]:checked').value;
    
    // Reset options visibility
    Array.from(targetYearSelect.options).forEach(opt => {
        if (opt.value === "") return;
        opt.style.display = 'block';
        opt.disabled = false;
    });

    if (type === 'kenaikan') {
        targetContainer.style.display = 'grid';
        targetYearSelect.required = true;
        targetKelasSelect.required = true;
        
        // Hide current year, user must select a different year (presumably the next one)
        Array.from(targetYearSelect.options).forEach(opt => {
            if (opt.getAttribute('data-is-current') === '1') {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        });
        
        targetYearSelect.value = "";
        targetKelasSelect.innerHTML = '<option value="">-- Pilih Kelas Tujuan --</option>';
        btnSubmit.innerHTML = '<i class="ri-rocket-line mr-2"></i> Proses Kenaikan Kelas';
    } 
    else if (type === 'kelulusan') {
        targetContainer.style.display = 'none';
        targetYearSelect.required = false;
        targetKelasSelect.required = false;
        btnSubmit.innerHTML = '<i class="ri-graduation-cap-line mr-2"></i> Proses Kelulusan';
    }
}

let isConfirmed = false;

function handleMutationSubmit(e) {
    if (isConfirmed) return true;
    e.preventDefault();
    
    const checkedBoxes = document.querySelectorAll('.student-check:checked');
    if (checkedBoxes.length === 0) {
        alert('Pilih minimal satu santri untuk diproses.');
        return false;
    }
    
    const sourceSelect = document.querySelector('select[name="kelas_id"]');
    const sourceText = sourceSelect.options[sourceSelect.selectedIndex].text.trim();
    
    const checkedRadio = document.querySelector('input[name="mutation_type"]:checked');
    const type = checkedRadio ? checkedRadio.value : 'kenaikan';
    
    let targetText = '';
    if (type === 'kelulusan') {
        targetText = 'Lulus / Alumni';
    } else {
        if (!targetYearSelect.value || !targetKelasSelect.value) {
            alert('Tahun ajaran dan kelas tujuan harus dipilih.');
            return false;
        }
        const yearText = targetYearSelect.options[targetYearSelect.selectedIndex].text.trim();
        const kelasText = targetKelasSelect.options[targetKelasSelect.selectedIndex].text.trim();
        targetText = `Naik ke ${kelasText} (Tahun ${yearText})`;
    }
    
    document.getElementById('confirm-source-class').textContent = sourceText;
    document.getElementById('confirm-target-class').textContent = targetText;
    document.getElementById('confirm-count-total').textContent = checkedBoxes.length;
    
    const listContainer = document.getElementById('confirm-list');
    listContainer.innerHTML = '';
    
    checkedBoxes.forEach(cb => {
        const tr = cb.closest('tr');
        const name = tr.querySelector('.student-name').textContent.trim();
        // Extract NIS from the second column
        let nis = tr.querySelector('td:nth-child(2) div:first-child');
        nis = nis ? nis.textContent.trim() : '-';
        
        const div = document.createElement('div');
        div.className = 'py-2 flex justify-between items-center';
        div.innerHTML = `
            <span class="font-bold text-gray-800">${name}</span>
            <span class="text-[10px] text-gray-400 font-mono">${nis}</span>
        `;
        listContainer.appendChild(div);
    });
    
    document.getElementById('confirm-modal').classList.remove('hidden');
    return false;
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
}

function submitMutationForm() {
    isConfirmed = true;
    document.getElementById('mutasiForm').submit();
}

if (document.querySelector('input[name="mutation_type"]:checked') && targetYearSelect) {
    updateMutationUI();
}
</script>
