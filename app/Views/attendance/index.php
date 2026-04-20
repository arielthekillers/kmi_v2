<?php renderHeader($title); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Absensi Pengajar</h2>
        <p class="text-gray-500 text-sm mb-6">Catat kehadiran guru pengajar setiap jam pelajaran.</p>

        <!-- Compact Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-3 mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 pl-1">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                    <i class="ri-calendar-check-line text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 leading-tight"><?= date('d M Y', strtotime($selectedDate)) ?></h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Total <?= count($schedule) ?> jadwal mengajar</p>
                </div>
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
    </div>

    <?php if (empty($schedule)): ?>
        <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
             <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <p class="mt-2 text-gray-500">Tidak ada jadwal mengajar pada hari ini.</p>
        </div>
    <?php else: 
        // Group by Jam
        $slotsByJam = [];
        foreach ($schedule as $slot) {
            $slotsByJam[$slot['hour']][] = $slot;
        }
        ksort($slotsByJam);
        
        reset($slotsByJam); $firstJam = key($slotsByJam);
        // Default to detected current hour if no parameter is provided
        $activeJam = $_GET['jam'] ?? ($currentDetectedHour ?? $firstJam);
        // Fallback to first available jam if detected hour has no schedule for today
        if (!isset($slotsByJam[$activeJam])) $activeJam = $firstJam;
    ?>
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar Navigation (Sticky on mobile for quick access) -->
            <aside class="w-full md:w-64 flex-shrink-0">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden sticky top-20 z-30">
                    <div class="hidden md:block px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Jam Pelajaran</h3>
                    </div>
                    <!-- Mobile Hint -->
                    <div class="md:hidden flex items-center justify-between px-4 py-2 border-b border-gray-50 bg-gray-50/30">
                        <span class="text-[10px] font-bold text-indigo-500 flex items-center gap-1 uppercase tracking-wider">
                            <i class="ri-arrow-left-right-line"></i> Geser jam ke samping
                        </span>
                        <div class="flex gap-1">
                            <div class="w-1 h-1 rounded-full bg-indigo-200 animate-pulse"></div>
                            <div class="w-1 h-1 rounded-full bg-indigo-300 animate-pulse" style="animation-delay: 0.2s"></div>
                            <div class="w-1 h-1 rounded-full bg-indigo-400 animate-pulse" style="animation-delay: 0.4s"></div>
                        </div>
                    </div>
                    <nav class="p-2 flex md:flex-col gap-2 overflow-x-auto md:overflow-visible pb-3 md:pb-2 no-scrollbar snap-x">
                        <?php foreach ($slotsByJam as $jam => $slots): 
                            $isActive = $jam == $activeJam;
                            $count = count($slots);
                            $doneCount = 0;
                            foreach($slots as $s) if(!empty($s['absensi'])) $doneCount++;
                            $isComplete = $doneCount === $count;
                            
                            $badgeClass = $isComplete
                                ? 'bg-green-100 text-green-700'
                                : ($doneCount > 0
                                    ? 'bg-yellow-100 text-yellow-700'
                                    : 'bg-gray-100 text-gray-500');
                            
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
                                    <span class="flex-shrink-0">Jam ke-<?= $jam ?></span>
                                    <?php if ($isDetected): ?>
                                        <span class="flex h-2 w-2 rounded-full bg-indigo-500 animate-pulse flex-shrink-0" title="Sedang Berlangsung"></span>
                                    <?php endif; ?>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ml-2 <?= $badgeClass ?>">
                                    <?= $doneCount ?>/<?= $count ?>
                                </span>
                            </a>
