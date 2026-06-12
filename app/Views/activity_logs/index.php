<?php renderHeader($title); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumbs & Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="<?= url('/') ?>" class="hover:text-indigo-600">Dashboard</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-indigo-600 font-semibold">Log Aktivitas</span>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <i class="ri-file-list-2-line text-lg text-indigo-600"></i>
                </div>
                Audit Log Aktivitas Sistem
            </h2>
            <p class="text-gray-500 text-sm mt-1">Daftar rekaman tindakan pengajar dan administrator di dalam aplikasi.</p>
        </div>
    </div>

    <!-- Search Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <form action="<?= url('/activity-logs') ?>" method="GET" class="flex flex-col sm:flex-row items-stretch gap-3">
            <div class="flex-1 relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>" 
                       placeholder="Cari pelaku, tindakan, halaman, IP..." 
                       class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50/30">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors flex items-center justify-center shadow-md shadow-indigo-100">
                    <i class="ri-filter-3-line mr-1.5"></i> Tampilkan Log
                </button>
                <?php if (!empty($q)): ?>
                    <a href="<?= url('/activity-logs') ?>" class="px-4 py-2 border border-gray-200 text-gray-500 hover:bg-gray-50 rounded-lg text-sm font-medium flex items-center justify-center">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Table Card (Full Width) -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Pelaku</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Tindakan / Aktivitas</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Halaman</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-indigo-50 text-indigo-300 rounded-full flex items-center justify-center mb-3">
                                        <i class="ri-history-line text-3xl"></i>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900">Belum ada Log</h3>
                                    <p class="text-xs text-gray-400 mt-1">Tidak ada catatan log aktivitas yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $index => $log): 
                            $role = strtolower($log['role'] ?? '');
                            $roleBadgeClass = $role === 'admin' ? 'bg-red-50 text-red-700 border-red-100' : ($role === 'pengajar' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-gray-50 text-gray-600 border-gray-100');
                            
                            $action = strtolower($log['action'] ?? '');
                            $actionColorClass = 'text-gray-800';
                            if (strpos($action, 'login') !== false) {
                                $actionColorClass = 'text-emerald-700 font-medium';
                            } elseif (strpos($action, 'hapus') !== false || strpos($action, 'nonaktif') !== false) {
                                $actionColorClass = 'text-red-700 font-medium';
                            } elseif (strpos($action, 'tambah') !== false || strpos($action, 'membuat') !== false) {
                                $actionColorClass = 'text-indigo-700 font-medium';
                            } elseif (strpos($action, 'simpan') !== false || strpos($action, 'update') !== false || strpos($action, 'memperbarui') !== false) {
                                $actionColorClass = 'text-blue-700 font-medium';
                            }
                        ?>
                        <tr class="hover:bg-indigo-50/20 transition-colors cursor-pointer group" onclick='showLogDetail(<?= json_encode($log, ENT_QUOTES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 font-mono">
                                <?= str_pad($offset + $index + 1, 4, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-xs font-bold">
                                        <?= mb_strtoupper(mb_substr($log['nama'] ?? $log['username'] ?? '?', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 leading-tight"><?= htmlspecialchars($log['nama'] ?? 'Guest') ?></div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-[10px] text-gray-400 font-mono">@<?= htmlspecialchars($log['username'] ?? 'guest') ?></span>
                                            <span class="px-1.5 py-0.2 border text-[8px] font-semibold rounded-full capitalize <?= $roleBadgeClass ?>">
                                                <?= htmlspecialchars($log['role'] ?? 'Guest') ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm <?= $actionColorClass ?>" title="<?= htmlspecialchars($log['action']) ?>">
                                <?= htmlspecialchars($log['action']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono" title="<?= htmlspecialchars($log['page']) ?>">
                                <?= htmlspecialchars($log['page']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                <span class="px-2.5 py-1 bg-gray-100 rounded-lg text-[10px] text-gray-600 font-semibold">
                                    <?= htmlspecialchars($log['ip_address'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400">
                                <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-gray-50/50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-xs text-gray-500">
                    Menampilkan <span class="font-bold text-gray-700"><?= count($logs) ?></span> dari <span class="font-bold text-gray-700"><?= $totalData ?></span> entri log
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('/activity-logs?q=' . urlencode($q) . '&page=' . ($page - 1)) ?>" 
                           class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-semibold hover:bg-white text-gray-600 shadow-sm transition-all">
                            <i class="ri-arrow-left-s-line"></i> Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <div class="flex items-center px-4 text-xs font-bold text-gray-400">
                        Halaman <?= $page ?> dari <?= $totalPages ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('/activity-logs?q=' . urlencode($q) . '&page=' . ($page + 1)) ?>" 
                           class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-semibold hover:bg-white text-gray-600 shadow-sm transition-all">
                            Selanjutnya <i class="ri-arrow-right-s-line"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- ─── Modal: Detail Log Aktivitas ─────────────────────────────────── -->
<div id="logDetailModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeLogModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        
        <!-- Modal Panel -->
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg sm:my-8 sm:align-middle mx-auto animate-in fade-in zoom-in duration-200">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4 flex items-center justify-between text-white">
                <h3 class="text-base font-bold flex items-center gap-2">
                    <i class="ri-file-list-2-line text-lg"></i>
                    Detail Log Aktivitas
                </h3>
                <button onclick="closeLogModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="px-6 py-5 space-y-4">
                <!-- Row: Pelaku -->
                <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-50">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pelaku</span>
                    <div class="col-span-2 text-sm text-gray-800">
                        <span class="font-bold" id="detailNama"></span> 
                        <span class="text-xs text-gray-400 font-mono block" id="detailUsername"></span>
                    </div>
                </div>

                <!-- Row: Role -->
                <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-50">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Role Hak Akses</span>
                    <div class="col-span-2 text-sm text-gray-800">
                        <span class="px-2 py-0.5 border text-xs font-semibold rounded-full capitalize bg-indigo-50 text-indigo-700 border-indigo-100" id="detailRole"></span>
                    </div>
                </div>

                <!-- Row: Aksi -->
                <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-50">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tindakan (Aksi)</span>
                    <div class="col-span-2 text-sm font-semibold text-gray-900 bg-gray-50 p-2.5 rounded-lg border border-gray-100" id="detailAksi"></div>
                </div>

                <!-- Row: Halaman -->
                <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-50">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Halaman (URL)</span>
                    <div class="col-span-2 text-xs text-indigo-600 font-mono break-all" id="detailHalaman"></div>
                </div>

                <!-- Row: IP Address -->
                <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-50">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">IP Address</span>
                    <div class="col-span-2 text-sm text-gray-800 font-mono" id="detailIP"></div>
                </div>

                <!-- Row: Waktu -->
                <div class="grid grid-cols-3 gap-2 py-2 border-b border-gray-50">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Waktu Kejadian</span>
                    <div class="col-span-2 text-sm text-gray-800" id="detailWaktu"></div>
                </div>

                <!-- Row: User Agent -->
                <div class="space-y-1 py-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">User Agent (Browser & Device)</span>
                    <div class="text-[11px] text-gray-500 font-mono bg-gray-50 p-3 rounded-lg border border-gray-100 break-words leading-relaxed" id="detailUserAgent"></div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button type="button" onclick="closeLogModal()" class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-xl shadow-sm transition-all">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showLogDetail(log) {
        document.getElementById('detailNama').textContent = log.nama || 'Guest';
        document.getElementById('detailUsername').textContent = '@' + (log.username || 'guest');
        document.getElementById('detailRole').textContent = log.role || 'Guest';
        document.getElementById('detailAksi').textContent = log.action;
        document.getElementById('detailHalaman').textContent = log.page;
        document.getElementById('detailIP').textContent = log.ip_address || '-';
        
        // Format Date
        const dateObj = new Date(log.created_at);
        const formattedDate = dateObj.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        document.getElementById('detailWaktu').textContent = formattedDate + ' WIB';
        document.getElementById('detailUserAgent').textContent = log.user_agent || '-';
        
        document.getElementById('logDetailModal').classList.remove('hidden');
    }

    function closeLogModal() {
        document.getElementById('logDetailModal').classList.add('hidden');
    }
</script>

<?php renderFooter(); ?>
