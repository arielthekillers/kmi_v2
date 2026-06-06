<?php renderHeader($title); ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="<?= url('/') ?>" class="hover:text-indigo-600">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">Settings</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">TV Showcase</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
        <aside class="w-full md:w-56 flex-shrink-0">
            <nav class="flex md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-2 md:pb-0">
                <a href="<?= url('/settings/general') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    General
                </a>
                <div class="mt-4 mb-2 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden md:block">TV Showcase</div>
                <a href="<?= url('/settings/tv/layout') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg bg-indigo-50 text-indigo-700 font-semibold">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Mode Tampilan
                </a>
                <a href="<?= url('/settings/tv/bgm') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                    Background Music (BGM)
                </a>
                <a href="<?= url('/settings/tv/hours') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Jam Pelajaran
                </a>
                <a href="<?= url('/settings/tv/quotes') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Quotes
                </a>
            </nav>
        </aside>

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

            <!-- ── TV Showcase Link ────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 mb-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Link TV Showcase</h3>
                <div class="flex items-center gap-3">
                    <input type="text" readonly value="<?= url('/tvshowcase') ?>"
                           class="flex-1 text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-600 font-mono"
                           onclick="this.select()">
                    <a href="<?= url('/tvshowcase') ?>" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka
                    </a>
                </div>
            </div>

        </div><!-- /main content -->
    </div><!-- /layout -->

</main>

<?php renderFooter(); ?>
