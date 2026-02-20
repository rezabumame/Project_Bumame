<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Dashboard Overview</h1>
            <p class="page-header-subtitle"><?php echo date('l, d F Y'); ?></p>
        </div>
        <div>
            <!-- Optional: Add a refresh button or date filter here -->
        </div>
    </div>

    <!-- Widgets -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #204EAB 0%, #4FA3D1 100%);">
                <div class="card-body position-relative p-4">
                    <div class="d-flex justify-content-between align-items-start z-1 position-relative">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75 fw-bold" style="letter-spacing: 1px;">Total Projects</h6>
                            <h2 class="mb-0 fw-bold display-5"><?php echo $stats['total']; ?></h2>
                            <div class="mt-3 badge bg-white bg-opacity-25 rounded-pill px-3 py-1 fw-normal">
                                <i class="fas fa-chart-line me-1"></i> All Time
                            </div>
                        </div>
                        <div class="p-3 bg-white bg-opacity-10 rounded-circle">
                            <i class="fas fa-folder-open fa-2x"></i>
                        </div>
                    </div>
                    <!-- Decorative Circle -->
                    <div class="position-absolute bottom-0 end-0 opacity-10" style="transform: translate(30%, 30%);">
                        <i class="fas fa-folder fa-10x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #ffc107 0%, #ffdb6e 100%); color: #5c4000 !important;">
                <div class="card-body position-relative p-4">
                    <div class="d-flex justify-content-between align-items-start z-1 position-relative">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75 fw-bold text-dark" style="letter-spacing: 1px;">Need Approval</h6>
                            <h2 class="mb-0 fw-bold display-5 text-dark"><?php echo $stats['need_approval']; ?></h2>
                            <div class="mt-3 badge bg-dark bg-opacity-10 text-dark rounded-pill px-3 py-1 fw-normal">
                                <i class="fas fa-exclamation-circle me-1"></i> Action Required
                            </div>
                        </div>
                        <div class="p-3 bg-white bg-opacity-25 rounded-circle text-dark">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                     <!-- Decorative Circle -->
                     <div class="position-absolute bottom-0 end-0 opacity-10 text-dark" style="transform: translate(30%, 30%);">
                        <i class="fas fa-hourglass-half fa-10x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                <div class="card-body position-relative p-4">
                    <div class="d-flex justify-content-between align-items-start z-1 position-relative">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75 fw-bold" style="letter-spacing: 1px;">Completed</h6>
                            <h2 class="mb-0 fw-bold display-5"><?php echo $stats['completed']; ?></h2>
                            <div class="mt-3 badge bg-white bg-opacity-25 rounded-pill px-3 py-1 fw-normal">
                                <i class="fas fa-check-double me-1"></i> Successfully Done
                            </div>
                        </div>
                        <div class="p-3 bg-white bg-opacity-10 rounded-circle">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                     <!-- Decorative Circle -->
                     <div class="position-absolute bottom-0 end-0 opacity-10" style="transform: translate(30%, 30%);">
                        <i class="fas fa-clipboard-check fa-10x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded me-3 text-primary">
                        <i class="fas fa-chart-bar fa-lg"></i>
                    </div>
                    <h5 class="mb-0 fw-bold text-dark">Project Status Breakdown</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 350px; position: relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-2 rounded me-3 text-success">
                        <i class="fas fa-chart-line fa-lg"></i>
                    </div>
                    <h5 class="mb-0 fw-bold text-dark">Monthly Trend</h5>
                </div>
                <div class="card-body px-4 pb-4 position-relative d-flex align-items-center justify-content-center">
                    <div style="width: 100%; height: 300px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Widgets Row -->
    <?php if (isset($sales_leaderboard) && isset($upcoming_projects)): ?>
    <div class="row g-4 mb-4">
        <!-- Sales Leaderboard -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-2 rounded me-3 text-warning">
                        <i class="fas fa-trophy fa-lg"></i>
                    </div>
                    <h5 class="mb-0 fw-bold text-dark">Sales Leaderboard (Top 5)</h5>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 border-0 text-muted small text-uppercase fw-bold">Rank</th>
                                    <th class="border-0 text-muted small text-uppercase fw-bold">Sales Name</th>
                                    <th class="border-0 text-muted small text-uppercase fw-bold text-center">Projects</th>
                                    <th class="border-0 text-muted small text-uppercase fw-bold text-end px-4">Total Pax</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                while($row = $sales_leaderboard->fetch(PDO::FETCH_ASSOC)): 
                                    $medal = '';
                                    if($rank == 1) $medal = '🥇';
                                    elseif($rank == 2) $medal = '🥈';
                                    elseif($rank == 3) $medal = '🥉';
                                ?>
                                <tr>
                                    <td class="px-4 fw-bold text-secondary"><?php echo $medal ? $medal : $rank; ?></td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($row['sales_name']); ?></td>
                                    <td class="text-center">
                                        <span class="fw-bold text-dark fs-6"><?php echo $row['total_projects']; ?></span>
                                    </td>
                                    <td class="text-end px-4 fw-bold text-secondary"><?php echo number_format($row['total_pax']); ?></td>
                                </tr>
                                <?php $rank++; endwhile; ?>
                                <?php if($rank == 1): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Projects -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-2 rounded me-3 text-info">
                        <i class="fas fa-calendar-alt fa-lg"></i>
                    </div>
                    <h5 class="mb-0 fw-bold text-dark">Upcoming Projects</h5>
                </div>
                <div class="card-body px-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($upcoming_projects)): ?>
                            <div class="text-center py-4 text-muted">No upcoming projects found</div>
                        <?php else: ?>
                            <?php foreach ($upcoming_projects as $project): ?>
                            <div class="list-group-item px-4 py-3 border-0 border-bottom">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-2 me-3 text-center" style="min-width: 50px;">
                                            <small class="d-block text-uppercase fw-bold text-muted" style="font-size: 10px;"><?php echo DateHelper::getShortMonthIndonesian($project['date']); ?></small>
                                            <span class="d-block fw-bold fs-5 text-dark"><?php echo date('d', strtotime($project['date'])); ?></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($project['nama_project']); ?></h6>
                                            <small class="d-block text-muted mb-1" style="font-size: 11px;">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?php echo DateHelper::formatSmartDateIndonesian($project['tanggal_mcu']); ?>
                                            </small>
                                            <?php 
                                                $statusLabel = ucfirst(str_replace('_', ' ', $project['status_project']));
                                                if ($project['status_project'] == 'vendor_assigned') $statusLabel = 'Vendor Confirmed';
                                                if ($project['status_project'] == 'no_vendor_needed') $statusLabel = 'No Vendor Needed';
                                            ?>
                                            <span class="badge rounded-pill bg-light text-secondary border fw-normal" style="font-size: 11px;">
                                                <?php echo $statusLabel; ?>
                                            </span>
                                            <?php
                                                $vendorBadge = 'bg-secondary';
                                                $vendorLabel = 'PENDING';
                                                $vStatus = $project['status_vendor'] ?? 'pending';
                                                switch($vStatus) {
                                                    case 'pending': $vendorBadge = 'bg-secondary'; $vendorLabel = 'Not Selected'; break;
                                                    case 'requested': $vendorBadge = 'bg-warning text-dark'; $vendorLabel = 'Vendor Requested'; break;
                                                    case 'assigned': $vendorBadge = 'bg-success'; $vendorLabel = 'Vendor Assigned'; break;
                                                    case 'no_vendor_needed': $vendorBadge = 'bg-info text-dark'; $vendorLabel = 'No Vendor Required'; break;
                                                }
                                            ?>
                                            <span class="badge <?php echo $vendorBadge; ?> rounded-pill px-2 py-1 ms-1" style="font-size: 0.65rem;">
                                                <?php echo $vendorLabel; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="loadProjectDetail('<?php echo $project['project_id']; ?>')">
                                        View
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Project List for Warehouse Admins -->
    <?php if (isset($all_projects_dashboard) && (in_array($_SESSION['role'], ['admin_gudang_warehouse', 'admin_gudang_aset']))): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-transparent border-0 py-4 px-4 d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-2 rounded me-3 text-primary">
                <i class="fas fa-list-ul fa-lg"></i>
            </div>
            <h5 class="mb-0 fw-bold text-dark">All Projects (View Only)</h5>
        </div>
        <div class="card-body p-0">
            <!-- Desktop Table View -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-uppercase text-muted small fw-bold border-0">Project Info</th>
                            <th class="text-center text-uppercase text-muted small fw-bold border-0">Sales</th>
                            <th class="text-center text-uppercase text-muted small fw-bold border-0">Total Participants</th>
                            <th class="text-center text-uppercase text-muted small fw-bold border-0">MCU Date</th>
                            <th class="text-center text-uppercase text-muted small fw-bold border-0">Korlap</th>
                            <th class="text-center text-uppercase text-muted small fw-bold border-0">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($all_projects_dashboard) > 0): ?>
                            <?php foreach ($all_projects_dashboard as $row): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="mb-2">
                                            <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                                <i class="fas fa-hashtag me-1"></i><?php echo $row['project_id']; ?>
                                            </span>
                                        </div>
                                        <div class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($row['nama_project']); ?></div>
                                        <div class="text-muted small"><i class="far fa-building me-1"></i><?php echo htmlspecialchars($row['company_name']); ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="avatar-circle bg-primary-subtle text-primary me-2" style="width:30px;height:30px;line-height:30px;font-size:12px; text-align: center; border-radius: 50%;">
                                                <?php echo substr($row['sales_name'] ?? '?', 0, 1); ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($row['sales_name'] ?? 'Unknown'); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                            <i class="fas fa-users me-1"></i> <?php echo $row['total_peserta']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center text-muted small">
                                            <i class="far fa-calendar-alt me-2 text-primary"></i>
                                            <div>
                                                <?php echo DateHelper::formatSmartDate($row['tanggal_mcu']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php echo htmlspecialchars($row['korlap_name'] ?? '-'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $badgeClass = 'bg-secondary';
                                            $statusLabel = strtoupper(str_replace(['_', '-'], ' ', $row['status_project']));
                                            
                                            switch($row['status_project']) {
                                                case 'need_approval_manager':
                                                case 'need_approval_head':
                                                    $badgeClass = 'bg-warning text-dark';
                                                    break;
                                                case 'approved':
                                                    $badgeClass = 'bg-success';
                                                    break;
                                                case 'process_vendor':
                                                    $badgeClass = 'bg-primary';
                                                    break;
                                                case 'vendor_assigned':
                                                    $badgeClass = 'bg-info text-dark';
                                                    $statusLabel = 'VENDOR CONFIRMED';
                                                    break;
                                                case 'no_vendor_needed':
                                                    $badgeClass = 'bg-secondary text-white';
                                                    $statusLabel = 'NO VENDOR NEEDED';
                                                    break;
                                                case 'rejected':
                                                case 'cancelled':
                                                case 're-nego':
                                                    $badgeClass = 'bg-danger';
                                                    break;
                                                case 'completed':
                                                    $badgeClass = 'bg-info';
                                                    break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No projects found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Card View -->
            <div class="d-block d-md-none bg-light p-3">
                <?php if (count($all_projects_dashboard) > 0): ?>
                    <?php foreach ($all_projects_dashboard as $row): ?>
                        <div class="card mb-3 border-0 shadow-sm rounded-4">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-2 py-1 mb-2" style="font-size: 0.7rem;">
                                            <i class="fas fa-hashtag me-1"></i><?php echo $row['project_id']; ?>
                                        </span>
                                        <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($row['nama_project']); ?></h6>
                                        <small class="text-muted d-block mt-1"><i class="far fa-building me-1"></i><?php echo htmlspecialchars($row['company_name']); ?></small>
                                    </div>
                                    <?php 
                                        $badgeClass = 'bg-secondary';
                                        $statusLabel = strtoupper(str_replace(['_', '-'], ' ', $row['status_project']));
                                        switch($row['status_project']) {
                                            case 'need_approval_manager':
                                            case 'need_approval_head': $badgeClass = 'bg-warning text-dark'; break;
                                            case 'approved': $badgeClass = 'bg-success'; break;
                                            case 'process_vendor': $badgeClass = 'bg-primary'; break;
                                            case 'vendor_assigned': 
                                                $badgeClass = 'bg-info text-dark'; 
                                                $statusLabel = 'VENDOR CONFIRMED';
                                                break;
                                            case 'no_vendor_needed': 
                                                $badgeClass = 'bg-secondary text-white'; 
                                                $statusLabel = 'NO VENDOR NEEDED';
                                                break;
                                            case 'rejected':
                                            case 'cancelled':
                                            case 're-nego': $badgeClass = 'bg-danger'; break;
                                            case 'completed': $badgeClass = 'bg-info'; break;
                                        }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                </div>
                                
                                <hr class="my-2 border-light">
                                
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem; font-weight: 700;">Sales</small>
                                        <div class="d-flex align-items-center mt-1">
                                            <div class="avatar-circle bg-primary-subtle text-primary me-2" style="width:20px;height:20px;line-height:20px;font-size:10px; text-align: center; border-radius: 50%;">
                                                <?php echo substr($row['sales_name'] ?? '?', 0, 1); ?>
                                            </div>
                                            <span class="text-dark small text-truncate" style="max-width: 100px;"><?php echo htmlspecialchars($row['sales_name'] ?? 'Unknown'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem; font-weight: 700;">Participants</small>
                                        <span class="text-dark small fw-bold"><i class="fas fa-users me-1 text-secondary"></i> <?php echo $row['total_peserta']; ?></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem; font-weight: 700;">Date</small>
                                        <span class="text-dark small fw-bold"><i class="far fa-calendar-alt me-1 text-primary"></i> <?php echo DateHelper::formatSmartDate($row['tanggal_mcu']); ?></span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem; font-weight: 700;">Korlap</small>
                                        <span class="text-dark small text-truncate d-block"><?php echo htmlspecialchars($row['korlap_name'] ?? '-'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">No projects found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Calendar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-transparent border-0 py-4 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="bg-info bg-opacity-10 p-2 rounded me-3 text-info">
                    <i class="fas fa-calendar-alt fa-lg"></i>
                </div>
                <h5 class="mb-0 fw-bold text-dark">Project Calendar</h5>
            </div>
        </div>
        <div class="card-body px-4 pb-4">
            <div id="calendar" class="fc-modern"></div>
        </div>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>Project Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h4 id="modalTitle" class="fw-bold mb-3"></h4>
                <div class="d-flex align-items-center mb-3">
                    <span id="modalStatus" class="badge rounded-pill px-3 py-2"></span>
                </div>
                <p class="text-muted mb-1"><i class="far fa-clock me-2"></i>Date:</p>
                <p id="modalDate" class="fw-medium mb-3"></p>
                
                <div id="modalFacilities" class="mb-3" style="display: none;"></div>

                <div class="d-grid">
                     <a href="javascript:void(0)" onclick="viewFullDetails()" id="modalLink" class="btn btn-primary rounded-pill">View Full Details</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Project Detail Modal -->
<?php include '../views/partials/project_detail_modal.php'; ?>
<script>
    // Global variable for project_detail.js
    var userRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
</script>
<script src="js/project_detail.js?v=<?php echo time(); ?>"></script>

<style>
    /* Medical Modern Calendar Theme */
    :root {
        --fc-border-color: #e9edf2;
        --fc-button-text-color: #5b6e88;
        --fc-button-bg-color: #ffffff;
        --fc-button-border-color: #dce1e7;
        --fc-button-hover-bg-color: #f8faff;
        --fc-button-hover-border-color: #cdd5df;
        --fc-button-active-bg-color: #eff4ff;
        --fc-button-active-border-color: #bfd3f2;
        --fc-today-bg-color: #f0f7ff;
        --medical-primary: #00A9E0; /* Medical Blue */
        --medical-secondary: #00BFA5; /* Teal */
    }

    .fc-modern {
        font-family: 'Poppins', sans-serif;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 0 0 1px rgba(0,0,0,0.03);
    }

    /* Toolbar Styling */
    .fc-modern .fc-toolbar {
        margin-bottom: 24px !important;
        padding: 8px;
    }

    .fc-modern .fc-toolbar-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
        letter-spacing: -0.5px;
    }

    .fc-modern .fc-button {
        border-radius: 50px; /* Pill shape */
        padding: 8px 20px;
        font-weight: 500;
        font-size: 0.85rem;
        letter-spacing: 0.3px;
        text-transform: capitalize;
        box-shadow: none;
        transition: all 0.2s ease;
    }

    .fc-modern .fc-button:focus {
        box-shadow: 0 0 0 3px rgba(0, 169, 224, 0.15);
    }

    .fc-modern .fc-button-primary {
        background-color: var(--fc-button-bg-color);
        color: var(--fc-button-text-color);
        border: 1px solid var(--fc-button-border-color);
    }

    .fc-modern .fc-button-primary:hover {
        background-color: var(--fc-button-hover-bg-color);
        border-color: var(--medical-primary);
        color: var(--medical-primary);
        transform: translateY(-1px);
    }

    .fc-modern .fc-button-primary:not(:disabled).fc-button-active {
        background-color: var(--medical-primary);
        border-color: var(--medical-primary);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 169, 224, 0.3);
    }

    /* Header Cells */
    .fc-modern .fc-col-header-cell {
        background-color: #f8faff;
        padding: 12px 0;
        border: none;
        border-bottom: 2px solid #eef2f7;
    }

    .fc-modern .fc-col-header-cell-cushion {
        color: #8fa0b5;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 1.2px;
    }

    /* Grid & Days */
    .fc-modern .fc-scrollgrid {
        border: none;
    }

    .fc-modern td, .fc-modern th {
        border-color: #f1f4f8;
    }

    .fc-modern .fc-daygrid-day-top {
        justify-content: center; /* Center date number */
        padding-top: 12px;
    }

    .fc-modern .fc-daygrid-day-number {
        font-size: 0.95rem;
        font-weight: 500;
        color: #7b8a9e;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
        text-decoration: none !important;
    }

    .fc-modern .fc-day-today .fc-daygrid-day-number {
        background-color: var(--medical-primary);
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 4px 8px rgba(0, 169, 224, 0.25);
    }

    .fc-modern .fc-daygrid-day:hover {
        background-color: #fafbfc;
    }

    /* Events Styling - Medical Card Style */
    .fc-modern .fc-daygrid-day-events {
        margin-top: 8px;
    }

    .fc-modern .fc-event {
        background: transparent;
        border: none;
        margin-bottom: 4px;
        padding: 0 4px;
    }

    .custom-event-pill {
        background-color: #ffffff;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 0.75rem;
        color: #344767;
        display: flex;
        align-items: center;
        width: 100%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        border: 1px solid #edf2f7;
        border-left-width: 4px; /* Status indicator */
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .custom-event-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.06);
        border-color: #e2e8f0;
        z-index: 10;
    }

    .custom-event-pill i {
        margin-right: 8px;
        font-size: 0.8rem;
        opacity: 1; /* Full opacity for vibrant icons */
    }

    .custom-event-pill .fc-event-title {
        font-weight: 600;
        color: #344767;
        letter-spacing: 0.2px;
    }
    
    /* More Link */
    .fc-modern .fc-daygrid-more-link {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--medical-primary);
        background: rgba(0, 169, 224, 0.08);
        padding: 4px 10px;
        border-radius: 20px;
        margin-top: 4px;
        text-decoration: none;
    }

    .fc-modern .fc-daygrid-more-link:hover {
        background: rgba(0, 169, 224, 0.15);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Status Mapping
        const statusMap = {
            'need_approval_manager': 'Wait Manager',
            'need_approval_head': 'Wait Head',
            'approved': 'Approved',
            'in_progress_ops': 'In Progress Ops',
            'rejected': 'Rejected',
            'cancelled': 'Cancelled',
            're-nego': 'Re-Nego',
            'completed': 'Completed'
        };

        const statusColors = {
            'need_approval_manager': '#ffc107',
            'need_approval_head': '#fd7e14',
            'approved': '#0d6efd',
            'in_progress_ops': '#6610f2',
            'rejected': '#dc3545',
            'cancelled': '#6c757d',
            're-nego': '#ffc107',
            'completed': '#198754'
        };

        // Status Chart
        var rawStatusKeys = <?php echo json_encode(array_keys($stats['status_breakdown'])); ?>;
        var statusLabels = rawStatusKeys.map(key => statusMap[key] || key.replace(/_/g, ' '));
        var statusBgColors = rawStatusKeys.map(key => statusColors[key] || '#204EAB');

        var ctxStatus = document.getElementById('statusChart').getContext('2d');
        var statusChart = new Chart(ctxStatus, {
            type: 'bar',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Projects',
                    data: <?php echo json_encode(array_values($stats['status_breakdown'])); ?>,
                    backgroundColor: statusBgColors,
                    borderRadius: 8,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(32, 78, 171, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f0f0f0' },
                        ticks: { font: { family: "'Poppins', sans-serif" } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Poppins', sans-serif" }, autoSkip: false, maxRotation: 45, minRotation: 0 }
                    }
                }
            }
        });

        // Monthly Trend Chart (Replaces Vendor Chart)
        var ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
        var monthlyChart = new Chart(ctxMonthly, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Projects Created',
                    data: <?php echo json_encode(array_values($monthly_stats)); ?>,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#198754',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(25, 135, 84, 0.9)',
                        padding: 10,
                        cornerRadius: 6,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f0f0f0' },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // FullCalendar
        var calendarEl = document.getElementById('calendar');
        var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'standard', // We use custom CSS
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                list: 'List'
            },
            dayMaxEvents: 5, // Limit to 5 events per day
            fixedWeekCount: false, // Don't force 6 rows
            contentHeight: 'auto', // Auto height
            events: 'index.php?page=get_calendar_events',
            eventContent: function(arg) {
                var status = arg.event.extendedProps.status;
                var color = statusColors[status] || '#3788d8';
                
                // Icon Logic
                let icon = 'fa-circle';
                let status_lower = status ? status.toLowerCase() : '';

                if (status_lower.includes('approved')) icon = 'fa-check-circle';
                else if (status_lower.includes('rejected') || status_lower.includes('cancelled')) icon = 'fa-times-circle';
                else if (status_lower.includes('re-nego')) icon = 'fa-sync-alt';
                else if (status_lower.includes('completed')) icon = 'fa-flag-checkered';
                else if (status_lower.includes('need_approval')) icon = 'fa-clock';
                else if (status_lower.includes('created')) icon = 'fa-plus-circle';
                else if (status_lower.includes('sph')) icon = 'fa-file-pdf';
                else if (status_lower.includes('vendor')) icon = 'fa-truck';
                else if (status_lower.includes('assign')) icon = 'fa-user-tag';
                else if (status_lower.includes('korlap')) icon = 'fa-hard-hat';

                let customHtml = `
                    <div class="custom-event-pill" style="border-left-color: ${color};">
                        <i class="fas ${icon}" style="color: ${color};"></i>
                        <div class="fc-event-title">${arg.event.title}</div>
                    </div>
                `;
                
                return { html: customHtml };
            },
            eventDidMount: function(info) {
                // Add tooltip
                if(info.event.title) {
                    info.el.title = info.event.title + (info.event.extendedProps.description ? '\n' + info.event.extendedProps.description : '');
                }
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault(); // don't let the browser navigate

                document.getElementById('modalTitle').textContent = info.event.title;
                document.getElementById('modalDate').textContent = info.event.extendedProps.formatted_date || info.event.start.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                
                var status = info.event.extendedProps.status;
                var statusEl = document.getElementById('modalStatus');
                
                // Standardized Icon Logic
                let badgeClass = 'secondary';
                let icon = 'fa-circle';
                let status_lower = status.toLowerCase();

                if (status_lower == 'approved' || status_lower.indexOf('status changed to approved') !== -1 || status_lower == 'consumption approved' || status_lower.indexOf('approved by manager') !== -1 || status_lower.indexOf('approved by head') !== -1) { 
                    badgeClass = 'success'; 
                    icon = 'fa-check-circle'; 
                }
                else if (status_lower == 'rejected' || status_lower == 'cancelled') { 
                    badgeClass = 'danger'; 
                    icon = 'fa-times-circle'; 
                }
                else if (status_lower == 're-nego') { 
                    badgeClass = 'warning'; 
                    icon = 'fa-sync-alt'; 
                }
                else if (status_lower == 'completed') { 
                    badgeClass = 'primary'; 
                    icon = 'fa-flag-checkered'; 
                }
                else if (status_lower == 'need_approval_manager' || status_lower.indexOf('need approval manager') !== -1) { 
                    badgeClass = 'warning'; 
                    icon = 'fa-user-tie'; 
                }
                else if (status_lower == 'need_approval_head' || status_lower.indexOf('need approval head') !== -1) { 
                    badgeClass = 'info'; 
                    icon = 'fa-user-shield'; 
                }
                else if (status_lower.indexOf('created') !== -1) { 
                    badgeClass = 'primary'; 
                    icon = 'fa-plus-circle'; 
                }
                else if (status_lower.indexOf('sph uploaded') !== -1) { 
                    badgeClass = 'danger'; 
                    icon = 'fa-file-pdf'; 
                }
                else if (status_lower == 'vendor_assigned' || status_lower.indexOf('vendor assigned') !== -1) { 
                    badgeClass = 'info'; 
                    icon = 'fa-truck'; 
                }
                else if (status_lower == 'process_vendor' || status_lower.indexOf('vendor package') !== -1) { 
                    badgeClass = 'info'; 
                    icon = 'fa-truck-loading'; 
                }
                else if (status_lower.indexOf('korlap') !== -1) { 
                    badgeClass = 'success'; 
                    icon = 'fa-hard-hat'; 
                }
                else if (status_lower.indexOf('assign') !== -1) { 
                    badgeClass = 'info'; 
                    icon = 'fa-user-tag'; 
                }

                // Format Status Text
                let statusText = status.replace(/_/g, ' ').replace(/-/g, ' ').toUpperCase();
                
                statusEl.innerHTML = `<i class="fas ${icon} me-2"></i>${statusText}`;
                statusEl.className = `badge rounded-pill px-3 py-2 bg-${badgeClass}`;
                
                // Clear inline styles that might override class
                statusEl.style.backgroundColor = '';
                statusEl.style.color = '';
                statusEl.style.borderColor = '';

                // Facilities
                var props = info.event.extendedProps;
                var facilitiesHtml = '';
                
                if (props.lunch && props.lunch.toLowerCase() === 'ya') {
                    facilitiesHtml += '<div class="mb-2"><small class="text-muted d-block">Lunch</small><span class="fw-medium text-dark">Yes</span>';
                    if (props.lunch_notes) {
                        facilitiesHtml += '<div class="text-muted small fst-italic mt-1"><i class="fas fa-utensils me-1"></i>' + props.lunch_notes + '</div>';
                    }
                    facilitiesHtml += '</div>';
                }
                
                if (props.snack && props.snack.toLowerCase() === 'ya') {
                     facilitiesHtml += '<div class="mb-2"><small class="text-muted d-block">Snack</small><span class="fw-medium text-dark">Yes</span>';
                    if (props.snack_notes) {
                        facilitiesHtml += '<div class="text-muted small fst-italic mt-1"><i class="fas fa-cookie-bite me-1"></i>' + props.snack_notes + '</div>';
                    }
                     facilitiesHtml += '</div>';
                }

                var facilitiesContainer = document.getElementById('modalFacilities');
                if (facilitiesHtml) {
                    facilitiesContainer.innerHTML = '<hr class="my-3">' + facilitiesHtml;
                    facilitiesContainer.style.display = 'block';
                } else {
                    facilitiesContainer.style.display = 'none';
                }

                // Set Link
                // document.getElementById('modalLink').href = 'index.php?page=project_detail&id=' + info.event.id;
                document.getElementById('modalLink').dataset.id = info.event.id;
                
                eventModal.show();
            },
            height: 650
        });
        calendar.render();
    });

    function viewFullDetails() {
        var id = document.getElementById('modalLink').dataset.id;
        if(id) {
            // Hide small modal
            var eventModalEl = document.getElementById('eventModal');
            var eventModal = bootstrap.Modal.getInstance(eventModalEl);
            eventModal.hide();
            
            // Open full modal
            loadProjectDetail(id);
        }
    }
</script>

<?php include '../views/layouts/footer.php'; ?>
