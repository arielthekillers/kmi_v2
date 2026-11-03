<?php
renderHeader("Smart Tap — Terminal (Device)");

$totalDevices = count($devices);
$pairedDevices = 0;
$activeDevices = 0;

foreach ($devices as $d) {
    if (!empty($d['is_paired'])) $pairedDevices++;
    if (!empty($d['is_active'])) $activeDevices++;
}
$unpairedDevices = $totalDevices - $pairedDevices;
?>

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ri-checkbox-circle-fill text-xl text-emerald-600"></i>
                <span class="text-sm font-semibold"><?= $_SESSION['success'] ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ri-error-warning-fill text-xl text-rose-600"></i>
                <span class="text-sm font-semibold"><?= $_SESSION['error'] ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-lg leading-none">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Master Header Card -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Left Info -->
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl flex-shrink-0 shadow-xs">
                    <i class="ri-base-station-line"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Terminal (Device)</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
                        Kelola perangkat terminal presensi di berbagai titik (Masjid, Kantor, Gerbang, Aula) serta inisiasi sinkronisasi data offline terminal.
                    </p>
                </div>
            </div>

            <!-- Right Action Button -->
            <div class="flex items-center gap-3 self-start lg:self-center flex-wrap">
                <a href="<?= url('/smart-tap/download-client') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-indigo-50/50 text-gray-700 hover:text-indigo-700 text-sm font-semibold rounded-2xl border border-gray-200 hover:border-indigo-200 shadow-xs transition-all whitespace-nowrap" title="Unduh Paket Lengkap Aplikasi Terminal Presensi (.zip Siap Ekstrak)">
                    <i class="ri-download-cloud-2-line text-indigo-600 text-lg"></i>
                    <span>Unduh Kiosk Client (.zip)</span>
                </a>
                <button onclick="openDeviceModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold rounded-2xl shadow-sm shadow-indigo-200 transition-all hover:shadow-md whitespace-nowrap">
                    <i class="ri-add-line text-base"></i>
                    <span>Daftarkan Perangkat Baru</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Perangkat -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-400 block mb-1">Total Perangkat Terdaftar</span>
                <span class="text-2xl font-bold text-gray-900"><?= $totalDevices ?> Perangkat</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="ri-device-line"></i>
            </div>
        </div>

        <!-- Terpasang & Online -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-emerald-600 block mb-1">Perangkat Terpasang (2FA)</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-emerald-700"><?= $pairedDevices ?></span>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                        <?= $totalDevices > 0 ? round(($pairedDevices / $totalDevices) * 100) : 0 ?>% Terpasang
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="ri-shield-check-line"></i>
            </div>
        </div>

        <!-- Menunggu Pairing -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-amber-600 block mb-1">Menunggu Pairing 2FA</span>
                <span class="text-2xl font-bold text-amber-700"><?= $unpairedDevices ?> Perangkat</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="ri-shield-keyhole-line"></i>
            </div>
        </div>
    </div>

    <!-- Info Guide for Client Sync -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 rounded-3xl shadow-sm border border-indigo-900/40">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-1.5">
                <div class="text-[11px] font-bold text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="ri-terminal-box-line text-sm"></i>
                    <span>Integrasi Aplikasi Client (Python / IoT)</span>
                </div>
                <h3 class="text-base font-bold text-white">Sinkronisasi Database & Presensi Otomatis</h3>
                <p class="text-xs text-indigo-200/90 max-w-2xl leading-relaxed">
                    Setiap perangkat terminal terhubung secara aman menggunakan sistem inisiasi otomatis (2FA) agar dapat mengunduh master kartu dan menyinkronkan data presensi secara offline maupun online.
                </p>
            </div>

            <!-- Endpoint Box -->
            <div class="bg-white/10 backdrop-blur-sm p-3.5 rounded-2xl border border-white/10 flex flex-col sm:flex-row sm:items-center gap-3 flex-shrink-0">
                <div>
                    <span class="text-[10px] text-indigo-300 font-bold uppercase block">Endpoint Master Sync:</span>
                    <span class="font-mono text-xs text-emerald-300 font-bold select-all" id="endpoint-url"><?= url('/api/smart-tap/sync-master') ?></span>
                </div>
                <button type="button" onclick="copyEndpoint()" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 self-start sm:self-auto whitespace-nowrap">
                    <i class="ri-file-copy-line"></i> Salin
                </button>
            </div>
        </div>
    </div>

    <!-- Devices Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-device-line text-indigo-600"></i> Daftar Perangkat Presensi Terdaftar
            </h3>
            <span class="text-xs font-semibold text-gray-500">
                <?= $totalDevices ?> Perangkat Siap Digunakan
            </span>
        </div>

        <?php if (empty($devices)): ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm text-gray-400">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto text-3xl mb-3 shadow-inner">
                    <i class="ri-base-station-line"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">Belum Ada Perangkat yang Didaftarkan</h4>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto leading-relaxed">
                    Daftarkan perangkat terminal absensi baru untuk diletakkan di titik presensi masjid atau kantor.
                </p>
                <button type="button" onclick="openDeviceModal()" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                    <i class="ri-add-line"></i> Daftarkan Perangkat Sekarang
                </button>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($devices as $d): 
                    $isPaired = !empty($d['is_paired']);
                    $isActive = !empty($d['is_active']);
                ?>
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-xs hover:shadow-md transition flex flex-col justify-between relative group">
                        <div>
                            <!-- Header Pill Badges -->
                            <div class="flex items-center justify-between mb-3.5">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-mono font-bold bg-gray-100 text-gray-800 border border-gray-200/60">
                                    <i class="ri-cpu-line text-gray-500"></i>
                                    <?= htmlspecialchars($d['device_code']) ?>
                                </span>
                                
                                <div class="flex items-center gap-1.5">
                                    <?php if ($isPaired): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Terpasang
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Belum Dipairing
                                        </span>
                                    <?php endif; ?>

                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $isActive ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-500' ?>">
                                        <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Name & Location -->
                            <h4 class="text-lg font-bold text-gray-900 mb-1 leading-tight"><?= htmlspecialchars($d['device_name']) ?></h4>
                            <p class="text-xs text-gray-500 flex items-center gap-1.5 mb-4">
                                <i class="ri-map-pin-2-line text-indigo-500"></i>
                                <span><?= htmlspecialchars($d['location'] ?: 'Lokasi titik belum ditentukan') ?></span>
                            </p>

                            <!-- Technical Specs Box -->
                            <div class="bg-gray-50/80 rounded-2xl p-4 space-y-2.5 text-xs border border-gray-100 mb-5">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="flex items-center gap-1.5">
                                        <i class="ri-key-line text-gray-400"></i> PIN Otorisasi Kiosk:
                                    </span>
                                    <span class="font-mono font-bold text-gray-900 bg-white px-2 py-0.5 rounded-lg border border-gray-200">
                                        <?= htmlspecialchars($d['pin_code']) ?>
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="flex items-center gap-1.5">
                                        <i class="ri-time-line text-gray-400"></i> Terakhir Sinkronisasi:
                                    </span>
                                    <span class="font-medium text-gray-800">
                                        <?= $d['last_sync_at'] ? date('d M Y, H:i', strtotime($d['last_sync_at'])) : '<span class="text-gray-400 italic">Belum pernah sync</span>' ?>
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="flex items-center gap-1.5">
                                        <i class="ri-router-line text-gray-400"></i> IP Address Terakhir:
                                    </span>
                                    <span class="font-mono text-gray-700 font-semibold">
                                        <?= htmlspecialchars($d['last_ip'] ?: '-') ?>
                                    </span>
                                </div>
                                
                                <!-- Detail Teknis Token (Otomatis ditangani via Wizard 2FA) -->
                                <details class="group/token border-t border-gray-200/60 pt-2 text-[11px]">
                                    <summary class="cursor-pointer text-gray-400 hover:text-indigo-600 flex items-center justify-between font-semibold select-none py-1">
                                        <span class="flex items-center gap-1">
                                            <i class="ri-shield-keyhole-line text-xs text-indigo-500"></i> Token Autentikasi (Secret)
                                        </span>
                                        <span class="text-[10px] text-gray-400 group-open/token:rotate-180 transition-transform">▼</span>
                                    </summary>
                                    <div class="mt-2 space-y-1.5 bg-gray-50/80 p-2.5 rounded-xl border border-gray-200/60">
                                        <p class="text-[10px] text-gray-500 leading-tight">Token ini otomatis terpasang saat inisiasi 2FA. Salin manual hanya jika diperlukan.</p>
                                        <div class="flex items-center gap-1.5">
                                            <input type="password" value="<?= htmlspecialchars($d['device_token']) ?>" id="token-<?= $d['id'] ?>" readonly class="w-full text-[11px] font-mono bg-white border border-gray-200 rounded-lg py-1.5 px-2.5 text-gray-700 tracking-wider focus:outline-none">
                                            <button type="button" onclick="copyToken('token-<?= $d['id'] ?>')" class="p-1.5 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition border border-indigo-100 flex-shrink-0" title="Salin Token">
                                                <i class="ri-file-copy-line"></i>
                                            </button>
                                            <button type="button" onclick="toggleToken('token-<?= $d['id'] ?>', this)" class="p-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition border border-gray-200 flex-shrink-0" title="Lihat">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </details>
                            </div>

                            <!-- Inisiasi Perangkat Action -->
                            <div class="space-y-2 mb-4">
                                <button type="button" onclick="startDeviceSetup(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['device_name'])) ?>', '<?= htmlspecialchars(addslashes($d['device_code'])) ?>')" class="w-full py-2.5 px-4 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-2xl text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition hover:shadow-md">
                                    <i class="ri-shield-keyhole-line text-sm"></i>
                                    <span><?= $isPaired ? 'Inisiasi Ulang Perangkat' : 'Mulai Inisiasi Perangkat (Wizard)' ?></span>
                                </button>

                                <?php if ($isPaired): ?>
                                    <a href="<?= url('/smart-tap/devices/unpair?id=' . $d['id']) ?>" onclick="return confirm('Putus hubungan (Unpair) perangkat ini? Perangkat harus di-pairing ulang dengan wizard setup agar dapat sync kembali.')" class="text-rose-500 hover:text-rose-700 text-xs font-bold block text-center py-1 transition">
                                        <i class="ri-link-unlink-m"></i> Putus Hubungan Perangkat Ini
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Footer Actions -->
                        <div class="flex items-center justify-between pt-3.5 border-t border-gray-100 text-xs">
                            <a href="<?= url('/smart-tap/devices/token?id=' . $d['id']) ?>" onclick="return confirm('Regenerate token akan memutuskan koneksi perangkat sampai token baru dimasukkan. Lanjutkan?')" class="text-amber-600 hover:text-amber-800 font-bold flex items-center gap-1 transition">
                                <i class="ri-refresh-line"></i> Ganti Token
                            </a>

                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick='editDevice(<?= json_encode($d) ?>)' class="px-3 py-1 text-xs font-bold text-indigo-600 hover:bg-indigo-50 rounded-xl transition">
                                    Edit
                                </button>
                                <a href="<?= url('/smart-tap/devices/delete?id=' . $d['id']) ?>" onclick="return confirm('Hapus perangkat <?= htmlspecialchars(addslashes($d['device_name'])) ?>?')" class="px-2.5 py-1 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl transition">
                                    Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ============================================== -->
