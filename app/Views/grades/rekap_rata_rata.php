<?php
renderHeader($title ?? 'Rekapitulasi Nilai Rata-rata Kelas');
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="no-print mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="<?= url('/grades') ?>" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition-colors flex items-center gap-1 shrink-0">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <div class="min-w-0">
                <h4 class="text-sm font-bold text-gray-800 truncate"><?= htmlspecialchars($title) ?></h4>
                <p class="text-xs text-gray-400 truncate">Sesi: <?= htmlspecialchars($sessionName ?? '') ?></p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <button onclick="window.print()" class="flex-1 md:flex-initial justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5 whitespace-nowrap">
                <i class="ri-printer-line text-sm"></i> Cetak PDF
            </button>
        </div>
    </div>

    <style>
        .rekap-table {
            border-collapse: collapse;
            width: 100%;
            border: 2px solid #000;
        }
        .rekap-table th, .rekap-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: center;
            vertical-align: middle;
            color: #000;
        }
        .rekap-table th {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        @media print {
            body {
                background-color: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            nav, aside, footer, .no-print {
                display: none !important;
            }
            main {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-area {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>

    <div class="print-area bg-white p-6 shadow-md rounded-2xl border border-gray-100 overflow-x-auto">
        <div class="text-center mb-6">
            <h2 class="text-lg font-bold uppercase tracking-wider mb-2">REKAPITULASI NILAI RATA-RATA KELAS</h2>
        </div>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th class="w-16">No.</th>
                    <th>Kelas</th>
                    <th>Nilai Rata-Rata</th>
                    <th>Ranking</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekap)): ?>
                    <tr>
                        <td colspan="4" class="py-4 text-gray-500 italic">Belum ada data nilai.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $ranking = 1;
                    $prevValue = null;
                    $actualRank = 1;
                    foreach ($rekap as $idx => $r): 
                        $rata = $r['nilai_rata_rata'] !== null ? round($r['nilai_rata_rata'], 2) : 0;
                        $formattedRata = number_format($rata, 2, ',', '.');
                        
                        if ($prevValue !== null && $rata < $prevValue) {
                            $actualRank = $idx + 1;
                        }
                        $prevValue = $rata;
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td><?= $idx + 1 ?></td>
                            <td class="font-bold"><?= htmlspecialchars($r['nama_kelas']) ?></td>
                            <td><?= $formattedRata ?></td>
                            <td><?= $actualRank ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
