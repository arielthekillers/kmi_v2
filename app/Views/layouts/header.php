<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'KMI App') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
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

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo & Desktop Nav -->
                <div class="flex items-center gap-8">
                    <a href="<?= url('/') ?>" class="text-xl font-bold text-indigo-600 flex items-center gap-2">
                        <!-- Use RemixIcon or similar SVG -->
                        <i class="ri-school-fill text-2xl"></i>
                        KMI App (MVC)
                    </a>

                    <div class="hidden md:flex space-x-1">
                        <a href="<?= url('/') ?>" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100 text-gray-700">Dashboard</a>
                        
                        <!-- Filter Role: Admin -->
                         <?php 
                         $role = $_SESSION['role'] ?? 'guest';
                         if($role === 'admin'): 
                         ?>
                            <!-- Master Data -->
                            <div class="relative group">
                                <button class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100 text-gray-700 flex items-center">
                                    Master Data <i class="ri-arrow-down-s-line ml-1"></i>
                                </button>
                                <div class="absolute left-0 mt-0 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block border border-gray-100">
                                    <a href="<?= url('/subjects') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pelajaran</a>
                                    <a href="<?= url('/teachers') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengajar</a>
                                    <a href="<?= url('/classes') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Kelas</a>
                                    <a href="<?= url('/students') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Santri</a>
                                </div>
                            </div>
                         <?php endif; ?>

                        <div class="relative group">
                            <button class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100 text-gray-700 flex items-center">
                                Jadwal & Piket <i class="ri-arrow-down-s-line ml-1"></i>
                            </button>
                            <div class="absolute left-0 mt-0 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block border border-gray-100">
                                <a href="<?= url('/duties') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Jadwal Syeikh & Piket</a>
                                <a href="<?= url('/attendance') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Absensi Pengajar</a>
                            </div>
                        </div>

                        <div class="relative group">
                            <button class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100 text-gray-700 flex items-center">
                                Tanqih & Koreksi <i class="ri-arrow-down-s-line ml-1"></i>
                            </button>
                            <div class="absolute left-0 mt-0 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block border border-gray-100">
                                <a href="<?= url('/teaching-logs') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Laporan Tanqih Idad</a>
                                <a href="<?= url('/grades') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Koreksi Materi Ujian</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: User Profile -->
                <div class="flex items-center">
                    <div class="ml-3 relative group">
                        <div class="flex items-center space-x-2 cursor-pointer">
                            <div class="bg-indigo-100 p-2 rounded-full text-indigo-600">
                                <i class="ri-user-line"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></span>
                        </div>
                         <!-- Dropdown -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block border border-gray-100 top-full">
                            <a href="<?= url('/logout') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Sign out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