<?php endforeach; ?>
                    </nav>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Peserta KBM - Jam ke-<?= $activeJam ?></h3>
                    <div class="text-xs text-gray-500">
                        Menampilkan <?= count($slotsByJam[$activeJam]) ?> kelas
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($slotsByJam[$activeJam] as $slot): 
                        $absensi = $slot['absensi'];
                        $hasRecord = !empty($absensi);
                        $status = $absensi['status'] ?? '';
                        
                        $statusBadge = '';
                        $cardBorder = 'border-gray-200';
                        $cardBg = 'bg-white';
                        
                        if ($status === 'hadir') {
                            $cardBorder = 'border-green-200';
                            $cardBg = 'bg-green-50/50';
                            $ketepatan = $absensi['ketepatan'] ?? '';
                            if ($ketepatan === 'tepat_waktu') {
                                $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 uppercase tracking-wider">Hadir Tepat</span>';
                            } else {
                                $jamDatang = $absensi['jam_datang'] ?? '';
                                $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800 uppercase tracking-wider">Terlambat (' . htmlspecialchars($jamDatang) . ')</span>';
                            }
                        } elseif ($status === 'tidak_hadir' || $status === 'alpha') {
                            $cardBorder = 'border-red-200';
                            $cardBg = 'bg-red-50/50';
                            $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 uppercase tracking-wider">Tidak Hadir</span>';
                        } elseif ($status === 'diganti' || $status === 'substitute') {
                            $cardBorder = 'border-blue-200';
                            $cardBg = 'bg-blue-50/50';
                            $penggantiId = $absensi['pengajar_pengganti'] ?? '';
                            $penggantiName = $pengajarList[$penggantiId]['nama'] ?? 'Unknown';
                            $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">Diganti: ' . htmlspecialchars($penggantiName) . '</span>';
                        }
                    ?>
                    <div class="<?= $cardBg ?> border-2 <?= $cardBorder ?> rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group overflow-hidden">
                        <div class="p-5 flex-1">
                            <div class="flex justify-between items-start mb-4">
                                <div class="px-3 py-1 rounded-lg bg-white border border-gray-100 text-xs font-bold text-gray-700 shadow-sm group-hover:border-indigo-100 group-hover:text-indigo-600 transition-colors">
                                    Kelas <?= htmlspecialchars($slot['kelas_name']) ?>
                                </div>
                                <?php if ($hasRecord): ?>
                                    <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center text-white shadow-lg shadow-green-100">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                <?php else: ?>
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-200 group-hover:border-indigo-300 transition-colors"></div>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="text-base font-bold text-gray-900 mb-1 leading-tight line-clamp-2" title="<?= htmlspecialchars($slot['mapel_name']) ?>">
                                <?= htmlspecialchars($slot['mapel_name']) ?>
                            </h3>
                            <p class="text-xs text-gray-500 mb-6 flex items-center gap-1.5 font-medium">
                                <i class="ri-user-star-line text-gray-400"></i>
                                <span class="line-clamp-1">
                                   <?= htmlspecialchars($slot['teacher_name']) ?>
                                </span>
                            </p>

                            <?php if ($hasRecord): ?>
                                <div class="mt-2">
                                    <?= $statusBadge ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="px-5 py-4 bg-gray-50/50 border-t border-gray-100">
                             <button onclick="openAbsensiModal('<?= $slot['key'] ?>', <?= htmlspecialchars(json_encode($slot['kelas_name']), ENT_QUOTES) ?>, '<?= $slot['hour'] ?>', <?= htmlspecialchars(json_encode($slot['mapel_name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($slot['teacher_name']), ENT_QUOTES) ?>, '<?= $slot['pengajar_id'] ?>', <?= htmlspecialchars(json_encode($absensi), ENT_QUOTES, 'UTF-8') ?>)" 
                                    class="w-full inline-flex justify-center items-center px-4 py-2.5 text-xs font-bold rounded-xl shadow-sm text-white transition-all duration-200 <?= $hasRecord ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-100' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-100' ?>">
                                <i class="<?= $hasRecord ? 'ri-edit-line' : 'ri-checkbox-circle-line' ?> mr-2 text-sm"></i>
                                <?= $hasRecord ? 'Edit Absensi' : 'Catat Absensi' ?>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- Absensi Modal -->
