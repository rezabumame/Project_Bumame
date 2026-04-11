<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Memo - <?php echo htmlspecialchars($project['nama_project']); ?></title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 10mm;
        }
        .header {
            margin-bottom: 20px;
            position: relative;
        }
        .logo {
            height: 40px;
            margin-bottom: 5px;
        }
        .company-name {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .address {
            font-size: 11px;
            margin-bottom: 10px;
        }
        .memo-title {
            position: absolute;
            top: 25px;
            right: 0;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
        }
        
        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }
        .info-label {
            width: 160px;
        }
        .colon {
            width: 10px;
            text-align: center;
            border-left: none !important;
            border-right: none !important;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #0056b3; /* Blue header */
            color: white;
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .items-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
            vertical-align: top;
        }
        
        /* Notes */
        .notes-box {
            border: 1px solid #000;
            padding: 8px;
            min-height: 40px;
            margin-bottom: 30px;
            font-size: 12px;
        }
        
        /* Signatures */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: 1px solid #000;
            width: 25%;
            vertical-align: top;
            padding: 0;
        }
        .sig-header {
            padding: 5px;
            text-align: center;
            height: 20px;
        }
        .sig-content {
            padding-bottom: 5px;
            position: relative;
        }
        .sig-name {
            text-align: center;
            font-weight: bold;
            margin-top: 40px;
            padding: 0 5px;
        }
        .sig-title {
            text-align: center;
            font-size: 11px;
            margin-bottom: 5px;
        }
        .qr-code {
            width: 70px;
            height: 70px;
            margin: 5px auto;
            display: block;
        }
        .verified-badge {
            font-size: 8px;
            color: #28a745;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        
        /* Print adjustments */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <img src="assets/images/logo.png" alt="Bumame" class="logo">
        <div class="company-name">PT. BUMAME CAHAYA MEDIKA</div>
        <div class="memo-title">INTERNAL MEMO</div>
        <div class="address"><?php echo htmlspecialchars($company_address); ?></div>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">No P/O</td>
            <td style="border-left: none;">: <strong><?php echo 'BUMAME-MNJ-IM-' . date('ymd') . '-' . htmlspecialchars($project['project_id']); ?></strong></td>
        </tr>
        <tr>
            <td class="info-label">Tanggal P/O</td>
            <td style="border-left: none;">: <?php echo DateHelper::formatIndonesianDate($submission_date); ?></td>
        </tr>
        <tr>
            <td class="info-label">Issue by</td>
            <td style="border-left: none;">: <?php echo htmlspecialchars($project['nama_project']); ?></td>
        </tr>
        <tr>
            <td class="info-label">Vendor</td>
            <td style="border-left: none;">: <strong><?php echo htmlspecialchars($assigned_vendor_display); ?></strong></td>
        </tr>
        <tr>
            <td class="info-label">Pembayaran</td>
            <td style="border-left: none;">: Invoice</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal Pelaksanaan</td>
            <td style="border-left: none;">: <?php echo DateHelper::formatSmartDateIndonesian($project['tanggal_mcu']); ?></td>
        </tr>
        <tr>
            <td class="info-label">Kode Biaya</td>
            <td style="border-left: none;">: <?php echo isset($selected_cost_code) ? htmlspecialchars($selected_cost_code['code']) : '-'; ?></td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40px;">NO</th>
                <th>NAMA PEMERIKSAAN</th>
                <th style="width: 80px;">JUMLAH</th>
                <th>REMARKS</th>
                <th>VENDOR</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($allocations)): ?>
                <?php $i = 1; foreach ($allocations as $row): ?>
                <tr>
                    <td style="text-align: center;"><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['exam_type']); ?></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($row['participant_count']); ?></td>
                    <td><?php echo htmlspecialchars($row['notes']); ?></td>
                    <td><?php echo htmlspecialchars($row['assigned_vendor_name'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No vendor assignments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="notes-box">
        Notes:
        <div style="margin-top: 5px;">
             <?php echo isset($vendor_memo_notes) ? nl2br(htmlspecialchars($vendor_memo_notes)) : ''; ?>
        </div>
    </div>

    <table class="signature-table">
        <tr>
            <td style="width: 25%;">
                <div class="sig-header">Diajukan oleh:</div>
                <div class="sig-content">
                    <?php
                    $get_qr_verify = function($page, $params) {
                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                        // Use public verify page
                        $verify_url = $scheme . $host . $basePath . '/index.php?page=' . $page . '&' . http_build_query($params);
                        return "https://quickchart.io/qr?text=" . urlencode($verify_url) . "&size=100&margin=1";
                    };
                    
                    // QR for Preparer
                    $qr_url_preparer = $get_qr_verify('qr_verify_internal_memo', [
                        'id' => $project['project_id'],
                        'cost_code_id' => $cost_code_id,
                        'who' => 'preparer',
                        'pn' => $prepared_by_name,
                        'pt' => $prepared_by_title
                    ]);
                    ?>
                    <div class="text-center">
                        <div class="verified-badge">Verified Digital Signature</div>
                        <img src="<?php echo $qr_url_preparer; ?>" alt="QR" class="qr-code">
                    </div>
                    <div class="sig-name"><?php echo htmlspecialchars($prepared_by_name); ?></div>
                    <div class="sig-title"><?php echo htmlspecialchars($prepared_by_title); ?></div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="sig-header">Disetujui Oleh:</div>
                <div class="sig-content">
                    <?php
                    // QR for Approver
                    $qr_url_approver = $get_qr_verify('qr_verify_internal_memo', [
                        'id' => $project['project_id'],
                        'cost_code_id' => $cost_code_id,
                        'who' => 'approver'
                    ]);
                    ?>
                    <div class="text-center">
                        <div class="verified-badge">Verified Digital Signature</div>
                        <img src="<?php echo $qr_url_approver; ?>" alt="QR" class="qr-code">
                    </div>
                    <div class="sig-name"><?php echo htmlspecialchars($approved_by_1_name); ?></div>
                    <div class="sig-title"><?php echo htmlspecialchars($approved_by_1_title); ?></div>
                </div>
            </td>
            <td>
                <div class="sig-header"></div>
                <div class="sig-content">
                    <!-- Empty 3rd Column -->
                </div>
            </td>
            <td>
                <div class="sig-header"></div>
                <div class="sig-content">
                    <!-- Empty 4th Column -->
                </div>
            </td>
        </tr>
    </table>

</body>
</html>