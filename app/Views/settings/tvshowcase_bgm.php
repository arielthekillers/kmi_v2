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
            <span class="font-medium text-gray-700">TV Showcase</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-indigo-600 font-semibold">BGM</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                 <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            Background Music Settings
        </h2>
        <p class="text-gray-500 text-sm mt-1">Kelola musik latar untuk TV Showcase.</p>
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
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg bg-indigo-50 text-indigo-700 font-semibold">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                    Background Music (BGM)
                </a>
                <a href="<?= url('/settings/tv/hours') ?>"
                   class="whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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

            <!-- ── Background Music ────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Link YouTube BGM</h3>
                        <p class="text-xs text-gray-500">Gunakan audio dari video YouTube untuk musik latar.</p>
                    </div>
                </div>
                <div class="px-6 py-5 border-b border-gray-100">
                    <form action="<?= url('/settings/tv/bgm-youtube') ?>" method="POST">
                        <?= csrf_token_field() ?>
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">URL Video YouTube</label>
                                <input type="text" name="youtube_url" value="<?= htmlspecialchars($bgmYoutube) ?>" 
                                       placeholder="https://www.youtube.com/watch?v=..."
                                       class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                            </div>
                            <div class="self-end">
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    Simpan Link
                                </button>
                            </div>
                        </div>
                        <p class="mt-2 text-[10px] text-gray-400">Jika link YouTube diisi, maka file audio (.mp3) di bawah akan diabaikan.</p>
                    </form>
                </div>

                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Fallback: File Audio BGM</h3>
                        <p class="text-xs text-gray-500">File audio cadangan jika link YouTube tidak diisi.</p>
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
</script>

<?php renderFooter(); ?>
