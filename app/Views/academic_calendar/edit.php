<?php
$kategoriConfig = [
    'Akademik' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'dot' => 'bg-blue-500',   'border' => 'border-blue-200'],
    'Ujian'    => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'dot' => 'bg-red-500',    'border' => 'border-red-200'],
    'Kegiatan' => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'dot' => 'bg-green-500',  'border' => 'border-green-200'],
    'Libur'    => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200'],
    'Lainnya'  => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'dot' => 'bg-gray-400',   'border' => 'border-gray-200'],
];
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="<?= url('/academic-calendar') ?>"
                class="flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all shadow-sm">
                <i class="ri-arrow-left-line text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    <i class="ri-edit-2-fill text-indigo-500"></i>
                    Edit Kegiatan Kalender
                </h1>
                <p class="text-sm text-gray-500 mt-1">Sesuaikan informasi dan preferensi kegiatan akademik.</p>
            </div>
        </div>
        
        <!-- Top Actions -->
        <div>
            <button type="button" onclick="document.getElementById('edit-form').submit();"
                class="hidden sm:inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="ri-save-line text-lg"></i>
                Simpan Perubahan
            </button>
        </div>
    </div>

    <!-- Form Container -->
    <form id="edit-form" method="POST" action="<?= url('/academic-calendar/update') ?>">
        <?= csrf_token_field() ?>
        <input type="hidden" name="id" value="<?= $event['id'] ?>">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Basic Info -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm p-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                    <h3 class="text-base font-semibold text-gray-900 mb-5 flex items-center gap-2">
                        <i class="ri-information-line text-indigo-500"></i> Informasi Dasar
                    </h3>
                    
                    <div class="space-y-5">
                        <!-- Keterangan -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Nama Kegiatan <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="ri-text text-gray-400 text-sm"></i>
                                </div>
                                <input type="text" name="keterangan" required
                                    value="<?= htmlspecialchars($event['keterangan']) ?>"
                                    class="w-full pl-9 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all shadow-sm font-medium text-gray-800">
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Tanggal Mulai <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal_mulai" id="edit-tanggal-mulai" required
                                    value="<?= htmlspecialchars($event['tanggal_mulai']) ?>"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all shadow-sm font-medium text-gray-800">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Sampai <span class="text-gray-400 lowercase italic">(opsi)</span></label>
                                <input type="date" name="tanggal_selesai" id="edit-tanggal-selesai"
                                    value="<?= htmlspecialchars($event['tanggal_selesai'] ?? '') ?>"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all shadow-sm font-medium text-gray-800">
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">Kategori <span class="text-red-500">*</span></label>
                            <div class="flex flex-wrap gap-1">
                                <?php 
                                $kategoriClasses = [
                                    'Akademik' => 'peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:border-blue-300 peer-checked:ring-1 peer-checked:ring-blue-500/10',
                                    'Ujian'    => 'peer-checked:bg-red-50 peer-checked:text-red-700 peer-checked:border-red-300 peer-checked:ring-1 peer-checked:ring-red-500/10',
                                    'Kegiatan' => 'peer-checked:bg-green-50 peer-checked:text-green-700 peer-checked:border-green-300 peer-checked:ring-1 peer-checked:ring-green-500/10',
                                    'Libur'    => 'peer-checked:bg-yellow-50 peer-checked:text-yellow-700 peer-checked:border-yellow-300 peer-checked:ring-1 peer-checked:ring-yellow-500/10',
                                    'Lainnya'  => 'peer-checked:bg-gray-100 peer-checked:text-gray-700 peer-checked:border-gray-300 peer-checked:ring-1 peer-checked:ring-gray-500/10',
                                ];
                                foreach ($kategoriConfig as $kat => $cfg): 
                                    $activeClass = $kategoriClasses[$kat] ?? $kategoriClasses['Lainnya'];
                                ?>
                                    <label class="cursor-pointer select-none">
                                        <input type="radio" name="kategori" value="<?= $kat ?>"
                                            <?= $event['kategori'] === $kat ? 'checked' : '' ?>
                                            class="sr-only peer">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold border border-gray-200 text-gray-400 bg-white hover:bg-gray-50 hover:text-gray-500 transition-all duration-200 <?= $activeClass ?> shadow-sm">
                                            <span class="w-1 h-1 rounded-full bg-current opacity-70"></span>
                                            <?= $kat ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Override Settings -->
            <div class="lg:col-span-7 space-y-6">
                
                <?php
                $isOverride = !empty($existingOverride);
                $isFullDay = $isOverride ? (bool)$existingOverride['is_full_day'] : true;
                ?>
                
                <div class="bg-white rounded-2xl shadow-sm p-6 relative overflow-hidden transition-all duration-300" id="override_card">
                    <div class="absolute top-0 left-0 w-1 h-full transition-colors duration-300 <?= $isOverride ? 'bg-indigo-500' : 'bg-gray-200' ?>" id="override_indicator"></div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                <i class="ri-settings-4-line text-indigo-500"></i>
                                Override KBM
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Aktifkan untuk meliburkan KBM pada kelas atau jam tertentu.</p>
                        </div>
                        
                        <!-- Toggle Switch -->
                        <label class="flex items-center cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="is_override" id="is_override_toggle" class="sr-only" <?= $isOverride ? 'checked' : '' ?>>
                                <div class="w-12 h-7 rounded-full shadow-inner transition-colors duration-300 toggle-bg <?= $isOverride ? 'bg-indigo-600' : 'bg-gray-200 group-hover:bg-gray-300' ?>"></div>
                                <div class="absolute left-1 top-1 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300 toggle-dot <?= $isOverride ? 'translate-x-5' : '' ?>"></div>
                            </div>
                        </label>
                    </div>

                    <!-- Override Options Container -->
                    <div id="override_options" class="<?= $isOverride ? 'max-h-[2000px] mt-6' : 'max-h-0 overflow-hidden' ?> transition-all duration-500 ease-in-out">
                        <div class="space-y-6">
                            
                            <!-- Waktu Kegiatan -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-800 mb-2.5 flex items-center gap-2">
                                    <div class="w-5 h-5 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="ri-time-line text-xs"></i></div> 
                                    Durasi Waktu
                                </label>
                                
                                <div class="flex items-center gap-6">
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" name="is_full_day" id="is_full_day" value="1" class="sr-only peer" <?= $isFullDay ? 'checked' : '' ?>>
                                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-semibold border border-gray-200 rounded-xl text-gray-600 bg-white hover:bg-gray-50 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 transition-all duration-200 shadow-sm">
                                            <i class="ri-time-line text-sm"></i>
                                            Seharian (Full Day)
                                        </span>
                                    </label>
                                    
                                    <?php
                                        $hourStart = $hourStart ?? '';
                                        $hourEnd = $hourEnd ?? '';
                                    ?>
                                    <div id="jam_ke_container" class="<?= $isFullDay ? 'hidden' : 'flex' ?> items-center gap-3">
                                        <span class="text-sm text-gray-300">|</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">Jam ke-</span>
                                            <input type="number" name="hour_start" min="1" max="10" placeholder="1" value="<?= $hourStart ?>"
                                                class="w-12 border border-gray-200 rounded-lg px-1.5 py-1 text-center text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all shadow-sm">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">s/d</span>
                                            <input type="number" name="hour_end" min="1" max="10" placeholder="7" value="<?= $hourEnd ?>"
                                                class="w-12 border border-gray-200 rounded-lg px-1.5 py-1 text-center text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all shadow-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Target Kegiatan -->
                            <div class="pt-2">
                                <label class="block text-sm font-semibold text-gray-800 mb-2.5 flex items-center gap-2">
                                    <div class="w-5 h-5 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="ri-group-line text-xs"></i></div> 
                                    Target Kelas
                                </label>
                                
                                <div class="space-y-4">
                                    <!-- Check All -->
                                    <div class="pb-3 border-b border-gray-100/80">
                                        <label class="cursor-pointer select-none">
                                            <input type="checkbox" id="check_all_sekolah" class="sr-only peer">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold border border-dashed border-indigo-200 rounded-xl text-indigo-600 bg-indigo-50/30 hover:bg-indigo-50 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 transition-all duration-200 shadow-sm">
                                                <i class="ri-team-line"></i>
                                                Pilih Semua Santri (Seluruh Sekolah)
                                            </span>
                                        </label>
                                    </div>
                                    
                                    <div class="space-y-3.5">
                                        <?php 
                                        $kelasByTingkat = [];
                                        foreach ($allKelas as $k) {
                                            $isIntensif = (stripos($k['abjad'], 'Int') !== false);
                                            $groupKey = $k['tingkat'] . ($isIntensif ? ' Int' : '');
                                            $groupSlug = $k['tingkat'] . ($isIntensif ? '-int' : '');
                                            $k['group_slug'] = $groupSlug;
                                            $kelasByTingkat[$groupKey][] = $k;
                                        }
                                        ksort($kelasByTingkat, SORT_NATURAL);
                                        
                                        foreach ($kelasByTingkat as $groupName => $kelass): 
                                            $groupSlug = $kelass[0]['group_slug'];
                                        ?>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="w-16 sm:w-20 text-xs font-semibold text-gray-500">
                                                Kelas <?= htmlspecialchars($groupName) ?>
                                            </div>
                                            
                                            <!-- Check All Angkatan -->
                                            <?php if (count($kelass) > 1): ?>
                                                <label class="cursor-pointer select-none">
                                                    <input type="checkbox" class="sr-only peer check-angkatan" data-tingkat="<?= htmlspecialchars($groupSlug) ?>">
                                                    <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-semibold border border-dashed border-gray-300 rounded-lg text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:border-indigo-300 transition-all duration-200 text-center min-w-[36px]">
                                                        All
                                                    </span>
                                                </label>
                                            <?php endif; ?>
                                            
                                            <!-- Individual Classes -->
                                            <?php foreach ($kelass as $k): 
                                                $selected = in_array($k['id'], $existingTargets) ? 'checked' : '';
                                            ?>
                                                <label class="cursor-pointer select-none">
                                                    <input type="checkbox" name="target_kelas[]" value="<?= $k['id'] ?>" class="sr-only peer check-kelas tingkat-<?= htmlspecialchars($groupSlug) ?>" <?= $selected ?>>
                                                    <span class="inline-flex items-center justify-center px-3 py-1 text-xs font-medium border border-gray-200 rounded-lg text-gray-600 bg-white hover:border-indigo-400 hover:text-indigo-600 transition-all duration-150 min-w-[56px] text-center peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 shadow-sm">
                                                        <?= htmlspecialchars($k['tingkat'] . $k['abjad']) ?>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Submit Button -->
                <button type="submit" class="sm:hidden w-full flex justify-center items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-md transition-colors">
                    <i class="ri-save-line text-lg"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<script>


