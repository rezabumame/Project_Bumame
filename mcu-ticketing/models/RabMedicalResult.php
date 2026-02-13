<?php
class RabMedicalResult {
    private $conn;
    private $table = "rab_medical_results";
    private $table_dates = "rab_medical_result_dates";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table . " 
                      (project_id, needs_hardcopy, send_whatsapp, send_email, notes, status, created_by)
                      VALUES (:pid, :hardcopy, :whatsapp, :email, :notes, 'draft', :uid)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":pid", $data['project_id']);
            $stmt->bindParam(":hardcopy", $data['needs_hardcopy']);
            $stmt->bindParam(":whatsapp", $data['send_whatsapp']);
            $stmt->bindParam(":email", $data['send_email']);
            $stmt->bindParam(":notes", $data['notes']);
            $stmt->bindParam(":uid", $data['created_by']);
            $stmt->execute();
            $rab_id = $this->conn->lastInsertId();

            // Insert Dates
            if (!empty($data['dates'])) {
                $qDate = "INSERT INTO " . $this->table_dates . " (rab_id, date, personnel_count, personnel_details) VALUES (:rid, :date, :count, :details)";
                $stmtDate = $this->conn->prepare($qDate);
                foreach ($data['dates'] as $d) {
                    $stmtDate->execute([
                        ':rid' => $rab_id,
                        ':date' => $d['date'],
                        ':count' => $d['count'],
                        ':details' => $d['details'] ?? null
                    ]);
                }
            }

            $this->conn->commit();
            return $rab_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE " . $this->table . " 
                      SET project_id = :pid, needs_hardcopy = :hardcopy, send_whatsapp = :whatsapp, send_email = :email, notes = :notes, updated_at = NOW()";
            
            if (isset($data['status'])) {
                $query .= ", status = :status";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":pid", $data['project_id']);
            $stmt->bindParam(":hardcopy", $data['needs_hardcopy']);
            $stmt->bindParam(":whatsapp", $data['send_whatsapp']);
            $stmt->bindParam(":email", $data['send_email']);
            $stmt->bindParam(":notes", $data['notes']);
            if (isset($data['status'])) {
                $stmt->bindParam(":status", $data['status']);
            }
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            // Replace Dates
            // First delete existing
            $qDelete = "DELETE FROM " . $this->table_dates . " WHERE rab_id = :rid";
            $stmtDelete = $this->conn->prepare($qDelete);
            $stmtDelete->bindParam(":rid", $id);
            $stmtDelete->execute();

            // Insert new dates
            if (!empty($data['dates'])) {
                $qDate = "INSERT INTO " . $this->table_dates . " (rab_id, date, personnel_count, personnel_details) VALUES (:rid, :date, :count, :details)";
                $stmtDate = $this->conn->prepare($qDate);
                foreach ($data['dates'] as $d) {
                    $stmtDate->execute([
                        ':rid' => $id,
                        ':date' => $d['date'],
                        ':count' => $d['count'],
                        ':details' => $d['details'] ?? null
                    ]);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getAll($filters = [], $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT r.*, p.nama_project, u.full_name as creator_name,
                  (SELECT SUM(personnel_count) FROM rab_medical_result_dates WHERE rab_id = r.id) as total_personnel,
                  (SELECT COUNT(*) FROM rab_medical_result_dates WHERE rab_id = r.id) as total_days
                  FROM " . $this->table . " r
                  JOIN projects p ON r.project_id = p.project_id
                  JOIN users u ON r.created_by = u.user_id
                  WHERE 1=1";
        
        if (!empty($filters['status'])) {
            $query .= " AND r.status = :status";
        }
        
        if (!empty($filters['status_not'])) {
            $query .= " AND r.status != :status_not";
        }
        
        $query .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        if (!empty($filters['status_not'])) {
            $stmt->bindParam(':status_not', $filters['status_not']);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " r WHERE 1=1";
        
        if (!empty($filters['status'])) {
            $query .= " AND r.status = :status";
        }
        
        if (!empty($filters['status_not'])) {
            $query .= " AND r.status != :status_not";
        }
        
        $stmt = $this->conn->prepare($query);
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        if (!empty($filters['status_not'])) {
            $stmt->bindParam(':status_not', $filters['status_not']);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getById($id) {
        $query = "SELECT r.*, p.nama_project, p.company_name, u.full_name as creator_name,
                         m.full_name as approved_manager_name, h.full_name as approved_head_name
                  FROM " . $this->table . " r
                  JOIN projects p ON r.project_id = p.project_id
                  JOIN users u ON r.created_by = u.user_id
                  LEFT JOIN users m ON r.approved_manager_by = m.user_id
                  LEFT JOIN users h ON r.approved_head_by = h.user_id
                  WHERE r.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $rab = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rab) {
            $rab['dates'] = $this->getDates($id);
        }
        return $rab;
    }

    public function getDates($rab_id) {
        $query = "SELECT * FROM " . $this->table_dates . " WHERE rab_id = :rid ORDER BY date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rid", $rab_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approveManager($id, $user_id) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'approved_manager', approved_manager_by = :uid, approved_manager_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $user_id);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function approveHead($id, $user_id) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'approved_head', approved_head_by = :uid, approved_head_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $user_id);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function reject($id, $user_id, $reason) {
        $query = "UPDATE " . $this->table . " SET status = 'rejected', rejection_reason = :reason WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":reason", $reason);
        return $stmt->execute();
    }

    public function countPendingApprovals($role) {
        $status = '';
        if ($role == 'manager_ops') {
            $status = 'submitted';
        } elseif ($role == 'head_ops') {
            $status = 'approved_manager';
        } else {
            return 0;
        }

        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    }

    public function submit($id) {
        $query = "UPDATE " . $this->table . " SET status = 'submitted' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function markAsCompleted($id) {
        $query = "UPDATE " . $this->table . " SET status = 'completed', updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Dashboard Statistics
    public function getStats() {
        $stats = [
            'active_projects' => 0,
            'daily_needs' => 0,
            'pending_approval' => 0
        ];

        // Active Projects (submitted or approved or completed)
        $q1 = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE status IN ('submitted', 'approved_manager', 'approved_head', 'completed')";
        $stmt1 = $this->conn->prepare($q1);
        $stmt1->execute();
        $stats['active_projects'] = $stmt1->fetch(PDO::FETCH_ASSOC)['count'];

        // Daily Needs (Sum of personnel_count for today)
        $q2 = "SELECT SUM(personnel_count) as count FROM " . $this->table_dates . " WHERE date = CURDATE()";
        $stmt2 = $this->conn->prepare($q2);
        $stmt2->execute();
        $stats['daily_needs'] = $stmt2->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Pending Approval
        $q3 = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE status = 'submitted'";
        $stmt3 = $this->conn->prepare($q3);
        $stmt3->execute();
        $stats['pending_approval'] = $stmt3->fetch(PDO::FETCH_ASSOC)['count'];

        return $stats;
    }
}
