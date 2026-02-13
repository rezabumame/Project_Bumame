<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Request - <?php echo $data['header']['request_number']; ?></title>
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #000; background: #fff; font-size: 11px; }
        .page { width: 210mm; min-height: 297mm; padding: 15mm; margin: 10mm auto; background: white; box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); position: relative; box-sizing: border-box; }
        @media print {
            body { margin: 0; padding: 0; }
            .page { width: 100%; margin: 0; border: none; padding: 10mm 15mm; box-shadow: none; }
            .no-print { display: none; }
        }
        
        /* Header Layout */
        .header-container { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .header-left { width: 45%; }
        .logo { max-height: 40px; margin-bottom: 5px; }
        .company-name { font-weight: bold; font-size: 14px; margin-bottom: 2px; }
        .company-address { font-size: 10px; line-height: 1.3; color: #333; }
        
        .header-right { width: 50%; }
        .header-title-box { background-color: #000; color: #fff; padding: 5px 10px; font-weight: bold; text-align: center; margin-bottom: 10px; text-transform: uppercase; font-size: 14px; }
        .header-info-table { width: 100%; font-size: 11px; border-collapse: collapse; }
        .header-info-table td { padding: 2px 0; vertical-align: top; }
        .header-info-table .label { width: 100px; font-weight: normal; }
        .header-info-table .sep { width: 10px; text-align: center; }
        .header-info-table .value { font-weight: normal; border-bottom: 1px dotted #ccc; }

        /* Content Table */
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        .content-table th, .content-table td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        .content-table th { text-align: center; font-weight: bold; text-transform: uppercase; height: 30px; background-color: #fff; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        
        /* Signatures */
        .signatures { margin-top: 30px; display: flex; justify-content: space-between; page-break-inside: avoid; position: relative; }
        .signature-box { width: 22%; text-align: center; font-size: 11px; }
        .signature-title { margin-bottom: 10px; font-weight: bold; }
        .signature-line { border-bottom: 1px solid #000; margin: 50px auto 5px; width: 100%; }
        .signature-name { font-weight: bold; margin-top: 5px; }
        .signature-role { font-size: 10px; }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px;">Print / Save as PDF</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer; border-radius: 5px; margin-left: 10px;">Close</button>
    </div>

    <div class="page">
        <!-- Header -->
        <div class="header-container">
            <div class="header-left">
                <img src="assets/images/logo.png" alt="Logo" class="logo">
                <div class="company-name">PT BUMAME CAHAYA MEDIKA</div>
                <div class="company-address">
                    JL. TB SIMATUPANG NO.33 RT.01/ RW.05, RAGUNAN, PS MINGGU, JAKARTA SELATAN, DKI JAKARTA 12550
                </div>
            </div>
            <div class="header-right">
                <div class="header-title-box">FORM INVENTORY REQUEST</div>
                <div class="text-center text-bold" style="margin-bottom: 10px; font-size: 12px; border-bottom: 1px solid #000; padding-bottom: 5px;">
                    <?php echo strtoupper(str_replace('_', ' ', $data['header']['warehouse_type'])); ?>
                </div>
                <table class="header-info-table">
                    <tr>
                        <td class="label">ID Project</td>
                        <td class="sep">:</td>
                        <td class="value"><?php echo htmlspecialchars($data['header']['project_id']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Request</td>
                        <td class="sep">:</td>
                        <td class="value"><?php echo date('d-M-Y H:i', strtotime($data['header']['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Nama Project</td>
                        <td class="sep">:</td>
                        <td class="value"><?php echo htmlspecialchars($data['header']['nama_project']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Project</td>
                        <td class="sep">:</td>
                        <td class="value">
                            <?php 
                            $tgl = $data['header']['tanggal_mcu'];
                            if (!empty($tgl) && $tgl != '0000-00-00' && $tgl != '1970-01-01') {
                                echo DateHelper::formatSmartDateIndonesian($tgl);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Requester</td>
                        <td class="sep">:</td>
                        <td class="value"><?php echo htmlspecialchars($data['header']['requester_name']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Content Table -->
        <table class="content-table">
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th width="15%">KATEGORI</th>
                    <th width="35%">NAMA BARANG</th>
                    <th width="5%">QTY</th>
                    <th width="8%">SATUAN</th>
                    <th width="10%">CEK FISIK</th>
                    <th width="12%">PENGEMBALIAN</th>
                    <?php if ($data['header']['warehouse_type'] == 'GUDANG_KONSUMABLE'): ?>
                    <th width="10%">SISA BARANG</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; foreach ($data['items'] as $item): ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td class="text-center"><?php echo $item['qty_request']; ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($item['unit']); ?></td>
                    <td></td>
                    <td></td>
                    <?php if ($data['header']['warehouse_type'] == 'GUDANG_KONSUMABLE'): ?>
                    <td></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Diajukan Oleh,</div>
                <div class="signature-line"></div>
                <div class="signature-name"><?php echo $data['header']['requester_name']; ?></div>
                <div class="signature-role"><?php echo $data['header']['requester_jabatan'] ?? 'Korlap'; ?></div>
            </div>
            
            <div class="signature-box">
                <div class="signature-title">Disiapkan Oleh,</div>
                <div class="signature-line"></div>
                <div class="signature-name"><?php echo $data['header']['preparer_name'] ?? '( ........................... )'; ?></div>
                <div class="signature-role">Admin Gudang</div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Barang Diambil Oleh,</div>
                <div class="signature-line"></div>
                <div class="signature-name"><?php echo $data['header']['picker_name'] ?? '( ........................... )'; ?></div>
                <div class="signature-role">Penerima Barang</div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Dikembalikan Oleh,</div>
                <div class="signature-line"></div>
                <div class="signature-name">( ........................... )</div>
                <div class="signature-role">Pik. Pengembalian</div>
            </div>
        </div>
    </div>
</body>
</html>