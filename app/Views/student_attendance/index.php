<?php renderHeader("Absensi Santri"); 
$role = auth_get_role();
$userId = auth_get_user_id();

// Determine editing permissions
$canEdit = false;
if ($activeSession && (bool)$activeSession['is_open']) {
    $canEdit = auth_can_manage_attendance($activeSession['id']);
}
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate flex items-center gap-3">
                Absensi Santri
                <?php if (isset($currentYear)): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 border border-indigo-200">
                        <i class="ri-calendar-line mr-1"></i> TA: <?= htmlspecialchars($currentYear['name'] ?? '-') ?>
                    </span>
                <?php endif; ?>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Pencatatan ketidakhadiran santri (Alpha, Izin, Sakit).
            </p>
        </div>
        
        <div class="flex items-center gap-1.5 md:gap-2 flex-wrap md:flex-nowrap">
            <?php if ($activeSession): ?>
                <span class="inline-flex items-center px-2.5 py-1.5 md:px-3 md:py-1 rounded-full text-[10px] md:text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 whitespace-nowrap shadow-sm">
                    Sesi: Semester <?= htmlspecialchars($activeSession['semester']) ?>
                </span>
                <span class="inline-flex items-center px-2.5 py-1.5 md:px-3 md:py-1 rounded-full text-[10px] md:text-xs font-bold <?= $activeSession['is_open'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> border border-current whitespace-nowrap shadow-sm">
                    <i class="ri-door-<?= $activeSession['is_open'] ? 'open' : 'closed' ?>-line mr-1 text-[11px] md:text-xs"></i>
                    Input: <?= $activeSession['is_open'] ? 'DIBUKA' : 'DITUTUP' ?>
                </span>
            <?php else: ?>
                <span class="inline-flex items-center px-2.5 py-1.5 md:px-3 md:py-1 rounded-full text-[10px] md:text-xs font-bold bg-red-100 text-red-700 border border-red-200 whitespace-nowrap shadow-sm">
                    <i class="ri-error-warning-line mr-1"></i> Belum ada Sesi Semester Aktif
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$activeSession): ?>
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-alert-line text-amber-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-amber-800">Sesi Semester Aktif Belum Ditentukan</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        Absensi santri tidak dapat diinput karena belum ada sesi semester yang diaktifkan oleh Admin KMI. Hubungi bagian Pengajaran KMI / Admin untuk mengaktifkan sesi semester di menu <strong>Petugas Absensi</strong>.
                    </p>
                    <?php if ($role === 'admin'): ?>
                        <div class="mt-3">
                            <a href="<?= url('/student-attendance/pbm') ?>" class="text-xs font-bold text-amber-900 bg-amber-100 hover:bg-amber-200 px-3 py-1.5 rounded-lg border border-amber-300 transition-colors inline-flex items-center gap-1">
                                <i class="ri-settings-4-line"></i> Buka Manajemen PBM
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Navigation (Daftar Kelas) -->
            <aside class="w-full lg:w-64 flex-shrink-0">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden sticky top-20 z-30">
                    <div class="hidden lg:block px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Daftar Kelas</h3>
                    </div>
                    <!-- Mobile Hint -->
                    <div class="lg:hidden flex items-center justify-between px-4 py-2 border-b border-gray-50 bg-gray-50/30">
                        <span class="text-[10px] font-bold text-indigo-500 flex items-center gap-1 uppercase tracking-wider">
                            <i class="ri-arrow-left-right-line"></i> Geser kelas ke samping
                        </span>
                    </div>
                    <nav class="p-2 flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-3 lg:pb-2 no-scrollbar snap-x">
                        <?php foreach ($kelas as $k): 
                            $isActive = $k['id'] == $activeKelasId;
                            $activeClass = $isActive 
                                ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-sm ring-1 ring-indigo-100' 
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
                            
                            $sidebarUrl = url('/student-attendance?kelas=' . $k['id'] . '&date=' . urlencode($selectedDate) . '&tab=' . urlencode($tab));
                        ?>
                            <a href="<?= $sidebarUrl ?>" 
                               id="class-nav-item-<?= $k['id'] ?>"
                               class="whitespace-nowrap flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl transition-all duration-200 group <?= $activeClass ?> snap-start min-w-[45%] lg:min-w-0">
                                <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-xs <?= $isActive ? 'bg-indigo-600 text-white shadow-indigo-200 shadow-lg' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200' ?>">
                                    <i class="ri-community-line"></i>
                                </div>
                                <span><?= htmlspecialchars($k['tingkat']) ?> - <?= htmlspecialchars($k['abjad']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1">
                <?php
                // Find active class name
                $activeKelasName = '';
                foreach ($kelas as $k) {
                    if ($k['id'] == $activeKelasId) {
                        $activeKelasName = $k['tingkat'] . '-' . $k['abjad'];
                        break;
                    }
                }
                ?>

                <!-- Class Title Card -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Absensi Kelas</span>
                            <h3 class="text-xl font-bold text-gray-900">Kelas <?= htmlspecialchars($activeKelasName) ?></h3>
                        </div>

                        <!-- Date Selector / Toggle Tab -->
                        <div class="flex items-center gap-3 self-start sm:self-center">
                            <div class="flex bg-gray-100 p-0.5 rounded-lg border border-gray-200">
                                <a href="<?= url('/student-attendance?kelas=' . $activeKelasId . '&date=' . urlencode($selectedDate) . '&tab=input') ?>" 
                                   class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all <?= $tab === 'input' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' ?>">
                                    Input Harian
                                </a>
                                <a href="<?= url('/student-attendance?kelas=' . $activeKelasId . '&date=' . urlencode($selectedDate) . '&tab=rekap') ?>" 
                                   class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all <?= $tab === 'rekap' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' ?>">
                                    Rekap Semester
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Harian Tab -->
                <?php if ($tab === 'input'): ?>
                    <form action="<?= url('/student-attendance/store') ?>" method="POST" id="attendance-form" onsubmit="return handleFormSubmit(event)">
                        <?= csrf_token_field() ?>
                        <input type="hidden" name="session_id" value="<?= htmlspecialchars($activeSession['id']) ?>">
                        <input type="hidden" name="kelas_id" value="<?= htmlspecialchars($activeKelasId) ?>">
                        <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>">

                        <!-- Pre-render Hidden inputs for student statuses -->
                        <?php foreach ($students as $student): ?>
                            <input type="hidden" name="absences[<?= $student['student_id'] ?>][status]" 
                                   id="status-<?= $student['student_id'] ?>" 
                                   data-name="<?= htmlspecialchars($student['nama']) ?>" 
                                   value="<?= htmlspecialchars($student['status'] ?? '') ?>">
                        <?php endforeach; ?>

                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                            <!-- Filter Date Bar -->
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <label for="date-picker" class="text-xs font-bold text-gray-500 uppercase tracking-wide">Tanggal Absensi:</label>
                                    <input type="text" id="date-picker" value="<?= htmlspecialchars($selectedDate) ?>" 
                                           class="bg-white border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 w-32" />
                                </div>
                                <div class="text-xs text-gray-400 italic font-medium">
                                    * Secara default seluruh santri dianggap Hadir. Hanya pilih santri yang berhalangan hadir.
                                </div>
                            </div>

                            <!-- Desktop Layout: Table of Students -->
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="w-12 px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">No</th>
                                            <th class="w-28 px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">NIS</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Nama Santri</th>
                                            <th class="w-96 px-6 py-3 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Status Ketidakhadiran</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php if (empty($students)): ?>
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400 italic">
                                                    Tidak ada data santri aktif di kelas ini.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($students as $idx => $student): 
                                                $studentId = $student['student_id'];
                                                $status = $student['status'] ?? '';
                                                
                                                $isSakit = ($status === 'sakit');
                                                $isIzin = ($status === 'izin');
                                                $isAlpha = ($status === 'alpha');

                                                $sakitClass = $isSakit ? 'bg-yellow-100 text-yellow-800 border-yellow-300 shadow-sm' : 'bg-gray-50 text-gray-400 hover:bg-gray-100 border-gray-200';
                                                $izinClass = $isIzin ? 'bg-blue-100 text-blue-800 border-blue-300 shadow-sm' : 'bg-gray-50 text-gray-400 hover:bg-gray-100 border-gray-200';
                                                $alphaClass = $isAlpha ? 'bg-red-100 text-red-800 border-red-300 shadow-sm' : 'bg-gray-50 text-gray-400 hover:bg-gray-100 border-gray-200';
                                            ?>
                                                <tr class="hover:bg-gray-50/50 transition-colors">
                                                    <td class="px-6 py-3 text-sm text-gray-500"><?= $idx + 1 ?></td>
                                                    <td class="px-6 py-3 text-sm font-mono text-gray-600"><?= htmlspecialchars($student['nis']) ?></td>
                                                    <td class="student-name px-6 py-3 text-sm font-bold text-gray-800"><?= htmlspecialchars($student['nama']) ?></td>
                                                    <td class="px-6 py-3">
                                                        <div class="flex justify-center items-center gap-3">
                                                            <button type="button" id="btn-sakit-<?= $studentId ?>" onclick="toggleStatus(<?= $studentId ?>, 'sakit')" 
                                                                    class="px-4 py-1.5 text-xs font-bold rounded-xl border <?= $sakitClass ?> transition-all cursor-pointer" <?= !$canEdit ? 'disabled' : '' ?>>
                                                                Sakit
                                                              </button>
                                                            <button type="button" id="btn-izin-<?= $studentId ?>" onclick="toggleStatus(<?= $studentId ?>, 'izin')" 
                                                                    class="px-4 py-1.5 text-xs font-bold rounded-xl border <?= $izinClass ?> transition-all cursor-pointer" <?= !$canEdit ? 'disabled' : '' ?>>
                                                                Izin
                                                              </button>
                                                            <button type="button" id="btn-alpha-<?= $studentId ?>" onclick="toggleStatus(<?= $studentId ?>, 'alpha')" 
                                                                    class="px-4 py-1.5 text-xs font-bold rounded-xl border <?= $alphaClass ?> transition-all cursor-pointer" <?= !$canEdit ? 'disabled' : '' ?>>
                                                                Alpha
                                                              </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Layout: Card Lists -->
                            <div class="block md:hidden divide-y divide-gray-100 px-4">
                                <?php if (empty($students)): ?>
                                    <div class="py-8 text-center text-sm text-gray-400 italic">
                                        Tidak ada data santri aktif di kelas ini.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($students as $idx => $student): 
                                        $studentId = $student['student_id'];
                                        $status = $student['status'] ?? '';
                                        
                                        $isSakit = ($status === 'sakit');
                                        $isIzin = ($status === 'izin');
                                        $isAlpha = ($status === 'alpha');

                                        $sakitClass = $isSakit ? 'bg-yellow-100 text-yellow-800 border-yellow-300 shadow-sm' : 'bg-gray-50 text-gray-400 hover:bg-gray-100 border-gray-200';
                                        $izinClass = $isIzin ? 'bg-blue-100 text-blue-800 border-blue-300 shadow-sm' : 'bg-gray-50 text-gray-400 hover:bg-gray-100 border-gray-200';
                                        $alphaClass = $isAlpha ? 'bg-red-100 text-red-800 border-red-300 shadow-sm' : 'bg-gray-50 text-gray-400 hover:bg-gray-100 border-gray-200';
                                    ?>
                                        <div class="py-4 flex flex-col gap-3">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="text-[10px] font-mono text-gray-400">#<?= $idx + 1 ?> | NIS: <?= htmlspecialchars($student['nis']) ?></div>
                                                    <h4 class="text-sm font-bold text-gray-800 mt-0.5"><?= htmlspecialchars($student['nama']) ?></h4>
                                                </div>
                                            </div>
                                            <!-- Status Pills -->
                                            <div class="flex gap-2">
                                                <button type="button" id="btn-sakit-mobile-<?= $studentId ?>" onclick="toggleStatus(<?= $studentId ?>, 'sakit')" 
                                                        class="flex-1 py-2 text-xs font-bold rounded-xl border <?= $sakitClass ?> text-center transition-all cursor-pointer" <?= !$canEdit ? 'disabled' : '' ?>>
                                                    Sakit
                                                </button>
                                                <button type="button" id="btn-izin-mobile-<?= $studentId ?>" onclick="toggleStatus(<?= $studentId ?>, 'izin')" 
                                                        class="flex-1 py-2 text-xs font-bold rounded-xl border <?= $izinClass ?> text-center transition-all cursor-pointer" <?= !$canEdit ? 'disabled' : '' ?>>
                                                    Izin
                                                </button>
                                                <button type="button" id="btn-alpha-mobile-<?= $studentId ?>" onclick="toggleStatus(<?= $studentId ?>, 'alpha')" 
                                                        class="flex-1 py-2 text-xs font-bold rounded-xl border <?= $alphaClass ?> text-center transition-all cursor-pointer" <?= !$canEdit ? 'disabled' : '' ?>>
                                                    Alpha
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action Bar -->
                        <?php if ($canEdit && !empty($students)): ?>
                            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 bg-white p-4 border border-gray-200 rounded-2xl shadow-sm">
                                <span class="text-xs text-gray-400 font-medium text-center sm:text-left">
                                    * Klik simpan absensi untuk merekap nama-nama santri yang berhalangan hadir.
                                </span>
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-100 active:scale-95 transition-all">
                                    <i class="ri-save-line mr-1.5"></i> Simpan Absensi
                                </button>
                            </div>
                        <?php elseif (!empty($students)): ?>
                            <div class="flex items-center justify-center gap-2 bg-gray-100 p-4 border border-gray-200 rounded-2xl">
                                <i class="ri-lock-line text-gray-400"></i>
                                <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider text-center">
                                    Sesi terkunci / Anda tidak memiliki akses edit untuk kelas & tanggal ini.
                                </span>
                            </div>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <!-- Rekap Semester Tab -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                                Rekap Ketidakhadiran Semester <?= htmlspecialchars($activeSession['semester']) ?>
                            </h3>
                        </div>

                        <!-- Desktop Rekap Table -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-12 px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">No</th>
                                        <th class="w-28 px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">NIS</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Nama Santri</th>
                                        <th class="w-24 px-4 py-3 text-center text-xs font-bold text-yellow-600 uppercase tracking-widest">Sakit</th>
                                        <th class="w-24 px-4 py-3 text-center text-xs font-bold text-blue-600 uppercase tracking-widest">Izin</th>
                                        <th class="w-24 px-4 py-3 text-center text-xs font-bold text-red-600 uppercase tracking-widest">Alpha</th>
                                        <th class="w-28 px-4 py-3 text-center text-xs font-bold text-indigo-600 uppercase tracking-widest">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($students)): ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400 italic">
                                                Tidak ada data santri aktif di kelas ini.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $idx => $student): 
                                            $studentId = $student['student_id'];
                                            $sakit = $summary[$studentId]['sakit'] ?? 0;
                                            $izin = $summary[$studentId]['izin'] ?? 0;
                                            $alpha = $summary[$studentId]['alpa'] ?? 0;
                                            $total = $sakit + $izin + $alpha;
                                        ?>
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-6 py-3 text-sm text-gray-500"><?= $idx + 1 ?></td>
                                                <td class="px-6 py-3 text-sm font-mono text-gray-600"><?= htmlspecialchars($student['nis']) ?></td>
                                                <td class="px-6 py-3 text-sm font-bold text-gray-800"><?= htmlspecialchars($student['nama']) ?></td>
                                                <td class="px-4 py-3 text-center text-sm font-semibold text-yellow-600 bg-yellow-50/30"><?= $sakit ?></td>
                                                <td class="px-4 py-3 text-center text-sm font-semibold text-blue-600 bg-blue-50/30"><?= $izin ?></td>
                                                <td class="px-4 py-3 text-center text-sm font-semibold text-red-600 bg-red-50/30"><?= $alpha ?></td>
                                                <td class="px-4 py-3 text-center text-sm font-bold text-indigo-700 bg-indigo-50/30"><?= $total ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Rekap Cards -->
                        <div class="block md:hidden divide-y divide-gray-100 px-4">
                            <?php if (empty($students)): ?>
                                <div class="py-8 text-center text-sm text-gray-400 italic">
                                    Tidak ada data santri aktif di kelas ini.
                                </div>
                            <?php else: ?>
                                <?php foreach ($students as $idx => $student): 
                                    $studentId = $student['student_id'];
                                    $sakit = $summary[$studentId]['sakit'] ?? 0;
                                    $izin = $summary[$studentId]['izin'] ?? 0;
                                    $alpha = $summary[$studentId]['alpa'] ?? 0;
                                    $total = $sakit + $izin + $alpha;
                                ?>
                                    <div class="py-4 flex flex-col gap-2">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="text-[10px] font-mono text-gray-400">#<?= $idx + 1 ?> | NIS: <?= htmlspecialchars($student['nis']) ?></div>
                                                <h4 class="text-sm font-bold text-gray-800 mt-0.5"><?= htmlspecialchars($student['nama']) ?></h4>
                                            </div>
                                        </div>
                                        <!-- Summary Badges -->
                                        <div class="grid grid-cols-4 gap-2 text-center mt-1">
                                            <div class="bg-yellow-50/50 border border-yellow-100 py-1 rounded-lg">
                                                <span class="block text-[9px] font-medium text-yellow-600">Sakit</span>
                                                <span class="text-xs font-bold text-yellow-700"><?= $sakit ?></span>
                                            </div>
                                            <div class="bg-blue-50/50 border border-blue-100 py-1 rounded-lg">
                                                <span class="block text-[9px] font-medium text-blue-600">Izin</span>
                                                <span class="text-xs font-bold text-blue-700"><?= $izin ?></span>
                                            </div>
                                            <div class="bg-red-50/50 border border-red-100 py-1 rounded-lg">
                                                <span class="block text-[9px] font-medium text-red-600">Alpha</span>
                                                <span class="text-xs font-bold text-red-700"><?= $alpha ?></span>
                                            </div>
                                            <div class="bg-indigo-50/50 border border-indigo-100 py-1 rounded-lg">
                                                <span class="block text-[9px] font-medium text-indigo-600">Total</span>
                                                <span class="text-xs font-bold text-indigo-700"><?= $total ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeConfirmModal()"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-50 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="ri-question-line text-indigo-600 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-base font-bold leading-6 text-gray-900" id="modal-title">Konfirmasi Simpan Absensi</h3>
                            <div class="mt-4">
                                <p class="text-xs text-gray-500 mb-3">
                                    Berikut adalah ringkasan santri <span class="font-bold text-indigo-600">Kelas <?= htmlspecialchars($activeKelasName) ?></span> yang <strong>tidak hadir</strong> pada tanggal <span id="confirm-date" class="font-bold text-gray-800"></span>:
                                </p>
                                
                                <!-- Statistics Summary -->
                                <div class="grid grid-cols-3 gap-3 mb-4">
                                    <div class="bg-yellow-50 border border-yellow-100 p-2.5 rounded-xl text-center">
                                        <span class="block text-xs font-medium text-yellow-600">Sakit</span>
                                        <span id="confirm-count-sakit" class="text-lg font-black text-yellow-700">0</span>
                                    </div>
                                    <div class="bg-blue-50 border border-blue-100 p-2.5 rounded-xl text-center">
                                        <span class="block text-xs font-medium text-blue-600">Izin</span>
                                        <span id="confirm-count-izin" class="text-lg font-black text-blue-700">0</span>
                                    </div>
                                    <div class="bg-red-50 border border-red-100 p-2.5 rounded-xl text-center">
                                        <span class="block text-xs font-medium text-red-600">Alpha</span>
                                        <span id="confirm-count-alpha" class="text-lg font-black text-red-700">0</span>
                                    </div>
                                </div>
                                
                                <!-- Detailed List -->
                                <div class="border border-gray-150 rounded-xl overflow-hidden bg-gray-50/50">
                                    <div class="px-4 py-2 border-b border-gray-150 bg-gray-50 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                        Daftar Santri Tidak Hadir (<span id="confirm-count-total">0</span>)
                                    </div>
                                    <div id="confirm-list" class="max-h-48 overflow-y-auto px-4 py-2 text-xs divide-y divide-gray-100 font-medium text-gray-700">
                                        <!-- Dynamic list -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-2xl border-t border-gray-100">
                    <button type="button" onclick="submitAttendanceForm()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white focus:outline-none transition-colors cursor-pointer">
                        Ya, Simpan
                    </button>
                    <button type="button" onclick="closeConfirmModal()" class="w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors cursor-pointer">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
