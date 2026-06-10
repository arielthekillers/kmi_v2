<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pusat Pendaftaran PPSB</h1>
            <p class="text-sm text-gray-500">Kelola dan evaluasi data pendaftar santri baru.</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= url('/ppsb/daftar') ?>" target="_blank" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 flex items-center shadow-sm">
                <i class="ri-external-link-line mr-2"></i> Buka Link Pendaftaran
            </a>
        </div>
    </div>

    <!-- Counters Row -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center text-xl font-bold"><i class="ri-time-line"></i></div>
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Menunggu Evaluasi</div>
                <div class="text-lg font-black text-gray-900 mt-0.5">
                    <?= $count_pending ?>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-xl font-bold"><i class="ri-checkbox-circle-line"></i></div>
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Dinyatakan Lulus</div>
                <div class="text-lg font-black text-gray-900 mt-0.5">
                    <?= $count_passed ?>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold"><i class="ri-user-check-line"></i></div>
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sudah Masuk Kelas</div>
                <div class="text-lg font-black text-gray-900 mt-0.5">
                    <?= $count_enrolled ?>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold"><i class="ri-close-circle-line"></i></div>
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tidak Lulus</div>
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
                </select>
            </div>
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

    <!-- Data Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Registrasi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Calon Santri</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orang Tua / Wali</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi Evaluasi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php if (empty($registrations)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center text-sm text-gray-400 italic">
                            Tidak ditemukan data pendaftar yang sesuai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registrations as $index => $r): ?>
                    <tr class="hover:bg-indigo-50/20 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                            <?= str_pad(($page - 1) * 10 + $index + 1, 3, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-slate-800 font-mono"><?= htmlspecialchars($r['registration_no']) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-indigo-600 flex items-center justify-center text-xs font-bold mr-3">
                                    <?= mb_strtoupper(mb_substr($r['nama'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($r['nama']) ?></div>
                                    <div class="text-[10px] text-gray-400 uppercase tracking-wider">
                                        <?= $r['gender'] === 'L' ? '<span class="text-blue-500">Laki-laki</span>' : '<span class="text-pink-500">Perempuan</span>' ?>
                                        &bull; lahir <?= htmlspecialchars($r['tempat_lahir']) . ', ' . date('d/m/Y', strtotime($r['tanggal_lahir'])) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-700 font-semibold"><?= htmlspecialchars($r['nama_wali']) ?></div>
                            <div class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($r['no_hp_wali']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($r['status'] === 'Pending'): ?>
                                <span class="px-2.5 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full bg-yellow-100 text-yellow-800 uppercase tracking-wider">
                                    Pending
                                </span>
                            <?php elseif ($r['status'] === 'Passed'): ?>
                                <span class="px-2.5 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full bg-green-100 text-green-800 uppercase tracking-wider">
                                    Lulus Ujian
                                </span>
                            <?php elseif ($r['status'] === 'Failed'): ?>
                                <span class="px-2.5 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full bg-red-100 text-red-800 uppercase tracking-wider">
                                    Gagal
                                </span>
                            <?php elseif ($r['status'] === 'Enrolled'): ?>
                                <span class="px-2.5 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                                    Enrolled
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                            <?= date('d M Y, H:i', strtotime($r['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <?php if ($r['status'] === 'Pending'): ?>
                                    <form action="<?= url('/admin/ppsb/status') ?>" method="POST" class="inline">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" name="status" value="Passed" class="px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg text-xs font-bold transition-all">
                                            <i class="ri-check-line mr-0.5"></i> Lulus
                                        </button>
                                        <button type="submit" name="status" value="Failed" class="px-3 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg text-xs font-bold transition-all" onclick="return confirm('Tolak pendaftaran ini?')">
                                            <i class="ri-close-line mr-0.5"></i> Gagal
                                        </button>
                                    </form>
                                <?php elseif ($r['status'] === 'Passed'): ?>
                                    <button onclick="openEnrollModal(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['nama'])) ?>', '<?= $r['gender'] ?>')" 
                                            class="px-3 py-1.5 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg text-xs font-bold shadow-md transition-all">
                                        <i class="ri-community-line mr-0.5"></i> Tempatkan Kelas
                                    </button>
                                    <form action="<?= url('/admin/ppsb/status') ?>" method="POST" class="inline">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" name="status" value="Failed" class="px-2.5 py-1.5 text-red-600 hover:bg-red-50 rounded-lg text-xs font-bold transition-all" onclick="return confirm('Tolak pendaftaran ini?')">
                                            Tolak
                                        </button>
                                    </form>
                                <?php elseif ($r['status'] === 'Failed'): ?>
                                    <form action="<?= url('/admin/ppsb/status') ?>" method="POST" class="inline">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" name="status" value="Pending" class="px-2.5 py-1.5 text-gray-500 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-all">
                                            Reset Pending
                                        </button>
                                    </form>
                                <?php elseif ($r['status'] === 'Enrolled' && !empty($r['student_id'])): ?>
                                    <a href="<?= url('/students/history?id=' . $r['student_id']) ?>" class="px-3 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-xs font-bold transition-all inline-flex items-center">
                                        Lihat Santri <i class="ri-arrow-right-s-line ml-0.5"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?= url('/admin/ppsb/delete?id=' . $r['id']) ?>" 
                                   class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" 
                                   onclick="return confirm('Hapus pendaftaran ini secara permanen?')">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Menampilkan <span class="font-bold"><?= count($registrations) ?></span> dari <span class="font-bold"><?= $total_items ?></span> pendaftar
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('/admin/ppsb?q=' . urlencode($q) . '&status=' . $selected_status . '&page=' . ($page - 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                            Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <div class="flex items-center px-4 text-sm font-bold text-gray-500">
                        Halaman <?= $page ?> dari <?= $total_pages ?>
                    </div>

                    <?php if ($page < $total_pages): ?>
                        <a href="<?= url('/admin/ppsb?q=' . urlencode($q) . '&status=' . $selected_status . '&page=' . ($page + 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
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
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tahun Ajaran Aktif</label>
                                    <input type="text" readonly value="<?= htmlspecialchars($currentYear['name'] ?? 'None') ?>" class="w-full px-3 py-2 bg-slate-50 border border-gray-200 rounded-lg text-sm text-indigo-600 font-bold outline-none">
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
    </script>

</main>
