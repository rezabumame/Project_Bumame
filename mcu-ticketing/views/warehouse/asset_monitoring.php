<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<?php
$months = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
    5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
    9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];
$currentYear = (int)date('Y');

// KPI computed from summary
$totalItems   = count($summary);
$totalCodes   = array_sum(array_column($summary, 'total_codes'));
$totalUsed    = array_sum(array_column($summary, 'used_codes'));
$totalIdle    = $totalCodes - $totalUsed;
$activeItems  = count(array_filter($summary, function($r) { return $r['total_projects'] > 0; }));
$usageRate    = $totalCodes > 0 ? round($totalUsed / $totalCodes * 100) : 0;

// Group unique categories
$categories = array_unique(array_column($summary, 'category'));
sort($categories);
?>

<style>
/* ---- KPI Cards ---- */
.kpi-card { border: none; border-radius: 12px; transition: transform .15s; }
.kpi-card:hover { transform: translateY(-2px); }
.kpi-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }

/* ---- Filter bar ---- */
.filter-bar { background: #f8f9fa; border-radius: 10px; padding: 14px 20px; border: 1px solid #e9ecef; }

/* ---- Table improvements ---- */
#tblSummary thead th { font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; font-weight: 600; background: #f8f9fa; }
.usage-bar { height: 6px; border-radius: 3px; background: #e9ecef; overflow: hidden; min-width: 80px; }
.usage-bar-fill { height: 100%; border-radius: 3px; transition: width .4s; }
.category-badge { font-size: .72rem; padding: 3px 8px; border-radius: 20px; font-weight: 500; }

/* ---- Status dot ---- */
.status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 4px; }

/* ---- Modal improvements ---- */
.modal-header-custom { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff; border-radius: 8px 8px 0 0; }
.modal-header-custom .btn-close { filter: brightness(0) invert(1); }
.code-chip { display: inline-flex; align-items: center; gap: 6px; background: #f1f3f5; border-radius: 6px; padding: 4px 10px; font-size: .85rem; font-weight: 600; }

/* ---- Print ---- */
@media print {
    .no-print, .sidebar, nav, .filter-bar button, .btn { display: none !important; }
    .kpi-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    body { font-size: 11px; }
    .print-header { display: block !important; }
}
.print-header { display: none; text-align: center; margin-bottom: 16px; }
</style>

<div class="container-fluid px-4">

    <!-- Print Header (hidden on screen) -->
    <div class="print-header">
        <h4 class="mb-0">Laporan Monitoring Penggunaan Aset</h4>
        <p class="text-muted mb-0"><?php echo $months[$month] . ' ' . $year; ?> &mdash; PT Bumame Cahaya Medika</p>
        <hr>
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center page-header-container no-print">
        <div>
            <h1 class="page-header-title">Monitoring Penggunaan Aset</h1>
            <p class="page-header-subtitle">Pantau pemakaian kode aset per bulan &mdash; data laporan siap ekspor.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success btn-sm" id="btnExportExcel">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3 no-print">
        <li class="nav-item">
            <a class="nav-link" href="index.php?page=warehouse_dashboard">
                <i class="fas fa-inbox me-1"></i>Request Masuk
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="index.php?page=warehouse_asset_monitoring">
                <i class="fas fa-chart-bar me-1"></i>Monitoring Aset
            </a>
        </li>
    </ul>

    <!-- Filter Bar -->
    <div class="filter-bar mb-4 no-print">
        <form method="GET" action="index.php" class="d-flex align-items-center gap-3 flex-wrap">
            <input type="hidden" name="page" value="warehouse_asset_monitoring">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <i class="fas fa-calendar-alt text-primary"></i>
                <label class="mb-0 fw-semibold">Periode:</label>
                <select name="month" class="form-select form-select-sm" style="width:140px;">
                    <?php foreach ($months as $num => $name): ?>
                    <option value="<?php echo $num; ?>" <?php echo $month == $num ? 'selected' : ''; ?>>
                        <?php echo $name; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select name="year" class="form-select form-select-sm" style="width:90px;">
                    <?php for ($y = $currentYear; $y >= $currentYear - 3; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="fas fa-search me-1"></i>Tampilkan
                </button>
            </div>
            <div class="ms-auto">
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="fas fa-calendar-check me-1"></i><?php echo $months[$month] . ' ' . $year; ?>
                </span>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background:#e8f4fd;">
                        <i class="fas fa-boxes" style="color:#0d6efd;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Item Aset</div>
                        <div class="fs-3 fw-bold lh-1"><?php echo $totalItems; ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?php echo count($categories); ?> kategori</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background:#fff3cd;">
                        <i class="fas fa-tags" style="color:#fd7e14;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Kode Aset</div>
                        <div class="fs-3 fw-bold lh-1"><?php echo $totalCodes; ?></div>
                        <div class="text-muted" style="font-size:.75rem;">terdaftar</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background:#d1e7dd;">
                        <i class="fas fa-check-circle" style="color:#198754;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Dipakai Bulan Ini</div>
                        <div class="fs-3 fw-bold lh-1 text-success"><?php echo $totalUsed; ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?php echo $usageRate; ?>% dari total kode</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background:#f8d7da;">
                        <i class="fas fa-pause-circle" style="color:#dc3545;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tidak Dipakai</div>
                        <div class="fs-3 fw-bold lh-1 text-danger"><?php echo $totalIdle; ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?php echo $totalItems - $activeItems; ?> item idle</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Rate Bar (global) -->
    <?php if ($totalCodes > 0): ?>
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-semibold small">Tingkat Pemakaian Keseluruhan</span>
                <span class="fw-bold text-primary"><?php echo $usageRate; ?>%</span>
            </div>
            <div class="progress" style="height:12px; border-radius:6px;">
                <div class="progress-bar bg-primary" role="progressbar"
                     style="width:<?php echo $usageRate; ?>%; border-radius:6px;"
                     aria-valuenow="<?php echo $usageRate; ?>" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <small class="text-muted"><?php echo $totalUsed; ?> kode dipakai dari <?php echo $totalCodes; ?> total</small>
                <small class="text-muted"><?php echo $activeItems; ?> dari <?php echo $totalItems; ?> item aktif digunakan</small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Summary Table -->
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center gap-2 py-3">
            <i class="fas fa-table text-primary"></i>
            <h5 class="card-title mb-0 flex-grow-1">Detail Penggunaan per Item</h5>
            <span class="badge bg-secondary"><?php echo $totalItems; ?> item</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tblSummary">
                    <thead>
                        <tr>
                            <th style="width:140px;">Kategori</th>
                            <th>Nama Item</th>
                            <th class="text-center" style="width:110px;">Total Kode</th>
                            <th style="width:180px;">Tingkat Pemakaian</th>
                            <th class="text-center" style="width:130px;">Project Bulan Ini</th>
                            <th class="text-center no-print" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($summary)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-3 d-block text-muted opacity-50"></i>
                                Tidak ada data penggunaan aset untuk periode ini.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        <?php
                        // Palette: [bg rgba, text hex, border rgba]
                        $catPalette = [
                            ['rgba(13,110,253,.1)',  '#084298', 'rgba(13,110,253,.25)'],   // blue
                            ['rgba(25,135,84,.1)',   '#0a3622', 'rgba(25,135,84,.25)'],    // green
                            ['rgba(13,202,240,.1)',  '#055160', 'rgba(13,202,240,.25)'],   // cyan
                            ['rgba(255,193,7,.15)',  '#664d03', 'rgba(255,193,7,.4)'],     // yellow
                            ['rgba(214,51,132,.1)',  '#6f1042', 'rgba(214,51,132,.25)'],   // pink
                            ['rgba(111,66,193,.1)',  '#3d1a78', 'rgba(111,66,193,.25)'],   // purple
                            ['rgba(253,126,20,.1)',  '#6d2c00', 'rgba(253,126,20,.25)'],   // orange
                            ['rgba(32,201,151,.1)',  '#0a3622', 'rgba(32,201,151,.3)'],    // teal
                        ];
                        $catMap = [];
                        $ci = 0;
                        foreach ($categories as $cat) { $catMap[$cat] = $catPalette[$ci++ % count($catPalette)]; }
                        ?>
                        <?php foreach ($summary as $row):
                            $rate = $row['total_codes'] > 0 ? round($row['used_codes'] / $row['total_codes'] * 100) : 0;
                            $barColor = $rate >= 70 ? '#198754' : ($rate >= 30 ? '#fd7e14' : '#dc3545');
                            $pal = $catMap[$row['category']] ?? $catPalette[0];
                        ?>
                        <tr>
                            <td>
                                <span class="category-badge" style="background:<?php echo $pal[0]; ?>; color:<?php echo $pal[1]; ?>; border:1px solid <?php echo $pal[2]; ?>;">
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($row['item_name']); ?></div>
                                <?php if ($row['total_projects'] == 0): ?>
                                <small class="text-muted"><span class="status-dot bg-danger"></span>Tidak dipakai bulan ini</small>
                                <?php else: ?>
                                <small class="text-success"><span class="status-dot bg-success"></span>Aktif digunakan</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold"><?php echo $row['total_codes']; ?></span>
                                <div class="text-muted" style="font-size:.72rem;">kode</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="usage-bar flex-grow-1">
                                        <div class="usage-bar-fill" style="width:<?php echo $rate; ?>%; background:<?php echo $barColor; ?>;"></div>
                                    </div>
                                    <span class="small fw-semibold" style="min-width:38px; color:<?php echo $barColor; ?>;"><?php echo $rate; ?>%</span>
                                </div>
                                <div class="text-muted" style="font-size:.72rem;"><?php echo $row['used_codes']; ?> / <?php echo $row['total_codes']; ?> dipakai</div>
                            </td>
                            <td class="text-center">
                                <?php if ($row['total_projects'] > 0): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fs-6 px-2 py-1">
                                        <i class="fas fa-project-diagram me-1" style="font-size:.7rem;"></i><?php echo $row['total_projects']; ?> project
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center no-print">
                                <button type="button" class="btn btn-sm btn-primary btn-lihat-kode"
                                        data-item-id="<?php echo $row['id']; ?>"
                                        data-item-name="<?php echo htmlspecialchars($row['item_name'], ENT_QUOTES); ?>"
                                        data-total-codes="<?php echo $row['total_codes']; ?>"
                                        data-used-codes="<?php echo $row['used_codes']; ?>">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3 no-print">
        <i class="fas fa-info-circle me-1"></i>
        Data berdasarkan tanggal MCU project. 1 project dihitung 1 pemakaian meskipun terdapat beberapa jadwal.
        Digenerate: <?php echo date('d M Y H:i'); ?>
    </div>
</div>

<!-- ============================
     Modal 1: Kode Aset per Item
     ============================ -->
<div class="modal fade" id="modalItemCodes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <div>
                    <h5 class="modal-title mb-0"><i class="fas fa-tags me-2"></i><span id="modalItemCodesTitle">Kode Aset</span></h5>
                    <div id="modalItemCodesMeta" class="small mt-1 opacity-75"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="itemCodesLoading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-2"></div>
                    <div class="text-muted small">Memuat data kode aset...</div>
                </div>
                <div id="itemCodesContent" style="display:none;">
                    <!-- Mini stats -->
                    <div id="itemCodesMiniStats" class="d-flex gap-3 p-3 bg-light border-bottom flex-wrap"></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d;">#</th>
                                    <th style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d;">Kode Aset</th>
                                    <th class="text-center" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d;">Status Bulan Ini</th>
                                    <th class="text-center" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d;">Terakhir Dipakai</th>
                                    <th class="text-center" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d;">History</th>
                                </tr>
                            </thead>
                            <tbody id="itemCodesBody"></tbody>
                        </table>
                    </div>
                </div>
                <div id="itemCodesEmpty" class="text-center text-muted py-5" style="display:none;">
                    <i class="fas fa-tag fa-2x mb-3 d-block opacity-25"></i>
                    Tidak ada kode aset terdaftar untuk item ini.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ================================
     Modal 2: History Project per Kode
     ================================ -->
<div class="modal fade" id="modalCodeHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <div>
                    <h5 class="modal-title mb-0"><i class="fas fa-history me-2"></i>Riwayat Pemakaian</h5>
                    <div class="small mt-1 opacity-75">Kode: <span id="modalCodeHistoryTitle" class="fw-bold"></span></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="codeHistoryLoading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-2"></div>
                    <div class="text-muted small">Memuat riwayat pemakaian...</div>
                </div>
                <div id="codeHistoryContent" style="display:none;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d;">ID Project</th>
                                    <th style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d;">Nama Project</th>
                                    <th class="text-center" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d; width:120px;">Tgl MCU</th>
                                    <th style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d;">No Request</th>
                                    <th class="text-center" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#6c757d;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="codeHistoryBody"></tbody>
                        </table>
                    </div>
                </div>
                <div id="codeHistoryEmpty" class="text-center text-muted py-5" style="display:none;">
                    <i class="fas fa-history fa-2x mb-3 d-block opacity-25"></i>
                    Kode aset ini belum pernah digunakan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnBackToCodes">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Kode Aset
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- SheetJS for Excel export -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>

<script>
var MONTH = <?php echo $month; ?>;
var YEAR  = <?php echo $year; ?>;
var MONTH_NAME = '<?php echo $months[$month]; ?>';
var lastItemId   = null;
var lastItemName = '';

$(document).ready(function() {
    $('#tblSummary').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: [5] },
            { targets: [3], type: 'num' }
        ],
        pageLength: 25
    });
});

