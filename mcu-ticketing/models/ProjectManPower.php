<?php
class ProjectManPower {
    private $conn;
    private $table_name = "project_man_power";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function assign($data) {
        // Check overlap first
        if ($this->checkOverlap($data['man_power_id'], $data['date'], $data['project_id'])) {
            return ['status' => false, 'message' => 'Petugas sudah ditugaskan di project lain pada tanggal tersebut.'];
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (project_id, man_power_id, role, date, notes, doctor_details, created_by) 
                  VALUES (:project_id, :man_power_id, :role, :date, :notes, :doctor_details, :created_by)";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $data['role'] = htmlspecialchars(strip_tags($data['role']));
        $data['notes'] = htmlspecialchars(strip_tags($data['notes']));
        // doctor_details is JSON, so we don't strip tags strictly or we assume it's safe JSON string. 
        // Better to not strip tags from JSON string to avoid breaking it, but htmlspecialchars is ok.
        // Actually, let's just bind it.

        $stmt->bindParam(":project_id", $data['project_id']);
        $stmt->bindParam(":man_power_id", $data['man_power_id']);
        $stmt->bindParam(":role", $data['role']);
        $stmt->bindParam(":date", $data['date']);
        $stmt->bindParam(":notes", $data['notes']);
        $stmt->bindParam(":doctor_details", $data['doctor_details']);
        $stmt->bindParam(":created_by", $data['created_by']);

        if ($stmt->execute()) {
            return ['status' => true, 'id' => $this->conn->lastInsertId()];
        }
        return ['status' => false, 'message' => 'Database error.'];
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getAssignments($project_id) {
        $query = "SELECT pmp.*, mp.name as man_power_name, mp.status as man_power_status, mp.email as man_power_email, u.full_name as created_by_name 
                  FROM " . $this->table_name . " pmp
                  JOIN man_powers mp ON pmp.man_power_id = mp.id
                  LEFT JOIN users u ON pmp.created_by = u.user_id
                  WHERE pmp.project_id = :project_id
                  ORDER BY pmp.date ASC, pmp.role ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkOverlap($man_power_id, $date, $current_project_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE man_power_id = :man_power_id 
                  AND date = :date 
                  AND project_id != :project_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":man_power_id", $man_power_id);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":project_id", $current_project_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] > 0;
    }

    public function getSummaryByDate($project_id) {
        $query = "SELECT date, role, COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE project_id = :project_id 
                  GROUP BY date, role";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $summary = [];
        foreach ($results as $row) {
            $summary[$row['date']][$row['role']] = $row['total'];
        }
        return $summary;
    }

    public function getAssignmentsByDateRange($start_date, $end_date) {
        $query = "SELECT pmp.date, pmp.man_power_id, pmp.project_id, pmp.role, p.nama_project, mp.name as man_power_name, mp.skills
                  FROM " . $this->table_name . " pmp
                  JOIN projects p ON pmp.project_id = p.project_id
                  JOIN man_powers mp ON pmp.man_power_id = mp.id
                  WHERE pmp.date BETWEEN :start_date AND :end_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>