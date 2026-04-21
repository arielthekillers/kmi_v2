<?php renderHeader("Panitia Ujian"); ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-group-line text-indigo-600"></i>
                Panitia Ujian
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Kelola sesi ujian dan penugasan panitia untuk Tahun Ajaran <?= htmlspecialchars($activeAY['name']) ?>.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                <i class="ri-checkbox-circle-line mr-1"></i> Mode Manajemen Aktif
            </span>
        </div>
    </div>

    <!-- Alert Info -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8 rounded-r-lg shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="ri-information-line text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Sistem hanya mengizinkan <strong>satu sesi aktif</strong> dalam satu waktu. Sesi yang <strong>"Terbuka"</strong> memungkinkan pengajar menginput nilai. Panitia yang ditugaskan memiliki akses Admin khusus untuk sesi tersebut.
                </p>
            </div>
        </div>
    </div>

    <!-- Sessions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($sessions as $session): 
            $isOpen = (bool)$session['is_open'];
            $isActive = (bool)$session['is_active'];
            $typeMap = [
                'UUPT' => 'Ulangan Umum Pertengahan Tahun',
                'UPT' => 'Ujian Pertengahan Tahun',
                'UUAT' => 'Ulangan Umum Akhir Tahun',
                'UAT' => 'Ujian Akhir Tahun'
            ];
            $semesterMap = [
                'UUPT' => 1, 'UPT' => 1, 'UUAT' => 2, 'UAT' => 2
            ];
        ?>
            <div class="bg-white rounded-xl shadow-sm border <?= $isActive ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200' ?> overflow-hidden flex flex-col transition-all">
                <div class="px-5 py-4 border-b border-gray-100 <?= $isActive ? 'bg-indigo-50/30' : 'bg-gray-50/50' ?>">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-800 tracking-tight">
                                        SMT <?= $semesterMap[$session['type']] ?>
                                    </span>
                                    <h3 class="text-base font-extrabold text-gray-900"><?= $session['type'] ?></h3>
                                </div>
                                <p class="text-[11px] text-gray-400 font-medium tracking-wide"><?= $typeMap[$session['type']] ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-2 <?= !$isActive ? 'opacity-30 pointer-events-none' : '' ?>" title="<?= !$isActive ? 'Hanya bisa dibuka jika sesi AKTIF' : 'Buka/Tutup input nilai' ?>">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Input:</span>
                                <form action="<?= url('/grades/panitia/session/status') ?>" method="POST" id="form-toggle-<?= $session['id'] ?>" class="flex items-center">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="id" value="<?= $session['id'] ?>">
                                    <input type="hidden" name="is_open" value="<?= $isOpen ? '0' : '1' ?>">
                                    <button type="submit" class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none <?= $isOpen ? 'bg-green-500' : 'bg-gray-200' ?>">
                                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out <?= $isOpen ? 'translate-x-5' : 'translate-x-0' ?>"></span>
                                    </button>
                                </form>
                            </div>

                            <?php if ($isActive): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-indigo-600 text-white shadow-lg shadow-indigo-200">
                                    <i class="ri-flashlight-line mr-1"></i> AKTIF
                                </span>
                            <?php else: ?>
                                <form action="<?= url('/grades/panitia/session/status') ?>" method="POST">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="id" value="<?= $session['id'] ?>">
                                    <input type="hidden" name="is_active" value="1">
                                    <button type="submit" class="text-xs font-bold text-indigo-600 hover:bg-indigo-50 border border-indigo-200 px-3 py-1 rounded-lg transition-all hover:shadow-sm">
                                        Aktifkan
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="p-4 flex-1 flex flex-col min-h-0">
                    <form action="<?= url('/grades/panitia/committee/update') ?>" method="POST" class="flex-1 flex flex-col min-h-0">
                        <?= csrf_token_field() ?>
                        <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                        <div class="flex-1 flex flex-col min-h-0 border border-gray-100 rounded-xl p-3 bg-gray-50/20">
                        <!-- Add Member Section -->
                        <div class="flex gap-2 mb-3">
                            <div class="flex-1">
                                <select id="add-member-<?= $session['id'] ?>" class="tom-select block w-full" placeholder="Cari nama pengajar...">
                                    <option value="">Pilih Pengajar...</option>
                                    <?php foreach ($allTeachers as $teacher): ?>
                                        <option value="<?= $teacher['id'] ?>" data-nama="<?= htmlspecialchars($teacher['nama']) ?>">
                                            <?= htmlspecialchars($teacher['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" onclick="addCommitteeMember(<?= $session['id'] ?>)" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-700 transition-colors flex items-center gap-1 shadow-sm">
                                <i class="ri-user-add-line"></i> Tambah
                            </button>
                        </div>

                        <!-- Current Members Table -->
                        <div class="flex-1 overflow-y-auto border border-gray-200 rounded-lg bg-white mb-3 shadow-inner" style="max-height: 200px;">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50/80 sticky top-0 z-10 backdrop-blur-sm">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Personil</th>
                                        <th class="px-3 py-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest w-16">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="member-list-<?= $session['id'] ?>" class="divide-y divide-gray-50">
                                    <?php 
                                    $assignedIds = $session['committee_ids'] ?? [];
                                    $count = 0;
                                    foreach ($allTeachers as $t): 
                                        if (in_array($t['id'], $assignedIds)):
                                            $count++;
                                    ?>
                                        <tr data-user-id="<?= $t['id'] ?>" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-3 py-1 text-xs text-gray-700 font-medium">
                                                <input type="hidden" name="user_ids[]" value="<?= $t['id'] ?>">
                                                <?= htmlspecialchars($t['nama']) ?>
                                            </td>
                                            <td class="px-3 py-1 text-right">
                                                <button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 p-1 transition-colors">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php 
                                        endif; 
                                    endforeach; 
                                    ?>
                                    <?php if ($count === 0): ?>
                                        <tr class="empty-state">
                                            <td colspan="2" class="px-3 py-4 text-center text-[10px] text-gray-400 italic">
                                                Belum ada panitia ditugaskan
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-gray-100 mt-auto shadow-sm">
                            <div class="text-[9px] text-gray-400 font-medium italic">
                                * Klik simpan setelah update
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded-lg shadow-md active:scale-95 transition-all">
                                <i class="ri-save-line mr-1.5"></i> Simpan Daftar
                            </button>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php renderFooter(); ?>

<script>
function addCommitteeMember(sessionId) {
    const select = document.getElementById('add-member-' + sessionId);
    const userId = select.value;
    if (!userId) return;

    const option = select.options[select.selectedIndex];
    const nama = option.getAttribute('data-nama');
    const tbody = document.getElementById('member-list-' + sessionId);

    // Check if already in list
    if (tbody.querySelector(`tr[data-user-id="${userId}"]`)) {
        alert('Pengajar ini sudah ada dalam daftar.');
        return;
    }

    // Remove empty state if exists
    const emptyState = tbody.querySelector('.empty-state');
    if (emptyState) emptyState.remove();

    // Add new row
    const tr = document.createElement('tr');
    tr.setAttribute('data-user-id', userId);
    tr.className = 'bg-white divide-y divide-gray-100 animate-in fade-in slide-in-from-left-2 duration-300';
    tr.innerHTML = `
        <td class="px-4 py-3 text-sm text-gray-900 font-medium">
            <input type="hidden" name="user_ids[]" value="${userId}">
            ${nama}
        </td>
        <td class="px-4 py-3 text-right">
            <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 p-1 rounded-md hover:bg-red-50 transition-colors">
                <i class="ri-delete-bin-line text-lg"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);

    // Clear select
    select.tomselect.clear();
}
</script>
