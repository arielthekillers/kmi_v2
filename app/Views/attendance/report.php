<?php renderHeader($title); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Laporan Piket Keliling</h2>
        <p class="text-gray-500 text-sm mb-6">Laporan absensi pengajar hasil patroli Syeikh Diwan.</p>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="w-full md:w-auto">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($filter['date']) ?>" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                </div>
                <div class="w-full md:w-1/4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Kelas</label>
                    <select name="kelas_id" class="tom-select block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelasData as $kls): ?>
                            <option value="<?= $kls['id'] ?>" <?= $filter['kelas_id'] == $kls['id'] ? 'selected' : '' ?>>
                                Kelas <?= htmlspecialchars($kls['tingkat']) ?>-<?= htmlspecialchars($kls['abjad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w-full md:w-1/6">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jam</label>
                    <select name="jam" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        <option value="">-- Semua --</option>
                        <?php foreach ($hoursConfig as $h): ?>
                            <?php if ($h['type'] === 'jam'): ?>
                                <option value="<?= $h['value'] ?>" <?= $filter['jam'] == $h['value'] ? 'selected' : '' ?>>
                                    Jam <?= htmlspecialchars($h['value']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w-full md:w-1/4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pengajar</label>
                    <select name="pengajar_id" class="tom-select block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        <option value="">-- Semua Pengajar --</option>
                        <?php foreach ($pengajarData as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $filter['pengajar_id'] == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm whitespace-nowrap">
                        Tampilkan
                    </button>
                    <a href="<?= url('/attendance/report') ?>" class="bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium shadow-sm border border-gray-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <div class="text-sm text-gray-500 mb-1">Total Absensi</div>
                <div class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?></div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg shadow-sm border border-green-200">
                <div class="text-sm text-green-600 mb-1">Hadir</div>
                <div class="text-2xl font-bold text-green-700"><?= $stats['hadir'] ?></div>
                <div class="text-xs text-green-600 mt-1">Tepat: <?= $stats['tepat'] ?> | Terlambat: <?= $stats['terlambat'] ?></div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg shadow-sm border border-red-200">
                <div class="text-sm text-red-600 mb-1">Tidak Hadir</div>
                <div class="text-2xl font-bold text-red-700"><?= $stats['tidak_hadir'] ?></div>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg shadow-sm border border-blue-200">
                <div class="text-sm text-blue-600 mb-1">Diganti</div>
                <div class="text-2xl font-bold text-blue-700"><?= $stats['diganti'] ?></div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jam</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mapel</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengajar</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">
                                    Tidak ada data absensi untuk filter ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $r): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d M Y', strtotime($r['date'])) ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Kelas <?= $r['tingkat'] ?>-<?= $r['abjad'] ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-900">
                                    <?= $r['hour'] ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <?= htmlspecialchars($r['mapel_name']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <?= htmlspecialchars($r['teacher_nama'] ?? 'Unknown') ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <?php if ($r['status'] === 'hadir'): ?>
                                        <?php if ($r['ketepatan'] === 'tepat_waktu'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Hadir Tepat
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Terlambat (<?= htmlspecialchars($r['jam_datang']) ?>)
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif ($r['status'] === 'tidak_hadir'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Tidak Hadir
                                        </span>
                                    <?php elseif ($r['status'] === 'diganti' || $r['status'] === 'substitute'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Diganti: <?= htmlspecialchars($r['subst_nama'] ?? '-') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php renderFooter(); ?>
