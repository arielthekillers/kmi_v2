<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/layout.php';
require_login();

// Database Connection
require_once __DIR__ . '/../../app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$pdo = $db->getConnection();

// Helper Data
$currentUserRole = function_exists('auth_get_role') ? auth_get_role() : 'admin';
$currentUserId = function_exists('auth_get_user_id') ? auth_get_user_id() : null;
$todayDate = date('Y-m-d');
$dayMap = [
    'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
    'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
];
$todayDay = $dayMap[date('D')] ?? '';

// 1. Basic Counts (Master Data)
$stats = [
    'pelajaran' => $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
    'kelas' => $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn(),
    'pengajar' => $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('pengajar', 'admin')")->fetchColumn(), // Admin is also teacher? Usually separate.
    'koreksi' => 0
];
// Adjust pengajar count if role is strictly 'pengajar'
// $stats['pengajar'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pengajar'")->fetchColumn(); 

// 2. Koreksi Stats
$koreksiSql = "SELECT COUNT(*) as total, SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) as selesaicount FROM exams";
if ($currentUserRole === 'pengajar' && $currentUserId) {
    $stmt = $pdo->prepare($koreksiSql . " WHERE teacher_id = ?");
    $stmt->execute([$currentUserId]);
    $kRes = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $kRes = $pdo->query($koreksiSql)->fetch(PDO::FETCH_ASSOC);
}
$stats['koreksi'] = $kRes['total'];
$totalKoreksi = $kRes['total'];
$finishedKoreksi = $kRes['selesaicount'] ?? 0;
$correctionPercent = $totalKoreksi > 0 ? round(($finishedKoreksi / $totalKoreksi) * 100) : 0;

// 3. Tanqih Idad Summary (Attendance)
// Total Slots Today
$sqlSlots = "SELECT COUNT(*) FROM schedules WHERE day = ?";
$paramsSlots = [$todayDay];

if ($currentUserRole === 'pengajar' && $currentUserId) {
    $sqlSlots .= " AND teacher_id = ?";
    $paramsSlots[] = $currentUserId;
}

$stmtSlots = $pdo->prepare($sqlSlots);
$stmtSlots->execute($paramsSlots);
$totalSlotsToday = $stmtSlots->fetchColumn();

// Verified Slots (Tanqih Table)
// Assuming table `tanqih` has columns: date, kelas_id, hour, ...
// We need to count distinct verified slots for today.
// Warning: If schedule changed but tanqih exists? Usually fine.
$sqlVerified = "SELECT COUNT(*) FROM tanqih WHERE date = ?";
$paramsVerified = [$todayDate];

if ($currentUserRole === 'pengajar' && $currentUserId) {
    // We need to verify if the slot verified belongs to this teacher.
    // This is tricky without joining schedules.
    // Query: Count tanqih entries where (kelas_id, hour) matches a schedule entry for this teacher on this day.
    $sqlVerified = "
        SELECT COUNT(*) 
        FROM tanqih t
        JOIN schedules s ON t.kelas_id = s.kelas_id AND t.hour = s.hour
        WHERE t.date = ? AND s.day = ? AND s.teacher_id = ?
    ";
    $paramsVerified = [$todayDate, $todayDay, $currentUserId];
}

$stmtVerified = $pdo->prepare($sqlVerified);
$stmtVerified->execute($paramsVerified);
$verifiedCount = $stmtVerified->fetchColumn();

$attendancePercent = $totalSlotsToday > 0 ? round(($verifiedCount / $totalSlotsToday) * 100) : 0;


// 4. Piket Summary (Syeikh & Keliling)
// Syeikh
$stmt = $pdo->prepare("
    SELECT u.nama 
    FROM piket_schedule p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.type = 'syeikh' AND p.day = ?
");
$stmt->execute([$todayDay]);
$piketTodayNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Keliling
$stmt = $pdo->prepare("
    SELECT u.nama 
    FROM piket_schedule p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.type = 'keliling' AND p.day = ?
");
$stmt->execute([$todayDay]);
$piketKelilingTodayNames = $stmt->fetchAll(PDO::FETCH_COLUMN);


// 5. Absensi Pengajar Summary
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as cnt 
    FROM attendance_logs 
    WHERE date = ? 
    GROUP BY status
");
$stmt->execute([$todayDate]);
$absensiStats = ['hadir' => 0, 'tidak_hadir' => 0]; // 'recorded' is sum
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['status'] === 'hadir') $absensiStats['hadir'] = $row['cnt'];
    elseif ($row['status'] === 'tidak_hadir') $absensiStats['tidak_hadir'] = $row['cnt'];
}

// Calculate Total Santri (Sum of jumlah_murid in kelas)
$totalSantri = $pdo->query("SELECT SUM(jumlah_murid) FROM kelas")->fetchColumn() ?? 0;



