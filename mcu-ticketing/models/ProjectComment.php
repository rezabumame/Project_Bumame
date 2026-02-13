<?php
class ProjectComment {
    private $conn;
    private $table_name = "project_comments";

    public $id;
    public $project_id;
    public $user_id;
    public $parent_id;
    public $message;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    project_id = :project_id,
                    user_id = :user_id,
                    parent_id = :parent_id,
                    message = :message";

        $stmt = $this->conn->prepare($query);

        $this->project_id = htmlspecialchars(strip_tags($this->project_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        // parent_id can be null
        $this->message = htmlspecialchars(strip_tags($this->message));

        $stmt->bindParam(":project_id", $this->project_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":parent_id", $this->parent_id);
        $stmt->bindParam(":message", $this->message);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readByProject($project_id) {
        // Updated query to fetch parent message info
        $query = "SELECT c.id, c.project_id, c.user_id, c.message, c.created_at, c.parent_id,
                         u.full_name, u.role,
                         p.message as parent_message, 
                         pu.full_name as parent_user_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.user_id = u.user_id
                  LEFT JOIN " . $this->table_name . " p ON c.parent_id = p.id
                  LEFT JOIN users pu ON p.user_id = pu.user_id
                  WHERE c.project_id = ?
                  ORDER BY c.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $project_id);
        $stmt->execute();

        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT c.*, u.full_name, u.username as email 
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.user_id = u.user_id
                  WHERE c.id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
