<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/CostCode.php';

class CostCodeController {
    private $db;
    private $costCode;

    public function __construct() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
            // Only superadmin for now, or maybe admin_ops
            // Let's allow superadmin only for configuration
            if ($_SESSION['role'] != 'superadmin') {
                header("Location: index.php?page=dashboard");
                exit;
            }
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->costCode = new CostCode($this->db);
    }

    public function index() {
        $stmt = $this->costCode->readAll();
        $cost_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include '../views/cost_codes/index.php';
    }

    private function getTabId($category) {
        switch ($category) {
            case 'Vendor (Internal Memo)': return 'vendor';
            case 'RAB': return 'rab';
            case 'Konsumsi': return 'konsumsi';
            default: return 'vendor';
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->costCode->code = $_POST['code'];
            $this->costCode->category = $_POST['category'];
            $this->costCode->lookup_value = $_POST['lookup_value'];
            $this->costCode->description = $_POST['description'];

            $tab = $this->getTabId($_POST['category']);

            if ($this->costCode->create()) {
                header("Location: index.php?page=cost_codes_index&status=success&tab=" . $tab);
            } else {
                header("Location: index.php?page=cost_codes_index&status=error&tab=" . $tab);
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->costCode->id = $_POST['id'];
            $this->costCode->code = $_POST['code'];
            $this->costCode->category = $_POST['category'];
            $this->costCode->lookup_value = $_POST['lookup_value'];
            $this->costCode->description = $_POST['description'];

            $tab = $this->getTabId($_POST['category']);

            if ($this->costCode->update()) {
                header("Location: index.php?page=cost_codes_index&status=updated&tab=" . $tab);
            } else {
                header("Location: index.php?page=cost_codes_index&status=error&tab=" . $tab);
            }
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $this->costCode->id = $_GET['id'];
            
            // Get category first for redirection
            $item = $this->costCode->getById($_GET['id']);
            $tab = $item ? $this->getTabId($item['category']) : 'vendor';

            if ($this->costCode->delete()) {
                header("Location: index.php?page=cost_codes_index&status=deleted&tab=" . $tab);
            } else {
                header("Location: index.php?page=cost_codes_index&status=error&tab=" . $tab);
            }
        }
    }
}
