<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<style>
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0c63e4;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    .badge-gimmick {
        font-size: 0.8em;
        vertical-align: middle;
    }
    .card-sales-person {
        transition: all 0.2s;
        border-left: 4px solid transparent;
    }
    .card-sales-person:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
    }
    .sales-stats-box {
        background: #f8f9fc;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        border: 1px solid #e3e6f0;
    }
    .sales-stats-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #858796;
        font-weight: bold;
    }
    .sales-stats-value {
        font-size: 1.2rem;
        font-weight: bold;
        color: #5a5c69;
    }
    .crown-icon {
        color: #f6c23e;
        margin-right: 5px;
    }
    .trophy-icon {
        color: #e74a3b;
        margin-right: 5px;
    }
    /* New Clean Design Styles */
    .stat-bar-container {
        width: 100%;
    }
    .stat-value-text {
        font-weight: 700;
        color: #333;
        font-size: 1rem;
    }
    .stat-label-text {
        font-size: 0.75rem;
        color: #888;
        font-weight: 500;
        text-transform: uppercase;
    }
    .custom-progress {
        height: 8px;
        background-color: #f1f3f9;
        border-radius: 10px;
        overflow: hidden;
        width: 100%;
        display: flex; /* Ensure inner bar stretches */
    }
    .progress-bar-dark-blue {
        background-color: #1a237e; /* Dark Blue */
        height: 100%;
    }
    .progress-bar-light-blue {
        background-color: #4fc3f7; /* Light Blue */
        height: 100%;
    }
    .dropdown-toggle::after {
        display: none;
    }
    .btn-icon-only {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #6c757d;
        transition: all 0.2s;
    }
    .btn-icon-only:hover, .btn-icon-only:focus {
        background-color: #f8f9fa;
        color: #333;
    }
    .sales-person-row {
        background: #fff;
        border: 1px solid #edf2f9;
        border-radius: 12px !important;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .sales-person-row:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
        border-color: #cce5ff;
    }
</style>

