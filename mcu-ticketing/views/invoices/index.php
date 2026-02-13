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

    <?php if (in_array($_SESSION['role'], ['finance', 'superadmin'])): ?>
    <div class="card border-0 shadow-sm rounded-lg mb-4 bg-light">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-1 fw-bold"><i class="fas fa-file-import me-2"></i>Bulk Update Payment</h6>
                    <p class="small text-muted mb-0">Update status invoice (SENT -> PAID) secara massal menggunakan file Excel.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end gap-2 mt-2 mt-md-0">
                        <button type="button" onclick="downloadExcelTemplate()" class="btn btn-outline-info btn-sm rounded-pill px-3">
                            <i class="fas fa-download me-1"></i> Download Template
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                            <i class="fas fa-upload me-1"></i> Upload Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Modal -->
    <div class="modal fade" id="bulkUploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <form id="bulkUploadForm">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-upload me-2"></i>Upload File Excel</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih File Excel (.xlsx, .xls)</label>
                            <input type="file" id="bulk_file" name="bulk_file" class="form-control" accept=".xlsx, .xls" required>
                            <div class="form-text mt-2 small">
                                <i class="fas fa-info-circle me-1 text-primary"></i> Pastikan format file sesuai dengan template (No Invoice & Tanggal Pembayaran).
                            </div>
                        </div>
                        <div id="uploadStatus" class="mt-3" style="display: none;">
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                            </div>
                            <p class="small text-center text-muted mb-0">Memproses file...</p>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btnSubmitBulk" class="btn btn-primary px-4 rounded-pill fw-bold">Mulai Proses Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-file-invoice-dollar me-2"></i>Daftar Invoice (Finance)
            </h6>
            <?php if (in_array($_SESSION['role'], ['finance', 'superadmin'])): ?>
            <div class="d-flex gap-2 align-items-center">
                <form action="index.php" method="GET" class="d-flex gap-2 align-items-center mb-0">
                    <input type="hidden" name="page" value="invoice_processing_index">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="date" name="start_date" class="form-control" value="<?php echo $_GET['start_date'] ?? ''; ?>" title="Start Date">
                    </div>
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="date" name="end_date" class="form-control" value="<?php echo $_GET['end_date'] ?? ''; ?>" title="End Date">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold shadow-sm">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="index.php?page=invoice_processing_export_csv&start_date=<?php echo urlencode($_GET['start_date'] ?? ''); ?>&end_date=<?php echo urlencode($_GET['end_date'] ?? ''); ?>" class="btn btn-sm btn-success fw-bold shadow-sm" title="Export CSV">
                        <i class="fas fa-file-csv"></i> Export
                    </a>
                </form>
            </div>
            <?php endif; ?>
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
                            <th class="py-3">Tanggal Invoice</th>
                            <th class="py-3">Tanggal Bayar</th>
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
                                    <div class="small text-dark fw-bold"><?php echo $row['invoice_date'] ? date('d/m/Y', strtotime($row['invoice_date'])) : '-'; ?></div>
                                    <div class="small text-muted">Ref: <?php echo htmlspecialchars($row['request_number']); ?></div>
                                </td>
                                <td>
                                    <?php if ($row['payment_date']): ?>
                                        <div class="small text-success fw-bold">
                                            <i class="fas fa-calendar-check me-1"></i><?php echo date('d/m/Y', strtotime($row['payment_date'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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

        // Handle Bulk Upload Form
        $('#bulkUploadForm').on('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('bulk_file');
            const file = fileInput.files[0];
            if (!file) return;

            const submitBtn = $('#btnSubmitBulk');
            const statusDiv = $('#uploadStatus');
            
            submitBtn.prop('disabled', true);
            statusDiv.show();

            const reader = new FileReader();
            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                const jsonData = XLSX.utils.sheet_to_json(worksheet, {header: 1});

                //jsonData[0] is header
                // Expected columns: No Invoice, Tanggal Pembayaran
                const payload = [];
                for (let i = 1; i < jsonData.length; i++) {
                    const row = jsonData[i];
                    if (row.length >= 2) {
                        let paymentDate = row[1];
                        
                        // Handle Excel Date Number
                        if (typeof paymentDate === 'number') {
                            // SheetJS date conversion
                            const date = new Date((paymentDate - 25569) * 86400 * 1000);
                            if (!isNaN(date.getTime())) {
                                paymentDate = date.toISOString().split('T')[0];
                            }
                        }

                        payload.push({
                            invoice_number: row[0],
                            payment_date: paymentDate
                        });
                    }
                }

                if (payload.length === 0) {
                    Swal.fire('Error', 'File kosong atau format tidak sesuai.', 'error');
                    submitBtn.prop('disabled', false);
                    statusDiv.hide();
                    return;
                }

                // Send to server
                $.ajax({
                    url: 'index.php?page=invoice_processing_bulk_update_json',
                    method: 'POST',
                    data: JSON.stringify({data: payload}),
                    contentType: 'application/json',
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan saat mengirim data.', 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        statusDiv.hide();
                    }
                });
            };
            reader.readAsArrayBuffer(file);
        });
    });

    function downloadExcelTemplate() {
        // Fetch current SENT invoices for template
        $.ajax({
            url: 'index.php?page=invoice_processing_get_sent_json',
            method: 'GET',
            success: function(response) {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    const invoices = res.data;
                    const rows = [
                        ['No Invoice', 'Tanggal Pembayaran (YYYY-MM-DD)', 'Client']
                    ];
                    
                    invoices.forEach(inv => {
                        rows.push([inv.invoice_number, '', inv.client_company]);
                    });

                    const ws = XLSX.utils.aoa_to_sheet(rows);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, "Bulk Payment Template");

                    // Set column widths
                    ws['!cols'] = [
                        {wch: 25}, // No Invoice
                        {wch: 30}, // Tanggal Pembayaran
                        {wch: 40}, // Client
                    ];

                    XLSX.writeFile(wb, `template_bulk_payment_${new Date().getTime()}.xlsx`);
                } else {
                    Swal.fire('Error', 'Gagal mengambil data invoice.', 'error');
                }
            }
        });
    }
</script>

<?php include '../views/layouts/footer.php'; ?>
