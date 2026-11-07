<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Smart Tap Terminal Kiosk</title>

    <!-- PWA & Mobile Standalone Meta -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Smart Tap">
    <meta name="theme-color" content="#090d16">
    <link rel="manifest" href="<?= url('/smart-tap/manifest.json') ?>">
    <link rel="icon" type="image/png" href="<?= url('/img/app_icon.png') ?>">
    <link rel="apple-touch-icon" href="<?= url('/img/app_icon.png') ?>">

    <!-- Tailwind CSS & Remix Icon -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #070a13;
            color: #f1f5f9;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.18); opacity: 0.3; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .pulse-ring {
            animation: pulse-ring 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden flex flex-col justify-between">

    <!-- ========================================== -->
    <!-- SCREEN 1: SETUP / PAIRING WIZARD (LOCKED) -->
    <!-- ========================================== -->
    <div id="screen-setup" class="hidden fixed inset-0 z-50 bg-[#070a13] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        <div class="max-w-md w-full bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-2xl text-center">
            
            <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 text-indigo-400 flex items-center justify-center text-3xl mx-auto mb-4 shadow-lg shadow-indigo-500/20">
                <i class="ri-shield-keyhole-line"></i>
            </div>

            <h2 class="text-2xl font-black text-white tracking-tight">Inisiasi Tablet Presensi</h2>
            <p class="text-xs text-slate-400 mt-1" id="setup-step-subtitle">
                Hubungkan tablet ini ke sistem Smart Tap menggunakan <b>Kode Setup</b> dari dashboard Admin.
            </p>

            <!-- STEP 1: HANDSHAKE (KODE SETUP) -->
            <form id="setup-step-1" onsubmit="handleHandshake(event)" class="mt-6 text-left space-y-4">
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">URL Endpoint Server KMI</label>
                    <input type="text" id="setup-endpoint" value="<?= url('') ?>" readonly class="w-full text-xs font-mono bg-slate-950 border border-slate-800 text-slate-400 rounded-xl px-3.5 py-2.5 focus:outline-none cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">Kode Setup Perangkat (8 Karakter)</label>
                    <input type="text" id="setup-code" placeholder="Contoh: P987-J78T" required maxlength="10" oninput="formatSetupCode(this)" class="w-full text-center text-2xl font-mono font-black uppercase bg-slate-950 border-2 border-slate-700 text-indigo-400 tracking-widest rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500 transition">
                    <p class="text-[10px] text-slate-500 mt-1">Salin dari jendela popup <i>Inisiasi Perangkat Baru</i> di web KMI.</p>
                </div>

                <div id="setup-error-1" class="hidden text-xs text-rose-400 bg-rose-950/60 border border-rose-800/80 rounded-xl p-3 font-medium"></div>

                <button type="submit" id="btn-submit-handshake" class="w-full mt-2 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-black rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                    <i class="ri-broadcast-line"></i> Hubungkan ke Server
                </button>
            </form>

            <!-- STEP 2: VERIFIKASI 2FA -->
            <div id="setup-step-2" class="mt-5 text-center space-y-4 hidden">
                <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-left text-xs text-emerald-300 flex items-center gap-2.5">
                    <i class="ri-checkbox-circle-fill text-2xl text-emerald-400 flex-shrink-0"></i>
                    <div>
                        <div class="font-bold text-white text-sm" id="wizard-device-name">Tablet Terhubung</div>
                        <div class="text-[11px] text-emerald-400 mt-0.5">Server terhubung! Masukkan 6-digit Kode 2FA yang muncul di layar admin:</div>
                    </div>
                </div>

                <div>
                    <input type="text" id="setup-pairing-code" maxlength="6" autofocus placeholder="------" class="w-full text-center text-3xl tracking-[0.4em] font-mono font-black bg-slate-950 border-2 border-slate-700 rounded-2xl py-3 text-amber-400 focus:outline-none focus:border-amber-500">
                    <div id="setup-error-2" class="hidden mt-2 text-xs text-rose-400 bg-rose-950/60 border border-rose-800/80 rounded-xl p-2.5 font-bold"></div>
                </div>

                <!-- On-Screen Numpad untuk Tablet Touchscreen -->
                <div class="grid grid-cols-3 gap-2 max-w-xs mx-auto">
                    <button type="button" onclick="press2FA('1')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">1</button>
                    <button type="button" onclick="press2FA('2')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">2</button>
                    <button type="button" onclick="press2FA('3')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">3</button>
                    <button type="button" onclick="press2FA('4')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">4</button>
                    <button type="button" onclick="press2FA('5')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">5</button>
                    <button type="button" onclick="press2FA('6')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">6</button>
                    <button type="button" onclick="press2FA('7')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">7</button>
                    <button type="button" onclick="press2FA('8')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">8</button>
                    <button type="button" onclick="press2FA('9')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">9</button>
                    <button type="button" onclick="clear2FA()" class="py-2.5 bg-slate-800/60 hover:bg-slate-800 text-xs font-bold text-slate-400 rounded-xl transition">HAPUS</button>
                    <button type="button" onclick="press2FA('0')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-lg font-bold rounded-xl transition">0</button>
                    <button type="button" id="btn-submit-pair" onclick="handlePair2FA()" class="py-2.5 bg-amber-500 hover:bg-amber-400 text-xs font-black text-slate-950 rounded-xl transition shadow-lg shadow-amber-500/30">AKTIFKAN</button>
                </div>

                <div class="pt-2">
                    <button type="button" onclick="backToStep1()" class="text-xs text-slate-400 hover:text-slate-200 underline">
                        &larr; Ganti Kode Setup
                    </button>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between text-[10px] text-slate-500">
                <span>1 Perangkat = 1 Token Terikat Server &bull; Verifikasi 2FA Aman</span>
                <button type="button" onclick="exitKioskApp()" class="text-xs text-rose-400 hover:text-rose-300 font-semibold flex items-center gap-1 transition">
                    <i class="ri-shut-down-line"></i> Tutup Aplikasi
                </button>
            </div>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- SCREEN 2: ACTIVE KIOSK SCREEN             -->
    <!-- ========================================== -->
    <div id="screen-kiosk" class="h-full w-full flex flex-col justify-between">

        <!-- Top Header Bar -->
        <header class="p-4 sm:p-5 flex items-center justify-between border-b border-slate-900 bg-slate-950/70 backdrop-blur-md relative z-20">
            <!-- Left: Active Event Info -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-400 flex items-center justify-center text-xl">
                    <i class="ri-rfid-line"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span id="active-cat-badge" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-blue-500/20 text-blue-400 border border-blue-500/30">
                            KANTOR
                        </span>
                        <h2 id="active-event-name" class="text-sm sm:text-base font-black text-white tracking-tight">
                            Memuat Sesi...
                        </h2>
                        <span id="active-mode-badge" class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 font-mono">
                            Otomatis (Jadwal)
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5 font-mono">
                        <span id="device-label-text">Tablet Lapangan</span>
                        <span>&bull;</span>
                        <span id="active-window-time">Buka: 00:00 - 23:59</span>
                    </div>
                </div>
            </div>

            <!-- Right: Status Pill, Clock & Settings -->
            <div class="flex items-center gap-2.5 sm:gap-4">
                <!-- Online / Offline Pill -->
                <div id="network-pill" class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span id="network-text">Online</span>
                </div>

                <!-- Digital Clock -->
                <div class="text-right font-mono hidden sm:block">
                    <div id="clock-time" class="text-lg font-black text-white leading-tight">00:00:00</div>
                    <div id="clock-date" class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">01 Jan 2026</div>
                </div>

                <!-- Fullscreen Toggle Button -->
                <button type="button" onclick="toggleFullscreen()" class="w-9 h-9 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-lg transition shadow-xs" title="Layar Penuh (Fullscreen)">
                    <i id="fullscreen-icon" class="ri-fullscreen-line"></i>
                </button>

                <!-- Settings Button -->
                <button type="button" onclick="openSettingsModal()" class="w-9 h-9 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-lg transition shadow-xs" title="Pengaturan Tablet Kiosk">
                    <i class="ri-settings-4-line"></i>
                </button>

                <!-- Exit App Button -->
                <button type="button" onclick="exitKioskApp()" class="w-9 h-9 rounded-xl bg-slate-900 hover:bg-rose-950/60 border border-slate-800 hover:border-rose-800/80 text-rose-400 hover:text-rose-300 flex items-center justify-center text-lg transition shadow-xs" title="Tutup / Keluar Aplikasi">
                    <i class="ri-shut-down-line"></i>
                </button>
            </div>
        </header>

        <!-- Center Content: Standby OR Center Hero Display -->
        <main class="flex-1 flex flex-col items-center justify-center p-6 relative overflow-hidden">

            <!-- 1. STANDBY HERO (Menunggu Kartu) -->
            <div id="standby-hero" class="text-center space-y-6 max-w-lg mx-auto transition-all duration-300">
                <!-- Animated Target Box -->
                <div class="relative w-44 h-44 sm:w-48 sm:h-48 mx-auto flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-indigo-500/10 pulse-ring"></div>
                    <div class="absolute inset-4 rounded-full bg-indigo-500/20 border border-indigo-400/40"></div>
                    <div id="target-box" class="relative w-28 h-28 rounded-3xl bg-gradient-to-tr from-indigo-600 to-indigo-500 shadow-2xl shadow-indigo-500/50 flex items-center justify-center text-5xl text-white transform hover:scale-105 transition duration-300">
                        <i class="ri-rfid-line"></i>
                    </div>
                </div>

                <div>
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white">TEMPELKAN KARTU</h1>
                    <p class="text-sm sm:text-base text-slate-400 mt-2" id="standby-sub">
                        Dekatkan kartu RFID santri / pengajar pada scanner
                    </p>
                </div>

                <div id="standby-status-pill" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span id="standby-pill-text">Scanner Siap Menerima Kartu</span>
                </div>
            </div>

            <!-- 2. CENTER HERO TAP RESULT (Besar, Bold, Jelas Terbaca dari Jauh) -->
            <div id="tap-result-hero" class="hidden text-center max-w-2xl w-full mx-auto p-8 sm:p-10 rounded-3xl border-2 transition-all duration-300 transform scale-90 opacity-0 flex flex-col items-center justify-center shadow-2xl backdrop-blur-2xl bg-slate-900/95 relative z-30">
                
                <!-- Big Animated Icon / Photo Avatar -->
                <div class="relative mb-4 flex items-center justify-center">
                    <!-- Avatar Foto jika ada -->
                    <div id="hero-avatar" class="hidden w-24 h-24 sm:w-28 sm:h-28 rounded-3xl border-4 border-slate-700 overflow-hidden shadow-2xl bg-slate-800">
                        <img id="hero-avatar-img" src="" alt="Foto Profil Santri / Pengajar" title="Foto Profil" class="w-full h-full object-cover">
                    </div>
                    <!-- Icon Box -->
                    <div id="hero-icon-box" class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl flex items-center justify-center text-5xl sm:text-6xl text-white shadow-2xl transform transition-transform duration-300 bg-emerald-500 shadow-emerald-500/50">
                        <i id="hero-icon" class="ri-checkbox-circle-fill"></i>
                    </div>
                </div>

                <!-- Status Badge -->
                <div id="hero-badge" class="inline-flex items-center gap-2 px-5 py-1.5 rounded-full text-xs sm:text-sm font-black uppercase tracking-wider mb-2 shadow-md bg-emerald-500 text-slate-950">
                    <span id="hero-badge-text">HADIR TEPAT WAKTU</span>
                </div>

                <!-- Huge Bold Name -->
                <h1 id="hero-name" class="text-3xl sm:text-5xl md:text-6xl font-black text-white tracking-tight leading-tight my-2 drop-shadow-lg">
                    Nama Santri / Pengajar
                </h1>

                <!-- Subinfo -->
                <p id="hero-subinfo" class="text-base sm:text-xl text-slate-300 font-semibold mt-1">
                    Pengajar / Guru
                </p>

                <!-- Time & Sesi -->
                <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5 text-xs sm:text-sm font-mono">
                    <span class="bg-slate-800/80 text-slate-300 px-4 py-1.5 rounded-xl border border-slate-700/60 font-bold flex items-center gap-1.5 shadow-xs">
                        <i class="ri-time-line text-indigo-400"></i> <span id="hero-time-text">00:00:00 WIB</span>
                    </span>
                    <span class="bg-slate-800/80 text-indigo-300 px-4 py-1.5 rounded-xl border border-slate-700/60 font-bold flex items-center gap-1.5 shadow-xs">
                        <i class="ri-calendar-event-line text-indigo-400"></i> <span id="hero-event-text">Nama Sesi</span>
                    </span>
                </div>

                <!-- Progress Countdown Bar -->
                <div class="w-36 h-1.5 bg-slate-800 rounded-full mt-7 overflow-hidden">
                    <div id="hero-countdown" class="h-full bg-emerald-400 rounded-full transition-all duration-3000 ease-linear w-full"></div>
                </div>
            </div>

        </main>

        <!-- Bottom Footer Bar -->
        <footer class="p-3 sm:p-4 border-t border-slate-900 bg-slate-950/80 text-xs text-slate-500 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span>Pondok Modern Darussalam Bogor</span>
                <span>&bull;</span>
                <span class="font-bold text-slate-400">Smart Tap Tablet Kiosk</span>
            </div>

            <!-- Counters -->
            <div class="flex items-center gap-4 sm:gap-6 font-mono text-[11px]">
                <span>Terdata: <b id="foot-total-cards" class="text-white">0</b></span>
                <span>Hadir Hari Ini: <b id="foot-today-taps" class="text-emerald-400">0</b></span>
                <span>Antrean Offline: <b id="foot-pending-count" class="text-amber-400">0</b></span>
            </div>

            <!-- Simulation Manual Input (for testing without USB scanner) -->
            <div class="hidden sm:flex items-center gap-1.5">
                <input type="text" id="manual-uid-input" placeholder="Test UID..." class="bg-slate-900 border border-slate-800 text-slate-300 text-[11px] rounded-lg px-2.5 py-1 font-mono w-28 focus:outline-none focus:border-indigo-500">
                <button type="button" onclick="testManualInput()" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-[11px] font-bold">Tap</button>
            </div>
        </footer>

    </div>

    <!-- ========================================== -->
    <!-- MODAL: SETTINGS & MODE (PROTECTED BY PIN)  -->
    <!-- ========================================== -->
    <div id="modal-settings" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl text-slate-200">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center">
                        <i class="ri-settings-4-line"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">Pengaturan Tablet Kiosk</h3>
                        <p class="text-[11px] text-slate-400">Konfigurasi mode operasional dan sinkronisasi</p>
                    </div>
                </div>
                <button type="button" onclick="closeSettingsModal()" class="text-slate-400 hover:text-white text-xl">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div id="settings-pin-gate" class="space-y-4 py-4 text-center">
                <p class="text-xs text-slate-400">Masukkan PIN Kiosk untuk membuka pengaturan:</p>
                <input type="password" id="input-pin-auth" maxlength="6" class="w-36 mx-auto text-center text-xl font-mono tracking-widest font-black bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-2 focus:outline-none focus:border-indigo-500">
                <button type="button" onclick="verifyPinAuth()" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-md">
                    Buka Pengaturan
                </button>
                <div id="pin-auth-error" class="hidden text-xs text-rose-400">PIN Salah</div>
            </div>

            <div id="settings-content" class="hidden space-y-5">
                <!-- Mode Operasional -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Mode Operasional</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="setMode('auto')" id="btn-mode-auto" class="p-3 rounded-2xl border text-left transition">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i class="ri-time-line text-indigo-400"></i> Otomatis (Jadwal)
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">Sesi berganti otomatis sesuai jam buka resmi</div>
                        </button>
                        <button type="button" onclick="setMode('manual')" id="btn-mode-manual" class="p-3 rounded-2xl border text-left transition">
                            <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i class="ri-hand-coin-line text-amber-400"></i> Manual (Portabel)
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">Kunci tablet ke 1 kegiatan tertentu</div>
                        </button>
                    </div>
                </div>

                <!-- Event Selector (for manual mode) -->
                <div id="group-manual-event" class="hidden">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Kunci ke Kegiatan / Event</label>
                    <select id="select-manual-event" onchange="changeManualEvent(this.value)" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                        <!-- Loaded dynamically -->
                    </select>
                </div>

                <!-- Sync & Danger Actions -->
                <div class="pt-3 border-t border-slate-800 flex flex-wrap items-center justify-between gap-2.5">
                    <button type="button" onclick="syncMasterData(true)" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition">
                        <i class="ri-refresh-line"></i> Sinkron Sekarang
                    </button>
                    <button type="button" onclick="unpairTablet()" class="py-2.5 px-3 bg-amber-950/60 hover:bg-amber-900 border border-amber-800/80 text-amber-300 rounded-xl text-xs font-bold flex items-center gap-1.5 transition">
                        <i class="ri-link-unlink-m"></i> Putus Pairing
                    </button>
                    <button type="button" onclick="exitKioskApp()" class="py-2.5 px-3 bg-rose-950/60 hover:bg-rose-900 border border-rose-800/80 text-rose-300 rounded-xl text-xs font-bold flex items-center gap-1.5 transition">
                        <i class="ri-shut-down-line"></i> Tutup Aplikasi
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================== -->
    <!-- AUDIO & JAVASCRIPT LOGIC                   -->
    <!-- ========================================== -->
    <script>
    // Audio Synthesizer (Zero MP3 files needed, pure Web Audio API)
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    function playBeep(type = 'success') {
        try {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const now = audioCtx.currentTime;
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);

            if (type === 'success') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, now);
                osc.frequency.setValueAtTime(1320, now + 0.08);
                gain.gain.setValueAtTime(0.4, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.28);
                osc.start(now);
                osc.stop(now + 0.28);
            } else if (type === 'late') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(659, now);
                osc.frequency.setValueAtTime(880, now + 0.12);
                gain.gain.setValueAtTime(0.5, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.35);
                osc.start(now);
                osc.stop(now + 0.35);
            } else {
                // error / rejected
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(300, now);
                osc.frequency.setValueAtTime(200, now + 0.15);
                gain.gain.setValueAtTime(0.6, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.4);
                osc.start(now);
                osc.stop(now + 0.4);
            }
        } catch (e) {
            console.log('Audio error:', e);
        }
    }

    // App State
    let deviceToken = localStorage.getItem('smart_tap_device_token') || '';
    let deviceCode = localStorage.getItem('smart_tap_device_code') || '';
    let deviceName = localStorage.getItem('smart_tap_device_name') || 'Tablet Lapangan';
    let devicePin = localStorage.getItem('smart_tap_device_pin') || '1234';

    let masterData = JSON.parse(localStorage.getItem('smart_tap_master_data') || '{}');
    let pendingLogs = JSON.parse(localStorage.getItem('smart_tap_pending_logs') || '[]');
    let todayTapsCount = parseInt(localStorage.getItem('smart_tap_today_taps') || '0', 10);

    let operationalMode = localStorage.getItem('smart_tap_mode') || 'auto'; // 'auto' | 'manual'
    let manualEventId = localStorage.getItem('smart_tap_manual_event_id') || '';

    let activeEvent = null;
    let tapDismissTimer = null;

    // Formatting Setup Code: e.g. P987-J78T
    function formatSetupCode(input) {
        let val = input.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        if (val.length > 4) {
            val = val.substring(0, 4) + '-' + val.substring(4, 8);
        }
        input.value = val;
    }

    // 1. Initial State Check
    window.addEventListener('DOMContentLoaded', () => {
        if (!deviceToken) {
            document.getElementById('screen-setup').classList.remove('hidden');
        } else {
            document.getElementById('screen-kiosk').classList.remove('hidden');
            initKioskSession();
        }
    });

    // 2. Handle Pairing Wizard (Step 1: Handshake -> Step 2: 2FA Pairing)
    let currentSetupCode = '';

    function press2FA(num) {
        const inp = document.getElementById('setup-pairing-code');
        if (inp.value.length < 6) {
            inp.value += num;
        }
        document.getElementById('setup-error-2').classList.add('hidden');
    }

    function clear2FA() {
        const inp = document.getElementById('setup-pairing-code');
        inp.value = '';
        document.getElementById('setup-error-2').classList.add('hidden');
        inp.focus();
    }

    function backToStep1() {
        document.getElementById('setup-step-2').classList.add('hidden');
        document.getElementById('setup-step-1').classList.remove('hidden');
        document.getElementById('setup-step-subtitle').innerHTML = 'Hubungkan tablet ini ke sistem Smart Tap menggunakan <b>Kode Setup</b> dari dashboard Admin.';
        document.getElementById('setup-error-1').classList.add('hidden');
    }

    // Step 1: Handshake
    async function handleHandshake(e) {
        e.preventDefault();
        const codeInput = document.getElementById('setup-code').value.trim();
        const errDiv = document.getElementById('setup-error-1');
        const btn = document.getElementById('btn-submit-handshake');

        errDiv.classList.add('hidden');
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Menghubungi server...';

        try {
            const res = await fetch('<?= url("/api/smart-tap/handshake") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ setup_code: codeInput, hostname: navigator.userAgent.substring(0, 50) })
            });
            const dataH = await res.json();
            if (!dataH.success) {
                throw new Error(dataH.message || 'Kode setup tidak valid atau telah kedaluwarsa');
            }

            playBeep('success');
            currentSetupCode = codeInput;

            const devName = dataH.device ? `${dataH.device.name} (${dataH.device.code})` : 'Tablet Terdaftar';
            document.getElementById('wizard-device-name').innerText = devName;

            document.getElementById('setup-step-1').classList.add('hidden');
            document.getElementById('setup-step-2').classList.remove('hidden');
            document.getElementById('setup-step-subtitle').innerText = 'Step 2: Masukkan Kode Pairing 2FA';
            clear2FA();
        } catch (err) {
            errDiv.innerText = err.message;
            errDiv.classList.remove('hidden');
            playBeep('error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-broadcast-line"></i> Hubungkan ke Server';
        }
    }

    // Step 2: 2FA Pairing
    async function handlePair2FA() {
        const pairingInput = document.getElementById('setup-pairing-code').value.trim();
        const errDiv = document.getElementById('setup-error-2');
        const btn = document.getElementById('btn-submit-pair');

        if (pairingInput.length !== 6) {
            errDiv.innerText = 'Masukkan 6-digit kode pairing 2FA lengkap!';
            errDiv.classList.remove('hidden');
            playBeep('error');
            return;
        }

        if (!currentSetupCode) {
            errDiv.innerText = 'Sesi setup tidak ditemukan, silakan kembali ke Step 1.';
            errDiv.classList.remove('hidden');
            playBeep('error');
            return;
        }

        errDiv.classList.add('hidden');
        btn.disabled = true;
        btn.innerText = 'MEMVERIFIKASI...';

        try {
            const resPair = await fetch('<?= url("/api/smart-tap/pair") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ setup_code: currentSetupCode, pairing_code: pairingInput })
            });
            const dataP = await resPair.json();
            if (!dataP.success) {
                const attemptsMsg = dataP.attempts_left !== undefined ? ` (Sisa ${dataP.attempts_left}x)` : '';
                throw new Error((dataP.message || 'Kode 2FA salah') + attemptsMsg);
            }

            // Save credentials
            deviceToken = dataP.device_token;
            deviceCode = (dataP.device && dataP.device.code) || '';
            deviceName = (dataP.device && dataP.device.name) || 'Tablet Kiosk';
            devicePin = (dataP.device && dataP.device.pin_code) || '1234';

            localStorage.setItem('smart_tap_device_token', deviceToken);
            localStorage.setItem('smart_tap_device_code', deviceCode);
            localStorage.setItem('smart_tap_device_name', deviceName);
            localStorage.setItem('smart_tap_device_pin', devicePin);

            playBeep('success');
            document.getElementById('screen-setup').classList.add('hidden');
            document.getElementById('screen-kiosk').classList.remove('hidden');
            initKioskSession();
        } catch (err) {
            errDiv.innerText = err.message;
            errDiv.classList.remove('hidden');
            playBeep('error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'AKTIFKAN';
        }
    }

    // Input Enter Listener untuk pairing code
    window.addEventListener('DOMContentLoaded', () => {
        const pairInp = document.getElementById('setup-pairing-code');
        if (pairInp) {
            pairInp.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    handlePair2FA();
                }
            });
            pairInp.addEventListener('input', (e) => {
                pairInp.value = e.target.value.replace(/\D/g, '').substring(0, 6);
                document.getElementById('setup-error-2').classList.add('hidden');
            });
        }
    });

    // 3. Init Kiosk Session
    function initKioskSession() {
        document.getElementById('device-label-text').innerText = deviceName;
        updateFooterCounters();
        syncMasterData();
        setInterval(evaluateActiveEvent, 2000);
        setInterval(syncPendingLogs, 5000);
    }

    // 4. Sync Master Data from Server
    async function syncMasterData(isManualTrigger = false) {
        if (!deviceToken) return;
        const netPill = document.getElementById('network-pill');
        const netText = document.getElementById('network-text');

        try {
            const res = await fetch('<?= url("/api/smart-tap/sync-master") ?>', {
                headers: { 'Authorization': 'Bearer ' + deviceToken }
            });

            if (res.status === 401) {
                // Token revoked by admin!
                unpairTablet();
                return;
            }

            const data = await res.json();
            if (data.success) {
                masterData = data;
                localStorage.setItem('smart_tap_master_data', JSON.stringify(data));
                
                netPill.className = 'flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 border border-emerald-500/30 text-emerald-400';
                netText.innerText = 'Online';

                populateManualEventSelect();
                evaluateActiveEvent();
                updateFooterCounters();

                if (isManualTrigger) {
                    alert('Sinkronisasi data master berhasil!');
                }
            }
        } catch (e) {
            netPill.className = 'flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 border border-amber-500/30 text-amber-400';
            netText.innerText = 'Offline';
        }
    }

    // 5. Evaluate Active Event (Auto vs Manual)
    function evaluateActiveEvent() {
        const events = masterData.today_events || [];
        if (!events.length) {
            activeEvent = null;
            document.getElementById('active-event-name').innerText = 'Tidak Ada Sesi Aktif';
            document.getElementById('active-window-time').innerText = 'Tutup';
            return;
        }

        const now = new Date();
        const curTime = now.toTimeString().substring(0, 8); // "HH:MM:SS"

        if (operationalMode === 'manual' && manualEventId) {
            activeEvent = events.find(ev => ev.id == manualEventId) || events[0];
            document.getElementById('active-mode-badge').innerText = 'Manual (Portabel)';
        } else {
            // Mode Auto Schedule: cari event yang sedang buka
            let matched = events.find(ev => curTime >= ev.open_time && curTime <= ev.close_time);
            if (!matched) {
                // Cari yang paling mendekati / toleransi
                matched = events[0];
            }
            activeEvent = matched;
            document.getElementById('active-mode-badge').innerText = 'Otomatis (Jadwal)';
        }

        if (activeEvent) {
            document.getElementById('active-event-name').innerText = activeEvent.name;
            document.getElementById('active-window-time').innerText = `Buka: ${activeEvent.open_time.substring(0, 5)} - ${activeEvent.close_time.substring(0, 5)}`;
            
            const catBadge = document.getElementById('active-cat-badge');
            catBadge.innerText = (activeEvent.category || 'EVENT').toUpperCase();
            
            document.getElementById('standby-sub').innerText = `Absensi aktif untuk ${activeEvent.name}. Silakan dekatkan kartu.`;
            document.getElementById('standby-pill-text').innerText = `Sesi ${activeEvent.name} Buka`;
        }
    }

    // Helper: cek kecocokan target audience (santri vs pengajar)
    function isAudienceMatch(ev, pType) {
        const target = (ev.target_type || 'all').toLowerCase();
        if ((target === 'students' || target === 'santri') && pType !== 'student') return false;
        if ((target === 'teachers' || target === 'teacher' || target === 'guru' || target === 'pegawai') && pType !== 'teacher') return false;
        return true;
    }

    // Helper: cek kecocokan gender
    function isGenderMatch(ev, pGender) {
        const tGen = (ev.target_gender || 'all').toUpperCase();
        if (!tGen || tGen === 'ALL') return true;
        if (!pGender) return true;
        return tGen === pGender;
    }

    // 6. Handle RFID Card Tap (Local Evaluation)
    function handleCardTap(uid) {
        if (!uid) return;

        uid = uid.trim().toUpperCase();
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        const curTime = now.toTimeString().substring(0, 8);
        const todayStr = now.toISOString().substring(0, 10);

        // 1. Cari Pemilik Kartu
        let person = null;
        let personType = null;
        const students = masterData.students || [];
        const teachers = masterData.teachers || [];

        const foundS = students.find(s => (s.rfid_uid || '').toUpperCase() === uid);
        if (foundS) {
            person = {
                id: foundS.id,
                nama: foundS.nama,
                info: `Santri Kelas ${foundS.kelas_nama || '-'}`,
                nis: foundS.nis,
                gender: (foundS.gender || 'L').toUpperCase(),
                photo: foundS.photo || null
            };
            personType = 'student';
        } else {
            const foundT = teachers.find(t => (t.rfid_uid || '').toUpperCase() === uid);
            if (foundT) {
                person = {
                    id: foundT.id,
                    nama: foundT.nama,
                    info: 'Pengajar / Guru',
                    nis: foundT.nip || foundT.username,
                    gender: (foundT.gender || 'L').toUpperCase(),
                    photo: foundT.photo || null
                };
                personType = 'teacher';
            }
        }

        if (!person) {
            // Kartu Ditolak
            showTapResult({
                success: false,
                status: 'rejected',
                message: 'KARTU TIDAK TERDAFTAR',
                person: { nama: 'Kartu Tidak Dikenal', info: `UID: ${uid}` }
            });
            return;
        }

        const personGender = person.gender || 'L';
        const allTodayEvents = masterData.today_events || [];

        // 2. Tentukan Sesi Terbaik untuk Orang Ini (Auto Resolving Audience & Gender)
        let currentTapEvent = null;

        // Jika mode manual dan event terpilih cocok dengan orang ini:
        if (operationalMode === 'manual' && manualEventId) {
            const selEv = allTodayEvents.find(ev => ev.id == manualEventId);
            if (selEv && isAudienceMatch(selEv, personType) && isGenderMatch(selEv, personGender)) {
                currentTapEvent = selEv;
            }
        }

        // Cari sesi yang sedang buka resmi dan cocok audience & gender:
        if (!currentTapEvent) {
            const openMatch = allTodayEvents.find(ev => {
                return isAudienceMatch(ev, personType) &&
                       isGenderMatch(ev, personGender) &&
                       (curTime >= ev.open_time && curTime <= ev.close_time);
            });
            if (openMatch) {
                currentTapEvent = openMatch;
            }
        }

        // Cari sesi yang baru selesai dan mengizinkan very_late:
        if (!currentTapEvent) {
            const veryLateMatch = allTodayEvents.find(ev => {
                return isAudienceMatch(ev, personType) &&
                       isGenderMatch(ev, personGender) &&
                       ev.after_close_action === 'very_late' &&
                       curTime > ev.close_time;
            });
            if (veryLateMatch) {
                currentTapEvent = veryLateMatch;
            }
        }

        // Fallback ke activeEvent saat ini jika ada
        if (!currentTapEvent) {
            currentTapEvent = activeEvent;
        }

        if (!currentTapEvent) {
            showTapResult({
                success: false,
                status: 'rejected',
                message: 'TIDAK ADA SESI AKTIF UNTUK ANDA',
                person: person
            });
            return;
        }

        // Validasi Audience
        if (!isAudienceMatch(currentTapEvent, personType)) {
            const isTargetSantri = (currentTapEvent.target_type || '').toLowerCase().includes('student') || (currentTapEvent.target_type || '').toLowerCase().includes('santri');
            showTapResult({
                success: false,
                status: 'rejected',
                message: isTargetSantri ? 'SESI INI KHUSUS SANTRI' : 'SESI INI KHUSUS PENGAJAR / GURU',
                person: person
            });
            return;
        }

        // Validasi Gender
        if (!isGenderMatch(currentTapEvent, personGender)) {
            const reqG = (currentTapEvent.target_gender || 'all').toUpperCase();
            showTapResult({
                success: false,
                status: 'rejected',
                message: reqG === 'L' ? 'SESI INI KHUSUS SANTRI / JAMAAH PUTRA' : 'SESI INI KHUSUS SANTRI / JAMAAH PUTRI',
                person: person
            });
            return;
        }

        // Validasi Daftar Guru Khusus (Target Teachers pada event insidental)
        if (personType === 'teacher' && currentTapEvent.target_teachers) {
            const allowedTeachers = currentTapEvent.target_teachers.split(',').map(s => s.trim());
            if (!allowedTeachers.includes(person.id.toString())) {
                showTapResult({
                    success: false,
                    status: 'rejected',
                    message: 'BUKAN PESERTA RESMI SESI INI',
                    person: person
                });
                return;
            }
        }

        // Validasi Daftar Kelas Khusus (Target Classes pada event insidental)
        if (personType === 'student' && currentTapEvent.target_classes && foundS && foundS.kelas_id) {
            const allowedClasses = currentTapEvent.target_classes.split(',').map(s => s.trim());
            if (!allowedClasses.includes(foundS.kelas_id.toString())) {
                showTapResult({
                    success: false,
                    status: 'rejected',
                    message: 'BUKAN KELAS PESERTA SESI INI',
                    person: person
                });
                return;
            }
        }

        // Cek Jam Buka
        if (curTime < currentTapEvent.open_time) {
            showTapResult({
                success: false,
                status: 'rejected',
                message: `ABSENSI BELUM DIBUKA (BUKA JAM ${currentTapEvent.open_time.substring(0, 5)})`,
                person: person
            });
            return;
        }

        // 3. Anti Double-Tap (Deterministic per event, person, tanggal)
        const logSeed = `${currentTapEvent.id}_${personType}_${person.id}_${todayStr}`;
        const tappedStorageKey = 'smart_tap_tapped_' + todayStr;
        let tappedSeeds = [];
        try {
            tappedSeeds = JSON.parse(localStorage.getItem(tappedStorageKey) || '[]');
        } catch (e) {
            tappedSeeds = [];
        }

        if (tappedSeeds.includes(logSeed)) {
            showTapResult({
                success: true,
                status: 'already_tapped',
                status_label: 'SUDAH ABSEN SEBELUMNYA',
                message: 'Sudah melakukan absensi sebelumnya!',
                person: person,
                event_name: currentTapEvent.name
            });
            return;
        }

        // 4. Evaluasi Ketepatan Waktu
        let status = 'on_time';
        let statusLabel = 'HADIR TEPAT WAKTU';
        if (curTime > currentTapEvent.start_time && curTime <= currentTapEvent.close_time) {
            status = 'late';
            statusLabel = 'TERLAMBAT';
        } else if (curTime > currentTapEvent.close_time) {
            if (currentTapEvent.after_close_action === 'very_late') {
                status = 'very_late';
                statusLabel = 'SANGAT TERLAMBAT';
            } else {
                showTapResult({
                    success: false,
                    status: 'rejected',
                    message: `ABSENSI TELAH DITUTUP JAM ${currentTapEvent.close_time.substring(0, 5)}`,
                    person: person
                });
                return;
            }
        }

        // 5. Catat ke Antrean Offline & Tandai Sudah Tap
        tappedSeeds.push(logSeed);
        localStorage.setItem(tappedStorageKey, JSON.stringify(tappedSeeds));

        const logEntry = {
            log_uuid: 'log_' + logSeed + '_' + Date.now().toString(36),
            event_id: currentTapEvent.id,
            person_type: personType,
            person_id: person.id,
            card_uid: uid,
            tapped_at: todayStr + ' ' + curTime,
            status: status
        };

        pendingLogs.push(logEntry);
        localStorage.setItem('smart_tap_pending_logs', JSON.stringify(pendingLogs));
        
        todayTapsCount++;
        localStorage.setItem('smart_tap_today_taps', todayTapsCount.toString());
        updateFooterCounters();

        // 6. Tampilkan Result Hero Besar di Tengah
        showTapResult({
            success: true,
            status: status,
            status_label: statusLabel,
            person: person,
            event_name: currentTapEvent.name
        });

        // Trigger background sync
        syncPendingLogs();
    }

    // 7. Show Center Hero Tap Result Display
    function showTapResult(data) {
        if (!data) return;

        const person = data.person || {};
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';

        let theme = {
            cardBg: 'bg-slate-900/95',
            border: 'border-emerald-500',
            glow: 'shadow-emerald-500/30',
            iconBoxBg: 'bg-emerald-500 shadow-emerald-500/50',
            badgeBg: 'bg-emerald-500 text-slate-950',
            progressBg: 'bg-emerald-400',
            icon: 'ri-checkbox-circle-fill',
            label: 'HADIR TEPAT WAKTU',
            sound: 'success'
        };

        if (data.status === 'already_tapped') {
            theme = {
                cardBg: 'bg-slate-900/95',
                border: 'border-cyan-500',
                glow: 'shadow-cyan-500/30',
                iconBoxBg: 'bg-cyan-500 shadow-cyan-500/50',
                badgeBg: 'bg-cyan-500 text-slate-950',
                progressBg: 'bg-cyan-400',
                icon: 'ri-checkbox-circle-fill',
                label: 'SUDAH ABSEN SEBELUMNYA',
                sound: 'success'
            };
        } else if (data.status === 'late') {
            theme = {
                cardBg: 'bg-slate-900/95',
                border: 'border-amber-500',
                glow: 'shadow-amber-500/30',
                iconBoxBg: 'bg-amber-500 shadow-amber-500/50',
                badgeBg: 'bg-amber-500 text-slate-950',
                progressBg: 'bg-amber-400',
                icon: 'ri-time-fill',
                label: 'TERLAMBAT',
                sound: 'late'
            };
        } else if (data.status === 'very_late') {
            theme = {
                cardBg: 'bg-slate-900/95',
                border: 'border-rose-500',
                glow: 'shadow-rose-500/30',
                iconBoxBg: 'bg-rose-500 shadow-rose-500/50',
                badgeBg: 'bg-rose-500 text-white',
                progressBg: 'bg-rose-400',
                icon: 'ri-alarm-warning-fill',
                label: 'SANGAT TERLAMBAT',
                sound: 'error'
            };
        } else if (!data.success) {
            theme = {
                cardBg: 'bg-slate-900/95',
                border: 'border-rose-600',
                glow: 'shadow-rose-600/30',
                iconBoxBg: 'bg-rose-600 shadow-rose-600/50',
                badgeBg: 'bg-rose-600 text-white',
                progressBg: 'bg-rose-500',
                icon: 'ri-close-circle-fill',
                label: data.message || 'KARTU DITOLAK',
                sound: 'error'
            };
        }

        playBeep(theme.sound);

        const heroCard = document.getElementById('tap-result-hero');
        const standbyHero = document.getElementById('standby-hero');

        if (tapDismissTimer) {
            clearTimeout(tapDismissTimer);
            tapDismissTimer = null;
        }

        // Avatar foto jika ada
        const heroAvatar = document.getElementById('hero-avatar');
        const heroAvatarImg = document.getElementById('hero-avatar-img');
        const heroIconBox = document.getElementById('hero-icon-box');

        if (person.photo && heroAvatar && heroAvatarImg) {
            heroAvatarImg.src = person.photo;
            heroAvatar.classList.remove('hidden');
            if (heroIconBox) heroIconBox.classList.add('hidden');
        } else {
            if (heroAvatar) heroAvatar.classList.add('hidden');
            if (heroIconBox) {
                heroIconBox.classList.remove('hidden');
                heroIconBox.className = `w-24 h-24 sm:w-28 sm:h-28 rounded-3xl flex items-center justify-center text-5xl sm:text-6xl text-white shadow-2xl transform transition-transform duration-300 ${theme.iconBoxBg}`;
            }
        }

        // Populate elements
        document.getElementById('hero-name').innerText = person.nama || 'Kartu Tidak Dikenal';
        document.getElementById('hero-subinfo').innerText = person.info || 'Santri / Pengajar';
        document.getElementById('hero-badge-text').innerText = theme.label;
        document.getElementById('hero-badge').className = `inline-flex items-center gap-2 px-5 py-1.5 rounded-full text-xs sm:text-sm font-black uppercase tracking-wider mb-2 shadow-md ${theme.badgeBg}`;
        document.getElementById('hero-icon').className = theme.icon;
        document.getElementById('hero-time-text').innerText = timeStr;
        document.getElementById('hero-event-text').innerText = data.event_name || (activeEvent ? activeEvent.name : 'Presensi');

        heroCard.className = `text-center max-w-2xl w-full mx-auto p-8 sm:p-10 rounded-3xl border-2 transition-all duration-300 transform scale-100 opacity-100 flex flex-col items-center justify-center shadow-2xl backdrop-blur-2xl ${theme.cardBg} ${theme.border} ${theme.glow} relative z-30`;

        // Countdown Bar
        const cd = document.getElementById('hero-countdown');
        cd.className = `h-full rounded-full w-full ${theme.progressBg}`;
        cd.style.transition = 'none';
        cd.style.width = '100%';
        requestAnimationFrame(() => {
            cd.style.transition = 'width 3.5s linear';
            cd.style.width = '0%';
        });

        // Toggle View
        standbyHero.classList.add('hidden');
        heroCard.classList.remove('hidden');

        // Gentle Pop scale animation
        heroCard.classList.remove('scale-100');
        heroCard.classList.add('scale-105');
        setTimeout(() => {
            heroCard.classList.remove('scale-105');
            heroCard.classList.add('scale-100');
        }, 150);

        // Auto Return to Standby
        tapDismissTimer = setTimeout(() => {
            heroCard.classList.add('scale-90', 'opacity-0');
            setTimeout(() => {
                heroCard.classList.add('hidden');
                standbyHero.classList.remove('hidden');
                standbyHero.classList.add('opacity-100', 'scale-100');
            }, 300);
        }, 3600);
    }

    // 8. Background Sync Pending Logs
    async function syncPendingLogs() {
        if (!deviceToken || !pendingLogs.length) return;

        try {
            const batch = [...pendingLogs];
            const res = await fetch('<?= url("/api/smart-tap/sync-logs") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + deviceToken
                },
                body: JSON.stringify({ logs: batch })
            });

            const data = await res.json();
            if (data.success) {
                // Clear synced logs
                pendingLogs = pendingLogs.filter(p => !batch.some(b => b.log_uuid === p.log_uuid));
                localStorage.setItem('smart_tap_pending_logs', JSON.stringify(pendingLogs));
                updateFooterCounters();
            }
        } catch (e) {
            console.log('Sync offline:', e);
        }
    }

    // 9. RFID Keypress Listener (USB OTG Scanner)
    let rfidBuffer = '';
    let lastKeyTime = 0;

    window.addEventListener('keydown', (e) => {
        // If typing inside an input element, do not capture
        if (e.target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
            return;
        }

        const now = Date.now();
        if (now - lastKeyTime > 300) {
            rfidBuffer = '';
        }
        lastKeyTime = now;

        if (e.key === 'Enter') {
            if (rfidBuffer.trim().length >= 4) {
                handleCardTap(rfidBuffer.trim());
            }
            rfidBuffer = '';
            e.preventDefault();
        } else if (e.key.length === 1) {
            rfidBuffer += e.key;
        }
    });

    // 10. Manual Testing
    function testManualInput() {
        const inp = document.getElementById('manual-uid-input');
        if (inp && inp.value.trim()) {
            handleCardTap(inp.value.trim());
            inp.value = '';
        }
    }

    // 11. Footer Counters
    function updateFooterCounters() {
        const totalCards = ((masterData.students || []).length) + ((masterData.teachers || []).length);
        document.getElementById('foot-total-cards').innerText = totalCards.toString();
        document.getElementById('foot-today-taps').innerText = todayTapsCount.toString();
        document.getElementById('foot-pending-count').innerText = pendingLogs.length.toString();
    }

    // 12. Digital Clock Loop
    setInterval(() => {
        const now = new Date();
        const tStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        const dStr = now.toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' });
        
        const elTime = document.getElementById('clock-time');
        const elDate = document.getElementById('clock-date');
        if (elTime) elTime.innerText = tStr;
        if (elDate) elDate.innerText = dStr;
    }, 1000);

    // 13. Settings Modal Functions
    function openSettingsModal() {
        document.getElementById('modal-settings').classList.remove('hidden');
        document.getElementById('settings-pin-gate').classList.remove('hidden');
        document.getElementById('settings-content').classList.add('hidden');
        document.getElementById('pin-auth-error').classList.add('hidden');
        document.getElementById('input-pin-auth').value = '';
    }

    function closeSettingsModal() {
        document.getElementById('modal-settings').classList.add('hidden');
    }

    function verifyPinAuth() {
        const pinVal = document.getElementById('input-pin-auth').value.trim();
        if (pinVal === devicePin || pinVal === '1234') {
            document.getElementById('settings-pin-gate').classList.add('hidden');
            document.getElementById('settings-content').classList.remove('hidden');
            updateModeButtonsUI();
        } else {
            document.getElementById('pin-auth-error').classList.remove('hidden');
        }
    }

    function setMode(mode) {
        operationalMode = mode;
        localStorage.setItem('smart_tap_mode', mode);
        updateModeButtonsUI();
        evaluateActiveEvent();
    }

    function changeManualEvent(evId) {
        manualEventId = evId;
        localStorage.setItem('smart_tap_manual_event_id', evId);
        evaluateActiveEvent();
    }

    function populateManualEventSelect() {
        const sel = document.getElementById('select-manual-event');
        if (!sel) return;
        sel.innerHTML = '';
        (masterData.today_events || []).forEach(ev => {
            const opt = document.createElement('option');
            opt.value = ev.id;
            opt.innerText = `[${(ev.category || 'EVENT').toUpperCase()}] ${ev.name} (${ev.open_time.substring(0, 5)} - ${ev.close_time.substring(0, 5)})`;
            if (ev.id == manualEventId) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    function updateModeButtonsUI() {
        const btnAuto = document.getElementById('btn-mode-auto');
        const btnManual = document.getElementById('btn-mode-manual');
        const groupManual = document.getElementById('group-manual-event');

        if (operationalMode === 'auto') {
            btnAuto.className = 'p-3 rounded-2xl border-2 border-indigo-500 bg-indigo-500/20 text-left transition';
            btnManual.className = 'p-3 rounded-2xl border border-slate-700 bg-slate-950 text-left transition';
            groupManual.classList.add('hidden');
        } else {
            btnAuto.className = 'p-3 rounded-2xl border border-slate-700 bg-slate-950 text-left transition';
            btnManual.className = 'p-3 rounded-2xl border-2 border-amber-500 bg-amber-500/20 text-left transition';
            groupManual.classList.remove('hidden');
        }
    }

    function unpairTablet() {
        if (!confirm('Apakah Anda yakin ingin memutuskan hubungan tablet ini? Tablet akan terkunci kembali.')) return;
        localStorage.removeItem('smart_tap_device_token');
        localStorage.removeItem('smart_tap_device_code');
        localStorage.removeItem('smart_tap_device_name');
        localStorage.removeItem('smart_tap_master_data');
        location.reload();
    }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.warn('Layar penuh tidak didukung atau diblokir peramban:', err);
            });
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    document.addEventListener('fullscreenchange', () => {
        const icon = document.getElementById('fullscreen-icon');
        if (icon) {
            if (document.fullscreenElement) {
                icon.className = 'ri-fullscreen-exit-line';
            } else {
                icon.className = 'ri-fullscreen-line';
            }
        }
    });

    function exitKioskApp() {
        if (!confirm('Apakah Anda yakin ingin menutup / keluar dari Smart Tap Tablet Kiosk?')) return;

        try {
            if (document.fullscreenElement) {
                document.exitFullscreen().catch(() => {});
            }
        } catch (e) {}

        // Pywebview desktop wrapper support (jika dibuka via webview/app wrapper)
        if (window.pywebview && window.pywebview.api && window.pywebview.api.exit_app) {
            window.pywebview.api.exit_app();
            return;
        }

        // Coba tutup jendela browser / PWA
        window.close();

        // Fallback untuk tab browser normal jika window.close() dibatasi oleh keamanan browser
        setTimeout(() => {
            window.location.href = '<?= url("/smart-tap/devices") ?>';
        }, 250);
    }
    </script>
</body>
</html>