document.getElementById('edit-tanggal-mulai').addEventListener('change', function() {
    var selesai = document.getElementById('edit-tanggal-selesai');
    selesai.min = this.value;
    if (selesai.value && selesai.value < this.value) selesai.value = '';
});

// Override KBM Toggle Logic
const overrideToggle = document.getElementById('is_override_toggle');
const overrideOptions = document.getElementById('override_options');
const toggleDot = overrideToggle.parentNode.querySelector('.toggle-dot');
const toggleBg = overrideToggle.parentNode.querySelector('.toggle-bg');
const overrideCard = document.getElementById('override_card');
const overrideIndicator = document.getElementById('override_indicator');

if (overrideToggle) {
    overrideToggle.addEventListener('change', function() {
        if (this.checked) {
            overrideOptions.classList.remove('max-h-0', 'overflow-hidden');
            overrideOptions.classList.add('max-h-[2000px]', 'mt-6');
            toggleDot.classList.add('translate-x-5');
            toggleBg.classList.replace('bg-gray-200', 'bg-indigo-600');
            toggleBg.classList.replace('group-hover:bg-gray-300', 'group-hover:bg-indigo-700');
            overrideCard?.classList.add('ring-1', 'ring-indigo-500/20');
            overrideIndicator?.classList.replace('bg-gray-200', 'bg-indigo-500');
        } else {
            overrideOptions.classList.remove('max-h-[2000px]', 'mt-6');
            overrideOptions.classList.add('max-h-0', 'overflow-hidden');
            toggleDot.classList.remove('translate-x-5');
            toggleBg.classList.replace('bg-indigo-600', 'bg-gray-200');
            toggleBg.classList.replace('group-hover:bg-indigo-700', 'group-hover:bg-gray-300');
            overrideCard?.classList.remove('ring-1', 'ring-indigo-500/20');
            overrideIndicator?.classList.replace('bg-indigo-500', 'bg-gray-200');
        }
    });
}

