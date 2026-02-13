<?php
class RabRealization {
    private $conn;
    private $table_name = "rab_realizations";
    private $items_table = "rab_realization_items";

    public $id;
    public $rab_id;
    public $project_id;
    public $date;
    public $total_amount;
    public $actual_participants;
    public $doctor_participants;
    public $doctor_fee_per_patient;
    public $doctor_total_fee;
    public $notes;
    public $status;
    public $created_by;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all realizations (can be filtered)
    public function getAll($project_id = null, $role = null, $user_id = null, $limit = null, $offset = null, $filters = []) {
        $query = "SELECT r.*, p.nama_project, u.full_name as creator_name, rab.rab_number, rab.grand_total as rab_grand_total, rab.status as rab_status,
                  korlap.full_name as korlap_name 
                  FROM " . $this->table_name . " r
                  LEFT JOIN projects p ON r.project_id = p.project_id
                  LEFT JOIN users u ON r.created_by = u.user_id
                  LEFT JOIN rabs rab ON r.rab_id = rab.id
                  LEFT JOIN users korlap ON p.korlap_id = korlap.user_id";
        
        $conditions = [];
        $params = [];

        if ($project_id) {
            $conditions[] = "r.project_id = :project_id";
            $params[':project_id'] = $project_id;
        }

        if ($role == 'korlap') {
            $conditions[] = "p.korlap_id = :user_id";
            $params[':user_id'] = $user_id;
        }

        // Date Range Filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $conditions[] = "r.date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY r.date DESC";

        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt;
    }

    public function countAll($project_id = null, $role = null, $user_id = null, $filters = []) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " r
                  JOIN projects p ON r.project_id = p.project_id";
        
        $conditions = [];
        $params = [];

        if ($project_id) {
            $conditions[] = "r.project_id = :project_id";
            $params[':project_id'] = $project_id;
        }

        if ($role == 'korlap') {
            $conditions[] = "p.korlap_id = :user_id";
            $params[':user_id'] = $user_id;
        }

