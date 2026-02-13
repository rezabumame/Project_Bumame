<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<style>
    /* Medical Theme Audit Styles */
    :root {
        --medical-primary: #005EB8; /* Bumame Blue */
        --medical-secondary: #48c9b0;
        --medical-accent: #e74c3c;
        --medical-bg: #f4f6f9;
        --medical-card-bg: #ffffff;
        --timeline-line: #bdc3c7;
    }


    .search-card {
        background: white;
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border-left: 5px solid var(--medical-primary);
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        height: 100%;
    }

    .stat-title {
        color: #7f8c8d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        color: #2c3e50;
        font-size: 1.5rem;
        font-weight: 700;
    }

    /* Modern Flowchart / Timeline */
    .flow-container {
        position: relative;
        padding: 2rem 0;
    }

    .flow-line {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #e0e0e0;
        transform: translateX(-50%);
        z-index: 1;
    }

    .flow-node {
        position: relative;
        margin-bottom: 3rem;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 2;
    }

    .flow-card {
        width: 45%;
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        position: relative;
        border-top: 4px solid var(--medical-primary);
        transition: transform 0.3s ease;
    }
    
    .flow-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .flow-node:nth-child(odd) .flow-card {
        margin-right: auto;
        margin-left: 0;
    }

    .flow-node:nth-child(even) .flow-card {
        margin-left: auto;
        margin-right: 0;
        border-top-color: var(--medical-secondary);
    }

    .flow-icon-center {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 50px;
        height: 50px;
        background: white;
        border: 4px solid var(--medical-primary);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--medical-primary);
        font-size: 1.2rem;
        z-index: 3;
        box-shadow: 0 0 0 5px var(--medical-bg);
    }
    
    .flow-node:nth-child(even) .flow-icon-center {
        border-color: var(--medical-secondary);
        color: var(--medical-secondary);
    }

    /* Connector Arrows */
    .flow-card::before {
        content: '';
        position: absolute;
        top: 50%;
        width: 30px;
        height: 2px;
        background: #e0e0e0;
    }

    .flow-node:nth-child(odd) .flow-card::before {
        right: -30px;
    }

    .flow-node:nth-child(even) .flow-card::before {
        left: -30px;
    }

    .badge-medical {
        background-color: #e8f4f8;
        color: var(--medical-primary);
        border-radius: 20px;
        padding: 0.4em 0.8em;
        font-weight: 600;
    }

    .time-badge {
        font-size: 0.8rem;
        color: #95a5a6;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        background-color: var(--medical-primary) !important;
        color: white;
    }

    .nav-pills .nav-link.active .text-muted {
        color: rgba(255, 255, 255, 0.9) !important;
    }
    
    .nav-pills .nav-link {
        color: var(--medical-primary);
    }

</style>