<!-- MODAL 1: FORM DAFTARKAN / EDIT PERANGKAT      -->
<!-- ============================================== -->
<div id="modal-device" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg">
                    <i class="ri-device-line"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900" id="modal-device-title">Daftarkan Perangkat Baru</h3>
                    <p class="text-[11px] text-gray-500">Terminal mesin presensi di lokasi pondok</p>
                </div>
            </div>
            <button onclick="closeDeviceModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <form method="POST" action="<?= url('/smart-tap/devices/store') ?>" class="p-6 space-y-4">
            <input type="hidden" name="id" id="device_id">

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Kode Perangkat (Unik) <span class="text-rose-500">*</span></label>
                <input type="text" name="device_code" id="device_code" required placeholder="Contoh: PRK-MASJID-01" class="w-full text-xs font-mono uppercase rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Perangkat / Terminal <span class="text-rose-500">*</span></label>
                <input type="text" name="device_name" id="device_name" required placeholder="Contoh: Terminal Pintu Masjid Utama" class="w-full text-xs rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">PIN Otorisasi (4 Digit)</label>
                    <input type="text" name="pin_code" id="device_pin" maxlength="6" value="1234" required class="w-full text-xs font-mono font-bold tracking-widest rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Lokasi Titik</label>
                    <input type="text" name="location" id="device_location" placeholder="Contoh: Masjid Lt. 1" class="w-full text-xs rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
                </div>
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                    <input type="checkbox" name="is_active" id="device_is_active" value="1" checked class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span>Perangkat Aktif & Diizinkan Sinkronisasi</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeDeviceModal()" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm">Simpan Perangkat</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL 2: WIZARD INISIASI PERANGKAT (KODE SETUP & 2FA) -->
<!-- ============================================== -->
<div id="modal-pairing" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all text-center p-6 space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2 text-left">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                    <i class="ri-shield-keyhole-line"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-gray-900" id="wizard-device-name">Inisiasi Perangkat</h3>
                    <p class="text-[11px] text-gray-500" id="wizard-device-code">TERMINAL</p>
                </div>
            </div>
            <button type="button" onclick="closeSetupModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <!-- Stepper Indicator -->
        <div class="flex items-center justify-between text-[11px] font-bold text-gray-400 px-2">
            <span id="step-indicator-1" class="text-indigo-600 flex items-center gap-1">
                <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px]">1</span>
                Siapkan
            </span>
            <span class="h-0.5 w-6 bg-gray-200" id="step-line-1"></span>
            <span id="step-indicator-2" class="flex items-center gap-1 text-gray-400">
                <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[10px]">2</span>
                Handshake
            </span>
            <span class="h-0.5 w-6 bg-gray-200" id="step-line-2"></span>
            <span id="step-indicator-3" class="flex items-center gap-1 text-gray-400">
                <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[10px]">3</span>
                2FA
            </span>
        </div>

        <!-- STAGE 1: MENUNGGU HANDSHAKE (Input URL & Setup Code di Kiosk) -->
        <div id="stage-waiting-handshake" class="space-y-4 text-left">
            <div class="p-3.5 bg-indigo-50/70 border border-indigo-100 rounded-2xl text-xs text-indigo-900 leading-relaxed">
                <span class="font-bold block text-indigo-950 mb-1"><i class="ri-information-line mr-1"></i>Langkah di Mesin Absensi Client:</span>
                Buka aplikasi Smart Tap Kiosk, jalankan <strong>Wizard Inisiasi</strong>, lalu masukkan informasi koneksi berikut pada <strong>Step 2</strong>.
            </div>

            <!-- Server URL -->
            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">URL Endpoint Server:</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="wizard-server-url" readonly value="<?= rtrim(url('/'), '/') ?>" class="w-full text-xs font-mono bg-gray-50 border border-gray-200 rounded-xl py-2 px-3 text-gray-800 select-all focus:outline-none">
                    <button type="button" onclick="copyWizardVal('wizard-server-url', 'URL Server')" class="p-2 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl border border-indigo-100 font-bold whitespace-nowrap">
                        <i class="ri-file-copy-line"></i> Salin
                    </button>
                </div>
            </div>

            <!-- Setup Code -->
            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Kode Setup Perangkat:</label>
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 border-2 border-dashed border-indigo-300 rounded-2xl p-4 text-center">
                    <div id="wizard-setup-code" class="font-mono text-3xl font-black text-indigo-700 tracking-[0.25em] select-all">
                        ---- ----
                    </div>
                    <div class="text-[11px] text-indigo-600 font-medium mt-1 flex items-center justify-center gap-1">
                        <i class="ri-time-line"></i> Berlaku 60 Menit &bull; Sekali Pakai
                    </div>
                </div>
            </div>

            <div class="text-center text-xs text-amber-600 font-semibold flex items-center justify-center gap-2 pt-1">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                <span>Menunggu mesin absensi menghubungi server...</span>
            </div>
        </div>

        <!-- STAGE 2: HANDSHAKE MASUK -> TAMPILKAN KODE 2FA -->
        <div id="stage-waiting-pairing" class="space-y-4 hidden text-left">
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-900 flex items-center gap-2">
                <i class="ri-checkbox-circle-fill text-emerald-600 text-lg flex-shrink-0"></i>
                <div>
                    <span class="font-bold">Mesin Terhubung!</span>
                    <div class="text-[11px] text-emerald-700" id="wizard-handshake-meta">IP: 192.168.1.100</div>
                </div>
            </div>

            <div class="text-center">
                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Masukkan Kode 2FA Ini di Kiosk:</span>
                <div class="bg-gradient-to-br from-amber-50 to-amber-100/60 border-2 border-dashed border-amber-300 rounded-2xl py-4 px-2 text-center">
                    <div id="wizard-pairing-code" class="font-mono text-4xl font-black text-amber-600 tracking-[0.25em] select-all">
                        ------
                    </div>
                    <div class="text-[11px] text-amber-700 font-medium mt-1">
                        Berlaku 10 Menit &bull; Sisa Percobaan: <span id="wizard-attempts-left" class="font-bold">5</span>x
                    </div>
                </div>
            </div>

            <p class="text-[11px] text-gray-500 text-center">
                Ketik 6 digit kode di atas pada layar Step 3 Kiosk untuk menyelesaikan pairing.
            </p>
        </div>

        <!-- STAGE 3: SUKSES PAIRING -->
        <div id="stage-paired" class="space-y-4 hidden text-center py-4">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mx-auto">
                <i class="ri-check-line font-black"></i>
            </div>
            <div>
                <h4 class="text-base font-bold text-gray-900">Inisiasi Selesai!</h4>
                <p class="text-xs text-gray-500 mt-1">Perangkat berhasil dipairing dan token rahasia sudah tersimpan di client.</p>
            </div>
            <button type="button" onclick="window.location.reload()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold shadow-sm">
                Selesai & Muat Ulang Halaman
            </button>
        </div>

        <!-- STAGE 4: KEDALUWARSA ATAU TERKUNCI -->
        <div id="stage-error" class="space-y-3 hidden text-center py-2">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl mx-auto">
                <i class="ri-error-warning-line"></i>
            </div>
            <div class="text-xs text-rose-700 font-bold" id="wizard-error-msg">Sesi inisiasi telah berakhir.</div>
            <button type="button" onclick="restartSetup()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl">
                Mulai Ulang Inisiasi
            </button>
        </div>

        <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
            <button type="button" onclick="cancelCurrentSetup()" class="text-rose-500 hover:text-rose-700 font-bold">
                Batalkan Inisiasi
            </button>
            <button type="button" onclick="closeSetupModal()" class="text-gray-500 hover:text-gray-700 font-semibold">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
