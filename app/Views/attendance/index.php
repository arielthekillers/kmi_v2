<?php renderHeader($title); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Absensi Pengajar</h2>
        <p class="text-gray-500 text-sm mb-6">Catat kehadiran guru pengajar setiap jam pelajaran.</p>

        <!-- Date Filter -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
            <form method="GET" class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Tanggal</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                </div>
                <div>
                    <a href="?date=<?= date('Y-m-d') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Hari Ini
                    </a>
                </div>
            </form>
        </div>
        
        <div class="bg-indigo-50 border-l-4 border-indigo-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                     <!-- Info Icon -->
                     <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-indigo-700">
                        <strong><?= date('d M Y', strtotime($selectedDate)) ?></strong> - Total <?= count($schedule) ?> jadwal mengajar
                    </p>
                </div>
            </div>
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
        
        $firstJam = array_key_first($slotsByJam);
        $activeJam = $_GET['jam'] ?? $firstJam;
        if (!isset($slotsByJam[$activeJam])) $activeJam = $firstJam;
    ?>
        <!-- Hour Tabs -->
        <div class="border-b border-gray-200 mb-6 overflow-x-auto">
            <nav class="-mb-px flex space-x-4 px-2" aria-label="Tabs">
                <?php foreach ($slotsByJam as $jam => $slots): 
                    $isActive = $jam == $activeJam;
                    $count = count($slots);
                    $doneCount = 0;
                    foreach($slots as $s) if(!empty($s['absensi'])) $doneCount++;
                    $isComplete = $doneCount === $count;
                    $indicatorColor = $isComplete ? 'bg-green-500' : ($doneCount > 0 ? 'bg-yellow-500' : 'bg-gray-300');
                ?>
                    <a href="?date=<?= htmlspecialchars($selectedDate) ?>&jam=<?= $jam ?>" 
                       class="<?= $isActive ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                        Jam ke-<?= $jam ?>
                        <span class="inline-flex items-center justify-center h-5 w-5 rounded-full text-xs text-white <?= $indicatorColor ?>">
                            <?= $doneCount ?>/<?= $count ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Grid Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($slotsByJam[$activeJam] as $slot): 
                $absensi = $slot['absensi'];
                $hasRecord = !empty($absensi);
                $status = $absensi['status'] ?? '';
                
                $statusBadge = '';
                $cardBorder = 'border-gray-200';
                $cardBg = 'bg-white';
                
                if ($status === 'hadir') {
                    $cardBorder = 'border-green-200';
                    $cardBg = 'bg-green-50';
                    $ketepatan = $absensi['ketepatan'] ?? '';
                    if ($ketepatan === 'tepat_waktu') {
                        $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Hadir Tepat Waktu</span>';
                    } else {
                        $jamDatang = $absensi['jam_datang'] ?? '';
                        $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Terlambat (' . htmlspecialchars($jamDatang) . ')</span>';
                    }
                } elseif ($status === 'tidak_hadir') {
                    $cardBorder = 'border-red-200';
                    $cardBg = 'bg-red-50';
                    $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Tidak Hadir</span>';
                } elseif ($status === 'diganti' || $status === 'substitute') {
                    $cardBorder = 'border-blue-200';
                    $cardBg = 'bg-blue-50';
                    $penggantiId = $absensi['pengajar_pengganti'] ?? '';
                    $penggantiName = $pengajarList[$penggantiId]['nama'] ?? 'Unknown';
                    $statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Diganti: ' . htmlspecialchars($penggantiName) . '</span>';
                }
            ?>
            <div class="<?= $cardBg ?> border <?= $cardBorder ?> rounded-xl shadow-sm hover:shadow-md transition-all duration-200 flex flex-col">
                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-4">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-white border border-gray-200 text-xs font-bold text-gray-600 shadow-sm">
                            <?= htmlspecialchars($slot['kelas_name']) ?>
                        </span>
                        <?php if ($hasRecord): ?>
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php else: ?>
                            <span class="h-5 w-5 block rounded-full border-2 border-gray-300"></span>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="text-base font-bold text-gray-900 mb-1 line-clamp-1" title="<?= htmlspecialchars($slot['mapel_name']) ?>">
                        <?= htmlspecialchars($slot['mapel_name']) ?>
                    </h3>
                    <p class="text-sm text-gray-600 mb-4 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="line-clamp-1" title="<?= htmlspecialchars($slot['teacher_name']) ?>">
                           <?= htmlspecialchars($slot['teacher_name']) ?>
                        </span>
                    </p>

                    <?php if ($hasRecord): ?>
                        <div class="mt-2 text-center">
                            <?= $statusBadge ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="px-5 py-3 bg-white/50 border-t border-gray-100 rounded-b-xl">
                     <button onclick="openAbsensiModal('<?= $slot['key'] ?>', <?= htmlspecialchars(json_encode($slot['kelas_name']), ENT_QUOTES) ?>, '<?= $slot['hour'] ?>', <?= htmlspecialchars(json_encode($slot['mapel_name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($slot['teacher_name']), ENT_QUOTES) ?>, '<?= $slot['pengajar_id'] ?>', <?= htmlspecialchars(json_encode($absensi), ENT_QUOTES, 'UTF-8') ?>)" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg shadow-sm text-white <?= $hasRecord ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-indigo-600 hover:bg-indigo-700' ?> transition-colors">
                        <?= $hasRecord ? 'Edit Absensi' : 'Catat Absensi' ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
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
