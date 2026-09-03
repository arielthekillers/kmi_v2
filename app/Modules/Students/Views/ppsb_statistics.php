<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 6mm 8mm;
    }
    body {
        background: white !important;
        color: #111827 !important;
        font-size: 11px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    main {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    /* Compact margins and grid gaps for single page fit */
    .grid {
        gap: 0.5rem !important;
    }
    .mb-6 {
        margin-bottom: 0.45rem !important;
    }
    .p-6 {
        padding: 0.45rem 0.65rem !important;
    }
    .px-6 {
        padding-left: 0.65rem !important;
        padding-right: 0.65rem !important;
    }
    .py-4 {
        padding-top: 0.3rem !important;
        padding-bottom: 0.3rem !important;
    }
    .py-8 {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    .p-4 {
        padding: 0.3rem 0.5rem !important;
    }
    .space-y-5 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0.3rem !important;
    }
    .mb-4 {
        margin-bottom: 0.3rem !important;
    }
    .mb-3 {
        margin-bottom: 0.2rem !important;
    }
    /* Typography scaling */
    .text-4xl {
        font-size: 1.4rem !important;
        line-height: 1.6rem !important;
        margin-bottom: 0.1rem !important;
    }
    .text-3xl {
        font-size: 1.2rem !important;
        line-height: 1.4rem !important;
    }
    .text-2xl {
        font-size: 1.1rem !important;
        line-height: 1.3rem !important;
    }
    .w-16.h-16 {
        width: 2.25rem !important;
        height: 2.25rem !important;
        font-size: 1.1rem !important;
        border-radius: 0.5rem !important;
    }
    .rounded-2xl {
        border-radius: 0.5rem !important;
    }
    .rounded-xl {
        border-radius: 0.375rem !important;
    }
    /* Force multi-column grids in print mode */
    .overview-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
    .two-col-grid {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
    .domisili-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
    .shadow-lg, .shadow-sm, .shadow-inner {
        box-shadow: none !important;
    }
    .ri-group-line.text-9xl {
        display: none !important;
    }
}
</style>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="<?= url('/admin/ppsb') ?>" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-indigo-600 transition-colors no-print">
                <i class="ri-arrow-left-line text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= $title ?></h1>
                <p class="text-sm text-gray-500">Ringkasan dan analitik data pendaftar santri baru.</p>
            </div>
        </div>
        <div class="no-print">
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 shadow-sm transition-all flex items-center">
                <i class="ri-printer-line mr-2"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 overview-grid">
        <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
            <div class="absolute right-0 top-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
                <i class="ri-group-line text-9xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-indigo-100 text-sm font-bold uppercase tracking-wider mb-1">Total Pendaftar</p>
                <h3 class="text-4xl font-black mb-2"><?= number_format($stats['total']) ?> <span class="text-lg font-medium opacity-75">Santri</span></h3>
                <p class="text-indigo-100 text-sm">Telah masuk dalam database PPSB.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-3xl shadow-inner">
                <i class="ri-men-line"></i>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Pendaftar Putra (L)</p>
                <h3 class="text-3xl font-black text-gray-900"><?= number_format($stats['laki_laki']) ?></h3>
                <p class="text-gray-500 text-sm font-medium mt-1">
                    <?= $stats['total'] > 0 ? round(($stats['laki_laki'] / $stats['total']) * 100) : 0 ?>% dari total
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-pink-50 text-pink-500 flex items-center justify-center text-3xl shadow-inner">
                <i class="ri-women-line"></i>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Pendaftar Putri (P)</p>
                <h3 class="text-3xl font-black text-gray-900"><?= number_format($stats['perempuan']) ?></h3>
                <p class="text-gray-500 text-sm font-medium mt-1">
                    <?= $stats['total'] > 0 ? round(($stats['perempuan'] / $stats['total']) * 100) : 0 ?>% dari total
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 two-col-grid">
        <!-- Demografi Usia -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-gray-800 font-bold flex items-center gap-2">
                    <i class="ri-calendar-event-line text-indigo-500"></i> Demografi Usia
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100">
                        <h4 class="text-blue-800 font-bold text-sm mb-4 border-b border-blue-200/50 pb-2">Santri Putra</h4>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm text-gray-600 font-medium">Usia Termuda</span>
                            <span class="font-bold text-lg text-blue-700"><?= $stats['usia_termuda_l'] ?? '-' ?> <span class="text-xs font-normal">thn</span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 font-medium">Usia Tertua</span>
                            <span class="font-bold text-lg text-blue-700"><?= $stats['usia_tertua_l'] ?? '-' ?> <span class="text-xs font-normal">thn</span></span>
                        </div>
                    </div>
                    <div class="bg-pink-50/50 rounded-xl p-4 border border-pink-100">
                        <h4 class="text-pink-800 font-bold text-sm mb-4 border-b border-pink-200/50 pb-2">Santri Putri</h4>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm text-gray-600 font-medium">Usia Termuda</span>
                            <span class="font-bold text-lg text-pink-700"><?= $stats['usia_termuda_p'] ?? '-' ?> <span class="text-xs font-normal">thn</span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 font-medium">Usia Tertua</span>
                            <span class="font-bold text-lg text-pink-700"><?= $stats['usia_tertua_p'] ?? '-' ?> <span class="text-xs font-normal">thn</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progres Kelengkapan Data -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-gray-800 font-bold flex items-center gap-2">
                    <i class="ri-file-list-3-line text-emerald-500"></i> Progres Kelengkapan Data
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-5">
                    <?php 
                    $categories = [
                        ['label' => 'Sangat Lengkap (76% - 100%)', 'val' => $stats['completeness_100'], 'color' => 'bg-emerald-500', 'bg' => 'bg-emerald-50'],
                        ['label' => 'Cukup Lengkap (51% - 75%)', 'val' => $stats['completeness_75'], 'color' => 'bg-blue-500', 'bg' => 'bg-blue-50'],
                        ['label' => 'Kurang Lengkap (26% - 50%)', 'val' => $stats['completeness_50'], 'color' => 'bg-yellow-400', 'bg' => 'bg-yellow-50'],
                        ['label' => 'Sangat Kurang (0% - 25%)', 'val' => $stats['completeness_25'], 'color' => 'bg-red-500', 'bg' => 'bg-red-50'],
                    ];
                    foreach ($categories as $cat): 
                        $pct = $stats['total'] > 0 ? round(($cat['val'] / $stats['total']) * 100) : 0;
                    ?>
                    <div>
                        <div class="flex justify-between items-end mb-1.5">
                            <span class="text-sm font-bold text-gray-700"><?= $cat['label'] ?></span>
                            <span class="text-sm font-bold text-gray-900"><?= number_format($cat['val']) ?> <span class="text-xs text-gray-400 font-normal">santri (<?= $pct ?>%)</span></span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                            <div class="<?= $cat['color'] ?> h-2.5 rounded-full" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Domisili Terbanyak -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-gray-800 font-bold flex items-center gap-2">
                <i class="ri-map-pin-line text-red-500"></i> Domisili Kabupaten / Kota Terbanyak
            </h3>
        </div>
        <div class="p-6">
            <?php if (empty($stats['kabupaten_terbanyak'])): ?>
                <div class="text-center text-gray-400 py-8 italic text-sm">Belum ada data domisili pendaftar.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 domisili-grid">
                    <?php 
                    $maxKab = max(array_values($stats['kabupaten_terbanyak']));
                    foreach ($stats['kabupaten_terbanyak'] as $kab => $count): 
                        $pct = round(($count / $stats['total']) * 100);
                        $width = round(($count / $maxKab) * 100);
                    ?>
                    <div class="p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors relative overflow-hidden group">
                        <div class="absolute bottom-0 left-0 h-1 bg-indigo-500 opacity-20 group-hover:opacity-100 transition-opacity" style="width: <?= $width ?>%"></div>
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-gray-800 text-sm truncate pr-2" title="<?= htmlspecialchars($kab) ?>"><?= htmlspecialchars($kab) ?></h4>
                            <span class="bg-indigo-50 text-indigo-700 text-[10px] px-2 py-0.5 rounded font-bold"><?= $pct ?>%</span>
                        </div>
                        <div class="text-2xl font-black text-indigo-600">
                            <?= number_format($count) ?> <span class="text-xs text-gray-400 font-medium">Santri</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