        // Date Range Filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $conditions[] = "r.date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getAccommodationAdvanceTotalByRab($rab_id) {
        $query = "SELECT SUM(accommodation_advance) as total_advance FROM " . $this->table_name . " WHERE rab_id = :rab_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rab_id", $rab_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_advance'] ?? 0;
    }

    public function getById($id) {
        $query = "SELECT r.*, p.nama_project, p.korlap_id, rab.rab_number 
                  FROM " . $this->table_name . " r
                  LEFT JOIN projects p ON r.project_id = p.project_id
                  LEFT JOIN rabs rab ON r.rab_id = rab.id
                  WHERE r.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItems($realization_id) {
        $query = "SELECT * FROM " . $this->items_table . " WHERE realization_id = :realization_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":realization_id", $realization_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRealizedItemsByRab($rab_id) {
        $query = "SELECT i.category, i.item_name, SUM(i.qty) as total_qty, SUM(i.subtotal) as total_amount,
                  GROUP_CONCAT(DISTINCT r.date ORDER BY r.date ASC SEPARATOR ', ') as realization_dates,
                  GROUP_CONCAT(DISTINCT i.notes SEPARATOR ', ') as notes
                  FROM " . $this->items_table . " i
                  JOIN " . $this->table_name . " r ON i.realization_id = r.id
                  WHERE r.rab_id = :rab_id
                  GROUP BY i.category, i.item_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rab_id", $rab_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRealizedTotalByRab($rab_id) {
        $query = "SELECT SUM(total_amount) as grand_total FROM " . $this->table_name . " WHERE rab_id = :rab_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rab_id", $rab_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['grand_total'] ?? 0;
    }



    public function create($data, $items) {
        try {
            $this->conn->beginTransaction();

            // Insert Realization Header
            $query = "INSERT INTO " . $this->table_name . " 
                      (rab_id, project_id, date, total_amount, actual_participants, doctor_participants, doctor_fee_per_patient, doctor_total_fee, accommodation_advance, notes, created_by, status)
                      VALUES (:rab_id, :project_id, :date, :total_amount, :actual_participants, :doctor_participants, :doctor_fee_per_patient, :doctor_total_fee, :accommodation_advance, :notes, :created_by, :status)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":rab_id", $data['rab_id']);
            $stmt->bindParam(":project_id", $data['project_id']);
            $stmt->bindParam(":date", $data['date']);
            $stmt->bindParam(":total_amount", $data['total_amount']);
            $stmt->bindParam(":actual_participants", $data['actual_participants']);
            $stmt->bindParam(":doctor_participants", $data['doctor_participants']);
            $stmt->bindParam(":doctor_fee_per_patient", $data['doctor_fee_per_patient']);
            $stmt->bindParam(":doctor_total_fee", $data['doctor_total_fee']);
            $stmt->bindParam(":accommodation_advance", $data['accommodation_advance']);
            $stmt->bindParam(":notes", $data['notes']);
            $stmt->bindParam(":created_by", $data['created_by']);
            $stmt->bindParam(":status", $data['status']);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating realization header.");
            }
            
            $realization_id = $this->conn->lastInsertId();

            // Insert Items
            $queryItem = "INSERT INTO " . $this->items_table . "
                        (realization_id, category, item_name, qty, price, subtotal, notes, is_extra_item)
                        VALUES (:realization_id, :category, :item_name, :qty, :price, :subtotal, :notes, :is_extra_item)";
            
            $stmtItem = $this->conn->prepare($queryItem);

            foreach ($items as $item) {
                $stmtItem->bindParam(":realization_id", $realization_id);
                $stmtItem->bindParam(":category", $item['category']);
                $stmtItem->bindParam(":item_name", $item['item_name']);
                $stmtItem->bindParam(":qty", $item['qty']);
                $stmtItem->bindParam(":price", $item['price']);
                $stmtItem->bindParam(":subtotal", $item['subtotal']);
                $stmtItem->bindParam(":notes", $item['notes']);
                $stmtItem->bindParam(":is_extra_item", $item['is_extra_item']);
                
                if (!$stmtItem->execute()) {
                    throw new Exception("Error creating realization item.");
                }
            }

            $this->conn->commit();
            return $realization_id;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function update($id, $data, $items) {
        try {
            $this->conn->beginTransaction();

            // Update Header
            $query = "UPDATE " . $this->table_name . " 
                      SET actual_participants = :actual_participants,
                          doctor_participants = :doctor_participants,
                          doctor_total_fee = :doctor_total_fee,
                          accommodation_advance = :accommodation_advance,
                          notes = :notes,
                          total_amount = :total_amount";
            
            // Add status if provided
            if (isset($data['status'])) {
                $query .= ", status = :status";
            }

            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":actual_participants", $data['actual_participants']);
            $stmt->bindParam(":doctor_participants", $data['doctor_participants']);
            $stmt->bindParam(":doctor_total_fee", $data['doctor_total_fee']);
            $stmt->bindParam(":accommodation_advance", $data['accommodation_advance']);
            $stmt->bindParam(":notes", $data['notes']);
            $stmt->bindParam(":total_amount", $data['total_amount']);
            $stmt->bindParam(":id", $id);
            
            if (isset($data['status'])) {
                $stmt->bindParam(":status", $data['status']);
            }

            if (!$stmt->execute()) {
                throw new Exception("Error updating realization header.");
            }

            // Delete existing items
            $deleteQuery = "DELETE FROM " . $this->items_table . " WHERE realization_id = :id";
            $delStmt = $this->conn->prepare($deleteQuery);
            $delStmt->bindParam(":id", $id);
            if (!$delStmt->execute()) {
                throw new Exception("Error deleting existing items.");
            }

            // Insert New Items
            $queryItem = "INSERT INTO " . $this->items_table . "
                        (realization_id, category, item_name, qty, price, subtotal, notes, is_extra_item)
                        VALUES (:realization_id, :category, :item_name, :qty, :price, :subtotal, :notes, :is_extra_item)";
            
            $stmtItem = $this->conn->prepare($queryItem);

            foreach ($items as $item) {
                $stmtItem->bindParam(":realization_id", $id);
                $stmtItem->bindParam(":category", $item['category']);
                $stmtItem->bindParam(":item_name", $item['item_name']);
                $stmtItem->bindParam(":qty", $item['qty']);
                $stmtItem->bindParam(":price", $item['price']);
                $stmtItem->bindParam(":subtotal", $item['subtotal']);
                $stmtItem->bindParam(":notes", $item['notes']);
                $stmtItem->bindParam(":is_extra_item", $item['is_extra_item']);
                
                if (!$stmtItem->execute()) {
                    throw new Exception("Error creating realization item.");
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function isDateRealized($rab_id, $date) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE rab_id = :rab_id AND date = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rab_id", $rab_id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    public function getRealizedDatesByRabIds($rab_ids) {
        if (empty($rab_ids)) return [];
        
        $placeholders = implode(',', array_fill(0, count($rab_ids), '?'));
        $query = "SELECT rab_id, date FROM " . $this->table_name . " WHERE rab_id IN ($placeholders)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_values($rab_ids));
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['rab_id']][] = $row['date'];
        }
        return $result;
    }

    public function getRealizationTotalsByRabIds($rab_ids) {
        if (empty($rab_ids)) return [];
        
        $placeholders = implode(',', array_fill(0, count($rab_ids), '?'));
        $query = "SELECT rab_id, SUM(total_amount) as total FROM " . $this->table_name . " 
                  WHERE rab_id IN ($placeholders) AND status != 'rejected'
                  GROUP BY rab_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_values($rab_ids));
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['rab_id']] = $row['total'];
        }
        return $result;
    }

    public function getCategoryTotalsByRealizationIds($realization_ids) {
        if (empty($realization_ids)) return [];
        
        $placeholders = implode(',', array_fill(0, count($realization_ids), '?'));
        $query = "SELECT realization_id, category, SUM(subtotal) as total 
                  FROM " . $this->items_table . " 
                  WHERE realization_id IN ($placeholders)
                  GROUP BY realization_id, category";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_values($realization_ids));
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['realization_id']][$row['category']] = $row['total'];
        }
        return $result;
    }

    public function updateStatusByRabId($rab_id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE rab_id = :rab_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":rab_id", $rab_id);
        return $stmt->execute();
    }
}
?>