// ---- Export Excel ----
$('#btnExportExcel').on('click', function() {
    var wb = XLSX.utils.book_new();

    // Sheet 1: Summary
    var summaryData = [
        ['Laporan Monitoring Penggunaan Aset - ' + MONTH_NAME + ' ' + YEAR],
        ['Digenerate: ' + new Date().toLocaleString('id-ID')],
        [],
        ['No', 'Kategori', 'Nama Item', 'Total Kode', 'Dipakai Bulan Ini', 'Tidak Dipakai', 'Tingkat Pemakaian (%)', 'Project Bulan Ini']
    ];

    var rows = [];
    $('#tblSummary tbody tr').each(function(i) {
        var cells = $(this).find('td');
        if (cells.length < 5) return;
        var totalCodes = parseInt($(cells[2]).text().trim()) || 0;
        var usedText   = $(cells[3]).find('.usage-bar-fill').length
            ? parseInt($(cells[3]).find('.small').text()) || 0
            : 0;

        // Read from data-* attributes of the button instead
        var btn = $(this).find('.btn-lihat-kode');
        var tc  = parseInt(btn.data('total-codes')) || 0;
        var uc  = parseInt(btn.data('used-codes'))  || 0;
        var rate = tc > 0 ? Math.round(uc / tc * 100) : 0;

        var projText = $(cells[4]).text().trim().replace(' project','').trim();
        var proj = parseInt(projText) || 0;

        rows.push([
            i + 1,
            $(cells[0]).text().trim(),
            $(cells[1]).find('.fw-semibold').text().trim() || $(cells[1]).text().trim(),
            tc,
            uc,
            tc - uc,
            rate,
            proj
        ]);
    });

    rows.forEach(function(r) { summaryData.push(r); });

    // Footer totals
    summaryData.push([]);
    var totalItems = rows.length;
    var totalCodes = rows.reduce(function(s,r){ return s + r[3]; }, 0);
    var totalUsed  = rows.reduce(function(s,r){ return s + r[4]; }, 0);
    var totalIdle  = rows.reduce(function(s,r){ return s + r[5]; }, 0);
    summaryData.push(['', 'TOTAL', '', totalCodes, totalUsed, totalIdle,
        totalCodes > 0 ? Math.round(totalUsed/totalCodes*100) : 0, '']);

    var ws1 = XLSX.utils.aoa_to_sheet(summaryData);

    // Column widths
    ws1['!cols'] = [
        {wch:4},{wch:16},{wch:30},{wch:12},{wch:16},{wch:14},{wch:22},{wch:18}
    ];

    XLSX.utils.book_append_sheet(wb, ws1, 'Summary');
    XLSX.writeFile(wb, 'Monitoring_Aset_' + MONTH_NAME + '_' + YEAR + '.xlsx');
});

