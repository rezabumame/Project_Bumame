<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Invoice Requests</h1>
            <p class="page-header-subtitle">Daftar Pengajuan Invoice</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (in_array($_SESSION['role'], ['finance', 'superadmin'])): ?>
            <form action="index.php" method="GET" class="d-flex gap-2">
                <input type="hidden" name="page" value="invoice_requests_index">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-calendar-alt text-muted"></i>
                    </span>
                    <input type="date" name="start_date" class="form-control border-start-0" value="<?php echo $_GET['start_date'] ?? ''; ?>" placeholder="Start Date">
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-calendar-alt text-muted"></i>
                    </span>
                    <input type="date" name="end_date" class="form-control border-start-0" value="<?php echo $_GET['end_date'] ?? ''; ?>" placeholder="End Date">
                </div>
                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3 shadow-sm">Filter</button>
                <a href="index.php?page=invoice_requests_export_csv&start_date=<?php echo urlencode($_GET['start_date'] ?? ''); ?>&end_date=<?php echo urlencode($_GET['end_date'] ?? ''); ?>" class="btn btn-sm btn-success fw-bold px-3 shadow-sm" title="Export CSV">
                    <i class="fas fa-file-csv me-1"></i> Export
                </a>
            </form>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] == 'admin_sales' || $_SESSION['role'] == 'superadmin'): ?>
                <a href="index.php?page=invoice_requests_create" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus me-2"></i>Buat Request Baru
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <i class="fas fa-file-invoice-dollar me-1 text-primary"></i>
            Data Invoice Requests
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-hover table-striped table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>No Request</th>
                            <th>ID Project</th>
                            <th>Reff No SPH</th>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Sales / PIC</th>
                            <th>Company / Client</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = $requests->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['request_number']); ?></td>
                                <td class="small"><?php echo htmlspecialchars($row['project_ids'] ?? '-'); ?></td>
                                <td class="small"><?php echo htmlspecialchars($row['sph_numbers'] ?? '-'); ?></td>
                                <td><?php echo DateHelper::formatSmartDateIndonesian($row['request_date']); ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo htmlspecialchars($row['partner_type'] ?? '-'); ?>
                                    </span>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['event_type'] ?? '-'); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['sales_name']); ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['client_company']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['client_pic']); ?></small>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $badgeClass = 'secondary';
                                    $statusIcon = 'fa-clock';
                                    
                                    if ($row['status'] == 'SUBMITTED') {
                                        $badgeClass = 'info text-dark';
                                        $statusIcon = 'fa-paper-plane';
                                    } elseif ($row['status'] == 'APPROVED_SALES') {
                                        $badgeClass = 'primary';
                                        $statusIcon = 'fa-user-check';
                                    } elseif ($row['status'] == 'APPROVED_SPV') {
                                        $badgeClass = 'warning text-dark';
                                        $statusIcon = 'fa-user-tie';
                                    } elseif ($row['status'] == 'APPROVED_MANAGER') {
                                        $badgeClass = 'success';
                                        $statusIcon = 'fa-check-double';
                                    } elseif ($row['status'] == 'PROCESSED') {
                                        $badgeClass = 'primary';
                                        $statusIcon = 'fa-cog';
                                    } elseif ($row['status'] == 'COMPLETED') {
                                        $badgeClass = 'success';
                                        $statusIcon = 'fa-check-circle';
                                    } elseif ($row['status'] == 'CANCELLED') {
                                        $badgeClass = 'danger';
                                        $statusIcon = 'fa-times-circle';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $badgeClass; ?> rounded-pill">
                                        <i class="fas <?php echo $statusIcon; ?> me-1"></i><?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?page=invoice_requests_view&id=<?php echo $row['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($row['status'] == 'DRAFT' && ($_SESSION['role'] == 'admin_sales' || $_SESSION['role'] == 'superadmin')): ?>
                                        <a href="index.php?page=invoice_requests_edit&id=<?php echo $row['id']; ?>" 
                                           class="btn btn-outline-warning btn-sm" 
                                           title="Edit Draft">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?page=invoice_requests_delete&id=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                                           class="btn btn-outline-danger btn-sm swal-confirm-delete" 
                                           title="Hapus Draft">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.swal-confirm-delete').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        
        Swal.fire({
            title: 'Hapus Draft?',
            text: 'Apakah Anda yakin ingin menghapus draft invoice ini? Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>
