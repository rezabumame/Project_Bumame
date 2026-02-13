<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Pilih Project untuk Invoice</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=invoice_requests_index">Invoice Requests</a></li>
        <li class="breadcrumb-item active">Pilih Project</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-list me-1"></i>
            Daftar Project Siap Invoice
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="index.php?page=invoice_requests_create&step=2" method="POST">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="datatablesSimple">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center"><input type="checkbox" id="selectAll"></th>
                                <th>Project ID</th>
                                <th>Nama Project</th>
                                <th>Company / Client</th>
                                <th>Tanggal MCU</th>
                                <th>Sales</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $hasProjects = false;
                            while ($row = $projects->fetch(PDO::FETCH_ASSOC)): 
                                $hasProjects = true;
                            ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="project_ids[]" value="<?php echo htmlspecialchars($row['project_id']); ?>" class="project-checkbox">
                                </td>
                                <td><?php echo htmlspecialchars($row['project_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_project']); ?></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td><?php echo DateHelper::formatSmartDateIndonesian($row['tanggal_mcu']); ?></td>
                                <td><?php echo htmlspecialchars($row['sales_name']); ?></td>
                                <td>
                                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['status_project']); ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <?php if (!$hasProjects): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada project yang siap untuk di-invoice.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" <?php echo !$hasProjects ? 'disabled' : ''; ?>>
                        Lanjut ke Form Invoice <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.project-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
});
</script>

<?php include '../views/layouts/footer.php'; ?>