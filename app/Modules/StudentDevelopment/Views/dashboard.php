<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">

    <!-- Top Hero Section -->
    <div class="bg-gradient-to-r from-indigo-800 to-indigo-950 text-white rounded-3xl p-6 md:p-8 shadow-xl mb-8 relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-12 -translate-y-12">
            <i class="ri-heart-pulse-line text-[240px]"></i>
        </div>
        <div class="relative z-10 max-w-2xl">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Pemantauan Perkembangan Santri</h1>
            <p class="text-indigo-200 mt-2 text-sm leading-relaxed">
                Mencatat dan mengapresiasi setiap perkembangan santri. Sistem dirancang untuk membantu pendidik memahami, mendampingi, dan mendeteksi pola perkembangan santri secara menyeluruh tanpa konsep hukuman formal.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= url('/student-development/observe') ?>" class="bg-white hover:bg-slate-100 text-indigo-950 px-5 py-2.5 rounded-xl text-sm font-semibold shadow-md transition-all flex items-center gap-2">
                    <i class="ri-add-line text-lg font-bold"></i> Catat Observasi Baru
                </a>
                <?php if ($role === 'admin'): ?>
                    <a href="<?= url('/student-development/categories') ?>" class="bg-indigo-700 hover:bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                        <i class="ri-settings-4-line text-lg"></i> Pengaturan Kategori
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MAIN INTERFACE ACCORDING TO ROLE -->
    <?php if ($role === 'admin'): ?>
        <!-- ==================== ADMIN & BK DASHBOARD ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Filter & List Observasi (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Advanced Filters -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="ri-filter-2-line text-indigo-600"></i> Saring Data Observasi
                    </h2>
                    <form method="GET" action="<?= url('/student-development') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Cari Santri / Catatan</label>
                            <div class="relative">
                                <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" placeholder="Ketik nama santri atau potongan catatan..." class="block w-full border-slate-200 rounded-xl text-sm p-3 pl-10 border focus:border-indigo-500 focus:ring-indigo-500">
                                <i class="ri-search-line absolute left-3.5 top-3.5 text-slate-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Kelas</label>
                            <select id="filter-kelas" name="kelas_id" class="block w-full rounded-xl text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach ($kelas_list as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= ($filters['kelas_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                                        Kelas <?= htmlspecialchars($k['tingkat']) ?>-<?= htmlspecialchars($k['abjad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tipe</label>
                            <select name="type" class="block w-full border-slate-200 rounded-xl text-sm p-3 border focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Tipe --</option>
                                <option value="Positif" <?= ($filters['type'] ?? '') === 'Positif' ? 'selected' : '' ?>>Positif</option>
                                <option value="Perhatian" <?= ($filters['type'] ?? '') === 'Perhatian' ? 'selected' : '' ?>>Perhatian</option>
                                <option value="Informasi" <?= ($filters['type'] ?? '') === 'Informasi' ? 'selected' : '' ?>>Informasi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                            <select id="filter-kategori" name="category_id" class="block w-full rounded-xl text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Kategori --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex gap-2 items-end">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-xl text-sm font-semibold transition-all">
                                Cari
                            </button>
                            <a href="<?= url('/student-development') ?>" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-700 p-3 rounded-xl text-sm font-semibold text-center transition-all">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Recent Observations List -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-slate-800">Catatan Observasi Santri</h2>
                    </div>

                    <?php if (empty($observations)): ?>
                        <div class="p-8 text-center text-slate-400">
                            <i class="ri-chat-delete-line text-4xl mb-2 block"></i>
                            Tidak ada catatan observasi yang cocok dengan filter pencarian.
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100">
                            <?php foreach ($observations as $obs): 
                                $isCollective = empty($obs['student_id']);
                                $bgClass = $isCollective ? 'bg-indigo-50/20 hover:bg-indigo-50/40' : 'hover:bg-slate-50/50';
                            ?>
                                <div class="p-4 <?= $bgClass ?> transition-all flex flex-col sm:flex-row gap-3 justify-between sm:items-center">
                                    <div class="space-y-1">
                                        <!-- Header: Student Name + NIS + Class -->
                                        <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                                            <?php if (empty($obs['student_id'])): ?>
                                                <span class="text-indigo-650 text-sm font-black flex items-center gap-1">
                                                    <i class="ri-team-line text-indigo-500"></i> Kolektif Kelas
                                                </span>
                                            <?php else: ?>
                                                <a href="<?= url('/student-development/student?id=' . $obs['student_id']) ?>" class="text-slate-800 hover:text-indigo-600 transition-all text-sm font-black">
                                                    <?= htmlspecialchars($obs['student_name'] ?? '') ?>
                                                </a>
                                                <span class="text-slate-400 font-normal">(<?= htmlspecialchars($obs['student_nis'] ?? '') ?>)</span>
                                            <?php endif; ?>
                                            
                                            <?php if ($obs['tingkat']): ?>
                                                <span class="text-slate-400 font-semibold">• Kelas <?= htmlspecialchars($obs['tingkat']) ?>-<?= htmlspecialchars($obs['abjad']) ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Content Description -->
                                        <p class="text-slate-600 text-xs leading-relaxed"><?= htmlspecialchars($obs['content']) ?></p>

                                        <!-- Footer info: Badges & Creator/Date Info -->
                                        <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-400">
                                            <!-- Target Type Badge -->
                                            <?php if (empty($obs['student_id'])): ?>
                                                <span class="text-indigo-700 font-bold bg-indigo-50 px-1.5 py-0.2 rounded border border-indigo-200">Kolektif</span>
                                            <?php else: ?>
                                                <span class="text-slate-600 font-bold bg-slate-50 px-1.5 py-0.2 rounded border border-slate-200">Personal</span>
                                            <?php endif; ?>

                                            <!-- Tipe Badge -->
                                            <?php if ($obs['type'] === 'Positif'): ?>
                                                <span class="text-green-600 font-bold bg-green-50 px-1.5 py-0.2 rounded border border-green-200">Positif</span>
                                            <?php elseif ($obs['type'] === 'Perhatian'): ?>
                                                <span class="text-amber-600 font-bold bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">Perhatian</span>
                                            <?php else: ?>
                                                <span class="text-blue-600 font-bold bg-blue-50 px-1.5 py-0.2 rounded border border-blue-200">Info</span>
                                            <?php endif; ?>

                                            <!-- Kategori -->
                                            <span class="px-1.5 py-0.2 rounded border font-medium text-[10px]" style="background-color: <?= ($obs['category_color'] ?? '#64748b') ?>15; color: <?= $obs['category_color'] ?? '#64748b' ?>; border-color: <?= $obs['category_color'] ?? '#64748b' ?>30;">
                                                <?= htmlspecialchars($obs['category_name']) ?>
                                            </span>
                                            
                                            <span>• Oleh: <span class="font-semibold text-slate-500"><?= htmlspecialchars($obs['teacher_name']) ?></span></span>
                                            <span>• <i class="ri-calendar-line text-[9px]"></i> <?= date('d M Y', strtotime($obs['observation_date'])) ?></span>

                                            <!-- Context Banner inline if present -->
                                            <?php if (!empty($obs['context'])): ?>
                                                <span class="text-slate-500 italic bg-slate-50 border-l border-slate-300 pl-1">Konteks: <?= htmlspecialchars($obs['context']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Right side actions -->
                                    <div class="flex items-center gap-2 shrink-0">
                                        <?php if ($obs['response_count'] > 0): ?>
                                            <span class="text-[10px] text-green-600 bg-green-50 border border-green-200 font-bold px-2 py-0.5 rounded-lg flex items-center gap-0.5" title="<?= $obs['response_count'] ?> Respons">
                                                <i class="ri-chat-1-line"></i> <?= $obs['response_count'] ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($obs['student_id'])): ?>
                                            <a href="<?= url('/student-development/student?id=' . $obs['student_id']) ?>" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-[10px] font-bold px-3 py-1.5 rounded-lg border border-indigo-100 transition-all flex items-center gap-0.5">
                                                Detail <i class="ri-arrow-right-s-line"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="p-6 border-t border-slate-100 flex justify-center gap-2">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="<?= url('/student-development?page=' . $i . '&' . http_build_query(array_filter($filters))) ?>" class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-semibold transition-all <?= $page == $i ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-100' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kelas Statistics & Admin Actions (1 Col) -->
            <div class="space-y-6">
                <!-- Class Stats Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <div class="mb-4">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="ri-home-4-line text-indigo-600"></i> Statistik per Kelas
                        </h2>
                        <p class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                            <i class="ri-mouse-line"></i> Gulir (scroll) ke bawah untuk melihat kelas lain
                        </p>
                    </div>
                    <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto pr-1">
                        <?php foreach ($classes_stats as $c): ?>
                            <div class="py-4 space-y-2">
                                <div class="flex justify-between items-center gap-2">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-bold text-slate-800">Kelas <?= htmlspecialchars($c['tingkat']) ?>-<?= htmlspecialchars($c['abjad']) ?></h4>
                                        <p class="text-[11px] text-slate-400 truncate" title="Wali: <?= htmlspecialchars($c['wali_kelas'] ?? 'Belum Ditunjuk') ?>">Wali: <?= htmlspecialchars($c['wali_kelas'] ?? 'Belum Ditunjuk') ?></p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100 whitespace-nowrap"><?= $c['total_observations'] ?> catatan</span>
                                        <a href="<?= url('/student-development?kelas_id=' . $c['id']) ?>" class="text-indigo-600 hover:bg-indigo-50 p-1.5 rounded-lg transition-all" title="Lihat Catatan Kelas Ini">
                                            <i class="ri-arrow-right-s-line text-xl font-bold"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php if ($c['total_observations'] > 0): 
                                    $pPct = ($c['total_positif'] / $c['total_observations']) * 100;
                                    $aPct = ($c['total_perhatian'] / $c['total_observations']) * 100;
                                    $iPct = ($c['total_informasi'] / $c['total_observations']) * 100;
                                ?>
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden flex" title="Positif: <?= $c['total_positif'] ?>, Perhatian: <?= $c['total_perhatian'] ?>, Informasi: <?= $c['total_informasi'] ?>">
                                        <?php if ($c['total_positif'] > 0): ?>
                                            <div class="bg-green-500 h-full transition-all" style="width: <?= $pPct ?>%"></div>
                                        <?php endif; ?>
                                        <?php if ($c['total_perhatian'] > 0): ?>
                                            <div class="bg-amber-500 h-full transition-all" style="width: <?= $aPct ?>%"></div>
                                        <?php endif; ?>
                                        <?php if ($c['total_informasi'] > 0): ?>
                                            <div class="bg-blue-500 h-full transition-all" style="width: <?= $iPct ?>%"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex gap-3 text-[10px] font-bold text-slate-400">
                                        <?php if ($c['total_positif'] > 0): ?>
                                            <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> <?= $c['total_positif'] ?> Positif</span>
                                        <?php endif; ?>
                                        <?php if ($c['total_perhatian'] > 0): ?>
                                            <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> <?= $c['total_perhatian'] ?> Perhatian</span>
                                        <?php endif; ?>
                                        <?php if ($c['total_informasi'] > 0): ?>
                                            <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> <?= $c['total_informasi'] ?> Info</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ==================== GURU & WALI KELAS DASHBOARD ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: My Observations -->
            <div class="lg:col-span-2 space-y-8">
                <!-- My Observations List (Guru) -->
                <div class="space-y-4">
                    <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2">
                        <i class="ri-chat-history-line text-indigo-600"></i> Observasi yang Saya Catat
                    </h2>
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <?php if (empty($my_observations)): ?>
                            <div class="p-12 text-center text-slate-400">
                                <i class="ri-edit-circle-line text-5xl mb-3 block text-slate-300"></i>
                                Anda belum pernah membuat catatan observasi perkembangan santri.<br>
                                <a href="<?= url('/student-development/observe') ?>" class="text-indigo-600 font-semibold hover:underline mt-2 inline-block">Klik di sini untuk membuat catatan pertama.</a>
                            </div>
                        <?php else: ?>
                            <div class="divide-y divide-slate-100">
                                <?php foreach ($my_observations as $obs): 
                                    $isCollective = empty($obs['student_id']);
                                    $bgClass = $isCollective ? 'bg-indigo-50/20 hover:bg-indigo-50/40' : 'hover:bg-slate-50/50';
                                ?>
                                    <div class="p-6 <?= $bgClass ?> transition-all">
                                        <div class="flex flex-wrap items-center gap-2 text-xs mb-2">
                                            <!-- Target Type Badge -->
                                            <?php if (empty($obs['student_id'])): ?>
                                                <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-semibold border border-indigo-200">Kolektif</span>
                                            <?php else: ?>
                                                <span class="bg-slate-50 text-slate-700 px-2 py-0.5 rounded-full font-semibold border border-slate-200">Personal</span>
                                            <?php endif; ?>

                                            <?php if ($obs['type'] === 'Positif'): ?>
                                                <span class="bg-green-50 text-green-700 px-2 py-0.5 rounded-full font-semibold border border-green-200">Positif</span>
                                            <?php elseif ($obs['type'] === 'Perhatian'): ?>
                                                <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full font-semibold border border-amber-200">Perhatian</span>
                                            <?php else: ?>
                                                <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full font-semibold border border-blue-200">Informasi</span>
                                            <?php endif; ?>
                                            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full font-medium border border-slate-200"><?= htmlspecialchars($obs['category_name']) ?></span>
                                            <span class="text-slate-400"><i class="ri-calendar-line"></i> <?= date('d M Y', strtotime($obs['observation_date'])) ?></span>
                                        </div>
                                        <h4 class="font-bold text-slate-800 text-base">
                                            <?php if (empty($obs['student_id'])): ?>
                                                <span class="text-indigo-650 flex items-center gap-1">
                                                    <i class="ri-team-line text-indigo-500"></i> Kolektif Kelas <?= htmlspecialchars($obs['tingkat']) ?>-<?= htmlspecialchars($obs['abjad']) ?>
                                                </span>
                                            <?php else: ?>
                                                <a href="<?= url('/student-development/student?id=' . $obs['student_id']) ?>" class="hover:text-indigo-600 transition-all">
                                                    <?= htmlspecialchars($obs['student_name'] ?? '') ?>
                                                </a>
                                            <?php endif; ?>
                                        </h4>
                                        <p class="text-slate-600 text-sm mt-1 whitespace-pre-line"><?= htmlspecialchars($obs['content']) ?></p>
                                        
                                        <?php if (!empty($obs['context'])): ?>
                                            <div class="mt-2 bg-slate-50 border-l-4 border-slate-300 p-2.5 rounded-r-lg text-xs text-slate-500 italic">
                                                Konteks: <?= htmlspecialchars($obs['context']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Pagination -->
                            <?php if ($my_total_pages > 1): ?>
                                <div class="p-6 border-t border-slate-100 flex justify-center gap-2">
                                    <?php for ($i = 1; $i <= $my_total_pages; $i++): ?>
                                        <a href="<?= url('/student-development?page=' . $i) ?>" class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-semibold transition-all <?= $my_page == $i ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-100' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Info & Quick Links -->
            <div class="space-y-6">
                <div class="bg-indigo-900 text-indigo-100 rounded-3xl p-6 shadow-xl relative overflow-hidden">
                    <h3 class="font-black text-lg text-white mb-2 flex items-center gap-2">
                        <i class="ri-lightbulb-line text-amber-300"></i> Etika & Poin Pendidik
                    </h3>
                    <ul class="space-y-3.5 text-xs text-indigo-200 list-disc pl-4 leading-relaxed">
                        <li><strong>Catat, Jangan Menghakimi:</strong> Tulis fakta yang terlihat ("Budi tidur di kelas"), hindari label kepribadian santri ("Budi anak malas").</li>
                        <li><strong>Konteks Sangat Berharga:</strong> Jika Anda tahu penyebab perilaku (misal: santri menjadi ketua kepanitiaan), masukkan ke kolom Konteks.</li>
                        <li><strong>Apresiasi Hal Positif:</strong> Peningkatan akademik sekecil apapun, sikap membantu teman, dan bakat kepemimpinan adalah hal krusial untuk dicatat.</li>
                        <li><strong>Bukan Tiket / Komplain:</strong> Observasi tidak memiliki status "selesai/closed". Ini adalah memori perjalanan santri.</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof TomSelect !== "undefined") {
            const elKelas = document.getElementById('filter-kelas');
            if (elKelas) {
                new TomSelect(elKelas, {
                    create: false,
                    plugins: ['no_backspace_delete']
                });
            }
            const elKategori = document.getElementById('filter-kategori');
            if (elKategori) {
                new TomSelect(elKategori, {
                    create: false,
                    plugins: ['no_backspace_delete']
                });
            }
        }
    });
</script>
