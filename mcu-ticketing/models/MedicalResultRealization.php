<?php
class MedicalResultRealization {
    private $conn;
    private $table = "medical_result_realizations";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (rab_id, user_id, date, notes)
                  VALUES (:rid, :uid, :date, :notes)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rid", $data['rab_id']);
        $stmt->bindParam(":uid", $data['user_id']);
        $stmt->bindParam(":date", $data['date']);
        $stmt->bindParam(":notes", $data['notes']);
        
        return $stmt->execute();
    }

    public function getByRabId($rab_id) {
        $query = "SELECT r.*, u.full_name as user_name 
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.user_id
                  WHERE r.rab_id = :rid
                  ORDER BY r.date ASC, u.full_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rid", $rab_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkOverlap($user_id, $date) {
        // Check if user is already assigned to another project on the same date
        $query = "SELECT r.*, p.nama_project, rab.project_id
                  FROM " . $this->table . " r
                  JOIN rab_medical_results rab ON r.rab_id = rab.id
                  JOIN projects p ON rab.project_id = p.project_id
                  WHERE r.user_id = :uid AND r.date = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $user_id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function deleteByRabId($rab_id) {
        $query = "DELETE FROM " . $this->table . " WHERE rab_id = :rid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rid", $rab_id);
        return $stmt->execute();
    }
    public function getWorkloadForMonth($month, $year, $filters = []) {
        $query = "SELECT 
                    r.date, 
                    rab.project_id, 
                    p.nama_project, 
                    r.user_id as dw_user_id, 
                    udw.full_name as dw_user_name,
                    rab.created_by as kohas_user_id, 
                    uk.full_name as kohas_name,
                    r.notes
                  FROM " . $this->table . " r
                  JOIN rab_medical_results rab ON r.rab_id = rab.id
                  JOIN projects p ON rab.project_id = p.project_id
                  JOIN users udw ON r.user_id = udw.user_id
                  JOIN users uk ON rab.created_by = uk.user_id
                  WHERE MONTH(r.date) = :month AND YEAR(r.date) = :year";
        
        if (!empty($filters['project_id'])) {
            $query .= " AND rab.project_id = :pid";
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND rab.created_by = :uid";
        }
        
        $query .= " ORDER BY r.date ASC, p.nama_project ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":month", $month);
        $stmt->bindParam(":year", $year);
        
        if (!empty($filters['project_id'])) {
            $stmt->bindParam(":pid", $filters['project_id']);
        }
        
        if (!empty($filters['user_id'])) {
            $stmt->bindParam(":uid", $filters['user_id']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByProjectId($project_id) {
        $query = "SELECT r.*, u.full_name as user_name, uk.full_name as kohas_name
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.user_id
                  JOIN rab_medical_results rab ON r.rab_id = rab.id
                  JOIN users uk ON rab.created_by = uk.user_id
                  WHERE rab.project_id = :pid
                  ORDER BY r.date ASC, u.full_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pid", $project_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>