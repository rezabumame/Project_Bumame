<?php
include_once '../config/database.php';
include_once '../models/NationalHoliday.php';

class NationalHolidayController {
    private $db;
    private $holiday;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
            header("Location: index.php?page=login");
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->holiday = new NationalHoliday($this->db);
    }

    public function index() {
        $stmt = $this->holiday->readAll();
        include '../views/superadmin/holidays/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->holiday->holiday_date = $_POST['holiday_date'];
            $this->holiday->description = $_POST['description'];

            if ($this->holiday->create()) {
                header("Location: index.php?page=holidays&status=success");
            } else {
                header("Location: index.php?page=holidays&status=error");
            }
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $this->holiday->id = $_GET['id'];
            if ($this->holiday->delete()) {
                header("Location: index.php?page=holidays&status=deleted");
            } else {
                header("Location: index.php?page=holidays&status=error");
            }
        }
    }
}
?>