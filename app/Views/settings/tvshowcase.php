<?php
// Detect actual server upload limits
function parseIniSize($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $num  = (int) $val;
    switch($last) {
        case 'g': $num *= 1024;
        case 'm': $num *= 1024;
        case 'k': $num *= 1024;
    }
    return $num; // bytes
}
$uploadMaxBytes = parseIniSize(ini_get('upload_max_filesize'));
$postMaxBytes   = parseIniSize(ini_get('post_max_size'));
$effectiveMax   = min($uploadMaxBytes, $postMaxBytes);
$effectiveMaxMB = round($effectiveMax / 1024 / 1024, 0);
$limitLow       = $effectiveMaxMB < 20;
?>
<?php renderHeader($title); ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="<?= url('/') ?>" class="hover:text-indigo-600">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">Settings</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-indigo-600 font-semibold">TV Showcase</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            TV Showcase Settings
        </h2>
        <p class="text-gray-500 text-sm mt-1">Konfigurasi tampilan dan konten TV Showcase.</p>
    </div>

    <!-- Sidebar tabs + content layout -->
    <div class="flex flex-col md:flex-row gap-6">

        <!-- Sidebar nav -->
        <aside class="w-full md:w-44 flex-shrink-0">
            <nav class="flex md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-2 md:pb-0">
                <a href="<?= url('/settings/general') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    General
                </a>
                <a href="<?= url('/settings/tvshowcase') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg bg-indigo-50 text-indigo-700 font-semibold">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    TV Showcase
                </a>
                <a href="<?= url('/settings/quotes') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Motto & Quotes
                </a>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="flex-1 space-y-6">

            <!-- ── Background Music ────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Background Music (BGM)</h3>
                        <p class="text-xs text-gray-500">Audio yang diputar saat TV Showcase aktif.</p>
                    </div>
                </div>
                <div class="px-6 py-5">

                    <!-- Current file status -->
                    <?php if ($bgmExists): ?>
                    <div class="flex items-center gap-3 mb-5 p-3 bg-green-50 border border-green-200 rounded-xl">
                        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-green-800">BGM aktif — bgm.mp3</div>
                            <div class="text-xs text-green-600"><?= $bgmSize ?> &bull; Diperbarui <?= $bgmMtime ?></div>
                        </div>
                        <a href="<?= url('/uploads/bgm.mp3') ?>" target="_blank"
                           class="ml-auto text-xs text-green-700 hover:text-green-900 underline">Putar</a>
                    </div>
                    <?php else: ?>
                    <div class="flex items-center gap-3 mb-5 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <div class="text-sm text-amber-800">Belum ada file BGM. Upload file di bawah.</div>
                    </div>
                    <?php endif; ?>

                    <!-- Server limit warning -->
                    <?php if ($limitLow): ?>
                    <div class="mb-4 flex gap-3 p-4 bg-orange-50 border border-orange-200 rounded-xl">
                        <svg class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <div class="text-sm">
                            <p class="font-semibold text-orange-800 mb-1">Batas upload server: <?= $effectiveMaxMB ?>MB</p>
                            <p class="text-orange-700 text-xs leading-relaxed">
                                Server ini membatasi upload maksimal <strong><?= $effectiveMaxMB ?>MB</strong>.
                                Untuk menaikkan batas, masuk ke <strong>cPanel → MultiPHP INI Editor</strong>,
                                pilih folder aplikasi, lalu set <code class="bg-orange-100 px-1 rounded">upload_max_filesize = 32M</code>
                                dan <code class="bg-orange-100 px-1 rounded">post_max_size = 32M</code>.
                                Atau deploy file <code class="bg-orange-100 px-1 rounded">.user.ini</code> ke root folder.
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Upload form -->
                    <form action="<?= url('/settings/upload-audio') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_token_field() ?>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload File Audio Baru</label>
                        <div id="drop-zone"
                             class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer"
                             onclick="document.getElementById('audioInput').click()"
                             ondragover="event.preventDefault(); this.classList.add('border-indigo-500','bg-indigo-50/50')"
                             ondragleave="this.classList.remove('border-indigo-500','bg-indigo-50/50')"
                             ondrop="handleDrop(event)">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                            </svg>
                            <p id="drop-label" class="text-sm text-gray-500">Klik atau drag-and-drop file audio di sini</p>
                            <p class="text-xs text-gray-400 mt-1">MP3, OGG, WAV, M4A &bull; Maks. <strong><?= $effectiveMaxMB ?>MB</strong> (batas server)</p>
                        </div>
                        <input type="file" name="audio" id="audioInput" accept="audio/mpeg,audio/ogg,audio/wav,audio/mp4,audio/x-m4a,.mp3,.ogg,.wav,.m4a"
                               class="hidden" onchange="updateLabel(this)">

                        <div class="flex justify-end mt-4">
                            <button type="submit" id="uploadBtn" disabled
                                    class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-sm transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Upload & Terapkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── TV Showcase Link ────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 mb-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Link TV Showcase</h3>
                <div class="flex items-center gap-3">
                    <input type="text" readonly value="<?= url('/tvshowcase') ?>"
                           class="flex-1 text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-600 font-mono"
                           onclick="this.select()">
                    <a href="<?= url('/tvshowcase') ?>" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka
                    </a>
                </div>
            </div>

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
                            + Jam
                        </button>
                        <button type="button" onclick="addHourRow('break')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold rounded-lg transition-colors border border-emerald-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            + Istirahat
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
                                            <input type="text" name="label[]" value="<?= htmlspecialchars($row['label']) ?>" class="bg-transparent border-none focus:ring-0 p-0 text-gray-900 font-medium placeholder-gray-300 w-full" placeholder="Ex: Jam 1">
                                        </td>
                                        <td class="py-3 border-y border-gray-100">
                                            <input type="time" name="start[]" value="<?= $row['start'] ?>" class="bg-white border border-gray-200 rounded px-2 py-1 text-xs focus:ring-indigo-500 focus:border-indigo-500 text-gray-600">
                                        </td>
                                        <td class="py-3 border-y border-gray-100">
                                            <input type="time" name="end[]" value="<?= $row['end'] ?>" class="bg-white border border-gray-200 rounded px-2 py-1 text-xs focus:ring-indigo-500 focus:border-indigo-500 text-gray-600">
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
function updateLabel(input) {
    const label   = document.getElementById('drop-label');
    const btn     = document.getElementById('uploadBtn');
    const maxBytes = <?= $effectiveMax ?>;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        if (file.size > maxBytes) {
            label.textContent = `⚠️ ${file.name} (${sizeMB} MB) — melebihi batas server ${<?= $effectiveMaxMB ?>}MB!`;
            label.className = 'text-sm text-red-600 font-semibold';
            btn.disabled = true;
        } else {
            label.textContent = `${file.name} (${sizeMB} MB)`;
            label.className = 'text-sm text-indigo-700 font-semibold';
            btn.disabled = false;
        }
    }
}

function handleDrop(event) {
    event.preventDefault();
    const zone = document.getElementById('drop-zone');
    zone.classList.remove('border-indigo-500', 'bg-indigo-50/50');
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById('audioInput');
        // Transfer dropped file to the input
        const dt = new DataTransfer();
        dt.items.add(files[0]);
        input.files = dt.files;
        updateLabel(input);
    }
}

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
            <input type="text" name="label[]" class="bg-transparent border-none focus:ring-0 p-0 text-gray-900 font-medium placeholder-gray-300 w-full" placeholder="Ex: ${isJam ? 'Jam X' : 'Istirahat X'}">
        </td>
        <td class="py-3 border-y border-gray-100">
            <input type="time" name="start[]" value="" class="bg-white border border-gray-200 rounded px-2 py-1 text-xs focus:ring-indigo-500 focus:border-indigo-500 text-gray-600">
        </td>
        <td class="py-3 border-y border-gray-100">
            <input type="time" name="end[]" value="" class="bg-white border border-gray-200 rounded px-2 py-1 text-xs focus:ring-indigo-500 focus:border-indigo-500 text-gray-600">
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
}
</script>

<?php renderFooter(); ?>
