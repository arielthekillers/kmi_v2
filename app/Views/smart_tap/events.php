<?php
renderHeader("Smart Tap — Master Event & Pengaturan Jam Absen");

$today = date('Y-m-d');
$todayHolidayInfo = $todayHolidayInfo ?? (new \App\Models\SmartTapEventModel())->getAcademicHolidayInfo($today);
$activeTab = $_GET['tab'] ?? 'all';

// Categorize events
$sholatEvents = [];
$officeEvents = [];
$activeIncidentalEvents = [];
$archivedEvents = [];

foreach ($events as $ev) {
    $cat = $ev['category'] ?? '';
    $nameLower = strtolower($ev['name']);
    $isDaily = ($ev['event_type'] === 'daily');
    $isOneOff = ($ev['event_type'] === 'one_off');
    $eventDate = $ev['event_date'] ?? null;
    $isActive = (int)$ev['is_active'];

    // Check if it's an archived/past event
    $isPast = ($isOneOff && !empty($eventDate) && $eventDate < $today);
    
    if ($isPast || ($isActive === 0 && $isOneOff)) {
        $archivedEvents[] = $ev;
        continue;
    }

    // Sholat
    if ($cat === 'sholat' || (empty($cat) && strpos($nameLower, 'sholat') !== false)) {
        $sholatEvents[] = $ev;
    } 
    // Office
    elseif ($cat === 'office' || (empty($cat) && (strpos($nameLower, 'kantor') !== false || strpos($nameLower, 'office') !== false || strpos($nameLower, 'piket') !== false))) {
        $officeEvents[] = $ev;
    } 
    // Incidental
    else {
        $activeIncidentalEvents[] = $ev;
    }
}
?>

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ri-checkbox-circle-fill text-xl text-emerald-600"></i>
                <span class="text-sm font-semibold"><?= $_SESSION['success'] ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ri-error-warning-fill text-xl text-rose-600"></i>
                <span class="text-sm font-semibold"><?= $_SESSION['error'] ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-lg leading-none">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Master Header Card -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Left Info -->
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl flex-shrink-0 shadow-xs">
                    <i class="ri-calendar-event-line"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Master Event & Jam Presensi</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
                        Atur jadwal rutin Sholat 5 Waktu & Kantor, kelola event insidental terdata, serta pantau arsip kegiatan yang telah terlewat.
                    </p>
                </div>
            </div>

            <!-- Right Action -->
            <div class="flex items-center gap-3 self-start lg:self-center">
                <button onclick="openEventModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold rounded-2xl shadow-sm shadow-indigo-200 transition-all hover:shadow-md whitespace-nowrap">
                    <i class="ri-add-line text-base"></i>
                    <span>Tambah Event Baru</span>
                </button>
            </div>
        </div>

        <!-- Category Tab Switcher -->
        <div class="mt-6 pt-5 border-t border-gray-100 flex items-center gap-2 overflow-x-auto pb-1" id="event-tabs-bar">
            <button type="button" onclick="switchTab('all')" id="tab-btn-all" 
               class="tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 whitespace-nowrap bg-indigo-50 text-indigo-700 border border-indigo-200/60 shadow-xs">
                <i class="ri-apps-2-line text-sm"></i>
                <span>Semua Kategori</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-indigo-600 text-white">
                    <?= count($sholatEvents) + count($officeEvents) + count($activeIncidentalEvents) ?>
                </span>
            </button>
            
            <button type="button" onclick="switchTab('sholat')" id="tab-btn-sholat" 
               class="tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 whitespace-nowrap text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                <i class="ri-moon-clear-line text-sm text-emerald-600"></i>
                <span>Sholat 5 Waktu</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800">
                    <?= count($sholatEvents) ?>
                </span>
            </button>

            <button type="button" onclick="switchTab('office')" id="tab-btn-office" 
               class="tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 whitespace-nowrap text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                <i class="ri-building-line text-sm text-blue-600"></i>
                <span>Presensi Kantor (Office)</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-blue-100 text-blue-800">
                    <?= count($officeEvents) ?>
                </span>
            </button>

            <button type="button" onclick="switchTab('incidental')" id="tab-btn-incidental" 
               class="tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 whitespace-nowrap text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                <i class="ri-calendar-todo-line text-sm text-amber-500"></i>
                <span>Insidental Aktif</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800">
                    <?= count($activeIncidentalEvents) ?>
                </span>
            </button>

            <button type="button" onclick="switchTab('archive')" id="tab-btn-archive" 
               class="tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 whitespace-nowrap text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                <i class="ri-archive-line text-sm text-gray-400"></i>
                <span>Arsip & Terlewat</span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-gray-200 text-gray-700">
                    <?= count($archivedEvents) ?>
                </span>
            </button>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- SECTION 1: SHOLAT 5 WAKTU (ULTRA COMPACT ROW)   -->
    <!-- ============================================== -->
    <div id="section-sholat" class="event-section space-y-3">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                    <i class="ri-moon-clear-line"></i>
                </span>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Jadwal Sholat 5 Waktu (Masjid)</h3>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Rutin Tiap Hari &bull; Santri & Guru
                </span>
            </div>
            <button onclick="openEventModal('sholat')" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                <i class="ri-add-line"></i> Tambah Sholat
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
            <?php 
            $prayerIcons = [
                'subuh' => ['ri-sun-foggy-line', 'from-blue-600 to-indigo-700'],
                'jumat' => ['ri-community-line', 'from-emerald-600 to-teal-700'],
                'jum\'at' => ['ri-community-line', 'from-emerald-600 to-teal-700'],
                'dzuhur' => ['ri-sun-fill', 'from-amber-500 to-amber-600'],
                'ashar' => ['ri-sun-line', 'from-orange-500 to-amber-600'],
                'maghrib' => ['ri-moon-foggy-line', 'from-rose-500 to-indigo-800'],
                'isya' => ['ri-moon-clear-line', 'from-indigo-800 to-slate-900'],
            ];

            foreach ($sholatEvents as $ev): 
                $nameKey = 'subuh';
                foreach (array_keys($prayerIcons) as $k) {
                    if (stripos($ev['name'], $k) !== false) {
                        $nameKey = $k;
                        break;
                    }
                }
                $iconData = $prayerIcons[$nameKey] ?? ['ri-moon-line', 'from-indigo-600 to-indigo-700'];

                // Day badges
                $daysArr = !empty($ev['days_of_week']) ? explode(',', $ev['days_of_week']) : ['1','2','3','4','5','6','7'];
                sort($daysArr);
                $daysStr = implode(',', $daysArr);
                if (count($daysArr) === 7) {
                    $dayText = 'Tiap Hari';
                    $dayBadge = 'bg-gray-100 text-gray-600';
                } elseif ($daysStr === '5') {
                    $dayText = 'Khusus Jumat';
                    $dayBadge = 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold';
                } elseif ($daysStr === '1,2,3,4,6,7') {
                    $dayText = 'Sabtu – Kamis';
                    $dayBadge = 'bg-amber-50 text-amber-700 border border-amber-200 font-bold';
                } else {
                    $dayMap = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Ahd'];
                    $labels = [];
                    foreach ($daysArr as $d) {
                        if (isset($dayMap[$d])) $labels[] = $dayMap[$d];
                    }
                    $dayText = implode(', ', $labels);
                    $dayBadge = 'bg-indigo-50 text-indigo-700 border border-indigo-200';
                }
            ?>
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs hover:shadow-md transition flex flex-col justify-between relative group">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br <?= $iconData[1] ?> text-white flex items-center justify-center text-sm shadow-xs flex-shrink-0">
                                    <i class="<?= $iconData[0] ?>"></i>
                                </div>
                                <h4 class="font-bold text-gray-900 text-sm leading-tight"><?= htmlspecialchars($ev['name']) ?></h4>
                            </div>

                            <span class="w-2.5 h-2.5 rounded-full <?= $ev['is_active'] ? 'bg-emerald-500 shadow-xs shadow-emerald-200' : 'bg-gray-300' ?>" title="<?= $ev['is_active'] ? 'Aktif' : 'Non-Aktif' ?>"></span>
                        </div>

                        <!-- Day & Gender Indicator -->
                        <div class="mb-2.5 flex items-center gap-1.5 flex-wrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] <?= $dayBadge ?>">
                                <i class="ri-calendar-line text-[10px]"></i> <?= $dayText ?>
                            </span>
                            <?php if (($ev['target_gender'] ?? 'all') === 'L'): ?>
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] bg-blue-50 text-blue-700 border border-blue-200 font-bold" title="Khusus Jamaah Putra">
                                    <i class="ri-men-line text-[10px]"></i> Putra
                                </span>
                            <?php elseif (($ev['target_gender'] ?? 'all') === 'P'): ?>
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] bg-rose-50 text-rose-700 border border-rose-200 font-bold" title="Khusus Jamaah Putri">
                                    <i class="ri-women-line text-[10px]"></i> Putri
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Compact Time Badges -->
                        <div class="bg-gray-50/80 rounded-xl p-2.5 space-y-1.5 text-[11px] border border-gray-100">
                            <div class="flex items-center justify-between text-gray-500">
                                <span>Buka Tap:</span>
                                <span class="font-mono font-bold text-gray-800"><?= substr($ev['open_time'], 0, 5) ?></span>
                            </div>
                            <div class="flex items-center justify-between text-emerald-700 font-medium">
                                <span>Tepat Waktu:</span>
                                <span class="font-mono font-bold"><?= substr($ev['start_time'], 0, 5) ?></span>
                            </div>
                            <div class="flex items-center justify-between text-rose-600">
                                <span>Tutup Tap:</span>
                                <span class="font-mono font-bold"><?= substr($ev['close_time'], 0, 5) ?></span>
                            </div>
                        </div>

                        <div class="mt-2 text-[10px] text-gray-400 flex items-center justify-between">
                            <span>Lewat jam:</span>
                            <span class="font-semibold <?= $ev['after_close_action'] === 'very_late' ? 'text-amber-600' : 'text-rose-600' ?>">
                                <?= $ev['after_close_action'] === 'very_late' ? 'Sangat Telat' : 'Tolak Total' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Compact Footer Actions -->
                    <div class="flex items-center justify-end gap-1.5 pt-3 mt-3 border-t border-gray-100">
                        <button type="button" onclick='editEvent(<?= json_encode($ev) ?>)' class="px-2.5 py-1 text-[11px] font-bold text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                            <i class="ri-edit-line"></i> Edit
                        </button>
                        <a href="<?= url('/smart-tap/events/delete?id=' . $ev['id']) ?>" onclick="return confirm('Hapus jadwal <?= htmlspecialchars(addslashes($ev['name'])) ?>?')" class="p-1 text-gray-400 hover:text-rose-600 rounded-lg transition" title="Hapus">
                            <i class="ri-delete-bin-line text-xs"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- SECTION 2: PRESENSI KANTOR (OFFICE 3 EVENT)    -->
    <!-- ============================================== -->
    <div id="section-office" class="event-section space-y-3">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                    <i class="ri-building-line"></i>
                </span>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Presensi Kantor & Kedinasan</h3>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    Rutin Kerja &bull; Guru & Karyawan
                </span>
            </div>
            <button onclick="openEventModal('office')" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                <i class="ri-add-line"></i> Tambah Event Kantor
            </button>
        </div>

        <?php if (!empty($todayHolidayInfo['is_holiday'])): ?>
            <div class="bg-amber-50/90 border border-amber-200/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-base flex-shrink-0">
                        <i class="ri-calendar-close-line"></i>
                    </div>
                    <div>
                        <span class="font-bold text-amber-950">Status Akademik Hari Ini (<?= date('d M Y') ?>):</span>
                        <span class="ml-1 font-medium"><?= htmlspecialchars($todayHolidayInfo['reason']) ?></span>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-white/80 border border-amber-200 text-amber-800 font-bold text-[11px] self-start sm:self-center shadow-xs">
                    <i class="ri-shield-check-line text-amber-600"></i> Presensi Kantor Diliburkan Otomatis
                </span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php if (empty($officeEvents)): ?>
                <div class="col-span-3 bg-white rounded-2xl p-6 border border-gray-100 text-center text-gray-400">
                    <i class="ri-building-line text-3xl mb-1 block text-blue-400"></i>
                    Belum ada jadwal kantor yang terdaftar.
                    <button onclick="openEventModal('office')" class="mt-2 text-xs text-indigo-600 font-bold block mx-auto underline">
                        + Tambah Jadwal Kantor (Pagi/Siang/Sore)
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($officeEvents as $ev): ?>
                    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    <i class="ri-briefcase-line text-[10px]"></i> Presensi Kantor
                                </span>
                                <span class="flex items-center gap-1 text-[11px] font-bold <?= $ev['is_active'] ? 'text-emerald-600' : 'text-gray-400' ?>">
                                    <span class="w-2 h-2 rounded-full <?= $ev['is_active'] ? 'bg-emerald-500' : 'bg-gray-300' ?>"></span>
                                    <?= $ev['is_active'] ? 'Aktif' : 'Non-Aktif' ?>
                                </span>
                            </div>

                            <h4 class="font-bold text-gray-900 text-sm mb-1"><?= htmlspecialchars($ev['name']) ?></h4>
                            <p class="text-[11px] text-gray-400 mb-3">
                                Target: <span class="text-gray-700 font-semibold">Pengajar & Pegawai</span>
                            </p>

                            <!-- Horizontal Times -->
                            <div class="bg-gray-50 rounded-xl p-2.5 flex items-center justify-between text-xs border border-gray-100">
                                <div class="text-center">
                                    <div class="text-[10px] text-gray-400 uppercase">Buka</div>
                                    <div class="font-mono font-bold text-gray-800"><?= substr($ev['open_time'], 0, 5) ?></div>
                                </div>
                                <div class="text-gray-300">&rarr;</div>
                                <div class="text-center">
                                    <div class="text-[10px] text-emerald-600 uppercase font-bold">Tepat</div>
                                    <div class="font-mono font-bold text-emerald-700"><?= substr($ev['start_time'], 0, 5) ?></div>
                                </div>
                                <div class="text-gray-300">&rarr;</div>
                                <div class="text-center">
                                    <div class="text-[10px] text-rose-500 uppercase">Tutup</div>
                                    <div class="font-mono font-bold text-rose-600"><?= substr($ev['close_time'], 0, 5) ?></div>
                                </div>
                            </div>

                            <?php if (!empty($ev['sync_academic_calendar'])): ?>
                                <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between text-[10px]">
                                    <span class="font-bold text-indigo-700 flex items-center gap-1">
                                        <i class="ri-calendar-check-line text-indigo-500"></i> Ikuti Kalender KBM
                                    </span>
                                    <button type="button" onclick='openDirectParticipantModal(<?= json_encode($ev) ?>)' class="font-bold px-2 py-0.5 rounded-md <?= !empty($ev['target_teachers']) ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                                        <i class="ri-user-star-line"></i> <?= !empty($ev['target_teachers']) ? count(array_filter(explode(',', $ev['target_teachers']))) . ' Staf Khusus' : 'Semua Staf' ?>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-end text-[10px]">
                                    <button type="button" onclick='openDirectParticipantModal(<?= json_encode($ev) ?>)' class="font-bold px-2 py-0.5 rounded-md <?= !empty($ev['target_teachers']) ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                                        <i class="ri-user-star-line"></i> <?= !empty($ev['target_teachers']) ? count(array_filter(explode(',', $ev['target_teachers']))) . ' Staf Khusus' : 'Semua Staf' ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-100">
                            <span class="text-[11px] text-gray-400">
                                <?= $ev['allow_any_device'] ? 'Bebas terminal' : 'Terminal tertentu' ?>
                            </span>
                            <div class="flex items-center gap-1">
                                <button type="button" onclick='editEvent(<?= json_encode($ev) ?>)' class="px-2.5 py-1 text-xs font-bold text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                    <i class="ri-edit-line"></i> Edit
                                </button>
                                <a href="<?= url('/smart-tap/events/delete?id=' . $ev['id']) ?>" onclick="return confirm('Hapus event kantor ini?')" class="p-1 text-gray-400 hover:text-rose-600 rounded-lg transition">
                                    <i class="ri-delete-bin-line text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- SECTION 3: EVENT INSIDENTAL AKTIF (TABLE)       -->
    <!-- ============================================== -->
    <div id="section-incidental" class="event-section space-y-3">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header bar with search -->
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold shadow-xs">
                        <i class="ri-calendar-todo-line"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Event Insidental Aktif (Mendatang & Hari Ini)</h3>
                        <p class="text-[11px] text-gray-500">Kegiatan sekali jalan seperti Upacara, Rapat, Acara Khusus</p>
                    </div>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800 ml-2">
                        <?= count($activeIncidentalEvents) ?> Event
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="search-incidental" oninput="filterIncidental(this.value)" placeholder="Cari kegiatan..." class="text-xs rounded-xl border border-gray-200 bg-white py-2 pl-8 pr-3 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    </div>
                    <button onclick="openEventModal('incidental')" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-xs whitespace-nowrap">
                        <i class="ri-add-line"></i> Tambah Insidental
                    </button>
                </div>
            </div>

            <!-- Compact Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="table-incidental">
                    <thead>
                        <tr class="bg-gray-50/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="py-3 px-4">Tanggal Pelaksanaan</th>
                            <th class="py-3 px-4">Nama Kegiatan</th>
                            <th class="py-3 px-4">Target Peserta</th>
                            <th class="py-3 px-4">Jam Absensi (Buka &bull; Batas &bull; Tutup)</th>
                            <th class="py-3 px-4">Lokasi Terminal</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if (empty($activeIncidentalEvents)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-12 px-4 text-gray-400">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto text-2xl mb-2 shadow-inner">
                                        <i class="ri-calendar-check-line"></i>
                                    </div>
                                    <div class="font-bold text-gray-700 text-xs">Tidak Ada Event Insidental Aktif</div>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Event yang tanggalnya terlewat secara otomatis dipindahkan ke tab Arsip.</p>
                                    <button type="button" onclick="openEventModal('incidental')" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-xl transition border border-amber-200">
                                        <i class="ri-add-line"></i> Buat Event Insidental Baru
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activeIncidentalEvents as $ev): 
                                $isToday = ($ev['event_date'] === $today);
                            ?>
                                <tr class="hover:bg-amber-50/20 transition incidental-row">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold <?= $isToday ? 'bg-emerald-100 text-emerald-800 border border-emerald-200 animate-pulse' : 'bg-gray-100 text-gray-700' ?>">
                                                <i class="ri-calendar-event-line text-[11px]"></i>
                                                <?= !empty($ev['event_date']) ? date('d M Y', strtotime($ev['event_date'])) : 'Rutin' ?>
                                                <?= $isToday ? '(Hari Ini)' : '' ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-gray-900 event-name"><?= htmlspecialchars($ev['name']) ?></div>
                                        <div class="text-[11px] text-gray-400"><?= $ev['after_close_action'] === 'very_late' ? 'Toleransi: Terima Sangat Telat' : 'Toleransi: Tolak Total' ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-col gap-1 items-start">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?= $ev['target_type'] == 'all' ? 'bg-indigo-50 text-indigo-700' : ($ev['target_type'] == 'students' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700') ?>">
                                                <?= $ev['target_type'] == 'all' ? 'Santri & Guru' : ($ev['target_type'] == 'students' ? 'Santri Saja' : 'Pengajar Saja') ?>
                                            </span>
                                            <?php if (!empty($ev['target_teachers'])): 
                                                $tIds = array_filter(explode(',', $ev['target_teachers']));
                                            ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200" title="Kewajiban Khusus <?= count($tIds) ?> Guru">
                                                    <i class="ri-user-star-line text-[10px]"></i> <?= count($tIds) ?> Guru Tertentu
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($ev['target_classes'])): 
                                                $cIds = array_filter(explode(',', $ev['target_classes']));
                                            ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" title="<?= count($cIds) ?> Kelas Santri">
                                                    <i class="ri-team-line text-[10px]"></i> <?= count($cIds) ?> Kelas
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs">
                                        <span class="text-gray-700 font-bold"><?= substr($ev['open_time'], 0, 5) ?></span>
                                        <span class="text-gray-400 mx-1">&rarr;</span>
                                        <span class="text-emerald-700 font-bold"><?= substr($ev['start_time'], 0, 5) ?></span>
                                        <span class="text-gray-400 mx-1">&rarr;</span>
                                        <span class="text-rose-600 font-bold"><?= substr($ev['close_time'], 0, 5) ?></span>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-gray-500">
                                        <?= $ev['allow_any_device'] ? '<span class="text-gray-600">Semua Terminal</span>' : '<span class="text-amber-700 font-semibold">Terminal Khusus</span>' ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold <?= $ev['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $ev['is_active'] ? 'bg-emerald-500' : 'bg-gray-400' ?>"></span>
                                            <?= $ev['is_active'] ? 'Aktif' : 'Non-Aktif' ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" onclick='editEvent(<?= json_encode($ev) ?>)' class="px-2.5 py-1 text-xs font-bold text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                                <i class="ri-edit-line"></i> Edit
                                            </button>
                                            <a href="<?= url('/smart-tap/events/delete?id=' . $ev['id']) ?>" onclick="return confirm('Hapus event insidental ini?')" class="p-1 text-gray-400 hover:text-rose-600 rounded-lg transition" title="Hapus">
                                                <i class="ri-delete-bin-line text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- SECTION 4: ARSIP & EVENT TERLEWAT (TABLE)       -->
    <!-- ============================================== -->
    <div id="section-archive" class="event-section space-y-3">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-gray-200 text-gray-600 flex items-center justify-center text-sm font-bold shadow-xs">
                        <i class="ri-archive-line"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-900">Arsip Event Terlewat & Riwayat</h3>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-gray-200 text-gray-700">
                                <?= count($archivedEvents) ?> Arsip
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-0.5">
                            Event insidental yang tanggalnya telah terlewati otomatis diarsipkan di sini agar tidak memenuhi jadwal aktif.
                        </p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/70 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                            <th class="py-3 px-4">Tanggal Acara</th>
                            <th class="py-3 px-4">Nama Kegiatan</th>
                            <th class="py-3 px-4">Target Peserta</th>
                            <th class="py-3 px-4">Jam Absensi</th>
                            <th class="py-3 px-4 text-center">Status Arsip</th>
                            <th class="py-3 px-4 text-center">Aksi / Gunakan Lagi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if (empty($archivedEvents)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-12 px-4 text-gray-400">
                                    <i class="ri-inbox-line text-3xl mb-1 block text-gray-300"></i>
                                    Belum ada arsip event yang tersimpan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($archivedEvents as $ev): ?>
                                <tr class="hover:bg-gray-50/60 transition text-gray-600">
                                    <td class="py-3.5 px-4 text-xs font-medium">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-semibold bg-gray-100 text-gray-600">
                                            <i class="ri-calendar-line text-[11px]"></i>
                                            <?= !empty($ev['event_date']) ? date('d M Y', strtotime($ev['event_date'])) : 'Terlewat' ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-gray-800">
                                        <?= htmlspecialchars($ev['name']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        <?= $ev['target_type'] == 'all' ? 'Santri & Pengajar' : ($ev['target_type'] == 'students' ? 'Santri' : 'Pengajar') ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-gray-500">
                                        <?= substr($ev['open_time'], 0, 5) ?> &mdash; <?= substr($ev['close_time'], 0, 5) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500">
                                            <i class="ri-time-line text-[10px]"></i> Selesai / Terlewat
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" onclick='duplicateEvent(<?= json_encode($ev) ?>)' class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-indigo-600 hover:bg-indigo-50 rounded-xl transition border border-indigo-200" title="Gunakan ulang data ini untuk tanggal baru">
                                                <i class="ri-file-copy-line"></i> Gunakan Lagi
                                            </button>
                                            <a href="<?= url('/smart-tap/events/delete?id=' . $ev['id']) ?>" onclick="return confirm('Hapus permanen arsip ini?')" class="p-1 text-gray-400 hover:text-rose-600 rounded-lg transition" title="Hapus Arsip">
                                                <i class="ri-delete-bin-line text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ============================================== -->
<!-- MODAL CREATE / EDIT / DUPLICATE EVENT          -->
<!-- ============================================== -->
<div id="modal-event" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-5 hidden">
    <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
        <!-- Header Sticky (Selalu Terlihat di Atas) -->
        <div class="p-5 sm:px-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/70 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl shadow-xs">
                    <i class="ri-calendar-event-line"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 tracking-tight" id="modal-event-title">Tambah Event Absensi</h3>
                    <p class="text-[11px] text-gray-500">Konfigurasi jam buka/tutup dan aturan presensi Smart Tap</p>
                </div>
            </div>
            <button type="button" onclick="closeEventModal()" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-800 flex items-center justify-center text-xl transition leading-none">&times;</button>
        </div>

        <!-- Form with 2-Column Grid Body -->
        <form method="POST" action="<?= url('/smart-tap/events/store') ?>" id="form_event_main" class="flex-1 flex flex-col min-h-0 overflow-hidden">
            <input type="hidden" name="id" id="event_id">
            <input type="hidden" name="event_type" id="hidden_event_type" value="daily">
            <input type="hidden" name="target_type" id="hidden_target_type" value="all">
            <input type="hidden" name="target_gender" id="hidden_target_gender" value="all">
            <input type="hidden" name="days_of_week" id="hidden_days_of_week" value="1,2,3,4,5,6,7">
            <input type="hidden" name="target_teachers" id="hidden_target_teachers" value="">
            <input type="hidden" name="target_classes" id="hidden_target_classes" value="">

            <div class="p-5 sm:p-6 overflow-y-auto space-y-5 flex-1">
                <!-- Row 1: Kategori Kegiatan (Full Width 3 Kolom) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Kategori Kegiatan <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex flex-col items-center justify-center p-3 border rounded-2xl cursor-pointer hover:bg-emerald-50/50 text-xs font-bold border-gray-200 transition text-center category-card" id="card-cat-sholat">
                            <input type="radio" name="category" value="sholat" checked onchange="onCategoryChange('sholat')" class="text-emerald-600 focus:ring-emerald-500 mb-1">
                            <span class="text-emerald-800">Sholat 5 Waktu</span>
                        </label>
                        <label class="flex flex-col items-center justify-center p-3 border rounded-2xl cursor-pointer hover:bg-blue-50/50 text-xs font-bold border-gray-200 transition text-center category-card" id="card-cat-office">
                            <input type="radio" name="category" value="office" onchange="onCategoryChange('office')" class="text-blue-600 focus:ring-blue-500 mb-1">
                            <span class="text-blue-800">Kantor (Office)</span>
                        </label>
                        <label class="flex flex-col items-center justify-center p-3 border rounded-2xl cursor-pointer hover:bg-amber-50/50 text-xs font-bold border-gray-200 transition text-center category-card" id="card-cat-incidental">
                            <input type="radio" name="category" value="incidental" onchange="onCategoryChange('incidental')" class="text-amber-600 focus:ring-amber-500 mb-1">
                            <span class="text-amber-800">Insidental</span>
                        </label>
                    </div>
                </div>

                <!-- Row 2: 2 Kolom Seimbang -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- KOLOM KIRI: Disesuaikan per Kategori -->
                    <div class="space-y-4">
                        <!-- ========================= -->
                        <!-- A. VIEW KHUSUS SHOLAT     -->
                        <!-- ========================= -->
                        <div id="view-sholat" class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Pilih Waktu Sholat <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" onclick="selectSholatPreset('Subuh')" class="sholat-pill px-2.5 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-emerald-50 hover:border-emerald-300 transition text-center">Subuh</button>
                                    <button type="button" onclick="selectSholatPreset('Dzuhur')" class="sholat-pill px-2.5 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-emerald-50 hover:border-emerald-300 transition text-center">Dzuhur</button>
                                    <button type="button" onclick="selectSholatPreset('Ashar')" class="sholat-pill px-2.5 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-emerald-50 hover:border-emerald-300 transition text-center">Ashar</button>
                                    <button type="button" onclick="selectSholatPreset('Maghrib')" class="sholat-pill px-2.5 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-emerald-50 hover:border-emerald-300 transition text-center">Maghrib</button>
                                    <button type="button" onclick="selectSholatPreset('Isya')" class="sholat-pill px-2.5 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 hover:bg-emerald-50 hover:border-emerald-300 transition text-center">Isya</button>
                                    <button type="button" onclick="selectSholatPreset('Jumat')" class="sholat-pill px-2.5 py-2 rounded-xl border border-emerald-300 bg-emerald-50/50 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition text-center">🕌 Sholat Jumat</button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Jadwal Sholat</label>
                                <input type="text" name="name" id="sholat_name" required placeholder="Contoh: Sholat Subuh" class="w-full text-xs rounded-2xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3 font-medium">
                            </div>

                            <!-- Smart Note Sholat -->
                            <div id="sholat_smart_banner" class="p-3 bg-emerald-50/70 rounded-2xl border border-emerald-200 text-[11px] text-emerald-900 leading-relaxed">
                                <span class="font-bold flex items-center gap-1.5 mb-0.5">
                                    <i class="ri-information-line text-emerald-600"></i> Aturan Rutin Sholat 5 Waktu
                                </span>
                                <span id="sholat_smart_text">
                                    Berjalan 24 jam penuh setiap hari untuk seluruh santri & ustadz.
                                </span>
                            </div>
                        </div>

                        <!-- ========================= -->
                        <!-- B. VIEW KHUSUS KANTOR     -->
                        <!-- ========================= -->
                        <div id="view-office" class="hidden space-y-3.5">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-gray-700">Nama Presensi Kantor <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center gap-1.5 text-[10px]">
                                        <button type="button" onclick="setOfficePreset('Masuk Kantor Pagi', '06:45', '07:15', '07:45')" class="text-blue-600 hover:underline font-bold">+ Pagi</button>
                                        <span class="text-gray-300">&bull;</span>
                                        <button type="button" onclick="setOfficePreset('Pulang Kantor Sore', '15:30', '16:00', '17:00')" class="text-blue-600 hover:underline font-bold">+ Sore</button>
                                    </div>
                                </div>
                                <input type="text" name="office_name" id="office_name" placeholder="Contoh: Masuk Kantor Pagi / Piket Siang" class="w-full text-xs rounded-2xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 py-2.5 px-3 font-medium">
                            </div>

                            <!-- Opsi Kalender Akademik -->
                            <div class="p-3 bg-blue-50/70 rounded-2xl border border-blue-200">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="sync_academic_calendar" id="office_sync_academic" value="1" checked class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <span class="text-xs font-bold text-blue-950 flex items-center gap-1">
                                            <i class="ri-calendar-check-line text-blue-600"></i> Ikuti Kalender Akademik KBM
                                        </span>
                                        <p class="text-[11px] text-blue-800/80 mt-0.5 leading-tight">
                                            Otomatis libur saat santri/KBM libur (libur kalender akademik & dispensasi KBM). Hilangkan centang jika staf kantor tetap wajib hadir saat KBM santri libur.
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <!-- Peserta Wajib Staf (Ringkas via Popup) -->
                            <div class="p-3 bg-indigo-50/70 rounded-2xl border border-indigo-100 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-indigo-950 block">Kewajiban Presensi Staf</span>
                                    <span class="text-[11px] text-indigo-700 font-semibold truncate block" id="office-participant-label">
                                        Seluruh Dewan Guru & Staf (Default)
                                    </span>
                                </div>
                                <button type="button" onclick="openParticipantModalFromForm('office')" class="flex-shrink-0 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1">
                                    <i class="ri-user-settings-line"></i> Atur Staf Khusus
                                </button>
                            </div>
                        </div>

                        <!-- ========================= -->
                        <!-- C. VIEW KHUSUS INSIDENTAL -->
                        <!-- ========================= -->
                        <div id="view-incidental" class="hidden space-y-3.5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Acara / Kegiatan <span class="text-rose-500">*</span></label>
                                <input type="text" name="incidental_name" id="incidental_name" placeholder="Contoh: Upacara Bendera / Rapat Pleno Guru" class="w-full text-xs rounded-2xl border-gray-200 focus:border-amber-500 focus:ring-amber-500 py-2.5 px-3 font-medium">
                            </div>

                            <!-- Pilihan Waktu Pelaksanaan -->
                            <div class="p-3 bg-gray-50/80 rounded-2xl border border-gray-200 space-y-2.5">
                                <label class="block text-xs font-bold text-gray-700">Waktu Pelaksanaan</label>
                                <div class="flex items-center gap-4 text-xs font-medium">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="incidental_time_mode" id="inc_time_one_off" value="one_off" checked onchange="toggleIncidentalTimeMode('one_off')" class="text-amber-600 focus:ring-amber-500">
                                        <span>Satu Kali (Tanggal Tertentu)</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="incidental_time_mode" id="inc_time_daily" value="daily" onchange="toggleIncidentalTimeMode('daily')" class="text-amber-600 focus:ring-amber-500">
                                        <span>Rutin Mingguan</span>
                                    </label>
                                </div>

                                <div id="box_incidental_date">
                                    <input type="date" name="event_date" id="incidental_date_input" value="<?= $today ?>" class="w-full text-xs rounded-xl border-gray-200 focus:border-amber-500 focus:ring-amber-500 py-2 px-3 font-medium bg-white">
                                </div>

                                <div id="box_incidental_days" class="hidden space-y-1.5">
                                    <div class="grid grid-cols-7 gap-1">
                                        <?php
                                        $dayNames = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Ahd'];
                                        foreach ($dayNames as $num => $dName):
                                        ?>
                                            <label class="flex flex-col items-center justify-center p-1.5 rounded-xl border border-gray-200 cursor-pointer hover:bg-white transition text-center select-none bg-white">
                                                <input type="checkbox" value="<?= $num ?>" checked onchange="syncIncidentalDays()" class="incidental-day-cb rounded text-amber-600 focus:ring-amber-500 mb-1 w-3.5 h-3.5">
                                                <span class="text-[11px] font-bold text-gray-700"><?= $dName ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Target Peserta Dropdown -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Target Peserta</label>
                                <select id="incidental_target_type_select" onchange="onIncidentalTargetTypeChange(this.value)" class="w-full text-xs rounded-2xl border-gray-200 focus:border-amber-500 focus:ring-amber-500 py-2.5 px-3 font-medium">
                                    <option value="all">Santri & Guru (Semua)</option>
                                    <option value="students">Santri Saja</option>
                                    <option value="teachers">Pengajar / Pegawai Saja</option>
                                </select>
                            </div>

                            <!-- Peserta Wajib Khusus (Ringkas via Popup) -->
                            <div class="p-3 bg-amber-50/70 rounded-2xl border border-amber-200 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-amber-950 block">Kewajiban Khusus</span>
                                    <span class="text-[11px] text-amber-800 font-semibold truncate block" id="incidental-participant-label">
                                        Seluruh Peserta Sesuai Target
                                    </span>
                                </div>
                                <button type="button" onclick="openParticipantModalFromForm('incidental')" class="flex-shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1">
                                    <i class="ri-user-search-line"></i> Pilih Peserta Tertentu
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN: Waktu, Toleransi & Terminal -->
                    <div class="space-y-4">
                        <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/80 space-y-3">
                            <span class="block text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="ri-time-line text-indigo-600"></i> Konfigurasi Jam & Toleransi
                            </span>
                            <div class="grid grid-cols-3 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Jam Buka</label>
                                    <input type="time" name="open_time" id="event_open_time" required class="w-full text-xs rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-2 font-mono">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-emerald-700 mb-1">Tepat Waktu</label>
                                    <input type="time" name="start_time" id="event_start_time" required class="w-full text-xs rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-2 font-mono text-emerald-800 font-bold">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-rose-600 mb-1">Jam Tutup</label>
                                    <input type="time" name="close_time" id="event_close_time" required class="w-full text-xs rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2 px-2 font-mono text-rose-800 font-bold">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Aturan Ketika Lewat Jam Tutup</label>
                            <select name="after_close_action" id="event_after_close_action" class="w-full text-xs rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3 font-medium">
                                <option value="reject">Tolak Absensi (Pintu ditutup, status Ghaib/Alpa)</option>
                                <option value="very_late">Tetap Terima (Masuk status 'Sangat Terlambat')</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Terminal / Titik Absensi yang Berlaku</label>
                            <div class="space-y-2 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-200/80">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                                    <input type="radio" name="allow_any_device" id="device_all_radio" value="1" checked onchange="toggleDeviceOptions(true)" class="text-indigo-600 focus:ring-indigo-500">
                                    <span>Semua Terminal (Fleksibel di seluruh titik)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                                    <input type="radio" name="allow_any_device" id="device_specific_radio" value="0" onchange="toggleDeviceOptions(false)" class="text-indigo-600 focus:ring-indigo-500">
                                    <span>Hanya Berlaku di Terminal Tertentu:</span>
                                </label>
                                <div id="device_checkboxes_container" class="hidden pl-6 pt-2 space-y-1.5 border-t border-gray-200/60 mt-2 max-h-32 overflow-y-auto">
                                    <?php foreach ($devices as $dev): ?>
                                        <label class="flex items-center gap-2 text-xs text-gray-600">
                                            <input type="checkbox" name="allowed_devices[]" value="<?= $dev['id'] ?>" class="dev-checkbox rounded text-indigo-600 focus:ring-indigo-500">
                                            <span><?= htmlspecialchars($dev['device_name']) ?> (<?= htmlspecialchars($dev['location'] ?: '-') ?>)</span>
                                        </label>
                                    <?php endforeach; ?>
                                    <?php if (empty($devices)): ?>
                                        <div class="text-[11px] text-gray-400 italic">Belum ada terminal terdaftar.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                                <input type="checkbox" name="is_active" id="event_is_active" value="1" checked class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span>Event Ini Aktif</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Sticky (Selalu Terlihat di Bawah) -->
            <div class="p-4 sm:px-6 bg-gray-50/70 border-t border-gray-100 flex items-center justify-end gap-2.5 flex-shrink-0">
                <button type="button" onclick="closeEventModal()" class="px-5 py-2.5 text-xs font-bold text-gray-600 hover:bg-gray-200/60 rounded-xl transition">Batal</button>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm shadow-indigo-200">Simpan Event</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- POPUP MODAL TERPISAH: PILIH PESERTA WAJIB (GURU & KELAS SANTRI)  -->
<!-- ============================================================== -->
<div id="modal-target-participants" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-gray-100 flex flex-col max-h-[90vh] overflow-hidden animate-scale-up">
        <!-- Header -->
        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/60">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="ri-user-star-line text-indigo-600"></i> Pilih Peserta Wajib Presensi
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Peserta yang dicentang wajib hadir. Yang tidak dicentang tidak akan kena kewajiban absen.</p>
            </div>
            <button type="button" onclick="closeParticipantModal()" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center text-lg">&times;</button>
        </div>

        <!-- Tab Bar -->
        <div class="px-5 pt-3 border-b border-gray-100 flex items-center gap-4 text-xs font-bold" id="popup-tab-bar">
            <button type="button" onclick="switchPopupTab('teachers')" id="popup-tab-teachers-btn" class="pb-2.5 border-b-2 border-indigo-600 text-indigo-600 flex items-center gap-1.5">
                <i class="ri-user-tie-line"></i> Dewan Guru & Staf
                <span class="bg-indigo-100 text-indigo-700 text-[10px] px-2 py-0.5 rounded-full" id="popup-badge-teacher-count">0 Terpilih</span>
            </button>
            <button type="button" onclick="switchPopupTab('classes')" id="popup-tab-classes-btn" class="pb-2.5 border-b-2 border-transparent text-gray-400 hover:text-gray-700 flex items-center gap-1.5">
                <i class="ri-team-line"></i> Kelas Santri
                <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded-full" id="popup-badge-class-count">0 Terpilih</span>
            </button>
        </div>

        <!-- Body -->
        <div class="p-5 overflow-y-auto flex-1 space-y-4">
            <!-- Tab 1: Guru -->
            <div id="popup-panel-teachers" class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="relative flex-1">
                        <i class="ri-search-line absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        <input type="text" id="popup_teacher_search" oninput="filterPopupTeacherList(this.value)" placeholder="Cari nama Ustadz / Ustadzah atau NIP..." class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50/50">
                    </div>
                    <div class="flex items-center gap-1.5 text-xs">
                        <button type="button" onclick="selectAllPopupTeachers(true)" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl font-bold transition">Pilih Semua</button>
                        <button type="button" onclick="selectAllPopupTeachers(false)" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition">Batal Semua</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto pr-1" id="popup-teachers-grid">
                    <?php foreach ($teachers as $t): ?>
                        <label class="flex items-center justify-between p-2.5 hover:bg-indigo-50/50 rounded-xl cursor-pointer border border-gray-100 transition popup-teacher-item" data-name="<?= strtolower(htmlspecialchars(($t['nama_gelar'] ?? '') . ' ' . ($t['nama'] ?? ''))) ?>">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <input type="checkbox" value="<?= $t['id'] ?>" onchange="updatePopupCounters()" class="popup-t-cb rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                <div class="truncate">
                                    <div class="text-xs font-bold text-gray-800 truncate"><?= htmlspecialchars($t['nama_gelar'] ?? $t['nama']) ?></div>
                                    <div class="text-[10px] text-gray-400 font-mono"><?= htmlspecialchars($t['nip'] ?: $t['username']) ?></div>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tab 2: Kelas Santri -->
            <div id="popup-panel-classes" class="hidden space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Centang kelas yang memiliki kewajiban hadir:</span>
                    <div class="flex items-center gap-1.5 text-xs">
                        <button type="button" onclick="selectAllPopupClasses(true)" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl font-bold transition">Pilih Semua</button>
                        <button type="button" onclick="selectAllPopupClasses(false)" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition">Batal Semua</button>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 max-h-72 overflow-y-auto pr-1">
                    <?php foreach ($classes as $c): ?>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-gray-100 hover:bg-blue-50/50 cursor-pointer transition">
                            <input type="checkbox" value="<?= $c['id'] ?>" onchange="updatePopupCounters()" class="popup-c-cb rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                            <span class="text-xs font-bold text-gray-700"><?= htmlspecialchars(($c['tingkat'] ?? '') . ' ' . ($c['abjad'] ?? '')) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
            <span class="text-xs text-gray-500 font-medium" id="popup-summary-text">0 Guru & 0 Kelas Terpilih</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeParticipantModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-xl transition">Batal</button>
                <button type="button" onclick="applyParticipantModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1">
                    <i class="ri-check-line"></i> Simpan & Terapkan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Form for Direct Participant Update (From Event Cards) -->
<form id="form_direct_participant_update" method="POST" action="<?= url('/smart-tap/events/update-participants') ?>" class="hidden">
    <input type="hidden" name="id" id="direct_event_id">
    <input type="hidden" name="target_teachers" id="direct_target_teachers">
    <input type="hidden" name="target_classes" id="direct_target_classes">
</form>

<script>
// Preset Data Sholat 5 Waktu & Sholat Jumat
const SHOLAT_CONFIG = {
    'Subuh': {
        name: 'Sholat Subuh',
        open: '04:00', start: '04:30', close: '05:00',
        gender: 'all', days: '1,2,3,4,5,6,7',
        banner: 'Berjalan 24 jam penuh setiap hari untuk seluruh jamaah (Santri & Ustadz).'
    },
    'Dzuhur': {
        name: 'Sholat Dzuhur',
        open: '11:45', start: '12:15', close: '12:45',
        gender: 'all', days: '1,2,3,4,5,6,7',
        banner: 'Bergulir tiap hari. Khusus di hari Jumat, otomatis berlaku untuk Jamaah Putri (Santriwati & Ustadzah), sedangkan Jamaah Putra Sholat Jumat.'
    },
    'Ashar': {
        name: 'Sholat Ashar',
        open: '15:00', start: '15:30', close: '16:00',
        gender: 'all', days: '1,2,3,4,5,6,7',
        banner: 'Berjalan 24 jam penuh setiap hari untuk seluruh jamaah (Santri & Ustadz).'
    },
    'Maghrib': {
        name: 'Sholat Maghrib',
        open: '17:45', start: '18:15', close: '18:45',
        gender: 'all', days: '1,2,3,4,5,6,7',
        banner: 'Berjalan 24 jam penuh setiap hari untuk seluruh jamaah (Santri & Ustadz).'
    },
    'Isya': {
        name: 'Sholat Isya',
        open: '19:00', start: '19:30', close: '20:00',
        gender: 'all', days: '1,2,3,4,5,6,7',
        banner: 'Berjalan 24 jam penuh setiap hari untuk seluruh jamaah (Santri & Ustadz).'
    },
    'Jumat': {
        name: 'Sholat Jumat',
        open: '11:15', start: '11:45', close: '12:30',
        gender: 'L', days: '5',
        banner: '🕌 Khusus Hari Jumat & Jamaah Putra (Santri & Ustadz). Menggantikan sholat Dzuhur bagi jamaah putra.'
    }
};

let currentModalMode = 'form'; // 'form' or 'direct'
let directTargetEventId = null;

// Tab Filter di Atas Halaman Utama
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('bg-indigo-50', 'text-indigo-700', 'border', 'border-indigo-200/60', 'shadow-xs');
        btn.classList.add('text-gray-500', 'hover:text-gray-900', 'hover:bg-gray-50');
    });

    const activeBtn = document.getElementById('tab-btn-' + tab);
    if (activeBtn) {
        activeBtn.classList.add('bg-indigo-50', 'text-indigo-700', 'border', 'border-indigo-200/60', 'shadow-xs');
        activeBtn.classList.remove('text-gray-500', 'hover:text-gray-900', 'hover:bg-gray-50');
    }

    const sSholat = document.getElementById('section-sholat');
    const sOffice = document.getElementById('section-office');
    const sIncidental = document.getElementById('section-incidental');
    const sArchive = document.getElementById('section-archive');

    if (tab === 'all') {
        sSholat.classList.remove('hidden');
        sOffice.classList.remove('hidden');
        sIncidental.classList.remove('hidden');
        sArchive.classList.remove('hidden');
    } else if (tab === 'sholat') {
        sSholat.classList.remove('hidden');
        sOffice.classList.add('hidden');
        sIncidental.classList.add('hidden');
        sArchive.classList.add('hidden');
    } else if (tab === 'office') {
        sSholat.classList.add('hidden');
        sOffice.classList.remove('hidden');
        sIncidental.classList.add('hidden');
        sArchive.classList.add('hidden');
    } else if (tab === 'incidental') {
        sSholat.classList.add('hidden');
        sOffice.classList.add('hidden');
        sIncidental.classList.remove('hidden');
        sArchive.classList.add('hidden');
    } else if (tab === 'archive') {
        sSholat.classList.add('hidden');
        sOffice.classList.add('hidden');
        sIncidental.classList.add('hidden');
        sArchive.classList.remove('hidden');
    }
}

