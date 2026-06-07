<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Showcase - Masa Ujian & Koreksi (Modern)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="<?= url('/js/antigravity-particles.js') ?>"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            overflow: hidden;
            background-color: #f8fafc;
            background-image:
                radial-gradient(at 0% 0%, hsla(253, 16%, 90%, 1) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225, 39%, 95%, 1) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339, 49%, 90%, 1) 0, transparent 50%);
            color: #1e293b;
        }

        .glass-clock-block {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', monospace;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .glass-clock-block::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 40%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 100%);
            pointer-events: none;
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        }

        .card-gradient-orange {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
        }

        .card-gradient-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            color: white;
        }

        .card-gradient-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        @keyframes fade-in-down {
            0% { opacity: 0; transform: translateY(-20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in-down {
            animation: fade-in-down 0.5s ease-out;
        }

        /* Splash Screen */
        #splash-screen {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.8s ease-in-out, visibility 0.8s;
        }

        #splash-screen.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .splash-loader {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(99, 102, 241, 0.2);
            border-left-color: #6366f1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .splash-btn {
            background: white;
            color: #4f46e5;
            padding: 12px 32px;
            border-radius: 9999px;
            font-size: 1.125rem;
            font-weight: 600;
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.3);
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .splash-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4);
        }

        @keyframes float1 {
            0%, 100% { transform: translate(0, 0); }
            33% { transform: translate(10px, -8px); }
            66% { transform: translate(-8px, 10px); }
        }

        @keyframes float2 {
            0%, 100% { transform: translate(0, 0); }
            33% { transform: translate(-10px, 8px); }
            66% { transform: translate(8px, -10px); }
        }

        .animate-float-1 { animation: float1 8s ease-in-out infinite; }
        .animate-float-2 { animation: float2 10s ease-in-out infinite; }

        /* Clock flip animation */
        .clock-digit {
            position: relative;
            display: inline-block;
            height: 1em;
            line-height: 1em;
            padding: 0 2px;
        }

        .clock-digit-inner {
            display: inline-block;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Pulse for correction in progress */
        @keyframes pulse-yellow {
            0%, 100% { background-color: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.3); }
            50% { background-color: rgba(245, 158, 11, 0.2); border-color: rgba(245, 158, 11, 0.6); }
        }
        .pulse-proses {
            animation: pulse-yellow 2s infinite;
        }

        /* Arabic styling for mapel */
        .arabic-text {
            font-family: 'Noto Naskh Arabic', 'Amiri', serif;
            direction: rtl;
        }
    </style>
</head>