let activeSetupDeviceId = null;
let setupPollingInterval = null;

function startDeviceSetup(deviceId, deviceName, deviceCode) {
    activeSetupDeviceId = deviceId;
    document.getElementById('wizard-device-name').innerText = deviceName;
    document.getElementById('wizard-device-code').innerText = deviceCode;

    // Reset view
    setWizardStage('waiting_handshake');
    document.getElementById('modal-pairing').classList.remove('hidden');

    fetch('<?= url("/smart-tap/devices/start-setup") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'id=' + encodeURIComponent(deviceId)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.status) {
            renderWizardStatus(data.status);
            startSetupPolling();
        } else {
            alert(data.message || 'Gagal memulai inisiasi');
            closeSetupModal();
        }
    })
    .catch(err => {
        alert('Gagal menghubungi server: ' + err);
        closeSetupModal();
    });
}

function startSetupPolling() {
    if (setupPollingInterval) clearInterval(setupPollingInterval);
    setupPollingInterval = setInterval(() => {
        if (!activeSetupDeviceId) return;
        fetch('<?= url("/smart-tap/devices/setup-status?id=") ?>' + encodeURIComponent(activeSetupDeviceId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.status) {
                renderWizardStatus(data.status);
            }
        })
        .catch(() => {});
    }, 2500);
}

