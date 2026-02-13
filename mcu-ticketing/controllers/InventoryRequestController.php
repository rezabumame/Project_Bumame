<?php
class InventoryRequestController extends BaseController {
    private $inventoryRequest;
    private $inventoryItem;
    private $project;
    private $rab;

    public function __construct() {
        parent::__construct();
        $this->inventoryRequest = $this->loadModel('InventoryRequest');
        $this->inventoryItem = $this->loadModel('InventoryItem');
        $this->project = $this->loadModel('Project');
        $this->rab = $this->loadModel('Rab'); // To reuse getAvailableProjects logic
    }

    public function index() {
        $this->checkRole(['korlap', 'superadmin', 'manager_ops', 'admin_ops']);
        
        // Pass role to getByKorlap to handle filtering logic
        $requests = $this->inventoryRequest->getByKorlap($_SESSION['user_id'], $_SESSION['role']);
        $requests_list = $requests->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('inventory/request/index', ['requests' => $requests_list]);
    }

    public function create() {
        $this->checkRole(['korlap', 'superadmin', 'manager_ops', 'admin_ops']);
        
        // Use RAB's available project logic which we just optimized for Korlap
        $stmt = $this->rab->getAvailableProjects($_SESSION['role'], $_SESSION['user_id']);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get Active Inventory Items
        $stmtItems = $this->inventoryItem->readAllActive();
        $raw_items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        
        // Group items by category
        $items_by_category = [];
        foreach ($raw_items as $item) {
            $items_by_category[$item['category']][] = $item;
        }
        
        $this->view('inventory/request/create', [
            'projects' => $projects,
            'items_by_category' => $items_by_category
        ]);
    }

    public function store() {
        $this->checkRole(['korlap', 'superadmin', 'manager_ops', 'admin_ops']);
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('inventory_request_create');
        }
        
        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        $project_id = $_POST['project_id'];
        if (empty($project_id)) {
            $this->redirect('inventory_request_create');
            return;
        }

        // IDOR Protection
        $this->verifyProjectAccess($project_id);
        
        // Generate Request Number (Now uses Project ID)
        $request_number = $project_id;
        