// Buka Modal Tambah Event
function openEventModal(defaultCategory = 'sholat') {
    document.getElementById('event_id').value = '';
    document.getElementById('hidden_target_teachers').value = '';
    document.getElementById('hidden_target_classes').value = '';
    document.getElementById('event_is_active').checked = true;

    setCategoryRadio(defaultCategory);

    // Default Sholat: Subuh
    if (defaultCategory === 'sholat') {
        selectSholatPreset('Subuh');
    } else if (defaultCategory === 'office') {
        setOfficePreset('Masuk Kantor Pagi', '06:45', '07:15', '07:45');
    } else {
        document.getElementById('incidental_name').value = '';
        document.getElementById('event_open_time').value = '06:30';
        document.getElementById('event_start_time').value = '07:00';
        document.getElementById('event_close_time').value = '07:30';
        toggleIncidentalTimeMode('one_off');
    }

    updateParticipantBadges();
    document.getElementById('modal-event-title').innerText = 'Tambah Event Baru';
    document.getElementById('modal-event').classList.remove('hidden');
}

function setCategoryRadio(cat) {
    const radio = document.querySelector(`input[name="category"][value="${cat}"]`);
    if (radio) radio.checked = true;
    onCategoryChange(cat);
}

function onCategoryChange(cat) {
    // Sembunyikan semua subview
    document.getElementById('view-sholat').classList.add('hidden');
    document.getElementById('view-office').classList.add('hidden');
    document.getElementById('view-incidental').classList.add('hidden');

    // Highlight card
    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('border-emerald-500', 'border-blue-500', 'border-amber-500', 'bg-emerald-50/40', 'bg-blue-50/40', 'bg-amber-50/40'));

    if (cat === 'sholat') {
        document.getElementById('view-sholat').classList.remove('hidden');
        document.getElementById('card-cat-sholat').classList.add('border-emerald-500', 'bg-emerald-50/40');
        document.getElementById('hidden_event_type').value = 'daily';
        document.getElementById('hidden_target_type').value = 'all';
        document.getElementById('event_after_close_action').value = 'reject';
    } else if (cat === 'office') {
        document.getElementById('view-office').classList.remove('hidden');
        document.getElementById('card-cat-office').classList.add('border-blue-500', 'bg-blue-50/40');
        document.getElementById('hidden_event_type').value = 'daily';
        document.getElementById('hidden_target_type').value = 'teachers';
        document.getElementById('hidden_target_gender').value = 'all';
        document.getElementById('hidden_days_of_week').value = '1,2,3,4,5,6,7';
        document.getElementById('event_after_close_action').value = 'very_late';
    } else { // incidental
        document.getElementById('view-incidental').classList.remove('hidden');
        document.getElementById('card-cat-incidental').classList.add('border-amber-500', 'bg-amber-50/40');
        document.getElementById('event_after_close_action').value = 'very_late';
        syncIncidentalDays();
    }
}

