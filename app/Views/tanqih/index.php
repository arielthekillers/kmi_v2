<?php renderHeader($title); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Tanqih Idad
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Verifikasi persiapan mengajar guru sebelum masuk kelas.
                <?php if ($isPiketToday): ?>
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Anda Petugas Piket Hari Ini
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
            </form>
        </div>
    </div>

    <!-- Stats & Filters -->
    <div class="mb-6 space-y-4">
        <!-- Search & Tabs -->
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <!-- Search -->
            <div class="w-full sm:w-1/2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="searchInput" placeholder="Cari nama pengajar, mapel, atau kelas..." 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out">
            </div>
            
            <!-- Tabs -->
            <div class="w-full sm:w-auto flex bg-gray-100 p-1 rounded-lg">
                <button onclick="filterList('pending')" id="tab-pending" class="flex-1 sm:flex-none px-4 py-2 text-sm font-medium rounded-md shadow-sm bg-white text-gray-900 transition-all">
                    Belum (<span id="count-pending">0</span>)
                </button>
                <button onclick="filterList('verified')" id="tab-verified" class="flex-1 sm:flex-none px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-700 transition-all">
                    Sudah (<span id="count-verified">0</span>)
                </button>
                <button onclick="filterList('all')" id="tab-all" class="flex-1 sm:flex-none px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-700 transition-all">
                    Semua
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($dailySchedule)): ?>
        <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada jadwal</h3>
            <p class="mt-1 text-sm text-gray-500">Tidak ada kegiatan belajar mengajar pada hari ini.</p>
        </div>
    <?php else: ?>

        <div class="bg-white shadow overflow-hidden rounded-md border border-gray-200">
            <ul role="list" class="divide-y divide-gray-200">
                <?php foreach ($dailySchedule as $item): 
                    $isVerified = $item['is_verified'];
                    $isJustified = $isVerified && isset($item['verification']['status']) && $item['verification']['status'] === 'justified';
                    $timestamp = $isVerified ? ($item['verification']['timestamp'] ?? 0) : 0;
                ?>
                <li class="bg-white hover:bg-gray-50 transition-colors schedule-item" 
                    data-name="<?= strtolower(htmlspecialchars($item['teacher_name'] . ' ' . $item['subject_name'] . ' ' . $item['kelas_name'])) ?>"
                    data-status="<?= $isVerified ? 'verified' : 'pending' ?>"
                    data-timestamp="<?= $timestamp ?>"
                    data-original-order="<?= $item['hour'] * 1000 + $item['kelas_id'] ?>">
                    <div class="px-3 py-3 sm:px-6 flex items-center justify-between gap-3">
                        <div class="flex items-center min-w-0 gap-3">
                            <!-- Time Badge -->
                            <div class="flex-shrink-0 flex flex-col items-center justify-center h-12 w-12 rounded-lg bg-indigo-100 text-indigo-700 font-bold border border-indigo-200">
                                <span class="text-xs uppercase">Jam</span>
                                <span class="text-lg leading-none"><?= $item['hour'] ?></span>
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-indigo-600 truncate"><?= htmlspecialchars($item['teacher_name']) ?></p>
                                <p class="flex items-center flex-wrap text-sm text-gray-500">
                                    <span class="font-semibold text-gray-800 mr-2"><?= htmlspecialchars($item['subject_name']) ?></span>
                                    <span class="text-gray-400 whitespace-nowrap">• <?= htmlspecialchars($item['kelas_name']) ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <?php if ($isVerified): ?>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Oleh <?= htmlspecialchars($item['verifier_name']) ?></p>
                                    <p class="text-xs text-gray-400">
                                        <?= date('H:i', $item['verification']['timestamp']) ?>
                                    </p>
                                </div>
                                <?php if ($canVerify): ?>
                                <form action="<?= url('/tanqih/verify') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan verifikasi ini? Data akan dihapus dari laporan.');">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="date" value="<?= $selectedDate ?>">
                                    <input type="hidden" name="kelas_id" value="<?= $item['kelas_id'] ?>">
                                    <input type="hidden" name="hour" value="<?= $item['hour'] ?>">
                                    <input type="hidden" name="pengajar_id" value="<?= $item['pengajar_id'] ?>">
                                    <input type="hidden" name="action" value="unverify">
                                    <input type="hidden" name="ajax" value="1">
                                    <button type="button" onclick="verifyAsync(this.form)" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full <?= $isJustified ? 'text-yellow-700 bg-yellow-100 font-semibold' : 'text-green-700 bg-green-100' ?> hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer transition-colors" title="Batalkan Verifikasi?">
                                        <svg class="mr-1.5 h-2 w-2 <?= $isJustified ? 'text-yellow-500' : 'text-green-500' ?>" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        <?= $isJustified ? 'Justifikasi' : 'Sudah' ?>
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full <?= $isJustified ? 'text-yellow-700 bg-yellow-100 font-semibold' : 'text-green-700 bg-green-100' ?>">
                                        <svg class="mr-1.5 h-2 w-2 <?= $isJustified ? 'text-yellow-500' : 'text-green-500' ?>" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        <?= $isJustified ? 'Justifikasi' : 'Sudah' ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($canVerify): ?>
                                <form id="form-verify-<?= $item['kelas_id'] ?>-<?= $item['hour'] ?>" action="<?= url('/tanqih/verify') ?>" method="POST">
                                    <?= csrf_token_field() ?>
                                    <input type="hidden" name="date" value="<?= $selectedDate ?>">
                                    <input type="hidden" name="kelas_id" value="<?= $item['kelas_id'] ?>">
                                    <input type="hidden" name="hour" value="<?= $item['hour'] ?>">
                                    <input type="hidden" name="pengajar_id" value="<?= $item['pengajar_id'] ?>">
                                    <input type="hidden" name="action" value="verify">
                                    <input type="hidden" name="status" id="status-<?= $item['kelas_id'] ?>-<?= $item['hour'] ?>" value="verified">
                                    <input type="hidden" name="ajax" value="1">
                                    <button type="button" 
                                        onclick="openVerifyModal(<?= htmlspecialchars(json_encode($item['teacher_name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($item['subject_name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($item['kelas_name']), ENT_QUOTES) ?>, '<?= $item['hour'] ?>', 'form-verify-<?= $item['kelas_id'] ?>-<?= $item['hour'] ?>', 'status-<?= $item['kelas_id'] ?>-<?= $item['hour'] ?>')"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                        <svg class="h-5 w-5 mr-2 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Verifikasi
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full text-gray-500 bg-gray-100 italic">
                                        Belum
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php endif; ?>

