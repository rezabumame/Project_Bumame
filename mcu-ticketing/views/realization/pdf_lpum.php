<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LPUM - <?php echo htmlspecialchars($rab['rab_number']); ?></title>
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #000; background: #fff; font-size: 11px; }
        .page { width: 210mm; min-height: 297mm; padding: 10mm 15mm; margin: 10mm auto; background: white; box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); position: relative; box-sizing: border-box; }
        @media print {
            body { margin: 0; padding: 0; }
            .page { width: 100%; margin: 0; border: none; padding: 10mm 15mm; box-shadow: none; }
            .no-print { display: none; }
        }
        
        /* Header Layout */
        .header-container { display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #000; }
        .header-left { width: 45%; }
        .logo { max-height: 50px; margin-bottom: 5px; }
        .company-name { font-weight: bold; font-size: 14px; margin-bottom: 2px; color: #1a428a; text-transform: uppercase; }
        .company-address { font-size: 10px; line-height: 1.3; color: #333; }
        
        .header-right { width: 50%; display: flex; flex-direction: column; align-items: flex-end; }
        .header-title-box { background-color: #000; color: #fff; padding: 8px 10px; font-weight: bold; text-align: center; margin-bottom: 15px; text-transform: uppercase; font-size: 12px; width: 100%; box-sizing: border-box; }
        .header-info-table { width: 100%; font-size: 11px; border-collapse: collapse; }
        .header-info-table td { padding: 2px 0; vertical-align: top; }
        .header-label { width: 100px; font-weight: normal; }
        .header-sep { width: 10px; text-align: center; }
        
        /* Info Block */
        .info-block { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; margin-top: 10px; }
        .info-block td { padding: 5px 0; vertical-align: top; }
        .info-label { width: 180px; font-weight: normal; color: #000; }
        .info-sep { width: 15px; text-align: center; font-weight: bold; }
        .info-value { width: auto; position: relative; border-bottom: 1px dotted #000; }
        
        /* Specific Styles for Values */
        .val-bold { font-weight: bold; }
        .fpum-container { display: flex; justify-content: space-between; width: 100%; }
        .fpum-date-label { margin-left: auto; margin-right: 10px; }
        
        /* Content Table */
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px; margin-top: 10px; }
        .content-table th, .content-table td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        .content-table th { text-align: center; font-weight: bold; height: 25px; background-color: #fff; }
        
        .col-no { width: 25px; text-align: center; }
        .col-uraian { width: auto; }
        /* .col-penerima { width: 80px; } Removed per request */
        .col-bukti-date { width: 60px; text-align: center; }
        .col-bukti-nomor { width: 60px; text-align: center; }
        .col-bukti-code { width: 160px; text-align: center; } /* Increased width */
        .col-jumlah { width: 80px; text-align: right; }
        .col-ket { width: 80px; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .pl-3 { padding-left: 20px; }
        .pl-4 { padding-left: 40px; }
        
        /* Signatures */
        .signatures { margin-top: 30px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .signature-box { width: 24%; font-size: 10px; display: flex; flex-direction: column; justify-content: space-between; min-height: 180px; }
        .signature-title { margin-bottom: 5px; font-weight: bold; text-align: center; font-size: 11px; }
        .signature-img-box { height: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 5px; }
        .qr-code { width: 70px; height: 70px; }
        .sig-details { width: 100%; text-align: left; }
        .sig-row { display: flex; margin-bottom: 2px; }
        .sig-label { width: 45px; }
        .sig-sep { width: 10px; text-align: center; }
        .sig-val { flex: 1; border-bottom: 1px solid #000; min-height: 14px; }
        .sig-val-noborder { flex: 1; min-height: 14px; }
        .sig-footer { text-align: center; font-size: 9px; margin-top: 10px; font-weight: bold; }
        
    </style>
</head>
<body>
    <?php
    // Define QR Verify URL function
    $get_qr_verify = function($page, $params) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $verify_url = $scheme . $host . $basePath . '/index.php?page=' . $page . '&' . http_build_query($params);
        return "https://quickchart.io/qr?text=" . urlencode($verify_url) . "&size=100&margin=1";
    };
    ?>

    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px;">Print / Save as PDF</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer; border-radius: 5px; margin-left: 10px;">Close</button>
    </div>

    <div class="page">
        <!-- Header -->
        <div class="header-container">
            <div class="header-left">
                <img src="assets/images/logo.png" alt="BUMAME" class="logo">
                <div class="company-name">PT BUMAME CAHAYA MEDIKA</div>
                <div class="company-address">
                    <?php echo nl2br(htmlspecialchars($company_address)); ?>
                </div>
            </div>
            <div class="header-right">
                <div class="header-title-box">LAPORAN PERTANGGUNG<br>JAWABAN UANG MUKA KEGIATAN</div>
                <table class="header-info-table">
                    <tr>
                        <td class="header-label">Nomor LPUM</td>
                        <td class="header-sep">:</td>
                        <td><?php echo date('Y/m', strtotime($rab['created_at'])) . '/LPUM/' . str_pad($rab['id'], 3, '0', STR_PAD_LEFT); ?></td>
                    </tr>
                    <tr>
                        <td class="header-label">Tanggal LPUM</td>
                        <td class="header-sep">:</td>
                        <td><?php echo date('d M Y'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Info Block -->
        <table class="info-block">
            <tr>
                <td class="info-label">1 Divisi</td>
                <td class="info-sep">:</td>
                <td class="info-value val-bold">Operational MCU</td>
            </tr>
            <tr>
                <td class="info-label">2 Nama Penanggung Jawab</td>
                <td class="info-sep">:</td>
                <td class="info-value"><?php echo htmlspecialchars($rab['creator_name']); ?></td>
            </tr>
            <tr>
                <td class="info-label">3 Jabatan Penanggung Jawab</td>
                <td class="info-sep">:</td>
                <td class="info-value"><?php echo !empty($rab['creator_jabatan']) ? htmlspecialchars($rab['creator_jabatan']) : 'Operation Support'; ?></td>
            </tr>
            <tr>
                <td class="info-label">4 No. FPUM / Tanggal FPUM</td>
                <td class="info-sep">:</td>
                <td class="info-value">
                    <div class="fpum-container">
                        <span><?php echo htmlspecialchars($rab['rab_number']); ?></span>
                        <span class="fpum-date-label">tanggal:</span>
                        <span><?php echo date('d F Y', strtotime($rab['created_at'])); ?></span>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="info-label">5 Nama Kegiatan</td>
                <td class="info-sep">:</td>
                <td class="info-value val-bold">Project Medical Check Up</td>
            </tr>
            <tr>
                <td class="info-label">6 Tujuan Kegiatan</td>
                <td class="info-sep">:</td>
                <td class="info-value">MCU <?php echo htmlspecialchars($rab['nama_project'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="info-label">7 Jumlah Uang Muka</td>
                <td class="info-sep">:</td>
                <td class="info-value">Rp <?php echo number_format($rab['grand_total']); ?></td>
            </tr>
        </table>
        
        <p style="margin-bottom: 10px; font-size: 11px;">Berikut ini adalah rincian penggunaan uang muka :</p>
        
        <!-- Content Table -->
        <table class="content-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-no">No.</th>
                    <th rowspan="2" class="col-uraian">Uraian</th>
                    <!-- <th rowspan="2" class="col-penerima">Penerima /<br>Vendor</th> -->
                    <th colspan="3" class="col-bukti">Bukti</th>
                    <th rowspan="2" class="col-jumlah">Jumlah</th>
                    <th rowspan="2" class="col-ket">Keterangan</th>
                </tr>
                <tr>
                    <th class="col-bukti-date">Tanggal</th>
                    <th class="col-bukti-nomor">Nomor</th>
                    <th class="col-bukti-code">Expense Code</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                $sub_alpha = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
                
                // Categories order
                $cats = ['PETUGAS MEDIS & LAPANGAN', 'TRANSPORTASI & AKOMODASI', 'KONSUMSI & LAINNYA', 'OTHER'];
                
                // Ensure grouped_items exists
                if (!isset($grouped_items)) $grouped_items = [];
                
                // Helper to safely get value
                function safe_val($arr, $key, $default = '') {
                    return isset($arr[$key]) ? $arr[$key] : $default;
                }
                
                // Iterate categories
                foreach ($cats as $cat_key):
                    if (!isset($grouped_items[$cat_key])) continue;
                    $group = $grouped_items[$cat_key];
                ?>
                <tr>
                    <td class="text-center text-bold"><?php echo $no++; ?></td>
                    <td colspan="6" class="text-bold"><?php echo $cat_key; ?></td>
                </tr>
                
                <?php 
                $idx = 0;
                // If it has 'main' items (Transport case)
                if (isset($group['main'])) {
                    foreach ($group['main'] as $item) {
                        $date_raw = safe_val($item, 'realization_dates');
                        if ($date_raw) {
                            $dates = explode(', ', $date_raw);
                            $formatted_dates = array_map(function($d) {
                                return date('d M Y', strtotime(trim($d)));
                            }, $dates);
                            $date_disp = implode(', ', $formatted_dates);
                        } else {
                            $date_str = safe_val($item, 'date');
                            $date_disp = $date_str ? date('d M Y', strtotime($date_str)) : '-';
                        }
                        ?>
                        <tr>
                            <td></td>
                            <td class="pl-3"><?php echo $sub_alpha[$idx++] . '. ' . htmlspecialchars(safe_val($item, 'item_name')); ?></td>
                            <!-- <td><?php echo htmlspecialchars(safe_val($item, 'vendor_name', '-')); ?></td> -->
                            <td class="text-center" style="font-size: 10px;"><?php echo $date_disp; ?></td>
                            <td class="text-center small"><?php echo htmlspecialchars($rab['project_code'] ?? '-'); ?></td>
                            <td class="text-center small"><?php echo htmlspecialchars(safe_val($item, 'cost_code', '-')); ?></td>
                            <td class="text-right"><?php echo number_format(safe_val($item, 'total_amount', 0)); ?></td>
                            <td><?php echo htmlspecialchars(safe_val($item, 'notes')); ?></td>
                        </tr>
                        <?php
                    }
                }
                
                // If it has 'emergency' items (Transport case)
                if (isset($group['emergency']) && count($group['emergency']) > 0) {
                    // Header for Emergency Cost
                    ?>
                    <tr>
                        <td></td>
                        <td class="pl-3 text-bold"><?php echo $sub_alpha[$idx++] . '. Emergency Cost'; ?></td>
                        <!-- <td></td> -->
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right"></td>
                        <td></td>
                    </tr>
                    <?php
                    // Details
                    foreach ($group['emergency'] as $item) {
                        $date_raw = safe_val($item, 'realization_dates');
                        if ($date_raw) {
                            $dates = explode(', ', $date_raw);
                            $formatted_dates = array_map(function($d) {
                                return date('d M Y', strtotime(trim($d)));
                            }, $dates);
                            $date_disp = implode(', ', $formatted_dates);
                        } else {
                            $date_str = safe_val($item, 'date');
                            $date_disp = $date_str ? date('d M Y', strtotime($date_str)) : '-';
                        }
                        ?>
                        <tr>
                            <td></td>
                            <td class="pl-4 small">- <?php echo htmlspecialchars(str_replace(['EMERGENCY:', 'Emergency:'], '', safe_val($item, 'item_name'))); ?></td>
                            <!-- <td><?php echo htmlspecialchars(safe_val($item, 'vendor_name', '-')); ?></td> -->
                            <td class="text-center" style="font-size: 10px;"><?php echo $date_disp; ?></td>
                            <td class="text-center small"><?php echo htmlspecialchars($rab['project_code'] ?? '-'); ?></td>
                            <td class="text-center small"><?php echo htmlspecialchars(safe_val($item, 'cost_code', '-')); ?></td>
                            <td class="text-right"><?php echo number_format(safe_val($item, 'total_amount', 0)); ?></td>
                            <td><?php echo htmlspecialchars(safe_val($item, 'notes')); ?></td>
                        </tr>
                        <?php
                    }
                }
                
                // If it has just 'items' (Other categories)
                if (isset($group['items'])) {
                     foreach ($group['items'] as $item) {
                        $date_raw = safe_val($item, 'realization_dates');
                        if ($date_raw) {
                            $dates = explode(', ', $date_raw);
                            $formatted_dates = array_map(function($d) {
                                return date('d M Y', strtotime(trim($d)));
                            }, $dates);
                            $date_disp = implode(', ', $formatted_dates);
                        } else {
                            $date_str = safe_val($item, 'date');
                            $date_disp = $date_str ? date('d M Y', strtotime($date_str)) : '-';
                        }
                        ?>
                        <tr>
                            <td></td>
                            <td class="pl-3"><?php echo $sub_alpha[$idx++] . '. ' . htmlspecialchars(safe_val($item, 'item_name')); ?></td>
                            <!-- <td><?php echo htmlspecialchars(safe_val($item, 'vendor_name', '-')); ?></td> -->
                            <td class="text-center" style="font-size: 10px;"><?php echo $date_disp; ?></td>
                            <td class="text-center small"><?php echo htmlspecialchars($rab['project_code'] ?? '-'); ?></td>
                            <td class="text-center small"><?php echo htmlspecialchars(safe_val($item, 'cost_code', '-')); ?></td>
                            <td class="text-right"><?php echo number_format(safe_val($item, 'total_amount', 0)); ?></td>
                            <td><?php echo htmlspecialchars(safe_val($item, 'notes')); ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                
                <?php endforeach; ?>
                
                <!-- Spacer rows to fill page if needed -->
                <tr>
                    <td colspan="7" style="height: 20px;"></td>
                </tr>
            </tbody>
            <tfoot>

                <tr>
                    <td colspan="5" class="text-right text-bold">TOTAL BIAYA</td>
                    <td class="text-right text-bold"><?php echo number_format($total_realized); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right text-bold">JUMLAH UANG MUKA</td>
                    <td class="text-right text-bold"><?php echo number_format($rab['grand_total']); ?></td>
                    <td></td>
                </tr>
                <?php 
                $variance = $rab['grand_total'] - $total_realized;
                $label = "UANG MUKA DIKEMBALIKAN / (DIBAYARKAN)";
                ?>
                <tr>
                    <td colspan="5" class="text-right text-bold"><?php echo $label; ?></td>
                    <td class="text-right text-bold"><?php echo number_format($variance); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        
        <div style="font-size: 10px; margin-bottom: 20px;">
            Bukti-bukti tersebut diatas disimpan sesuai ketentuan yang berlaku pada Perusahaan<br>
            Untuk kelengkapan administrasi dan keperluan pemeriksaan divisi Keuangan Akunting dan Pajak.
        </div>
        
        <div style="font-size: 11px; margin-bottom: 10px;">
            Demikian laporan pertanggung jawaban ini dibuat dengan sebenarnya
        </div>
        
        <!-- Signatures with QR Codes -->
        <div class="signatures">
            <!-- 1. Dibuat Oleh (Creator) -->
            <div class="signature-box">
                <div>
                    <div class="signature-title">Dibuat oleh,</div>
                    <?php 
                        $c_role = !empty($rab['creator_jabatan']) ? htmlspecialchars($rab['creator_jabatan']) : 'Operation Support';
                        $c_date = date('d-M-Y H:i', strtotime($rab['created_at'])); 
                        // User Request: "di tanda tangan lpum tanggal di bagian Dibuat oleh, ikutin timestiamps manager approved"
                        $c_date_lpum = !empty($rab['approved_date_manager']) ? date('d M Y', strtotime($rab['approved_date_manager'])) : date('d M Y');
                        $qr_url = $get_qr_verify('qr_verify_lpum', ['rab_id' => $rab['id'], 'who' => 'creator']);
                    ?>
                    <div class="signature-img-box">
                        <img src="<?php echo $qr_url; ?>" alt="QR" class="qr-code">
                    </div>
                    <div class="sig-details">
                        <div class="sig-row">
                            <div class="sig-label">Nama</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val"><?php echo htmlspecialchars($rab['creator_name']); ?></div>
                        </div>
                        <div class="sig-row">
                            <div class="sig-label">Jabatan</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val-noborder"><?php echo $c_role; ?></div>
                        </div>
                        <div class="sig-row">
                            <div class="sig-label">Tgl</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val-noborder"><?php echo $c_date_lpum; ?></div>
                        </div>
                    </div>
                </div>
                <div class="sig-footer">Penanggung Jawab</div>
            </div>
            
            <!-- 2. Diketahui Oleh (Manager) -->
            <div class="signature-box">
                <div>
                    <div class="signature-title">Diketahui Oleh,</div>
                    <?php 
                        $m_name = !empty($rab['manager_name']) ? $rab['manager_name'] : 'Iqbal Adhika';
                        $m_role = !empty($rab['manager_jabatan']) ? $rab['manager_jabatan'] : 'Manager Ops General';
                        $m_date = !empty($rab['approved_date_manager']) ? date('d M Y', strtotime($rab['approved_date_manager'])) : date('d M Y');
                        $qr_url = $get_qr_verify('qr_verify_lpum', ['rab_id' => $rab['id'], 'who' => 'manager']);
                    ?>
                    <div class="signature-img-box">
                        <img src="<?php echo $qr_url; ?>" alt="QR" class="qr-code">
                    </div>
                    <div class="sig-details">
                        <div class="sig-row">
                            <div class="sig-label">Nama</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val"><?php echo htmlspecialchars($m_name); ?></div>
                        </div>
                        <div class="sig-row">
                            <div class="sig-label">Jabatan</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val-noborder"><?php echo htmlspecialchars($m_role); ?></div>
                        </div>
                        <div class="sig-row">
                            <div class="sig-label">Tgl</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val-noborder"><?php echo $m_date; ?></div>
                        </div>
                    </div>
                </div>
                <div class="sig-footer">Kepala Divisi</div>
            </div>
            
            <!-- 3. Diperiksa Oleh (Finance) -->
            <div class="signature-box">
                <div>
                    <div class="signature-title">Diperiksa Oleh,</div>
                    <div class="signature-img-box">
                        <!-- Empty for manual signature -->
                    </div>
                    <div class="sig-details">
                        <div class="sig-row">
                            <div class="sig-label">Nama</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val"></div>
                        </div>
                        <div class="sig-row">
                            <div class="sig-label">Jabatan</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val-noborder"></div>
                        </div>
                        <div class="sig-row">
                            <div class="sig-label">Tgl</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val-noborder"></div>
                        </div>
                    </div>
                </div>
                <div class="sig-footer">Finance</div>
            </div>
            
            <!-- 4. Disetujui Oleh (Direksi) -->
            <div class="signature-box">
                <div>
                    <div class="signature-title">Disetujui Oleh,</div>
                    <div class="signature-img-box">
                        <!-- Empty for manual signature -->
                    </div>
                    <div class="sig-details">
                        <div class="sig-row">
                            <div class="sig-label">Nama</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val"></div>
                        </div>
                        <div class="sig-row">
                            <div class="sig-label">Jabatan</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val-noborder"></div>
                        </div>
                        <div class="sig-row">
                            <div class="sig-label">Tgl</div>
                            <div class="sig-sep">:</div>
                            <div class="sig-val-noborder"></div>
                        </div>
                    </div>
                </div>
                <div class="sig-footer">Direksi</div>
            </div>
        </div>
    </div>
</body>
</html>
