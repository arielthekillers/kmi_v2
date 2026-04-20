<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Data Kelas</h2>
            <p class="text-gray-500 text-sm">Kelola data kelas dan jumlah santri.</p>
        </div>
        <button onclick="openAdd()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
            + Tambah Kelas
        </button>
    </div>

<!-- Content -->
<div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-7 gap-3">
    <?php foreach ($groupedKelas as $tingkat => $items): ?>
        <?php foreach ($items as $k): ?>
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow relative group">
                <div class="p-3">
                    <div class="flex justify-between items-start">
                        <a href="<?= url('/classes/detail?id=' . $k['id']) ?>" class="text-xl font-bold text-gray-900 hover:text-indigo-600 transition-colors">
                            <?= htmlspecialchars($k['tingkat']) ?>-<?= htmlspecialchars($k['abjad']) ?>
                        </a>
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute top-2 right-2 flex bg-white/90 rounded-md shadow-sm border border-gray-100">
                            <button onclick='editKelas("<?= $k['id'] ?>", "<?= $k['tingkat'] ?>", "<?= $k['abjad'] ?>", "<?= $k['location'] ?>", "<?= $k['teacher_id'] ?>")' class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-l-md" title="Edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <div class="w-px bg-gray-200"></div>
                            <a href="<?= url('/classes/delete?id=' . $k['id']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data kelas ini?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-r-md" title="Hapus">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </a>
                        </div>
                    </div>
                    <div class="mt-2 space-y-1">
                        <div class="flex items-center gap-1.5 text-[10px] text-gray-500 font-medium">
                            <i class="ri-user-star-line text-indigo-400"></i>
                            <span class="truncate"><?= htmlspecialchars($k['wali_kelas'] ?? '-') ?></span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400 italic">
                            <i class="ri-map-pin-line"></i>
                            <span class="truncate"><?= htmlspecialchars($k['location'] ?: '-') ?></span>
                        </div>
                        <div class="pt-1 flex items-center justify-between text-[11px]">
                            <span class="flex items-center gap-1 text-indigo-600 font-bold">
                                <i class="ri-team-line"></i>
                                <?= number_format($k['jumlah_murid']) ?> Santri
                            </span>
                            <a href="<?= url('/classes/detail?id=' . $k['id']) ?>" class="text-[10px] text-indigo-500 font-bold hover:underline">Detail &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<?php if (empty($groupedKelas)): ?>
    <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data kelas</h3>
        <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan kelas baru.</p>
    </div>
<?php endif; ?>

<!-- Modal -->
<div id="addKelasModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('addKelasModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-md sm:my-8 sm:align-middle sm:max-w-lg sm:w-full mx-auto">
            <form action="<?= url('/classes/store') ?>" method="POST">
                <?= csrf_token_field() ?>
                <input type="hidden" name="id" id="inputId">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Tambah Kelas</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tingkat/Nama</label>
                                <input type="text" name="tingkat" id="inputTingkat" placeholder="1, 2, 1 Int" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Abjad</label>
                                <input type="text" name="abjad" id="inputAbjad" placeholder="A, B..." required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Wali Kelas</label>
                            <select name="teacher_id" id="inputTeacher" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                <option value="">-- Pilih Wali Kelas --</option>
                                <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lokasi Kelas</label>
                            <input type="text" name="location" id="inputLocation" placeholder="Gedung A, Lantai 2, dll" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                            <p class="mt-1 text-[10px] text-gray-400 italic">* Boleh dikosongkan</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="toggleModal('addKelasModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function openAdd() {
        document.getElementById('inputTingkat').value = '';
        document.getElementById('inputAbjad').value = '';
        document.getElementById('inputLocation').value = '';
        document.getElementById('inputTeacher').value = '';
        document.getElementById('inputId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Kelas';
        toggleModal('addKelasModal');
    }

    function editKelas(id, tingkat, abjad, location, teacherId) {
        document.getElementById('inputTingkat').value = tingkat;
        document.getElementById('inputAbjad').value = abjad;
        document.getElementById('inputLocation').value = location;
        document.getElementById('inputTeacher').value = teacherId;
        document.getElementById('inputId').value = id;
        document.getElementById('modalTitle').textContent = 'Edit Kelas';
        toggleModal('addKelasModal');
    }
</script>
</main>