// Preset Sholat 5 Waktu & Jumat
function selectSholatPreset(key) {
    const cfg = SHOLAT_CONFIG[key];
    if (!cfg) return;

    // Highlight pill
    document.querySelectorAll('.sholat-pill').forEach(btn => {
        btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
        btn.classList.add('text-gray-700', 'border-gray-200');
    });
    event?.target?.classList?.add('bg-emerald-600', 'text-white', 'border-emerald-600');

    document.getElementById('sholat_name').value = cfg.name;
    document.getElementById('event_open_time').value = cfg.open;
    document.getElementById('event_start_time').value = cfg.start;
    document.getElementById('event_close_time').value = cfg.close;

    document.getElementById('hidden_target_gender').value = cfg.gender;
    document.getElementById('hidden_days_of_week').value = cfg.days;
    document.getElementById('hidden_event_type').value = 'daily';
    document.getElementById('hidden_target_type').value = 'all';

    document.getElementById('sholat_smart_text').innerText = cfg.banner;
}

// Preset Kantor Pagi / Sore
function setOfficePreset(name, open, start, close) {
    document.getElementById('office_name').value = name;
    document.getElementById('event_open_time').value = open;
    document.getElementById('event_start_time').value = start;
    document.getElementById('event_close_time').value = close;
}

