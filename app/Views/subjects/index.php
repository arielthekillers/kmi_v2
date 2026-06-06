<?php
// app/Views/subjects/index.php
?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Master Pelajaran</h2>
            <p class="text-gray-500 text-sm">Database mata pelajaran dan konfigurasi nilai.</p>
        </div>
        <button onclick="openAdd()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
            + Tambah Pelajaran
        </button>
    </div>

    <!-- Stats & Filters -->
    <div class="mb-6 bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="text-gray-700 text-sm">
            Total: <strong class="text-indigo-600"><?= $total ?></strong> pelajaran
        </div>
        <form method="GET" action="" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- Search -->
            <div class="relative flex-1 sm:w-48">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Cari nama pelajaran..." class="w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-xs shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="ri-search-line text-gray-400 text-sm"></i>
                </div>
            </div>
            
            <!-- Filter Rumpun -->
            <select name="category" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-700 shadow-sm cursor-pointer">
                <option value="">Semua Rumpun</option>
                <option value="arabic" <?= ($category ?? '') === 'arabic' ? 'selected' : '' ?>>Bahasa Arab (اللغة العربية)</option>
                <option value="islamic" <?= ($category ?? '') === 'islamic' ? 'selected' : '' ?>>Dirasah Islamiyah (العقائد والشرائع)</option>
                <option value="foreign" <?= ($category ?? '') === 'foreign' ? 'selected' : '' ?>>Bahasa Asing (الأجنبية)</option>
                <option value="general" <?= ($category ?? '') === 'general' ? 'selected' : '' ?>>Umum & Seni (العامة والفنون)</option>
            </select>
            
            <!-- Filter Tipe -->
            <select name="type" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-700 shadow-sm cursor-pointer">
                <option value="">Semua Tipe</option>
                <option value="reguler" <?= ($type ?? '') === 'reguler' ? 'selected' : '' ?>>Reguler</option>
                <option value="special" <?= ($type ?? '') === 'special' ? 'selected' : '' ?>>Ujian Khusus</option>
            </select>

            <?php if (!empty($search) || !empty($category) || !empty($type)): ?>
                <a href="<?= url('/subjects') ?>" class="text-xs text-red-600 hover:text-red-800 font-semibold flex items-center gap-1 justify-center py-1 sm:py-0 px-2 rounded hover:bg-red-50 transition-colors">
                    <i class="ri-close-circle-line"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

<!-- Empty State -->
<?php if (empty($displayPelajaran)): ?>
    <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
        <p class="mt-1 text-sm text-gray-500"><?= $search ? 'Tidak ada pelajaran yang cocok dengan pencarian.' : 'Belum ada pelajaran.' ?></p>
    </div>
