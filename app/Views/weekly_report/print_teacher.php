<?php
$bulanId = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$tglStart = ltrim(date('d', strtotime($start)), '0');
$blnStart = $bulanId[date('m', strtotime($start))];
$thnStart = date('Y', strtotime($start));
$tglEnd = ltrim(date('d', strtotime($end)), '0');
$blnEnd = $bulanId[date('m', strtotime($end))];
$thnEnd = date('Y', strtotime($end));

if ($blnStart === $blnEnd && $thnStart === $thnEnd) {
    $periode = "$tglStart-$tglEnd $blnStart $thnStart";
} else {
    $periode = "$tglStart $blnStart $thnStart - $tglEnd $blnEnd $thnEnd";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Mingguan Kehadiran Guru</title>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 2cm; }
            .no-print { display: none !important; }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            line-height: 1.5;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 2cm;
        }
        .header { text-align: center; font-weight: bold; margin-bottom: 20px; line-height: 1.3; font-size: 16px; }
        .sub-header { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: center; }
        td.text-left { text-align: left; }
        .footer-signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-box { text-align: center; width: 250px; }
        .signature-box .date { margin-bottom: 60px; }
        .signature-box .name { font-weight: bold; text-decoration: underline; }
        
        .no-print-btn {
            position: fixed; top: 20px; right: 20px;
            padding: 10px 20px; background: #4f46e5; color: #fff;
            border: none; border-radius: 8px; cursor: pointer;
            font-family: sans-serif; font-weight: bold;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print no-print-btn">Cetak Laporan</button>

    <div class="header">
        LAPORAN MINGGUAN KEHADIRAN GURU<br>
        SPM DARUSSALAM BOGOR<br>
        TAHUN PELAJARAN <?= htmlspecialchars($__currentYearName ?? '') ?>
    </div>

    <div class="sub-header">
        Periode Tanggal <?= $periode ?>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">No</th>
                <th rowspan="2" style="width: 35%;">Nama Guru</th>
                <th rowspan="2" style="width: 10%;">Jumlah Jam Mengajar<br>/ Minggu</th>
                <th colspan="6">Rekapitulasi Ketidakhadiran</th>
                <th rowspan="2" style="width: 10%;">% Kehadiran</th>
            </tr>
            <tr>
                <th style="width: 5%;">S</th>
                <th style="width: 7%;">%</th>
                <th style="width: 5%;">I</th>
                <th style="width: 7%;">%</th>
                <th style="width: 5%;">A</th>
                <th style="width: 7%;">%</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $totalExpected = 0;
            $totalS = 0;
            $totalI = 0;
            $totalA = 0;
            $totalHadir = 0;

            foreach ($report as $r): 
                $totalExpected += $r['expected'];
                $totalS += $r['sakit'];
                $totalI += $r['izin'];
                $totalA += $r['alfa'];
                $totalHadir += $r['hadir'];
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td class="text-left"><?= htmlspecialchars($r['nama']) ?></td>
                <td><?= $r['expected'] ?> Jam</td>
                <td><?= $r['sakit'] ?></td>
                <td><?= number_format($r['pct_s'], 2, ',', '.') ?></td>
                <td><?= $r['izin'] ?></td>
                <td><?= number_format($r['pct_i'], 2, ',', '.') ?></td>
                <td><?= $r['alfa'] ?></td>
                <td><?= number_format($r['pct_a'], 2, ',', '.') ?></td>
                <td><?= number_format($r['pct_hadir'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="2">Jumlah & Persentase jam pelajaran seluruh guru</td>
                <td><?= $totalExpected ?> Jam</td>
                <td><?= $totalS ?></td>
                <td><?= $totalExpected > 0 ? number_format(($totalS/$totalExpected)*100, 2, ',', '.') : '0,00' ?></td>
                <td><?= $totalI ?></td>
                <td><?= $totalExpected > 0 ? number_format(($totalI/$totalExpected)*100, 2, ',', '.') : '0,00' ?></td>
                <td><?= $totalA ?></td>
                <td><?= $totalExpected > 0 ? number_format(($totalA/$totalExpected)*100, 2, ',', '.') : '0,00' ?></td>
                <td><?= $totalExpected > 0 ? number_format((($totalExpected - $totalS - $totalI - $totalA)/$totalExpected)*100, 2, ',', '.') : '0,00' ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 13px; margin-top: 20px;">
        <p><strong>NB:</strong><br>Data diambil dari KMI App, dengan ketentuan:</p>
        <ul style="margin-top: 5px; padding-left: 20px;">
            <li>S = Sakit, I = Izin, A = Alfa</li>
            <li>Persentase dihitung berdasarkan Jumlah Jam Mengajar.</li>
        </ul>
    </div>

    <?php if (!empty($topSubstitutions)): ?>
    <div style="margin-top: 30px;">
        <p style="font-weight:bold;">Laporan Jam Mengganti (Badal) Terbanyak:</p>
        <table style="width: 50%;">
            <thead>
                <tr>
                    <th style="width: 10%;">No</th>
                    <th style="width: 60%;">Nama Guru</th>
                    <th style="width: 30%;">Jumlah Jam</th>
                </tr>
            </thead>
            <tbody>
                <?php $sn = 1; foreach ($topSubstitutions as $sub): ?>
                <tr>
                    <td><?= $sn++ ?></td>
                    <td class="text-left"><?= htmlspecialchars($sub['nama']) ?></td>
                    <td><?= $sub['count'] ?> Jam</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="footer-signatures">
        <div class="signature-box" style="visibility: hidden;">
            <!-- Placeholder for left side if needed -->
        </div>
        <div class="signature-box">
            <div class="date">Kamis, <?= $tglEnd ?> <?= $blnEnd ?> <?= $thnEnd ?><br>Kepala Biro KMI</div>
            <div class="name">______________________</div>
        </div>
    </div>
</body>
</html>
