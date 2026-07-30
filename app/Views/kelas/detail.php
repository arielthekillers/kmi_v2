<!-- e:\xampp\htdocs\kmi_v2\app\Views\kelas\detail.php -->
<style>
    /* Sembunyikan scrollbar untuk navigasi tab horizontal di mobile */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;  /* IE/Edge */
        scrollbar-width: none;  /* Firefox */
    }
</style>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb & Header (Settings Style) -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="<?= url('/') ?>" class="hover:text-indigo-600">
                <span class="hidden sm:inline">Dashboard</span>
                <i class="ri-home-4-line sm:hidden text-base"></i>
            </a>
            <?php if (auth_get_role() === 'admin'): ?>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="<?= url('/classes') ?>" class="hover:text-indigo-600">Data Kelas</a>
            <?php endif; ?>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-indigo-600 font-semibold">Detail Kelas</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                <i class="ri-community-line text-indigo-600 text-xl"></i>
            </div>
            Kelas <?= htmlspecialchars($kelas['tingkat']) ?>-<?= htmlspecialchars($kelas['abjad']) ?>
        </h2>
        <p class="text-gray-500 text-sm mt-1">Kelola data santri dan jadwal pelajaran untuk kelas ini.</p>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar Navigation (Settings Style) -->
        <aside class="w-full md:w-56 flex-shrink-0">
            <nav class="flex md:flex-col gap-1.5 overflow-x-auto no-scrollbar pb-3 md:pb-0 scroll-smooth">
                <div class="mt-4 mb-2 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden md:block">Utama</div>
                
                <a href="?id=<?= $kelas['id'] ?>&tab=overview" 
                   <?= $tab === 'overview' ? 'id="active-tab"' : '' ?>
                   class="whitespace-nowrap flex items-center gap-2 px-4 py-2.5 md:px-3 md:py-2 text-sm rounded-full md:rounded-lg border md:border-0 transition-all <?= $tab === 'overview' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 font-semibold shadow-sm' : 'bg-white md:bg-transparent border-gray-200 text-gray-600 hover:bg-gray-100' ?>">
                    <svg class="w-4 h-4 <?= $tab === 'overview' ? 'text-indigo-500' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Ringkasan
                </a>

                <div class="mt-4 mb-2 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden md:block">Akademik</div>
                
                <a href="?id=<?= $kelas['id'] ?>&tab=santri" 
                   <?= $tab === 'santri' ? 'id="active-tab"' : '' ?>
                   class="whitespace-nowrap flex items-center gap-2 px-4 py-2.5 md:px-3 md:py-2 text-sm rounded-full md:rounded-lg border md:border-0 transition-all <?= $tab === 'santri' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 font-semibold shadow-sm' : 'bg-white md:bg-transparent border-gray-200 text-gray-600 hover:bg-gray-100' ?>">
                    <svg class="w-4 h-4 <?= $tab === 'santri' ? 'text-indigo-500' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Daftar Santri
                </a>

                <a href="?id=<?= $kelas['id'] ?>&tab=jadwal" 
                   <?= $tab === 'jadwal' ? 'id="active-tab"' : '' ?>
                   class="whitespace-nowrap flex items-center gap-2 px-4 py-2.5 md:px-3 md:py-2 text-sm rounded-full md:rounded-lg border md:border-0 transition-all <?= $tab === 'jadwal' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 font-semibold shadow-sm' : 'bg-white md:bg-transparent border-gray-200 text-gray-600 hover:bg-gray-100' ?>">
                    <svg class="w-4 h-4 <?= $tab === 'jadwal' ? 'text-indigo-500' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Jadwal Pelajaran
                </a>

                <a href="?id=<?= $kelas['id'] ?>&tab=nilai" 
                   <?= ($tab === 'nilai' || $tab === 'view_nilai') ? 'id="active-tab"' : '' ?>
                   class="whitespace-nowrap flex items-center gap-2 px-4 py-2.5 md:px-3 md:py-2 text-sm rounded-full md:rounded-lg border md:border-0 transition-all <?= ($tab === 'nilai' || $tab === 'view_nilai') ? 'bg-indigo-50 border-indigo-200 text-indigo-700 font-semibold shadow-sm' : 'bg-white md:bg-transparent border-gray-200 text-gray-600 hover:bg-gray-100' ?>">
                    <svg class="w-4 h-4 <?= ($tab === 'nilai' || $tab === 'view_nilai') ? 'text-indigo-500' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Nilai Ujian
                </a>

                <a href="?id=<?= $kelas['id'] ?>&tab=raport" 
                   <?= ($tab === 'raport' || $tab === 'raport_detail' || $tab === 'leger' || $tab === 'raport_arab') ? 'id="active-tab"' : '' ?>
                   class="whitespace-nowrap flex items-center gap-2 px-4 py-2.5 md:px-3 md:py-2 text-sm rounded-full md:rounded-lg border md:border-0 transition-all <?= ($tab === 'raport' || $tab === 'raport_detail' || $tab === 'leger' || $tab === 'raport_arab') ? 'bg-indigo-50 border-indigo-200 text-indigo-700 font-semibold shadow-sm' : 'bg-white md:bg-transparent border-gray-200 text-gray-600 hover:bg-gray-100' ?>">
                    <svg class="w-4 h-4 <?= ($tab === 'raport' || $tab === 'raport_detail' || $tab === 'leger' || $tab === 'raport_arab') ? 'text-indigo-500' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Raport Santri
                </a>

                <a href="?id=<?= $kelas['id'] ?>&tab=perilaku" 
                   <?= $tab === 'perilaku' ? 'id="active-tab"' : '' ?>
                   class="whitespace-nowrap flex items-center gap-2 px-4 py-2.5 md:px-3 md:py-2 text-sm rounded-full md:rounded-lg border md:border-0 transition-all <?= $tab === 'perilaku' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 font-semibold shadow-sm' : 'bg-white md:bg-transparent border-gray-200 text-gray-600 hover:bg-gray-100' ?>">
                    <svg class="w-4 h-4 <?= $tab === 'perilaku' ? 'text-indigo-500' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Nilai Perilaku
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            
            <?php if ($tab === 'overview'): ?>
                <!-- Overview Tab -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 p-6 rounded-2xl border border-indigo-100 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-sm shadow-indigo-200">
                                <i class="ri-user-star-line text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-widest">Wali Kelas</p>
                                <p class="text-base font-bold text-gray-900"><?= htmlspecialchars($kelas['wali_kelas'] ?? 'Belum Ditentukan') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-6 rounded-2xl border border-emerald-100 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-sm shadow-emerald-200">
                                <i class="ri-map-pin-line text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest">Lokasi Kelas</p>
                                <p class="text-base font-bold text-gray-900"><?= htmlspecialchars($kelas['location'] ?: 'Belum Ditentukan') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Informasi Tambahan
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-8">
                        <div>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mb-1">Kelompok Kelas</p>
                            <p class="text-sm font-semibold text-gray-700"><?= $kelas['gender'] === 'Pa' ? 'Putra' : ($kelas['gender'] === 'Pi' ? 'Putri' : 'Campuran') ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mb-1">Tingkat Pendidikan</p>
                            <p class="text-sm font-semibold text-gray-700">Kelas <?= htmlspecialchars($kelas['tingkat']) ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mb-1">Identitas Kelas</p>
                            <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($kelas['abjad']) ?></p>
                        </div>
                    </div>
                </div>

            <?php elseif ($tab === 'santri'): ?>
                <!-- Santri Tab -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Daftar Santri Terdaftar
                        </h3>
                        <span class="self-start sm:self-center px-2.5 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-bold uppercase tracking-wider"><?= count($students) ?> Santri</span>
                    </div>
                    
                    <!-- Desktop View Table -->
                    <div class="overflow-x-auto hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">No</th>
                                    <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">NIS</th>
                                    <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">Nama Lengkap</th>
                                    <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 text-[13px]">
                                <?php foreach ($students as $index => $s): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3.5 text-gray-400"><?= $index + 1 ?></td>
                                    <td class="px-6 py-3.5 font-mono text-gray-600"><?= htmlspecialchars($s['nis']) ?></td>
                                    <td class="px-6 py-3.5 font-bold text-gray-900">
                                        <?php if (auth_get_role() === 'admin'): ?>
                                            <a href="<?= url('/students/edit?id=' . $s['id']) ?>" class="hover:text-indigo-600 transition-colors"><?= htmlspecialchars($s['nama']) ?></a>
                                        <?php else: ?>
                                            <?= htmlspecialchars($s['nama']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 text-emerald-700 uppercase">Aktif</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($students)): ?>
                                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada santri terdaftar di kelas ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile List View -->
                    <div class="grid grid-cols-1 divide-y divide-gray-100 md:hidden bg-white">
                        <?php foreach ($students as $index => $s): ?>
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50/30 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 font-bold text-xs flex items-center justify-center">
                                    <?= $index + 1 ?>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">
                                        <?php if (auth_get_role() === 'admin'): ?>
                                            <a href="<?= url('/students/edit?id=' . $s['id']) ?>" class="hover:text-indigo-600 transition-colors"><?= htmlspecialchars($s['nama']) ?></a>
                                        <?php else: ?>
                                            <?= htmlspecialchars($s['nama']) ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-500 font-mono mt-0.5">NIS: <?= htmlspecialchars($s['nis']) ?></p>
                                </div>
                            </div>
                            <div>
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 text-emerald-700 uppercase">Aktif</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($students)): ?>
                        <div class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada santri terdaftar di kelas ini.</div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($tab === 'jadwal'): ?>
                <!-- Jadwal Tab -->
                <div class="grid grid-cols-1 gap-6">
                    <?php 
                    $daysSorted = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    foreach ($daysSorted as $day): 
                        if (empty($schedule[$day])) continue; // Optional: show empty days or hide them
                    ?>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-3 bg-gray-50 border-b border-gray-100">
                            <h4 class="text-[10px] font-bold text-gray-900 uppercase tracking-widest"><?= $day ?></h4>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <?php ksort($schedule[$day]); foreach ($schedule[$day] as $hour => $slot): ?>
                            <div class="px-6 py-4 flex items-center gap-6 hover:bg-gray-50/30 transition-colors">
                                <div class="w-10 text-center">
                                    <div class="text-[10px] text-gray-300 font-bold uppercase leading-none mb-1">Jam</div>
                                    <div class="text-lg font-black text-indigo-600 leading-none"><?= $hour ?></div>
                                </div>
                                <div class="w-px h-8 bg-gray-100"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-900 mb-0.5"><?= htmlspecialchars($slot['subject_name']) ?></p>
                                    <div class="flex items-center gap-1.5 text-[11px] text-gray-500 italic">
                                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <?= htmlspecialchars($slot['teacher_name'] ?: 'Tanpa Pengajar') ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($schedule)): ?>
                    <div class="bg-white px-6 py-16 rounded-2xl border border-gray-200 shadow-sm text-center">
                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 mb-1">Belum Ada Jadwal</h4>
                        <p class="text-xs text-gray-400">Jadwal pelajaran belum diatur/diinput untuk kelas ini pada tahun ajaran ini.</p>
                    </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($tab === 'nilai'): ?>
                <!-- Nilai Ujian Tab -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Rekap Nilai Ujian Kelas
                        </h3>
                        <div>
                            <form action="" method="GET" class="flex items-center">
                                <input type="hidden" name="id" value="<?= $kelas['id'] ?>">
                                <input type="hidden" name="tab" value="nilai">
                                <select name="session_id" onchange="this.form.submit()" class="text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-1.5 focus:border-indigo-500 focus:ring-0 shadow-sm cursor-pointer hover:border-gray-300 transition-colors w-full sm:w-auto">
                                    <option value="">Semua Sesi Ujian</option>
                                    <?php 
                                    $typeMap = [
                                        'UUPT' => 'Ulangan Umum Pertengahan Tahun',
                                        'UPT' => 'Ujian Pertengahan Tahun',
                                        'UUAT' => 'Ulangan Umum Akhir Tahun',
                                        'UAT' => 'Ujian Akhir Tahun'
                                    ];
                                    foreach ($sessions as $session): 
                                        $sessionName = $typeMap[$session['type']] ?? $session['type'];
                                    ?>
                                        <option value="<?= $session['id'] ?>" <?= $selected_session_id == $session['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($session['type'] . ' (' . $sessionName . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Desktop View Table -->
                    <div class="overflow-x-auto hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">Pelajaran</th>
                                    <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">Pengajar</th>
                                    <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">Sesi Ujian</th>
                                    <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest">Rata-rata</th>
                                    <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                                    <th class="px-6 py-3 text-right text-[9px] font-bold text-gray-400 uppercase tracking-widest">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 text-[13px]">
                                <?php foreach ($exams as $exam): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3.5 font-bold text-gray-900">
                                        <?= htmlspecialchars($exam['mapel_nama'] ?? 'Unknown') ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-gray-600">
                                        <?= htmlspecialchars($exam['pengajar_nama'] ?? 'Tanpa Pengajar') ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-gray-600">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-800">
                                            <?= htmlspecialchars($exam['exam_type'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-center font-bold text-indigo-600">
                                        <?= ($exam['status'] === 'selesai' && $exam['average_score'] !== null) ? number_format($exam['average_score'], 2) : '-' ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        <?php if ($exam['status'] === 'selesai'): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-green-50 text-green-700 uppercase">Selesai</span>
                                        <?php elseif ($exam['status'] === 'proses'): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-blue-50 text-blue-700 uppercase">Proses</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-gray-50 text-gray-600 uppercase">Belum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-right font-medium">
                                        <?php if ($exam['status'] === 'selesai'): ?>
                                            <a href="<?= url('/classes/detail?id=' . $kelas['id'] . '&tab=view_nilai&exam_id=' . $exam['id']) ?>" class="text-indigo-600 hover:text-indigo-900 text-xs flex items-center justify-end gap-1">
                                                <i class="ri-eye-line"></i> Lihat Nilai
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs flex items-center justify-end gap-1 cursor-not-allowed" title="Nilai belum diverifikasi">
                                                <i class="ri-eye-off-line"></i> Lihat Nilai
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($exams)): ?>
                                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada data nilai ujian untuk kelas ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile List View -->
                    <div class="grid grid-cols-1 divide-y divide-gray-100 md:hidden bg-white">
                        <?php foreach ($exams as $exam): ?>
                        <div class="p-4 flex flex-col gap-3.5 hover:bg-gray-50/30 transition-colors">
                            <div class="flex justify-between items-start gap-4">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-gray-900 text-sm truncate">
                                        <?= htmlspecialchars($exam['mapel_nama'] ?? 'Unknown') ?>
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                        <i class="ri-user-star-line text-[11px] text-gray-400"></i>
                                        <span class="truncate"><?= htmlspecialchars($exam['pengajar_nama'] ?? 'Tanpa Pengajar') ?></span>
                                    </p>
                                </div>
                                <span class="shrink-0 px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase">
                                    <?= htmlspecialchars($exam['exam_type'] ?? '-') ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between bg-gray-50/70 p-2.5 rounded-xl border border-gray-100 text-center">
                                <div>
                                    <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Rata-rata</div>
                                    <div class="text-sm font-black text-indigo-600">
                                        <?= ($exam['status'] === 'selesai' && $exam['average_score'] !== null) ? number_format($exam['average_score'], 2) : '-' ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Status</div>
                                    <div>
                                        <?php if ($exam['status'] === 'selesai'): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-green-50 text-green-700 uppercase">Selesai</span>
                                        <?php elseif ($exam['status'] === 'proses'): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-blue-50 text-blue-700 uppercase">Proses</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-gray-50 text-gray-600 uppercase">Belum</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Aksi</div>
                                    <div>
                                        <?php if ($exam['status'] === 'selesai'): ?>
                                            <a href="<?= url('/classes/detail?id=' . $kelas['id'] . '&tab=view_nilai&exam_id=' . $exam['id']) ?>" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs flex items-center justify-end gap-0.5">
                                                Detail <i class="ri-arrow-right-s-line text-sm"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs flex items-center justify-end gap-0.5 cursor-not-allowed" title="Nilai belum diverifikasi">
                                                Locked <i class="ri-lock-line text-[11px]"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($exams)): ?>
                        <div class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada data nilai ujian untuk kelas ini.</div>
                        <?php endif; ?>
                    </div>
                </div>


            <?php elseif ($tab === 'view_nilai'): ?>
                <!-- Detail Nilai Ujian Tab -->
                <?php include __DIR__ . '/view_nilai.php'; ?>
                
            <?php elseif ($tab === 'raport'): ?>
                <!-- Raport Santri (Leger) Tab -->
                <?php include __DIR__ . '/view_raport.php'; ?>
                
            <?php elseif ($tab === 'raport_detail'): ?>
                <!-- Raport Detail Santri Tab -->
                <?php include __DIR__ . '/view_raport_detail.php'; ?>
                
            <?php elseif ($tab === 'perilaku'): ?>
                <!-- Nilai Perilaku Tab -->
                <?php include __DIR__ . '/view_perilaku.php'; ?>

            <?php elseif ($tab === 'raport_arab'): ?>
                <!-- Cetak Rapor Arab Tab -->
                <?php include __DIR__ . '/view_raport_arab.php'; ?>

            <?php elseif ($tab === 'leger'): ?>
                <!-- Leger Tab -->
                <?php include __DIR__ . '/view_leger.php'; ?>

            <?php endif; ?>

        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const activeTab = document.getElementById("active-tab");
        if (activeTab) {
            activeTab.scrollIntoView({ behavior: "instant", block: "nearest", inline: "center" });
        }
    });
</script>
