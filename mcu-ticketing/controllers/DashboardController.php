<?php


class DashboardController extends BaseController {
    private $project;

    public function __construct() {
        parent::__construct();
        $this->project = $this->loadModel('Project');
    }

    public function index() {

        if ($_SESSION['role'] == 'procurement') {
            $stats = $this->project->getProcurementStats();
            $this->view('dashboard/procurement', ['stats' => $stats]);
            return;
        }

        $korlap_id = ($_SESSION['role'] == 'korlap') ? $_SESSION['user_id'] : null;
        
        $role = null;
        $user_id = null;
        if ($_SESSION['role'] == 'sales' || $_SESSION['role'] == 'manager_sales' || $_SESSION['role'] == 'dw_tim_hasil') {
            $role = $_SESSION['role'];
            $user_id = $_SESSION['user_id'];
        }

        $stats = $this->project->getStatistics($korlap_id, $role, $user_id);
        $vendor_stats = $this->project->getVendorStats();
        $monthly_stats = $this->project->getMonthlyProjectStats($korlap_id, $role, $user_id);
        
        // Add new widgets for specific roles (include CEO for view-only)
        $allowed_roles = ['superadmin', 'manager_ops', 'head_ops', 'admin_ops', 'admin_sales', 'ceo', 'finance', 'admin_gudang_warehouse', 'admin_gudang_aset', 'sales_support_supervisor', 'sales_performance_manager'];
        if (in_array($_SESSION['role'], $allowed_roles)) {
            $sales_leaderboard = $this->project->getSalesLeaderboard();
            $upcoming_projects = $this->project->getUpcomingProjects();
        }

        if (in_array($_SESSION['role'], ['admin_gudang_warehouse', 'admin_gudang_aset'])) {
            $all_projects_stmt = $this->project->readAll($_SESSION['role']);
            $all_projects_dashboard = $all_projects_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $this->view('dashboard/index', [
            'stats' => $stats,
            'vendor_stats' => $vendor_stats,
            'monthly_stats' => $monthly_stats,
            'sales_leaderboard' => $sales_leaderboard ?? null,
            'upcoming_projects' => $upcoming_projects ?? null,
            'all_projects_dashboard' => $all_projects_dashboard ?? []
        ]);
    }

    public function getCalendarEvents() {
        $korlap_id = ($_SESSION['role'] == 'korlap') ? $_SESSION['user_id'] : null;
        
        $role = null;
        $user_id = null;
        if ($_SESSION['role'] == 'sales' || $_SESSION['role'] == 'manager_sales' || $_SESSION['role'] == 'dw_tim_hasil') {
            $role = $_SESSION['role'];
            $user_id = $_SESSION['user_id'];
        }

        $stmt = $this->project->getAllForCalendar($korlap_id, $role, $user_id);
        $events = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Parse JSON dates
            $dates = json_decode($row['tanggal_mcu']);
            if (json_last_error() !== JSON_ERROR_NONE && !is_array($dates)) {
                // Handle plain string or single date if necessary, assuming array for now per requirements
                $dates = [$row['tanggal_mcu']]; 
            }

            $color = '#204EAB'; // Default Blue (Normal)
            
            if ($row['status_project'] == 're-nego' || $row['status_project'] == 'cancelled' || $row['status_project'] == 'rejected') {
                $color = '#dc3545'; // Red
            } elseif ($row['status_project'] == 'approved') {
                $color = '#204EAB';
            } elseif (strpos($row['status_project'], 'need_approval') !== false) {
                $color = '#fd7e14'; // Orange
            } elseif ($row['status_project'] == 'completed') {
                $color = '#198754'; // Green
            }

            if (is_array($dates)) {
                foreach ($dates as $date) {
                    $events[] = [
                        'id' => $row['project_id'], // Add ID for click event
                        'title' => $row['nama_project'],
                        'start' => $date,
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                        'extendedProps' => [
                            'status' => $row['status_project'],
                            'lunch' => $row['lunch'],
                            'lunch_notes' => $row['lunch_notes'],
                            'snack' => $row['snack'],
                            'snack_notes' => $row['snack_notes']
                        ]
                    ];
                }
            }
        }

        $this->jsonResponse($events);
    }
}
