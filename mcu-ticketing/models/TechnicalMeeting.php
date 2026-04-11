<?php
class TechnicalMeeting {
    private $conn;
    private $table_name = "technical_meetings";

    public $id;
    public $project_id;
    public $tm_date;
    public $tm_type;
    public $setting_alat_date;
    public $notes;
    public $tm_file_path;
    public $layout_file_path;
    public $created_by;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByProject($project_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE project_id = :project_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