<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Sales Team Performance (Dashboard)</h1>
            <p class="page-header-subtitle">Overview of workload distribution and top performers.</p>
        </div>
        <div>
            <?php if(in_array($_SESSION['role'], ['superadmin','admin_sales'])): ?>
                <button type="button" class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalSalesManager" onclick="prepareAddSalesManager()">
                    <i class="fas fa-plus me-2"></i>New Manager
                </button>
                <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSalesPerson" onclick="prepareAddSalesPerson()">
                    <i class="fas fa-plus me-2"></i>New Sales Person
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_GET['status']) || isset($_SESSION['success_message']) || isset($_SESSION['error_message'])): ?>
        <div class="row mb-3">
            <div class="col-12">
                <?php if (isset($_GET['status'])): ?>
                    <?php if ($_GET['status'] == 'success'): ?>
                        <div class="alert alert-success alert-dismissible fade show">Action completed successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php elseif ($_GET['status'] == 'updated'): ?>
                        <div class="alert alert-success alert-dismissible fade show">Updated successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php elseif ($_GET['status'] == 'deleted'): ?>
                        <div class="alert alert-success alert-dismissible fade show">Deleted successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php elseif ($_GET['status'] == 'error'): ?>
                        <div class="alert alert-danger alert-dismissible fade show">An error occurred!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Global Stats Cards -->
    <div class="row mb-4">
        <!-- Total Pax -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pax (All Time)</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800" style="font-size: 2.5rem;"><?php echo number_format($globalStats['total_pax']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Projects -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Active Projects</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800" style="font-size: 2.5rem;"><?php echo number_format($globalStats['total_projects']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performer (Pax) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Top Sales (Pax)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $globalTopPax ? htmlspecialchars($globalTopPax['sales_name']) : '-'; ?>
                            </div>
                            <small class="text-muted"><?php echo $globalTopPax ? number_format($globalTopPax['total_pax']) . ' Pax' : ''; ?></small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performer (Projects) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Most Active Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $globalTopProjects ? htmlspecialchars($globalTopProjects['sales_name']) : '-'; ?>
                            </div>
                            <small class="text-muted"><?php echo $globalTopProjects ? number_format($globalTopProjects['total_projects']) . ' Projects' : ''; ?></small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Managers Accordion -->
    <div class="accordion shadow mb-5" id="managersAccordion">
        <?php foreach ($hierarchy as $index => $group): ?>
            <?php 
                $mgr = $group['manager'];
                $team = $group['team'];
                $stats = $group['stats'];
                $mgrId = $mgr['id'];
                $collapseId = "collapseManager" . $mgrId;
                $headingId = "headingManager" . $mgrId;
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false" aria-controls="<?php echo $collapseId; ?>">
                        <div class="d-flex w-100 justify-content-between align-items-center pe-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($mgr['name'] ?? $mgr['manager_name']); ?></h5>
                                    <small class="text-muted"><?php echo count($team); ?> Sales Persons</small>
                                </div>
                            </div>
                            
                            <!-- Manager Summary Stats -->
                            <div class="d-none d-md-flex gap-4 text-center">
                                <div>
                                    <span class="d-block text-xs fw-bold text-uppercase text-muted">Total Pax</span>
                                    <span class="fw-bold text-dark h5 mb-0"><?php echo number_format($stats['total_pax']); ?></span>
                                </div>
                                <div>
                                    <span class="d-block text-xs fw-bold text-uppercase text-muted">Projects</span>
                                    <span class="fw-bold text-dark h5 mb-0"><?php echo number_format($stats['total_projects']); ?></span>
                                </div>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse" aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#managersAccordion">
                    <div class="accordion-body bg-light">
                        <!-- Manager Actions -->
                        <div class="d-flex justify-content-end mb-3">
                            <?php if($mgrId > 0 && in_array($_SESSION['role'], ['superadmin','admin_sales'])): ?>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="prepareEditSalesManager('<?php echo $mgrId; ?>', '<?php echo htmlspecialchars(addslashes($mgr['name'] ?? $mgr['manager_name'])); ?>', '<?php echo $mgr['user_id']; ?>')" data-bs-toggle="modal" data-bs-target="#modalSalesManager">
                                    <i class="fas fa-edit me-1"></i> Edit Manager
                                </button>
                                <a href="index.php?page=sales_managers_delete&id=<?php echo $mgrId; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this manager?');">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Sales Persons List -->
                        <?php if (empty($team)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-user-slash fa-2x mb-2"></i>
                                <p>No sales persons assigned yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($team as $sp): ?>
                                    <?php 
                                        $spId = $sp['id'];
                                        $spCollapseId = "collapseSP" . $spId;
                                        
                                        // Check for Gimmicks
                                        $isTopPax = ($stats['top_pax_sales'] && $stats['top_pax_sales']['id'] == $spId);
                                        $isTopProj = ($stats['top_project_sales'] && $stats['top_project_sales']['id'] == $spId);
                                    ?>
                                    <div class="sales-person-row shadow-sm">
                                        <!-- Sales Person Header -->
                                        <div class="p-3">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                <!-- Identity -->
                                                <div class="d-flex align-items-center mb-2 mb-md-0" style="min-width: 250px; flex: 1;">
                                                    <div class="bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                                                        <span class="fw-bold fs-5"><?php echo substr($sp['sales_name'], 0, 1); ?></span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 1rem;">
                                                            <?php echo htmlspecialchars($sp['sales_name']); ?>
                                                        </h6>
                                                        <div class="mt-1">
                                                            <?php if ($isTopPax): ?>
                                                                <span class="badge bg-warning text-dark badge-gimmick rounded-pill px-2" title="Top Revenue/Pax"><i class="fas fa-crown"></i> Top Pax</span>
                                                            <?php endif; ?>
                                                            <?php if ($isTopProj && !$isTopPax): ?>
                                                                <span class="badge bg-info text-white badge-gimmick rounded-pill px-2" title="Most Active"><i class="fas fa-fire"></i> Most Active</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Gimmick Bar (Stats) -->
                                                <div class="px-md-4 mb-2 mb-md-0" style="flex: 2; min-width: 200px;">
                                                    <?php 
                                                        $maxPax = ($globalTopPax && $globalTopPax['total_pax'] > 0) ? $globalTopPax['total_pax'] : 1;
                                                        $maxProj = ($globalTopProjects && $globalTopProjects['total_projects'] > 0) ? $globalTopProjects['total_projects'] : 1;
                                                        
                                                        $paxPct = min(100, ($sp['total_pax'] / $maxPax) * 100);
                                                        $projPct = min(100, ($sp['total_projects'] / $maxProj) * 100);
                                                    ?>
                                                    <div class="d-flex flex-column gap-3 w-100">
                                                        <!-- Projects Bar -->
                                                        <div class="stat-bar-container">
                                                            <div class="d-flex justify-content-between align-items-end mb-1">
                                                                <span class="stat-value-text"><?php echo number_format($sp['total_projects']); ?></span>
                                                                <span class="stat-label-text">Projects</span>
                                                            </div>
                                                            <div class="custom-progress">
                                                                <div class="progress-bar progress-bar-dark-blue" role="progressbar" style="width: <?php echo $projPct; ?>%" aria-valuenow="<?php echo $sp['total_projects']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $maxProj; ?>"></div>
                                                            </div>
                                                        </div>

                                                        <!-- Pax Bar -->
                                                        <div class="stat-bar-container">
                                                            <div class="d-flex justify-content-between align-items-end mb-1">
                                                                <span class="stat-value-text"><?php echo number_format($sp['total_pax']); ?></span>
                                                                <span class="stat-label-text">Pax</span>
                                                            </div>
                                                            <div class="custom-progress">
                                                                <div class="progress-bar progress-bar-light-blue" role="progressbar" style="width: <?php echo $paxPct; ?>%" aria-valuenow="<?php echo $sp['total_pax']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $maxPax; ?>"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="ms-auto ps-3 align-self-start">
                                                    <div class="dropdown">
                                                        <button class="btn btn-icon-only" type="button" id="dropdownMenuButton<?php echo $spId; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="dropdownMenuButton<?php echo $spId; ?>">
                                                            <?php if(in_array($_SESSION['role'], ['superadmin','admin_sales'])): ?>
                                                                <li>
                                                                    <a class="dropdown-item" href="#" onclick="prepareEditSalesPerson('<?php echo $spId; ?>', '<?php echo htmlspecialchars(addslashes($sp['sales_name'])); ?>', '<?php echo $sp['sales_manager_id']; ?>', '<?php echo $sp['user_id']; ?>')" data-bs-toggle="modal" data-bs-target="#modalSalesPerson">
                                                                        <i class="fas fa-edit me-2 text-muted"></i> Edit
                                                                    </a>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item text-danger" href="index.php?page=sales_persons_delete&id=<?php echo $spId; ?>" onclick="return confirm('Are you sure you want to delete this sales person?');">
                                                                        <i class="fas fa-trash me-2"></i> Delete
                                                                    </a>
                                                                </li>
                                                            <?php else: ?>
                                                                <li><span class="dropdown-item text-muted">No actions available</span></li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Sales Person -->
<div class="modal fade" id="modalSalesPerson" tabindex="-1" aria-labelledby="modalSalesPersonLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=sales_persons_store" method="POST" id="formSalesPerson">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSalesPersonLabel">Add Sales Person</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="salesPersonId">
                    <div class="mb-3">
                        <label for="salesUserId" class="form-label">Sales Person (User)</label>
                        <select class="form-select" id="salesUserId" name="user_id" required>
                            <option value="">Select User...</option>
                            <?php foreach ($all_sales_users as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="salesName" name="sales_name">
                    </div>
                    <div class="mb-3">
                        <label for="salesManagerId" class="form-label">Manager</label>
                        <select class="form-select" id="salesManagerId" name="sales_manager_id">
                            <option value="">Select Manager (Optional)</option>
                            <?php foreach ($managers as $manager): ?>
                                <option value="<?php echo $manager['id']; ?>"><?php echo htmlspecialchars($manager['name'] ?? $manager['manager_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sales Manager -->
<div class="modal fade" id="modalSalesManager" tabindex="-1" aria-labelledby="modalSalesManagerLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=sales_managers_store" method="POST" id="formSalesManager">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSalesManagerLabel">Add Sales Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="managerId">
                    <div class="mb-3">
                        <label for="managerUserId" class="form-label">Manager (User)</label>
                        <select class="form-select" id="managerUserId" name="user_id">
                            <option value="">Select User...</option>
                            <?php foreach ($all_manager_users as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="managerName" name="manager_name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
<script>
    // Initialize tooltips if using Bootstrap 5
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Prepare Modals
    function prepareAddSalesPerson() {
        document.getElementById('modalSalesPersonLabel').innerText = 'Add Sales Person';
        document.getElementById('formSalesPerson').action = 'index.php?page=sales_persons_store';
        document.getElementById('salesPersonId').value = '';
        document.getElementById('salesUserId').value = '';
        document.getElementById('salesName').value = '';
        document.getElementById('salesManagerId').value = '';
    }

    function prepareEditSalesPerson(id, name, managerId, userId) {
        document.getElementById('modalSalesPersonLabel').innerText = 'Edit Sales Person';
        document.getElementById('formSalesPerson').action = 'index.php?page=sales_persons_update';
        document.getElementById('salesPersonId').value = id;
        document.getElementById('salesUserId').value = userId;
        document.getElementById('salesName').value = name;
        document.getElementById('salesManagerId').value = managerId;
    }

    function prepareAddSalesManager() {
        document.getElementById('modalSalesManagerLabel').innerText = 'Add Sales Manager';
        document.getElementById('formSalesManager').action = 'index.php?page=sales_managers_store';
        document.getElementById('managerId').value = '';
        document.getElementById('managerUserId').value = '';
        document.getElementById('managerName').value = '';
    }

    function prepareEditSalesManager(id, name, userId) {
        document.getElementById('modalSalesManagerLabel').innerText = 'Edit Sales Manager';
        document.getElementById('formSalesManager').action = 'index.php?page=sales_managers_update';
        document.getElementById('managerId').value = id;
        document.getElementById('managerUserId').value = userId;
        document.getElementById('managerName').value = name;
    }

    // Auto-fill hidden name fields on select change
    document.getElementById('salesUserId').addEventListener('change', function() {
        var text = this.options[this.selectedIndex].text;
        // Simple extraction, better handled by backend logic if name is strictly from users table
        var name = text.split(' (')[0]; 
        document.getElementById('salesName').value = name;
    });

    document.getElementById('managerUserId').addEventListener('change', function() {
        var text = this.options[this.selectedIndex].text;
        var name = text.split(' (')[0];
        document.getElementById('managerName').value = name;
    });
</script>