</main>

<!-- Verification Modal -->
<div id="verifyModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeVerifyModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Konfirmasi Verifikasi
                    </h3>
                    <div class="mt-4 bg-gray-50 rounded-lg p-4 space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between border-b pb-2">
                            <span class="font-medium">Pengajar:</span>
                            <span class="text-gray-900 font-bold" id="modal-pengajar"></span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="font-medium">Mata Pelajaran:</span>
                            <span class="text-gray-900" id="modal-mapel"></span>
                        </div>
                        <div class="flex justify-between border-b py-2">
                            <span class="font-medium">Kelas:</span>
                            <span class="text-gray-900" id="modal-kelas"></span>
                        </div>
                        <div class="flex justify-between pt-2">
                            <span class="font-medium">Jam Ke:</span>
                            <span class="text-gray-900 font-bold" id="modal-jam"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                <button type="button" onclick="submitVerify('verified')" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                    Verifikasi
                </button>
                <button type="button" onclick="submitVerify('justified')" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:w-auto sm:text-sm">
                    Justifikasi
                </button>
                <button type="button" onclick="closeVerifyModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentVerifyFormId = null;
let currentStatusInputId = null;

function openVerifyModal(pengajar, mapel, kelas, jam, formId, statusInputId) {
    document.getElementById('modal-pengajar').textContent = pengajar;
    document.getElementById('modal-mapel').textContent = mapel;
    document.getElementById('modal-kelas').textContent = kelas;
    document.getElementById('modal-jam').textContent = jam;
    currentVerifyFormId = formId;
    currentStatusInputId = statusInputId;
    
    document.getElementById('verifyModal').classList.remove('hidden');
}

function closeVerifyModal() {
    document.getElementById('verifyModal').classList.add('hidden');
    currentVerifyFormId = null;
    currentStatusInputId = null;
}

function submitVerify(status) {
    if (currentVerifyFormId && currentStatusInputId) {
        document.getElementById(currentStatusInputId).value = status;
        const form = document.getElementById(currentVerifyFormId);
        verifyAsync(form);
        closeVerifyModal();
    }
}
</script>

