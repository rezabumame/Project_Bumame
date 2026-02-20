<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Detail Invoice</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent ps-0">
                    <li class="breadcrumb-item"><a href="index.php?page=invoice_processing_index">Invoice Processing</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View #<?php echo $invoice['id']; ?></li>
                </ol>
            </nav>
        </div>
        <div>
             <a href="index.php?page=invoice_processing_index" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Invoice Details & Items -->
        <div class="col-xl-8">
            <div class="card h-100 border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Detail Tagihan
                    </h6>
                    <div class="small text-muted">
                        <i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($invoice['company_name']); ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4 p-3 bg-light rounded mx-1">
                        <div class="col-md-7 border-end">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="30%">Ref Request</td>
                                    <td class="fw-bold text-dark">: 
                                        <?php echo htmlspecialchars($invoice['request_number']); ?>
                                        <a href="index.php?page=invoice_requests_print&id=<?php echo $invoice['invoice_request_id']; ?>" target="_blank" class="btn btn-sm btn-outline-info ms-2 py-0 px-2" title="Lihat Dokumen Request">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Sales PIC</td>
                                    <td class="fw-bold text-dark">: <?php echo htmlspecialchars($invoice['client_pic']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Terms</td>
                                    <td class="text-dark">: <?php echo nl2br(htmlspecialchars($invoice['invoice_terms'])); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Link NPWP</td>
                                    <td class="text-dark">: 
                                        <?php if (!empty($invoice['link_gdrive_npwp'])): ?>
                                            <a href="<?php echo htmlspecialchars($invoice['link_gdrive_npwp']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-id-card me-1"></i> NPWP
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Link Absensi</td>
                                    <td class="text-dark">: 
                                        <?php if (!empty($invoice['link_gdrive_absensi'])): ?>
                                            <a href="<?php echo htmlspecialchars($invoice['link_gdrive_absensi']); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-file-excel me-1"></i> Absensi
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Alamat Kirim</td>
                                    <td class="text-dark">: <?php echo nl2br(htmlspecialchars($invoice['shipping_address'] ?? '-')); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Notes</td>
                                    <td class="text-dark">: <?php echo nl2br(htmlspecialchars($invoice['notes'] ?? '-')); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-5 d-flex flex-column justify-content-center align-items-end ps-4">
                            <small class="text-uppercase text-muted fw-bold mb-1">Total Amount</small>
                            <h2 class="text-primary fw-bold mb-2">Rp <?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?></h2>
                            <?php 
                            $badgeClass = 'secondary';
                            switch($invoice['status']) {
                                case 'SENT': $badgeClass = 'primary'; break;
                                case 'PAID': $badgeClass = 'success'; break;
                                default: $badgeClass = 'secondary'; break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $badgeClass; ?> rounded-pill px-3 py-2">
                                <?php echo $invoice['status']; ?>
                            </span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary mb-3 px-1"><i class="fas fa-folder-open me-2"></i>Dokumen Pendukung</h6>
                    <div class="mb-4">
                        <?php if (!empty($invoice['linked_projects'])): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($invoice['linked_projects'] as $proj): ?>
                                    <div class="border rounded p-3">
                                        <div class="fw-bold mb-2"><i class="fas fa-briefcase me-1"></i><?php echo htmlspecialchars($proj['nama_project']); ?></div>
                                        <div class="mb-2">
                                            <span class="small text-uppercase text-muted fw-bold">BA</span>
                                            <div class="mt-2 d-flex flex-wrap gap-2">
                                                <?php 
                                                    $records = isset($proj['ba_records']) ? $proj['ba_records'] : [];
                                                    $schedule = isset($proj['schedule_dates']) ? $proj['schedule_dates'] : [];
                                                ?>
                                                <?php if (!empty($records)): ?>
                                                    <?php foreach ($records as $rec): ?>
                                                        <?php $d = date('d M Y', strtotime($rec['tanggal_mcu'])); ?>
                                                        <?php if ($rec['status'] === 'uploaded' && !empty($rec['file_path'])): ?>
                                                            <a href="index.php?page=download_ba&project_id=<?php echo $proj['project_id']; ?>&date=<?php echo htmlspecialchars($rec['tanggal_mcu']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                BA - <?php echo $d; ?>
                                                            </a>
                                                        <?php elseif ($rec['status'] === 'cancelled'): ?>
                                                            <span class="badge bg-danger">BA - <?php echo $d; ?> (Cancelled)</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark">BA - <?php echo $d; ?> (Pending)</span>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php elseif (!empty($schedule)): ?>
                                                    <?php foreach ($schedule as $sd): ?>
                                                        <?php $d = date('d M Y', strtotime($sd)); ?>
                                                        <span class="badge bg-warning text-dark">BA - <?php echo $d; ?> (Pending)</span>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="mb-1">
                                            <span class="small text-uppercase text-muted fw-bold">SPH</span>
                                            <div class="mt-2">
                                                <?php if (!empty($proj['sph_file'])): ?>
                                                    <?php if (preg_match('#^https?://#', $proj['sph_file'])): ?>
                                                        <a href="<?php echo htmlspecialchars($proj['sph_file']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-file-pdf me-1"></i> SPH
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="index.php?page=download_sph&project_id=<?php echo $proj['project_id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-file-pdf me-1"></i> SPH
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted">Tidak ada project terkait.</div>
                        <?php endif; ?>
                    </div>

                    <h6 class="fw-bold text-secondary mb-3 px-1"><i class="fas fa-list-ol me-2"></i>Rincian Item</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="ps-3 py-3">Deskripsi</th>
                                    <th class="text-end py-3">Harga</th>
                                    <th class="text-center py-3">Qty</th>
                                    <th class="text-end pe-3 py-3">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoice['items'] as $item): ?>
                                    <tr>
                                        <td class="ps-3"><?php echo htmlspecialchars($item['description']); ?></td>
                                        <td class="text-end text-muted">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                        <td class="text-center"><?php echo $item['qty']; ?></td>
                                        <td class="text-end pe-3 fw-bold text-dark">Rp <?php echo number_format($item['total'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end py-3 fw-bold text-uppercase small">Grand Total</td>
                                    <td class="text-end pe-3 py-3 fw-bold text-primary h6 m-0">Rp <?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Status & Info (Read Only) -->
        <div class="col-xl-4">
            <div class="card h-100 border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-eye me-2"></i>Informasi Finance
                    </h6>
                </div>
                <div class="card-body">
                    <!-- 1. Validasi & Input Resmi -->
                    <div class="mb-4">
                        <h6 class="small fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">
                            <span class="badge bg-light text-dark border me-1">1</span> Invoice Resmi
                        </h6>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nomor Invoice</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-barcode text-muted"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($invoice['invoice_number'] ?? '-'); ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tanggal Invoice</label>
                            <input type="text" class="form-control" value="<?php echo $invoice['invoice_date'] ? date('d/m/Y', strtotime($invoice['invoice_date'])) : '-'; ?>" readonly>
                        </div>
                    </div>

                    <!-- 2. Pengiriman -->
                    <div class="mb-4">
                        <h6 class="small fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">
                            <span class="badge bg-light text-dark border me-1">2</span> Pengiriman
                        </h6>
                        <div class="form-check p-3 border rounded bg-light mb-3">
                            <input type="checkbox" class="form-check-input" <?php echo ($invoice['is_hardcopy_sent']) ? 'checked' : ''; ?> disabled>
                            <label class="form-check-label fw-bold small text-dark">Hardcopy Dikirim?</label>
                        </div>
                        <?php if ($invoice['is_hardcopy_sent']): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nomor Resi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-truck text-muted"></i></span>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($invoice['delivery_receipt_number'] ?? '-'); ?>" readonly>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- 3. Pembayaran -->
                    <?php if ($invoice['status'] == 'PAID'): ?>
                    <div class="mb-4">
                        <h6 class="small fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">
                            <span class="badge bg-light text-dark border me-1">3</span> Pembayaran
                        </h6>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tanggal Bayar</label>
                            <input type="text" class="form-control" value="<?php echo $invoice['payment_date'] ? date('d/m/Y', strtotime($invoice['payment_date'])) : '-'; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catatan Pembayaran</label>
                            <div class="p-2 border rounded bg-light small">
                                <?php echo nl2br(htmlspecialchars($invoice['payment_notes'] ?? '-')); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card bg-light border-0 p-3 mb-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark mb-2">Status:</label>
                            <?php if ($invoice['status'] == 'PAID'): ?>
                                <div class="alert alert-success py-2 px-3 mb-0">
                                    <i class="fas fa-check-circle me-2"></i><strong>PAID</strong> (Lunas)
                                </div>
                            <?php elseif ($invoice['status'] == 'SENT'): ?>
                                <div class="alert alert-primary py-2 px-3 mb-0">
                                    <i class="fas fa-paper-plane me-2"></i><strong>SENT</strong> (Terkirim)
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary py-2 px-3 mb-0">
                                    <i class="fas fa-pencil-alt me-2"></i><strong>DRAFT</strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
