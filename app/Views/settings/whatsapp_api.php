<?php renderHeader($title); ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="<?= url('/') ?>" class="hover:text-indigo-600">
                <span class="hidden sm:inline">Dashboard</span>
                <i class="ri-home-4-line sm:hidden text-base"></i>
            </a>
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
        <?php $active_settings_tab = 'whatsapp'; ?>
        <?php include __DIR__ . '/_sidebar.php'; ?>

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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pengiriman</label>
                            <div class="flex items-center space-x-6 mt-2">
                                <label class="flex items-center">
                                    <input type="radio" name="send_method" value="direct" class="form-radio h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" <?= ($sendMethod ?? 'direct') === 'direct' ? 'checked' : '' ?>>
                                    <span class="ml-2 text-sm text-gray-900">Direct Send (Kirim Langsung)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="send_method" value="queue" class="form-radio h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" <?= ($sendMethod ?? '') === 'queue' ? 'checked' : '' ?>>
                                    <span class="ml-2 text-sm text-gray-900">Queue (Antrean)</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2"><b>Catatan:</b> Jika memilih opsi Queue, jangan lupa menghidupkan dan mematikan scheduling (cron) pada server.</p>
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