        $items = [];
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item_id => $qty) {
                if ($qty > 0) {
                    $type = $_POST['item_type'][$item_id] ?? 'ASET';
                    $warehouse = $_POST['item_warehouse'][$item_id] ?? 'GUDANG_ASET';
                    
                    $items[] = [
                        'id' => $item_id,
                        'qty' => $qty,
                        'type' => $type,
                        'warehouse' => $warehouse
                    ];
                }
            }
        }
        
        if (empty($items)) {
            $this->redirect('inventory_request_create');
            return;
        }
        
        $this->inventoryRequest->project_id = $project_id;
        $this->inventoryRequest->request_number = $request_number;
        $this->inventoryRequest->created_by = $_SESSION['user_id'];
        
        if ($this->inventoryRequest->create($items)) {
            // Log Action
            $this->project->logAction(
                $project_id, 
                'Inventory Request Created', 
                $_SESSION['user_id'], 
                "Request Number: $request_number. Items count: " . count($items)
            );
            
            // Email Notification to Procurement
            try {
                $userModel = $this->loadModel('User');
                
                // Determine target audiences based on item types
                $sendToAset = false;
                $sendToWarehouse = false;
                
                foreach ($items as $item) {
                    if (isset($item['type']) && strtoupper($item['type']) == 'ASET') {
                        $sendToAset = true;
                    } else {
                        $sendToWarehouse = true;
                    }
                }

                $emails = [];
                if ($sendToAset) {
                    $emails_aset = $userModel->getEmailsByRole('admin_gudang_aset');
                    $emails = array_merge($emails, $emails_aset);
                }
                if ($sendToWarehouse) {
                    $emails_warehouse = $userModel->getEmailsByRole('admin_gudang_warehouse');
                    $emails = array_merge($emails, $emails_warehouse);
                }
                $emails = array_unique($emails);
                
                if (!empty($emails)) {
                    $projectData = $this->project->getProjectById($project_id);
                    $salesName = $projectData['sales_name'] ?? '-';
                    $totalPeserta = $projectData['total_peserta'] ?? '-';
                    $tanggalMcu = isset($projectData['tanggal_mcu']) ? DateHelper::formatSmartDateIndonesian($projectData['tanggal_mcu']) : '-';

                    $subject = "[Permintaan] Pengajuan Barang: " . $projectData['nama_project'];
                    $content = "Korlap telah mengajukan permintaan barang (inventory request) untuk project berikut:<br><br>";
                    $content .= "<b>Nama Project:</b> " . $projectData['nama_project'] . "<br>";
                    $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                    $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                    $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                    $content .= "<b>Jumlah Item:</b> " . count($items) . "<br>";
                    
                    $link = MailHelper::getBaseUrl() . "?page=inventory_request_index";
                    $html = MailHelper::getTemplate("Pengajuan Barang Baru", $content, $link);
                    MailHelper::send($emails, $subject, $html);
                }
            } catch (Exception $e) {
                error_log("Email notification failed on Inventory Request creation: " . $e->getMessage());
            }
            
            // Trigger Check Status (Auto In Progress Ops)
            $this->project->checkAndSetInProgressOps($project_id);

            // Success
            $this->redirect('inventory_request_index');
        } else {
            $this->redirect('inventory_request_create');
        }
    }

    public function detail() {
        $this->checkRole(['korlap', 'superadmin', 'manager_ops', 'admin_ops']);
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('inventory_request_index');
        }
        
        $data = $this->inventoryRequest->getDetail($id);
        
        if (!$data) {
            die("Request not found");
        }
        
        // Check ownership (Check Project Korlap ID instead of Created By)
        if ($_SESSION['role'] == 'korlap' && $data['header']['korlap_id'] != $_SESSION['user_id']) {
            die("Access Denied");
        }
        
        $this->view('inventory/request/detail', ['data' => $data]);
    }

    public function edit() {
        $this->checkRole(['korlap', 'superadmin', 'manager_ops', 'admin_ops']);
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('inventory_request_index');
        }

        // Check if editable
        if (!$this->inventoryRequest->canEdit($id)) {
            $this->redirect('inventory_request_index');
            return;
        }

        $data = $this->inventoryRequest->getDetail($id);
        if (!$data) {
            die("Request not found");
        }

        // Check ownership
        if ($_SESSION['role'] == 'korlap' && $data['header']['korlap_id'] != $_SESSION['user_id']) {
            die("Access Denied");
        }

        $stmt = $this->rab->getAvailableProjects($_SESSION['role'], $_SESSION['user_id']);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get Active Inventory Items
        $stmtItems = $this->inventoryItem->readAllActive();
        $raw_items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        
        // Group items by category
        $items_by_category = [];
        foreach ($raw_items as $item) {
            $items_by_category[$item['category']][] = $item;
        }

        $this->view('inventory/request/edit', [
            'data' => $data,
            'projects' => $projects,
            'items_by_category' => $items_by_category
        ]);
    }

    public function update() {
        $this->checkRole(['korlap', 'superadmin', 'manager_ops']);
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('inventory_request_index');
        }

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        $id = $_POST['id'];
        $project_id = $_POST['project_id'];

        // IDOR Protection
        $this->verifyProjectAccess($project_id);

        if (!$this->inventoryRequest->canEdit($id)) {
             $this->redirect('inventory_request_index');
             return;
        }

        $items = [];
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item_id => $qty) {
                if ($qty > 0) {
                    $type = $_POST['item_type'][$item_id] ?? 'ASET';
                    $warehouse = $_POST['item_warehouse'][$item_id] ?? 'GUDANG_ASET';
                    
                    $items[] = [
                        'id' => $item_id,
                        'qty' => $qty,
                        'type' => $type,
                        'warehouse' => $warehouse
                    ];
                }
            }
        }
        
        if (empty($items)) {
            $this->redirect('inventory_request_edit', ['id' => $id]);
            return;
        }

        $this->inventoryRequest->project_id = $project_id;
        
        if ($this->inventoryRequest->update($id, $items)) {
            // Log Action
            $this->project->logAction(
                $project_id, 
                'Inventory Request Updated', 
                $_SESSION['user_id'], 
                "Request ID: $id. Items count: " . count($items)
            );

            // Trigger Check Status (Auto In Progress Ops)
            $this->project->checkAndSetInProgressOps($project_id);

            $this->redirect('inventory_request_index');
        } else {
             $this->redirect('inventory_request_edit', ['id' => $id]);
        }
    }
}
?>