<?php
class NationalHoliday {
    private $conn;
    private $table_name = "national_holidays";

    public $id;
    public $holiday_date;
    public $description;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY holiday_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET holiday_date=:holiday_date, description=:description";
        $stmt = $this->conn->prepare($query);

        $this->holiday_date = htmlspecialchars(strip_tags($this->holiday_date));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":holiday_date", $this->holiday_date);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get array of holiday dates for JS/Validation
    public function getHolidayDates() {
        $query = "SELECT holiday_date FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $dates = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dates[] = $row['holiday_date'];
        }
        return $dates;
    }
}
?>