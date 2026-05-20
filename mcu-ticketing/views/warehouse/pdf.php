<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Request - <?php echo $data['header']['request_number']; ?></title>
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #000; background: #fff; font-size: 10px; }
        .page { width: 210mm; min-height: 297mm; padding: 12mm 15mm; margin: 8mm auto; background: white; box-shadow: 0 0 5px rgba(0,0,0,0.1); box-sizing: border-box; }
        @media print {
            body { margin: 0; padding: 0; }
            .page { width: 100%; margin: 0; border: none; padding: 10mm 15mm; box-shadow: none; }
            .no-print { display: none; }
        }

        /* Header */
        .header-container { display: flex; justify-content: space-between; margin-bottom: 14px; }
        .header-left { width: 44%; }
        .logo { max-height: 34px; margin-bottom: 4px; }
        .company-name { font-weight: bold; font-size: 12px; margin-bottom: 2px; }
        .company-address { font-size: 9px; line-height: 1.3; color: #333; }

        .header-right { width: 52%; }
        .header-title-box { background: #000; color: #fff; padding: 4px 8px; font-weight: bold; text-align: center; text-transform: uppercase; font-size: 12px; margin-bottom: 6px; }
        .header-subtitle { text-align: center; font-weight: bold; font-size: 11px; border-bottom: 1px solid #000; padding-bottom: 4px; margin-bottom: 5px; }
        .info-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .info-table td { padding: 1px 0; vertical-align: top; }
        .info-table .lbl { width: 95px; }
        .info-table .sep { width: 8px; text-align: center; }
        .info-table .val { border-bottom: 1px dotted #ccc; }

        /* Items Table */
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10px; }
        .content-table th, .content-table td { border: 1px solid #000; padding: 3px 4px; vertical-align: middle; }
        .content-table th { text-align: center; font-weight: bold; text-transform: uppercase; background: #fff; }
        .text-center { text-align: center; }

        /* Signatures — compact 5-column layout */
        .signatures { margin-top: 20px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sig-box { width: 18%; text-align: center; font-size: 9px; }
        .sig-title { margin-bottom: 6px; font-weight: bold; font-size: 9px; }
        .sig-line { border-bottom: 1px solid #000; margin: 36px auto 4px; width: 90%; }
        .sig-name { font-weight: bold; margin-top: 3px; font-size: 9px; }
        .sig-role { font-size: 8px; color: #444; }

        .asset-codes-cell { font-size: 9px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="no-print" style="position:fixed;top:10px;right:10px;z-index:1000;">
        <button onclick="window.print()" style="padding:8px 18px;background:#007bff;color:#fff;border:none;cursor:pointer;border-radius:5px;">Print / Save as PDF</button>
        <button onclick="window.close()" style="padding:8px 18px;background:#6c757d;color:#fff;border:none;cursor:pointer;border-radius:5px;margin-left:8px;">Close</button>
    </div>

    <div class="page">
        <!-- Header -->
        <div class="header-container">
            <div class="header-left">
                <img src="assets/images/logo.png" alt="Logo" class="logo">
                <div class="company-name">PT BUMAME CAHAYA MEDIKA</div>
                <div class="company-address">JL. TB SIMATUPANG NO.33 RT.01/ RW.05, RAGUNAN, PS MINGGU, JAKARTA SELATAN, DKI JAKARTA 12550</div>
            </div>
            <div class="header-right">
                <div class="header-title-box">FORM INVENTORY REQUEST</div>
                <div class="header-subtitle"><?php echo strtoupper(str_replace('_', ' ', $data['header']['warehouse_type'])); ?></div>
                <table class="info-table">
                    <tr>
                        <td class="lbl">ID Project</td><td class="sep">:</td>
                        <td class="val"><?php echo htmlspecialchars($data['header']['project_id']); ?></td>
                    </tr>
                    <tr>
                        <td class="lbl">Tanggal Request</td><td class="sep">:</td>
                        <td class="val"><?php echo date('d-M-Y H:i', strtotime($data['header']['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td class="lbl">Nama Project</td><td class="sep">:</td>
                        <td class="val"><?php echo htmlspecialchars($data['header']['nama_project']); ?></td>
                    </tr>
                    <tr>
                        <td class="lbl">Tanggal Project</td><td class="sep">:</td>
                        <td class="val"><?php
                            $tgl = $data['header']['tanggal_mcu'];
                            echo (!empty($tgl) && $tgl != '0000-00-00' && $tgl != '1970-01-01')
                                ? DateHelper::formatSmartDateIndonesian($tgl) : '-';
                        ?></td>
                    </tr>
                    <tr>
                        <td class="lbl">Requester</td><td class="sep">:</td>
                        <td class="val"><?php echo htmlspecialchars($data['header']['requester_name']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <?php
        $isAset = $data['header']['warehouse_type'] === 'GUDANG_ASET';
        $isKons = $data['header']['warehouse_type'] === 'GUDANG_KONSUMABLE';
        $selectedCodes = $data['selectedCodes'] ?? [];
        $konsItemCodes = $konsItemCodes ?? [];
        ?>
        <table class="content-table">
            <thead>
                <tr>
                    <th width="4%">NO</th>
                    <th width="12%">KATEGORI</th>
                    <?php if ($isKons): ?>
                    <th width="14%">KODE ITEM</th>
                    <?php endif; ?>
                    <th width="<?php echo $isAset ? '24%' : ($isKons ? '22%' : '30%'); ?>">NAMA BARANG</th>
                    <th width="5%">QTY</th>
                    <th width="7%">SATUAN</th>
                    <?php if ($isAset): ?>
                    <th width="20%">KODE ASET</th>
                    <?php endif; ?>
                    <th width="9%">CEK FISIK</th>
                    <th width="11%">PENGEMBALIAN</th>
                    <?php if ($isKons): ?>
                    <th width="10%">TERPAKAI</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($data['items'] as $item): ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <?php if ($isKons): ?>
                    <td class="asset-codes-cell">
                        <?php
                        $itemCodes = $konsItemCodes[$item['item_id']] ?? [];
                        if (!empty($itemCodes)) {
                            foreach ($itemCodes as $code) { echo htmlspecialchars($code) . '<br>'; }
                        }
                        ?>
                    </td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td class="text-center"><?php echo $item['qty_request']; ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($item['unit']); ?></td>
                    <?php if ($isAset): ?>
                    <td class="asset-codes-cell">
                        <?php
                        $codes = $selectedCodes[$item['request_item_id']] ?? [];
                        if (!empty($codes)) {
                            foreach ($codes as $c) { echo htmlspecialchars($c['asset_code']) . '<br>'; }
                        }
                        ?>
                    </td>
                    <?php endif; ?>
                    <td></td>
                    <td></td>
                    <?php if ($isKons): ?><td></td><?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signatures">
            <div class="sig-box">
                <div class="sig-title">Diajukan Oleh,</div>
                <div class="sig-line"></div>
                <div class="sig-name"><?php echo $data['header']['requester_name']; ?></div>
                <div class="sig-role"><?php echo $data['header']['requester_jabatan'] ?? 'Korlap'; ?></div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Disiapkan Oleh,</div>
                <div class="sig-line"></div>
                <div class="sig-name"><?php echo $data['header']['preparer_name'] ?? '( .................. )'; ?></div>
                <div class="sig-role">Admin Gudang</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Diambil Oleh,</div>
                <div class="sig-line"></div>
                <div class="sig-name">( .................. )</div>
                <div class="sig-role">Penerima Barang</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Dikembalikan Oleh,</div>
                <div class="sig-line"></div>
                <div class="sig-name">( .................. )</div>
                <div class="sig-role">Pik. Pengembalian</div>
            </div>
        </div>
    </div>
</body>
</html>
