<?php
class WarehouseController extends BaseController {
    private $inventoryRequest;
    private $project;

    public function __construct() {
        parent::__construct();
        $this->inventoryRequest = $this->loadModel('InventoryRequest');
        $this->project = $this->loadModel('Project');
    }

    public function index() {
        // Determine Warehouse Type based on Role
        $role = $_SESSION['role'];
        $warehouse_type = '';
        
        if ($role == 'admin_gudang_aset') {
            $warehouse_type = 'GUDANG_ASET';
            $page_title = "Dashboard Gudang Aset";
        } elseif ($role == 'admin_gudang_warehouse') {
            $warehouse_type = 'GUDANG_KONSUMABLE';
            $page_title = "Dashboard Gudang Konsumable";
        } elseif ($role == 'superadmin') {
            // Superadmin can see both? Let's default to Aset or allow filter.
            // For simplicity, let's show links or specific view. 
            // Or just check GET param.
            $warehouse_type = $_GET['type'] ?? 'GUDANG_ASET';
            $page_title = "Dashboard " . str_replace('_', ' ', $warehouse_type);
        } else {
            die("Access Denied");
        }
        
        $stmt = $this->inventoryRequest->getWarehouseRequests($warehouse_type);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('warehouse/index', [
            'requests' => $requests, 
            'page_title' => $page_title,
            'warehouse_type' => $warehouse_type
        ]);
    }

    public function detail() {
        $id = $_GET['id'];
        $data = $this->inventoryRequest->getWarehouseRequestDetail($id);
        
        if (!$data) {
            die("Request not found");
        }
        
        // Authorization Check
        $role = $_SESSION['role'];
        $allowed = false;
        if ($role == 'superadmin') $allowed = true;
        if ($role == 'admin_gudang_aset' && $data['header']['warehouse_type'] == 'GUDANG_ASET') $allowed = true;
        if ($role == 'admin_gudang_warehouse' && $data['header']['warehouse_type'] == 'GUDANG_KONSUMABLE') $allowed = true;
        
        if (!$allowed) die("Access Denied");
        
        $this->view('warehouse/detail', ['data' => $data]);
    }
    
    public function update_status() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }
        
        $id = $_POST['id'];
        $status = $_POST['status'];
        $items_data = json_decode($_POST['items_data'] ?? '[]', true) ?? [];
        
        // Authorization Check
        $req_detail = $this->inventoryRequest->getWarehouseRequestDetail($id);
        if (!$req_detail) die("Request not found");
        
        $role = $_SESSION['role'];
        $allowed = false;
        
        // Standard Warehouse/Superadmin access
        if ($role == 'superadmin') $allowed = true;
        if ($role == 'admin_gudang_aset' && $req_detail['header']['warehouse_type'] == 'GUDANG_ASET') $allowed = true;
        if ($role == 'admin_gudang_warehouse' && $req_detail['header']['warehouse_type'] == 'GUDANG_KONSUMABLE') $allowed = true;
        
        // Korlap access (Can only change to COMPLETED)
        if ($role == 'korlap' && $req_detail['header']['korlap_id'] == $_SESSION['user_id'] && $status == 'COMPLETED') {
            $allowed = true;
        }
        
        if (!$allowed) {
            die("Access Denied");
        }
        
        $proof_path = null;
        
        if ($this->inventoryRequest->updateWarehouseStatus($id, $status, $proof_path, $_SESSION['user_id'], $items_data)) {
            // Get Project ID for logging
            $project_id = $req_detail['header']['project_id'];
            $wh_type = str_replace('_', ' ', $req_detail['header']['warehouse_type']);
            $this->project->logAction(
                $project_id, 
                "Warehouse Status: $status", 
                $_SESSION['user_id'], 
                "Warehouse: $wh_type. Status updated to $status."
            );

            // Redirect based on role
            if ($role == 'korlap') {
                $this->redirect('inventory_request_detail', ['id' => $req_detail['header']['inventory_request_id']]);
            } else {
                $this->redirect('warehouse_detail', ['id' => $id]);
            }
        } else {
            echo "Failed to update.";
        }
    }
    
    public function print_pdf() {
        $id = $_GET['id'];
        $data = $this->inventoryRequest->getWarehouseRequestDetail($id);
        
        if (!$data) die("Not found");
        
        // Authorization Check
        $role = $_SESSION['role'];
        $allowed = false;
        if ($role == 'superadmin') $allowed = true;
        if ($role == 'admin_gudang_aset' && $data['header']['warehouse_type'] == 'GUDANG_ASET') $allowed = true;
        if ($role == 'admin_gudang_warehouse' && $data['header']['warehouse_type'] == 'GUDANG_KONSUMABLE') $allowed = true;
        // Allow Korlap if they are the Korlap of the project
        if ($role == 'korlap' && $data['header']['korlap_id'] == $_SESSION['user_id']) $allowed = true;
        
        if (!$allowed) die("Access Denied");

        // QR Verify Closure
        $get_qr_verify = function($page, $params) {
            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            // Assuming index.php is in public/
            $base_url = dirname(dirname($base_url)) . "/mcu-ticketing/public/index.php"; 
            // Fix base url logic if needed, usually we can just use relative or hardcoded if simple
            // Better: use current path
            $base_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
            return $base_url . "?page=$page&" . http_build_query($params);
        };
        
        // Simple HTML Print View
        $this->view('warehouse/pdf', ['data' => $data, 'get_qr_verify' => $get_qr_verify]);
    }

    public function qr_verify() {
        $id = $_GET['id'] ?? null;
        $who = strtolower($_GET['who'] ?? 'requester');
        if (!$id) {
            die("Invalid Request ID");
        }
        $data = $this->inventoryRequest->getWarehouseRequestDetail($id);
        if (!$data) {
            die("Request not found");
        }

        $doc_title = "Inventory Request";
        $doc_number = $data['header']['request_number'] ?? '-';
        $project_name = $data['header']['nama_project'] ?? '-';
        $company = "PT BUMAME CAHAYA MEDIKA"; // Static or from Project

        $name = '-';
        $role = '-';
        $approved_at = null;
        $status_label = 'Pending';

        if ($who === 'requester') {
            $name = $data['header']['requester_name'] ?? '-';
            $role = $data['header']['requester_jabatan'] ?? 'Korlap';
            $approved_at = $data['header']['created_at'] ?? null; // Request Date
            $status_label = 'Diajukan';
        } elseif ($who === 'preparer') {
            $name = $data['header']['preparer_name'] ?? '-';
            $role = 'Admin Gudang';
            $approved_at = $data['header']['prepared_at'] ?? null;
            $status_label = 'Disiapkan';
        } elseif ($who === 'picker') {
            $name = $data['header']['picker_name'] ?? '-';
            $role = 'Penerima Barang';
            $approved_at = $data['header']['picked_up_at'] ?? null;
            $status_label = 'Diterima';
        }

        // Overall Status
        $current_status = $data['header']['status'];

        include '../views/warehouse/qr_verify.php';
        exit;
    }
}
?>