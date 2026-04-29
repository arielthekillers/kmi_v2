<?php
// app/Views/teachers/index.php

// Consume reset result from session (if any)
if (session_status() === PHP_SESSION_NONE) session_start();
$resetResult = $_SESSION['reset_result'] ?? null;
unset($_SESSION['reset_result']);
?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Pengajar</h1>
            <p class="text-sm text-gray-500">Kelola daftar ustadz/pengajar secara komprehensif.</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= url('/teachers/trash') ?>" class="px-4 py-2 border border-red-200 text-red-600 rounded-md text-sm font-medium hover:bg-red-50 flex items-center">
                <i class="ri-delete-bin-line mr-2"></i> Tempat Sampah
            </a>
            <button onclick="openAdd()" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 flex items-center shadow-sm">
                <i class="ri-user-add-line mr-2"></i> Tambah Pengajar
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <form action="<?= url('/teachers') ?>" method="GET" class="flex flex-wrap items-end gap-4">
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
                <i class="ri-filter-3-line mr-2"></i> Tampilkan Data
            </button>
            <?php if (!empty($q)): ?>
                <a href="<?= url('/teachers') ?>" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm font-medium">
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (!$is_searching): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-indigo-50 text-indigo-200 rounded-full flex items-center justify-center mb-4">
                                        <i class="ri-search-eye-line text-5xl"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">Mulai Pencarian</h3>
                                    <p class="text-sm text-gray-500 max-w-xs mx-auto">Masukkan Nama atau No HP untuk menampilkan data pengajar. Hal ini dilakukan untuk optimasi performa sistem.</p>
                                </div>
                            </td>
                        </tr>
                    <?php elseif (empty($displayPengajar)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center text-sm text-gray-400 italic">
                                Tidak ditemukan data pengajar yang sesuai dengan kriteria pencarian Anda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($displayPengajar as $index => $p): 
                            $id = $p['id'];
                        ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                                <?= str_pad(($page - 1) * $perPage + $index + 1, 3, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold mr-3">
                                        <?= mb_strtoupper(mb_substr($p['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($p['nama']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($p['hp'] ?? '-') ?>
                                <?php if (!empty($p['password_plain']) && !empty($p['hp'])):
                                    $hpNum = preg_replace('/[^0-9]/', '', $p['hp']);
                                    if (substr($hpNum, 0, 1) === '0') $hpNum = '62' . substr($hpNum, 1);
                                    $loginUrl = url('/login');
                                    $msg = "Assalamu'alaikum Wr. Wb.\n\nBerikut akun antum untuk login di KMI App:\n\nUsername: " . $p['hp'] . "\nPassword: " . $p['password_plain'] . "\n\nLink Login: " . $loginUrl . "\n\nMohon dijaga kerahasiaannya.\n\nSyukron";
                                    $waLink = "https://wa.me/$hpNum?text=" . rawurlencode($msg);
                                ?>
                                    <a href="<?= $waLink ?>" target="_blank"
                                       class="ml-2 inline-flex items-center px-2 py-1 border border-transparent text-[10px] font-bold rounded-full text-green-700 bg-green-100 hover:bg-green-200 uppercase"
                                       title="Kirim WA">
                                        <i class="ri-whatsapp-line mr-1"></i> Share
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2 items-center">
                                    <button onclick="editPengajar('<?= $id ?>', <?= htmlspecialchars(json_encode($p['nama']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($p['hp'] ?? ''), ENT_QUOTES) ?>)"
                                            class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-colors" title="Edit Data">
                                        <i class="ri-edit-box-line text-lg"></i>
                                    </button>
                                    <button onclick="openReset('<?= $id ?>', <?= htmlspecialchars(json_encode($p['nama']), ENT_QUOTES) ?>)"
                                            class="p-2 text-yellow-600 hover:bg-yellow-100 rounded-lg transition-colors" title="Reset Password">
                                        <i class="ri-key-2-line text-lg"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?= $id ?>, '<?= addslashes($p['nama']) ?>')"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Data">
                                        <i class="ri-delete-bin-line text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($is_searching && $totalPages > 1): ?>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Menampilkan <span class="font-bold"><?= count($displayPengajar) ?></span> dari <span class="font-bold"><?= $totalData ?></span> pengajar
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('/teachers?q=' . urlencode($q) . '&page=' . ($page - 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                            <i class="ri-arrow-left-s-line"></i> Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <div class="flex items-center px-4 text-sm font-bold text-gray-500">
                        Halaman <?= $page ?> dari <?= $totalPages ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('/teachers?q=' . urlencode($q) . '&page=' . ($page + 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                            Selanjutnya <i class="ri-arrow-right-s-line"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<!-- ─── Modal: Tambah/Edit Pengajar ─────────────────────────────────── -->

<div id="addPengajarModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('addPengajarModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all w-full max-w-md sm:my-8 sm:align-middle sm:max-w-lg sm:w-full mx-auto">
            <form action="<?= url('/teachers/store') ?>" method="POST">
                <?= csrf_token_field() ?>
                <input type="hidden" name="id" id="inputId">
                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4" id="modalTitle">Tambah Pengajar</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama" id="inputNama" required class="block w-full border border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                            <input type="text" name="hp" id="inputHp" class="block w-full border border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        <!-- Password: only shown when ADDING, hidden when editing -->
                        <div id="passwordSection">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <p class="text-xs text-gray-500 mb-2">Kosongkan untuk auto-generate password acak.</p>
                            <input type="text" name="password" id="inputPassword" class="block w-full border border-gray-300 rounded-lg shadow-sm px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Auto-generated...">
                        </div>
                        <div id="passwordEditHint" class="hidden rounded-lg bg-yellow-50 border border-yellow-200 px-3 py-2 text-xs text-yellow-700 flex items-center gap-2">
                            <i class="ri-information-line"></i>
                            <span>Untuk mengubah password, gunakan tombol <strong class="mx-1">Reset Password</strong> (ikon kunci) pada baris pengajar.</span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="toggleModal('addPengajarModal')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ─── Modal: Konfirmasi Reset Password ─────────────────────────────── -->
<div id="resetPasswordModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('resetPasswordModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all w-full max-w-md sm:my-8 sm:align-middle mx-auto">
            <form action="<?= url('/teachers/reset-password') ?>" method="POST">
                <?= csrf_token_field() ?>
                <input type="hidden" name="id" id="resetId">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                            <i class="ri-key-2-line text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Reset Password</h3>
                            <p class="text-sm text-gray-500">Password akan direset ke angka acak 6 digit.</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700">Yakin reset password <strong id="resetNama"></strong>?</p>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 sm:w-auto sm:text-sm">Ya, Reset</button>
                    <button type="button" onclick="toggleModal('resetPasswordModal')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ─── Modal: Hasil Reset Password ─────────────────────────────────── -->
<?php if ($resetResult): 
    $rrHp  = preg_replace('/[^0-9]/', '', $resetResult['hp'] ?? '');
    if ($rrHp && substr($rrHp, 0, 1) === '0') $rrHp = '62' . substr($rrHp, 1);
?>
<div id="resetResultModal" class="fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('resetResultModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all w-full max-w-md sm:my-8 sm:align-middle mx-auto">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="ri-check-line text-xl"></i>
                    Password Berhasil Direset
                </h3>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nama</span>
                        <span class="font-bold text-gray-900"><?= htmlspecialchars($resetResult['nama']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Username (HP)</span>
                        <span class="font-bold text-gray-900"><?= htmlspecialchars($resetResult['hp']) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Password Baru</span>
                        <span class="font-black text-2xl text-indigo-600 tracking-widest"><?= htmlspecialchars($resetResult['password']) ?></span>
                    </div>
                </div>

                <?php if ($resetResult['wa_link']): ?>
                <a href="<?= $resetResult['wa_link'] ?>" target="_blank"
                   class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition-colors text-sm shadow-sm">
                    <i class="ri-whatsapp-line text-lg"></i>
                    Kirim via WhatsApp
                </a>
                <?php endif; ?>
            </div>
            <div class="px-6 pb-5">
                <button onclick="toggleModal('resetResultModal')" class="w-full text-center text-sm font-medium text-gray-500 hover:text-gray-700 py-2 transition-colors">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Auto-open reset result modal on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('resetResultModal').classList.remove('hidden');
    });
</script>
<?php endif; ?>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function openAdd() {
        document.getElementById('inputNama').value = '';
        document.getElementById('inputHp').value = '';
        document.getElementById('inputId').value = '';
        document.getElementById('inputPassword').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Pengajar';
        // Show password field for new entries
        document.getElementById('passwordSection').classList.remove('hidden');
        document.getElementById('passwordEditHint').classList.add('hidden');
        toggleModal('addPengajarModal');
    }

    function editPengajar(id, nama, hp) {
        document.getElementById('inputNama').value = nama;
        document.getElementById('inputHp').value = hp;
        document.getElementById('inputId').value = id;
        document.getElementById('inputPassword').value = '';
        document.getElementById('modalTitle').textContent = 'Edit Pengajar';
        // Hide password field — use Reset Password button instead
        document.getElementById('passwordSection').classList.add('hidden');
        document.getElementById('passwordEditHint').classList.remove('hidden');
        toggleModal('addPengajarModal');
    }

    function openReset(id, nama) {
        document.getElementById('resetId').value = id;
        document.getElementById('resetNama').textContent = nama;
        toggleModal('resetPasswordModal');
    }

    function confirmDelete(id, name) {
        if (confirm('Apakah Anda yakin ingin menghapus data pengajar "' + name + '"?\n\nData akan dipindahkan ke Tempat Sampah.')) {
            window.location.href = '<?= url("/teachers/delete?id=") ?>' + id;
        }
    }
</script>
</main>