let isConfirmed = false;

function toggleStatus(studentId, newStatus) {
    <?php if (!$canEdit): ?>return;<?php endif; ?>
    const input = document.getElementById('status-' + studentId);
    
    // Desktop Buttons
    const btnSakit = document.getElementById('btn-sakit-' + studentId);
    const btnIzin = document.getElementById('btn-izin-' + studentId);
    const btnAlpha = document.getElementById('btn-alpha-' + studentId);

    // Mobile Buttons
    const btnSakitMobile = document.getElementById('btn-sakit-mobile-' + studentId);
    const btnIzinMobile = document.getElementById('btn-izin-mobile-' + studentId);
    const btnAlphaMobile = document.getElementById('btn-alpha-mobile-' + studentId);

    const currentStatus = input.value;
    
    // Inactive Class names
    const inactiveClassDesktop = "px-4 py-1.5 text-xs font-bold rounded-xl border bg-gray-50 text-gray-400 hover:bg-gray-100 border-gray-200 transition-all cursor-pointer";
    const inactiveClassMobile = "flex-1 py-2 text-xs font-bold rounded-xl border bg-gray-50 text-gray-400 hover:bg-gray-100 border-gray-200 text-center transition-all cursor-pointer";

    // Reset classes to default inactive style
    if (btnSakit) btnSakit.className = inactiveClassDesktop;
    if (btnIzin) btnIzin.className = inactiveClassDesktop;
    if (btnAlpha) btnAlpha.className = inactiveClassDesktop;

    if (btnSakitMobile) btnSakitMobile.className = inactiveClassMobile;
    if (btnIzinMobile) btnIzinMobile.className = inactiveClassMobile;
    if (btnAlphaMobile) btnAlphaMobile.className = inactiveClassMobile;

    if (currentStatus === newStatus) {
        // Deselect -> set to empty (Hadir)
        input.value = "";
    } else {
        // Select new status
        input.value = newStatus;
        if (newStatus === 'sakit') {
            if (btnSakit) btnSakit.className = "px-4 py-1.5 text-xs font-bold rounded-xl border bg-yellow-100 text-yellow-800 border-yellow-300 shadow-sm transition-all cursor-pointer";
            if (btnSakitMobile) btnSakitMobile.className = "flex-1 py-2 text-xs font-bold rounded-xl border bg-yellow-100 text-yellow-800 border-yellow-300 shadow-sm text-center transition-all cursor-pointer";
        } else if (newStatus === 'izin') {
            if (btnIzin) btnIzin.className = "px-4 py-1.5 text-xs font-bold rounded-xl border bg-blue-100 text-blue-800 border-blue-300 shadow-sm transition-all cursor-pointer";
            if (btnIzinMobile) btnIzinMobile.className = "flex-1 py-2 text-xs font-bold rounded-xl border bg-blue-100 text-blue-800 border-blue-300 shadow-sm text-center transition-all cursor-pointer";
        } else if (newStatus === 'alpha') {
            if (btnAlpha) btnAlpha.className = "px-4 py-1.5 text-xs font-bold rounded-xl border bg-red-100 text-red-800 border-red-300 shadow-sm transition-all cursor-pointer";
            if (btnAlphaMobile) btnAlphaMobile.className = "flex-1 py-2 text-xs font-bold rounded-xl border bg-red-100 text-red-800 border-red-300 shadow-sm text-center transition-all cursor-pointer";
        }
    }
}

