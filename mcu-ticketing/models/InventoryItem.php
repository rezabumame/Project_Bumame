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
        $query = "SELECT ii.*, COALESCE(ac.code_count, 0) AS asset_code_count
                  FROM " . $this->table_name . " ii
                  LEFT JOIN (
                      SELECT inventory_item_id, COUNT(*) AS code_count
                      FROM inventory_asset_codes
                      GROUP BY inventory_item_id
                  ) ac ON ii.id = ac.inventory_item_id
                  ORDER BY ii.category ASC, ii.item_name ASC";
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

    public function getAssetCodes($item_id) {
        $query = "SELECT asset_code FROM inventory_asset_codes WHERE inventory_item_id = :item_id ORDER BY asset_code ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_id", $item_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function replaceAssetCodes($item_id, array $codes) {
        $del = $this->conn->prepare("DELETE FROM inventory_asset_codes WHERE inventory_item_id = :item_id");
        $del->bindParam(":item_id", $item_id);
        $del->execute();

        if (empty($codes)) {
            return true;
        }

        $ins = $this->conn->prepare("INSERT INTO inventory_asset_codes (inventory_item_id, asset_code) VALUES (:item_id, :asset_code)");
        foreach ($codes as $code) {
            $code = trim(htmlspecialchars(strip_tags($code)));
            if ($code === '') continue;
            $ins->bindParam(":item_id", $item_id);
            $ins->bindParam(":asset_code", $code);
            $ins->execute();
        }
        return true;
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
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function getLastInsertId() {
        return $this->id;
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

    public function getAssetUsageSummary($month, $year) {
        $query = "SELECT ii.id, ii.item_name, ii.category,
                         COUNT(DISTINCT ac.id) as total_codes,
                         COUNT(DISTINCT CASE WHEN p.project_id IS NOT NULL THEN irac.asset_code_id END) as used_codes,
                         COUNT(DISTINCT p.project_id) as total_projects
                  FROM inventory_items ii
                  JOIN inventory_asset_codes ac ON ac.inventory_item_id = ii.id
                  LEFT JOIN inventory_request_asset_codes irac ON irac.asset_code_id = ac.id
                  LEFT JOIN warehouse_requests wr ON irac.warehouse_request_id = wr.id
                  LEFT JOIN inventory_requests ir ON wr.inventory_request_id = ir.id
                  LEFT JOIN projects p ON ir.project_id = p.project_id
                      AND MONTH(JSON_UNQUOTE(JSON_EXTRACT(p.tanggal_mcu, '$[0]'))) = :month
                      AND YEAR(JSON_UNQUOTE(JSON_EXTRACT(p.tanggal_mcu, '$[0]'))) = :year
                  WHERE ii.item_type = 'ASET' AND ii.is_active = 1
                  GROUP BY ii.id, ii.item_name, ii.category
                  ORDER BY total_projects DESC, ii.category ASC, ii.item_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":month", $month);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItemCodesWithUsage($item_id, $month, $year) {
        $query = "SELECT ac.id, ac.asset_code,
                         COUNT(DISTINCT p.project_id) as usage_count,
                         MAX(p.tanggal_mcu) as last_used
                  FROM inventory_asset_codes ac
                  LEFT JOIN inventory_request_asset_codes irac ON irac.asset_code_id = ac.id
                  LEFT JOIN warehouse_requests wr ON irac.warehouse_request_id = wr.id
                  LEFT JOIN inventory_requests ir ON wr.inventory_request_id = ir.id
                  LEFT JOIN projects p ON ir.project_id = p.project_id
                      AND MONTH(JSON_UNQUOTE(JSON_EXTRACT(p.tanggal_mcu, '$[0]'))) = :month
                      AND YEAR(JSON_UNQUOTE(JSON_EXTRACT(p.tanggal_mcu, '$[0]'))) = :year
                  WHERE ac.inventory_item_id = :item_id
                  GROUP BY ac.id, ac.asset_code
                  ORDER BY usage_count DESC, ac.asset_code ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_id", $item_id);
        $stmt->bindParam(":month", $month);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCodeProjectHistory($asset_code_id) {
        $query = "SELECT p.project_id, p.nama_project, p.tanggal_mcu,
                         ir.request_number, wr.status as warehouse_status, wr.id as warehouse_request_id
                  FROM inventory_request_asset_codes irac
                  JOIN warehouse_requests wr ON irac.warehouse_request_id = wr.id
                  JOIN inventory_requests ir ON wr.inventory_request_id = ir.id
                  JOIN projects p ON ir.project_id = p.project_id
                  WHERE irac.asset_code_id = :code_id
                  GROUP BY p.project_id, p.nama_project, p.tanggal_mcu, ir.request_number, wr.status, wr.id
                  ORDER BY p.tanggal_mcu DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code_id", $asset_code_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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