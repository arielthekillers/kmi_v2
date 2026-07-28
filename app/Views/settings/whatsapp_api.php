<?php renderHeader($title); ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="<?= url('/') ?>" class="hover:text-indigo-600">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-500 font-semibold">Settings</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-indigo-600 font-semibold">WhatsApp API</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                <i class="ri-whatsapp-line text-indigo-600 text-xl"></i>
            </div>
            WhatsApp API Settings
        </h2>
        <p class="text-gray-500 text-sm mt-1">Konfigurasi integrasi WhatsApp Device (Ruang WA).</p>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <aside class="w-full md:w-56 flex-shrink-0">
            <nav class="flex md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-2 md:pb-0">
                <a href="<?= url('/settings/general') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    General
                </a>
                
                <div class="mt-4 mb-2 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden md:block">Integration</div>
                <a href="<?= url('/settings/whatsapp') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg bg-indigo-50 text-indigo-700 font-semibold">
                    <i class="ri-whatsapp-line text-lg text-indigo-500"></i>
                    WhatsApp API
                </a>

                <div class="mt-4 mb-2 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden md:block">TV Showcase</div>
                <a href="<?= url('/settings/tv/layout') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
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

        <div class="flex-1 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="ri-whatsapp-line text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Ruang WA Device</h3>
                        <p class="text-xs text-gray-500">Konfigurasi token dari akun Ruang WA Anda.</p>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <form action="<?= url('/settings/whatsapp/update') ?>" method="POST" class="space-y-4">
                        <?= csrf_token_field() ?>
                        
                        <div>
                            <label for="device_key" class="block text-sm font-medium text-gray-700 mb-1">Device Key</label>
                            <input type="text" name="device_key" id="device_key" value="<?= htmlspecialchars($deviceKey ?? '') ?>" placeholder="Masukkan Device Key..." required class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                            <p class="text-xs text-gray-500 mt-1">Dapatkan dari dashboard Ruang WA.</p>
                        </div>

                        <div>
                            <label for="api_key" class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                            <input type="text" name="api_key" id="api_key" value="<?= htmlspecialchars($apiKey ?? '') ?>" placeholder="Masukkan API Key..." required class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all whitespace-nowrap">
                                Simpan Konfigurasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php renderFooter(); ?>
