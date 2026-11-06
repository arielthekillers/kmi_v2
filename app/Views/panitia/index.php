<?php renderHeader("Panitia Ujian"); 
$isAdmin = auth_get_role() === 'admin';

$typeMap = [
    'UUPT' => 'Ulangan Umum Pertengahan Tahun',
    'UPT'  => 'Ujian Pertengahan Tahun',
    'UUAT' => 'Ulangan Umum Akhir Tahun',
    'UAT'  => 'Ujian Akhir Tahun',
    'USA'  => 'Ujian Siswa Akhir'
];
$semesterMap = [
    'UUPT' => 1, 'UPT' => 1, 'UUAT' => 2, 'UAT' => 2, 'USA' => 2
];

$teacherMap = [];
foreach ($allTeachers as $t) {
    $teacherMap[$t['id']] = $t['nama'];
}

$sessionsDataArray = [];
foreach ($sessions as $s) {
    $cList = [];
    $assignedIds = $s['committee_ids'] ?? [];
    foreach ($allTeachers as $t) {
        if (in_array($t['id'], $assignedIds)) {
            $cList[] = [
                'id' => (int)$t['id'],
                'nama' => $t['nama']
            ];
        }
    }
    
    $smt = 1;
    if (isset($s['semester']) && $s['semester']) {
        $smt = (int)$s['semester'];
    } elseif (isset($semesterMap[$s['type']])) {
        $smt = (int)$semesterMap[$s['type']];
    } elseif (strpos($s['type'], '2') !== false || in_array($s['type'], ['UUAT', 'UAT'])) {
        $smt = 2;
    }

    $sessionsDataArray[] = [
        'id' => (int)$s['id'],
        'type' => $s['type'],
        'name' => $typeMap[$s['type']] ?? ($s['name'] ?? $s['type']),
        'semester' => $smt,
        'committee' => $cList
    ];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
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
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                <i class="ri-checkbox-circle-line mr-1"></i> Mode Manajemen Aktif
            </span>
        </div>
    </div>

    <!-- Alert Info -->
    <div class="bg-blue-50/80 border-l-4 border-blue-500 p-4 mb-6 rounded-r-xl shadow-xs">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="ri-information-line text-blue-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-xs sm:text-sm text-blue-800 leading-relaxed">
                    Sistem hanya mengizinkan <strong>satu sesi aktif</strong> dalam satu waktu. Sesi yang <strong>"Terbuka"</strong> memungkinkan pengajar menginput nilai. Panitia yang ditugaskan memiliki hak akses koreksi dan administrasi untuk sesi tersebut.
                </p>
            </div>
        </div>
    </div>

    <!-- Sessions Grid (Compact 3-Column on Desktop) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($sessions as $session): 
            $isOpen = (bool)$session['is_open'];
            $isActive = (bool)$session['is_active'];
            $useBayanat = (int)($session['use_bayanat'] ?? 1);
            $typeName = $typeMap[$session['type']] ?? ($session['name'] ?? $session['type']);
            
            $semester = 1;
            if (isset($session['semester']) && $session['semester']) {
                $semester = (int)$session['semester'];
            } elseif (isset($semesterMap[$session['type']])) {
                $semester = (int)$semesterMap[$session['type']];
            } elseif (strpos($session['type'], '2') !== false || in_array($session['type'], ['UUAT', 'UAT'])) {
                $semester = 2;
            }

            $assignedTeachers = [];
            $assignedIds = $session['committee_ids'] ?? [];
            foreach ($allTeachers as $t) {
                if (in_array($t['id'], $assignedIds)) {
                    $assignedTeachers[] = $t;
                }
            }
            $committeeCount = count($assignedTeachers);
        ?>
            <div class="bg-white rounded-xl border <?= $isActive ? 'border-indigo-500 ring-2 ring-indigo-500/20 shadow-md shadow-indigo-100' : 'border-gray-200 hover:border-gray-300 shadow-xs' ?> flex flex-col justify-between transition-all duration-200">
                <!-- Card Header -->
                <div class="p-4 border-b border-gray-100 <?= $isActive ? 'bg-gradient-to-r from-indigo-50/80 to-white' : 'bg-gray-50/50' ?>">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 tracking-wide">
                                    SMT <?= $semester ?>
                                </span>
                                <h3 class="text-base font-extrabold text-gray-900 truncate" title="<?= htmlspecialchars($session['type']) ?>">
                                    <?= htmlspecialchars($session['type']) ?>
                                </h3>
                            </div>
                            <p class="text-xs text-gray-500 font-medium truncate" title="<?= htmlspecialchars($typeName) ?>">
                                <?= htmlspecialchars($typeName) ?>
                            </p>
                        </div>

                        <div class="flex-shrink-0">
                            <?php if ($isActive): ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-200 animate-pulse">
                                    <i class="ri-flashlight-fill mr-1"></i> AKTIF
                                </span>
                            <?php elseif ($isAdmin): ?>
                                <form action="<?= url('/grades/panitia/session/status') ?>" method="POST" class="inline">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="id" value="<?= $session['id'] ?>">
                                    <input type="hidden" name="is_active" value="1">
                                    <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded-lg bg-white hover:bg-indigo-50 transition shadow-xs">
                                        Aktifkan
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-400">
                                    Nonaktif
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-4 flex-1 flex flex-col justify-between gap-3.5">
                    <!-- Switches Grid (Input Nilai & Bayanat) -->
                    <div class="grid grid-cols-2 gap-2 bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
                        <!-- Input Nilai Switch -->
                        <div class="flex flex-col gap-1.5 <?= !$isActive ? 'opacity-40 pointer-events-none' : '' ?>">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Input Nilai</span>
                                <span class="text-[10px] font-bold <?= $isOpen ? 'text-green-600' : 'text-gray-400' ?>">
                                    <?= $isOpen ? 'Buka' : 'Tutup' ?>
                                </span>
                            </div>
                            <?php if ($isAdmin): ?>
                                <form action="<?= url('/grades/panitia/session/status') ?>" method="POST" id="form-toggle-<?= $session['id'] ?>" class="flex items-center">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="id" value="<?= $session['id'] ?>">
                                    <input type="hidden" name="is_open" value="<?= $isOpen ? '0' : '1' ?>">
                                    <button type="submit" title="<?= !$isActive ? 'Hanya bisa dibuka jika sesi AKTIF' : ($isOpen ? 'Klik untuk Menutup Input Nilai' : 'Klik untuk Membuka Input Nilai') ?>" class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none <?= $isOpen ? 'bg-green-500' : 'bg-gray-300' ?>">
                                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out <?= $isOpen ? 'translate-x-4' : 'translate-x-0' ?>"></span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold <?= $isOpen ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $isOpen ? 'Terbuka' : 'Tertutup' ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Bayanat Switch -->
                        <div class="flex flex-col gap-1.5 border-l border-gray-200 pl-2.5 <?= !$isActive ? 'opacity-40 pointer-events-none' : '' ?>">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Bayanat</span>
                                <span class="text-[10px] font-bold <?= $useBayanat ? 'text-indigo-600' : 'text-gray-400' ?>">
                                    <?= $useBayanat ? 'Aktif' : 'Off' ?>
                                </span>
                            </div>
                            <?php if ($isAdmin): ?>
                                <form action="<?= url('/grades/panitia/session/status') ?>" method="POST" id="form-bayanat-<?= $session['id'] ?>" class="flex items-center">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="id" value="<?= $session['id'] ?>">
                                    <input type="hidden" name="use_bayanat" value="<?= $useBayanat ? '0' : '1' ?>">
                                    <button type="submit" title="<?= $useBayanat ? 'Bayanat Aktif (Ujian Anonim)' : 'Bayanat Non-Aktif (Ulangan Langsung)' ?>" class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none <?= $useBayanat ? 'bg-indigo-600' : 'bg-gray-300' ?>">
                                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out <?= $useBayanat ? 'translate-x-4' : 'translate-x-0' ?>"></span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold <?= $useBayanat ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' ?>">
                                    <?= $useBayanat ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Committee Preview Box -->
                    <div class="border border-gray-100 rounded-lg p-2.5 bg-white">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1">
                                <i class="ri-user-star-line text-indigo-600"></i> Panitia Ditugaskan
                            </span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold <?= $committeeCount > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-400' ?>">
                                <?= $committeeCount ?> Orang
                            </span>
                        </div>

                        <?php if ($committeeCount > 0): ?>
                            <div class="text-[11px] text-gray-600 truncate" title="<?= htmlspecialchars(implode(', ', array_column($assignedTeachers, 'nama'))) ?>">
                                <?= htmlspecialchars(implode(', ', array_slice(array_column($assignedTeachers, 'nama'), 0, 2))) ?><?php if ($committeeCount > 2): ?><span class="text-indigo-600 font-semibold"> +<?= ($committeeCount - 2) ?> lainnya</span><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-[11px] text-gray-400 italic">
                                Belum ada panitia ditugaskan
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Open Committee Modal Button -->
                    <button type="button" onclick="openCommitteeModal(<?= $session['id'] ?>)" class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-bold rounded-lg border border-indigo-200 bg-indigo-50/70 text-indigo-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all shadow-xs group">
                        <i class="ri-team-line group-hover:scale-110 transition-transform"></i>
                        <?= $isAdmin ? 'Kelola Panitia' : 'Lihat Panitia' ?> (<?= $committeeCount ?>)
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Kelola Panitia Ujian -->
<div id="modal-committee" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto" onclick="closeCommitteeModal()">
    <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden transform transition-all my-8 animate-in fade-in zoom-in-95 duration-200" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 via-white to-indigo-50/30 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-200 flex-shrink-0">
                    <i class="ri-team-line text-xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span id="modal-session-smt" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">SMT 1</span>
                        <h3 id="modal-session-title" class="text-base font-bold text-gray-900">UPT</h3>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Penugasan Panitia Ujian (memiliki akses administrasi & koreksi)</p>
                </div>
            </div>
            <button type="button" onclick="closeCommitteeModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 flex items-center justify-center transition">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="modal-committee-form" action="<?= url('/grades/panitia/committee/update') ?>" method="POST">
            <?= csrf_token_field() ?>
            <input type="hidden" name="session_id" id="modal-session-id" value="">

            <div class="p-6 space-y-4">
                <?php if ($isAdmin): ?>
                <!-- Search & Add Teacher -->
                <div class="bg-gray-50/90 p-3.5 rounded-xl border border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <i class="ri-user-add-line text-indigo-600"></i> Tambah Pengajar ke Panitia
                    </label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <select id="modal-teacher-select" class="block w-full text-xs" placeholder="Cari nama pengajar...">
                                <option value="">Pilih Pengajar...</option>
                                <?php foreach ($allTeachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" onclick="addModalCommitteeMember()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm active:scale-95 transition-all flex items-center gap-1 flex-shrink-0">
                            <i class="ri-add-line"></i> Tambah
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Committee List Section -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ri-list-check-2 text-indigo-600"></i> Daftar Panitia Ditugaskan
                        </span>
                        <span id="modal-committee-count" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            0 Personil
                        </span>
                    </div>

                    <!-- Scrollable List Container -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-inner">
                        <div id="modal-committee-list" class="divide-y divide-gray-100 max-h-60 overflow-y-auto p-1">
                            <!-- Injected by JavaScript -->
                        </div>

                        <!-- Empty State -->
                        <div id="modal-empty-state" class="hidden py-8 px-4 text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-2">
                                <i class="ri-group-line text-2xl"></i>
                            </div>
                            <p class="text-xs font-medium text-gray-500">Belum ada panitia ditugaskan untuk sesi ujian ini.</p>
                            <?php if ($isAdmin): ?>
                            <p class="text-[11px] text-gray-400 mt-1">Pilih nama pengajar pada kolom di atas lalu klik Tambah.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <p class="text-[11px] text-gray-500 italic">
                    <?= $isAdmin ? '* Klik Simpan Panitia setelah selesai mengubah daftar' : 'Mode lihat daftar saja' ?>
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeCommitteeModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-xs font-semibold text-gray-600 hover:bg-white hover:text-gray-800 transition">
                        Batal
                    </button>
                    <?php if ($isAdmin): ?>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-md active:scale-95 transition-all flex items-center gap-1.5">
                        <i class="ri-save-line text-sm"></i> Simpan Panitia
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<?php renderFooter(); ?>

<script>
const sessionsData = <?= json_encode($sessionsDataArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const teachersMap = <?= json_encode($teacherMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

let currentSessionData = null;
let modalTomSelect = null;

document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('modal-teacher-select');
    if (el && window.TomSelect) {
        modalTomSelect = new TomSelect(el, {
            create: false,
            placeholder: 'Cari dan pilih pengajar...',
            maxOptions: 500,
            dropdownParent: 'body'
        });
    }
});

function openCommitteeModal(sessionId) {
    const session = sessionsData.find(s => s.id == sessionId);
    if (!session) return;

    // Clone session committee state
    currentSessionData = {
        id: session.id,
        type: session.type,
        name: session.name,
        semester: session.semester,
        committee: (session.committee || []).map(c => ({ id: c.id, nama: c.nama }))
    };

    document.getElementById('modal-session-id').value = currentSessionData.id;
    document.getElementById('modal-session-title').textContent = currentSessionData.type + ' (' + currentSessionData.name + ')';
    document.getElementById('modal-session-smt').textContent = 'SMT ' + currentSessionData.semester;

    renderModalCommitteeList();

    if (modalTomSelect) {
        modalTomSelect.clear();
    }

    const modal = document.getElementById('modal-committee');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCommitteeModal() {
    const modal = document.getElementById('modal-committee');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function renderModalCommitteeList() {
    const container = document.getElementById('modal-committee-list');
    const countBadge = document.getElementById('modal-committee-count');
    const emptyState = document.getElementById('modal-empty-state');

    container.innerHTML = '';
    const members = currentSessionData ? (currentSessionData.committee || []) : [];
    countBadge.textContent = members.length + ' Personil';

    if (members.length === 0) {
        emptyState.classList.remove('hidden');
    } else {
        emptyState.classList.add('hidden');
        members.forEach(m => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between p-2.5 hover:bg-gray-50 rounded-lg transition-colors';

            const parts = (m.nama || '').trim().split(/\s+/);
            const initials = parts.slice(0, 2).map(p => p[0] || '').join('').toUpperCase();

            row.innerHTML = `
                <div class="flex items-center gap-3">
                    <input type="hidden" name="user_ids[]" value="${m.id}">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center flex-shrink-0 border border-indigo-200">
                        ${initials || 'U'}
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-800">${escapeHtml(m.nama)}</div>
                        <div class="text-[10px] text-gray-400">Panitia Sesi</div>
                    </div>
                </div>
                ${isAdmin ? `
                <button type="button" onclick="removeModalCommitteeMember(${m.id})" class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition-colors" title="Hapus dari Panitia">
                    <i class="ri-delete-bin-line text-base"></i>
                </button>
                ` : ''}
            `;
            container.appendChild(row);
        });
    }
}

function addModalCommitteeMember() {
    if (!modalTomSelect) return;
    const userId = modalTomSelect.getValue();
    if (!userId) {
        alert('Silakan pilih pengajar terlebih dahulu.');
        return;
    }

    const exists = currentSessionData.committee.some(m => m.id == userId);
    if (exists) {
        alert('Pengajar ini sudah ada dalam daftar panitia.');
        return;
    }

    const name = teachersMap[userId] || 'Pengajar';
    currentSessionData.committee.push({ id: parseInt(userId), nama: name });
    renderModalCommitteeList();
    modalTomSelect.clear();
}

function removeModalCommitteeMember(userId) {
    if (!currentSessionData) return;
    currentSessionData.committee = currentSessionData.committee.filter(m => m.id != userId);
    renderModalCommitteeList();
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modal-committee');
        if (modal && !modal.classList.contains('hidden')) {
            closeCommitteeModal();
        }
    }
});
</script>
