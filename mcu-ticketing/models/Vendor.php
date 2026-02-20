<?php
class Vendor {
    private $conn;
    private $table_name = "vendors";

    public $vendor_id;
    public $vendor_name;
    public $pic_name;
    public $phone_number;
    public $services;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT *, name as vendor_name, id as vendor_id FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT *, name as vendor_name, id as vendor_id FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, pic_name, phone_number, services) 
                  VALUES (:vendor_name, :pic_name, :phone_number, :services)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->vendor_name = htmlspecialchars(strip_tags($this->vendor_name ?? ''));
        $this->pic_name = htmlspecialchars(strip_tags($this->pic_name ?? ''));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number ?? ''));
        $this->services = htmlspecialchars(strip_tags($this->services ?? ''));

        // Bind
        $stmt->bindParam(":vendor_name", $this->vendor_name);
        $stmt->bindParam(":pic_name", $this->pic_name);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":services", $this->services);

        if ($stmt->execute()) {
            return true;
        }
        
        // Log error if needed
        // error_log(print_r($stmt->errorInfo(), true));
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET name = :vendor_name,
                      pic_name = :pic_name,
                      phone_number = :phone_number,
                      services = :services
                  WHERE id = :vendor_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->vendor_name = htmlspecialchars(strip_tags($this->vendor_name ?? ''));
        $this->pic_name = htmlspecialchars(strip_tags($this->pic_name ?? ''));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number ?? ''));
        $this->services = htmlspecialchars(strip_tags($this->services ?? ''));
        $this->vendor_id = htmlspecialchars(strip_tags($this->vendor_id ?? ''));

        // Bind
        $stmt->bindParam(":vendor_name", $this->vendor_name);
        $stmt->bindParam(":pic_name", $this->pic_name);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":services", $this->services);
        $stmt->bindParam(":vendor_id", $this->vendor_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
