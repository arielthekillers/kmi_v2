<?php 
renderHeader("Koreksi Ujian"); 
$isAdmin = (auth_get_role() === 'admin');
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate flex items-center gap-3">
                Koreksi Ujian
                <?php if (isset($currentYear)): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 border border-indigo-200">
                        <i class="ri-calendar-line mr-1"></i> TA: <?= htmlspecialchars($currentYear['name'] ?? '-') ?>
                    </span>
                <?php endif; ?>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Kelola jadwal koreksi dan input nilai untuk tahun ajaran aktif.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <?php if (isset($activeSession)): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?= $activeSession['is_open'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> border border-current">
                    <i class="ri-door-<?= $activeSession['is_open'] ? 'open' : 'closed' ?>-line mr-1"></i>
                    Sesi <?= $activeSession['type'] ?>: <?= $activeSession['is_open'] ? 'DIBUKA' : 'DITUTUP' ?>
                </span>
            <?php endif; ?>

            <?php if (auth_get_role() === 'admin' || auth_is_panitia()): ?>
                <a href="<?= url('/grades/trash') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <i class="ri-delete-bin-line mr-2"></i> Tong Sampah
                </a>
                <button onclick="toggleModal('addKoreksiModal')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                    + Tambah Koreksi
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Instructions (Collapsible) -->
    <details class="bg-indigo-50 border border-indigo-100 rounded-lg mb-8 group open:ring-0">
        <summary class="list-none flex items-center gap-2 p-4 cursor-pointer text-indigo-900 font-medium hover:bg-indigo-100/50 transition-colors rounded-lg">
             <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Petunjuk Penggunaan</span>
            <svg class="w-4 h-4 ml-auto text-indigo-500 transform transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </summary>
        <div class="px-6 pb-6 text-indigo-800 text-sm leading-relaxed border-t border-indigo-100/50 pt-4 space-y-5">

            <?php if (auth_get_role() === 'admin' || auth_is_panitia()): ?>
            <!-- Alur Admin / Panitia -->
            <div>
                <p class="font-bold text-indigo-900 mb-2 flex items-center gap-2">
                    <span class="inline-block w-5 h-5 rounded-full bg-indigo-600 text-white text-center leading-5 text-xs">A</span>
                    Alur Kerja Admin / Panitia
                </p>
                <ol class="list-decimal ml-5 space-y-2">
                    <li>Pastikan <strong>Sesi Ujian</strong> (UUPT/UPT/UUAT/UAT) sudah <strong>diaktifkan</strong> dan <strong>dibuka</strong> melalui menu Panitia Ujian sebelum memulai.</li>
                    <li>Klik <strong>+ Tambah Koreksi</strong> untuk mendaftarkan mata pelajaran yang akan dikoreksi. Pilih kelas, mata pelajaran, dan pemeriksa (pengajar).</li>
                    <li>Klik <strong>Input Nilai</strong> pada baris mata pelajaran, lalu isi kolom <strong>Nomor Bayanat</strong> untuk setiap santri. Pemeriksa (pengajar) baru bisa mulai menginput nilai setelah nomor bayanat seluruh santri terisi.</li>
                    <li>Setelah pemeriksa menyelesaikan koreksi (status <em>Selesai</em>), jika diperlukan koreksi ulang, klik tombol <strong>Buka Akses</strong> untuk mengizinkan pemeriksa mengedit kembali.</li>
                    <li>Untuk menghapus sementara suatu jadwal koreksi, klik ikon <strong>hapus</strong> (🗑). Data tidak akan hilang permanen dan dapat dipulihkan melalui menu <strong>Tong Sampah</strong>.</li>
                </ol>
            </div>
            <?php endif; ?>

            <?php if (auth_get_role() === 'pengajar'): ?>
            <!-- Alur Pengajar / Pemeriksa -->
            <div>
                <p class="font-bold text-indigo-900 mb-2 flex items-center gap-2">
                    <span class="inline-block w-5 h-5 rounded-full bg-green-600 text-white text-center leading-5 text-xs">P</span>
                    Alur Kerja Pemeriksa (Pengajar)
                </p>
                <ol class="list-decimal ml-5 space-y-2">
                    <li>Tombol <strong>Input Nilai</strong> hanya aktif jika: sesi ujian sedang <em>dibuka</em> oleh Panitia <strong>dan</strong> Nomor Bayanat seluruh santri sudah diisi oleh Admin/Panitia.</li>
                    <li>Klik <strong>Input Nilai</strong>, lalu isi <strong>Skor yang Diraih</strong> sesuai nomor bayanat masing-masing santri. Daftar santri diurutkan berdasarkan nomor bayanat untuk memudahkan koreksi.</li>
                    <li>Kolom skor <strong>tidak boleh dikosongkan</strong>. Untuk santri yang tidak mengikuti ujian, isi dengan tanda <strong>strip (-)</strong>.</li>
                    <li>Klik <strong>Simpan sebagai Draft</strong> untuk menyimpan sementara dan melanjutkan di lain waktu. Status akan menjadi <em>Proses</em>.</li>
                    <li>Setelah seluruh skor terisi dan sudah diyakini benar, klik <strong>Selesai Diperiksa</strong>. Status akan berubah menjadi <em>Selesai</em> dan akses edit akan terkunci otomatis.</li>
                </ol>
            </div>
            <?php endif; ?>

            <!-- Catatan Umum -->
            <div class="p-3 bg-white/60 rounded border border-indigo-200 text-xs space-y-1">
                <p><strong>⚠ Nilai hanya dapat dilihat</strong> oleh Wali Kelas jika status koreksian sudah <em>Selesai</em>.</p>
                <p><strong>🔓 Buka Akses</strong> hanya tersedia bagi Admin & Panitia untuk membuka kembali koreksi yang sudah berstatus Selesai.</p>
                <p><strong>🗑 Tong Sampah:</strong> Data yang dihapus dapat dipulihkan (<em>Restore</em>) atau dihapus permanen. Tidak bisa membuat entri baru jika data yang sama masih ada di Tong Sampah — gunakan <em>Restore</em> terlebih dahulu.</p>
            </div>

        </div>
    </details>


    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-indigo-50 p-3">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Koreksi</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?= $stats['total'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-green-50 p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Selesai Diperiksa</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?= $stats['selesai'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proses (Draft) -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-yellow-50 p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Sedang Diproses</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?= $stats['proses'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Belum -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-gray-50 p-3">
                            <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Belum Diperiksa</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?= $stats['belum'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <form method="GET" action="<?= url('/grades') ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Kelas -->
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kelas</label>
                <select name="kelas" class="tom-select block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs p-2.5 border bg-gray-50/50">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelas as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filters['kelas'] == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['tingkat']) ?> - <?= htmlspecialchars($k['abjad']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Pelajaran -->
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pelajaran</label>
                <select name="pelajaran" class="tom-select block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs p-2.5 border bg-gray-50/50">
                    <option value="">Semua Pelajaran</option>
                    <?php foreach ($pelajaran as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $filters['pelajaran'] == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Pengajar -->
            <?php if (auth_get_role() === 'admin' || auth_is_panitia()): ?>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pemeriksa</label>
                <select name="pengajar" class="tom-select block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs p-2.5 border bg-gray-50/50">
                    <option value="">Semua Pemeriksa</option>
                    <?php foreach ($pengajar as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $filters['pengajar'] == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <!-- Status -->
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</label>
                <select name="status" class="tom-select block w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs p-2.5 border bg-gray-50/50">
                    <option value="">Semua Status</option>
                    <option value="selesai" <?= $filters['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="proses" <?= $filters['status'] === 'proses' ? 'selected' : '' ?>>Proses / Draft</option>
                    <option value="belum" <?= $filters['status'] === 'belum' ? 'selected' : '' ?>>Belum Diperiksa</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-[27px]">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 rounded-xl text-xs transition-all shadow-sm shadow-indigo-100 flex items-center justify-center gap-2 h-[38px]">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                <a href="<?= url('/grades') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold px-4 rounded-xl text-xs transition-all flex items-center justify-center h-[38px]">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <?php if (empty($exams)): ?>
        <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data koreksi</h3>
            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan sesi koreksi baru.</p>
        </div>
    <?php else: ?>
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelajaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahap</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemeriksa</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($exams as $k):
                        $id = $k['id'];
                        $isAdmin = (auth_get_role() === 'admin');
                        $isPanitia = auth_is_panitia($k['exam_session_id']);
                        $mapel = $k['mapel_nama'] ?? 'Unknown';
                        $klsFull = "Kelas " . ($k['tingkat'] ?? '?') . "-" . ($k['abjad'] ?? '?');
                        $guru = $k['pengajar_nama'] ?? 'Unknown';
                        $isDone = ($k['status'] ?? '') === 'selesai';

                        // Progress
                        $totalStudents = (int)($k['jumlah_murid'] ?? 0);
                        $gradedCount = (int)($k['graded_count'] ?? 0);

                        $percentage = $totalStudents > 0 ? round(($gradedCount / $totalStudents) * 100) : 0;
                        $colorClass = $percentage >= 100 ? 'bg-green-600' : 'bg-indigo-600';
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($isDone): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Selesai
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Proses
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($mapel) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= htmlspecialchars($klsFull) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded">
                                    <?= htmlspecialchars($k['exam_type'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap align-middle">
                                <div class="w-full max-w-xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-semibold text-gray-700"><?= $gradedCount ?> / <?= $totalStudents ?></span>
                                        <span class="text-xs font-semibold text-gray-500"><?= $percentage ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="<?= $colorClass ?> h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    <?= htmlspecialchars($guru) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <?php if ($isDone): ?>
                                        <a href="<?= url('/grades/edit?id=' . $id) ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md">
                                            Lihat Nilai
                                        </a>
                                        <?php if (auth_can_manage_grades($k['exam_session_id'])): ?>
                                            <form action="<?= url('/grades/unlock') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kembali akses edit untuk koreksi ini?');" class="inline">
                                                <?= csrf_token_field() ?>
                                                <input type="hidden" name="id" value="<?= $id ?>">
                                                <button type="submit" class="text-orange-600 hover:text-orange-900 bg-orange-50 px-3 py-1 rounded-md">
                                                    Buka Akses
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php 
                                            $isBayanatComplete = ($totalStudents > 0 && $k['bayanat_count'] >= $totalStudents);
                                            $isDesignatedExaminer = ($k['teacher_id'] == auth_get_user_id());
                                            
                                            // Admin & Panitia ALWAYS can enter (to fill bayanat or scores)
                                            // Designated Examiner can ONLY enter if bayanat is complete AND session is open
                                            if ($isAdmin || $isPanitia) {
                                                $canInput = true;
                                                $disabledReason = "";
                                            } elseif ($isDesignatedExaminer) {
                                                if (!$isBayanatComplete) {
                                                    $canInput = false;
                                                    $disabledReason = "Bayanat Belum Lengkap";
                                                } elseif ($k['session_is_open'] != 1) {
                                                    $canInput = false;
                                                    $disabledReason = "Sesi Ditutup";
                                                } else {
                                                    $canInput = true;
                                                    $disabledReason = "";
                                                }
                                            } else {
                                                $canInput = false;
                                                $disabledReason = "Bukan Pemeriksa";
                                            }
                                        ?>
                                        <?php if ($canInput): ?>
                                            <a href="<?= url('/grades/edit?id=' . $id) ?>" class="text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded-md shadow-sm">
                                                Input Nilai
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 bg-gray-50 px-3 py-1 rounded-md cursor-not-allowed italic text-xs border border-gray-200" title="<?= $disabledReason ?>">
                                                <?= $disabledReason ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if (auth_can_manage_grades($k['exam_session_id'])): ?>
                                        <a href="<?= url('/grades/delete?id=' . $id) ?>" onclick="return confirmDelete('<?= htmlspecialchars($mapel) ?>', '<?= htmlspecialchars($klsFull) ?>')" class="text-red-600 hover:text-red-900 p-1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                       </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
        </div>
    <?php endif; ?>

</main>

<!-- Modal Add -->
<div id="addKoreksiModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleModal('addKoreksiModal')"></div> <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-md sm:my-8 sm:align-middle sm:max-w-lg sm:w-full mx-auto">
            <form action="<?= url('/grades/create') ?>" method="POST">
                <?= csrf_token_field() ?>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 flex items-center justify-between">
                        Tambah Koreksi Baru
                        <?php if (isset($activeSession)): ?>
                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Sesi: <?= $activeSession['type'] ?></span>
                        <?php endif; ?>
                    </h3>

                    <?php if (!isset($activeSession)): ?>
                        <div class="bg-red-50 text-red-700 p-3 rounded text-sm mb-4">
                            <strong>Peringatan!</strong> Belum ada sesi ujian (UUPT/UPT/dll) yang diaktifkan oleh Panitia. Anda tidak dapat membuat data koreksi baru.
                        </div>
                    <?php endif; ?>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="id_kelas" id="modal_id_kelas" required class="tom-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white" onchange="onClassChange()">
                                <option value="">Pilih Kelas...</option>
                                <?php foreach ($kelas as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['tingkat']) ?> - <?= htmlspecialchars($k['abjad']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pelajaran</label>
                            <select name="id_pelajaran" id="modal_id_pelajaran" required class="tom-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white" onchange="onSubjectChange()">
                                <option value="">Pilih Kelas Terlebih Dahulu...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pemeriksa</label>
                            <select name="id_pengajar" id="modal_id_pengajar" required class="tom-select mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                <?php foreach ($pengajar as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Skor Tertinggi (Total Poin Soal)</label>
                            <input type="number" name="skor_maks" required value="100" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                        <!-- Toggle Pelajaran Khusus -->
                        <?php if (!empty($specialSubjects)): ?>
                        <div class="border-t border-gray-100 pt-3">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" id="toggle_special" class="sr-only" onchange="onToggleSpecial()">
                                    <div class="w-10 h-5 bg-gray-200 rounded-full shadow-inner transition-colors" id="toggle_special_track"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform" id="toggle_special_thumb"></div>
                                </div>
                                <span class="text-sm font-medium text-purple-700">
                                    <i class="ri-star-fill text-purple-500 mr-1"></i>Tampilkan Pelajaran Ujian Khusus
                                </span>
                            </label>
                            <p class="text-xs text-gray-400 mt-1 ml-12">Aktifkan untuk memilih pelajaran di luar jadwal (Praktek Mengajar, dll). Pengajar dipilih manual.</p>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-center">
                            <input type="checkbox" name="include_lisan" id="include_lisan" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="include_lisan" class="ml-2 block text-sm text-gray-700 font-medium">
                                Include Ujian Lisan
                            </label>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="toggleModal('addKoreksiModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const teachingMap = <?= json_encode($teachingMap) ?>;
    const specialSubjects = <?= json_encode($specialSubjects ?? []) ?>;
    let isSpecialMode = false;

    function resetKoreksiForm() {
        // Reset special toggle
        isSpecialMode = false;
        const tog = document.getElementById('toggle_special');
        if (tog) { tog.checked = false; updateToggleUI(false); }

        // Reset include lisan
        const lisan = document.getElementById('include_lisan');
        if (lisan) lisan.checked = false;

        // Reset skor_maks
        const skorMaks = document.querySelector('#addKoreksiModal input[name="skor_maks"]');
        if (skorMaks) skorMaks.value = 100;

        // Reset TomSelect fields (kelas, pelajaran, pengajar)
        setTimeout(() => {
            const tsKelas = document.getElementById('modal_id_kelas');
            const tsPelajaran = document.getElementById('modal_id_pelajaran');
            const tsPengajar = document.getElementById('modal_id_pengajar');

            if (tsKelas && tsKelas.tomselect) {
                tsKelas.tomselect.clear();
            }
            if (tsPelajaran && tsPelajaran.tomselect) {
                tsPelajaran.tomselect.clear();
                tsPelajaran.tomselect.clearOptions();
                tsPelajaran.tomselect.addOption({ value: '', text: 'Pilih Kelas Terlebih Dahulu...' });
            }
            if (tsPengajar && tsPengajar.tomselect) {
                tsPengajar.tomselect.clear();
            }
        }, 60);
    }

    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
        if (!document.getElementById(id).classList.contains('hidden')) {
            setTimeout(initTomSelects, 50);
            resetKoreksiForm();
        }
    }

    function updateToggleUI(active) {
        const track = document.getElementById('toggle_special_track');
        const thumb = document.getElementById('toggle_special_thumb');
        if (!track) return;
        if (active) {
            track.classList.remove('bg-gray-200'); track.classList.add('bg-purple-500');
            thumb.style.transform = 'translateX(20px)';
        } else {
            track.classList.remove('bg-purple-500'); track.classList.add('bg-gray-200');
            thumb.style.transform = 'translateX(0)';
        }
    }

    function onToggleSpecial() {
        isSpecialMode = document.getElementById('toggle_special').checked;
        updateToggleUI(isSpecialMode);
        const tsSubject = document.getElementById('modal_id_pelajaran').tomselect;
        tsSubject.clear();
        tsSubject.clearOptions();
        if (isSpecialMode) {
            specialSubjects.forEach(s => { tsSubject.addOption({ value: s.id, text: s.nama }); });
            tsSubject.refreshOptions(false);
        } else {
            onClassChange();
        }
    }

    function onClassChange() {
        if (isSpecialMode) return;
        const classSelect = document.getElementById('modal_id_kelas');
        const subjectSelect = document.getElementById('modal_id_pelajaran');
        const classId = classSelect.value;
        const tsSubject = subjectSelect.tomselect;
        tsSubject.clear();
        tsSubject.clearOptions();
        if (classId && teachingMap[classId]) {
            const subjects = teachingMap[classId];
            subjects.forEach(s => { tsSubject.addOption({ value: s.subject_id, text: s.subject_name }); });
            tsSubject.refreshOptions(false);
        } else {
            tsSubject.addOption({ value: "", text: "Pilih Kelas Terlebih Dahulu..." });
        }
    }

    function onSubjectChange() {
        if (isSpecialMode) return; // Pengajar dipilih manual saat mode khusus
        const classId = document.getElementById('modal_id_kelas').value;
        const subjectId = document.getElementById('modal_id_pelajaran').value;
        const pengajarSelect = document.getElementById('modal_id_pengajar');
        if (classId && subjectId && teachingMap[classId]) {
            const assignment = teachingMap[classId].find(s => s.subject_id == subjectId);
            if (assignment && assignment.teacher_id) {
                pengajarSelect.tomselect.setValue(assignment.teacher_id);
            }
        }
    }
</script>

<script>
    function confirmDelete(mapel, kelas) {
        return confirm('PERINGATAN: Anda akan menghapus data koreksi ' + mapel + ' (' + kelas + ').\n\nData yang dihapus akan disembunyikan dan hanya bisa dipulihkan oleh tim IT/Database Admin.\n\nApakah Anda yakin ingin melanjutkan?');
    }
</script>

<?php renderFooter(); ?>
