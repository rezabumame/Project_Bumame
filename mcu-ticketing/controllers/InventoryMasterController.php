<?php
class InventoryMasterController extends BaseController {
    private $inventoryItem;

    public function __construct() {
        parent::__construct();
        $this->inventoryItem = $this->loadModel('InventoryItem');
    }

    private function checkPermission() {
        // Allow ONLY Superadmin to manage master data
        if ($_SESSION['role'] != 'superadmin') {
            die("Access Denied");
        }
    }

    public function index() {
        $this->checkPermission();
        
        $stmt = $this->inventoryItem->readAll();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('inventory/master/index', ['items' => $items]);
    }

    public function create() {
        $this->checkPermission();
        $this->view('inventory/master/create');
    }

    public function store() {
        $this->checkPermission();
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('inventory_master_index');
        }

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token.");
        }

        $this->inventoryItem->category = $_POST['category'];
        $this->inventoryItem->item_name = $_POST['item_name'];
        $this->inventoryItem->item_type = $_POST['item_type']; // ASET or KONSUMABLE
        $this->inventoryItem->unit = $_POST['unit'];
        $this->inventoryItem->target_warehouse = $_POST['target_warehouse']; // GUDANG_ASET or GUDANG_KONSUMABLE

        if ($this->inventoryItem->create()) {
            echo "<script>alert('Item created successfully'); window.location.href='index.php?page=inventory_master_index';</script>";
        } else {
            echo "<script>alert('Failed to create item'); history.back();</script>";
        }
    }

    public function edit() {
        $this->checkPermission();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('inventory_master_index');
        }

        $item = $this->inventoryItem->getById($id);
        if (!$item) {
            echo "<script>alert('Item not found'); window.location.href='index.php?page=inventory_master_index';</script>";
            return;
        }

        $this->view('inventory/master/edit', ['item' => $item]);
    }

    public function update() {
        $this->checkPermission();
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('inventory_master_index');
        }

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token.");
        }

        $this->inventoryItem->id = $_POST['id'];
        $this->inventoryItem->category = $_POST['category'];
        $this->inventoryItem->item_name = $_POST['item_name'];
        $this->inventoryItem->item_type = $_POST['item_type'];
        $this->inventoryItem->unit = $_POST['unit'];
        $this->inventoryItem->target_warehouse = $_POST['target_warehouse'];
        $this->inventoryItem->is_active = $_POST['is_active'];

        if ($this->inventoryItem->update()) {
            echo "<script>alert('Item updated successfully'); window.location.href='index.php?page=inventory_master_index';</script>";
        } else {
            echo "<script>alert('Failed to update item'); history.back();</script>";
        }
    }

    public function delete() {
        $this->checkPermission();
        
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->inventoryItem->id = $id;
            if ($this->inventoryItem->delete()) {
                echo "<script>alert('Item deactivated successfully'); window.location.href='index.php?page=inventory_master_index';</script>";
            } else {
                echo "<script>alert('Failed to deactivate item'); window.location.href='index.php?page=inventory_master_index';</script>";
            }
        } else {
            $this->redirect('inventory_master_index');
        }
    }
}
?>