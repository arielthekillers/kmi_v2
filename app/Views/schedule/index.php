<?php renderHeader("Jadwal Pelajaran"); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Jadwal Pelajaran</h2>
        <p class="text-gray-500 text-sm mb-6">Lihat dan atur jadwal pelajaran.</p>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
            <form method="GET" action="<?= url('/schedule') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <div>
                    <label for="kelas_id" class="block text-xs font-medium text-gray-500 mb-1">Pilih Kelas</label>
                    <select name="kelas_id" id="kelas_id" onchange="if(this.value) { document.getElementById('pengajar_id').value=''; } this.form.submit()" class="tom-select block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelasData as $id => $kls): ?>
                            <option value="<?= $id ?>" <?= $selectedKelasId == $id ? 'selected' : '' ?>>
                                Kelas <?= htmlspecialchars($kls['tingkat']) ?>-<?= htmlspecialchars($kls['abjad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="pengajar_id" class="block text-xs font-medium text-gray-500 mb-1">Pilih Pengajar</label>
                    <select name="pengajar_id" id="pengajar_id" onchange="if(this.value) { document.getElementById('kelas_id').value=''; } this.form.submit()" class="tom-select block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                        <option value="">-- Pilih Pengajar --</option>
                        <?php foreach ($teacherOptions as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $selectedPengajarId == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-transparent mb-1">Aksi</label>
                    <div class="flex gap-2">
                        <a href="<?= url('/schedule') ?>" class="bg-white hover:bg-gray-50 text-gray-500 font-medium py-2 px-3 rounded-md text-sm transition-colors border border-gray-300 flex items-center justify-center w-full">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($selectedKelasId || $selectedPengajarId): ?>
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($viewTitle) ?></h3>
                
                <?php if ($selectedKelasId): ?>
                    <button onclick="openEditModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit Jadwal Kelas
                    </button>
                <?php endif; ?>
            </div>

            <div class="bg-white shadow overflow-hidden rounded-lg border border-gray-200 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 w-24 border-r">Hari</th>
                            <?php foreach ($hours as $h): ?>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Jam ke-<?= $h ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        <?php foreach ($days as $day): ?>
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap font-medium text-gray-900 bg-gray-50 sticky left-0 border-r">
                                    <?= $day ?>
                                </td>
                                <?php foreach ($hours as $h): ?>
                                    <td class="px-2 py-3 text-center align-top border-l border-gray-100 hover:bg-gray-50 transition-colors">
                                        <?php 
                                        if ($selectedKelasId) {
                                            // Class View
                                            $slot = $currentJadwal[$day][$h] ?? [];
                                            $mapelId = $slot['mapel'] ?? '';
                                            $pengajarId = $slot['pengajar'] ?? '';
                                            
                                            $mapelName = $pelajaranData[$mapelId]['nama'] ?? '-';
                                            $pengajarName = $pengajarData[$pengajarId]['nama'] ?? '-';
                                            
                                            if ($mapelId) {
                                                echo '<div class="font-semibold text-indigo-700 mb-1 line-clamp-2" title="'.htmlspecialchars($mapelName).'">'.htmlspecialchars($mapelName).'</div>';
                                                echo '<div class="text-xs text-gray-500 line-clamp-2" title="'.htmlspecialchars($pengajarName).'">'.htmlspecialchars($pengajarName).'</div>';
                                            } else {
                                                echo '<span class="text-gray-300">-</span>';
                                            }
                                        } elseif ($selectedPengajarId) {
                                            // Teacher View
                                            $slot = $teacherSchedule[$day][$h] ?? null;
                                            
                                            if ($slot) {
                                                $mapelId = $slot['mapel'];
                                                $kelasId = $slot['kelas'];
                                                
                                                $mapelName = $pelajaranData[$mapelId]['nama'] ?? '-';
                                                $k = $kelasData[$kelasId] ?? null;
                                                $kelasName = $k ? "Kelas {$k['tingkat']}-{$k['abjad']}" : '-';
                                                
                                                echo '<div class="font-semibold text-indigo-700 mb-1 line-clamp-2" title="'.htmlspecialchars($mapelName).'">'.htmlspecialchars($mapelName).'</div>';
                                                echo '<div class="text-xs text-gray-600 bg-gray-100 rounded px-1.5 py-0.5 inline-block" title="'.htmlspecialchars($kelasName).'">'.htmlspecialchars($kelasName).'</div>';
                                            } else {
                                                echo '<span class="text-gray-300">-</span>';
                                            }
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <p class="mt-2 text-gray-500">Silakan pilih <strong>Kelas</strong> atau <strong>Pengajar</strong> untuk melihat jadwal.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- Edit Modal (Only for Class View) -->
    <?php if ($selectedKelasId && isset($kelasData[$selectedKelasId])): 
        $k = $kelasData[$selectedKelasId];
    ?>
    <div id="editJadwalModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('editJadwalModal')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                <form action="<?= url('/schedule/store') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan perubahan jadwal ini?');">
                    <?= csrf_token_field() ?>
                    <input type="hidden" name="kelas_id" value="<?= htmlspecialchars($selectedKelasId) ?>">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Jadwal: Kelas <?= htmlspecialchars($k['tingkat']) ?>-<?= htmlspecialchars($k['abjad']) ?></h3>
                        
                        <div class="overflow-x-auto max-h-[70vh] border border-gray-200 rounded-md">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-20 w-24 border-r">Hari</th>
                                        <?php foreach ($hours as $h): ?>
                                            <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-64">Jam ke-<?= $h ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-xs">
                                    <?php foreach ($days as $day): ?>
                                        <tr>
                                            <td class="px-4 py-2 font-medium text-gray-900 bg-gray-50 sticky left-0 border-r z-10">
                                                <?= $day ?>
                                            </td>
                                            <?php foreach ($hours as $h): 
                                                $slot = $currentJadwal[$day][$h] ?? [];
                                                $currMapel = $slot['mapel'] ?? '';
                                                $currPengajar = $slot['pengajar'] ?? '';
                                            ?>
                                                <td class="px-2 py-2 border-l border-gray-100 bg-white">
                                                    <div class="space-y-2">
                                                        <div class="border-l-4 border-blue-400 pl-2 rounded-r bg-blue-50 py-1">
                                                            <span class="text-[10px] uppercase font-bold text-blue-600 block mb-0.5 px-0.5">Mapel</span>
                                                            <select name="schedule[<?= $day ?>][<?= $h ?>][mapel]" class="tom-select block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs py-1">
                                                                <option value="">- Mapel -</option>
                                                                <?php foreach ($subjectOptions as $p): ?>
                                                                    <option value="<?= $p['id'] ?>" <?= $currMapel == $p['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($p['nama']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="border-l-4 border-orange-400 pl-2 rounded-r bg-orange-50 py-1">
                                                            <span class="text-[10px] uppercase font-bold text-orange-600 block mb-0.5 px-0.5">Pengajar</span>
                                                            <select name="schedule[<?= $day ?>][<?= $h ?>][pengajar]" class="tom-select block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs py-1">
                                                                <option value="">- Pengajar -</option>
                                                                <?php foreach ($teacherOptions as $p): ?>
                                                                    <option value="<?= $p['id'] ?>" <?= $currPengajar == $p['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($p['nama']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Perubahan
                        </button>
                        <button type="button" onclick="toggleModal('editJadwalModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
        if (!document.getElementById(id).classList.contains('hidden')) {
            setTimeout(initTomSelects, 50); // Init TomSelect when modal opens
        }
    }

    function openEditModal() {
        toggleModal('editJadwalModal');
    }
</script>

<?php renderFooter(); ?>
