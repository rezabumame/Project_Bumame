<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-white border-end" id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom">
            <div class="d-flex align-items-center justify-content-center">
                <img src="assets/images/logo.png?v=<?php echo time(); ?>" alt="Bumame" style="max-height: 30px; width: auto;">
            </div>
        </div>
        <div class="list-group list-group-flush my-0">
            <?php
            $role = $_SESSION['role'] ?? '';
            $user_id = $_SESSION['user_id'] ?? 0;
            $currentPage = $_GET['page'] ?? 'dashboard';

            // Urgent Count Logic
            $urgentCount = 0;
            $urgentLink = 'index.php?page=manager_ops_kanban';
            $notifyRoles = ['manager_ops', 'head_ops', 'admin_ops', 'superadmin'];
            
            if (in_array($role, $notifyRoles)) {
                if (!class_exists('Database')) include_once '../config/database.php';
                if (!class_exists('Project')) include_once '../models/Project.php';
                
                try {
                    $db = (new Database())->getConnection();
                    $projectModel = new Project($db);
                    $urgentProjects = $projectModel->getUrgentProjects($role);
                    $urgentCount = count($urgentProjects);
                    if ($urgentCount == 1) {
                        $urgentLink = 'index.php?page=manager_ops_kanban&open_project_id=' . $urgentProjects[0]['project_id'];
                    }
                } catch (Exception $e) {}
            }

            // Budget Counts
            $rabPendingCount = 0;
            $realizationPendingCount = 0;
            $rabMedicalPendingCount = 0;
            $rabRoles = ['manager_ops', 'head_ops', 'ceo', 'finance'];
            if (in_array($role, $rabRoles)) {
                if (!class_exists('Database')) include_once '../config/database.php';
                if (!class_exists('Rab')) include_once '../models/Rab.php';
                try {
                    $db_rab = (new Database())->getConnection();
                    $rabModel = new Rab($db_rab);
                    $rabPendingCount = $rabModel->getPendingCount($role);
                    if ($role == 'manager_ops') {
                        $realizationPendingCount = $rabModel->getPendingRealizationCount($role);
                    }
                } catch (Exception $e) {}
            }

            // RAB Medical Counts
            $rabMedicalNotifyRoles = ['manager_ops', 'head_ops'];
            if (in_array($role, $rabMedicalNotifyRoles)) {
                if (!class_exists('Database')) include_once '../config/database.php';
                if (!class_exists('RabMedicalResult')) include_once '../models/RabMedicalResult.php';
                try {
                    $db_rab_med = (new Database())->getConnection();
                    $rabMedicalModel = new RabMedicalResult($db_rab_med);
                    $rabMedicalPendingCount = $rabMedicalModel->countPendingApprovals($role);
                } catch (Exception $e) {}
            }
            ?>

            <style>
                .sidebar-category-header {
                    font-size: 0.75rem;
                    text-transform: uppercase;
                    letter-spacing: 0.05rem;
                    color: #adb5bd;
                    padding: 1.5rem 1.25rem 0.5rem;
                    font-weight: 700;
                }
                #sidebar-wrapper .list-group-item {
                    border: none;
                    padding: 0.7rem 1.25rem;
                    font-size: 0.9rem;
                    color: #495057;
                    transition: all 0.2s ease;
                    background-color: transparent;
                }
                #sidebar-wrapper .list-group-item:hover {
                    background-color: #f8f9fa;
                    color: #204EAB;
                }
                #sidebar-wrapper .list-group-item.active {
                    background-color: #204EAB !important;
                    color: #ffffff !important;
                    border-radius: 0.5rem;
                    margin: 0.2rem 0.75rem;
                    font-weight: 600;
                }
                #sidebar-wrapper .list-group-item.active i {
                    color: #ffffff !important;
                }
                #sidebar-wrapper .list-group-item:not(.active) {
                    margin: 0.2rem 0.75rem;
                    border-radius: 0.5rem;
                }
                .urgent-item {
                    background-color: #fff5f5 !important;
                    border-left: 4px solid #dc3545 !important;
                    margin: 0.5rem 0.75rem;
                    border-radius: 0.5rem;
                }
                .urgent-item:hover { background-color: #ffe3e3 !important; }
            </style>

            <?php if ($urgentCount > 0): ?>
            <a href="<?php echo $urgentLink; ?>" class="list-group-item list-group-item-action urgent-item text-danger fw-bold shadow-sm py-2">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <span class="text-truncate"><i class="fas fa-exclamation-circle me-2 animate__animated animate__pulse animate__infinite"></i>Urgent Approval</span>
                    <span class="badge rounded-pill bg-danger px-2"><?php echo $urgentCount; ?></span>
                </div>
            </a>
            <?php endif; ?>

            <!-- 1. Dashboard -->
            <a href="index.php?page=dashboard" class="list-group-item list-group-item-action <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?> mt-2">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>

            <!-- 2. Sales & Projects -->
            <?php 
            $canSeeNewProject = in_array($role, ['superadmin', 'admin_sales']);
            $canSeeAllProjects = in_array($role, ['superadmin', 'ceo', 'admin_sales', 'sales', 'manager_sales', 'manager_ops', 'head_ops', 'admin_ops', 'procurement', 'korlap', 'sales_support_supervisor', 'sales_performance_manager', 'surat_hasil', 'dw_tim_hasil', 'finance']);
            $canSeeKanban = in_array($role, ['superadmin', 'ceo', 'admin_sales', 'manager_ops', 'head_ops', 'admin_ops', 'sales_support_supervisor', 'sales_performance_manager', 'sales', 'manager_sales', 'surat_hasil']);
            $canSeeVendors = in_array($role, ['superadmin', 'procurement']);
            
            if ($canSeeNewProject || $canSeeAllProjects || $canSeeKanban || $canSeeVendors): 
            ?>
            <div class="sidebar-category-header">Sales & Projects</div>
            <?php if ($canSeeNewProject): ?>
            <a href="index.php?page=projects_create" class="list-group-item list-group-item-action <?php echo ($currentPage == 'projects_create') ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle me-2"></i>New Project
            </a>
            <?php endif; ?>
            
            <?php if ($canSeeAllProjects): ?>
            <a href="index.php?page=all_projects" class="list-group-item list-group-item-action <?php echo ($currentPage == 'all_projects' || $currentPage == 'projects_list') ? 'active' : ''; ?>">
                <i class="fas fa-list me-2"></i>All Projects
            </a>
            <?php endif; ?>

            <?php if ($canSeeKanban): ?>
            <a href="index.php?page=manager_ops_kanban" class="list-group-item list-group-item-action <?php echo ($currentPage == 'manager_ops_kanban') ? 'active' : ''; ?>">
                <i class="fas fa-columns me-2"></i>Kanban Board
            </a>
            <?php endif; ?>

            <?php if ($canSeeVendors): ?>
            <a href="index.php?page=vendors_list" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'vendor') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-truck me-2"></i>Vendors
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <!-- 3. Project Execution -->
            <?php 
            $canSeeTM = in_array($role, ['korlap', 'admin_ops', 'head_ops', 'manager_ops', 'surat_hasil', 'superadmin']);
            $canSeeInvReq = in_array($role, ['korlap', 'superadmin', 'manager_ops', 'admin_ops']);
            $canSeeInvDash = in_array($role, ['admin_gudang_aset', 'admin_gudang_warehouse', 'superadmin']);
            
            if ($canSeeTM || $canSeeInvReq || $canSeeInvDash):
            ?>
            <div class="sidebar-category-header">Project Execution</div>
            <?php if ($canSeeTM): ?>
            <a href="index.php?page=technical_meeting_list" class="list-group-item list-group-item-action <?php echo ($currentPage == 'technical_meeting_list') ? 'active' : ''; ?>">
                <i class="fas fa-handshake me-2"></i>Technical Meeting
            </a>
            <?php endif; ?>

            <?php if ($canSeeInvReq): ?>
            <a href="index.php?page=inventory_request_index" class="list-group-item list-group-item-action <?php echo ($currentPage == 'inventory_request_index') ? 'active' : ''; ?>">
                <i class="fas fa-box-open me-2"></i>Inventory Request
            </a>
            <?php endif; ?>

            <?php if ($canSeeInvDash): ?>
            <a href="index.php?page=warehouse_dashboard" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'warehouse') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list me-2"></i>Inventory Dashboard
            </a>
            <?php endif; ?>
            
            <?php if (in_array($role, ['superadmin', 'admin_ops', 'manager_ops', 'head_ops'])): ?>
            <a href="index.php?page=man_power_mcu" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'man_power_mcu') !== false || strpos($currentPage, 'project_man_power') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-users-cog me-2"></i>Staff Assignment
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <!-- 4. Medical Report -->
            <?php 
            $canSeeMR = in_array($role, ['superadmin', 'manager_ops', 'head_ops', 'surat_hasil', 'admin_ops']);
            if ($canSeeMR):
            ?>
            <div class="sidebar-category-header">Medical Report</div>
            <a href="index.php?page=medical_results_index" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'medical_results') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-file-contract me-2"></i>Result Entry
            </a>
            <?php endif; ?>

            <!-- 5. Budget & Cost Control -->
            <?php 
            $canSeeRabs = in_array($role, ['manager_ops', 'head_ops', 'ceo', 'finance', 'superadmin', 'korlap', 'admin_ops']);
            $canSeeRealization = in_array($role, ['manager_ops', 'head_ops', 'ceo', 'finance', 'superadmin', 'korlap', 'admin_ops']);
            
            // Logic for RAB Medical Visibility
            // Default roles
            $canSeeRabMed = in_array($role, ['superadmin', 'manager_ops', 'medical_result_team', 'surat_hasil', 'admin_ops']);
            
            // Conditional check for head_ops based on system setting
            if ($role == 'head_ops') {
                if (!class_exists('Database')) include_once '../config/database.php';
                if (!class_exists('SystemSetting')) include_once '../models/SystemSetting.php';
                try {
                    $db_temp = (new Database())->getConnection();
                    $sysSetting = new SystemSetting($db_temp);
                    // Check if approval is enabled for head_ops
                    if ($sysSetting->get('approval_head_medical_result') == 'true') {
                        $canSeeRabMed = true;
                    }
                } catch (Exception $e) {}
            }
            
            if ($canSeeRabs || $canSeeRealization || $canSeeRabMed):
            ?>
            <div class="sidebar-category-header">Budget & Cost Control</div>
            <?php if ($canSeeRabs): ?>
            <a href="index.php?page=rabs_list" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'rabs') !== false) ? 'active' : ''; ?>">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <span><i class="fas fa-file-invoice-dollar me-2"></i>Field Budget Request</span>
                    <?php if ($rabPendingCount > 0): ?>
                        <span class="badge rounded-pill bg-danger"><?php echo $rabPendingCount; ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endif; ?>
            
            <?php if ($canSeeRealization): ?>
            <a href="index.php?page=realization_list" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'realization') !== false) ? 'active' : ''; ?>">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <span><i class="fas fa-receipt me-2"></i>Field Budget Realization</span>
                    <?php if ($realizationPendingCount > 0): ?>
                        <span class="badge rounded-pill bg-danger"><?php echo $realizationPendingCount; ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($canSeeRabMed): ?>
            <a href="index.php?page=rab_medical_index" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'rab_medical') !== false) ? 'active' : ''; ?>">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <span><i class="fas fa-notes-medical me-2"></i>Medical Report Budget</span>
                    <?php if ($rabMedicalPendingCount > 0): ?>
                        <span class="badge rounded-pill bg-danger"><?php echo $rabMedicalPendingCount; ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <!-- 6. Finance -->
            <?php 
            $canSeeInvReqFin = in_array($role, ['admin_sales', 'sales', 'manager_sales', 'superadmin', 'sales_support_supervisor', 'sales_performance_manager', 'finance']);
            $canSeeInvProc = in_array($role, ['finance', 'superadmin', 'admin_sales', 'sales_support_supervisor', 'sales_performance_manager']);
            
            if ($canSeeInvReqFin || $canSeeInvProc):
            ?>
            <div class="sidebar-category-header">Finance</div>
            <?php if ($canSeeInvReqFin): ?>
            <a href="index.php?page=invoice_requests_index" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'invoice_requests') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice me-2"></i>Invoice Requests
            </a>
            <?php endif; ?>

            <?php if ($canSeeInvProc): ?>
            <a href="index.php?page=invoice_processing_index" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'invoice_processing') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-cash-register me-2"></i>Invoice Processing
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <!-- 7. Audit & Monitoring -->
            <?php 
            $canSeeAudit = in_array($role, ['manager_ops', 'head_ops', 'superadmin', 'ceo']);
            $canSeeProductivity = in_array($role, ['superadmin', 'manager_ops', 'head_ops']);
            
            if ($canSeeAudit || $canSeeProductivity):
            ?>
            <div class="sidebar-category-header">Audit & Monitoring</div>
            
            <?php if ($canSeeProductivity): ?>
            <a href="index.php?page=productivity_ops" class="list-group-item list-group-item-action <?php echo ($currentPage == 'productivity_ops') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line me-2"></i>Productivity Ops
            </a>
            <?php endif; ?>

            <?php if ($canSeeAudit): ?>
            <a href="index.php?page=audit_index" class="list-group-item list-group-item-action <?php echo ($currentPage == 'audit_index') ? 'active' : ''; ?>">
                <i class="fas fa-history me-2"></i>Audit System
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <!-- 8. Administration -->
            <?php 
            $isSuper = ($role == 'superadmin');
            $canSeeSalesMgmt = in_array($role, ['superadmin', 'admin_sales', 'ceo', 'sales_support_supervisor', 'sales_performance_manager']);
            $canSeeStaffMgmt = in_array($role, ['superadmin', 'admin_ops', 'manager_ops', 'head_ops']);
            
            if ($isSuper || $canSeeSalesMgmt || $canSeeStaffMgmt):
            ?>
            <div class="sidebar-category-header">Administration</div>
            <?php if ($isSuper): ?>
            <a href="index.php?page=superadmin_users" class="list-group-item list-group-item-action <?php echo ($currentPage == 'superadmin_users') ? 'active' : ''; ?>">
                <i class="fas fa-users-cog me-2"></i>User Management
            </a>
            <?php endif; ?>

            <?php if (in_array($role, ['superadmin', 'admin_ops', 'manager_ops', 'head_ops'])): ?>
            <a href="index.php?page=man_power_management" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'man_power_management') !== false || strpos($currentPage, 'man_power_edit') !== false || strpos($currentPage, 'man_power_create') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-id-card me-2"></i>Staff Management
            </a>
            <?php endif; ?>

            <?php if ($canSeeSalesMgmt): ?>
            <a href="index.php?page=sales_persons_index" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'sales_persons') !== false || strpos($currentPage, 'sales_managers') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-user-tie me-2"></i>Sales Management
            </a>
            <?php endif; ?>

            <?php if ($isSuper): ?>
            <a href="index.php?page=cost_codes_index" class="list-group-item list-group-item-action <?php echo ($currentPage == 'cost_codes_index') ? 'active' : ''; ?>">
                <i class="fas fa-tags me-2"></i>Expense Code
            </a>
            <a href="index.php?page=inventory_master_index" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'inventory_master') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-database me-2"></i>Master Inventory
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <!-- 9. System -->
            <?php if ($isSuper): ?>
            <div class="sidebar-category-header">System</div>
            <a href="index.php?page=holidays" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'holidays') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-calendar-times me-2"></i>National Holidays
            </a>
            <a href="index.php?page=settings" class="list-group-item list-group-item-action <?php echo (strpos($currentPage, 'settings') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-cogs me-2"></i>System Settings
            </a>
            <?php endif; ?>

            <!-- 10. My Profile -->
            <div class="sidebar-category-header">My Profile</div>
            <a href="index.php?page=profile_edit" class="list-group-item list-group-item-action <?php echo ($currentPage == 'profile_edit') ? 'active' : ''; ?>">
                <i class="fas fa-user-cog me-2"></i>Edit Profile
            </a>
            <a href="index.php?page=logout" class="list-group-item list-group-item-action text-danger fw-bold">
                <i class="fas fa-power-off me-2"></i>Logout
            </a>
        </div>
    </div>

    <!-- Script to restore sidebar scroll position -->
    <script>
        (function() {
            var sidebar = document.getElementById("sidebar-wrapper");
            if (sidebar) {
                var scrollPos = localStorage.getItem("sidebarScrollPos");
                if (scrollPos) sidebar.scrollTop = scrollPos;
                sidebar.addEventListener("scroll", function() {
                    localStorage.setItem("sidebarScrollPos", sidebar.scrollTop);
                });
            }
        })();
    </script>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-2 px-4 shadow-sm mb-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                <h3 class="m-0 text-primary fw-bold">Bumame Ticketing</h3>
            </div>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <!-- Notification Icon -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative text-dark" href="#" id="notifDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-bell fs-5"></i>
                            <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="display: none;">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-0 shadow border-0" style="width: 320px;">
                            <li class="p-3 border-bottom bg-light d-flex justify-content-between">
                                <span class="fw-bold small">NOTIFICATIONS</span>
                                <a href="#" class="small text-decoration-none mark-all-read-btn">Mark all read</a>
                            </li>
                            <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                                <li class="p-4 text-center text-muted small">No new notifications</li>
                            </div>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle second-text fw-bold text-dark" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fs-5 me-1"></i><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="z-index: 9999;">
                            <li><a class="dropdown-item" href="index.php?page=profile_edit"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="index.php?page=logout"><i class="fas fa-power-off me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
