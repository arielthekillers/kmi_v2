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
    <title>Laporan Mingguan Tanqih Idad Guru</title>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 2cm; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
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
        .sub-header { font-weight: bold; margin-bottom: 20px; }
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
        .stats-box { width: 50%; margin: 0 auto 30px auto; }
        .stats-box table th, .stats-box table td { padding: 4px 8px; font-size: 14px; }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print no-print-btn">Cetak Laporan</button>

    <div class="header">
        LAPORAN MINGGUAN TANQIH IDAD GURU<br>
        PONDOK PESANTREN DARUSSALAM BOGOR<br>
        TAHUN PELAJARAN <?= htmlspecialchars($__currentYearName ?? '') ?>
    </div>

    <div class="sub-header" style="text-align: center;">
        Periode Tanggal : <?= $periode ?>
    </div>

    <div class="stats-box">
        <p style="font-weight: bold; text-align: center; margin-bottom: 10px;">Statistik Persentase Tanqih</p>
        <table>
            <thead>
                <tr>
                    <th>Persentase</th>
                    <th>Jumlah Guru</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>100%</td><td><?= $stats['100'] ?> Orang</td></tr>
                <tr><td>76% - 99%</td><td><?= $stats['76_99'] ?> Orang</td></tr>
                <tr><td>51% - 75%</td><td><?= $stats['51_75'] ?> Orang</td></tr>
                <tr><td>26% - 50%</td><td><?= $stats['26_50'] ?> Orang</td></tr>
                <tr><td><= 25%</td><td><?= $stats['0_25'] ?> Orang</td></tr>
            </tbody>
        </table>
    </div>

    <div style="margin-bottom: 10px;">Guru-guru dengan persentase tertinggi:</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">No.</th>
                <th style="width: 50%;">Nama</th>
                <th style="width: 20%;">Jumlah Jam Mengajar</th>
                <th style="width: 20%;">Persentase Tanqih</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($highest as $r): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td class="text-left"><?= htmlspecialchars($r['nama']) ?></td>
                <td><?= $r['expected'] ?> Jam</td>
                <td><?= number_format($r['pct'], 0, ',', '.') ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="header">
        LAPORAN MINGGUAN TANQIH IDAD GURU<br>
        PONDOK PESANTREN DARUSSALAM BOGOR<br>
        TAHUN PELAJARAN <?= htmlspecialchars($__currentYearName ?? '') ?>
    </div>
    
    <div style="margin-bottom: 10px; margin-top: 20px;">Guru-guru dengan persentase terendah:</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">No.</th>
                <th style="width: 50%;">Nama</th>
                <th style="width: 20%;">Jumlah Jam Mengajar</th>
                <th style="width: 20%;">Persentase Tanqih</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($lowest as $r): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td class="text-left"><?= htmlspecialchars($r['nama']) ?></td>
                <td><?= $r['expected'] ?> Jam</td>
                <td><?= number_format($r['pct'], 0, ',', '.') ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-signatures">
        <div class="signature-box" style="visibility: hidden;">
        </div>
        <div class="signature-box">
            <div class="date">Kamis, <?= $tglEnd ?> <?= $blnEnd ?> <?= $thnEnd ?><br>Kepala Bidang PBM</div>
            <div class="name" style="margin-top: 60px;">______________________</div>
        </div>
    </div>
</body>
</html>