// Mode Waktu Insidental (Satu Kali vs Rutin Mingguan)
function toggleIncidentalTimeMode(mode) {
    document.getElementById('hidden_event_type').value = mode;
    if (mode === 'one_off') {
        document.getElementById('box_incidental_date').classList.remove('hidden');
        document.getElementById('box_incidental_days').classList.add('hidden');
    } else {
        document.getElementById('box_incidental_date').classList.add('hidden');
        document.getElementById('box_incidental_days').classList.remove('hidden');
        syncIncidentalDays();
    }
}

function syncIncidentalDays() {
    const checked = [];
    document.querySelectorAll('.incidental-day-cb:checked').forEach(cb => checked.push(cb.value));
    document.getElementById('hidden_days_of_week').value = checked.join(',') || '1,2,3,4,5,6,7';
}

function onIncidentalTargetTypeChange(val) {
    document.getElementById('hidden_target_type').value = val;
    updateParticipantBadges();
}

// Sinkronisasi Nama saat Submit
document.getElementById('form_event_main').addEventListener('submit', function(e) {
    const cat = document.querySelector('input[name="category"]:checked')?.value || 'sholat';
    let nameField = null;

    if (cat === 'sholat') {
        nameField = document.getElementById('sholat_name');
    } else if (cat === 'office') {
        nameField = document.getElementById('office_name');
    } else {
        nameField = document.getElementById('incidental_name');
    }

    if (!nameField || !nameField.value.trim()) {
        e.preventDefault();
        alert('Silakan isi Nama Kegiatan / Event.');
        if (nameField) nameField.focus();
        return false;
    }

    // Pastikan input name yang dikirim ke server berisi value yang benar
    nameField.setAttribute('name', 'name');
    if (cat !== 'sholat') document.getElementById('sholat_name').removeAttribute('name');
    if (cat !== 'office') document.getElementById('office_name').removeAttribute('name');
    if (cat !== 'incidental') document.getElementById('incidental_name').removeAttribute('name');
});

