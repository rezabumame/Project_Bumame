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
</style>

<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: #204EAB;">Sales Team Performance</h2>
            <p class="text-muted mb-0">Overview of workload distribution and top performers.</p>
        </div>
        <a href="index.php?page=sales_managers_create" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-2"></i>New Manager
        </a>
    </div>

    <!-- Alert Messages -->
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
                    <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="<?php echo $collapseId; ?>">
                        <div class="d-flex w-100 justify-content-between align-items-center pe-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($mgr['manager_name']); ?></h5>
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
                <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#managersAccordion">
                    <div class="accordion-body bg-light">
                        <!-- Manager Actions -->
                        <div class="d-flex justify-content-end mb-3">
                            <?php if($mgrId > 0): ?>
                            <div class="btn-group btn-group-sm">
                                <a href="index.php?page=sales_managers_edit&id=<?php echo $mgrId; ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-edit me-1"></i> Edit Manager
                                </a>
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
                                    <div class="list-group-item list-group-item-action card-sales-person p-0 border-0 mb-2 shadow-sm rounded">
                                        <!-- Sales Person Header -->
                                        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#<?php echo $spCollapseId; ?>" style="cursor: pointer;">
                                            <div class="d-flex align-items-center mb-2 mb-md-0 col-md-4">
                                                <div class="bg-white border text-primary rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 36px; height: 36px;">
                                                    <span class="fw-bold"><?php echo substr($sp['sales_name'], 0, 1); ?></span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">
                                                        <?php echo htmlspecialchars($sp['sales_name']); ?>
                                                        <?php if ($isTopPax): ?>
                                                            <span class="badge bg-warning text-dark ms-2 badge-gimmick" title="Top Revenue/Pax"><i class="fas fa-crown"></i> Top Pax</span>
                                                        <?php endif; ?>
                                                        <?php if ($isTopProj && !$isTopPax): ?>
                                                            <span class="badge bg-info text-white ms-2 badge-gimmick" title="Most Active"><i class="fas fa-fire"></i> Most Active</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                </div>
                                            </div>

                                            <div class="col-md-4 d-flex justify-content-center gap-4 mb-2 mb-md-0">
                                                <div class="text-center">
                                                    <span class="text-xs text-muted text-uppercase fw-bold">Pax</span>
                                                    <div class="h6 mb-0 fw-bold text-dark"><?php echo number_format($sp['total_pax']); ?></div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="text-xs text-muted text-uppercase fw-bold">Projects</span>
                                                    <div class="h6 mb-0 fw-bold text-dark"><?php echo number_format($sp['total_projects']); ?></div>
                                                </div>
                                            </div>

                                            <div class="col-md-3 text-end">
                                                <small class="text-muted fst-italic me-2">Click to view projects</small>
                                                <i class="fas fa-chevron-down text-muted"></i>
                                            </div>
                                        </div>

                                        <!-- Sales Person Projects (Collapsed) -->
                                        <div id="<?php echo $spCollapseId; ?>" class="collapse bg-white border-top">
                                            <div class="p-3">
                                                <?php if (empty($sp['projects'])): ?>
                                                    <p class="text-muted text-center mb-0 text-sm">No active projects.</p>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-borderless table-hover mb-0">
                                                            <thead class="bg-light text-muted">
                                                                <tr>
                                                                    <th style="width: 40%;">Project Name</th>
                                                                    <th>Pax</th>
                                                                    <th>Date</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($sp['projects'] as $proj): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($proj['nama_project']); ?></span>
                                                                        </td>
                                                                        <td><?php echo number_format($proj['total_peserta']); ?></td>
                                                                        <td><?php echo $proj['tanggal_mcu'] ? date('d M Y', strtotime($proj['tanggal_mcu'])) : '-'; ?></td>
                                                                        <td>
                                                                            <span class="badge bg-secondary text-white" style="font-size: 0.7em;">
                                                                                <?php echo htmlspecialchars($proj['status_project'] ?? 'N/A'); ?>
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Action Buttons for Sales Person -->
                                                <div class="d-flex justify-content-end mt-2 pt-2 border-top">
                                                    <!-- Link to Edit Sales Person -->
                                                    <!-- Note: Assuming sales person controller page exists or modal triggers -->
                                                    <!-- Since user asked to keep existing add/edit popups, we link to existing sales person index or modal -->
                                                    <a href="index.php?page=sales_persons_edit&id=<?php echo $spId; ?>" class="btn btn-sm btn-link text-muted">
                                                        Edit Sales Person Profile
                                                    </a>
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

<?php include '../views/layouts/footer.php'; ?>
<script>
    // Initialize tooltips if using Bootstrap 5
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
