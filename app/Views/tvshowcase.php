<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Showcase - KMI (Modern)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Modern Glass Clock */
        .glass-clock-block {
            background: rgba(15, 23, 42, 0.85);
            /* Dark Slate 900 */
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
            border-radius: 0.75rem;
            /* rounded-xl */
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', monospace;
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Shine effect */
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

        .card-gradient-1 {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
        }

        .card-gradient-2 {
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            color: white;
        }

        .card-gradient-3 {
            background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
            color: white;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        @keyframes fade-in-down {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-down {
            animation: fade-in-down 0.5s ease-out;
        }

        .fade-transition {
            transition: opacity 0.5s ease-in-out;
            opacity: 1;
        }

        .fade-out {
            opacity: 0;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
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
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes gradient-x {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .animate-gradient-x {
            animation: gradient-x 6s ease infinite;
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

        /* Floating animations for decorative circles */
        @keyframes float1 {

            0%,
            100% {
                transform: translate(0, 0);
            }

            33% {
                transform: translate(10px, -8px);
            }

            66% {
                transform: translate(-8px, 10px);
            }
        }

        @keyframes float2 {

            0%,
            100% {
                transform: translate(0, 0);
            }

            33% {
                transform: translate(-10px, 8px);
            }

            66% {
                transform: translate(8px, -10px);
            }
        }

        .animate-float-1 {
            animation: float1 8s ease-in-out infinite;
        }

        .animate-float-2 {
            animation: float2 10s ease-in-out infinite;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 1024px) {

            /* Tablet adjustments */
            main {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            .col-span-3,
            .col-span-6,
            .col-span-12 {
                grid-column: span 1 !important;
            }

            #schedule-carousel {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 768px) {

            /* Mobile phones */
            body {
                padding: 0.5rem !important;
            }

            /* Header adjustments */
            header {
                padding: 0.75rem !important;
                gap: 0.75rem !important;
            }

            /* Logo smaller */
            header img {
                max-width: 80px !important;
            }

            /* Clock compact */
            #clock-container {
                gap: 0.5rem !important;
            }

            .glass-clock-block {
                width: 2.5rem !important;
                height: 2.5rem !important;
                font-size: 1.25rem !important;
            }

            #clock-container span {
                font-size: 1.25rem !important;
            }

            /* Stats grid 2 columns on mobile */
            #stats-container {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.5rem !important;
            }

            .stats-card {
                padding: 0.75rem !important;
            }

            .stats-card .text-3xl {
                font-size: 1.5rem !important;
            }

            .stats-card .text-xs {
                font-size: 0.65rem !important;
            }

            /* Main content single column */
            main {
                gap: 0.75rem !important;
            }

            /* Schedule cards single column on small phones */
            #schedule-carousel {
                grid-template-columns: 1fr !important;
                gap: 0.75rem !important;
            }

            /* Headers compact */
            .p-5 {
                padding: 0.75rem !important;
            }

            .p-6 {
                padding: 1rem !important;
            }

            /* Smaller fonts in headers */
            h2 {
                font-size: 1rem !important;
            }

            h2 svg {
                width: 1rem !important;
                height: 1rem !important;
            }

            /* Hide decorative circles on mobile */
            .animate-float-1,
            .animate-float-2 {
                display: none !important;
            }

            /* Smaller profile pictures */
            img.w-10 {
                width: 2rem !important;
                height: 2rem !important;
            }

            img.w-12 {
                width: 2.5rem !important;
                height: 2.5rem !important;
            }
        }

        @media (max-width: 480px) {

            /* Extra small phones */
            #stats-container {
                grid-template-columns: 1fr !important;
            }

            /* Stack clock elements */
            #clock-container {
                flex-direction: column !important;
                align-items: center !important;
            }
        }

        /* Clock flip animation */
        .clock-digit {
            position: relative;
            display: inline-block;
            /* overflow: hidden; Removed to prevent clipping when static */
            height: 1em;
            line-height: 1em;
            padding: 0 2px;
            /* Add static padding */
        }

        .clock-digit-inner {
            display: inline-block;
            transition: transform 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .clock-digit.flip .clock-digit-inner {
            animation: slideUp 0.6s ease-in-out;
        }

        @keyframes slideUp {
            0% {
                transform: translateY(0);
                opacity: 1;
            }

            100% {
                transform: translateY(-100%);
                opacity: 0;
            }
        }
    </style>
</head>

<body class="h-screen flex flex-col p-6 gap-6 selection:bg-indigo-200 selection:text-indigo-900">

    <!-- Splash Screen -->
    <div id="splash-screen">
        <div class="flex flex-col items-center gap-6 animate-fade-in-down">
            <div
                class="bg-white p-6 rounded-3xl h-40 w-40 flex items-center justify-center shadow-[0_20px_50px_-12px_rgba(79,70,229,0.3)] mb-4">
                <img src="<?= url('/img/kmi.png') ?>" alt="Logo" class="h-32 w-auto object-contain">
            </div>

            <h1
                class="text-6xl font-black bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-violet-600 tracking-tight text-center">
                TV Showcase
            </h1>
            <p class="text-slate-500 text-xl font-medium tracking-wide text-center max-w-lg">
                Monitoring Kegiatan Belajar Mengajar<br>Pondok Modern Darussalam Bogor
            </p>
        </div>

        <div class="mt-16 h-16 flex items-center justify-center">
            <div id="splash-loader" class="splash-loader"></div>
            <button id="splash-start-btn" class="splash-btn hidden" onclick="enterShowcase()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Mulai Showcase</span>
            </button>
        </div>

        <div class="absolute bottom-8 text-slate-400 text-sm font-mono">
            v2.1.0 &copy; 2026 KMI Dev Team
        </div>
    </div>

    <!-- Top Bar: Logo + Clock + Stats Cards -->
    <header class="flex items-center gap-4">
        <!-- Logo Section -->
        <div class="flex items-center gap-3 flex-shrink-0">
            <img src="<?= url('/img/kmi.png') ?>" alt="Logo" class="h-12 w-auto object-contain drop-shadow-md"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
            <span class="text-indigo-600 font-bold text-2xl hidden">KMI</span>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">KMI App Showcase</h1>
                <p class="text-slate-500 text-sm font-medium">Monitoring Kegiatan Belajar Mengajar</p>
            </div>
        </div>

        <!-- Clock Section -->
        <div class="flex items-center gap-2 flex-shrink-0 ml-4">
            <!-- Hours -->
            <div class="glass-clock-block w-16 h-16">
                <span id="clock-hours" class="text-4xl font-bold tracking-tighter clock-digit">00</span>
            </div>

            <!-- Separator -->
            <div class="flex flex-col gap-1.5 opacity-60">
                <div class="w-1 h-1 bg-slate-800 rounded-full"></div>
                <div class="w-1 h-1 bg-slate-800 rounded-full"></div>
            </div>

            <!-- Minutes -->
            <div class="glass-clock-block w-16 h-16">
                <span id="clock-minutes" class="text-4xl font-bold tracking-tighter clock-digit">00</span>
            </div>

            <!-- Separator -->
            <div class="flex flex-col gap-1.5 opacity-60">
                <div class="w-1 h-1 bg-slate-800 rounded-full"></div>
                <div class="w-1 h-1 bg-slate-800 rounded-full"></div>
            </div>

            <!-- Seconds -->
            <div class="glass-clock-block w-16 h-16 relative">
                <span id="clock-seconds"
                    class="text-4xl font-bold tracking-tighter text-yellow-400 clock-digit">00</span>
            </div>

            <!-- Date (Compact) -->
            <div class="ml-2 pl-2 border-l border-slate-200 flex-shrink-0">
                <div id="clock-day" class="text-xs font-bold text-slate-700 uppercase tracking-wide leading-none">SENIN
                </div>
                <div id="clock-date-full" class="text-[10px] font-medium text-slate-500">1 Jan 2026</div>
            </div>
        </div>

        <!-- Stats Cards Row -->
        <div class="flex gap-2 ml-auto">
            <!-- Pelajaran Card -->
            <div
                class="bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl p-3 shadow-lg hover:shadow-xl hover:scale-105 transition-all w-28 h-28 flex flex-col justify-between">
                <div>
                    <div class="text-white/80 text-[9px] font-bold uppercase tracking-wider mb-0.5">Pelajaran</div>
                    <div id="stat-pelajaran" class="text-3xl font-black text-white">0</div>
                </div>
                <div class="flex justify-end">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Santri Card -->
            <div
                class="bg-gradient-to-br from-teal-400 to-cyan-500 rounded-2xl p-3 shadow-lg hover:shadow-xl hover:scale-105 transition-all w-28 h-28 flex flex-col justify-between">
                <div>
                    <div class="text-white/80 text-[9px] font-bold uppercase tracking-wider mb-0.5">Total Santri</div>
                    <div class="text-3xl font-black text-white">714</div>
                </div>
                <div class="flex justify-end">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Kelas Card -->
            <div
                class="bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl p-3 shadow-lg hover:shadow-xl hover:scale-105 transition-all w-28 h-28 flex flex-col justify-between">
                <div>
                    <div class="text-white/80 text-[9px] font-bold uppercase tracking-wider mb-0.5">Kelas</div>
                    <div id="stat-kelas" class="text-3xl font-black text-white">0</div>
                </div>
                <div class="flex justify-end">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pengajar Card -->
            <div
                class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-3 shadow-lg hover:shadow-xl hover:scale-105 transition-all w-28 h-28 flex flex-col justify-between">
                <div>
                    <div class="text-white/80 text-[9px] font-bold uppercase tracking-wider mb-0.5">Pengajar</div>
                    <div id="stat-pengajar" class="text-3xl font-black text-white">0</div>
                </div>
                <div class="flex justify-end">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </header>



    <!-- Main Content Grid -->
    <main class="flex-1 grid grid-cols-12 gap-6 min-h-0">

        <!-- Left Column: Rotating Widgets (3 cols) -->
        <div class="col-span-3 flex flex-col gap-6">
            <div class="card-gradient-1 rounded-3xl p-6 flex flex-col items-center justify-center flex-1 relative transition-all duration-500 overflow-hidden shadow-xl shadow-indigo-200"
                id="left-sidebar-content">
                <div class="flex h-full w-full items-center justify-center text-white/80">
                    Memuat Data...
                </div>
            </div>
        </div>

        <!-- Center Column: Schedule Carousel (6 cols) -->
        <div
            class="col-span-6 flex flex-col bg-white rounded-3xl shadow-lg border border-slate-100 overflow-hidden relative">
            <div
                class="p-5 border-b border-indigo-100 bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-600 relative overflow-hidden flex justify-between items-center">
                <!-- Decorative background elements -->
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20 animate-float-1">
                </div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16 animate-float-2">
                </div>

                <h2 class="text-xl font-black text-white flex items-center gap-3 relative z-10">
                    <div
                        class="p-2 bg-white/20 backdrop-blur-sm text-white rounded-xl shadow-lg transform transition-transform hover:scale-110 hover:rotate-12 duration-300 animate-pulse">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <div class="tracking-tight">Jadwal Pelajaran</div>
                        <div class="text-[10px] font-normal text-indigo-100 tracking-wide -mt-1">Monitoring Proses
                            Belajar Mengajar</div>
                    </div>
                </h2>
                <div id="schedule-hour-badge"
                    class="px-4 py-1.5 rounded-full bg-white/20 backdrop-blur-sm text-white font-bold text-sm shadow-lg border border-white/30 relative z-10">
                    Jam Ke-1
                </div>
            </div>

            <div
                class="flex-1 p-6 overflow-hidden relative bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9IiNlMmU4ZjAiLz48L3N2Zz4=')]">
                <div id="schedule-container" class="h-full w-full transition-opacity duration-500">
                    <div class="flex h-full w-full items-center justify-center text-slate-400 font-medium">
                        Memuat Jadwal...
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 h-1.5 bg-slate-100 w-full">
                    <div id="slide-progress" class="h-full bg-indigo-500 w-0 transition-all duration-100 ease-linear">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Latest Updates (3 cols) -->
        <div class="col-span-3 bg-white rounded-3xl shadow-lg border border-slate-100 flex flex-col overflow-hidden">
            <div
                class="p-5 border-b border-emerald-100 bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 relative overflow-hidden">
                <!-- Decorative background elements -->
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20 animate-float-1">
                </div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16 animate-float-2">
                </div>

                <h2 class="text-xl font-black text-white flex items-center gap-3 relative z-10">
                    <div
                        class="p-2 bg-white/20 backdrop-blur-sm text-white rounded-xl shadow-lg transform transition-transform hover:scale-110 hover:rotate-12 duration-300 animate-pulse">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <div class="tracking-tight">Tanqih Terbaru</div>
                        <div class="text-[10px] font-normal text-emerald-100 tracking-wide -mt-1">Verifikasi Tanqih Idad
                            Real-time</div>
                    </div>
                </h2>
            </div>
            <div class="flex-1 overflow-hidden relative p-4 bg-slate-50/30"
                style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;20&quot; height=&quot;20&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Ccircle cx=&quot;2&quot; cy=&quot;2&quot; r=&quot;1&quot; fill=&quot;%2310b981&quot; fill-opacity=&quot;0.15&quot;/%3E%3C/svg%3E');">
                <div id="latest-list" class="flex flex-col gap-0">
                    <!-- Items inserted here -->
                </div>
                <div
                    class="absolute bottom-0 left-0 w-full h-24 bg-gradient-to-t from-white to-transparent pointer-events-none">
                </div>
            </div>
        </div>

    </main>

    <!-- Footer: Quote Rotator -->
    <footer
        class="rounded-2xl py-2 px-8 flex items-center justify-center min-h-[60px] relative overflow-hidden shadow-xl border border-white/20 bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-600 bg-[length:200%_200%] animate-gradient-x text-white">
        <div id="quote-container"
            class="text-xl font-bold text-center transition-all duration-500 opacity-0 transform translate-y-2 text-indigo-900">
            <!-- Quote injected here -->
        </div>
    </footer>

    <!-- Audio Player -->
    <audio id="bgm" loop>
        <source src="<?= url('/sound/bgm.mp3') ?>" type="audio/mpeg">
    </audio>

    <!-- Music Control -->
    <button id="music-toggle" onclick="toggleMusic()"
        class="fixed bottom-6 right-6 p-4 bg-white/80 backdrop-blur-md rounded-full text-indigo-600 hover:bg-white hover:scale-110 hover:shadow-indigo-500/30 transition-all shadow-lg z-50 group border border-indigo-100">
        <svg id="icon-play" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
            </path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>

        <svg id="icon-pause" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z">
            </path>
        </svg>

        <span
            class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-slate-800 text-white px-3 py-1 rounded-lg text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">
            Play Music
        </span>
    </button>

    <script>
        const API_URL = '<?= url('/api/tv-data') ?>';
        const SLIDE_DURATION = 10000;
        const ITEMS_PER_PAGE = 6;

        let appData = {
            schedule: {},
            activeSlides: [],
            currentSlideIndex: 0,
            stats: null,
            latest: [],
            piket: { syeikh: [], keliling: [] }
        };

        let slideInterval = null;
        let sidebarWidgetIndex = 1; // Start with Syeikh Diwan (skip stats at 0)
        let sidebarInterval = null;

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
            if (splash) {
                splash.classList.add('hidden');
            }

            startDataFetcher();
            startCarousel();
            startSidebarRotation();
            setTimeout(startQuoteRotator, 1000);
            toggleMusic(true);
        }

        function getWIBDate() {
            return new Date(new Date().toLocaleString("en-US", { timeZone: "Asia/Jakarta" }));
        }

        function getCurrentHourBlock() {
            const now = getWIBDate();
            const h = now.getHours();
            const m = now.getMinutes();
            const time = h + (m / 60);

            // Jam 1: 07:00 - 07:45
            if (time >= 7.0 && time < 7.75) return 1;

            // Jam 2: 07:45 - 08:30
            if (time >= 7.75 && time < 8.5) return 2;

            // Istirahat Pertama: 08:30 - 09:00 (return null to show no schedule)
            if (time >= 8.5 && time < 9.0) return null;

            // Jam 3: 09:00 - 09:45
            if (time >= 9.0 && time < 9.75) return 3;

            // Jam 4: 09:45 - 10:30
            if (time >= 9.75 && time < 10.5) return 4;

            // Istirahat Kedua: 10:30 - 11:00 (return null)
            if (time >= 10.5 && time < 11.0) return null;

            // Jam 5: 11:00 - 11:45
            if (time >= 11.0 && time < 11.75) return 5;

            // Jam 6: 11:45 - 12:30
            if (time >= 11.75 && time < 12.5) return 6;

            // Istirahat Sholat Dzuhur & Makan Siang: 12:30 - 14:00 (return null)
            if (time >= 12.5 && time < 14.0) return null;

            // Jam 7: 14:00 - 14:45
            if (time >= 14.0 && time < 14.75) return 7;

            // Allow URL parameter override for testing
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('hour')) return parseInt(urlParams.get('hour'));

            return null; // Outside class hours
        }

        function processSlides(scheduleData) {
            const activeHour = getCurrentHourBlock();
            const slides = [];

            // Check if currently in break time
            const now = getWIBDate();
            const h = now.getHours();
            const m = now.getMinutes();
            const time = h + (m / 60);

            let breakType = null;
            if (time >= 8.5 && time < 9.0) {
                breakType = 'istirahat1';
            } else if (time >= 10.5 && time < 11.0) {
                breakType = 'istirahat2';
            } else if (time >= 12.5 && time < 14.0) {
                breakType = 'dzuhur';
            }

            // If break time, add break slide
            if (breakType) {
                slides.push({
                    type: 'break',
                    breakType: breakType
                });
                return slides;
            }

            if (activeHour && scheduleData[activeHour]) {
                const items = scheduleData[activeHour];
                const groups = {};
                items.forEach(item => {
                    const match = item.kelas.match(/^(\d+)/);
                    const prefix = match ? match[1] : 'Others';
                    if (!groups[prefix]) groups[prefix] = [];
                    groups[prefix].push(item);
                });

                const prefixes = Object.keys(groups).sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));

                prefixes.forEach(prefix => {
                    const groupItems = groups[prefix];
                    const totalPages = Math.ceil(groupItems.length / ITEMS_PER_PAGE);

                    for (let i = 0; i < totalPages; i++) {
                        slides.push({
                            type: 'schedule',
                            hour: activeHour,
                            items: groupItems.slice(i * ITEMS_PER_PAGE, (i + 1) * ITEMS_PER_PAGE),
                            page: i + 1,
                            totalPages: totalPages,
                            grade: prefix
                        });
                    }
                });
            }

            if (slides.length === 0) {
                slides.push({
                    type: 'empty',
                    message: activeHour ? 'Tidak ada data untuk Jam Ke-' + activeHour : 'Tidak ada kegiatan KBM saat ini'
                });
            }

            return slides;
        }

        let carouselTimeout;
        let fetchTimeout;
        let sidebarTimeout;

        function startCarousel() {
            if (typeof slideInterval !== 'undefined' && slideInterval) clearInterval(slideInterval);
            if (carouselTimeout) clearTimeout(carouselTimeout);
            runCarouselLoop();
        }

        function runCarouselLoop() {
            const variance = (Math.random() * 6000 - 3000);
            const duration = SLIDE_DURATION + variance;

            const progressBar = document.getElementById('slide-progress');
            let startTime = Date.now();

            const animateProgress = () => {
                const elapsed = Date.now() - startTime;
                const pct = Math.min((elapsed / duration) * 100, 100);
                if (progressBar) progressBar.style.width = pct + '%';

                if (elapsed < duration) {
                    requestAnimationFrame(animateProgress);
                }
            };
            animateProgress();

            carouselTimeout = setTimeout(() => {
                nextSlide();
                runCarouselLoop();
            }, duration);
        }

        function startDataFetcher() {
            if (fetchTimeout) clearTimeout(fetchTimeout);
            scheduleNextFetch();
        }

        function scheduleNextFetch() {
            const duration = 8000 + (Math.random() * 4000 - 2000);
            fetchTimeout = setTimeout(async () => {
                await fetchData();
                scheduleNextFetch();
            }, duration);
        }

        function nextSlide() {
            if (appData.activeSlides.length === 0) return;
            appData.currentSlideIndex = (appData.currentSlideIndex + 1) % appData.activeSlides.length;
            renderCurrentSlide();
        }

        function startSidebarRotation() {
            if (sidebarTimeout) clearTimeout(sidebarTimeout);
            scheduleNextSidebarRotation();
        }

        function scheduleNextSidebarRotation() {
            let duration = 12000;
            if (sidebarWidgetIndex === 1) duration = 18000;
            if (sidebarWidgetIndex === 2) duration = 15000;
            duration += (Math.random() * 4000 - 2000);

            sidebarTimeout = setTimeout(() => {
                const container = document.getElementById('left-sidebar-content');
                if (container) {
                    container.classList.add('fade-out');
                    setTimeout(() => {
                        // Toggle between 1 (Syeikh Diwan) and 2 (Piket Keliling)
                        // If current is 1, go to 2. If 2, go back to 1.
                        sidebarWidgetIndex = sidebarWidgetIndex === 1 ? 2 : 1;

                        renderSidebarWidget();
                        container.classList.remove('fade-out');
                        scheduleNextSidebarRotation();
                    }, 500);
                } else {
                    scheduleNextSidebarRotation();
                }
            }, duration);
        }

        async function fetchData() {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000);

            try {
                const res = await fetch(API_URL, { signal: controller.signal });
                clearTimeout(timeoutId);
                const data = await res.json();

                appData.stats = data.stats;
                appData.latest = data.latest_verifications;
                appData.schedule = data.schedule_by_hour;
                appData.piket = data.piket || { syeikh: [], keliling: [] };

                const newSlides = processSlides(data.schedule_by_hour);
                appData.activeSlides = newSlides;

                if (appData.currentSlideIndex >= appData.activeSlides.length) {
                    appData.currentSlideIndex = 0;
                }

                updateUI(data);

                if (!appData.initLoaded) {
                    appData.initLoaded = true;
                    onDataReady();
                }

            } catch (err) {
                clearTimeout(timeoutId);
                console.error("Failed to fetch data", err);
            }
        }

        function updateUI(data) {
            renderLatest(data.latest_verifications);
            updateStatsCards();
            appData.quotes = data.quotes || [];
        }

        function updateStatsCards() {
            const activeHour = getCurrentHourBlock();
            let activePelajaran = 0;
            let uniqueKelas = new Set();
            let uniquePengajar = new Set();

            if (activeHour && appData.schedule[activeHour]) {
                const items = appData.schedule[activeHour];
                activePelajaran = items.length;
                items.forEach(item => {
                    if (item.kelas) uniqueKelas.add(item.kelas);
                    if (item.pengajar) uniquePengajar.add(item.pengajar);
                });
            } else {
                // Fallback to total for the day
                Object.values(appData.schedule).flat().forEach(item => {
                    if (item.kelas) uniqueKelas.add(item.kelas);
                    if (item.pengajar) uniquePengajar.add(item.pengajar);
                });
                activePelajaran = Object.values(appData.schedule).flat().length;
            }

            const elPelajaran = document.getElementById('stat-pelajaran');
            const elKelas = document.getElementById('stat-kelas');
            const elPengajar = document.getElementById('stat-pengajar');

            if (elPelajaran) elPelajaran.textContent = activePelajaran;
            if (elKelas) elKelas.textContent = uniqueKelas.size;
            if (elPengajar) elPengajar.textContent = uniquePengajar.size;
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }




        function renderCurrentSlide() {
            const container = document.getElementById('schedule-container');
            const badge = document.getElementById('schedule-hour-badge');

            if (appData.activeSlides.length === 0) return;

            const slide = appData.activeSlides[appData.currentSlideIndex];

            if (slide.type === 'schedule') {
                const gradeLabel = slide.grade ? ` â€¢ Kelas ${slide.grade}` : '';
                badge.textContent = `Jam Ke-${slide.hour}${gradeLabel} (${slide.page}/${slide.totalPages})`;
            } else if (slide.type === 'break') {
                // Update badge for break time
                if (slide.breakType === 'istirahat1') {
                    badge.textContent = 'Istirahat I (08:30 - 09:00)';
                } else if (slide.breakType === 'istirahat2') {
                    badge.textContent = 'Istirahat II (10:30 - 11:00)';
                } else if (slide.breakType === 'dzuhur') {
                    badge.textContent = 'Istirahat Dzuhur (12:30 - 14:00)';
                }
            } else {
                badge.textContent = 'Info';
            }

            container.style.opacity = '0';

            setTimeout(() => {
                let html = '';

                if (slide.type === 'break') {
                    // Different messages for different break types
                    let breakIcon = '';
                    let breakMessage = '';
                    let breakSubmessage = '';

                    if (slide.breakType === 'istirahat1') {
                        breakIcon = `<svg class="w-20 h-20 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>`;
                        breakMessage = 'Waktu Istirahat I';
                        breakSubmessage = '30 Menit â€¢ 08:30 - 09:00';
                    } else if (slide.breakType === 'istirahat2') {
                        breakIcon = `<svg class="w-20 h-20 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>`;
                        breakMessage = 'Waktu Istirahat II';
                        breakSubmessage = '30 Menit â€¢ 10:30 - 11:00';
                    } else if (slide.breakType === 'dzuhur') {
                        breakIcon = `<svg class="w-20 h-20 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>`;
                        breakMessage = 'Waktu Istirahat Dzuhur & Makan Siang';
                        breakSubmessage = '90 Menit â€¢ 12:30 - 14:00';
                    }

                    html = `<div class="flex h-full w-full flex-col items-center justify-center gap-6">
                        <div class="animate-pulse">${breakIcon}</div>
                        <div class="text-center">
                            <div class="text-3xl font-black text-slate-700 mb-2">${breakMessage}</div>
                            <div class="text-lg font-medium text-slate-500">${breakSubmessage}</div>
                        </div>
                    </div>`;
                } else if (slide.type === 'empty') {
                    html = `<div class="flex h-full w-full flex-col items-center justify-center text-slate-400 gap-4">
                        <svg class="w-16 h-16 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <span class="text-2xl font-light">${slide.message}</span>
                    </div>`;
                } else {
                    html = '<div class="grid grid-cols-3 gap-4 h-full content-start">';

                    slide.items.forEach(item => {
                        let statusIcon = '';
                        let borderClass = 'border-slate-100 bg-white hover:border-indigo-200 hover:shadow-md';

                        // 1. Parse Class Type (Putra/Putri) - IMPROVED REGEX
                        let rawKelas = item.kelas;
                        let classType = 'campuran'; // Default
                        let cleanKelas = rawKelas;

                        // Match "PA+PI", "PA", "PI" with optional separators and brackets
                        // Examples: "1-B PA", "1 INT-PA+PI", "1-C (PA)"
                        if (/(?:-|\s+)?\(?PA\+PI\)?$/i.test(rawKelas)) {
                            classType = 'campuran';
                            cleanKelas = rawKelas.replace(/(?:-|\s+)?\(?PA\+PI\)?$/i, '').trim();
                        } else if (/(?:-|\s+)?\(?PA\)?$/i.test(rawKelas)) {
                            classType = 'putra';
                            cleanKelas = rawKelas.replace(/(?:-|\s+)?\(?PA\)?$/i, '').trim();
                        } else if (/(?:-|\s+)?\(?PI\)?$/i.test(rawKelas)) {
                            classType = 'putri';
                            cleanKelas = rawKelas.replace(/(?:-|\s+)?\(?PI\)?$/i, '').trim();
                        }

                        // 2. Define Background Icons (Muslim Boy/Girl Silhouettes)
                        let bgDecoration = '';
                        if (classType === 'putra') {
                            // Muslim Boy (Peci)
                            bgDecoration = `<div class="absolute -right-2 -bottom-6 opacity-[0.08] pointer-events-none transform rotate-6 transition-transform group-hover:scale-105 duration-500">
                                <svg class="w-28 h-28 text-blue-900" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C9 2 7 3.5 7 5C7 6 8 8 9 9C6 9.5 4 11 4 14V22H20V14C20 11 18 9.5 15 9C16 8 17 6 17 5C17 3.5 15 2 12 2M12 4C13.5 4 14.5 4.5 14.5 5.5C14.5 6.5 13.5 7 12 7C10.5 7 9.5 6.5 9.5 5.5C9.5 4.5 10.5 4 12 4M7 14C7 12.5 9 11 12 11C15 11 17 12.5 17 14V20H7V14Z"/>
                                </svg>
                            </div>`;
                            borderClass = 'border-blue-100 bg-gradient-to-br from-blue-50/80 to-white';
                        } else if (classType === 'putri') {
                            // Muslim Girl (Hijab)
                            bgDecoration = `<div class="absolute -right-2 -bottom-6 opacity-[0.08] pointer-events-none transform rotate-6 transition-transform group-hover:scale-105 duration-500">
                                <svg class="w-28 h-28 text-pink-900" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C8 2 5 5 5 9C5 11.5 6 13.5 8 14.5V22H16V14.5C18 13.5 19 11.5 19 9C19 5 16 2 12 2M12 4C14.5 4 16.5 6 16.5 9C16.5 11.5 14.5 13.5 12 13.5C9.5 13.5 7.5 11.5 7.5 9C7.5 6 9.5 4 12 4Z"/>
                                </svg>
                            </div>`;
                            borderClass = 'border-pink-100 bg-gradient-to-br from-pink-50/80 to-white';
                        } else {
                            // Group/Mixed
                            bgDecoration = `<div class="absolute -right-4 -bottom-4 opacity-[0.06] pointer-events-none transform rotate-12 transition-transform group-hover:scale-110 duration-500">
                                <svg class="w-24 h-24 text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                            </div>`;
                        }

                        if (item.status === 'substitute') {
                            borderClass = 'border-amber-200 bg-amber-50'; // Override for substitute
                            statusIcon = `<div class="bg-amber-100 p-1.5 rounded-full text-amber-600 shadow-sm z-10 relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>`;
                        } else if (item.verified) {
                            if (item.status === 'justified') {
                                borderClass = 'border-orange-200 bg-orange-50';
                                statusIcon = `<div class="bg-orange-100 p-1.5 rounded-full text-orange-600 shadow-sm z-10 relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>`;
                            } else {
                                // Verified but no special status override
                                statusIcon = `<div class="bg-emerald-100 p-1.5 rounded-full text-emerald-600 shadow-sm z-10 relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>`;
                            }
                        } else {
                            statusIcon = `<div class="bg-slate-100 p-1.5 rounded-full text-slate-400 shadow-sm z-10 relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>`;
                        }

                        html += `
                        <div class="rounded-2xl border ${borderClass} p-4 flex flex-col justify-between h-[180px] relative overflow-hidden group transition-all duration-300 shadow-sm hover:shadow-lg">
                            ${bgDecoration}
                            
                            <!-- Header: Class & Status -->
                            <div class="flex justify-between items-start gap-3 relative z-10">
                                <div class="flex-1 min-w-0">
                                    <div class="text-2xl font-black text-slate-800 uppercase tracking-tight mb-0.5 truncate">${escapeHtml(cleanKelas)}</div>
                                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest truncate w-full" title="${escapeHtml(item.mapel)}">${escapeHtml(item.mapel)}</div>
                                </div>
                                <div class="flex-shrink-0">${statusIcon}</div>
                            </div>
                            
                            <!-- Footer: Teacher Info -->
                            <div class="mt-2 flex items-center gap-3 relative z-10">
                                ${item.pengajar_profile ? `
                                    <img src="${escapeHtml(item.pengajar_profile.profile_picture)}" 
                                         alt="Photo" 
                                         class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-md flex-shrink-0"
                                         onerror="this.src='avatar.php?id=${escapeHtml(item.pengajar_profile.id)}'">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col justify-center">
                                            <!-- Badge Above Name -->
                                            <div class="mb-0.5">
                                                <span class="inline-block px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider ${item.pengajar_profile.badge_color === 'blue' ? 'bg-blue-100 text-blue-700' :
                                    item.pengajar_profile.badge_color === 'pink' ? 'bg-pink-100 text-pink-700' :
                                        'bg-slate-100 text-slate-500'
                                }">
                                                    ${item.pengajar_profile.badge_text || 'UST/USTZH'}
                                                </span>
                                            </div>
                                            
                                            <!-- Name -->
                                            <div class="text-sm font-bold text-slate-700 leading-snug truncate">
                                                ${escapeHtml(item.pengajar_profile.nama_display)}
                                            </div>
                                        </div>
                                    </div>
                                ` : `
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-bold text-slate-600 leading-snug line-clamp-2" title="${escapeHtml(item.pengajar)}">
                                            ${escapeHtml(item.pengajar)}
                                        </div>
                                    </div>
                                `}
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

        function renderLatest(list) {
            const container = document.getElementById('latest-list');
            if (!container) return;

            if (!list || list.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-40 text-slate-400/60">
                        <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <span class="text-sm">Belum ada data terbaru</span>
                    </div>
                `;
                return;
            }

            let html = '';
            list.forEach((item, index) => {
                const delay = index * 75;
                const statusColor = item.status === 'justified' ? 'border-amber-400' : 'border-emerald-500';

                html += `
                <div class="p-1 border-l-4 ${statusColor} flex gap-2 items-center animate-fade-in-down mb-2" style="animation-delay: ${delay}ms; animation-fill-mode: both;">
                    ${item.pengajar_profile ? `
                        <!-- Profile Picture -->
                        <img src="${escapeHtml(item.pengajar_profile.profile_picture)}" 
                             alt="Photo" 
                             class="w-10 h-10 rounded-full object-cover border-2 border-slate-200 flex-shrink-0"
                             onerror="this.src='avatar.php?id=${escapeHtml(item.pengajar_profile.id)}'">
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <!-- Badge above name -->
                            ${item.pengajar_profile.gender ? `
                                <div class="mb-0.5">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wide ${item.pengajar_profile.gender === 'Laki-laki'
                                ? 'bg-blue-100 text-blue-700'
                                : item.pengajar_profile.gender === 'Perempuan'
                                    ? 'bg-pink-100 text-pink-700'
                                    : 'bg-gray-100 text-gray-600'
                            }">
                                        ${item.pengajar_profile.gender === 'Laki-laki' ? 'Ustadz' : item.pengajar_profile.gender === 'Perempuan' ? 'Ustadzah' : 'Ust/Ustzh'}
                                    </span>
                                </div>
                            ` : `
                                <div class="mb-0.5">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wide bg-gray-100 text-gray-600">
                                        Ust/Ustzh
                                    </span>
                                </div>
                            `}
                            
                            <!-- Name -->
                            <div class="font-bold text-slate-800 text-sm truncate mb-0.5">
                                ${escapeHtml(item.pengajar_profile.nama_display)}
                            </div>
                            
                            <!-- Subject and Class -->
                            <div class="text-xs text-slate-500 font-medium">
                                ${escapeHtml(item.mapel)} <span class="text-slate-300">â€¢</span> ${escapeHtml(item.kelas)}
                            </div>
                        </div>
                        
                        <!-- Time and Verifier (Right Column) -->
                        <div class="flex-shrink-0 flex flex-col items-end gap-0.5">
                            <div class="text-[9px] font-mono font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded-md">
                                ${item.time_formatted}
                            </div>
                            ${item.verifier && item.verifier !== '-' ? `
                                <div class="text-[9px] text-slate-400 flex items-center gap-0.5">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    ${escapeHtml(item.verifier)}
                                </div>
                            ` : ''}
                        </div>
                    ` : `
                        <!-- Fallback if no profile -->
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-800 text-sm truncate">${escapeHtml(item.pengajar)}</div>
                            <div class="text-xs text-slate-500 mt-0.5 font-medium">${escapeHtml(item.mapel)} <span class="text-slate-300">â€¢</span> ${escapeHtml(item.kelas)}</div>
                            ${item.verifier && item.verifier !== '-' ? `<div class="text-[10px] text-slate-400 mt-1 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> by ${escapeHtml(item.verifier)}</div>` : ''}
                        </div>
                        <div class="shrink-0">
                            <div class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-md">
                                ${item.time_formatted}
                            </div>
                        </div>
                    `}
                </div>
                `;
            });
            container.innerHTML = html;
        }



        function renderSidebarWidget() {
            const container = document.getElementById('left-sidebar-content');
            if (!container) return; // Guard

            let html = '';

            // Common List Item Renderer based on renderLatest style
            const renderItem = (item, borderColorClass) => {
                // Determine Badge Logic
                let badgeText = 'Ust/Ustzh';
                let badgeClass = 'bg-gray-100 text-gray-600';

                if (item.gender === 'Laki-laki') {
                    badgeText = 'Ustadz';
                    badgeClass = 'bg-blue-100 text-blue-700';
                } else if (item.gender === 'Perempuan') {
                    badgeText = 'Ustadzah';
                    badgeClass = 'bg-pink-100 text-pink-700';
                } else {
                    // Regex Fallback if API didn't return gender but name has prefix
                    if (item.nama_display && item.nama_display.match(/\b(Ustadzah|Al-Ustadzah)\b/i)) {
                        badgeText = 'Ustadzah';
                        badgeClass = 'bg-pink-100 text-pink-700';
                    } else if (item.nama_display && item.nama_display.match(/\b(Ustadz|Al-Ustadz)\b/i)) {
                        badgeText = 'Ustadz';
                        badgeClass = 'bg-blue-100 text-blue-700';
                    }
                }

                // Profile Pic
                const profilePic = item.profile_picture || `avatar.php?name=${encodeURIComponent(item.nama || item.nama_display)}`;

                return `
                <div class="p-1 flex gap-2 items-center mb-2 rounded-r-lg">
                    <!-- Profile Picture -->
                    <img src="${escapeHtml(profilePic)}" 
                         alt="Photo" 
                         class="w-10 h-10 rounded-full object-cover border-2 border-slate-200 flex-shrink-0"
                         onerror="this.src='avatar.php?name=${escapeHtml(item.nama_display)}'">
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Badge -->
                        <div class="mb-0.5">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wide ${badgeClass}">
                                ${badgeText}
                            </span>
                        </div>
                        
                        <!-- Name -->
                        <div class="font-bold text-slate-100 text-sm truncate mb-0.5">
                            ${escapeHtml(item.nama_display || item.nama)}
                        </div>
                    </div>
                </div>
                `;
            };


            if (sidebarWidgetIndex === 0) {
                // --- STATS VIEW (Keep as is, though currently index 0 is skipped in rotation logic, kept for safety) ---
                container.className = "card-gradient-1 rounded-3xl p-6 flex flex-col items-center justify-center flex-1 relative transition-all duration-500 overflow-hidden shadow-2xl shadow-purple-500/20";

                html = `
                    <div class="flex flex-col h-full w-full items-center justify-center fade-transition">
                        <h3 class="text-xl font-bold mb-4 text-slate-100 tracking-wide text-center">Status Tanqih</h3>
                        <div style="width: 150px; height: 150px;" class="mb-4">
                            <canvas id="statsChart"></canvas>
                        </div>
                         <div class="text-center">
                            <span id="stat-percent" class="text-4xl font-extrabold text-white">0%</span>
                            <div class="text-xs text-slate-300 uppercase">Selesai</div>
                        </div>
                    </div>
                `;
                container.innerHTML = html;
                // Removed call to renderStatsChart as it's missing/unused
                // setTimeout(() => { renderStatsChart(appData.stats); }, 50);

            } else if (sidebarWidgetIndex === 1) {
                // --- SYEIKH DIWAN VIEW ---
                container.className = "card-gradient-2 rounded-3xl p-6 flex flex-col flex-1 relative transition-all duration-500 overflow-hidden shadow-2xl shadow-blue-500/20";

                const list = (appData.piket?.syeikh || []).map(item => renderItem(item, 'border-indigo-500')).join('');

                html = `
                    <!-- Decorative Animations (Solid/High Visibility) -->
                    <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/30 rounded-full blur-sm animate-float-1 pointer-events-none"></div>
                    <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-white/20 rounded-full blur-sm animate-float-2 pointer-events-none"></div>

                    <div class="flex flex-col h-full fade-transition relative z-10">
                        <div class="flex items-center gap-3 mb-4 border-b border-indigo-500/30 pb-3">
                             <span class="p-2 bg-indigo-500/20 rounded-xl">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                             </span>
                             <h3 class="text-xl font-bold text-white tracking-wide">Syeikh Diwan</h3>
                        </div>
                        <div class="flex-1 overflow-y-auto px-1 no-scrollbar">
                            <div class="flex flex-col">
                                ${list || '<div class="text-center text-blue-100/80 italic py-10">Belum ada data Syeikh Diwan</div>'}
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML = html;

            } else if (sidebarWidgetIndex === 2) {
                // --- PIKET KELILING VIEW ---
                container.className = "card-gradient-3 rounded-3xl p-6 flex flex-col flex-1 relative transition-all duration-500 overflow-hidden shadow-2xl shadow-emerald-500/20";

                const list = (appData.piket?.keliling || []).map(item => renderItem(item, 'border-teal-500')).join('');

                html = `
                    <!-- Decorative Animations (Solid/High Visibility) -->
                    <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/30 rounded-full blur-sm animate-float-1 pointer-events-none"></div>
                    <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-white/20 rounded-full blur-sm animate-float-2 pointer-events-none"></div>

                    <div class="flex flex-col h-full fade-transition relative z-10">
                        <div class="flex items-center gap-3 mb-4 border-b border-teal-500/30 pb-3">
                            <span class="p-2 bg-teal-500/20 rounded-xl">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            <h3 class="text-xl font-bold text-white tracking-wide">Piket Keliling</h3>
                        </div>
                        <div class="flex-1 overflow-y-auto px-1 no-scrollbar">
                             <div class="flex flex-col">
                                ${list || '<div class="text-center text-emerald-100/80 italic py-10">Belum ada data Piket Keliling</div>'}
                             </div>
                        </div>
                    </div>
                `;
                container.innerHTML = html;
            }
        }

        let quoteInterval;
        let currentQuoteIndex = 0;

        function startQuoteRotator() {
            if (quoteInterval) clearInterval(quoteInterval);
            showNextQuote();
            quoteInterval = setInterval(showNextQuote, 8000);
        }

        function showNextQuote() {
            const container = document.getElementById('quote-container');
            const quotes = appData.quotes || [];

            if (quotes.length === 0) return;

            container.classList.remove('opacity-100', 'translate-y-0');
            container.classList.add('opacity-0', 'translate-y-2');

            setTimeout(() => {
                const text = quotes[currentQuoteIndex];
                container.textContent = text;

                const arabicRegex = /[\u0600-\u06FF]/;
                const isArabic = arabicRegex.test(text);

                // Reset classes with common base
                container.className = "text-center transition-all duration-500 opacity-0 transform translate-y-2 leading-relaxed drop-shadow-md px-4 text-white relative z-10";

                if (isArabic) {
                    container.style.fontFamily = "'Noto Naskh Arabic', serif";
                    container.style.direction = "rtl";
                    // White Text for Arabic
                    container.classList.add('text-2xl', 'font-bold');
                } else {
                    container.style.fontFamily = "'Outfit', sans-serif";
                    container.style.direction = "ltr";
                    // White Text for Latin
                    container.classList.add('text-lg', 'font-bold');
                }

                requestAnimationFrame(() => {
                    container.classList.remove('opacity-0', 'translate-y-2');
                    container.classList.add('opacity-100', 'translate-y-0');
                });

                currentQuoteIndex = (currentQuoteIndex + 1) % quotes.length;
            }, 500);
        }

        function initClock() {
            const elHours = document.getElementById('clock-hours');
            const elMinutes = document.getElementById('clock-minutes');
            const elSeconds = document.getElementById('clock-seconds');
            const elDay = document.getElementById('clock-day');
            const elDateFull = document.getElementById('clock-date-full');

            // Function to update digit with odometer animation
            // Function to update digit with odometer animation
            function updateDigit(element, newValue) {
                if (!element) return;

                const currentValue = element.textContent.trim();
                if (currentValue === newValue && element.children.length === 0) return;

                // Force container style
                element.style.position = 'relative';
                element.style.display = 'inline-block';
                element.style.overflow = 'hidden';
                element.style.verticalAlign = 'bottom';

                const oldDiv = document.createElement('div');
                oldDiv.style.cssText = 'position:absolute;top:0;left:0;right:0;height:100%;display:flex;align-items:center;justify-content:center;will-change:transform,opacity;';
                oldDiv.textContent = currentValue;

                const newDiv = document.createElement('div');
                newDiv.style.cssText = 'position:absolute;top:100%;left:0;right:0;height:100%;display:flex;align-items:center;justify-content:center;will-change:transform;';
                newDiv.textContent = newValue;

                element.innerHTML = '';
                element.appendChild(oldDiv);
                element.appendChild(newDiv);

                // Trigger reflow
                oldDiv.getBoundingClientRect();

                // Start animation in next frame
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        // Set transition
                        oldDiv.style.transition = 'transform 0.5s ease-in-out, opacity 0.5s ease-in-out';
                        newDiv.style.transition = 'transform 0.5s ease-in-out';

                        // Apply transform
                        oldDiv.style.transform = 'translateY(-100%)';
                        oldDiv.style.opacity = '0';
                        newDiv.style.transform = 'translateY(-100%)';
                    });
                });

                // Cleanup
                setTimeout(() => {
                    element.innerHTML = newValue;
                    element.style.position = '';
                    element.style.overflow = '';
                    element.style.verticalAlign = '';

                    // Reset any inline styles that might interfere
                    if (element.children.length === 0) {
                        element.textContent = newValue;
                    }
                }, 550); // Slightly longer than transition
            }

            // Corrected Odometer Animation Function
            function updateClockDigit(element, newValue) {
                if (!element) return;

                const currentValue = element.textContent.trim();
                // Check if value actually changed and we are not currently animating (no children)
                if (currentValue === newValue && element.children.length === 0) return;

                // Get current dimensions to prevent collapse
                const rect = element.getBoundingClientRect();
                const width = rect.width;
                const height = rect.height;

                // Force container style
                element.style.position = 'relative';
                // Use inline-flex to keep strict sizing if needed, but inline-block + specific width/height is safer
                element.style.display = 'inline-block';
                // Add padding to prevent clipping (2px left/right)
                element.style.width = width ? `${width + 4}px` : 'auto';
                element.style.height = height ? `${height}px` : 'auto';
                element.style.overflow = 'hidden';
                element.style.verticalAlign = 'bottom';

                const commonStyle = 'position:absolute;left:0;right:0;height:100%;display:flex;align-items:center;justify-content:center;will-change:transform;';

                const oldDiv = document.createElement('div');
                oldDiv.style.cssText = `${commonStyle}top:0;`;
                oldDiv.textContent = currentValue;

                const newDiv = document.createElement('div');
                newDiv.style.cssText = `${commonStyle}top:100%;`;
                newDiv.textContent = newValue;

                element.innerHTML = '';
                element.appendChild(oldDiv);
                element.appendChild(newDiv);

                // Trigger reflow
                oldDiv.getBoundingClientRect();

                // Start animation
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        // Set transition
                        oldDiv.style.transition = 'transform 0.6s ease-in-out, opacity 0.6s ease-in-out';
                        newDiv.style.transition = 'transform 0.6s ease-in-out';

                        // Apply transform
                        oldDiv.style.transform = 'translateY(-100%)';
                        oldDiv.style.opacity = '0';
                        newDiv.style.transform = 'translateY(-100%)';
                    });
                });

                // Cleanup
                setTimeout(() => {
                    element.innerHTML = newValue;
                    element.style.position = '';
                    element.style.display = '';
                    element.style.width = '';
                    element.style.height = '';
                    element.style.overflow = '';
                    element.style.verticalAlign = '';

                    if (element.children.length === 0) {
                        element.textContent = newValue;
                    }
                }, 650);
            }

            const update = () => {
                const now = getWIBDate();
                const h = String(now.getHours()).padStart(2, '0');
                const m = String(now.getMinutes()).padStart(2, '0');
                const s = String(now.getSeconds()).padStart(2, '0');

                updateClockDigit(elHours, h);
                updateClockDigit(elMinutes, m);
                updateClockDigit(elSeconds, s);

                // Date - 'now' is already WIB, so treat it as local
                const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                const dateParts = now.toLocaleDateString('id-ID', options).split(','); // "Senin, 1 Januari 2026"

                if (dateParts.length > 1) {
                    elDay.textContent = dateParts[0].trim().toUpperCase();
                    elDateFull.textContent = dateParts.slice(1).join(',').trim();
                } else {
                    // Fallback
                    elDay.textContent = now.toLocaleDateString('id-ID', { weekday: 'long' });
                    elDateFull.textContent = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                }
            };

            setInterval(update, 1000);
            update();
        }

        function toggleMusic(forcePlay = false) {
            const bgm = document.getElementById('bgm');
            const btn = document.getElementById('music-toggle');
            const iconPlay = document.getElementById('icon-play');
            const iconPause = document.getElementById('icon-pause');
            const textSpan = btn.querySelector('span');

            if (!bgm) return;

            if (forcePlay || bgm.paused) {
                bgm.volume = 0.5;
                bgm.play().then(() => {
                    iconPlay.classList.add('hidden');
                    iconPause.classList.remove('hidden');
                    btn.classList.add('bg-indigo-600', 'text-white', 'shadow-indigo-500/50');
                    btn.classList.remove('bg-white/80', 'text-indigo-600');
                    textSpan.textContent = 'Pause Music';
                    textSpan.classList.add('text-white');
                    textSpan.classList.remove('text-slate-800');
                }).catch(e => {
                    console.error("Audio play failed or requires interaction", e);
                });
            } else {
                bgm.pause();
                iconPlay.classList.remove('hidden');
                iconPause.classList.add('hidden');
                btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-indigo-500/50');
                btn.classList.add('bg-white/80', 'text-indigo-600');
                textSpan.textContent = 'Play Music';
                textSpan.classList.remove('text-white');
                textSpan.classList.add('text-slate-800');
            }
        }
    </script>
</body>

</html>
