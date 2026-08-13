<?php
$kategoriConfig = [
    'Akademik' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'dot' => 'bg-blue-500',   'border' => 'border-blue-200'],
    'Ujian'    => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'dot' => 'bg-red-500',    'border' => 'border-red-200'],
    'Kegiatan' => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'dot' => 'bg-green-500',  'border' => 'border-green-200'],
    'Libur'    => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500', 'border' => 'border-yellow-200'],
    'Lainnya'  => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'dot' => 'bg-gray-400',   'border' => 'border-gray-200'],
];
?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('/academic-calendar') ?>"
            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
            <i class="ri-arrow-left-line text-lg"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-edit-2-line text-indigo-600"></i>
                Edit Kegiatan Kalender
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">Perbarui informasi kegiatan akademik</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form method="POST" action="<?= url('/academic-calendar/update') ?>" class="px-6 py-6 space-y-5">
            <?= csrf_token_field() ?>
            <input type="hidden" name="id" value="<?= $event['id'] ?>">

            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama / Keterangan Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="keterangan" required
                    value="<?= htmlspecialchars($event['keterangan']) ?>"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
            </div>

            <!-- Tanggal -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" id="edit-tanggal-mulai" required
                        value="<?= htmlspecialchars($event['tanggal_mulai']) ?>"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-gray-400 text-xs">(opsional)</span></label>
                    <input type="date" name="tanggal_selesai" id="edit-tanggal-selesai"
                        value="<?= htmlspecialchars($event['tanggal_selesai'] ?? '') ?>"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
                </div>
            </div>

            <!-- Kategori -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($kategoriConfig as $kat => $cfg): ?>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="kategori" value="<?= $kat ?>"
                                <?= $event['kategori'] === $kat ? 'checked' : '' ?>
                                class="hidden kategori-radio-edit">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border-2 cursor-pointer transition-all
                                kategori-option-edit <?= $cfg['bg'] ?> <?= $cfg['text'] ?> border-transparent hover:border-current"
                                style="<?= $event['kategori'] === $kat ? 'border-color:currentColor' : '' ?>">
                                <span class="w-2 h-2 rounded-full <?= $cfg['dot'] ?>"></span>
                                <?= $kat ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Override KBM Toggle -->
            <?php
            $isOverride = !empty($existingOverride);
            $isFullDay = $isOverride ? (bool)$existingOverride['is_full_day'] : true;
            
            // Because we don't save "angkatan" or "sekolah" explicitly in DB (we expand them),
            // the safest way to edit is to just show them as pre-selected specific classes.
            ?>
            <div class="border-t border-gray-100 pt-4 mt-4">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" name="is_override" id="is_override_toggle" class="sr-only" <?= $isOverride ? 'checked' : '' ?>>
                        <div class="w-10 h-6 rounded-full shadow-inner transition-colors toggle-bg <?= $isOverride ? 'bg-indigo-600 group-hover:bg-indigo-700' : 'bg-gray-200 group-hover:bg-gray-300' ?>"></div>
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform toggle-dot <?= $isOverride ? 'translate-x-4' : '' ?>"></div>
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-gray-800">Terapkan sebagai Override KBM</span>
                        <p class="text-[11px] text-gray-500">Jika aktif, kegiatan ini akan menimpa jadwal mengajar rutin.</p>
                    </div>
                </label>
            </div>

            <!-- Override Options (Hidden by default) -->
            <div id="override_options" class="<?= $isOverride ? '' : 'hidden' ?> flex flex-col gap-4 mt-3 pt-3 border-t border-gray-100">
                
                <!-- Waktu Kegiatan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="ri-time-line text-indigo-500 mr-1"></i> Waktu
                    </label>
                    <label class="flex items-center text-sm font-normal cursor-pointer mb-2">
                        <input type="checkbox" name="is_full_day" id="is_full_day" value="1" class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4" <?= $isFullDay ? 'checked' : '' ?>>
                        <span class="ml-2 text-gray-700">Full Day (Seharian)</span>
                    </label>
                    
                    <?php
                        $hourStart = $existingOverride['hour_start'] ?? '';
                        $hourEnd = $existingOverride['hour_end'] ?? '';
                    ?>
                    <div id="jam_ke_container" class="<?= $isFullDay ? 'hidden' : '' ?> grid grid-cols-2 gap-2 mt-1">
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-0.5">Dari Jam Ke-</label>
                            <input type="number" name="hour_start" min="1" max="10" placeholder="1" value="<?= $hourStart ?>"
                                class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-0.5">Sampai Jam Ke-</label>
                            <input type="number" name="hour_end" min="1" max="10" placeholder="7" value="<?= $hourEnd ?>"
                                class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Target Kegiatan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="ri-group-line text-indigo-500 mr-1"></i> Target
                    </label>
                    
                    <select name="target_type" id="target_type" class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 outline-none bg-white mb-2">
                        <option value="kelas" selected>Pilih Kelas Manual</option>
                        <option value="sekolah">Seluruh Santri</option>
                        <option value="angkatan">Per Angkatan</option>
                    </select>

                    <div id="target_angkatan_container" class="hidden">
                        <select name="target_tingkat" class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 outline-none bg-white">
                            <?php 
                            $uniqueTingkat = [];
                            foreach ($allKelas as $k) {
                                if (!in_array($k['tingkat'], $uniqueTingkat)) {
                                    $uniqueTingkat[] = $k['tingkat'];
                                }
                            }
                            foreach ($uniqueTingkat as $t): ?>
                                <option value="<?= htmlspecialchars($t) ?>">Tingkat <?= htmlspecialchars($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="target_kelas_container" class="">
                        <select name="target_kelas[]" multiple class="tom-select w-full text-sm" placeholder="Ketik kelas...">
                            <?php 
                            $currentTingkat = null;
                            foreach ($allKelas as $k): 
                                $selected = in_array($k['id'], $existingTargets) ? 'selected' : '';
                                if ($currentTingkat !== $k['tingkat']):
                                    if ($currentTingkat !== null) echo '</optgroup>';
                                    $currentTingkat = $k['tingkat'];
                                    echo '<optgroup label="Tingkat ' . $currentTingkat . '">';
                                endif;
                            ?>
                                <option value="<?= $k['id'] ?>" <?= $selected ?>>Kelas <?= htmlspecialchars($k['tingkat'] . $k['abjad']) ?></option>
                            <?php endforeach; 
                            if ($currentTingkat !== null) echo '</optgroup>';
                            ?>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Buttons -->
            <div class="flex justify-between items-center pt-2 border-t border-gray-50">
                <a href="<?= url('/academic-calendar') ?>"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">
                    <i class="ri-save-line mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.kategori-radio-edit').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.kategori-option-edit').forEach(function(opt) {
            opt.style.borderColor = '';
        });
        var label = radio.closest('label').querySelector('.kategori-option-edit');
        if (label) label.style.borderColor = 'currentColor';
    });
});

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

