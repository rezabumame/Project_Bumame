<?php
class SalesManager {
    private $conn;
    private $table_name = "sales_managers";

    public $id;
    public $manager_name;
    public $user_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT *, name as manager_name FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, user_id) VALUES (:manager_name, :user_id)";
        $stmt = $this->conn->prepare($query);

        $this->manager_name = htmlspecialchars(strip_tags($this->manager_name));
        $this->user_id = !empty($this->user_id) ? htmlspecialchars(strip_tags($this->user_id)) : null;

        $stmt->bindParam(":manager_name", $this->manager_name);
        $stmt->bindParam(":user_id", $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->manager_name = $row['name'];
            $this->user_id = $row['user_id'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = :manager_name, user_id = :user_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->manager_name = htmlspecialchars(strip_tags($this->manager_name));
        $this->user_id = !empty($this->user_id) ? htmlspecialchars(strip_tags($this->user_id)) : null;
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":manager_name", $this->manager_name);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>