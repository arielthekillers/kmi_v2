<?php
$date = $date ?? date('Y-m-d');
$bulanId = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$tgl = date('d', strtotime($date));
$bln = $bulanId[date('m', strtotime($date))];
$thn = date('Y', strtotime($date));
$indoDate = ltrim($tgl, '0') . " $bln $thn";
$groupedSchedules = $groupedSchedules ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Rekomendasi Mengajar</title>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            body { -webkit-print-color-adjust: exact; margin: 0; padding: 0; background: #fff; }
            .no-print { display: none !important; }
            .page-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-template-rows: repeat(2, 1fr);
                column-gap: 8mm;
                row-gap: 5mm;
                height: 281mm; /* A4 297mm - 16mm margins */
                page-break-after: always;
                box-sizing: border-box;
                margin: 0;
                padding: 0;
                box-shadow: none;
                width: 194mm;
            }
            .slip { padding: 8px 10px; }
            .header { font-size: 13px; margin-bottom: 7px; }
            .meta { font-size: 11px; margin-bottom: 5px; }
            .meta td { padding: 2px 0; }
            .sub-header { font-size: 10px; margin-bottom: 4px; }
            .data-table th, .data-table td { font-size: 11px; height: 20px; }
            .note { font-size: 11px; margin-bottom: 8px; }
            .footer { font-size: 11px; }
            .footer .arabic { font-size: 15px; }
        }
        
        /* For screen view */
        body {
            font-family: Arial, sans-serif;
            background: #e5e7eb;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
        }
        
        .page-container {
            width: 210mm;
            height: 297mm;
            background: white;
            padding: 10mm;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: repeat(2, 1fr);
            column-gap: 15mm;
            row-gap: 10mm;
            box-sizing: border-box;
        }

        .slip {
            border: 2px solid #000;
            padding: 12px 15px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            background: #fff;
            height: 100%;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .meta {
            font-size: 13px;
            margin-bottom: 8px;
        }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 3px 0; vertical-align: bottom; }
        .meta .label { width: 35px; }
        
        .sub-header {
            font-size: 12px;
            margin-bottom: 5px;
            padding-left: 35px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            text-align: center;
            font-size: 13px;
            height: 24px;
        }
        .data-table th {
            font-weight: normal;
        }
        
        .note {
            font-size: 13px;
            margin-bottom: 15px;
            display: flex;
            align-items: flex-end;
        }
        
        .footer {
            text-align: center;
            font-size: 13px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .footer .signature-space {
            flex-grow: 1;
        }
        .footer .arabic {
            font-family: 'Traditional Arabic', Arial, sans-serif;
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">Cetak Rekomendasi</button>

    <?php 
    // Convert associative array to indexed array to chunk it
    $slips = [];
    foreach ($groupedSchedules as $subName => $data) {
        $slips[] = [
            'name' => $subName,
            'gender' => $data['gender'],
            'schedules' => $data['schedules']
        ];
    }
    
    // Chunk into pages of 4
    $pages = array_chunk($slips, 4);
    
    foreach ($pages as $pageSlips):
    ?>
    <div class="page-container">
        <?php foreach ($pageSlips as $slip): 
            $subName = $slip['name'];
            $gender = $slip['gender'];
            $title = ($gender === 'Perempuan') ? 'Mrs.' : (($gender === 'Laki-laki') ? 'Mr.' : 'Mr./Mrs.');
            
            // Map schedules by hour (1 to 7)
            $hours = [];
            for ($i = 1; $i <= 7; $i++) {
                $hours[$i] = null;
            }
            foreach ($slip['schedules'] as $sch) {
                $h = (int)$sch['hour'];
                if ($h >= 1 && $h <= 7) {
                    $hours[$h] = $sch;
                }
            }
        ?>
        <div class="slip">
            <div class="header">RECOMMENDATION</div>
            
            <div class="meta">
                <table>
                    <tr>
                        <td class="label">Date</td>
                        <td style="width: 15px; text-align: center;">:</td>
                        <td style="border-bottom: 1px solid #000; font-weight: bold; padding-left: 5px;">
                            <?= $indoDate ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">To</td>
                        <td style="text-align: center;">:</td>
                        <td style="border-bottom: 1px solid #000; font-weight: bold; padding-left: 5px;">
                            <?= $title ?> <?= htmlspecialchars($subName) ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="sub-header">Your honorable requested to teach:</div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 18%">Period</th>
                        <th style="width: 52%">Lesson</th>
                        <th style="width: 30%">Class</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 1; $i <= 7; $i++): ?>
                        <tr>
                            <td><?= $i ?></td>
                            <td>
                                <?php if ($hours[$i]): ?>
                                    <?= htmlspecialchars($hours[$i]['subject_name']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($hours[$i]): ?>
                                    <?= htmlspecialchars($hours[$i]['tingkat'] . ' - ' . $hours[$i]['abjad']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            
            <div class="note">
                <span style="margin-right: 5px;">Note:</span>
                <div style="flex-grow: 1; border-bottom: 1px solid #000; height: 18px;"></div>
            </div>
            
            <div class="footer">
                <div style="margin-top: 5px;">Pengajaran KMI</div>
                <div class="signature-space"></div>
                <div style="width: 120px; margin: 0 auto; border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</body>
</html>
