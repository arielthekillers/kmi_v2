<?php
$kelasData = $data['kelasData'] ?? [];
$pelajaranData = $data['pelajaranData'] ?? [];
$days = $data['days'] ?? [];
$hours = $data['hours'] ?? [];
$mySchedule = $data['mySchedule'] ?? [];
$substitutions = $data['substitutions'] ?? [];

$daysIndoMap = [0 => 'Ahad', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
$subsByDay = [];
foreach ($substitutions as $sub) {
    $dayIndex = date('w', strtotime($sub['date']));
    $dayName = $daysIndoMap[$dayIndex] ?? '';
    if ($dayName) {
        $subsByDay[$dayName][] = $sub;
    }
}

renderHeader("Jadwal Mengajar Saya");
?>

<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 border-b border-gray-200 pb-4">
        <h1 class="text-2xl font-bold text-gray-900">Jadwal Mengajar</h1>
        <p class="text-gray-500 text-sm mt-1">Jadwal rutin dan tugas menggantikan Anda.</p>
    </div>

    <div class="space-y-6">
        <?php foreach ($days as $day): 
            $dailySlots = $mySchedule[$day] ?? [];
            $dailySubs = $subsByDay[$day] ?? [];
            
            $combinedSlots = [];
            
            // Add regular slots
            foreach ($dailySlots as $hour => $slot) {
                $combinedSlots[] = [
                    'type' => 'regular',
                    'hour' => $hour,
                    'data' => $slot
                ];
            }
            
            // Add substitution slots
            foreach ($dailySubs as $sub) {
                $combinedSlots[] = [
                    'type' => 'substitute',
                    'hour' => $sub['hour'],
                    'data' => $sub
                ];
            }
            
            // Sort combined slots by hour
            usort($combinedSlots, function($a, $b) {
                return $a['hour'] <=> $b['hour'];
            });
            
            $totalJam = count($combinedSlots);
        ?>
            <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-100">
                <div class="bg-indigo-50 px-4 py-3 border-b border-indigo-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-indigo-800"><?= $day ?></h3>
                    <span class="text-xs font-semibold text-indigo-600 bg-indigo-100 px-2 py-1 rounded-full">
                        <?= $totalJam ?> Jam
                    </span>
                </div>
                
                <?php if (empty($combinedSlots)): ?>
                    <div class="px-4 py-4 text-center text-gray-400 italic text-sm">
                        Tidak ada jam mengajar.
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($combinedSlots as $item): 
                            if ($item['type'] === 'regular'):
                                $slot = $item['data'];
                                $hour = $item['hour'];
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
                        <?php else: 
                                $sub = $item['data'];
                                $hour = $item['hour'];
                        ?>
                            <div class="px-4 py-3 hover:bg-purple-50 transition-colors flex items-center justify-between bg-purple-50/30">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold text-sm">
                                        <?= $hour ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-purple-900 line-clamp-1"><?= htmlspecialchars($sub['subject_name']) ?></p>
                                        <p class="text-xs text-purple-600">Kelas <?= $sub['tingkat'] ?>-<?= $sub['abjad'] ?> (<?= date('d M', strtotime($sub['date'])) ?>)</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 text-[10px] font-bold rounded bg-purple-100 text-purple-700 border border-purple-200">PENGGANTI</span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<?php renderFooter(); ?>
