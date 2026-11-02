<?php
renderHeader("Smart Tap — Petugas Smart Tap");
?>

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="ri-checkbox-circle-fill text-lg"></i>
                <span class="text-sm font-semibold"><?= $_SESSION['success'] ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="ri-error-warning-fill text-lg"></i>
                <span class="text-sm font-semibold"><?= $_SESSION['error'] ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-user-star-line text-indigo-600"></i> Petugas Smart Tap
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Kelola hak akses pengajar / staf yang ditugaskan untuk mengoperasikan modul Smart Tap presensi.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                <i class="ri-shield-user-line"></i> Khusus Administrator
            </span>
        </div>
    </div>

    <!-- Info Guide Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-5 rounded-2xl shadow-sm border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="text-xs font-semibold text-indigo-300 uppercase tracking-wider flex items-center gap-1.5">
                <i class="ri-information-line text-indigo-400"></i> Ketentuan Hak Akses
            </div>
            <h3 class="text-base font-bold text-white">Otorisasi Pengajar Petugas Presensi</h3>
            <p class="text-xs text-indigo-200/90 max-w-3xl leading-relaxed">
                Pengajar yang ditandai sebagai <strong>Petugas Smart Tap</strong> akan memiliki akses penuh ke menu <strong>Live Monitor Kehadiran</strong>, <strong>Rekapitulasi & Disiplin</strong>, dan <strong>Input Perizinan Santri</strong>. Pengajar yang tidak terdaftar tidak dapat melihat menu maupun mengakses rute Smart Tap.
            </p>
        </div>
        <div class="text-right flex-shrink-0">
            <div class="text-xs text-slate-400">Total Petugas Aktif</div>
            <div class="text-2xl font-black text-emerald-400 font-mono"><?= count($officers) ?> Orang</div>
        </div>
    </div>

    <!-- Main Grid: List Officers & Assignment Selector -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Active Officers List (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <i class="ri-team-line text-indigo-600"></i> Daftar Petugas Terdaftar
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pengajar yang saat ini memegang akses Smart Tap</p>
                </div>
            </div>

            <div class="divide-y divide-gray-100 flex-1 overflow-y-auto max-h-[600px]">
                <?php if (empty($officers)): ?>
                    <div class="p-12 text-center text-gray-400 space-y-3">
                        <i class="ri-user-unfollow-line text-5xl text-gray-300"></i>
                        <p class="text-sm font-medium">Belum ada pengajar yang ditugaskan sebagai petugas Smart Tap.</p>
                        <p class="text-xs text-gray-400">Pilih nama pengajar di panel sebelah kanan untuk memberikan akses.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($officers as $officer): ?>
                        <div class="p-4 hover:bg-gray-50/80 transition flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-base shadow-sm">
                                    <?= strtoupper(substr($officer['teacher_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 leading-tight">
                                        <?= htmlspecialchars($officer['teacher_name']) ?>
                                    </h4>
                                    <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                        <span class="font-mono text-gray-400">@<?= htmlspecialchars($officer['username']) ?></span>
                                        <?php if (!empty($officer['rfid_uid'])): ?>
                                            <span>&bull;</span>
                                            <span class="text-[11px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono">
                                                RFID: <?= htmlspecialchars($officer['rfid_uid']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        Ditugaskan: <?= date('d M Y, H:i', strtotime($officer['created_at'])) ?>
                                        <?= !empty($officer['assigned_by_name']) ? 'oleh ' . htmlspecialchars($officer['assigned_by_name']) : '' ?>
                                    </div>
                                </div>
                            </div>

                            <a href="<?= url('/smart-tap/officers/delete?id=' . $officer['id']) ?>" 
                               onclick="return confirm('Cabut hak akses Smart Tap dari <?= htmlspecialchars(addslashes($officer['teacher_name'])) ?>?')"
                               class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition" 
                               title="Cabut Akses">
                                <i class="ri-delete-bin-line text-lg"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Assignment Form (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <div class="border-b border-gray-100 pb-4 mb-4">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <i class="ri-user-add-line text-indigo-600"></i> Kelola Hak Akses Pengajar
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Centang nama pengajar yang ingin diberikan hak akses modul Smart Tap, lalu klik simpan.
                    </p>
                </div>

                <!-- Live Search Filter for Teachers -->
                <div class="mb-3 relative">
                    <i class="ri-search-line absolute left-3.5 top-2.5 text-gray-400 text-sm"></i>
                    <input type="text" id="search-teacher-input" onkeyup="filterTeachers()" placeholder="Cari nama pengajar..." 
                           class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <!-- Form Bulk Update -->
                <form action="<?= url('/smart-tap/officers/update') ?>" method="POST" id="officer-form">
                    <?= csrf_input() ?>

                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2 px-1">
                        <span>Pilih Pengajar:</span>
                        <div class="space-x-2">
                            <button type="button" onclick="selectAll(true)" class="text-indigo-600 hover:underline font-semibold">Pilih Semua</button>
                            <span>&bull;</span>
                            <button type="button" onclick="selectAll(false)" class="text-gray-500 hover:underline">Hapus Pilihan</button>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-xl divide-y divide-gray-50 max-h-[380px] overflow-y-auto" id="teacher-checkbox-list">
                        <?php foreach ($teachers as $teacher): ?>
                            <?php $isAssigned = in_array($teacher['id'], $officerUserIds); ?>
                            <label class="teacher-item flex items-center justify-between p-3 hover:bg-indigo-50/50 cursor-pointer transition select-none">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="user_ids[]" value="<?= $teacher['id'] ?>" 
                                           <?= $isAssigned ? 'checked' : '' ?>
                                           class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                    <div>
                                        <div class="text-xs font-bold text-gray-800 teacher-name"><?= htmlspecialchars($teacher['nama']) ?></div>
                                        <div class="text-[11px] text-gray-400 font-mono">@<?= htmlspecialchars($teacher['username']) ?></div>
                                    </div>
                                </div>
                                <?php if ($isAssigned): ?>
                                    <span class="text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-bold">
                                        Petugas
                                    </span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-5">
                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                            <i class="ri-save-line text-base"></i> Simpan Penugasan Petugas
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

<script>
// Filter teachers list by search input
function filterTeachers() {
    const query = document.getElementById('search-teacher-input').value.toLowerCase();
    const items = document.querySelectorAll('.teacher-item');
    items.forEach(item => {
        const name = item.querySelector('.teacher-name').innerText.toLowerCase();
        if (name.includes(query)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Select / Deselect all visible
function selectAll(checked) {
    const checkboxes = document.querySelectorAll('#teacher-checkbox-list input[type="checkbox"]');
    checkboxes.forEach(cb => {
        const parent = cb.closest('.teacher-item');
        if (parent && parent.style.display !== 'none') {
            cb.checked = checked;
        }
    });
}
</script>

<?php
renderFooter();
?>

