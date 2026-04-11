<?php


class AuthController extends BaseController {
    private $user;

    public function __construct() {
        parent::__construct();
        $this->user = $this->loadModel('User');
    }

    public function login() {
        $error_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->user->username = $_POST['username'];
            $this->user->password = $_POST['password'];

            try {
                if ($this->user->login()) {
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    session_regenerate_id(true);
                    $old_csrf = $_SESSION['csrf_token'] ?? null;
                    $_SESSION = array();
                    $_SESSION['user_id'] = $this->user->user_id;
                    $_SESSION['full_name'] = $this->user->full_name;
                    $_SESSION['role'] = $this->user->role;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $this->redirectBasedOnRole($this->user->role);
                } else {
                    $error_message = "Invalid username or password.";
                }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), "doesn't exist") !== false) {
                    $error_message = "Database schema missing. Please run mcu_ticketing_full_install.sql on your database.";
                } else {
                    $error_message = "Database error. Check server error log.";
                }
            }
        }
        $this->view('auth/login', ['error_message' => $error_message]);
    }

    private function redirectBasedOnRole($role) {
        if ($role == 'ceo') {
            $this->redirect('rabs_list');
        } else {
            $this->redirect('dashboard');
        }
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Unset all session variables
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
        
        $this->redirect('login');
    }
}
