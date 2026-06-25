<!-- e:\xampp\htdocs\kmi_v2\app\Views\kelas\view_perilaku.php -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i class="ri-user-smile-line text-lg"></i>
            </div>
            Input Nilai Perilaku Santri
        </h3>
        <p class="text-xs text-gray-500 mt-1">Input nilai akhlak/sikap santri yang akan dicetak di lembar raport.</p>
    </div>
    <div>
        <form action="" method="GET" class="flex items-center">
            <input type="hidden" name="id" value="<?= $kelas['id'] ?>">
            <input type="hidden" name="tab" value="perilaku">
            <select name="session_id" onchange="this.form.submit()" class="text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-lg px-4 py-2 focus:border-indigo-500 focus:ring-0 shadow-sm cursor-pointer hover:border-gray-300 transition-colors">
                <option value="">Pilih Sesi Ujian...</option>
                <?php 
                $typeMap = [
                    'UUPT' => 'Ulangan Umum Pertengahan Tahun',
                    'UPT' => 'Ujian Pertengahan Tahun',
                    'UUAT' => 'Ulangan Umum Akhir Tahun',
                    'UAT' => 'Ujian Akhir Tahun'
                ];
                foreach ($sessions as $session): 
                    $sessionName = $typeMap[$session['type']] ?? $session['type'];
                ?>
                    <option value="<?= $session['id'] ?>" <?= $selected_session_id == $session['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($session['type'] . ' (' . $sessionName . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if (empty($selected_session_id)): ?>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
            <i class="ri-search-line text-2xl"></i>
        </div>
        <h4 class="text-lg font-bold text-gray-900 mb-2">Silakan Pilih Sesi Ujian</h4>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">Pilih salah satu sesi ujian dari dropdown di atas untuk memasukkan atau memperbarui nilai perilaku santri.</p>
    </div>
<?php elseif (empty($students)): ?>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
            <i class="ri-user-unfollow-line text-2xl"></i>
        </div>
        <h4 class="text-lg font-bold text-gray-900 mb-2">Tidak Ada Santri</h4>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">Belum ada data santri aktif terdaftar di kelas ini untuk tahun ajaran aktif.</p>
    </div>
<?php else: ?>
    <form action="<?= url('/classes/save-perilaku') ?>" method="POST">
        <?= csrf_token_field() ?>
        <input type="hidden" name="class_id" value="<?= $kelas['id'] ?>">
        <input type="hidden" name="session_id" value="<?= $selected_session_id ?>">

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <!-- Desktop View Table -->
            <div class="overflow-x-auto w-full hidden md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest w-16">No</th>
                            <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest w-40">NIS</th>
                            <th class="px-6 py-3 text-left text-[9px] font-bold text-gray-400 uppercase tracking-widest">Nama Santri</th>
                            <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest w-32">Suluk<br><span class="text-[8px] font-normal text-gray-400">(Kelakuan)</span></th>
                            <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest w-32">Muwathobah<br><span class="text-[8px] font-normal text-gray-400">(Kerajinan)</span></th>
                            <th class="px-6 py-3 text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest w-32">Nadhofah<br><span class="text-[8px] font-normal text-gray-400">(Kebersihan)</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 text-[13px]">
                        <?php foreach ($students as $idx => $s): 
                            $studentId = $s['id'];
                            $b = $behaviors[$studentId] ?? null;
                            $sulukVal = $b ? $b['suluk'] : '';
                            $muwathobahVal = $b ? $b['muwathobah'] : '';
                            $nadhofahVal = $b ? $b['nadhofah'] : '';
                        ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 text-gray-400 font-medium"><?= $idx + 1 ?></td>
                                <td class="px-6 py-4 font-mono text-gray-600"><?= htmlspecialchars($s['nis']) ?></td>
                                <td class="px-6 py-4 font-bold text-gray-900"><?= htmlspecialchars($s['nama']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <input type="hidden" name="student_id[]" value="<?= $studentId ?>">
                                    <input type="number" 
                                           id="desk-suluk-<?= $studentId ?>"
                                           name="suluk[<?= $studentId ?>]" 
                                           value="<?= htmlspecialchars($sulukVal) ?>" 
                                           min="0" max="100"
                                           placeholder="-"
                                           oninput="document.getElementById('mob-suluk-<?= $studentId ?>').value = this.value"
                                           class="w-20 text-center font-bold text-gray-800 border border-gray-300 rounded-lg p-1.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <input type="number" 
                                           id="desk-muwathobah-<?= $studentId ?>"
                                           name="muwathobah[<?= $studentId ?>]" 
                                           value="<?= htmlspecialchars($muwathobahVal) ?>" 
                                           min="0" max="100"
                                           placeholder="-"
                                           oninput="document.getElementById('mob-muwathobah-<?= $studentId ?>').value = this.value"
                                           class="w-20 text-center font-bold text-gray-800 border border-gray-300 rounded-lg p-1.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <input type="number" 
                                           id="desk-nadhofah-<?= $studentId ?>"
                                           name="nadhofah[<?= $studentId ?>]" 
                                           value="<?= htmlspecialchars($nadhofahVal) ?>" 
                                           min="0" max="100"
                                           placeholder="-"
                                           oninput="document.getElementById('mob-nadhofah-<?= $studentId ?>').value = this.value"
                                           class="w-20 text-center font-bold text-gray-800 border border-gray-300 rounded-lg p-1.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View Card List -->
            <div class="grid grid-cols-1 divide-y divide-gray-150 md:hidden bg-white">
                <?php foreach ($students as $idx => $s): 
                    $studentId = $s['id'];
                    $b = $behaviors[$studentId] ?? null;
                    $sulukVal = $b ? $b['suluk'] : '';
                    $muwathobahVal = $b ? $b['muwathobah'] : '';
                    $nadhofahVal = $b ? $b['nadhofah'] : '';
                ?>
                    <div class="p-4 flex flex-col gap-3">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 font-bold text-xs flex items-center justify-center"><?= $idx + 1 ?></span>
                                <h4 class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($s['nama']) ?></h4>
                            </div>
                            <span class="font-mono text-[10px] text-gray-400">NIS: <?= htmlspecialchars($s['nis']) ?></span>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1 text-center">Suluk</label>
                                <input type="number" 
                                       id="mob-suluk-<?= $studentId ?>"
                                       name="suluk_mob[<?= $studentId ?>]" 
                                       value="<?= htmlspecialchars($sulukVal) ?>" 
                                       min="0" max="100"
                                       placeholder="-"
                                       oninput="document.getElementById('desk-suluk-<?= $studentId ?>').value = this.value"
                                       class="w-full text-center font-bold text-gray-800 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1 text-center">Muwathobah</label>
                                <input type="number" 
                                       id="mob-muwathobah-<?= $studentId ?>"
                                       name="muwathobah_mob[<?= $studentId ?>]" 
                                       value="<?= htmlspecialchars($muwathobahVal) ?>" 
                                       min="0" max="100"
                                       placeholder="-"
                                       oninput="document.getElementById('desk-muwathobah-<?= $studentId ?>').value = this.value"
                                       class="w-full text-center font-bold text-gray-800 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1 text-center">Nadhofah</label>
                                <input type="number" 
                                       id="mob-nadhofah-<?= $studentId ?>"
                                       name="nadhofah_mob[<?= $studentId ?>]" 
                                       value="<?= htmlspecialchars($nadhofahVal) ?>" 
                                       min="0" max="100"
                                       placeholder="-"
                                       oninput="document.getElementById('desk-nadhofah-<?= $studentId ?>').value = this.value"
                                       class="w-full text-center font-bold text-gray-800 border border-gray-300 rounded-lg p-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Sticky Save Button for Mobile / Standard Save Button for Desktop -->
        <div class="sticky bottom-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-t border-indigo-100 p-4 -mx-4 flex justify-end gap-3 md:relative md:border-0 md:p-0 md:bg-transparent md:mx-0 md:mt-8 z-30 shadow-lg md:shadow-none no-print mb-8">
            <a href="?id=<?= $kelas['id'] ?>&tab=overview" class="px-4 py-2 border border-gray-300 bg-white text-sm font-semibold text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">Batal</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-sm font-semibold text-white rounded-lg shadow-md transition-colors flex items-center gap-2">
                <i class="ri-save-line"></i> Simpan Nilai Perilaku
            </button>
        </div>
    </form>
<?php endif; ?>
