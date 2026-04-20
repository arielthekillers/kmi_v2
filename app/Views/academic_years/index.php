<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- List Tahun Ajaran -->
        <div class="w-full lg:w-2/3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h5 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="ri-calendar-todo-line mr-2 text-indigo-600"></i>
                        Daftar Tahun Ajaran
                    </h5>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Tahun Ajaran</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Status</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($years as $year): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                        <?= htmlspecialchars($year['name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($year['is_active']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="ri-checkbox-circle-line mr-1"></i> Aktif
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                Tidak Aktif
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <?php if (!$year['is_active']): ?>
                                            <form action="<?= url('/academic-years/set-active') ?>" method="POST" class="inline">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="id" value="<?= $year['id'] ?>">
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">
                                                    Set Aktif
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form Tambah -->
        <div class="w-full lg:w-1/3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-24">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h5 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="ri-add-circle-line mr-2 text-green-600"></i>
                        Tambah Tahun
                    </h5>
                </div>
                <div class="p-6">
                    <form action="<?= url('/academic-years/store') ?>" method="POST" class="space-y-4">
                        <?= csrf_input() ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tahun Ajaran</label>
                            <input type="text" name="name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" 
                                   placeholder="Contoh: 2026-2027" required>
                            <p class="mt-1 text-xs text-gray-500 font-italic">Format: YYYY-YYYY</p>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-indigo-700 transition-colors">
                            Simpan Tahun Ajaran
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-100 text-blue-700 text-sm flex gap-3">
                <i class="ri-information-line text-lg flex-shrink-0"></i>
                <p>Mengubah tahun ajaran aktif akan mengubah seluruh data yang ditampilkan di dashboard, jadwal, dan nilai.</p>
            </div>
        </div>
    </div>
</div>
