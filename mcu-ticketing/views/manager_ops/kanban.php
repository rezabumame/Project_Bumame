<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Project Kanban Board</h1>
            <p class="page-header-subtitle">Track project progress and operational status.</p>
            <div class="mt-2">
                <?php if($_SESSION['role'] == 'manager_ops'): ?>
                    <span class="badge bg-primary">Manager Ops View</span>
                <?php elseif($_SESSION['role'] == 'admin_ops'): ?>
                    <span class="badge bg-danger">SPV Ops/Admin Ops View (Read Only)</span>
                <?php elseif($_SESSION['role'] == 'admin_sales'): ?>
                    <span class="badge bg-secondary">Admin Sales View (Read Only)</span>
                <?php elseif($_SESSION['role'] == 'superadmin'): ?>
                    <span class="badge bg-info">Superadmin View</span>
                <?php elseif($_SESSION['role'] == 'ceo'): ?>
                    <span class="badge bg-dark">CEO View (Read Only)</span>
                <?php elseif(in_array($_SESSION['role'], ['sales', 'manager_sales', 'sales_support_supervisor', 'sales_performance_manager'])): ?>
                    <span class="badge bg-info">Sales View (Read Only)</span>
                <?php elseif(in_array($_SESSION['role'], ['surat_hasil'])): ?>
                    <span class="badge bg-primary">Medical Result View (Read Only)</span>
                <?php elseif($_SESSION['role'] == 'head_ops'): ?>
                    <span class="badge bg-info">Head Ops View</span>
                <?php else: ?>
                    <span class="badge bg-info"><?php echo ucwords(str_replace('_', ' ', $_SESSION['role'])); ?> View</span>
                <?php endif; ?>
            </div>
        </div>
        <a href="index.php?page=all_projects" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-list me-2"></i> View All Projects
        </a>
    </div>



