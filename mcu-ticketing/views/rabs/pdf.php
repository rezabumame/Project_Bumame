<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAB - <?php echo htmlspecialchars($rab['rab_number']); ?></title>
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
        .content-table th { text-align: center; font-weight: bold; text-transform: uppercase; height: 30px; }
        
        .col-no { width: 30px; text-align: center; }
        .col-uraian { width: 130px; }
        .col-code { width: 160px; text-align: center; }
        .col-price { width: 65px; text-align: right; }
        .col-qty { width: 35px; text-align: center; }
        .col-total { width: 75px; text-align: right; }
        .col-notes { width: auto; }

        .group-header { font-weight: bold; background-color: #fff; }
        .group-subtotal { font-weight: bold; text-align: right; }
        
        /* Soft green color instead of bright lime */
        .bg-green { background-color: #f2fff2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        
        /* Signatures */
    .signatures { margin-top: 30px; display: flex; justify-content: space-between; page-break-inside: avoid; }
    .signature-box { width: 21%; text-align: center; font-size: 11px; }
    .signature-title { margin-bottom: 5px; white-space: nowrap; }
        .signature-line { border-bottom: 1px solid #000; margin: 5px auto; width: 100%; }
        .signature-name { font-weight: bold; margin-top: 5px; }
        .signature-role { font-size: 10px; }
        .qr-code { width: 80px; height: 80px; margin: 5px auto; display: block; }
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
                    <?php echo nl2br(htmlspecialchars($company_address)); ?>
                </div>
            </div>
            <div class="header-right">
                <div class="header-title-box">RINCIAN ANGGARAN BELANJA</div>
                <table class="header-info-table">
                    <tr>
                        <td class="label">No RAB</td>
                        <td class="sep">:</td>
                        <td class="value"><?php echo htmlspecialchars($rab['rab_number']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="sep">:</td>
                        <td class="value"><?php echo date('d-M-Y', strtotime($rab['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Nama</td>
                        <td class="sep">:</td>
                        <td class="value"><?php echo htmlspecialchars($rab['creator_name']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Jabatan</td>
                        <td class="sep">:</td>
                        <td class="value"><?php echo !empty($rab['creator_jabatan']) ? htmlspecialchars($rab['creator_jabatan']) : '-'; ?></td>
                    </tr>
                    <tr>
                        <td class="label">Divisi</td>
                        <td class="sep">:</td>
                        <td class="value">Operational Medical Check Up</td>
                    </tr>
                    <tr>
                        <td class="label">Nama Kegiatan</td>
                        <td class="sep">:</td>
                        <td class="value"><?php echo htmlspecialchars($rab['nama_project']); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="sep">:</td>
                        <td class="value">
                            <?php 
                                echo DateHelper::formatSmartDateIndonesian($rab['selected_dates']);
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php
            $get_qr_verify = function($page, $params) {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                $verify_url = $scheme . $host . $basePath . '/index.php?page=' . $page . '&' . http_build_query($params);
                return "https://quickchart.io/qr?text=" . urlencode($verify_url) . "&size=100&margin=1";
            };

            // Pre-process items for grouping
            $groups = [
                'personnel' => ['label' => 'Petugas Eksternal', 'items' => [], 'total' => 0],
                'vendor' => ['label' => 'Vendor', 'items' => [], 'total' => 0],
                'transport' => ['label' => 'Transportasi', 'items' => [], 'total' => 0],
                'consumption' => ['label' => 'Konsumsi Peserta & Petugas', 'items' => [], 'total' => 0]
            ];

            $calculated_grand_total = 0;

            foreach ($items as $item) {
                // Map Cost Code
                if (isset($cost_code_map[$item['item_name']])) {
                    $item['cost_code'] = $cost_code_map[$item['item_name']];
                } else {
                    $item['cost_code'] = '';
                }

                // Fix Price/Subtotal if 0 (Fallback)
                if ((float)$item['price'] == 0 && $item['category'] == 'personnel') {
                    $role = $item['item_name'];
                    $loc = $rab['location_type'];
                    $key_fee = "fee_" . $loc . "_" . str_replace(' ', '_', $role);
                    if (isset($fee_settings[$key_fee])) {
                         $price = (float)str_replace('.', '', $fee_settings[$key_fee]);
                         $item['price'] = $price;
                         $item['subtotal'] = $price * $item['qty'] * $item['days'];
                    }
                }

                $cat = $item['category'];
                // Map to groups
                if ($cat == 'personnel') $key = 'personnel';
                elseif ($cat == 'vendor') $key = 'vendor';
                elseif ($cat == 'transport') $key = 'transport';
                elseif ($cat == 'consumption') $key = 'consumption';
                else $key = 'personnel'; // Default fallback

                $groups[$key]['items'][] = $item;
                $groups[$key]['total'] += $item['subtotal'];
                $calculated_grand_total += $item['subtotal'];
            }
            
            // Use calculated grand total for display if available
            if ($calculated_grand_total > 0) {
                $rab['grand_total'] = $calculated_grand_total;
            }
            
            // Filter empty groups except required ones? Or just show all?
            // User layout shows 1, 2, 3, 4. 
            // If vendor is empty, maybe skip? But layout implies structure.
            // I will skip empty groups to be safe, but map IDs.
            
            $dates = isset($rab['selected_dates']) ? json_decode($rab['selected_dates'], true) : [];
            $total_days = is_array($dates) ? count($dates) : 0;
        ?>

        <table class="content-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-uraian">URAIAN</th>
                    <th class="col-code">EXPENSE CODE</th>
                    <th class="col-price">HARGA SATUAN</th>
                    <th class="col-qty">VOLUME /<br>SATUAN</th>
                    <th class="col-total">JUMLAH</th>
                    <th class="col-notes">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data Kegiatan Section -->
                <tr style="background-color: #fff; border-bottom: 2px solid #000;">
                    <td class="text-center"></td>
                    <td colspan="6" class="text-bold" style="text-transform: uppercase; padding-left: 5px;">DATA KEGIATAN</td>
                </tr>
                <tr>
                    <td class="text-center"></td>
                    <td colspan="2" style="padding-left: 5px; font-style: italic;"><?php echo htmlspecialchars($rab['nama_project']); ?></td>
                    <td></td>
                    <td class="text-center"><?php echo $rab['total_participants']; ?></td>
                    <td></td>
                    <td class="text-center"><?php echo $total_days; ?> Hari</td>
                </tr>
                
                <!-- Anggaran Section Header -->
                <tr style="border-top: 2px solid #000;">
                    <td class="text-center"></td>
                    <td colspan="6" class="text-bold" style="text-transform: uppercase; padding-left: 5px;">ANGGARAN</td>
                </tr>

                <!-- Loop Groups -->
                <?php 
                $g_index = 1;
                foreach ($groups as $key => $group): 
                    // Skip empty groups to ensure correct numbering 
                    // (e.g. if Vendor exists, it becomes #2; if not, Transport becomes #2)
                    if (empty($group['items'])) continue;
                    
                    $is_transport = ($key == 'transport');
                    $bg_style = $is_transport ? 'background-color: #00FF00;' : '';
                ?>
                    <!-- Group Header -->
                    <tr style="<?php echo $bg_style; ?>">
                        <td class="text-center"><?php echo $g_index; ?></td>
                        <td colspan="6" class="text-bold"><?php echo $group['label']; ?></td>
                    </tr>

                    <!-- Group Items -->
                    <?php 
                    $char_index = 'a';
                    $index = 0;
                    $total_items = count($group['items']);
                    foreach ($group['items'] as $item): 
                        // Determine border styles for clean grouping
                        $row_style = $bg_style;
                        if ($total_items > 1) {
                            if ($index == 0) {
                                // First item: border-bottom usually visible, but if we want to merge look, maybe remove it?
                                // Table uses collapsed borders.
                                // Let's try setting border-bottom: none for all except last
                            }
                        }
                        
                        // Override td borders to hide internal horizontal lines
                        $td_style = "border-top: none; border-bottom: none;";
                        if ($index == 0) $td_style = "border-bottom: none;";
                        if ($index == $total_items - 1) $td_style = "border-top: none;";
                        if ($total_items == 1) $td_style = "";
                        
                        // But wait, content-table td has border: 1px solid #000 defined in CSS.
                        // We need to override it inline.
                        
                        $no_border_bottom = "border-bottom: 1px solid transparent;"; 
                        $no_border_top = "border-top: 1px solid transparent;";
                        
                        // Better approach:
                        // Top item: has top border (from group header or previous), bottom border transparent
                        // Middle: top/bottom transparent
                        // Bottom item: top transparent, bottom border black (for subtotal separation)
                        
                        $cell_style = "";
                        if ($total_items > 1) {
                            if ($index < $total_items - 1) {
                                $cell_style .= "border-bottom: 1px solid transparent;";
                            }
                            if ($index > 0) {
                                $cell_style .= "border-top: 1px solid transparent;";
                            }
                        }
                    ?>
                    <tr style="<?php echo $bg_style; ?>">
                        <td class="text-right" style="<?php echo $cell_style; ?> padding-right: 5px;"><?php echo $char_index++; ?></td>
                        <td style="padding-left: 5px; <?php echo $cell_style; ?>">
                            <?php echo htmlspecialchars($item['item_name']); ?>
                        </td>
                        <td class="text-center" style="font-size: 9px; <?php echo $cell_style; ?>"><?php echo !empty($item['cost_code']) ? htmlspecialchars($item['cost_code']) : ''; ?></td>
                         <td class="text-right" style="<?php echo $cell_style; ?>">
                            <?php 
                                $price = (float)($item['price'] ?? 0);
                                echo ($price == 0) ? '-' : number_format($price, 0, ',', '.'); 
                            ?>
                         </td>
                         <td class="text-center" style="<?php echo $cell_style; ?>">
                            <?php 
                                echo $item['qty']; 
                                if (isset($item['days']) && $item['days'] > 0 && $item['days'] != $total_days) {
                                    echo '<br><span style="font-size: 9px;">(' . $item['days'] . ' Hari)</span>';
                                }
                            ?>
                        </td>
                         <td class="text-right" style="<?php echo $cell_style; ?>">
                            <?php 
                                $subtotal = (float)($item['subtotal'] ?? 0);
                                echo ($subtotal == 0) ? '-' : number_format($subtotal, 0, ',', '.'); 
                            ?>
                         </td>
                        
                        <?php if ($key == 'personnel'): ?>
                            <?php if ($index === 0): ?>
                                <td rowspan="<?php echo count($group['items']); ?>" style="font-size: 10px; vertical-align: middle; text-align: center;">
                                    <?php echo htmlspecialchars($rab['personnel_notes'] ?? ''); ?>
                                </td>
                            <?php endif; ?>
                        <?php else: ?>
                            <td style="font-size: 10px; <?php echo $cell_style; ?>"><?php echo htmlspecialchars($item['notes'] ?? ''); ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php 
                        $index++;
                    endforeach; 
                    ?>

                    <!-- Group Subtotal -->
                    <tr style="<?php echo $bg_style; ?>">
                        <td colspan="5" class="text-center" style="border-top: 1px double #000;">Subtotal</td>
                        <td class="text-right" style="border-top: 1px double #000; font-weight: bold;"><?php echo number_format($group['total'], 0, ',', '.'); ?></td>
                        <td style="border-top: 1px double #000;"></td>
                    </tr>
                    
                <?php 
                    $g_index++;
                endforeach; 
                ?>

                <!-- Grand Total -->
                <tr style="border-top: 2px solid #000; border-bottom: 2px solid #000;">
                    <td colspan="5" class="text-center text-bold" style="font-size: 12px;">TOTAL RAB</td>
                    <td class="text-right text-bold" style="font-size: 12px;"><?php echo number_format((float)($rab['grand_total'] ?? 0), 0, ',', '.'); ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Tanggal, <?php echo DateHelper::formatIndonesianDate($rab['created_at']); ?></div>
                <div class="signature-title">Dibuat oleh,</div>
                <?php 
                    $c_role = !empty($rab['creator_jabatan']) ? htmlspecialchars($rab['creator_jabatan']) : 'Operation Support';
                    $qr_url = $get_qr_verify('qr_verify_rab', ['id' => $rab['id'], 'who' => 'creator']);
                ?>
                <img src="<?php echo $qr_url; ?>" alt="QR" class="qr-code">
                <div class="signature-name"><?php echo htmlspecialchars($rab['creator_name']); ?></div>
                <div class="signature-line"></div>
                <div class="signature-role"><?php echo $c_role; ?></div>
            </div>

            <?php if ($rab['approved_by_manager']): ?>
            <div class="signature-box">
                <div class="signature-title">&nbsp;</div>
                <div class="signature-title">Diketahui oleh,</div>
                <?php 
                    $m_role = !empty($rab['manager_jabatan']) ? htmlspecialchars($rab['manager_jabatan']) : 'Manager Operation General';
                    $qr_url = $get_qr_verify('qr_verify_rab', ['id' => $rab['id'], 'who' => 'manager']);
                ?>
                <img src="<?php echo $qr_url; ?>" alt="QR" class="qr-code">
                <div class="signature-name"><?php echo htmlspecialchars($rab['manager_name']); ?></div>
                <div class="signature-line"></div>
                <div class="signature-role"><?php echo $m_role; ?></div>
            </div>
            <?php endif; ?>

            <?php if ($rab['approved_by_head']): ?>
            <div class="signature-box">
                <div class="signature-title">&nbsp;</div>
                <div class="signature-title">Disetujui oleh,</div>
                <?php 
                    $h_role = !empty($rab['head_jabatan']) ? htmlspecialchars($rab['head_jabatan']) : 'Head of Operations';
                    $qr_url = $get_qr_verify('qr_verify_rab', ['id' => $rab['id'], 'who' => 'head']);
                ?>
                <img src="<?php echo $qr_url; ?>" alt="QR" class="qr-code">
                <div class="signature-name"><?php echo htmlspecialchars($rab['head_name']); ?></div>
                <div class="signature-line"></div>
                <div class="signature-role"><?php echo $h_role; ?></div>
            </div>
            <?php endif; ?>
            
             <?php if ($rab['approved_by_ceo']): ?>
            <div class="signature-box">
                <div class="signature-title">&nbsp;</div>
                <div class="signature-title">Disetujui oleh,</div>
                <?php 
                    $ce_role = !empty($rab['ceo_jabatan']) ? htmlspecialchars($rab['ceo_jabatan']) : 'CEO';
                    $ce_date = !empty($rab['approved_date_ceo']) ? date('d-M-Y H:i', strtotime($rab['approved_date_ceo'])) : '';
                    $qr_url = $get_qr_url($rab['ceo_name'], $ce_role, $ce_date);
                ?>
                <img src="<?php echo $qr_url; ?>" alt="QR" class="qr-code">
                <div class="signature-name"><?php echo htmlspecialchars($rab['ceo_name']); ?></div>
                <div class="signature-line"></div>
                <div class="signature-role"><?php echo $ce_role; ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Second Page: Formulir Permintaan Uang Muka -->
    <div class="page" style="page-break-before: always;">
        <!-- Header -->
        <table style="width: 100%; border: 2px solid #000; border-collapse: collapse;">
            <tr>
                <!-- Logo & Address -->
                <td style="width: 40%; padding: 10px; border-right: 2px solid #000; vertical-align: top;">
                    <img src="assets/images/logo.png" alt="Logo" style="max-height: 40px; margin-bottom: 5px;">
                    <div style="font-weight: bold; font-size: 12px;">PT BUMAME CAHAYA MEDIKA</div>
                    <div style="font-size: 9px; line-height: 1.2;">
                        <?php echo nl2br(htmlspecialchars($company_address)); ?>
                    </div>
                </td>
                <!-- User Info -->
                <td style="width: 35%; padding: 5px; border-right: 2px solid #000; vertical-align: top; font-size: 11px;">
                    <table style="width: 100%;">
                        <tr><td style="width: 50px;">Nama</td><td>: <?php echo htmlspecialchars($rab['creator_name']); ?></td></tr>
                        <tr><td>Jabatan</td><td>: <?php echo !empty($rab['creator_jabatan']) ? htmlspecialchars($rab['creator_jabatan']) : '-'; ?></td></tr>
                        <tr><td>Divisi</td><td>: OPS MCU</td></tr>
                    </table>
                </td>
                <!-- Form Info -->
                <td style="width: 25%; padding: 5px; vertical-align: top; font-size: 11px;">
                    <table style="width: 100%;">
                        <tr><td style="width: 60px;">No FPUM</td><td>: ________________</td></tr>
                        <tr><td>Tanggal UM</td><td>: ________________</td></tr>
                        <tr><td>Tanggal Penyelesaian</td><td>: ________________</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div style="background-color: #000; color: #fff; text-align: center; font-weight: bold; padding: 5px; font-size: 14px; margin-top: 2px;">
            FORMULIR PERMINTAAN UANG MUKA KEGIATAN
        </div>

        <!-- Content -->
        <div style="border: 2px solid #000; border-top: none; padding: 15px; font-size: 12px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 150px; padding: 5px 0; font-weight: bold;">Uang Sebesar</td>
                    <td style="padding: 5px 0; border-bottom: 1px dotted #000; font-weight: bold; background-color: #e6e6e6;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 30px; padding-left: 5px;">Rp.</td>
                                <td style="text-align: right; padding-right: 5px;"><?php echo number_format($groups['transport']['total'], 0, ',', '.'); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; padding: 5px 0; font-weight: bold;">Terbilang</td>
                    <td style="padding: 5px 0; font-style: italic;">: <?php echo trim(NumberHelper::terbilang($groups['transport']['total'])) . ' Rupiah'; ?></td>
                </tr>
                <tr><td colspan="2" style="border-bottom: 1px solid #000; padding-top: 5px;"></td></tr>
                
                <tr>
                    <td style="width: 150px; padding: 10px 0; font-weight: bold; vertical-align: top;">Untuk Pembayaran</td>
                    <td style="padding: 10px 0;">
                        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                            <tr>
                                <td style="width: 10px; vertical-align: top; padding: 0;">:</td>
                                <td style="padding: 0;">Biaya Transportasi Project MCU <?php echo htmlspecialchars($rab['nama_project']); ?></td>
                            </tr>
                            <tr>
                                <td style="width: 10px; vertical-align: top; padding: 0;"></td>
                                <td style="padding: 2px 0 0 0;">
                                    <div style="border-top: 1px solid #000; width: 100%; padding-top: 2px;">
                                        <?php echo DateHelper::formatSmartDateIndonesian($rab['selected_dates']); ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr><td colspan="2" style="border-bottom: 1px solid #000; padding-top: 5px;"></td></tr>
                <tr><td colspan="2" style="border-bottom: 2px solid #000; height: 3px;"></td></tr>
            </table>

            <!-- Berdasarkan -->
            <div style="margin-top: 10px;">
                <div style="font-weight: bold; margin-bottom: 5px;">Berdasarkan :</div>
                <table style="width: 100%; font-size: 12px;">
                    <tr>
                        <td style="width: 20px;">1</td>
                        <td style="width: 200px;">Rincian Anggaran Belanja</td>
                        <td style="width: 30px;">No</td>
                        <td style="border-bottom: 1px solid #000; width: 200px;"><?php echo htmlspecialchars($rab['rab_number']); ?></td>
                        <td style="width: 60px; text-align: right; padding-right: 5px;">Tanggal</td>
                        <td style="border-bottom: 1px solid #000;"><?php echo DateHelper::formatIndonesianDate($rab['created_at']); ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Surat Perintah / Penugasan</td>
                        <td>No</td>
                        <td style="border-bottom: 1px solid #000;"></td>
                        <td style="text-align: right; padding-right: 5px;">Tanggal</td>
                        <td style="border-bottom: 1px solid #000;"></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Dokumen ............................</td>
                        <td>No</td>
                        <td style="border-bottom: 1px solid #000;"></td>
                        <td style="text-align: right; padding-right: 5px;">Tanggal</td>
                        <td style="border-bottom: 1px solid #000;"></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Signatures -->
        <table style="width: 100%; border: 2px solid #000; border-top: none; border-collapse: collapse; text-align: center; font-size: 11px;">
            <tr>
                <td style="width: 20%; border-right: 1px solid #000; padding: 5px; vertical-align: top; height: 120px;">
                    <div style="margin-bottom: 30px;">Dibuat,</div>
                    <div style="text-align: left; margin-bottom: 5px;">Tgl.</div>
                    <?php 
                        $c_role = !empty($rab['creator_jabatan']) ? htmlspecialchars($rab['creator_jabatan']) : 'Operation Support';
                        $c_date = date('d-M-Y H:i', strtotime($rab['created_at'])); 
                        $qr_url = $get_qr_verify('qr_verify_rab', ['id' => $rab['id'], 'who' => 'creator']);
                    ?>
                    <img src="<?php echo $qr_url; ?>" alt="QR" style="width: 60px; height: 60px;">
                    <div style="font-weight: bold; border-bottom: 1px solid #000; margin-top: 5px;"><?php echo htmlspecialchars($rab['creator_name']); ?></div>
                    <div style="font-size: 10px;"><?php echo $c_role; ?></div>
                    <div style="font-size: 9px; margin-top: 5px;">Pemohon Uang Muka</div>
                </td>
                <td style="width: 20%; border-right: 1px solid #000; padding: 5px; vertical-align: top;">
                    <div style="margin-bottom: 30px;">Diketahui,</div>
                    <div style="text-align: left; margin-bottom: 5px;">Tgl.</div>
                     <?php if ($rab['approved_by_manager']): ?>
                        <?php 
                            $m_role = !empty($rab['manager_jabatan']) ? htmlspecialchars($rab['manager_jabatan']) : 'Manager Operation General';
                            $m_date = !empty($rab['approved_date_manager']) ? date('d-M-Y H:i', strtotime($rab['approved_date_manager'])) : '';
                            $qr_url = $get_qr_verify('qr_verify_rab', ['id' => $rab['id'], 'who' => 'manager']);
                        ?>
                        <img src="<?php echo $qr_url; ?>" alt="QR" style="width: 60px; height: 60px;">
                        <div style="font-weight: bold; border-bottom: 1px solid #000; margin-top: 5px;"><?php echo htmlspecialchars($rab['manager_name']); ?></div>
                        <div style="font-size: 10px;"><?php echo $m_role; ?></div>
                    <?php else: ?>
                         <div style="height: 60px;"></div>
                         <div style="border-bottom: 1px solid #000; margin-top: 5px;">&nbsp;</div>
                    <?php endif; ?>
                    <div style="font-size: 9px; margin-top: 5px;">Kepala Divisi</div>
                </td>
                <td style="width: 20%; border-right: 1px solid #000; padding: 5px; vertical-align: top;">
                    <div style="margin-bottom: 30px;">Disetujui Oleh,</div>
                    <div style="text-align: left; margin-bottom: 5px;">Tgl.</div>
                    <div style="height: 60px;"></div>
                    <div style="border-bottom: 1px solid #000; margin-top: 5px;">&nbsp;</div>
                    <div style="font-size: 9px; margin-top: 5px;">Kepala Div Keuangan</div>
                </td>
                <td style="width: 20%; border-right: 1px solid #000; padding: 5px; vertical-align: top;">
                    <div style="margin-bottom: 30px;">Dibayarkan Oleh,</div>
                    <div style="text-align: left; margin-bottom: 5px;">Tgl.</div>
                    <div style="height: 60px;"></div>
                    <div style="border-bottom: 1px solid #000; margin-top: 5px;">&nbsp;</div>
                    <div style="font-size: 9px; margin-top: 5px;">Finance</div>
                </td>
                <td style="width: 20%; padding: 5px; vertical-align: top;">
                    <div style="margin-bottom: 30px;">Diterima Oleh,</div>
                    <div style="text-align: left; margin-bottom: 5px;">Tgl.</div>
                    <div style="height: 60px;"></div>
                    <div style="border-bottom: 1px solid #000; margin-top: 5px;">&nbsp;</div>
                    <div style="font-size: 9px; margin-top: 5px;">Penerima Uang Muka</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
