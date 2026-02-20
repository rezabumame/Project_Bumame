<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: #204EAB;"><?php echo isset($vendor_data) ? 'Edit Vendor' : 'Add New Vendor'; ?></h2>
            <p class="text-muted mb-0"><?php echo isset($vendor_data) ? 'Update vendor information.' : 'Register a new vendor.'; ?></p>
        </div>
        <a href="index.php?page=vendors" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card shadow mb-4" style="max-width: 800px;">
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="vendor_name" required value="<?php echo isset($vendor_data) ? htmlspecialchars($vendor_data['vendor_name'] ?? '') : ''; ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">PIC Name</label>
                        <input type="text" class="form-control" name="pic_name" value="<?php echo isset($vendor_data) ? htmlspecialchars($vendor_data['pic_name'] ?? '') : ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="phone_number" value="<?php echo isset($vendor_data) ? htmlspecialchars($vendor_data['phone_number'] ?? '') : ''; ?>">
                    </div>
                </div>

                <?php
                $services_list = ['Rontgen', 'EKG', 'Audiometri', 'Spirometri', 'USG Abdomen', 'USG Mammae', 'Papsmear', 'Autorefraksi', 'Treadmill'];
                $current_services = isset($vendor_data) && isset($vendor_data['services']) ? explode(',', $vendor_data['services']) : [];
                $current_services = array_map('trim', $current_services);
                
                // Calculate other services
                $other_services_array = array_diff($current_services, $services_list);
                // Filter out empty strings just in case
                $other_services_array = array_filter($other_services_array, function($value) { return !empty(trim($value)); });
                $other_services_value = implode(', ', $other_services_array);
                ?>
                <div class="mb-3">
                    <label class="form-label">Services / Handled Exams</label>
                    <div class="card p-3 bg-light border-0">
                        <div class="row">
                            <?php foreach($services_list as $service): ?>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="services[]" value="<?php echo $service; ?>" id="check_<?php echo str_replace(' ', '', $service); ?>" <?php echo in_array($service, $current_services) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="check_<?php echo str_replace(' ', '', $service); ?>">
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
                        <input type="text" class="form-control" name="other_services" value="<?php echo htmlspecialchars($other_services_value); ?>" placeholder="e.g. MRI, CT Scan (separate with comma)">
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary me-2">Save Vendor</button>
                    <a href="index.php?page=vendors_list" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
