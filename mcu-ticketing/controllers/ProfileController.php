<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/User.php';

class ProfileController extends BaseController {
    private $user;
    
    public function __construct() {
        parent::__construct();
        $this->user = $this->loadModel('User');
    }

    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        
        $user_data = $this->user->getUserById($_SESSION['user_id']);
        
        include '../views/profile/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                die("Invalid CSRF token.");
            }
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                $error = "New password and confirmation do not match.";
                include '../views/profile/edit.php';
                return;
            }

            // Verify old password
            if ($this->user->verifyPassword($_SESSION['user_id'], $old_password)) {
                if ($this->user->updatePassword($_SESSION['user_id'], $new_password)) {
                    $success = "Password updated successfully.";
                    include '../views/profile/edit.php';
                } else {
                    $error = "Failed to update password.";
                    include '../views/profile/edit.php';
                }
            } else {
                $error = "Incorrect old password.";
                include '../views/profile/edit.php';
            }
        }
    }
}
