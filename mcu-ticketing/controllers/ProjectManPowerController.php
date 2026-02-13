<?php
class ProjectManPowerController extends BaseController {
    private $projectManPower;
    private $manPower;
    private $project;
    private $rab;
    private $setting;

    public function __construct() {
        parent::__construct();
        // Access: Superadmin, Admin Ops, Manager, Head
        $this->checkRole(['superadmin', 'admin_ops', 'manager_ops', 'head_ops']);
        
        $this->projectManPower = $this->loadModel('ProjectManPower');
        $this->manPower = $this->loadModel('ManPower');
        $this->project = $this->loadModel('Project');
        $this->rab = $this->loadModel('Rab');
        $this->setting = $this->loadModel('SystemSetting');
    }

    public function index() {
        $page_title = "Man Power MCU - Project List";
        
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        
        // Use existing available projects logic but filter for those with RAB
        // Ideally we want projects that have an Approved RAB
        // For simplicity, reusing Rab->getAvailableProjects or Project->readAll
        // But we specifically need projects where Man Power is relevant (likely Approved Projects)
        
        $stmt = $this->project->readAll($role, $user_id); 
        // We might want to filter this further in the view or here
        $all_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filter: Only approved projects or those in progress
        $projects = [];
        foreach ($all_projects as $p) {
            if (in_array($p['status_project'], ['approved', 'in_progress_ops', 'completed'])) {
                $projects[] = $p;
            }
        }

        $this->view('project_man_power/index', [
            'projects' => $projects
        ]);
    }

