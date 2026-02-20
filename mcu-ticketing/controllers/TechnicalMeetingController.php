<?php


class TechnicalMeetingController extends BaseController {
    private $project;
    private $conn; // Legacy variable support

    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->project = $this->loadModel('Project');
        $this->conn = $this->db; // Keep alias if methods use $this->conn
    }

    private function checkCreateAccess() {
        $allowed = ['korlap', 'admin_ops', 'superadmin'];
        if (!in_array($_SESSION['role'], $allowed)) {
            echo "<script>alert('You do not have permission to create or edit Technical Meetings.'); window.location.href='index.php?page=technical_meeting_list';</script>";
            exit;
        }
    }

    public function index() {
        // Pagination
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Base Query
        $sql = "FROM technical_meetings tm JOIN projects p ON tm.project_id = p.project_id";
        $where = "";
        $params = [];

        if ($_SESSION['role'] == 'korlap') {
            $where = " WHERE p.korlap_id = :user_id";
            $params[':user_id'] = $_SESSION['user_id'];
        }

        // 1. Get Count
        $countQuery = "SELECT COUNT(*) as total " . $sql . $where;
        $stmtCount = $this->conn->prepare($countQuery);
        foreach ($params as $key => $val) {
            $stmtCount->bindValue($key, $val);
        }
        $stmtCount->execute();
        $total_rows = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total_rows / $limit);

        // 2. Get Data
        $dataQuery = "SELECT tm.*, p.nama_project, p.company_name, p.korlap_id " . $sql . $where . " ORDER BY tm.tm_date DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($dataQuery);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $tms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('technical_meeting/index', [
            'tms' => $tms,
            'page' => $page,
            'total_rows' => $total_rows,
            'total_pages' => $total_pages
        ]);
    }

    public function detail() {
        if (!isset($_GET['project_id'])) {
            $this->redirect('technical_meeting_list');
        }

        $project_id = $_GET['project_id'];
        // Use getById from Project model (inherited access via $this->project)
        // Note: $this->project is initialized in __construct as $this->loadModel('Project')
        // But getById is a method of Project model.
        $project = $this->project->getById($project_id);

        if (!$project) {
            die("Project not found.");
        }
        
        // Get TM Data
        $query = "SELECT * FROM technical_meetings WHERE project_id = :project_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        $tm = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tm) {
            echo "<script>alert('Technical Meeting data not found.'); window.location.href='index.php?page=technical_meeting_list';</script>";
            exit;
        }

        $this->view('technical_meeting/view', ['project' => $project, 'tm' => $tm]);
    }

    public function create() {
        $this->checkCreateAccess();
        if (!isset($_GET['project_id'])) {
            $this->redirect('projects_list');
        }

        $project_id = $_GET['project_id'];
        $project = $this->project->getProjectById($project_id);

        if (!$project) {
            die("Project not found.");
        }

        // Rule: TM hanya bisa dibuat setelah Korlap sudah di-assign
        if (empty($project['korlap_id'])) {
             // Redirect or show error
             echo "<script>alert('Error: Korlap must be assigned before creating Technical Meeting.'); window.location.href='index.php?page=all_projects';</script>";
             exit;
        }

        // Check if TM already exists
        $query = "SELECT * FROM technical_meetings WHERE project_id = :project_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        $existing_tm = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->view('technical_meeting/create', ['project' => $project, 'existing_tm' => $existing_tm]);
    }

    public function store() {
        $this->checkCreateAccess();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('projects_list');
        }

        $project_id = $_POST['project_id'];
        $tm_date = $_POST['tm_date'];
        $tm_type = $_POST['tm_type'];
        $setting_alat_date = !empty($_POST['setting_alat_date']) ? $_POST['setting_alat_date'] : null;
        $notes = $_POST['notes'];
        $created_by = $_SESSION['user_id'];

        // File Uploads
        $target_dir = "../public/uploads/tm/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $tm_file_path = "";
        if (isset($_FILES['tm_file']) && $_FILES['tm_file']['error'] == 0) {
             $file_extension = strtolower(pathinfo($_FILES["tm_file"]["name"], PATHINFO_EXTENSION));
             if ($file_extension == "pdf") {
                 $new_filename = $project_id . "_TM_" . time() . ".pdf";
                 if (move_uploaded_file($_FILES["tm_file"]["tmp_name"], $target_dir . $new_filename)) {
                     $tm_file_path = $new_filename;
                 }
             }
        }

        $layout_file_path = "";
        if (isset($_FILES['layout_file']) && $_FILES['layout_file']['error'] == 0) {
             $file_extension = strtolower(pathinfo($_FILES["layout_file"]["name"], PATHINFO_EXTENSION));
             if (in_array($file_extension, ['pdf', 'jpg', 'jpeg', 'png', 'ppt', 'pptx'])) {
                 $new_filename = $project_id . "_LAYOUT_" . time() . "." . $file_extension;
                 if (move_uploaded_file($_FILES["layout_file"]["tmp_name"], $target_dir . $new_filename)) {
                     $layout_file_path = $new_filename;
                 }
             }
        }

        // Check exist
        $query = "SELECT id, tm_file_path, layout_file_path FROM technical_meetings WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Update
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = $row['id'];
            
            // Keep old files if new ones are not uploaded
            if (empty($tm_file_path)) $tm_file_path = $row['tm_file_path'];
            if (empty($layout_file_path)) $layout_file_path = $row['layout_file_path'];

            $sql = "UPDATE technical_meetings SET tm_date=:tm_date, tm_type=:tm_type, setting_alat_date=:setting_alat_date, notes=:notes, tm_file_path=:tm_file_path, layout_file_path=:layout_file_path WHERE id=:id";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':tm_date', $tm_date);
            $stmt->bindParam(':tm_type', $tm_type);
            $stmt->bindParam(':setting_alat_date', $setting_alat_date);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':tm_file_path', $tm_file_path);
            $stmt->bindParam(':layout_file_path', $layout_file_path);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                 $this->project->logAction($project_id, 'Technical Meeting Updated', $_SESSION['user_id'], 'TM details updated.');
                 // Trigger Check Status
                 $this->project->checkAndSetInProgressOps($project_id);
                 $this->redirect('technical_meeting_create', ['project_id' => $project_id, 'msg' => 'tm_updated']);
            } else {
                echo "Error updating TM.";
            }

        } else {
            // Insert
            if (empty($tm_file_path)) {
                 echo "<script>alert('Error: Technical Meeting Document (PDF) is mandatory.'); window.history.back();</script>";
                 exit;
            }

            $sql = "INSERT INTO technical_meetings (project_id, tm_date, tm_type, setting_alat_date, notes, tm_file_path, layout_file_path, created_by) VALUES (:project_id, :tm_date, :tm_type, :setting_alat_date, :notes, :tm_file_path, :layout_file_path, :created_by)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':project_id', $project_id);
            $stmt->bindParam(':tm_date', $tm_date);
            $stmt->bindParam(':tm_type', $tm_type);
            $stmt->bindParam(':setting_alat_date', $setting_alat_date);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':tm_file_path', $tm_file_path);
            $stmt->bindParam(':layout_file_path', $layout_file_path); // Can be empty string or null? DB allows NULL for layout_file_path? Let's check schema.
            // My schema said `layout_file_path` varchar(255) DEFAULT NULL.
            // If empty string, it stores empty string. That's fine.
            // But better store NULL if empty.
            $layout_file_path_param = empty($layout_file_path) ? null : $layout_file_path;
            // Wait, I bound variable.
            // Let's re-bind or handle logic before.
            // Actually, if I bindParam, it binds the variable reference.
            // So I should set $layout_file_path correctly.
            if (empty($layout_file_path)) $layout_file_path = null;
            
            // Re-binding logic is tricky with variables.
            // Let's just use execute array or be careful.
            // I'll stick to bindParam but ensure variable holds NULL if needed.
            
            $stmt->bindParam(':created_by', $created_by);

            if ($stmt->execute()) {
                $this->project->logAction($project_id, 'Technical Meeting Created', $_SESSION['user_id'], 'TM details created.');
                // Trigger Check Status
                $this->project->checkAndSetInProgressOps($project_id);
                $this->redirect('technical_meeting_create', ['project_id' => $project_id, 'msg' => 'tm_created']);
            } else {
                echo "Error creating TM.";
            }
        }
    }
}