// ---- Modal 1: Kode Aset ----
$(document).on('click', '.btn-lihat-kode', function() {
    var itemId    = $(this).data('item-id');
    var itemName  = $(this).data('item-name');
    var totalCodes = $(this).data('total-codes');
    var usedCodes  = $(this).data('used-codes');
    lastItemId    = itemId;
    lastItemName  = itemName;

    $('#modalItemCodesTitle').text(itemName);
    $('#modalItemCodesMeta').html(
        '<i class="fas fa-calendar me-1"></i>' + MONTH_NAME + ' ' + YEAR +
        ' &nbsp;|&nbsp; ' + totalCodes + ' kode terdaftar'
    );
    $('#itemCodesLoading').show();
    $('#itemCodesContent, #itemCodesEmpty').hide();

    var modal = new bootstrap.Modal(document.getElementById('modalItemCodes'));
    modal.show();

    fetch('index.php?page=warehouse_asset_item_codes&item_id=' + itemId + '&month=' + MONTH + '&year=' + YEAR)
        .then(r => r.json())
        .then(function(codes) {
            $('#itemCodesLoading').hide();
            if (!codes || codes.length === 0) {
                $('#itemCodesEmpty').show();
                return;
            }

            // Mini stats
            var used  = codes.filter(function(c){ return c.usage_count > 0; }).length;
            var idle  = codes.length - used;
            $('#itemCodesMiniStats').html(
                '<span class="text-muted small"><strong>' + codes.length + '</strong> total kode</span>' +
                '<span class="text-success small"><i class="fas fa-check-circle me-1"></i><strong>' + used + '</strong> dipakai bulan ini</span>' +
                '<span class="text-danger small"><i class="fas fa-pause-circle me-1"></i><strong>' + idle + '</strong> tidak dipakai</span>'
            );

            var rows = codes.map(function(c, i) {
                var isUsed = c.usage_count > 0;
                var statusBadge = isUsed
                    ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="fas fa-check me-1"></i>Dipakai ' + c.usage_count + 'x</span>'
                    : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><i class="fas fa-minus me-1"></i>Idle</span>';
                var lastUsed = c.last_used
                    ? '<span class="fw-semibold">' + formatDate(c.last_used) + '</span>'
                    : '<span class="text-muted">—</span>';
                return '<tr class="' + (isUsed ? '' : 'opacity-75') + '">' +
                    '<td class="text-muted small">' + (i+1) + '</td>' +
                    '<td><div class="code-chip"><i class="fas fa-barcode text-muted" style="font-size:.7rem;"></i>' + escHtml(c.asset_code) + '</div></td>' +
                    '<td class="text-center">' + statusBadge + '</td>' +
                    '<td class="text-center">' + lastUsed + '</td>' +
                    '<td class="text-center">' +
                        '<button class="btn btn-sm btn-outline-primary btn-lihat-history" ' +
                        'data-code-id="' + c.id + '" data-code="' + escHtml(c.asset_code) + '">' +
                        '<i class="fas fa-history"></i></button>' +
                    '</td>' +
                '</tr>';
            });
            $('#itemCodesBody').html(rows.join(''));
            $('#itemCodesContent').show();
        })
        .catch(function() {
            $('#itemCodesLoading').html('<div class="py-4 text-center text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Gagal memuat data.</div>');
        });
});

