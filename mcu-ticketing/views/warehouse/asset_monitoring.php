<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<?php
$months = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
    5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
    9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];
$currentYear = (int)date('Y');
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Monitoring Penggunaan Aset</h1>
            <p class="page-header-subtitle">Pantau pemakaian item dan kode aset per bulan.</p>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3">
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

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <form method="GET" action="index.php" class="d-flex align-items-center gap-3 flex-wrap">
                <input type="hidden" name="page" value="warehouse_asset_monitoring">
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0 fw-semibold text-nowrap">Filter Bulan:</label>
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
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter me-1"></i>Terapkan
                    </button>
                </div>
                <span class="text-muted small">
                    Menampilkan data: <strong><?php echo $months[$month] . ' ' . $year; ?></strong>
                </span>
            </form>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">
                <i class="fas fa-boxes me-2"></i>Summary Item Aset
            </h5>
            <span class="text-muted small"><?php echo count($summary); ?> item</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tblSummary">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th>Nama Item</th>
                            <th class="text-center">Total Kode</th>
                            <th class="text-center">Dipakai Bulan Ini</th>
                            <th class="text-center">Project Bulan Ini</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($summary)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Tidak ada data penggunaan aset untuk bulan ini.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($summary as $row): ?>
                        <tr>
                            <td><span class="text-muted small"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?php echo $row['total_codes']; ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($row['used_codes'] > 0): ?>
                                    <span class="badge bg-warning text-dark"><?php echo $row['used_codes']; ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($row['total_projects'] > 0): ?>
                                    <span class="badge bg-primary"><?php echo $row['total_projects']; ?> project</span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-lihat-kode"
                                        data-item-id="<?php echo $row['id']; ?>"
                                        data-item-name="<?php echo htmlspecialchars($row['item_name']); ?>">
                                    <i class="fas fa-tag me-1"></i>Lihat Kode
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
</div>

<!-- Modal 1: Kode Aset per Item -->
<div class="modal fade" id="modalItemCodes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tag me-2"></i><span id="modalItemCodesTitle">Kode Aset</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="itemCodesLoading" class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
                <div id="itemCodesContent" style="display:none;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Aset</th>
                                <th class="text-center">Pemakaian Bulan Ini</th>
                                <th class="text-center">Terakhir Dipakai</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemCodesBody"></tbody>
                    </table>
                </div>
                <div id="itemCodesEmpty" class="text-center text-muted py-3" style="display:none;">
                    Tidak ada kode aset untuk item ini.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: History Project per Kode Aset -->
<div class="modal fade" id="modalCodeHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history me-2"></i>Histori Pemakaian: <span id="modalCodeHistoryTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="codeHistoryLoading" class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
                <div id="codeHistoryContent" style="display:none;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project</th>
                                <th>Nama Project</th>
                                <th class="text-center">Tanggal Project</th>
                                <th>No Request</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="codeHistoryBody"></tbody>
                    </table>
                </div>
                <div id="codeHistoryEmpty" class="text-center text-muted py-3" style="display:none;">
                    Belum pernah digunakan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnBackToCodes">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Kode Aset
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var MONTH = <?php echo $month; ?>;
var YEAR  = <?php echo $year; ?>;
var lastItemId = null;
var lastItemName = '';

$(document).ready(function() {
    $('#tblSummary').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        order: [[4, 'desc']],
        columnDefs: [{ orderable: false, targets: [5] }]
    });
});

// Buka Modal 1: Kode Aset
$(document).on('click', '.btn-lihat-kode', function() {
    var itemId   = $(this).data('item-id');
    var itemName = $(this).data('item-name');
    lastItemId   = itemId;
    lastItemName = itemName;

    $('#modalItemCodesTitle').text(itemName);
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
            var rows = codes.map(function(c) {
                var usageBadge = c.usage_count > 0
                    ? '<span class="badge bg-warning text-dark">' + c.usage_count + 'x</span>'
                    : '<span class="text-muted">—</span>';
                var lastUsed = c.last_used
                    ? formatDate(c.last_used)
                    : '<span class="text-muted">—</span>';
                return '<tr>' +
                    '<td class="fw-semibold">' + escHtml(c.asset_code) + '</td>' +
                    '<td class="text-center">' + usageBadge + '</td>' +
                    '<td class="text-center">' + lastUsed + '</td>' +
                    '<td class="text-center">' +
                        '<button class="btn btn-sm btn-outline-primary btn-lihat-history" ' +
                        'data-code-id="' + c.id + '" data-code="' + escHtml(c.asset_code) + '">' +
                        '<i class="fas fa-history me-1"></i>History</button>' +
                    '</td>' +
                '</tr>';
            });
            $('#itemCodesBody').html(rows.join(''));
            $('#itemCodesContent').show();
        })
        .catch(function() {
            $('#itemCodesLoading').html('<p class="text-danger">Gagal memuat data.</p>');
        });
});

// Buka Modal 2: History Project
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
                'PENDING': 'secondary', 'IN_PREPARATION': 'info',
                'READY': 'primary', 'COMPLETED': 'success'
            };
            var rows = history.map(function(h) {
                var badge = statusMap[h.warehouse_status] || 'secondary';
                return '<tr>' +
                    '<td><span class="badge bg-light text-dark border">' + escHtml(h.project_id) + '</span></td>' +
                    '<td class="fw-semibold">' + escHtml(h.nama_project) + '</td>' +
                    '<td class="text-center">' + (h.tanggal_mcu ? formatDate(h.tanggal_mcu) : '—') + '</td>' +
                    '<td><small class="text-muted">' + escHtml(h.request_number) + '</small></td>' +
                    '<td class="text-center"><span class="badge bg-' + badge + '">' + h.warehouse_status + '</span></td>' +
                '</tr>';
            });
            $('#codeHistoryBody').html(rows.join(''));
            $('#codeHistoryContent').show();
        })
        .catch(function() {
            $('#codeHistoryLoading').html('<p class="text-danger">Gagal memuat data.</p>');
        });
});

// Tombol kembali ke Modal 1
$('#btnBackToCodes').on('click', function() {
    bootstrap.Modal.getInstance(document.getElementById('modalCodeHistory')).hide();
    document.getElementById('modalCodeHistory').addEventListener('hidden.bs.modal', function handler() {
        document.getElementById('modalCodeHistory').removeEventListener('hidden.bs.modal', handler);
        document.getElementById('modalItemCodes').querySelector('.btn-lihat-kode');
        var m1 = new bootstrap.Modal(document.getElementById('modalItemCodes'));
        m1.show();
    });
});

function escHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    var d = new Date(dateStr);
    var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}
</script>

<?php include '../views/layouts/footer.php'; ?>
