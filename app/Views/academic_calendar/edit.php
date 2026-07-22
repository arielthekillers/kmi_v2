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
</script>
