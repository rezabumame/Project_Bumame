<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div id="page-content-wrapper">
    <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
        <div class="d-flex align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #204EAB;">Add Sales Person</h2>
                <p class="text-muted mb-0">Register a new sales team member.</p>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
            <div class="alert alert-danger">Failed to create sales person. Please try again.</div>
        <?php endif; ?>

        <div class="row my-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-card">
                    <div class="card-body p-4">
                        <form action="index.php?page=sales_persons_store" method="POST">
                            <div class="mb-3">
                                <label for="user_id" class="form-label fw-bold">Select Sales Person (User Account) <span class="text-danger">*</span></label>
                                <?php if (empty($available_users)): ?>
                                    <div class="alert alert-warning">
                                        No available 'sales' users found. Please <a href="index.php?page=users_create">create a new user</a> with role 'sales' first.
                                    </div>
                                <?php else: ?>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">Select User...</option>
                                        <?php foreach($available_users as $user): ?>
                                            <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Select a user to register as a Sales Person. The name will be automatically used.</div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="sales_manager_id" class="form-label fw-bold">Manager</label>
                                <div class="input-group">
                                    <select class="form-select" id="sales_manager_id" name="sales_manager_id">
                                        <option value="">Select Manager (Optional)</option>
                                        <?php foreach($managers as $mgr): ?>
                                            <option value="<?php echo $mgr['id']; ?>"><?php echo htmlspecialchars($mgr['manager_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <a href="index.php?page=sales_managers_create" class="btn btn-outline-secondary" title="Add New Manager">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                                <div class="form-text">Select a manager from the list or add a new one.</div>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <a href="index.php?page=sales_persons_index" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Sales Person</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
