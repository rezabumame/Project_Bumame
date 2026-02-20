<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public $id;
    public $user_id;
    public $type;
    public $message;
    public $link;
    public $is_read;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    user_id = :user_id,
                    type = :type,
                    message = :message,
                    link = :link,
                    is_read = 0";

        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->message = htmlspecialchars(strip_tags($this->message));
        $this->link = htmlspecialchars(strip_tags($this->link));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":link", $this->link);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readUnreadByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE user_id = ? AND is_read = 0
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function markAsRead($id, $user_id) {
        $query = "UPDATE " . $this->table_name . "
                SET is_read = 1
                WHERE id = ? AND user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->bindParam(2, $user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    public function markAllAsRead($user_id) {
        $query = "UPDATE " . $this->table_name . "
                SET is_read = 1
                WHERE user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
