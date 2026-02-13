<?php
class RabMedicalResultController extends BaseController {
    private $rabModel;
    private $realizationModel;
    private $projectModel;
    private $userModel;
    private $settingModel;

    public function __construct() {
        parent::__construct();
        $this->rabModel = $this->loadModel('RabMedicalResult');
        $this->realizationModel = $this->loadModel('MedicalResultRealization');
        $this->projectModel = $this->loadModel('Project');
        $this->userModel = $this->loadModel('User');
        $this->settingModel = $this->loadModel('SystemSetting');
    }

    public function index() {
        // Role check
        
        $view = $_GET['view'] ?? 'list';
        
        if ($view === 'calendar') {
            $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
            $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
            
            $filters = [
                'project_id' => $_GET['project_id'] ?? null,
                'user_id' => $_GET['user_id'] ?? null
            ];
            
            $workload_data = $this->realizationModel->getWorkloadForMonth($month, $year, $filters);
            
            // Get all projects for filter
            $projects_stmt = $this->projectModel->getAllProjects($_SESSION['role'], $_SESSION['user_id'], 1, 1000);
            $projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get all unique Kohas who have created RABs
            $kohas_query = "SELECT DISTINCT u.user_id, u.full_name 
                            FROM rab_medical_results r 
                            JOIN users u ON r.created_by = u.user_id 
                            ORDER BY u.full_name ASC";
            $kohas_stmt = $this->db->prepare($kohas_query);
            $kohas_stmt->execute();
            $kohas_list = $kohas_stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->view('rab_medical/index', [
                'view' => 'calendar',
                'month' => $month,
                'year' => $year,
                'workload_data' => $workload_data,
                'projects' => $projects,
                'kohas_list' => $kohas_list,
                'filters' => $filters
            ]);
            return;
        }

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $filters = []; // Can be extended with search params later
        
        // Manager Ops should see everything except drafts
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager_ops') {
            $filters['status_not'] = 'draft';
        }

        $stats = $this->rabModel->getStats();
        $rabs = $this->rabModel->getAll($filters, $page, $limit);
        $total_rabs = $this->rabModel->countAll($filters);
        $total_pages = ceil($total_rabs / $limit);

        $this->view('rab_medical/index', [
            'view' => 'list',
            'stats' => $stats,
            'rabs' => $rabs,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
    }

    public function dwDashboard() {
        // Read-only dashboard for DW TIM HASIL
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $filters = [];

        $stats = $this->rabModel->getStats();
        $rabs = $this->rabModel->getAll($filters, $page, $limit);
        $total_rabs = $this->rabModel->countAll($filters);
        $total_pages = ceil($total_rabs / $limit);

        $this->view('rab_medical/dashboard_dw', [
            'stats' => $stats,
            'rabs' => $rabs,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
    }

    private function getConfiguredRoles() {
        $stmt = $this->settingModel->getAll();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $roles = [];
        foreach ($settings as $item) {
            if (strpos($item['setting_key'], 'fee_dalam_kota_') === 0) {
                $role_slug = str_replace('fee_dalam_kota_', '', $item['setting_key']);
                // Convert slug back to Title Case
                $role_name = ucwords(str_replace('_', ' ', $role_slug));
                $roles[] = $role_name;
            }
        }
        sort($roles);
        return array_unique($roles);
    }

    public function create() {
        // Access Check
        if (!in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])) {
            header("Location: index.php?page=rab_medical_index");
            return;
        }

        // Get projects available for RAB submission
        // For simplicity, get all projects for now, user can select.
        // Ideally filter out projects that already have RAB or handled via AJAX.
        $stmt = $this->projectModel->getAllProjects($_SESSION['role'], $_SESSION['user_id'], 1, 1000);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter for Kohas (dw_tim_hasil / surat_hasil) - Only show assigned projects
        if (in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil'])) {
            // Get assigned project IDs from medical_result_items
            $query = "SELECT DISTINCT mr.project_id 
                      FROM medical_results mr
                      JOIN medical_result_items mri ON mr.id = mri.medical_result_id
                      WHERE mri.assigned_to_user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $assigned_project_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Filter $projects array to keep only assigned projects
            $projects = array_filter($projects, function($p) use ($assigned_project_ids) {
                return in_array($p['project_id'], $assigned_project_ids);
            });
        }

        $selected_project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

        // Get users with role 'dw_tim_hasil' for personnel details dropdown
        $users = $this->userModel->getUsersByRole('dw_tim_hasil')->fetchAll(PDO::FETCH_ASSOC);
        
        // Get configured roles from settings
        $configured_roles = $this->getConfiguredRoles();

        $this->view('rab_medical/create', [
            'projects' => $projects,
            'selected_project_id' => $selected_project_id,
            'users' => $users,
            'configured_roles' => $configured_roles
        ]);
    }

    public function store() {
        // Access Check
        if (!in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])) {
            header("Location: index.php?page=rab_medical_index");
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'project_id' => $_POST['project_id'],
                'needs_hardcopy' => isset($_POST['needs_hardcopy']) ? 1 : 0,
                'send_whatsapp' => isset($_POST['send_whatsapp']) ? 1 : 0,
                'send_email' => isset($_POST['send_email']) ? 1 : 0,
                'notes' => $_POST['notes'],
                'created_by' => $_SESSION['user_id'],
                'dates' => []
            ];

