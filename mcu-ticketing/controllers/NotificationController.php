<?php


class NotificationController extends BaseController {
    private $notification;

    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->notification = $this->loadModel('Notification');
    }

    public function getUnread() {
        try {
            if (!isset($_SESSION['user_id'])) {
                // Return empty array if no user session
                $this->jsonResponse([]);
                return;
            }

            $stmt = $this->notification->readUnreadByUser($_SESSION['user_id']);
            
            if ($stmt) {
                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($notifications === false) {
                    $notifications = [];
                }
                $this->jsonResponse($notifications);
            } else {
                $this->jsonResponse([]);
            }
        } catch (Exception $e) {
            // Log error and return empty array
            error_log("Notification Error: " . $e->getMessage());
            $this->jsonResponse([]);
        }
    }

    public function markRead() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            if ($id && isset($_SESSION['user_id'])) {
                if ($this->notification->markAsRead($id, $_SESSION['user_id'])) {
                    $this->jsonResponse(['status' => 'success']);
                } else {
                    $this->jsonResponse(['status' => 'error', 'message' => 'Failed to update']);
                }
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'Missing ID or Session']);
            }
        }
    }

    public function markAllRead() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->notification->markAllAsRead($_SESSION['user_id'])) {
                $this->jsonResponse(['status' => 'success']);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'Failed to update']);
            }
        }
    }
}
?>
