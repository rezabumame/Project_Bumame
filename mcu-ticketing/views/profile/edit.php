<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">My Profile</h1>
            <p class="page-header-subtitle">Manage your account settings and preferences.</p>
        </div>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="text-center pt-5 pb-4 px-4 bg-light bg-gradient border-bottom">
                        <!-- Avatar Circle -->
                        <?php 
                            $initials = strtoupper(substr($user_data['full_name'] ?? 'U', 0, 1));
                        ?>
                        <div class="mb-3 mx-auto d-flex align-items-center justify-content-center rounded-circle text-white shadow-lg" 
                             style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold; background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                            <?php echo $initials; ?>
                        </div>
                        
                        <h4 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($user_data['full_name'] ?? 'User'); ?></h4>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($user_data['username'] ?? '-'); ?></p>
                        
                        <span class="badge rounded-pill bg-dark px-3 py-2 shadow-sm">
                            <?php echo ucwords(str_replace('_', ' ', $user_data['role'] ?? '-')); ?>
                        </span>
                    </div>

                    <div class="p-4">
                        <div class="d-flex align-items-center p-3 bg-white rounded-3 border shadow-sm mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="fas fa-calendar-alt text-primary fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Joined Date</small>
                                <span class="fw-medium text-dark"><?php echo isset($user_data['created_at']) ? date('d F Y', strtotime($user_data['created_at'])) : '-'; ?></span>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center p-3 bg-white rounded-3 border shadow-sm">
                            <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3">
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Account Status</small>
                                <span class="fw-medium text-dark">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-user-shield me-2"></i>Security Settings</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2 fs-4"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2 fs-4"></i>
                                <div><?php echo $success; ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?page=profile_update" method="POST">
                        <p class="text-muted mb-4 small">Ensure your account is secure by using a strong password.</p>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase" for="inputOldPassword">Current Password</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-key"></i></span>
                                <input class="form-control bg-light border-start-0 ps-0" id="inputOldPassword" type="password" name="old_password" placeholder="Enter your current password" required />
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold text-dark small text-uppercase" for="inputNewPassword">New Password</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                                    <input class="form-control bg-light border-start-0 ps-0" id="inputNewPassword" type="password" name="new_password" placeholder="Min. 8 characters" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small text-uppercase" for="inputConfirmPassword">Confirm Password</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-check-double"></i></span>
                                    <input class="form-control bg-light border-start-0 ps-0" id="inputConfirmPassword" type="password" name="confirm_password" placeholder="Re-enter new password" required />
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-2">
                            <button class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-lg hover-scale" type="submit">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-scale {
        transition: transform 0.2s;
    }
    .hover-scale:hover {
        transform: translateY(-2px);
    }
</style>

<?php include '../views/layouts/footer.php'; ?>
