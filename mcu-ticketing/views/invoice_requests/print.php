<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @page {
            margin: 0;
        }
        @media print {
            .no-print { display: none; }
            body { 
                padding: 20mm; 
            }
        }
        .container {
            width: 100%;
            max-width: 210mm; /* A4 width */
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header img {
            max-height: 50px;
            margin-bottom: 5px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            color: #2c3e50; /* Bumame blue-ish */
        }
        .header p {
            font-size: 9px;
            margin: 2px 0;
        }
        .title-bar {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 5px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .req-number {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border: 2px solid #000;
            padding: 10px;
        }
        .col-left, .col-right {
            width: 48%;
        }
        .row-item {
            display: flex;
            margin-bottom: 4px;
            align-items: flex-start;
        }
        .label {
            width: 120px;
            font-weight: normal;
            flex-shrink: 0;
        }
        .separator {
            width: 10px;
            text-align: center;
        }
        .value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 14px;
        }
        .value-multiline {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 28px; /* 2 lines */
        }
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
        }
        .checkbox-box {
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            margin-right: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            line-height: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 2px solid #000;
        }
        th, td {
            border: 2px solid #000;
            padding: 4px 8px;
            font-size: 11px;
        }
        th {
            text-align: center;
            font-weight: bold;
            background-color: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left !important; }
        
        .footer-section {
            margin-top: 10px;
        }
        .note-box {
            border: 2px solid #000;
            padding: 5px;
            width: 45%;
            font-size: 10px;
            margin-bottom: 20px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .sig-col {
            width: 200px;
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            margin-top: 60px;
            margin-bottom: 5px;
        }
        .date-line {
            display: flex;
            margin-bottom: 40px;
        }
        .date-label {
            width: 80px;
        }
        .date-value {
            border-bottom: 1px solid #000;
            width: 200px;
        }
        .qr-code {
            width: 70px;
            height: 70px;
            margin: 5px auto;
            display: block;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print / Save as PDF</button>
        <button onclick="window.history.back()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">Back</button>
    </div>

    <div class="container">
        <div class="no-print" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #2c3e50; color: white; border: none; border-radius: 4px;">
                <i class="fas fa-print"></i> Print Request
            </button>
        </div>

        <?php
            $get_qr_verify = function($page, $params) {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                $verify_url = $scheme . $host . $basePath . '/index.php?page=' . $page . '&' . http_build_query($params);
                return "https://quickchart.io/qr?text=" . urlencode($verify_url) . "&size=100&margin=1";
            };
            $req_id = $_GET['id'] ?? ($request['id'] ?? null);
        ?>
        <!-- Header -->
        <div class="header">
            <!-- Logo Placeholder if file exists, otherwise text -->
            <img src="assets/images/logo.png" alt="BUMAME" style="height: 40px;">
            <h1>PT BUMAME CAHAYA MEDIKA</h1>
            <p>JL. TB SIMATUPANG NO.33 RT.01/ RW.05, RAGUNAN, PS MINGGU, JAKARTA SELATAN, DKI JAKARTA 12550</p>
        </div>

        <div class="title-bar">FORMULIR PENGAJUAN INVOICE</div>
        <div class="req-number">Nomor : <?php echo htmlspecialchars($request['request_number'] ?? '..................................'); ?></div>

        <div class="info-section">
            <div class="col-left">
                <div class="row-item">
                    <div class="label">Jenis Rekanan</div>
                    <div class="separator">:</div>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <div class="checkbox-box"><?php echo ($request['partner_type'] ?? '') == 'Lab Partner' ? '✓' : ''; ?></div> Lab Partner
                        </div>
                        <div class="checkbox-item">
                            <div class="checkbox-box"><?php echo ($request['partner_type'] ?? '') == 'Corporate' ? '✓' : ''; ?></div> Corporate
                        </div>
                        <div class="checkbox-item">
                            <div class="checkbox-box"></div> ____________________
                        </div>
                    </div>
                </div>
                
                <div class="row-item" style="margin-top: 10px;">
                    <div class="label">Jenis Event</div>
                    <div class="separator">:</div>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <div class="checkbox-box"><?php echo ($request['event_type'] ?? '') == 'Walk In' ? '✓' : ''; ?></div> Walk In
                        </div>
                        <div class="checkbox-item">
                            <div class="checkbox-box"><?php echo ($request['event_type'] ?? '') == 'On Site' ? '✓' : ''; ?></div> On Site
                        </div>
                        <div class="checkbox-item">
                            <div class="checkbox-box"><?php echo ($request['event_type'] ?? '') == 'Subcon' ? '✓' : ''; ?></div> Subcon
                        </div>
                    </div>
                </div>

                <div class="row-item" style="margin-top: 10px;">
                    <div class="label">Nama Event</div>
                    <div class="separator">:</div>
                    <div class="value"><b><?php echo htmlspecialchars($request['client_company']); ?></b></div>
                </div>

                <div class="row-item">
                    <div class="label">Tanggal Pelaksanaan</div>
                    <div class="separator">:</div>
                    <div class="value"><?php echo DateHelper::formatSmartDateIndonesian($request['request_date']); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">PIC Sales</div>
                    <div class="separator">:</div>
                    <div class="value"><?php echo htmlspecialchars($request['sales_name']); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Nama NPWP</div>
                    <div class="separator">:</div>
                    <div class="value"><?php echo htmlspecialchars($request['client_company']); ?></div>
                </div>
            </div>

            <div class="col-right">
                <div class="row-item">
                    <div class="label">Nama PIC Rekanan</div>
                    <div class="separator">:</div>
                    <div class="value"><?php echo htmlspecialchars($request['client_pic']); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Nomor Telp. Rekanan</div>
                    <div class="separator">:</div>
                    <div class="value"><?php echo htmlspecialchars($request['client_phone']); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Email PIC Rekanan</div>
                    <div class="separator">:</div>
                    <div class="value"><?php echo htmlspecialchars($request['client_email'] ?? '-'); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Ketentuan Invoice</div>
                    <div class="separator">:</div>
                    <div class="value"><?php echo htmlspecialchars($request['invoice_terms']); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Alamat Pengiriman</div>
                    <div class="separator">:</div>
                    <div class="value-multiline" style="height: auto;"><?php echo nl2br(htmlspecialchars($request['shipping_address'])); ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Catatan</div>
                    <div class="separator">:</div>
                    <div class="value-multiline" style="height: auto;"><?php echo nl2br(htmlspecialchars($request['notes'])); ?></div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th class="text-left">JENIS PEMERIKSAAN</th>
                    <th width="15%">Harga Pemeriksaan</th>
                    <th width="8%">Qty</th>
                    <th width="15%">Total Harga</th>
                    <th width="15%">Ket.</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($request['items'] as $item): 
                    $total = $item['price'] * $item['qty'];
                ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td class="text-left">
                        <?php echo htmlspecialchars($item['item_description']); ?>
                    </td>
                    <td class="text-left">Rp <span style="float:right"><?php echo number_format($item['price'], 0, ',', '.'); ?></span></td>
                    <td class="text-center"><?php echo $item['qty']; ?></td>
                    <td class="text-left">Rp <span style="float:right"><?php echo number_format($total, 0, ',', '.'); ?></span></td>
                    <td class="text-center"><?php echo htmlspecialchars($item['remarks']); ?></td>
                </tr>
                <?php endforeach; ?>
                <!-- Fill empty rows if needed for visual match, but functionality first -->
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-center" style="font-weight:bold;">TOTAL INVOICE</td>
                    <td class="text-left" style="font-weight:bold;">Rp <span style="float:right"><?php echo number_format($grand_total, 0, ',', '.'); ?></span></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer-section">
            <div class="note-box">
                *Wajib melampirkan Surat Penawaran / PKS, Breakdown Harga, Surat Pengantar, NPWP, dan Data Absensi
            </div>

            <div class="date-line">
                <div class="date-label">Tanggal,</div>
                <div class="date-value text-center"><?php echo DateHelper::formatSmartDateIndonesian(date('Y-m-d')); ?></div>
            </div>

            <div class="signatures">
                <div class="sig-col">
                    Dibuat oleh,
                    <?php 
                        $c_name = $request['sales_name'];
                        $c_role = $request['sales_jabatan'] ?: 'Sales PIC';
                        
                        // Show QR
                        $qr_url = $get_qr_verify('qr_verify_invoice_request', ['id' => $req_id, 'who' => 'sales']);
                        if (!empty($request['approved_by_sales_at'])):
                    ?>
                        <img src="<?php echo $qr_url; ?>" alt="QR" class="qr-code">
                        <b><?php echo htmlspecialchars($c_name); ?></b><br>
                        <?php echo htmlspecialchars($c_role); ?>
                    <?php else: ?>
                        <div style="height: 70px; margin: 5px auto;"></div>
                        <div class="sig-line"></div>
                        <b><?php echo htmlspecialchars($c_name); ?></b><br>
                        <?php echo htmlspecialchars($c_role); ?>
                    <?php endif; ?>
                </div>

                <div class="sig-col">
                    Diperiksa oleh,
                    <?php if (!empty($request['approved_by_supervisor_at'])): ?>
                        <?php 
                            $s_name = $request['approver_spv_name'];
                            $s_role = $request['approver_spv_jabatan'] ?: 'Sales Support SPV';
                            $qr_url = $get_qr_verify('qr_verify_invoice_request', ['id' => $req_id, 'who' => 'spv']);
                        ?>
                        <img src="<?php echo $qr_url; ?>" alt="QR" class="qr-code">
                        <b><?php echo htmlspecialchars($s_name); ?></b><br>
                        <?php echo htmlspecialchars($s_role); ?>
                    <?php else: ?>
                        <div style="height: 70px; margin: 5px auto;"></div>
                        <div class="sig-line"></div>
                        <b>(..............................)</b><br>
                        Sales Support SPV
                    <?php endif; ?>
                </div>

                <div class="sig-col">
                    Disetujui oleh,
                    <?php if (!empty($request['approved_by_manager_at'])): ?>
                        <?php 
                            $m_name = $request['approver_mgr_name'];
                            $m_role = $request['approver_mgr_jabatan'] ?: 'Sales Performance Manager';
                            $qr_url = $get_qr_verify('qr_verify_invoice_request', ['id' => $req_id, 'who' => 'manager']);
                        ?>
                        <img src="<?php echo $qr_url; ?>" alt="QR" class="qr-code">
                        <b><?php echo htmlspecialchars($m_name); ?></b><br>
                        <?php echo htmlspecialchars($m_role); ?>
                    <?php else: ?>
                        <div style="height: 70px; margin: 5px auto;"></div>
                        <div class="sig-line"></div>
                        <b>(..............................)</b><br>
                        Sales Performance Manager
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
