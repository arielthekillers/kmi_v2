<?php
// Prepare Data
$id = $exam['id'];
$userRole = auth_get_role();
$userId = auth_get_user_id();
$isAdmin = ($userRole === 'admin');
$isPanitia = $isPanitia ?? false;
$isAdminOrPanitia = ($isAdmin || $isPanitia);
$isExaminer = (isset($exam['teacher_id']) && $exam['teacher_id'] == $userId);
$canEditOralScore = $isAdminOrPanitia;
$isFinished = ($exam['status'] === 'selesai');
$sessionOpen = (isset($exam['session_is_open']) && $exam['session_is_open'] == 1);

// Logic:
// 1. Finished exams are always read-only for everyone (must Unlock first)
// 2. Admin/Panitia can only edit Skor Maks (Configuration)
// 3. Designated Examiner can only edit Scores (subject to Session Open)
$isReadOnly = $isFinished || (!$isAdminOrPanitia && !$sessionOpen);
$canEditSkorMaks = $isAdminOrPanitia && !$isFinished;
$canEditScores = $isExaminer && $sessionOpen && !$isFinished;

// Insight: Honesty & Integrity First.
// If the user is the designated examiner for this subject, they MUST be blind-folded,
// even if they hold an Admin or Panitia role. Names are only visible to auditors (Admin/Panitia who are NOT the examiner).
$showNames = ($isAdminOrPanitia && !$isExaminer);

// Parse Scale for JS
$skala = $exam['skala'] ?? '80-30';
list($max_val, $min_val) = explode('-', $skala);
$max_val = (int)$max_val;
$min_val = (int)$min_val;
$skor_maks = (float)($exam['skor_maks'] ?? 100);

// Locale-safe values for JS (ensure dot as decimal separator)
$js_skor_maks = number_format($skor_maks, 2, '.', '');
$js_nilai_maks = number_format($max_val, 2, '.', '');
$js_nilai_min = number_format($min_val, 2, '.', '');

renderHeader("Input Nilai - " . htmlspecialchars($exam['mapel_nama']));
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24">
    <style>
        @media (max-width: 767px) {
            .col-nilai {
                display: none !important;
            }
            #studentTableBody tr {
                display: grid !important;
                grid-template-columns: 1fr 2fr !important;
                gap: 0.75rem !important;
            }
            .col-nama {
                grid-column: span 2 !important;
            }
            .col-bayanat {
                grid-column: span 1 !important;
            }
            .col-tulis, .col-lisan {
                grid-column: span 1 !important;
            }
            .col-lisan:not(.hidden) ~ .col-tulis {
                grid-column: span 2 !important;
            }
        }
    </style>
