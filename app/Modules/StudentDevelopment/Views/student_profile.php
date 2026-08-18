<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">

    <!-- Back Navigation -->
    <div class="mb-6 flex justify-between items-center">
        <a href="<?= url('/student-development') ?>" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-semibold transition-all">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
        <a href="<?= url('/student-development/observe?student_id=' . $profile['id']) ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition-all flex items-center gap-1">
            <i class="ri-add-line"></i> Catat Observasi Baru
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar (1 Col) -->
        <div class="space-y-6">
            <!-- Student Identity Card -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 flex flex-col items-center text-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl font-black shrink-0">
                    <?= strtoupper(substr($profile['nama'], 0, 2)) ?>
                </div>
                <div>
                    <h1 class="text-base font-black text-slate-800 leading-tight"><?= htmlspecialchars($profile['nama']) ?></h1>
                    <p class="text-xs text-slate-400 font-medium mt-1">NIS: <?= htmlspecialchars($profile['nis']) ?></p>
                    <p class="text-xs text-slate-400 font-medium mt-0.5"><?= $profile['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></p>
                    <div class="mt-3 flex flex-col items-center gap-1.5">
                        <span class="bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-indigo-100">
                            Kelas <?= htmlspecialchars($profile['tingkat']) ?>-<?= htmlspecialchars($profile['abjad']) ?>
                        </span>
                        <span class="text-xs text-slate-400">
                            Wali: <span class="font-bold text-slate-500"><?= htmlspecialchars($profile['wali_kelas'] ?? '-') ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <!-- Metrics Summary Card -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-3">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="ri-dashboard-3-line text-indigo-600"></i> Ringkasan Observasi
                </h3>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-2 bg-green-50/50 rounded-xl border border-green-100">
                        <span class="text-base font-black text-green-700 block leading-none"><?= $stats['positif'] ?></span>
                        <span class="text-[9px] font-bold text-green-600 block mt-1">Positif</span>
                    </div>
                    <div class="p-2 bg-amber-50/50 rounded-xl border border-amber-100">
                        <span class="text-base font-black text-amber-700 block leading-none"><?= $stats['perhatian'] ?></span>
                        <span class="text-[9px] font-bold text-amber-600 block mt-1">Perhatian</span>
                    </div>
                    <div class="p-2 bg-blue-50/50 rounded-xl border border-blue-100">
                        <span class="text-base font-black text-blue-700 block leading-none"><?= $stats['informasi'] ?></span>
                        <span class="text-[9px] font-bold text-blue-600 block mt-1">Info</span>
                    </div>
                </div>
            </div>

            <!-- Sidebar Category Distribution -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <i class="ri-bar-chart-2-line text-indigo-600"></i> Distribusi Kategori
                </h3>
                <div class="space-y-3">
                    <?php foreach ($distribution as $dist): ?>
                        <div>
                            <div class="flex justify-between text-[11px] font-semibold mb-1">
                                <span class="text-slate-600"><?= htmlspecialchars($dist['category']) ?></span>
                                <span class="text-slate-400"><?= $dist['count'] ?></span>
                            </div>
                            <div class="w-full bg-slate-100 h-1 rounded-full overflow-hidden">
                                <?php 
                                    $percent = $stats['total'] > 0 ? ($dist['count'] / $stats['total']) * 100 : 0; 
                                ?>
                                <div class="bg-indigo-600 h-full rounded-full transition-all" style="width: <?= $percent ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Timeline Perkembangan (3 Cols) -->
        <div class="lg:col-span-3 space-y-6">
            <h2 class="text-lg font-black text-slate-800 flex items-center gap-2 mb-4">
                <i class="ri-git-commit-line text-indigo-600"></i> Riwayat & Timeline Perkembangan
            </h2>

            <?php if (empty($timeline)): ?>
                <div class="bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-100">
                    <i class="ri-chat-delete-line text-5xl mb-3 text-slate-300 block"></i>
                    Belum ada riwayat catatan observasi untuk santri ini.
                </div>
            <?php else: ?>
                <!-- Timeline container -->
                <div class="relative pl-6 border-l-2 border-slate-100 ml-4 space-y-5 pb-2">
                    <?php foreach ($timeline as $obs): ?>
                        <!-- Timeline Node -->
                        <div class="relative pb-1">
                            <!-- Bullet Icon on line -->
                            <div class="absolute -left-[35px] top-0 w-6 h-6 rounded-full border-4 border-white shadow-md flex items-center justify-center text-white
                                <?php if ($obs['type'] === 'Positif'): ?> bg-green-500
                                <?php elseif ($obs['type'] === 'Perhatian'): ?> bg-amber-500
                                <?php else: ?> bg-blue-500 <?php endif; ?>">
                                <?php if ($obs['type'] === 'Positif'): ?><i class="ri-checkbox-circle-fill text-[10px]"></i>
                                <?php elseif ($obs['type'] === 'Perhatian'): ?><i class="ri-alert-fill text-[10px]"></i>
                                <?php else: ?><i class="ri-information-fill text-[10px]"></i><?php endif; ?>
                            </div>

                            <!-- Date beside the dot (above the card) -->
                            <div class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 h-6 mb-1">
                                <i class="ri-calendar-line text-[10px]"></i> <?= date('d M Y', strtotime($obs['observation_date'])) ?>
                            </div>

                            <!-- Timeline Card -->
                            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-50 pb-2">
                                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                        <!-- Tipe Badge -->
                                        <?php if ($obs['type'] === 'Positif'): ?>
                                            <span class="bg-green-50 text-green-700 px-2 py-0.5 rounded-md font-bold text-[10px] border border-green-200">Positif</span>
                                        <?php elseif ($obs['type'] === 'Perhatian'): ?>
                                            <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md font-bold text-[10px] border border-amber-200">Perhatian</span>
                                        <?php else: ?>
                                            <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-md font-bold text-[10px] border border-blue-200">Informasi</span>
                                        <?php endif; ?>

                                        <!-- Kategori -->
                                        <span class="px-2 py-0.5 rounded-md font-medium text-[10px] border" style="background-color: <?= ($obs['category_color'] ?? '#64748b') ?>15; color: <?= $obs['category_color'] ?? '#64748b' ?>; border-color: <?= $obs['category_color'] ?? '#64748b' ?>30;">
                                            <?= htmlspecialchars($obs['category_name']) ?>
                                        </span>

                                        <?php if ($obs['subject_name']): ?>
                                            <span class="bg-indigo-50/50 text-indigo-600 px-2 py-0.5 rounded-md font-medium text-[10px] border border-indigo-100">
                                                <?= htmlspecialchars($obs['subject_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-semibold flex items-center gap-1">
                                        <span><i class="ri-user-line"></i> <?= htmlspecialchars($obs['teacher_name']) ?></span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <p class="text-slate-700 text-sm leading-relaxed whitespace-pre-line"><?= htmlspecialchars($obs['content']) ?></p>

                                 <!-- Context Banner (if context present) -->
                                 <?php if (!empty($obs['context'])): ?>
                                     <div class="bg-amber-50/40 border-l-4 border-amber-300 p-2.5 rounded-r-lg text-xs flex items-start gap-1.5">
                                         <i class="ri-information-line text-amber-600 mt-0.5 shrink-0"></i>
                                         <div>
                                             <span class="font-bold text-amber-950">Konteks:</span> 
                                             <span class="italic text-slate-600"><?= htmlspecialchars($obs['context']) ?></span>
                                         </div>
                                     </div>
                                 <?php endif; ?>

                                 <!-- Responses List -->
                                 <?php if (!empty($obs['responses'])): ?>
                                     <div class="bg-indigo-50/30 border-l-4 border-indigo-300 rounded-r-lg p-3 space-y-2">
                                         <div class="text-[10px] font-bold text-indigo-800 uppercase tracking-wider flex items-center gap-1">
                                             <i class="ri-reply-line"></i> Tanggapan & Tindak Lanjut
                                         </div>
                                         <div class="space-y-2 divide-y divide-slate-100">
                                             <?php foreach ($obs['responses'] as $resp): ?>
                                                 <div class="pt-2 first:pt-0 text-xs">
                                                     <div class="flex justify-between font-bold text-slate-700">
                                                         <span><?= htmlspecialchars($resp['user_name']) ?> <span class="text-[9px] text-indigo-600 bg-indigo-100/50 font-semibold px-1.5 py-0.2 rounded"><?= ucfirst($resp['user_role']) ?></span></span>
                                                         <span class="text-slate-400 font-normal text-[10px]"><?= date('d M H:i', strtotime($resp['created_at'])) ?></span>
                                                     </div>
                                                     <p class="text-slate-600 mt-0.5 leading-relaxed"><?= htmlspecialchars($resp['content']) ?></p>
                                                 </div>
                                             <?php endforeach; ?>
                                         </div>
                                     </div>
                                 <?php endif; ?>

                                <!-- Interactive Actions (Update Context or Add Response) for Authorized roles -->
                                <?php 
                                    $isCreator = ($obs['teacher_id'] == $userId);
                                    $isWali = false;
                                    $waliClasses = auth_get_wali_kelas_kelas();
                                    foreach ($waliClasses as $kls) {
                                        if ($kls['id'] == $profile['kelas_id']) {
                                            $isWali = true;
                                            break;
                                        }
                                    }
                                    $isAdmin = ($role === 'admin');
                                ?>

                                <?php if ($isCreator || $isWali || $isAdmin): ?>
                                    <div class="flex flex-wrap gap-2 text-[10px] pt-1">
                                        <!-- Show Context form toggle -->
                                        <button onclick="toggleForm('context-form-<?= $obs['id'] ?>')" class="text-slate-500 hover:text-slate-800 bg-slate-50 hover:bg-slate-100 font-semibold px-2.5 py-1 rounded-lg border border-slate-200 transition-all">
                                            <?= empty($obs['context']) ? '<i class="ri-add-line"></i> Tambah Konteks' : '<i class="ri-edit-line"></i> Edit Konteks' ?>
                                        </button>

                                        <!-- Show Response form toggle (Wali kelas & Admin only) -->
                                        <?php if ($isWali || $isAdmin): ?>
                                            <button onclick="toggleForm('response-form-<?= $obs['id'] ?>')" class="text-indigo-600 hover:text-white bg-indigo-50 hover:bg-indigo-600 font-semibold px-2.5 py-1 rounded-lg border border-indigo-100 hover:border-indigo-600 transition-all">
                                                <i class="ri-reply-line"></i> Beri Tanggapan
                                            </button>
                                        <?php endif; ?>

                                        <!-- Hapus Button (Creator & Admin only) -->
                                        <?php if ($isCreator || $isAdmin): ?>
                                            <form method="POST" action="<?= url('/student-development/observation/delete') ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan observasi ini?')" class="inline-block">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="observation_id" value="<?= $obs['id'] ?>">
                                                <input type="hidden" name="student_id" value="<?= $profile['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-white bg-red-50 hover:bg-red-600 font-semibold px-2.5 py-1 rounded-lg border border-red-100 hover:border-red-600 transition-all flex items-center gap-1">
                                                    <i class="ri-delete-bin-line"></i> Hapus
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Add Context Form -->
                                    <form id="context-form-<?= $obs['id'] ?>" method="POST" action="<?= url('/student-development/observation/context') ?>" class="hidden bg-slate-50 p-3 rounded-lg border border-slate-200 space-y-2 animate-fadeIn">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="observation_id" value="<?= $obs['id'] ?>">
                                        <input type="hidden" name="student_id" value="<?= $profile['id'] ?>">
                                        <textarea name="context" rows="2" placeholder="Masukkan konteks..." class="block w-full border-slate-200 rounded-lg text-xs p-2 border focus:border-indigo-500 focus:ring-indigo-500" required><?= htmlspecialchars($obs['context'] ?? '') ?></textarea>
                                        <div class="flex gap-2">
                                            <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white text-[10px] font-bold px-2.5 py-1 rounded-md">Simpan</button>
                                            <button type="button" onclick="toggleForm('context-form-<?= $obs['id'] ?>')" class="text-slate-500 bg-white border border-slate-200 text-[10px] font-semibold px-2.5 py-1 rounded-md hover:bg-slate-50">Batal</button>
                                        </div>
                                    </form>

                                    <!-- Add Response Form -->
                                    <form id="response-form-<?= $obs['id'] ?>" method="POST" action="<?= url('/student-development/observation/respond') ?>" class="hidden bg-indigo-50/30 p-3 rounded-lg border border-indigo-100 space-y-2 animate-fadeIn">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="observation_id" value="<?= $obs['id'] ?>">
                                        <input type="hidden" name="student_id" value="<?= $profile['id'] ?>">
                                        <textarea name="content" rows="2" placeholder="Ketik tanggapan..." class="block w-full border-slate-200 rounded-lg text-xs p-2 border focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                                        <div class="flex gap-2">
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold px-2.5 py-1 rounded-md">Kirim</button>
                                            <button type="button" onclick="toggleForm('response-form-<?= $obs['id'] ?>')" class="text-slate-500 bg-white border border-slate-200 text-[10px] font-semibold px-2.5 py-1 rounded-md hover:bg-slate-50">Batal</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<script>
    function toggleForm(formId) {
        const form = document.getElementById(formId);
        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
        } else {
            form.classList.add('hidden');
        }
    }
</script>