function handleFormSubmit(e) {
    if (isConfirmed) return true;
    
    e.preventDefault();
    
    const students = [];
    document.querySelectorAll('input[id^="status-"]').forEach(input => {
        const status = input.value;
        if (status) {
            const name = input.getAttribute('data-name');
            students.push({ name: name, status: status });
        }
    });

    let countSakit = 0;
    let countIzin = 0;
    let countAlpha = 0;
    
    students.forEach(s => {
        if (s.status === 'sakit') countSakit++;
        if (s.status === 'izin') countIzin++;
        if (s.status === 'alpha') countAlpha++;
    });

    // Populate confirmation modal fields
    document.getElementById('confirm-date').textContent = document.getElementsByName('date')[0].value;
    document.getElementById('confirm-count-sakit').textContent = countSakit;
    document.getElementById('confirm-count-izin').textContent = countIzin;
    document.getElementById('confirm-count-alpha').textContent = countAlpha;
    document.getElementById('confirm-count-total').textContent = students.length;

    const listContainer = document.getElementById('confirm-list');
    listContainer.innerHTML = '';

    if (students.length === 0) {
        listContainer.innerHTML = '<div class="py-4 text-center text-gray-400 italic">Semua santri tercatat Hadir (tidak ada ketidakhadiran).</div>';
    } else {
        students.forEach(s => {
            const div = document.createElement('div');
            div.className = 'py-2 flex justify-between items-center';
            
            let badgeColor = '';
            let statusText = '';
            if (s.status === 'sakit') {
                badgeColor = 'bg-yellow-100 text-yellow-800';
                statusText = 'Sakit';
            } else if (s.status === 'izin') {
                badgeColor = 'bg-blue-100 text-blue-800';
                statusText = 'Izin';
            } else if (s.status === 'alpha') {
                badgeColor = 'bg-red-100 text-red-800';
                statusText = 'Alpha';
            }

            div.innerHTML = `
                <span class="font-bold text-gray-800">${s.name}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-bold ${badgeColor}">
                    ${statusText}
                </span>
            `;
            listContainer.appendChild(div);
        });
    }

    // Open Modal
    document.getElementById('confirm-modal').classList.remove('hidden');
    return false;
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
}

function submitAttendanceForm() {
    isConfirmed = true;
    document.getElementById('attendance-form').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    // Scroll active class nav item into view on mobile horizontal scroll bar
    const activeClassId = "<?= $activeKelasId ?>";
    const activeNavItem = document.getElementById('class-nav-item-' + activeClassId);
    if (activeNavItem) {
        activeNavItem.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'center' });
    }

    // Initialize flatpickr date picker
    const picker = flatpickr("#date-picker", {
        dateFormat: "Y-m-d",
        maxDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            // Redirect with new date
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('date', dateStr);
            window.location.search = urlParams.toString();
        }
    });
});
</script>

<?php renderFooter(); ?>
