<?php renderHeader($title); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <?= htmlspecialchars($title) ?>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                <?= htmlspecialchars($desc) ?>
            </p>
        </div>
        <?php if (auth_get_role() === 'admin'): ?>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button onclick="document.getElementById('editModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Atur Jadwal
            </button>
        </div>
        <?php endif; ?>
    </div>

    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                                    Hari
                                </th>
                                <?php if ($type === 'keliling'): ?>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesi 1 (1-2)</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesi 2 (3-4)</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesi 3 (5-6)</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesi 4 (7)</th>
                                <?php else: ?>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Petugas Piket
                                    </th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($days as $day): 
                                $dayData = $schedule[$day] ?? [];
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 bg-gray-50/50 align-top">
                                    <?= $day ?>
                                </td>
                                
                                <?php if ($type === 'keliling'): ?>
                                    <?php for ($s = 1; $s <= 4; $s++): 
                                        $petugasIds = $dayData[$s] ?? [];
                                    ?>
                                        <td class="px-3 py-4 text-xs text-gray-700 align-top border-l border-gray-100">
                                            <?php if (empty($petugasIds)): ?>
                                                <span class="text-gray-300 italic">-</span>
                                            <?php else: ?>
                                                <ul class="space-y-1">
                                                    <?php foreach ($petugasIds as $id): 
                                                        $nama = $teachers[$id]['nama'] ?? 'Unknown';
                                                    ?>
                                                        <li class="font-medium"><?= htmlspecialchars($nama) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                <?php else: 
                                    $petugasIds = $dayData;
                                ?>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php if (empty($petugasIds)): ?>
                                            <span class="text-gray-400 italic">Belum ada petugas assigned</span>
                                        <?php else: ?>
                                            <ul class="list-disc pl-5 space-y-1">
                                                <?php foreach ($petugasIds as $id): 
                                                    $nama = $teachers[$id]['nama'] ?? 'Unknown';
                                                ?>
                                                    <li class="font-medium"><?= htmlspecialchars($nama) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Edit Modal -->
<?php if (auth_get_role() === 'admin'): ?>
<?php 
    $sessionLabels = [
        1 => 'Sesi 1 (Jam 1-2)',
        2 => 'Sesi 2 (Jam 3-4)',
        3 => 'Sesi 3 (Jam 5-6)',
        4 => 'Sesi 4 (Jam 7)'
    ];
?>
<div id="editModal" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
            <form action="<?= url($actionUrl) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan perubahan jadwal ini?');">
                <?= csrf_token_field() ?>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Atur <?= htmlspecialchars($title) ?>
                            </h3>
                            
                            <div class="space-y-8 max-h-[70vh] overflow-y-auto p-1">
                                <?php foreach ($days as $day): 
                                    $currentDayData = $schedule[$day] ?? [];
                                ?>
                                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                    <div class="bg-gray-800 px-4 py-2">
                                        <h4 class="font-bold text-white"><?= $day ?></h4>
                                    </div>
                                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 bg-gray-50">
                                        <?php if ($type === 'keliling'): ?>
                                            <?php for ($s = 1; $s <= 4; $s++): 
                                                $sessionSpecificIds = $currentDayData[$s] ?? [];
                                            ?>
                                            <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                                <h5 class="font-bold text-indigo-700 text-xs uppercase tracking-wider mb-2 border-b border-indigo-100 pb-1">
                                                    <?= $sessionLabels[$s] ?>
                                                </h5>
                                                <div class="space-y-1 h-48 overflow-y-auto text-xs">
                                                    <?php foreach ($teachers as $id => $p): ?>
                                                        <label class="flex items-center space-x-2 py-0.5 hover:bg-indigo-50 rounded px-1 transition-colors cursor-pointer">
                                                            <input type="checkbox" name="piket[<?= $day ?>][<?= $s ?>][]" value="<?= $id ?>" <?= in_array($id, $sessionSpecificIds) ? 'checked' : '' ?> class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 w-3 h-3">
                                                            <span class="text-gray-700 truncate"><?= htmlspecialchars($p['nama']) ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endfor; ?>
                                        <?php else: 
                                            // Syeikh Diwan - Whole day
                                            $sessionSpecificIds = $currentDayData;
                                        ?>
                                            <div class="col-span-full bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                                <h5 class="font-bold text-indigo-700 text-xs uppercase tracking-wider mb-2 border-b border-indigo-100 pb-1">
                                                    Petugas Hari <?= $day ?>
                                                </h5>
                                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 h-48 overflow-y-auto text-xs p-2">
                                                    <?php foreach ($teachers as $id => $p): ?>
                                                        <label class="flex items-center space-x-2 py-1 hover:bg-indigo-50 rounded px-1 transition-colors cursor-pointer">
                                                            <input type="checkbox" name="piket[<?= $day ?>][]" value="<?= $id ?>" <?= in_array($id, $sessionSpecificIds) ? 'checked' : '' ?> class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                                            <span class="text-gray-700 truncate"><?= htmlspecialchars($p['nama']) ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Perubahan
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('editModal').classList.add('hidden')">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php renderFooter(); ?>
