<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Invoice Processing</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent ps-0">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Invoice Processing</li>
                </ol>
            </nav>
        </div>
        <div>
             <a href="index.php?page=invoice_processing_index" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-sync-alt me-1"></i> Refresh Data
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-file-invoice-dollar me-2"></i>Daftar Invoice (Finance)
            </h6>
        </div>
        <div class="card-body p-0">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="datatablesSimple">
                    <thead class="bg-light text-uppercase small fw-bold text-muted">
                        <tr>
                            <th class="ps-4 py-3">No Invoice</th>
                            <th class="py-3">Company / Client</th>
                            <th class="py-3">Total Amount</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3">Ref Request</th>
                            <th class="py-3">Tanggal</th>
                            <th class="pe-4 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $invoices->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary">
                                    <?php if($row['invoice_number']): ?>
                                        <i class="fas fa-file-alt me-2 text-muted"></i><?php echo htmlspecialchars($row['invoice_number']); ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill"><i class="fas fa-pencil-alt me-1"></i> DRAFT</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['company_name']); ?></div>
                                    <div class="small text-muted"><i class="fas fa-building me-1"></i><?php echo htmlspecialchars($row['client_company'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="fw-bold text-dark">
                                    Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $badgeClass = 'secondary';
                                    $badgeIcon = 'circle';
                                    $statusLabel = $row['status'];

                                    switch($row['status']) {
                                        case 'SENT':
                                            $badgeClass = 'primary';
                                            $badgeIcon = 'shipping-fast';
                                            break;
                                        case 'PAID':
                                            $badgeClass = 'success';
                                            $badgeIcon = 'check-double';
                                            break;
                                        case 'DRAFT':
                                        default:
                                            $badgeClass = 'secondary';
                                            $badgeIcon = 'file-signature';
                                            break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $badgeClass; ?> rounded-pill px-3 py-2 shadow-sm">
                                        <i class="fas fa-<?php echo $badgeIcon; ?> me-1"></i> <?php echo $statusLabel; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-dark fw-bold"><?php echo htmlspecialchars($row['request_number']); ?></div>
                                    <div class="small text-muted">Date: <?php echo date('d/m/Y', strtotime($row['request_date'])); ?></div>
                                </td>
                                <td>
                                    <div class="small text-muted" title="Created At">
                                        <i class="far fa-clock me-1"></i> <?php echo date('d M Y H:i', strtotime($row['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                <?php if (!in_array($_SESSION['role'], ['finance', 'superadmin'])): ?>
                                    <a href="index.php?page=invoice_processing_view&id=<?php echo $row['id']; ?>" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-bold">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                <?php elseif ($row['status'] == 'PAID'): ?>
                                    <a href="index.php?page=invoice_processing_view&id=<?php echo $row['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold">
                                        <i class="fas fa-eye me-1"></i> Lihat
                                    </a>
                                <?php elseif ($row['status'] == 'SENT'): ?>
                                        <a href="index.php?page=invoice_processing_edit&id=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold mb-1">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold mb-1" 
                                                data-bs-toggle="modal" data-bs-target="#paymentModal" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-number="<?php echo htmlspecialchars($row['invoice_number']); ?>">
                                            <i class="fas fa-check-double me-1"></i> Verifikasi
                                        </button>
                                    <?php else: ?>
                                        <a href="index.php?page=invoice_processing_edit&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-sm">
                                            <i class="fas fa-cog me-1"></i> Proses
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (isset($total_pages) && $total_pages > 1): ?>
            <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing <?php echo ($invoices->rowCount()); ?> of <?php echo $total_rows; ?> entries</small>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?page=invoice_processing_index&p=<?php echo $page - 1; ?>">&laquo; Prev</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?page=invoice_processing_index&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?page=invoice_processing_index&p=<?php echo $page + 1; ?>">Next &raquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Payment Confirmation Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentModalLabel"><i class="fas fa-check-double me-2"></i>Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=invoice_processing_pay" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="payment_invoice_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor Invoice</label>
                        <input type="text" class="form-control" id="payment_invoice_number" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="payment_date" class="form-label fw-bold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="payment_notes" class="form-label fw-bold">Catatan Pembayaran (Optional)</label>
                        <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3" placeholder="Contoh: Transfer via BCA, No Ref: ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold">Konfirmasi Lunas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#datatablesSimple').DataTable({
            paging: false, // PHP handled
            info: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            }
        });

        // Handle Payment Modal Data
        $('#paymentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var number = button.data('number');
            
            var modal = $(this);
            modal.find('#payment_invoice_id').val(id);
            modal.find('#payment_invoice_number').val(number);
        });
    });
</script>

<?php include '../views/layouts/footer.php'; ?>
