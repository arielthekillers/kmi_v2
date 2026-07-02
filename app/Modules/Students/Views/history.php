<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header Back Link -->
    <div class="mb-6 flex items-center gap-4">
        <a href="<?= url('/students') ?>" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Akademik & Transaksi Santri</h1>
            <p class="text-sm text-gray-500">Pantau pergerakan kelas, kelulusan, mutasi, dan re-enrollment santri.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Sidebar: Profil Ringkas & Aksi Transisi -->
        <div class="space-y-6 lg:col-span-1">
            
            <!-- Profil Ringkas -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-xl font-bold shadow-md mb-4">
                        <?= mb_strtoupper(mb_substr($student['nama'], 0, 2)) ?>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($student['nama']) ?></h2>
                    <p class="text-sm font-mono text-gray-500 mt-1">NIS: <?= htmlspecialchars($student['nis'] ?? '-') ?></p>
                    
                    <div class="mt-4 flex items-center gap-2">
                        <?php 
                        $isActive = ($student['enrollment_status'] === 'Active');
                        if ($isActive): 
                        ?>
                            <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-bold rounded-full bg-green-100 text-green-800 uppercase font-sans">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                Aktif
                            </span>
                        <?php else: 
                            $statusLabel = 'Belum Terdaftar';
                            if ($student['enrollment_status'] === 'Out') $statusLabel = 'Keluar / Pindah Sekolah';
                            elseif ($student['enrollment_status'] === 'Moved') $statusLabel = 'Pindah Kelas';
                            elseif ($student['enrollment_status'] === 'Graduated') $statusLabel = 'Lulus';
                        ?>
                            <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-bold rounded-full bg-red-100 text-red-800 uppercase font-sans">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Inaktif (<?= htmlspecialchars($statusLabel) ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="border-t border-gray-100 mt-6 pt-6 space-y-3.5 text-xs font-medium text-gray-600">
                    <div class="flex justify-between">
                        <span>Jenis Kelamin:</span>
                        <span class="font-bold text-gray-900"><?= $student['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Wali:</span>
                        <span class="font-bold text-gray-900"><?= htmlspecialchars($student['nama_wali'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tahun Masuk:</span>
                        <span class="font-bold text-gray-900 font-mono"><?= htmlspecialchars($student['tahun_masuk'] ?: '-') ?></span>
                    </div>
                </div>
            </div>

            <!-- Panel Aksi Transisi -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Aksi Transisi Akademik</h3>
                
                <?php if ($isActive): 
                    $isKelas6 = false;
                    $activeYearId = null;
                    $activeKelasId = null;
                    foreach ($history as $h) {
                        if ($h['status'] === 'Active') {
                            if ((int)$h['tingkat'] === 6) {
                                $isKelas6 = true;
                            }
                            $activeYearId = $h['academic_year_id'];
                            $activeKelasId = $h['kelas_id'];
                            break;
                        }
                    }
                ?>
                    <!-- If Active: Can Graduate (only if Kelas 6), Leave, or Transfer -->
                    <div class="space-y-3">
                        <p class="text-xs text-gray-500 mb-2 leading-relaxed">Pilih salah satu aksi di bawah untuk memperbarui status santri di tahun ajaran aktif ini.</p>
                        
                        <?php if ($isKelas6): ?>
                        <form action="<?= url('/students/history/update-status') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin meluluskan santri ini?')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                            <button type="submit" name="status" value="Graduated" 
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 rounded-xl text-sm font-bold transition-all shadow-sm active:scale-95">
                                <i class="ri-graduation-cap-line text-lg"></i> Nyatakan Lulus
                            </button>
                        </form>
                        <?php endif; ?>

                        <form action="<?= url('/students/history/update-status') ?>" method="POST" onsubmit="return confirm('Santri keluar/pindah sekolah akan di-nonaktifkan. Lanjutkan?')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                            <button type="submit" name="status" value="Out" 
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 rounded-xl text-sm font-bold transition-all shadow-sm active:scale-95">
                                <i class="ri-logout-box-r-line text-lg"></i> Keluar / Pindah Sekolah
                            </button>
                        </form>
                        
                        <!-- Perpindahan Kelas (Individu) -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pindah Kelas (Tahun Ini)</label>
                            <form action="<?= url('/students/history/re-enroll') ?>" method="POST" class="flex gap-2">
                                <?= csrf_input() ?>
                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                <input type="hidden" name="academic_year_id" value="<?= htmlspecialchars($activeYearId) ?>">
                                <select name="kelas_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php foreach ($kelas as $k): ?>
                                        <?php if ($k['academic_year_id'] == $activeYearId && $k['id'] != $activeKelasId): ?>
                                        <option value="<?= $k['id'] ?>">
                                            Kelas <?= htmlspecialchars($k['tingkat'] . '-' . $k['abjad']) ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" onclick="return confirm('Pindahkan kelas santri ini?')" class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                    <i class="ri-arrow-right-line"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- If Inactive/Out/Graduated: Can Re-enroll / Masuk Kembali -->
                    <div class="space-y-4">
                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3 text-xs text-indigo-700 leading-relaxed font-semibold">
                            <i class="ri-information-line"></i> Santri saat ini tidak terdaftar/aktif. Anda dapat mendaftarkannya masuk kembali ke kelas.
                        </div>

                        <form action="<?= url('/students/history/re-enroll') ?>" method="POST" class="space-y-3.5">
                            <?= csrf_input() ?>
                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Tahun Ajaran</label>
                                <select name="academic_year_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Pilih Tahun --</option>
                                    <?php foreach ($allYears as $y): ?>
                                        <option value="<?= $y['id'] ?>" <?= $y['is_active'] ? 'selected' : '' ?>>
                                            Tahun <?= htmlspecialchars($y['name']) ?> <?= $y['is_active'] ? '(Aktif)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Kelas</label>
                                <select name="kelas_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php foreach ($kelas as $k): ?>
                                        <option value="<?= $k['id'] ?>">
                                            Kelas <?= htmlspecialchars($k['tingkat'] . '-' . $k['abjad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" 
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl text-sm font-bold shadow-lg hover:shadow-indigo-500/10 transition-all active:scale-95">
                                <i class="ri-user-received-line"></i> Daftarkan Masuk Kembali
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Column: Timeline Riwayat Akademik -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <i class="ri-history-line text-indigo-600"></i> Garis Waktu Riwayat Belajar
                </h3>

                <?php if (empty($history)): ?>
                    <div class="text-center py-12 text-gray-400 italic">
                        Belum ada riwayat penempatan kelas atau status transaksional untuk santri ini.
                    </div>
                <?php else: ?>
                    <div class="relative pl-8 border-l-2 border-slate-200 space-y-8">
                        <?php foreach ($history as $h): ?>
                        <div class="relative">
                            
                            <!-- Timeline Dot Icon -->
                            <div class="absolute -left-[41px] top-1 w-5 h-5 rounded-full border-4 border-white shadow-sm flex items-center justify-center
                                <?php 
                                if ($h['status'] === 'Active') echo 'bg-green-500';
                                elseif ($h['status'] === 'Graduated') echo 'bg-indigo-600';
                                elseif ($h['status'] === 'Out') echo 'bg-red-500';
                                elseif ($h['status'] === 'Moved') echo 'bg-yellow-500';
                                else echo 'bg-slate-400';
                                ?>">
                            </div>

                            <!-- Content Card -->
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700 mb-1.5 uppercase font-mono">
                                        TA: <?= htmlspecialchars($h['year_name']) ?>
                                    </span>
                                    <div class="text-sm font-bold text-slate-800">
                                        Kelas <?= htmlspecialchars($h['tingkat'] . ' - ' . $h['abjad']) ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-1 flex items-center gap-1.5">
                                        <i class="ri-calendar-line"></i>
                                        <?= date('d M Y', strtotime($h['start_date'])) ?> 
                                        <?= $h['end_date'] ? ' — ' . date('d M Y', strtotime($h['end_date'])) : ' (Berjalan)' ?>
                                    </div>
                                </div>
                                <div class="text-left sm:text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider
                                        <?php 
                                        if ($h['status'] === 'Active') echo 'bg-green-50 text-green-700 border border-green-200';
                                        elseif ($h['status'] === 'Graduated') echo 'bg-indigo-50 text-indigo-700 border border-indigo-200';
                                        elseif ($h['status'] === 'Out') echo 'bg-red-50 text-red-700 border border-red-200';
                                        elseif ($h['status'] === 'Moved') echo 'bg-yellow-50 text-yellow-700 border border-yellow-200';
                                        else echo 'bg-slate-50 text-slate-700';
                                        ?>">
                                        <?php 
                                        if ($h['status'] === 'Active') {
                                            echo 'Aktif';
                                        } elseif ($h['status'] === 'Graduated') {
                                            echo 'Lulus';
                                        } elseif ($h['status'] === 'Out') {
                                            echo 'Keluar / Pindah Sekolah';
                                        } elseif ($h['status'] === 'Moved') {
                                            echo 'Pindah Kelas';
                                        } else {
                                            echo htmlspecialchars($h['status']);
                                        }
                                        ?>
                                    </span>
                                    
                                    <form action="<?= url('/students/history/rollback') ?>" method="POST" class="inline-block mt-2 sm:mt-0 sm:ml-2" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat ini? Status santri akan dikembalikan ke riwayat sebelumnya.')">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                        <input type="hidden" name="enrollment_id" value="<?= $h['id'] ?>">
                                        <button type="submit" class="px-2 py-1 text-[10px] font-bold bg-red-50 text-red-600 border border-red-200 rounded hover:bg-red-100 transition-colors" title="Hapus Riwayat & Batalkan Mutasi">
                                            <i class="ri-delete-bin-line"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
