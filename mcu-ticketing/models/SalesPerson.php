<?php
class SalesPerson {
    private $conn;
    private $table_name = "sales_persons";

    public $id;
    public $sales_name;
    public $sales_manager_id;
    public $user_id; // Linked User ID
    public $manager_name; // For display
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all sales persons with manager name
    public function readAll() {
        $query = "SELECT sp.*, sm.name as manager_name 
                  FROM " . $this->table_name . " sp
                  LEFT JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                  ORDER BY sp.sales_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create new sales person
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET sales_name=:sales_name, sales_manager_id=:sales_manager_id, user_id=:user_id";
        $stmt = $this->conn->prepare($query);

        $this->sales_name = htmlspecialchars(strip_tags($this->sales_name));
        $this->sales_manager_id = !empty($this->sales_manager_id) ? htmlspecialchars(strip_tags($this->sales_manager_id)) : null;
        $this->user_id = !empty($this->user_id) ? htmlspecialchars(strip_tags($this->user_id)) : null;

        $stmt->bindParam(":sales_name", $this->sales_name);
        $stmt->bindParam(":sales_manager_id", $this->sales_manager_id);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get single sales person
    public function getById() {
        $query = "SELECT sp.*, sm.name as manager_name 
                  FROM " . $this->table_name . " sp
                  LEFT JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                  WHERE sp.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->sales_name = $row['sales_name'];
            $this->sales_manager_id = $row['sales_manager_id'];
            $this->user_id = $row['user_id'];
            $this->manager_name = $row['manager_name'];
            return true;
        }
        return false;
    }

    // Update sales person
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET sales_name = :sales_name, sales_manager_id = :sales_manager_id, user_id = :user_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->sales_name = htmlspecialchars(strip_tags($this->sales_name));
        $this->sales_manager_id = !empty($this->sales_manager_id) ? htmlspecialchars(strip_tags($this->sales_manager_id)) : null;
        $this->user_id = !empty($this->user_id) ? htmlspecialchars(strip_tags($this->user_id)) : null;
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':sales_name', $this->sales_name);
        $stmt->bindParam(':sales_manager_id', $this->sales_manager_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete sales person
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>