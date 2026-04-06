<?php require __DIR__ . '/../../../Views/layouts/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Absensi Harian</h1>
    </div>

    <!-- Filter Form -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6 mb-6">
        <form action="" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="w-full sm:w-1/3">
                <label for="kelas_id" class="block text-sm font-medium text-gray-700">Pilih Kelas</label>
                <select name="kelas_id" id="kelas_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $k['id'] == $selectedKelas ? 'selected' : '' ?>>
                            <?= $k['tingkat'] . ' ' . $k['abjad'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="w-full sm:w-1/3">
                <label for="date" class="block text-sm font-medium text-gray-700">Tanggal</label>
                <input type="date" name="date" id="date" value="<?= $selectedDate ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            </div>

            <button type="submit" class="w-full sm:w-auto px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                Tampilkan
            </button>
        </form>
    </div>

    <?php if (!empty($students)): ?>
    <form action="/kmi/public/attendance/store" method="POST">
        <input type="hidden" name="date" value="<?= $selectedDate ?>">
        <input type="hidden" name="kelas_id" value="<?= $selectedKelas ?>">
        
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden mb-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hadir</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sakit</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Izin</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Alpha</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($students as $s): ?>
                        <?php $status = $s['status'] ?? 'H'; ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($s['nis']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($s['nama']) ?></td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?= $s['student_id'] ?>][status]" value="H" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" <?= $status == 'H' ? 'checked' : '' ?>>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?= $s['student_id'] ?>][status]" value="S" class="focus:ring-indigo-500 h-4 w-4 text-yellow-500 border-gray-300" <?= $status == 'S' ? 'checked' : '' ?>>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?= $s['student_id'] ?>][status]" value="I" class="focus:ring-indigo-500 h-4 w-4 text-blue-500 border-gray-300" <?= $status == 'I' ? 'checked' : '' ?>>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?= $s['student_id'] ?>][status]" value="A" class="focus:ring-indigo-500 h-4 w-4 text-red-500 border-gray-300" <?= $status == 'A' ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="flex justify-end">
             <button type="submit" class="px-6 py-3 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none">
                Simpan Absensi
            </button>
        </div>
    </form>
    <?php endif; ?>

</main>

<?php require __DIR__ . '/../../../Views/layouts/footer.php'; ?>
