<?php renderHeader($title); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Compact Filter Bar -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-3 mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4 pl-1">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                <i class="ri-checkbox-circle-line text-xl"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900 leading-tight"><?= date('d M Y', strtotime($selectedDate)) ?></h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Total <?= count($dailySchedule) ?> jadwal verifikasi</p>
            </div>
            <?php if ($isPiketToday): ?>
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 uppercase tracking-wider">
                    Piket Hari Ini
                </span>
            <?php endif; ?>
        </div>
        
        <form method="GET" class="flex items-center gap-2 w-full md:w-auto">
            <div class="relative flex-1 md:flex-none">
                <input type="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()" 
                       class="block w-full md:w-48 pl-3 pr-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none">
            </div>
            <a href="?date=<?= date('Y-m-d') ?>" 
               class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm flex-shrink-0">
                Hari Ini
            </a>
        </form>
    </div>

    <?php if (empty($dailySchedule)): ?>
        <div class="text-center py-12 bg-white rounded-2xl shadow-sm border border-gray-100">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada jadwal</h3>
            <p class="mt-1 text-sm text-gray-500">Tidak ada kegiatan belajar mengajar pada hari ini.</p>
        </div>
    <?php else: 
        // Group by Jam
        $slotsByJam = [];
        foreach ($dailySchedule as $slot) {
            $slotsByJam[$slot['hour']][] = $slot;
        }
        ksort($slotsByJam);
        reset($slotsByJam); $firstJam = key($slotsByJam);
        $activeJam = $_GET['jam'] ?? ($currentDetectedHour ?? $firstJam);
        if (!isset($slotsByJam[$activeJam])) $activeJam = $firstJam;
    ?>
    
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden sticky top-20 z-30">
                <div class="hidden md:block px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Jam Pelajaran</h3>
                </div>
                <!-- Mobile Scroll Hint -->
                <div class="md:hidden flex items-center justify-between px-4 py-2 border-b border-gray-50 bg-gray-50/30">
                    <span class="text-[10px] font-bold text-indigo-500 flex items-center gap-1 uppercase tracking-wider">
                        <i class="ri-arrow-left-right-line"></i> Geser jam
                    </span>
                    <div class="flex gap-1">
                        <div class="w-1 h-1 rounded-full bg-indigo-200 animate-pulse"></div>
                        <div class="w-1 h-1 rounded-full bg-indigo-400 animate-pulse" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
                <nav class="p-2 flex md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-3 md:pb-2 no-scrollbar snap-x">
                    <?php for ($jam = 1; $jam <= 7; $jam++): 
                        $slots = $slotsByJam[$jam] ?? [];
                        $isActive = $jam == $activeJam;
                        $count = count($slots);
                        $doneCount = 0;
                        $pendingCount = 0;
                        foreach($slots as $s) {
                            if($s['is_verified']) $doneCount++;
                            else $pendingCount++;
                        }
                        $isComplete = $count > 0 && $doneCount === $count;
                        
                        $badgeClass = $isComplete
                            ? 'bg-green-100 text-green-700'
                            : ($doneCount > 0
                                ? 'bg-yellow-100 text-yellow-700'
                                : 'bg-gray-100 text-gray-400');
                        
                        $activeClass = $isActive 
                            ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-sm ring-1 ring-indigo-100' 
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
                        
                        $isDetected = ($jam == ($currentDetectedHour ?? ''));
                    ?>
                        <a href="?date=<?= htmlspecialchars($selectedDate) ?>&jam=<?= $jam ?>" 
                           id="<?= $isActive ? 'active-jam-tab' : '' ?>"
                           class="whitespace-nowrap flex items-center justify-between px-3 py-2.5 text-sm rounded-xl transition-all duration-200 group <?= $activeClass ?> snap-start min-w-[55%] md:min-w-0">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-xs <?= $isActive ? 'bg-indigo-600 text-white shadow-indigo-200 shadow-lg' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200' ?>">
                                    <?= $jam ?>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold leading-none">Jam ke-<?= $jam ?></span>
                                    <?php if ($pendingCount > 0): ?>
                                        <span class="text-[9px] text-red-500 font-bold mt-1"><?= $pendingCount ?> belum</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isDetected): ?>
                                    <span class="flex h-2 w-2 rounded-full bg-indigo-500 animate-pulse flex-shrink-0" title="Sedang Berlangsung"></span>
                                <?php endif; ?>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold ml-2 <?= $badgeClass ?>">
                                <?= $doneCount ?>/<?= $count ?>
                            </span>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="mb-6 flex flex-col sm:flex-row gap-4 justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-gray-200">
                <div class="w-full sm:w-1/2 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari data..." 
                        class="block w-full pl-10 pr-3 py-2 bg-gray-50 border border-gray-100 rounded-xl leading-5 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white sm:text-sm transition-all">
                </div>
                
                <div class="w-full sm:w-auto flex bg-gray-100 p-1 rounded-xl">
                    <button onclick="filterList('pending')" id="tab-pending" class="flex-1 sm:flex-none px-4 py-1.5 text-xs font-bold rounded-lg shadow-sm bg-white text-gray-900 transition-all">
                        Belum (<span id="count-pending">0</span>)
                    </button>
                    <button onclick="filterList('verified')" id="tab-verified" class="flex-1 sm:flex-none px-4 py-1.5 text-xs font-bold rounded-lg text-gray-500 hover:text-gray-700 transition-all">
                        Sudah (<span id="count-verified">0</span>)
                    </button>
                    <button onclick="filterList('all')" id="tab-all" class="flex-1 sm:flex-none px-4 py-1.5 text-xs font-bold rounded-lg text-gray-500 hover:text-gray-700 transition-all">
                        Semua
                    </button>
                </div>
            </div>

            <div id="tanqih-list" class="grid grid-cols-1 gap-4">
                <?php foreach ($dailySchedule as $item): 
                    $isVerified = $item['is_verified'];
                    $isJustified = $isVerified && isset($item['verification']['status']) && $item['verification']['status'] === 'justified';
                    $timestamp = $isVerified ? ($item['verification']['timestamp'] ?? 0) : 0;
                ?>
                <div class="bg-white rounded-2xl border-2 <?= $isVerified ? 'border-green-100 bg-green-50/10' : 'border-gray-100' ?> p-4 hover:shadow-md transition-all schedule-item" 
                    data-name="<?= strtolower(htmlspecialchars($item['teacher_name'] . ' ' . $item['subject_name'] . ' ' . $item['kelas_name'])) ?>"
                    data-status="<?= $isVerified ? 'verified' : 'pending' ?>"
                    data-timestamp="<?= $timestamp ?>"
                    data-hour="<?= $item['hour'] ?>"
                    data-original-order="<?= $item['hour'] * 1000 + $item['kelas_id'] ?>">
                    
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-[10px] font-bold border border-indigo-100">
                                    <?= htmlspecialchars($item['kelas_name']) ?>
                                </span>
                                <!-- Jam Badge (Hidden by default, shown during search) -->
                                <span class="jam-badge hidden px-2 py-0.5 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-bold border border-gray-200">
                                    Jam <?= $item['hour'] ?>
                                </span>
                                <?php if ($isVerified): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $isJustified ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' ?>">
                                        <i class="ri-checkbox-circle-fill mr-1"></i> <?= $isJustified ? 'Justifikasi' : 'Terverifikasi' ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h4 class="text-base font-bold text-gray-900 truncate"><?= htmlspecialchars($item['teacher_name']) ?></h4>
                            <p class="text-xs text-gray-500 font-medium"><?= htmlspecialchars($item['subject_name']) ?></p>
                        </div>

                        <div class="flex items-center gap-3">
                            <?php if ($isVerified): ?>
                                <div class="hidden sm:block text-right">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Oleh <?= htmlspecialchars($item['verifier_name']) ?></p>
                                    <p class="text-xs text-gray-500"><?= date('H:i', $item['verification']['timestamp']) ?></p>
                                </div>
                                <?php if ($canVerify): ?>
                                <form action="<?= url('/tanqih/verify') ?>" method="POST">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="date" value="<?= $selectedDate ?>">
                                    <input type="hidden" name="kelas_id" value="<?= $item['kelas_id'] ?>">
                                    <input type="hidden" name="hour" value="<?= $item['hour'] ?>">
                                    <input type="hidden" name="pengajar_id" value="<?= $item['pengajar_id'] ?>">
                                    <input type="hidden" name="action" value="unverify">
                                    <input type="hidden" name="ajax" value="1">
                                    <button type="button" onclick="verifyAsync(this.form)" class="w-10 h-10 rounded-xl bg-gray-100 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center shadow-sm" title="Batalkan Verifikasi?">
                                        <i class="ri-close-line text-xl"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($canVerify): ?>
                                <form id="form-verify-<?= $item['kelas_id'] ?>-<?= $item['hour'] ?>" action="<?= url('/tanqih/verify') ?>" method="POST">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="date" value="<?= $selectedDate ?>">
                                    <input type="hidden" name="kelas_id" value="<?= $item['kelas_id'] ?>">
                                    <input type="hidden" name="hour" value="<?= $item['hour'] ?>">
                                    <input type="hidden" name="pengajar_id" value="<?= $item['pengajar_id'] ?>">
                                    <input type="hidden" name="action" value="verify">
                                    <input type="hidden" name="status" id="status-<?= $item['kelas_id'] ?>-<?= $item['hour'] ?>" value="verified">
                                    <input type="hidden" name="ajax" value="1">
                                    <button type="button" 
                                        onclick="openVerifyModal(<?= htmlspecialchars(json_encode($item['teacher_name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($item['subject_name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($item['kelas_name']), ENT_QUOTES) ?>, '<?= $item['hour'] ?>', 'form-verify-<?= $item['kelas_id'] ?>-<?= $item['hour'] ?>', 'status-<?= $item['kelas_id'] ?>-<?= $item['hour'] ?>')"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                                        Verifikasi
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="text-xs font-bold text-gray-300 uppercase italic tracking-widest">Belum</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

