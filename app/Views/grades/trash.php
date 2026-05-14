<?php 
renderHeader("Tong Sampah - Koreksi Ujian"); 
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="<?= url('/grades') ?>" class="hover:text-indigo-600 transition-colors">Daftar Koreksi Ujian</a>
                <i class="ri-arrow-right-s-line"></i>
                <span class="text-gray-900 font-medium">Tong Sampah</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
                    <i class="ri-delete-bin-line"></i>
                </div>
                Tong Sampah Koreksi Ujian
            </h1>
        </div>
    </div>

    <!-- Alert -->
    <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm p-4 rounded-xl flex items-start gap-3">
        <i class="ri-error-warning-fill text-yellow-500 text-lg"></i>
        <div>
            <p class="font-bold mb-1">Perhatian!</p>
            <p>Data di bawah ini adalah data koreksi ujian yang telah dihapus sementara (*soft-delete*). Anda dapat mengembalikannya (Restore) atau menghapusnya secara permanen. <strong>Menghapus permanen akan menghilangkan seluruh data nilai santri untuk ujian tersebut secara permanen.</strong></p>
        </div>
    </div>

    <!-- List -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-bold">Mata Pelajaran</th>
                        <th class="px-6 py-4 font-bold">Kelas</th>
                        <th class="px-6 py-4 font-bold">Pengajar</th>
                        <th class="px-6 py-4 font-bold">Sesi Ujian</th>
                        <th class="px-6 py-4 font-bold">Waktu Hapus</th>
                        <th class="px-6 py-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($deletedExams)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="ri-inbox-line text-2xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-500">Tidak ada data ujian di tong sampah.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($deletedExams as $exam): ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900"><?= htmlspecialchars($exam['mapel_nama'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700">
                                        <?= htmlspecialchars(($exam['tingkat'] ?? '') . '-' . ($exam['abjad'] ?? '')) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <?= htmlspecialchars($exam['pengajar_nama'] ?? 'Tidak ada') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-gray-100 text-gray-600 uppercase tracking-widest">
                                        <?= htmlspecialchars($exam['exam_type'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 font-mono">
                                    <?= htmlspecialchars($exam['updated_at'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= url('/grades/restore?id=' . $exam['id']) ?>" 
                                           onclick="return confirm('Kembalikan data koreksi ini ke daftar utama?')"
                                           class="w-8 h-8 rounded-lg flex items-center justify-center text-green-600 hover:bg-green-50 transition-colors group relative">
                                            <i class="ri-refresh-line"></i>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-[10px] font-medium rounded whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity">
                                                Restore
                                            </div>
                                        </a>
                                        
                                        <a href="<?= url('/grades/forceDelete?id=' . $exam['id']) ?>" 
                                           onclick="return confirm('PERINGATAN: Aksi ini tidak dapat dibatalkan! Seluruh data nilai santri untuk ujian ini akan dihapus permanen. Lanjutkan?')"
                                           class="w-8 h-8 rounded-lg flex items-center justify-center text-red-600 hover:bg-red-50 transition-colors group relative">
                                            <i class="ri-delete-bin-2-line"></i>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-[10px] font-medium rounded whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity">
                                                Hapus Permanen
                                            </div>
                                        </a>
                                    </div>
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
