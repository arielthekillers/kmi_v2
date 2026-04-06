<?php
// helpers/layout.php



function renderHeader($title = "KMI App")
{
?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
            /* Tom Select Tailwind Tweaks */
            .ts-control {
                border-radius: 0.375rem; /* rounded-md */
                padding: 0.5rem 0.75rem;
                border-color: #d1d5db; /* gray-300 */
            }
            .ts-wrapper.single .ts-control {
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            }
            .ts-dropdown {
                border-radius: 0.375rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                z-index: 50; /* Ensure high z-index */
            }
            /* Fix for hidden/empty selects in modals */
            .ts-wrapper {
                min-width: 150px;
            }
        </style>
    </head>

    <body class="bg-gray-50 text-gray-800">

        <?php
        if (session_status() === PHP_SESSION_NONE) session_start();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        ?>

        <?php if ($flash): ?>
            <div id="flash-message" class="fixed top-20 left-4 right-4 sm:left-auto sm:right-5 z-50 sm:max-w-sm sm:w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <?php if ($flash['type'] === 'success'): ?>
                                <!-- Heroicon name: check-circle -->
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            <?php else: ?>
                                <!-- Heroicon name: x-circle -->
                                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">
                                <?= $flash['type'] === 'success' ? 'Sukses!' : 'Gagal!' ?>
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                <?= htmlspecialchars($flash['message']) ?>
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="document.getElementById('flash-message').remove()">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                // Auto hide after 3 seconds
                setTimeout(() => {
                    const el = document.getElementById('flash-message');
                    if(el) {
                        el.style.transition = 'opacity 0.5s ease-out';
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 500);
                    }
                }, 3000);
            </script>
        <?php endif; ?>

        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo & Desktop Nav -->
                    <div class="flex items-center gap-8">
                        <a href="<?= url('/') ?>" class="text-xl font-bold text-indigo-600 flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            KMI App
                        </a>

                        <?php $current = basename($_SERVER['PHP_SELF']); ?>
                        <div class="hidden md:flex items-center space-x-1">
                            <a href="<?= url('/') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php' || strpos($_SERVER['REQUEST_URI'], '/kmi/') === 0 && strlen($_SERVER['REQUEST_URI']) <= 5) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50' ?>" <?= ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php' || strpos($_SERVER['REQUEST_URI'], '/kmi/') === 0 && strlen($_SERVER['REQUEST_URI']) <= 5) ? 'aria-current="page"' : '' ?>>Dashboard</a>
                            <?php if (auth_get_role() === 'admin'): ?>
                                <!-- Master Data Dropdown -->
                                <div class="relative group">
                                    <button class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 inline-flex items-center gap-1">
                                        Master Data
                                        <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <div class="absolute left-0 mt-0 w-48 bg-white rounded-md shadow-lg border border-gray-100 hidden group-hover:block py-1 z-50">
                                        <a href="<?= url('/subjects') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 <?= (strpos($_SERVER['REQUEST_URI'], '/subjects') !== false) ? 'bg-gray-50 text-indigo-600' : '' ?>">Master Pelajaran</a>
                                        <a href="<?= url('/teachers') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 <?= (strpos($_SERVER['REQUEST_URI'], '/teachers') !== false) ? 'bg-gray-50 text-indigo-600' : '' ?>">Data Pengajar</a>
                                        <a href="<?= url('/classes') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 <?= (strpos($_SERVER['REQUEST_URI'], '/classes') !== false) ? 'bg-gray-50 text-indigo-600' : '' ?>">Data Kelas</a>
                                    </div>
                                </div>
                                <!-- Jadwal Dropdown -->
                                <div class="relative group">
                                    <button class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 inline-flex items-center gap-1">
                                        Jadwal
                                        <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <div class="absolute left-0 mt-0 w-48 bg-white rounded-md shadow-lg border border-gray-100 hidden group-hover:block py-1 z-50">
                                        <a href="<?= url('/schedule') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 <?= (strpos($_SERVER['REQUEST_URI'], '/schedule') !== false) ? 'bg-gray-50 text-indigo-600' : '' ?>">Jadwal Pelajaran</a>
                                        <a href="<?= url('/piket/office') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 <?= (strpos($_SERVER['REQUEST_URI'], '/piket/office') !== false) ? 'bg-gray-50 text-indigo-600' : '' ?>">Jadwal Syeikh Diwan</a>
                                        <a href="<?= url('/piket/roaming') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 <?= (strpos($_SERVER['REQUEST_URI'], '/piket/roaming') !== false) ? 'bg-gray-50 text-indigo-600' : '' ?>">Jadwal Piket Keliling</a>
                                        <a href="<?= url('/attendance') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 <?= (strpos($_SERVER['REQUEST_URI'], '/attendance') !== false && strpos($_SERVER['REQUEST_URI'], '/report') === false) ? 'bg-gray-50 text-indigo-600' : '' ?>">Absensi Pengajar</a>
                                        <a href="<?= url('/attendance/report') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 <?= (strpos($_SERVER['REQUEST_URI'], '/attendance/report') !== false) ? 'bg-gray-50 text-indigo-600' : '' ?>">Laporan Piket Keliling</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (auth_get_role() === 'pengajar'): ?>
                                <a href="<?= url('/jadwal-saya') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/jadwal-saya') !== false) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50' ?>" <?= (strpos($_SERVER['REQUEST_URI'], '/jadwal-saya') !== false) ? 'aria-current="page"' : '' ?>>Jadwal Mengajar</a>
                            <?php endif; ?>

                            <?php if (auth_get_role() === 'admin' || auth_get_role() === 'pengajar'): ?>
                                <a href="<?= url('/tanqih') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/tanqih') !== false && strpos($_SERVER['REQUEST_URI'], '/report') === false) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50' ?>">Tanqih Idad</a>
                                <a href="<?= url('/tanqih/report') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/tanqih/report') !== false) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50' ?>">Laporan Tanqih Idad</a>
                            <?php endif; ?>

                            <?php if (auth_get_role() === 'pengajar' && auth_is_piket_keliling_today()): ?>
                                <a href="<?= url('/attendance') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/attendance') !== false)  ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50' ?>" <?= (strpos($_SERVER['REQUEST_URI'], '/attendance') !== false)  ? 'aria-current="page"' : '' ?>>Absensi Pengajar</a>
                            <?php endif; ?>
                            <a href="<?= url('/grades') ?>" class="px-3 py-2 rounded-md text-sm font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/grades') !== false) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50' ?>" <?= (strpos($_SERVER['REQUEST_URI'], '/grades') !== false) ? 'aria-current="page"' : '' ?>>Koreksi Ujian</a>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex items-center md:hidden">
                        <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="text-gray-500 hover:text-gray-700 p-2">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- User / Auth -->
                    <div class="hidden md:flex items-center gap-4">
                        <?php if (is_logged_in()):
                            $__user = function_exists('auth_get_current_user') ? auth_get_current_user() : null;
                            $__display_name = function_exists('auth_get_display_name') ? auth_get_display_name() : (is_array($__user) ? ($__user['nama'] ?? ($__user['username'] ?? '')) : (string)$__user);
                        ?>
                            <span class="text-sm text-gray-600">Halo, <strong><?= htmlspecialchars($__display_name) ?></strong></span>
                            <?php if (auth_get_role() === 'pengajar'): ?>
                                <a href="<?= url('/profil') ?>" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Profil Saya</a>
                            <?php endif; ?>
                            <a href="<?= url('/logout') ?>" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Logout</a>
                        <?php else: ?>
                            <a href="<?= url('/login') ?>" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="<?= url('/') ?>" class="block px-3 py-2 rounded-md text-base font-medium <?= ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php' || strpos($_SERVER['REQUEST_URI'], '/kmi/') === 0 && strlen($_SERVER['REQUEST_URI']) <= 5) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50' ?>" <?= ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php' || strpos($_SERVER['REQUEST_URI'], '/kmi/') === 0 && strlen($_SERVER['REQUEST_URI']) <= 5) ? 'aria-current="page"' : '' ?>>Dashboard</a>
                    <?php if (auth_get_role() === 'pengajar'): ?>
                        <a href="<?= url('/jadwal-saya') ?>" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/jadwal-saya') !== false) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50' ?>">Jadwal Mengajar</a>
                    <?php endif; ?>
                    <?php if (auth_get_role() === 'admin'): ?>
                        <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Master Data</div>
                        <a href="<?= url('/subjects') ?>" class="block pl-6 pr-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/subjects') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' ?>">Daftar Pelajaran</a>
                        <a href="<?= url('/teachers') ?>" class="block pl-6 pr-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/teachers') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' ?>">Data Pengajar</a>
                        <a href="<?= url('/classes') ?>" class="block pl-6 pr-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/classes') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' ?>">Data Kelas</a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jadwal</div>
                        <a href="<?= url('/schedule') ?>" class="block pl-6 pr-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/schedule') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' ?>">Jadwal Pelajaran</a>
                        <a href="<?= url('/piket/office') ?>" class="block pl-6 pr-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/piket/office') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' ?>">Jadwal Piket Kantor</a>
                        <a href="<?= url('/piket/roaming') ?>" class="block pl-6 pr-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/piket/roaming') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' ?>">Jadwal Piket Keliling</a>
                        <a href="<?= url('/attendance') ?>" class="block pl-6 pr-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/attendance') !== false && strpos($_SERVER['REQUEST_URI'], '/report') === false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' ?>">Absensi Pengajar</a>
                        <a href="<?= url('/attendance/report') ?>" class="block pl-6 pr-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/attendance/report') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:bg-gray-50' ?>">Laporan Piket Keliling</a>
                    <?php endif; ?>

                    <?php if (auth_get_role() === 'admin' || auth_get_role() === 'pengajar'): ?>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="<?= url('/tanqih') ?>" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/tanqih') !== false && strpos($_SERVER['REQUEST_URI'], '/report') === false) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50' ?>">Tanqih Idad</a>
                        <a href="<?= url('/tanqih/report') ?>" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/tanqih/report') !== false) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50' ?>">Laporan Tanqih Idad</a>
                    <?php endif; ?>

                    <?php if (auth_get_role() === 'pengajar' && auth_is_piket_keliling_today()): ?>
                        <a href="<?= url('/attendance') ?>" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/attendance') !== false) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50' ?>">Absensi Pengajar</a>
                    <?php endif; ?>
                    <a href="<?= url('/grades') ?>" class="block px-3 py-2 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], '/grades') !== false) ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50' ?>">Koreksi Ujian</a>

                    <?php if (is_logged_in()): ?>
                        <?php if (auth_get_role() === 'pengajar'): ?>
                            <a href="<?= url('/profil') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">Profil Saya</a>
                        <?php endif; ?>
                        <a href="<?= url('/logout') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">Logout</a>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    <?php
}

function renderFooter()
{
    ?>
    <footer class="mt-12 py-6 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                Created by <a href="https://github.com/arielthekillers" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">arielthekillers</a>
            </p>
        </div>
    </footer>

    <script>
        function initTomSelects() {
            document.querySelectorAll('select.tom-select').forEach((el) => {
                if (el.tomselect) return; // Already initialized
                
                // Check if it's in a hidden container (like a modal) that is currently not displayed
                // TomSelect needs visibility to calculate width correctly, but usually works okay.
                // We'll proceed.
                
                new TomSelect(el, {
                    create: false,
                    dropdownParent: 'body', // Fix z-index clipping
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    onInitialize: function() {
                        // Fix for Tailwind resets
                        this.wrapper.classList.remove('form-control');
                        this.wrapper.style.display = 'block'; // Ensure wrapper is block
                        // Remove Tailwind border/shadow classes copied from select to avoid double border
                        this.wrapper.classList.remove('border', 'border-gray-300', 'shadow-sm', 'rounded-md', 'p-2');
                    }
                });
            });
        }
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', initTomSelects);
    </script>
    </body>

    </html>
<?php
}
?>