function editEvent(ev) {
    document.getElementById('event_id').value = ev.id;
    let cat = ev.category || 'sholat';
    if (!ev.category) {
        if (ev.event_type === 'one_off') cat = 'incidental';
        else if (ev.name.toLowerCase().includes('kantor') || ev.name.toLowerCase().includes('piket')) cat = 'office';
    }

    setCategoryRadio(cat);

    // Isi Nama
    if (cat === 'sholat') {
        document.getElementById('sholat_name').value = ev.name;
        // Deteksi banner preset
        if (ev.name.toLowerCase().includes('jumat')) {
            document.getElementById('sholat_smart_text').innerText = SHOLAT_CONFIG['Jumat'].banner;
        } else if (ev.name.toLowerCase().includes('dzuhur')) {
            document.getElementById('sholat_smart_text').innerText = SHOLAT_CONFIG['Dzuhur'].banner;
        } else {
            document.getElementById('sholat_smart_text').innerText = 'Berjalan 24 jam penuh setiap hari untuk seluruh jamaah (Santri & Ustadz).';
        }
    } else if (cat === 'office') {
        document.getElementById('office_name').value = ev.name;
        document.getElementById('office_sync_academic').checked = (ev.sync_academic_calendar == 1);
    } else {
        document.getElementById('incidental_name').value = ev.name;
        document.getElementById('incidental_target_type_select').value = ev.target_type || 'all';
        if (ev.event_type === 'one_off') {
            document.getElementById('inc_time_one_off').checked = true;
            document.getElementById('incidental_date_input').value = ev.event_date || '<?= $today ?>';
            toggleIncidentalTimeMode('one_off');
        } else {
            document.getElementById('inc_time_daily').checked = true;
            toggleIncidentalTimeMode('daily');
            const d = (ev.days_of_week || '1,2,3,4,5,6,7').split(',');
            document.querySelectorAll('.incidental-day-cb').forEach(cb => {
                cb.checked = d.includes(cb.value);
            });
        }
    }

    // Hidden inputs
    document.getElementById('hidden_event_type').value = ev.event_type;
    document.getElementById('hidden_target_type').value = ev.target_type;
    document.getElementById('hidden_target_gender').value = ev.target_gender || 'all';
    document.getElementById('hidden_days_of_week').value = ev.days_of_week || '1,2,3,4,5,6,7';
    document.getElementById('hidden_target_teachers').value = ev.target_teachers || '';
    document.getElementById('hidden_target_classes').value = ev.target_classes || '';

    // Times
    document.getElementById('event_open_time').value = (ev.open_time || '').substr(0, 5);
    document.getElementById('event_start_time').value = (ev.start_time || '').substr(0, 5);
    document.getElementById('event_close_time').value = (ev.close_time || '').substr(0, 5);
    document.getElementById('event_after_close_action').value = ev.after_close_action;
    document.getElementById('event_is_active').checked = ev.is_active == 1;

    // Devices
    const isAllDev = ev.allow_any_device == 1;
    document.getElementById('device_all_radio').checked = isAllDev;
    document.getElementById('device_specific_radio').checked = !isAllDev;
    toggleDeviceOptions(isAllDev);

    const allowedList = (ev.allowed_devices || '').split(',');
    document.querySelectorAll('.dev-checkbox').forEach(cb => {
        cb.checked = allowedList.includes(cb.value);
    });

    updateParticipantBadges();
    document.getElementById('modal-event-title').innerText = 'Edit Event: ' + ev.name;
    document.getElementById('modal-event').classList.remove('hidden');
}

