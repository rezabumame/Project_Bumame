<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Procurement Dashboard</h1>
            <p class="page-header-subtitle">Overview of procurement activities and needs.</p>
        </div>
        <div class="text-muted small"><?php echo date('d M Y'); ?></div>
    </div>

    <!-- Widgets -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card bg-warning text-white h-100 shadow-sm rounded-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Pending Vendor Assignment</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $stats['process_vendor']; ?></h2>
                            <small>Projects needing vendor</small>
                        </div>
                        <i class="fas fa-truck-loading fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-info text-white h-100 shadow-sm rounded-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Pending Consumption Approval</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $stats['need_consumption']; ?></h2>
                            <small>Projects needing food approval</small>
                        </div>
                        <i class="fas fa-utensils fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card shadow-sm rounded-card">
        <div class="card-header bg-white border-0 py-3">
             <h5 class="mb-0 text-primary fw-bold">Quick Actions</h5>
        </div>
        <div class="card-body">
            <p>Go to "All Projects" to manage assignments and approvals.</p>
            <a href="index.php?page=all_projects" class="btn btn-primary">View Projects</a>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
