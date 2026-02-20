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
        
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $stmt = $this->manPower->getAll($search, $limit, $offset);
        $man_powers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_rows = $this->manPower->countAll($search);
        $total_pages = ceil($total_rows / $limit);

        // Decode skills for display
        foreach ($man_powers as &$mp) {
            $mp['skills_array'] = json_decode($mp['skills'], true) ?? [];
        }

        $can_edit = in_array($_SESSION['role'], ['superadmin', 'admin_ops']);

        $this->view('man_power_management/index', [
            'man_powers' => $man_powers,
            'search' => $search,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'can_edit' => $can_edit
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
            $this->manPower->status = $_POST['status'];
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
            $this->manPower->status = $_POST['status'];
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