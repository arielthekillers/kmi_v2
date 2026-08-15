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
    <title>Laporan Mingguan Kehadiran Santri</title>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 0.4cm 0.6cm; }
            .no-print { display: none !important; }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0.4cm 0.6cm;
        }
        .header { text-align: center; font-weight: bold; margin-bottom: 12px; line-height: 1.3; font-size: 15px; }
        .sub-header { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: center; }
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
        LAPORAN MINGGUAN KEHADIRAN SANTRI<br>
        PONDOK PESANTREN DARUSSALAM BOGOR<br>
        TAHUN PELAJARAN <?= htmlspecialchars($__currentYearName ?? '') ?>
    </div>

    <div class="sub-header">
        Periode Tanggal : <?= $periode ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kelas</th>
                <th>Jumlah<br>Hari<br>Efektif</th>
                <th>Jumlah<br>Santri</th>
                <th>Alfa</th>
                <th>Persentase</th>
                <th>Izin</th>
                <th>Persentase</th>
                <th>Sakit</th>
                <th>Persentase</th>
                <th>Keseluruhan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $totalSantri = 0;
            $totalAlfa = 0;
            $totalIzin = 0;
            $totalSakit = 0;

            foreach ($report as $r): 
                $totalSantri += $r['jumlah_santri'];
                $totalAlfa += $r['alfa'];
                $totalIzin += $r['izin'];
                $totalSakit += $r['sakit'];
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($r['kelas']) ?></td>
                <td><?= $r['hari_efektif'] ?></td>
                <td><?= $r['jumlah_santri'] ?></td>
                <td><?= $r['alfa'] ?></td>
                <td><?= number_format($r['pct_a'], 2, ',', '.') ?></td>
                <td><?= $r['izin'] ?></td>
                <td><?= number_format($r['pct_i'], 2, ',', '.') ?></td>
                <td><?= $r['sakit'] ?></td>
                <td><?= number_format($r['pct_s'], 2, ',', '.') ?></td>
                <td><?= number_format($r['pct_hadir'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="3">Jumlah rata-rata kehadiran santri</td>
                <td><?= $totalSantri ?></td>
                <td><?= $totalAlfa ?></td>
                <td><?= $totalSantri > 0 ? number_format(($totalAlfa/($totalSantri*6))*100, 2, ',', '.') : '0,00' ?></td>
                <td><?= $totalIzin ?></td>
                <td><?= $totalSantri > 0 ? number_format(($totalIzin/($totalSantri*6))*100, 2, ',', '.') : '0,00' ?></td>
                <td><?= $totalSakit ?></td>
                <td><?= $totalSantri > 0 ? number_format(($totalSakit/($totalSantri*6))*100, 2, ',', '.') : '0,00' ?></td>
                <td><?= $totalSantri > 0 ? number_format((($totalSantri*6 - $totalAlfa - $totalIzin - $totalSakit)/($totalSantri*6))*100, 2, ',', '.') : '0,00' ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
        <div style="width: 45%;">
            <p style="font-weight: bold; margin-bottom: 5px;">Kelas-kelas dengan persentase kehadiran tertinggi</p>
            <ol style="margin-top: 0; padding-left: 20px;">
                <?php foreach ($top3 as $t): ?>
                    <li>Kelas <?= htmlspecialchars($t['kelas']) ?> &nbsp;&nbsp;&nbsp; <?= number_format($t['pct_hadir'], 2, ',', '.') ?>%</li>
                <?php endforeach; ?>
            </ol>
            
            <p style="font-weight: bold; margin-top: 15px; margin-bottom: 5px;">Kelas dengan persentase kehadiran terendah</p>
            <ol style="margin-top: 0; padding-left: 20px;">
                <?php foreach ($bottom3 as $b): ?>
                    <li>Kelas <?= htmlspecialchars($b['kelas']) ?> &nbsp;&nbsp;&nbsp; <?= number_format($b['pct_hadir'], 2, ',', '.') ?>%</li>
                <?php endforeach; ?>
            </ol>
        </div>
        <div style="width: 45%;">
            <table style="width: 100%;">
                <thead>
                    <tr><th colspan="2">Jumlah Keseluruhan</th></tr>
                </thead>
                <tbody>
                    <tr><td style="text-align:left;">Total Santri</td><td><?= $totalSantri ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer-signatures">
        <div class="signature-box">
            <div class="date">Kepala Biro Pengajaran</div>
            <div class="name" style="margin-top: 60px;">______________________</div>
        </div>
        <div class="signature-box">
            <div class="date">Kamis, <?= $tglEnd ?> <?= $blnEnd ?> <?= $thnEnd ?><br>Kepala Bidang PBM</div>
            <div class="name" style="margin-top: 60px;">______________________</div>
        </div>
    </div>
</body>
</html>
