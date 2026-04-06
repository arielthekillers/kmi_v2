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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Petugas Piket
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($days as $day): 
                                $petugasIds = $schedule[$day] ?? [];
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 bg-gray-50/50 align-top">
                                    <?= $day ?>
                                </td>
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
<div id="editModal" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <form action="<?= url($actionUrl) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan perubahan jadwal ini?');">
                <?= csrf_token_field() ?>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Atur <?= htmlspecialchars($title) ?>
                            </h3>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-h-[60vh] overflow-y-auto p-1">
                                <?php foreach ($days as $day): 
                                    $currentIds = $schedule[$day] ?? [];
                                ?>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <h4 class="font-bold text-gray-700 mb-2 border-b border-gray-200 pb-1"><?= $day ?></h4>
                                    <div class="space-y-2 h-48 overflow-y-auto text-sm">
                                        <?php foreach ($teachers as $id => $p): ?>
                                            <label class="flex items-center space-x-2">
                                                <input type="checkbox" name="piket[<?= $day ?>][]" value="<?= $id ?>" <?= in_array($id, $currentIds) ? 'checked' : '' ?> class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                                <span class="text-gray-700 truncate"><?= htmlspecialchars($p['nama']) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
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