    public function detail() {
        if (!isset($_GET['project_id'])) {
            header("Location: index.php?page=man_power_mcu");
            exit;
        }

        $project_id = $_GET['project_id'];
        $project = $this->project->getById($project_id);
        
        if (!$project) {
            echo "Project not found.";
            return;
        }

        // 1. Get Project Dates (Aggregated from Project + all approved Rabs)
        $project_dates = [];
        if (!empty($project['tanggal_mcu'])) {
            if (strpos($project['tanggal_mcu'], '[') === 0) {
                $decoded = json_decode($project['tanggal_mcu'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $project_dates = $decoded;
                }
            } else {
                $project_dates = [$project['tanggal_mcu']];
            }
        }

        // 2. Get ALL relevant Rabs for this project to aggregate dates and requirements
        $db = (new Database())->getConnection();
        $query = "SELECT * FROM rabs 
                  WHERE project_id = :project_id 
                  AND status IN ('approved', 'submitted_to_finance', 'advance_paid', 'need_approval_realization', 'realization_approved', 'completed') 
                  ORDER BY id ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        $all_rabs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rab_requirements = [];
        $role_qty_map = []; // Aggregated Role -> Total Qty across all Rabs
        
        foreach ($all_rabs as $r) {
            // Aggregate dates from Rab
            $r_dates = json_decode($r['selected_dates'], true);
            if (is_array($r_dates)) {
                $project_dates = array_merge($project_dates, $r_dates);
            }

            // Aggregate personnel items from Rab
            $items = $this->rab->getItems($r['id']);
            foreach ($items as $item) {
                if ($item['category'] == 'personnel') {
                    $role = $item['item_name'];
                    $qty = (int)$item['qty'];
                    $days = (int)$item['days'];
                    
                    if (!isset($role_qty_map[$role])) {
                        $role_qty_map[$role] = 0;
                    }
                    $role_qty_map[$role] += ($qty * $days);
                }
            }
        }
        
        // Finalize project dates
        $project_dates = array_unique($project_dates);
        sort($project_dates);

        // Convert role_qty_map to expected format for the view
        foreach ($role_qty_map as $role => $qty) {
            $rab_requirements[] = [
                'item_name' => $role,
                'qty' => $qty
            ];
        }

        // Keep the latest Rab for reference if needed (optional compatibility)
        $rab = !empty($all_rabs) ? end($all_rabs) : null;

        // 3. Get Existing Assignments
        $assignments = $this->projectManPower->getAssignments($project_id);
        $summary = $this->projectManPower->getSummaryByDate($project_id);

        // 4. Get Master Man Power
        $man_powers = $this->manPower->getActiveManPower();
        
        // Decode skills for UI logic
        foreach ($man_powers as &$mp) {
            $mp['skills_array'] = json_decode($mp['skills'], true) ?? [];
        }

        $can_edit = in_array($_SESSION['role'], ['superadmin', 'admin_ops']);

        $this->view('project_man_power/detail', [
            'project' => $project,
            'project_dates' => $project_dates,
            'rab' => $rab,
            'rab_requirements' => $rab_requirements,
            'assignments' => $assignments,
            'summary' => $summary,
            'man_powers' => $man_powers,
            'can_edit' => $can_edit
        ]);
    }

    public function store() {
        $this->checkRole(['superadmin', 'admin_ops']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $project_id = $_POST['project_id'];
            $man_power_id = $_POST['man_power_id'];
            $dates = $_POST['dates'] ?? []; // Array of dates
            
            // Add manual date if provided
            if (!empty($_POST['manual_date'])) {
                $dates[] = $_POST['manual_date'];
            }
            $dates = array_unique($dates); // Prevent duplicates

            $role = $_POST['role'];
            $notes = $_POST['notes'];
            
            // Handle Doctor specific details
            $doctor_details = null;
            if ($role === 'Dokter' && isset($_POST['activity_types']) && is_array($_POST['activity_types'])) {
                $details = [];
                foreach ($_POST['activity_types'] as $type) {
                    // Handle keys like "Health Talk" -> "Health_Talk" for post vars if needed, 
                    // but usually radio buttons name can contain spaces if we use quotes, 
                    // but better to use underscore in name attribute in HTML.
                    // Let's assume HTML uses name="method_Pemaparan" etc.
                    $method_key = 'method_' . str_replace(' ', '_', $type); 
                    $method = $_POST[$method_key] ?? null;
                    $details[] = [
                        'type' => $type,
                        'method' => $method
                    ];
                }
                if (!empty($details)) {
                    $doctor_details = json_encode($details);
                }
            }

            if (empty($dates) || empty($man_power_id) || empty($role)) {
                $this->redirect('man_power_detail&project_id=' . $project_id . '&error=Missing required fields');
            }

            $errors = [];
            $success_count = 0;

            foreach ($dates as $date) {
                $data = [
                    'project_id' => $project_id,
                    'man_power_id' => $man_power_id,
                    'role' => $role,
                    'date' => $date,
                    'notes' => $notes,
                    'doctor_details' => $doctor_details,
                    'created_by' => $_SESSION['user_id']
                ];
                
                $result = $this->projectManPower->assign($data);
                if (!$result['status']) {
                    $errors[] = "Date $date: " . $result['message'];
                } else {
                    $success_count++;
                }
            }

            $msg = "$success_count assigned.";
            if (!empty($errors)) {
                $msg .= " Errors: " . implode(", ", $errors);
            }

            header("Location: index.php?page=man_power_detail&project_id=" . $project_id . "&msg=" . urlencode($msg));
        }
    }

    public function delete() {
        $this->checkRole(['superadmin', 'admin_ops']);
        
        if (isset($_GET['id']) && isset($_GET['project_id'])) {
            $this->projectManPower->delete($_GET['id']);
            header("Location: index.php?page=man_power_detail&project_id=" . $_GET['project_id']);
        }
    }

    public function heatmap() {
        $page_title = "Availability Heatmap";
        
        // 1. Setup Filters
        $start_date = $_GET['start_date'] ?? date('Y-m-d');
        $end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $role_filter = $_GET['role'] ?? '';
        $status_filter = $_GET['status'] ?? ''; // e.g. 'External'

        // 2. Get Skills
        $all_skills = $this->getAvailableSkills();

        // 3. Get Active Man Power
        $all_mps = $this->manPower->getActiveManPower();
        
        // 4. Calculate Initial Capacity per Skill
        // Map: SkillName -> [mp_id1, mp_id2, ...]
        $skill_capacity = [];
        $mp_details = []; // Cache MP details for quick lookup

        foreach ($all_mps as $mp) {
            $mp_details[$mp['id']] = $mp;
            
            // Case-insensitive check for status
            if ($status_filter && strcasecmp(trim($mp['status']), trim($status_filter)) !== 0) continue;

            $mp_skills = json_decode($mp['skills'], true) ?? [];
            foreach ($mp_skills as $skill) {
                // Filter by role if set
                if ($role_filter && $skill !== $role_filter) continue;
                
                if (!isset($skill_capacity[$skill])) {
                    $skill_capacity[$skill] = [];
                }
                $skill_capacity[$skill][] = $mp['id'];
            }
        }

        // 5. Get Assignments
        $assignments = $this->projectManPower->getAssignmentsByDateRange($start_date, $end_date);
        
        // 6. Map Assignments: Date -> ManPowerID -> Assignment Details
        $assigned_map = [];
        foreach ($assignments as $asn) {
            $date = $asn['date'];
            $mp_id = $asn['man_power_id'];
            
            if (!isset($assigned_map[$date])) {
                $assigned_map[$date] = [];
            }
            $assigned_map[$date][$mp_id] = $asn;
        }

        // 7. Build Heatmap Data
        // Date -> Skill -> { capacity: N, used: N, available: N, details: ... }
        $heatmap_data = [];
        $dates = [];
        
        try {
            $period = new DatePeriod(
                new DateTime($start_date),
                new DateInterval('P1D'),
                (new DateTime($end_date))->modify('+1 day')
            );

            foreach ($period as $dt) {
                $date_str = $dt->format('Y-m-d');
                $dates[] = $date_str;
                $heatmap_data[$date_str] = [];

                foreach ($all_skills as $skill_item) {
                    $skill_name = $skill_item['name'];
                    if ($role_filter && $skill_name !== $role_filter) continue;

                    $eligible_mps = $skill_capacity[$skill_name] ?? [];
                    $total_capacity = count($eligible_mps);
                    
                    $used_count = 0;
                    $used_details = [];
                    $available_details = [];
                    
                    foreach ($eligible_mps as $mp_id) {
                        if (isset($assigned_map[$date_str][$mp_id])) {
                            $used_count++;
                            $used_details[] = $assigned_map[$date_str][$mp_id];
                        } else {
                            // Available
                            $available_details[] = [
                                'id' => $mp_id,
                                'name' => $mp_details[$mp_id]['name'],
                                'status' => $mp_details[$mp_id]['status']
                            ];
                        }
                    }
                    
                    $available = $total_capacity - $used_count;
                    
                    // Determine Status Color
                    // You might want to make thresholds configurable later
                    $status = 'safe';
                    if ($available <= 0) {
                        $status = 'critical';
                    } elseif ($available < 3) { // Warning threshold
                        $status = 'warning';
                    }
                    
                    $heatmap_data[$date_str][$skill_name] = [
                        'capacity' => $total_capacity,
                        'used' => $used_count,
                        'available' => $available,
                        'status' => $status,
                        'used_details' => $used_details,
                        'available_details' => $available_details
                    ];
                }
            }
        } catch (Exception $e) {
            // Handle date errors
        }

        $this->view('project_man_power/heatmap', [
            'heatmap_data' => $heatmap_data,
            'skills' => $all_skills,
            'dates' => $dates,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'filters' => ['role' => $role_filter, 'status' => $status_filter]
        ]);
    }

    public function export() {
        // Access Check
        $this->checkRole(['superadmin', 'admin_ops', 'manager_ops', 'head_ops']);

        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $include_internal = isset($_GET['include_internal']) && $_GET['include_internal'] == '1';

        if (empty($start_date) || empty($end_date)) {
            die("Start Date and End Date are required.");
        }

        // Fetch Data
        $db = (new Database())->getConnection();
        
        $sql = "SELECT 
                    p.nama_project, 
                    pmp.date as assignment_date, 
                    mp.name as man_power_name, 
                    pmp.role, 
                    pmp.notes,
                    mp.status as man_power_status
                FROM project_man_power pmp
                JOIN projects p ON p.project_id = pmp.project_id
                JOIN man_powers mp ON mp.id = pmp.man_power_id
                WHERE pmp.date BETWEEN :start_date AND :end_date";

        if (!$include_internal) {
            $sql .= " AND mp.status != 'internal'";
        }

        $sql .= " ORDER BY p.nama_project ASC, pmp.date ASC, pmp.role ASC, mp.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return JSON for Frontend processing
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'include_internal' => $include_internal
        ]);
        exit;
    }

    private function getAvailableSkills() {
        // Fetch from RAB Configuration (personnel codes)
        $mappingStr = $this->setting->get('rab_personnel_codes');
        
        // Try parsing as JSON first
        $mapping = json_decode($mappingStr, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Parse as key=value lines
            $mapping = [];
            $lines = explode("\n", str_replace("\r", "", $mappingStr));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $parts = explode('=', $line, 2);
                if (count($parts) == 2) {
                    $mapping[trim($parts[0])] = trim($parts[1]);
                }
            }
        }
        
        $items = [];
        foreach ($mapping as $role_key => $lookup_value) {
            $items[] = [
                'name' => $lookup_value,
                'role_key' => $role_key
            ];
        }
        
        usort($items, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $items;
    }
}
?>