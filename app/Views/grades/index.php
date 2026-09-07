<?php 
renderHeader("Koreksi Ujian"); 
$isAdmin = (auth_get_role() === 'admin');
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <style>
        /* Make TomSelect compact for filters */
        .compact-filters .ts-control {
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            overflow: hidden !important;
            padding: 0 12px !important;
            font-size: 13px !important;
            border-radius: 10px !important;
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        .compact-filters .ts-wrapper {
            min-width: 120px;
        }
        .compact-filters .ts-control input {
            font-size: 13px !important;
        }
        .compact-filters .ts-dropdown {
            border-radius: 10px !important;
            font-size: 13px !important;
        }
        @keyframes progress-bar-stripes {
            0% { background-position: 1rem 0; }
            to { background-position: 0 0; }
        }
        .animate-stripes {
            background-image: linear-gradient(
                45deg,
                rgba(255, 255, 255, 0.15) 25%,
                transparent 25%,
                transparent 50%,
                rgba(255, 255, 255, 0.15) 50%,
                rgba(255, 255, 255, 0.15) 75%,
                transparent 75%,
                transparent
            );
            background-size: 1rem 1rem;
            animation: progress-bar-stripes 1s linear infinite;
        }
    </style>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate flex items-center gap-3">
                Koreksi Ujian
                <?php if (isset($currentYear)): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 border border-indigo-200">
                        <i class="ri-calendar-line mr-1"></i> TA: <?= htmlspecialchars($currentYear['name'] ?? '-') ?>
                    </span>
                <?php endif; ?>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Kelola jadwal koreksi dan input nilai untuk tahun ajaran aktif.
            </p>
        </div>
        <div class="flex items-center gap-1.5 md:gap-2 flex-wrap md:flex-nowrap">
            <?php if (isset($activeSession)): ?>
                <span class="inline-flex items-center px-2.5 py-1.5 md:px-3 md:py-1 rounded-full text-[10px] md:text-xs font-bold <?= $activeSession['is_open'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> border border-current whitespace-nowrap shadow-sm" style="white-space: nowrap;">
                    <i class="ri-door-<?= $activeSession['is_open'] ? 'open' : 'closed' ?>-line mr-1 text-[11px] md:text-xs"></i>
                    <span class="hidden sm:inline">Sesi&nbsp;</span><?= $activeSession['type'] ?>: <?= $activeSession['is_open'] ? 'DIBUKA' : 'DITUTUP' ?>
                </span>
            <?php endif; ?>

            <?php if (auth_get_role() === 'admin' || auth_is_panitia()): ?>
                <a href="<?= url('/grades/trash') ?>" class="inline-flex items-center justify-center px-2.5 py-1.5 md:px-4 md:py-2 border border-gray-300 rounded-xl text-[11px] md:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors whitespace-nowrap shadow-sm" style="white-space: nowrap;" title="Tong Sampah">
                    <i class="ri-delete-bin-line md:mr-2 text-gray-400"></i>
                    <span class="hidden md:inline">Tong Sampah</span>
                </a>
                <button onclick="toggleModal('addKoreksiModal')" class="inline-flex items-center justify-center px-2.5 py-1.5 md:px-4 md:py-2 border border-transparent rounded-xl text-[11px] md:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors whitespace-nowrap shadow-sm shadow-indigo-100" style="white-space: nowrap;">
                    <i class="ri-add-line mr-1"></i>
                    <span class="hidden sm:inline">Tambah&nbsp;</span>Koreksi
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation (Daftar Kelas) -->
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden sticky top-20 z-30">
                <div class="hidden lg:block px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Daftar Kelas</h3>
                </div>
                <!-- Mobile Hint -->
                <div class="lg:hidden flex items-center justify-between px-4 py-2 border-b border-gray-50 bg-gray-50/30">
                    <span class="text-[10px] font-bold text-indigo-500 flex items-center gap-1 uppercase tracking-wider">
                        <i class="ri-arrow-left-right-line"></i> Geser kelas ke samping
                    </span>
                    <div class="flex gap-1">
                        <div class="w-1 h-1 rounded-full bg-indigo-200 animate-pulse"></div>
                        <div class="w-1 h-1 rounded-full bg-indigo-300 animate-pulse" style="animation-delay: 0.2s"></div>
                        <div class="w-1 h-1 rounded-full bg-indigo-400 animate-pulse" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
                <nav class="p-2 flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-3 lg:pb-2 no-scrollbar snap-x">
                    <?php foreach ($kelas as $k): 
                        $isActive = $k['id'] == $filters['kelas'];
                        $totalExams = $k['total_exams'] ?? 0;
                        $selesaiExams = $k['selesai_exams'] ?? 0;
                        $isComplete = $totalExams > 0 && $selesaiExams === $totalExams;
                        
                        $badgeClass = $isComplete
                            ? 'bg-green-100 text-green-700'
                            : ($selesaiExams > 0
                                ? 'bg-yellow-100 text-yellow-700'
                                : 'bg-gray-100 text-gray-500');
                        
                        $activeClass = $isActive 
                            ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-sm ring-1 ring-indigo-100' 
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
                        
                        // Preserve other active filters when switching class
                        $sidebarQuery = array_filter(array_merge($filters, ['kelas' => $k['id']]));
                        $sidebarUrl = url('/grades?' . http_build_query($sidebarQuery));
                    ?>
                        <a href="<?= $sidebarUrl ?>" 
                           id="<?= $isActive ? 'active-class-tab' : '' ?>"
                           class="whitespace-nowrap flex items-center justify-between px-3 py-2.5 text-sm rounded-xl transition-all duration-200 group <?= $activeClass ?> snap-start min-w-[55%] lg:min-w-0">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-xs <?= $isActive ? 'bg-indigo-600 text-white shadow-indigo-200 shadow-lg' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200' ?>">
                                    <i class="ri-slideshow-3-line"></i>
                                </div>
                                <span class="flex-shrink-0"><?= htmlspecialchars($k['tingkat']) ?> - <?= htmlspecialchars($k['abjad']) ?></span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ml-2 <?= $badgeClass ?>">
                                <?= $selesaiExams ?>/<?= $totalExams ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1">
            <?php
            // Find active class name
            $activeKelasName = '';
            foreach ($kelas as $k) {
                if ($k['id'] == $filters['kelas']) {
                    $activeKelasName = $k['tingkat'] . ' - ' . $k['abjad'];
                    break;
                }
            }
            ?>
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Kelas <?= htmlspecialchars($activeKelasName) ?></h3>
                <div class="text-xs text-gray-500">
                    Menampilkan <?= count($exams) ?> pelajaran
                </div>
            </div>

            <!-- Filters -->
            <?php
            $gridCols = (auth_get_role() === 'admin' || auth_is_panitia()) ? 'md:grid-cols-5' : 'md:grid-cols-4';
            
            // Check if there are active optional filters to determine panel visibility on mobile
            $hasActiveFilters = !empty($_GET['pelajaran']) || !empty($_GET['pengajar']) || !empty($_GET['status']) || (isset($_GET['has_oral']) && $_GET['has_oral'] !== '');
            $filterPanelClass = $hasActiveFilters 
                ? 'bg-white p-3 rounded-2xl shadow-sm border border-gray-200 mb-6 compact-filters' 
                : 'bg-white p-3 rounded-2xl shadow-sm border border-gray-200 mb-6 compact-filters hidden md:block';
            $toggleText = $hasActiveFilters ? 'Sembunyikan Filter' : 'Tampilkan Filter';
            $toggleIconClass = $hasActiveFilters ? 'ri-eye-off-line' : 'ri-filter-3-line';
            ?>

            <!-- Toggle Button for Mobile View -->
            <div class="block md:hidden mb-4">
                <button onclick="toggleMobileFilters()" class="w-full bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold px-4 py-2.5 rounded-xl text-xs transition-all shadow-sm flex items-center justify-center gap-2 h-[38px]">
                    <i id="filter-toggle-icon" class="<?= $toggleIconClass ?> text-indigo-600 text-sm"></i>
                    <span id="filter-toggle-text"><?= $toggleText ?></span>
                    <?php if ($hasActiveFilters): ?>
                        <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                    <?php endif; ?>
                </button>
            </div>

            <div id="filter-panel" class="<?= $filterPanelClass ?>">
                <form method="GET" action="<?= url('/grades') ?>" class="grid grid-cols-1 <?= $gridCols ?> gap-3 items-center">
                    <!-- Hidden Kelas Filter -->
                    <input type="hidden" name="kelas" value="<?= htmlspecialchars($filters['kelas'] ?? '') ?>">

                    <!-- Pelajaran -->
                    <div>
                        <select name="pelajaran" class="tom-select block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs p-2.5 border bg-gray-50/50">
                            <option value="">Semua Pelajaran</option>
                            <?php foreach ($pelajaran as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $filters['pelajaran'] == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Pengajar -->
                    <?php if (auth_get_role() === 'admin' || auth_is_panitia()): ?>
                    <div>
                        <select name="pengajar" class="tom-select block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs p-2.5 border bg-gray-50/50">
                            <option value="">Semua Pemeriksa</option>
                            <?php foreach ($pengajar as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $filters['pengajar'] == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <!-- Status -->
                    <div>
                        <select name="status" class="tom-select block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs p-2.5 border bg-gray-50/50">
                            <option value="">Semua Status</option>
                            <option value="selesai" <?= $filters['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="proses" <?= $filters['status'] === 'proses' ? 'selected' : '' ?>>Proses / Draft</option>
                            <option value="belum" <?= $filters['status'] === 'belum' ? 'selected' : '' ?>>Belum Diperiksa</option>
                        </select>
                    </div>
                    <!-- Jenis Koreksi -->
                    <div>
                        <select name="has_oral" class="tom-select block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs p-2.5 border bg-gray-50/50">
                            <option value="">Semua Jenis</option>
                            <option value="0" <?= (isset($filters['has_oral']) && $filters['has_oral'] === '0') ? 'selected' : '' ?>>Tulis</option>
                            <option value="1" <?= (isset($filters['has_oral']) && $filters['has_oral'] === '1') ? 'selected' : '' ?>>Tulis & Lisan</option>
                            <option value="2" <?= (isset($filters['has_oral']) && $filters['has_oral'] === '2') ? 'selected' : '' ?>>Lisan</option>
                        </select>
                    </div>
         
                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 rounded-xl text-xs transition-all shadow-sm shadow-indigo-100 flex items-center justify-center gap-2 h-[38px]">
                            <i class="ri-filter-3-line"></i> Filter
                        </button>
                        <a href="<?= url('/grades?kelas=' . $filters['kelas']) ?>" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold px-4 rounded-xl text-xs transition-all flex items-center justify-center h-[38px]">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <?php if (empty($exams)): ?>
                <div class="text-center py-12 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center p-6">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 mb-3 border border-slate-100">
                        <i class="ri-file-shield-2-line text-xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Belum ada data koreksi</h3>
                    <p class="mt-1 text-xs text-gray-500 max-w-xs leading-relaxed">
                        <?= !empty(array_filter(array_diff_key($_GET, ['kelas' => '']))) ? 'Tidak ada data koreksi yang cocok dengan filter pencarian Anda.' : 'Mulai dengan menambahkan sesi koreksi baru untuk kelas ini.' ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="hidden md:block bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden mb-8">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/30">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelajaran & Detail</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($exams as $k):
                                $id = $k['id'];
                                $isAdmin = (auth_get_role() === 'admin');
                                $isPanitia = auth_is_panitia($k['exam_session_id']);
                                $mapel = $k['mapel_nama'] ?? 'Unknown';
                                $klsFull = "Kelas " . ($k['tingkat'] ?? '?') . "-" . ($k['abjad'] ?? '?');
                                $guru = $k['pengajar_nama'] ?? 'Unknown';
                                $isDone = ($k['status'] ?? '') === 'selesai';

                                // Progress
                                $totalStudents = (int)($k['jumlah_murid'] ?? 0);
                                $gradedCount = (int)($k['graded_count'] ?? 0);
                                $gradedOralCount = (int)($k['graded_oral_count'] ?? 0);

                                $percentage = $totalStudents > 0 ? round(($gradedCount / $totalStudents) * 100) : 0;
                                $percentageOral = $totalStudents > 0 ? round(($gradedOralCount / $totalStudents) * 100) : 0;

                                $colorClass = $percentage >= 100 ? 'bg-green-600' : 'bg-indigo-600';
                                $colorClassOral = $percentageOral >= 100 ? 'bg-green-600' : 'bg-pink-600';
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-bold text-gray-900 leading-tight"><?= htmlspecialchars($mapel) ?></span>
                                            <div class="flex items-center flex-wrap gap-x-2 gap-y-1 text-xs text-gray-500">
                                                <span class="flex items-center gap-1.5 whitespace-nowrap">
                                                    <img src="<?= url('/avatar?id=' . $k['teacher_id']) ?>" 
                                                         alt="Avatar" 
                                                         class="w-5 h-5 rounded-full bg-gray-100 border border-gray-100 flex-shrink-0"
                                                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($guru) ?>&background=F3F4F6&color=1F2937'">
                                                    <?= htmlspecialchars($guru) ?>
                                                </span>
                                                <span class="text-gray-300 select-none">•</span>
                                                <div class="flex items-center gap-1">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-700 uppercase tracking-wider border border-slate-200">
                                                        <?= htmlspecialchars($k['exam_type'] ?? '-') ?>
                                                    </span>
                                                    <?php if ($k['has_oral'] == 1): ?>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 border border-indigo-100 text-indigo-700 uppercase tracking-wide">
                                                            Tulis & Lisan
                                                        </span>
                                                    <?php elseif ($k['has_oral'] == 2): ?>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-pink-50 border border-pink-100 text-pink-700 uppercase tracking-wide">
                                                            Lisan
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-50 border border-slate-100 text-slate-700 uppercase tracking-wide">
                                                            Tulis
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($isDone): ?>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-50 border border-green-100 text-green-700 uppercase tracking-wide">
                                                            Selesai
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-yellow-50 border border-yellow-100 text-yellow-700 uppercase tracking-wide">
                                                            Proses
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="w-full max-w-xs min-w-[180px] flex flex-col gap-1.5">
                                            <?php if ($k['has_oral'] == 0 || $k['has_oral'] == 1): // Tulis Progress bar ?>
                                                <?php 
                                                    $tulisBg = $percentage >= 100 ? 'bg-green-600' : 'bg-indigo-600 animate-stripes';
                                                ?>
                                                <div class="relative w-full bg-slate-100 border border-slate-200/60 rounded-lg h-[18px] overflow-hidden flex items-center shadow-inner">
                                                    <!-- Background Text (Dark on light bg) -->
                                                    <div class="absolute inset-0 flex items-center justify-between px-2 text-[9px] font-extrabold text-slate-700 tracking-wider">
                                                        <span class="flex items-center gap-1 select-none"><i class="ri-pencil-line text-slate-500"></i> Tulis</span>
                                                        <span><?= $gradedCount ?>/<?= $totalStudents ?> (<?= $percentage ?>%)</span>
                                                    </div>
                                                    
                                                    <!-- Filled Progress Bar Fill -->
                                                    <div class="absolute left-0 top-0 bottom-0 <?= $tulisBg ?> transition-all duration-300" style="width: <?= $percentage ?>%"></div>
                                                    
                                                    <!-- Foreground Text (White on filled solid bg) -->
                                                    <div class="absolute inset-0 flex items-center justify-between px-2 text-[9px] font-extrabold text-white tracking-wider transition-all duration-300" style="clip-path: inset(0 <?= 100 - $percentage ?>% 0 0);">
                                                        <span class="flex items-center gap-1 select-none"><i class="ri-pencil-line"></i> Tulis</span>
                                                        <span><?= $gradedCount ?>/<?= $totalStudents ?> (<?= $percentage ?>%)</span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($k['has_oral'] == 2 || $k['has_oral'] == 1): // Lisan Progress bar ?>
                                                <?php 
                                                    $lisanBg = $percentageOral >= 100 ? 'bg-green-600' : 'bg-pink-600 animate-stripes';
                                                ?>
                                                <div class="relative w-full bg-slate-100 border border-slate-200/60 rounded-lg h-[18px] overflow-hidden flex items-center shadow-inner">
                                                    <!-- Background Text (Dark on light bg) -->
                                                    <div class="absolute inset-0 flex items-center justify-between px-2 text-[9px] font-extrabold text-slate-700 tracking-wider">
                                                        <span class="flex items-center gap-1 select-none"><i class="ri-mic-line text-slate-500"></i> Lisan</span>
                                                        <span><?= $gradedOralCount ?>/<?= $totalStudents ?> (<?= $percentageOral ?>%)</span>
                                                    </div>
                                                    
                                                    <!-- Filled Progress Bar Fill -->
                                                    <div class="absolute left-0 top-0 bottom-0 <?= $lisanBg ?> transition-all duration-300" style="width: <?= $percentageOral ?>%"></div>
                                                    
                                                    <!-- Foreground Text (White on filled solid bg) -->
                                                    <div class="absolute inset-0 flex items-center justify-between px-2 text-[9px] font-extrabold text-white tracking-wider transition-all duration-300" style="clip-path: inset(0 <?= 100 - $percentageOral ?>% 0 0);">
                                                        <span class="flex items-center gap-1 select-none"><i class="ri-mic-line"></i> Lisan</span>
                                                        <span><?= $gradedOralCount ?>/<?= $totalStudents ?> (<?= $percentageOral ?>%)</span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <?php if ($isDone): ?>
                                                <a href="<?= url('/grades/edit?id=' . $id) ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                                    Lihat Nilai
                                                </a>
                                                <?php if (auth_can_manage_grades($k['exam_session_id'])): ?>
                                                    <form action="<?= url('/grades/unlock') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kembali akses edit untuk koreksi ini?');" class="inline">
                                                        <?= csrf_token_field() ?>
                                                        <input type="hidden" name="id" value="<?= $id ?>">
                                                        <button type="submit" class="text-orange-600 hover:text-orange-900 bg-orange-50 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                                            Buka Akses
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php 
                                                    $useBayanat = (int)($k['use_bayanat'] ?? 1);
                                                    $isBayanatComplete = ($useBayanat == 0) || ($totalStudents > 0 && $k['bayanat_count'] >= $totalStudents);
                                                    $isDesignatedExaminer = ($k['teacher_id'] == auth_get_user_id());
                                                    
                                                    // Admin & Panitia ALWAYS can enter (to fill bayanat or scores)
                                                    // Designated Examiner can ONLY enter if bayanat is complete AND session is open
                                                    if ($isAdmin || $isPanitia) {
                                                        $canInput = true;
                                                        $disabledReason = "";
                                                    } elseif ($isDesignatedExaminer) {
                                                        if (!$isBayanatComplete) {
                                                            $canInput = false;
                                                            $disabledReason = "Bayanat Belum Lengkap";
                                                        } elseif ($k['session_is_open'] != 1) {
                                                            $canInput = false;
                                                            $disabledReason = "Sesi Ditutup";
                                                        } else {
                                                            $canInput = true;
                                                            $disabledReason = "";
                                                        }
                                                    } else {
                                                        $canInput = false;
                                                        $disabledReason = "Bukan Pemeriksa";
                                                    }
                                                ?>
                                                <?php if ($canInput): ?>
                                                    <a href="<?= url('/grades/edit?id=' . $id) ?>" class="text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg shadow-sm text-xs font-bold transition-colors">
                                                        Input Nilai
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg cursor-not-allowed italic text-[11px] border border-gray-200" title="<?= $disabledReason ?>">
                                                        <?= $disabledReason ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php if (auth_can_manage_grades($k['exam_session_id'])): ?>
                                                <a href="<?= url('/grades/delete?id=' . $id) ?>" onclick="return confirmDelete('<?= htmlspecialchars($mapel) ?>', '<?= htmlspecialchars($klsFull) ?>')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded-lg transition-colors inline-flex items-center justify-center" title="Hapus Koreksi">
                                                    <i class="ri-delete-bin-line text-[15px]"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile View Cards -->
                <div class="block md:hidden space-y-4 mb-8">
                    <?php foreach ($exams as $k): 
                        $id = $k['id'];
                        $isAdmin = (auth_get_role() === 'admin');
                        $isPanitia = auth_is_panitia($k['exam_session_id']);
                        $mapel = $k['mapel_nama'] ?? 'Unknown';
                        $klsFull = "Kelas " . ($k['tingkat'] ?? '?') . "-" . ($k['abjad'] ?? '?');
                        $guru = $k['pengajar_nama'] ?? 'Unknown';
                        $isDone = ($k['status'] ?? '') === 'selesai';

                        // Progress
                        $totalStudents = (int)($k['jumlah_murid'] ?? 0);
                        $gradedCount = (int)($k['graded_count'] ?? 0);
                        $gradedOralCount = (int)($k['graded_oral_count'] ?? 0);

                        $percentage = $totalStudents > 0 ? round(($gradedCount / $totalStudents) * 100) : 0;
                        $percentageOral = $totalStudents > 0 ? round(($gradedOralCount / $totalStudents) * 100) : 0;
                        
                        $colorClass = $percentage >= 100 ? 'bg-green-600' : 'bg-indigo-600';
                        $colorClassOral = $percentageOral >= 100 ? 'bg-green-600' : 'bg-pink-600';

                        // Action permissions (same as table)
                        $useBayanat = (int)($k['use_bayanat'] ?? 1);
                        $isBayanatComplete = ($useBayanat == 0) || ($totalStudents > 0 && $k['bayanat_count'] >= $totalStudents);
                        $isDesignatedExaminer = ($k['teacher_id'] == auth_get_user_id());
                        
                        if ($isAdmin || $isPanitia) {
                            $canInput = true;
                            $disabledReason = "";
                        } elseif ($isDesignatedExaminer) {
                            if (!$isBayanatComplete) {
                                $canInput = false;
                                $disabledReason = "Bayanat Belum Lengkap";
                            } elseif ($k['session_is_open'] != 1) {
                                $canInput = false;
                                $disabledReason = "Sesi Ditutup";
                            } else {
                                $canInput = true;
                                $disabledReason = "";
                            }
                        } else {
                            $canInput = false;
                            $disabledReason = "Bukan Pemeriksa";
                        }
                    ?>
                        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-col gap-3">
                            <!-- Header: Pelajaran, Pengajar & Badges -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-extrabold text-gray-900 truncate"><?= htmlspecialchars($mapel) ?></h4>
                                    <!-- Teacher Avatar & Name -->
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <img src="<?= url('/avatar?id=' . $k['teacher_id']) ?>" 
                                             alt="Avatar" 
                                             class="w-5 h-5 rounded-full bg-gray-100 border border-gray-100 flex-shrink-0"
                                             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($guru) ?>&background=F3F4F6&color=1F2937'">
                                        <span class="text-[11px] text-gray-600 font-medium truncate"><?= htmlspecialchars($guru) ?></span>
                                    </div>
                                </div>

                                <!-- Badges Group in Top Right -->
                                <div class="flex items-center gap-1.5 flex-wrap justify-end flex-shrink-0">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-700 uppercase tracking-wider border border-slate-200">
                                        <?= htmlspecialchars($k['exam_type'] ?? '-') ?>
                                    </span>
                                    <?php if ($k['has_oral'] == 1): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 border border-indigo-100 text-indigo-700 uppercase tracking-wide">
                                            Tulis & Lisan
                                        </span>
                                    <?php elseif ($k['has_oral'] == 2): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-pink-50 border border-pink-100 text-pink-700 uppercase tracking-wide">
                                            Lisan
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-50 border border-slate-100 text-slate-700 uppercase tracking-wide">
                                            Tulis
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($isDone): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-50 border border-green-100 text-green-700 uppercase tracking-wide">
                                            Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-yellow-50 border border-yellow-100 text-yellow-700 uppercase tracking-wide">
                                            Proses
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Progress & Actions -->
                            <div class="border-t border-gray-100 pt-3 flex items-center justify-between gap-4">
                                <div class="flex-1 min-w-0 flex flex-col gap-1.5">
                                    <?php if ($k['has_oral'] == 0 || $k['has_oral'] == 1): // Tulis Progress bar ?>
                                        <?php 
                                            $tulisBg = $percentage >= 100 ? 'bg-green-600' : 'bg-indigo-600 animate-stripes';
                                        ?>
                                        <div class="relative w-full bg-slate-100 border border-slate-200/60 rounded-lg h-[18px] overflow-hidden flex items-center shadow-inner">
                                            <!-- Background Text (Dark on light bg) -->
                                            <div class="absolute inset-0 flex items-center justify-between px-2 text-[9px] font-extrabold text-slate-700 tracking-wider">
                                                <span class="flex items-center gap-1 select-none"><i class="ri-pencil-line text-slate-500"></i> Tulis</span>
                                                <span><?= $gradedCount ?>/<?= $totalStudents ?> (<?= $percentage ?>%)</span>
                                            </div>
                                            
                                            <!-- Filled Progress Bar Fill -->
                                            <div class="absolute left-0 top-0 bottom-0 <?= $tulisBg ?> transition-all duration-300" style="width: <?= $percentage ?>%"></div>
                                            
                                            <!-- Foreground Text (White on filled solid bg) -->
                                            <div class="absolute inset-0 flex items-center justify-between px-2 text-[9px] font-extrabold text-white tracking-wider transition-all duration-300" style="clip-path: inset(0 <?= 100 - $percentage ?>% 0 0);">
                                                <span class="flex items-center gap-1 select-none"><i class="ri-pencil-line"></i> Tulis</span>
                                                <span><?= $gradedCount ?>/<?= $totalStudents ?> (<?= $percentage ?>%)</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($k['has_oral'] == 2 || $k['has_oral'] == 1): // Lisan Progress bar ?>
                                        <?php 
                                            $lisanBg = $percentageOral >= 100 ? 'bg-green-600' : 'bg-pink-600 animate-stripes';
                                        ?>
                                        <div class="relative w-full bg-slate-100 border border-slate-200/60 rounded-lg h-[18px] overflow-hidden flex items-center shadow-inner">
                                            <!-- Background Text (Dark on light bg) -->
                                            <div class="absolute inset-0 flex items-center justify-between px-2 text-[9px] font-extrabold text-slate-700 tracking-wider">
                                                <span class="flex items-center gap-1 select-none"><i class="ri-mic-line text-slate-500"></i> Lisan</span>
                                                <span><?= $gradedOralCount ?>/<?= $totalStudents ?> (<?= $percentageOral ?>%)</span>
                                            </div>
                                            
                                            <!-- Filled Progress Bar Fill -->
                                            <div class="absolute left-0 top-0 bottom-0 <?= $lisanBg ?> transition-all duration-300" style="width: <?= $percentageOral ?>%"></div>
                                            
                                            <!-- Foreground Text (White on filled solid bg) -->
                                            <div class="absolute inset-0 flex items-center justify-between px-2 text-[9px] font-extrabold text-white tracking-wider transition-all duration-300" style="clip-path: inset(0 <?= 100 - $percentageOral ?>% 0 0);">
                                                <span class="flex items-center gap-1 select-none"><i class="ri-mic-line"></i> Lisan</span>
                                                <span><?= $gradedOralCount ?>/<?= $totalStudents ?> (<?= $percentageOral ?>%)</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <?php if ($isDone): ?>
                                        <a href="<?= url('/grades/edit?id=' . $id) ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                            Lihat
                                        </a>
                                        <?php if (auth_can_manage_grades($k['exam_session_id'])): ?>
                                            <form action="<?= url('/grades/unlock') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kembali akses edit untuk koreksi ini?');" class="inline">
                                                <?= csrf_token_field() ?>
                                                <input type="hidden" name="id" value="<?= $id ?>">
                                                <button type="submit" class="text-orange-600 bg-orange-50 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                                    Buka
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($canInput): ?>
                                            <a href="<?= url('/grades/edit?id=' . $id) ?>" class="text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg shadow-sm text-xs font-bold transition-colors">
                                                Input
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg cursor-not-allowed italic text-[10px] border border-gray-200" title="<?= $disabledReason ?>">
                                                <?= $disabledReason ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (auth_can_manage_grades($k['exam_session_id'])): ?>
                                        <a href="<?= url('/grades/delete?id=' . $id) ?>" onclick="return confirmDelete('<?= htmlspecialchars($mapel) ?>', '<?= htmlspecialchars($klsFull) ?>')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded-lg transition-colors inline-flex items-center justify-center">
                                            <i class="ri-delete-bin-line text-sm"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<!-- Modal Add -->
<div id="addKoreksiModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('addKoreksiModal')"></div> <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-md sm:my-8 sm:align-middle sm:max-w-lg sm:w-full mx-auto">
            <form action="<?= url('/grades/create') ?>" method="POST">
                <?= csrf_token_field() ?>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 flex items-center justify-between">
                        Tambah Koreksi Baru
                        <?php if (isset($activeSession)): ?>
                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Sesi: <?= $activeSession['type'] ?></span>
                        <?php endif; ?>
                    </h3>

                    <?php if (!isset($activeSession)): ?>
                        <div class="bg-red-50 text-red-700 p-3 rounded text-sm mb-4">
                            <strong>Peringatan!</strong> Belum ada sesi ujian (UUPT/UPT/dll) yang diaktifkan oleh Panitia. Anda tidak dapat membuat data koreksi baru.
                        </div>
                    <?php endif; ?>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="id_kelas" id="modal_id_kelas" required class="tom-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white" onchange="onClassChange()">
                                <option value="">Pilih Kelas...</option>
                                <?php foreach ($allKelas as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['tingkat']) ?> - <?= htmlspecialchars($k['abjad']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pelajaran</label>
                            <select name="id_pelajaran" id="modal_id_pelajaran" required class="tom-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white" onchange="onSubjectChange()">
                                <option value="">Pilih Kelas Terlebih Dahulu...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pemeriksa</label>
                            <select name="id_pengajar" id="modal_id_pengajar" required class="tom-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                <?php foreach ($pengajar as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Skor Tertinggi (Total Poin Soal)</label>
                            <input type="number" name="skor_maks" required value="100" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                        <!-- Toggle Pelajaran Khusus -->
                        <?php if (!empty($specialSubjects)): ?>
                        <div class="border-t border-gray-100 pt-3">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" id="toggle_special" class="sr-only" onchange="onToggleSpecial()">
                                    <div class="w-10 h-5 bg-gray-200 rounded-full shadow-inner transition-colors" id="toggle_special_track"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform" id="toggle_special_thumb"></div>
                                </div>
                                <span class="text-sm font-medium text-purple-700">
                                    <i class="ri-star-fill text-purple-500 mr-1"></i>Tampilkan Pelajaran Ujian Khusus
                                </span>
                            </label>
                            <p class="text-xs text-gray-400 mt-1 ml-12">Aktifkan untuk memilih pelajaran di luar jadwal (Praktek Mengajar, dll). Pengajar dipilih manual.</p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Koreksi</label>
                            <select name="has_oral" id="modal_has_oral" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white text-sm">
                                <option value="0">Tulis</option>
                                <option value="1">Tulis & Lisan</option>
                                <option value="2">Lisan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="toggleModal('addKoreksiModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const teachingMap = <?= json_encode($teachingMap) ?>;
    const specialSubjects = <?= json_encode($specialSubjects ?? []) ?>;
    let isSpecialMode = false;

    function resetKoreksiForm() {
        // Reset special toggle
        isSpecialMode = false;
        const tog = document.getElementById('toggle_special');
        if (tog) { tog.checked = false; updateToggleUI(false); }

        // Reset has_oral select
        const hasOralSelect = document.getElementById('modal_has_oral');
        if (hasOralSelect) hasOralSelect.value = "0";

        // Reset skor_maks
        const skorMaks = document.querySelector('#addKoreksiModal input[name="skor_maks"]');
        if (skorMaks) skorMaks.value = 100;

        // Reset TomSelect fields (kelas, pelajaran, pengajar)
        setTimeout(() => {
            const tsKelas = document.getElementById('modal_id_kelas');
            const tsPelajaran = document.getElementById('modal_id_pelajaran');
            const tsPengajar = document.getElementById('modal_id_pengajar');

            if (tsKelas && tsKelas.tomselect) {
                tsKelas.tomselect.clear();
            }
            if (tsPelajaran && tsPelajaran.tomselect) {
                tsPelajaran.tomselect.clear();
                tsPelajaran.tomselect.clearOptions();
                tsPelajaran.tomselect.addOption({ value: '', text: 'Pilih Kelas Terlebih Dahulu...' });
            }
            if (tsPengajar && tsPengajar.tomselect) {
                tsPengajar.tomselect.clear();
            }
        }, 60);
    }

    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
        if (!document.getElementById(id).classList.contains('hidden')) {
            setTimeout(initTomSelects, 50);
            resetKoreksiForm();
        }
    }

    function updateToggleUI(active) {
        const track = document.getElementById('toggle_special_track');
        const thumb = document.getElementById('toggle_special_thumb');
        if (!track) return;
        if (active) {
            track.classList.remove('bg-gray-200'); track.classList.add('bg-purple-500');
            thumb.style.transform = 'translateX(20px)';
        } else {
            track.classList.remove('bg-purple-500'); track.classList.add('bg-gray-200');
            thumb.style.transform = 'translateX(0)';
        }
    }

    function onToggleSpecial() {
        isSpecialMode = document.getElementById('toggle_special').checked;
        updateToggleUI(isSpecialMode);
        const tsSubject = document.getElementById('modal_id_pelajaran').tomselect;
        tsSubject.clear();
        tsSubject.clearOptions();
        if (isSpecialMode) {
            specialSubjects.forEach(s => { tsSubject.addOption({ value: s.id, text: s.nama }); });
            tsSubject.refreshOptions(false);
        } else {
            onClassChange();
        }
    }

    function onClassChange() {
        if (isSpecialMode) return;
        const classSelect = document.getElementById('modal_id_kelas');
        const subjectSelect = document.getElementById('modal_id_pelajaran');
        const classId = classSelect.value;
        const tsSubject = subjectSelect.tomselect;
        tsSubject.clear();
        tsSubject.clearOptions();
        if (classId && teachingMap[classId]) {
            const subjects = teachingMap[classId];
            subjects.forEach(s => { tsSubject.addOption({ value: s.subject_id, text: s.subject_name }); });
            tsSubject.refreshOptions(false);
        } else {
            tsSubject.addOption({ value: "", text: "Pilih Kelas Terlebih Dahulu..." });
        }
    }

    function onSubjectChange() {
        if (isSpecialMode) return; // Pengajar dipilih manual saat mode khusus
        const classId = document.getElementById('modal_id_kelas').value;
        const subjectId = document.getElementById('modal_id_pelajaran').value;
        const pengajarSelect = document.getElementById('modal_id_pengajar');
        if (classId && subjectId && teachingMap[classId]) {
            const assignment = teachingMap[classId].find(s => s.subject_id == subjectId);
            if (assignment && assignment.teacher_id) {
                pengajarSelect.tomselect.setValue(assignment.teacher_id);
            }
        }
    }
</script>

<script>
    function toggleMobileFilters() {
        const panel = document.getElementById('filter-panel');
        const textSpan = document.getElementById('filter-toggle-text');
        const iconEl = document.getElementById('filter-toggle-icon');
        
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            textSpan.textContent = 'Sembunyikan Filter';
            iconEl.className = 'ri-eye-off-line text-indigo-600 text-sm';
        } else {
            panel.classList.add('hidden');
            textSpan.textContent = 'Tampilkan Filter';
            iconEl.className = 'ri-filter-3-line text-indigo-600 text-sm';
        }
    }
</script>

<script>
    function confirmDelete(mapel, kelas) {
        return confirm('PERINGATAN: Anda akan menghapus data koreksi ' + mapel + ' (' + kelas + ').\n\nData yang dihapus akan disembunyikan dan hanya bisa dipulihkan oleh tim IT/Database Admin.\n\nApakah Anda yakin ingin melanjutkan?');
    }
</script>

<script>
    // Auto-scroll active tab into view on mobile
    document.addEventListener('DOMContentLoaded', () => {
        const activeTab = document.getElementById('active-class-tab');
        if (activeTab && window.innerWidth < 1024) {
            activeTab.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest', 
                inline: 'center' 
            });
        }
    });
</script>

<?php renderFooter(); ?>
