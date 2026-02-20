<?php
class InventoryItem {
    private $conn;
    private $table_name = "inventory_items";

    public $id;
    public $category;
    public $item_name;
    public $item_type; // ASET / KONSUMABLE
    public $unit;
    public $target_warehouse; // GUDANG_ASET / GUDANG_KONSUMABLE
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all active items, grouped by category if needed
    public function readAllActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_active = 1 ORDER BY category ASC, item_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY category ASC, item_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET category=:category, item_name=:item_name, item_type=:item_type, 
                      unit=:unit, target_warehouse=:target_warehouse, is_active=1";
        $stmt = $this->conn->prepare($query);

        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->item_name = htmlspecialchars(strip_tags($this->item_name));
        $this->item_type = htmlspecialchars(strip_tags($this->item_type));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->target_warehouse = htmlspecialchars(strip_tags($this->target_warehouse));

        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":item_name", $this->item_name);
        $stmt->bindParam(":item_type", $this->item_type);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":target_warehouse", $this->target_warehouse);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET category=:category, item_name=:item_name, item_type=:item_type, 
                      unit=:unit, target_warehouse=:target_warehouse, is_active=:is_active
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->item_name = htmlspecialchars(strip_tags($this->item_name));
        $this->item_type = htmlspecialchars(strip_tags($this->item_type));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->target_warehouse = htmlspecialchars(strip_tags($this->target_warehouse));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));

        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":item_name", $this->item_name);
        $stmt->bindParam(":item_type", $this->item_type);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":target_warehouse", $this->target_warehouse);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        // Soft delete
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>