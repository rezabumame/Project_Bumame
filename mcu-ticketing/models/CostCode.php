<?php
class CostCode {
    private $conn;
    private $table_name = "cost_codes";

    public $id;
    public $code;
    public $category;
    public $lookup_value;
    public $description;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY category, code";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET code=:code, category=:category, lookup_value=:lookup_value, description=:description";
        
        $stmt = $this->conn->prepare($query);

        $this->code = htmlspecialchars(strip_tags($this->code));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->lookup_value = htmlspecialchars(strip_tags($this->lookup_value));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":lookup_value", $this->lookup_value);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET code=:code, category=:category, lookup_value=:lookup_value, description=:description
                WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->code = htmlspecialchars(strip_tags($this->code));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->lookup_value = htmlspecialchars(strip_tags($this->lookup_value));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":lookup_value", $this->lookup_value);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    public function getByCategory($category) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category = :category ORDER BY code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category", $category);
        $stmt->execute();
        return $stmt;
    }

    public function getEmergencyTypes() {
        $query = "SELECT lookup_value as name, code as expense_code 
                  FROM " . $this->table_name . " 
                  WHERE description LIKE 'emergency cost' 
                  ORDER BY lookup_value ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCodeByLookupValue($lookupValue) {
        $query = "SELECT code FROM " . $this->table_name . " WHERE lookup_value = :lookup_value LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":lookup_value", $lookupValue);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['code'] : null;
    }
}
?>
