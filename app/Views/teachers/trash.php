<?php
// app/Views/teachers/trash.php
?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tempat Sampah Pengajar</h1>
            <p class="text-sm text-gray-500">Daftar pengajar yang telah dihapus.</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= url('/teachers') ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 flex items-center shadow-sm">
                <i class="ri-arrow-left-line mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <form action="<?= url('/teachers/trash') ?>" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Cari Nama / No HP</label>
                <div class="relative">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>" 
                           placeholder="Ketik nama atau no hp..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors flex items-center shadow-md">
                <i class="ri-filter-3-line mr-2"></i> Cari di Sampah
            </button>
            <?php if (!empty($q)): ?>
                <a href="<?= url('/teachers/trash') ?>" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm font-medium">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden ring-1 ring-black ring-opacity-5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pengajar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. HP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dihapus Pada</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($displayPengajar)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mb-4">
                                        <i class="ri-delete-bin-line text-5xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">Tempat Sampah Kosong</h3>
                                    <p class="text-sm text-gray-500">Tidak ada data pengajar di tempat sampah.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($displayPengajar as $index => $p): 
                            $id = $p['id'];
                        ?>
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                                <?= str_pad(($page - 1) * $perPage + $index + 1, 3, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-xs font-bold mr-3">
                                        <?= mb_strtoupper(mb_substr($p['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($p['nama']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($p['hp'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 font-medium">
                                <?= date('d M Y H:i', strtotime($p['deleted_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2 items-center">
                                    <button onclick="confirmRestore(<?= $id ?>, '<?= addslashes($p['nama']) ?>')"
                                            class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Pulihkan Data">
                                        <i class="ri-refresh-line text-lg"></i>
                                    </button>
                                    <button onclick="confirmForceDelete(<?= $id ?>, '<?= addslashes($p['nama']) ?>')"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Permanen">
                                        <i class="ri-delete-bin-2-line text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Menampilkan <span class="font-bold"><?= count($displayPengajar) ?></span> dari <span class="font-bold"><?= $totalData ?></span> pengajar
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('/teachers/trash?q=' . urlencode($q) . '&page=' . ($page - 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                            <i class="ri-arrow-left-s-line"></i> Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <div class="flex items-center px-4 text-sm font-bold text-gray-500">
                        Halaman <?= $page ?> dari <?= $totalPages ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('/teachers/trash?q=' . urlencode($q) . '&page=' . ($page + 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                            Selanjutnya <i class="ri-arrow-right-s-line"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmRestore(id, name) {
            if (confirm('Apakah Anda yakin ingin memulihkan data pengajar "' + name + '"?')) {
                window.location.href = '<?= url("/teachers/restore?id=") ?>' + id;
            }
        }

        function confirmForceDelete(id, name) {
            if (confirm('PERINGATAN!\n\nApakah Anda yakin ingin menghapus data pengajar "' + name + '" SECARA PERMANEN?\n\nData yang dihapus permanen TIDAK DAPAT dikembalikan.')) {
                window.location.href = '<?= url("/teachers/forceDelete?id=") ?>' + id;
            }
        }
    </script>
</main>
