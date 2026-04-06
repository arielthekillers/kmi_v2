<?php renderHeader($title); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Laporan Tanqih Idad
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Rekapitulasi kesiapan mengajar guru per periode.
        </p>
    </div>

    <!-- Filter Date -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Mulai Tanggal</label>
                <input type="date" name="start" value="<?= $startDate ?>" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2 border">
            </div>
            <div class="w-full md:flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end" value="<?= $endDate ?>" class="block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2 border">
            </div>
            <div class="w-full md:w-auto">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 w-full md:w-auto shadow-sm">
                    Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Total Jadwal -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Jadwal</dt>
                            <dd class="text-lg font-medium text-gray-900"><?= $globalStats['total_jadwal'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Terlaksana -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Verified</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                <?= $globalStats['total_verified'] ?>
                                <span class="text-xs text-gray-400 font-normal ml-1">
                                    (<?= $globalStats['total_jadwal'] > 0 ? round(($globalStats['total_verified'] / $globalStats['total_jadwal']) * 100) : 0 ?>%)
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Justified/Izin -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Justified</dt>
                            <dd class="text-lg font-medium text-gray-900"><?= $globalStats['total_justified'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Belum -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Unverified</dt>
                            <dd class="text-lg font-medium text-gray-900"><?= $globalStats['total_belum'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden rounded-lg border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pengajar</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Justified</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unverified</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Kepatuhan (%)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($report)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 text-sm">
                                Tidak ada data jadwal pada rentang tanggal ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($report as $r): 
                            $pct = $r['expected'] > 0 ? round(($r['verified_all'] / $r['expected']) * 100) : 0;
                            $belum = $r['expected'] - $r['verified_all'];
                            
                            // Color Coding
                            if ($pct >= 75) {
                                $badgeColor = 'bg-green-100 text-green-800';
                                $statusText = 'Excellent';
                            } elseif ($pct >= 50) {
                                $badgeColor = 'bg-blue-100 text-blue-800';
                                $statusText = 'Baik';
                            } elseif ($pct >= 25) {
                                $badgeColor = 'bg-yellow-100 text-yellow-800';
                                $statusText = 'Cukup';
                            } else {
                                $badgeColor = 'bg-red-100 text-red-800';
                                $statusText = 'Perlu Perhatian';
                            }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($r['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                <?= $r['expected'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-center font-semibold">
                                <?= $r['verified_real'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 text-center font-semibold">
                                <?= $r['justified'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-center font-semibold">
                                <?= $belum ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center">
                                    <span class="text-sm font-bold text-gray-700 mr-2"><?= $pct ?>%</span>
                                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-indigo-600 h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeColor ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php renderFooter(); ?>
