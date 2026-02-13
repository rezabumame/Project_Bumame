<?php
class ChatParticipant {
    private $conn;
    private $table_name = "project_chat_participants";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function subscribe($project_id, $user_id) {
        $query = "INSERT INTO " . $this->table_name . " (project_id, user_id) VALUES (:project_id, :user_id)
                  ON DUPLICATE KEY UPDATE project_id = project_id"; // Do nothing if exists
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    public function toggleMute($project_id, $user_id) {
        // First ensure record exists
        $this->subscribe($project_id, $user_id);
        
        $query = "UPDATE " . $this->table_name . " 
                  SET is_muted = NOT is_muted 
                  WHERE project_id = :project_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user_id);
        
        if($stmt->execute()) {
             return $this->getParticipantStatus($project_id, $user_id);
        }
        return false;
    }

    public function markRead($project_id, $user_id) {
        // First ensure record exists
        $this->subscribe($project_id, $user_id);

        $query = "UPDATE " . $this->table_name . " 
                  SET last_read_at = NOW() 
                  WHERE project_id = :project_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }
    
    public function getParticipantStatus($project_id, $user_id) {
        $query = "SELECT is_muted, last_read_at FROM " . $this->table_name . " 
                  WHERE project_id = :project_id AND user_id = :user_id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return ['is_muted' => 0, 'last_read_at' => null];
    }

    public function getSubscribers($project_id) {
        $query = "SELECT user_id, is_muted FROM " . $this->table_name . " WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        return $stmt;
    }
    
    public function getUnreadCount($project_id, $user_id) {
        // Get last_read_at
        $status = $this->getParticipantStatus($project_id, $user_id);
        $last_read = $status['last_read_at'];
        
        $query = "SELECT COUNT(*) as unread_count FROM project_comments 
                  WHERE project_id = :project_id";
        
        if ($last_read) {
            $query .= " AND created_at > :last_read";
        }
        
        // Don't count own comments
        $query .= " AND user_id != :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user_id);
        if ($last_read) {
            $stmt->bindParam(':last_read', $last_read);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['unread_count'];
    }
}
?>