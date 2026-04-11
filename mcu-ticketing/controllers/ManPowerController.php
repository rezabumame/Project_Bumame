<?php
class ManPowerController extends BaseController {
    private $manPower;
    private $setting;
    private $costCode;

    public function __construct() {
        parent::__construct();
        $this->checkRole(['superadmin', 'admin_ops', 'manager_ops', 'head_ops']);
        
        $this->manPower = $this->loadModel('ManPower');
        $this->setting = $this->loadModel('SystemSetting');
        $this->costCode = $this->loadModel('CostCode');
    }

    public function index() {
        $page_title = "Staff Management";
        
        // Temporary fix for data casing issue
        if (isset($_GET['fix_data']) && $_SESSION['role'] === 'superadmin') {
            $this->runDataFix();
        }

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status_filter'] ?? '',
            'skill' => $_GET['skill_filter'] ?? ''
        ];
        
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $stmt = $this->manPower->getAll($filters, $limit, $offset);
        $man_powers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_rows = $this->manPower->countAll($filters);
        $total_pages = ceil($total_rows / $limit);

        // Decode skills for display
        foreach ($man_powers as &$mp) {
            $mp['skills_array'] = json_decode($mp['skills'], true) ?? [];
        }

        $can_edit = in_array($_SESSION['role'], ['superadmin', 'admin_ops']);
        $available_skills = $this->getAvailableSkills();

        $this->view('man_power_management/index', [
            'man_powers' => $man_powers,
            'filters' => $filters,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'can_edit' => $can_edit,
            'available_skills' => $available_skills
        ]);
    }

    public function create() {
        $this->checkRole(['superadmin', 'admin_ops']);
        
        $page_title = "Add New Staff";
        $skills = $this->getAvailableSkills();

        $this->view('man_power_management/create', [
            'skills' => $skills
        ]);
    }

    public function store() {
        $this->checkRole(['superadmin', 'admin_ops']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->manPower->name = $_POST['name'];
            // Status from switch/checkbox: if checked it sends 'Internal' (or 'on'), else 'External'
            $status = $_POST['status'] ?? 'External';
            $this->manPower->status = ($status === 'on' || $status === 'Internal') ? 'Internal' : 'External';
            $this->manPower->email = $_POST['email'];
            $this->manPower->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Handle skills
            $skills = $_POST['skills'] ?? [];
            $this->manPower->skills = json_encode($skills);

            if ($this->manPower->create()) {
                header("Location: index.php?page=man_power_management");
            } else {
                echo "Error creating Man Power.";
            }
        }
    }

    public function edit() {
        $this->checkRole(['superadmin', 'admin_ops']);
        
        $id = $_GET['id'];
        $man_power = $this->manPower->getById($id);
        
        if (!$man_power) {
            echo "Man Power not found.";
            return;
        }

        $man_power['skills_array'] = json_decode($man_power['skills'], true) ?? [];
        $skills = $this->getAvailableSkills();

        $this->view('man_power_management/edit', [
            'man_power' => $man_power,
            'skills' => $skills
        ]);
    }

    public function update() {
        $this->checkRole(['superadmin', 'admin_ops']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->manPower->id = $_POST['id'];
            $this->manPower->name = $_POST['name'];
            // Status from switch: if checked it sends 'Internal', else 'External'
            $status = $_POST['status'] ?? 'External';
            $this->manPower->status = ($status === 'on' || $status === 'Internal') ? 'Internal' : 'External';
            $this->manPower->email = $_POST['email'];
            $this->manPower->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $skills = $_POST['skills'] ?? [];
            $this->manPower->skills = json_encode($skills);

            if ($this->manPower->update()) {
                header("Location: index.php?page=man_power_management");
            } else {
                echo "Error updating Man Power.";
            }
        }
    }

    private function runDataFix() {
        // 1. Get official skills
        $mappingStr = $this->setting->get('rab_personnel_codes');
        $officialSkills = [];
        $lines = explode("\n", str_replace("\r", "", $mappingStr));
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) == 2) {
                $officialSkills[] = trim($parts[1]);
            }
        }

        // 2. Fetch all man_powers
        $stmt = $this->manPower->getAll('', 9999, 0);
        $man_powers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updatedCount = 0;
        foreach ($man_powers as $mp) {
            $needsUpdate = false;
            
            // Normalize status
            $newStatus = ucfirst(strtolower($mp['status']));
            if ($newStatus !== $mp['status']) {
                $needsUpdate = true;
            }

            // Normalize skills
            $skills = json_decode($mp['skills'], true) ?? [];
            $newSkills = [];
            foreach ($skills as $s) {
                $matched = false;
                foreach ($officialSkills as $os) {
                    if (strcasecmp($s, $os) == 0) {
                        $newSkills[] = $os;
                        if ($s !== $os) $needsUpdate = true;
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) $newSkills[] = $s;
            }

            if ($needsUpdate) {
                $this->manPower->id = $mp['id'];
                $this->manPower->name = $mp['name'];
                $this->manPower->status = $newStatus;
                $this->manPower->skills = json_encode($newSkills);
                $this->manPower->email = $mp['email'];
                $this->manPower->is_active = $mp['is_active'];
                if ($this->manPower->update()) {
                    $updatedCount++;
                }
            }
        }
        
        $_SESSION['fix_msg'] = "Automation: Processed all records. Updated $updatedCount staff members with inconsistent casing.";
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