<?php
class User {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $username;
    public $password;
    public $full_name;
    public $jabatan;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $query = "SELECT user_id, full_name, password, role FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $this->username = htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($this->password, $row['password'])) {
                $this->user_id = $row['user_id'];
                $this->full_name = $row['full_name'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    public function searchUsers($term) {
        $query = "SELECT user_id, username, full_name, role FROM " . $this->table_name . " 
                  WHERE username LIKE ? OR full_name LIKE ? 
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $term = "%{$term}%";
        $stmt->bindParam(1, $term);
        $stmt->bindParam(2, $term);
        $stmt->execute();
        
        return $stmt;
    }

    public function getAllUsers() {
        $query = "SELECT user_id, username, full_name, role FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getSalesList() {
        $query = "SELECT user_id, full_name FROM " . $this->table_name . " WHERE role = 'admin_sales' AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getUsersByRole($role) {
        $query = "SELECT user_id, full_name, username FROM " . $this->table_name . " WHERE role = :role";
        // Check if is_active column exists or just assume it does based on getSalesList
        // Since getSalesList uses it, it must exist.
        $query .= " AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt;
    }

    public function getUserById($user_id) {
        $query = "SELECT user_id, username, full_name, role, jabatan, created_at FROM " . $this->table_name . " WHERE user_id = :user_id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function verifyPassword($user_id, $password) {
        $query = "SELECT password FROM " . $this->table_name . " WHERE user_id = :user_id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                return true;
            }
        }
        return false;
    }

    public function updatePassword($user_id, $new_password) {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getEmailsByRole($role) {
        $query = "SELECT username as email FROM " . $this->table_name . " WHERE role = :role AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        
        $emails = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $email = trim($row['email']);
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }
        return $emails;
    }
}
