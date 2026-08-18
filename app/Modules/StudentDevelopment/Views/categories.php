<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">

    <!-- Back Navigation -->
    <div class="mb-6">
        <a href="<?= url('/student-development') ?>" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-semibold transition-all">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- List Kategori (2 Cols) -->
        <div class="lg:col-span-2 space-y-4">
            <h1 class="text-2xl font-black text-slate-800 flex items-center gap-2">
                <i class="ri-settings-4-line text-indigo-600"></i> Kelola Kategori Observasi
            </h1>
            <p class="text-xs text-slate-400">Gunakan kategori ini untuk mempermudah pencarian dan pengelompokan riwayat observasi santri.</p>
            
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50/50 text-slate-400 text-xs uppercase tracking-wider font-semibold border-b border-slate-100">
                            <tr>
                                <th class="p-4 pl-6">Nama Kategori</th>
                                <th class="p-4">Deskripsi</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach ($categories as $cat): ?>
                                <tr class="hover:bg-slate-50/40 transition-all">
                                    <td class="p-4 pl-6 font-bold text-slate-800 flex items-center">
                                        <span class="inline-block w-3 h-3 rounded-full mr-2 shrink-0 border border-black/5" style="background-color: <?= $cat['color'] ?? '#64748b' ?>"></span>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </td>
                                    <td class="p-4 text-xs text-slate-500 max-w-xs truncate" title="<?= htmlspecialchars($cat['description'] ?? '') ?>">
                                        <?= htmlspecialchars($cat['description'] ?? '-') ?>
                                    </td>
                                    <td class="p-4 pr-6 text-right flex justify-end gap-2">
                                        <button onclick="editCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>', '<?= htmlspecialchars(addslashes($cat['description'] ?? '')) ?>', '<?= $cat['color'] ?? '#64748b' ?>')" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-semibold px-3 py-1.5 rounded-xl border border-indigo-100 transition-all">
                                            Edit
                                        </button>
                                        <a href="<?= url('/student-development/categories/delete?id=' . $cat['id']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Penghapusan akan gagal jika kategori sudah digunakan dalam catatan observasi.')" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold px-3 py-1.5 rounded-xl border border-red-100 transition-all">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form Tambah / Edit (1 Col) -->
        <div class="space-y-6">
            <!-- Add Card -->
            <div id="add-card" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-1.5">
                    <i class="ri-add-circle-line text-indigo-600"></i> Tambah Kategori Baru
                </h3>
                <form method="POST" action="<?= url('/student-development/categories/store') ?>" class="space-y-4">
                    <?= csrf_input() ?>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="name" placeholder="Misal: Kedisiplinan" class="block w-full border-slate-200 rounded-xl text-sm p-3 border focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Deskripsi <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                        <textarea name="description" rows="3" placeholder="Tuliskan keterangan detail kategori ini..." class="block w-full border-slate-200 rounded-xl text-sm p-3 border focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Warna Penanda</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="color" value="#6366f1" class="w-10 h-10 border border-slate-200 rounded-xl cursor-pointer p-0.5">
                            <span class="text-[11px] text-slate-400">Pilih warna khusus untuk badge kategori ini</span>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-xl text-sm font-bold shadow-md shadow-indigo-100 transition-all flex justify-center items-center gap-2">
                        <i class="ri-save-line text-lg"></i> Simpan Kategori
                    </button>
                </form>
            </div>

            <!-- Edit Card (Hidden initially) -->
            <div id="edit-card" class="hidden bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-1.5">
                    <i class="ri-edit-box-line text-amber-600"></i> Edit Kategori
                </h3>
                <form method="POST" action="<?= url('/student-development/categories/update') ?>" class="space-y-4">
                    <?= csrf_input() ?>
                    <input type="hidden" id="edit-id" name="id">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" id="edit-name" name="name" class="block w-full border-slate-200 rounded-xl text-sm p-3 border focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Deskripsi <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                        <textarea id="edit-desc" name="description" rows="3" class="block w-full border-slate-200 rounded-xl text-sm p-3 border focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Warna Penanda</label>
                        <div class="flex items-center gap-2">
                            <input type="color" id="edit-color" name="color" class="w-10 h-10 border border-slate-200 rounded-xl cursor-pointer p-0.5">
                            <span class="text-[11px] text-slate-400">Sesuaikan warna untuk badge kategori ini</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-grow bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-xl text-sm font-bold shadow-md shadow-indigo-100 transition-all">
                            Simpan Perubahan
                        </button>
                        <button type="button" onclick="cancelEdit()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 p-3 rounded-xl text-sm font-semibold transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</main>

<script>
    function editCategory(id, name, desc, color) {
        // Toggle card visibility
        document.getElementById('add-card').classList.add('hidden');
        document.getElementById('edit-card').classList.remove('hidden');

        // Fill values
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-desc').value = desc;
        document.getElementById('edit-color').value = color || '#64748b';

        // Smooth scroll to card
        document.getElementById('edit-card').scrollIntoView({ behavior: 'smooth' });
    }

    function cancelEdit() {
        document.getElementById('edit-card').classList.add('hidden');
        document.getElementById('add-card').classList.remove('hidden');
    }
</script>
