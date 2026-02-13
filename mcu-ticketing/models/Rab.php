<?php
class Rab {
    private $conn;
    private $table_name = "rabs";
    private $items_table = "rab_items";

    public $id;
    public $rab_number;
    public $project_id;
    public $created_by;
    public $status;
    public $total_personnel;
    public $total_transport;
    public $total_consumption;
    public $grand_total;
    public $location_type;
    public $selected_dates; // JSON
    public $total_participants;
    public $personnel_notes;
    
    // Approval
    public $approved_by_manager;
    public $approved_date_manager;
    public $approved_by_head;
    public $approved_date_head;
    public $approved_by_ceo;
    public $approved_date_ceo;
    
    public $rejection_reason;
    public $rejection_stage;
    
    // Cost (Manager)
    public $cost_value;
    public $cost_percentage;

    // Finance Workflow
    public $submitted_to_finance_at;
    public $submitted_to_finance_by;
    public $finance_paid_at;
    public $finance_paid_by;
    public $transfer_proof_path;
    public $settlement_proof_path;
    public $finance_note;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function generateRabNumber() {
        // Format: RAB/MM(romawi)/YYYY/XXX
        $month = date('n');
        $year = date('Y');
        $romans = array('', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII');
        $roman_month = $romans[$month];
        
        // Reset counter monthly
        // Check last RAB from this month/year
        $query = "SELECT rab_number FROM " . $this->table_name . " 
                  WHERE YEAR(created_at) = :year AND MONTH(created_at) = :month 
                  ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":year", $year);
        $stmt->bindParam(":month", $month);
        $stmt->execute();
        
        $last_number = 0;
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $parts = explode('/', $row['rab_number']);
            $last_number = intval(end($parts));
        }
        
        $new_sequence = str_pad($last_number + 1, 3, '0', STR_PAD_LEFT);
        