<div id="absensiModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('absensiModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg sm:my-8 sm:align-middle sm:w-full mx-auto">
            <form action="<?= url('/attendance/store') ?>" method="POST">
                <?= csrf_token_field() ?>
                <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
                <input type="hidden" name="key" id="modal_key">
                <input type="hidden" name="pengajar_id" id="modal_pengajar_id">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal_title">Catat Absensi</h3>
                    
                    <div class="space-y-4">
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Kehadiran</label>
                            <div class="space-y-2">
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="status" value="hadir" onclick="updateStatusFields('hadir')" class="text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span class="text-sm font-medium">Hadir</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="status" value="tidak_hadir" onclick="updateStatusFields('tidak_hadir')" class="text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span class="text-sm font-medium">Tidak Hadir</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="status" value="diganti" onclick="updateStatusFields('diganti')" class="text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span class="text-sm font-medium">Diganti Guru Pengganti</span>
                                </label>
                            </div>
                        </div>

                        <!-- Ketepatan (shown if hadir) -->
                        <div id="field_ketepatan" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ketepatan Waktu</label>
                            <div class="space-y-2">
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="ketepatan" value="tepat_waktu" onclick="updateKetepatanFields('tepat_waktu')" class="text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span class="text-sm">Tepat Waktu</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="ketepatan" value="terlambat" onclick="updateKetepatanFields('terlambat')" class="text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span class="text-sm">Terlambat</span>
                                </label>
                            </div>
                        </div>

                        <!-- Jam Datang (shown if terlambat) -->
                        <div id="field_jam_datang" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Kedatangan</label>
                            <input type="time" name="jam_datang" id="input_jam_datang" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        </div>

                        <!-- Pengajar Pengganti (shown if diganti) -->
                        <div id="field_pengganti" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Guru Pengganti</label>
                            <select name="pengajar_pengganti" id="input_pengganti" class="tom-select block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                                <option value="">-- Pilih Guru Pengganti --</option>
                                <?php foreach ($pengajarList as $id => $p): ?>
                                    <option value="<?= $id ?>"><?= htmlspecialchars($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="toggleModal('absensiModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleModal(id) {
    document.getElementById(id).classList.toggle('hidden');
    // Re-init TomSelect if needed when showing
    if (!document.getElementById(id).classList.contains('hidden')) {
        // Simple trick to ensure rendering if hidden initially
        // initTomSelects(); // Global function
    }
}

function openAbsensiModal(key, kelas, jam, mapel, pengajar, pengajarId, absensi) {
    document.getElementById('modal_title').textContent = `Jam ${jam} - ${kelas}`;
    document.getElementById('modal_key').value = key;
    document.getElementById('modal_pengajar_id').value = pengajarId;
    
    // Reset form
    document.querySelectorAll('input[name="status"]').forEach(r => r.checked = false);
    document.querySelectorAll('input[name="ketepatan"]').forEach(r => r.checked = false);
    document.getElementById('field_ketepatan').classList.add('hidden');
    document.getElementById('field_jam_datang').classList.add('hidden');
    document.getElementById('field_pengganti').classList.add('hidden');
    document.getElementById('input_jam_datang').value = '';
    
    // Pre-fill if editing
    if (absensi && absensi.status) {
        let status = absensi.status;
        if(status === 'substitute') status = 'diganti'; // Normalize

        const statusRadio = document.querySelector(`input[name="status"][value="${status}"]`);
        if(statusRadio) statusRadio.checked = true;
        updateStatusFields(status);
        
        if (status === 'hadir' && absensi.ketepatan) {
            document.querySelector(`input[name="ketepatan"][value="${absensi.ketepatan}"]`).checked = true;
            updateKetepatanFields(absensi.ketepatan);
            if (absensi.ketepatan === 'terlambat' && absensi.jam_datang) {
                document.getElementById('input_jam_datang').value = absensi.jam_datang;
            }
        }
        
        if (status === 'diganti' && absensi.pengajar_pengganti) {
            document.getElementById('input_pengganti').value = absensi.pengajar_pengganti;
            if (document.getElementById('input_pengganti').tomselect) {
                document.getElementById('input_pengganti').tomselect.setValue(absensi.pengajar_pengganti);
            }
        }
    }
    
    toggleModal('absensiModal');
}

// Auto-scroll active tab into view on mobile
document.addEventListener('DOMContentLoaded', () => {
    const activeTab = document.getElementById('active-jam-tab');
    if (activeTab && window.innerWidth < 768) { // Only on mobile/tablet
        activeTab.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'nearest', 
            inline: 'center' 
        });
    }
});

function updateStatusFields(status) {
    document.getElementById('field_ketepatan').classList.add('hidden');
    document.getElementById('field_jam_datang').classList.add('hidden');
    document.getElementById('field_pengganti').classList.add('hidden');
    
    if (status === 'hadir') {
        document.getElementById('field_ketepatan').classList.remove('hidden');
    } else if (status === 'diganti') {
        document.getElementById('field_pengganti').classList.remove('hidden');
    }
}

function updateKetepatanFields(ketepatan) {
    document.getElementById('field_jam_datang').classList.add('hidden');
    
    if (ketepatan === 'terlambat') {
        document.getElementById('field_jam_datang').classList.remove('hidden');
    }
}
</script>

<?php renderFooter(); ?>
