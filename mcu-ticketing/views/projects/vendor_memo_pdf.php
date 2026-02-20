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
            <td style="border-left: none;">: </td> <!-- Empty as per image requirement if no data, or maybe allow write-in -->
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
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No vendor assignments found.</td>
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
            <td>
                <div class="sig-header">Diajukan oleh:</div>
                <div class="sig-content">
                    <div class="sig-name"><?php echo htmlspecialchars($prepared_by_name); ?></div>
                    <div class="sig-title"><?php echo htmlspecialchars($prepared_by_title); ?></div>
                </div>
            </td>
            <td>
                <div class="sig-header">Disetujui Oleh:</div>
                <div class="sig-content">
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