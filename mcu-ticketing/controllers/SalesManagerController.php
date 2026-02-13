<?php
include_once '../config/database.php';
include_once '../models/SalesManager.php';

class SalesManagerController extends BaseController {
    private $salesManager;

    public function __construct() {
        parent::__construct();
        $this->salesManager = $this->loadModel('SalesManager');
        $this->checkPermission();
    }

    private function checkPermission() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'superadmin' && $_SESSION['role'] != 'admin_sales')) {
            header("Location: index.php?page=login");
            exit;
        }
    }

    public function index() {
        // 1. Get All Managers
        $queryManagers = "SELECT * FROM sales_managers ORDER BY name ASC";
        $stmtManagers = $this->db->prepare($queryManagers);
        $stmtManagers->execute();
        $managers = $stmtManagers->fetchAll(PDO::FETCH_ASSOC);

        // 2. Get All Sales Persons with aggregated stats
        // We use LEFT JOIN to ensure we get sales persons even if they have no projects
        $querySales = "SELECT 
                        sp.id, 
                        sp.sales_name, 
                        sp.sales_manager_id, 
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
                            tanggal_mcu
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
                'manager' => ['id' => 0, 'manager_name' => 'Unassigned Sales Persons', 'user_id' => null],
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

        // Include the View
        include '../views/sales_persons/dashboard.php';
    }

    public function create() {
        // Fetch available users (Role: manager_sales) who are not yet linked to a sales manager
        $query = "SELECT user_id, full_name, username FROM users WHERE role = 'manager_sales' AND user_id NOT IN (SELECT COALESCE(user_id,0) FROM sales_managers) ORDER BY full_name ASC";
        $stmtUsers = $this->db->prepare($query);
        $stmtUsers->execute();
        $available_users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        include '../views/sales_managers/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 die("Invalid CSRF token.");
            }
            // If manager_name is empty but user_id is provided, fetch name from user
            if (empty($_POST['manager_name']) && !empty($_POST['user_id'])) {
                $query = "SELECT full_name FROM users WHERE user_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$_POST['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $_POST['manager_name'] = $user['full_name'];
                }
            }

            $this->salesManager->manager_name = $_POST['manager_name'] ?? '';
            $this->salesManager->user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;

            if ($this->salesManager->create()) {
            $_SESSION['success_message'] = "Sales Manager created successfully.";
            header("Location: index.php?page=sales_persons_index");
        } else {
            $_SESSION['error_message'] = "Unable to create sales manager.";
            header("Location: index.php?page=sales_persons_index");
        }
        }
    }

    public function edit() {
        $id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');
        $this->salesManager->id = $id;
        $this->salesManager->getById();

        // Fetch available users + current user
        $currentUserClause = "";
        $params = [];
        if (!empty($this->salesManager->user_id)) {
            $currentUserClause = " OR user_id = ?";
            $params[] = $this->salesManager->user_id;
        }
        
        $query = "SELECT user_id, full_name, username FROM users WHERE (role = 'manager_sales' AND user_id NOT IN (SELECT COALESCE(user_id,0) FROM sales_managers)) $currentUserClause ORDER BY full_name ASC";
        $stmtUsers = $this->db->prepare($query);
        $stmtUsers->execute($params);
        $available_users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        include '../views/sales_managers/edit.php';
    }

    public function update() {
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
                    $_POST['manager_name'] = $user['full_name'];
                }
            }

            $this->salesManager->id = $_POST['id'];
            $this->salesManager->manager_name = $_POST['manager_name'];
            $this->salesManager->user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;

            if ($this->salesManager->update()) {
                $_SESSION['success_message'] = "Sales Manager updated successfully.";
                header("Location: index.php?page=sales_persons_index");
            } else {
                $_SESSION['error_message'] = "Unable to update sales manager.";
                header("Location: index.php?page=sales_persons_index");
            }
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $this->salesManager->id = $_GET['id'];
            if ($this->salesManager->delete()) {
                $_SESSION['success_message'] = "Sales Manager deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Unable to delete sales manager.";
            }
            header("Location: index.php?page=sales_persons_index");
        }
    }
}
?>