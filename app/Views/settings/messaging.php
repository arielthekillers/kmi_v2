<?php
$title = "Messaging - Settings";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Messaging
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Pusat kontrol pengiriman pesan WhatsApp.
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button type="button" onclick="openSendModal()"
                class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="ri-send-plane-fill mr-2"></i> Kirim Pesan
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 sm:flex sm:items-center sm:justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Status Antrean Pesan
            </h3>
            
            <div class="mt-3 sm:mt-0 sm:ml-4 sm:flex sm:items-center">
                <form action="" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <div class="relative rounded-md shadow-sm w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 py-2 sm:text-sm border-gray-300 rounded-md" placeholder="Cari pesan/nomor...">
                    </div>
                    
                    <select name="status" class="block w-full sm:w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= (($_GET['status'] ?? '') === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="sent" <?= (($_GET['status'] ?? '') === 'sent') ? 'selected' : '' ?>>Terkirim</option>
                        <option value="failed" <?= (($_GET['status'] ?? '') === 'failed') ? 'selected' : '' ?>>Gagal</option>
                    </select>
                </form>

                <div id="bulkActions" class="ml-2 hidden flex space-x-2">
                    <button type="button" onclick="bulkResendSelected()" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="ri-restart-line mr-1"></i> Kirim Ulang Terpilih
                    </button>
                    <button type="button" onclick="bulkDeleteSelected()" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="ri-delete-bin-line mr-1"></i> Hapus Terpilih
                    </button>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-4">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Penerima</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Isi
                            Pesan</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dikirim Oleh</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu
                            Dibuat</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Tidak ada
                                pesan dalam antrean.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" value="<?= $msg['id'] ?>" class="rowCheckbox focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" onclick="toggleRowCheckbox()">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    #<?= htmlspecialchars($msg['id']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($msg['recipient_number']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"
                                    title="<?= htmlspecialchars($msg['message']) ?>">
                                    <?= htmlspecialchars($msg['message']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($msg['status'] === 'pending'): ?>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    <?php elseif ($msg['status'] === 'sent'): ?>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Terkirim</span>
                                    <?php elseif ($msg['status'] === 'failed'): ?>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                                            title="<?= htmlspecialchars($msg['response']) ?>">Gagal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium <?= ($msg['sender'] === 'System') ? 'bg-gray-100 text-gray-800' : 'bg-indigo-100 text-indigo-800' ?>">
                                        <?= htmlspecialchars($msg['sender'] ?? 'System') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 flex items-center justify-between">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Halaman <span class="font-medium"><?= $page ?></span> dari <span
                                class="font-medium"><?= $totalPages ?></span>
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php 
                                $queryParams = [];
                                if (isset($_GET['q']) && $_GET['q'] !== '') $queryParams['q'] = $_GET['q'];
                                if (isset($_GET['status']) && $_GET['status'] !== '') $queryParams['status'] = $_GET['status'];
                                $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                            ?>
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?><?= $queryString ?>"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?><?= $queryString ?>"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Kirim Pesan -->
<div id="sendModal" class="hidden fixed z-[9999] inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="closeSendModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative overflow-visible">
            <form action="<?= url('/settings/messaging/send') ?>" method="POST" id="sendForm">
                <?= csrf_input() ?>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-2xl">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Kirim Pesan Baru
                            </h3>
                            <div class="mt-4 space-y-4">

                                <div>
                                    <label class="flex items-center mb-2">
                                        <input type="checkbox" name="everyone" id="everyoneCheck" value="1"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            onchange="toggleRecipientInput()">
                                        <span class="ml-2 text-sm text-gray-900 font-medium">Broadcast ke Semua Pengguna
                                            (@everyone)</span>
                                    </label>
                                </div>

                                <div id="recipientContainer">
                                    <label for="recipients" class="block text-sm font-medium text-gray-700">Penerima
                                        (@nama)</label>
                                    <div class="mt-1">
                                        <select id="recipients" name="recipients[]" multiple
                                            placeholder="Ketik nama untuk mencari..." autocomplete="off"></select>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Cari berdasarkan nama pengguna, pengajar, atau
                                        admin.</p>
                                </div>

                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Isi
                                        Pesan</label>

                                    <!-- Custom WA Editor Toolbar -->
                                    <div class="border border-gray-300 rounded-md overflow-hidden shadow-sm">
                                        <div
                                            class="flex flex-wrap items-center gap-1 bg-gray-50 border-b border-gray-300 p-1.5">
                                            <button type="button" onclick="formatWaText('bold')"
                                                class="p-1.5 text-gray-700 hover:bg-gray-200 hover:text-indigo-600 rounded transition-colors"
                                                title="Tebal (Bold)"><i class="ri-bold"></i></button>
                                            <button type="button" onclick="formatWaText('italic')"
                                                class="p-1.5 text-gray-700 hover:bg-gray-200 hover:text-indigo-600 rounded transition-colors"
                                                title="Miring (Italic)"><i class="ri-italic"></i></button>
                                            <button type="button" onclick="formatWaText('strikethrough')"
                                                class="p-1.5 text-gray-700 hover:bg-gray-200 hover:text-indigo-600 rounded transition-colors"
                                                title="Coret (Strikethrough)"><i class="ri-strikethrough"></i></button>
                                            <div class="w-px h-5 bg-gray-300 mx-1"></div>
                                            <button type="button" onclick="formatWaText('monospace')"
                                                class="p-1.5 text-gray-700 hover:bg-gray-200 hover:text-indigo-600 rounded transition-colors"
                                                title="Monospace (Blok Kode)"><i class="ri-code-box-line"></i></button>
                                            <button type="button" onclick="formatWaText('inline_code')"
                                                class="p-1.5 text-gray-700 hover:bg-gray-200 hover:text-indigo-600 rounded transition-colors"
                                                title="Kode Inline"><i class="ri-code-view"></i></button>
                                            <div class="w-px h-5 bg-gray-300 mx-1"></div>
                                            <button type="button" onclick="formatWaText('ul')"
                                                class="p-1.5 text-gray-700 hover:bg-gray-200 hover:text-indigo-600 rounded transition-colors"
                                                title="Daftar Berpoin"><i class="ri-list-unordered"></i></button>
                                            <button type="button" onclick="formatWaText('ol')"
                                                class="p-1.5 text-gray-700 hover:bg-gray-200 hover:text-indigo-600 rounded transition-colors"
                                                title="Daftar Bernomor"><i class="ri-list-ordered"></i></button>
                                            <div class="w-px h-5 bg-gray-300 mx-1"></div>
                                            <button type="button" onclick="formatWaText('quote')"
                                                class="p-1.5 text-gray-700 hover:bg-gray-200 hover:text-indigo-600 rounded transition-colors"
                                                title="Tanda Kutip"><i class="ri-double-quotes-l"></i></button>
                                        </div>
                                        <textarea id="message" name="message" rows="6" required
                                            class="block w-full sm:text-sm border-0 p-3 focus:ring-0 focus:outline-none"
                                            placeholder="Tulis pesan Anda di sini..."></textarea>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Gunakan toolbar di atas untuk memformat teks
                                        (tebal, miring, dll) agar sesuai dengan tampilan WhatsApp.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Kirim
                    </button>
                    <button type="button" onclick="closeSendModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let tomSelectInstance = null;

    function openSendModal() {
        document.getElementById('sendModal').classList.remove('hidden');

        if (!tomSelectInstance) {
            tomSelectInstance = new TomSelect('#recipients', {
                valueField: 'id',
                labelField: 'nama',
                searchField: 'nama',
                load: function (query, callback) {
                    if (!query.length) return callback();
                    fetch(`<?= url('/api/users/search?q=') ?>` + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(json => {
                            callback(json);
                        }).catch(() => {
                            callback();
                        });
                },
                render: {
                    option: function (item, escape) {
                        return `<div class="py-2 flex">
                            <div>
                                <div class="mb-1">
                                    <span class="font-medium">${escape(item.nama)}</span>
                                </div>
                                <div class="text-xs text-gray-500">${escape(item.id)}</div>
                            </div>
                        </div>`;
                    },
                    item: function (item, escape) {
                        return `<div>${escape(item.nama)}</div>`;
                    }
                }
            });
        }
    }

    function closeSendModal() {
        document.getElementById('sendModal').classList.add('hidden');
        if (tomSelectInstance) {
            tomSelectInstance.clear();
        }
        document.getElementById('sendForm').reset();
        toggleRecipientInput();
    }

    function toggleRecipientInput() {
        const isEveryone = document.getElementById('everyoneCheck').checked;
        const container = document.getElementById('recipientContainer');
        if (isEveryone) {
            container.style.display = 'none';
            if (tomSelectInstance) tomSelectInstance.clear();
        } else {
            container.style.display = 'block';
        }
    }

    function formatWaText(type) {
        const textarea = document.getElementById('message');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selectedText = text.substring(start, end);
        const placeholder = 'teks';
        const isSelected = selectedText.length > 0;

        let replacement = '';
        let innerText = isSelected ? selectedText : placeholder;
        let selectStart = start;
        let selectEnd = start;

        switch (type) {
            case 'bold':
                replacement = `*${innerText}*`;
                selectStart = start + 1;
                selectEnd = selectStart + innerText.length;
                break;
            case 'italic':
                replacement = `_${innerText}_`;
                selectStart = start + 1;
                selectEnd = selectStart + innerText.length;
                break;
            case 'strikethrough':
                replacement = `~${innerText}~`;
                selectStart = start + 1;
                selectEnd = selectStart + innerText.length;
                break;
            case 'monospace':
                replacement = "```" + innerText + "```";
                selectStart = start + 3;
                selectEnd = selectStart + innerText.length;
                break;
            case 'inline_code':
                replacement = `\`${innerText}\``;
                selectStart = start + 1;
                selectEnd = selectStart + innerText.length;
                break;
            case 'ul':
                if (isSelected) {
                    replacement = selectedText.split('\n').map(line => `- ${line}`).join('\n');
                    selectStart = start;
                    selectEnd = start + replacement.length;
                } else {
                    replacement = `- ${placeholder}`;
                    selectStart = start + 2;
                    selectEnd = selectStart + placeholder.length;
                }
                break;
            case 'ol':
                if (isSelected) {
                    replacement = selectedText.split('\n').map((line, i) => `${i + 1}. ${line}`).join('\n');
                    selectStart = start;
                    selectEnd = start + replacement.length;
                } else {
                    replacement = `1. ${placeholder}`;
                    selectStart = start + 3;
                    selectEnd = selectStart + placeholder.length;
                }
                break;
            case 'quote':
                if (isSelected) {
                    replacement = selectedText.split('\n').map(line => `> ${line}`).join('\n');
                    selectStart = start;
                    selectEnd = start + replacement.length;
                } else {
                    replacement = `> ${placeholder}`;
                    selectStart = start + 2;
                    selectEnd = selectStart + placeholder.length;
                }
                break;
        }

        textarea.value = text.substring(0, start) + replacement + text.substring(end);
        textarea.focus();
        textarea.setSelectionRange(selectStart, selectEnd);
    }

    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.rowCheckbox');
        for (let i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
        toggleBulkDeleteButton();
    }

    function toggleRowCheckbox() {
        const checkboxes = document.querySelectorAll('.rowCheckbox');
        const selectAll = document.getElementById('selectAll');
        let allChecked = true;
        for (let i = 0; i < checkboxes.length; i++) {
            if (!checkboxes[i].checked) {
                allChecked = false;
                break;
            }
        }
        selectAll.checked = checkboxes.length > 0 && allChecked;
        toggleBulkDeleteButton();
    }

    function toggleBulkDeleteButton() {
        const checkboxes = document.querySelectorAll('.rowCheckbox:checked');
        const container = document.getElementById('bulkActions');
        if (checkboxes.length > 0) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function bulkResendSelected() {
        const checkboxes = document.querySelectorAll('.rowCheckbox:checked');
        if (checkboxes.length === 0) return;

        const ids = Array.from(checkboxes).map(cb => cb.value);

        Swal.fire({
            title: 'Kirim ulang pesan?',
            text: `${ids.length} pesan akan diubah statusnya menjadi antrean (pending) untuk dikirim ulang.`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, kirim ulang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                ids.forEach(id => formData.append('ids[]', id));

                fetch('<?= url("/settings/messaging/bulk-resend") ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat mengantrekan pesan', 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
                });
            }
        });
    }

    function bulkDeleteSelected() {
        const checkboxes = document.querySelectorAll('.rowCheckbox:checked');
        if (checkboxes.length === 0) return;

        const ids = Array.from(checkboxes).map(cb => cb.value);

        Swal.fire({
            title: 'Hapus pesan terpilih?',
            text: `${ids.length} pesan akan dihapus dari antrean dan tidak akan dikirim.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus semua!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                ids.forEach(id => formData.append('ids[]', id));

                fetch('<?= url("/settings/messaging/bulk-delete") ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Terhapus!',
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat menghapus pesan', 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
                });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>