<div class="kanban-board">
    <!-- Column 1: Need Approval -->
    <div class="kanban-column">
        <div class="kanban-column-header">
            <span>
                <?php 
                    $fullFlowRoles = ['admin_ops', 'admin_sales', 'superadmin', 'sales', 'manager_sales', 'sales_support_supervisor', 'sales_performance_manager', 'surat_hasil', 'dw_tim_hasil'];
                    if (in_array($_SESSION['role'], $fullFlowRoles)) {
                        echo '<i class="fas fa-clock me-2"></i>Need Manager Approval';
                    } elseif ($_SESSION['role'] == 'manager_ops') {
                        echo '<i class="fas fa-clipboard-check me-2"></i>Need My Approval';
                    } else {
                        echo '<i class="fas fa-clipboard-check me-2"></i>Need My Approval';
                    }
                ?>
            </span>
            <span class="badge bg-white text-dark shadow-sm"><?php echo count($need_approval); ?></span>
        </div>
        <div id="col-need-approval" class="kanban-items" data-status="<?php echo (in_array($_SESSION['role'], ['manager_ops', 'admin_ops', 'admin_sales', 'superadmin', 'sales', 'manager_sales', 'sales_support_supervisor', 'sales_performance_manager', 'surat_hasil'])) ? 'need_approval_manager' : 'need_approval_manager'; ?>">
            <?php foreach($need_approval as $p): 
                $dates = DateHelper::parseDateArray($p['tanggal_mcu']);
                $is_urgent = false;
                
                // Urgent / SLA Logic
                if (in_array($_SESSION['role'], ['manager_ops', 'head_ops', 'admin_ops', 'superadmin'])) {
                    // 1. Existing H-1 Logic
                    $target_date = !empty($dates) ? $dates[0] : null;

                    if ($target_date) {
                        $working_days = DateHelper::countWorkingDays(date('Y-m-d'), $target_date, $holidays ?? []);
                        if ($working_days <= 1) { 
                            $is_urgent = true;
                        }
                    }
                    
                    // 2. SLA Breach Logic
                    if (!$is_urgent) {
                        $start_time = 0;
                        if ($p['status_project'] == 'need_approval_manager') {
                            $start_time = strtotime($p['created_at']);
                        } elseif ($p['status_project'] == 'need_approval_head') {
                            $start_time = !empty($p['approved_date_manager']) ? strtotime($p['approved_date_manager']) : strtotime($p['created_at']);
                        }
                        
                        if ($start_time > 0) {
                            $start_date = date('Y-m-d', $start_time);
                            $today = date('Y-m-d');
                            
                            if ($start_date != $today) {
                                $working_days_elapsed = DateHelper::countWorkingDays($start_date, $today, $holidays ?? []);
                                if (isset($approval_sla_days) && $working_days_elapsed > $approval_sla_days) {
                                    $is_urgent = true;
                                }
                            }
                        }
                    }
                }
            ?>
                <div class="kanban-card <?php echo $is_urgent ? 'border-danger border-2' : ''; ?>" data-id="<?php echo $p['project_id']; ?>" data-is-urgent="<?php echo $is_urgent ? '1' : '0'; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-light text-muted border shadow-sm">#<?php echo $p['project_id']; ?></span>
                        <?php if($is_urgent): ?>
                            <span class="badge bg-danger animate__animated animate__pulse animate__infinite urgent-badge">URGENT</span>
                        <?php endif; ?>
                        <small class="text-muted fw-bold" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i><?php 
                            if (!empty($dates)) {
                                echo date('d M', strtotime($dates[0]));
                                if(count($dates) > 1) echo ' (' . count($dates) . ' hari)';
                            } else {
                                echo date('d M', strtotime($p['created_at']));
                            }
                        ?></small>
                    </div>
                    
                    <h6 class="mb-2" title="<?php echo htmlspecialchars($p['nama_project']); ?>">
                        <?php echo htmlspecialchars($p['nama_project']); ?>
                    </h6>
                    
                    <div class="kanban-meta-row">
                        <i class="far fa-building"></i> 
                        <span class="text-truncate"><?php echo htmlspecialchars($p['company_name']); ?></span>
                    </div>
                    
                    <div class="kanban-meta-row">
                        <i class="fas fa-users"></i> 
                        <span><?php echo htmlspecialchars($p['total_peserta'] ?: '-'); ?> Pax</span>
                    </div>
                    
                    <div class="kanban-footer">
                        <?php if(empty($p['sph_file'])): ?>
                            <span class="badge bg-danger me-1" title="SPH Not Set">No SPH Link</span>
                        <?php endif; ?>
                        <?php if(!empty($p['sales_name'])): ?>
                            <div class="sales-badge" title="Sales: <?php echo htmlspecialchars($p['sales_name']); ?>">
                                <i class="fas fa-user-tie"></i> 
                                <span class="text-truncate" style="max-width: 120px;"><?php echo htmlspecialchars($p['sales_name']); ?></span>
                            </div>
                        <?php else: ?>
                             <span class="text-muted small fst-italic ms-1">No Sales</span>
                        <?php endif; ?>
                        
                        <button class="btn btn-detail shadow-sm" data-id="<?php echo $p['project_id']; ?>">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Column 2: Need Head Approval (Hidden for Head Ops) -->
    <?php if($_SESSION['role'] != 'head_ops'): ?>
    <div class="kanban-column">
        <div class="kanban-column-header">
            <span><i class="fas fa-user-shield me-2"></i>Need Head Approval</span>
            <span class="badge bg-white text-dark shadow-sm"><?php echo count($next_approval); ?></span>
        </div>
        <div id="col-need-head" class="kanban-items" data-status="need_approval_head">
            <?php foreach($next_approval as $p): 
                $dates = DateHelper::parseDateArray($p['tanggal_mcu']);
                $is_urgent = false;
                
                // Urgent / SLA Logic
                // 1. Existing H-1 Logic
                $target_date = !empty($dates) ? $dates[0] : null;

                if ($target_date) {
                    $working_days = DateHelper::countWorkingDays(date('Y-m-d'), $target_date, $holidays ?? []);
                    if ($working_days <= 1) { 
                        $is_urgent = true;
                    }
                }
                
                // 2. SLA Breach Logic
                if (!$is_urgent) {
                    $start_time = !empty($p['approved_date_manager']) ? strtotime($p['approved_date_manager']) : strtotime($p['created_at']);
                    
                    if ($start_time > 0) {
                        $start_date = date('Y-m-d', $start_time);
                        $today = date('Y-m-d');
                        
                        if ($start_date != $today) {
                            $working_days_elapsed = DateHelper::countWorkingDays($start_date, $today, $holidays ?? []);
                            if (isset($approval_sla_days) && $working_days_elapsed > $approval_sla_days) {
                                $is_urgent = true;
                            }
                        }
                    }
                }
            ?>
                <div class="kanban-card <?php echo $is_urgent ? 'border-danger border-2' : ''; ?>" data-id="<?php echo $p['project_id']; ?>" data-is-urgent="<?php echo $is_urgent ? '1' : '0'; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-light text-muted border shadow-sm">#<?php echo $p['project_id']; ?></span>
                        <?php if($is_urgent): ?>
                            <span class="badge bg-danger animate__animated animate__pulse animate__infinite urgent-badge">URGENT</span>
                        <?php endif; ?>
                        <small class="text-muted fw-bold" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i><?php 
                            if (!empty($dates)) {
                                echo date('d M', strtotime($dates[0]));
                                if(count($dates) > 1) echo ' (' . count($dates) . ' hari)';
                            } else {
                                echo date('d M', strtotime($p['created_at']));
                            }
                        ?></small>
                    </div>
                    
                    <h6 class="mb-2" title="<?php echo htmlspecialchars($p['nama_project']); ?>">
                        <?php echo htmlspecialchars($p['nama_project']); ?>
                    </h6>
                    
                    <div class="kanban-meta-row">
                        <i class="far fa-building"></i> 
                        <span class="text-truncate"><?php echo htmlspecialchars($p['company_name']); ?></span>
                    </div>
                    
                    <div class="kanban-meta-row">
                        <i class="fas fa-users"></i> 
                        <span><?php echo htmlspecialchars($p['total_peserta'] ?: '-'); ?> Pax</span>
                    </div>

                    <div class="kanban-footer">
                        <?php if(empty($p['sph_file'])): ?>
                            <span class="badge bg-danger me-1" title="SPH Not Uploaded">No SPH</span>
                        <?php endif; ?>
                        <?php if(!empty($p['sales_name'])): ?>
                            <div class="sales-badge" title="Sales: <?php echo htmlspecialchars($p['sales_name']); ?>">
                                <i class="fas fa-user-tie"></i> 
                                <span class="text-truncate" style="max-width: 120px;"><?php echo htmlspecialchars($p['sales_name']); ?></span>
                            </div>
                        <?php else: ?>
                             <span class="text-muted small fst-italic ms-1">No Sales</span>
                        <?php endif; ?>
                        
                        <button class="btn btn-detail shadow-sm" data-id="<?php echo $p['project_id']; ?>">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Column 3: Approved -->
    <div class="kanban-column">
        <div class="kanban-column-header">
            <span><i class="fas fa-check-circle me-2"></i>Approved</span>
            <span class="badge bg-white text-dark shadow-sm"><?php 
                echo count(array_filter($approved, function($p) {
                    return trim($p['status_project']) == 'approved';
                })); 
            ?></span>
        </div>
        <div id="col-approved" class="kanban-items" data-status="approved">
             <?php foreach($approved as $p): ?>
                <?php if (trim($p['status_project']) != 'approved') continue; ?>
                <div class="kanban-card" data-id="<?php echo $p['project_id']; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge bg-light text-muted border shadow-sm">#<?php echo $p['project_id']; ?></span>
                        </div>
                        <small class="text-muted fw-bold" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i><?php 
                            $dates = DateHelper::parseDateArray($p['tanggal_mcu']);
                            if (!empty($dates)) {
                                echo date('d M', strtotime($dates[0]));
                                if(count($dates) > 1) echo ' (' . count($dates) . ' hari)';
                            } else {
                                echo date('d M', strtotime($p['created_at']));
                            }
                        ?></small>
                    </div>
                    
                    <h6 class="mb-2" title="<?php echo htmlspecialchars($p['nama_project']); ?>">
                        <?php echo htmlspecialchars($p['nama_project']); ?>
                    </h6>
                    
                    <div class="kanban-meta-row">
                        <i class="far fa-building"></i> 
                        <span class="text-truncate"><?php echo htmlspecialchars($p['company_name']); ?></span>
                    </div>
                    
                    <div class="kanban-meta-row">
                        <i class="fas fa-users"></i> 
                        <span><?php echo htmlspecialchars($p['total_peserta'] ?: '-'); ?> Pax</span>
                    </div>

                    <div class="kanban-footer">
                        <?php if(!empty($p['sales_name'])): ?>
                            <div class="sales-badge" title="Sales: <?php echo htmlspecialchars($p['sales_name']); ?>">
                                <i class="fas fa-user-tie"></i> 
                                <span class="text-truncate" style="max-width: 120px;"><?php echo htmlspecialchars($p['sales_name']); ?></span>
                            </div>
                        <?php else: ?>
                             <span class="text-muted small fst-italic ms-1">No Sales</span>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'korlap' && in_array($p['status_project'], ['approved', 'in_progress_ops'])): ?>
                            <button class="btn btn-sm btn-outline-primary ms-1" onclick="loadProjectDetail('<?php echo $p['project_id']; ?>', 'ba')" title="Upload BA">
                                <i class="fas fa-file-upload"></i> BA
                            </button>
                        <?php endif; ?>

                        <button class="btn btn-detail shadow-sm" data-id="<?php echo $p['project_id']; ?>">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Column 4: Rejected -->
    <div class="kanban-column">
        <div class="kanban-column-header">
            <span><i class="fas fa-times-circle me-2"></i>Rejected</span>
            <span class="badge bg-white text-dark shadow-sm"><?php echo count($rejected); ?></span>
        </div>
        <div id="col-rejected" class="kanban-items" data-status="rejected">
             <?php foreach($rejected as $p): ?>
                <div class="kanban-card" data-id="<?php echo $p['project_id']; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-light text-muted border shadow-sm">#<?php echo $p['project_id']; ?></span>
                        <span class="badge bg-danger">Rejected</span>
                    </div>
                    
                    <h6 class="mb-2" title="<?php echo htmlspecialchars($p['nama_project']); ?>">
                        <?php echo htmlspecialchars($p['nama_project']); ?>
                    </h6>
                    
                    <div class="kanban-meta-row">
                        <i class="far fa-building"></i> 
                        <span class="text-truncate"><?php echo htmlspecialchars($p['company_name']); ?></span>
                    </div>
                    
                    <div class="kanban-meta-row">
                        <i class="fas fa-users"></i> 
                        <span><?php echo htmlspecialchars($p['total_peserta']); ?> Pax</span>
                    </div>

                    <div class="kanban-meta-row text-muted small">
                        <i class="far fa-clock"></i>
                        <span><?php 
                            $dates = DateHelper::parseDateArray($p['tanggal_mcu']);
                            if (!empty($dates)) {
                                echo date('d M', strtotime($dates[0]));
                                if(count($dates) > 1) echo ' (' . count($dates) . ' hari)';
                            } else {
                                echo date('d M', strtotime($p['created_at']));
                            }
                        ?></span>
                    </div>

                    <div class="kanban-footer">
                        <?php if(!empty($p['sales_name'])): ?>
                            <div class="sales-badge" title="Sales: <?php echo htmlspecialchars($p['sales_name']); ?>">
                                <i class="fas fa-user-tie"></i> 
                                <span class="text-truncate" style="max-width: 120px;"><?php echo htmlspecialchars($p['sales_name']); ?></span>
                            </div>
                        <?php else: ?>
                             <span class="text-muted small fst-italic ms-1">No Sales</span>
                        <?php endif; ?>
                        
                        <button class="btn btn-detail shadow-sm" data-id="<?php echo $p['project_id']; ?>">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Column 5: Re-nego -->
    <div class="kanban-column">
        <div class="kanban-column-header">
            <span><i class="fas fa-sync me-2"></i>Re-nego</span>
            <span class="badge bg-white text-dark shadow-sm"><?php echo count($renego); ?></span>
        </div>
        <div id="col-renego" class="kanban-items" data-status="re-nego">
             <?php foreach($renego as $p): ?>
                <div class="kanban-card" data-id="<?php echo $p['project_id']; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-light text-muted border shadow-sm">#<?php echo $p['project_id']; ?></span>
                        <span class="badge bg-warning text-dark">Re-nego</span>
                    </div>
                    
                    <h6 class="mb-2" title="<?php echo htmlspecialchars($p['nama_project']); ?>">
                        <?php echo htmlspecialchars($p['nama_project']); ?>
                    </h6>
                    
                    <div class="kanban-meta-row">
                        <i class="far fa-building"></i> 
                        <span class="text-truncate"><?php echo htmlspecialchars($p['company_name']); ?></span>
                    </div>
                    
                    <div class="kanban-meta-row">
                        <i class="fas fa-users"></i> 
                        <span><?php echo htmlspecialchars($p['total_peserta']); ?> Pax</span>
                    </div>

                    <div class="kanban-meta-row text-muted small">
                        <i class="far fa-clock"></i>
                        <span><?php 
                            $dates = DateHelper::parseDateArray($p['tanggal_mcu']);
                            if (!empty($dates)) {
                                echo date('d M', strtotime($dates[0]));
                                if(count($dates) > 1) echo ' (' . count($dates) . ' hari)';
                            } else {
                                echo date('d M', strtotime($p['created_at']));
                            }
                        ?></span>
                    </div>

                    <div class="kanban-footer">
                        <?php if(!empty($p['sales_name'])): ?>
                            <div class="sales-badge" title="Sales: <?php echo htmlspecialchars($p['sales_name']); ?>">
                                <i class="fas fa-user-tie"></i> 
                                <span class="text-truncate" style="max-width: 120px;"><?php echo htmlspecialchars($p['sales_name']); ?></span>
                            </div>
                        <?php else: ?>
                             <span class="text-muted small fst-italic ms-1">No Sales</span>
                        <?php endif; ?>
                        
                        <button class="btn btn-detail shadow-sm" data-id="<?php echo $p['project_id']; ?>">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Column 6: Cancelled -->
    <div class="kanban-column">
        <div class="kanban-column-header">
            <span><i class="fas fa-ban me-2"></i>Cancelled</span>
            <span class="badge bg-white text-dark shadow-sm"><?php echo count($cancelled); ?></span>
        </div>
        <div id="col-cancelled" class="kanban-items" data-status="cancelled">
             <?php foreach($cancelled as $p): ?>
                <div class="kanban-card" data-id="<?php echo $p['project_id']; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-light text-muted border shadow-sm">#<?php echo $p['project_id']; ?></span>
                        <span class="badge bg-secondary">Cancelled</span>
                    </div>
                    
                    <h6 class="mb-2" title="<?php echo htmlspecialchars($p['nama_project']); ?>">
                        <?php echo htmlspecialchars($p['nama_project']); ?>
                    </h6>
                    
                    <div class="kanban-meta-row">
                        <i class="far fa-building"></i> 
                        <span class="text-truncate"><?php echo htmlspecialchars($p['company_name']); ?></span>
                    </div>
                    
                    <div class="kanban-meta-row">
                        <i class="fas fa-users"></i> 
                        <span><?php echo htmlspecialchars($p['total_peserta']); ?> Pax</span>
                    </div>

                    <div class="kanban-footer">
                        <?php if(!empty($p['sales_name'])): ?>
                            <div class="sales-badge" title="Sales: <?php echo htmlspecialchars($p['sales_name']); ?>">
                                <i class="fas fa-user-tie"></i> 
                                <span class="text-truncate" style="max-width: 120px;"><?php echo htmlspecialchars($p['sales_name']); ?></span>
                            </div>
                        <?php else: ?>
                             <span class="text-muted small fst-italic ms-1">No Sales</span>
                        <?php endif; ?>
                        
                        <button class="btn btn-detail shadow-sm" data-id="<?php echo $p['project_id']; ?>">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<!-- Include Shared Project Detail Modal -->