<?php else: ?>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Pelajaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rumpun / Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Urutan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rentang Nilai (Max-Min)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php 
                    $catLabels = [
                        'arabic' => 'Bahasa Arab (اللغة العربية)',
                        'islamic' => 'Dirasah Islamiyah (العقائد والشرائع)',
                        'foreign' => 'Bahasa Asing (الأجنبية)',
                        'general' => 'Umum & Seni (العامة والفنون)'
                    ];
                    foreach ($displayPelajaran as $p): 
                        // Note: $p is an array row here. ID is $p['id']
                        $id = $p['id'];
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($p['nama']) ?></div>
                                <?php if (!empty($p['nama_ar'])): ?>
                                    <div class="text-xs text-gray-500 font-semibold mt-0.5" dir="rtl"><?= htmlspecialchars($p['nama_ar']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($catLabels[$p['category'] ?? ''] ?? 'Belum Diatur') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= (int)($p['urutan'] ?? 0) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?= htmlspecialchars($p['skala']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($p['is_special']): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                        <i class="ri-star-fill text-[10px]"></i> Ujian Khusus
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Reguler
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <button onclick="editPelajaran('<?= $id ?>', <?= htmlspecialchars(json_encode($p['nama']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($p['nama_ar'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($p['category'] ?? ''), ENT_QUOTES) ?>, <?= (int)($p['urutan'] ?? 0) ?>, <?= htmlspecialchars(json_encode($p['skala']), ENT_QUOTES) ?>, <?= (int)$p['is_special'] ?>)" class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <a href="<?= url('/subjects/delete?id=' . $id) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data pelajaran ini?')" class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-lg shadow-sm">
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium"><?= $offset + 1 ?></span>
                    to
                    <span class="font-medium"><?= min($offset + $limit, $total) ?></span>
                    of
                    <span class="font-medium"><?= $total ?></span>
                    results
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&type=<?= urlencode($type) ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&type=<?= urlencode($type) ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $i === $page ? 'bg-indigo-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&type=<?= urlencode($type) ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
        <!-- Mobile Pagination -->
        <div class="flex sm:hidden justify-between w-full">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&type=<?= urlencode($type) ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&type=<?= urlencode($type) ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<!-- Modal -->
<div id="addModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('addModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-md sm:my-8 sm:align-middle sm:max-w-lg sm:w-full mx-auto">
            <form action="<?= url('/subjects/store') ?>" method="POST" id="formPelajaran">
                <?= csrf_token_field() ?>
                <input type="hidden" name="id" id="inputId">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Tambah Pelajaran</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Pelajaran</label>
                            <input type="text" name="nama" id="inputNama" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Pelajaran (Arab)</label>
                            <input type="text" name="nama_ar" id="inputNamaAr" placeholder="مثال: الإملاء العربي" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-right font-medium" dir="rtl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rumpun Bidang Studi</label>
                            <select name="category" id="inputCategory" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                <option value="">Pilih Rumpun...</option>
                                <option value="arabic">Bahasa Arab (اللغة العربية)</option>
                                <option value="islamic">Dirasah Islamiyah (العقائد والشرائع)</option>
                                <option value="foreign">Bahasa Asing (الأجنبية)</option>
                                <option value="general">Umum & Seni (العامة والفنون)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Urutan Tampilan</label>
                            <input type="number" name="urutan" id="inputUrutan" min="0" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rentang Nilai (Max - Min)</label>
                            <select name="skala" id="inputSkala" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                <option value="80-30">80 - 30 (Standar)</option>
                                <option value="100-0">100 - 0 (Penuh)</option>
                                <option value="100-10">100 - 10 (Lebar)</option>
                                <option value="90-10">90 - 10 (Ketat)</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Pilih rentang nilai konversi.</p>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-purple-50 border border-purple-100 rounded-lg">
                            <input type="checkbox" name="is_special" id="inputIsSpecial" value="1" class="mt-0.5 h-4 w-4 text-purple-600 border-gray-300 rounded">
                            <div>
                                <label for="inputIsSpecial" class="block text-sm font-medium text-gray-700 cursor-pointer">Pelajaran Ujian Khusus</label>
                                <p class="text-xs text-gray-500 mt-0.5">Centang jika pelajaran ini tidak masuk jadwal kelas reguler (contoh: Praktek Mengajar, Kemasyarakatan, Pemeriksaan Buku Paket). Pelajaran ini akan dapat dipilih melalui toggle khusus saat membuat Koreksi Ujian.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="toggleModal('addModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
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
        document.getElementById('inputNama').value = '';
        document.getElementById('inputNamaAr').value = '';
        document.getElementById('inputCategory').value = '';
        document.getElementById('inputUrutan').value = '0';
        document.getElementById('inputSkala').value = '80-30';
        document.getElementById('inputId').value = '';
        document.getElementById('inputIsSpecial').checked = false;
        document.getElementById('modalTitle').textContent = 'Tambah Pelajaran';
        toggleModal('addModal');
    }

    function editPelajaran(id, nama, namaAr, category, urutan, skala, isSpecial) {
        document.getElementById('inputNama').value = nama;
        document.getElementById('inputNamaAr').value = namaAr;
        document.getElementById('inputCategory').value = category;
        document.getElementById('inputUrutan').value = urutan;
        document.getElementById('inputSkala').value = skala;
        document.getElementById('inputId').value = id;
        document.getElementById('inputIsSpecial').checked = isSpecial == 1;
        document.getElementById('modalTitle').textContent = 'Edit Pelajaran';
        toggleModal('addModal');
    }
</script>
</main>
