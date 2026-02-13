<?php

class BaseController {
    protected $db;
    protected $models = [];

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $database = new Database();
        $this->db = $database->getConnection();
    }

    protected function loadModel($modelName) {
        if (!isset($this->models[$modelName])) {
            // Autoloader handles the include
            if (class_exists($modelName)) {
                $this->models[$modelName] = new $modelName($this->db);
            } else {
                throw new Exception("Model $modelName not found");
            }
        }
        return $this->models[$modelName];
    }

    protected function view($viewPath, $data = []) {
        // Extract data to make it available as variables in the view
        extract($data);
        
        // Handle path relative to views directory
        // Assuming views are in ../views/
        $fullPath = __DIR__ . '/../views/' . $viewPath . '.php';
        
        if (file_exists($fullPath)) {
            include $fullPath;
        } else {
            die("View $viewPath not found.");
        }
    }

    protected function jsonResponse($data, $statusCode = 200) {
        // Clear buffer
        while (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/json');
        http_response_code($statusCode);
        
        $json = json_encode($data);
        if ($json === false) {
            // Handle JSON encoding error
            error_log("JSON Encode Error: " . json_last_error_msg());
            // Return a safe empty array or error object depending on context
            // Since we can't know the expected format, returning a standard error object is safest
            echo json_encode(['error' => 'Internal Server Error (JSON Encoding)']);
        } else {
            echo $json;
        }
        exit;
    }

    protected function redirect($page, $params = []) {
        $url = "index.php?page=" . $page;
        if (!empty($params)) {
            $url .= "&" . http_build_query($params);
        }
        header("Location: $url");
        exit;
    }

    protected function checkAuth() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $this->redirect('login');
        }
    }

    /**
     * Generate CSRF token for forms
     * @return string CSRF token
     */
    protected function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token from POST request
     * @param string $token Token to validate
     * @return bool True if valid
     */
    protected function validateCsrfToken($token = null) {
        // CSRF validation disabled per user request for internal system
        return true;
    }

    /**
     * Get CSRF token input field HTML
     * @return string HTML input field
     */
    protected function getCsrfField() {
        $token = $this->generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Sanitize input to prevent XSS
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    protected function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function checkRole($allowedRoles) {
        $this->checkAuth();
        
        if (is_string($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        if (!in_array($_SESSION['role'], $allowedRoles)) {
            die("Unauthorized Access. Required role: " . implode(', ', $allowedRoles));
        }
    }

    /**
     * Unified Project Access Verification
     * @param int $project_id
     * @param bool $enforce_edit_lock
     * @return array Project data if authorized
     */
    protected function verifyProjectAccess($project_id, $enforce_edit_lock = false) {
        $this->checkAuth();
        
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        // 1. Load Project Model
        $projectModel = $this->loadModel('Project');
        $project = $projectModel->getById($project_id);
        
        if (!$project) {
            die("Project not found.");
        }

        // 2. Global access roles
        $global_roles = ['superadmin', 'ceo', 'manager_ops', 'head_ops', 'admin_ops', 'procurement', 'finance', 'admin_sales', 'sales_support_supervisor', 'sales_performance_manager', 'admin_gudang_warehouse', 'admin_gudang_aset'];
        $has_global_access = in_array($role, $global_roles);

        // 3. Ownership / Assignment Check for restricted roles
        $is_authorized = $has_global_access;

        if (!$is_authorized) {
            if ($role == 'sales') {
                if ($projectModel->isAssignedToSales($project_id, $user_id)) $is_authorized = true;
            } elseif ($role == 'manager_sales') {
                if ($projectModel->isAssignedToSalesManager($project_id, $user_id)) $is_authorized = true;
            } elseif ($role == 'korlap') {
                if ($project['korlap_id'] == $user_id) $is_authorized = true;
            } elseif ($role == 'surat_hasil' || $role == 'dw_tim_hasil') {
                // Check Medical Result Assignment
                $medicalResultModel = $this->loadModel('MedicalResult');
                if ($medicalResultModel->isUserAssignedToProject($project_id, $user_id)) $is_authorized = true;
            }
            
            // Fallback: Check Chat Participation
            if (!$is_authorized) {
                try {
                    $chatModel = $this->loadModel('ChatParticipant');
                    if ($chatModel->isParticipant($project_id, $user_id)) $is_authorized = true;
                } catch (Exception $e) {
                }
            }
        }

        if (!$is_authorized) {
            die("Unauthorized Access to this Project.");
        }

        // 4. Enforce Edit Lock (e.g. for Medical Results if Completed)
        if ($enforce_edit_lock && $role != 'superadmin') {
            $medicalResultModel = $this->loadModel('MedicalResult');
            $mr = $medicalResultModel->getByProjectId($project_id);
            if ($mr && $mr['status'] == 'COMPLETED') {
                $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php?page=all_projects";
                header("Location: $redirect_url&err=Project Completed. Edits restricted.");
                exit;
            }
        }

        return $project;
    }

    protected function verifyInvoiceAccess($invoice_id, $is_request = false) {
        $this->checkAuth();
        
        // Global access roles
        if (in_array($_SESSION['role'], ['superadmin', 'ceo', 'manager_ops', 'head_ops', 'admin_ops', 'finance', 'admin_sales', 'procurement', 'sales_support_supervisor', 'sales_performance_manager'])) {
            return true;
        }

        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        // Get details (id can be invoice_id or invoice_request_id)
        if ($is_request) {
            $query = "SELECT ir.id as request_id, ir.pic_sales_id 
                      FROM invoice_requests ir 
                      WHERE ir.id = :id";
        } else {
            $query = "SELECT i.id, i.invoice_request_id as request_id, ir.pic_sales_id 
                      FROM invoices i 
                      JOIN invoice_requests ir ON i.invoice_request_id = ir.id 
                      WHERE i.id = :id";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $invoice_id);
        $stmt->execute();
        $details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$details) return false;

        $request_id = $details['request_id'];

        // 1. Check if User is the Sales PIC of the request
        if ($role == 'sales' && $details['pic_sales_id'] == $user_id) {
            return true;
        }

        // 2. Check Role-based project assignment
        // Fetch projects linked to this request
        $queryProjects = "SELECT project_id FROM invoice_request_projects WHERE invoice_request_id = :rid";
        $stmtProjects = $this->db->prepare($queryProjects);
        $stmtProjects->bindParam(':rid', $request_id);
        $stmtProjects->execute();
        $project_ids = $stmtProjects->fetchAll(PDO::FETCH_COLUMN);

        if (empty($project_ids)) return false;

        $projectModel = $this->loadModel('Project');
        foreach ($project_ids as $pid) {
            // Check simple ownership / assignment
            if ($role == 'sales') {
                if ($projectModel->isAssignedToSales($pid, $user_id)) return true;
            } elseif ($role == 'manager_sales') {
                if ($projectModel->isAssignedToSalesManager($pid, $user_id)) return true;
            } elseif ($role == 'korlap') {
                $pData = $projectModel->getById($pid);
                if ($pData && $pData['korlap_id'] == $user_id) return true;
            }
        }

        return false;
    }
}
