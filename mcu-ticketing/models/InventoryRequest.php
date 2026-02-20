<?php
class InventoryRequest {
    private $conn;
    private $table_name = "inventory_requests";
    private $items_table = "inventory_request_items";
    private $warehouse_table = "warehouse_requests";

    public $id;
    public $project_id;
    public $request_number;
    public $created_by;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($items) {
        try {
            $this->conn->beginTransaction();

            // 1. Create Header
            $query = "INSERT INTO " . $this->table_name . " 
                      SET project_id=:project_id, request_number=:request_number, 
                          created_by=:created_by, status='SUBMITTED'"; // Direct Submit as per requirement? Or DRAFT? 
                                                                       // Requirement: Flow User (Korlap) -> Submit Inventory Request.
                                                                       // So we can assume it starts as DRAFT or goes directly to SUBMITTED if they click Submit.
                                                                       // Let's support SUBMITTED immediately for simplicity unless DRAFT needed.
                                                                       // Prompt says: Status Flow: DRAFT -> SUBMITTED.
            
            // Let's start with DRAFT if saved, SUBMITTED if sent.
            // But for now, let's implement the Submit logic.
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":project_id", $this->project_id);
            $stmt->bindParam(":request_number", $this->request_number);
            $stmt->bindParam(":created_by", $this->created_by);
            $stmt->execute();
            $this->id = $this->conn->lastInsertId();

            // 2. Insert Items
            $queryItem = "INSERT INTO " . $this->items_table . " 
                          SET request_id=:request_id, item_id=:item_id, qty_request=:qty, 
                              item_type_snapshot=:item_type, warehouse_snapshot=:warehouse";
            $stmtItem = $this->conn->prepare($queryItem);

            $assets_count = 0;
            $consumables_count = 0;

            foreach ($items as $item) {
                if ($item['qty'] > 0) {
                    $stmtItem->bindParam(":request_id", $this->id);
                    $stmtItem->bindParam(":item_id", $item['id']);
                    $stmtItem->bindParam(":qty", $item['qty']);
                    $stmtItem->bindParam(":item_type", $item['type']);
                    $stmtItem->bindParam(":warehouse", $item['warehouse']);
                    $stmtItem->execute();

                    if ($item['warehouse'] == 'GUDANG_ASET') $assets_count++;
                    if ($item['warehouse'] == 'GUDANG_KONSUMABLE') $consumables_count++;
                }
            }

            // 3. Auto Split Logic
            if ($assets_count > 0) {
                $this->createWarehouseRequest($this->id, 'GUDANG_ASET');
            }
            if ($consumables_count > 0) {
                $this->createWarehouseRequest($this->id, 'GUDANG_KONSUMABLE');
            }
            
            // Update Main Status to SPLIT_SYSTEM
            $updateQuery = "UPDATE " . $this->table_name . " SET status='SPLIT_SYSTEM' WHERE id=:id";
            $stmtUpdate = $this->conn->prepare($updateQuery);
            $stmtUpdate->bindParam(":id", $this->id);
            $stmtUpdate->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function canEdit($id) {
        // Can only edit if ALL warehouse requests are PENDING
        $query = "SELECT COUNT(*) as count FROM " . $this->warehouse_table . " 
                  WHERE inventory_request_id = :id AND status != 'PENDING'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['count'] == 0;
    }

    public function update($id, $items) {
        if (!$this->canEdit($id)) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            // 1. Update Header (only update modified fields if needed, but mainly we might just keep project_id same or allow change)
            // If project_id is allowed to change:
            if ($this->project_id) {
                $query = "UPDATE " . $this->table_name . " SET project_id=:project_id WHERE id=:id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":project_id", $this->project_id);
                $stmt->bindParam(":id", $id);
                $stmt->execute();
            }

            // 2. Delete existing data to re-create
            // Delete warehouse requests
            $qDelW = "DELETE FROM " . $this->warehouse_table . " WHERE inventory_request_id=:id";
            $sDelW = $this->conn->prepare($qDelW);
            $sDelW->bindParam(":id", $id);
            $sDelW->execute();

            // Delete items
            $qDelI = "DELETE FROM " . $this->items_table . " WHERE request_id=:id";
            $sDelI = $this->conn->prepare($qDelI);
            $sDelI->bindParam(":id", $id);
            $sDelI->execute();

            // 3. Insert New Items
            $queryItem = "INSERT INTO " . $this->items_table . " 
                          SET request_id=:request_id, item_id=:item_id, qty_request=:qty, 
                              item_type_snapshot=:item_type, warehouse_snapshot=:warehouse";
            $stmtItem = $this->conn->prepare($queryItem);

            $assets_count = 0;
            $consumables_count = 0;

            foreach ($items as $item) {
                if ($item['qty'] > 0) {
                    $stmtItem->bindParam(":request_id", $id);
                    $stmtItem->bindParam(":item_id", $item['id']);
                    $stmtItem->bindParam(":qty", $item['qty']);
                    $stmtItem->bindParam(":item_type", $item['type']);
                    $stmtItem->bindParam(":warehouse", $item['warehouse']);
                    $stmtItem->execute();

                    if ($item['warehouse'] == 'GUDANG_ASET') $assets_count++;
                    if ($item['warehouse'] == 'GUDANG_KONSUMABLE') $consumables_count++;
                }
            }

            // 4. Re-create Splits
            if ($assets_count > 0) {
                $this->createWarehouseRequest($id, 'GUDANG_ASET');
            }
            if ($consumables_count > 0) {
                $this->createWarehouseRequest($id, 'GUDANG_KONSUMABLE');
            }

            // 5. Update Status back to SPLIT_SYSTEM (or ensure it is)
            $updateQuery = "UPDATE " . $this->table_name . " SET status='SPLIT_SYSTEM' WHERE id=:id";
            $stmtUpdate = $this->conn->prepare($updateQuery);
            $stmtUpdate->bindParam(":id", $id);
            $stmtUpdate->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    private function createWarehouseRequest($request_id, $type) {
        $query = "INSERT INTO " . $this->warehouse_table . " 
                  SET inventory_request_id=:rid, warehouse_type=:type, status='PENDING'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rid", $request_id);
        $stmt->bindParam(":type", $type);
        $stmt->execute();
    }

    public function getByKorlap($user_id, $role = 'korlap') {
        $query = "SELECT r.*, p.nama_project, p.korlap_id, p.tanggal_mcu, u.full_name as requester_name,
                  (SELECT GROUP_CONCAT(CONCAT(warehouse_type, ':', status) SEPARATOR '|') FROM " . $this->warehouse_table . " wr WHERE wr.inventory_request_id = r.id) as warehouse_statuses
                  FROM " . $this->table_name . " r
                  JOIN projects p ON r.project_id = p.project_id
                  JOIN users u ON r.created_by = u.user_id";
        
        if ($role == 'korlap') {
            $query .= " WHERE p.korlap_id = :uid";
        }
        // Superadmin sees all, so no WHERE clause needed or maybe filter by created_by if desired, but user said superadmin helps request in korlap menu, so superadmin should probably see all or filtered by their own projects if they act as korlap?
        // User said: "klo pun superadmin bantu request di menu korlap pun harus muncul karena project punya dia"
        // This implies: If Superadmin makes a request for Project A (Korlap X), Korlap X must see it.
        // And if Korlap X logs in, they see all requests for their projects.
        // What if Superadmin logs in? They should probably see everything.
        
        $query .= " ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($role == 'korlap') {
            $stmt->bindParam(":uid", $user_id);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getDetail($id) {
        // 1. Get Header
        $query = "SELECT r.*, p.nama_project, p.korlap_id, u.full_name as requester_name
                  FROM " . $this->table_name . " r
                  JOIN projects p ON r.project_id = p.project_id
                  JOIN users u ON r.created_by = u.user_id
                  WHERE r.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) return null;

        // 2. Get Items
        $queryItems = "SELECT iri.*, ii.item_name, ii.unit, ii.category
                       FROM " . $this->items_table . " iri
                       JOIN inventory_items ii ON iri.item_id = ii.id
                       WHERE iri.request_id = :rid";
        $stmtItems = $this->conn->prepare($queryItems);
        $stmtItems->bindParam(":rid", $id);
        $stmtItems->execute();
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // 3. Get Warehouse Requests (Splits)
        $querySplits = "SELECT * FROM " . $this->warehouse_table . " WHERE inventory_request_id = :rid";
        $stmtSplits = $this->conn->prepare($querySplits);
        $stmtSplits->bindParam(":rid", $id);
        $stmtSplits->execute();
        $splits = $stmtSplits->fetchAll(PDO::FETCH_ASSOC);

        return ['header' => $header, 'items' => $items, 'splits' => $splits];
    }

    public function getWarehouseRequests($warehouse_type) {
        // Only get requests relevant to this warehouse
        $query = "SELECT wr.*, ir.request_number, ir.project_id, p.nama_project, u.full_name as requester_name, ir.created_at as request_date
                  FROM " . $this->warehouse_table . " wr
                  JOIN " . $this->table_name . " ir ON wr.inventory_request_id = ir.id
                  JOIN projects p ON ir.project_id = p.project_id
                  JOIN users u ON ir.created_by = u.user_id
                  WHERE wr.warehouse_type = :w_type
                  ORDER BY wr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":w_type", $warehouse_type);
        $stmt->execute();
        return $stmt;
    }

    public function getWarehouseRequestDetail($warehouse_request_id) {
        $query = "SELECT wr.*, ir.request_number, ir.project_id, p.nama_project, p.tanggal_mcu, p.korlap_id, 
                         u.full_name as requester_name, u.role as requester_role, u.jabatan as requester_jabatan,
                         uk.full_name as korlap_name, uk.role as korlap_role, uk.jabatan as korlap_jabatan,
                         up.full_name as preparer_name, uc.full_name as picker_name
                  FROM " . $this->warehouse_table . " wr
                  JOIN " . $this->table_name . " ir ON wr.inventory_request_id = ir.id
                  JOIN projects p ON ir.project_id = p.project_id
                  JOIN users u ON ir.created_by = u.user_id
                  LEFT JOIN users uk ON p.korlap_id = uk.user_id
                  LEFT JOIN users up ON wr.prepared_by = up.user_id
                  LEFT JOIN users uc ON wr.picked_up_by = uc.user_id
                  WHERE wr.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $warehouse_request_id);
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) return null;

