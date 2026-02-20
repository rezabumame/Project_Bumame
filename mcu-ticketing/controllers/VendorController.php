<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Vendor.php';

class VendorController extends BaseController {
    private $vendor;

    public function __construct() {
        parent::__construct();
        $this->vendor = $this->loadModel('Vendor');
    }

    public function index() {
        // Access control: Admin Ops, Procurement, Manager Ops, Head Ops
        $this->checkRole(['admin_ops', 'procurement', 'manager_ops', 'head_ops', 'superadmin']);

        $stmt = $this->vendor->readAll();
        $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include '../views/vendors/list.php';
    }

    public function create() {
        if ($_SESSION['role'] != 'admin_ops' && $_SESSION['role'] != 'procurement' && $_SESSION['role'] != 'superadmin') {
            // Strict control for creation
             header("Location: index.php?page=vendors_list");
             exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 die("Invalid CSRF token.");
            }
            $this->vendor->vendor_name = $_POST['vendor_name'];
            $this->vendor->pic_name = $_POST['pic_name'];
            $this->vendor->phone_number = $_POST['phone_number'];
            
            $services = isset($_POST['services']) ? $_POST['services'] : [];
            if (!is_array($services)) {
                $services = [];
            }
            
            // Handle other services (free text)
            if (isset($_POST['other_services']) && !empty(trim($_POST['other_services']))) {
                $others = array_map('trim', explode(',', $_POST['other_services']));
                $others = array_filter($others); // Remove empty values
                $services = array_merge($services, $others);
            }
            
            // Remove duplicates
            $services = array_unique($services);
            
            $this->vendor->services = implode(', ', $services);

            if ($this->vendor->create()) {
                header("Location: index.php?page=vendors_list&status=success");
            } else {
                header("Location: index.php?page=vendors_create&status=error");
            }
        } else {
            include '../views/vendors/form.php';
        }
    }

    public function edit() {
        if ($_SESSION['role'] != 'admin_ops' && $_SESSION['role'] != 'procurement' && $_SESSION['role'] != 'superadmin') {
             header("Location: index.php?page=vendors_list");
             exit;
        }

        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $vendor_data = $this->vendor->getById($id);

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // CSRF Protection
                if (!$this->validateCsrfToken()) {
                    die("Invalid CSRF token.");
                }
                $this->vendor->vendor_id = $id;
                $this->vendor->vendor_name = $_POST['vendor_name'];
                $this->vendor->pic_name = $_POST['pic_name'];
                $this->vendor->phone_number = $_POST['phone_number'];
                
                $services = isset($_POST['services']) ? $_POST['services'] : [];
                if (!is_array($services)) {
                    $services = [];
                }
                
                // Handle other services (free text)
                if (isset($_POST['other_services']) && !empty(trim($_POST['other_services']))) {
                    $others = array_map('trim', explode(',', $_POST['other_services']));
                    $others = array_filter($others); // Remove empty values
                    $services = array_merge($services, $others);
                }
                
                // Remove duplicates
                $services = array_unique($services);
                
                $this->vendor->services = implode(', ', $services);

                if ($this->vendor->update()) {
                    header("Location: index.php?page=vendors_list&status=updated");
                } else {
                    $error = "Update failed";
                    include '../views/vendors/form.php';
                }
            } else {
                include '../views/vendors/form.php';
            }
        }
    }

    public function delete() {
        if ($_SESSION['role'] != 'admin_ops' && $_SESSION['role'] != 'procurement' && $_SESSION['role'] != 'superadmin') {
            header("Location: index.php?page=vendors_list");
            exit;
       }

        if (isset($_GET['id'])) {
            if ($this->vendor->delete($_GET['id'])) {
                header("Location: index.php?page=vendors_list&status=deleted");
            } else {
                header("Location: index.php?page=vendors_list&status=error");
            }
        }
    }

    public function get_all_ajax() {
        // Restrict AJAX access to authorized roles
        $allowed_roles = ['admin_ops', 'procurement', 'manager_ops', 'head_ops', 'superadmin'];
        
        if (!in_array($_SESSION['role'], $allowed_roles)) {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             exit;
        }

        $stmt = $this->vendor->readAll();
        $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $vendors]);
    }
}
?>
