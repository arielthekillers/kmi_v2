<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>
<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">

    <!-- Back Navigation -->
    <div class="mb-6">
        <a href="<?= url('/student-development') ?>" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-semibold transition-all">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white p-5 md:p-8">
            <h1 class="text-xl md:text-2xl font-extrabold flex items-center gap-2">
                <i class="ri-draft-line"></i> <?= htmlspecialchars($title) ?>
            </h1>
            <p class="text-indigo-100 text-[11px] md:text-sm mt-1 leading-relaxed">
                Tulis fakta objektif dari perilaku, peningkatan, prestasi, atau perubahan yang teramati pada santri atau kelas secara umum.
            </p>
        </div>

        <form method="POST" action="<?= $action ?>" class="p-4 sm:p-6 md:p-8 space-y-6">
            <?= csrf_input() ?>
            
            <?php if (!empty($_GET['student_id']) || !empty($observation)): ?>
                <input type="hidden" name="redirect_student_profile" value="1">
            <?php endif; ?>

            <?php if (!empty($observation)): ?>
                <input type="hidden" name="observation_id" value="<?= $observation['id'] ?>">
            <?php endif; ?>

            <!-- Target Selection Toggle -->
            <?php 
            $isEdit = !empty($observation);
            $targetType = 'student';
            if ($isEdit) {
                $targetType = empty($observation['student_id']) ? 'class' : 'student';
            }
            ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="target_type" value="<?= $targetType ?>">
            <?php endif; ?>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Target Observasi <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-2xl w-full">
                    <label class="inline-flex items-center justify-center cursor-pointer px-4 py-2.5 rounded-xl font-bold text-xs transition-all select-none text-center" id="label-target-student">
                        <input type="radio" name="target_type" value="student" class="sr-only" <?= $targetType === 'student' ? 'checked' : '' ?> onchange="toggleTarget(this.value)" <?= $isEdit ? 'disabled' : '' ?>>
                        <i class="ri-user-line mr-1.5"></i> Personal (Per Santri)
                    </label>
                    <label class="inline-flex items-center justify-center cursor-pointer px-4 py-2.5 rounded-xl font-bold text-xs transition-all text-slate-500 select-none text-center" id="label-target-class">
                        <input type="radio" name="target_type" value="class" class="sr-only" <?= $targetType === 'class' ? 'checked' : '' ?> onchange="toggleTarget(this.value)" <?= $isEdit ? 'disabled' : '' ?>>
                        <i class="ri-home-4-line mr-1.5"></i> Kolektif (Per Kelas)
                    </label>
                </div>
            </div>

            <!-- 1. Student Selector -->
            <div id="student-selector-container" class="space-y-1">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Santri <span class="text-red-500">*</span></label>
                <select id="student-select" name="student_ids[]" class="block w-full rounded-xl focus:ring-indigo-500 focus:border-indigo-500" multiple required>
                    <option value="">-- Cari Nama atau NIS Santri --</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>" data-class-id="<?= $s['kelas_id'] ?>" <?= (isset($observation) && $observation['student_id'] == $s['id']) || $preselected_student == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nama']) ?> (NIS: <?= htmlspecialchars($s['nis']) ?>) - Kelas <?= htmlspecialchars($s['tingkat']) ?>-<?= htmlspecialchars($s['abjad']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 2. Class Selector (Hidden by default, shown only for Class target type) -->
            <div id="class-selector-container" class="hidden space-y-1">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kelas <span class="text-red-500">*</span></label>
                <select id="kelas-select" name="kelas_id" class="block w-full rounded-xl text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= isset($observation) && $observation['kelas_id'] == $k['id'] ? 'selected' : '' ?>>Kelas <?= htmlspecialchars($k['tingkat']) ?>-<?= htmlspecialchars($k['abjad']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 3. Contextual Schedule Prompter (If available) -->
            <?php if (!empty($schedule_context)): ?>
                <div id="schedule-prompter" class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4">
                    <h4 class="text-xs font-bold text-indigo-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i class="ri-time-line"></i> Konteks Jadwal KBM Hari Ini
                    </h4>
                    <p class="text-xs text-indigo-600 mb-3">Klik tombol jadwal di bawah ini jika observasi terjadi saat pembelajaran berlangsung untuk mengisi Kelas & Mapel secara otomatis:</p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($schedule_context as $sch): ?>
                            <button type="button" 
                                    onclick="applySchedule(<?= $sch['kelas_id'] ?>, <?= $sch['subject_id'] ?>)"
                                    class="bg-white hover:bg-indigo-600 hover:text-white text-indigo-700 text-xs font-bold px-3 py-2 rounded-xl border border-indigo-200 transition-all flex items-center gap-1">
                                <i class="ri-book-open-line"></i>
                                Jam <?= htmlspecialchars($sch['hour']) ?>: Kelas <?= htmlspecialchars($sch['tingkat']) ?>-<?= htmlspecialchars($sch['abjad']) ?> - <?= htmlspecialchars($sch['subject_name']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 4. Observation Type (Positif, Perhatian, Informasi) -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tipe Observasi <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Positif -->
                    <label class="relative flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:bg-slate-50/50 transition-all select-none group focus-within:ring-2 focus-within:ring-green-500">
                        <input type="radio" name="type" value="Positif" class="sr-only peer" <?= (!isset($observation) || $observation['type'] === 'Positif') ? 'checked' : '' ?> required>
                        <div class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center peer-checked:bg-green-600 peer-checked:text-white transition-all shrink-0">
                            <i class="ri-checkbox-circle-line text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-700 block">Positif</span>
                            <span class="text-[10px] text-slate-400 block truncate">Prestasi, peningkatan, kebaikan</span>
                        </div>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-green-500 rounded-2xl pointer-events-none"></div>
                    </label>

                    <!-- Perhatian -->
                    <label class="relative flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:bg-slate-50/50 transition-all select-none group focus-within:ring-2 focus-within:ring-amber-500">
                        <input type="radio" name="type" value="Perhatian" class="sr-only peer" <?= (isset($observation) && $observation['type'] === 'Perhatian') ? 'checked' : '' ?> required>
                        <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center peer-checked:bg-amber-600 peer-checked:text-white transition-all shrink-0">
                            <i class="ri-alert-line text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-700 block">Perhatian</span>
                            <span class="text-[10px] text-slate-400 block truncate">Mengantuk, menarik diri, tidak fokus</span>
                        </div>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-amber-500 rounded-2xl pointer-events-none"></div>
                    </label>

                    <!-- Informasi -->
                    <label class="relative flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:bg-slate-50/50 transition-all select-none group focus-within:ring-2 focus-within:ring-blue-500">
                        <input type="radio" name="type" value="Informasi" class="sr-only peer" <?= (isset($observation) && $observation['type'] === 'Informasi') ? 'checked' : '' ?> required>
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center peer-checked:bg-blue-600 peer-checked:text-white transition-all shrink-0">
                            <i class="ri-information-line text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-700 block">Informasi</span>
                            <span class="text-[10px] text-slate-400 block truncate">Lomba, kepanitiaan, kegiatan</span>
                        </div>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-blue-500 rounded-2xl pointer-events-none"></div>
                    </label>
                </div>
            </div>

            <!-- 5. Category -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kategori <span class="text-red-500">*</span></label>
                <select id="category-select" name="category_id" class="block w-full rounded-xl focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-description="<?= htmlspecialchars($cat['description'] ?? '') ?>" <?= isset($observation) && $observation['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="category-description-text" class="text-[11px] text-slate-400 mt-1.5 italic hidden"></div>
            </div>

            <!-- 6. Observation Content -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Isi Catatan Observasi <span class="text-red-500">*</span></label>
                    <span class="text-[11px] text-slate-400 italic">Tuliskan fakta objektif</span>
                </div>
                <textarea name="content" rows="4" placeholder="Contoh: Ahmad tertidur pada menit ke-20 pembelajaran Matematika berlangsung.&#10;Atau untuk Kelas: Kelas 5-C Pa terlihat sangat berantakan dan kotor sebelum jam pelajaran pertama dimulai." class="block w-full border-slate-200 rounded-xl text-sm p-3.5 border focus:border-indigo-500 focus:ring-indigo-500" required><?= isset($observation) ? htmlspecialchars($observation['content']) : '' ?></textarea>
            </div>

            <!-- Toggle Opsi Tambahan -->
            <div class="pt-2">
                <button type="button" onclick="toggleAdvancedOptions()" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 text-xs font-bold transition-all" id="toggle-advanced-btn">
                    <i class="ri-add-circle-line text-sm"></i> Tampilkan Opsi Tambahan (Konteks, Mapel, Tanggal)
                </button>
            </div>

            <!-- Advanced Options Container -->
            <div id="advanced-options" class="hidden space-y-6 pt-4 border-t border-dashed border-slate-200">
                <!-- 7. Additional Context (Optional) -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Konteks Tambahan <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                    </div>
                    <textarea name="context" rows="2" placeholder="Contoh: Kejadian ini disebabkan oleh keterlambatan petugas piket kelas membersihkan ruang kelas." class="block w-full border-slate-200 rounded-xl text-sm p-3 border focus:border-indigo-500 focus:ring-indigo-500"><?= isset($observation) ? htmlspecialchars($observation['context']) : '' ?></textarea>
                </div>

                <!-- 8. Academic Context (Optional & Prefilled by schedule) -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Ditautkan ke Mata Pelajaran <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                    <select id="subject-select" name="subject_id" class="block w-full rounded-xl text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Tanpa Pelajaran Khusus --</option>
                        <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>" <?= isset($observation) && $observation['subject_id'] == $sub['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sub['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 9. Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Kejadian</label>
                    <div class="relative">
                        <input type="date" name="observation_date" value="<?= isset($observation) ? htmlspecialchars($observation['observation_date']) : date('Y-m-d') ?>" class="flatpickr block w-full border-slate-200 rounded-xl text-sm p-3 pl-10 border focus:border-indigo-500 focus:ring-indigo-500">
                        <i class="ri-calendar-line absolute left-3.5 top-3.5 text-slate-400"></i>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-slate-100 flex gap-3">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white p-3.5 rounded-xl text-sm font-bold shadow-md shadow-indigo-100 transition-all flex justify-center items-center gap-2">
                    <i class="ri-save-line text-lg"></i> Simpan
                </button>
                <a href="<?= url('/student-development') ?>" class="w-1/3 bg-slate-50 hover:bg-slate-100 text-slate-700 p-3.5 rounded-xl text-sm font-semibold border border-slate-200 text-center transition-all">
                    Batal
                </a>
            </div>
        </form>
    </div>

</main>

<script>
    // Initialize TomSelect and Flatpickr on page load
    document.addEventListener("DOMContentLoaded", function() {
        window.tomSelectInstance = new TomSelect("#student-select", {
            create: false,
            plugins: ['remove_button'],
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        window.tomSelectKelas = new TomSelect("#kelas-select", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        window.tomSelectSubject = new TomSelect("#subject-select", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        window.tomSelectCategory = new TomSelect("#category-select", {
            create: false,
            render: {
                option: function(data, escape) {
                    return '<div class="py-1.5 px-3 border-b border-slate-50/50">' +
                        '<div class="font-bold text-slate-800 text-sm">' + escape(data.text) + '</div>' +
                        (data.description ? '<div class="text-[10px] text-slate-400 leading-normal whitespace-normal mt-0.5">' + escape(data.description) + '</div>' : '') +
                    '</div>';
                },
                item: function(data, escape) {
                    return '<div class="font-semibold text-slate-700 text-sm">' + escape(data.text) + '</div>';
                }
            }
        });

        // Initialize flatpickr on date fields
        flatpickr(".flatpickr", {
            dateFormat: "Y-m-d",
            maxDate: "today"
        });

        // Setup style initial target state
        toggleTarget('<?= $targetType ?>');

        <?php if (isset($observation) && (!empty($observation['context']) || !empty($observation['subject_id']))): ?>
            toggleAdvancedOptions();
        <?php endif; ?>

        // Watch category selection change to update description banner
        window.tomSelectCategory.on('change', function(value) {
            const descDiv = document.getElementById("category-description-text");
            if (!value) {
                descDiv.classList.add('hidden');
                descDiv.innerText = '';
                return;
            }
            const option = window.tomSelectCategory.options[value];
            const description = option ? option.$option.getAttribute('data-description') : null;
            if (description) {
                descDiv.classList.remove('hidden');
                descDiv.innerHTML = '<i class="ri-information-line"></i> Keterangan: ' + description;
            } else {
                descDiv.classList.add('hidden');
                descDiv.innerText = '';
            }
        });

        // Watch student selection change
        window.tomSelectInstance.on('change', function(value) {
            if (!value || value.length === 0) {
                clearSubjects();
                return;
            }
            // For multiple selection, we get an array. Take the first selected student's class to load subjects.
            const firstVal = Array.isArray(value) ? value[0] : value;
            const option = window.tomSelectInstance.options[firstVal];
            const classId = option ? option.$option.getAttribute('data-class-id') : null;
            if (classId) {
                fetchClassSubjects(classId);
            } else {
                clearSubjects();
            }
        });

        // Watch class selection change
        window.tomSelectKelas.on('change', function(value) {
            if (value) {
                fetchClassSubjects(value);
            } else {
                clearSubjects();
            }
        });

        // Handle pre-selected student initial subjects load
        const initialStudent = document.getElementById("student-select").value;
        const initialSubject = '<?= $observation['subject_id'] ?? '' ?>';
        if (initialStudent) {
            const opt = window.tomSelectInstance.options[initialStudent];
            const cid = opt ? opt.$option.getAttribute('data-class-id') : null;
            if (cid) {
                fetchClassSubjects(cid, initialSubject);
            }
        } else {
            const initialClass = document.getElementById("kelas-select").value;
            if (initialClass) {
                fetchClassSubjects(initialClass, initialSubject);
            }
        }
    });

    function toggleTarget(target) {
        const studentContainer = document.getElementById("student-selector-container");
        const classContainer = document.getElementById("class-selector-container");
        
        const labelStudent = document.getElementById("label-target-student");
        const labelClass = document.getElementById("label-target-class");
        
        const studentSelect = document.getElementById("student-select");
        const classSelect = document.getElementById("kelas-select");
        
        const schedulePrompter = document.getElementById("schedule-prompter");

        if (target === 'student') {
            // Display student, hide class
            studentContainer.classList.remove('hidden');
            classContainer.classList.add('hidden');
            if (schedulePrompter) schedulePrompter.classList.remove('hidden');

            studentSelect.setAttribute('required', 'required');
            classSelect.removeAttribute('required');

            if (window.tomSelectKelas) {
                window.tomSelectKelas.clear();
            }

            // Style labels
            labelStudent.className = "inline-flex items-center justify-center cursor-pointer px-4 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-xs transition-all select-none text-center";
            labelClass.className = "inline-flex items-center justify-center cursor-pointer px-4 py-2.5 text-slate-500 rounded-xl font-bold text-xs transition-all select-none text-center hover:text-slate-800";
            
            // Re-trigger subject loading based on active student
            if (window.tomSelectInstance && window.tomSelectInstance.getValue()) {
                const val = window.tomSelectInstance.getValue();
                const firstVal = Array.isArray(val) ? val[0] : val;
                if (firstVal) {
                    const opt = window.tomSelectInstance.options[firstVal];
                    const cid = opt ? opt.$option.getAttribute('data-class-id') : null;
                    if (cid) fetchClassSubjects(cid);
                } else {
                    clearSubjects();
                }
            } else {
                clearSubjects();
            }
        } else {
            // Display class, hide student
            studentContainer.classList.add('hidden');
            classContainer.classList.remove('hidden');
            if (schedulePrompter) schedulePrompter.classList.add('hidden');

            studentSelect.removeAttribute('required');
            classSelect.setAttribute('required', 'required');

            if (window.tomSelectInstance) {
                window.tomSelectInstance.clear();
            }

            // Style labels
            labelStudent.className = "inline-flex items-center justify-center cursor-pointer px-4 py-2.5 text-slate-500 rounded-xl font-bold text-xs transition-all select-none text-center hover:text-slate-800";
            labelClass.className = "inline-flex items-center justify-center cursor-pointer px-4 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-xs transition-all select-none text-center";
            
            // Re-trigger subject loading based on active class selector
            if (window.tomSelectKelas) {
                const cid = window.tomSelectKelas.getValue();
                if (cid) {
                    fetchClassSubjects(cid);
                } else {
                    clearSubjects();
                }
            }
        }
    }

    // Fetch subjects list for specific class
    function fetchClassSubjects(classId, selectedId = '') {
        fetch('<?= url("/api/student-development/class-subjects?kelas_id=") ?>' + classId)
            .then(response => response.json())
            .then(data => {
                if (window.tomSelectSubject) {
                    window.tomSelectSubject.clear();
                    window.tomSelectSubject.clearOptions();
                    
                    window.tomSelectSubject.addOption({value: '', text: '-- Tanpa Pelajaran Khusus --'});
                    if (data && data.length > 0) {
                        data.forEach(sub => {
                            window.tomSelectSubject.addOption({value: sub.id, text: sub.nama});
                        });
                    }
                    window.tomSelectSubject.setValue(selectedId);
                }
            })
            .catch(err => console.error('Error fetching subjects:', err));
    }

    // Clear subjects list
    function clearSubjects() {
        if (window.tomSelectSubject) {
            window.tomSelectSubject.clear();
            window.tomSelectSubject.clearOptions();
            window.tomSelectSubject.addOption({value: '', text: '-- Tanpa Pelajaran Khusus --'});
            window.tomSelectSubject.setValue('');
        }
    }

    // Helper to auto-fill KBM context from schedule and pre-load all subjects for that class
    function applySchedule(kelasId, subjectId) {
        fetch('<?= url("/api/student-development/class-subjects?kelas_id=") ?>' + kelasId)
            .then(response => response.json())
            .then(data => {
                if (window.tomSelectSubject) {
                    window.tomSelectSubject.clear();
                    window.tomSelectSubject.clearOptions();
                    
                    window.tomSelectSubject.addOption({value: '', text: '-- Tanpa Pelajaran Khusus --'});
                    if (data && data.length > 0) {
                        data.forEach(sub => {
                            window.tomSelectSubject.addOption({value: sub.id, text: sub.nama});
                        });
                    }
                    window.tomSelectSubject.setValue(subjectId);
                }
            })
            .catch(err => console.error(err));
        
        // Show subtle notification
        const flashMsg = document.createElement('div');
        flashMsg.className = 'fixed bottom-4 right-4 bg-indigo-900 text-white px-4 py-2.5 rounded-xl text-xs font-semibold shadow-lg z-50 flex items-center gap-2';
        flashMsg.innerHTML = '<i class="ri-checkbox-circle-line text-green-400 text-sm"></i> Mata Pelajaran KBM diterapkan!';
        document.body.appendChild(flashMsg);
        setTimeout(() => flashMsg.remove(), 2500);
    }

    function toggleAdvancedOptions() {
        const advDiv = document.getElementById("advanced-options");
        const btn = document.getElementById("toggle-advanced-btn");
        if (advDiv.classList.contains('hidden')) {
            advDiv.classList.remove('hidden');
            advDiv.classList.add('animate-fadeIn');
            btn.innerHTML = '<i class="ri-indeterminate-circle-line text-sm"></i> Sembunyikan Opsi Tambahan';
        } else {
            advDiv.classList.add('hidden');
            advDiv.classList.remove('animate-fadeIn');
            btn.innerHTML = '<i class="ri-add-circle-line text-sm"></i> Tampilkan Opsi Tambahan (Konteks, Mapel, Tanggal)';
        }
    }
</script>