if (overrideToggle) {
    overrideToggle.addEventListener('change', function() {
        if (this.checked) {
            overrideOptions.classList.remove('hidden');
            toggleDot.classList.add('translate-x-4');
            toggleBg.classList.replace('bg-gray-200', 'bg-indigo-600');
            toggleBg.classList.replace('group-hover:bg-gray-300', 'group-hover:bg-indigo-700');
        } else {
            overrideOptions.classList.add('hidden');
            toggleDot.classList.remove('translate-x-4');
            toggleBg.classList.replace('bg-indigo-600', 'bg-gray-200');
            toggleBg.classList.replace('group-hover:bg-indigo-700', 'group-hover:bg-gray-300');
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
        } else {
            jamKeContainer.classList.remove('hidden');
        }
    });
}

// Target Type Selection Logic
const targetTypeSelect = document.getElementById('target_type');
const targetAngkatanContainer = document.getElementById('target_angkatan_container');
const targetKelasContainer = document.getElementById('target_kelas_container');

if (targetTypeSelect) {
    targetTypeSelect.addEventListener('change', function() {
        const val = this.value;
        if (val === 'sekolah') {
            targetAngkatanContainer.classList.add('hidden');
            targetKelasContainer.classList.add('hidden');
        } else if (val === 'angkatan') {
            targetAngkatanContainer.classList.remove('hidden');
            targetKelasContainer.classList.add('hidden');
        } else if (val === 'kelas') {
            targetAngkatanContainer.classList.add('hidden');
            targetKelasContainer.classList.remove('hidden');
        }
    });
}
</script>
<style>
    /* Fix z-index dropdown TomSelect di dalam edit form */
    .ts-dropdown {
        z-index: 2000 !important;
    }
</style>