<?php include __DIR__ . '/../partials/project_detail_modal.php'; ?>

<script>
    // Global variable for project_detail.js
    // userRole needs to be defined in the global scope before project_detail.js is loaded
    // if the script relies on it immediately, or it can be accessed by functions later.
    // To be safe, we define it here.
    if (typeof userRole === 'undefined') {
        var userRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
    }
</script>

<!-- Include Shared Project Detail JS -->
<script src="js/project_detail.js?v=<?php echo time(); ?>"></script>

<script>
    // Kanban specific scripts
    document.addEventListener('DOMContentLoaded', function () {
        // userRole is already defined globally above
        
        // Disable drag and drop for read-only roles
        const readOnlyRoles = ['admin_ops', 'admin_sales', 'ceo', 'sales', 'manager_sales', 'sales_support_supervisor', 'sales_performance_manager', 'surat_hasil', 'dw_tim_hasil'];
        if (!readOnlyRoles.includes(userRole)) {
            const containers = document.querySelectorAll('.kanban-items');

            containers.forEach(function (container) {
                new Sortable(container, {
                    group: 'kanban', // set both lists to same group
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function (evt) {
                        var itemEl = evt.item;  // dragged HTMLElement
                        var newStatus = evt.to.getAttribute('data-status');
                        var projectId = itemEl.getAttribute('data-id');

                        // Check for Rejected or Re-nego
                        if(newStatus === 'rejected' || newStatus === 're-nego') {
                            Swal.fire({
                                title: newStatus === 'rejected' ? 'Reject Project' : 'Re-negotiate Project',
                                text: "Please provide a reason:",
                                input: 'textarea',
                                inputPlaceholder: 'Enter your reason here...',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: newStatus === 'rejected' ? '#d33' : '#f0ad4e',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: newStatus === 'rejected' ? 'Yes, Reject' : 'Submit Re-nego',
                                inputValidator: (value) => {
                                    if (!value) {
                                        return 'You need to write a reason!'
                                    }
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    handleUrgentStatus(itemEl, newStatus);
                                    updateStatus(projectId, newStatus, result.value);
                                } else {
                                    location.reload(); // Revert drag if cancelled
                                }
                            });
                            return;
                        }

                        handleUrgentStatus(itemEl, newStatus);
                        updateStatus(projectId, newStatus);
                        
                        function handleUrgentStatus(card, status) {
                            var isUrgent = card.getAttribute('data-is-urgent') === '1';
                            if (!isUrgent) return;
                            
                            var urgentBadge = card.querySelector('.urgent-badge');
                            
                            if (status === 'approved' || status === 'rejected' || status === 're-nego' || status === 'cancelled') {
                                card.classList.remove('border-danger', 'border-2');
                                if (urgentBadge) urgentBadge.style.display = 'none';
                            } else {
                                // Moving back to need approval (or similar)
                                card.classList.add('border-danger', 'border-2');
                                if (urgentBadge) urgentBadge.style.display = 'inline-block';
                            }
                        }

                        function updateStatus(id, status, reason = null) {
                            $.ajax({
                                url: 'index.php?page=update_project_status',
                                method: 'POST',
                                data: {
                                    project_id: id,
                                    status: status,
                                    reason: reason
                                },
                                success: function(response) {
                                    try {
                                        var res = (typeof response === 'string') ? JSON.parse(response) : response;
                                        
                                        if(res.status === 'success') {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Updated!',
                                                text: 'Project status has been updated.',
                                                timer: 1500,
                                                showConfirmButton: false
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'warning',
                                                title: 'Cannot Update Status',
                                                text: res.message || 'Unknown error occurred.',
                                                confirmButtonColor: '#3085d6'
                                            }).then(() => {
                                                location.reload();
                                            });
                                        }
                                    } catch(e) {
                                        console.error("Kanban Update Error:", e, response);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'System Error',
                                            text: 'Failed to process response. Check console for details.',
                                        }).then(() => {
                                            location.reload();
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            });
        }

    // Detail Modal
        $(document).on('click', '.btn-detail', function() {
            var projectId = $(this).data('id');
            loadProjectDetail(projectId);
        });

        // Auto open modal from URL
        const urlParams = new URLSearchParams(window.location.search);
        const openProjectId = urlParams.get('open_project_id');
        const openTab = urlParams.get('open_tab');
        
        if (openProjectId) {
             setTimeout(() => {
                 loadProjectDetail(openProjectId, openTab);
             }, 150);
        }

        const openCompletion = urlParams.get('open_completion');
        if (openCompletion) {
             const section = document.getElementById('completion-approvals-section');
             if (section) {
                 setTimeout(() => {
                     section.scrollIntoView({ behavior: 'smooth' });
                     section.classList.add('animate__animated', 'animate__pulse');
                 }, 150);
             }
        }

    });
</script>

<?php include '../views/layouts/footer.php'; ?>
