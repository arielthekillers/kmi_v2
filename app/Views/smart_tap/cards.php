<?php
renderHeader("Smart Tap — Pairing & Manajemen Kartu RFID");

$tab = $tab ?? 'students';
$currentStats = ($tab === 'students') ? ($stats['students'] ?? ['total' => 0, 'paired' => 0, 'unpaired' => 0]) : ($stats['teachers'] ?? ['total' => 0, 'paired' => 0, 'unpaired' => 0]);
$totalCurrent = $currentStats['total'];
$pairedCurrent = $currentStats['paired'];
$unpairedCurrent = $currentStats['unpaired'];
$pairedPercent = ($totalCurrent > 0) ? round(($pairedCurrent / $totalCurrent) * 100) : 0;
?>

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ri-checkbox-circle-fill text-xl text-emerald-600"></i>
                <span class="text-sm font-semibold"><?= $_SESSION['success'] ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ri-error-warning-fill text-xl text-rose-600"></i>
                <span class="text-sm font-semibold"><?= $_SESSION['error'] ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-lg leading-none">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Master Header & Tab Card -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Left Info -->
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl flex-shrink-0 shadow-xs">
                    <i class="ri-bank-card-line"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Pairing & Manajemen Kartu RFID</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
                        Hubungkan UID kartu fisik RFID ke akun santri dan guru untuk digunakan pada terminal perangkat saat absensi sholat maupun kegiatan pondok.
                    </p>
                </div>
            </div>

            <!-- Right Quick Stat Pill -->
            <div class="flex items-center gap-3 self-start lg:self-center">
                <div class="bg-gray-50 px-4 py-2.5 rounded-2xl border border-gray-200/80 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-base">
                        <i class="ri-rfid-line"></i>
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold text-gray-400">Progres Terhubung</div>
                        <div class="text-xs font-bold text-gray-800">
                            <span class="text-indigo-600"><?= $pairedCurrent ?></span> dari <?= $totalCurrent ?> (<?= $pairedPercent ?>%)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seamless Segmented Tabs -->
        <div class="mt-6 pt-5 border-t border-gray-100 flex items-center gap-2 overflow-x-auto pb-1">
            <a href="<?= url('/smart-tap/cards?tab=students') ?>" 
               class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 whitespace-nowrap <?= $tab === 'students' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/60 shadow-xs' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                <i class="ri-graduation-cap-line text-sm"></i>
                <span>Data Kartu Santri</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold <?= $tab === 'students' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' ?>">
                    <?= $stats['students']['total'] ?? 0 ?>
                </span>
            </a>
            
            <a href="<?= url('/smart-tap/cards?tab=teachers') ?>" 
               class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 whitespace-nowrap <?= $tab === 'teachers' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/60 shadow-xs' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                <i class="ri-user-star-line text-sm"></i>
                <span>Data Kartu Pengajar & Staf</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold <?= $tab === 'teachers' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' ?>">
                    <?= $stats['teachers']['total'] ?? 0 ?>
                </span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-400 block mb-1">
                    Total <?= $tab === 'students' ? 'Santri Terdaftar' : 'Pengajar Terdaftar' ?>
                </span>
                <span class="text-2xl font-bold text-gray-900"><?= $totalCurrent ?></span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="<?= $tab === 'students' ? 'ri-team-line' : 'ri-user-tie-line' ?>"></i>
            </div>
        </div>

        <!-- Paired -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-emerald-600 block mb-1">Sudah Memiliki Kartu</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-emerald-700"><?= $pairedCurrent ?></span>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100"><?= $pairedPercent ?>%</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="ri-checkbox-circle-line"></i>
            </div>
        </div>

        <!-- Unpaired -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-amber-600 block mb-1">Belum Terhubung (Pending)</span>
                <span class="text-2xl font-bold text-amber-700"><?= $unpairedCurrent ?></span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="ri-error-warning-line"></i>
            </div>
        </div>
    </div>

    <!-- Unified Table Card with Integrated Toolbar -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Integrated Filter Bar -->
        <div class="p-5 border-b border-gray-100 bg-gray-50/50">
            <form method="GET" action="<?= url('/smart-tap/cards') ?>" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">

                <!-- Left Title & Count -->
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-gray-900">
                        Daftar <?= $tab === 'students' ? 'Santri' : 'Pengajar' ?>
                    </h3>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <?= $tab === 'students' ? $totalStudents : count($teachers) ?> Data
                    </span>
                </div>

                <!-- Right Filters -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-56">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama, NIS, atau UID..." class="w-full text-xs rounded-xl border border-gray-200 bg-white py-2 pl-8 pr-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium text-gray-700">
                    </div>

                    <!-- Kelas Filter (Students only) -->
                    <?php if ($tab === 'students'): ?>
                        <div class="relative">
                            <select name="kelas_id" class="text-xs rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium text-gray-700">
                                <option value="">Semua Kelas</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($kelasId == $c['id']) ? 'selected' : '' ?>>
                                        Kelas <?= htmlspecialchars($c['tingkat'] . ' ' . $c['abjad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- Status Filter -->
                    <div class="relative">
                        <select name="has_card" class="text-xs rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium text-gray-700">
                            <option value="">Semua Status Kartu</option>
                            <option value="yes" <?= $statusCard === 'yes' ? 'selected' : '' ?>>Sudah Terhubung Kartu</option>
                            <option value="no" <?= $statusCard === 'no' ? 'selected' : '' ?>>Belum Ada Kartu</option>
                        </select>
                    </div>

                    <!-- Submit & Reset -->
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                        <i class="ri-filter-3-line"></i> Cari
                    </button>

                    <?php if (!empty($search) || !empty($statusCard) || !empty($kelasId)): ?>
                        <a href="<?= url('/smart-tap/cards?tab=' . $tab) ?>" class="text-xs font-semibold text-gray-400 hover:text-gray-700 px-2 py-2">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="table-cards">
                <thead>
                    <tr class="bg-gray-50/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="py-3.5 px-5">Nama Lengkap</th>
                        <th class="py-3.5 px-5"><?= $tab === 'students' ? 'Kelas' : 'Jabatan / Info' ?></th>
                        <th class="py-3.5 px-5">UID Kartu RFID</th>
                        <th class="py-3.5 px-5 text-center">Status Kartu</th>
                        <th class="py-3.5 px-5 text-center w-36">Aksi Pairing</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php 
                    $list = ($tab === 'students') ? $students : $teachers;
                    if (empty($list)): 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-16 px-4 text-gray-400">
                                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto text-3xl mb-3 shadow-inner">
                                    <i class="ri-user-search-line"></i>
                                </div>
                                <h4 class="font-bold text-gray-800 text-sm">Tidak Ditemukan Data yang Sesuai</h4>
                                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto leading-relaxed">
                                    Coba ubah kata kunci pencarian, pilihan kelas, atau filter status kartu RFID Anda.
                                </p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list as $item): 
                            $hasUid = !empty($item['rfid_uid']) && trim($item['rfid_uid']) !== '';
                            $personType = ($tab === 'students') ? 'student' : 'teacher';
                            $initial = mb_substr($item['nama'], 0, 1);
                        ?>
                            <tr class="hover:bg-indigo-50/20 transition card-row" id="row-<?= $personType ?>-<?= $item['id'] ?>" data-person-id="<?= $item['id'] ?>" data-person-type="<?= $personType ?>" data-person-name="<?= htmlspecialchars($item['nama']) ?>" data-has-uid="<?= $hasUid ? '1' : '0' ?>">
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl <?= $tab === 'students' ? 'bg-indigo-100 text-indigo-700' : 'bg-purple-100 text-purple-700' ?> font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-xs">
                                            <?= $initial ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 leading-tight"><?= htmlspecialchars($item['nama']) ?></div>
                                            <div class="text-[11px] text-gray-400 font-medium mt-0.5">
                                                <?= $tab === 'students' ? 'NIS: ' . htmlspecialchars($item['nis'] ?: '-') : 'Username: ' . htmlspecialchars($item['username']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <?php if ($tab === 'students'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200/60">
                                            <i class="ri-door-line text-[11px] text-gray-400"></i>
                                            <?= htmlspecialchars(!empty($item['kelas_nama']) ? 'Kelas ' . $item['kelas_nama'] : 'Belum Ditentukan') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                                            <i class="ri-user-star-line text-[11px]"></i>
                                            <?= htmlspecialchars($item['nip'] ? 'NIP: ' . $item['nip'] : 'Dewan Pengajar') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="py-4 px-5">
                                    <div id="uid-container-<?= $personType ?>-<?= $item['id'] ?>">
                                        <?php if ($hasUid): ?>
                                            <span class="inline-flex items-center gap-1.5 font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-200/80 shadow-xs" id="uid-badge-<?= $personType ?>-<?= $item['id'] ?>">
                                                <i class="ri-rfid-line text-indigo-500"></i>
                                                <?= htmlspecialchars($item['rfid_uid']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-xs text-gray-400 font-medium px-2.5 py-1 rounded-lg border border-dashed border-gray-200" id="uid-badge-<?= $personType ?>-<?= $item['id'] ?>">
                                                <i class="ri-indeterminate-circle-line text-[11px]"></i> Belum Terhubung
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="py-4 px-5 text-center">
                                    <span id="status-pill-<?= $personType ?>-<?= $item['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?= $hasUid ? 'bg-emerald-100 text-emerald-800 border border-emerald-200/70' : 'bg-gray-100 text-gray-500 border border-gray-200/60' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $hasUid ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' ?>"></span>
                                        <?= $hasUid ? 'Terhubung' : 'Kosong' ?>
                                    </span>
                                </td>

                                <td class="py-4 px-5 text-center">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <button type="button" 
                                                id="btn-pair-<?= $personType ?>-<?= $item['id'] ?>"
                                                onclick="openPairModal('<?= $personType ?>', <?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['nama'])) ?>', '<?= htmlspecialchars($item['rfid_uid'] ?? '') ?>')" 
                                                class="px-3 py-1.5 text-xs font-bold rounded-xl transition flex items-center gap-1 <?= $hasUid ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs' ?>">
                                            <i class="ri-bank-card-line"></i>
                                            <span><?= $hasUid ? 'Ganti' : 'Hubungkan' ?></span>
                                        </button>

                                        <?php if ($hasUid): ?>
                                            <button type="button" 
                                                    id="btn-unpair-<?= $personType ?>-<?= $item['id'] ?>"
                                                    onclick="unpairCard('<?= $personType ?>', <?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['nama'])) ?>')" 
                                                    class="p-1.5 text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition border border-rose-200/70" 
                                                    title="Lepas Kartu">
                                                <i class="ri-link-unlink-m"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination for students -->
        <?php if ($tab === 'students' && $totalPages > 1): ?>
            <div class="p-5 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-gray-500 bg-gray-50/40">
                <div>
                    Menampilkan halaman <strong class="text-gray-800"><?= $page ?></strong> dari <strong class="text-gray-800"><?= $totalPages ?></strong> (Total <?= $totalStudents ?> santri)
                </div>
                <div class="flex items-center gap-1.5">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('/smart-tap/cards?tab=students&page=' . ($page - 1) . '&search=' . urlencode($search) . '&has_card=' . urlencode($statusCard) . '&kelas_id=' . urlencode($kelasId ?? '')) ?>" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white font-semibold hover:bg-gray-50 transition shadow-xs">
                            &larr; Sebelumnya
                        </a>
                    <?php endif; ?>

                    <span class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                        <?= $page ?>
                    </span>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('/smart-tap/cards?tab=students&page=' . ($page + 1) . '&search=' . urlencode($search) . '&has_card=' . urlencode($statusCard) . '&kelas_id=' . urlencode($kelasId ?? '')) ?>" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white font-semibold hover:bg-gray-50 transition shadow-xs">
                            Berikutnya &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ============================================== -->
<!-- MODAL PAIRING KARTU RFID (FAST-TAP MODE)       -->
<!-- ============================================== -->
<div id="modal-pair" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg">
                    <i class="ri-rfid-line"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Tap / Hubungkan Kartu RFID</h3>
                    <p class="text-[11px] text-gray-500">Scanner USB siap menerima kartu fisik</p>
                </div>
            </div>
            <button onclick="closePairModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <form id="pair-form" onsubmit="submitPair(event)" class="p-6 space-y-4">
            <input type="hidden" id="pair_person_type">
            <input type="hidden" id="pair_person_id">

            <div class="bg-indigo-50/80 p-4 rounded-2xl border border-indigo-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0" id="pair_avatar">
                    ?
                </div>
                <div>
                    <div class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider">Target Akun:</div>
                    <div class="text-sm font-bold text-gray-900" id="pair_person_name"></div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                    Tap Kartu pada Scanner Reader USB:
                </label>
                <div class="relative">
                    <i class="ri-rfid-line absolute left-3.5 top-1/2 -translate-y-1/2 text-indigo-500 text-lg"></i>
                    <input type="text" id="pair_card_uid" required autofocus placeholder="Tap kartu atau ketik UID..." class="w-full text-base font-mono font-bold rounded-2xl border-indigo-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 py-3 pl-10 pr-3 bg-indigo-50/20 text-gray-900 tracking-wider">
                </div>
                <p class="text-[11px] text-gray-400 mt-2 flex items-center gap-1.5">
                    <i class="ri-information-line text-indigo-500"></i> Scanner USB akan otomatis mengisi nomor UID dan menekan Enter.
                </p>
            </div>

            <!-- Auto-next Checkbox for Fast Batch Pairing -->
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 flex items-center justify-between">
                <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                    <input type="checkbox" id="auto_next_checkbox" checked class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span>Mode Cepat (Lanjut otomatis ke orang berikutnya)</span>
                </label>
                <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Cepat</span>
            </div>

            <div id="pair-feedback" class="text-xs font-semibold hidden p-3 rounded-xl"></div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="closePairModal()" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition">Batal</button>
                <button type="submit" id="pair-submit-btn" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                    Simpan Kartu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPairIndex = -1;

function openPairModal(type, id, name, currentUid) {
    document.getElementById('pair_person_type').value = type;
    document.getElementById('pair_person_id').value = id;
    document.getElementById('pair_person_name').innerText = name;
    document.getElementById('pair_avatar').innerText = name.charAt(0).toUpperCase();
    
    const uidInput = document.getElementById('pair_card_uid');
    uidInput.value = currentUid || '';
    
    const feedback = document.getElementById('pair-feedback');
    feedback.className = 'text-xs font-semibold hidden p-3 rounded-xl';
    feedback.innerText = '';

    document.getElementById('modal-pair').classList.remove('hidden');
    setTimeout(() => {
        uidInput.focus();
        uidInput.select();
    }, 150);
}

function closePairModal() {
    document.getElementById('modal-pair').classList.add('hidden');
}

function submitPair(e) {
    e.preventDefault();
    const type = document.getElementById('pair_person_type').value;
    const id = document.getElementById('pair_person_id').value;
    const uid = document.getElementById('pair_card_uid').value.trim();
    const btn = document.getElementById('pair-submit-btn');
    const feedback = document.getElementById('pair-feedback');
    const autoNext = document.getElementById('auto_next_checkbox').checked;

    if (!uid) return;

    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    const formData = new FormData();
    formData.append('person_type', type);
    formData.append('person_id', id);
    formData.append('card_uid', uid);

    fetch('<?= url('/smart-tap/cards/pair') ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = 'Simpan Kartu';

        if (data.success) {
            feedback.className = 'text-xs font-semibold p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 block';
            feedback.innerText = data.message;

            // Update row UI live
            const uidContainer = document.getElementById(`uid-container-${type}-${id}`);
            if (uidContainer) {
                uidContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-200/80 shadow-xs" id="uid-badge-${type}-${id}">
                        <i class="ri-rfid-line text-indigo-500"></i>
                        ${uid}
                    </span>
                `;
            }

            const statusPill = document.getElementById(`status-pill-${type}-${id}`);
            if (statusPill) {
                statusPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200/70";
                statusPill.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Terhubung`;
            }

            const row = document.getElementById(`row-${type}-${id}`);
            if (row) {
                row.setAttribute('data-has-uid', '1');
                const btnPair = document.getElementById(`btn-pair-${type}-${id}`);
                if (btnPair) {
                    btnPair.className = "px-3 py-1.5 text-xs font-bold rounded-xl transition flex items-center gap-1 bg-gray-100 text-gray-700 hover:bg-gray-200";
                    btnPair.innerHTML = `<i class="ri-bank-card-line"></i><span>Ganti</span>`;
                }
            }

            if (autoNext) {
                // Find next row without UID
                const rows = Array.from(document.querySelectorAll('.card-row'));
                const currentIndex = rows.findIndex(r => r.getAttribute('data-person-id') == id);
                let nextRow = null;
                for (let i = currentIndex + 1; i < rows.length; i++) {
                    if (rows[i].getAttribute('data-has-uid') === '0') {
                        nextRow = rows[i];
                        break;
                    }
                }

                if (nextRow) {
                    setTimeout(() => {
                        const nType = nextRow.getAttribute('data-person-type');
                        const nId = nextRow.getAttribute('data-person-id');
                        const nName = nextRow.getAttribute('data-person-name');
                        openPairModal(nType, nId, nName, '');
                    }, 500);
                } else {
                    setTimeout(() => closePairModal(), 600);
                }
            } else {
                setTimeout(() => closePairModal(), 600);
            }

        } else {
            feedback.className = 'text-xs font-semibold p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 block';
            feedback.innerText = data.message;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = 'Simpan Kartu';
        feedback.className = 'text-xs font-semibold p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 block';
        feedback.innerText = 'Terjadi kesalahan sistem.';
    });
}

function unpairCard(type, id, name) {
    if (!confirm(`Lepas kartu RFID dari ${name}? Kartu ini tidak akan bisa digunakan lagi untuk presensi sebelum dipairing kembali.`)) return;

    const formData = new FormData();
    formData.append('person_type', type);
    formData.append('person_id', id);

    fetch('<?= url('/smart-tap/cards/unpair') ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const uidContainer = document.getElementById(`uid-container-${type}-${id}`);
            if (uidContainer) {
                uidContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1 text-xs text-gray-400 font-medium px-2.5 py-1 rounded-lg border border-dashed border-gray-200" id="uid-badge-${type}-${id}">
                        <i class="ri-indeterminate-circle-line text-[11px]"></i> Belum Terhubung
                    </span>
                `;
            }
            const statusPill = document.getElementById(`status-pill-${type}-${id}`);
            if (statusPill) {
                statusPill.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200/60";
                statusPill.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Kosong`;
            }
            const btnUnpair = document.getElementById(`btn-unpair-${type}-${id}`);
            if (btnUnpair) btnUnpair.remove();

            const btnPair = document.getElementById(`btn-pair-${type}-${id}`);
            if (btnPair) {
                btnPair.className = "px-3 py-1.5 text-xs font-bold rounded-xl transition flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs";
                btnPair.innerHTML = `<i class="ri-bank-card-line"></i><span>Hubungkan</span>`;
            }

            const row = document.getElementById(`row-${type}-${id}`);
            if (row) row.setAttribute('data-has-uid', '0');
        } else {
            alert(data.message);
        }
    });
}
</script>

<?php
renderFooter();
?>
