<?php include '../views/layouts/header.php'; ?>
<?php // Helper should be included by Controller, but for safety in view:
if (!class_exists('DateHelper')) {
    include_once __DIR__ . '/../../helpers/DateHelper.php';
}
?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">All Projects</h1>
            <p class="page-header-subtitle">Manage and view all MCU projects.</p>
        </div>
        <div>
            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin_sales' || $_SESSION['role'] == 'superadmin')): ?>
            <a href="index.php?page=projects_create" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i>New Project
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="page" value="all_projects">
                
                <div class="col-md-3">
                    <label class="form-label text-muted small text-uppercase fw-bold">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light" name="search" placeholder="Project Name, Company..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-muted small text-uppercase fw-bold">Status</label>
                    <select class="form-select bg-light" name="status">
                        <option value="">All Statuses</option>
                        <option value="need_approval_manager" <?php echo $status == 'need_approval_manager' ? 'selected' : ''; ?>>Need Approval (Manager)</option>
                        <option value="need_approval_head" <?php echo $status == 'need_approval_head' ? 'selected' : ''; ?>>Need Approval (Head)</option>
                        <option value="approved" <?php echo $status == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="re-nego" <?php echo $status == 're-nego' ? 'selected' : ''; ?>>Re-nego</option>
                        <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="ready_for_invoicing" <?php echo $status == 'ready_for_invoicing' ? 'selected' : ''; ?>>Ready for Invoicing</option>
                        <option value="invoice_requested" <?php echo $status == 'invoice_requested' ? 'selected' : ''; ?>>Invoice Requested</option>
                        <option value="invoiced" <?php echo $status == 'invoiced' ? 'selected' : ''; ?>>Invoiced</option>
                        <option value="paid" <?php echo $status == 'paid' ? 'selected' : ''; ?>>Paid</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted small text-uppercase fw-bold">Date Range (Created)</label>
                    <div class="input-group">
                        <input type="date" class="form-control bg-light" name="date_from" value="<?php echo $date_from; ?>">
                        <span class="input-group-text bg-light border-0">to</span>
                        <input type="date" class="form-control bg-light" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-card">
        <div class="card-body p-0">
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 text-uppercase text-muted fw-bold border-0">Project Info</th>
                            <th class="text-center text-uppercase text-muted fw-bold border-0">Type</th>
                            <th class="text-center text-uppercase text-muted fw-bold border-0">Sales</th>
                            <th class="text-center text-uppercase text-muted fw-bold border-0">Pax</th>
                            <th class="text-center text-uppercase text-muted fw-bold border-0">MCU Date</th>
                            <th class="text-center text-uppercase text-muted fw-bold border-0">Korlap</th>
                            <th class="text-center text-uppercase text-muted fw-bold border-0">Kohas</th>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'procurement'): ?>
                                <th class="text-center text-uppercase text-muted fw-bold border-0" width="10%">F&B</th>
                            <?php endif; ?>
                            <th class="text-center text-uppercase text-muted fw-bold border-0">Project Status</th>
                            <th class="text-center text-uppercase text-muted fw-bold border-0">Vendor Status</th>
                            <th class="text-center text-uppercase text-muted fw-bold border-0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($projects) > 0): ?>
                            <?php foreach ($projects as $row): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="mb-1">
                                            <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-2">
                                                <i class="fas fa-hashtag me-1"></i><?php echo $row['project_id']; ?>
                                            </span>
                                        </div>
                                        <div class="fw-bold text-dark" title="<?php echo htmlspecialchars($row['nama_project']); ?>"><?php echo htmlspecialchars($row['nama_project']); ?></div>
                                        <div class="text-muted small text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($row['company_name']); ?>"><i class="far fa-building me-1"></i><?php echo htmlspecialchars($row['company_name']); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $pType = $row['project_type'] ?? 'on-site';
                                            // Handle both database format (walk_in) and form value (walk-in)
                                            if($pType == 'walk-in' || $pType == 'walk_in') {
                                                echo '<span class="badge bg-info text-dark rounded-pill"><i class="fas fa-walking me-1"></i>Walk-In</span>';
                                                if(!empty($row['clinic_location'])) {
                                                     echo '<div class="small text-muted mt-1" style="font-size: 0.75rem;"><i class="fas fa-hospital me-1"></i>' . htmlspecialchars($row['clinic_location']) . '</div>';
                                                }
                                            } else {
                                                echo '<span class="badge bg-light text-dark border rounded-pill"><i class="fas fa-building me-1"></i>On-Site</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="avatar-circle bg-primary-subtle text-primary me-2" style="width:30px;height:30px;line-height:30px;font-size:12px;text-align:center;border-radius:50%;" title="<?php echo htmlspecialchars($row['sales_name'] ?? 'Unknown'); ?>">
                                                <?php echo substr($row['sales_name'] ?? '?', 0, 1); ?>
                                            </div>
                                            <span class="text-dark fw-bold"><?php echo htmlspecialchars($row['sales_name'] ?? '-'); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border rounded-pill px-2">
                                            <?php echo $row['total_peserta']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center text-muted small">
                                            <div>
                                                <?php echo DateHelper::formatSmartDate($row['tanggal_mcu']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center small">
                                        <?php echo htmlspecialchars($row['korlap_name'] ?? '-'); ?>
                                    </td>
                                    <td class="text-center small">
                                        <?php echo htmlspecialchars($row['kohas_names'] ?? '-'); ?>
                                    </td>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'procurement'): ?>
                                        <td>
                                            <?php 
                                                $hasCons = false;
                                                // Check Lunch
                                                if ($row['lunch'] == 'Ya') {
                                                    $hasCons = true;
                                                    echo '<div class="d-flex align-items-center mb-1">';
                                                    echo '<div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-1" style="width:24px;height:24px;" title="Lunch"><i class="fas fa-utensils" style="font-size:12px;"></i></div>';
                                                    if (!empty($row['lunch_notes'])) {
                                                        echo '<span class="small text-truncate" style="max-width: 150px;" title="' . htmlspecialchars($row['lunch_notes']) . '">' . htmlspecialchars($row['lunch_notes']) . '</span>';
                                                    } else {
                                                        echo '<span class="small">Lunch</span>';
                                                    }
                                                    echo '</div>';
                                                }
                                                // Check Snack
                                                if ($row['snack'] == 'Ya') {
                                                    $hasCons = true;
                                                    echo '<div class="d-flex align-items-center">';
                                                    echo '<div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-1" style="width:24px;height:24px;" title="Snack"><i class="fas fa-cookie-bite" style="font-size:12px;"></i></div>';
                                                    if (!empty($row['snack_notes'])) {
                                                        echo '<span class="small text-truncate" style="max-width: 150px;" title="' . htmlspecialchars($row['snack_notes']) . '">' . htmlspecialchars($row['snack_notes']) . '</span>';
                                                    } else {
                                                        echo '<span class="small">Snack</span>';
                                                    }
                                                    echo '</div>';
                                                }
                                                if (!$hasCons) {
                                                    echo '<span class="text-muted small">-</span>';
                                                }
                                            ?>
                                        </td>
                                    <?php endif; ?>
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
                                                case 'process_vendor': // Legacy handling
                                                    $badgeClass = 'bg-primary';
                                                    break;
                                                case 'vendor_assigned': // Legacy handling
                                                    $badgeClass = 'bg-info text-dark';
                                                    $statusLabel = 'VENDOR CONFIRMED';
                                                    break;
                                                case 'no_vendor_needed': // Legacy handling
                                                    $badgeClass = 'bg-secondary text-white';
                                                    $statusLabel = 'NO VENDOR NEEDED';
                                                    break;
                                                case 'rejected':
                                                case 'cancelled':
                                                case 're-nego':
                                                    $badgeClass = 'bg-danger';
                                                    break;
                                                case 'in_progress_ops':
                                                    $badgeClass = 'bg-primary';
                                                    break;
                                                case 'completed':
                                                    $badgeClass = 'bg-success';
                                                    break;
                                                case 'ready_for_invoicing':
                                                    $badgeClass = 'bg-info text-dark';
                                                    $statusLabel = 'READY FOR INVOICING';
                                                    break;
                                                case 'invoice_requested':
                                                    $badgeClass = 'bg-info text-dark';
                                                    $statusLabel = 'INVOICE REQUESTED';
                                                    break;
                                                case 'invoiced':
                                                    $badgeClass = 'bg-primary';
                                                    $statusLabel = 'INVOICED';
                                                    break;
                                                case 'paid':
                                                    $badgeClass = 'bg-success';
                                                    $statusLabel = 'PAID';
                                                    break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                            $vendorBadge = 'bg-secondary';
                                            $vendorLabel = 'PENDING';
                                            $vStatus = $row['status_vendor'] ?? 'pending';
                                            
                                            switch($vStatus) {
                                                case 'pending':
                                                    $vendorBadge = 'bg-secondary';
                                                    $vendorLabel = 'Not Selected';
                                                    break;
                                                case 'requested':
                                                    $vendorBadge = 'bg-warning text-dark';
                                                    $vendorLabel = 'Vendor Requested';
                                                    break;
                                                case 'assigned':
                                                    $vendorBadge = 'bg-success';
                                                    $vendorLabel = 'Vendor Assigned';
                                                    break;
                                                case 'no_vendor_needed':
                                                    $vendorBadge = 'bg-info text-dark';
                                                    $vendorLabel = 'No Vendor Required';
                                                    break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $vendorBadge; ?> rounded-pill px-3">
                                            <?php echo $vendorLabel; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex justify-content-end align-items-center gap-1">
                                            <button class="btn btn-sm btn-outline-secondary" onclick="loadProjectDetail('<?php echo $row['project_id']; ?>')" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin_sales' || $_SESSION['role'] == 'superadmin')): ?>
                                                
                                                <?php 
                                                $can_edit = false;
                                                // Walk-In approved projects can only be edited by superadmin
                                                if ($row['project_type'] == 'walk_in' && $row['status_project'] == 'approved') {
                                                    $can_edit = ($_SESSION['role'] == 'superadmin');
                                                } elseif ($row['status_project'] == 're-nego' || $row['status_project'] == 'rejected' || $row['status_project'] == 'need_approval_manager') {
                                                    $can_edit = true;
                                                } elseif ($row['status_project'] == 'need_approval_head' && empty($row['approved_by_manager'])) {
                                                    $can_edit = true;
                                                }
                                                
                                                if ($can_edit): 
                                                ?>
                                                    <a href="index.php?page=projects_edit&id=<?php echo $row['project_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin_ops' || $_SESSION['role'] == 'korlap') && in_array(trim($row['status_project']), ['approved', 'in_progress_ops'])): ?>
                                                <button class="btn btn-sm btn-outline-warning btn-assign-vendor" 
                                                        data-id="<?php echo $row['project_id']; ?>" 
                                                        data-project-name="<?php echo htmlspecialchars($row['nama_project']); ?>"
                                                        data-lunch="<?php echo $row['lunch']; ?>"
                                                        data-snack="<?php echo $row['snack']; ?>"
                                                        data-mcu-date="<?php echo DateHelper::formatSmartDateIndonesian($row['tanggal_mcu']); ?>"
                                                        data-sales-name="<?php echo htmlspecialchars($row['sales_name'] ?? '-'); ?>"
                                                        title="Request Vendor Requirements">
                                                    <i class="fas fa-tasks"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin_ops' && in_array(trim($row['status_project']), ['approved', 'in_progress_ops'])): ?>
                                                <?php if (empty($row['korlap_id'])): ?>
                                                    <button class="btn btn-sm btn-outline-info btn-assign-korlap" data-id="<?php echo $row['project_id']; ?>" title="Assign Korlap">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="editKorlap('<?php echo $row['project_id']; ?>')" title="Edit Korlap">
                                                        <i class="fas fa-user-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if (!empty($row['korlap_id']) && (isset($_SESSION['role']) && ($_SESSION['role'] == 'korlap' || $_SESSION['role'] == 'admin_ops')) && empty($row['tm_id'])): ?>
                                                <a href="index.php?page=technical_meeting_create&project_id=<?php echo $row['project_id']; ?>" class="btn btn-sm btn-outline-info" title="Technical Meeting">
                                                    <i class="fas fa-handshake"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'procurement'): ?>
                                                <?php 
                                                // Procurement sees "Assign Vendor Name" if status_vendor is requested or assigned
                                                $vStatus = $row['status_vendor'] ?? 'pending';
                                                if ($vStatus == 'requested' || $vStatus == 'assigned' || $row['status_project'] == 'process_vendor' || $row['status_project'] == 'vendor_assigned'): 
                                                ?>
                                                    <button class="btn btn-sm btn-outline-success btn-procurement-assign" data-id="<?php echo $row['project_id']; ?>" title="Assign Vendor Name">
                                                        <i class="fas fa-truck-loading"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (($row['lunch'] == 'Ya' || $row['snack'] == 'Ya') && (!isset($row['consumption_status']) || $row['consumption_status'] != 'approved')): ?>
                                                    <?php if (!in_array(trim($row['status_project']), ['rejected', 'cancelled'])): ?>
                                                        <button class="btn btn-sm btn-outline-warning" 
                                                                onclick="openConsumptionModal(this)"
                                                                data-project-id="<?php echo $row['project_id']; ?>"
                                                                data-lunch="<?php echo $row['lunch']; ?>"
                                                                data-snack="<?php echo $row['snack']; ?>"
                                                                data-lunch-qty="<?php echo $row['procurement_lunch_qty'] ?? 0; ?>"
                                                                data-snack-qty="<?php echo $row['procurement_snack_qty'] ?? 0; ?>"
                                                                data-lunch-budget="<?php echo htmlspecialchars($row['lunch_budget'] ?? 0); ?>"
                                                                data-snack-budget="<?php echo htmlspecialchars($row['snack_budget'] ?? 0); ?>"
                                                                data-lunch-items="<?php 
                                                                    $lItems = $row['lunch_items'] ?? '[]';
                                                                    echo htmlspecialchars(empty($lItems) ? '[]' : $lItems, ENT_QUOTES); 
                                                                ?>"
                                                                data-snack-items="<?php 
                                                                    $sItems = $row['snack_items'] ?? '[]';
                                                                    echo htmlspecialchars(empty($sItems) ? '[]' : $sItems, ENT_QUOTES); 
                                                                ?>"
                                                                title="Approve Consumption">
                                                            <i class="fas fa-utensils"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php elseif (($row['lunch'] == 'Ya' || $row['snack'] == 'Ya') && isset($row['consumption_status']) && $row['consumption_status'] == 'approved'): ?>
                                                     <span class="badge bg-success" title="Consumption Approved" style="font-size: 0.65rem;"><i class="fas fa-check"></i> Food</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php 
                                            // Define statuses where the Cancel button should be HIDDEN (Kanban statuses)
                                            $kanban_statuses = [
                                                'need_approval_manager', 
                                                'need_approval_head', 
                                                'approved', 
                                                'rejected', 
                                                're-nego', 
                                                'cancelled'
                                            ];
                                            ?>
                                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin' && !in_array(trim($row['status_project']), $kanban_statuses)): ?>
                                                <button class="btn btn-sm btn-outline-danger btn-cancel-project" data-id="<?php echo $row['project_id']; ?>" title="Cancel Project">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">No projects found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Card View -->
            <div class="d-block d-md-none bg-light p-3">
                <?php if (count($projects) > 0): ?>
                    <?php foreach ($projects as $row): ?>
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
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <?php 
                                            $badgeClass = 'bg-secondary';
                                            $statusLabel = strtoupper(str_replace(['_', '-'], ' ', $row['status_project']));
                                            switch($row['status_project']) {
                                                case 'need_approval_manager':
                                                case 'need_approval_head': $badgeClass = 'bg-warning text-dark'; break;
                                                case 'approved': $badgeClass = 'bg-success'; break;
                                                case 'process_vendor': 
                                                case 'in_progress_ops': 
                                                    $badgeClass = 'bg-primary'; break;
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
                                                case 'ready_for_invoicing':
                                                    $badgeClass = 'bg-info text-dark'; 
                                                    $statusLabel = 'READY FOR INVOICING';
                                                    break;
                                                case 'invoice_requested':
                                                    $badgeClass = 'bg-info text-dark'; 
                                                    $statusLabel = 'INVOICE REQUESTED';
                                                    break;
                                                case 'invoiced':
                                                    $badgeClass = 'bg-primary'; 
                                                    $statusLabel = 'INVOICED';
                                                    break;
                                                case 'paid':
                                                    $badgeClass = 'bg-success'; 
                                                    $statusLabel = 'PAID';
                                                    break;
                                                case 'completed': $badgeClass = 'bg-info'; break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                            <?php echo $statusLabel; ?>
                                        </span>

                                        <!-- Vendor Status Badge -->
                                        <?php
                                            $vendorBadge = 'bg-secondary';
                                            $vendorLabel = 'PENDING';
                                            $vStatus = $row['status_vendor'] ?? 'pending';
                                            switch($vStatus) {
                                                case 'pending': $vendorBadge = 'bg-secondary'; $vendorLabel = 'Not Selected'; break;
                                                case 'requested': $vendorBadge = 'bg-warning text-dark'; $vendorLabel = 'Vendor Requested'; break;
                                                case 'assigned': $vendorBadge = 'bg-success'; $vendorLabel = 'Vendor Assigned'; break;
                                                case 'no_vendor_needed': $vendorBadge = 'bg-info text-dark'; $vendorLabel = 'No Vendor Required'; break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $vendorBadge; ?> rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                            <?php echo $vendorLabel; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <hr class="my-2 border-light">
                                
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem; font-weight: 700;">Sales</small>
                                        <div class="d-flex align-items-center mt-1">
                                            <div class="avatar-circle bg-primary-subtle text-primary me-2" style="width:20px;height:20px;line-height:20px;font-size:10px;">
                                                <?php echo substr($row['sales_name'] ?? '?', 0, 1); ?>
                                            </div>
                                            <span class="text-dark small"><?php echo htmlspecialchars($row['sales_name'] ?? 'Unknown'); ?></span>
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
                                    <div class="col-6">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem; font-weight: 700;">Kohas</small>
                                        <span class="text-dark small text-truncate d-block"><?php echo htmlspecialchars($row['kohas_names'] ?? '-'); ?></span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="loadProjectDetail('<?php echo $row['project_id']; ?>')" title="Detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    
                                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin_sales' || $_SESSION['role'] == 'superadmin')): ?>
                                        
                                        <?php 
                                        $can_edit = false;
                                        if ($row['status_project'] == 're-nego' || $row['status_project'] == 'rejected' || $row['status_project'] == 'need_approval_manager') {
                                            $can_edit = true;
                                        } elseif ($row['status_project'] == 'need_approval_head' && empty($row['approved_by_manager'])) {
                                            $can_edit = true;
                                        }
                                        if ($can_edit): 
                                        ?>
                                            <a href="index.php?page=projects_edit&id=<?php echo $row['project_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin_ops' || $_SESSION['role'] == 'korlap') && in_array(trim($row['status_project']), ['approved', 'process_vendor', 'no_vendor_needed', 'in_progress_ops'])): ?>
                                        <button class="btn btn-sm btn-outline-warning btn-assign-vendor" 
                                                data-id="<?php echo $row['project_id']; ?>" 
                                                data-project-name="<?php echo htmlspecialchars($row['nama_project']); ?>"
                                                data-lunch="<?php echo $row['lunch']; ?>"
                                                data-snack="<?php echo $row['snack']; ?>"
                                                data-mcu-date="<?php echo DateHelper::formatSmartDateIndonesian($row['tanggal_mcu']); ?>"
                                                data-sales-name="<?php echo htmlspecialchars($row['sales_name'] ?? '-'); ?>"
                                                title="Request Vendor">
                                            <i class="fas fa-tasks"></i> Vendor
                                        </button>
                                    <?php endif; ?>

                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin_ops' && in_array(trim($row['status_project']), ['approved', 'process_vendor', 'vendor_assigned', 'no_vendor_needed', 'in_progress_ops'])): ?>
                                            <?php if (empty($row['korlap_id'])): ?>
                                                <button class="btn btn-sm btn-outline-info btn-assign-korlap" data-id="<?php echo $row['project_id']; ?>" title="Assign Korlap">
                                                    <i class="fas fa-user-check"></i> Assign Korlap
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="editKorlap('<?php echo $row['project_id']; ?>')" title="Edit Korlap">
                                                    <i class="fas fa-user-edit"></i> Edit Korlap
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if (!empty($row['korlap_id']) && (isset($_SESSION['role']) && ($_SESSION['role'] == 'korlap' || $_SESSION['role'] == 'admin_ops')) && empty($row['tm_id'])): ?>
                                            <a href="index.php?page=technical_meeting_create&project_id=<?php echo $row['project_id']; ?>" class="btn btn-sm btn-outline-info shadow-sm" title="Technical Meeting">
                                                <i class="fas fa-handshake"></i> TM
                                            </a>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'procurement'): ?>
                                            <?php 
                                            $vStatus = $row['status_vendor'] ?? 'pending';
                                            if ($vStatus == 'requested' || $vStatus == 'assigned' || $row['status_project'] == 'process_vendor' || $row['status_project'] == 'vendor_assigned'): 
                                            ?>
                                                <button class="btn btn-sm btn-outline-success btn-procurement-assign" data-id="<?php echo $row['project_id']; ?>" title="Assign Vendor Name">
                                                    <i class="fas fa-truck-loading"></i> Assign
                                                </button>
                                            <?php endif; ?>
                                            <?php if (($row['lunch'] == 'Ya' || $row['snack'] == 'Ya') && (!isset($row['consumption_status']) || $row['consumption_status'] != 'approved')): ?>
                                                <?php if (in_array(trim($row['status_project']), ['approved', 'process_vendor', 'vendor_assigned', 'in_progress_ops'])): ?>
                                                    <button class="btn btn-sm btn-outline-warning" 
                                                            onclick="openConsumptionModal(this)"
                                                            data-project-id="<?php echo $row['project_id']; ?>"
                                                            data-lunch="<?php echo $row['lunch']; ?>"
                                                            data-snack="<?php echo $row['snack']; ?>"
                                                            data-lunch-qty="<?php echo $row['procurement_lunch_qty'] ?? 0; ?>"
                                                            data-snack-qty="<?php echo $row['procurement_snack_qty'] ?? 0; ?>"
                                                            data-lunch-budget="<?php echo $row['lunch_budget'] ?? 0; ?>"
                                                            data-snack-budget="<?php echo $row['snack_budget'] ?? 0; ?>"
                                                            data-mcu-date="<?php echo DateHelper::formatSmartDateIndonesian($row['tanggal_mcu']); ?>"
                                                            data-project-name="<?php echo htmlspecialchars($row['nama_project']); ?>"
                                                            data-sales-name="<?php echo htmlspecialchars($row['sales_name'] ?? '-'); ?>"
                                                            data-lunch-items="<?php 
                                                                $lItems = $row['lunch_items'] ?? '[]';
                                                                echo htmlspecialchars(empty($lItems) ? '[]' : $lItems, ENT_QUOTES); 
                                                            ?>"
                                                            data-snack-items="<?php 
                                                                $sItems = $row['snack_items'] ?? '[]';
                                                                echo htmlspecialchars(empty($sItems) ? '[]' : $sItems, ENT_QUOTES); 
                                                            ?>"
                                                            title="Acknowledge Consumption">
                                                        <i class="fas fa-utensils"></i> Food
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php 
                                        $kanban_statuses = [
                                            'need_approval_manager', 
                                            'need_approval_head', 
                                            'approved', 
                                            'rejected', 
                                            're-nego', 
                                            'cancelled'
                                        ];
                                        ?>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin' && !in_array(trim($row['status_project']), $kanban_statuses)): ?>
                                            <button class="btn btn-sm btn-outline-danger shadow-sm btn-cancel-project" data-id="<?php echo $row['project_id']; ?>" title="Cancel Project">
                                                <i class="fas fa-ban"></i> Cancel
                                            </button>
                                        <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">No projects found.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?page=all_projects&p=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $status; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Project Detail Modal -->
<?php include '../views/partials/project_detail_modal.php'; ?>
<?php include '../views/admin_sales/lark_template_modal.php'; ?>
<script>
    // Global variable for project_detail.js
    var userRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
</script>
<script src="js/project_detail.js?v=<?php echo time(); ?>"></script>

<!-- Assign Vendor Modal -->
<div class="modal fade" id="assignVendorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Vendor Requirements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="vendor_project_id">
                <div class="table-responsive">
                    <table class="table table-bordered" id="vendorTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Exam Type</th>
                                <th width="150">Count</th>
                                <th>Notes</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows -->
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-success" onclick="addVendorRow()"><i class="fas fa-plus"></i> Add Row</button>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <input type="hidden" id="vendor_lark_clicked" value="0">
                <div id="vendorLarkContainer" class="d-none">
                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3" onclick="showLarkTemplate()">
                        <i class="fas fa-file-invoice me-1"></i> Template Lark
                    </button>
                    <a href="<?php echo $lark_link ?? 'https://www.larksuite.com'; ?>" target="_blank" class="btn btn-primary rounded-pill px-4" onclick="markLarkClicked(); if(typeof markVendorLarkClicked === 'function') markVendorLarkClicked();">
                    <i class="fas fa-external-link-alt me-2"></i>Open Lark
                </a>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary rounded-pill px-3 ms-1 btn-save-vendor" onclick="saveVendorAllocations()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Korlap Modal -->
<div class="modal fade" id="assignKorlapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Korlap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="korlap_project_id">
                <div class="mb-3">
                    <label class="form-label">Select Korlap</label>
                    <select class="form-select" id="korlap_select">
                        <option value="">Select Korlap...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveKorlapAssignment()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Vendor Procurement Modal -->
<div class="modal fade" id="assignVendorProcurementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Procurement: Assign Vendors</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="procurement_project_id">
                <datalist id="vendorList">
                    <!-- Populated by AJAX -->
                </datalist>
                <div class="alert alert-info small">
                    Please assign a vendor for each requirement listed below.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="procurementVendorTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Package / Item</th>
                                <th>Count</th>
                                <th>Notes</th>
                                <th width="250">Assign Vendor Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveProcurementVendor()">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Consumption Modal -->
<div class="modal fade" id="approveConsumptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pe-4 pt-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Konfirmasi Konsumsi</h5>
                        <small class="text-muted">Review detail permintaan dari Sales</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="consumption_modal_body">
                <!-- Content injected by JS -->
            </div>
            <div class="modal-footer border-0 bg-light p-3 d-flex justify-content-between">
                <button type="button" class="btn btn-light text-muted fw-medium rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="submitConsumptionApproval()">
                    <i class="fas fa-check-circle me-2"></i>Acknowledge & Confirm
                </button>
            </div>
            <!-- Hidden inputs -->
            <input type="hidden" id="consumption_project_id">
            <input type="hidden" id="lunch_qty">
            <input type="hidden" id="snack_qty">
        </div>
    </div>
</div>

<script>
// userRole already defined at top of file
</script>
<script>
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const openProjectId = urlParams.get('open_project_id');
    const openTab = urlParams.get('open_tab');
    
    if (openProjectId) {
        // Wait for project_detail.js to load if needed, but since it's included above, it should be fine.
        // Also ensure loadProjectDetail is defined.
        if (typeof loadProjectDetail === 'function') {
            loadProjectDetail(openProjectId, openTab);
        }
    }
});
</script>
<script>
    // Cancel Project Handler for List View
    $(document).on('click', '.btn-cancel-project', function(e) {
        e.preventDefault();
        const projectId = $(this).data('id');
        
        Swal.fire({
            title: 'Cancel Project?',
            text: "Are you sure you want to cancel this project? This action cannot be undone easily.",
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Enter cancellation reason...',
            inputAttributes: {
                'aria-label': 'Cancellation reason'
            },
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Cancel Project',
            preConfirm: (reason) => {
                if (!reason) {
                    Swal.showValidationMessage('Reason is required');
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Reuse the submitStatusUpdate function if available in project_detail.js
                // But since we are in list view, we might not have it loaded globally or it might be scoped.
                // project_detail.js IS included in list.php line 454.
                // Let's check if submitStatusUpdate is global.
                // Based on previous file reads, it seemed to be.
                
                // If not, we implement direct AJAX here to be safe.
                
                Swal.fire({
                    title: 'Processing...',
                    didOpen: () => Swal.showLoading(),
                    allowOutsideClick: false
                });

                $.ajax({
                    url: 'index.php?page=update_project_status',
                    type: 'POST',
                    data: {
                        project_id: projectId,
                        status: 'cancelled',
                        reason: result.value
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire(
                                'Cancelled!',
                                'Project has been cancelled.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to cancel project: ' + (response.message || 'Unknown error'),
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error updating status:", error);
                        Swal.fire(
                            'Error!',
                            'An error occurred while connecting to the server.',
                            'error'
                        );
                    }
                });
            }
        });
    });

const currentUserRole = '<?php echo $_SESSION['role'] ?? ''; ?>';

function openConsumptionModal(btn) {
    var $btn = $(btn);
    var projectId = $btn.data('project-id');
    var lunch = $btn.data('lunch');
    var snack = $btn.data('snack');
    var lunchQty = $btn.data('lunch-qty');
    var snackQty = $btn.data('snack-qty');
    var lunchBudgetRaw = $btn.attr('data-lunch-budget') || '0';
    var snackBudgetRaw = $btn.attr('data-snack-budget') || '0';

    var parseBudget = function(val) {
        if (!val) return 0;
        val = String(val);
        // Try standard number parsing first (handles 50000.00 correctly)
        var num = Number(val);
        if (!isNaN(num)) return parseInt(num);
        // If NaN, maybe it has formatting chars (e.g. 50.000 or Rp 50.000)
        return parseInt(val.replace(/[^0-9]/g, '')) || 0;
    };

    var lunchBudget = parseBudget(lunchBudgetRaw);
    var snackBudget = parseBudget(snackBudgetRaw);
    var lunchItemsRaw = $btn.data('lunch-items');
    var snackItemsRaw = $btn.data('snack-items');

    $('#consumption_project_id').val(projectId);
    $('#lunch_qty').val(lunchQty);
    $('#snack_qty').val(snackQty);

    // Set data for Lark template
    $('#approveConsumptionModal').data('project-name', $btn.data('project-name'));
    $('#approveConsumptionModal').data('mcu-date', $btn.data('mcu-date'));
    $('#approveConsumptionModal').data('sales-name', $btn.data('sales-name'));
    $('#approveConsumptionModal').data('has-lunch', lunch);
    $('#approveConsumptionModal').data('has-snack', snack);
    
    // Parse Items
    let parsedLunchItems = [];
    let parsedSnackItems = [];
    let totalLunchCost = 0;
    let totalSnackCost = 0;
    
    if (typeof lunchItemsRaw === 'object') {
        parsedLunchItems = lunchItemsRaw;
    } else if (typeof lunchItemsRaw === 'string') {
        try { parsedLunchItems = JSON.parse(lunchItemsRaw); } catch(e) { console.error('Error parsing lunch items', e); }
    }
    
    if (typeof snackItemsRaw === 'object') {
        parsedSnackItems = snackItemsRaw;
    } else if (typeof snackItemsRaw === 'string') {
        try { parsedSnackItems = JSON.parse(snackItemsRaw); } catch(e) { console.error('Error parsing snack items', e); }
    }

    // Helper to calculate total from items or budget
    const calculateCost = (items, budget, assignedQty) => {
        let itemsSum = 0;
        let maxQty = 0;
        
        if (items && items.length > 0) {
            items.forEach(item => {
                let t = parseInt(item.total) || 0;
                let q = parseInt(item.qty) || 0;
                let p = parseInt(item.price) || 0;
                
                if (q > maxQty) maxQty = q;
                
                if (t === 0 && q > 0 && p > 0) {
                    t = q * p;
                }
                itemsSum += t;
            });
        }
        
        // Use items sum if available, otherwise use budget * qty
        if (itemsSum > 0) {
            return itemsSum;
        } else if (budget > 0) {
            // Use assignedQty if > 0, else maxQty from items
            let q = parseInt(assignedQty) || 0;
            if (q === 0) q = maxQty;
            return budget * q;
        }
        return 0;
    };

    totalLunchCost = calculateCost(parsedLunchItems, lunchBudget, lunchQty);
    totalSnackCost = calculateCost(parsedSnackItems, snackBudget, snackQty);
    
    const grandTotal = totalLunchCost + totalSnackCost;

    // --- Build HTML Content ---
    let htmlContent = '';

    // 1. Summary Cards
    htmlContent += '<div class="row g-3 mb-4">';
    
    // Lunch Card
    htmlContent += '<div class="col-6">';
    htmlContent += '<div class="p-3 border rounded-4 bg-light text-center h-100 position-relative overflow-hidden">';
    if (lunch === 'Ya') {
        htmlContent += '<div class="position-absolute top-0 end-0 p-2 opacity-10"><i class="fas fa-utensils fa-3x text-warning"></i></div>';
        htmlContent += '<div class="small text-uppercase text-muted fw-bold mb-1 mt-3">Makan Siang</div>';
        htmlContent += `<h4 class="fw-bold mb-0 text-dark">Rp ${totalLunchCost.toLocaleString('id-ID')}</h4>`;
        if (lunchBudget > 0) {
             htmlContent += `<small class="text-muted d-block">Budget: Rp ${lunchBudget.toLocaleString('id-ID')}/pax</small>`;
        }
    } else {
        htmlContent += '<div class="text-muted opacity-50 pt-2"><i class="fas fa-utensils fa-2x mb-2"></i><br>Tidak Ada Request</div>';
    }
    htmlContent += '</div></div>';

    // Snack Card
    htmlContent += '<div class="col-6">';
    htmlContent += '<div class="p-3 border rounded-4 bg-light text-center h-100 position-relative overflow-hidden">';
    if (snack === 'Ya') {
        htmlContent += '<div class="position-absolute top-0 end-0 p-2 opacity-10"><i class="fas fa-cookie-bite fa-3x text-info"></i></div>';
        htmlContent += '<div class="small text-uppercase text-muted fw-bold mb-1 mt-3">Snack</div>';
        htmlContent += `<h4 class="fw-bold mb-0 text-dark">Rp ${totalSnackCost.toLocaleString('id-ID')}</h4>`;
        if (snackBudget > 0) {
             htmlContent += `<small class="text-muted d-block">Budget: Rp ${snackBudget.toLocaleString('id-ID')}/pax</small>`;
        }
    } else {
        htmlContent += '<div class="text-muted opacity-50 pt-2"><i class="fas fa-cookie-bite fa-2x mb-2"></i><br>Tidak Ada Request</div>';
    }
    htmlContent += '</div></div>';
    
    htmlContent += '</div>'; // End Row

    // 2. Detailed List
    if (lunch === 'Ya' || snack === 'Ya') {
        htmlContent += '<div class="card border-0 shadow-sm rounded-4 overflow-hidden">';
        htmlContent += '<div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-bold"><i class="fas fa-list-ul me-2 text-primary"></i>Rincian Item</h6></div>';
        htmlContent += '<div class="card-body p-0"><div class="list-group list-group-flush">';

        // Helper to render items
        const renderItems = (items, type, budget) => {
            let listHtml = '';
            let itemsSum = 0; // Check if we need to show budget fallback row
            
            if (items && items.length > 0) {
                items.forEach(item => {
                    let itemName = typeof item === 'object' ? (item.item || '-') : item;
                    let itemQty = (typeof item === 'object' && item.qty) ? item.qty : '';
                    let itemPrice = (typeof item === 'object' && item.price) ? parseInt(item.price) : 0;
                    let itemTotal = (typeof item === 'object' && item.total) ? parseInt(item.total) : 0;
                    
                    if (itemTotal === 0 && itemQty && itemPrice) itemTotal = itemQty * itemPrice;
                    itemsSum += itemTotal;
                    
                    let icon = type === 'lunch' ? 'fa-utensils text-warning' : 'fa-cookie-bite text-info';

                    listHtml += '<div class="list-group-item p-3 border-0 border-bottom">';
                    listHtml += '<div class="d-flex justify-content-between align-items-center">';
                    
                    // Left side: Icon + Name
                    listHtml += '<div class="d-flex align-items-center">';
                    listHtml += `<div class="me-3 text-center" style="width:24px;"><i class="fas ${icon}"></i></div>`;
                    listHtml += '<div>';
                    listHtml += `<div class="fw-bold text-dark">${itemName}</div>`;
                    if (itemPrice > 0) {
                        listHtml += `<small class="text-muted">@ Rp ${itemPrice.toLocaleString('id-ID')}</small>`;
                    }
                    listHtml += '</div>';
                    listHtml += '</div>'; // End Left

                    // Right side: Qty + Total
                    listHtml += '<div class="text-end">';
                    if (itemQty) {
                        listHtml += `<div class="small text-muted mb-1">${itemQty} pax</div>`;
                    }
                    if (itemTotal > 0) {
                        listHtml += `<div class="fw-bold text-dark">Rp ${itemTotal.toLocaleString('id-ID')}</div>`;
                    } else {
                         // Removed dash as requested
                    }
                    listHtml += '</div>';
                    
                    listHtml += '</div></div>';
                });
            }
            
            // If items have no cost but we have a budget, show a "Budget Estimate" row
            if (itemsSum === 0 && budget > 0) {
                let icon = type === 'lunch' ? 'fa-utensils text-warning' : 'fa-cookie-bite text-info';
                listHtml += '<div class="list-group-item p-3 border-0 border-bottom bg-light">';
                listHtml += '<div class="d-flex justify-content-between align-items-center">';
                listHtml += '<div class="d-flex align-items-center">';
                listHtml += `<div class="me-3 text-center" style="width:24px;"><i class="fas fa-coins text-secondary"></i></div>`;
                listHtml += '<div><div class="fw-bold text-dark fst-italic">Estimasi dari Budget</div></div>';
                listHtml += '</div>';
                
                // Calculate estimated total
                let maxQty = 0;
                if (items) items.forEach(i => { if(parseInt(i.qty) > maxQty) maxQty = parseInt(i.qty); });
                let estTotal = budget * maxQty;
                
                listHtml += `<div class="text-end fw-bold text-dark">Rp ${estTotal.toLocaleString('id-ID')}</div>`;
                listHtml += '</div></div>';
            }
            
            return listHtml;
        };

        if (lunch === 'Ya') {
             htmlContent += renderItems(parsedLunchItems, 'lunch', lunchBudget);
        }
        if (snack === 'Ya') {
             htmlContent += renderItems(parsedSnackItems, 'snack', snackBudget);
        }

        htmlContent += '</div></div>'; // End Card Body & List Group

        // Footer Total
        htmlContent += '<div class="card-footer bg-light border-top p-3">';
        htmlContent += '<div class="d-flex justify-content-between align-items-center">';
        htmlContent += '<span class="fw-bold text-muted text-uppercase small">Total Estimasi Biaya</span>';
        htmlContent += `<span class="h4 mb-0 fw-bold text-primary">Rp ${grandTotal.toLocaleString('id-ID')}</span>`;
        htmlContent += '</div></div>';
        
        htmlContent += '</div>'; // End Card
    } else {
        htmlContent += '<div class="text-center py-4 text-muted">Tidak ada data konsumsi untuk ditampilkan.</div>';
    }

    // Inject Content
    $('#consumption_modal_body').html(htmlContent);
    
    var modal = new bootstrap.Modal(document.getElementById('approveConsumptionModal'));
    modal.show();
}

function submitConsumptionApproval() {
    const projectId = $('#consumption_project_id').val();
    // We use the quantities passed initially (hidden inputs) or just proceed since it's FYI
    // But backend expects lunch_qty/snack_qty
    const lunchQty = $('#lunch_qty').val();
    const snackQty = $('#snack_qty').val();
    
    // Logic check: if Lunch is 'Ya' (visible) but no quantity/items?
    // Since it's FYI, we assume the data from Sales is what we are approving.
    // Just pass what we have.

    Swal.fire({
        title: 'Confirm Consumption?',
        text: "Are you sure you want to confirm this consumption request?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Confirm'
    }).then((result) => {
        if (result.isConfirmed) {
            // Hide modal first
            var modalEl = document.getElementById('approveConsumptionModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            Swal.fire({
                title: 'Processing...',
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: 'index.php?page=approve_consumption_ajax',
                method: 'POST',
                data: { 
                    project_id: projectId,
                    lunch_qty: lunchQty,
                    snack_qty: snackQty
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Confirmed!', 'Consumption status updated.', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Failed to update', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Request failed', 'error');
                }
            });
        }
    });
}




$(document).ready(function() {
    // Assign/Request Vendor Logic with prompt
    $('.btn-assign-vendor').click(function() {
        var projectId = $(this).data('id');
        var projectName = $(this).data('project-name');
        // Robust check for consumption
        var lunchVal = String($(this).attr('data-lunch') || '').trim().toLowerCase();
        var snackVal = String($(this).attr('data-snack') || '').trim().toLowerCase();
        var hasConsumption = (lunchVal === 'ya' || snackVal === 'ya');

        var mcuDate = $(this).attr('data-mcu-date') || '-';
        var salesName = $(this).attr('data-sales-name') || '-';

        $('#vendor_project_id').val(projectId);
        $('#assignVendorModal').data('project-name', projectName);
        $('#assignVendorModal').data('has-lunch', lunchVal === 'ya');
        $('#assignVendorModal').data('has-snack', snackVal === 'ya');
        $('#assignVendorModal').data('mcu-date', mcuDate);
        $('#assignVendorModal').data('sales-name', salesName);
        
        resetVendorLark();

        console.log('Project:', projectName, 'Lunch:', lunchVal, 'Snack:', snackVal);

        // Always show Lark container for Vendor Requirements context
        $('#vendorLarkContainer').removeClass('d-none');
        
        // Initially disable Save Changes until Lark is clicked
        $('.btn-save-vendor').prop('disabled', true).css('opacity', '0.6');

        Swal.fire({
            title: 'Butuh vendor untuk project ini?',
            showDenyButton: true,
            confirmButtonText: 'Ya, butuh',
            denyButtonText: 'Tidak perlu',
        }).then((result) => {
            if (result.isConfirmed) {
                var modal = new bootstrap.Modal(document.getElementById('assignVendorModal'));
                $('#vendorTable tbody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
                modal.show();
                $.ajax({
                    url: 'index.php?page=get_vendor_allocations_ajax',
                    data: { project_id: projectId },
                    success: function(response) {
                        var res = JSON.parse(response);
                        $('#vendorTable tbody').empty();
                        if(res.data && res.data.length > 0) {
                            res.data.forEach(function(item) {
                                addVendorRow(item.exam_type, item.participant_count, item.notes);
                            });
                        } else {
                            addVendorRow();
                        }
                    }
                });
            } else if (result.isDenied) {
                Swal.fire({
                    title: 'Set tanpa vendor?',
                    text: 'Status akan ditandai selesai tahap vendor.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjut',
                    cancelButtonText: 'Batal'
                }).then((res2) => {
                    if (res2.isConfirmed) {
                        $.ajax({
                            url: 'index.php?page=mark_no_vendor_needed_ajax',
                            method: 'POST',
                            data: { project_id: projectId },
                            success: function(response) {
                                try {
                                    var r = JSON.parse(response);
                                    if (r.status === 'success') {
                                        Swal.fire('Tersimpan', 'Project ditandai tanpa vendor', 'success').then(() => location.reload());
                                    } else {
                                        Swal.fire('Error', r.message || 'Gagal menyimpan', 'error');
                                    }
                                } catch(e) {
                                    Swal.fire('Error', 'Response tidak valid', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Server error', 'error');
                            }
                        });
                    }
                });
            }
        });
    });

    // Assign Korlap Logic
    $('.btn-assign-korlap').click(function() {
        var projectId = $(this).data('id');
        $('#korlap_project_id').val(projectId);
        var modal = new bootstrap.Modal(document.getElementById('assignKorlapModal'));
        
        // Load Korlaps
        $.ajax({
            url: 'index.php?page=get_korlaps_ajax',
            data: { project_id: projectId },
            success: function(response) {
                var res = JSON.parse(response);
                $('#korlap_select').empty();
                if(res.data && res.data.length > 0) {
                    $('#korlap_select').append('<option value="">Select Korlap</option>');
                    res.data.forEach(function(item) {
                        let disabled = !item.is_available ? 'disabled' : '';
                        let label = item.name;
                        if (!item.is_available) {
                            label += ` (${item.conflict_info})`;
                        }
                        $('#korlap_select').append(`<option value="${item.korlap_id}" ${disabled}>${label}</option>`);
                    });
                } else {
                    $('#korlap_select').append('<option value="">No Korlaps found</option>');
                }
                modal.show();
            }
        });
    });
});

$('.btn-procurement-assign').click(function() {
    var projectId = $(this).data('id');
    $('#procurement_project_id').val(projectId);
    var modal = new bootstrap.Modal(document.getElementById('assignVendorProcurementModal'));
    
    // Clear table
    $('#procurementVendorTable tbody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
    modal.show();

    // Fetch All Vendors first
    $.ajax({
        url: 'index.php?page=get_all_vendors_ajax',
        method: 'GET',
        dataType: 'json',
        success: function(vendorRes) {
            if (vendorRes.status === 'success') {
                const allVendors = vendorRes.data;
                
                // Fetch existing requirements
                $.ajax({
                    url: 'index.php?page=get_vendor_allocations_ajax',
                    data: { project_id: projectId },
                    success: function(response) {
                        var res = JSON.parse(response);
                        $('#procurementVendorTable tbody').empty();
                        
                        if(res.data && res.data.length > 0) {
                            res.data.forEach(function(item) {
                                var vendorVal = item.assigned_vendor_name || '';
                                
                                // Categorize vendors: Recommended (match service) vs Others
                                let recommendedVendors = [];
                                let otherVendors = [];
                                
                                let reqType = item.exam_type.toLowerCase().trim();

                                allVendors.forEach(v => {
                                    let isMatch = false;
                                    if (v.services) {
                                        let services = v.services.toLowerCase().split(',').map(s => s.trim());
                                        if (services.includes(reqType) || v.services.toLowerCase().includes(reqType)) {
                                            isMatch = true;
                                        }
                                    }
                                    
                                    if (isMatch) {
                                        recommendedVendors.push(v);
                                    } else {
                                        otherVendors.push(v);
                                    }
                                });
                                
                                // Combine: Recommended first
                                let displayVendors = [...recommendedVendors, ...otherVendors];
                                
                                let isManual = false;
                                let inList = displayVendors.some(v => v.vendor_name === vendorVal);
                                if (vendorVal && !inList) {
                                    isManual = true;
                                }

                                let vendorInput = `<div class="vendor-wrapper">`;
                                // Select
                                vendorInput += `<select class="form-select form-select-sm vendor-select ${isManual ? 'd-none' : ''}">`;
                                vendorInput += `<option value="">-- Select Vendor --</option>`;
                                
                                if (recommendedVendors.length > 0) {
                                    vendorInput += `<optgroup label="Recommended">`;
                                    recommendedVendors.forEach(v => {
                                        let sel = (v.vendor_name === vendorVal) ? 'selected' : '';
                                        vendorInput += `<option value="${v.vendor_name}" ${sel}>${v.vendor_name}</option>`;
                                    });
                                    vendorInput += `</optgroup>`;
                                }
                                
                                if (otherVendors.length > 0) {
                                    vendorInput += `<optgroup label="All Vendors">`;
                                    otherVendors.forEach(v => {
                                        let sel = (v.vendor_name === vendorVal) ? 'selected' : '';
                                        vendorInput += `<option value="${v.vendor_name}" ${sel}>${v.vendor_name}</option>`;
                                    });
                                    vendorInput += `</optgroup>`;
                                }
                                
                                vendorInput += `<option value="__manual__" ${isManual ? 'selected' : ''}>+ Input Manual (Type Name)</option>`;
                                vendorInput += `</select>`;

                                // Manual Input
                                vendorInput += `<div class="input-group input-group-sm vendor-manual-group ${isManual ? '' : 'd-none'}">`;
                                vendorInput += `<input type="text" class="form-control vendor-input-text" value="${isManual ? vendorVal : ''}" placeholder="Type vendor name...">`;
                                vendorInput += `<button class="btn btn-outline-secondary btn-cancel-manual" type="button" title="Back to List"><i class="fas fa-times"></i></button>`;
                                vendorInput += `</div></div>`;
                                
                                var row = `
                                    <tr data-id="${item.id}">
                                        <td>${item.exam_type}</td>
                                        <td>${item.participant_count}</td>
                                        <td>${item.notes || '-'}</td>
                                        <td>
                                            ${vendorInput}
                                        </td>
                                    </tr>
                                `;
                                $('#procurementVendorTable tbody').append(row);
                            });
                        } else {
                             $('#procurementVendorTable tbody').html('<tr><td colspan="4" class="text-center">No requirements found.</td></tr>');
                        }
                    }
                });
            } else {
                $('#procurementVendorTable tbody').html('<tr><td colspan="4" class="text-center text-danger">Failed to load vendors.</td></tr>');
            }
        },
        error: function() {
            $('#procurementVendorTable tbody').html('<tr><td colspan="4" class="text-center text-danger">Error loading vendors.</td></tr>');
        }
    });
});



function saveProcurementVendor() {
    var projectId = $('#procurement_project_id').val();
    var fulfillments = [];
    
    $('#procurementVendorTable tbody tr').each(function() {
        var id = $(this).data('id');
        var container = $(this).find('.vendor-wrapper');
        var vendorName = '';
        
        if (container.length > 0) {
            var isManual = !container.find('.vendor-manual-group').hasClass('d-none');
            if (isManual) {
                vendorName = container.find('.vendor-input-text').val();
            } else {
                vendorName = container.find('.vendor-select').val();
                if (vendorName === '__manual__') vendorName = '';
            }
        } else {
             vendorName = $(this).find('.vendor-name-input').val();
        }

        if(id) {
            fulfillments.push({
                id: id,
                assigned_vendor_name: vendorName
            });
        }
    });

    if(fulfillments.length === 0) return;

    Swal.fire({
        title: 'Saving...',
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: 'index.php?page=assign_vendor_procurement_ajax',
        method: 'POST',
        data: {
            project_id: projectId,
            fulfillments: fulfillments
        },
        success: function(response) {
             var res = JSON.parse(response);
             if(res.status === 'success') {
                 Swal.fire('Success', 'Vendors assigned successfully', 'success').then(() => location.reload());
             } else {
                 Swal.fire('Error', res.message || 'Failed to save', 'error');
             }
        },
        error: function() {
             Swal.fire('Error', 'Server error', 'error');
        }
    });
}

function addVendorRow(type = '', count = '', notes = '') {
    const examOptions = [
        'Rontgen', 'EKG', 'Audiometri', 'Spirometri', 
        'USG Abdomen', 'USG Mammae', 'Papsmear', 
        'Autorefraksi', 'Treadmill', 'Other'
    ];
    
    let selectHtml = '<select class="form-select form-select-sm exam_type_select" onchange="toggleOtherInput(this)">';
    selectHtml += '<option value="">Select Exam...</option>';
    
    let isOther = type && !examOptions.includes(type) && type !== '';
    let selectValue = isOther ? 'Other' : type;
    
    examOptions.forEach(opt => {
        let selected = (opt === selectValue) ? 'selected' : '';
        selectHtml += `<option value="${opt}" ${selected}>${opt}</option>`;
    });
    selectHtml += '</select>';
    
    let displayOther = isOther ? 'block' : 'none';
    let otherValue = isOther ? type : '';

    var html = `
        <tr>
            <td>
                ${selectHtml}
                <input type="text" class="form-control form-control-sm mt-1 exam_type_other" value="${otherValue}" placeholder="Specify other..." style="display:${displayOther}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm participant_count" value="${count}" placeholder="Qty">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm notes" value="${notes}" placeholder="Notes">
            </td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="$(this).closest('tr').remove()"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `;
    $('#vendorTable tbody').append(html);
}

function toggleOtherInput(selectEl) {
    var otherInput = $(selectEl).siblings('.exam_type_other');
    if($(selectEl).val() === 'Other') {
        otherInput.show();
        otherInput.focus();
    } else {
        otherInput.hide();
        otherInput.val('');
    }
}

// Global event listeners for Vendor Assign
$(document).on('change', '.vendor-select', function() {
    if ($(this).val() === '__manual__') {
        let wrapper = $(this).closest('.vendor-wrapper');
        $(this).addClass('d-none');
        wrapper.find('.vendor-manual-group').removeClass('d-none');
        wrapper.find('.vendor-input-text').focus();
    }
});

$(document).on('click', '.btn-cancel-manual', function() {
    let wrapper = $(this).closest('.vendor-wrapper');
    wrapper.find('.vendor-manual-group').addClass('d-none');
    let select = wrapper.find('.vendor-select');
    select.removeClass('d-none');
    select.val(''); // Reset to empty or first option
});

function markVendorLarkClicked() {
    $('#vendor_lark_clicked').val('1');
    $('.btn-save-vendor').prop('disabled', false).css('opacity', '1');
}

function resetVendorLark() {
    $('#vendor_lark_clicked').val('0');
    // Initial state: disable if needed, but we do it in the click handler too
}

function saveVendorAllocations() {
    var projectId = $('#vendor_project_id').val();
    
    // Lark Validation
    var hasLunch = $('#assignVendorModal').data('has-lunch');
    var hasSnack = $('#assignVendorModal').data('has-snack');
    var larkClicked = $('#vendor_lark_clicked').val() === '1';

    var allocations = [];
    $('#vendorTable tbody tr').each(function() {
        var selectVal = $(this).find('.exam_type_select').val();
        var otherVal = $(this).find('.exam_type_other').val();
        var type = (selectVal === 'Other') ? otherVal : selectVal;
        var count = $(this).find('.participant_count').val();
        var notes = $(this).find('.notes').val();
        
        if(type && count) {
            allocations.push({
                exam_type: type,
                participant_count: count,
                notes: notes
            });
        }
    });

    // Lark Validation: Required if consumption exists OR if vendor items are added
    if ((hasLunch || hasSnack || allocations.length > 0) && !larkClicked) {
        Swal.fire({
            icon: 'warning',
            title: 'Koordinasi Lark Diperlukan',
            text: 'Silakan klik button "Open Lark" terlebih dahulu untuk koordinasi kebutuhan vendor / konsumsi.',
            confirmButtonColor: '#204EAB'
        });
        return false;
    }

    Swal.fire({
        title: 'Saving...',
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: 'index.php?page=assign_vendor_ajax',
        method: 'POST',
        data: {
            project_id: projectId,
            allocations: allocations
        },
        success: function(response) {
            var res = JSON.parse(response);
            if(res.status === 'success') {
                Swal.fire('Success', 'Requirements saved!', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
}

function saveKorlapAssignment() {
    var projectId = $('#korlap_project_id').val();
    var korlapId = $('#korlap_select').val();
    
    if(!korlapId) {
        Swal.fire('Warning', 'Please select a Korlap', 'warning');
        return;
    }

    Swal.fire({
        title: 'Saving...',
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: 'index.php?page=assign_korlap_ajax',
        method: 'POST',
        data: {
            project_id: projectId,
            korlap_id: korlapId
        },
        success: function(response) {
            var res = JSON.parse(response);
            if(res.status === 'success') {
                Swal.fire('Success', 'Korlap assigned!', 'success').then(() => {
                    location.reload(); // Refresh to update table and hide button
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
}

function editKorlap(projectId) {
    // Open the same modal but triggered from detail
    $('#korlap_project_id').val(projectId);
    var modal = new bootstrap.Modal(document.getElementById('assignKorlapModal'));
    
    // Load Korlaps
    $.ajax({
        url: 'index.php?page=get_korlaps_ajax',
        data: { project_id: projectId },
        success: function(response) {
            var res = JSON.parse(response);
            $('#korlap_select').empty();
            if(res.data && res.data.length > 0) {
                $('#korlap_select').append('<option value="">Select Korlap</option>');
                res.data.forEach(function(item) {
                    let disabled = !item.is_available ? 'disabled' : '';
                    let label = item.name;
                    if (!item.is_available) {
                        label += ` (${item.conflict_info})`;
                    }
                    $('#korlap_select').append(`<option value="${item.korlap_id}" ${disabled}>${label}</option>`);
                });
                
                // If this project already has a korlap, select it.
                // But wait, the list might not indicate WHICH one is current if it marks itself busy?
                // The get_korlaps_ajax logic excludes CURRENT project from conflict check, so it should be available.
                // We need to fetch current korlap ID to pre-select. 
                // We can get it from the detail view logic or fetch again.
                // For simplicity, let's fetch project detail again or assume user selects new.
                // Ideally, select current.
                $.get('index.php?page=get_project_detail_ajax&id=' + projectId, function(detailResp) {
                    var d = JSON.parse(detailResp);
                    if(d && d.korlap_id) {
                         $('#korlap_select').val(d.korlap_id);
                     }
                 });

            } else {
                $('#korlap_select').append('<option value="">No Korlaps found</option>');
            }
            modal.show();
        }
    });
}

function openSphModal(projectId) {
    document.getElementById('sph_project_id').value = projectId;
    var myModal = new bootstrap.Modal(document.getElementById('sphModal'));
    myModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const openProjectId = urlParams.get('open_project_id');
    const openTab = urlParams.get('open_tab');
    
    if (openProjectId) {
        // Use timeout to ensure DOM is fully ready and other scripts initialized
        setTimeout(() => {
            loadProjectDetail(openProjectId, openTab || 'details');
        }, 500);
    }
});
</script>

<!-- SPH Link Modal -->
<div class="modal fade" id="sphModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set SPH Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=upload_sph" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="project_id" id="sph_project_id">
                    <div class="mb-3">
                        <label for="sph_file" class="form-label">Google Drive Link</label>
                        <input class="form-control" type="url" id="sph_file" name="sph_file" placeholder="https://drive.google.com/..." required pattern="https?://.+">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Check for success messages from URL
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    
    if (msg === 'project_created') {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Project created successfully!',
            timer: 2000,
            showConfirmButton: false
        });
        // Clean URL
        const newUrl = window.location.pathname + "?page=all_projects";
        window.history.replaceState({}, document.title, newUrl);
    } else if (msg === 'project_updated') {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Project updated successfully!',
            timer: 2000,
            showConfirmButton: false
        });
        // Clean URL
        const newUrl = window.location.pathname + "?page=projects_list";
        window.history.replaceState({}, document.title, newUrl);
    } else if (msg === 'ba_uploaded') {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Berita Acara uploaded successfully!',
            timer: 2000,
            showConfirmButton: false
        });
        // Clean URL but keep open params
        const openId = urlParams.get('open_project_id');
        const openTab = urlParams.get('open_tab');
        let newUrl = window.location.pathname + "?page=all_projects";
        if(openId) newUrl += "&open_project_id=" + openId;
        if(openTab) newUrl += "&open_tab=" + openTab;
        
        window.history.replaceState({}, document.title, newUrl);
    } else if (msg === 'ba_cancelled') {
        Swal.fire({
            icon: 'success',
            title: 'Cancelled',
            text: 'Berita Acara date cancelled.',
            timer: 2000,
            showConfirmButton: false
        });
        // Clean URL but keep open params
        const openId = urlParams.get('open_project_id');
        const openTab = urlParams.get('open_tab');
        let newUrl = window.location.pathname + "?page=all_projects";
        if(openId) newUrl += "&open_project_id=" + openId;
        if(openTab) newUrl += "&open_tab=" + openTab;
        
        window.history.replaceState({}, document.title, newUrl);
    }
});
</script>

<?php include '../views/layouts/footer.php'; ?>
