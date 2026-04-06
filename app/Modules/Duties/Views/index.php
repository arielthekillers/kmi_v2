<?php require __DIR__ . '/../../../Views/layouts/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Jadwal Piket</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($schedule as $day => $types): ?>
            <?php if (empty($types)) continue; ?>
            
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-indigo-600 px-4 py-2">
                    <h3 class="text-lg font-semibold text-white"><?= $day ?></h3>
                </div>
                <div class="p-4">
                    <!-- Piket Diwan -->
                    <?php if (!empty($types['diwan'])): ?>
                        <div class="mb-4">
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Syeikh Diwan</h4>
                            <ul class="space-y-2">
                                <?php foreach ($types['diwan'] as $staff): ?>
                                    <li class="flex items-center text-gray-700">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        <?= htmlspecialchars($staff['nama']) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Piket Keliling -->
                    <?php if (!empty($types['keliling'])): ?>
                        <div>
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Piket Keliling</h4>
                            <ul class="space-y-2">
                                <?php foreach ($types['keliling'] as $staff): ?>
                                    <li class="flex items-center text-gray-700">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                        <?= htmlspecialchars($staff['nama']) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<?php require __DIR__ . '/../../../Views/layouts/footer.php'; ?>
