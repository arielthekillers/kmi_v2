<?php
/**
 * Smart Tap - Modern Realtime Live Monitoring Feed
 * Focused on incoming RFID taps, concurrent multi-session support, and zero clutter.
 */
renderHeader("Smart Tap Realtime Monitoring", "smart_tap");
?>

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6" id="live-monitoring-wrapper">

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ri-checkbox-circle-fill text-xl text-emerald-600"></i>
                <span class="text-sm font-semibold"><?= $_SESSION['success'] ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Minimalist Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Live Streaming
                </span>
                <span class="text-xs text-gray-400 font-mono" id="server-clock-display">
                    <?= date('d M Y') ?> &bull; <?= date('H:i:s') ?> WIB
                </span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 mt-1 tracking-tight flex items-center gap-2">
                <i class="ri-radar-fill text-indigo-600"></i> Smart Tap Live Monitor
            </h1>
        </div>

        <!-- Quick Actions & Tools -->
        <div class="flex items-center gap-2 flex-wrap">
            <?php if (!empty($events) && count($events) > 1): ?>
                <button type="button" onclick="document.getElementById('modal-reassign-logs').classList.remove('hidden')" class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold rounded-xl flex items-center gap-1.5 transition shadow-xs" title="Pindahkan data presensi jika salah memilih sesi di terminal">
                    <i class="ri-arrow-left-right-line"></i> Pindahkan Log
                </button>
            <?php endif; ?>

            <!-- Audio Mute Toggle -->
            <button type="button" id="btn-toggle-sound" onclick="toggleAudio()" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200 text-xs font-semibold rounded-xl flex items-center gap-1.5 transition shadow-xs" title="Nyalakan/Matikan Suara Beep">
                <i class="ri-volume-mute-line text-sm" id="sound-icon"></i> <span id="sound-text">Mute</span>
            </button>

            <!-- Fullscreen TV Toggle -->
            <button type="button" onclick="toggleFullScreen()" class="px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold rounded-xl flex items-center gap-1.5 transition shadow-xs" title="Mode TV Monitor / Layar Penuh">
                <i class="ri-fullscreen-line text-sm"></i> Layar Penuh
            </button>
        </div>
    </div>

    <!-- Multi-Event Session Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <!-- Tab: Semua Sesi Aktif -->
        <a href="<?= url('/smart-tap/live?event_id=all') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap shadow-xs <?= $selectedEventId === 'all' ? 'bg-indigo-600 text-white shadow-indigo-200' : 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200' ?>">
            <i class="ri-apps-2-line"></i>
            <span>Semua Sesi Aktif Hari Ini</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black <?= $selectedEventId === 'all' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' ?>">
                <?= $totalAllTaps ?> Tap
            </span>
        </a>

        <!-- Individual Events Tabs -->
        <?php foreach ($eventsWithStats as $ev): 
            $isSel = ($selectedEventId !== 'all' && (int)$selectedEventId === (int)$ev['id']);
            $cat = $ev['category'] ?? 'sholat';
            $icon = 'ri-time-line';
            if ($cat === 'sholat') $icon = 'ri-moon-line';
            elseif ($cat === 'office') $icon = 'ri-building-line';
            elseif ($cat === 'incidental') $icon = 'ri-calendar-event-line';
        ?>
            <a href="<?= url('/smart-tap/live?event_id=' . $ev['id']) ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap shadow-xs <?= $isSel ? 'bg-indigo-600 text-white shadow-indigo-200' : 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200' ?>">
                <i class="<?= $icon ?>"></i>
                <span><?= htmlspecialchars($ev['name']) ?></span>
                <span class="text-[11px] opacity-75 font-mono">(<?= substr($ev['open_time'], 0, 5) ?> - <?= substr($ev['close_time'], 0, 5) ?>)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black <?= $isSel ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-700' ?>">
                    <?= $ev['total_hadir'] ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Active Sessions Quick Strip (Side-by-Side if Concurrent) -->
    <?php if (!empty($eventsWithStats)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-<?= min(4, max(1, count($eventsWithStats))) ?> gap-4">
            <?php foreach ($eventsWithStats as $ev): 
                $isCurrentlyActive = (date('H:i:s') >= $ev['open_time'] && date('H:i:s') <= $ev['close_time']);
                $cat = $ev['category'] ?? 'sholat';
                $cardBorder = $isCurrentlyActive ? 'border-emerald-300 ring-2 ring-emerald-400/20 bg-gradient-to-br from-emerald-50/40 via-white to-white' : 'border-gray-200 bg-white';
                $badgeBg = ($cat === 'office') ? 'bg-blue-100 text-blue-800' : (($cat === 'incidental') ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800');
            ?>
                <div class="p-4 rounded-2xl border shadow-xs transition flex flex-col justify-between <?= $cardBorder ?>">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider <?= $badgeBg ?>">
                                <?= strtoupper($cat) ?>
                            </span>
                            <?php if ($isCurrentlyActive): ?>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-100/80 px-2 py-0.5 rounded-full animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Jam Buka
                                </span>
                            <?php else: ?>
                                <span class="text-[10px] text-gray-400 font-mono">
                                    <?= substr($ev['open_time'], 0, 5) ?> - <?= substr($ev['close_time'], 0, 5) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h3 class="text-base font-black text-gray-900 mt-2 tracking-tight truncate" title="<?= htmlspecialchars($ev['name']) ?>">
                            <?= htmlspecialchars($ev['name']) ?>
                        </h3>
                        <p class="text-[11px] text-gray-500 capitalize">
                            Target: <?= $ev['target_type'] === 'all' ? 'Santri & Pengajar' : ($ev['target_type'] === 'students' ? 'Santri Saja' : 'Pengajar Saja') ?>
                        </p>
                    </div>

                    <!-- 3 Stats Counters -->
                    <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-gray-100 text-center">
                        <div>
                            <div class="text-[10px] text-gray-400 font-semibold uppercase">Total Masuk</div>
                            <div class="text-lg font-black text-gray-900" id="stat-total-<?= $ev['id'] ?>"><?= $ev['total_hadir'] ?></div>
                        </div>
                        <div>
                            <div class="text-[10px] text-emerald-600 font-semibold uppercase">Tepat</div>
                            <div class="text-lg font-black text-emerald-600" id="stat-ontime-<?= $ev['id'] ?>"><?= $ev['on_time'] ?></div>
                        </div>
                        <div>
                            <div class="text-[10px] text-amber-600 font-semibold uppercase">Telat</div>
                            <div class="text-lg font-black text-amber-600" id="stat-late-<?= $ev['id'] ?>"><?= $ev['late'] ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- MAIN SPOTLIGHT: LIVE TAP ATTENDANCE FEED -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs flex flex-col overflow-hidden">
        
        <!-- Feed Top Controls -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-base shadow-xs">
                    <i class="ri-rfid-line"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-gray-900 tracking-tight">Stream Riwayat Tap Masuk</h2>
                    <p class="text-[11px] text-gray-500">Pembaruan otomatis tiap 3 detik &bull; Kartu terbaru muncul di paling atas</p>
                </div>
            </div>

            <!-- Search in feed & Pause Button -->
            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-60">
                    <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="feed-search" oninput="filterFeed(this.value)" placeholder="Cari nama santri / guru..." class="w-full text-xs rounded-xl border-gray-200 pl-8 pr-3 py-1.5 bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>
                <button type="button" id="btn-pause" onclick="togglePause()" class="px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-xl text-xs font-semibold flex items-center gap-1 shadow-xs transition" title="Jeda pembaruan otomatis">
                    <i class="ri-pause-line" id="pause-icon"></i> <span id="pause-text">Jeda</span>
                </button>
            </div>
        </div>

        <!-- Feed List Container -->
        <div id="live-feed-container" class="divide-y divide-gray-100 p-2 sm:p-4 space-y-2 max-h-[750px] overflow-y-auto">
            <?php if (empty($recentLogs)): ?>
                <div id="feed-empty-state" class="text-center py-16 text-gray-400">
                    <div class="w-16 h-16 rounded-3xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto text-3xl mb-3 shadow-inner">
                        <i class="ri-rfid-line animate-pulse"></i>
                    </div>
                    <div class="font-bold text-gray-700 text-sm">Menunggu Tap Kartu...</div>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                        Belum ada kartu presensi yang di-tap untuk sesi ini hari ini. Silakan tempelkan kartu RFID pada mesin terminal.
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($recentLogs as $log): 
                    $tapTime = date('H:i:s', strtotime($log['tapped_at']));
                    $isOntime = ($log['status'] === 'on_time');
                    $statusBadge = $isOntime ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200';
                    $statusIcon = $isOntime ? 'ri-checkbox-circle-fill text-emerald-600' : 'ri-time-fill text-amber-600';
                    $statusText = $isOntime ? 'Tepat Waktu' : 'Terlambat';
                    
                    $cat = $log['event_category'] ?? 'sholat';
                    $evBadge = ($cat === 'office') ? 'bg-blue-50 text-blue-700 border-blue-200' : (($cat === 'incidental') ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200');

                    // Random color avatar
                    $avatarColors = ['bg-indigo-600', 'bg-blue-600', 'bg-emerald-600', 'bg-purple-600', 'bg-rose-600', 'bg-amber-600', 'bg-teal-600'];
                    $colorIndex = abs(crc32($log['person_name'])) % count($avatarColors);
                    $avatarColor = $avatarColors[$colorIndex];
                ?>
                    <div class="feed-item p-3.5 sm:p-4 rounded-2xl bg-white hover:bg-slate-50/80 border border-gray-100 shadow-xs transition-all flex items-center justify-between gap-4" data-name="<?= strtolower(htmlspecialchars($log['person_name'])) ?>">
                        <!-- Left: Avatar & Info -->
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-12 h-12 rounded-2xl <?= $avatarColor ?> text-white font-black text-lg flex items-center justify-center flex-shrink-0 shadow-sm uppercase">
                                <?= mb_substr($log['person_name'], 0, 1) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-base font-black text-gray-900 truncate">
                                        <?= htmlspecialchars($log['person_name']) ?>
                                    </h4>
                                    <!-- Event Badge (Penting jika 2 event berjalan bersamaan) -->
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black border uppercase tracking-wider <?= $evBadge ?>">
                                        <?= htmlspecialchars($log['event_name'] ?? 'Sesi Umum') ?>
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                    <span class="font-medium text-gray-700"><?= htmlspecialchars($log['person_subinfo'] ?: '-') ?></span>
                                    <span>&bull;</span>
                                    <span class="font-mono text-gray-400">ID: <?= htmlspecialchars($log['person_identifier'] ?: $log['card_uid']) ?></span>
                                    <span>&bull;</span>
                                    <span class="text-gray-400 flex items-center gap-1">
                                        <i class="ri-computer-line text-xs"></i> <?= htmlspecialchars($log['device_name'] ?: 'Scanner USB') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Time & Big Status Badge -->
                        <div class="flex items-center gap-4 flex-shrink-0 text-right">
                            <div>
                                <div class="text-base font-mono font-black text-gray-900 leading-tight"><?= $tapTime ?></div>
                                <div class="text-[10px] text-gray-400">WIB</div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-black <?= $statusBadge ?> shadow-xs">
                                <i class="<?= $statusIcon ?> text-sm"></i>
                                <?= $statusText ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

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
                        <option value="<?= $ev['id'] ?>" <?= ($selectedEventId !== 'all' && $ev['id'] == $selectedEventId) ? 'selected' : '' ?>>
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

<!-- Audio element for Tap Beep -->
<audio id="tap-sound" preload="auto">
    <source src="data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU9vT18A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A/38A" type="audio/wav">
</audio>

<script>
let currentEventFilter = '<?= $selectedEventId ?>';
let isPaused = false;
let audioEnabled = false;
let lastKnownTopLogId = <?= !empty($recentLogs) ? (int)$recentLogs[0]['id'] : 0 ?>;

// Sound toggle
function toggleAudio() {
    audioEnabled = !audioEnabled;
    const icon = document.getElementById('sound-icon');
    const text = document.getElementById('sound-text');
    if (audioEnabled) {
        icon.className = 'ri-volume-up-line text-sm text-indigo-600';
        text.innerText = 'Sound On';
        // Play small dummy sound to satisfy browser user gesture
        playBeep();
    } else {
        icon.className = 'ri-volume-mute-line text-sm';
        text.innerText = 'Mute';
    }
}

function playBeep() {
    if (!audioEnabled) return;
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5 note
        gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.15);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch (e) {}
}

// Pause toggle
function togglePause() {
    isPaused = !isPaused;
    const icon = document.getElementById('pause-icon');
    const text = document.getElementById('pause-text');
    if (isPaused) {
        icon.className = 'ri-play-line';
        text.innerText = 'Lanjut';
    } else {
        icon.className = 'ri-pause-line';
        text.innerText = 'Jeda';
    }
}

// Fullscreen Toggle
function toggleFullScreen() {
    const elem = document.documentElement;
    if (!document.fullscreenElement) {
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

// Feed Quick Search
function filterFeed(val) {
    val = val.toLowerCase().trim();
    const items = document.querySelectorAll('.feed-item');
    items.forEach(el => {
        const name = el.getAttribute('data-name') || '';
        if (!val || name.includes(val)) {
            el.style.display = 'flex';
        } else {
            el.style.display = 'none';
        }
    });
}

// Color palette generator based on name
function getAvatarColor(name) {
    const colors = ['bg-indigo-600', 'bg-blue-600', 'bg-emerald-600', 'bg-purple-600', 'bg-rose-600', 'bg-amber-600', 'bg-teal-600'];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    const idx = Math.abs(hash) % colors.length;
    return colors[idx];
}

// Realtime AJAX Polling every 3 seconds
setInterval(function() {
    if (isPaused) return;

    const url = '<?= url("/api/smart-tap/live-data") ?>?event_id=' + encodeURIComponent(currentEventFilter) + '&date=<?= $today ?>';

    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;

            // Update clock
            if (data.server_time) {
                document.getElementById('server-clock-display').innerHTML = '<?= date("d M Y") ?> &bull; ' + data.server_time + ' WIB';
            }

            // Update session counter cards
            if (data.events_with_stats) {
                data.events_with_stats.forEach(ev => {
                    const elTotal = document.getElementById('stat-total-' + ev.id);
                    const elOntime = document.getElementById('stat-ontime-' + ev.id);
                    const elLate = document.getElementById('stat-late-' + ev.id);
                    if (elTotal) elTotal.innerText = ev.total_hadir;
                    if (elOntime) elOntime.innerText = ev.on_time;
                    if (elLate) elLate.innerText = ev.late;
                });
            }

            // Update feed items
            const logs = data.recent_logs || [];
            if (logs.length > 0) {
                // Check if newest log is new
                const topId = parseInt(logs[0].id);
                if (topId > lastKnownTopLogId) {
                    playBeep();
                    lastKnownTopLogId = topId;
                }

                const container = document.getElementById('live-feed-container');
                let html = '';

                logs.forEach(log => {
                    const tapTime = (log.tapped_at || '').substr(11, 8);
                    const isOntime = (log.status === 'on_time');
                    const statusBadge = isOntime ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200';
                    const statusIcon = isOntime ? 'ri-checkbox-circle-fill text-emerald-600' : 'ri-time-fill text-amber-600';
                    const statusText = isOntime ? 'Tepat Waktu' : 'Terlambat';

                    const cat = log.event_category || 'sholat';
                    const evBadge = (cat === 'office') ? 'bg-blue-50 text-blue-700 border-blue-200' : ((cat === 'incidental') ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200');

                    const avatarColor = getAvatarColor(log.person_name || 'Santri');
                    const initial = (log.person_name || 'S').substr(0, 1).toUpperCase();

                    html += `
                        <div class="feed-item p-3.5 sm:p-4 rounded-2xl bg-white hover:bg-slate-50/80 border border-gray-100 shadow-xs transition-all flex items-center justify-between gap-4" data-name="${(log.person_name || '').toLowerCase()}">
                            <!-- Left: Avatar & Info -->
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-12 h-12 rounded-2xl ${avatarColor} text-white font-black text-lg flex items-center justify-center flex-shrink-0 shadow-sm uppercase">
                                    ${initial}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="text-base font-black text-gray-900 truncate">
                                            ${log.person_name || 'Tidak Diketahui'}
                                        </h4>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black border uppercase tracking-wider ${evBadge}">
                                            ${log.event_name || 'Sesi Umum'}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                        <span class="font-medium text-gray-700">${log.person_subinfo || '-'}</span>
                                        <span>&bull;</span>
                                        <span class="font-mono text-gray-400">ID: ${log.person_identifier || log.card_uid}</span>
                                        <span>&bull;</span>
                                        <span class="text-gray-400 flex items-center gap-1">
                                            <i class="ri-computer-line text-xs"></i> ${log.device_name || 'Scanner USB'}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Time & Big Status Badge -->
                            <div class="flex items-center gap-4 flex-shrink-0 text-right">
                                <div>
                                    <div class="text-base font-mono font-black text-gray-900 leading-tight">${tapTime}</div>
                                    <div class="text-[10px] text-gray-400">WIB</div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-black ${statusBadge} shadow-xs">
                                    <i class="${statusIcon} text-sm"></i>
                                    ${statusText}
                                </span>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;

                // Re-apply current search query if user was typing
                const searchVal = document.getElementById('feed-search').value;
                if (searchVal) {
                    filterFeed(searchVal);
                }
            }
        })
        .catch(err => console.log('Polling err:', err));
}, 3000);
</script>

<?php
renderFooter();
?>
