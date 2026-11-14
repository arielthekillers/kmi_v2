<?php 
$title = "Runner — Virtual Run & Kebugaran Guru";
renderHeader($title); 

function runner_format_hms($seconds) {
    if (!$seconds || $seconds <= 0) return '00:00:00';
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return sprintf('%02d:%02d:%02d', $h, $m, $s);
}

function runner_format_pace($seconds) {
    if (!$seconds || $seconds <= 0) return '-';
    $m = floor($seconds / 60);
    $s = $seconds % 60;
    return sprintf('%02d:%02d', $m, $s);
}
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Top Bar: Header & Event Dropdown Selector -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">LEADERBOARD & KEDISIPLINAN</div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight mt-0.5">Statistik Event</h1>
            <p class="text-xs text-gray-500 mt-1">Pilih event untuk melihat peringkat jarak, akumulasi lari, dan catatan aktivitas.</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative min-w-[280px]">
                <label for="event_selector" class="sr-only">Pilih Event</label>
                <select id="event_selector" onchange="changeRunnerEvent(this.value)" 
                        class="w-full pl-4 pr-10 py-2.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all cursor-pointer outline-none">
                    <?php if (empty($events)): ?>
                        <option value="">Belum ada event lari aktif</option>
                    <?php else: ?>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= ($eventId == $ev['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['name']) ?> (Target: <?= (float)$ev['target_km'] ?>K)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <?php if ($isAdmin): ?>
                <button type="button" onclick="openModal('modal-add-event')" 
                        class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                    <i class="ri-add-line text-sm"></i> Event Baru
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 gap-2">
        <button type="button" onclick="switchRunnerTab('leaderboard')" id="tab-btn-leaderboard"
                class="tab-btn pb-3 px-4 text-sm font-semibold border-b-2 transition-all flex items-center gap-2 <?= ($activeTab === 'leaderboard') ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
            <i class="ri-trophy-line text-base"></i> Leaderboard
        </button>

        <button type="button" onclick="switchRunnerTab('my_activities')" id="tab-btn-my_activities"
                class="tab-btn pb-3 px-4 text-sm font-semibold border-b-2 transition-all flex items-center gap-2 <?= ($activeTab === 'my_activities') ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
            <i class="ri-run-line text-base"></i> Aktivitas Saya
            <?php if (!empty($myActivities)): ?>
                <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-emerald-100 text-emerald-700 rounded-full font-bold">
                    <?= count($myActivities) ?>
                </span>
            <?php endif; ?>
        </button>

        <?php if ($isAdmin): ?>
            <button type="button" onclick="switchRunnerTab('admin_panel')" id="tab-btn-admin_panel"
                    class="tab-btn pb-3 px-4 text-sm font-semibold border-b-2 transition-all flex items-center gap-2 <?= ($activeTab === 'admin_panel') ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
                <i class="ri-shield-check-line text-base"></i> Kelola & Validasi
                <?php if (!empty($pendingActivities) && count($pendingActivities) > 0): ?>
                    <span class="ml-1 px-2 py-0.5 text-[10px] bg-amber-500 text-white rounded-full font-extrabold animate-pulse">
                        <?= count($pendingActivities) ?> Pending
                    </span>
                <?php endif; ?>
            </button>
        <?php endif; ?>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: LEADERBOARD & STATISTIK EVENT                      -->
    <!-- ======================================================== -->
    <div id="tab-content-leaderboard" class="tab-pane <?= ($activeTab === 'leaderboard') ? '' : 'hidden' ?> space-y-6">
        
        <?php if ($event): ?>
            <!-- Event Hero / Summary Card (matches Screenshot 1) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 relative overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase tracking-wider">
                                <?= htmlspecialchars($event['mode']) ?>
                            </span>
                            <?php if (!empty($event['group_name'])): ?>
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-gray-100 text-gray-600">
                                    <?= htmlspecialchars($event['group_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">
                            <?= htmlspecialchars($event['name']) ?>
                        </h2>
                        <p class="text-xs text-gray-500 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span><i class="ri-calendar-line mr-1 text-gray-400"></i><?= date('d M Y', strtotime($event['start_date'])) ?> – <?= date('d M Y', strtotime($event['end_date'])) ?></span>
                            <span>•</span>
                            <span><i class="ri-flag-2-line mr-1 text-gray-400"></i>Target: <strong><?= (float)$event['target_km'] ?> KM</strong></span>
                            <span>•</span>
                            <span><i class="ri-medal-line mr-1 text-emerald-500"></i>Finisher: <strong class="text-emerald-600"><?= (int)$event['finisher_count'] ?> orang</strong></span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <?php if ($myParticipant): ?>
                            <div class="text-right">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                                    <i class="ri-checkbox-circle-fill text-emerald-600"></i> Anda Terdaftar
                                    <?= $myParticipant['bib_number'] ? '(BIB: ' . htmlspecialchars($myParticipant['bib_number']) . ')' : '' ?>
                                </span>
                                <div class="text-[11px] text-gray-400 mt-1">
                                    Total Lari: <strong class="text-gray-700"><?= number_format($myParticipant['total_km'], 2) ?> KM</strong>
                                </div>
                            </div>
                        <?php else: ?>
                            <button type="button" onclick="openJoinModal(<?= $event['id'] ?>, '<?= htmlspecialchars($event['name'], ENT_QUOTES) ?>', <?= (float)$event['cost'] ?>)"
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                                <i class="ri-user-add-line"></i> Gabung Event Ini
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="ri-bar-chart-grouped-line text-emerald-600"></i>
                        Peringkat Peserta Terdaftar (<?= count($leaderboard) ?> Runner)
                    </h3>
                    <span class="text-[11px] text-gray-400 italic">*Hanya aktivitas yang telah disetujui (Approved) yang dihitung ke Total KM</span>
                </div>

                <?php if (empty($leaderboard)): ?>
                    <div class="p-12 text-center text-gray-400">
                        <i class="ri-run-line text-5xl text-gray-300 block mb-2"></i>
                        <p class="text-sm font-medium">Belum ada peserta yang bergabung di event ini.</p>
                        <p class="text-xs text-gray-400 mt-1">Jadilah yang pertama bergabung dan catat lari Anda!</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                    <th class="py-3.5 px-4 text-center w-12">#</th>
                                    <th class="py-3.5 px-6">RUNNER</th>
                                    <th class="py-3.5 px-4 text-center">BIB</th>
                                    <th class="py-3.5 px-6 text-right">TOTAL KM</th>
                                    <th class="py-3.5 px-6 text-center">FINISH TARGET</th>
                                    <th class="py-3.5 px-6 text-right">TOTAL DURASI</th>
                                    <th class="py-3.5 px-4 text-center">AKTIVITAS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <?php 
                                $rank = 1;
                                foreach ($leaderboard as $row): 
                                    $isMe = ($row['user_id'] == $user['id']);
                                    $targetKm = (float)($event['target_km'] ?? 42.0);
                                    $percent = ($targetKm > 0) ? min(100, round(($row['total_km'] / $targetKm) * 100, 1)) : 0;
                                ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors <?= $isMe ? 'bg-emerald-50/40' : '' ?>">
                                        <!-- Rank -->
                                        <td class="py-4 px-4 text-center font-bold text-gray-500">
                                            <?php if ($rank === 1): ?>
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 text-xs font-black shadow-sm">1</span>
                                            <?php elseif ($rank === 2): ?>
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-700 text-xs font-black shadow-sm">2</span>
                                            <?php elseif ($rank === 3): ?>
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-50 text-amber-800 text-xs font-black shadow-sm">3</span>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs"><?= $rank ?></span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Runner Profile -->
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 text-white font-bold flex items-center justify-center text-sm shadow-sm flex-shrink-0 overflow-hidden ring-2 ring-white">
                                                    <?php if (!empty($row['profile_picture']) && file_exists('public/uploads/profile/' . $row['profile_picture'])): ?>
                                                        <img src="<?= url('/uploads/profile/' . $row['profile_picture']) ?>" alt="" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <?= mb_strtoupper(mb_substr($row['nama'], 0, 1)) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900 leading-snug flex items-center gap-1.5">
                                                        <?= htmlspecialchars($row['nama']) ?>
                                                        <?php if ($isMe): ?>
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-800 font-bold">Saya</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-xs text-gray-400">
                                                        @<?= htmlspecialchars($row['username'] ?? $row['nip'] ?? '-') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- BIB -->
                                        <td class="py-4 px-4 text-center text-xs font-mono font-medium text-gray-500">
                                            <?= htmlspecialchars($row['bib_number'] ?? '-') ?>
                                        </td>

                                        <!-- Total KM -->
                                        <td class="py-4 px-6 text-right font-extrabold text-base text-gray-900 tracking-tight">
                                            <?= number_format($row['total_km'], 2) ?>
                                            <span class="text-xs font-normal text-gray-400">KM</span>
                                        </td>

                                        <!-- Finish Target -->
                                        <td class="py-4 px-6 text-center">
                                            <?php if (!empty($row['is_finisher'])): ?>
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black shadow-sm">
                                                        #<?= $row['finisher_rank'] ?>
                                                    </span>
                                                    <span class="text-[10px] font-semibold text-emerald-600 mt-1">
                                                        Finish <?= !empty($row['finished_at']) ? date('d M Y', strtotime($row['finished_at'])) : '' ?>
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-32 mx-auto">
                                                    <div class="flex justify-between text-[10px] text-gray-400 font-bold mb-1">
                                                        <span><?= $percent ?>%</span>
                                                        <span><?= (float)$row['total_km'] ?> / <?= $targetKm ?>K</span>
                                                    </div>
                                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: <?= $percent ?>%"></div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Total Duration -->
                                        <td class="py-4 px-6 text-right font-mono text-xs text-gray-600 font-medium">
                                            <?= runner_format_hms($row['total_duration_seconds']) ?>
                                        </td>

                                        <!-- Total Activity -->
                                        <td class="py-4 px-4 text-center">
                                            <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-bold font-mono">
                                                <?= (int)$row['activity_count'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php 
                                    $rank++;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center text-gray-400">
                <i class="ri-calendar-event-line text-5xl text-gray-300 block mb-2"></i>
                <p class="text-base font-bold text-gray-700">Belum ada event lari yang dipilih atau aktif.</p>
                <p class="text-xs text-gray-400 mt-1">Silakan pilih event lain dari menu atas atau hubungi Admin untuk membuat event baru.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: AKTIVITAS SAYA (Input Manual & Riwayat Lari Guru)  -->
    <!-- ======================================================== -->
    <div id="tab-content-my_activities" class="tab-pane <?= ($activeTab === 'my_activities') ? '' : 'hidden' ?> space-y-6">

        <?php if (!$event): ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center text-gray-400">
                <p class="text-sm font-semibold text-gray-600">Pilih event lari terlebih dahulu.</p>
            </div>
        <?php elseif (!$myParticipant): ?>
            <!-- Not Registered Prompt -->
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-8 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <span class="px-2.5 py-1 rounded-full bg-white/20 text-white text-[11px] font-bold uppercase tracking-wider inline-block mb-2">
                        Pendaftaran Event
                    </span>
                    <h3 class="text-2xl font-black tracking-tight"><?= htmlspecialchars($event['name']) ?></h3>
                    <p class="text-emerald-100 text-xs mt-1 max-w-xl">
                        Anda belum terdaftar dalam event lari ini. Klik tombol di samping untuk bergabung, agar setiap kilometer lari yang Anda setor tercatat di papan peringkat!
                    </p>
                </div>
                <button type="button" onclick="openJoinModal(<?= $event['id'] ?>, '<?= htmlspecialchars($event['name'], ENT_QUOTES) ?>', <?= (float)$event['cost'] ?>)"
                        class="px-6 py-3 bg-white text-emerald-800 hover:bg-emerald-50 rounded-xl font-bold text-sm shadow-md transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="ri-user-add-line text-base"></i> Gabung Event Ini Sekarang
                </button>
            </div>
        <?php else: ?>
            <!-- Event Hero Banner (Matches Screenshot 2) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase tracking-wider">
                                <?= htmlspecialchars($event['mode']) ?>
                            </span>
                            <?php if ($myParticipant['is_finisher']): ?>
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-amber-100 text-amber-800">
                                    🏆 Finisher #<?= $myParticipant['finisher_rank'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">
                            <?= htmlspecialchars($event['name']) ?>
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            Metode input: <strong class="text-gray-700"><?= htmlspecialchars($event['mode']) ?></strong> · Periode: <strong><?= date('d M Y', strtotime($event['start_date'])) ?> – <?= date('d M Y', strtotime($event['end_date'])) ?></strong>
                        </p>
                        <div class="flex items-center gap-2 mt-2">
                            <?php if ($event['cost'] > 0): ?>
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-md bg-gray-100 text-gray-700">
                                    Biaya Rp <?= number_format($event['cost'], 0, ',', '.') ?>
                                </span>
                            <?php endif; ?>
                            <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-md bg-emerald-100 text-emerald-800">
                                TERDAFTAR <?= $myParticipant['bib_number'] ? '(BIB: ' . htmlspecialchars($myParticipant['bib_number']) . ')' : '' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Personal Cumulative Box -->
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 min-w-[220px]">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Progres Lari Saya</div>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-2xl font-black text-emerald-600"><?= number_format($myParticipant['total_km'], 2) ?></span>
                            <span class="text-xs text-gray-400 font-semibold">/ <?= (float)$event['target_km'] ?> KM</span>
                        </div>
                        <?php 
                            $myPercent = ($event['target_km'] > 0) ? min(100, round(($myParticipant['total_km'] / $event['target_km']) * 100, 1)) : 0;
                        ?>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= $myPercent ?>%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-500 font-semibold mt-1">
                            <span><?= $myPercent ?>% tercapai</span>
                            <span><?= runner_format_hms($myParticipant['total_duration_seconds']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Form: Input Aktivitas Manual (Matches Screenshot 2) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-5">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="ri-add-circle-line text-emerald-600 text-base"></i> Input Aktivitas Manual
                    </h3>
                    <span class="text-xs text-gray-400">Wajib sertakan link Strava untuk validasi admin</span>
                </div>

                <form action="<?= url('/ekskul/runner/activity/store') ?>" method="POST" id="form-submit-activity" class="space-y-4">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Tanggal -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal</label>
                            <input type="date" name="activity_date" value="<?= date('Y-m-d') ?>" required
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all">
                        </div>

                        <!-- Jarak (KM) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jarak (KM) *</label>
                            <input type="number" step="0.01" min="0.01" name="distance_km" id="act_dist" placeholder="contoh: 5.25" required
                                   oninput="calculatePace('act_dist', 'act_dur', 'act_pace')"
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all">
                        </div>

                        <!-- Durasi (HH:MM:SS) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Durasi (HH:MM:SS) *</label>
                            <input type="text" name="duration" id="act_dur" placeholder="00:35:00" required
                                   oninput="calculatePace('act_dist', 'act_dur', 'act_pace')"
                                   pattern="([0-9]{1,2}:)?[0-9]{2}:[0-9]{2}"
                                   title="Format: HH:MM:SS atau MM:SS (contoh 01:05:30 atau 45:00)"
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium font-mono focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all">
                            <span class="text-[10px] text-gray-400 mt-0.5 block">Format: HH:MM:SS (contoh: 00:45:00 untuk 45 menit)</span>
                        </div>

                        <!-- Pace (auto calculated) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pace (otomatis dihitung, MM:SS /km)</label>
                            <input type="text" name="pace" id="act_pace" placeholder="06:30"
                                   class="w-full px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm font-medium font-mono text-gray-700 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="text-[10px] text-gray-400 mt-0.5 block">Terisi otomatis dari perbandingan jarak dan durasi</span>
                        </div>

                        <!-- Tipe Aktivitas -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tipe Aktivitas</label>
                            <select name="activity_type" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                                <option value="Run">Run (Lari Luar Ruang)</option>
                                <option value="Walk">Walk (Jalan Sehat)</option>
                                <option value="Treadmill">Treadmill</option>
                            </select>
                        </div>

                        <!-- Kenaikan / Elevation (m) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kenaikan / Elevation Gain (m)</label>
                            <input type="number" name="elevation_gain_m" value="0" min="0" placeholder="0"
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                        </div>
                    </div>

                    <!-- Link Strava (Crucial for Admin Validation) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Link Strava (Sangat Direkomendasikan) *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                                <i class="ri-external-link-line"></i>
                            </div>
                            <input type="url" name="strava_url" placeholder="https://www.strava.com/activities/1234567890"
                                   class="w-full pl-10 pr-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all">
                        </div>
                        <span class="text-[11px] text-gray-400 mt-1 block">
                            Salin URL aktivitas dari aplikasi / web Strava Anda agar Admin dapat memvalidasi keaslian lari dengan 1 klik.
                        </span>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan (opsional)</label>
                        <input type="text" name="notes" placeholder="Rute lari, kondisi fisik, dsb."
                               class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                            <i class="ri-save-line"></i> Simpan Aktivitas
                        </button>
                    </div>
                </form>
            </div>

            <!-- List: Aktivitas Terbaru (Matches Screenshot 2) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="ri-history-line text-emerald-600"></i> Aktivitas Terbaru
                    </h3>
                    <span class="text-xs text-gray-400"><?= count($myActivities) ?> catatan tersimpan</span>
                </div>

                <?php if (empty($myActivities)): ?>
                    <div class="p-8 text-center text-gray-400">
                        <i class="ri-file-list-line text-4xl text-gray-300 block mb-2"></i>
                        <p class="text-xs font-medium">Belum ada aktivitas lari yang disetor.</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Gunakan formulir di atas untuk mencatat lari perdana Anda!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($myActivities as $act): ?>
                            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-gray-200 hover:shadow-sm transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-bold text-gray-900 text-sm">
                                            <?= htmlspecialchars($act['activity_type']) ?> · <?= number_format($act['distance_km'], 2) ?> KM
                                        </span>

                                        <!-- Status Badge -->
                                        <?php if ($act['status'] === 'approved'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                                <i class="ri-checkbox-circle-fill"></i> Disetujui
                                            </span>
                                        <?php elseif ($act['status'] === 'rejected'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 flex items-center gap-1" title="<?= htmlspecialchars($act['rejection_reason'] ?? 'Tidak memenuhi syarat') ?>">
                                                <i class="ri-close-circle-fill"></i> Ditolak
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 flex items-center gap-1">
                                                <i class="ri-time-line"></i> Menunggu Verifikasi
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="text-xs text-gray-500 mt-1 flex flex-wrap items-center gap-x-2">
                                        <span><?= date('Y-m-d', strtotime($act['activity_date'])) ?></span>
                                        <span>|</span>
                                        <span>Durasi: <strong><?= runner_format_hms($act['duration_seconds']) ?></strong></span>
                                        <span>|</span>
                                        <span>Pace: <strong><?= runner_format_pace($act['pace_seconds']) ?>/km</strong></span>
                                        <?php if ($act['elevation_gain_m'] > 0): ?>
                                            <span>|</span>
                                            <span>Elev: <?= (int)$act['elevation_gain_m'] ?>m</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($act['notes'])): ?>
                                        <p class="text-[11px] text-gray-400 italic mt-1"><?= htmlspecialchars($act['notes']) ?></p>
                                    <?php endif; ?>

                                    <?php if ($act['status'] === 'rejected' && !empty($act['rejection_reason'])): ?>
                                        <p class="text-[11px] text-rose-600 font-semibold mt-1">Alasan ditolak: <?= htmlspecialchars($act['rejection_reason']) ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center gap-2 self-end sm:self-center">
                                    <?php if (!empty($act['strava_url'])): ?>
                                        <a href="<?= htmlspecialchars($act['strava_url']) ?>" target="_blank" 
                                           class="p-2 text-orange-600 hover:text-orange-700 hover:bg-orange-50 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors" title="Lihat di Strava">
                                            <i class="ri-external-link-line text-sm"></i> Strava
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($act['status'] === 'pending'): ?>
                                        <button type="button" onclick='openEditActivityModal(<?= json_encode($act) ?>)'
                                                class="p-2 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors" title="Edit Data Lari">
                                            <i class="ri-edit-line text-sm"></i>
                                        </button>

                                        <form action="<?= url('/ekskul/runner/activity/delete') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan lari ini?');" class="inline">
                                            <input type="hidden" name="activity_id" value="<?= $act['id'] ?>">
                                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                            <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                                <i class="ri-delete-bin-line text-sm"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: KELOLA & VALIDASI (Khusus Admin)                    -->
    <!-- ======================================================== -->
    <?php if ($isAdmin): ?>
    <div id="tab-content-admin_panel" class="tab-pane <?= ($activeTab === 'admin_panel') ? '' : 'hidden' ?> space-y-6">

        <!-- 3A: Validasi Aktivitas Guru (Pending Queue) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="ri-checkbox-multiple-line text-amber-500"></i>
                        Antrean Verifikasi Lari Guru
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Admin mengecek keabsahan rute & jarak via link Strava sebelum disetujui</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold <?= (count($pendingActivities) > 0) ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' ?>">
                    <?= count($pendingActivities) ?> Menunggu
                </span>
            </div>

            <?php if (empty($pendingActivities)): ?>
                <div class="p-10 text-center text-gray-400">
                    <i class="ri-shield-check-line text-5xl text-emerald-400 block mb-2"></i>
                    <p class="text-sm font-bold text-gray-700">Semua aktivitas lari telah divalidasi!</p>
                    <p class="text-xs text-gray-400 mt-1">Tidak ada antrean aktivitas yang menunggu persetujuan saat ini.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3 px-4">GURU / RUNNER</th>
                                <th class="py-3 px-4">EVENT</th>
                                <th class="py-3 px-4">TANGGAL</th>
                                <th class="py-3 px-4 text-right">JARAK</th>
                                <th class="py-3 px-4 text-center">DURASI / PACE</th>
                                <th class="py-3 px-4 text-center">LINK STRAVA</th>
                                <th class="py-3 px-4 text-center">AKSI VALIDASI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            <?php foreach ($pendingActivities as $pAct): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-gray-900">
                                        <?= htmlspecialchars($pAct['nama']) ?>
                                        <div class="text-[10px] text-gray-400 font-normal">
                                            @<?= htmlspecialchars($pAct['username'] ?? $pAct['nip'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-gray-600">
                                        <?= htmlspecialchars($pAct['event_name']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600">
                                        <?= date('d M Y', strtotime($pAct['activity_date'])) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-emerald-600 font-mono text-sm">
                                        <?= number_format($pAct['distance_km'], 2) ?> KM
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono text-gray-600">
                                        <?= runner_format_hms($pAct['duration_seconds']) ?>
                                        <div class="text-[10px] text-gray-400">Pace: <?= runner_format_pace($pAct['pace_seconds']) ?>/km</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if (!empty($pAct['strava_url'])): ?>
                                            <a href="<?= htmlspecialchars($pAct['strava_url']) ?>" target="_blank" 
                                               class="inline-flex items-center gap-1 px-3 py-1 bg-orange-50 hover:bg-orange-100 text-orange-600 rounded-lg font-bold text-xs transition-colors">
                                                <i class="ri-external-link-line"></i> Buka Strava ↗
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <!-- One-Click Approve Form -->
                                            <form action="<?= url('/ekskul/runner/activity/verify') ?>" method="POST" class="inline">
                                                <input type="hidden" name="activity_id" value="<?= $pAct['id'] ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                                <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs transition-colors shadow-sm" title="Setujui Lari">
                                                    <i class="ri-check-line"></i> Setujui
                                                </button>
                                            </form>

                                            <!-- Reject with Reason Button (triggers modal) -->
                                            <button type="button" onclick="openRejectModal(<?= $pAct['id'] ?>, '<?= htmlspecialchars($pAct['nama'], ENT_QUOTES) ?>', '<?= number_format($pAct['distance_km'], 2) ?> KM')"
                                                    class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg font-bold text-xs transition-colors" title="Tolak">
                                                <i class="ri-close-line"></i> Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3B: Kelola Master Event (Matches Screenshot 3) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="ri-calendar-event-line text-emerald-600"></i>
                        Daftar Event Lari (Master Event)
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Kelola event, periode tanggal, target KM, dan mode input</p>
                </div>
                <button type="button" onclick="openModal('modal-add-event')" 
                        class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                    <i class="ri-add-line text-sm"></i> Tambah Event Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                            <th class="py-3 px-6">NAMA</th>
                            <th class="py-3 px-4">GRUP</th>
                            <th class="py-3 px-4">PERIODE</th>
                            <th class="py-3 px-4 text-center">MODE</th>
                            <th class="py-3 px-4 text-center">BIAYA</th>
                            <th class="py-3 px-4 text-center">TIM</th>
                            <th class="py-3 px-4 text-center">STATUS</th>
                            <th class="py-3 px-6 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        <?php foreach ($allEvents as $ev): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-4 px-6 font-bold text-gray-900">
                                    <div class="text-sm font-bold"><?= htmlspecialchars($ev['name']) ?></div>
                                    <div class="text-[10px] text-gray-400 font-normal">
                                        Target: <strong><?= (float)$ev['target_km'] ?> KM</strong> · Peserta: <strong><?= (int)$ev['participant_count'] ?></strong> · Finisher: <strong><?= (int)$ev['finisher_count'] ?></strong>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-gray-600 font-medium">
                                    <?= htmlspecialchars($ev['group_name'] ?? 'Umum') ?>
                                </td>
                                <td class="py-4 px-4 font-mono text-gray-500 whitespace-nowrap">
                                    <?= $ev['start_date'] ?> – <br><?= $ev['end_date'] ?>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-gray-700">
                                    <?= htmlspecialchars($ev['mode']) ?>
                                </td>
                                <td class="py-4 px-4 text-center font-semibold text-gray-600">
                                    <?= ($ev['cost'] > 0) ? 'Rp ' . number_format($ev['cost'], 0, ',', '.') : 'Gratis' ?>
                                </td>
                                <td class="py-4 px-4 text-center text-gray-600">
                                    <?= ucfirst($ev['team_mode'] ?? 'Solo') ?>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <?php if ($ev['status'] === 'ACTIVE'): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">ACTIVE</span>
                                    <?php elseif ($ev['status'] === 'DRAFT'): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">DRAFT</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800"><?= $ev['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" onclick='openEditEventModal(<?= json_encode($ev) ?>)'
                                                class="text-emerald-600 hover:text-emerald-800 font-bold transition-colors">
                                            Edit
                                        </button>

                                        <form action="<?= url('/ekskul/runner/events/delete') ?>" method="POST" onsubmit="return confirm('Hapus event lari ini beserta seluruh data pesertanya?');" class="inline">
                                            <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <?php endif; ?>

</main>

<!-- ======================================================== -->
<!-- MODAL: GABUNG EVENT LARI                                 -->
<!-- ======================================================== -->
<div id="modal-join-event" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative animate-fade-in">
        <button type="button" onclick="closeModal('modal-join-event')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="ri-close-line text-xl"></i>
        </button>

        <div class="text-center mb-5">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-2xl font-bold">
                <i class="ri-run-line"></i>
            </div>
            <h3 class="text-lg font-extrabold text-gray-900">Gabung Event Lari</h3>
            <p id="join-event-title" class="text-xs text-gray-500 mt-1 font-semibold"></p>
        </div>

        <form action="<?= url('/ekskul/runner/join') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="event_id" id="join-event-id" value="">

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor BIB / Dada (opsional)</label>
                <input type="text" name="bib_number" placeholder="contoh: 087"
                       class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div id="join-cost-container" class="hidden">
                <label class="block text-xs font-bold text-gray-700 mb-1">Bukti Transfer Biaya (opsional)</label>
                <input type="file" name="payment_proof" accept="image/*"
                       class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md transition-all">
                    Konfirmasi Gabung Event
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: EDIT AKTIVITAS LARI                               -->
<!-- ======================================================== -->
<div id="modal-edit-activity" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative animate-fade-in max-h-[90vh] overflow-y-auto">
        <button type="button" onclick="closeModal('modal-edit-activity')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="ri-close-line text-xl"></i>
        </button>

        <h3 class="text-base font-extrabold text-gray-900 mb-4 flex items-center gap-2">
            <i class="ri-edit-line text-emerald-600"></i> Edit Catatan Lari
        </h3>

        <form action="<?= url('/ekskul/runner/activity/update') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="activity_id" id="edit_act_id">
            <input type="hidden" name="event_id" value="<?= $eventId ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="activity_date" id="edit_act_date" required
                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jarak (KM)</label>
                    <input type="number" step="0.01" min="0.01" name="distance_km" id="edit_act_dist" required
                           oninput="calculatePace('edit_act_dist', 'edit_act_dur', 'edit_act_pace')"
                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Durasi (HH:MM:SS)</label>
                    <input type="text" name="duration" id="edit_act_dur" required
                           oninput="calculatePace('edit_act_dist', 'edit_act_dur', 'edit_act_pace')"
                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Pace (MM:SS /km)</label>
                    <input type="text" name="pace" id="edit_act_pace"
                           class="w-full px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm font-medium font-mono text-gray-700 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tipe Aktivitas</label>
                    <select name="activity_type" id="edit_act_type" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="Run">Run</option>
                        <option value="Walk">Walk</option>
                        <option value="Treadmill">Treadmill</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kenaikan (m)</label>
                    <input type="number" name="elevation_gain_m" id="edit_act_elev" value="0" min="0"
                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Link Strava</label>
                <input type="url" name="strava_url" id="edit_act_strava" placeholder="https://www.strava.com/activities/..."
                       class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan</label>
                <input type="text" name="notes" id="edit_act_notes"
                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: TOLAK AKTIVITAS DENGAN ALASAN (Admin)            -->
<!-- ======================================================== -->
<div id="modal-reject-activity" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative animate-fade-in">
        <button type="button" onclick="closeModal('modal-reject-activity')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="ri-close-line text-xl"></i>
        </button>

        <div class="mb-4">
            <h3 class="text-base font-extrabold text-gray-900">Tolak Aktivitas Lari</h3>
            <p id="reject-subinfo" class="text-xs text-gray-500 mt-1"></p>
        </div>

        <form action="<?= url('/ekskul/runner/activity/verify') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="activity_id" id="reject_activity_id" value="">
            <input type="hidden" name="status" value="rejected">
            <input type="hidden" name="event_id" value="<?= $eventId ?>">

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Penolakan *</label>
                <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Link Strava tidak dapat diakses, atau durasi tidak sesuai dengan rute Strava..."
                          class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <button type="button" onclick="closeModal('modal-reject-activity')" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                    Konfirmasi Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: TAMBAH / EDIT MASTER EVENT (Admin)                -->
<!-- ======================================================== -->
<div id="modal-add-event" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl relative animate-fade-in max-h-[90vh] overflow-y-auto">
        <button type="button" onclick="closeModal('modal-add-event')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="ri-close-line text-xl"></i>
        </button>

        <h3 id="modal-event-title" class="text-base font-extrabold text-gray-900 mb-4 flex items-center gap-2">
            <i class="ri-calendar-event-line text-emerald-600"></i> Tambah Event Lari Baru
        </h3>

        <form action="<?= url('/ekskul/runner/events/store') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="event_form_id" value="">

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Event *</label>
                <input type="text" name="name" id="event_form_name" required placeholder="Contoh: Blitza Virtual Run 42K (Multiple Run) 2026"
                       class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Grup Kategori</label>
                    <select name="group_id" id="event_form_group" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">Pilih Grup...</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Atau Buat Grup Baru</label>
                    <input type="text" name="new_group_name" placeholder="Nama grup baru..."
                           class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Mulai *</label>
                    <input type="date" name="start_date" id="event_form_start" value="<?= date('Y-m-01') ?>" required
                           class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Berakhir *</label>
                    <input type="date" name="end_date" id="event_form_end" value="<?= date('Y-m-t') ?>" required
                           class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Target Jarak (KM) *</label>
                    <input type="number" step="0.1" name="target_km" id="event_form_target" value="42.0" required
                           class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Mode Input</label>
                    <select name="mode" id="event_form_mode" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="MANUAL">MANUAL (Setor Mandiri via Form)</option>
                        <option value="HYBRID">HYBRID</option>
                        <option value="AUTO">AUTO</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Biaya Pendaftaran (Rp)</label>
                    <input type="number" name="cost" id="event_form_cost" value="0" placeholder="0 jika Gratis"
                           class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status Event</label>
                    <select name="status" id="event_form_status" class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="ACTIVE">ACTIVE (Sedang Berlangsung)</option>
                        <option value="DRAFT">DRAFT (Disembunyikan)</option>
                        <option value="CLOSED">CLOSED (Ditutup)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi & Syarat Event (opsional)</label>
                <textarea name="description" id="event_form_desc" rows="2" placeholder="Ketentuan lari, rute wajib, dsb."
                          class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md transition-all">
                    Simpan Data Event
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentTab = '<?= $activeTab ?>';
    let currentEventId = '<?= $eventId ?>';

    function switchRunnerTab(tabName) {
        currentTab = tabName;
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('border-emerald-600', 'text-emerald-600');
            el.classList.add('border-transparent', 'text-gray-500');
        });

        const targetPane = document.getElementById('tab-content-' + tabName);
        const targetBtn = document.getElementById('tab-btn-' + tabName);

        if (targetPane) targetPane.classList.remove('hidden');
        if (targetBtn) {
            targetBtn.classList.remove('border-transparent', 'text-gray-500');
            targetBtn.classList.add('border-emerald-600', 'text-emerald-600');
        }

        // Update URL state without reload
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    function changeRunnerEvent(evId) {
        if (!evId) return;
        window.location.href = '<?= url('/ekskul/runner') ?>?event_id=' + evId + '&tab=' + currentTab;
    }

    function calculatePace(distId, durId, paceId) {
        const distEl = document.getElementById(distId);
        const durEl = document.getElementById(durId);
        const paceEl = document.getElementById(paceId);

        if (!distEl || !durEl || !paceEl) return;

        const dist = parseFloat(distEl.value) || 0;
        const durStr = durEl.value.trim();

        if (dist <= 0 || !durStr) return;

        // Parse HH:MM:SS or MM:SS
        const parts = durStr.split(':').map(p => parseInt(p, 10) || 0);
        let totalSeconds = 0;
        if (parts.length === 3) {
            totalSeconds = (parts[0] * 3600) + (parts[1] * 60) + parts[2];
        } else if (parts.length === 2) {
            totalSeconds = (parts[0] * 60) + parts[1];
        }

        if (totalSeconds > 0) {
            const paceSec = Math.round(totalSeconds / dist);
            const pMin = Math.floor(paceSec / 60);
            const pSec = paceSec % 60;
            paceEl.value = String(pMin).padStart(2, '0') + ':' + String(pSec).padStart(2, '0');
        }
    }

    function openModal(id) {
        const m = document.getElementById(id);
        if (m) m.classList.remove('hidden');
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        if (m) m.classList.add('hidden');
    }

    function openJoinModal(eventId, eventName, cost) {
        document.getElementById('join-event-id').value = eventId;
        document.getElementById('join-event-title').textContent = eventName;
        const costContainer = document.getElementById('join-cost-container');
        if (cost > 0) {
            costContainer.classList.remove('hidden');
        } else {
            costContainer.classList.add('hidden');
        }
        openModal('modal-join-event');
    }

    function openRejectModal(activityId, teacherName, distance) {
        document.getElementById('reject_activity_id').value = activityId;
        document.getElementById('reject-subinfo').textContent = 'Guru: ' + teacherName + ' (' + distance + ')';
        openModal('modal-reject-activity');
    }

    function openEditActivityModal(act) {
        document.getElementById('edit_act_id').value = act.id;
        document.getElementById('edit_act_date').value = act.activity_date;
        document.getElementById('edit_act_dist').value = act.distance_km;
        
        // Format seconds to HH:MM:SS
        const s = parseInt(act.duration_seconds, 10) || 0;
        const h = String(Math.floor(s / 3600)).padStart(2, '0');
        const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
        const sec = String(s % 60).padStart(2, '0');
        document.getElementById('edit_act_dur').value = h + ':' + m + ':' + sec;

        // Pace
        const ps = parseInt(act.pace_seconds, 10) || 0;
        const pm = String(Math.floor(ps / 60)).padStart(2, '0');
        const psec = String(ps % 60).padStart(2, '0');
        document.getElementById('edit_act_pace').value = pm + ':' + psec;

        document.getElementById('edit_act_type').value = act.activity_type || 'Run';
        document.getElementById('edit_act_elev').value = act.elevation_gain_m || 0;
        document.getElementById('edit_act_strava').value = act.strava_url || '';
        document.getElementById('edit_act_notes').value = act.notes || '';

        openModal('modal-edit-activity');
    }

    function openEditEventModal(ev) {
        document.getElementById('modal-event-title').innerHTML = '<i class="ri-edit-line text-emerald-600"></i> Edit Event Lari';
        document.getElementById('event_form_id').value = ev.id;
        document.getElementById('event_form_name').value = ev.name;
        document.getElementById('event_form_group').value = ev.group_id || '';
        document.getElementById('event_form_start').value = ev.start_date;
        document.getElementById('event_form_end').value = ev.end_date;
        document.getElementById('event_form_target').value = ev.target_km;
        document.getElementById('event_form_mode').value = ev.mode;
        document.getElementById('event_form_cost').value = ev.cost;
        document.getElementById('event_form_status').value = ev.status;
        document.getElementById('event_form_desc').value = ev.description || '';
        openModal('modal-add-event');
    }
</script>

<?php renderFooter(); ?>
