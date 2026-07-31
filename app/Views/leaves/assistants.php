<?php
$assistants = $assistants ?? [];
$teachers = $teachers ?? [];
$subjects = $subjects ?? [];
?>

<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-7xl mx-auto">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="<?= url('/leaves') ?>" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 transition-all shadow-sm">
                <i class="ri-arrow-left-line text-xl"></i>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl text-gray-900 font-bold">Asisten Pengajar Tetap</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola asisten pengajar tetap untuk pengajar di tahun ajaran ini.</p>
            </div>
        </div>
        
        <div>
            <button type="button" onclick="document.getElementById('modal-tambah-asisten').classList.remove('hidden')" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white font-medium text-sm rounded-xl hover:bg-indigo-700 shadow-sm transition-colors gap-2">
                <i class="ri-add-line"></i> Tambah Asisten
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengajar</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Asisten Pengajar Tetap</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    <?php if (empty($assistants)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 mb-4">
                                    <i class="ri-team-line text-2xl text-indigo-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">Belum Ada Asisten</h3>
                                <p class="text-gray-500 text-sm">Anda belum menambahkan asisten pengajar tetap sama sekali.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assistants as $a): ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    <?= htmlspecialchars($a['teacher_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-indigo-100 text-indigo-600">
                                        <i class="ri-shield-star-fill text-[13px]"></i>
                                    </span>
                                    <span class="text-indigo-700"><?= htmlspecialchars($a['assistant_name']) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php if($a['subject_name']): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <?= htmlspecialchars($a['subject_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">
                                            Semua Pelajaran
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="<?= url('/leaves/assistants/delete') ?>" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus asisten ini?')">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors tooltip-btn" title="Hapus Asisten">
                                            <i class="ri-delete-bin-line text-lg"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Asisten -->
<div id="modal-tambah-asisten" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('modal-tambah-asisten').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-visible">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/80 rounded-t-2xl">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="ri-user-add-line text-indigo-600"></i> Tambah Asisten
            </h3>
            <button type="button" onclick="document.getElementById('modal-tambah-asisten').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <form action="<?= url('/leaves/assistants/store') ?>" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Pengajar <span class="text-red-500">*</span></label>
                    <select name="teacher_id" class="tom-select w-full" required>
                        <option value="">Pilih Pengajar...</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Asisten Pengajar Tetap <span class="text-red-500">*</span></label>
                    <select name="assistant_id" class="tom-select w-full" required>
                        <option value="">Pilih Asisten...</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mata Pelajaran <span class="text-gray-400 font-normal text-xs">(Opsional)</span></label>
                    <select name="subject_id" class="tom-select w-full">
                        <option value="">Semua Mata Pelajaran</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500">Kosongkan jika asisten ini dapat menggantikan untuk semua mata pelajaran pengajar tersebut.</p>
                </div>
                
                <div class="pt-4 mt-6 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-tambah-asisten').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-medium text-sm rounded-xl hover:bg-indigo-700 shadow-sm transition-colors flex items-center gap-2">
                        <i class="ri-save-line"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let subjectSelect;
    let assistantSelect;
    let allTeachers = <?= json_encode($teachers) ?>;

    document.querySelectorAll('.tom-select').forEach((el) => {
        let instance = new TomSelect(el, {
            create: false
        });
        
        if (el.name === 'subject_id') {
            subjectSelect = instance;
        }

        if (el.name === 'assistant_id') {
            assistantSelect = instance;
        }
        
        if (el.name === 'teacher_id') {
            instance.on('change', function(value) {
                if (assistantSelect) {
                    let currentAssistant = assistantSelect.getValue();
                    assistantSelect.clear();
                    assistantSelect.clearOptions();
                    assistantSelect.addOption({value: '', text: 'Pilih Asisten...'});
                    
                    allTeachers.forEach(t => {
                        if (String(t.id) !== String(value)) {
                            assistantSelect.addOption({value: String(t.id), text: t.nama});
                        }
                    });
                    
                    if (currentAssistant !== String(value)) {
                        assistantSelect.setValue(currentAssistant);
                    }
                    assistantSelect.refreshOptions(false);
                }

                if (!value) {
                    subjectSelect.clearOptions();
                    subjectSelect.addOption({value: '', text: 'Semua Mata Pelajaran'});
                    subjectSelect.setValue('');
                    return;
                }
                
                // Show loading state
                subjectSelect.clear();
                subjectSelect.clearOptions();
                subjectSelect.addOption({value: '', text: 'Loading...'});
                subjectSelect.setValue('');
                
                fetch('<?= url('/leaves/teacher_subjects') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'teacher_id=' + value
                })
                .then(response => response.json())
                .then(data => {
                    subjectSelect.clear();
                    subjectSelect.clearOptions();
                    subjectSelect.addOption({value: '', text: 'Semua Mata Pelajaran'});
                    
                    data.forEach(subject => {
                        subjectSelect.addOption({value: String(subject.id), text: subject.nama});
                    });
                    
                    subjectSelect.refreshOptions(false);
                    subjectSelect.setValue('');
                })
                .catch(error => {
                    console.error('Error fetching subjects:', error);
                    subjectSelect.clear();
                    subjectSelect.clearOptions();
                    subjectSelect.addOption({value: '', text: 'Semua Mata Pelajaran'});
                    subjectSelect.refreshOptions(false);
                    subjectSelect.setValue('');
                });
            });
        }
    });
</script>

<?php renderFooter(); ?>
