<?php renderHeader($title); ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="<?= url('/') ?>" class="hover:text-indigo-600">
                <span class="hidden sm:inline">Dashboard</span>
                <i class="ri-home-4-line sm:hidden text-base"></i>
            </a>
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">Settings</span>
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">TV Showcase</span>
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-indigo-600 font-semibold">Mode Tampilan</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                 <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            TV Showcase Settings
        </h2>
        <p class="text-gray-500 text-sm mt-1">Konfigurasi tata letak (layout) layar TV Showcase.</p>
    </div>

    <!-- Sidebar tabs + content layout -->
    <div class="flex flex-col md:flex-row gap-6">

        <!-- Sidebar nav -->
        <?php $active_settings_tab = 'tv_layout'; ?>
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <!-- Main content -->
        <div class="flex-1 space-y-6">

            <!-- ── TV Showcase Mode ────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Mode Tampilan TV</h3>
                        <p class="text-xs text-gray-500">Pilih tata letak (layout) aktif untuk layar TV Showcase.</p>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <form action="<?= url('/settings/tv/update-mode') ?>" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-4">
                        <?= csrf_token_field() ?>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">Pilih Mode Tampilan Aktif</label>
                            <select name="tv_showcase_mode" class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border bg-white">
                                <option value="normal" <?= ($showcaseMode ?? 'normal') === 'normal' ? 'selected' : '' ?>>Normal (Kegiatan Belajar Mengajar)</option>
                                <option value="exam" <?= ($showcaseMode ?? 'normal') === 'exam' ? 'selected' : '' ?>>Masa Ujian (Informasi Panitia & Koreksi)</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all whitespace-nowrap">
                                Simpan Mode Tampilan
                            </button>
                        </div>
                    </form>
                </div>
            </div>



        </div><!-- /main content -->
    </div><!-- /layout -->

</main>

<?php renderFooter(); ?>