renderHeader("Dashboard");
?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard KMI</h1>
            <p class="mt-2 text-gray-600">
                Ringkasan aktivitas hari ini, <strong><?= date('d M Y') ?> (<?= $todayDay ?>)</strong>
            </p>
        </div>
        <div class="mt-4 md:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                Tahun Ajaran 2025/2026
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <!-- Stats Grid Row 1: Syeikh & Keliling -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        
        <!-- 1. Syeikh Diwan Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-indigo-50 rounded-lg p-3">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= $todayDay ?></span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Syeikh Diwan</h3>
            <div class="flex-1 mt-2">
                <?php if (empty($piketTodayNames)): ?>
                    <p class="text-sm text-gray-400 italic">Tidak ada jadwal.</p>
                <?php else: ?>
                    <ul class="space-y-1">
                        <?php foreach ($piketTodayNames as $name): ?>
                        <li class="flex items-center text-sm font-medium text-gray-700">
                            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full mr-2"></span>
                            <?= htmlspecialchars($name) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <a href="<?= url('/piket/office') ?>" class="mt-4 text-sm text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center">
                Lihat Jadwal <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <!-- 1b. Piket Keliling Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-teal-50 rounded-lg p-3">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= $todayDay ?></span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Piket Keliling</h3>
            <div class="flex-1 mt-2">
                <?php if (empty($piketKelilingTodayNames)): ?>
                    <p class="text-sm text-gray-400 italic">Tidak ada jadwal.</p>
                <?php else: ?>
                    <ul class="space-y-1">
                        <?php foreach ($piketKelilingTodayNames as $name): ?>
                        <li class="flex items-center text-sm font-medium text-gray-700">
                            <span class="w-1.5 h-1.5 bg-teal-400 rounded-full mr-2"></span>
                            <?= htmlspecialchars($name) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <a href="<?= url('/piket/roaming') ?>" class="mt-4 text-sm text-teal-600 hover:text-teal-800 font-medium inline-flex items-center">
                Lihat Jadwal <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </div>

    <!-- Stats Grid Row 2: Absensi, Tanqih, Koreksi -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <?php
        // 2. Absensi Pengajar Summary (Already loaded into $absensiStats in header)
        ?>
        <!-- 2. Absensi Pengajar Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-green-50 rounded-lg p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <span class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-1 rounded-full">New</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Absensi Pengajar</h3>
            <div class="flex-1 mt-2">
                <div class="grid grid-cols-2 gap-2 text-center">
                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="text-xl font-bold text-gray-900"><?= $absensiStats['hadir'] ?></div>
                        <div class="text-xs text-gray-500">Hadir</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="text-xl font-bold text-gray-900"><?= $absensiStats['tidak_hadir'] ?></div>
                        <div class="text-xs text-gray-500">Absen</div>
                    </div>
                </div>
            </div>
            <a href="<?= url('/attendance/report') ?>" class="mt-4 text-sm text-green-600 hover:text-green-800 font-medium inline-flex items-center">
                Lihat Laporan <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <!-- 3. Tanqih Idad Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-blue-50 rounded-lg p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-xs font-semibold text-gray-500"><?= $attendancePercent ?>%</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Tanqih Idad</h3>
            <div class="flex-1 mt-2">
                 <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $attendancePercent ?>%"></div>
                </div>
                <p class="text-sm text-gray-600">
                    <strong><?= $verifiedCount ?></strong> dari <strong><?= $totalSlotsToday ?></strong> jadwal terverifikasi.
                </p>
            </div>
            <a href="<?= url('/tanqih') ?>" class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
                <?= ($currentUserRole === 'admin' || $currentUserRole === 'pengajar') ? 'Buka Tanqih' : 'Lihat Data' ?>
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <!-- 4. Koreksi Ujian Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-purple-50 rounded-lg p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <span class="text-xs font-semibold text-gray-500"><?= $correctionPercent ?>%</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Koreksi Ujian</h3>
            <div class="flex-1 mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?= $correctionPercent ?>%"></div>
                </div>
                <p class="text-sm text-gray-600">
                    <strong><?= $finishedKoreksi ?></strong> dari <strong><?= $totalKoreksi ?></strong> mapel selesai.
                </p>
            </div>
            <a href="<?= url('/grades') ?>" class="mt-4 text-sm text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                Lihat Progres <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

    </div>

    <!-- Admin Master Data Section (Mini Cards) -->
    <?php if ($currentUserRole === 'admin'): ?>
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Master Data</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="<?= url('/subjects') ?>" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Pelajaran</p>
                   <p class="text-xl font-bold text-gray-900 mt-1"><?= $stats['pelajaran'] ?></p>
                </div>
                <div class="bg-blue-50 p-2 rounded-lg text-blue-600 group-hover:bg-blue-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
            </div>
        </a>
        <a href="<?= url('/classes') ?>" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Santri</p>
                   <p class="text-xl font-bold text-gray-900 mt-1"><?= $totalSantri ?></p>
                </div>
                <div class="bg-pink-50 p-2 rounded-lg text-pink-600 group-hover:bg-pink-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </a>
        <a href="<?= url('/classes') ?>" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Kelas</p>
                   <p class="text-xl font-bold text-gray-900 mt-1"><?= $stats['kelas'] ?></p>
                </div>
                <div class="bg-green-50 p-2 rounded-lg text-green-600 group-hover:bg-green-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
        </a>
        <a href="<?= url('/teachers') ?>" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Pengajar</p>
                   <p class="text-xl font-bold text-gray-900 mt-1"><?= $stats['pengajar'] ?></p>
                </div>
                <div class="bg-purple-50 p-2 rounded-lg text-purple-600 group-hover:bg-purple-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </a>
        <a href="<?= url('/schedule') ?>" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow group">
             <div class="flex items-center justify-between">
                <div>
                   <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Jadwal</p>
                   <p class="text-xl font-bold text-gray-900 mt-1">Total</p>
                </div>
                <div class="bg-orange-50 p-2 rounded-lg text-orange-600 group-hover:bg-orange-100 transition-colors">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>

</main>
<?php renderFooter(); ?>