<!-- Verification Modal -->
<div id="verifyModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeVerifyModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Konfirmasi Verifikasi
                    </h3>
                    <div class="mt-4 bg-gray-50 rounded-lg p-4 space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between border-b pb-2">
                            <span class="font-medium">Pengajar:</span>
                            <span class="text-gray-900 font-bold" id="modal-pengajar"></span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="font-medium">Mata Pelajaran:</span>
                            <span class="text-gray-900" id="modal-mapel"></span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="font-medium">Kelas:</span>
                            <span class="text-gray-900" id="modal-kelas"></span>
                        </div>
                        <div class="flex justify-between pt-2">
                            <span class="font-medium">Jam Ke:</span>
                            <span class="text-gray-900 font-bold" id="modal-jam"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                <button type="button" onclick="submitVerify('verified')" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                    Verifikasi
                </button>
                <button type="button" onclick="submitVerify('justified')" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:w-auto sm:text-sm">
                    Justifikasi
                </button>
                <button type="button" onclick="closeVerifyModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentVerifyFormId = null;
let currentStatusInputId = null;

function openVerifyModal(pengajar, mapel, kelas, jam, formId, statusInputId) {
    document.getElementById('modal-pengajar').textContent = pengajar;
    document.getElementById('modal-mapel').textContent = mapel;
    document.getElementById('modal-kelas').textContent = kelas;
    document.getElementById('modal-jam').textContent = jam;
    currentVerifyFormId = formId;
    currentStatusInputId = statusInputId;
    
    document.getElementById('verifyModal').classList.remove('hidden');
}

