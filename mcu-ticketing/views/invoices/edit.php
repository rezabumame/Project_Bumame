<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>
<?php $isFinance = in_array($_SESSION['role'], ['finance', 'superadmin']); ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Proses Invoice</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent ps-0">
                    <li class="breadcrumb-item"><a href="index.php?page=invoice_processing_index">Invoice Processing</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit #<?php echo $invoice['id']; ?></li>
                </ol>
            </nav>
        </div>
        <div>
             <a href="index.php?page=invoice_processing_index" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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

        <!-- Right Column: Processing Form -->
        <div class="col-xl-4">
            <div class="card h-100 border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-edit me-2"></i>Form Proses Finance
                    </h6>
                </div>
                <div class="card-body">
                    <form action="index.php?page=invoice_processing_update" method="POST">
                        <input type="hidden" name="id" value="<?php echo $invoice['id']; ?>">

                        <!-- 1. Validasi & Input Resmi -->
                        <div class="mb-4">
                            <h6 class="small fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">
                                <span class="badge bg-light text-dark border me-1">1</span> Invoice Resmi
                            </h6>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nomor Invoice <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-barcode text-muted"></i></span>
                                    <input type="text" class="form-control" name="invoice_number" 
                                           value="<?php echo htmlspecialchars($invoice['invoice_number'] ?? ''); ?>"
                                           <?php echo (!$isFinance || $invoice['status'] == 'PAID') ? 'readonly' : ''; ?>
                                           placeholder="Contoh: INV/2026/001">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Tanggal Invoice</label>
                                <input type="date" class="form-control" name="invoice_date" 
                                       value="<?php echo $invoice['invoice_date']; ?>"
                                       <?php echo (!$isFinance || $invoice['status'] == 'PAID') ? 'readonly' : ''; ?>>
                            </div>
                        </div>

                        <!-- 2. Pengiriman -->
                        <div class="mb-4">
                            <h6 class="small fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">
                                <span class="badge bg-light text-dark border me-1">2</span> Pengiriman
                            </h6>
                            <div class="form-check p-3 border rounded bg-light mb-3">
                                <input type="checkbox" class="form-check-input" name="is_hardcopy_sent" id="hardcopyCheck" 
                                       <?php echo ($invoice['is_hardcopy_sent']) ? 'checked' : ''; ?>
                                       <?php echo (!$isFinance || $invoice['status'] == 'PAID') ? 'disabled' : ''; ?>>
                                <label class="form-check-label fw-bold small text-dark" for="hardcopyCheck">Hardcopy Dikirim?</label>
                            </div>
                            <div class="mb-3" id="resiContainer" style="<?php echo ($invoice['is_hardcopy_sent']) ? '' : 'display:none;'; ?>">
                                <label class="form-label small fw-bold">Nomor Resi (Jika ada)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-truck text-muted"></i></span>
                                    <input type="text" class="form-control" name="delivery_receipt_number" 
                                           value="<?php echo htmlspecialchars($invoice['delivery_receipt_number'] ?? ''); ?>"
                                           <?php echo (!$isFinance || $invoice['status'] == 'PAID') ? 'readonly' : ''; ?>>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Pembayaran (Visible only if PAID) -->
                        <?php if ($invoice['status'] == 'PAID'): ?>
                        <div class="mb-4">
                            <h6 class="small fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">
                                <span class="badge bg-light text-dark border me-1">3</span> Pembayaran
                            </h6>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Tanggal Bayar</label>
                                <input type="date" class="form-control" name="payment_date" 
                                       value="<?php echo $invoice['payment_date']; ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Catatan Pembayaran</label>
                                <textarea class="form-control" name="payment_notes" rows="2" readonly><?php echo htmlspecialchars($invoice['payment_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="card bg-light border-0 p-3 mb-3">
                            <!-- Status Display Removed as per request -->
                            <?php if ($invoice['status'] == 'PAID'): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark mb-2">Status:</label>
                                    <div class="alert alert-success py-2 px-3 mb-0">
                                        <i class="fas fa-check-circle me-2"></i><strong>PAID</strong> (Lunas)
                                    </div>
                                    <input type="hidden" name="status" value="PAID">
                                    <div class="mt-2 small text-danger fw-bold"><i class="fas fa-lock me-1"></i> Status PAID mengunci data.</div>
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2">
                                <?php if ($isFinance && $invoice['status'] != 'PAID'): ?>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <button type="submit" name="action" value="draft" class="btn btn-outline-secondary w-100 fw-bold py-2 shadow-sm">
                                                <i class="fas fa-save me-2"></i>Simpan Draft
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" name="action" value="process" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                                                <i class="fas fa-paper-plane me-2"></i>Proses
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted fst-italic">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <strong>Draft:</strong> Simpan sementara.<br>
                                            <strong>Proses:</strong> Terbitkan Invoice (SENT).
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($isFinance && $invoice['status'] == 'SENT'): ?>
                                    <button type="button" class="btn btn-success fw-bold py-2 shadow-sm mt-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                        <i class="fas fa-check-double me-2"></i>Verifikasi Pembayaran (Close)
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($invoice['status'] == 'PAID'): ?>
                                    <button type="button" class="btn btn-secondary fw-bold py-2" disabled>
                                        <i class="fas fa-check-double me-2"></i>Sudah Lunas
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=invoice_processing_pay" method="POST">
                <input type="hidden" name="id" value="<?php echo $invoice['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Konfirmasi Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle me-1"></i> Pastikan pembayaran telah diterima sepenuhnya. Tindakan ini akan mengubah status invoice menjadi <strong>PAID</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Bayar <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="payment_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan Pembayaran</label>
                        <textarea class="form-control" name="payment_notes" rows="3" placeholder="Masukkan detail pembayaran, bank, dll."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="fas fa-check me-1"></i> Simpan & Tandai Lunas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hardcopyCheck = document.getElementById('hardcopyCheck');
        const resiContainer = document.getElementById('resiContainer');

        if(hardcopyCheck && resiContainer) {
            hardcopyCheck.addEventListener('change', function() {
                if (this.checked) {
                    resiContainer.style.display = 'block';
                } else {
                    resiContainer.style.display = 'none';
                }
            });
        }
    });
</script>

<?php include '../views/layouts/footer.php'; ?>
