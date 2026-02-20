<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<style>
    /* Mobile View Styles */
    .mobile-list-container {
        padding-bottom: 80px; /* Space for sticky footer */
    }
    .mobile-item-card {
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s;
    }
    .mobile-item-card:active {
        background-color: #f8f9fa;
    }
    .mobile-item-header {
        padding: 12px 15px;
        cursor: pointer;
    }
    .mobile-item-details {
        padding: 0 15px 15px 15px;
        background-color: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }
    .qty-summary {
        font-size: 0.9rem;
        background: #eef2f7;
        padding: 4px 8px;
        border-radius: 4px;
        color: #333;
    }
    .sticky-actions {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        padding: 12px 15px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .sticky-actions .btn-icon-only {
        width: 42px;
        height: 42px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    /* Hide desktop table on mobile */
    @media (max-width: 767.98px) {
        .desktop-view { display: none !important; }
        .mobile-view { display: block !important; }
        .page-header-container { flex-direction: column; align-items: flex-start; gap: 10px; }
        .page-header-container > div { width: 100%; }
        .page-header-container .btn { width: 100%; }
        
        /* Mobile Signature Spacing */
        .signature-block { margin-bottom: 40px; }
        .signature-block:last-child { margin-bottom: 0; }
    }
    @media (min-width: 768px) {
        .desktop-view { display: block !important; }
        .mobile-view { display: none !important; }
        .sticky-actions { display: none; }
    }
</style>

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
                                    <td>
                                        <span class="badge bg-info"><?php echo $data['header']['warehouse_type']; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-0 text-muted">Status</td>
                                    <td>
                                        <span class="badge bg-primary fs-6"><?php echo $data['header']['status']; ?></span>
                                    </td>
                                </tr>
                            </table>

                            <div class="mt-4 d-grid gap-2">
                                <a href="index.php?page=warehouse_print&id=<?php echo $data['header']['id']; ?>" target="_blank" class="btn btn-secondary">
                                    <i class="fas fa-print"></i> Cetak Dokumen Request
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Status Update Form -->
                    <div class="card">
                        <div class="card-header bg-soft-warning">
                            <h5 class="card-title mb-0">Update Status</h5>
                        </div>
                        <div class="card-body">
                            <form action="index.php?page=warehouse_update_status" method="POST" enctype="multipart/form-data">
                                <?php echo $this->getCsrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo $data['header']['id']; ?>">
                                <input type="hidden" name="items_data" id="itemsDataInput">
                                
                                <div class="d-grid gap-2">
                                    <?php if ($data['header']['status'] == 'PENDING'): ?>
                                    <div class="alert alert-warning text-center mb-2">
                                        Status: <b>PENDING</b>
                                    </div>
                                    <button type="submit" name="status" value="IN_PREPARATION" class="btn btn-primary">
                                        <i class="fas fa-play-circle"></i> Proses Request
                                    </button>
                                    <?php elseif ($data['header']['status'] == 'IN_PREPARATION'): ?>
                                    <button type="submit" name="status" value="READY" class="btn btn-success">
                                        <i class="fas fa-check-double"></i> Barang Sudah Disiapkan
                                    </button>
                                    <?php elseif ($data['header']['status'] == 'READY'): ?>
                                    <div class="alert alert-success text-center mb-2">
                                        Status: <b>READY</b>
                                    </div>
                                    <button type="submit" name="status" value="COMPLETED" class="btn btn-primary">
                                        <i class="fas fa-truck"></i> Barang Sudah Diambil (Completed)
                                    </button>
                                    <?php else: ?>
                                    <div class="alert alert-info text-center">
                                        Status: <b><?php echo $data['header']['status']; ?></b>
                                    </div>
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
                                            <th class="text-center">Qty Request</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo $item['category']; ?></td>
                                            <td class="fw-bold"><?php echo $item['item_name']; ?></td>
                                            <td><?php echo $item['unit']; ?></td>
                                            <td class="text-center fs-5"><?php echo $item['qty_request']; ?></td>
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
                                        <!-- Info -->
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark"><?php echo $item['item_name']; ?></div>
                                            <div class="text-muted small mt-1">
                                                <?php echo $item['category']; ?> • <?php echo $item['unit']; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Qty Summary -->
                                        <div class="ms-3 text-end">
                                            <span class="qty-summary">
                                                <?php echo $item['qty_request']; ?> <?php echo htmlspecialchars($item['unit']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

</div>

<!-- Sticky Actions Footer (Mobile Only) -->
<div class="sticky-actions">
    <a href="index.php?page=warehouse_print&id=<?php echo $data['header']['id']; ?>" target="_blank" class="btn btn-outline-secondary btn-icon-only" title="Cetak Dokumen">
        <i class="fas fa-file-alt"></i>
    </a>
    <button type="button" class="btn btn-primary flex-grow-1" id="mobileProcessBtn">
        Proses Request
    </button>
</div>

<script>
    $(document).ready(function() {
        $('#mobileProcessBtn').click(function() {
            var btn = $('form[action="index.php?page=warehouse_update_status"] button[type="submit"]:visible').first();
            if(btn.length > 0) {
                btn.click();
            } else {
                alert('Tidak ada aksi yang tersedia untuk status ini.');
            }
        });
    });
</script>

<?php include '../views/layouts/footer.php'; ?>