<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header Back Link -->
    <div class="mb-6 flex items-center gap-4">
        <a href="<?= url('/teachers') ?>" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Akademik Guru</h1>
            <p class="text-sm text-gray-500">Pantau daftar mata pelajaran dan kelas yang diampu guru lintas tahun ajaran.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Sidebar: Profil Ringkas -->
        <div class="space-y-6 lg:col-span-1">
            
            <!-- Profil Ringkas -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-xl font-bold shadow-md mb-4">
                        <?= mb_strtoupper(mb_substr($teacher['nama_raw'] ?? $teacher['nama'], 0, 2)) ?>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($teacher['nama']) ?></h2>
                    <p class="text-xs font-mono text-gray-500 mt-1"><?= htmlspecialchars($teacher['nip'] ? 'NIP: ' . $teacher['nip'] : 'NIP: -') ?></p>
                    
                    <div class="mt-4 flex items-center gap-2">
                        <?php if ((int)($teacher['is_active'] ?? 1) === 1): ?>
                            <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-bold rounded-full bg-green-100 text-green-800 uppercase font-sans">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                Aktif
                            </span>
                        <?php else: ?>
                            <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-bold rounded-full bg-red-100 text-red-800 uppercase font-sans">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                Inaktif
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="border-t border-gray-100 mt-6 pt-6 space-y-3.5 text-xs font-medium text-gray-600">
                    <div class="flex justify-between">
                        <span>No. HP:</span>
                        <span class="font-bold text-gray-900 font-mono"><?= htmlspecialchars($teacher['hp'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Jenis Kelamin:</span>
                        <span class="font-bold text-gray-900"><?= htmlspecialchars($teacher['gender'] ?: '-') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pendidikan:</span>
                        <span class="font-bold text-gray-900"><?= htmlspecialchars($teacher['education'] ?: '-') ?> <?= $teacher['year_graduated'] ? '(' . $teacher['year_graduated'] . ')' : '' ?></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span>Alamat:</span>
                        <span class="font-bold text-gray-950 block mt-0.5 leading-relaxed"><?= htmlspecialchars($teacher['address'] ?: '-') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Column: Timeline Riwayat Mengajar -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <i class="ri-history-line text-indigo-600"></i> Garis Waktu Riwayat Mengajar
                </h3>

                <?php if (empty($groupedHistory)): ?>
                    <div class="text-center py-12 text-gray-400 italic">
                        Belum ada riwayat mengajar (jadwal pelajaran) untuk guru ini di tahun ajaran manapun.
                    </div>
                <?php else: ?>
                    <div class="relative pl-8 border-l-2 border-slate-200 space-y-8">
                        <?php foreach ($groupedHistory as $yearName => $lessons): ?>
                        <div class="relative">
                            
                            <!-- Timeline Dot Icon -->
                            <div class="absolute -left-[41px] top-1 w-5 h-5 rounded-full border-4 border-white shadow-sm bg-indigo-500"></div>

                             <div class="bg-gray-50 border border-gray-150 rounded-xl p-5 shadow-sm">
                                 <span class="px-2.5 py-1 text-[10px] font-black bg-indigo-100 text-indigo-850 rounded-full uppercase tracking-wider">
                                     TA: <?= htmlspecialchars($yearName) ?>
                                 </span>
                                 
                                 <ul class="mt-4 space-y-2 list-disc list-inside text-sm text-gray-700 pl-1">
                                     <?php foreach ($lessons as $lesson): ?>
                                     <li>
                                         <span class="font-bold text-gray-900"><?= htmlspecialchars($lesson['subject_name']) ?></span>
                                         <span class="text-gray-400">kelas</span>
                                         <span class="font-semibold text-indigo-650"><?= htmlspecialchars($lesson['tingkat']) ?>-<?= htmlspecialchars($lesson['abjad']) ?></span>
                                     </li>
                                     <?php endforeach; ?>
                                 </ul>
                             </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
