<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: #204EAB;">Edit Sales Manager</h2>
            <p class="text-muted mb-0">Update sales manager information.</p>
        </div>
        <a href="index.php?page=sales_persons_index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <?php if(isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="index.php?page=sales_managers_update" method="POST">
                <input type="hidden" name="id" value="<?php echo $this->salesManager->id; ?>">
                <input type="hidden" id="manager_name" name="manager_name" value="<?php echo htmlspecialchars($this->salesManager->manager_name); ?>">
                <div class="mb-3">
                    <label for="user_id" class="form-label">Manager (User) <span class="text-danger">*</span></label>
                    <select class="form-select" id="user_id" name="user_id" required>
                        <option value="">Select User Account</option>
                        <?php foreach($available_users as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo ($this->salesManager->user_id == $user['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Link this manager to a user account for system access.</div>
                </div>
                <button type="submit" class="btn btn-primary">Update Manager</button>
            </form>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