// Buka Ulang Event Arsip / Duplikat
function duplicateEvent(ev) {
    editEvent(ev);
    document.getElementById('event_id').value = '';
    document.getElementById('modal-event-title').innerText = 'Gunakan Ulang Event: ' + ev.name;
}

function closeEventModal() {
    document.getElementById('modal-event').classList.add('hidden');
}

function toggleDeviceOptions(isAll) {
    const container = document.getElementById('device_checkboxes_container');
    if (isAll) {
        container.classList.add('hidden');
    } else {
        container.classList.remove('hidden');
    }
}

// ==============================================================
// POPUP LOGIC: PARTICIPANT MODAL
// ==============================================================
function updateParticipantBadges() {
    const tVal = document.getElementById('hidden_target_teachers').value;
    const cVal = document.getElementById('hidden_target_classes').value;

    const tArr = tVal ? tVal.split(',').filter(Boolean) : [];
    const cArr = cVal ? cVal.split(',').filter(Boolean) : [];

    // Office Badge
    const officeLabel = document.getElementById('office-participant-label');
    if (officeLabel) {
        if (tArr.length > 0) {
            officeLabel.innerHTML = `<span class="text-indigo-900 font-bold bg-indigo-100 px-2 py-0.5 rounded-md">✓ ${tArr.length} Staf Khusus Terpilih</span>`;
        } else {
            officeLabel.innerText = 'Seluruh Dewan Guru & Staf (Default)';
        }
    }

    // Incidental Badge
    const incLabel = document.getElementById('incidental-participant-label');
    if (incLabel) {
        let parts = [];
        if (tArr.length > 0) parts.push(`${tArr.length} Guru`);
        if (cArr.length > 0) parts.push(`${cArr.length} Kelas`);
        if (parts.length > 0) {
            incLabel.innerHTML = `<span class="text-amber-900 font-bold bg-amber-100 px-2 py-0.5 rounded-md">✓ Khusus: ${parts.join(' & ')}</span>`;
        } else {
            incLabel.innerText = 'Seluruh Peserta Sesuai Target';
        }
    }
}

