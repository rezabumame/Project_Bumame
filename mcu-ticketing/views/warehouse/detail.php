<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    @media (max-width: 767.98px) {
        .desktop-view { display: none !important; }
        .mobile-view { display: block !important; }
        .page-header-container { flex-direction: column; align-items: flex-start; gap: 10px; }
        .page-header-container > div { width: 100%; }
        .page-header-container .btn { width: 100%; }
        .signature-block { margin-bottom: 40px; }
        .signature-block:last-child { margin-bottom: 0; }
    }
    @media (min-width: 768px) {
        .desktop-view { display: block !important; }
        .mobile-view { display: none !important; }
        .sticky-actions { display: none; }
    }
    .sticky-actions {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: #fff; padding: 12px 15px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000; display: flex; gap: 10px; align-items: center;
    }
    .sticky-actions .btn-icon-only {
        width: 42px; height: 42px; padding: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; flex-shrink: 0;
    }
    .mobile-item-card { background: #fff; border-bottom: 1px solid #e9ecef; }
    .mobile-item-header { padding: 12px 15px; cursor: pointer; }
    .qty-summary { font-size: 0.9rem; background: #eef2f7; padding: 4px 8px; border-radius: 4px; color: #333; }
    .mobile-list-container { padding-bottom: 80px; }
    .select2-container { width: 100% !important; }
    .asset-code-tag { font-size: 0.75rem; }
    .select2-container--default .select2-selection--multiple { min-height: 38px; }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 3px;
        padding: 4px 6px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin: 0;
        float: none;
    }
    .select2-container--default .select2-selection--multiple .select2-search {
        width: 100%;
        margin: 0;
        float: none;
    }
    .select2-container--default .select2-selection--multiple .select2-search__field {
        width: 100% !important;
    }
</style>

<?php
$isGudangAset = ($_SESSION['role'] === 'admin_gudang_aset' || $_SESSION['role'] === 'superadmin')
                && $data['header']['warehouse_type'] === 'GUDANG_ASET';
$hasAsetItems = !empty(array_filter($data['items'], fn($i) => $i['item_type'] === 'ASET'));
$showAssetCodePanel = $isGudangAset && $hasAsetItems;
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Detail Request: <?php echo $data['header']['project_id']; ?></h1>
            <p class="page-header-subtitle">Review items and update preparation status.</p>
        </div>
        <div>
            <a href="index.php?page=warehouse_dashboard" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Info Column -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-soft-primary">
                    <h5 class="card-title mb-0">Info Project</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="ps-0 text-muted">Project</td>
                            <td class="fw-bold"><?php echo $data['header']['nama_project']; ?></td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">Requester</td>
                            <td><?php echo $data['header']['requester_name']; ?></td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">Tipe Gudang</td>
                            <td><span class="badge bg-info"><?php echo $data['header']['warehouse_type']; ?></span></td>
                        </tr>
                        <tr>
                            <td class="ps-0 text-muted">Status</td>
                            <td><span class="badge bg-primary fs-6"><?php echo $data['header']['status']; ?></span></td>
                        </tr>
                    </table>
                    <div class="mt-4 d-grid gap-2">
                        <a href="index.php?page=warehouse_print&id=<?php echo $data['header']['id']; ?>" target="_blank" class="btn btn-secondary">
                            <i class="fas fa-print"></i> Cetak Dokumen Request
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Update -->
            <div class="card">
                <div class="card-header bg-soft-warning">
                    <h5 class="card-title mb-0">Update Status</h5>
                </div>
                <div class="card-body">
                    <form action="index.php?page=warehouse_update_status" method="POST">
                        <?php echo $this->getCsrfField(); ?>
                        <input type="hidden" name="id" value="<?php echo $data['header']['id']; ?>">
                        <input type="hidden" name="items_data" id="itemsDataInput">
                        <div class="d-grid gap-2">
                            <?php if ($data['header']['status'] == 'PENDING'): ?>
                            <div class="alert alert-warning text-center mb-2">Status: <b>PENDING</b></div>
                            <button type="submit" name="status" value="IN_PREPARATION" class="btn btn-primary">
                                <i class="fas fa-play-circle"></i> Proses Request
                            </button>
                            <?php elseif ($data['header']['status'] == 'IN_PREPARATION'): ?>
                            <button type="submit" name="status" value="READY" class="btn btn-success">
                                <i class="fas fa-check-double"></i> Barang Sudah Disiapkan
                            </button>
                            <?php elseif ($data['header']['status'] == 'READY'): ?>
                            <div class="alert alert-success text-center mb-2">Status: <b>READY</b></div>
                            <button type="submit" name="status" value="COMPLETED" class="btn btn-primary">
                                <i class="fas fa-truck"></i> Barang Sudah Diambil (Completed)
                            </button>
                            <?php else: ?>
                            <div class="alert alert-info text-center">Status: <b><?php echo $data['header']['status']; ?></b></div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Items Column -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h5 class="card-title mb-0 flex-grow-1">Daftar Barang Yang Harus Disiapkan</h5>
                </div>
                <div class="card-body p-0 p-md-3">
                    <!-- Desktop View -->
                    <div class="table-responsive desktop-view">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Kategori</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th class="text-center">Qty</th>
                                    <th>Tipe</th>
                                    <?php if ($showAssetCodePanel): ?>
                                    <th>Kode Aset Terpilih</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                    <td class="text-center fs-5"><?php echo $item['qty_request']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $item['item_type'] === 'ASET' ? 'bg-primary' : 'bg-secondary'; ?>">
                                            <?php echo $item['item_type']; ?>
                                        </span>
                                    </td>
                                    <?php if ($showAssetCodePanel): ?>
                                    <td>
                                        <?php if ($item['item_type'] === 'ASET'): ?>
                                            <?php $selected = $data['selectedCodes'][$item['request_item_id']] ?? []; ?>
                                            <?php if (!empty($selected)): ?>
                                                <?php foreach ($selected as $sc): ?>
                                                    <span class="badge bg-primary asset-code-tag me-1 mb-1"><?php echo htmlspecialchars($sc['asset_code']); ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted small">Belum dipilih</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile View -->
                    <div class="mobile-view mobile-list-container">
                        <?php foreach ($data['items'] as $item): ?>
                        <div class="mobile-item-card">
                            <div class="d-flex align-items-center justify-content-between mobile-item-header">
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                    <div class="text-muted small mt-1">
                                        <?php echo htmlspecialchars($item['category']); ?> • <?php echo htmlspecialchars($item['unit']); ?>
                                        <span class="badge <?php echo $item['item_type'] === 'ASET' ? 'bg-primary' : 'bg-secondary'; ?> ms-1"><?php echo $item['item_type']; ?></span>
                                    </div>
                                </div>
                                <div class="ms-3 text-end">
                                    <span class="qty-summary"><?php echo $item['qty_request']; ?> <?php echo htmlspecialchars($item['unit']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Asset Code Assignment Panel -->
            <?php if ($showAssetCodePanel): ?>
            <div class="card mt-3">
                <div class="card-header bg-soft-primary d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1"><i class="fas fa-tag me-2"></i>Assign Kode Aset</h5>
                </div>
                <div class="card-body">
                    <form action="index.php?page=warehouse_save_asset_codes" method="POST">
                        <?php echo $this->getCsrfField(); ?>
                        <input type="hidden" name="warehouse_request_id" value="<?php echo $data['header']['id']; ?>">

                        <?php $hasAnyAset = false; ?>
                        <?php foreach ($data['items'] as $item): ?>
                        <?php if ($item['item_type'] !== 'ASET') continue; $hasAnyAset = true; ?>
                        <?php
                            $availCodes = $data['availableAssetCodes'][$item['item_id']] ?? [];
                            $selectedIds = array_column($data['selectedCodes'][$item['request_item_id']] ?? [], 'asset_code_id');
                        ?>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <?php echo htmlspecialchars($item['item_name']); ?>
                                <span class="text-muted fw-normal small ms-1">(Qty: <?php echo $item['qty_request']; ?>)</span>
                            </label>
                            <?php if (empty($availCodes)): ?>
                                <div class="alert alert-warning py-2 mb-0">Belum ada kode aset terdaftar untuk item ini.</div>
                            <?php else: ?>
                                <select name="asset_codes[<?php echo $item['request_item_id']; ?>][]"
                                        class="form-select select2-asset"
                                        multiple
                                        data-placeholder="Pilih kode aset..."
                                        data-qty="<?php echo $item['qty_request']; ?>">
                                    <?php foreach ($availCodes as $code): ?>
                                    <option value="<?php echo $code['id']; ?>"
                                        <?php echo in_array($code['id'], $selectedIds) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($code['asset_code']); ?>
                                        <?php if ($code['usage_count'] > 0): ?>
                                            (Used <?php echo $code['usage_count']; ?>x)
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Pilih <?php echo $item['qty_request']; ?> kode aset sesuai jumlah qty.</div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>

                        <?php if ($hasAnyAset): ?>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Kode Aset
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Sticky Actions Footer (Mobile Only) -->
<div class="sticky-actions">
    <a href="index.php?page=warehouse_print&id=<?php echo $data['header']['id']; ?>" target="_blank" class="btn btn-outline-secondary btn-icon-only" title="Cetak Dokumen">
        <i class="fas fa-file-alt"></i>
    </a>
    <button type="button" class="btn btn-primary flex-grow-1" id="mobileProcessBtn">Proses Request</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-asset').each(function() {
        var maxQty = parseInt($(this).data('qty')) || 999;
        $(this).select2({
            placeholder: $(this).data('placeholder'),
            allowClear: true,
            width: '100%',
            templateResult: function(option) {
                if (!option.id) return option.text;
                var isUsed = option.text.indexOf('(Used') !== -1;
                var color = isUsed ? '#856404' : '#155724';
                return $('<span style="color:' + color + '">' + option.text + '</span>');
            }
        }).on('select2:select', function() {
            var selected = $(this).val() || [];
            if (selected.length > maxQty) {
                var vals = selected.slice(0, maxQty);
                $(this).val(vals).trigger('change');
            }
        });
    });

    $('#mobileProcessBtn').click(function() {
        var btn = $('form[action="index.php?page=warehouse_update_status"] button[type="submit"]:visible').first();
        if (btn.length > 0) { btn.click(); }
        else { alert('Tidak ada aksi yang tersedia untuk status ini.'); }
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>
