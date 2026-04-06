<?php require __DIR__ . '/../../../Views/layouts/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nilai Santri (Gradebook)</h1>
    </div>

    <!-- Filter Form -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6 mb-8">
        <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="kelas" class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
                <select name="kelas_id" id="kelas" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>>
                            <?= $c['tingkat'] . ' ' . $c['abjad'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Pilih Mata Pelajaran</label>
                <select name="subject_id" id="subject" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- Pilih Pelajaran --</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $selectedSubject == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 shadow-sm font-medium transition-colors">
                    Lihat Nilai
                </button>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <?php if ($selectedClass && $selectedSubject): ?>
        <?php if (empty($rows)): ?>
            <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <p class="text-gray-500">Belum ada murid di kelas ini.</p>
            </div>
        <?php else: ?>
            <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                     <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <?= htmlspecialchars($subjectInfo['nama']) ?>
                        </h3>
                        <p class="text-sm text-gray-500">
                            Kelas <?= $classInfo['tingkat'] . ' ' . $classInfo['abjad'] ?>
                        </p>
                     </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Santri</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($rows as $i => $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $i + 1 ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['nis']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 font-bold">
                                <?= $row['score_final'] !== null ? $row['score_final'] : '<span class="text-gray-400">-</span>' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php elseif (!$selectedClass && !$selectedSubject): ?>
        <!-- Empty State Prompt -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Silakan pilih Kelas dan Pelajaran</h3>
            <p class="mt-1 text-sm text-gray-500">Gunakan filter di atas untuk melihat nilai.</p>
        </div>
    <?php endif; ?>

</main>

<?php require __DIR__ . '/../../../Views/layouts/footer.php'; ?>
