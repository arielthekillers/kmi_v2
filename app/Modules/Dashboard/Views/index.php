<?php
// app/Modules/Dashboard/Views/index.php
// Expected variables from Controller:
// $stats, $todayDay, $todayDate, $syeikh, $keliling, $absensi, $tanqih, $koreksi, $user_role, $user_name
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard KMI</h1>
            <p class="mt-2 text-gray-600">
                Ringkasan aktivitas hari ini, <strong><?= date('d M Y', strtotime($todayDate)) ?> (<?= $todayDay ?>)</strong>
            </p>
        </div>
        <div class="mt-4 md:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                Tahun Ajaran 2025/2026
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <!-- Stats Grid Row 1: Syeikh & Keliling -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        
        <!-- 1. Syeikh Diwan Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-indigo-50 rounded-lg p-3">
                    <i class="ri-shield-user-line text-2xl text-indigo-600"></i>
                </div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= $todayDay ?></span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Syeikh Diwan</h3>
            <div class="flex-1 mt-2">
                <?php if (empty($syeikh)): ?>
                    <p class="text-sm text-gray-400 italic">Tidak ada jadwal.</p>
                <?php else: ?>
                    <ul class="space-y-1">
                        <?php foreach ($syeikh as $row): ?>
                        <li class="flex items-center text-sm font-medium text-gray-700">
                            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full mr-2"></span>
                            <?= htmlspecialchars($row['nama']) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <a href="/duties" class="mt-4 text-sm text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center">
                Lihat Jadwal <i class="ri-arrow-right-line ml-1"></i>
            </a>
        </div>

        <!-- 1b. Piket Keliling Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-teal-50 rounded-lg p-3">
                    <i class="ri-walk-line text-2xl text-teal-600"></i>
                </div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= $todayDay ?></span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Piket Keliling</h3>
            <div class="flex-1 mt-2">
                <?php if (empty($keliling)): ?>
                    <p class="text-sm text-gray-400 italic">Tidak ada jadwal.</p>
                <?php else: ?>
                    <ul class="space-y-1">
                        <?php foreach ($keliling as $row): ?>
                        <li class="flex items-center text-sm font-medium text-gray-700">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mr-2"></span>
                            <?= htmlspecialchars($row['nama']) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <a href="/duties" class="mt-4 text-sm text-teal-600 hover:text-teal-800 font-medium inline-flex items-center">
                Lihat Jadwal <i class="ri-arrow-right-line ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Stats Grid Row 2: Absensi, Tanqih, Koreksi -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <!-- 2. Absensi Pengajar Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-green-50 rounded-lg p-3">
                    <i class="ri-user-follow-line text-2xl text-green-600"></i>
                </div>
                <span class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-1 rounded-full">New</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Absensi Pengajar</h3>
            <div class="flex-1 mt-2">
                <div class="grid grid-cols-2 gap-2 text-center">
                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="text-xl font-bold text-gray-900"><?= $absensi['hadir'] ?? 0 ?></div>
                        <div class="text-xs text-gray-500">Hadir</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="text-xl font-bold text-gray-900"><?= $absensi['tidak_hadir'] ?? 0 ?></div>
                        <div class="text-xs text-gray-500">Absen</div>
                    </div>
                </div>
            </div>
            <a href="/reports/piket" class="mt-4 text-sm text-green-600 hover:text-green-800 font-medium inline-flex items-center">
                Lihat Laporan <i class="ri-arrow-right-line ml-1"></i>
            </a>
        </div>

        <!-- 3. Tanqih Idad Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-blue-50 rounded-lg p-3">
                    <i class="ri-checkbox-multiple-line text-2xl text-blue-600"></i>
                </div>
                <span class="text-xs font-semibold text-gray-500"><?= $tanqih['percent'] ?>%</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Tanqih Idad</h3>
            <div class="flex-1 mt-2">
                 <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $tanqih['percent'] ?>%"></div>
                </div>
                <p class="text-sm text-gray-600">
                    <strong><?= $tanqih['verified'] ?></strong> dari <strong><?= $tanqih['total'] ?></strong> jadwal terverifikasi.
                </p>
            </div>
            <a href="/teaching-logs" class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                <?= ($user_role === 'admin' || $user_role === 'guru') ? 'Buka Tanqih' : 'Lihat Data' ?>
                <i class="ri-arrow-right-line ml-1"></i>
            </a>
        </div>

        <!-- 4. Koreksi Ujian Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-purple-50 rounded-lg p-3">
                    <i class="ri-pencil-ruler-2-line text-2xl text-purple-600"></i>
                </div>
                <span class="text-xs font-semibold text-gray-500"><?= $koreksi['percent'] ?>%</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Koreksi Ujian</h3>
            <div class="flex-1 mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?= $koreksi['percent'] ?>%"></div>
                </div>
                <p class="text-sm text-gray-600">
                    <strong><?= $koreksi['done'] ?></strong> dari <strong><?= $koreksi['total'] ?></strong> mapel selesai.
                </p>
            </div>
            <a href="/grades" class="mt-4 text-sm text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                Lihat Progres <i class="ri-arrow-right-line ml-1"></i>
            </a>
        </div>

    </div>

    <!-- Admin Master Data Section (Mini Cards) -->
    <?php if ($user_role === 'admin'): ?>
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Master Data</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="/subjects" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Pelajaran</p>
                   <p class="text-xl font-bold text-gray-900 mt-1"><?= $stats['pelajaran'] ?></p>
                </div>
                <div class="bg-blue-50 p-2 rounded-lg text-blue-600 group-hover:bg-blue-100 transition-colors">
                    <i class="ri-book-2-line text-xl"></i>
                </div>
            </div>
        </a>
        <a href="/students" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Santri</p>
                   <p class="text-xl font-bold text-gray-900 mt-1"><?= $stats['santri'] ?></p>
                </div>
                <div class="bg-pink-50 p-2 rounded-lg text-pink-600 group-hover:bg-pink-100 transition-colors">
                    <i class="ri-group-line text-xl"></i>
                </div>
            </div>
        </a>
        <a href="/classes" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Kelas</p>
                   <p class="text-xl font-bold text-gray-900 mt-1"><?= $stats['kelas'] ?></p>
                </div>
                <div class="bg-green-50 p-2 rounded-lg text-green-600 group-hover:bg-green-100 transition-colors">
                    <i class="ri-building-line text-xl"></i>
                </div>
            </div>
        </a>
        <a href="/teachers" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Pengajar</p>
                   <p class="text-xl font-bold text-gray-900 mt-1"><?= $stats['pengajar'] ?></p>
                </div>
                <div class="bg-purple-50 p-2 rounded-lg text-purple-600 group-hover:bg-purple-100 transition-colors">
                     <i class="ri-user-star-line text-xl"></i>
                </div>
            </div>
        </a>
        <a href="/schedules" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
             <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Jadwal</p>
                   <p class="text-xl font-bold text-gray-900 mt-1">Total</p>
                </div>
                <div class="bg-orange-50 p-2 rounded-lg text-orange-600 group-hover:bg-orange-100 transition-colors">
                     <i class="ri-calendar-todo-line text-xl"></i>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>
</main>
