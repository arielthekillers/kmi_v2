<?php
$kelasData = $data['kelasData'] ?? [];
$pelajaranData = $data['pelajaranData'] ?? [];
$days = $data['days'] ?? [];
$hours = $data['hours'] ?? [];
$mySchedule = $data['mySchedule'] ?? [];

renderHeader("Jadwal Mengajar Saya");
?>

<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 border-b border-gray-200 pb-4">
        <h1 class="text-2xl font-bold text-gray-900">Jadwal Mengajar</h1>
        <p class="text-gray-500 text-sm mt-1">Jadwal mengajar Anda minggu ini.</p>
    </div>

    <div class="space-y-6">
        <?php foreach ($days as $day): 
            $dailySlots = $mySchedule[$day] ?? [];
            // Sort by hour
            ksort($dailySlots);
        ?>
            <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-100">
                <div class="bg-indigo-50 px-4 py-3 border-b border-indigo-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-indigo-800"><?= $day ?></h3>
                    <span class="text-xs font-semibold text-indigo-600 bg-indigo-100 px-2 py-1 rounded-full">
                        <?= count($dailySlots) ?> Jam
                    </span>
                </div>
                
                <?php if (empty($dailySlots)): ?>
                    <div class="px-4 py-4 text-center text-gray-400 italic text-sm">
                        Tidak ada jam mengajar.
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($dailySlots as $hour => $slot): 
                            $mapelName = $pelajaranData[$slot['mapel']]['nama'] ?? 'Unknown Subject';
                            $kelasInfo = $kelasData[$slot['kelas']] ?? null;
                            $kelasName = $kelasInfo ? "Kelas {$kelasInfo['tingkat']}-{$kelasInfo['abjad']}" : 'Unknown Class';
                        ?>
                            <div class="px-4 py-3 hover:bg-gray-50 transition-colors flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-sm">
                                        <?= $hour ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 line-clamp-1"><?= htmlspecialchars($mapelName) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($kelasName) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block w-2 h-2 rounded-full bg-green-400"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<?php renderFooter(); ?>
