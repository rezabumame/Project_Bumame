<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/SalesPerson.php';
include_once __DIR__ . '/../models/SalesManager.php';
include_once __DIR__ . '/../models/Project.php';
include_once __DIR__ . '/../helpers/DateHelper.php';

class SalesPersonController extends BaseController {
    private $salesPerson;
    private $salesManager;
    private $project;

    public function __construct() {
        parent::__construct();
        $this->salesPerson = $this->loadModel('SalesPerson');
        $this->salesManager = $this->loadModel('SalesManager');
        $this->project = $this->loadModel('Project');
    }

    private function checkPermission() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin', 'admin_sales', 'sales_support_supervisor', 'sales_performance_manager'])) {
            header("Location: index.php?page=dashboard");
            exit;
        }
    }
    
    private function checkViewPermission() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin', 'admin_sales', 'ceo', 'sales_support_supervisor', 'sales_performance_manager'])) {
            header("Location: index.php?page=dashboard");
            exit;
        }
    }

    public function index() {
        $this->checkViewPermission();
        
        // --- LOGIC FOR PERFORMANCE DASHBOARD ---

        // 1. Get All Managers
        $stmtManager = $this->salesManager->readAll();
        $managers = $stmtManager->fetchAll(PDO::FETCH_ASSOC);

        // 2. Get All Sales Persons with aggregated stats
        // We use LEFT JOIN to ensure we get sales persons even if they have no projects
        $querySales = "SELECT 
                        sp.id, 
                        sp.sales_name, 
                        sp.sales_manager_id, 
                        sp.user_id,
                        COUNT(p.project_id) as total_projects, 
                        COALESCE(SUM(p.total_peserta), 0) as total_pax 
                      FROM sales_persons sp 
                      LEFT JOIN projects p ON sp.id = p.sales_person_id 
                      GROUP BY sp.id 
                      ORDER BY total_pax DESC"; // Order by Pax for easy ranking
        $stmtSales = $this->db->prepare($querySales);
        $stmtSales->execute();
        $salesPersons = $stmtSales->fetchAll(PDO::FETCH_ASSOC);

        // 3. Get Project Details for the list view
        // Only fetching necessary fields to keep it light
        $queryProjects = "SELECT 
                            project_id, 
                            nama_project, 
                            total_peserta, 
                            status_project, 
                            sales_person_id,
                            tanggal_mcu,
                            created_at
                          FROM projects 
                          ORDER BY created_at DESC";
        $stmtProjects = $this->db->prepare($queryProjects);
        $stmtProjects->execute();
        $allProjects = $stmtProjects->fetchAll(PDO::FETCH_ASSOC);

        // 4. Build Hierarchy & Calculate Global Stats
        $hierarchy = [];
        $globalStats = [
            'total_projects' => 0,
            'total_pax' => 0,
            'total_managers' => count($managers),
            'total_sales' => count($salesPersons)
        ];

        // Map Sales Persons by ID for easy lookup
        $salesMap = [];
        foreach ($salesPersons as $sp) {
            $sp['projects'] = [];
            $salesMap[$sp['id']] = $sp;
        }

        // Distribute Projects to Sales Persons
        foreach ($allProjects as $proj) {
            if (isset($salesMap[$proj['sales_person_id']])) {
                $salesMap[$proj['sales_person_id']]['projects'][] = $proj;
                // Update Global Stats
                $globalStats['total_projects']++;
                $globalStats['total_pax'] += $proj['total_peserta'];
            }
        }

        // Group Sales Persons by Manager
        $groupedSales = [];
        foreach ($salesMap as $sp) {
            $mgrId = $sp['sales_manager_id'] ? $sp['sales_manager_id'] : 'uncategorized';
            $groupedSales[$mgrId][] = $sp;
        }

        // Build Final Hierarchy structure with Manager Stats
        foreach ($managers as $mgr) {
            $mgrId = $mgr['id'];
            $team = isset($groupedSales[$mgrId]) ? $groupedSales[$mgrId] : [];
            
            // Calculate Manager Stats
            $mgrTotalProjects = 0;
            $mgrTotalPax = 0;
            $topSalesByPax = null;
            $topSalesByProject = null;

            foreach ($team as $member) {
                $mgrTotalProjects += $member['total_projects'];
                $mgrTotalPax += $member['total_pax'];
            }

            // Determine Top Performer in Team (Gimmick)
            if (!empty($team)) {
                // Sort by Pax for Top Pax
                usort($team, function($a, $b) { return $b['total_pax'] - $a['total_pax']; });
                $topSalesByPax = $team[0];

                // Sort by Project Count for Most Active
                $teamByProj = $team;
                usort($teamByProj, function($a, $b) { return $b['total_projects'] - $a['total_projects']; });
                $topSalesByProject = $teamByProj[0];
            }

            $hierarchy[] = [
                'manager' => $mgr,
                'team' => $team, // Note: $team is sorted by Pax desc here
                'stats' => [
                    'total_projects' => $mgrTotalProjects,
                    'total_pax' => $mgrTotalPax,
                    'top_pax_sales' => $topSalesByPax,
                    'top_project_sales' => $topSalesByProject
                ]
            ];
        }

        // Handle Uncategorized Sales Persons (if any)
        if (isset($groupedSales['uncategorized']) && count($groupedSales['uncategorized']) > 0) {
            $team = $groupedSales['uncategorized'];
            $totalProjects = 0;
            $totalPax = 0;
             foreach ($team as $member) {
                $totalProjects += $member['total_projects'];
                $totalPax += $member['total_pax'];
            }
            
            // Sort by Pax
            usort($team, function($a, $b) { return $b['total_pax'] - $a['total_pax']; });

            $hierarchy[] = [
                'manager' => ['id' => 0, 'manager_name' => 'Unassigned Sales Persons', 'user_id' => null, 'name' => 'Unassigned Sales Persons'],
                'team' => $team,
                'stats' => [
                    'total_projects' => $totalProjects,
                    'total_pax' => $totalPax,
                    'top_pax_sales' => $team[0],
                    'top_project_sales' => null // Skip for unassigned
                ]
            ];
        }

        // Determine Global Top Performers (Gimmick)
        $globalTopPax = null;
        $globalTopProjects = null;

        if (!empty($salesPersons)) {
            // Sort all sales by Pax
            usort($salesPersons, function($a, $b) { return $b['total_pax'] - $a['total_pax']; });
            $globalTopPax = $salesPersons[0];

            // Sort all sales by Projects
            usort($salesPersons, function($a, $b) { return $b['total_projects'] - $a['total_projects']; });
            $globalTopProjects = $salesPersons[0];
        }

        // --- END PERFORMANCE LOGIC ---

        // Fetch ALL users for dropdowns (we will filter in view/JS)
        $querySalesUsers = "SELECT user_id, full_name, username FROM users WHERE role = 'sales' ORDER BY full_name ASC";
        $stmtSalesUsers = $this->db->prepare($querySalesUsers);
        $stmtSalesUsers->execute();
        $all_sales_users = $stmtSalesUsers->fetchAll(PDO::FETCH_ASSOC);

        $queryManagerUsers = "SELECT user_id, full_name, username FROM users WHERE role = 'manager_sales' ORDER BY full_name ASC";
        $stmtManagerUsers = $this->db->prepare($queryManagerUsers);
        $stmtManagerUsers->execute();
        $all_manager_users = $stmtManagerUsers->fetchAll(PDO::FETCH_ASSOC);

        // Extract assigned user IDs for JS
        $assigned_sales_user_ids = array_filter(array_column($salesPersons, 'user_id'));
        $assigned_manager_user_ids = array_filter(array_column($managers, 'user_id'));
        
        include '../views/sales_persons/dashboard.php';
    }

    public function create() {
        $this->checkPermission();
        // Fetch managers for dropdown
        $stmt = $this->salesManager->readAll();
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch available users (Role: sales) who are not yet linked to a sales person
        $query = "SELECT user_id, full_name, username FROM users WHERE role IN ('sales', 'sales_support_supervisor', 'sales_performance_manager') AND user_id NOT IN (SELECT COALESCE(user_id,0) FROM sales_persons) ORDER BY full_name ASC";
        $stmtUsers = $this->db->prepare($query);
        $stmtUsers->execute();
        $available_users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        include '../views/sales_persons/create.php';
    }

    public function store() {
        $this->checkPermission();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 die("Invalid CSRF token.");
            }
            // If sales_name is empty but user_id is provided, fetch name from user
            if (empty($_POST['sales_name']) && !empty($_POST['user_id'])) {
                $query = "SELECT full_name FROM users WHERE user_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$_POST['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $_POST['sales_name'] = $user['full_name'];
                }
            }

            $this->salesPerson->sales_name = $_POST['sales_name'] ?? '';
            $this->salesPerson->sales_manager_id = !empty($_POST['sales_manager_id']) ? $_POST['sales_manager_id'] : null;
            $this->salesPerson->user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;

            if ($this->salesPerson->create()) {
                header("Location: index.php?page=sales_persons_index&status=success");
            } else {
                header("Location: index.php?page=sales_persons_create&status=error");
            }
        }
    }

    public function edit() {
        $this->checkPermission();
        if (isset($_GET['id'])) {
            $this->salesPerson->id = $_GET['id'];
            if ($this->salesPerson->getById()) {
                // Fetch managers for dropdown
                $stmt = $this->salesManager->readAll();
                $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Fetch available users + current user
                $currentUserClause = "";
                $params = [];
                if (!empty($this->salesPerson->user_id)) {
                    $currentUserClause = " OR user_id = ?";
                    $params[] = $this->salesPerson->user_id;
                }
                
                $query = "SELECT user_id, full_name, username FROM users WHERE (role IN ('sales', 'sales_support_supervisor', 'sales_performance_manager') AND user_id NOT IN (SELECT COALESCE(user_id,0) FROM sales_persons)) $currentUserClause ORDER BY full_name ASC";
                $stmtUsers = $this->db->prepare($query);
                $stmtUsers->execute($params);
                $available_users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

                // Pass the sales person object to the view
                $sales = $this->salesPerson; 
                include '../views/sales_persons/edit.php';
            } else {
                header("Location: index.php?page=sales_persons_index");
            }
        } else {
            header("Location: index.php?page=sales_persons_index");
        }
    }

    public function update() {
        $this->checkPermission();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 die("Invalid CSRF token.");
            }
            // If user_id is provided, fetch name from user to ensure consistency
            if (!empty($_POST['user_id'])) {
                $query = "SELECT full_name FROM users WHERE user_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$_POST['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $_POST['sales_name'] = $user['full_name'];
                }
            }

            $this->salesPerson->id = $_POST['id'];
            $this->salesPerson->sales_name = $_POST['sales_name'];
            $this->salesPerson->sales_manager_id = !empty($_POST['sales_manager_id']) ? $_POST['sales_manager_id'] : null;
            $this->salesPerson->user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;

            if ($this->salesPerson->update()) {
                header("Location: index.php?page=sales_persons_index&status=updated");
            } else {
                header("Location: index.php?page=sales_persons_edit&id=".$_POST['id']."&status=error");
            }
        }
    }

    public function delete() {
        $this->checkPermission();
        if (isset($_GET['id'])) {
            $this->salesPerson->id = $_GET['id'];
            if ($this->salesPerson->delete()) {
                header("Location: index.php?page=sales_persons_index&status=deleted");
            } else {
                header("Location: index.php?page=sales_persons_index&status=error");
            }
        }
    }

    public function get_projects() {
        $this->checkViewPermission(); // Use existing check: superadmin, admin_sales, ceo
        if (isset($_GET['id'])) {
            $sales_person_id = $_GET['id'];
            
            // Pagination parameters
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000; // Default high limit for backward compatibility if needed, but we will use 5
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            // Using raw query for simplicity as readAllBySalesPerson might not exist in Project model
            $query = "SELECT project_id, nama_project, status_project, total_peserta, tanggal_mcu FROM projects WHERE sales_person_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $sales_person_id);
            $stmt->bindParam(2, $limit, PDO::PARAM_INT);
            $stmt->bindParam(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format data
            foreach ($projects as &$project) {
                // Date Helper
                $project['tanggal_mcu_formatted'] = DateHelper::formatSmartDate($project['tanggal_mcu']);
                
                // Status Badge Color
                $statusColor = 'secondary';
                switch ($project['status_project']) {
                    case 'won': $statusColor = 'success'; break;
                    case 'lost': $statusColor = 'danger'; break;
                    case 'hot': $statusColor = 'warning'; break;
                    case 'warm': $statusColor = 'info'; break;
                    case 'cold': $statusColor = 'secondary'; break;
                }
                $project['status_badge'] = "<span class='badge bg-{$statusColor}'>" . ucfirst($project['status_project']) . "</span>";
            }

            header('Content-Type: application/json');
            echo json_encode(['data' => $projects]);
        }
    }
}
?>