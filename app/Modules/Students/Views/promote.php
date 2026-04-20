<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h5 class="text-lg font-bold text-gray-800 flex items-center">
                <i class="ri-graduation-cap-line mr-2 text-indigo-600 border border-transparent"></i>
                Promosi Santri ke Tahun Ajaran Baru
            </h5>
            <a href="<?= url('/students') ?>" class="text-sm font-medium text-gray-500 hover:text-gray-700">Kembali</a>
        </div>
        <div class="p-6">
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Kelas asal (Tahun <?= htmlspecialchars($currentYear->name ?? 'None') ?>)
                    </label>
                    <select name="kelas_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelas as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($sourceKelasId ?? '') == $k['id'] ? 'selected' : '' ?>>
                                Kelas <?= htmlspecialchars($k['tingkat'] . '-' . $k['abjad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <?php if ($sourceKelasId ?? false): ?>
                <form action="<?= url('/students/promote/store') ?>" method="POST">
                    <?= csrf_input() ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 p-6 bg-indigo-50 rounded-xl border border-indigo-100">
                        <div>
                            <label class="block text-sm font-bold text-indigo-900 mb-2">
                                <i class="ri-arrow-right-circle-line mr-1 text-indigo-600"></i> Tahun Ajaran Tujuan
                            </label>
                            <select name="target_year_id" class="w-full px-3 py-2 border border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                <?php foreach ($allYears as $y): ?>
                                    <?php if ($y['id'] != $currentYear->id): ?>
                                        <option value="<?= $y['id'] ?>"><?= htmlspecialchars($y['name']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-1 text-xs text-indigo-600">Data santri akan diduplikasi ke tahun ajaran ini.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-indigo-900 mb-2">
                                <i class="ri-community-line mr-1 text-indigo-600"></i> Kelas Tujuan
                            </label>
                            <select name="target_kelas_id" class="w-full px-3 py-2 border border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">-- Pilih Kelas Tujuan --</option>
                                <?php foreach ($kelas as $k): ?>
                                    <option value="<?= $k['id'] ?>">
                                        Kelas <?= htmlspecialchars($k['tingkat'] . '-' . $k['abjad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-hidden border border-gray-200 rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider w-10">
                                        <input type="checkbox" id="checkAll" class="rounded text-indigo-600 focus:ring-indigo-500" checked>
                                    </th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider">NIS</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider">Nama Santri</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider">Gender</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic bg-gray-50">Tidak ada data santri di kelas ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $s): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>" class="student-check rounded text-indigo-600 focus:ring-indigo-500" checked>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($s['nis']) ?></td>
                                            <td class="px-6 py-4 text-sm font-semibold text-gray-900"><?= htmlspecialchars($s['nama']) ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $s['gender'] == 'L' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' ?>">
                                                    <?= $s['gender'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 transition-all active:scale-95" onclick="return confirm('Apakah Anda yakin ingin memproses promosi ini?')">
                            <i class="ri-rocket-line mr-2"></i> Proses Kenaikan Kelas
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-graduation-cap-fill text-4xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Siap untuk Kenaikan Kelas?</h3>
                    <p class="text-gray-500">Silakan pilih kelas asal untuk memulai proses promosi santri.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.student-check').forEach(cb => cb.checked = this.checked);
});
</script>
