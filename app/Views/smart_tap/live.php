<?php
renderHeader("Smart Tap — Live Monitoring Kehadiran");
?>

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Top Navigation & Event Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> LIVE MONITOR
                </span>
                <span class="text-xs text-gray-500">Tanggal: <?= date('d M Y', strtotime($today)) ?></span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mt-1 flex items-center gap-2">
                <i class="ri-radar-line text-indigo-600"></i> Smart Tap Realtime Monitoring
            </h1>
            <p class="text-sm text-gray-500">
                Pantau kehadiran santri & dewan pengajar saat sesi berlangsung secara langsung.
            </p>
        </div>

        <!-- Event Selector -->
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="<?= url('/smart-tap/live') ?>" class="flex items-center gap-2">
                <label for="event_id" class="text-sm font-medium text-gray-700 whitespace-nowrap">Pilih Event:</label>
                <select name="event_id" id="event_id" onchange="this.form.submit()" class="rounded-xl border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 font-medium py-2 px-3 bg-gray-50">
                    <?php if (empty($events)): ?>
                        <option value="">Tidak ada event aktif hari ini</option>
                    <?php else: ?>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= $ev['id'] == $selectedEventId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['name']) ?> (<?= substr($ev['open_time'], 0, 5) ?> - <?= substr($ev['close_time'], 0, 5) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </form>
            <?php if (!empty($events) && count($events) > 1): ?>
                <button type="button" onclick="document.getElementById('modal-reassign-logs').classList.remove('hidden')" class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-semibold rounded-xl flex items-center gap-1.5 transition shadow-sm" title="Pindahkan data presensi hari ini jika salah memilih sesi">
                    <i class="ri-arrow-left-right-line"></i> Pindahkan Log
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$liveData): ?>
        <div class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto text-3xl mb-4">
                <i class="ri-calendar-event-line"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Belum Ada Sesi / Event yang Dipilih</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mt-1">
                Silakan buat event absensi baru pada menu Master Event atau pilih event aktif hari ini untuk mulai memantau.
            </p>
            <a href="<?= url('/smart-tap/events') ?>" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition">
                <i class="ri-add-line"></i> Buka Pengaturan Event
            </a>
        </div>
    <?php else: 
        $ev = $liveData['event'];
        $stats = $liveData['stats'];
    ?>

        <!-- Event Banner & Schedule Info -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 text-white shadow-lg flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-1">
                <div class="text-indigo-300 text-xs font-semibold tracking-wider uppercase flex items-center gap-2">
                    <i class="ri-time-line"></i> Sesi Berlangsung
                </div>
                <h2 class="text-2xl font-black tracking-tight text-white"><?= htmlspecialchars($ev['name']) ?></h2>
                <p class="text-xs text-slate-300">
                    Batas Tepat Waktu: <strong class="text-emerald-400"><?= substr($ev['start_time'], 0, 5) ?></strong> &bull; 
                    Tutup Absen: <strong class="text-rose-400"><?= substr($ev['close_time'], 0, 5) ?></strong> &bull;
                    Target: <span class="capitalize"><?= $ev['target_type'] == 'all' ? 'Santri & Pengajar' : ($ev['target_type'] == 'students' ? 'Santri Saja' : 'Pengajar Saja') ?></span>
                </p>
            </div>

            <!-- Attendance Progress Ring / Bar -->
            <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md px-5 py-3 rounded-xl border border-white/10">
                <div>
                    <div class="text-xs text-slate-300">Tingkat Kehadiran</div>
                    <div class="text-3xl font-extrabold text-white" id="stat-percent"><?= $stats['percent_presence'] ?>%</div>
                </div>
                <div class="w-32 bg-white/20 rounded-full h-3 overflow-hidden">
                    <div class="bg-emerald-400 h-3 rounded-full transition-all duration-500" id="stat-progress-bar" style="width: <?= min(100, $stats['percent_presence']) ?>%"></div>
                </div>
            </div>
        </div>

        <!-- 5 Stats Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- Total Target -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="ri-group-line"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Wajib Hadir</div>
                    <div class="text-2xl font-black text-gray-900" id="stat-total"><?= $stats['total_wajib'] ?></div>
                </div>
            </div>

            <!-- Hadir Tepat Waktu -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
                <div>
                    <div class="text-xs text-emerald-600 font-medium">Tepat Waktu</div>
                    <div class="text-2xl font-black text-emerald-700" id="stat-ontime"><?= $stats['total_hadir_ontime'] ?></div>
                </div>
            </div>

            <!-- Terlambat -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="ri-time-line"></i>
                </div>
                <div>
                    <div class="text-xs text-amber-600 font-medium">Terlambat</div>
                    <div class="text-2xl font-black text-amber-700" id="stat-late"><?= $stats['total_terlambat'] ?></div>
                </div>
            </div>

            <!-- Izin / Sakit -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="ri-hotel-bed-line"></i>
                </div>
                <div>
                    <div class="text-xs text-blue-600 font-medium">Izin / Sakit</div>
                    <div class="text-2xl font-black text-blue-700" id="stat-permission"><?= $stats['total_izin'] ?></div>
                </div>
            </div>

            <!-- Belum Hadir / Ghaib -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3 col-span-2 sm:col-span-1">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl flex-shrink-0">
                    <i class="ri-close-circle-line"></i>
                </div>
                <div>
                    <div class="text-xs text-rose-600 font-medium">Belum Hadir</div>
                    <div class="text-2xl font-black text-rose-700" id="stat-ghaib"><?= $stats['total_ghaib'] ?></div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left 2 Cols: Attendance Tabs & Lists (with Sweeping View) -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <!-- Tabs -->
                <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-1 bg-gray-100/80 p-1 rounded-xl text-sm font-semibold">
                        <button type="button" onclick="switchTab('ghaib')" id="tab-btn-ghaib" class="px-3.5 py-1.5 rounded-lg text-rose-700 bg-white shadow-sm transition-all flex items-center gap-1.5">
                            <i class="ri-error-warning-line"></i> Belum Hadir <span class="bg-rose-100 text-rose-800 text-xs px-1.5 py-0.2 rounded-full font-bold"><?= count($liveData['ghaib']) ?></span>
                        </button>
                        <button type="button" onclick="switchTab('hadir')" id="tab-btn-hadir" class="px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 transition-all flex items-center gap-1.5">
                            <i class="ri-check-line"></i> Tepat Waktu <span class="bg-gray-200 text-gray-700 text-xs px-1.5 py-0.2 rounded-full font-bold"><?= count($liveData['hadir']) ?></span>
                        </button>
                        <button type="button" onclick="switchTab('terlambat')" id="tab-btn-terlambat" class="px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 transition-all flex items-center gap-1.5">
                            <i class="ri-time-line"></i> Terlambat <span class="bg-gray-200 text-gray-700 text-xs px-1.5 py-0.2 rounded-full font-bold"><?= count($liveData['terlambat']) ?></span>
                        </button>
                        <button type="button" onclick="switchTab('izin')" id="tab-btn-izin" class="px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 transition-all flex items-center gap-1.5">
                            <i class="ri-hotel-bed-line"></i> Izin/Sakit <span class="bg-gray-200 text-gray-700 text-xs px-1.5 py-0.2 rounded-full font-bold"><?= count($liveData['izin']) ?></span>
                        </button>
                    </div>

                    <!-- Quick Filter Search -->
                    <div class="relative w-full sm:w-48">
                        <input type="text" id="live-search" onkeyup="filterList()" placeholder="Cari nama / kelas..." class="w-full text-xs rounded-xl border-gray-200 pl-8 pr-3 py-2 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition">
                        <i class="ri-search-line absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                </div>

                <!-- Tab Contents Container -->
                <div class="p-4 flex-1 overflow-y-auto max-h-[600px]">

                    <!-- TAB 1: BELUM HADIR (GHAIB) - PRIORITY FOR SWEEPING -->
                    <div id="tab-ghaib" class="tab-pane space-y-2">
                        <?php if (empty($liveData['ghaib'])): ?>
                            <div class="text-center py-12 text-gray-400">
                                <i class="ri-checkbox-circle-fill text-4xl text-emerald-500 mb-2 block"></i>
                                <span class="text-sm font-semibold text-gray-700">Luar Biasa! Semua Telah Hadir</span>
                                <p class="text-xs text-gray-400 mt-1">Tidak ada santri atau guru yang terlewat untuk event ini.</p>
                            </div>
                        <?php else: ?>
                            <div class="text-xs text-amber-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200/60 mb-3 flex items-center gap-2">
                                <i class="ri-information-line text-sm"></i>
                                <span>Gunakan daftar ini untuk penyisiran (sweeping) kamar / asrama / area sekolah.</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="list-ghaib-container">
                                <?php foreach ($liveData['ghaib'] as $g): ?>
                                    <div class="person-card flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:border-rose-200 hover:bg-rose-50/30 transition-all bg-white"
                                         data-name="<?= strtolower(htmlspecialchars($g['nama'])) ?>"
                                         data-subinfo="<?= strtolower(htmlspecialchars($g['subinfo'] ?? '')) ?>">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                                <?= mb_substr($g['nama'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 leading-tight"><?= htmlspecialchars($g['nama']) ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5">
                                                    <?= htmlspecialchars($g['subinfo']) ?> &bull; <?= $g['person_type'] == 'student' ? 'NIS: ' . htmlspecialchars($g['nis']) : 'Pengajar' ?>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-bold text-rose-600 bg-rose-100/70 px-2.5 py-1 rounded-full whitespace-nowrap">
                                            Alpa
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 2: HADIR TEPAT WAKTU -->
                    <div id="tab-hadir" class="tab-pane hidden space-y-2">
                        <?php if (empty($liveData['hadir'])): ?>
                            <div class="text-center py-12 text-gray-400 text-sm">Belum ada peserta yang hadir tepat waktu.</div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="list-hadir-container">
                                <?php foreach ($liveData['hadir'] as $h): ?>
                                    <div class="person-card flex items-center justify-between p-3 rounded-xl border border-emerald-100 bg-emerald-50/20"
                                         data-name="<?= strtolower(htmlspecialchars($h['nama'])) ?>"
                                         data-subinfo="<?= strtolower(htmlspecialchars($h['subinfo'] ?? '')) ?>">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                                <?= mb_substr($h['nama'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 leading-tight"><?= htmlspecialchars($h['nama']) ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5">
                                                    <?= htmlspecialchars($h['subinfo']) ?> &bull; Tap: <strong class="text-emerald-700"><?= date('H:i:s', strtotime($h['tapped_at'])) ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full whitespace-nowrap">
                                            Tepat Waktu
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 3: TERLAMBAT -->
                    <div id="tab-terlambat" class="tab-pane hidden space-y-2">
                        <?php if (empty($liveData['terlambat'])): ?>
                            <div class="text-center py-12 text-gray-400 text-sm">Tidak ada yang terlambat. Bagus!</div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="list-terlambat-container">
                                <?php foreach ($liveData['terlambat'] as $t): ?>
                                    <div class="person-card flex items-center justify-between p-3 rounded-xl border border-amber-200 bg-amber-50/20"
                                         data-name="<?= strtolower(htmlspecialchars($t['nama'])) ?>"
                                         data-subinfo="<?= strtolower(htmlspecialchars($t['subinfo'] ?? '')) ?>">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                                <?= mb_substr($t['nama'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 leading-tight"><?= htmlspecialchars($t['nama']) ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5">
                                                    <?= htmlspecialchars($t['subinfo']) ?> &bull; Tap: <strong class="text-amber-700"><?= date('H:i:s', strtotime($t['tapped_at'])) ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full whitespace-nowrap">
                                            Terlambat
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 4: IZIN / SAKIT -->
                    <div id="tab-izin" class="tab-pane hidden space-y-2">
                        <?php if (empty($liveData['izin'])): ?>
                            <div class="text-center py-12 text-gray-400 text-sm">Tidak ada data izin atau sakit untuk event ini.</div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="list-izin-container">
                                <?php foreach ($liveData['izin'] as $iz): ?>
                                    <div class="person-card flex items-center justify-between p-3 rounded-xl border border-blue-100 bg-blue-50/20"
                                         data-name="<?= strtolower(htmlspecialchars($iz['nama'])) ?>"
                                         data-subinfo="<?= strtolower(htmlspecialchars($iz['subinfo'] ?? '')) ?>">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center flex-shrink-0 capitalize">
                                                <?= mb_substr($iz['permission_type'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 leading-tight"><?= htmlspecialchars($iz['nama']) ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5">
                                                    <?= htmlspecialchars($iz['subinfo']) ?> &bull; Ket: <span class="italic"><?= htmlspecialchars($iz['reason'] ?: '-') ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-bold text-blue-800 bg-blue-100 px-2.5 py-1 rounded-full uppercase whitespace-nowrap">
                                            <?= htmlspecialchars($iz['permission_type']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- Right 1 Col: Live Activity Stream (Recent Taps) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Live Tap Feed
                    </h3>
                    <span class="text-[11px] text-gray-400 font-medium" id="live-timer-badge">Auto-refresh 3s</span>
                </div>

                <div class="p-4 flex-1 overflow-y-auto max-h-[600px] space-y-3" id="recent-logs-feed">
                    <?php if (empty($recentLogs)): ?>
                        <div class="text-center py-16 text-gray-400 text-xs">
                            <i class="ri-rfid-line text-3xl mb-1 block"></i>
                            Menunggu tap kartu RFID masuk...
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-sm transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        <?= mb_substr($log['person_name'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 leading-tight"><?= htmlspecialchars($log['person_name']) ?></div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            <?= htmlspecialchars($log['person_subinfo']) ?> &bull; Titik: <?= htmlspecialchars($log['device_name'] ?: 'Scanner USB') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-xs font-mono font-bold text-gray-700"><?= date('H:i:s', strtotime($log['tapped_at'])) ?></div>
                                    <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full <?= $log['status'] == 'on_time' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                        <?= $log['status'] == 'on_time' ? 'Tepat Waktu' : 'Terlambat' ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

<script>
// Tab Switching
function switchTab(tabId) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
    document.getElementById('tab-' + tabId).classList.remove('hidden');

    const buttons = ['ghaib', 'hadir', 'terlambat', 'izin'];
    buttons.forEach(id => {
        const btn = document.getElementById('tab-btn-' + id);
        if (id === tabId) {
            btn.className = "px-3.5 py-1.5 rounded-lg text-indigo-700 bg-white shadow-sm transition-all flex items-center gap-1.5 font-bold";
        } else {
            btn.className = "px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 transition-all flex items-center gap-1.5 font-medium";
        }
    });
}

// Live Filtering
function filterList() {
    const q = document.getElementById('live-search').value.toLowerCase();
    document.querySelectorAll('.person-card').forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const sub = card.getAttribute('data-subinfo') || '';
        if (name.includes(q) || sub.includes(q)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Periodic Polling
const selectedEventId = <?= (int)($selectedEventId ?? 0) ?>;
if (selectedEventId > 0) {
    setInterval(() => {
        fetch('<?= url('/api/smart-tap/live-data?event_id=') ?>' + selectedEventId)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                
                // Update stats
                if (data.stats) {
                    document.getElementById('stat-total').innerText = data.stats.total_wajib;
                    document.getElementById('stat-ontime').innerText = data.stats.total_hadir_ontime;
                    document.getElementById('stat-late').innerText = data.stats.total_terlambat;
                    document.getElementById('stat-permission').innerText = data.stats.total_izin;
                    document.getElementById('stat-ghaib').innerText = data.stats.total_ghaib;
                    document.getElementById('stat-percent').innerText = data.stats.percent_presence + '%';
                    document.getElementById('stat-progress-bar').style.width = Math.min(100, data.stats.percent_presence) + '%';
                }

                // Render recent logs if available
                if (data.recent_logs && data.recent_logs.length > 0) {
                    const container = document.getElementById('recent-logs-feed');
                    let html = '';
                    data.recent_logs.forEach(log => {
                        const isOntime = log.status === 'on_time';
                        const badgeClass = isOntime ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700';
                        const badgeText = isOntime ? 'Tepat Waktu' : 'Terlambat';
                        const initial = (log.person_name || '?').charAt(0);
                        const tapTime = (log.tapped_at || '').split(' ')[1] || '';

                        html += `
                            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-sm transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        ${initial}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 leading-tight">${log.person_name}</div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            ${log.person_subinfo || '-'} &bull; Titik: ${log.device_name || 'Scanner USB'}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-xs font-mono font-bold text-gray-700">${tapTime}</div>
                                    <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full ${badgeClass}">
                                        ${badgeText}
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                }
            })
            .catch(err => console.log('Polling err:', err));
    }, 4000);
}
</script>


<!-- Modal Pindahkan Log Presensi Hari Ini -->
<div id="modal-reassign-logs" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-arrow-left-right-line text-amber-600"></i> Pindahkan Log Presensi
            </h3>
            <button type="button" onclick="document.getElementById('modal-reassign-logs').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            Gunakan fitur ini jika operator mesin sebelumnya salah memilih sesi atau sesi terminal belum terkunci, sehingga log tercatat di sesi lain.
        </p>
        <form method="POST" action="<?= url('/smart-tap/reassign-logs') ?>" class="mt-4 space-y-4">
            <input type="hidden" name="date" value="<?= $today ?>">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Dari Sesi Asal (Salah Catat):</label>
                <select name="from_event_id" required class="w-full rounded-xl border-gray-300 shadow-sm text-sm py-2 px-3 bg-gray-50">
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= $ev['id'] ?>" <?= $ev['id'] == $selectedEventId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ev['name']) ?> (<?= substr($ev['open_time'], 0, 5) ?> - <?= substr($ev['close_time'], 0, 5) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pindahkan ke Sesi Tujuan (Yang Seharusnya):</label>
                <select name="to_event_id" required class="w-full rounded-xl border-gray-300 shadow-sm text-sm py-2 px-3 bg-gray-50">
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= $ev['id'] ?>">
                            <?= htmlspecialchars($ev['name']) ?> (<?= substr($ev['open_time'], 0, 5) ?> - <?= substr($ev['close_time'], 0, 5) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/60 text-[11px] text-amber-800 flex items-start gap-2">
                <i class="ri-information-line text-base text-amber-600 flex-shrink-0 mt-0.5"></i>
                <span>Status ketepatan waktu (Tepat Waktu / Terlambat) akan otomatis dihitung ulang menyesuaikan jam buka & batas waktu sesi tujuan.</span>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-reassign-logs').classList.add('hidden')" class="px-4 py-2 border border-gray-200 text-gray-700 text-xs font-semibold rounded-xl hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-sm">
                    Pindahkan Log Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<?php
renderFooter();
?>

