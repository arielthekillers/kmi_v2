<?php renderHeader($title); ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="<?= url('/') ?>" class="hover:text-indigo-600">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">Settings</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">TV Showcase</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-indigo-600 font-semibold">Jam Pelajaran</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                 <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            Jam Pelajaran Settings
        </h2>
        <p class="text-gray-500 text-sm mt-1">Atur jadwal jam belajar dan istirahat untuk TV Showcase.</p>
    </div>

    <!-- Sidebar tabs + content layout -->
    <div class="flex flex-col md:flex-row gap-6">

        <!-- Sidebar nav -->
        <aside class="w-full md:w-56 flex-shrink-0">
            <nav class="flex md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-2 md:pb-0">
                <a href="<?= url('/settings/general') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    General
                </a>
                <div class="mt-4 mb-2 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden md:block">TV Showcase</div>
                <a href="<?= url('/settings/tv/bgm') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                    Background Music (BGM)
                </a>
                <a href="<?= url('/settings/tv/hours') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg bg-indigo-50 text-indigo-700 font-semibold">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Jam Pelajaran
                </a>
                <a href="<?= url('/settings/tv/quotes') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Quotes
                </a>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="flex-1 space-y-6">

            <!-- ── Jam Pelajaran ───────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Manajemen Jam Pelajaran</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Atur jadwal jam belajar dan istirahat untuk TV Showcase</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="addHourRow('jam')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold rounded-lg transition-colors border border-indigo-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Jam
                        </button>
                        <button type="button" onclick="addHourRow('break')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold rounded-lg transition-colors border border-emerald-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Istirahat
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <form action="<?= url('/settings/update-hours') ?>" method="POST">
                        <?= csrf_token_field() ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm border-separate border-spacing-y-2" id="hoursTable">
                                <thead>
                                    <tr class="text-gray-400 font-medium">
                                        <th class="pb-2 pl-2">Tipe</th>
                                        <th class="pb-2">Label</th>
                                        <th class="pb-2">Mulai</th>
                                        <th class="pb-2">Selesai</th>
                                        <th class="pb-2">Value/Urutan</th>
                                        <th class="pb-2 text-right pr-2">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hoursConfig as $index => $row): ?>
                                    <tr class="bg-gray-50/50 border border-gray-100 rounded-xl group transition-all hover:bg-white hover:shadow-sm">
                                        <td class="py-3 pl-3 rounded-l-xl border-y border-l border-gray-100">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $row['type'] === 'jam' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                                <?= $row['type'] ?>
                                            </span>
                                            <input type="hidden" name="type[]" value="<?= $row['type'] ?>">
                                        </td>
                                        <td class="py-3 border-y border-gray-100">
                                            <input type="text" name="label[]" value="<?= htmlspecialchars($row['label']) ?>" class="bg-transparent border-none focus:ring-0 p-0 text-gray-900 font-medium placeholder-gray-300 w-32" placeholder="Ex: Jam 1">
                                        </td>
                                        <td class="py-3 border-y border-gray-100">
                                            <input type="time" name="start[]" value="<?= $row['start'] ?>" class="bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:ring-indigo-500 focus:border-indigo-500 text-gray-600 w-24">
                                        </td>
                                        <td class="py-3 border-y border-gray-100">
                                            <input type="time" name="end[]" value="<?= $row['end'] ?>" class="bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:ring-indigo-500 focus:border-indigo-500 text-gray-600 w-24">
                                        </td>
                                        <td class="py-3 border-y border-gray-100">
                                            <?php if ($row['type'] === 'jam'): ?>
                                            <input type="number" name="value[]" value="<?= $row['value'] ?>" class="bg-white border border-gray-200 rounded w-16 px-2 py-1 text-xs focus:ring-indigo-500 text-gray-600" placeholder="Urutan">
                                            <?php else: ?>
                                            <select name="value[]" class="bg-white border border-gray-200 rounded px-2 py-1 text-xs focus:ring-indigo-500 text-gray-600">
                                                <option value="istirahat1" <?= $row['value'] === 'istirahat1' ? 'selected' : '' ?>>Istirahat I</option>
                                                <option value="istirahat2" <?= $row['value'] === 'istirahat2' ? 'selected' : '' ?>>Istirahat II</option>
                                                <option value="dzuhur" <?= $row['value'] === 'dzuhur' ? 'selected' : '' ?>>Istirahat Sholat/Makan</option>
                                            </select>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 pr-3 text-right rounded-r-xl border-y border-r border-gray-100">
                                            <button type="button" onclick="this.closest('tr').remove()" class="text-gray-400 hover:text-red-500 p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                Simpan Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div><!-- /main content -->
    </div><!-- /layout -->

</main>

<script>
function initTimePickers(parent = document) {
    parent.querySelectorAll('input[type="time"]').forEach(el => {
        flatpickr(el, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            disableMobile: "true" // Force flatpickr on mobile too for consistency
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTimePickers();
});

function addHourRow(type) {
    const table = document.getElementById('hoursTable').getElementsByTagName('tbody')[0];
    const row = document.createElement('tr');
    row.className = 'bg-gray-50/50 border border-gray-100 rounded-xl group transition-all hover:bg-white hover:shadow-sm';
    
    const isJam = type === 'jam';
    const tagClass = isJam ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700';
    
    let valueInput = '';
    if (isJam) {
        valueInput = `<input type="number" name="value[]" value="1" class="bg-white border border-gray-200 rounded w-16 px-2 py-1 text-xs focus:ring-indigo-500 text-gray-600" placeholder="Urutan">`;
    } else {
        valueInput = `
            <select name="value[]" class="bg-white border border-gray-200 rounded px-2 py-1 text-xs focus:ring-indigo-500 text-gray-600">
                <option value="istirahat1">Istirahat I</option>
                <option value="istirahat2">Istirahat II</option>
                <option value="dzuhur">Istirahat Sholat/Makan</option>
            </select>`;
    }

    row.innerHTML = `
        <td class="py-3 pl-3 rounded-l-xl border-y border-l border-gray-100">
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${tagClass}">${type}</span>
            <input type="hidden" name="type[]" value="${type}">
        </td>
        <td class="py-3 border-y border-gray-100">
            <input type="text" name="label[]" class="bg-transparent border-none focus:ring-0 p-0 text-gray-900 font-medium placeholder-gray-300 w-32" placeholder="Ex: ${isJam ? 'Jam X' : 'Istirahat X'}">
        </td>
        <td class="py-3 border-y border-gray-100">
            <input type="time" name="start[]" value="" class="bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:ring-indigo-500 focus:border-indigo-500 text-gray-600 w-24">
        </td>
        <td class="py-3 border-y border-gray-100">
            <input type="time" name="end[]" value="" class="bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:ring-indigo-500 focus:border-indigo-500 text-gray-600 w-24">
        </td>
        <td class="py-3 border-y border-gray-100">
            ${valueInput}
        </td>
        <td class="py-3 pr-3 text-right rounded-r-xl border-y border-r border-gray-100">
            <button type="button" onclick="this.closest('tr').remove()" class="text-gray-400 hover:text-red-500 p-1 opacity-100 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;
    table.appendChild(row);
    initTimePickers(row); // Initialize Flatpickr on the new row
}
</script>

<?php renderFooter(); ?>