function openParticipantModalFromForm(context) {
    currentModalMode = 'form';
    directTargetEventId = null;

    // Load data dari hidden input form
    const currentTeachers = (document.getElementById('hidden_target_teachers').value || '').split(',').map(s => s.trim()).filter(Boolean);
    const currentClasses = (document.getElementById('hidden_target_classes').value || '').split(',').map(s => s.trim()).filter(Boolean);

    loadPopupSelections(currentTeachers, currentClasses);

    // Sesuaikan tab yang relevan
    if (context === 'office') {
        document.getElementById('popup-tab-classes-btn').classList.add('hidden');
        switchPopupTab('teachers');
    } else {
        document.getElementById('popup-tab-classes-btn').classList.remove('hidden');
        switchPopupTab('teachers');
    }

    document.getElementById('modal-target-participants').classList.remove('hidden');
}

function openDirectParticipantModal(ev) {
    currentModalMode = 'direct';
    directTargetEventId = ev.id;

    const currentTeachers = (ev.target_teachers || '').split(',').map(s => s.trim()).filter(Boolean);
    const currentClasses = (ev.target_classes || '').split(',').map(s => s.trim()).filter(Boolean);

    loadPopupSelections(currentTeachers, currentClasses);

    if (ev.category === 'office' || ev.target_type === 'teachers') {
        document.getElementById('popup-tab-classes-btn').classList.add('hidden');
        switchPopupTab('teachers');
    } else {
        document.getElementById('popup-tab-classes-btn').classList.remove('hidden');
        switchPopupTab('teachers');
    }

    document.getElementById('modal-target-participants').classList.remove('hidden');
}