        // Get Items filtered by warehouse type
        $queryItems = "SELECT iri.*, ii.item_name, ii.unit, ii.category
                       FROM " . $this->items_table . " iri
                       JOIN inventory_items ii ON iri.item_id = ii.id
                       WHERE iri.request_id = :rid AND iri.warehouse_snapshot = :w_type";
        $stmtItems = $this->conn->prepare($queryItems);
        $stmtItems->bindParam(":rid", $header['inventory_request_id']);
        $stmtItems->bindParam(":w_type", $header['warehouse_type']);
        $stmtItems->execute();
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        return ['header' => $header, 'items' => $items];
    }
    
    public function updateWarehouseStatus($id, $status, $proof_path = null, $user_id = null, $items_data = []) {
        try {
            $this->conn->beginTransaction();

            // 1. Update Header
            $query = "UPDATE " . $this->warehouse_table . " 
                      SET status = :status, updated_at = NOW()";
            
            if ($proof_path) {
                $query .= ", proof_file = :proof_file";
            }
            if ($status == 'READY' && $user_id) {
                $query .= ", prepared_by = :user_id, prepared_at = NOW()";
            }
            if ($status == 'COMPLETED' && $user_id) {
                $query .= ", picked_up_by = :user_id, picked_up_at = NOW()";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":id", $id);
            
            if ($proof_path) $stmt->bindParam(":proof_file", $proof_path);
            
            // Only bind user_id if it's used in the query
            if (($status == 'READY' || $status == 'COMPLETED') && $user_id) {
                $stmt->bindParam(":user_id", $user_id);
            }
            
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>