function closeVerifyModal() {
    document.getElementById('verifyModal').classList.add('hidden');
    currentVerifyFormId = null;
    currentStatusInputId = null;
}

function submitVerify(status) {
    if (currentVerifyFormId && currentStatusInputId) {
        document.getElementById(currentStatusInputId).value = status;
        const form = document.getElementById(currentVerifyFormId);
        verifyAsync(form);
        closeVerifyModal();
    }
}

let currentActiveJam = <?= json_encode($activeJam) ?>;

function filterList(status) {
    const searchInput = document.getElementById('searchInput');
    const search = searchInput.value.toLowerCase();
    const listContainer = document.getElementById('tanqih-list');
    const items = Array.from(document.querySelectorAll('.schedule-item'));
    const isSearching = search.length > 0;
        
        // Sorting Logic
        if (status === 'verified') {
            items.sort((a, b) => {
                return b.getAttribute('data-timestamp') - a.getAttribute('data-timestamp');
            });
        } else {
            items.sort((a, b) => {
                return a.getAttribute('data-original-order') - b.getAttribute('data-original-order');
            });
        }
        
        items.forEach(item => listContainer.appendChild(item));

        document.querySelectorAll('button[id^="tab-"]').forEach(btn => {
            btn.className = 'flex-1 sm:flex-none px-4 py-1.5 text-xs font-bold rounded-lg text-gray-500 hover:text-gray-700 transition-all';
        });
        
        const activeBtn = document.getElementById('tab-' + status);
        if(activeBtn) {
            activeBtn.className = 'flex-1 sm:flex-none px-4 py-1.5 text-xs font-bold rounded-lg shadow-sm bg-white text-gray-900 transition-all';
        }

        items.forEach(item => {
            const name = item.getAttribute('data-name');
            const itemStatus = item.getAttribute('data-status');
            const itemHour = item.getAttribute('data-hour');
            
            let matchesSearch = name.includes(search);
            let matchesStatus = (status === 'all') || (itemStatus === status);
            let matchesHour = isSearching || (itemHour == currentActiveJam);

            if (matchesSearch && matchesStatus && matchesHour) {
                item.style.display = '';
                if (itemStatus === 'pending') countPending++;
                if (itemStatus === 'verified') countVerified++;
                
                // Show Jam Badge if searching
                const jamBadge = item.querySelector('.jam-badge');
                if (jamBadge) {
                    if (isSearching) jamBadge.classList.remove('hidden');
                    else jamBadge.classList.add('hidden');
                }
            } else {
                item.style.display = 'none';
            }
        });
        
        updateStats();
    }

    function updateStats() {
        const items = Array.from(document.querySelectorAll('.schedule-item'));
        const searchInput = document.getElementById('searchInput');
        const search = searchInput.value.toLowerCase();
        const isSearching = search.length > 0;
        const currentStatusTab = Array.from(document.querySelectorAll('button[id^="tab-"]')).find(b => b.classList.contains('bg-white'))?.id.replace('tab-', '') || 'all';

        // 1. Update Sidebar Badges (Global Jam Stats - Not filtered by search)
        const jamStats = {};
        for(let j=1; j<=7; j++) jamStats[j] = { total: 0, verified: 0, pending: 0 };
        
        items.forEach(item => {
            const h = item.getAttribute('data-hour');
            const s = item.getAttribute('data-status');
            if (jamStats[h]) {
                jamStats[h].total++;
                if (s === 'verified') jamStats[h].verified++;
                else jamStats[h].pending++;
            }
        });

        for(let j=1; j<=7; j++) {
            const sidebarBtn = document.querySelector(`a[href*="jam=${j}"]`);
            if (sidebarBtn) {
                const badge = sidebarBtn.querySelector('.inline-flex');
                const pendingText = sidebarBtn.querySelector('.text-red-500');
                const numBadge = sidebarBtn.querySelector('.w-8.h-8');
                
                if (badge) badge.textContent = `${jamStats[j].verified}/${jamStats[j].total}`;
                
                if (pendingText) {
                    if (jamStats[j].pending > 0) {
                        pendingText.textContent = `${jamStats[j].pending} belum`;
                        pendingText.style.display = '';
                    } else {
                        pendingText.style.display = 'none';
                    }
                }

                // Update badge color based on completion
                if (badge) {
                    const isComplete = jamStats[j].total > 0 && jamStats[j].verified === jamStats[j].total;
                    if (isComplete) {
                        badge.className = 'inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold ml-2 bg-green-100 text-green-700';
                    } else if (jamStats[j].verified > 0) {
                        badge.className = 'inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold ml-2 bg-yellow-100 text-yellow-700';
                    } else {
                        badge.className = 'inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold ml-2 bg-gray-100 text-gray-400';
                    }
                }
            }
        }

        // 2. Update Header Tabs (Context Stats - Current Jam/Search)
        let visiblePending = 0;
        let visibleVerified = 0;
        
        items.forEach(item => {
            const name = item.getAttribute('data-name');
            const itemStatus = item.getAttribute('data-status');
            const itemHour = item.getAttribute('data-hour');
            
            let matchesSearch = name.includes(search);
            let matchesHour = isSearching || (itemHour == currentActiveJam);

            if (matchesSearch && matchesHour) {
                if (itemStatus === 'pending') visiblePending++;
                if (itemStatus === 'verified') visibleVerified++;
            }
        });

        const elPending = document.getElementById('count-pending');
        const elVerified = document.getElementById('count-verified');
        if (elPending) elPending.textContent = visiblePending;
        if (elVerified) elVerified.textContent = visibleVerified;
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateStats();
        filterList('pending');

        document.getElementById('searchInput').addEventListener('input', () => {
            let activeTab = 'all';
            if (document.getElementById('tab-pending').classList.contains('bg-white')) activeTab = 'pending';
            if (document.getElementById('tab-verified').classList.contains('bg-white')) activeTab = 'verified';
            filterList(activeTab);
        });

        // Auto-scroll active tab into view on mobile
        const activeTab = document.getElementById('active-jam-tab');
        if (activeTab && window.innerWidth < 768) {
            activeTab.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest', 
                inline: 'center' 
            });
        }
    });

    async function verifyAsync(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button');
        const originalContent = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        try {
            const response = await fetch('<?= url('/tanqih/verify') ?>', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('Non-JSON Response:', text);
                alert('Server Error: Respon tidak valid. ' + (text.substring(0, 100)));
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
                return;
            }

            if (result.success) {
                const newStatus = result.action === 'verify' ? 'verified' : 'pending';
                const container = form.closest('.schedule-item');
                
                container.setAttribute('data-status', newStatus);

                // Target the specific action area, not the whole card row
                const actionContainer = form.closest('.flex.items-center.gap-3');
                
                if (result.action === 'verify') {
                    const isJustified = result.data.status === 'justified';
                    const colorClass = isJustified ? 'text-yellow-700 bg-yellow-100 font-semibold' : 'text-green-700 bg-green-100';
                    const iconColor = isJustified ? 'text-yellow-500' : 'text-green-500';
                    const labelText = isJustified ? 'Justifikasi' : 'Sudah';

                    // Update Badge in the first column
                    const statusBadgeArea = container.querySelector('.flex.items-center.gap-2.mb-1');
                    if (statusBadgeArea) {
                        const existingBadge = statusBadgeArea.querySelector('.inline-flex');
                        if (existingBadge) existingBadge.remove();
                        statusBadgeArea.insertAdjacentHTML('beforeend', `
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ${colorClass}">
                                <i class="ri-checkbox-circle-fill mr-1"></i> ${labelText}
                            </span>
                        `);
                    }

                    const csrfTokenInput = document.querySelector('input[name="csrf_token"]');
                    const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

                    const newHtml = `
                    <div class="hidden sm:block text-right">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Oleh ${result.data.verifier_name}</p>
                        <p class="text-xs text-gray-500">${result.data.timestamp}</p>
                    </div>
                    <form action="<?= url('/tanqih/verify') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="${csrfToken}">
                        <input type="hidden" name="date" value="${formData.get('date')}">
                        <input type="hidden" name="kelas_id" value="${formData.get('kelas_id')}">
                        <input type="hidden" name="hour" value="${formData.get('hour')}">
                        <input type="hidden" name="pengajar_id" value="${formData.get('pengajar_id')}">
                        <input type="hidden" name="action" value="unverify">
                        <input type="hidden" name="ajax" value="1">
                        <button type="button" onclick="verifyAsync(this.form)" class="w-10 h-10 rounded-xl bg-gray-100 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center shadow-sm" title="Batalkan Verifikasi?">
                             <i class="ri-close-line text-xl"></i>
                        </button>
                    </form>
                    `;
                    actionContainer.innerHTML = newHtml;
                    
                    // Update main bg
                    container.classList.remove('border-gray-100');
                    container.classList.add('border-green-100', 'bg-green-50/10');

                    // AUTO-SWITCH logic
                    const searchInput = document.getElementById('searchInput');
                    const isSearching = searchInput.value.length > 0;
                    
                    if (isSearching) {
                        // Clear search and switch to that hour
                        searchInput.value = '';
                        currentActiveJam = result.data.hour;
                        
                        // Update Sidebar UI
                        document.querySelectorAll('nav a').forEach(a => {
                            const jamId = a.href.split('jam=')[1];
                            if (jamId == currentActiveJam) {
                                a.classList.add('bg-indigo-50', 'text-indigo-700', 'font-semibold', 'shadow-sm', 'ring-1', 'ring-indigo-100');
                                a.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
                                a.id = 'active-jam-tab';
                                
                                // Update sidebar number badge
                                const numBadge = a.querySelector('.w-8.h-8');
                                if (numBadge) numBadge.className = 'w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-xs bg-indigo-600 text-white shadow-indigo-200 shadow-lg';
                                
                                // Scroll it into view
                                if (window.innerWidth < 768) {
                                    a.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                                }
                            } else {
                                a.classList.remove('bg-indigo-50', 'text-indigo-700', 'font-semibold', 'shadow-sm', 'ring-1', 'ring-indigo-100');
                                a.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
                                a.id = '';
                                
                                const numBadge = a.querySelector('.w-8.h-8');
                                if (numBadge) numBadge.className = 'w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-xs bg-gray-100 text-gray-400 group-hover:bg-gray-200';
                            }
                        });
                    }
                    
                } else {
                    location.reload(); 
                    return;
                }

                let activeTabStatus = 'all';
                if (document.getElementById('tab-pending').classList.contains('bg-white')) activeTabStatus = 'pending';
                if (document.getElementById('tab-verified').classList.contains('bg-white')) activeTabStatus = 'verified';
                
                filterList(activeTabStatus);

            } else {
                alert(result.message || 'Gagal memproses.');
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            }

        } catch (e) {
            console.error(e);
            alert('Terjadi kesalahan koneksi ke server.');
            submitBtn.innerHTML = originalContent;
            submitBtn.disabled = false;
        }
    }
</script>

<?php renderFooter(); ?>