function loadPopupSelections(teachers, classes) {
    document.querySelectorAll('.popup-t-cb').forEach(cb => {
        cb.checked = teachers.includes(cb.value);
    });
    document.querySelectorAll('.popup-c-cb').forEach(cb => {
        cb.checked = classes.includes(cb.value);
    });
    document.getElementById('popup_teacher_search').value = '';
    filterPopupTeacherList('');
    updatePopupCounters();
}

function switchPopupTab(tab) {
    const tBtn = document.getElementById('popup-tab-teachers-btn');
    const cBtn = document.getElementById('popup-tab-classes-btn');
    const tPanel = document.getElementById('popup-panel-teachers');
    const cPanel = document.getElementById('popup-panel-classes');

    if (tab === 'teachers') {
        tBtn.classList.add('border-indigo-600', 'text-indigo-600');
        tBtn.classList.remove('border-transparent', 'text-gray-400');
        cBtn.classList.remove('border-indigo-600', 'text-indigo-600');
        cBtn.classList.add('border-transparent', 'text-gray-400');
        tPanel.classList.remove('hidden');
        cPanel.classList.add('hidden');
    } else {
        cBtn.classList.add('border-indigo-600', 'text-indigo-600');
        cBtn.classList.remove('border-transparent', 'text-gray-400');
        tBtn.classList.remove('border-indigo-600', 'text-indigo-600');
        tBtn.classList.add('border-transparent', 'text-gray-400');
        cPanel.classList.remove('hidden');
        tPanel.classList.add('hidden');
    }
}

function updatePopupCounters() {
    const selectedT = document.querySelectorAll('.popup-t-cb:checked').length;
    const selectedC = document.querySelectorAll('.popup-c-cb:checked').length;

    document.getElementById('popup-badge-teacher-count').innerText = `${selectedT} Terpilih`;
    document.getElementById('popup-badge-class-count').innerText = `${selectedC} Terpilih`;
    document.getElementById('popup-summary-text').innerText = `${selectedT} Guru & ${selectedC} Kelas Terpilih`;
}

function selectAllPopupTeachers(select) {
    document.querySelectorAll('.popup-teacher-item:not([style*="display: none"]) .popup-t-cb').forEach(cb => {
        cb.checked = select;
    });
    updatePopupCounters();
}

function selectAllPopupClasses(select) {
    document.querySelectorAll('.popup-c-cb').forEach(cb => {
        cb.checked = select;
    });
    updatePopupCounters();
}

function filterPopupTeacherList(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.popup-teacher-item').forEach(item => {
        const name = item.getAttribute('data-name') || '';
        item.style.display = name.includes(query) ? '' : 'none';
    });
}

function closeParticipantModal() {
    document.getElementById('modal-target-participants').classList.add('hidden');
}

function applyParticipantModal() {
    const selectedTeachers = [];
    document.querySelectorAll('.popup-t-cb:checked').forEach(cb => selectedTeachers.push(cb.value));

    const selectedClasses = [];
    document.querySelectorAll('.popup-c-cb:checked').forEach(cb => selectedClasses.push(cb.value));

    const tStr = selectedTeachers.join(',');
    const cStr = selectedClasses.join(',');

    if (currentModalMode === 'form') {
        document.getElementById('hidden_target_teachers').value = tStr;
        document.getElementById('hidden_target_classes').value = cStr;
        updateParticipantBadges();
        closeParticipantModal();
    } else if (currentModalMode === 'direct' && directTargetEventId) {
        document.getElementById('direct_event_id').value = directTargetEventId;
        document.getElementById('direct_target_teachers').value = tStr;
        document.getElementById('direct_target_classes').value = cStr;
        document.getElementById('form_direct_participant_update').submit();
    }
}

// Live Search for Incidental Table
function filterIncidental(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.incidental-row').forEach(row => {
        const name = row.querySelector('.event-name').innerText.toLowerCase();
        if (name.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php
renderFooter();
?>
