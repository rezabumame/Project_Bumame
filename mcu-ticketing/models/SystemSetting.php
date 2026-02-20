<?php
class SystemSetting {
    private $conn;
    private $table_name = "system_settings";

    public $setting_key;
    public $setting_value;
    public $description;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY setting_key ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function get($key) {
        $query = "SELECT setting_value FROM " . $this->table_name . " WHERE setting_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['setting_value'];
        }
        return null;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET setting_value = :value
                WHERE setting_key = :key";

        $stmt = $this->conn->prepare($query);

        $this->setting_value = $this->setting_value;
        $this->setting_key = $this->setting_key;

        $stmt->bindParam(":value", $this->setting_value);
        $stmt->bindParam(":key", $this->setting_key);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET setting_key = :key, setting_value = :value, description = :description";

        $stmt = $this->conn->prepare($query);

        // $this->setting_key = htmlspecialchars(strip_tags($this->setting_key));
        // $this->setting_value = htmlspecialchars(strip_tags($this->setting_value));
        // $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":key", $this->setting_key);
        $stmt->bindParam(":value", $this->setting_value);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($key) {
        $query = "DELETE FROM " . $this->table_name . " WHERE setting_key = :key";
        $stmt = $this->conn->prepare($query);
        $this->setting_key = $key;
        $stmt->bindParam(":key", $this->setting_key);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateDescription($key, $description) {
        $query = "UPDATE " . $this->table_name . "
                SET description = :description
                WHERE setting_key = :key";
        $stmt = $this->conn->prepare($query);
        $this->setting_key = $key;
        $description = $description;
        $stmt->bindParam(":key", $this->setting_key);
        $stmt->bindParam(":description", $description);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>