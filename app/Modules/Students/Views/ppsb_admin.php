<?php 
$currentQuery = $_GET;
unset($currentQuery['url']);
$returnUrl = '/admin/ppsb' . (empty($currentQuery) ? '' : '?' . http_build_query($currentQuery));
$returnUrlEncoded = urlencode($returnUrl);

// Helper function for sorting
function sortUrl($field, $currentSort, $currentDir, $q, $status) {
    $dir = 'asc';
    if ($currentSort === $field && $currentDir === 'asc') {
        $dir = 'desc';
    }
    return url('/admin/ppsb?q=' . urlencode($q) . '&status=' . urlencode($status) . '&sort=' . urlencode($field) . '&dir=' . urlencode($dir));
}
function sortIcon($field, $currentSort, $currentDir) {
    if ($currentSort !== $field) return '<i class="ri-expand-up-down-line text-gray-300 ml-1"></i>';
    return $currentDir === 'asc' ? '<i class="ri-sort-asc text-indigo-500 ml-1"></i>' : '<i class="ri-sort-desc text-indigo-500 ml-1"></i>';
}
?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Pusat Pendaftaran PPSB</h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1 md:mt-0">Kelola dan evaluasi data pendaftar santri baru.</p>
        </div>
        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            <a href="<?= url('/admin/ppsb/statistik') ?>" class="flex-1 md:flex-none justify-center px-3 py-2 bg-amber-500 text-white rounded-md text-xs md:text-sm font-bold hover:bg-amber-600 flex items-center shadow-sm transition-colors whitespace-nowrap">
                <i class="ri-bar-chart-box-line mr-1.5"></i> Statistik
            </a>
            <button onclick="openImportCsvModal()" class="flex-1 md:flex-none justify-center px-3 py-2 bg-emerald-600 text-white rounded-md text-xs md:text-sm font-medium hover:bg-emerald-700 flex items-center shadow-sm whitespace-nowrap">
                <i class="ri-file-excel-2-line mr-1.5"></i> Import CSV
            </button>
            <a href="<?= url('/ppsb/daftar') ?>" target="_blank" class="w-full md:w-auto justify-center px-3 py-2 bg-indigo-600 text-white rounded-md text-xs md:text-sm font-medium hover:bg-indigo-700 flex items-center shadow-sm whitespace-nowrap">
                <i class="ri-external-link-line mr-1.5"></i> Buka Link PPSB
            </a>
        </div>
    </div>

    <!-- Counters Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
        <div class="bg-white p-3 md:p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-2 md:gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center text-lg md:text-xl font-bold"><i class="ri-time-line"></i></div>
            <div>
                <div class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-wider leading-tight">Menunggu Evaluasi</div>
                <div class="text-lg font-black text-gray-900 mt-0.5">
                    <?= $count_pending ?>
                </div>
            </div>
        </div>
        <div class="bg-white p-3 md:p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-2 md:gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-lg md:text-xl font-bold"><i class="ri-checkbox-circle-line"></i></div>
            <div>
                <div class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-wider leading-tight">Dinyatakan Lulus</div>
                <div class="text-lg font-black text-gray-900 mt-0.5">
                    <?= $count_passed ?>
                </div>
            </div>
        </div>
        <div class="bg-white p-3 md:p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-2 md:gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg md:text-xl font-bold"><i class="ri-user-check-line"></i></div>
            <div>
                <div class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-wider leading-tight">Sudah Masuk Kelas</div>
                <div class="text-lg font-black text-gray-900 mt-0.5">
                    <?= $count_enrolled ?>
                </div>
            </div>
        </div>
        <div class="bg-white p-3 md:p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-2 md:gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-lg md:text-xl font-bold"><i class="ri-close-circle-line"></i></div>
            <div>
                <div class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-wider leading-tight">Tidak Lulus</div>
                <div class="text-lg font-black text-gray-900 mt-0.5">
                    <?= $count_failed ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <form action="<?= url('/admin/ppsb') ?>" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Cari Nama / No Registrasi / Wali</label>
                <div class="relative">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" 
                           placeholder="Ketik nama atau nomor registrasi..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>
            <div class="w-48">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status Evaluasi</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">Semua Status</option>
                    <option value="Pending" <?= $selected_status === 'Pending' ? 'selected' : '' ?>>Pending (Menunggu)</option>
                    <option value="Passed" <?= $selected_status === 'Passed' ? 'selected' : '' ?>>Passed (Lulus)</option>
                    <option value="Enrolled" <?= $selected_status === 'Enrolled' ? 'selected' : '' ?>>Enrolled (Masuk Kelas)</option>
                    <option value="Failed" <?= $selected_status === 'Failed' ? 'selected' : '' ?>>Failed (Tidak Lulus)</option>
                    <option value="Deleted" <?= $selected_status === 'Deleted' ? 'selected' : '' ?>>Deleted (Terhapus / Sampah)</option>
                    <option value="All" <?= $selected_status === 'All' ? 'selected' : '' ?>>Semua Termasuk Terhapus</option>
                </select>
            </div>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors flex items-center shadow-md">
                <i class="ri-filter-3-line mr-2"></i> Tampilkan
            </button>
            <?php if (!empty($q) || !empty($selected_status)): ?>
                <a href="<?= url('/admin/ppsb') ?>" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm font-medium">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Bulk Action Bar -->
    <div id="bulkActionBar" class="hidden mb-4 p-4 bg-indigo-50 border border-indigo-100 rounded-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
        <div class="text-sm font-semibold text-indigo-800 flex items-center">
            <span id="selectedCount" class="w-6 h-6 flex items-center justify-center bg-white text-indigo-600 rounded-full mr-2 shadow-sm">0</span> 
            santri dipilih
        </div>
        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            <form action="<?= url('/admin/ppsb/bulk') ?>" method="POST" id="bulkForm" class="hidden">
                <?= csrf_input() ?>
                <input type="hidden" name="action" id="bulkActionType">
                <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
            </form>
            <button onclick="confirmBulk('Passed')" class="flex-1 md:flex-none justify-center px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs md:text-sm font-bold shadow-sm hover:bg-green-700 transition-colors whitespace-nowrap"><i class="ri-check-line mr-1"></i> Luluskan</button>
            <button onclick="confirmBulk('Failed')" class="flex-1 md:flex-none justify-center px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs md:text-sm font-bold shadow-sm hover:bg-red-700 transition-colors whitespace-nowrap"><i class="ri-close-line mr-1"></i> Gagalkan</button>
            <button onclick="confirmBulk('Pending')" class="flex-1 md:flex-none justify-center px-3 py-1.5 bg-yellow-600 text-white rounded-lg text-xs md:text-sm font-bold shadow-sm hover:bg-yellow-700 transition-colors whitespace-nowrap"><i class="ri-refresh-line mr-1"></i> Reset</button>
            <?php if ($selected_status === 'Deleted'): ?>
                <button onclick="confirmBulk('ForceDelete')" class="flex-1 md:flex-none justify-center px-3 py-1.5 bg-red-800 text-white rounded-lg text-xs md:text-sm font-bold shadow-sm hover:bg-red-900 transition-colors whitespace-nowrap"><i class="ri-delete-bin-2-line mr-1"></i> Hapus Permanen</button>
            <?php else: ?>
                <button onclick="confirmBulk('Delete')" class="flex-1 md:flex-none justify-center px-3 py-1.5 bg-gray-600 text-white rounded-lg text-xs md:text-sm font-bold shadow-sm hover:bg-gray-700 transition-colors whitespace-nowrap"><i class="ri-delete-bin-line mr-1"></i> Hapus (Soft)</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left w-12">
                        <input type="checkbox" id="selectAll" onclick="toggleAll(this)" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4">
                    </th>
                    <th class="px-2 py-3 w-8 md:w-10 text-center text-[10px] md:text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                        <a href="<?= sortUrl('registration_no', $sort, $dir, $q, $selected_status) ?>" class="group inline-flex items-center hover:text-indigo-600 transition-colors">
                            No Registrasi <?= sortIcon('registration_no', $sort, $dir) ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="<?= sortUrl('nama', $sort, $dir, $q, $selected_status) ?>" class="group inline-flex items-center hover:text-indigo-600 transition-colors">
                            Calon Santri <?= sortIcon('nama', $sort, $dir) ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Orang Tua / Wali</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="<?= sortUrl('status', $sort, $dir, $q, $selected_status) ?>" class="group inline-flex items-center hover:text-indigo-600 transition-colors">
                            Status <?= sortIcon('status', $sort, $dir) ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                        <a href="<?= sortUrl('created_at', $sort, $dir, $q, $selected_status) ?>" class="group inline-flex items-center hover:text-indigo-600 transition-colors">
                            Data & Progres <?= sortIcon('created_at', $sort, $dir) ?>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php if (empty($registrations)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-20 text-center text-sm text-gray-400 italic">
                            Tidak ditemukan data pendaftar yang sesuai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registrations as $index => $r): ?>
                    <tr class="hover:bg-indigo-50/20 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap w-12">
                            <input type="checkbox" name="selected_ids[]" value="<?= $r['id'] ?>" data-name="<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>" class="row-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4" onchange="toggleBulkActionBar()">
                        </td>
                        <td class="px-2 py-4 whitespace-nowrap text-center">
                            <a href="<?= url('/admin/ppsb/edit?id=' . $r['id']) ?>&return_url=<?= $returnUrlEncoded ?>" class="text-yellow-500 hover:text-yellow-600 text-base md:text-lg transition-colors" title="Edit Data">
                                <i class="ri-pencil-fill"></i>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                            <div class="text-sm text-gray-900 font-mono font-bold"><?= str_pad(($page - 1) * 10 + $index + 1, 3, '0', STR_PAD_LEFT) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                            <span class="text-sm font-semibold text-slate-800 font-mono"><?= htmlspecialchars($r['registration_no']) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <?php $avatarBg = $r['gender'] === 'L' ? 'bg-blue-100 text-blue-600' : 'bg-pink-100 text-pink-600'; ?>
                                <div class="w-8 h-8 rounded-full <?= $avatarBg ?> flex items-center justify-center text-xs font-bold mr-3 shadow-sm">
                                    <?= mb_strtoupper(mb_substr($r['nama'], 0, 1)) ?>
                                </div>
                                <div>
                                    <button type="button" onclick="openDetailModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 text-left transition-colors cursor-pointer">
                                        <?= htmlspecialchars($r['nama']) ?>
                                    </button>
                                    <div class="text-[10px] text-gray-400 uppercase tracking-wider mb-1 mt-0.5">
                                        <?= htmlspecialchars($r['tempat_lahir'] ?? '-') . ', ' . ($r['tanggal_lahir'] ? date('d/m/Y', strtotime($r['tanggal_lahir'])) : '-') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                            <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($r['nama_wali'] ?? $r['nama_ayah'] ?? '-') ?></div>
                            <div class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($r['no_hp_wali'] ?? '-') ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (!empty($r['deleted_at'])): ?>
                                <span class="px-2 py-0.5 inline-flex text-[9px] leading-3 font-bold rounded-full bg-gray-100 text-gray-500 uppercase tracking-wider line-through">
                                    Terhapus
                                </span>
                            <?php elseif ($r['status'] === 'Pending'): ?>
                                <span class="px-2 py-0.5 inline-flex text-[9px] leading-3 font-bold rounded-full bg-yellow-100 text-yellow-800 uppercase tracking-wider">
                                    Pending
                                </span>
                            <?php elseif ($r['status'] === 'Passed'): ?>
                                <div class="flex flex-col items-start gap-1.5">
                                    <span class="px-2 py-0.5 inline-flex text-[9px] leading-3 font-bold rounded-full bg-green-100 text-green-800 uppercase tracking-wider">
                                        Lulus Ujian
                                    </span>
                                    <button onclick="openEnrollModal(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['nama'])) ?>', '<?= $r['gender'] ?>')" class="px-2 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded text-[10px] font-bold border border-indigo-200 transition-colors shadow-sm">
                                        <i class="ri-community-line"></i> Set Kelas
                                    </button>
                                </div>
                            <?php elseif ($r['status'] === 'Failed'): ?>
                                <span class="px-2 py-0.5 inline-flex text-[9px] leading-3 font-bold rounded-full bg-red-100 text-red-800 uppercase tracking-wider">
                                    Gagal
                                </span>
                            <?php elseif ($r['status'] === 'Enrolled'): ?>
                                <div class="flex flex-col items-start gap-1.5">
                                    <span class="px-2 py-0.5 inline-flex text-[9px] leading-3 font-bold rounded-full bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                                        Enrolled
                                    </span>
                                    <?php if (!empty($r['student_id'])): ?>
                                        <div class="flex items-center gap-1">
                                            <a href="<?= url('/students/history?id=' . $r['student_id']) ?>" class="px-2 py-1 bg-slate-50 text-slate-700 hover:bg-slate-100 rounded text-[10px] font-bold border border-slate-200 transition-colors shadow-sm inline-flex items-center">
                                                <i class="ri-user-smile-line mr-0.5"></i> Lihat Santri
                                            </a>
                                            <form action="<?= url('/admin/ppsb/cancel-enroll') ?>" method="POST" onsubmit="return confirm('Batalkan penempatan kelas? Data santri definitif akan dihapus permanen.');" class="inline">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
                                                <button type="submit" class="px-2 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded text-[10px] font-bold border border-red-200 transition-colors shadow-sm inline-flex items-center" title="Batalkan Penempatan">
                                                    <i class="ri-close-circle-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                            <div class="text-xs text-gray-500 font-mono mb-2">
                                <?= date('d M Y, H:i', strtotime($r['created_at'])) ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: <?= $r['completeness'] ?? 0 ?>%"></div>
                                </div>
                                <span class="text-[10px] font-bold text-gray-500"><?= $r['completeness'] ?? 0 ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="bg-gray-50 px-4 md:px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Menampilkan <span class="font-bold"><?= count($registrations) ?></span> dari <span class="font-bold"><?= $total_items ?></span> pendaftar
                </div>
                <div class="flex flex-wrap justify-center gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('/admin/ppsb?q=' . urlencode($q) . '&status=' . urlencode($selected_status) . '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) . '&page=' . ($page - 1)) ?>" 
                           class="px-3 md:px-4 py-2 border border-gray-300 rounded-lg text-xs md:text-sm font-medium hover:bg-gray-100 transition-colors">
                            Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <div class="flex items-center px-2 md:px-4 text-xs md:text-sm font-bold text-gray-500">
                        Halaman <?= $page ?> dari <?= $total_pages ?>
                    </div>

                    <?php if ($page < $total_pages): ?>
                        <a href="<?= url('/admin/ppsb?q=' . urlencode($q) . '&status=' . urlencode($selected_status) . '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir) . '&page=' . ($page + 1)) ?>" 
                           class="px-3 md:px-4 py-2 border border-gray-300 rounded-lg text-xs md:text-sm font-medium hover:bg-gray-100 transition-colors">
                            Selanjutnya
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Class Placement Modal -->
    <div id="enrollModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEnrollModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full max-w-lg mx-auto">
                <form action="<?= url('/admin/ppsb/enroll') ?>" method="POST" id="enrollForm">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" id="enroll_reg_id">
                    <input type="hidden" name="academic_year_id" value="<?= $targetYear['id'] ?? '' ?>">
                    <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">

                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <i class="ri-community-line text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Penempatan Kelas Santri</h3>
                                <p class="text-sm text-gray-500">Masukkan nomor NIS dan tentukan kelas awal pendaftar baru.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Calon Santri</label>
                                <input type="text" id="enroll_student_name" readonly class="w-full px-3 py-2 bg-slate-50 border border-gray-200 rounded-lg text-sm text-gray-600 font-bold outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                                    <input type="text" id="enroll_student_gender" readonly class="w-full px-3 py-2 bg-slate-50 border border-gray-200 rounded-lg text-sm text-gray-600 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tahun Ajaran (Tujuan)</label>
                                    <input type="text" readonly value="<?= htmlspecialchars($targetYear['name'] ?? 'None') ?>" class="w-full px-3 py-2 bg-slate-50 border border-gray-200 rounded-lg text-sm text-indigo-600 font-bold outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nomor Induk Santri (NIS) <span class="text-red-500">*</span></label>
                                <input type="text" name="nis" id="enroll_nis" required 
                                       placeholder="Ketik NIS untuk santri..." 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono font-bold">
                                <p class="text-[10px] text-indigo-600 mt-1 font-semibold">Telah disugestikan otomatis berdasarkan format NIS tahun ajaran saat ini.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tentukan Kelas <span class="text-red-500">*</span></label>
                                <select name="kelas_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php foreach ($kelas as $k): ?>
                                        <option value="<?= $k['id'] ?>">
                                            Kelas <?= htmlspecialchars($k['tingkat'] . '-' . $k['abjad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" onclick="closeEnrollModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-all shadow-md">
                            Tempatkan Kelas & Enroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const suggestedNis = "<?= $nextNis ?>";

        function toggleAll(source) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
            toggleBulkActionBar();
        }

        function toggleBulkActionBar() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const actionBar = document.getElementById('bulkActionBar');
            const countSpan = document.getElementById('selectedCount');
            
            if (checkboxes.length > 0) {
                countSpan.innerText = checkboxes.length;
                actionBar.classList.remove('hidden');
            } else {
                actionBar.classList.add('hidden');
                document.getElementById('selectAll').checked = false;
            }
        }

        let pendingBulkAction = '';

        function confirmBulk(action) {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            if (checkboxes.length === 0) return;
            
            pendingBulkAction = action;
            
            let actionText = '';
            if (action === 'Passed') actionText = 'Meluluskan';
            else if (action === 'Failed') actionText = 'Menggagalkan';
            else if (action === 'Pending') actionText = 'Reset Pending';
            else if (action === 'Delete') actionText = 'Menghapus (Soft)';
            else if (action === 'ForceDelete') actionText = 'Menghapus PERMANEN (Tak Bisa Kembali)';
            
            document.getElementById('bulkConfirmTitle').innerText = 'Konfirmasi ' + actionText;
            
            const list = document.getElementById('bulkConfirmList');
            list.innerHTML = '';
            checkboxes.forEach(cb => {
                const li = document.createElement('li');
                li.className = 'text-sm text-gray-700 py-1 border-b border-gray-100 last:border-0';
                li.innerText = cb.getAttribute('data-name');
                list.appendChild(li);
            });
            
            document.getElementById('bulkConfirmModal').classList.remove('hidden');
        }

        function closeBulkConfirmModal() {
            document.getElementById('bulkConfirmModal').classList.add('hidden');
        }
        
        function executeBulk() {
            submitBulk(pendingBulkAction);
        }

        function submitBulk(action) {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            if (checkboxes.length === 0) return;

            const form = document.getElementById('bulkForm');
            document.getElementById('bulkActionType').value = action;
            
            // Remove any old hidden inputs
            form.querySelectorAll('.bulk-input').forEach(el => el.remove());

            // Add new ones
            checkboxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = cb.value;
                input.className = 'bulk-input';
                form.appendChild(input);
            });

            form.submit();
        }

        function openEnrollModal(id, name, gender) {
            document.getElementById('enroll_reg_id').value = id;
            document.getElementById('enroll_student_name').value = name;
            document.getElementById('enroll_student_gender').value = (gender === 'L' ? 'Laki-laki' : 'Perempuan');
            document.getElementById('enroll_nis').value = suggestedNis;
            
            document.getElementById('enrollModal').classList.remove('hidden');
        }

        function closeEnrollModal() {
            document.getElementById('enrollModal').classList.add('hidden');
        }

        function openImportCsvModal() {
            document.getElementById('importCsvModal').classList.remove('hidden');
        }

        function closeImportCsvModal() {
            document.getElementById('importCsvModal').classList.add('hidden');
        }

        function openDetailModal(data) {
            document.getElementById('detail_nama').innerText = data.nama || '-';
            document.getElementById('detail_registration_no').innerText = data.registration_no || '-';
            
            document.getElementById('detail_nik').innerText = data.nik || '-';
            document.getElementById('detail_nisn').innerText = data.nisn || '-';
            document.getElementById('detail_gender').innerText = data.gender === 'L' ? 'Laki-laki' : 'Perempuan';
            
            let tgl = data.tanggal_lahir ? data.tanggal_lahir : '-'; // Already Y-m-d, can be formatted further if needed
            document.getElementById('detail_ttl').innerText = (data.tempat_lahir || '-') + ', ' + tgl;
            
            document.getElementById('detail_alamat').innerText = data.alamat || '-';
            document.getElementById('detail_rt_rw').innerText = data.rt_rw || '-';
            document.getElementById('detail_kelurahan').innerText = data.kelurahan || '-';
            document.getElementById('detail_kecamatan').innerText = data.kecamatan || '-';
            document.getElementById('detail_kabupaten').innerText = data.kabupaten || '-';
            document.getElementById('detail_provinsi').innerText = data.provinsi || '-';
            document.getElementById('detail_kode_pos').innerText = data.kode_pos || '-';
            
            document.getElementById('detail_nama_kk').innerText = data.nama_kk || '-';
            document.getElementById('detail_ayah').innerText = data.nama_wali || '-';
            document.getElementById('detail_pekerjaan_ayah').innerText = data.pekerjaan_ayah || '-';
            document.getElementById('detail_hp_ayah').innerText = data.no_hp_ayah || '-';
            
            document.getElementById('detail_ibu').innerText = data.nama_ibu || '-';
            document.getElementById('detail_pekerjaan_ibu').innerText = data.pekerjaan_ibu || '-';
            document.getElementById('detail_hp_ibu').innerText = data.no_hp_ibu || '-';
            
            document.getElementById('detailModal').classList.remove('hidden');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }
    </script>

    <!-- Import CSV Modal -->
    <div id="importCsvModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeImportCsvModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full max-w-lg mx-auto">
                <form action="<?= url('/admin/ppsb/import-csv') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_input() ?>

                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                <i class="ri-file-excel-2-line text-emerald-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Import Data dari Smart System (CSV)</h3>
                                <p class="text-sm text-gray-500">Unggah file CSV (hasil Export dari aplikasi Smart System) untuk mengimpor santri baru PPSB.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Pilih File CSV <span class="text-red-500">*</span></label>
                                <input type="file" name="csv_file" required accept=".csv"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                <p class="text-[10px] text-gray-500 mt-1">Pastikan format kolom sesuai dengan template Excel bawaan (Header di baris 4 dan 5).</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" onclick="closeImportCsvModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all shadow-md">
                            Mulai Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDetailModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full max-w-2xl mx-auto">
                <div class="bg-white px-6 pt-6 pb-6">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <i class="ri-user-search-line text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900" id="detail_nama">Nama Santri</h3>
                                <p class="text-sm font-mono text-gray-500" id="detail_registration_no">NO REG</p>
                            </div>
                        </div>
                        <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-500">
                            <i class="ri-close-line text-2xl"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <!-- Data Pribadi -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider border-b border-gray-100 pb-1">Data Pribadi</h4>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">NIK</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_nik"></span>
                            </div>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">NISN</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_nisn"></span>
                            </div>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">Gender</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_gender"></span>
                            </div>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">TTL</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_ttl"></span>
                            </div>
                        </div>

                        <!-- Data Alamat -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider border-b border-gray-100 pb-1">Data Alamat</h4>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">Alamat</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_alamat"></span>
                            </div>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">RT/RW</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_rt_rw"></span>
                            </div>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">Kelurahan</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_kelurahan"></span>
                            </div>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">Kecamatan</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_kecamatan"></span>
                            </div>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">Kabupaten</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_kabupaten"></span>
                            </div>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">Provinsi</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_provinsi"></span>
                            </div>
                            <div class="grid grid-cols-3 text-sm">
                                <span class="text-gray-500 col-span-1">Kode Pos</span>
                                <span class="font-semibold text-gray-900 col-span-2" id="detail_kode_pos"></span>
                            </div>
                        </div>

                        <!-- Data Orang Tua -->
                        <div class="space-y-3 md:col-span-2 mt-2">
                            <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider border-b border-gray-100 pb-1">Data Orang Tua / Wali</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="grid grid-cols-3 text-sm mb-2">
                                        <span class="text-gray-500 col-span-1">Nama KK</span>
                                        <span class="font-semibold text-gray-900 col-span-2" id="detail_nama_kk"></span>
                                    </div>
                                    <div class="grid grid-cols-3 text-sm mb-2">
                                        <span class="text-gray-500 col-span-1">Ayah/Wali</span>
                                        <span class="font-semibold text-gray-900 col-span-2" id="detail_ayah"></span>
                                    </div>
                                    <div class="grid grid-cols-3 text-sm mb-2">
                                        <span class="text-gray-500 col-span-1">Pekerjaan</span>
                                        <span class="font-semibold text-gray-900 col-span-2" id="detail_pekerjaan_ayah"></span>
                                    </div>
                                    <div class="grid grid-cols-3 text-sm mb-2">
                                        <span class="text-gray-500 col-span-1">HP Ayah</span>
                                        <span class="font-mono font-semibold text-gray-900 col-span-2" id="detail_hp_ayah"></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="grid grid-cols-3 text-sm mb-2">
                                        <span class="text-gray-500 col-span-1">Ibu</span>
                                        <span class="font-semibold text-gray-900 col-span-2" id="detail_ibu"></span>
                                    </div>
                                    <div class="grid grid-cols-3 text-sm mb-2">
                                        <span class="text-gray-500 col-span-1">Pekerjaan</span>
                                        <span class="font-semibold text-gray-900 col-span-2" id="detail_pekerjaan_ibu"></span>
                                    </div>
                                    <div class="grid grid-cols-3 text-sm mb-2">
                                        <span class="text-gray-500 col-span-1">HP Ibu</span>
                                        <span class="font-mono font-semibold text-gray-900 col-span-2" id="detail_hp_ibu"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

    <!-- Bulk Confirm Modal -->
    <div id="bulkConfirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeBulkConfirmModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full max-w-lg mx-auto">
                <div class="bg-white px-6 pt-6 pb-6">
                    <div class="flex items-center gap-3 mb-4 border-b border-gray-100 pb-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <i class="ri-list-check text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900" id="bulkConfirmTitle">Konfirmasi Aksi</h3>
                            <p class="text-sm text-gray-500">Daftar santri yang akan diproses:</p>
                        </div>
                    </div>
                    
                    <div class="max-h-60 overflow-y-auto mb-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <ul id="bulkConfirmList" class="space-y-1">
                            <!-- Injected by JS -->
                        </ul>
                    </div>
                    <p class="text-xs text-red-500 font-semibold">Tindakan ini tidak bisa dibatalkan secara langsung. Apakah Anda yakin ingin melanjutkan?</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" onclick="closeBulkConfirmModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all">
                        Batal
                    </button>
                    <button type="button" onclick="executeBulk()" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-all shadow-md">
                        Ya, Eksekusi Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