            // Process dates
            if (isset($_POST['dates']) && is_array($_POST['dates'])) {
                foreach ($_POST['dates'] as $date => $info) {
                    if (isset($info['count']) && $info['count'] > 0) {
                        $data['dates'][] = [
                            'date' => $date,
                            'count' => $info['count'],
                            'details' => $info['details'] ?? ''
                        ];
                    }
                }
            }

            $rab_id = $this->rabModel->create($data);
            if ($rab_id) {
                // Log Action
                $this->projectModel->logAction($data['project_id'], 'RAB Medical Created', $_SESSION['user_id'], 'Draft created.');

                // Redirect to view
                header("Location: index.php?page=rab_medical_view&id=" . $rab_id);
            } else {
                echo "Error creating RAB.";
            }
        }
    }

    public function edit() {
        // Access Check
        if (!in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])) {
            header("Location: index.php?page=rab_medical_index");
            return;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: index.php?page=rab_medical_index");
            return;
        }

        $rab = $this->rabModel->getById($id);
        if (!$rab) {
            echo "RAB Medical Result not found.";
            return;
        }
        
        // Only draft or rejected can be edited
        if (!in_array($rab['status'], ['draft', 'rejected'])) {
             echo "Only Draft or Rejected RAB can be edited.";
             return;
        }

        // Get projects available
        $stmt = $this->projectModel->getAllProjects($_SESSION['role'], $_SESSION['user_id'], 1, 1000);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter for Kohas (dw_tim_hasil / surat_hasil) - Only show assigned projects
        if (in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil'])) {
             // Get assigned project IDs
            $query = "SELECT DISTINCT mr.project_id 
                      FROM medical_results mr
                      JOIN medical_result_items mri ON mr.id = mri.medical_result_id
                      WHERE mri.assigned_to_user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $assigned_project_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Filter $projects array
            $projects = array_filter($projects, function($p) use ($assigned_project_ids) {
                return in_array($p['project_id'], $assigned_project_ids);
            });
        }
        
        // Get users for personnel details
        $users = $this->userModel->getUsersByRole('dw_tim_hasil')->fetchAll(PDO::FETCH_ASSOC);

        // Get configured roles from settings
        $configured_roles = $this->getConfiguredRoles();

        $this->view('rab_medical/create', [
            'projects' => $projects,
            'selected_project_id' => $rab['project_id'],
            'users' => $users,
            'rab' => $rab,
            'is_edit' => true,
            'configured_roles' => $configured_roles
        ]);
    }

    public function update() {
         // Access Check
        if (!in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])) {
            header("Location: index.php?page=rab_medical_index");
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
             // Verify status is still draft or rejected
            $rab = $this->rabModel->getById($id);
            if (!in_array($rab['status'], ['draft', 'rejected'])) {
                 echo "Cannot edit non-draft/non-rejected RAB.";
                 return;
            }

            $data = [
                'project_id' => $_POST['project_id'],
                'needs_hardcopy' => isset($_POST['needs_hardcopy']) ? 1 : 0,
                'send_whatsapp' => isset($_POST['send_whatsapp']) ? 1 : 0,
                'send_email' => isset($_POST['send_email']) ? 1 : 0,
                'notes' => $_POST['notes'],
                'dates' => []
            ];

            // If it was rejected, reset to draft so it can be resubmitted properly
            if ($rab['status'] == 'rejected') {
                $data['status'] = 'draft';
                // Also clear rejection reason? Maybe keep it for history, but status change implies new attempt.
                // For now, just changing status is enough.
            }

            // Process dates
            if (isset($_POST['dates']) && is_array($_POST['dates'])) {
                foreach ($_POST['dates'] as $date => $info) {
                    if (isset($info['count']) && $info['count'] > 0) {
                        $data['dates'][] = [
                            'date' => $date,
                            'count' => $info['count'],
                            'details' => $info['details'] ?? ''
                        ];
                    }
                }
            }

            if ($this->rabModel->update($id, $data)) {
                 // Log Action
                $this->projectModel->logAction($data['project_id'], 'RAB Medical Updated', $_SESSION['user_id'], 'RAB updated.');
                
                header("Location: index.php?page=rab_medical_view&id=" . $id);
            } else {
                echo "Error updating RAB.";
            }
        }
    }

    public function view_rab() {
        // Access Check: All allowed roles can view
        if (!in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'manager_ops', 'head_ops', 'superadmin', 'admin_ops'])) {
             header("Location: index.php?page=dashboard");
             return;
        }

        $id = $_GET['id'];
        $rab = $this->rabModel->getById($id);
        
        if (!$rab) {
            echo "RAB Medical Result not found.";
            return;
        }

        // Get Realizations
        $realizations = $this->realizationModel->getByRabId($id);

        // Check for overlaps
        foreach ($realizations as &$real) {
            $overlaps = $this->realizationModel->checkOverlap($real['user_id'], $real['date']);
            $real['overlaps'] = [];
            foreach($overlaps as $ov) {
                if ($ov['id'] != $real['id']) {
                     $real['overlaps'][] = $ov['nama_project'];
                }
            }
        }

        // Get config for approval head
        $head_approval_val = $this->settingModel->get('approval_head_medical_result');
        $head_approval_enabled = ($head_approval_val === 'true');

        // Get users for realization dropdown (Role: dw_tim_hasil)
        $users = $this->userModel->getUsersByRole('dw_tim_hasil')->fetchAll(PDO::FETCH_ASSOC);

        // Also fetch ALL users to ensure we have names for IDs that might not be dw_tim_hasil anymore or from other roles
        $all_users = $this->userModel->getAllUsers()->fetchAll(PDO::FETCH_ASSOC);
        $personnel_map = [];
        foreach ($all_users as $u) {
            $personnel_map[$u['user_id']] = $u['full_name'];
        }

        $this->view('rab_medical/view', [
            'rab' => $rab,
            'realizations' => $realizations,
            'head_approval_enabled' => $head_approval_enabled,
            'users' => $users,
            'personnel_map' => $personnel_map
        ]);
    }

    public function submit() {
        // Access Check
        if (!in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])) {
            header("Location: index.php?page=rab_medical_index");
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $rab = $this->rabModel->getById($id);
            
            if ($rab && $this->rabModel->submit($id)) {
                $this->projectModel->logAction($rab['project_id'], 'RAB Medical Submitted', $_SESSION['user_id'], 'Submitted for approval.');
            }
            
            header("Location: index.php?page=rab_medical_view&id=" . $id);
        }
    }

    public function approve() {
        // Access Check
        if (!in_array($_SESSION['role'], ['manager_ops', 'head_ops', 'superadmin'])) {
            header("Location: index.php?page=rab_medical_index");
            return;
        }

        // Handle approval logic
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $action = $_POST['action']; // 'approve_manager', 'approve_head', 'reject'
            
            $rab = $this->rabModel->getById($id);
            if (!$rab) {
                header("Location: index.php?page=rab_medical_index");
                return;
            }

            if ($action == 'approve_manager') {
                if ($this->rabModel->approveManager($id, $_SESSION['user_id'])) {
                    $this->projectModel->logAction($rab['project_id'], 'RAB Medical Manager Approved', $_SESSION['user_id'], 'Approved by Manager.');
                }
            } elseif ($action == 'approve_head') {
                if ($this->rabModel->approveHead($id, $_SESSION['user_id'])) {
                    $this->projectModel->logAction($rab['project_id'], 'RAB Medical Head Approved', $_SESSION['user_id'], 'Approved by Head.');
                }
            } elseif ($action == 'reject') {
                $reason = $_POST['rejection_reason'] ?? null;
                if ($this->rabModel->reject($id, $_SESSION['user_id'], $reason)) {
                    $logMsg = 'Rejected.';
                    if ($reason) {
                        $logMsg .= ' Reason: ' . $reason;
                    }
                    $this->projectModel->logAction($rab['project_id'], 'RAB Medical Rejected', $_SESSION['user_id'], $logMsg);
                }
            }
            
            header("Location: index.php?page=rab_medical_view&id=" . $id);
        }
    }

    public function store_realization() {
        // Access Check
        if (!in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])) {
            header("Location: index.php?page=rab_medical_index");
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rab_id = $_POST['rab_id'];
            $entries = $_POST['entries'] ?? null;

            // Get RAB to find Project ID for logging
            $rab = $this->rabModel->getById($rab_id);
            
            if (!$rab) {
                header("Location: index.php?page=rab_medical_index");
                return;
            }

            // Check for replace mode (Full Update)
            if (isset($_POST['mode']) && $_POST['mode'] == 'replace') {
                // Delete existing realizations
                $this->realizationModel->deleteByRabId($rab_id);
                
                // Process entries
                if (is_array($entries)) {
                    $logNote = "Realization Updated (Full Replace). ";
                    
                    foreach ($entries as $entry) {
                        if (empty($entry['user_id']) || empty($entry['date'])) continue;
                        
                        // Handle user_id as array (from multi-select) or single value
                        $userIds = is_array($entry['user_id']) ? $entry['user_id'] : [$entry['user_id']];
                        
                        foreach ($userIds as $userId) {
                            if (empty($userId)) continue;

                            $data = [
                                'rab_id' => $rab_id,
                                'user_id' => $userId,
                                'date' => $entry['date'],
                                'notes' => $entry['notes'] ?? ''
                            ];
                            
                            $overlaps = $this->realizationModel->checkOverlap($data['user_id'], $data['date']);
                            if (count($overlaps) > 0) {
                                $overlap_projects = [];
                                foreach($overlaps as $ov) {
                                    $overlap_projects[] = $ov['nama_project'];
                                }
                                $msg = "Overlap detected for user " . $userId . " on " . $entry['date'];
                                $_SESSION['overlap_warning'] = (isset($_SESSION['overlap_warning']) ? $_SESSION['overlap_warning'] . " | " : "") . $msg;
                                $logNote .= " " . $msg;
                            }
                            
                            $this->realizationModel->create($data);
                        }
                    }
                    
                    // Update RAB status to completed
                    $this->rabModel->markAsCompleted($rab_id);
                    
                    $this->projectModel->logAction($rab['project_id'], 'Medical Realization Updated', $_SESSION['user_id'], $logNote);
                }
            } elseif (is_array($entries)) {
                $has_created = false;
                foreach ($entries as $entry) {
                    if (empty($entry['user_id']) || empty($entry['date'])) continue;
                    $data = [
                        'rab_id' => $rab_id,
                        'user_id' => $entry['user_id'],
                        'date' => $entry['date'],
                        'notes' => $entry['notes'] ?? ''
                    ];
                    
                    $overlaps = $this->realizationModel->checkOverlap($data['user_id'], $data['date']);
                    if ($this->realizationModel->create($data)) {
                        $has_created = true;
                        $logNote = "Assigned user ID " . $data['user_id'] . " for date " . $data['date'];
                        if (count($overlaps) > 0) {
                            $overlap_projects = [];
                            foreach($overlaps as $ov) {
                                $overlap_projects[] = $ov['nama_project'];
                            }
                            $msg = "Overlap detected! User is also assigned to: " . implode(', ', $overlap_projects) . " on " . $data['date'];
                            $_SESSION['overlap_warning'] = $msg;
                            $logNote .= ". " . $msg;
                        }
                        $this->projectModel->logAction($rab['project_id'], 'Medical Realization Added', $_SESSION['user_id'], $logNote);
                    }
                }
                if ($has_created) {
                    $this->rabModel->markAsCompleted($rab_id);
                }
            } else {
                // Backward compatibility single entry
                $data = [
                    'rab_id' => $rab_id,
                    'user_id' => $_POST['user_id'] ?? null,
                    'date' => $_POST['date'] ?? null,
                    'notes' => $_POST['notes'] ?? ''
                ];
                if (!empty($data['user_id']) && !empty($data['date'])) {
                    $overlaps = $this->realizationModel->checkOverlap($data['user_id'], $data['date']);
                    if ($this->realizationModel->create($data)) {
                        $this->rabModel->markAsCompleted($rab_id);
                        $logNote = "Assigned user ID " . $data['user_id'] . " for date " . $data['date'];
                        if (count($overlaps) > 0) {
                            $overlap_projects = [];
                            foreach($overlaps as $ov) {
                                $overlap_projects[] = $ov['nama_project'];
                            }
                            $msg = "Overlap detected! User is also assigned to: " . implode(', ', $overlap_projects) . " on " . $data['date'];
                            $_SESSION['overlap_warning'] = $msg;
                            $logNote .= ". " . $msg;
                        }
                        $this->projectModel->logAction($rab['project_id'], 'Medical Realization Added', $_SESSION['user_id'], $logNote);
                    }
                }
            }

            header("Location: index.php?page=rab_medical_view&id=" . $rab_id);
        }
    }

    public function delete_realization() {
        // Access Check
        if (!in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])) {
            header("Location: index.php?page=rab_medical_index");
            return;
        }

        if ($_GET['id'] && $_GET['rab_id']) {
            $id = $_GET['id'];
            $rab_id = $_GET['rab_id'];
            
            $rab = $this->rabModel->getById($rab_id);

            if ($this->realizationModel->delete($id)) {
                $this->projectModel->logAction($rab['project_id'], 'Medical Realization Deleted', $_SESSION['user_id'], "Realization ID $id deleted.");
            }
            header("Location: index.php?page=rab_medical_view&id=" . $rab_id);
        }
    }
}
