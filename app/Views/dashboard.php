<?php
// app/Views/dashboard.php
// Logic has been moved to DashboardController.php
// Data is passed via $data array

renderHeader ("Dashboard");
?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard KMI</h1>
            <p class="mt-2 text-gray-600">
                Ringkasan aktivitas hari ini, <strong><?= date('d M Y') ?> (<?= $todayDay ?>)</strong>
            </p>
        </div>
        <div class="mt-4 md:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                Tahun Ajaran <?= htmlspecialchars($yearName) ?>
            </span>
        </div>
    </div>

    <?php 
    $myClasses = auth_get_wali_kelas_kelas();
    if (!empty($myClasses)): 
    ?>
    <div class="mb-6 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-md p-6 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold flex items-center gap-2">
                <span>Selamat Datang, Wali Kelas!</span>
            </h2>
            <p class="text-indigo-100 text-sm mt-1">Anda adalah wali kelas untuk kelas berikut pada tahun ajaran ini. Klik untuk melihat detail kelas.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <?php if (!empty($muwajjahPersonalStats)): ?>
                <div class="px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl text-xs font-semibold flex items-center gap-2" title="Statistik pendampingan Muwajjah Anda bulan ini">
                    <i class="ri-moon-clear-line text-amber-300 text-lg"></i>
                    <div>
                        <div class="text-[10px] text-indigo-200 uppercase font-bold tracking-wider">Muwajjah Bulan Ini</div>
                        <div class="text-sm font-bold text-white"><?= $muwajjahPersonalStats['compliance_rate'] ?>% Kehadiran <span class="text-[11px] font-normal text-indigo-200">(<?= $muwajjahPersonalStats['hadir'] ?>/<?= $muwajjahPersonalStats['total_effective_days'] ?>)</span></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($myClasses as $c): ?>
                <a href="<?= url('/classes/detail?id=' . $c['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-md border border-white/10 rounded-xl text-white font-semibold transition-all">
                    <i class="ri-community-line text-lg"></i>
                    Kelas <?= htmlspecialchars($c['tingkat'] . '-' . $c['abjad']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Grid Layout: Master Data (3 Kiri) | Kalender | Master Data (2 Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        
        <?php if ($role === 'admin'): ?>
        <!-- Kiri: 5 Master Data -->
        <div class="lg:col-span-1 grid grid-cols-2 lg:flex lg:flex-col gap-3 lg:gap-4">
            <a href="<?= url('/students') ?>" class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow group flex-1 flex flex-col justify-center">
                <div class="flex items-center justify-between">
                    <div>
                       <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Total Santri</p>
                       <p class="text-2xl font-bold text-gray-900 mt-1"><?= $stats['santri'] ?></p>
                    </div>
                    <div class="bg-pink-50 p-2.5 rounded-lg text-pink-600 group-hover:bg-pink-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
            </a>
            <a href="<?= url('/teachers') ?>" class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow group flex-1 flex flex-col justify-center">
                <div class="flex items-center justify-between">
                    <div>
                       <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Pengajar</p>
                       <p class="text-2xl font-bold text-gray-900 mt-1"><?= $stats['pengajar'] ?></p>
                    </div>
                    <div class="bg-purple-50 p-2.5 rounded-lg text-purple-600 group-hover:bg-purple-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
            </a>
            <a href="<?= url('/classes') ?>" class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow group flex-1 flex flex-col justify-center">
                <div class="flex items-center justify-between">
                    <div>
                       <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Kelas</p>
                       <p class="text-2xl font-bold text-gray-900 mt-1"><?= $stats['kelas'] ?></p>
                    </div>
                    <div class="bg-green-50 p-2.5 rounded-lg text-green-600 group-hover:bg-green-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                </div>
            </a>
            <a href="<?= url('/subjects') ?>" class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow group flex-1 flex flex-col justify-center">
                <div class="flex items-center justify-between">
                    <div>
                       <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Pelajaran</p>
                       <p class="text-2xl font-bold text-gray-900 mt-1"><?= $stats['pelajaran'] ?></p>
                    </div>
                    <div class="bg-blue-50 p-2.5 rounded-lg text-blue-600 group-hover:bg-blue-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                </div>
            </a>
            <a href="<?= url('/leaves') ?>" class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow group flex-1 flex flex-col justify-center">
                 <div class="flex items-center justify-between">
                    <div>
                       <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Pengajar Izin</p>
                       <p class="text-2xl font-bold text-gray-900 mt-1"><?= $stats['pengajar_izin'] ?? 0 ?></p>
                    </div>
                    <div class="bg-red-50 p-2.5 rounded-lg text-red-600 group-hover:bg-red-100 transition-colors">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <!-- Tengah: Calendar -->
        <div class="lg:col-span-<?= ($role === 'admin') ? '2' : '4' ?> space-y-6" id="calendar-wrapper" data-month="<?= $selectedMonth ?>" data-year="<?= $selectedYearVal ?>">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full">
                <!-- Calendar Header -->
                <div class="px-4 py-3 md:px-6 md:py-4 border-b border-gray-100 flex flex-row items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-2 md:gap-3">
                        <i class="ri-calendar-event-line text-indigo-600 text-2xl md:text-3xl"></i>
                        <div class="flex flex-col justify-center">
                            <h2 class="text-base md:text-lg font-bold text-gray-900 leading-none">Kalender Akademik</h2>
                            <span class="text-[11px] sm:text-xs font-medium text-indigo-600 leading-none mt-0.5 md:-mt-1.5"><?= $bulanId[sprintf('%02d', $selectedMonth)] ?> <?= $selectedYearVal ?></span>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex items-center gap-1 border border-gray-200 rounded-lg overflow-hidden p-0.5">
                        <button type="button" onclick="loadCalendar(<?= $selectedMonth == 1 ? 12 : $selectedMonth - 1 ?>, <?= $selectedMonth == 1 ? $selectedYearVal - 1 : $selectedYearVal ?>)" class="px-2 py-1 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="Bulan Sebelumnya">
                            <i class="ri-arrow-left-s-line text-lg"></i>
                        </button>
                        <button type="button" onclick="loadCalendar(<?= date('m') ?>, <?= date('Y') ?>)" class="px-2 py-1 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors border-x border-gray-100" title="Bulan Ini">
                            <i class="ri-focus-3-line text-lg"></i>
                        </button>
                        <button type="button" onclick="loadCalendar(<?= $selectedMonth == 12 ? 1 : $selectedMonth + 1 ?>, <?= $selectedMonth == 12 ? $selectedYearVal + 1 : $selectedYearVal ?>)" class="px-2 py-1 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="Bulan Selanjutnya">
                            <i class="ri-arrow-right-s-line text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="w-full flex-1">
                    <div class="border-l border-gray-100 h-full flex flex-col">
                        <!-- Days of week -->
                        <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50/50">
                            <?php 
                            $hari = ['Sabtu', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                            foreach($hari as $h): ?>
                                <div class="px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-wider border-r border-gray-100">
                                    <?= substr($h, 0, 3) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Days grid with adjusted height -->
                        <div class="grid grid-cols-7 auto-rows-[minmax(75px,_1fr)] flex-1">
                            <?php 
                            // Blank days before month starts
                            for ($i = 0; $i < $firstDayOffset; $i++) {
                                echo '<div class="border-r border-b border-gray-100 bg-gray-50/30"></div>';
                            }

                            // Days of the month
                            $totalRows = ceil(($firstDayOffset + $daysInMonth) / 7);
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $currentDateStr = sprintf('%04d-%02d-%02d', $selectedYearVal, $selectedMonth, $day);
                                $currentTimestamp = strtotime($currentDateStr);
                                
                                $isToday = (date('Y-m-d') === $currentDateStr);
                                $todayClass = $isToday ? 'bg-indigo-50' : 'bg-white';
                                $todayNumberClass = $isToday ? 'bg-indigo-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px]' : 'text-gray-700 p-0.5 text-xs';

                                $cellIndex = ($firstDayOffset + $day - 1) % 7;
                                $rowIndex = floor(($firstDayOffset + $day - 1) / 7);
                                
                                $originClass = 'origin-center';
                                if ($rowIndex == 0) {
                                    if ($cellIndex == 0) $originClass = 'origin-top-left';
                                    elseif ($cellIndex == 6) $originClass = 'origin-top-right';
                                    else $originClass = 'origin-top';
                                } elseif ($rowIndex == $totalRows - 1) {
                                    if ($cellIndex == 0) $originClass = 'origin-bottom-left';
                                    elseif ($cellIndex == 6) $originClass = 'origin-bottom-right';
                                    else $originClass = 'origin-bottom';
                                } else {
                                    if ($cellIndex == 0) $originClass = 'origin-left';
                                    elseif ($cellIndex == 6) $originClass = 'origin-right';
                                }

                                echo "<div class=\"border-r border-b border-gray-100 relative group cursor-pointer {$todayClass}\">";
                                // The absolute visual container
                                echo "<div class=\"absolute top-0 left-0 w-full min-h-full p-1 flex flex-col {$todayClass} {$originClass} transition-all duration-200 group-hover:scale-[1.15] group-hover:z-[70] group-hover:shadow-2xl group-hover:rounded-lg group-hover:h-fit z-10\">";
                                echo "<div class=\"flex justify-end\"><span class=\"font-medium {$todayNumberClass}\">{$day}</span></div>";
                                
                                // Render Events for this day
                                echo "<div class=\"flex-1 space-y-0.5 flex flex-col gap-0 overflow-hidden group-hover:overflow-visible no-scrollbar mt-0.5\">";
                                
                                foreach ($monthEvents as $event) {
                                    $start = strtotime($event['tanggal_mulai']);
                                    $end = empty($event['tanggal_selesai']) ? $start : strtotime($event['tanggal_selesai']);
                                    
                                    if ($currentTimestamp >= $start && $currentTimestamp <= $end) {
                                        $cfg = $kategoriConfig[$event['kategori']] ?? $kategoriConfig['Lainnya'];
                                        
                                        $isStart = ($currentTimestamp == $start);
                                        $isEnd = ($currentTimestamp == $end);
                                        
                                        $roundedClass = '';
                                        if ($isStart && $isEnd) $roundedClass = 'rounded mx-0.5';
                                        elseif ($isStart) $roundedClass = 'rounded-l ml-0.5 -mr-1 z-10 relative';
                                        elseif ($isEnd) $roundedClass = 'rounded-r mr-0.5 -ml-1 z-10 relative';
                                        else $roundedClass = '-mx-1 z-0 relative';

                                        $tooltip = htmlspecialchars($event['keterangan'] . ' (' . $event['kategori'] . ')');

                                        echo "<div title=\"{$tooltip}\" class=\"px-1 py-[2px] text-[8px] leading-tight font-medium truncate group-hover:whitespace-normal group-hover:text-clip group-hover:break-words {$cfg['bg']} {$cfg['text']} border-y border-transparent {$roundedClass}\">";
                                        if ($isStart || date('w', $currentTimestamp) == 6 || $day == 1) {
                                            echo htmlspecialchars($event['keterangan']);
                                        } else {
                                            echo "&nbsp;";
                                        }
                                        echo "</div>";
                                    }
                                }
                                
                                echo "</div></div></div>"; 
                            }

                            // Fill remaining grid cells
                            $totalCells = $firstDayOffset + $daysInMonth;
                            $remainder = $totalCells % 7;
                            if ($remainder > 0) {
                                $cellsToAdd = 7 - $remainder;
                                for ($i = 0; $i < $cellsToAdd; $i++) {
                                    echo '<div class="border-r border-b border-gray-100 bg-gray-50/30"></div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($role === 'admin'): ?>
        <!-- Kanan: Piket Hari Ini -->
        <div class="lg:col-span-1 flex flex-col gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-800">Piket Hari Ini</h3>
                    <span class="text-xs font-medium text-gray-500 bg-white px-2 py-1 rounded-md border border-gray-200"><?= $todayDay ?></span>
                </div>
                <div class="divide-y divide-gray-100 flex-1 overflow-y-auto min-h-0">
                    <!-- Syeikh Diwan -->
                    <div class="p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-indigo-50 rounded-lg p-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <h4 class="text-xs font-bold text-gray-900">Syeikh Diwan</h4>
                        </div>
                        <div class="pl-10">
                            <?php if (empty($piketSyeikh)): ?>
                                <p class="text-[10px] text-gray-400 italic">Tidak ada jadwal.</p>
                            <?php else: ?>
                                <ul class="space-y-1">
                                    <?php foreach ($piketSyeikh as $name): ?>
                                    <li class="flex items-center text-[11px] font-medium text-gray-700">
                                        <span class="w-1 h-1 bg-indigo-400 rounded-full mr-1.5"></span>
                                        <?= htmlspecialchars($name) ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Piket Keliling -->
                    <div class="p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-teal-50 rounded-lg p-2">
                                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <h4 class="text-xs font-bold text-gray-900">Piket Keliling</h4>
                        </div>
                        <div class="pl-10">
                            <?php if (empty($piketKeliling)): ?>
                                <p class="text-[10px] text-gray-400 italic">Tidak ada jadwal.</p>
                            <?php else: ?>
                                <ul class="space-y-1">
                                    <?php foreach ($piketKeliling as $name): ?>
                                    <li class="flex items-center text-[11px] font-medium text-gray-700">
                                        <span class="w-1 h-1 bg-teal-400 rounded-full mr-1.5"></span>
                                        <?= htmlspecialchars($name) ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 p-2 text-center border-t border-gray-100">
                    <a href="<?= url('/piket/office') ?>" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-bold inline-block">Kelola Jadwal &rarr;</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>

    <!-- Sisanya: Piket, Absensi, Tanqih, Koreksi -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Absensi Siswa -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-pink-50 rounded-lg p-2">
                    <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 flex-1">Absensi Siswa</h3>
            </div>
            <div class="grid grid-cols-2 gap-3 text-center mb-4 flex-1">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100 flex flex-col justify-center">
                    <div class="text-2xl font-bold text-gray-900"><?= $studentAbsensiStats['hadir'] ?></div>
                    <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-1">Hadir</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100 flex flex-col justify-center">
                    <div class="text-2xl font-bold text-gray-900"><?= $studentAbsensiStats['tidak_hadir'] ?></div>
                    <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-1">Absen</div>
                </div>
            </div>
            <a href="<?= url('/student-attendance') ?>" class="text-xs text-pink-600 hover:text-pink-800 font-medium text-center">Lihat Laporan Lengkap &rarr;</a>
        </div>

        <!-- Absensi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-green-50 rounded-lg p-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 flex-1">Absensi Pengajar</h3>
            </div>
            <div class="grid grid-cols-2 gap-3 text-center mb-4 flex-1">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100 flex flex-col justify-center">
                    <div class="text-2xl font-bold text-gray-900"><?= $absensiStats['hadir'] ?></div>
                    <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-1">Hadir</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100 flex flex-col justify-center">
                    <div class="text-2xl font-bold text-gray-900"><?= $absensiStats['tidak_hadir'] ?></div>
                    <div class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-1">Absen</div>
                </div>
            </div>
            <a href="<?= url('/attendance/report') ?>" class="text-xs text-green-600 hover:text-green-800 font-medium text-center">Lihat Laporan Lengkap &rarr;</a>
        </div>

        <!-- Tanqih -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-50 rounded-lg p-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Tanqih Idad</h3>
                </div>
                <span class="text-sm font-bold text-blue-600"><?= $attendancePercent ?>%</span>
            </div>
            <div class="flex-1 flex flex-col justify-center">
                <div class="w-full bg-gray-100 rounded-full h-3 mb-3">
                    <div class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: <?= $attendancePercent ?>%"></div>
                </div>
                <p class="text-xs text-gray-500 mb-4 text-center">
                    <strong><?= $verifiedCount ?></strong> dari <strong><?= $totalSlotsToday ?></strong> jadwal terverifikasi.
                </p>
            </div>
            <a href="<?= url('/tanqih') ?>" class="text-xs text-blue-600 hover:text-blue-800 font-medium text-center"><?= ($role === 'admin' || $role === 'pengajar') ? 'Buka Tanqih' : 'Lihat Data' ?> &rarr;</a>
        </div>

        <!-- Koreksi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-purple-50 rounded-lg p-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Koreksi Ujian</h3>
                </div>
                <span class="text-sm font-bold text-purple-600"><?= $correctionPercent ?>%</span>
            </div>
            <div class="flex-1 flex flex-col justify-center">
                <div class="w-full bg-gray-100 rounded-full h-3 mb-3">
                    <div class="bg-purple-500 h-3 rounded-full transition-all duration-500" style="width: <?= $correctionPercent ?>%"></div>
                </div>
                <p class="text-xs text-gray-500 mb-4 text-center">
                    <strong><?= $finishedKoreksi ?></strong> dari <strong><?= $totalKoreksi ?></strong> mapel selesai.
                </p>
            </div>
            <a href="<?= url('/grades') ?>" class="text-xs text-purple-600 hover:text-purple-800 font-medium text-center">Lihat Progres &rarr;</a>
        </div>
        
    </div>

</main>

<script>
function loadCalendar(month, year) {
    const calendarWrapper = document.getElementById('calendar-wrapper');
    if (!calendarWrapper) return;
    
    // Visual feedback
    calendarWrapper.style.opacity = '0.5';
    calendarWrapper.style.pointerEvents = 'none';
    
    fetch(`<?= url('/') ?>?month=${month}&year=${year}`)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newCalendar = doc.getElementById('calendar-wrapper');
            if (newCalendar) {
                calendarWrapper.innerHTML = newCalendar.innerHTML;
                calendarWrapper.setAttribute('data-month', month);
                calendarWrapper.setAttribute('data-year', year);
            }
            calendarWrapper.style.opacity = '1';
            calendarWrapper.style.pointerEvents = 'auto';
        })
        .catch(err => {
            console.error('Error loading calendar:', err);
            calendarWrapper.style.opacity = '1';
            calendarWrapper.style.pointerEvents = 'auto';
        });
}
</script>
<?php renderFooter(); ?>