// ---- Modal 2: History Project ----
$(document).on('click', '.btn-lihat-history', function() {
    var codeId   = $(this).data('code-id');
    var codeName = $(this).data('code');

    $('#modalCodeHistoryTitle').text(codeName);
    $('#codeHistoryLoading').show();
    $('#codeHistoryContent, #codeHistoryEmpty').hide();

    bootstrap.Modal.getInstance(document.getElementById('modalItemCodes')).hide();
    var modal2 = new bootstrap.Modal(document.getElementById('modalCodeHistory'));
    modal2.show();

    fetch('index.php?page=warehouse_asset_code_history&code_id=' + codeId)
        .then(r => r.json())
        .then(function(history) {
            $('#codeHistoryLoading').hide();
            if (!history || history.length === 0) {
                $('#codeHistoryEmpty').show();
                return;
            }
            var statusMap = {
                'PENDING': { color: 'secondary', label: 'Pending' },
                'IN_PREPARATION': { color: 'warning', label: 'Diproses' },
                'READY': { color: 'primary', label: 'Siap' },
                'COMPLETED': { color: 'success', label: 'Selesai' }
            };
            var rows = history.map(function(h) {
                var s = statusMap[h.warehouse_status] || { color: 'secondary', label: h.warehouse_status };
                return '<tr>' +
                    '<td><span class="badge bg-light text-dark border fw-bold">' + escHtml(h.project_id) + '</span></td>' +
                    '<td class="fw-semibold">' + escHtml(h.nama_project) + '</td>' +
                    '<td class="text-center">' + (h.tanggal_mcu ? formatDate(h.tanggal_mcu) : '<span class="text-muted">—</span>') + '</td>' +
                    '<td><small class="text-muted font-monospace">' + escHtml(h.request_number) + '</small></td>' +
                    '<td class="text-center"><span class="badge bg-' + s.color + ' bg-opacity-15 text-' + s.color + ' border border-' + s.color + ' border-opacity-25">' + s.label + '</span></td>' +
                '</tr>';
            });
            $('#codeHistoryBody').html(rows.join(''));
            $('#codeHistoryContent').show();
        })
        .catch(function() {
            $('#codeHistoryLoading').html('<div class="py-4 text-center text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Gagal memuat data.</div>');
        });
});

// ---- Kembali ke Modal 1 ----
$('#btnBackToCodes').on('click', function() {
    var m2 = bootstrap.Modal.getInstance(document.getElementById('modalCodeHistory'));
    m2.hide();
    document.getElementById('modalCodeHistory').addEventListener('hidden.bs.modal', function handler() {
        document.getElementById('modalCodeHistory').removeEventListener('hidden.bs.modal', handler);
        new bootstrap.Modal(document.getElementById('modalItemCodes')).show();
    });
});

function escHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    var parts = String(dateStr).split('-');
    if (parts.length !== 3) return dateStr;
    var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    var m = parseInt(parts[1], 10) - 1;
    return parseInt(parts[2], 10) + ' ' + (months[m] || parts[1]) + ' ' + parts[0];
}
</script>

<?php include '../views/layouts/footer.php'; ?>
