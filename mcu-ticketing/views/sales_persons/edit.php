<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div id="page-content-wrapper">
    <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
        <div class="d-flex align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #204EAB;">Edit Sales Person</h2>
                <p class="text-muted mb-0">Update sales person information.</p>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
            <div class="alert alert-danger">Failed to update sales person. Please try again.</div>
        <?php endif; ?>

        <div class="row my-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-card">
                    <div class="card-body p-4">
                        <form action="index.php?page=sales_persons_update" method="POST">
                            <input type="hidden" name="id" value="<?php echo $sales->id; ?>">
                            <input type="hidden" id="sales_name" name="sales_name" value="<?php echo htmlspecialchars($sales->sales_name); ?>">
                            <div class="mb-3">
                                <label for="user_id" class="form-label fw-bold">Sales Person (User)</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Select User Account</option>
                                    <?php foreach($available_users as $user): ?>
                                        <option value="<?php echo $user['user_id']; ?>" <?php echo ($sales->user_id == $user['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Link this sales person to a user account for system access.</div>
                            </div>
                            <div class="mb-3">
                                <label for="sales_manager_id" class="form-label fw-bold">Manager</label>
                                <div class="input-group">
                                    <select class="form-select" id="sales_manager_id" name="sales_manager_id">
                                        <option value="">Select Manager (Optional)</option>
                                        <?php foreach($managers as $mgr): ?>
                                            <option value="<?php echo $mgr['id']; ?>" <?php echo ($sales->sales_manager_id == $mgr['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($mgr['manager_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <a href="index.php?page=sales_managers_create" class="btn btn-outline-secondary" title="Add New Manager">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <a href="index.php?page=sales_persons_index" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Sales Person</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
