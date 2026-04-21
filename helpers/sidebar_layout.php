<?php
// helpers/sidebar_layout.php

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
        <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <style>
            body { font-family: 'Inter', sans-serif; }
            /* Tom Select Tweaks */
            .ts-control { border-radius: 0.375rem; padding: 0.5rem 0.75rem; border-color: #d1d5db; }
            .ts-wrapper.single .ts-control { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
            .ts-dropdown { border-radius: 0.375rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 50; }
            .ts-wrapper { min-width: 150px; }
            
            /* Sidebar transitions */
            .sidebar-transition { transition: transform 0.3s ease-in-out; }
        </style>
    </head>

    <body class="bg-gray-50 text-gray-800">

        <?php
        if (session_status() === PHP_SESSION_NONE) session_start();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        ?>

        <?php if ($flash): ?>
            <div id="flash-message" class="fixed top-5 right-5 z-[60] bg-white shadow-lg rounded-lg border-l-4 <?= $flash['type'] === 'success' ? 'border-green-500' : 'border-red-500' ?> p-4 animate-fade-in-down">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <?php if ($flash['type'] === 'success'): ?>
                            <i class="ri-checkbox-circle-fill text-green-500 text-xl"></i>
                        <?php else: ?>
                            <i class="ri-close-circle-fill text-red-500 text-xl"></i>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900"><?= $flash['type'] === 'success' ? 'Sukses' : 'Gagal' ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($flash['message']) ?></p>
                    </div>
                    <button onclick="document.getElementById('flash-message').remove()" class="ml-4 text-gray-400 hover:text-gray-500">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('flash-message');
                    if(el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }
                }, 3000);
            </script>
        <?php endif; ?>

        <div class="flex min-h-screen">
            
            <!-- Sidebar -->
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform -translate-x-full md:translate-x-0 sidebar-transition flex flex-col">
                <!-- Brand -->
                <div class="flex items-center justify-center h-16 border-b border-gray-200 px-6">
                    <a href="index.php" class="flex items-center gap-2 text-xl font-bold text-indigo-600">
                        <i class="ri-school-fill text-2xl"></i>
                        <span>KMI App</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                    <?php $current = basename($_SERVER['PHP_SELF']); ?>

                    <a href="index.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg <?= $current === 'index.php' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <i class="ri-dashboard-line mr-3 text-lg <?= $current === 'index.php' ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                        Dashboard
                    </a>

                    <?php if (auth_get_role() === 'admin'): ?>
                        <div class="pt-4 pb-1 pl-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Master Data</div>
                        
                        <a href="pelajaran.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= $current === 'pelajaran.php' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-book-2-line mr-3 text-lg <?= $current === 'pelajaran.php' ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Pelajaran
                        </a>
                        <a href="pengajar.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= $current === 'pengajar.php' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-user-star-line mr-3 text-lg <?= $current === 'pengajar.php' ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Pengajar
                        </a>
                        <a href="kelas.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= $current === 'kelas.php' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-building-line mr-3 text-lg <?= $current === 'kelas.php' ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Kelas & Santri
                        </a>

                        <div class="pt-4 pb-1 pl-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jadwal & Piket</div>
                        
                        <a href="jadwal.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= $current === 'jadwal.php' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-calendar-todo-line mr-3 text-lg <?= $current === 'jadwal.php' ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Jadwal Pelajaran
                        </a>
                        <a href="<?= url('/piket/office') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (strpos($_SERVER['REQUEST_URI'], '/piket/office') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                             <i class="ri-shield-user-line mr-3 text-lg <?= (strpos($_SERVER['REQUEST_URI'], '/piket/office') !== false) ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Syeikh Diwan
                        </a>
                        <a href="<?= url('/piket/roaming') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (strpos($_SERVER['REQUEST_URI'], '/piket/roaming') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                             <i class="ri-walk-line mr-3 text-lg <?= (strpos($_SERVER['REQUEST_URI'], '/piket/roaming') !== false) ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Piket Keliling
                        </a>
                        <a href="<?= url('/attendance') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (strpos($_SERVER['REQUEST_URI'], '/attendance') !== false && strpos($_SERVER['REQUEST_URI'], '/report') === false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-calendar-check-line mr-3 text-lg <?= (strpos($_SERVER['REQUEST_URI'], '/attendance') !== false && strpos($_SERVER['REQUEST_URI'], '/report') === false) ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Absensi Pengajar
                        </a>
                        <a href="<?= url('/attendance/report') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (strpos($_SERVER['REQUEST_URI'], '/attendance/report') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-file-list-3-line mr-3 text-lg <?= (strpos($_SERVER['REQUEST_URI'], '/attendance/report') !== false) ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Laporan Piket
                        </a>
                    <?php endif; ?>

                    <?php if (auth_get_role() === 'pengajar'): ?>
                        <div class="pt-4 pb-1 pl-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aktivitas</div>
                        <a href="jadwal_saya.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= $current === 'jadwal_saya.php' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-calendar-check-line mr-3 text-lg <?= $current === 'jadwal_saya.php' ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Jadwal Mengajar
                        </a>
                        <?php if (auth_is_piket_keliling_today()): ?>
                            <a href="absensi_pengajar.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= $current === 'absensi_pengajar.php' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                                <i class="ri-user-follow-line mr-3 text-lg <?= $current === 'absensi_pengajar.php' ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                                Absensi Pengajar
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="pt-4 pb-1 pl-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Akademik</div>
                    
                    <?php if (auth_get_role() === 'admin' || auth_get_role() === 'pengajar'): ?>
                        <a href="<?= url('/tanqih') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (strpos($_SERVER['REQUEST_URI'], '/tanqih') !== false && strpos($_SERVER['REQUEST_URI'], '/report') === false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-checkbox-multiple-line mr-3 text-lg <?= (strpos($_SERVER['REQUEST_URI'], '/tanqih') !== false && strpos($_SERVER['REQUEST_URI'], '/report') === false) ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Tanqih Idad
                        </a>
                        <a href="<?= url('/tanqih/report') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (strpos($_SERVER['REQUEST_URI'], '/tanqih/report') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-file-chart-line mr-3 text-lg <?= (strpos($_SERVER['REQUEST_URI'], '/tanqih/report') !== false) ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Laporan Tanqih
                        </a>
                    <?php endif; ?>

                    <a href="<?= url('/grades') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (strpos($_SERVER['REQUEST_URI'], '/grades') !== false && strpos($_SERVER['REQUEST_URI'], '/panitia') === false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                        <i class="ri-pencil-ruler-2-line mr-3 text-lg <?= (strpos($_SERVER['REQUEST_URI'], '/grades') !== false && strpos($_SERVER['REQUEST_URI'], '/panitia') === false) ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                        Koreksi Ujian
                    </a>

                    <?php if (auth_get_role() === 'admin' || auth_is_panitia()): ?>
                        <a href="<?= url('/grades/panitia') ?>" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (strpos($_SERVER['REQUEST_URI'], '/grades/panitia') !== false) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' ?>">
                            <i class="ri-group-line mr-3 text-lg <?= (strpos($_SERVER['REQUEST_URI'], '/grades/panitia') !== false) ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                            Panitia Ujian
                        </a>
                    <?php endif; ?>

                </nav>

                <!-- User Profile Bottom -->
                <div class="border-t border-gray-200 p-4">
                    <?php if (is_logged_in()):
                        $__user = function_exists('auth_get_current_user') ? auth_get_current_user() : null;
                        $__display_name = function_exists('auth_get_display_name') ? auth_get_display_name() : (is_array($__user) ? ($__user['nama'] ?? ($__user['username'] ?? '')) : (string)$__user);
                    ?>
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                    <?= substr($__display_name, 0, 1) ?>
                                </div>
                            </div>
                            <div class="ml-3 truncate">
                                <p class="text-sm font-medium text-gray-700 truncate"><?= htmlspecialchars($__display_name) ?></p>
                                <a href="logout.php" class="text-xs text-indigo-600 hover:text-indigo-800">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col md:ml-64 min-w-0">
                <!-- Topbar for Mobile Toggle -->
                <div class="md:hidden flex items-center justify-between bg-white border-b border-gray-200 px-4 py-2 sticky top-0 z-40">
                    <a href="index.php" class="text-lg font-bold text-indigo-600 flex items-center gap-1">
                        <i class="ri-school-fill"></i> KMI
                    </a>
                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none p-2" onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')">
                        <i class="ri-menu-line text-2xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <main class="flex-1">
