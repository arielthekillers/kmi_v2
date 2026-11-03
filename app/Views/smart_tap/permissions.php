<?php
renderHeader("Smart Tap — Manajemen Perizinan & Dispensasi Guru");
$tab = $tab ?? 'permissions';
$eventMap = [];
if (!empty($events)) {
    foreach ($events as $ev) {
        $eventMap[$ev['id']] = $ev['name'];
    }
}
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
                <div class="w-12 h-12 rounded-2xl <?= $tab === 'exemptions' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700' ?> flex items-center justify-center text-2xl flex-shrink-0 shadow-xs">
                    <i class="<?= $tab === 'exemptions' ? 'ri-shield-star-line' : 'ri-hotel-bed-line' ?>"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                        <?= $tab === 'exemptions' ? 'Guru Bebas Smart Tap' : 'Perizinan Santri & Pengajar' ?>
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
                        <?= $tab === 'exemptions' 
                            ? 'Daftar guru senior & pimpinan pondok yang dibebaskan permanen dari seluruh kewajiban absensi smart tap agar tidak terhitung Alpa/Ghaib.' 
                            : 'Catat santri dan guru yang sedang sakit di klinik atau izin keluar resmi agar otomatis terbebas dari Alpa pada jadwal Smart Tap.' ?>
                    </p>
                </div>
            </div>

            <!-- Right Action Button -->
            <div class="flex items-center gap-3 self-start lg:self-center">
                <?php if ($tab === 'exemptions'): ?>
                    <button onclick="openExemptionModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white text-sm font-bold rounded-2xl shadow-sm shadow-amber-200 transition-all hover:shadow-md">
                        <i class="ri-user-add-line text-base"></i>
                        <span>Tetapkan Guru Bebas</span>
                    </button>
                <?php else: ?>
                    <button onclick="openPermissionModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold rounded-2xl shadow-sm shadow-indigo-200 transition-all hover:shadow-md">
                        <i class="ri-add-line text-base"></i>
                        <span>Tambah Izin / Sakit</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seamless Segmented Tabs -->
        <div class="mt-6 pt-5 border-t border-gray-100 flex items-center gap-2 overflow-x-auto pb-1">
            <a href="<?= url('/smart-tap/permissions?tab=permissions') ?>" 
               class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 whitespace-nowrap <?= $tab === 'permissions' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/60 shadow-xs' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                <i class="ri-hotel-bed-line text-sm"></i>
                <span>Izin & Sakit Sementara</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold <?= $tab === 'permissions' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' ?>">
                    <?= count($permissions) ?>
                </span>
            </a>
            
            <a href="<?= url('/smart-tap/permissions?tab=exemptions') ?>" 
               class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 whitespace-nowrap <?= $tab === 'exemptions' ? 'bg-amber-50 text-amber-800 border border-amber-200/80 shadow-xs' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' ?>">
                <i class="ri-shield-star-line text-sm text-amber-500"></i>
                <span>Guru Bebas Smart Tap (Senior / Pengasuh)</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold <?= $tab === 'exemptions' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-500' ?>">
                    <?= count($exemptions) ?>
                </span>
            </a>
        </div>
    </div>

    <?php if ($tab === 'exemptions'): ?>
        <!-- ========================================== -->
        <!-- TAB 2: GURU BEBAS SMART TAP (GURU SENIOR)  -->
        <!-- ========================================== -->
        <div class="space-y-6">
            <!-- Notice Banner -->
            <div class="bg-gradient-to-r from-amber-50 via-amber-50/60 to-orange-50 border border-amber-200/80 rounded-3xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100/80 text-amber-800 flex items-center justify-center text-xl flex-shrink-0 mt-0.5 shadow-inner">
                        <i class="ri-information-line"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-950">Dispensasi Tetap Guru Senior / Pengasuh</h4>
                        <p class="text-xs text-amber-800/90 mt-0.5 leading-relaxed max-w-3xl">
                            Guru yang terdaftar di bawah ini secara otomatis <strong>dikeluarkan dari daftar wajib tap</strong> sholat berjamaah maupun jadwal absensi smart tap lainnya. Sistem tidak akan pernah mencatat mereka sebagai <strong>Alpa / Ghaib</strong>.
                        </p>
                    </div>
                </div>

                <div class="text-xs text-amber-700 bg-white/70 px-3.5 py-2 rounded-xl border border-amber-200/60 font-semibold self-start md:self-center whitespace-nowrap">
                    <i class="ri-shield-check-line text-amber-600 mr-1"></i> Berlaku Permanen
                </div>
            </div>

            <!-- Exemptions Table Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-gray-900">Daftar Guru Bebas Smart Tap</h3>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800">
                            <?= count($exemptions) ?> Guru
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3.5 px-5 w-12 text-center">No</th>
                                <th class="py-3.5 px-5">Nama Pengajar / Guru</th>
                                <th class="py-3.5 px-5">Kategori Dispensasi</th>
                                <th class="py-3.5 px-5">Cakupan Pembebasan</th>
                                <th class="py-3.5 px-5">Keterangan / Alasan</th>
                                <th class="py-3.5 px-5">Tanggal Ditetapkan</th>
                                <th class="py-3.5 px-5 text-center w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($exemptions)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-16 px-4">
                                        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto text-3xl mb-3 shadow-inner">
                                            <i class="ri-user-unfollow-line"></i>
                                        </div>
                                        <h4 class="font-bold text-gray-800 text-sm">Belum Ada Guru yang Dibebaskan</h4>
                                        <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto leading-relaxed">
                                            Saat ini seluruh guru aktif masih dikenakan kewajiban absensi smart tap. Tetapkan guru sepuh/pengasuh agar terbebas dari absensi.
                                        </p>
                                        <button type="button" onclick="openExemptionModal()" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-xl transition border border-amber-200">
                                            <i class="ri-user-add-line"></i> Tetapkan Guru Bebas Sekarang
                                        </button>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($exemptions as $ex): ?>
                                    <tr class="hover:bg-amber-50/20 transition">
                                        <td class="py-4 px-5 text-xs font-medium text-gray-400 text-center"><?= $no++ ?></td>
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-xs">
                                                    <?= mb_substr($ex['nama'], 0, 1) ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900"><?= htmlspecialchars($ex['nama_gelar'] ?: $ex['nama']) ?></div>
                                                    <div class="text-[11px] text-gray-400 font-medium">
                                                        <?= htmlspecialchars($ex['nip'] ? 'NIP: ' . $ex['nip'] : 'Username: ' . $ex['username']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200/80">
                                                <i class="ri-star-fill text-[11px] text-amber-500"></i>
                                                <?= htmlspecialchars($ex['category'] ?: 'Guru Senior') ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-5">
                                            <?php 
                                            $scope = $ex['scope'] ?? 'all';
                                            if ($scope === 'sholat'): ?>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                    <i class="ri-moon-fill text-emerald-600 text-xs"></i>
                                                    Khusus Sholat 5 Waktu
                                                </span>
                                            <?php elseif ($scope === 'custom'): 
                                                $evIds = array_filter(array_map('trim', explode(',', $ex['event_ids'] ?? '')));
                                                $evNames = [];
                                                foreach ($evIds as $eid) {
                                                    if (isset($eventMap[$eid])) {
                                                        $evNames[] = $eventMap[$eid];
                                                    }
                                                }
                                            ?>
                                                <div class="space-y-1">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                                        <i class="ri-list-check text-blue-600 text-xs"></i>
                                                        <?= count($evIds) ?> Event Tertentu
                                                    </span>
                                                    <?php if (!empty($evNames)): ?>
                                                        <div class="text-[11px] text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars(implode(', ', $evNames)) ?>">
                                                            <?= htmlspecialchars(implode(', ', $evNames)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-800 border border-purple-200">
                                                    <i class="ri-checkbox-circle-fill text-purple-600 text-xs"></i>
                                                    Semua Jadwal Absensi
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-5 text-xs text-gray-600">
                                            <?= htmlspecialchars($ex['reason'] ?: '-') ?>
                                        </td>
                                        <td class="py-4 px-5 text-xs text-gray-500 font-medium">
                                            <?= date('d M Y', strtotime($ex['created_at'])) ?>
                                        </td>
                                        <td class="py-4 px-5 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" 
                                                        onclick='editExemption(<?= htmlspecialchars(json_encode([
                                                            "id" => $ex["id"],
                                                            "user_id" => $ex["user_id"],
                                                            "nama" => $ex["nama_gelar"] ?: $ex["nama"],
                                                            "category" => $ex["category"],
                                                            "scope" => $ex["scope"] ?? "all",
                                                            "event_ids" => $ex["event_ids"] ?? "",
                                                            "reason" => $ex["reason"] ?? ""
                                                        ]), ENT_QUOTES, "UTF-8") ?>)'
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100/70 rounded-xl transition border border-amber-200"
                                                        title="Edit Aturan Dispensasi">
                                                    <i class="ri-edit-line"></i>
                                                    <span>Edit</span>
                                                </button>
                                                <a href="<?= url('/smart-tap/exemptions/delete?id=' . $ex['id']) ?>" 
                                                   onclick="return confirm('Cabut dispensasi untuk <?= htmlspecialchars(addslashes($ex['nama'])) ?>? Guru ini akan kembali dikenakan kewajiban absensi smart tap.')" 
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition border border-rose-200/70"
                                                   title="Cabut Dispensasi">
                                                    <i class="ri-delete-bin-line"></i>
                                                    <span>Cabut</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ========================================== -->
        <!-- TAB 1: PERIZINAN SEMENTARA (IZIN & SAKIT)  -->
        <!-- ========================================== -->
        <div class="space-y-6">
            <!-- Unified Card with Integrated Filters & Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Integrated Filter Header Bar -->
                <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                    <form method="GET" action="<?= url('/smart-tap/permissions') ?>" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <input type="hidden" name="tab" value="permissions">

                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-900">Daftar Perizinan Aktif & Terdata</h3>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                <?= count($permissions) ?> Data
                            </span>
                        </div>

                        <!-- Filter Controls -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <div class="relative">
                                <select name="person_type" class="text-xs rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium text-gray-700">
                                    <option value="">Semua Kategori</option>
                                    <option value="student" <?= ($filters['person_type'] ?? '') == 'student' ? 'selected' : '' ?>>Santri Saja</option>
                                    <option value="teacher" <?= ($filters['person_type'] ?? '') == 'teacher' ? 'selected' : '' ?>>Pengajar Saja</option>
                                </select>
                            </div>

                            <div class="relative">
                                <select name="permission_type" class="text-xs rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium text-gray-700">
                                    <option value="">Semua Jenis Izin</option>
                                    <option value="sakit" <?= ($filters['permission_type'] ?? '') == 'sakit' ? 'selected' : '' ?>>Sakit (Klinik)</option>
                                    <option value="izin" <?= ($filters['permission_type'] ?? '') == 'izin' ? 'selected' : '' ?>>Izin Pulang / Dinas</option>
                                    <option value="tugas_luar" <?= ($filters['permission_type'] ?? '') == 'tugas_luar' ? 'selected' : '' ?>>Tugas Luar</option>
                                </select>
                            </div>

                            <div>
                                <input type="date" name="date" value="<?= htmlspecialchars($filters['date'] ?? '') ?>" class="text-xs rounded-xl border border-gray-200 bg-white py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium text-gray-700">
                            </div>

                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                                <i class="ri-filter-3-line"></i> Filter
                            </button>
                            
                            <?php if (!empty($filters['person_type']) || !empty($filters['permission_type']) || !empty($filters['date'])): ?>
                                <a href="<?= url('/smart-tap/permissions?tab=permissions') ?>" class="text-xs font-semibold text-gray-400 hover:text-gray-700 px-2 py-2">
                                    Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3.5 px-5">Nama Santri / Pengajar</th>
                                <th class="py-3.5 px-5">Kategori & Kelas</th>
                                <th class="py-3.5 px-5">Jenis Izin</th>
                                <th class="py-3.5 px-5">Rentang Tanggal</th>
                                <th class="py-3.5 px-5">Alasan / Catatan</th>
                                <th class="py-3.5 px-5 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($permissions)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-16 px-4">
                                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto text-3xl mb-3 shadow-inner">
                                            <i class="ri-file-shield-line"></i>
                                        </div>
                                        <h4 class="font-bold text-gray-800 text-sm">Belum Ada Data Perizinan</h4>
                                        <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto leading-relaxed">
                                            <?php if (!empty($filters['person_type']) || !empty($filters['permission_type']) || !empty($filters['date'])): ?>
                                                Tidak ditemukan data perizinan yang sesuai dengan filter pencarian.
                                            <?php else: ?>
                                                Saat ini tidak ada santri atau guru yang tercatat dalam status izin sakit atau izin keluar.
                                            <?php endif; ?>
                                        </p>
                                        <button type="button" onclick="openPermissionModal()" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition border border-indigo-200">
                                            <i class="ri-add-line"></i> Tambah Izin Baru
                                        </button>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($permissions as $p): ?>
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl <?= $p['person_type'] == 'student' ? 'bg-indigo-100 text-indigo-700' : 'bg-purple-100 text-purple-700' ?> font-bold text-xs flex items-center justify-center flex-shrink-0">
                                                    <?= mb_substr($p['person_name'], 0, 1) ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900"><?= htmlspecialchars($p['person_name']) ?></div>
                                                    <div class="text-[11px] text-gray-400 font-medium"><?= htmlspecialchars($p['person_identifier']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium <?= $p['person_type'] == 'student' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-purple-50 text-purple-700 border border-purple-100' ?>">
                                                <i class="<?= $p['person_type'] == 'student' ? 'ri-user-line' : 'ri-user-star-line' ?> text-[11px]"></i>
                                                <?= htmlspecialchars($p['person_subinfo']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-5">
                                            <?php 
                                            $pBg = 'bg-blue-50 text-blue-700 border-blue-200';
                                            $pIcon = 'ri-file-text-line';
                                            if ($p['permission_type'] == 'sakit') {
                                                $pBg = 'bg-rose-50 text-rose-700 border-rose-200';
                                                $pIcon = 'ri-heart-pulse-line';
                                            } elseif ($p['permission_type'] == 'tugas_luar') {
                                                $pBg = 'bg-amber-50 text-amber-700 border-amber-200';
                                                $pIcon = 'ri-briefcase-line';
                                            }
                                            ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase border <?= $pBg ?>">
                                                <i class="<?= $pIcon ?> text-[11px]"></i>
                                                <?= htmlspecialchars($p['permission_type']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-5 text-xs font-medium text-gray-700">
                                            <div class="flex items-center gap-1.5">
                                                <i class="ri-calendar-line text-gray-400"></i>
                                                <span>
                                                    <?= date('d M Y', strtotime($p['start_date'])) ?> 
                                                    <?php if ($p['start_date'] != $p['end_date']): ?>
                                                        &mdash; <?= date('d M Y', strtotime($p['end_date'])) ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5 text-xs text-gray-500 max-w-xs truncate">
                                            <?= htmlspecialchars($p['reason'] ?: '-') ?>
                                        </td>
                                        <td class="py-4 px-5 text-center">
                                            <a href="<?= url('/smart-tap/permissions/delete?id=' . $p['id']) ?>" 
                                               onclick="return confirm('Hapus data izin ini?')" 
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition"
                                               title="Hapus Izin">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ============================================== -->
<!-- MODAL 1: FORM TAMBAH IZIN / SAKIT (SEMENTARA) -->
<!-- ============================================== -->
<div id="modal-permission" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg">
                    <i class="ri-hotel-bed-line"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Form Perizinan Sementara</h3>
                    <p class="text-[11px] text-gray-500">Santri / Guru tidak akan terhitung Alpa selama izin</p>
                </div>
            </div>
            <button onclick="closePermissionModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <form method="POST" action="<?= url('/smart-tap/permissions/store') ?>" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Kategori Peserta</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-3 border rounded-2xl cursor-pointer hover:bg-indigo-50/50 text-xs font-bold border-gray-200 transition">
                        <input type="radio" name="person_type" value="student" checked onchange="togglePersonSearch('student')" class="text-indigo-600 focus:ring-indigo-500">
                        <span>Santri</span>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 border rounded-2xl cursor-pointer hover:bg-indigo-50/50 text-xs font-bold border-gray-200 transition">
                        <input type="radio" name="person_type" value="teacher" onchange="togglePersonSearch('teacher')" class="text-indigo-600 focus:ring-indigo-500">
                        <span>Pengajar / Guru</span>
                    </label>
                </div>
            </div>

            <!-- Person Selector via search -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Cari & Pilih Nama</label>
                <div class="relative">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="person_search_input" oninput="searchPerson(this.value)" placeholder="Ketik nama atau NIS..." class="w-full text-xs rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 pl-9 pr-3">
                </div>
                
                <input type="hidden" name="person_id" id="selected_person_id" required>
                <div id="person_search_results" class="mt-2 max-h-40 overflow-y-auto border border-gray-100 rounded-2xl divide-y divide-gray-50 hidden bg-white shadow-lg"></div>
                <div id="selected_person_badge" class="mt-2 text-xs font-bold text-indigo-700 bg-indigo-50 p-2.5 rounded-xl hidden flex items-center justify-between border border-indigo-100">
                    <span id="selected_person_text"></span>
                    <button type="button" onclick="clearSelectedPerson()" class="text-indigo-500 hover:text-indigo-700 text-sm">&times;</button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Jenis Izin</label>
                <select name="permission_type" class="w-full text-xs rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
                    <option value="sakit">Sakit (Klinik Pondok / Istirahat Kamar)</option>
                    <option value="izin">Izin Pulang / Dinas Resmi</option>
                    <option value="tugas_luar">Tugas Luar / Kegiatan Pesantren</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required class="w-full text-xs rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="<?= date('Y-m-d') ?>" required class="w-full text-xs rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Alasan / Catatan (Opsional)</label>
                <textarea name="reason" rows="2" placeholder="Contoh: Demam tinggi di Poskestren / Izin ke dokter spesialis" class="w-full text-xs rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closePermissionModal()" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm">Simpan Izin</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL 2: FORM TETAPKAN GURU BEBAS SMART TAP    -->
<!-- ============================================== -->
<div id="modal-exemption" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all max-h-[92vh] flex flex-col">
        <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg">
                    <i class="ri-shield-star-line"></i>
                </div>
                <div>
                    <h3 id="modal-exemption-title" class="text-base font-bold text-gray-900">Tetapkan Guru Bebas Smart Tap</h3>
                    <p id="modal-exemption-subtitle" class="text-[11px] text-gray-500">Dispensasi fleksibel untuk asatidz & pengasuh</p>
                </div>
            </div>
            <button onclick="closeExemptionModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <form method="POST" action="<?= url('/smart-tap/exemptions/store') ?>" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Pilih Pengajar / Guru <span class="text-rose-500">*</span></label>
                <select name="user_id" id="exemption_user_id" required class="w-full text-xs rounded-2xl border-gray-200 focus:border-amber-500 focus:ring-amber-500 py-2.5 px-3 font-medium text-gray-800">
                    <option value="">-- Pilih Guru / Asatidz --</option>
                    <?php 
                    $exemptedUserIds = array_column($exemptions ?? [], 'user_id');
                    foreach ($teachers as $t): 
                        $isAlreadyExempt = in_array($t['id'], $exemptedUserIds);
                    ?>
                        <option value="<?= $t['id'] ?>" data-exempt="<?= $isAlreadyExempt ? '1' : '0' ?>" <?= $isAlreadyExempt ? 'class="text-amber-800 font-semibold bg-amber-50/50"' : '' ?>>
                            <?= htmlspecialchars($t['nama']) ?> <?= $isAlreadyExempt ? '(Sudah Ada - Klik untuk Ubah Aturan)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Kategori Status Dispensasi</label>
                <select name="category" id="exemption_category" class="w-full text-xs rounded-2xl border-gray-200 focus:border-amber-500 focus:ring-amber-500 py-2.5 px-3 font-medium text-gray-800">
                    <option value="Guru Senior">Guru Senior (Asatidz Sepuh)</option>
                    <option value="Pimpinan / Pengasuh">Pimpinan / Dewan Pengasuh</option>
                    <option value="Guru Laju / Domisili Luar">Guru Laju / Domisili Luar Pondok</option>
                    <option value="Ibu Menyusui / Balita">Ibu Menyusui / Mengasuh Balita</option>
                    <option value="Tugas Khusus">Tugas Khusus / Luar</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <!-- Cakupan Pembebasan (Scope) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">Cakupan Pembebasan (Scope) <span class="text-rose-500">*</span></label>
                <div class="space-y-2">
                    <!-- Option 1: Semua Absensi -->
                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-gray-200 hover:border-amber-300 hover:bg-amber-50/30 cursor-pointer transition has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/40">
                        <input type="radio" name="scope" value="all" checked onchange="toggleCustomEventsContainer('all')" class="mt-1 text-amber-600 focus:ring-amber-500">
                        <div>
                            <div class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                <i class="ri-checkbox-circle-fill text-purple-600"></i>
                                <span>Semua Jadwal Absensi (Penuh)</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed">
                                Bebas dari sholat berjamaah 5 waktu, piket kantor, rapat, dan seluruh event Smart Tap lainnya. Cocok untuk Kyai Sepuh & Pimpinan Pesantren.
                            </p>
                        </div>
                    </label>

                    <!-- Option 2: Khusus Sholat 5 Waktu -->
                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50/30 cursor-pointer transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/40">
                        <input type="radio" name="scope" value="sholat" onchange="toggleCustomEventsContainer('sholat')" class="mt-1 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <div class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                <i class="ri-moon-fill text-emerald-600"></i>
                                <span>Khusus Sholat 5 Waktu Berjamaah</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed">
                                Bebas dari absensi sholat berjamaah (guru domisili luar/laju atau punya balita), namun <strong>tetap wajib tap</strong> pada jadwal piket kantor, rapat dinas, dan event non-sholat.
                            </p>
                        </div>
                    </label>

                    <!-- Option 3: Kustom Event Tertentu -->
                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50/30 cursor-pointer transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/40">
                        <input type="radio" name="scope" value="custom" onchange="toggleCustomEventsContainer('custom')" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                <i class="ri-list-check text-blue-600"></i>
                                <span>Pilih Event / Jadwal Tertentu (Kustom)</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed">
                                Tentukan secara spesifik event-event apa saja yang dibebaskan untuk guru ini.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Checklist Event untuk Scope Custom -->
            <div id="custom-events-container" class="space-y-2 hidden">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-gray-700">Daftar Event yang Dibebaskan</label>
                    <div class="flex gap-2 text-[11px]">
                        <button type="button" onclick="toggleAllEvents(true)" class="text-blue-600 hover:underline font-semibold">Pilih Semua</button>
                        <span class="text-gray-300">|</span>
                        <button type="button" onclick="toggleAllEvents(false)" class="text-gray-500 hover:underline">Hapus Semua</button>
                    </div>
                </div>

                <div class="max-h-48 overflow-y-auto p-3 bg-gray-50/70 border border-gray-200/80 rounded-2xl space-y-1.5">
                    <?php if (empty($events)): ?>
                        <div class="text-xs text-gray-400 text-center py-2">Belum ada master event yang dibuat.</div>
                    <?php else: ?>
                        <?php foreach ($events as $ev): ?>
                            <label class="flex items-center justify-between p-2 rounded-xl hover:bg-white text-xs cursor-pointer border border-transparent hover:border-gray-200 transition">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="event_ids[]" value="<?= $ev['id'] ?>" class="text-blue-600 focus:ring-blue-500 rounded">
                                    <span class="font-bold text-gray-800"><?= htmlspecialchars($ev['name']) ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold <?= ($ev['category'] ?? '') === 'sholat' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' ?>">
                                        <?= htmlspecialchars(ucfirst($ev['category'] ?? 'Event')) ?>
                                    </span>
                                    <span class="text-[11px] text-gray-400 font-mono">
                                        <?= substr($ev['open_time'], 0, 5) ?> - <?= substr($ev['close_time'], 0, 5) ?>
                                    </span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Keterangan / Alasan (Opsional)</label>
                <textarea name="reason" id="exemption_reason" rows="2" placeholder="Contoh: Guru laju domisili di Parung, punya anak balita sehingga dibebaskan dari sholat Shubuh/Isya." class="w-full text-xs rounded-2xl border-gray-200 focus:border-amber-500 focus:ring-amber-500 py-2 px-3"></textarea>
            </div>

            <div class="bg-amber-50/80 p-3.5 rounded-2xl border border-amber-200/60 text-[11px] text-amber-900 flex items-start gap-2">
                <i class="ri-shield-check-line text-amber-600 text-base mt-0.5 flex-shrink-0"></i>
                <span class="leading-relaxed">Sistem Smart Tap akan mengecualikan guru ini sesuai cakupan yang dipilih sehingga tidak akan terhitung <strong>Alpa/Ghaib</strong> pada jadwal terkait.</span>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 flex-shrink-0">
                <button type="button" onclick="closeExemptionModal()" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                    Simpan Dispensasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Permission
function openPermissionModal() {
    document.getElementById('modal-permission').classList.remove('hidden');
}
function closePermissionModal() {
    document.getElementById('modal-permission').classList.add('hidden');
}

// Modal Exemption
function openExemptionModal() {
    resetExemptionModal();
    document.getElementById('modal-exemption').classList.remove('hidden');
}
function closeExemptionModal() {
    document.getElementById('modal-exemption').classList.add('hidden');
}

function setExemptionScope(scopeVal) {
    const radios = document.querySelectorAll('input[name="scope"]');
    radios.forEach(r => {
        r.checked = (r.value === scopeVal);
    });
    toggleCustomEventsContainer(scopeVal);
}

function toggleCustomEventsContainer(scopeVal) {
    const container = document.getElementById('custom-events-container');
    if (!container) return;
    if (scopeVal === 'custom') {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

function toggleAllEvents(check) {
    document.querySelectorAll('input[name="event_ids[]"]').forEach(cb => {
        cb.checked = check;
    });
}

function editExemption(data) {
    openExemptionModal();
    document.getElementById('modal-exemption-title').innerText = 'Edit Dispensasi Guru';
    document.getElementById('modal-exemption-subtitle').innerText = 'Perbarui aturan dan cakupan dispensasi guru';
    
    const userSelect = document.getElementById('exemption_user_id');
    userSelect.value = data.user_id;

    document.getElementById('exemption_category').value = data.category || 'Guru Senior';
    document.getElementById('exemption_reason').value = data.reason || '';

    const scope = data.scope || 'all';
    setExemptionScope(scope);

    const eventIds = (data.event_ids || '').split(',').map(s => s.trim());
    document.querySelectorAll('input[name="event_ids[]"]').forEach(cb => {
        cb.checked = eventIds.includes(cb.value);
    });
}

function resetExemptionModal() {
    document.getElementById('modal-exemption-title').innerText = 'Tetapkan Guru Bebas Smart Tap';
    document.getElementById('modal-exemption-subtitle').innerText = 'Dispensasi fleksibel untuk asatidz & pengasuh';
    document.getElementById('exemption_user_id').value = '';
    document.getElementById('exemption_category').value = 'Guru Senior';
    document.getElementById('exemption_reason').value = '';
    setExemptionScope('all');
    document.querySelectorAll('input[name="event_ids[]"]').forEach(cb => cb.checked = false);
}

let currentType = 'student';
function togglePersonSearch(type) {
    currentType = type;
    clearSelectedPerson();
    document.getElementById('person_search_input').value = '';
    document.getElementById('person_search_results').innerHTML = '';
    document.getElementById('person_search_results').classList.add('hidden');
}

let searchTimer;
function searchPerson(q) {
    clearTimeout(searchTimer);
    if (q.length < 2) {
        document.getElementById('person_search_results').classList.add('hidden');
        return;
    }

    searchTimer = setTimeout(() => {
        fetch('<?= url('/smart-tap/search-person?q=') ?>' + encodeURIComponent(q) + '&type=' + currentType)
            .then(res => res.json())
            .then(data => {
                const box = document.getElementById('person_search_results');
                if (!data || data.length === 0) {
                    box.innerHTML = '<div class="p-3 text-xs text-gray-400 text-center">Nama tidak ditemukan</div>';
                    box.classList.remove('hidden');
                    return;
                }

                let html = '';
                data.forEach(item => {
                    html += `
                        <div onclick="selectPerson(${item.id}, '${item.nama.replace(/'/g, "\\'")}')" class="p-2.5 hover:bg-indigo-50 cursor-pointer text-xs flex items-center justify-between transition">
                            <span class="font-bold text-gray-900">${item.nama}</span>
                            <span class="text-gray-400 text-[11px]">${item.subinfo || item.role || ''}</span>
                        </div>
                    `;
                });
                box.innerHTML = html;
                box.classList.remove('hidden');
            });
    }, 300);
}

function selectPerson(id, name) {
    document.getElementById('selected_person_id').value = id;
    document.getElementById('selected_person_text').innerText = 'Dipilih: ' + name;
    document.getElementById('selected_person_badge').classList.remove('hidden');
    document.getElementById('person_search_results').classList.add('hidden');
    document.getElementById('person_search_input').value = name;
}

function clearSelectedPerson() {
    document.getElementById('selected_person_id').value = '';
    document.getElementById('selected_person_text').innerText = '';
    document.getElementById('selected_person_badge').classList.add('hidden');
    document.getElementById('person_search_input').value = '';
}
</script>

<?php
renderFooter();
?>
