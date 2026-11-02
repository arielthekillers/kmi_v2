<?php
renderHeader("Smart Tap — Titik Terminal & Perangkat Presensi");

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
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Titik Terminal & Perangkat Presensi</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
                        Kelola perangkat terminal absensi di berbagai titik (Masjid, Kantor, Gerbang, Aula) serta pengaturan token sinkronisasi data offline client.
                    </p>
                </div>
            </div>

            <!-- Right Action Button -->
            <div class="flex items-center gap-3 self-start lg:self-center">
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
                    Setiap perangkat memerlukan <strong>Device Token</strong> untuk autentikasi API agar aplikasi client dapat mengunduh master kartu RFID dan mengirimkan rekaman tap absensi ke server secara otomatis.
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
                                
                                <!-- Secret Token Display -->
                                <div class="border-t border-gray-200/60 pt-3">
                                    <div class="flex items-center justify-between text-[11px] text-gray-500 mb-1.5">
                                        <span class="font-bold flex items-center gap-1">
                                            <i class="ri-shield-keyhole-line text-indigo-500"></i> Device Token (Secret):
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <input type="password" value="<?= htmlspecialchars($d['device_token']) ?>" id="token-<?= $d['id'] ?>" readonly class="w-full text-xs font-mono bg-white border border-gray-200 rounded-xl py-2 px-3 text-gray-700 tracking-wider focus:outline-none">
                                        <button type="button" onclick="copyToken('token-<?= $d['id'] ?>')" class="p-2 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl transition border border-indigo-100 flex-shrink-0" title="Salin Token">
                                            <i class="ri-file-copy-line text-sm"></i>
                                        </button>
                                        <button type="button" onclick="toggleToken('token-<?= $d['id'] ?>', this)" class="p-2 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition border border-gray-200 flex-shrink-0" title="Lihat">
                                            <i class="ri-eye-line text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 2FA Pairing Action -->
                            <div class="space-y-2 mb-4">
                                <button type="button" onclick="requestPairingCode(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['device_name'])) ?>')" class="w-full py-2.5 px-4 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-2xl text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition hover:shadow-md">
                                    <i class="ri-shield-keyhole-line text-sm"></i>
                                    <span>Buka Pairing / Generate Kode 2FA</span>
                                </button>

                                <?php if ($isPaired): ?>
                                    <a href="<?= url('/smart-tap/devices/unpair?id=' . $d['id']) ?>" onclick="return confirm('Putus hubungan (Unpair) perangkat ini? Perangkat harus di-pairing ulang dengan kode 2FA baru agar dapat digunakan kembali.')" class="text-rose-500 hover:text-rose-700 text-xs font-bold block text-center py-1 transition">
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
<!-- MODAL 2: KODE PAIRING 2FA (AKTIVASI PERANGKAT) -->
<!-- ============================================== -->
<div id="modal-pairing" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all text-center p-6 space-y-5">
        <div class="w-16 h-16 rounded-3xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
            <i class="ri-shield-keyhole-line"></i>
        </div>

        <div>
            <h3 class="text-lg font-black text-gray-900" id="pairing-device-title">Kode Pairing 2FA</h3>
            <p class="text-xs text-gray-500 mt-1">Masukkan 6 digit kode ini pada layar aktivasi perangkat di lokasi pemasangan.</p>
        </div>

        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 border-2 border-dashed border-indigo-300 rounded-2xl py-4 px-2">
            <span class="text-[10px] text-indigo-700 font-bold uppercase tracking-wider block mb-1">KODE AKTIVASI:</span>
            <div id="pairing-code-display" class="font-mono text-3xl font-black text-indigo-700 tracking-[0.25em] py-1 select-all">
                ------
            </div>
            <div class="text-[11px] text-indigo-600 font-semibold mt-1 flex items-center justify-center gap-1">
                <i class="ri-time-line"></i> Berlaku 15 Menit &bull; Sekali Pakai
            </div>
        </div>

        <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200/60 text-[11px] text-amber-900 text-left flex items-start gap-2">
            <i class="ri-information-line text-base text-amber-600 flex-shrink-0 mt-0.5"></i>
            <span>Begitu perangkat berhasil terhubung, kode ini otomatis hangus. Komputer lain tidak akan bisa masuk.</span>
        </div>

        <button type="button" onclick="closePairingModal()" class="w-full py-2.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-2xl transition shadow-sm">
            Tutup Jendela
        </button>
    </div>
</div>

<script>
function requestPairingCode(deviceId, deviceName) {
    document.getElementById('pairing-device-title').innerText = 'Kode Pairing: ' + deviceName;
    document.getElementById('pairing-code-display').innerText = 'MEMUAT...';
    document.getElementById('modal-pairing').classList.remove('hidden');

    fetch('<?= url("/smart-tap/devices/pairing-code") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'id=' + encodeURIComponent(deviceId)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.pairing_code) {
            const c = String(data.pairing_code);
            document.getElementById('pairing-code-display').innerText = c.slice(0, 3) + ' ' + c.slice(3);
        } else {
            alert(data.message || 'Gagal generate kode pairing');
            closePairingModal();
        }
    })
    .catch(err => {
        alert('Gagal menghubungi server: ' + err);
        closePairingModal();
    });
}

function closePairingModal() {
    document.getElementById('modal-pairing').classList.add('hidden');
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
