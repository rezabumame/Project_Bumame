<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Vendor Management</h1>
            <p class="page-header-subtitle">Manage external vendors and service providers.</p>
        </div>
        <?php if ($_SESSION['role'] == 'admin_ops' || $_SESSION['role'] == 'procurement' || $_SESSION['role'] == 'superadmin'): ?>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addVendorModal">
            <i class="fas fa-plus me-2"></i>Add New Vendor
        </button>
        <?php endif; ?>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <?php if($_GET['status'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Vendor added successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif($_GET['status'] == 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Vendor updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif($_GET['status'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Vendor deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="vendorTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Vendor Name</th>
                            <th>PIC</th>
                            <th>Phone</th>
                            <th>Services / Exams</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($vendors) > 0): ?>
                            <?php foreach ($vendors as $row): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($row['vendor_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['pic_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone_number'] ?? ''); ?></td>
                                    <td>
                                        <?php 
                                            echo nl2br(htmlspecialchars($row['services'] ?? '')); 
                                        ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($_SESSION['role'] == 'admin_ops' || $_SESSION['role'] == 'procurement' || $_SESSION['role'] == 'superadmin'): ?>
                                            <a href="index.php?page=vendors_edit&id=<?php echo $row['vendor_id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?page=vendors_delete&id=<?php echo $row['vendor_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this vendor?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No vendors found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Vendor Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1" aria-labelledby="addVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVendorModalLabel">Add New Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="index.php?page=vendors_create">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="vendor_name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PIC Name</label>
                            <input type="text" class="form-control" name="pic_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone_number">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Services / Handled Exams</label>
                        <div class="card p-3 bg-light border-0">
                            <div class="row">
                                <?php
                                $services_list = ['Rontgen', 'EKG', 'Audiometri', 'Spirometri', 'USG Abdomen', 'USG Mammae', 'Papsmear', 'Autorefraksi', 'Treadmill'];
                                foreach($services_list as $service): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="services[]" value="<?php echo $service; ?>" id="check_modal_<?php echo str_replace(' ', '', $service); ?>">
                                        <label class="form-check-label" for="check_modal_<?php echo str_replace(' ', '', $service); ?>">
                                            <?php echo $service; ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="form-text mt-2">Select the exam types this vendor can handle.</div>
                        <div class="mt-2">
                            <label class="form-label">Other Services (Free Text)</label>
                            <input type="text" class="form-control" name="other_services" placeholder="e.g. MRI, CT Scan (separate with comma)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#vendorTable').DataTable();
    });
</script>

<?php include '../views/layouts/footer.php'; ?>