function renderWizardStatus(status) {
    setWizardStage(status.stage);

    if (status.setup_code) {
        document.getElementById('wizard-setup-code').innerText = status.setup_code;
    }

    if (status.stage === 'waiting_pairing') {
        const c = String(status.pairing_code || '------');
        document.getElementById('wizard-pairing-code').innerText = c.length === 6 ? c.slice(0, 3) + ' ' + c.slice(3) : c;
        document.getElementById('wizard-handshake-meta').innerText = 
            `Komputer: ${status.handshake_hostname || 'Kiosk'} • IP: ${status.handshake_ip || '-'}`;
        document.getElementById('wizard-attempts-left').innerText = status.attempts_left;
    }

    if (status.stage === 'paired') {
        if (setupPollingInterval) clearInterval(setupPollingInterval);
    }

    if (status.stage === 'expired' || status.stage === 'locked') {
        if (setupPollingInterval) clearInterval(setupPollingInterval);
        document.getElementById('wizard-error-msg').innerText = 
            status.stage === 'locked' ? 'Terlalu banyak percobaan kode 2FA salah. Sesi dikunci.' : 'Kode setup atau 2FA telah kedaluwarsa.';
    }
}

function setWizardStage(stage) {
    const s1 = document.getElementById('stage-waiting-handshake');
    const s2 = document.getElementById('stage-waiting-pairing');
    const s3 = document.getElementById('stage-paired');
    const s4 = document.getElementById('stage-error');

    s1.classList.add('hidden');
    s2.classList.add('hidden');
    s3.classList.add('hidden');
    s4.classList.add('hidden');

    const ind1 = document.getElementById('step-indicator-1');
    const ind2 = document.getElementById('step-indicator-2');
    const ind3 = document.getElementById('step-indicator-3');

    if (stage === 'waiting_handshake') {
        s1.classList.remove('hidden');
        ind1.className = "text-indigo-600 flex items-center gap-1 font-bold";
        ind2.className = "text-gray-400 flex items-center gap-1";
        ind3.className = "text-gray-400 flex items-center gap-1";
    } else if (stage === 'waiting_pairing') {
        s2.classList.remove('hidden');
        ind1.className = "text-emerald-600 flex items-center gap-1 font-bold";
        ind2.className = "text-amber-600 flex items-center gap-1 font-bold";
        ind3.className = "text-gray-400 flex items-center gap-1";
    } else if (stage === 'paired') {
        s3.classList.remove('hidden');
        ind1.className = "text-emerald-600 flex items-center gap-1 font-bold";
        ind2.className = "text-emerald-600 flex items-center gap-1 font-bold";
        ind3.className = "text-emerald-600 flex items-center gap-1 font-bold";
    } else {
        s4.classList.remove('hidden');
    }
}

