<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tempat Sampah Santri</h1>
            <p class="text-sm text-gray-500">Daftar santri yang telah dihapus (Soft Delete).</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= url('/students') ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 flex items-center">
                <i class="ri-arrow-left-line mr-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <form action="<?= url('/students/trash') ?>" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Cari Nama / NIS / NIK</label>
                <div class="relative">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" 
                           placeholder="Ketik nama atau nomor identitas..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors flex items-center shadow-md">
                <i class="ri-filter-3-line mr-2"></i> Tampilkan Data
            </button>
            <?php if (!empty($q)): ?>
                <a href="<?= url('/students/trash') ?>" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm font-medium">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden ring-1 ring-black ring-opacity-5">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS / NISN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Santri</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas Terakhir</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dihapus Pada</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center text-sm text-gray-400 italic">
                            Tidak ada data santri di tempat sampah.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $index => $s): ?>
                    <tr class="hover:bg-indigo-50/30 transition-colors">
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                            <?= str_pad(($page - 1) * 10 + $index + 1, 3, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($s['nis']) ?></div>
                            <div class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($s['nisn'] ?: '-') ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-xs font-bold mr-3">
                                    <?= mb_strtoupper(mb_substr($s['nama'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($s['nama']) ?></div>
                                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">
                                        <?= $s['gender'] === 'L' ? '<span class="text-blue-500">Laki-laki</span>' : '<span class="text-pink-500">Perempuan</span>' ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($s['tingkat']): ?>
                                <span class="px-2.5 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full bg-gray-100 text-gray-700 uppercase">
                                    <?= htmlspecialchars($s['tingkat'] . ' ' . $s['abjad']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs italic">Tidak ada data kelas</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600"><?= date('d M Y', strtotime($s['deleted_at'])) ?></div>
                            <div class="text-xs text-gray-400 font-mono"><?= date('H:i', strtotime($s['deleted_at'])) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <a href="<?= url('/students/restore?id=' . $s['id']) ?>" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Pulihkan Data">
                                    <i class="ri-refresh-line text-lg"></i>
                                </a>
                                <button onclick="confirmForceDelete(<?= $s['id'] ?>, '<?= addslashes($s['nama']) ?>')" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors" title="Hapus Permanen">
                                    <i class="ri-delete-bin-2-line text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Menampilkan <span class="font-bold"><?= count($students) ?></span> dari <span class="font-bold"><?= $total_items ?></span> santri
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('/students/trash?q=' . urlencode($q) . '&page=' . ($page - 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                            <i class="ri-arrow-left-s-line"></i> Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <div class="flex items-center px-4 text-sm font-bold text-gray-500">
                        Halaman <?= $page ?> dari <?= $total_pages ?>
                    </div>

                    <?php if ($page < $total_pages): ?>
                        <a href="<?= url('/students/trash?q=' . urlencode($q) . '&page=' . ($page + 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                            Selanjutnya <i class="ri-arrow-right-s-line"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmForceDelete(id, name) {
            if (confirm('Apakah Anda yakin ingin menghapus PERMANEN data santri "' + name + '"?\n\nData ini TIDAK AKAN bisa dikembalikan lagi.')) {
                window.location.href = '<?= url("/students/forceDelete?id=") ?>' + id;
            }
        }
    </script>

</main>
