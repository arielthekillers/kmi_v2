<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="px-6 py-8 w-full max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="ri-calendar-todo-fill text-xl"></i>
                </div>
                Laporan Mingguan
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Pilih minggu untuk mencetak laporan mingguan.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Minggu Laporan</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="ri-calendar-event-line text-gray-400"></i>
                </div>
                <select id="week-select" class="block w-full pl-10 pr-10 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow appearance-none cursor-pointer bg-white">
                    <?php foreach ($week_options as $opt): ?>
                        <option value="<?= htmlspecialchars($opt['value']) ?>">
                            <?= htmlspecialchars($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="ri-arrow-down-s-line text-gray-400"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                * Laporan dihitung dari hari Sabtu sampai dengan hari Kamis.
            </p>
        </div>
    </div>

    <!-- Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 transform translate-x-4 -translate-y-4 group-hover:scale-110 transition-transform duration-300">
                <i class="ri-user-received-2-line text-6xl text-indigo-500"></i>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <i class="ri-user-received-2-line text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Kehadiran Guru</h3>
            <p class="text-sm text-gray-500 mb-6">Cetak rekapitulasi ketidakhadiran (Sakit, Izin, Alfa) dan persentase kehadiran guru, beserta laporan jam badal.</p>
            <button onclick="printReport('teacher-attendance')" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium text-sm rounded-xl transition-colors">
                <i class="ri-printer-line"></i> Cetak Laporan
            </button>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 transform translate-x-4 -translate-y-4 group-hover:scale-110 transition-transform duration-300">
                <i class="ri-group-line text-6xl text-purple-500"></i>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4">
                <i class="ri-group-line text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Kehadiran Santri</h3>
            <p class="text-sm text-gray-500 mb-6">Cetak rekapitulasi kehadiran santri per kelas, lengkap dengan top kelas tertinggi & terendah.</p>
            <button onclick="printReport('student-attendance')" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium text-sm rounded-xl transition-colors">
                <i class="ri-printer-line"></i> Cetak Laporan
            </button>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 transform translate-x-4 -translate-y-4 group-hover:scale-110 transition-transform duration-300">
                <i class="ri-checkbox-circle-line text-6xl text-emerald-500"></i>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                <i class="ri-checkbox-circle-line text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Tanqih Idad Guru</h3>
            <p class="text-sm text-gray-500 mb-6">Cetak laporan mingguan Tanqih Idad Guru dengan daftar guru persentase tertinggi, terendah, dan statistik.</p>
            <button onclick="printReport('tanqih')" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium text-sm rounded-xl transition-colors">
                <i class="ri-printer-line"></i> Cetak Laporan
            </button>
        </div>
    </div>
</div>

<script>
    function printReport(type) {
        const weekSelect = document.getElementById('week-select').value;
        if (!weekSelect) {
            alert('Silakan pilih minggu terlebih dahulu.');
            return;
        }

        const [start, end] = weekSelect.split('|');
        const url = `<?= url('/weekly-report/') ?>${type}?start=${start}&end=${end}`;
        window.open(url, '_blank');
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