<?php
}

function renderFooter()
{
?>
                </main>
                
                <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
                        <p>&copy; <?= date('Y') ?> KMI App. Created by <a href="https://github.com/arielthekillers" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">arielthekillers</a></p>
                    </div>
                </footer>
            </div>
            
            <!-- Overlay for mobile sidebar -->
            <div id="sidebar-overlay" onclick="document.getElementById('sidebar').classList.add('-translate-x-full')" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden hidden"></div>
            
        </div>

        <script>
            // Mobile Sidebar Toggle Logic
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const toggleBtn = document.querySelector('button[onclick*="sidebar"]');
            
            if(toggleBtn) {
                toggleBtn.onclick = (e) => {
                    e.preventDefault();
                    sidebar.classList.toggle('-translate-x-full');
                    overlay.classList.toggle('hidden');
                };
            }

            function initTomSelects() {
                document.querySelectorAll('select.tom-select').forEach((el) => {
                    if (el.tomselect) return;
                    new TomSelect(el, {
                        create: false,
                        dropdownParent: 'body',
                        sortField: { field: "text", direction: "asc" },
                        onInitialize: function() {
                            this.wrapper.classList.remove('form-control');
                            this.wrapper.style.display = 'block';
                        }
                    });
                });
            }
            document.addEventListener('DOMContentLoaded', initTomSelects);
        </script>
    </body>
    </html>
<?php
}
?>