function cancelCurrentSetup() {
    if (!activeSetupDeviceId) return;
    if (confirm('Batalkan inisiasi perangkat ini?')) {
        fetch('<?= url("/smart-tap/devices/cancel-setup") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'id=' + encodeURIComponent(activeSetupDeviceId)
        }).finally(() => {
            closeSetupModal();
        });
    }
}

function restartSetup() {
    if (activeSetupDeviceId) {
        const name = document.getElementById('wizard-device-name').innerText;
        const code = document.getElementById('wizard-device-code').innerText;
        startDeviceSetup(activeSetupDeviceId, name, code);
    }
}

function copyWizardVal(id, label) {
    const val = document.getElementById(id).value || document.getElementById(id).innerText;
    navigator.clipboard.writeText(val).then(() => {
        alert(label + ' berhasil disalin!');
    });
}

function closeSetupModal() {
    if (setupPollingInterval) clearInterval(setupPollingInterval);
    document.getElementById('modal-pairing').classList.add('hidden');
    activeSetupDeviceId = null;
}

function openDeviceModal() {
    document.getElementById('device_id').value = '';
    document.getElementById('device_code').value = '';
    document.getElementById('device_name').value = '';
    document.getElementById('device_pin').value = '1234';
    document.getElementById('device_location').value = '';
    document.getElementById('device_is_active').checked = true;
    document.getElementById('modal-device-title').innerText = 'Daftarkan Perangkat Baru';
    document.getElementById('modal-device').classList.remove('hidden');
}

function editDevice(d) {
    document.getElementById('device_id').value = d.id;
    document.getElementById('device_code').value = d.device_code;
    document.getElementById('device_name').value = d.device_name;
    document.getElementById('device_pin').value = d.pin_code;
    document.getElementById('device_location').value = d.location || '';
    document.getElementById('device_is_active').checked = d.is_active == 1;
    document.getElementById('modal-device-title').innerText = 'Edit Perangkat: ' + d.device_code;
    document.getElementById('modal-device').classList.remove('hidden');
}

function closeDeviceModal() {
    document.getElementById('modal-device').classList.add('hidden');
}

function copyToken(inputId) {
    const input = document.getElementById(inputId);
    navigator.clipboard.writeText(input.value).then(() => {
        alert('Device Token berhasil disalin ke clipboard!');
    });
}

function copyEndpoint() {
    const url = document.getElementById('endpoint-url').innerText;
    navigator.clipboard.writeText(url).then(() => {
        alert('Endpoint API berhasil disalin ke clipboard!');
    });
}

function toggleToken(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="ri-eye-off-line text-sm"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="ri-eye-line text-sm"></i>';
    }
}
</script>

<?php
renderFooter();
?>