<div class="main-content">
    <div class="container-fluid">
        
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center page-header-container">
            <div>
                <?php if (isset($project) && $project): ?>
                    <h1 class="page-header-title">Audit: <?php echo htmlspecialchars($project['nama_project']); ?></h1>
                    <p class="page-header-subtitle"><?php echo htmlspecialchars($project['company_name']); ?> | <?php echo htmlspecialchars($project['project_id']); ?></p>
                <?php else: ?>
                    <h1 class="page-header-title">Clinical Project Audit</h1>
                    <p class="page-header-subtitle">Advanced tracking and process visualization for Medical Checkup Projects.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Section -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="search-card p-4">
                    <form action="index.php" method="GET" class="d-flex gap-2">
                        <input type="hidden" name="page" value="audit_index">
                        <input type="text" name="project_id" class="form-control form-control-lg border-0 bg-light" placeholder="Enter Project ID or SPH Number" value="<?php echo isset($_GET['project_id']) ? htmlspecialchars($_GET['project_id']) : ''; ?>" required>
                        <button type="submit" class="btn btn-primary btn-lg px-4" style="background-color: var(--medical-primary);"><i class="fas fa-search me-2"></i>Audit</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if (isset($projects) && count($projects) > 1): ?>
            <?php
            $limit_tab = 5;
            $selected_index = 0;
            if ($project) {
                foreach ($projects as $idx => $p) {
                    if ($p['project_id'] == $project['project_id']) {
                        $selected_index = $idx;
                        break;
                    }
                }
            }
            
            // Default to page containing selected project unless overridden
            $default_page = floor($selected_index / $limit_tab) + 1;
            $page_tab = isset($_GET['tab_page']) ? (int)$_GET['tab_page'] : $default_page;
            
            $total_tabs = count($projects);
            $total_tab_pages = ceil($total_tabs / $limit_tab);
            $offset_tab = ($page_tab - 1) * $limit_tab;
            $display_projects = array_slice($projects, $offset_tab, $limit_tab);
            ?>
            
            <div class="row justify-content-center mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-2">
                            <ul class="nav nav-pills nav-fill" id="projectTabs">
                                <?php foreach($display_projects as $p): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($project && $p['project_id'] == $project['project_id']) ? 'active' : ''; ?>" 
                                           href="index.php?page=audit_index&project_id=<?php echo urlencode($search_term); ?>&selected_id=<?php echo urlencode($p['project_id']); ?>&tab_page=<?php echo $page_tab; ?>">
                                            <div class="fw-bold"><?php echo htmlspecialchars($p['nama_project']); ?></div>
                                            <div class="small text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($p['project_id']); ?> | <?php echo htmlspecialchars($p['company_name']); ?></div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <?php if ($total_tab_pages > 1): ?>
                                <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-1 border-top pt-2">
                                    <div>
                                        <?php if ($page_tab > 1): ?>
                                            <a href="index.php?page=audit_index&project_id=<?php echo urlencode($search_term); ?>&selected_id=<?php echo $project['project_id']; ?>&tab_page=<?php echo $page_tab - 1; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="fas fa-chevron-left me-1"></i> Previous
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled><i class="fas fa-chevron-left me-1"></i> Previous</button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <span class="small text-muted fw-bold text-uppercase">Page <?php echo $page_tab; ?> of <?php echo $total_tab_pages; ?></span>
                                    
                                    <div>
                                        <?php if ($page_tab < $total_tab_pages): ?>
                                            <a href="index.php?page=audit_index&project_id=<?php echo urlencode($search_term); ?>&selected_id=<?php echo $project['project_id']; ?>&tab_page=<?php echo $page_tab + 1; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                Next <i class="fas fa-chevron-right ms-1"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled>Next <i class="fas fa-chevron-right ms-1"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($project): ?>
            
            <!-- Progress Bar -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-primary mb-3"><i class="fas fa-tasks me-2"></i>Project Completion Health</h5>
                            <?php if (!empty($is_cancelled) && $is_cancelled): ?>
                                <div class="alert alert-danger mb-0 fw-bold text-center rounded-pill">
                                    <i class="fas fa-ban me-2"></i> PROJECT CANCELLED / REJECTED
                                </div>
                            <?php else: ?>
                                <div class="progress" style="height: 25px; border-radius: 15px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" 
                                         style="width: <?php echo $progress_percent; ?>%;" 
                                         aria-valuenow="<?php echo $progress_percent; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $progress_percent; ?>%
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-2 small text-muted">
                                    <span>Approval</span>
                                    <span>Operations</span>
                                    <span>Invoicing</span>
                                    <span>Completed</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Summary Stats -->
            <div class="row mb-4 g-4">
                <!-- Approval Duration -->
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-title">Approval Phase</div>
                        <div class="stat-value text-primary"><?php echo $durations['approval']; ?></div>
                        <small class="text-muted">Create ➝ Approved</small>
                    </div>
                </div>
                <!-- Ops Duration -->
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: #f39c12;">
                        <div class="stat-title">Operations Phase</div>
                        <div class="stat-value" style="color: #f39c12;"><?php echo $durations['ops']; ?></div>
                        <small class="text-muted">Approved ➝ Invoice Req</small>
                    </div>
                </div>
                <!-- Invoicing Duration -->
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: #9b59b6;">
                        <div class="stat-title">Invoicing Phase</div>
                        <div class="stat-value" style="color: #9b59b6;"><?php echo $durations['invoicing']; ?></div>
                        <small class="text-muted">Invoice Req ➝ Completed</small>
                    </div>
                </div>
                <!-- Current Status -->
                <div class="col-md-3">
                    <div class="stat-card" style="border-left-color: var(--medical-secondary);">
                        <div class="stat-title">Current Status</div>
                        <div class="stat-value" style="color: var(--medical-secondary); font-size: 1.1rem;">
                            <?php echo ucfirst(str_replace('_', ' ', $project['status_project'])); ?>
                        </div>
                        <small class="text-muted">Live Tracking</small>
                    </div>
                </div>
            </div>
            
            <!-- Secondary Stats -->
            <div class="row mb-4 g-4">
                 <div class="col-md-6">
                    <div class="stat-card" style="border-left-color: #34495e;">
                        <div class="stat-title">Participants</div>
                        <div class="stat-value text-dark"><?php echo number_format($project['total_peserta']); ?></div>
                        <small class="text-muted">Total Patients/Employees</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card" style="border-left-color: #7f8c8d;">
                        <div class="stat-title">Project Start Date</div>
                        <div class="stat-value text-secondary"><?php echo $start_date; ?></div>
                        <small class="text-muted">Initiation Timestamp</small>
                    </div>
                </div>
            </div>

            <!-- Details & Documents Grid -->
            <div class="row mb-5 g-4">
                <!-- Project Requirements / Details -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 rounded-3">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i>Project Requirements</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-hashtag me-2 text-muted"></i>Project ID</span>
                                    <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                        <?php echo htmlspecialchars($project['project_id']); ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-project-diagram me-2 text-muted"></i>Project Name</span>
                                    <span class="fw-bold"><?php echo htmlspecialchars($project['nama_project']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-building me-2 text-muted"></i>Company</span>
                                    <span class="fw-bold"><?php echo htmlspecialchars($project['company_name']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-map-marker-alt me-2 text-muted"></i>Location</span>
                                    <span class="text-end"><?php echo htmlspecialchars($project['alamat']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-utensils me-2 text-muted"></i>Lunch Request</span>
                                    <?php if($project['lunch'] == 'Ya'): ?>
                                        <span class="badge bg-success rounded-pill">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill">No</span>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-cookie-bite me-2 text-muted"></i>Snack Request</span>
                                    <?php if($project['snack'] == 'Ya'): ?>
                                        <span class="badge bg-success rounded-pill">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill">No</span>
                                    <?php endif; ?>
                                </li>
                                <?php if (isset($tm_data) && !empty($tm_data['setting_alat_date'])): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-tools me-2 text-muted"></i>Setting Alat Date</span>
                                    <span class="fw-bold"><?php echo date('d F Y H:i', strtotime($tm_data['setting_alat_date'])); ?></span>
                                </li>
                                <?php endif; ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-box-open me-2 text-muted"></i>Exam Type / Package</span>
                                    </div>
                                    <div class="small">
                                        <?php echo PackageHelper::renderMatrix($project['jenis_pemeriksaan'], $project['company_name']); ?>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Document Repository -->
                <div class="col-md-6">
                    <!-- Contact & Vendor Details -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-users me-2 text-primary"></i>Team & Vendors</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-user-tie me-2 text-muted"></i>Sales Person</span>
                                    <span class="fw-bold"><?php echo htmlspecialchars($project['sales_name'] ?? '-'); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-user-shield me-2 text-muted"></i>Korlap</span>
                                    <span class="fw-bold"><?php echo htmlspecialchars($project['korlap_name'] ?? '-'); ?></span>
                                </li>
                            </ul>

                            <h6 class="text-muted text-uppercase small fw-bold mt-3 mb-2">Vendor Allocations</h6>
                            <?php if (!empty($vendor_allocations)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Exam Type</th>
                                                <th>Pax</th>
                                                <th>Vendor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($vendor_allocations as $alloc): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($alloc['exam_type']); ?></td>
                                                    <td><?php echo htmlspecialchars($alloc['participant_count']); ?></td>
                                                    <td class="<?php echo !empty($alloc['assigned_vendor_name']) ? 'fw-bold text-success' : 'text-muted fst-italic'; ?>">
                                                        <?php echo htmlspecialchars($alloc['assigned_vendor_name'] ?? 'Pending'); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small fst-italic border rounded p-2 text-center bg-light">
                                    No vendor allocations recorded.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Field Team -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-user-friends me-2 text-info"></i>Field Team</h5>
                            <span class="badge bg-info rounded-pill"><?php echo count($staff_assignments); ?> Entries</span>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($staff_assignments)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Staff</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($staff_assignments as $staff): ?>
                                                <tr>
                                                    <td class="small fw-bold"><?php echo date('d M Y', strtotime($staff['date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($staff['man_power_name']); ?></td>
                                                    <td><span class="badge bg-light text-dark border small"><?php echo htmlspecialchars($staff['role']); ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small fst-italic border rounded p-2 text-center bg-light">
                                    No staff assignments recorded.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Results Team -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-clipboard-check me-2 text-success"></i>Results Team</h5>
                            <span class="badge bg-success rounded-pill"><?php echo count($dw_realizations); ?> Records</span>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($dw_realizations)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>DW Name</th>
                                                <th>Kohas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dw_realizations as $real): ?>
                                                <tr>
                                                    <td class="small fw-bold"><?php echo date('d M Y', strtotime($real['date'])); ?></td>
                                                    <td class="fw-bold text-primary"><?php echo htmlspecialchars($real['user_name']); ?></td>
                                                    <td class="small text-muted"><?php echo htmlspecialchars($real['kohas_name']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small fst-italic border rounded p-2 text-center bg-light">
                                    No realization records found.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-folder-open me-2 text-warning"></i>Document Repository</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-3">
                                <!-- SPH File -->
                                <div class="p-3 border rounded d-flex align-items-center justify-content-between hover-shadow">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold">SPH Document</h6>
                                            <small class="text-muted">Surat Penawaran Harga</small>
                                        </div>
                                    </div>
                                    <?php if (!empty($project['sph_file'])): ?>
                                        <?php if (preg_match('#^https?://#', $project['sph_file'])): ?>
                                            <a href="<?php echo htmlspecialchars($project['sph_file']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt me-1"></i> Link SPH
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?page=download_sph&project_id=<?php echo htmlspecialchars($project['project_id']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i> View File
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Uploaded</span>
                                    <?php endif; ?>
                                </div>

                                <!-- RAB Files -->
                                <h6 class="text-muted text-uppercase small fw-bold mt-4">RAB Documents</h6>
                                <?php if (!empty($rabs)): ?>
                                    <?php foreach ($rabs as $rab): ?>
                                        <div class="p-3 border rounded d-flex align-items-center justify-content-between hover-shadow mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-invoice-dollar fa-2x text-primary me-3"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($rab['rab_number'] ?? '-'); ?></h6>
                                                    <small class="text-muted">
                                                        Total: Rp <?php echo number_format($rab['grand_total'] ?? 0); ?>
                                                        <br>
                                                        Status: 
                                                        <?php 
                                                            $status = $rab['status'] ?? 'unknown';
                                                            $statusClass = 'text-warning';
                                                            if($status == 'approved') $statusClass = 'text-success';
                                                            elseif($status == 'rejected') $statusClass = 'text-danger';
                                                            elseif($status == 'completed') $statusClass = 'text-primary';
                                                        ?>
                                                        <span class="<?php echo $statusClass; ?> fw-bold"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                                                        <?php if (!empty($rab['finance_note'])): ?>
                                                            <br><span class="text-muted">Finance Note:</span> <span class="fw-semibold text-dark"><?php echo htmlspecialchars($rab['finance_note']); ?></span>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="index.php?page=rabs_export_pdf&id=<?php echo htmlspecialchars($rab['id']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-print me-1"></i> RAB
                                                </a>
                                                <?php 
                                                    $proof = isset($rab['transfer_proof_path']) ? $rab['transfer_proof_path'] : '';
                                                    $proof_url = '';
                                                    
                                                    if (!empty($proof)) {
                                                        // Clean path to get standard relative path (e.g. uploads/finance_proofs/file.jpg)
                                                        $clean_path = str_replace('../', '', $proof);
                                                        $clean_path = str_replace('public/', '', $clean_path);
                                                        
                                                        // Check where the file actually is
                                                        if (file_exists($clean_path)) {
                                                            // It exists in public/uploads (standard view path)
                                                            $proof_url = $clean_path;
                                                        } elseif (file_exists('../' . $clean_path)) {
                                                            // It exists in root uploads (mcu-ticketing/uploads)
                                                            $proof_url = '../' . $clean_path;
                                                        } else {
                                                            // Fallback to cleaned path (link might be 404 but we tried)
                                                            $proof_url = $clean_path;
                                                        }
                                                    }
                                                ?>
                                                <?php if (!empty($proof)): ?>
                                                    <a href="<?php echo htmlspecialchars($proof_url); ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Bukti Transfer Finance">
                                                        <i class="fas fa-receipt me-1"></i> Bukti TF
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php 
                                                    // Realization Documents (LPUM)
                                                    // Show if status implies realization exists
                                                    if (in_array($rab['status'], ['need_approval_realization', 'realization_approved', 'realization_rejected', 'completed'])):
                                                ?>
                                                    <a href="index.php?page=realization_export_lpum&rab_id=<?php echo $rab['id']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Laporan Realisasi (LPUM)">
                                                        <i class="fas fa-file-invoice me-1"></i> LPUM
                                                    </a>
                                                <?php endif; ?>

                                                <?php 
                            // Settlement Proof (Bukti Pengembalian/Kekurangan)
                            $settlement = isset($rab['transfer_proof_path']) ? $rab['transfer_proof_path'] : '';
                            $settlement_url = '';
                            
                            if (!empty($settlement)) {
                                // Settlement is stored in settlements dir
                                $base_path = 'uploads/settlements/' . $settlement;
                                
                                if (file_exists($base_path)) {
                                    $settlement_url = $base_path;
                                } elseif (file_exists('../' . $base_path)) {
                                    $settlement_url = '../' . $base_path;
                                } else {
                                    $settlement_url = $base_path;
                                }
                            }
                        ?>
                                                <?php if (!empty($settlement)): ?>
                                                    <a href="<?php echo htmlspecialchars($settlement_url); ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Bukti Pengembalian/Kekurangan">
                                                        <i class="fas fa-file-contract me-1"></i> Settlement
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted small fst-italic border rounded p-2 text-center bg-light">
                                        No RAB documents found.
                                    </div>
                                <?php endif; ?>

                                <!-- Berita Acara Files -->
                                <h6 class="text-muted text-uppercase small fw-bold mt-2">Berita Acara (BA)</h6>
                                <?php if (!empty($ba_files)): ?>
                                    <?php foreach ($ba_files as $date => $ba): ?>
                                        <div class="p-3 border rounded d-flex align-items-center justify-content-between hover-shadow">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-contract fa-2x text-info me-3"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">BA - <?php echo date('d M Y', strtotime($date)); ?></h6>
                                                    <small class="text-muted">Status: <?php echo ucfirst($ba['status']); ?></small>
                                                </div>
                                            </div>
                                            <?php if ($ba['status'] == 'uploaded' && !empty($ba['file_path'])): ?>
                                                <a href="index.php?page=download_ba&project_id=<?php echo htmlspecialchars($project['project_id']); ?>&date=<?php echo htmlspecialchars($date); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download me-1"></i> View
                                                </a>
                                            <?php elseif ($ba['status'] == 'cancelled'): ?>
                                                <span class="badge bg-danger">Cancelled</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center p-3 text-muted border border-dashed rounded">
                                        <i class="fas fa-inbox mb-2"></i><br>
                                        No Berita Acara documents found.
                                    </div>
                                <?php endif; ?>

                                <!-- Technical Meeting Files -->
                                <h6 class="text-muted text-uppercase small fw-bold mt-3">Technical Meeting</h6>
                                <?php if (!empty($tm_data)): ?>
                                    <div class="p-3 border rounded d-flex align-items-center justify-content-between hover-shadow">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-handshake fa-2x text-success me-3"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold">TM Notes & Docs</h6>
                                                <small class="text-muted">
                                                    Date: <?php echo date('d M Y', strtotime($tm_data['tm_date'])); ?> | 
                                                    Type: <?php echo htmlspecialchars($tm_data['tm_type']); ?>
                                                    <?php if (!empty($tm_data['setting_alat_date'])): ?>
                                                        <br><i class="fas fa-tools me-1"></i> Setting Alat: <?php echo date('d M Y H:i', strtotime($tm_data['setting_alat_date'])); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column gap-1">
                                            <?php if (!empty($tm_data['tm_file_path'])): ?>
                                                <a href="uploads/tm/<?php echo htmlspecialchars($tm_data['tm_file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="View TM Doc">
                                                    <i class="fas fa-file-pdf me-1"></i> Doc
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($tm_data['layout_file_path'])): ?>
                                                <a href="uploads/tm/<?php echo htmlspecialchars($tm_data['layout_file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View Layout">
                                                    <i class="fas fa-map me-1"></i> Layout
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- TM Notes Preview -->
                                    <div class="mt-2 p-2 bg-light border rounded small text-muted">
                                        <strong>Notes:</strong> 
                                        <?php 
                                            $notes_text = $tm_data['notes'];
                                            echo nl2br(htmlspecialchars(substr($notes_text, 0, 100)));
                                            if (strlen($notes_text) > 100) {
                                                echo '... <a href="javascript:void(0)" onclick="showTmNotesModal()" class="text-primary fw-bold text-decoration-none">Read More</a>';
                                            }
                                        ?>
                                    </div>
                                    <div id="tm_full_notes_content" style="display:none;"><?php echo nl2br(htmlspecialchars($notes_text)); ?></div>
                                <?php else: ?>
                                    <div class="text-center p-3 text-muted border border-dashed rounded">
                                        <i class="fas fa-times-circle mb-2"></i><br>
                                        No Technical Meeting data recorded.
                                    </div>
                                <?php endif; ?>

                                <!-- Medical Result Files -->
                                <h6 class="text-muted text-uppercase small fw-bold mt-3">Medical Results (Surat Hasil)</h6>
                                <?php if ($medical_result || !empty($medical_result_items)): ?>
                                    <!-- Summary Links -->
                                    <?php if (!empty($medical_result['link_summary_excel'])): ?>
                                        <div class="p-3 border rounded d-flex align-items-center justify-content-between hover-shadow mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-excel fa-2x text-success me-3"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Summary Excel</h6>
                                                    <small class="text-muted">Rekapitulasi Hasil MCU</small>
                                                </div>
                                            </div>
                                            <a href="<?php echo htmlspecialchars($medical_result['link_summary_excel']); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-external-link-alt me-1"></i> Open
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($medical_result['link_summary_dashboard'])): ?>
                                        <div class="p-3 border rounded d-flex align-items-center justify-content-between hover-shadow mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-chart-pie fa-2x text-primary me-3"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Dashboard Report</h6>
                                                    <small class="text-muted">Interactive Data Visualization</small>
                                                </div>
                                            </div>
                                            <a href="<?php echo htmlspecialchars($medical_result['link_summary_dashboard']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt me-1"></i> View
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <!-- PDF Result Links (Items) -->
                                    <?php foreach ($medical_result_items as $item): ?>
                                        <?php if (!empty($item['link_pdf'])): ?>
                                            <div class="p-3 border rounded d-flex align-items-center justify-content-between hover-shadow mb-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fab fa-google-drive fa-2x text-success me-3"></i>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">Hasil MCU - <?php echo date('d M Y', strtotime($item['date_mcu'])); ?></h6>
                                                        <small class="text-muted">
                                                            <span class="badge bg-light text-dark border ms-1">
                                                                <?php echo $item['actual_pax_released'] ?? 0; ?> Pax
                                                            </span>
                                                        </small>
                                                    </div>
                                                </div>
                                                <a href="<?php echo htmlspecialchars($item['link_pdf']); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-external-link-alt me-1"></i> Open Drive
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center p-3 text-muted border border-dashed rounded">
                                        <i class="fas fa-notes-medical mb-2"></i><br>
                                        No Medical Results uploaded yet.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chatter History -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-comments me-2 text-info"></i>Communication History (Chatter)</h5>
                        </div>
                        <div class="card-body bg-light" style="max-height: 400px; overflow-y: auto;">
                            <?php if (!empty($comments)): ?>
                                <?php foreach ($comments as $comment): ?>
                                    <div class="d-flex mb-3">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-circle bg-white text-primary border border-2 border-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <?php echo strtoupper(substr($comment['full_name'], 0, 1)); ?>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="bg-white p-3 rounded shadow-sm border">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="fw-bold mb-0 text-primary small"><?php echo htmlspecialchars($comment['full_name']); ?> <span class="text-muted fw-normal">(<?php echo htmlspecialchars($comment['role']); ?>)</span></h6>
                                                    <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('d M Y, H:i', strtotime($comment['created_at'])); ?></small>
                                                </div>
                                                <p class="mb-0 small text-dark"><?php echo nl2br($comment['message']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-comment-slash fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0">No communication history recorded.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Flowchart -->
            <div class="row">
                <div class="col-12 text-center mb-4">
                    <h3 class="fw-bold" style="color: var(--medical-primary);">Process Flow Chart</h3>
                    <div class="badge bg-light text-dark px-3 py-2 rounded-pill border">Visualization of Project Lifecycle</div>
                </div>
            </div>

            <div class="flow-container">
                <div class="flow-line"></div>

                <?php 
                // Reverse history to show chronological flow (Start to End)
                $chronological_history = array_reverse($history);
                $previous_time = null;
                
                foreach ($chronological_history as $index => $log): 
                    $status_lower = strtolower($log['status_to']);
                    $current_time = strtotime($log['changed_at']);
                    
                    // Calculate duration from previous step
                    $duration_str = '';
                    if ($previous_time !== null) {
                        $diff = $current_time - $previous_time;
                        if ($diff < 0) $diff = 0; // Prevent negative
                        
                        if ($diff < 60) {
                            $duration_str = $diff . ' sec';
                        } elseif ($diff < 3600) {
                            $duration_str = floor($diff / 60) . ' min ' . ($diff % 60) . ' sec';
                        } elseif ($diff < 86400) {
                            $hours = floor($diff / 3600);
                            $mins = floor(($diff % 3600) / 60);
                            $duration_str = $hours . ' hr ' . $mins . ' min';
                        } else {
                            $days = floor($diff / 86400);
                            $hours = floor(($diff % 86400) / 3600);
                            $duration_str = $days . ' days ' . $hours . ' hr';
                        }
                    }
                    $previous_time = $current_time;
                    
                    // Icon & Color Mapping (reused concept but styled for medical audit)
                    $icon = 'fa-circle';
                    if (strpos($status_lower, 'created') !== false) $icon = 'fa-notes-medical';
                    elseif (strpos($status_lower, 'approved') !== false) $icon = 'fa-check-double';
                    elseif (strpos($status_lower, 'sph') !== false) $icon = 'fa-file-prescription';
                    elseif (strpos($status_lower, 'completed') !== false) $icon = 'fa-flag-checkered';
                    elseif (strpos($status_lower, 'rejected') !== false || strpos($status_lower, 'cancelled') !== false) $icon = 'fa-ban';
                    elseif (strpos($status_lower, 'vendor') !== false) $icon = 'fa-ambulance';
                    elseif (strpos($status_lower, 'korlap') !== false) $icon = 'fa-user-nurse';
                    elseif (strpos($status_lower, 'berita acara') !== false) $icon = 'fa-file-signature';
                    elseif (strpos($status_lower, 'medical result') !== false || strpos($status_lower, 'result') !== false) $icon = 'fa-microscope';

                ?>
                    <div class="flow-node">
                        <div class="flow-icon-center">
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="flow-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge badge-medical"><?php echo strtoupper(str_replace('_', ' ', $log['status_to'])); ?></span>
                                <div class="time-badge">
                                    <i class="far fa-clock"></i>
                                    <?php echo date('d M Y, H:i', strtotime($log['changed_at'])); ?>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($log['changed_by_name'] ?? 'System'); ?></h6>
                            <p class="text-muted small mb-0 fst-italic">
                                "<?php echo nl2br(htmlspecialchars($log['notes'] ?? 'No notes recorded')); ?>"
                            </p>
                            
                            <?php if (!empty($duration_str)): ?>
                                <div class="mt-3 pt-2 border-top d-flex align-items-center text-muted small">
                                    <i class="fas fa-hourglass-start me-2 text-info"></i>
                                    <span>Duration from previous step: <span class="fw-bold text-dark"><?php echo $duration_str; ?></span></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($history)): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">No history logs found for this project.</p>
                    </div>
                <?php endif; ?>

            </div>

        <?php elseif (isset($_GET['project_id'])): ?>
            <div class="alert alert-danger text-center shadow-sm border-0" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> Project ID not found. Please verify the ID and try again.
            </div>
        <?php else: ?>
            <div class="text-center py-5 opacity-50">
                <i class="fas fa-search-location fa-5x mb-3 text-primary"></i>
                <h4>Ready to Audit</h4>
                <p>Enter a Project ID above to begin the investigation.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- TM Notes Modal -->
<div class="modal fade" id="tmNotesModal" tabindex="-1" aria-labelledby="tmNotesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="tmNotesModalLabel"><i class="fas fa-sticky-note me-2 text-primary"></i>Technical Meeting Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light rounded border" id="tmNotesModalBody" style="max-height: 60vh; overflow-y: auto;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showTmNotesModal() {
    var content = document.getElementById('tm_full_notes_content').innerHTML;
    document.getElementById('tmNotesModalBody').innerHTML = content;
    var myModal = new bootstrap.Modal(document.getElementById('tmNotesModal'));
    myModal.show();
}
</script>

<?php include '../views/layouts/footer.php'; ?>
