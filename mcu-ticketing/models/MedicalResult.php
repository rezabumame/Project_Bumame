<?php
class MedicalResult {
    private $conn;
    private $table_results = "medical_results";
    private $table_items = "medical_result_items";
    private $table_followups = "medical_result_followups";

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- Medical Result (Project Level) ---

    public function getByProjectId($project_id) {
        $query = "SELECT * FROM " . $this->table_results . " WHERE project_id = :project_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createOrUpdate($data) {
        // Check if exists
        $exists = $this->getByProjectId($data['project_id']);
        
        if ($exists) {
            $query = "UPDATE " . $this->table_results . " 
                      SET link_summary_excel = :excel, 
                          link_summary_dashboard = :dashboard
                      WHERE project_id = :project_id";
        } else {
            $query = "INSERT INTO " . $this->table_results . " 
                      (project_id, link_summary_excel, link_summary_dashboard, status)
                      VALUES (:project_id, :excel, :dashboard, 'IN_PROGRESS')";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $data['project_id']);
        $stmt->bindParam(":excel", $data['link_summary_excel']);
        $stmt->bindParam(":dashboard", $data['link_summary_dashboard']);
        
        return $stmt->execute();
    }

    public function setStatus($project_id, $status) {
        $exists = $this->getByProjectId($project_id);
        if ($exists) {
            $query = "UPDATE " . $this->table_results . " SET status = :status WHERE project_id = :project_id";
        } else {
            $query = "INSERT INTO " . $this->table_results . " (project_id, status) VALUES (:project_id, :status)";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":project_id", $project_id);
        return $stmt->execute();
    }

    public function setPendingParticipants($project_id, $count, $notes) {
        $query = "UPDATE " . $this->table_results . " 
                  SET status = 'PENDING_PARTICIPANTS',
                      pending_participants_count = :count,
                      pending_participants_notes = :notes
                  WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":count", $count);
        $stmt->bindParam(":notes", $notes);
        $stmt->bindParam(":project_id", $project_id);
        return $stmt->execute();
    }

    public function updateStatus($project_id, $status) {
        $query = "UPDATE " . $this->table_results . " SET status = :status WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":project_id", $project_id);
        return $stmt->execute();
    }

    // --- Items (Date Level) ---

    public function getItemsByResultId($result_id) {
        $query = "SELECT mri.*, u.full_name as assignee_name 
                  FROM " . $this->table_items . " mri 
                  LEFT JOIN users u ON mri.assigned_to_user_id = u.user_id 
                  WHERE mri.medical_result_id = :id 
                  ORDER BY mri.date_mcu ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $result_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getItemById($id) {
        $query = "SELECT * FROM " . $this->table_items . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveItem($data) {
        if (isset($data['id']) && !empty($data['id'])) {
            // Update
            $query = "UPDATE " . $this->table_items . " SET 
                      actual_pax_checked = :checked,
                      actual_pax_released = :released,
                      release_date = :rdate,
                      link_pdf = :pdf,
                      notes = :notes,
                      has_difference = :has_diff,
                      difference_names = :diff_names,
                      difference_reason = :diff_reason,
                      tat_overdue = :tat_overdue,
                      tat_issue = :tat_issue,
                      tat_issue_notes = :tat_issue_notes,
                      status = :status,
                      assigned_to_user_id = :assigned_user
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $data['id']);
        } else {
            // Create
            $query = "INSERT INTO " . $this->table_items . " 
                      (medical_result_id, date_mcu, actual_pax_checked, actual_pax_released, release_date, link_pdf, notes, has_difference, difference_names, difference_reason, tat_overdue, tat_issue, tat_issue_notes, status, assigned_to_user_id)
                      VALUES (:mid, :mdate, :checked, :released, :rdate, :pdf, :notes, :has_diff, :diff_names, :diff_reason, :tat_overdue, :tat_issue, :tat_issue_notes, :status, :assigned_user)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":mid", $data['medical_result_id']);
            $stmt->bindParam(":mdate", $data['date_mcu']);
        }

        $stmt->bindParam(":checked", $data['actual_pax_checked']);
        $stmt->bindParam(":released", $data['actual_pax_released']);
        $stmt->bindParam(":rdate", $data['release_date']);
        $stmt->bindParam(":pdf", $data['link_pdf']);
        $stmt->bindParam(":notes", $data['notes']);
        $stmt->bindParam(":has_diff", $data['has_difference']);
        $stmt->bindParam(":diff_names", $data['difference_names']);
        $stmt->bindParam(":diff_reason", $data['difference_reason']);
        $stmt->bindParam(":tat_overdue", $data['tat_overdue']);
        $stmt->bindParam(":tat_issue", $data['tat_issue']);
        $stmt->bindParam(":tat_issue_notes", $data['tat_issue_notes']);
        $stmt->bindParam(":status", $data['status']);
        
        // Handle nullable assignment
        $assigned_val = !empty($data['assigned_to_user_id']) ? $data['assigned_to_user_id'] : null;
        $stmt->bindParam(":assigned_user", $assigned_val);

        return $stmt->execute();
    }

    // --- Followups (Susulan) ---

    public function getFollowupsByItemId($item_id) {
        $query = "SELECT * FROM " . $this->table_followups . " WHERE medical_result_item_id = :id ORDER BY release_date_susulan ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $item_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFollowupById($id) {
        $query = "SELECT * FROM " . $this->table_followups . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveFollowup($data) {
        if (isset($data['id']) && !empty($data['id'])) {
            $query = "UPDATE " . $this->table_followups . " SET 
                      pax_susulan = :pax,
                      pax_names = :pax_names,
                      release_date_susulan = :rdate,
                      reason = :reason,
                      tat_overdue = :tat_overdue,
                      tat_issue = :tat_issue,
                      tat_issue_notes = :tat_issue_notes
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $data['id']);
        } else {
            $query = "INSERT INTO " . $this->table_followups . " 
                      (medical_result_item_id, pax_susulan, pax_names, release_date_susulan, reason, tat_overdue, tat_issue, tat_issue_notes)
                      VALUES (:item_id, :pax, :pax_names, :rdate, :reason, :tat_overdue, :tat_issue, :tat_issue_notes)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":item_id", $data['medical_result_item_id']);
        }

        $stmt->bindParam(":pax", $data['pax_susulan']);
        $stmt->bindParam(":pax_names", $data['pax_names']);
        $stmt->bindParam(":rdate", $data['release_date_susulan']);
        $stmt->bindParam(":reason", $data['reason']);
        $stmt->bindParam(":tat_overdue", $data['tat_overdue']);
        $stmt->bindParam(":tat_issue", $data['tat_issue']);
        $stmt->bindParam(":tat_issue_notes", $data['tat_issue_notes']);

        return $stmt->execute();
    }
    
    public function updateAssignment($id, $user_id) {
        $query = "UPDATE " . $this->table_items . " SET assigned_to_user_id = :uid WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $assigned_val = !empty($user_id) ? $user_id : null;
        $stmt->bindParam(":uid", $assigned_val);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function isUserAssignedToProject($project_id, $user_id) {
        // Get medical_result_id for this project
        $mr = $this->getByProjectId($project_id);
        if (!$mr) return false;
        
        // Check if ANY item is assigned to this user
        $query = "SELECT COUNT(*) as count FROM " . $this->table_items . " 
                  WHERE medical_result_id = :mid AND assigned_to_user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":mid", $mr['id']);
        $stmt->bindParam(":uid", $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['count'] > 0;
    }
}
?>
