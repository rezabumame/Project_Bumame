<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: #204EAB;">Create Sales Manager</h2>
            <p class="text-muted mb-0">Add a new sales manager to the system.</p>
        </div>
        <a href="index.php?page=sales_persons_index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="index.php?page=sales_managers_store" method="POST">
                <div class="mb-3">
                    <label for="user_id" class="form-label">Select Sales Manager (User Account) <span class="text-danger">*</span></label>
                    <?php if (empty($available_users)): ?>
                        <div class="alert alert-warning">
                            No available 'manager_sales' users found. Please <a href="index.php?page=users_create">create a new user</a> with role 'manager_sales' first.
                        </div>
                    <?php else: ?>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select User...</option>
                            <?php foreach($available_users as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Select a user to register as a Sales Manager. The name will be automatically used.</div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Save Manager</button>
            </form>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>