<form method="post" action="<?= url('/grades/update') ?>" id="gradeForm" autocomplete="off">
    <?= csrf_token_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" id="nilai_maks" value="<?= $js_nilai_maks ?>">
    <input type="hidden" id="nilai_min" value="<?= $js_nilai_min ?>">

    <!-- Header Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-indigo-600 px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="<?= url('/grades') ?>" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/10 text-white hover:bg-white/20 transition-all shadow-inner" title="Kembali ke Daftar">
                    <i class="ri-arrow-left-line text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight leading-none"><?= htmlspecialchars($exam['mapel_nama']) ?></h1>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-sm border border-white/10 uppercase">
                            Kelas <?= htmlspecialchars($exam['tingkat']) ?>-<?= htmlspecialchars($exam['abjad']) ?>
                        </span>
                        <span class="inline-flex items-center text-xs font-medium text-indigo-100 gap-1.5 px-2.5 py-0.5 rounded-full bg-white/10">
                            <i class="ri-user-star-line"></i>
                            <?= htmlspecialchars($exam['pengajar_nama'] ?? 'Unknown') ?>
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/50 text-white border border-white/10 uppercase tracking-widest">
                            <?= htmlspecialchars($exam['exam_type'] ?? '-') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <?php if ($isFinished): ?>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-xs font-black bg-white text-green-600 shadow-lg border border-green-100 uppercase tracking-tighter animate-pulse">
                        <i class="ri-checkbox-circle-fill mr-1.5 text-lg"></i> TERVERIFIKASI
                    </span>
                <?php elseif (!$isFinished && $sessionOpen): ?>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-xs font-black bg-white text-indigo-600 shadow-lg border border-indigo-100 uppercase tracking-tighter">
                        <i class="ri-edit-circle-fill mr-1.5 text-lg"></i> MODE INPUT AKTIF
                    </span>
                <?php elseif (!$isFinished && !$sessionOpen): ?>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-xs font-black bg-white text-red-600 shadow-lg border border-red-100 uppercase tracking-tighter">
                        <i class="ri-lock-2-fill mr-1.5 text-lg"></i> SESI TERKUNCI
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dashboard Bar -->
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100 bg-gray-50/50">
            <!-- Jenis Koreksi Select -->
            <div class="p-5 flex flex-col justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Koreksi</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Tipe kalkulasi ujian</p>
                </div>
                <div class="relative">
                    <select name="has_oral" id="has_oral_select" 
                        <?= !$canEditSkorMaks ? 'disabled' : '' ?>
                        onchange="updateConfig()"
                        class="w-full font-black text-sm text-indigo-600 bg-white border border-gray-200 focus:border-indigo-500 rounded-xl p-1.5 shadow-inner transition-all <?= !$canEditSkorMaks ? 'opacity-50 cursor-not-allowed' : 'hover:border-indigo-200' ?>">
                        <option value="0" <?= $exam['has_oral'] == 0 ? 'selected' : '' ?>>Tulis</option>
                        <option value="1" <?= $exam['has_oral'] == 1 ? 'selected' : '' ?>>Tulis & Lisan</option>
                        <option value="2" <?= $exam['has_oral'] == 2 ? 'selected' : '' ?>>Lisan</option>
                    </select>
                </div>
            </div>

            <!-- Skor Maks Input -->
            <div id="skor_maks_container" class="p-5 flex flex-col justify-between gap-3 <?= $exam['has_oral'] == 2 ? 'hidden' : '' ?>" style="<?= $exam['has_oral'] == 2 ? 'display: none;' : '' ?>">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Skor Maks (Soal)</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Total bobot poin soal</p>
                </div>
                <div class="relative">
                    <input type="number" name="skor_maks" id="skor_maks_input" value="<?= (float)$skor_maks ?>" 
                        <?= !$canEditSkorMaks ? 'disabled' : '' ?>
                        oninput="updateConfig()"
                        class="w-full text-right pr-3 pl-3 font-black text-xl text-indigo-600 bg-white border border-gray-200 focus:border-indigo-500 rounded-xl shadow-inner transition-all <?= !$canEditSkorMaks ? 'opacity-50 cursor-not-allowed' : 'hover:border-indigo-200' ?>">
                </div>
            </div>

            <!-- Nilai Maks Info -->
            <div id="nilai_maks_container" class="p-5 flex flex-col justify-between gap-3 <?= $exam['has_oral'] == 2 ? 'hidden' : '' ?>" style="<?= $exam['has_oral'] == 2 ? 'display: none;' : '' ?>">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Target Nilai (Max)</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Skala tertinggi rapor</p>
                </div>
                <p class="text-3xl font-black text-gray-900 mt-1"><?= $max_val ?></p>
            </div>

            <!-- Nilai Min Info -->
            <div id="nilai_min_container" class="p-5 flex flex-col justify-between gap-3 <?= $exam['has_oral'] == 2 ? 'hidden' : '' ?>" style="<?= $exam['has_oral'] == 2 ? 'display: none;' : '' ?>">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Base Nilai (Min)</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Skala terendah rapor</p>
                </div>
                <p class="text-3xl font-black text-red-500 mt-1"><?= $min_val ?></p>
            </div>
        </div>
    </div>

    <?php if (!$isAdmin && !$isPanitia && !$sessionOpen && !$isFinished): ?>
        <div class="bg-red-50 border border-red-100 p-4 mb-6 rounded-2xl flex items-center gap-4 animate-in fade-in slide-in-from-top-2 duration-500">
            <div class="bg-red-500 text-white rounded-xl p-3 shadow-md shadow-red-200">
                <i class="ri-error-warning-fill text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-red-900 font-black uppercase tracking-tight">Akses Terbatas: Sesi Ditutup</p>
                <p class="text-xs text-red-600/80 font-medium">Panitia telah menonaktifkan input nilai. Hubungi bagian kurikulum jika butuh perbaikan.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Student List Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden mb-12">
        <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Daftar Nilai Santri</h3>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">Rata-rata Kelas</p>
                    <?php 
                        $totalNilai = 0;
                        $countNilai = 0;
                        foreach ($students as $row) {
                            if (is_numeric($row['nilai'])) {
                                $totalNilai += $row['nilai'];
                                $countNilai++;
                            }
                        }
                    ?>
                    <p id="rataRataDisplay" class="text-lg font-black text-indigo-600 leading-none">
                        <?= $countNilai > 0 ? round($totalNilai / $countNilai, 2) : 0 ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto overflow-y-visible">
            <table class="min-w-full divide-y divide-gray-100 table-fixed md:table-auto">
                <thead class="hidden md:table-header-group">
                    <tr class="bg-white">
                        <?php if ($showNames): ?>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition-colors col-nama" onclick="sortTable(this)">
                                <div class="flex items-center gap-1">
                                    Nama Lengkap
                                    <i class="ri-sort-asc"></i>
                                </div>
                            </th>
                        <?php endif; ?>

                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/30 w-24 cursor-pointer hover:text-indigo-600 transition-colors col-bayanat <?= $exam['has_oral'] == 2 ? 'hidden' : '' ?>" onclick="sortTable(this, true)" style="<?= $exam['has_oral'] == 2 ? 'display: none;' : '' ?>">
                            <div class="flex items-center gap-1">
                                No. Bayanat
                                <i class="ri-sort-asc"></i>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-32 cursor-pointer hover:text-indigo-600 transition-colors col-lisan <?= ($exam['has_oral'] == 0 || !$canEditOralScore) ? 'hidden' : '' ?>" onclick="sortTable(this, true)" style="<?= ($exam['has_oral'] == 0 || !$canEditOralScore) ? 'display: none;' : '' ?>">
                            <div class="flex items-center justify-center gap-1">
                                <span id="header_lisan_label"><?= $exam['has_oral'] == 2 ? 'Nilai Lisan' : 'Nilai Lisan' ?></span>
                                <i class="ri-sort-asc"></i>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-40 col-tulis <?= $exam['has_oral'] == 2 ? 'hidden' : '' ?>" onclick="sortTable(this, true)" style="<?= $exam['has_oral'] == 2 ? 'display: none;' : '' ?>">Skor Tulis</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-24 cursor-pointer hover:text-indigo-600 transition-colors col-nilai <?= $exam['has_oral'] == 2 ? 'hidden' : '' ?>" onclick="sortTable(this, true)" style="<?= $exam['has_oral'] == 2 ? 'display: none;' : '' ?>">
                            <div class="flex items-center justify-center gap-1">
                                <span id="header_nilai_label"><?= $exam['has_oral'] == 2 ? 'Nilai Lisan' : 'Nilai Tulis' ?></span>
                                <i class="ri-sort-asc"></i>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody id="studentTableBody" class="divide-y divide-gray-50 block md:table-row-group">
                    <?php 
                    $canEditOral = $canEditOralScore && !$isFinished;
                    foreach ($students as $i => $row): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group flex flex-col md:table-row border-b md:border-b-0 border-gray-50 last:border-0 p-4 md:p-0">
                            <!-- Name (Auditors Only) -->
                            <?php if ($showNames): ?>
                            <td class="md:px-6 md:py-5 col-nama">
                                <div class="text-sm font-bold text-gray-800 tracking-tight leading-tight uppercase md:truncate md:max-w-xs lg:max-w-md">
                                    <?= htmlspecialchars($row['nama']) ?>
                                    <div class="text-[9px] text-gray-400 font-medium tracking-normal lowercase"><?= $row['nis'] ?></div>
                                </div>
                            </td>
                            <?php endif; ?>

                            <!-- No Bayanat -->
                            <td class="md:px-6 md:py-5 col-bayanat <?= $exam['has_oral'] == 2 ? 'hidden' : '' ?>" style="<?= $exam['has_oral'] == 2 ? 'display: none;' : '' ?>">
                                <div class="flex flex-col">
                                    <span class="block md:hidden text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">No. Bayanat</span>
                                    <input type="hidden" name="student_id[]" value="<?= $row['student_id'] ?>">
                                    <?php if ($isAdminOrPanitia): ?>
                                        <input type="number" name="no_bayanat[]" value="<?= $row['no_bayanat'] ?>" 
                                            placeholder="Set #"
                                            min="1"
                                            oninput="if(this.value < 1) this.value = ''; checkDuplicateBayanat();"
                                            <?= $isFinished ? 'disabled' : '' ?>
                                            class="bayanat-input w-full md:w-16 h-12 md:h-9 text-center font-black text-indigo-600 bg-gray-50 border-2 md:border border-gray-100 md:border-gray-200 rounded-2xl md:rounded-lg focus:border-indigo-500 focus:ring-0 transition-all shadow-sm md:shadow-none">
                                    <?php else: ?>
                                        <input type="hidden" name="no_bayanat[]" value="<?= $row['no_bayanat'] ?>">
                                        <div class="w-full md:w-16 h-12 md:h-9 bg-gray-50 md:bg-gray-100 rounded-2xl md:rounded-lg border-2 md:border border-gray-100 md:border-gray-200 flex items-center justify-center">
                                            <span class="text-lg md:text-sm font-black text-gray-900 text-center">
                                                <?= $row['no_bayanat'] ?: '??' ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Oral Exam Column -->
                            <td class="md:px-6 md:py-5 col-lisan <?= ($exam['has_oral'] == 0 || !$canEditOralScore) ? 'hidden' : 'md:table-cell' ?>" style="<?= ($exam['has_oral'] == 0 || !$canEditOralScore) ? 'display: none;' : '' ?>">
                                <div class="grid gap-3 md:flex md:items-center w-full grid-cols-1" id="container_lisan_<?= $i ?>">
                                    <div class="flex flex-col w-full">
                                        <label class="block md:hidden text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1 label-lisan-mobile">Nilai Lisan</label>
                                        <input type="text" name="skor_lisan[]" value="<?= $canEditOralScore ? htmlspecialchars($row['score_oral'] ?? '') : '' ?>"
                                            <?= !$canEditOral ? 'disabled' : '' ?>
                                            autocomplete="off"
                                            oninput="this.value = this.value.replace(/[^0-9.\-]/g, ''); if(parseFloat(this.value) < 0) this.value = '0'; calculateRow(this.closest('tr'));"
                                            class="w-full h-12 md:h-11 bg-white border-2 border-gray-100 rounded-2xl px-4 text-center font-black text-indigo-600 focus:border-indigo-500 focus:ring-0 transition-all shadow-sm hover:border-gray-200 disabled:bg-gray-50/50 disabled:text-gray-400 disabled:border-transparent"
                                            placeholder="...">
                                    </div>
                                    
                                    <!-- Mobile Final Score Output for Lisan Only Mode -->
                                    <div class="flex flex-col md:hidden w-full col-nilai-lisan-mobile hidden" style="display: none;">
                                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1 label-nilai-mobile">Nilai Lisan</label>
                                        <div class="w-full h-12 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-center">
                                            <input type="text" readonly tabindex="-1" value="<?= is_numeric($row['nilai']) ? round($row['nilai']) : '' ?>"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, ''); const row = this.closest('tr'); row.querySelector('input[name=\'nilai[]\']').value = this.value; const dsk = row.querySelector('.nilai-output'); if (dsk) dsk.value = this.value; updateAverage();"
                                                class="nilai-output-mobile bg-transparent text-indigo-600 font-black text-xl w-full text-center p-0 border-none pointer-events-none">
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Input & Result Container -->
                            <td class="md:px-6 md:py-5 col-tulis <?= $exam['has_oral'] == 2 ? 'hidden' : 'md:table-cell' ?>" style="<?= $exam['has_oral'] == 2 ? 'display: none;' : '' ?>">
                                <div class="grid grid-cols-2 gap-3 md:flex md:items-center">
                                    <!-- Input Skor -->
                                    <div class="flex flex-col">
                                        <label class="block md:hidden text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Skor Tulis</label>
                                        <input type="text" name="skor[]" value="<?= is_numeric($row['skor']) ? (float)$row['skor'] : $row['skor'] ?>"
                                            <?= !$canEditScores ? 'disabled' : '' ?>
                                            inputmode="decimal"
                                            autocomplete="off"
                                            oninput="this.value = this.value.replace(/[^0-9.\-]/g, ''); if(parseFloat(this.value) < 0) this.value = '0'; calculateRow(this.closest('tr'));"
                                            class="w-full h-12 md:h-11 bg-white border-2 border-gray-100 rounded-2xl px-4 text-center font-black text-gray-900 focus:border-indigo-500 focus:ring-0 transition-all shadow-sm hover:border-gray-200 disabled:bg-100/50 disabled:text-gray-400 disabled:border-transparent"
                                            placeholder="<?= !$canEditScores ? '-' : '...' ?>">
                                    </div>
                                    
                                    <!-- Nilai Result -->
                                    <div class="flex flex-col md:hidden">
                                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1 label-nilai-tulis-mobile">Nilai Tulis</label>
                                        <div class="w-full h-12 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-center">
                                            <input type="text" readonly tabindex="-1" value="<?= is_numeric($row['nilai']) ? round($row['nilai']) : '' ?>"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, ''); const row = this.closest('tr'); row.querySelector('input[name=\'nilai[]\']').value = this.value; const dsk = row.querySelector('.nilai-output'); if (dsk) dsk.value = this.value; updateAverage();"
                                                class="nilai-output-mobile bg-transparent text-indigo-600 font-black text-xl w-full text-center p-0 border-none pointer-events-none">
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Desktop Nilai Column -->
                            <td class="px-6 py-5 hidden md:table-cell col-nilai <?= $exam['has_oral'] == 2 ? 'hidden' : '' ?>" style="<?= $exam['has_oral'] == 2 ? 'display: none;' : '' ?>">
                                <div class="flex flex-col items-center justify-center">
                                    <input type="hidden" name="nilai[]" value="<?= is_numeric($row['nilai']) ? round($row['nilai']) : '' ?>">
                                    <input type="text" readonly tabindex="-1" value="<?= is_numeric($row['nilai']) ? round($row['nilai']) : '' ?>"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, ''); const row = this.closest('tr'); row.querySelector('input[name=\'nilai[]\']').value = this.value; const mob = row.querySelectorAll('.nilai-output-mobile'); if (mob.length > 0) mob.forEach(m => m.value = this.value); updateAverage();"
                                        class="nilai-output bg-transparent text-gray-900 font-black text-xl w-full text-center p-0 border-none pointer-events-none transition-all group-hover:scale-110 group-hover:text-indigo-600">
                                    <div class="h-1 w-8 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-indigo-400 transition-all" style="width: <?= min(100, (is_numeric($row['nilai']) ? round($row['nilai']) : 0)) ?>%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><b>-</b> = Absen</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-400"></span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><b>0</b> = Salah Semua</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Toolbar -->
    <?php if (!$isFinished): ?>
    <div class="fixed bottom-0 inset-x-0 pb-8 px-6 pointer-events-none z-50">
        <div class="max-w-4xl mx-auto pointer-events-auto flex items-center justify-between p-4 bg-white/80 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl ring-1 ring-black/5">
            <div class="hidden md:flex flex-col ml-4">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                    Role: <?= ($isAdminOrPanitia && $isExaminer) ? 'Panitia & Pemeriksa' : ($isAdminOrPanitia ? 'Panitia Ujian' : ($isExaminer ? 'Pemeriksa' : 'Viewer')) ?>
                </p>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full <?= $sessionOpen ? 'bg-green-500 animate-pulse' : 'bg-red-500' ?>"></span>
                    <p class="text-xs font-bold text-gray-600"><?= $sessionOpen ? 'Sesi Terbuka' : 'Sesi Terkunci' ?></p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:flex md:items-center gap-2 md:gap-3 w-full md:w-auto">
                <?php if ($isAdminOrPanitia): ?>
                    <div id="duplicateWarning" class="hidden flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-xl text-[10px] font-bold uppercase tracking-widest border border-red-100 animate-pulse">
                        <i class="ri-error-warning-fill text-lg"></i>
                        Ada Nomor Bayanat Ganda!
                    </div>
                    <button type="submit" id="saveConfigBtn" name="action" value="save" class="col-span-2 md:col-span-auto w-full md:w-auto h-12 md:h-14 px-4 md:px-10 rounded-xl md:rounded-2xl bg-indigo-600 text-white font-black text-xs md:text-sm uppercase tracking-widest shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="ri-settings-3-line text-lg"></i>
                        Update Konfigurasi & Bayanat
                    </button>
                <?php endif; ?>

                <?php 
                $canSaveAndFinish = $isAdminOrPanitia || ($isExaminer && $sessionOpen);
                if ($canSaveAndFinish): 
                ?>
                    <button type="submit" id="saveDraftBtn" name="action" value="save" onclick="return confirm('Simpan hasil koreksi sebagai draft?');" class="col-span-1 md:col-span-auto flex-1 md:flex-none w-full md:w-auto h-12 md:h-14 px-4 md:px-8 rounded-xl md:rounded-2xl bg-white border-2 border-gray-100 text-gray-600 font-black text-xs md:text-sm uppercase tracking-widest hover:bg-gray-50 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="ri-save-3-line text-lg"></i>
                        Simpan Draft
                    </button>
                    <button type="submit" id="finishBtn" name="action" value="finish" onclick="return validateFinish()" class="col-span-1 md:col-span-auto flex-[2] md:flex-none w-full md:w-auto h-12 md:h-14 px-4 md:px-10 rounded-xl md:rounded-2xl bg-green-600 text-white font-black text-xs md:text-sm uppercase tracking-widest shadow-xl shadow-green-200 hover:bg-green-700 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="ri-checkbox-circle-line text-lg"></i>
                        Selesai Diperiksa
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</form>
</main>

