<?php
include_once '../config/database.php';

class SuperadminController extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->checkRole('superadmin');
    }

    public function manageUsers() {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY role ASC, full_name ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get roles for dropdown
        
        // Hardcoded roles matching ENUM
        $roles = [
            ['role_name' => 'Superadmin', 'role_key' => 'superadmin'],
            ['role_name' => 'Admin Sales', 'role_key' => 'admin_sales'],
            ['role_name' => 'Sales', 'role_key' => 'sales'],
            ['role_name' => 'Manager Sales', 'role_key' => 'manager_sales'],
            ['role_name' => 'Manager Ops', 'role_key' => 'manager_ops'],
            ['role_name' => 'Head Ops', 'role_key' => 'head_ops'],
            ['role_name' => 'SPV Ops/Admin Ops', 'role_key' => 'admin_ops'],
            ['role_name' => 'Procurement', 'role_key' => 'procurement'],
            ['role_name' => 'Korlap', 'role_key' => 'korlap'],
            ['role_name' => 'Admin Gudang Aset', 'role_key' => 'admin_gudang_aset'],
            ['role_name' => 'Admin Gudang Warehouse', 'role_key' => 'admin_gudang_warehouse'],
            ['role_name' => 'Finance', 'role_key' => 'finance'],
            ['role_name' => 'CEO', 'role_key' => 'ceo'],
            ['role_name' => 'Sales Support SPV', 'role_key' => 'sales_support_supervisor'],
            ['role_name' => 'Performance Manager Sales', 'role_key' => 'sales_performance_manager'],
            ['role_name' => 'Surat Hasil (Kohas)', 'role_key' => 'surat_hasil'],
            ['role_name' => 'DW Tim Hasil', 'role_key' => 'dw_tim_hasil']
        ];

        // Seed default accounts for requested roles if missing
        try {
            $requiredUsers = [
                ['username' => 'spv_sales', 'full_name' => 'Sales Support SPV', 'jabatan' => 'Sales Support SPV', 'role' => 'sales_support_supervisor'],
                ['username' => 'perf_manager_sales', 'full_name' => 'Performance Manager Sales', 'jabatan' => 'Performance Manager Sales', 'role' => 'sales_performance_manager'],
                ['username' => 'dw_tim_hasil', 'full_name' => 'DW Tim Hasil', 'jabatan' => 'DW Tim Hasil', 'role' => 'dw_tim_hasil'],
                ['username' => 'surat_hasil', 'full_name' => 'Surat Hasil (Kohas)', 'jabatan' => 'Surat Hasil (Kohas)', 'role' => 'surat_hasil'],
            ];
            foreach ($requiredUsers as $u) {
                // Check if any user exists for the role
                $check = $this->db->prepare("SELECT COUNT(*) as cnt FROM users WHERE role = ?");
                $check->execute([$u['role']]);
                $existsByRole = (int)$check->fetch(PDO::FETCH_ASSOC)['cnt'] > 0;
                
                // Also avoid duplicate usernames
                $checkUser = $this->db->prepare("SELECT COUNT(*) as cnt FROM users WHERE username = ?");
                $checkUser->execute([$u['username']]);
                $existsByUsername = (int)$checkUser->fetch(PDO::FETCH_ASSOC)['cnt'] > 0;
                
                if (!$existsByRole && !$existsByUsername) {
                    $passwordHash = password_hash('ChangeMe123!', PASSWORD_BCRYPT);
                    $insert = $this->db->prepare("INSERT INTO users (username, full_name, jabatan, role, password, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
                    $insert->execute([$u['username'], $u['full_name'], $u['jabatan'], $u['role'], $passwordHash]);
                }
            }
        } catch (Exception $e) {
            // Silent fail; seeding is non-critical
        }

        include '../views/superadmin/users.php';
    }

    public function saveUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 die("Invalid CSRF token.");
            }
            $username = $_POST['username'];
            $fullName = $_POST['full_name'];
            $jabatan = $_POST['jabatan'];
            $role = $_POST['role'];
            $password = $_POST['password'];
            $userId = isset($_POST['user_id']) ? $_POST['user_id'] : null;

            // Check for duplicate username
            $sqlCheck = "SELECT user_id FROM users WHERE username = ?";
            $paramsCheck = [$username];
            if ($userId) {
                $sqlCheck .= " AND user_id != ?";
                $paramsCheck[] = $userId;
            }
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute($paramsCheck);
            
            if ($stmtCheck->rowCount() > 0) {
                 // Duplicate found
                 header("Location: index.php?page=superadmin_users&error=duplicate_username");
                 exit;
            }

            if ($userId) {
                // Update
                if (!empty($password)) {
                    $sql = "UPDATE users SET username=?, full_name=?, jabatan=?, role=?, password=? WHERE user_id=?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$username, $fullName, $jabatan, $role, password_hash($password, PASSWORD_BCRYPT), $userId]);
                } else {
                    $sql = "UPDATE users SET username=?, full_name=?, jabatan=?, role=? WHERE user_id=?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$username, $fullName, $jabatan, $role, $userId]);
                }
            } else {
                // Create
                $sql = "INSERT INTO users (username, full_name, jabatan, role, password, is_active) VALUES (?, ?, ?, ?, ?, 1)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$username, $fullName, $jabatan, $role, password_hash($password, PASSWORD_BCRYPT)]);
            }

            header("Location: index.php?page=superadmin_users");
            exit;
        }
    }

    public function deleteUser() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$id]);
            header("Location: index.php?page=superadmin_users");
            exit;
        }
    }
}