<body class="h-screen flex flex-col p-6 gap-6 selection:bg-indigo-200 selection:text-indigo-900">

    <!-- Splash Screen -->
    <div id="splash-screen">
        <div class="flex flex-col items-center gap-6 animate-fade-in-down">
            <div class="bg-white p-6 rounded-3xl h-40 w-40 flex items-center justify-center shadow-[0_20px_50px_-12px_rgba(79,70,229,0.3)] mb-4">
                <img src="<?= url('/img/kmi.png') ?>" alt="Logo" class="h-32 w-auto object-contain">
            </div>

            <h1 class="text-6xl font-black bg-clip-text text-transparent bg-gradient-to-r from-red-600 to-amber-600 tracking-tight text-center">
                TV Showcase Ujian
            </h1>
            <p class="text-slate-500 text-xl font-medium tracking-wide text-center max-w-lg">
                Monitoring Pelaksanaan & Koreksi Ujian<br>Pondok Modern Darussalam Bogor
            </p>
        </div>

        <div class="mt-16 h-16 flex items-center justify-center">
            <div id="splash-loader" class="splash-loader"></div>
            <button id="splash-start-btn" class="splash-btn hidden" onclick="enterShowcase()">
                <i class="ri-play-circle-fill text-2xl"></i>
                <span>Mulai Showcase</span>
            </button>
        </div>

        <div class="absolute bottom-8 text-slate-400 text-sm font-mono">
            v2.1.0 (Exam Mode) &copy; 2026 KMI Dev Team
        </div>
    </div>

    <!-- Top Bar: Logo + Clock + Stats Cards -->
    <header class="flex items-center gap-4">
        <div class="flex items-center gap-3 flex-shrink-0">
            <img src="<?= url('/img/kmi.png') ?>" alt="Logo" class="h-12 w-auto object-contain drop-shadow-md"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            <span class="text-red-600 font-bold text-2xl hidden">KMI</span>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">KMI Exam Monitoring</h1>
                <p class="text-slate-500 text-sm font-medium">Monitoring Ujian & Koreksi Lembar Jawaban</p>
            </div>
        </div>

        <!-- Clock Section -->
        <div class="flex items-center gap-2 flex-shrink-0 ml-4">
            <div class="glass-clock-block w-16 h-16">
                <span id="clock-hours" class="text-4xl font-bold tracking-tighter clock-digit">00</span>
            </div>
            <div class="flex flex-col gap-1.5 opacity-60">
                <div class="w-1 h-1 bg-slate-800 rounded-full"></div>
                <div class="w-1 h-1 bg-slate-800 rounded-full"></div>
            </div>
            <div class="glass-clock-block w-16 h-16">
                <span id="clock-minutes" class="text-4xl font-bold tracking-tighter clock-digit">00</span>
            </div>
            <div class="flex flex-col gap-1.5 opacity-60">
                <div class="w-1 h-1 bg-slate-800 rounded-full"></div>
                <div class="w-1 h-1 bg-slate-800 rounded-full"></div>
            </div>
            <div class="glass-clock-block w-16 h-16 relative">
                <span id="clock-seconds" class="text-4xl font-bold tracking-tighter text-amber-500 clock-digit">00</span>
            </div>

            <!-- Date -->
            <div class="ml-2 pl-2 border-l border-slate-200 flex-shrink-0">
                <div id="clock-day" class="text-xs font-bold text-slate-700 uppercase tracking-wide leading-none">AHAD</div>
                <div id="clock-date-full" class="text-[10px] font-medium text-slate-500">1 Jan 2026</div>
            </div>
        </div>

        <!-- Stats Cards Row -->
        <div class="flex gap-2 ml-auto">
            <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-3 shadow-lg w-28 h-28 flex flex-col justify-between">
                <div>
                    <div class="text-white/80 text-[9px] font-bold uppercase tracking-wider mb-0.5">Pelajaran</div>
                    <div id="stat-pelajaran" class="text-3xl font-black text-white">0</div>
                </div>
                <div class="flex justify-end">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <i class="ri-book-read-line text-white text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-3 shadow-lg w-28 h-28 flex flex-col justify-between">
                <div>
                    <div class="text-white/80 text-[9px] font-bold uppercase tracking-wider mb-0.5">Total Santri</div>
                    <div id="stat-total-santri" class="text-3xl font-black text-white">0</div>
                </div>
                <div class="flex justify-end">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <i class="ri-group-line text-white text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-3 shadow-lg w-28 h-28 flex flex-col justify-between">
                <div>
                    <div class="text-white/80 text-[9px] font-bold uppercase tracking-wider mb-0.5">Kelas</div>
                    <div id="stat-kelas" class="text-3xl font-black text-white">0</div>
                </div>
                <div class="flex justify-end">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <i class="ri-hotel-line text-white text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-3 shadow-lg w-28 h-28 flex flex-col justify-between">
                <div>
                    <div class="text-white/80 text-[9px] font-bold uppercase tracking-wider mb-0.5">Pengajar</div>
                    <div id="stat-pengajar" class="text-3xl font-black text-white">0</div>
                </div>
                <div class="flex justify-end">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <i class="ri-user-star-line text-white text-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Grid -->
    <main class="flex-1 grid grid-cols-12 gap-6 min-h-0">

        <!-- Left Column: Active Session info + Correction Stats (3 cols) -->
        <div class="col-span-3 flex flex-col gap-6">
            <!-- Active Session Details -->
            <div class="bg-slate-900 rounded-3xl p-6 shadow-xl text-white relative overflow-hidden flex flex-col justify-between h-44">
                <div class="absolute -right-8 -top-8 w-24 h-24 bg-white/5 rounded-full pointer-events-none"></div>
                <div>
                    <span class="px-2.5 py-1 bg-red-500/20 text-red-400 border border-red-500/30 rounded-full text-[10px] font-bold tracking-widest uppercase">Masa Evaluasi</span>
                    <h3 id="exam-session-title" class="text-xl font-black mt-3 leading-tight text-slate-100">Memuat Sesi...</h3>
                </div>
                <div class="flex items-center gap-2 text-slate-400 text-xs mt-2 font-mono">
                    <i class="ri-calendar-line"></i> Tahun Ajaran: <span id="exam-session-year">-</span>
                </div>
            </div>

            <!-- Correction Stats Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-lg flex-1 flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Statistik Koreksi</h4>
                    
                    <div class="flex items-center justify-center my-6 relative">
                        <!-- Big Percentage text -->
                        <div class="text-center">
                            <span id="correction-percent" class="text-5xl font-black text-slate-800">0%</span>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mt-1">Selesai Dikoreksi</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-100 rounded-full h-2 mb-6 overflow-hidden">
                        <div id="correction-progress-bar" class="bg-gradient-to-r from-red-500 to-amber-500 h-full w-0 transition-all duration-500"></div>
                    </div>

                    <!-- Details Stats -->
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2 bg-slate-50 rounded-xl">
                            <div id="stat-total-koreksi" class="text-lg font-extrabold text-slate-800">0</div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase">Total</span>
                        </div>
                        <div class="p-2 bg-amber-50/50 border border-amber-100/50 rounded-xl">
                            <div id="stat-proses-koreksi" class="text-lg font-extrabold text-amber-600">0</div>
                            <span class="text-[9px] font-bold text-amber-500 uppercase">Proses</span>
                        </div>
                        <div class="p-2 bg-emerald-50/50 border border-emerald-100/50 rounded-xl">
                            <div id="stat-selesai-koreksi" class="text-lg font-extrabold text-emerald-600">0</div>
                            <span class="text-[9px] font-bold text-emerald-500 uppercase">Selesai</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 text-[10px] text-center text-slate-400 font-medium">
                    Diperbarui otomatis secara real-time
                </div>
            </div>
        </div>

        <!-- Center Column: Correction Queue (6 cols) -->
        <div class="col-span-6 flex flex-col bg-white rounded-3xl shadow-lg border border-slate-100 overflow-hidden relative">
            <div class="p-5 border-b border-red-100 bg-gradient-to-r from-red-500 via-rose-600 to-orange-500 relative overflow-hidden flex justify-between items-center">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20 animate-float-1"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16 animate-float-2"></div>

                <h2 class="text-xl font-black text-white flex items-center gap-3 relative z-10">
                    <div class="p-2 bg-white/20 backdrop-blur-sm text-white rounded-xl shadow-lg animate-pulse">
                        <i class="ri-article-line text-lg"></i>
                    </div>
                    <div class="leading-tight">
                        <div class="tracking-tight">Status Koreksi Ujian</div>
                        <div class="text-[10px] font-normal text-red-100 tracking-wide -mt-1">Pemantauan Pemeriksaan Lembar Jawaban</div>
                    </div>
                </h2>
                <div id="correction-page-badge" class="px-4 py-1.5 rounded-full bg-white/20 backdrop-blur-sm text-white font-bold text-sm shadow-lg border border-white/30 relative z-10">
                    Halaman 1/1
                </div>
            </div>

            <div class="flex-1 p-6 overflow-hidden relative bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9IiNlMmU4ZjAiLz48L3N2Zz4=')]">
                <div id="correction-container" class="h-full w-full transition-opacity duration-500">
                    <div class="flex h-full w-full items-center justify-center text-slate-400 font-medium">
                        Memuat Data Koreksi...
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 h-1.5 bg-slate-100 w-full">
                    <div id="slide-progress" class="h-full bg-red-500 w-0 transition-all duration-100 ease-linear"></div>
                </div>
            </div>
        </div>

        <!-- Right Column: Exam Committee (Panitia Ujian) (3 cols) -->
        <div class="col-span-3 bg-white rounded-3xl shadow-lg border border-slate-100 flex flex-col overflow-hidden">
            <div class="p-5 border-b border-amber-100 bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20 animate-float-1"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16 animate-float-2"></div>

                <h2 class="text-xl font-black text-white flex items-center gap-3 relative z-10">
                    <div class="p-2 bg-white/20 backdrop-blur-sm text-white rounded-xl shadow-lg animate-pulse">
                        <i class="ri-user-settings-line text-lg"></i>
                    </div>
                    <div class="leading-tight">
                        <div class="tracking-tight">Panitia Ujian</div>
                        <div class="text-[10px] font-normal text-amber-100 tracking-wide -mt-1">Petugas Pengawas & Panitia Aktif</div>
                    </div>
                </h2>
            </div>
            
            <div class="flex-1 overflow-hidden relative p-4 bg-slate-50/30">
                <div id="panitia-list" class="flex flex-col gap-3 transition-opacity duration-500">
                    <div class="flex items-center justify-center h-48 text-slate-400 font-medium">Memuat Panitia...</div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-20 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
            </div>
        </div>

    </main>

    <!-- Footer: Quote Rotator -->
    <footer class="rounded-2xl py-2 px-8 flex items-center justify-center min-h-[60px] relative overflow-hidden shadow-xl border border-white/20 bg-gradient-to-r from-red-600 via-rose-600 to-orange-600 bg-[length:200%_200%] animate-gradient-x text-white">
        <div id="quote-container" class="text-xl font-bold text-center transition-all duration-500 opacity-0 transform translate-y-2 text-white">
            <!-- Quote injected here -->
        </div>
    </footer>

    <!-- Audio Player -->
    <audio id="bgm" loop>
        <?php if (file_exists(__DIR__ . '/../../uploads/bgm.mp3')): ?>
        <source src="<?= url('/uploads/bgm.mp3') ?>" type="audio/mpeg">
        <?php endif; ?>
    </audio>

    <!-- YouTube Player Container (Hidden) -->
    <div id="youtube-player-container" style="position: absolute; left: -9999px; top: -9999px;">
        <div id="youtube-player"></div>
    </div>

    <!-- Music Control -->
    <button id="music-toggle" onclick="toggleMusic()"
        class="fixed bottom-6 right-6 p-4 bg-white/80 backdrop-blur-md rounded-full text-red-600 hover:bg-white hover:scale-110 hover:shadow-red-500/30 transition-all shadow-lg z-50 group border border-red-100">
        <i id="icon-play" class="ri-play-fill text-2xl"></i>
        <i id="icon-pause" class="ri-pause-fill text-2xl hidden"></i>
        <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-slate-800 text-white px-3 py-1 rounded-lg text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">
            Play Music
        </span>
    </button>

    <script>
        const API_URL = '<?= url('/api/tv-data') ?>?mode=exam';
        const SLIDE_DURATION = 10000;
        const ITEMS_PER_PAGE = 6;

        let appData = {
            mode: 'exam',
            session: null,
            panitia: [],
            correction_stats: { total: 0, belum: 0, proses: 0, selesai: 0, percent: 0 },
            exams_list: [],
            total_santri: 0,
            total_kelas: 0,
            total_pelajaran: 0,
            total_pengajar: 0,
            bgm_youtube: '',
            quotes: []
        };

        let activeSlides = [];
        let currentSlideIndex = 0;
        let slideInterval = null;
        let ytPlayer = null;
        let ytReady = false;
        let ytRequestedPlay = false;

        // Load YouTube IFrame API
        const tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        function extractYoutubeId(url) {
            if (!url) return null;
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }

        window.onYouTubeIframeAPIReady = function() {
            ytReady = true;
            if (appData.bgm_youtube) {
                const ytId = extractYoutubeId(appData.bgm_youtube);
                if (ytId) initYoutubePlayer(ytId);
            }
        }

        function initYoutubePlayer(videoId) {
            if (!ytReady || !videoId || ytPlayer) return;
            
            ytPlayer = new YT.Player('youtube-player', {
                height: '1',
                width: '1',
                videoId: videoId,
                host: 'https://www.youtube.com',
                playerVars: {
                    'autoplay': 0,
                    'controls': 0,
                    'loop': 1,
                    'playlist': videoId,
                    'mute': 0,
                    'enablejsapi': 1,
                    'origin': window.location.origin
                },
                events: {
                    'onReady': (event) => {
                         if (ytRequestedPlay) {
                             toggleMusic(true);
                         }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            initClock();
            fetchData();
        });

        let autoStartTimeout;

        function onDataReady() {
            const loader = document.getElementById('splash-loader');
            const btn = document.getElementById('splash-start-btn');

            if (loader) loader.classList.add('hidden');
            if (btn) {
                btn.classList.remove('hidden');
                btn.classList.add('animate-fade-in-down');
            }

            autoStartTimeout = setTimeout(() => {
                enterShowcase();
            }, 5000);
        }

        function enterShowcase() {
            if (autoStartTimeout) clearTimeout(autoStartTimeout);

            const splash = document.getElementById('splash-screen');
            if (splash) splash.classList.add('hidden');

            // Play BGM automatically
            toggleMusic(true);

            // Initialize slide loop
            startSlideShow();
            
            // Periodically refresh data
            setInterval(fetchData, 30000);
        }

        function toggleMusic(forcePlay = null) {
            const bgm = document.getElementById('bgm');
            const iconPlay = document.getElementById('icon-play');
            const iconPause = document.getElementById('icon-pause');
            const textSpan = document.querySelector('#music-toggle span');

            let isPlaying = forcePlay !== null ? !forcePlay : (iconPlay.classList.contains('hidden'));

            if (!isPlaying) {
                // Play
                iconPlay.classList.add('hidden');
                iconPause.classList.remove('hidden');
                if (textSpan) textSpan.textContent = 'Pause Music';
                
                if (ytPlayer && typeof ytPlayer.playVideo === 'function') {
                    ytPlayer.playVideo();
                } else if (bgm && bgm.play) {
                    bgm.play().catch(e => console.log("Audio play blocked by browser. User interaction required."));
                }
                ytRequestedPlay = true;
            } else {
                // Pause
                iconPlay.classList.remove('hidden');
                iconPause.classList.add('hidden');
                if (textSpan) textSpan.textContent = 'Play Music';

                if (ytPlayer && typeof ytPlayer.pauseVideo === 'function') {
                    ytPlayer.pauseVideo();
                } else if (bgm && bgm.pause) {
                    bgm.pause();
                }
                ytRequestedPlay = false;
            }
        }

        function initClock() {
            const dayMap = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
            const monthMap = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

            function updateTime() {
                const now = new Date();
                const hrs = String(now.getHours()).padStart(2, '0');
                const mins = String(now.getMinutes()).padStart(2, '0');
                const secs = String(now.getSeconds()).padStart(2, '0');

                updateDigit('clock-hours', hrs);
                updateDigit('clock-minutes', mins);
                updateDigit('clock-seconds', secs);

                const dayName = dayMap[now.getDay()];
                const dateStr = `${now.getDate()} ${monthMap[now.getMonth()]} ${now.getFullYear()}`;

                document.getElementById('clock-day').textContent = dayName;
                document.getElementById('clock-date-full').textContent = dateStr;
            }

            function updateDigit(id, val) {
                const el = document.getElementById(id);
                if (el.textContent !== val) {
                    el.textContent = val;
                }
            }

            updateTime();
            setInterval(updateTime, 1000);
        }

        function fetchData() {
            fetch(API_URL)
                .then(res => res.json())
                .then(data => {
                    appData = data;
                    updateUI();
                    
                    if (!ytPlayer && data.bgm_youtube) {
                        const ytId = extractYoutubeId(data.bgm_youtube);
                        if (ytId) initYoutubePlayer(ytId);
                    }

                    // If splash is showing, let them know data is ready
                    const splash = document.getElementById('splash-screen');
                    if (splash && !splash.classList.contains('hidden')) {
                        onDataReady();
                    }
                })
                .catch(err => console.error("Error loading showcase data:", err));
        }

        function updateUI() {
            // Update Stats Cards
            document.getElementById('stat-pelajaran').textContent = appData.total_pelajaran || 0;
            document.getElementById('stat-total-santri').textContent = appData.total_santri || 0;
            document.getElementById('stat-kelas').textContent = appData.total_kelas || 0;
            document.getElementById('stat-pengajar').textContent = appData.total_pengajar || 0;

            // Update Exam Session Card
            const sessionTitle = document.getElementById('exam-session-title');
            const sessionYear = document.getElementById('exam-session-year');
            if (appData.session) {
                const typeMap = {
                    'UUPT': 'Ulangan Umum Pertengahan Tahun',
                    'UPT': 'Ujian Pertengahan Tahun',
                    'UUAT': 'Ulangan Umum Akhir Tahun',
                    'UAT': 'Ujian Akhir Tahun'
                };
                const sessName = typeMap[appData.session.type] || appData.session.type;
                sessionTitle.textContent = sessName;
                sessionYear.textContent = appData.session.year || '-';
            } else {
                sessionTitle.textContent = 'Tidak Ada Ujian Aktif';
                sessionYear.textContent = '-';
            }

            // Update Correction stats
            const percent = appData.correction_stats.percent || 0;
            document.getElementById('correction-percent').textContent = `${percent}%`;
            document.getElementById('correction-progress-bar').style.width = `${percent}%`;
            document.getElementById('stat-total-koreksi').textContent = appData.correction_stats.total || 0;
            document.getElementById('stat-proses-koreksi').textContent = appData.correction_stats.proses || 0;
            document.getElementById('stat-selesai-koreksi').textContent = appData.correction_stats.selesai || 0;

            // Render Panitia List (supervision)
            renderPanitia();

            // Prepare slides for exam correction queue
            prepareSlides();
            
            // Quotes rotator init
            initQuotes();
        }

        function renderPanitia() {
            const list = document.getElementById('panitia-list');
            list.style.opacity = '0';

            setTimeout(() => {
                let html = '';
                if (appData.panitia.length === 0) {
                    html = '<div class="flex items-center justify-center h-48 text-slate-400 italic text-sm">Tidak ada panitia ujian terdaftar.</div>';
                } else {
                    appData.panitia.forEach(p => {
                        if (!p) return;
                        html += `
                            <div class="flex items-center gap-3 p-2.5 bg-white border border-slate-100 rounded-2xl shadow-sm hover:border-amber-200 transition-all duration-300">
                                <img src="${p.profile_picture}" alt="${p.nama_display}" 
                                     class="w-10 h-10 rounded-full object-cover border border-slate-200"
                                     onerror="this.src='https://ui-avatars.com/api/?name='+encodeURIComponent('${p.nama_display}')+'&background=F3F4F6&color=1F2937'">
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-extrabold text-slate-800 truncate">${p.nama_display}</div>
                                    <div class="text-[9px] font-bold text-slate-400 mt-0.5 uppercase tracking-wide flex items-center gap-1">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        ${p.badge_text || 'Panitia'}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                list.innerHTML = html;
                list.style.opacity = '1';
            }, 300);
        }

        function prepareSlides() {
            const list = appData.exams_list || [];
            activeSlides = [];

            if (list.length === 0) {
                activeSlides.push({
                    type: 'empty',
                    message: 'Belum ada jadwal koreksi ujian aktif.'
                });
                return;
            }

            // Chunk list into pages of 6 items
            const totalPages = Math.ceil(list.length / ITEMS_PER_PAGE);
            for (let i = 0; i < totalPages; i++) {
                const chunk = list.slice(i * ITEMS_PER_PAGE, (i + 1) * ITEMS_PER_PAGE);
                activeSlides.push({
                    type: 'exams',
                    page: i + 1,
                    totalPages: totalPages,
                    items: chunk
                });
            }
        }

        function startSlideShow() {
            if (slideInterval) clearInterval(slideInterval);
            
            renderCurrentSlide();

            slideInterval = setInterval(() => {
                if (activeSlides.length > 1) {
                    currentSlideIndex = (currentSlideIndex + 1) % activeSlides.length;
                    renderCurrentSlide();
                }
            }, SLIDE_DURATION);

            // Progress bar animation loop
            const progress = document.getElementById('slide-progress');
            let start = null;
            function step(timestamp) {
                if (!start) start = timestamp;
                let elapsed = timestamp - start;
                let pct = Math.min((elapsed / SLIDE_DURATION) * 100, 100);
                progress.style.width = pct + '%';
                if (elapsed >= SLIDE_DURATION) {
                    start = null;
                }
                requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        function renderCurrentSlide() {
            const container = document.getElementById('correction-container');
            const badge = document.getElementById('correction-page-badge');

            if (activeSlides.length === 0) return;

            const slide = activeSlides[currentSlideIndex];
            badge.textContent = slide.type === 'empty' ? 'Info' : `Halaman ${slide.page}/${slide.totalPages}`;

            container.style.opacity = '0';

            setTimeout(() => {
                let html = '';

                if (slide.type === 'empty') {
                    html = `
                        <div class="flex h-full w-full flex-col items-center justify-center text-slate-400 gap-4">
                            <i class="ri-article-line text-5xl opacity-30"></i>
                            <span class="text-2xl font-light">${slide.message}</span>
                        </div>
                    `;
                } else {
                    html = '<div class="grid grid-cols-2 gap-4 h-full content-start">';

                    slide.items.forEach(item => {
                        let statusText = '';
                        let statusColor = '';
                        let borderClass = 'border-slate-100 bg-white hover:border-red-200 hover:shadow-md';

                        if (item.status === 'selesai') {
                            statusText = '<i class="ri-checkbox-circle-fill text-[11px] mr-1"></i>Selesai';
                            statusColor = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                            borderClass = 'border-emerald-100 bg-emerald-50/10 hover:border-emerald-200 hover:shadow-md';
                        } else if (item.status === 'proses') {
                            statusText = '<i class="ri-time-fill text-[11px] mr-1 animate-spin"></i>Pemeriksaan';
                            statusColor = 'bg-amber-100 text-amber-800 border border-amber-200 pulse-proses';
                            borderClass = 'border-amber-100 bg-amber-50/10 hover:border-amber-200 hover:shadow-md';
                        } else {
                            statusText = '<i class="ri-alert-fill text-[11px] mr-1"></i>Belum Mulai';
                            statusColor = 'bg-slate-100 text-slate-700 border border-slate-200';
                        }

                        let oralBadge = '';
                        if (item.has_oral == 1) {
                            oralBadge = '<span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-indigo-50 border border-indigo-100 text-indigo-700 ml-1.5 uppercase tracking-wide">Tulis & Lisan</span>';
                        } else if (item.has_oral == 2) {
                            oralBadge = '<span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-pink-50 border border-pink-100 text-pink-700 ml-1.5 uppercase tracking-wide">Lisan</span>';
                        } else {
                            oralBadge = '<span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-slate-50 border border-slate-100 text-slate-700 ml-1.5 uppercase tracking-wide">Tulisan</span>';
                        }

                        const p = item.pengajar_profile || { nama_display: item.pengajar, profile_picture: '', badge_text: 'Ust' };
                        const profilePic = p.profile_picture || 'https://ui-avatars.com/api/?name='+encodeURIComponent(p.nama_display)+'&background=F3F4F6&color=1F2937';

                        html += `
                            <div class="flex flex-col justify-between p-4 rounded-3xl border ${borderClass} shadow-sm transition-all duration-300 h-28 relative">
                                <div class="flex items-start justify-between">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-black px-2 py-0.5 rounded-full bg-slate-900 text-white shadow-sm font-mono">${item.kelas}</span>
                                            ${oralBadge}
                                        </div>
                                        <h3 class="text-sm font-extrabold text-slate-800 truncate mt-1.5 leading-tight">${item.mapel}</h3>
                                        ${item.mapel_ar ? `<span class="text-xs text-slate-400 font-semibold arabic-text mt-0.5 block" dir="rtl">${item.mapel_ar}</span>` : ''}
                                    </div>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-50 pt-2 mt-auto">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <img src="${profilePic}" alt="${p.nama_display}" class="w-6 h-6 rounded-full object-cover border border-slate-100">
                                        <span class="text-[10px] font-bold text-slate-500 truncate max-w-[120px]">${p.nama_display}</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wide flex items-center ${statusColor}">
                                        ${statusText}
                                    </span>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                }

                container.innerHTML = html;
                container.style.opacity = '1';
            }, 300);
        }

        // Quotes rotator
        let currentQuoteIndex = 0;
        let quotesInterval = null;

        function initQuotes() {
            const list = appData.quotes || [];
            if (list.length === 0 || quotesInterval) return;

            const rotateQuote = () => {
                const quote = list[currentQuoteIndex];
                const container = document.getElementById('quote-container');

                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';

                setTimeout(() => {
                    container.textContent = quote;
                    container.style.opacity = '1';
                    container.style.transform = 'translateY(0)';
                    currentQuoteIndex = (currentQuoteIndex + 1) % list.length;
                }, 500);
            };

            rotateQuote();
            quotesInterval = setInterval(rotateQuote, 12000);
        }
    </script>
</body>

</html>