<script>
    const isAdminOrPanitia = <?= $isAdminOrPanitia ? 'true' : 'false' ?>;
    const isExaminer = <?= $isExaminer ? 'true' : 'false' ?>;

    function validateFinish() {
        const skorMaks = parseFloat(document.getElementById('skor_maks_input').value) || 100;
        const hasOralVal = parseInt(document.getElementById('has_oral_select')?.value ?? '<?= $exam['has_oral'] ?>');
        
        if (hasOralVal === 0 || hasOralVal === 1) {
            const inputs = document.querySelectorAll('input[name="skor[]"]');
            for (let input of inputs) {
                const val = input.value.trim();
                if (val === '') {
                    alert('Gagal: Semua kolom skor tulis harus diisi sebelum menandai selesai.');
                    input.focus();
                    return false;
                }
                if (val !== '-' && parseFloat(val) > skorMaks) {
                    alert('Gagal: Ada skor tulis yang melebihi skor maksimal (' + skorMaks + '). Silakan periksa kembali.');
                    input.focus();
                    return false;
                }
            }
        }
        
        if ((hasOralVal === 1 || hasOralVal === 2) && isAdminOrPanitia) {
            const inputsLisan = document.querySelectorAll('input[name="skor_lisan[]"]');
            for (let input of inputsLisan) {
                const val = input.value.trim();
                if (val === '') {
                    alert('Gagal: Semua kolom nilai lisan harus diisi sebelum menandai selesai.');
                    input.focus();
                    return false;
                }
                // Tidak ada batasan nilai min/maks untuk nilai lisan — disimpan apa adanya.
            }
        }
        return confirm('Apakah Anda yakin ingin menyelesaikan pemeriksaan ini? Status akan menjadi Selesai dan tidak dapat diubah lagi.');
    }

    // Robust real-time calculation logic
    window.updateConfig = function() {
        const hasOralSelect = document.getElementById('has_oral_select');
        const hasOralVal = parseInt(hasOralSelect ? hasOralSelect.value : '<?= $exam['has_oral'] ?>');

        // Dynamically toggle config panels
        const skorMaksCont = document.getElementById('skor_maks_container');
        const nilaiMaksCont = document.getElementById('nilai_maks_container');
        const nilaiMinCont = document.getElementById('nilai_min_container');
        if (hasOralVal === 2) {
            if (skorMaksCont) { skorMaksCont.style.display = 'none'; skorMaksCont.classList.add('hidden'); }
            if (nilaiMaksCont) { nilaiMaksCont.style.display = 'none'; nilaiMaksCont.classList.add('hidden'); }
            if (nilaiMinCont) { nilaiMinCont.style.display = 'none'; nilaiMinCont.classList.add('hidden'); }
        } else {
            if (skorMaksCont) { skorMaksCont.style.display = ''; skorMaksCont.classList.remove('hidden'); }
            if (nilaiMaksCont) { nilaiMaksCont.style.display = ''; nilaiMaksCont.classList.remove('hidden'); }
            if (nilaiMinCont) { nilaiMinCont.style.display = ''; nilaiMinCont.classList.remove('hidden'); }
        }

        // Dynamically toggle column visibility on frontend
        const colsTulis = document.querySelectorAll('.col-tulis');
        const colsLisan = document.querySelectorAll('.col-lisan');
        const colsNilai = document.querySelectorAll('.col-nilai');
        const colsBayanat = document.querySelectorAll('.col-bayanat');
        
        const headerNilaiLabel = document.getElementById('header_nilai_label');
        const headerLisanLabel = document.getElementById('header_lisan_label');

        const labelLisanMobileList = document.querySelectorAll('.label-lisan-mobile');
        const colNilaiLisanMobileList = document.querySelectorAll('.col-nilai-lisan-mobile');

        // Set header text
        if (headerNilaiLabel) headerNilaiLabel.textContent = (hasOralVal === 2) ? 'Nilai Lisan' : 'Nilai Tulis';
        if (headerLisanLabel) headerLisanLabel.textContent = 'Nilai Lisan';
        labelLisanMobileList.forEach(el => el.textContent = 'Nilai Lisan');
        colNilaiLisanMobileList.forEach(el => { el.style.display = 'none'; el.classList.add('hidden'); });

        const showLisan = (hasOralVal === 1 || hasOralVal === 2) && isAdminOrPanitia;
        const showTulis = (hasOralVal === 0 || hasOralVal === 1);
        const showNilai = (hasOralVal === 0 || hasOralVal === 1);
        const showBayanat = (hasOralVal === 0 || hasOralVal === 1);

        if (showLisan) {
            colsLisan.forEach(el => { el.style.display = ''; el.classList.remove('hidden'); });
        } else {
            colsLisan.forEach(el => { el.style.display = 'none'; el.classList.add('hidden'); });
        }

        if (showTulis) {
            colsTulis.forEach(el => { el.style.display = ''; el.classList.remove('hidden'); });
        } else {
            colsTulis.forEach(el => { el.style.display = 'none'; el.classList.add('hidden'); });
        }

        if (showNilai) {
            colsNilai.forEach(el => { el.style.display = ''; el.classList.remove('hidden'); });
        } else {
            colsNilai.forEach(el => { el.style.display = 'none'; el.classList.add('hidden'); });
        }

        if (showBayanat) {
            colsBayanat.forEach(el => { el.style.display = ''; el.classList.remove('hidden'); });
        } else {
            colsBayanat.forEach(el => { el.style.display = 'none'; el.classList.add('hidden'); });
        }

        // Set parents to grid-cols-1
        document.querySelectorAll('[id^="container_lisan_"]').forEach(el => {
            el.classList.remove('grid-cols-2');
            el.classList.add('grid-cols-1');
        });

        // Recalculate all rows
        document.querySelectorAll('#studentTableBody tr').forEach(row => {
            window.calculateRow(row);
        });
        window.updateAverage();

        // Dynamically toggle buttons based on selected category and user role
        const saveConfigBtn = document.getElementById('saveConfigBtn');
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        const finishBtn = document.getElementById('finishBtn');

        if (isAdminOrPanitia) {
            if (hasOralVal === 2) {
                // Lisan: show draft/finish, hide config
                if (saveConfigBtn) { saveConfigBtn.style.display = 'none'; saveConfigBtn.classList.add('hidden'); }
                if (saveDraftBtn) { saveDraftBtn.style.display = ''; saveDraftBtn.classList.remove('hidden'); }
                if (finishBtn) { finishBtn.style.display = ''; finishBtn.classList.remove('hidden'); }
            } else {
                // Tulis / Tulis & Lisan: show config, hide draft/finish unless the user is also the examiner
                if (saveConfigBtn) { saveConfigBtn.style.display = ''; saveConfigBtn.classList.remove('hidden'); }
                if (isExaminer) {
                    if (saveDraftBtn) { saveDraftBtn.style.display = ''; saveDraftBtn.classList.remove('hidden'); }
                    if (finishBtn) { finishBtn.style.display = ''; finishBtn.classList.remove('hidden'); }
                } else {
                    if (saveDraftBtn) { saveDraftBtn.style.display = 'none'; saveDraftBtn.classList.add('hidden'); }
                    if (finishBtn) { finishBtn.style.display = 'none'; finishBtn.classList.add('hidden'); }
                }
            }
        }
    };

    window.calculateRow = function(row) {
        const inputTulis = row.querySelector('input[name="skor[]"]');
        const inputLisan = row.querySelector('input[name="skor_lisan[]"]');
        const outputs = row.querySelectorAll('.nilai-output, .nilai-output-mobile');
        const hiddenOutput = row.querySelector('input[name="nilai[]"]');
        const progressBar = row.querySelector('.h-full.bg-indigo-400');
        
        const nilaiMaks = parseFloat(document.getElementById('nilai_maks').value) || 100;
        const nilaiMin = parseFloat(document.getElementById('nilai_min').value) || 0;
        const skorMaks = parseFloat(document.getElementById('skor_maks_input').value) || 100;
        const hasOralVal = parseInt(document.getElementById('has_oral_select')?.value ?? '<?= $exam['has_oral'] ?>');

        let valTulis = inputTulis ? inputTulis.value.trim() : '';
        
        // ---------------------------------------------------------------
        // Nilai Tulis = konversi skor tulis ke skala rapor.
        // Formula: round((skor / skorMaks) * (nilaiMaks - nilaiMin) + nilaiMin)
        // Nilai Lisan disimpan apa adanya; TIDAK digabungkan di sini.
        // Penggabungan untuk nilai akhir rapor dilakukan di modul rapor/leger.
        // ---------------------------------------------------------------

        let finalNilai = null; // ini adalah "Nilai Tulis" (score_final)

        if (hasOralVal === 0 || hasOralVal === 1) {
            // Mode Tulis Saja atau Tulis & Lisan: hitung nilai tulis dari skor tulis
            if (valTulis === '-') {
                finalNilai = 0; // Absen
            } else if (valTulis === '0' || valTulis === 0 || valTulis === '0.0') {
                finalNilai = nilaiMin; // Salah semua → nilai minimum
            } else if (valTulis !== '') {
                let skor = parseFloat(valTulis.replace(',', '.'));
                if (!isNaN(skor)) {
                    if (skor < 0) skor = 0;
                    // Validasi: skor tidak boleh melebihi skor maks
                    if (inputTulis) {
                        if (skor > skorMaks) {
                            inputTulis.classList.add('border-red-500', 'bg-red-50', 'text-red-600', 'ring-1', 'ring-red-500');
                        } else {
                            inputTulis.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'ring-red-500');
                        }
                    }
                    // Formula: (skor / skor_maks) × (nilai_maks - nilai_min) + nilai_min
                    finalNilai = Math.round((skor / skorMaks) * (nilaiMaks - nilaiMin) + nilaiMin);
                    if (finalNilai < nilaiMin) finalNilai = nilaiMin;
                    if (finalNilai > nilaiMaks) finalNilai = nilaiMaks;
                }
            }
        } else if (hasOralVal === 2) {
            // Mode Lisan Saja: tidak ada nilai tulis (score_final = null).
            // Nilai lisan hanya ditampilkan di kolom lisan, tidak dikonversi di sini.
            finalNilai = null;
        }

        // Update outputs (kolom "Nilai Tulis")
        if (finalNilai !== null) {
            outputs.forEach(o => {
                o.value = Math.round(finalNilai);
            });
            if (hiddenOutput) hiddenOutput.value = Math.round(finalNilai);
            if (progressBar) progressBar.style.width = (((finalNilai - nilaiMin) / (nilaiMaks - nilaiMin)) * 100) + '%';
        } else {
            outputs.forEach(o => o.value = '');
            if (hiddenOutput) hiddenOutput.value = '';
            if (progressBar) progressBar.style.width = '0%';
        }

        // Hapus warning lama jika ada (sisa dari logika lama)
        const warnSpan = row.querySelector('.row-warning');
        if (warnSpan) warnSpan.remove();
    };

    window.updateAverage = function() {
        const outputs = document.querySelectorAll('.nilai-output');
        let total = 0;
        let count = 0;

        outputs.forEach(el => {
            let valStr = el.value.trim();
            if (valStr !== '') {
                let val = parseFloat(valStr);
                if (!isNaN(val)) {
                    total += val;
                    count++;
                }
            }
        });

        const avg = count > 0 ? (total / count).toFixed(2) : 0;
        const display = document.getElementById('rataRataDisplay');
        if (display) display.textContent = avg;
    };

    window.checkDuplicateBayanat = function() {
        const inputs = document.querySelectorAll('.bayanat-input');
        const warning = document.getElementById('duplicateWarning');
        const saveBtn = document.getElementById('saveConfigBtn');
        const values = [];
        let hasDuplicate = false;

        // Reset states
        inputs.forEach(input => {
            input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
            input.classList.add('border-gray-200', 'bg-gray-50', 'text-indigo-600');
        });

        inputs.forEach(input => {
            const val = input.value.trim();
            if (val !== '') {
                if (values.includes(val)) {
                    hasDuplicate = true;
                    // Highlight all inputs with this duplicate value
                    inputs.forEach(i => {
                        if (i.value.trim() === val) {
                            i.classList.remove('border-gray-200', 'bg-gray-50', 'text-indigo-600');
                            i.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
                        }
                    });
                }
                values.push(val);
            }
        });

        if (hasDuplicate) {
            warning.classList.remove('hidden');
            saveBtn.disabled = true;
            saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warning.classList.add('hidden');
            saveBtn.disabled = false;
            saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    };

    let sortDirections = {};

    window.sortTable = function(headerElement, isNumeric = false) {
        const tbody = document.getElementById('studentTableBody');
        if (!tbody) return;
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const ths = Array.from(headerElement.parentElement.children);
        const colIndex = ths.indexOf(headerElement);
        
        const direction = sortDirections[colIndex] === 'asc' ? 'desc' : 'asc';
        sortDirections[colIndex] = direction;

        // Reset icons
        document.querySelectorAll('thead th i').forEach(icon => {
            icon.className = 'ri-sort-asc';
            icon.parentElement.parentElement.classList.remove('text-indigo-600');
        });

        // Set active icon
        const activeIcon = headerElement.querySelector('i');
        if (activeIcon) activeIcon.className = direction === 'asc' ? 'ri-sort-asc' : 'ri-sort-desc';
        headerElement.classList.add('text-indigo-600');

        rows.sort((a, b) => {
            let aVal, bVal;

            if (headerElement.classList.contains('col-bayanat')) {
                const aInput = a.querySelector('input[name="no_bayanat[]"]');
                aVal = aInput ? aInput.value : (a.querySelector('span.text-lg') ? a.querySelector('span.text-lg').textContent.trim() : '');
                const bInput = b.querySelector('input[name="no_bayanat[]"]');
                bVal = bInput ? bInput.value : (b.querySelector('span.text-lg') ? b.querySelector('span.text-lg').textContent.trim() : '');
            } else if (headerElement.classList.contains('col-nama')) {
                const aName = a.querySelector('.text-sm.font-bold');
                const bName = b.querySelector('.text-sm.font-bold');
                aVal = aName ? aName.textContent.trim() : '';
                bVal = bName ? bName.textContent.trim() : '';
            } else if (headerElement.classList.contains('col-lisan')) {
                const aOut = a.querySelector('input[name="skor_lisan[]"]');
                const bOut = b.querySelector('input[name="skor_lisan[]"]');
                aVal = aOut ? aOut.value : '0';
                bVal = bOut ? bOut.value : '0';
            } else if (headerElement.classList.contains('col-tulis')) {
                const aOut = a.querySelector('input[name="skor[]"]');
                const bOut = b.querySelector('input[name="skor[]"]');
                aVal = aOut ? aOut.value : '0';
                bVal = bOut ? bOut.value : '0';
            } else {
                const aOut = a.querySelector('.nilai-output') || a.querySelector('.nilai-output-mobile');
                const bOut = b.querySelector('.nilai-output') || b.querySelector('.nilai-output-mobile');
                aVal = aOut ? aOut.value : '0';
                bVal = bOut ? bOut.value : '0';
            }

            if (isNumeric) {
                aVal = parseFloat(aVal) || 0;
                bVal = parseFloat(bVal) || 0;
                return direction === 'asc' ? aVal - bVal : bVal - aVal;
            } else {
                return direction === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            }
        });

        rows.forEach(row => tbody.appendChild(row));
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.updateConfig();
    });
</script>

<?php renderFooter(); ?>