<script>
    function filterList(status) {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const listContainer = document.querySelector('ul[role="list"]');
        const items = Array.from(document.querySelectorAll('.schedule-item'));
        
        // Sorting Logic
        if (status === 'verified') {
            items.sort((a, b) => {
                return b.getAttribute('data-timestamp') - a.getAttribute('data-timestamp');
            });
        } else {
            items.sort((a, b) => {
                return a.getAttribute('data-original-order') - b.getAttribute('data-original-order');
            });
        }
        
        items.forEach(item => listContainer.appendChild(item));

        let countPending = 0;
        let countVerified = 0;

        document.querySelectorAll('button[id^="tab-"]').forEach(btn => {
            btn.className = 'flex-1 sm:flex-none px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-700 transition-all';
        });
        
        const activeBtn = document.getElementById('tab-' + status);
        if(activeBtn) {
            activeBtn.className = 'flex-1 sm:flex-none px-4 py-2 text-sm font-medium rounded-md shadow-sm bg-white text-gray-900 transition-all';
        }

        items.forEach(item => {
            const name = item.getAttribute('data-name');
            const itemStatus = item.getAttribute('data-status');
            
            if (itemStatus === 'pending') countPending++;
            if (itemStatus === 'verified') countVerified++;

            let matchesSearch = name.includes(search);
            let matchesStatus = (status === 'all') || (itemStatus === status);

            if (matchesSearch && matchesStatus) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });

        document.getElementById('count-pending').textContent = countPending;
        document.getElementById('count-verified').textContent = countVerified;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const items = document.querySelectorAll('.schedule-item');
        let p = 0, v = 0;
        items.forEach(i => {
            if(i.getAttribute('data-status') === 'pending') p++;
            else v++;
        });
        document.getElementById('count-pending').textContent = p;
        document.getElementById('count-verified').textContent = v;

        filterList('pending');

        document.getElementById('searchInput').addEventListener('input', () => {
            let activeTab = 'all';
            if (document.getElementById('tab-pending').classList.contains('bg-white')) activeTab = 'pending';
            if (document.getElementById('tab-verified').classList.contains('bg-white')) activeTab = 'verified';
            filterList(activeTab);
        });
    });

    async function verifyAsync(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button');
        const originalContent = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        try {
            const response = await fetch('<?= url('/tanqih/verify') ?>', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                const newStatus = result.action === 'verify' ? 'verified' : 'pending';
                const containerLi = form.closest('li');
                
                containerLi.setAttribute('data-status', newStatus);

                const actionContainer = form.closest('div'); 
                
                if (result.action === 'verify') {
                    const isJustified = result.data.status === 'justified';
                    const colorClass = isJustified ? 'text-yellow-700 bg-yellow-100 font-semibold' : 'text-green-700 bg-green-100';
                    const iconColor = isJustified ? 'text-yellow-500' : 'text-green-500';
                    const text = isJustified ? 'Justifikasi' : 'Sudah';

                    const csrfToken = document.querySelector('input[name="csrf_token"]') ? document.querySelector('input[name="csrf_token"]').value : '';

                    const newHtml = `
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Oleh ${result.data.verifier_name}</p>
                        <p class="text-xs text-gray-400">${result.data.timestamp}</p>
                    </div>
                    <form action="<?= url('/tanqih/verify') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="${csrfToken}">
                        <input type="hidden" name="date" value="${formData.get('date')}">
                        <input type="hidden" name="kelas_id" value="${formData.get('kelas_id')}">
                        <input type="hidden" name="hour" value="${formData.get('hour')}">
                        <input type="hidden" name="pengajar_id" value="${formData.get('pengajar_id')}">
                        <input type="hidden" name="action" value="unverify">
                        <input type="hidden" name="ajax" value="1">
                        <button type="button" onclick="verifyAsync(this.form)" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full ${colorClass} hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer transition-colors" title="Batalkan Verifikasi?">
                             <svg class="mr-1.5 h-2 w-2 ${iconColor}" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                             ${text}
                        </button>
                    </form>
                    `;
                    actionContainer.innerHTML = newHtml;
                    
                } else {
                    location.reload(); 
                    return;
                }

                let activeTab = 'all';
                if (document.getElementById('tab-pending').classList.contains('bg-white')) activeTab = 'pending';
                if (document.getElementById('tab-verified').classList.contains('bg-white')) activeTab = 'verified';
                
                filterList(activeTab);

            } else {
                alert(result.message || 'Gagal memproses.');
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            }

        } catch (e) {
            console.error(e);
            alert('Terjadi kesalahan jaringan.');
            submitBtn.innerHTML = originalContent;
            submitBtn.disabled = false;
        }
    }
</script>

<?php renderFooter(); ?>