// Full Day Toggle Logic
const isFullDayCheckbox = document.getElementById('is_full_day');
const jamKeContainer = document.getElementById('jam_ke_container');
if (isFullDayCheckbox) {
    isFullDayCheckbox.addEventListener('change', function() {
        if (this.checked) {
            jamKeContainer.classList.add('hidden');
            jamKeContainer.classList.remove('flex');
        } else {
            jamKeContainer.classList.remove('hidden');
            jamKeContainer.classList.add('flex');
        }
    });
}

// Target Checkbox Logic
const checkAllSekolah = document.getElementById('check_all_sekolah');
const checkAngkatanList = document.querySelectorAll('.check-angkatan');
const checkKelasList = document.querySelectorAll('.check-kelas');

function updateCheckboxesState() {
    let allChecked = true;
    checkKelasList.forEach(cb => {
        if (!cb.checked) allChecked = false;
    });
    if (checkAllSekolah) checkAllSekolah.checked = checkKelasList.length > 0 && allChecked;

    checkAngkatanList.forEach(angkatanCb => {
        const tingkat = angkatanCb.getAttribute('data-tingkat');
        const tingkatClasses = document.querySelectorAll('.check-kelas.tingkat-' + tingkat);
        let angkatanAllChecked = true;
        tingkatClasses.forEach(cb => {
            if (!cb.checked) angkatanAllChecked = false;
        });
        angkatanCb.checked = tingkatClasses.length > 0 && angkatanAllChecked;
    });
}

if (checkAllSekolah) {
    checkAllSekolah.addEventListener('change', function() {
        const isChecked = this.checked;
        checkKelasList.forEach(cb => cb.checked = isChecked);
        checkAngkatanList.forEach(cb => cb.checked = isChecked);
    });
}

checkAngkatanList.forEach(angkatanCb => {
    angkatanCb.addEventListener('change', function() {
        const isChecked = this.checked;
        const tingkat = this.getAttribute('data-tingkat');
        const tingkatClasses = document.querySelectorAll('.check-kelas.tingkat-' + tingkat);
        tingkatClasses.forEach(cb => cb.checked = isChecked);
        updateCheckboxesState();
    });
});

checkKelasList.forEach(cb => {
    cb.addEventListener('change', updateCheckboxesState);
});

// Run once on load to sync state
updateCheckboxesState();
</script>
<style>
    /* Fix z-index dropdown TomSelect di dalam edit form */
    .ts-dropdown {
        z-index: 2000 !important;
    }
</style>