        return "RAB/{$roman_month}/{$year}/{$new_sequence}";
    }

    public function isProjectHasApprovedRabForDates($project_id, $selected_dates) {
        $query = "SELECT selected_dates FROM " . $this->table_name . " 
                  WHERE project_id = :project_id 
                  AND status IN ('approved', 'submitted_to_finance', 'advance_paid', 'need_approval_realization', 'realization_approved', 'completed', 'realization_rejected', 'closed')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existing_dates = json_decode($row['selected_dates'], true);
            if (is_array($existing_dates) && is_array($selected_dates)) {
                $intersection = array_intersect($existing_dates, $selected_dates);
                if (!empty($intersection)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    rab_number = :rab_number,
                    project_id = :project_id,
                    created_by = :created_by,
                    status = :status,
                    total_personnel = :total_personnel,
                    total_transport = :total_transport,
                    total_consumption = :total_consumption,
                    grand_total = :grand_total,
                    location_type = :location_type,
                    selected_dates = :selected_dates,
                    total_participants = :total_participants,
                    personnel_notes = :personnel_notes";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->project_id = htmlspecialchars(strip_tags($this->project_id));
        $this->location_type = htmlspecialchars(strip_tags($this->location_type));
        $this->personnel_notes = htmlspecialchars(strip_tags($this->personnel_notes));
        
        // Bind
        $stmt->bindParam(":rab_number", $this->rab_number);
        $stmt->bindParam(":project_id", $this->project_id);
        $stmt->bindParam(":created_by", $this->created_by);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":total_personnel", $this->total_personnel);
        $stmt->bindParam(":total_transport", $this->total_transport);
        $stmt->bindParam(":total_consumption", $this->total_consumption);
        $stmt->bindParam(":grand_total", $this->grand_total);
        $stmt->bindParam(":location_type", $this->location_type);
        $stmt->bindParam(":selected_dates", $this->selected_dates);
        $stmt->bindParam(":total_participants", $this->total_participants);
        $stmt->bindParam(":personnel_notes", $this->personnel_notes);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function addItems($items) {
        if (empty($items)) return true;

        $query = "INSERT INTO " . $this->items_table . " 
                  (rab_id, category, item_name, qty, days, price, subtotal, notes) 
                  VALUES (:rab_id, :category, :item_name, :qty, :days, :price, :subtotal, :notes)";
        
        $stmt = $this->conn->prepare($query);

        foreach ($items as $item) {
            $stmt->bindParam(":rab_id", $this->id);
            $stmt->bindParam(":category", $item['category']);
            $stmt->bindParam(":item_name", $item['item_name']);
            $stmt->bindParam(":qty", $item['qty']);
            $stmt->bindParam(":days", $item['days']);
            $stmt->bindParam(":price", $item['price']);
            $stmt->bindParam(":subtotal", $item['subtotal']);
            $stmt->bindParam(":notes", $item['notes']);
            
            if (!$stmt->execute()) {
                return false;
            }
        }
        return true;
    }

    public function deleteItems($rab_id) {
        $query = "DELETE FROM " . $this->items_table . " WHERE rab_id = :rab_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rab_id", $rab_id);
        return $stmt->execute();
    }
    
    public function getPendingCount($role) {
        $status = '';
        if ($role == 'manager_ops') $status = 'need_approval_manager';
        elseif ($role == 'head_ops') $status = 'need_approval_head';
        elseif ($role == 'ceo') $status = 'need_approval_ceo';
        elseif ($role == 'finance') $status = 'submitted_to_finance';
        
        if (empty($status)) return 0;
        
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getPendingRealizationCount($role) {
        $status = '';
        if ($role == 'manager_ops') $status = 'need_approval_realization';
        
        if (empty($status)) return 0;
        
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    total_personnel = :total_personnel,
                    total_transport = :total_transport,
                    total_consumption = :total_consumption,
                    grand_total = :grand_total,
                    location_type = :location_type,
                    selected_dates = :selected_dates,
                    total_participants = :total_participants,
                    personnel_notes = :personnel_notes,
                    status = :status,
                    cost_value = :cost_value,
                    cost_percentage = :cost_percentage,
                    updated_at = NOW()
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->location_type = htmlspecialchars(strip_tags($this->location_type));
        $this->personnel_notes = htmlspecialchars(strip_tags($this->personnel_notes));
        
        // Bind
        $stmt->bindParam(":total_personnel", $this->total_personnel);
        $stmt->bindParam(":total_transport", $this->total_transport);
        $stmt->bindParam(":total_consumption", $this->total_consumption);
        $stmt->bindParam(":grand_total", $this->grand_total);
        $stmt->bindParam(":location_type", $this->location_type);
        $stmt->bindParam(":selected_dates", $this->selected_dates);
        $stmt->bindParam(":total_participants", $this->total_participants);
        $stmt->bindParam(":personnel_notes", $this->personnel_notes);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":cost_value", $this->cost_value);
        $stmt->bindParam(":cost_percentage", $this->cost_percentage);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function delete() {
        // Delete items first
        $this->deleteItems($this->id);
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getById($id) {
        $query = "SELECT r.*, p.nama_project, p.korlap_id, p.alamat as project_location, u.full_name as creator_name,
                  m.full_name as manager_name, h.full_name as head_name, c.full_name as ceo_name
                  FROM " . $this->table_name . " r
                  LEFT JOIN projects p ON r.project_id = p.project_id
                  LEFT JOIN users u ON r.created_by = u.user_id
                  LEFT JOIN users m ON r.approved_by_manager = m.user_id
                  LEFT JOIN users h ON r.approved_by_head = h.user_id
                  LEFT JOIN users c ON r.approved_by_ceo = c.user_id
                  WHERE r.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItems($rab_id) {
        $query = "SELECT * FROM " . $this->items_table . " WHERE rab_id = :rab_id ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rab_id", $rab_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFilteredRabs($role, $user_id, $filters = [], $limit = null, $offset = null) {
        $query = "SELECT r.*, p.nama_project, u.full_name as creator_name, sp.sales_name 
                  FROM " . $this->table_name . " r
                  LEFT JOIN projects p ON r.project_id = p.project_id
                  LEFT JOIN users u ON r.created_by = u.user_id
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id";

        $conditions = [];
        $params = [];

        // Role-based filtering
        if ($role == 'korlap') {
            $conditions[] = "p.korlap_id = :user_id";
            $params[':user_id'] = $user_id;
        } elseif ($role == 'sales') {
            $conditions[] = "(p.sales_person_id = :user_id OR p.sales_id = :user_id)";
            $params[':user_id'] = $user_id;
        } elseif ($role == 'ceo') {
            // CEO only sees need_approval_ceo and approved
            $conditions[] = "r.status IN ('need_approval_ceo', 'approved')";
        } elseif ($role == 'finance') {
            // Finance only sees submitted_to_finance, advance_paid, and realization phases
            $conditions[] = "r.status IN ('submitted_to_finance', 'advance_paid', 'need_approval_realization', 'realization_approved', 'completed', 'realization_rejected')";
        }

        // Search Filter
        if (!empty($filters['search'])) {
            $conditions[] = "(r.rab_number LIKE :search OR p.nama_project LIKE :search)";
            $params[':search'] = "%" . $filters['search'] . "%";
        }

        // Status Filter
        if (!empty($filters['status']) && $filters['status'] != 'all') {
            $conditions[] = "r.status = :status_filter";
            $params[':status_filter'] = $filters['status'];
        }

        // Date Range Filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $conditions[] = "DATE(r.created_at) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // Sorting: Pending first, then newest
        // For Finance, submitted_to_finance is the "pending" state
        $query .= " ORDER BY 
                    CASE 
                        WHEN r.status IN ('need_approval_manager', 'need_approval_head', 'need_approval_ceo', 'submitted_to_finance') THEN 0 
                        ELSE 1 
                    END ASC,
                    r.created_at DESC, r.id DESC";

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

    public function countFilteredRabs($role, $user_id, $filters = []) {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table_name . " r
                  LEFT JOIN projects p ON r.project_id = p.project_id
                  LEFT JOIN users u ON r.created_by = u.user_id";

        $conditions = [];
        $params = [];

        // Role-based filtering
        if ($role == 'korlap') {
            $conditions[] = "p.korlap_id = :user_id";
            $params[':user_id'] = $user_id;
        } elseif ($role == 'sales') {
            $conditions[] = "(p.sales_person_id = :user_id OR p.sales_id = :user_id)";
            $params[':user_id'] = $user_id;
        } elseif ($role == 'ceo') {
            $conditions[] = "r.status IN ('need_approval_ceo', 'approved')";
        } elseif ($role == 'finance') {
            $conditions[] = "r.status IN ('submitted_to_finance', 'advance_paid', 'need_approval_realization', 'realization_approved', 'completed', 'realization_rejected')";
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(r.rab_number LIKE :search OR p.nama_project LIKE :search)";
            $params[':search'] = "%" . $filters['search'] . "%";
        }

        if (!empty($filters['status']) && $filters['status'] != 'all') {
            $conditions[] = "r.status = :status_filter";
            $params[':status_filter'] = $filters['status'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $conditions[] = "DATE(r.created_at) BETWEEN :start_date AND :end_date";
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

    public function getSummaryStats($role, $user_id) {
        $stats = [
            'total_month' => 0,
            'pending' => 0,
            'approved' => 0,
            'completed' => 0,
            'total_budget' => 0
        ];

        // Base condition
        $base_condition = "1=1";
        $params = [];
        if ($role == 'korlap') {
            $base_condition = "created_by = :user_id";
            $params[':user_id'] = $user_id;
        } elseif ($role == 'finance') {
             // Finance cares about submitted_to_finance and advance_paid
             $base_condition = "status IN ('submitted_to_finance', 'advance_paid', 'need_approval_realization', 'realization_approved', 'completed', 'realization_rejected')";
        }

        // Total RAB this month
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE " . $base_condition . " 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
                  AND MONTH(created_at) = MONTH(CURRENT_DATE())";
        $stmt = $this->conn->prepare($query);
        if ($role == 'korlap') $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $stats['total_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total Pending Approval
        $pending_statuses = "('need_approval_manager', 'need_approval_head', 'need_approval_ceo')";
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE " . $base_condition . " AND status IN $pending_statuses";
        $stmt = $this->conn->prepare($query);
        if ($role == 'korlap') $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $stats['pending'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total Approved
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE " . $base_condition . " AND status = 'approved'";
        $stmt = $this->conn->prepare($query);
        if ($role == 'korlap') $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['approved'] = $row['total'];

        // Total Completed
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE " . $base_condition . " AND status = 'completed'";
        $stmt = $this->conn->prepare($query);
        if ($role == 'korlap') $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['completed'] = $row['total'];

        // Total Budget (Active: Approved, Submitted, Paid, Completed, etc)
        // Excludes: draft, rejected, need_approval_*
        $query = "SELECT SUM(grand_total) as total_budget FROM " . $this->table_name . " 
                  WHERE " . $base_condition . " 
                  AND status IN ('approved', 'submitted_to_finance', 'advance_paid', 'need_approval_realization', 'realization_approved', 'completed')";
        $stmt = $this->conn->prepare($query);
        if ($role == 'korlap') $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_budget'] = $row['total_budget'] ?? 0;

        // Total Completed
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE " . $base_condition . " AND status = 'completed'";
        $stmt = $this->conn->prepare($query);
        if ($role == 'korlap') $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $stats['completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total Unpaid Advance (Uang Muka yang belum dibayar)
        // Status: need_approval_*, approved, submitted_to_finance (not yet advance_paid)
        $query = "SELECT SUM(total_transport) as total_unpaid FROM " . $this->table_name . " 
                  WHERE " . $base_condition . " 
                  AND status IN ('need_approval_manager', 'need_approval_head', 'need_approval_ceo', 'approved', 'submitted_to_finance')";
        $stmt = $this->conn->prepare($query);
        if ($role == 'korlap') $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['unpaid_advance'] = $row['total_unpaid'] ?? 0;

        return $stats;
    }

    public function getAvailableProjects($role, $user_id) {
        $query = "SELECT * FROM projects WHERE tanggal_mcu IS NOT NULL AND tanggal_mcu != ''";
        
        if ($role == 'korlap') {
            $query .= " AND korlap_id = :user_id";
            // Filter by specific statuses for Korlap as per requirements
            $query .= " AND status_project IN ('approved', 'in_progress_ops')";
        } elseif ($role == 'admin_ops') {
             // Admin Ops can see all approved and in-progress ops projects
             $query .= " AND status_project IN ('approved', 'in_progress_ops')";
        } elseif ($role == 'sales') {
            $query .= " AND (sales_person_id = :user_id OR sales_id = :user_id)";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($role == 'korlap' || $role == 'sales') {
            $stmt->bindParam(":user_id", $user_id);
        }
        $stmt->execute();
        return $stmt;
    }

    public function isProjectHasRab($project_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE project_id = :project_id AND status != 'rejected'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRabsByProjectIds($project_ids) {
        if (empty($project_ids)) return [];
        
        $placeholders = implode(',', array_fill(0, count($project_ids), '?'));
        $query = "SELECT id, project_id, selected_dates FROM " . $this->table_name . " 
                  WHERE project_id IN ($placeholders) AND status != 'rejected'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($project_ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateApproval($data) {
        $fields = [];
        $params = [':id' => $this->id];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        // Always update updated_at
        $fields[] = "updated_at = NOW()";
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute($params);
    }

    public function updateCostAnalysis($cost_value, $cost_percentage) {
        $query = "UPDATE " . $this->table_name . " 
                  SET cost_value = :cost_value, 
                      cost_percentage = :cost_percentage,
                      updated_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cost_value", $cost_value);
        $stmt->bindParam(":cost_percentage", $cost_percentage);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function submitToFinance($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'submitted_to_finance', 
                      submitted_to_finance_at = NOW(),
                      submitted_to_finance_by = :user_id,
                      updated_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function markAdvancePaid($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'advance_paid', 
                      finance_paid_at = NOW(),
                      finance_paid_by = :user_id,
                      transfer_proof_path = :proof_path,
                      finance_note = :note,
                      updated_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":proof_path", $data['proof_path']);
        $stmt->bindParam(":note", $data['note']);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function updateRealizationTotal($rab_id) {
        // 1. Calculate Sum
        $query = "SELECT SUM(total_amount) as total FROM rab_realizations WHERE rab_id = :rab_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rab_id", $rab_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $row['total'] ?? 0;

        // 2. Update RAB
        $queryUpdate = "UPDATE " . $this->table_name . " SET total_realization = :total, updated_at = NOW() WHERE id = :id";
        $stmtUpdate = $this->conn->prepare($queryUpdate);
        $stmtUpdate->bindParam(":total", $total);
        $stmtUpdate->bindParam(":id", $rab_id);
        
        return $stmtUpdate->execute();
    }
}
?>