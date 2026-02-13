<?php

class ProjectController extends BaseController {
    private $project;
    private $user;
    private $comment;
    private $notification;
    private $chatParticipant;


    public function __construct() {
        parent::__construct();
        
        $this->project = $this->loadModel('Project');
        $this->user = $this->loadModel('User');
        $this->comment = $this->loadModel('ProjectComment');
        $this->notification = $this->loadModel('Notification');
        $this->chatParticipant = $this->loadModel('ChatParticipant');
    }

    /**
     * Explicitly define verifyProjectAccess to match BaseController visibility
     * Fixes: Fatal error: Access level must be protected (as in class BaseController) or weaker
     */
    protected function verifyProjectAccess($project_id, $enforce_edit_lock = false) {
        return parent::verifyProjectAccess($project_id, $enforce_edit_lock);
    }

    public function get_comments() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        if (!isset($_GET['project_id'])) {
            $this->jsonResponse(['comments' => [], 'user_status' => []]);
        }
        $project_id = $_GET['project_id'];
        
        if (!$this->verifyProjectAccess($project_id)) {
            $this->jsonResponse(['error' => 'Access Denied'], 403);
        }
        
        // Mark as read for current user
        if (isset($_SESSION['user_id'])) {
            $this->chatParticipant->markRead($project_id, $_SESSION['user_id']);
        }

        $stmt = $this->comment->readByProject($project_id);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all users for mention highlighting
        $userStmt = $this->user->getAllUsers();
        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        // Collect all names/usernames
        $names = [];
        foreach ($users as $u) {
            if (!empty($u['full_name'])) $names[] = $u['full_name'];
            if (!empty($u['username'])) $names[] = $u['username'];
        }
        
        // Sort by length descending
        usort($names, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        if (!empty($names)) {
            // Create a single optimized regex
            // Chunking might be needed if too many users, but for <1000 it's fine.
            $escapedNames = array_map(function($n) { return preg_quote($n, '/'); }, $names);
            $pattern = '/(?<!>)(?<!\w)@(' . implode('|', $escapedNames) . ')(?!\w)/i';

            foreach ($comments as &$comment) {
                // $comment['message'] is already htmlspecialchars encoded
                $comment['message'] = preg_replace_callback($pattern, function($matches) {
                    return '<span class="text-primary fw-bold">@' . $matches[1] . '</span>';
                }, $comment['message']);
            }
        }

        // Get User Status (Muted?)
        $userStatus = ['is_muted' => 0];
        if (isset($_SESSION['user_id'])) {
            $userStatus = $this->chatParticipant->getParticipantStatus($project_id, $_SESSION['user_id']);
        }

        $this->jsonResponse([
            'comments' => $comments,
            'user_status' => $userStatus,
            'current_user_id' => $_SESSION['user_id'] ?? null
        ]);
    }

    public function toggle_chat_mute() {
        if (!isset($_POST['project_id']) || !isset($_SESSION['user_id'])) return;
        
        $project_id = $_POST['project_id'];
        if (!$this->verifyProjectAccess($project_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
            return;
        }

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
            return;
        }

        $status = $this->chatParticipant->toggleMute($project_id, $_SESSION['user_id']);
        echo json_encode($status);
    }

    public function get_unread_chat_count() {
         if (!isset($_GET['project_id']) || !isset($_SESSION['user_id'])) {
             echo json_encode(['unread' => 0]);
             return;
         }
         
         $project_id = $_GET['project_id'];
         if (!$this->verifyProjectAccess($project_id)) {
             echo json_encode(['unread' => 0]); // Fail silently or return error
             return;
         }

         $count = $this->chatParticipant->getUnreadCount($project_id, $_SESSION['user_id']);
         echo json_encode(['unread' => $count]);
    }

    public function add_comment() {
        if (!isset($_SESSION['user_id'])) {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['project_id']) && isset($_POST['message'])) {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
                return;
            }

            $project_id = $_POST['project_id'];
            if (!$this->verifyProjectAccess($project_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
                return;
            }

            $this->comment->project_id = $project_id;
            $this->comment->user_id = $_SESSION['user_id'];
            $this->comment->message = $_POST['message'];
            
            // Handle Parent ID (Reply)
            if (isset($_POST['parent_id']) && !empty($_POST['parent_id'])) {
                $this->comment->parent_id = $_POST['parent_id'];
            }

            if ($this->comment->create()) {
                // Subscribe the sender automatically
                $this->chatParticipant->subscribe($_POST['project_id'], $_SESSION['user_id']);

                // Get Project Name for Notification
                $projectInfo = $this->project->getProjectById($_POST['project_id']);
                $projectName = $projectInfo ? $projectInfo['nama_project'] : $_POST['project_id'];

                // Identify explicit mentions (Usernames)
                $mentioned_usernames = [];

                // 1. Use explicit mentions from frontend
                if (isset($_POST['mentions']) && is_array($_POST['mentions'])) {
                    $mentioned_usernames = array_unique($_POST['mentions']);
                }

                // 2. Fallback: Parse from message
                if (empty($mentioned_usernames)) {
                    preg_match_all('/@(\w+)/', $_POST['message'], $matches);
                    if (!empty($matches[1])) {
                        $mentioned_usernames = array_unique($matches[1]);
                    }
                }
                
                // Convert usernames to User IDs and collect emails
                $emails_to_notify = []; // [uid => email]
                if (!empty($mentioned_usernames)) {
                    foreach ($mentioned_usernames as $uname) {
                        $stmt = $this->user->searchUsers($uname);
                        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($users as $u) {
                            if ($u['username'] === $uname && $u['user_id'] != $_SESSION['user_id']) {
                                $emails_to_notify[$u['user_id']] = $u['username'];
                            }
                        }
                    }
                }

                // Collect notified user IDs for App Notifications duplicate check
                $notified_user_ids = [];

                // 1. Handle Mentions (High Priority - Always Notify)
                foreach ($emails_to_notify as $uid => $email) {
                    $this->notification->user_id = $uid;
                    $this->notification->type = 'mention';
                    $this->notification->message = $_SESSION['full_name'] . " mentioned you in project " . $projectName;
                    $this->notification->link = "index.php?page=all_projects&open_project_id=" . $project_id . "&open_tab=chatter";
                    $this->notification->create();
                    
                    // Also subscribe them if not already
                    $this->chatParticipant->subscribe($project_id, $uid);
                    $notified_user_ids[] = $uid;
                }

                // 2. Handle Replies (Email + App Notification)
                if (isset($_POST['parent_id']) && !empty($_POST['parent_id'])) {
                    $parentComment = $this->comment->getById($_POST['parent_id']);
                    if ($parentComment && $parentComment['user_id'] != $_SESSION['user_id']) {
                        $puid = $parentComment['user_id'];
                        $pemail = $parentComment['email'];
                        
                        if (!in_array($puid, $notified_user_ids)) {
                            $emails_to_notify[$puid] = $pemail;
                            
                            $this->notification->user_id = $puid;
                            $this->notification->type = 'project_comment';
                            $this->notification->message = $_SESSION['full_name'] . " replied to your comment in project " . $projectName;
                            $this->notification->link = "index.php?page=all_projects&open_project_id=" . $project_id . "&open_tab=chatter";
                            $this->notification->create();
                            
                            $notified_user_ids[] = $puid;
                        }
                    }
                }

                // 3. Handle Room Participants (App Notification only, if not muted and not already notified)
                $subscribersStmt = $this->chatParticipant->getSubscribers($project_id);
                $subscribers = $subscribersStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($subscribers as $sub) {
                    $uid = $sub['user_id'];
                    if ($uid == $_SESSION['user_id'] || in_array($uid, $notified_user_ids)) continue;

                    if ($sub['is_muted'] == 0) {
                        $this->notification->user_id = $uid;
                        $this->notification->type = 'project_comment';
                        $this->notification->message = $_SESSION['full_name'] . " commented in project " . $projectName;
                        $this->notification->link = "index.php?page=all_projects&open_project_id=" . $project_id . "&open_tab=chatter";
                        $this->notification->create();
                    }
                }

                // 4. Send Emails for Tags & Replies
                // Include MailHelper
                require_once __DIR__ . '/../helpers/MailHelper.php';
                foreach ($emails_to_notify as $uid => $email) {
                    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
                    
                    try {
                        $subject = "Notifikasi Chatter: " . $projectName;
                        $content = "Halo, <br><br>";
                        $content .= "<b>" . $_SESSION['full_name'] . "</b> telah mengirim pesan di Chatter proyek <b>$projectName</b>.<br><br>";
                        $content .= "<i>\"" . $_POST['message'] . "\"</i><br><br>";
                        
                        $link = MailHelper::getBaseUrl() . "?page=all_projects&open_project_id=" . $project_id . "&open_tab=chatter";
                        $html = MailHelper::getTemplate("Pesan Baru di Chatter", $content, $link, "Buka Chatter");
                        MailHelper::send($email, $subject, $html);
                    } catch (Exception $e) {
                        error_log("Failed to send Chatter email notification to $email: " . $e->getMessage());
                    }
                }

                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
        }
    }

    public function search_users() {
        if (!isset($_SESSION['user_id'])) {
             echo json_encode([]);
             return;
        }

        if (isset($_GET['term'])) {
            $term = $_GET['term'];
            $stmt = $this->user->searchUsers($term);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        }
    }





    public function upload_berita_acara() {
        $this->checkRole('korlap');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                die("Invalid CSRF token. Possible CSRF attack detected!");
            }

            $project_id = $_POST['project_id'];
            
            if (!$this->verifyProjectAccess($project_id)) {
                echo "<script>alert('Access Denied'); window.history.back();</script>";
                return;
            }

            $date = $_POST['date'];
            $original_date = isset($_POST['original_date']) ? $_POST['original_date'] : $date;

            // Handle Date Change
            if ($original_date !== $date) {
                if ($this->project->updateScheduleDate($project_id, $original_date, $date)) {
                     $this->project->logAction($project_id, 'Schedule Date Updated', $_SESSION['user_id'], "From $original_date to $date");
                }
            }
            
            // Check Project Status
            $project = $this->project->getProjectById($project_id);
            if (!in_array($project['status_project'], ['approved', 'in_progress_ops', 'completed'])) {
                 echo "<script>alert('Error: Project status (" . $project['status_project'] . ") does not allow BA upload.'); window.history.back();</script>";
                 return;
            }

            if (isset($_FILES['ba_file']) && $_FILES['ba_file']['error'] == 0) {
                $target_dir = "../public/uploads/ba/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["ba_file"]["name"], PATHINFO_EXTENSION));
                
                // Validate PDF - Check both extension and MIME type
                if ($file_extension != "pdf") {
                    echo "<script>alert('Error: Only PDF files are allowed.'); window.history.back();</script>";
                    return;
                }
                
                // Validate actual MIME type (more secure than just checking extension)
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $_FILES["ba_file"]["tmp_name"]);
                
                if ($mime_type != "application/pdf") {
                    echo "<script>alert('Error: Invalid file type. Only PDF files are allowed.'); window.history.back();</script>";
                    return;
                }
                
                // Validate Size (5MB)
                if ($_FILES["ba_file"]["size"] > 5000000) {
                    echo "<script>alert('Error: File size exceeds 5MB.'); window.history.back();</script>";
                    return;
                }

                $new_filename = $project_id . "_BA_" . date('Ymd', strtotime($date)) . "_" . time() . ".pdf";
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["ba_file"]["tmp_name"], $target_file)) {
                    if ($this->project->uploadBeritaAcara($project_id, $date, $new_filename, $_SESSION['user_id'])) {
                        // Check completion status (Auto-complete logic)
                        $status = $this->project->checkCompletionStatus($project_id, $_SESSION['user_id']);
                        
                        // Trigger IN PROGRESS OPS if needed
                        $this->project->checkAndSetInProgressOps($project_id);

                        // Redirect to all_projects to avoid access issues for korlap
                        // Use 'msg' instead of 'status' to avoid filtering conflict
                        header("Location: index.php?page=all_projects&msg=ba_uploaded&open_project_id=" . $project_id . "&open_tab=ba");
                    } else {
                        echo "<script>alert('Error updating database.'); window.history.back();</script>";
                    }
                } else {
                    echo "<script>alert('Error uploading file.'); window.history.back();</script>";
                }
            } else {
                echo "<script>alert('No file uploaded or error occurred.'); window.history.back();</script>";
            }
        }
    }

    public function generate_vendor_memo() {
         if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['project_id']) && isset($_POST['cost_code_id'])) {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                die("Invalid CSRF token. Possible CSRF attack detected!");
            }
            $project_id = $_POST['project_id'];
            
            if (!$this->verifyProjectAccess($project_id)) {
                die("Access Denied");
            }

            $cost_code_id = $_POST['cost_code_id'];
            
            $project = $this->project->getProjectById($project_id);
            if (!$project) {
                die("Project not found");
            }

            // Fetch Vendor Assignments / Exam Types
            $allocations_stmt = $this->project->getVendorAllocations($project_id);
            $allocations = $allocations_stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allocations)) {
                die("Error: No vendors assigned to this project. Cannot generate Vendor Memo.");
            }

            // Fetch System Settings for Signatures
            $setting = $this->loadModel('SystemSetting');
            
            $company_address = $setting->get('company_address') ?? 'Jl. TB Simatupang No. 1, Jakarta Selatan';
            
            // Prepared By (Signer 1)
            // Fetched from Project Korlap
            $prepared_by_name = !empty($project['korlap_name']) ? $project['korlap_name'] : ($_SESSION['full_name'] ?? 'Admin');
            $prepared_by_title = !empty($project['korlap_jabatan']) ? $project['korlap_jabatan'] : 'Operation Support';
            
            // Approved By 1 (Signer 2 - Head of Operations)
            $approved_by_1_name = $setting->get('vendor_memo_signer_2_name') ?? '';
            $approved_by_1_title = $setting->get('vendor_memo_signer_2_title') ?? 'Head of Operations';

            // Dates
            $submission_date = date('Y-m-d');
            
            // MCU Date logic: try to parse from project data
            include_once __DIR__ . '/../helpers/DateHelper.php';
            $mcu_date_display = DateHelper::formatSmartDateIndonesian($project['tanggal_mcu']);
            
            // Fix: Assign to $mcu_date for PDF view compatibility
            $mcu_date = $mcu_date_display;

            // Helper for formatting
            include_once __DIR__ . '/../helpers/DateHelper.php';
            
            // Get Cost Code Detail
            $costCode = $this->loadModel('CostCode');
            $selected_cost_code = $costCode->getById($cost_code_id);

            include '../views/projects/vendor_memo_pdf.php';
         }
    }



    public function cancel_berita_acara() {
        if (!in_array($_SESSION['role'], ['korlap', 'admin_ops', 'superadmin'])) {
            die("Unauthorized");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $project_id = $_POST['project_id'];
            
            if (!$this->verifyProjectAccess($project_id)) {
                echo "<script>alert('Access Denied'); window.history.back();</script>";
                return;
            }

            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 die("Invalid CSRF token. Possible CSRF attack detected!");
            }

            $date = $_POST['date'];
            $reason = htmlspecialchars($_POST['reason'], ENT_QUOTES, 'UTF-8');
            
            if (empty($reason)) {
                echo "<script>alert('Error: Reason is required.'); window.history.back();</script>";
                return;
            }

            if ($this->project->cancelBeritaAcara($project_id, $date, $reason, $_SESSION['user_id'])) {
                $this->project->checkCompletionStatus($project_id, $_SESSION['user_id']);
                // Redirect to all_projects for consistency, use msg instead of status
                header("Location: index.php?page=all_projects&msg=ba_cancelled&open_project_id=" . $project_id . "&open_tab=ba");
            } else {
                echo "<script>alert('Error cancelling date.'); window.history.back();</script>";
            }
        }
    }



    public function index() {
        // Default to allProjects for all roles
        $this->allProjects();
    }

    public function history() {
        if (!isset($_GET['id'])) {
            $this->redirect('all_projects');
        }
        $id = $_GET['id'];
        
        if (!$this->verifyProjectAccess($id)) {
             die("Access Denied");
        }

        $project = $this->project->getProjectById($id);
        if (!$project) {
            die("Project not found");
        }
        $history = $this->project->getHistory($id);
        $this->view('projects/history', ['project' => $project, 'history' => $history]);
    }

    public function create() {
        $this->checkRole(['admin_sales', 'superadmin']);

        $salesPerson = $this->loadModel('SalesPerson');
        $sales_stmt = $salesPerson->readAll();
        $sales_users = $sales_stmt->fetchAll(PDO::FETCH_ASSOC);

        $userModel = $this->loadModel('User');
        $korlap_stmt = $userModel->getUsersByRole('korlap');
        $korlap_users = $korlap_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate Min Date
        // Relaxing minDate to allow urgent projects (which will be routed to Manager)
        // Weekends/Holidays are disabled by JS
        $nationalHoliday = $this->loadModel('NationalHoliday');
        $holidays = $nationalHoliday->getHolidayDates();
        $minDate = 'today'; 
        // $minDate = DateHelper::addWorkingDays(date('Y-m-d'), 3, $holidays); // Previous Hard Constraint

        $settingModel = $this->loadModel('SystemSetting');
        $lark_link = $settingModel->get('lark_link') ?? 'https://www.larksuite.com';

        $this->view('admin_sales/form', [
            'sales_users' => $sales_users,
            'korlap_users' => $korlap_users,
            'page_title' => 'Create Project',
            'csrf_token' => $this->generateCsrfToken(),
            'lark_link' => $lark_link
        ]);
    }

    public function store() {
        if ($_SESSION['role'] != 'admin_sales' && $_SESSION['role'] != 'superadmin') {
            die("Unauthorized");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                die("Invalid CSRF token. Possible CSRF attack detected!");
            }
            
            // Server-side Validation
            $company_valid = !empty($_POST['company_name']) || (!empty($_POST['company_names']) && count(array_filter($_POST['company_names'])) > 0);
            
            if (empty($_POST['project_id']) || empty($_POST['nama_project']) || !$company_valid || empty($_POST['sales_person_id']) || empty($_POST['jenis_pemeriksaan']) || empty($_POST['total_peserta']) || empty($_POST['tanggal_mcu']) || empty($_POST['alamat'])) {
                $_SESSION['error_message'] = "Please fill in all required fields.";
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?page=projects_create");
                return;
            }

            // Validate SPH Link (Required)
            $sph_file = isset($_POST['sph_file']) ? trim($_POST['sph_file']) : '';
            if (empty($sph_file)) {
                $_SESSION['error_message'] = "Please provide the SPH Link.";
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?page=projects_create");
                return;
            }
            if (!preg_match('#^https?://#', $sph_file)) {
                $_SESSION['error_message'] = "SPH harus berupa URL yang valid (http/https).";
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?page=projects_create");
                return;
            }

            // Check for Duplicate ID
            if ($this->project->checkIdExists($_POST['project_id'])) {
                $_SESSION['error_message'] = "Error: Project ID '" . htmlspecialchars($_POST['project_id']) . "' already exists. Please choose a different ID.";
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?page=projects_create");
                return;
            }

            // CSV Processing
            $participants_data = [];
            if (isset($_FILES['participants_csv']) && $_FILES['participants_csv']['error'] == 0) {
                $csv_file = $_FILES['participants_csv']['tmp_name'];
                if (($handle = fopen($csv_file, "r")) !== FALSE) {
                    $first_row = fgetcsv($handle, 1000, ",");
                    if ($first_row && (stripos($first_row[0], 'nama') !== false || stripos($first_row[0], 'name') !== false)) {
                        // Header
                    } else {
                         rewind($handle);
                    }

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if (count($data) >= 5) {
                            $participants_data[] = [
                                'nama' => $data[0],
                                'nik' => $data[1],
                                'gender' => $data[2],
                                'dob' => $data[3],
                                'department' => $data[4]
                            ];
                        }
                    }
                    fclose($handle);
                }
            }

            $this->project->project_id = $_POST['project_id'];
            $this->project->nama_project = $_POST['nama_project'];
            $companies = isset($_POST['company_names']) ? $_POST['company_names'] : [];
            if ((empty($companies) || count(array_filter($companies)) === 0) && isset($_POST['company_name'])) {
                $companies = [$_POST['company_name']];
            }
            $companies = array_filter(array_map(function($s){ return trim($s); }, $companies));
            $this->project->company_name = implode(', ', $companies);
            $this->project->sales_person_id = $_POST['sales_person_id'];
            $this->project->jenis_pemeriksaan = $_POST['jenis_pemeriksaan'];
            $this->project->foto_peserta = $_POST['foto_peserta'];
            $this->project->lunch = $_POST['lunch'];
            $this->project->lunch_notes = $_POST['lunch_notes'] ?? '';
            $this->project->snack = $_POST['snack'];
            $this->project->snack_notes = $_POST['snack_notes'] ?? '';
            
            // Process Lunch Items
            $lunch_items = [];
            $total_lunch_qty = 0;
            if (isset($_POST['lunch_item_name']) && isset($_POST['lunch_item_qty'])) {
                for ($i = 0; $i < count($_POST['lunch_item_name']); $i++) {
                    $item = trim($_POST['lunch_item_name'][$i]);
                    $qty = (int)$_POST['lunch_item_qty'][$i];
                    if (!empty($item) || $qty > 0) {
                        $lunch_items[] = ['item' => $item, 'qty' => $qty];
                        $total_lunch_qty += $qty;
                    }
                }
            }
            $this->project->lunch_items = json_encode($lunch_items);
            $this->project->procurement_lunch_qty = $total_lunch_qty; // Auto-calculate total qty

            // Process Snack Items
            $snack_items = [];
            $total_snack_qty = 0;
            if (isset($_POST['snack_item_name']) && isset($_POST['snack_item_qty'])) {
                for ($i = 0; $i < count($_POST['snack_item_name']); $i++) {
                    $item = trim($_POST['snack_item_name'][$i]);
                    $qty = (int)$_POST['snack_item_qty'][$i];
                    if (!empty($item) || $qty > 0) {
                        $snack_items[] = ['item' => $item, 'qty' => $qty];
                        $total_snack_qty += $qty;
                    }
                }
            }
            $this->project->snack_items = json_encode($snack_items);
            $this->project->procurement_snack_qty = $total_snack_qty; // Auto-calculate total qty

            $this->project->lunch_budget = $_POST['lunch_budget'] ?? 0;
            $this->project->snack_budget = $_POST['snack_budget'] ?? 0;

            $this->project->header_footer = $_POST['header_footer'];
            $this->project->total_peserta = $_POST['total_peserta'];
            
            $dates = explode(', ', $_POST['tanggal_mcu']);
            $this->project->tanggal_mcu = json_encode($dates); 
            
            $this->project->alamat = $_POST['alamat'];
            $this->project->sph_file = $sph_file;
            $this->project->sph_number = isset($_POST['sph_number']) ? trim($_POST['sph_number']) : '';
            $this->project->notes = $_POST['notes'];
            
            $this->project->project_type = $_POST['project_type'] ?? 'on_site';
            $this->project->clinic_location = ($this->project->project_type == 'walk_in') ? ($_POST['clinic_location'] ?? null) : null;

            // Determine Status based on System Settings
            $setting = $this->loadModel('SystemSetting');
            $max_projects = $setting->get('max_projects_daily') ?? 5;
            $min_days = $setting->get('min_days_notice') ?? 3;
            $cutoff_hour = $setting->get('cutoff_hour') ?? 14; // Default cutoff 14:00
            
            $nationalHoliday = $this->loadModel('NationalHoliday');
            $holidays = $nationalHoliday->getHolidayDates();

            $needs_manager = false;
            
            // Determine effective start date based on cutoff time
            $startDate = date('Y-m-d');
            if (intval(date('H')) >= $cutoff_hour) {
                $startDate = date('Y-m-d', strtotime('+1 day'));
            }

            foreach ($dates as $date) {
                // Check daily project count
                $count = $this->project->getProjectCountByDate($date);
                if ($count >= $max_projects) {
                    $needs_manager = true;
                    break;
                }
                
                // Check urgent dates (<= min_days working days from today)
                // H-3 means 3 working days from today.
                // If today is 23rd, 3 working days (assuming no weekends) is 26th.
                // If user picks 26th (H-3) -> Manager.
                // If user picks 27th (H-4) -> Head.
                $daysDiff = DateHelper::countWorkingDays($startDate, $date, $holidays);
                if ($daysDiff <= $min_days) {
                    $needs_manager = true;
                    break;
                }
            }
            
            // Walk-In projects are auto-approved (skip manager/head approval)
            if ($this->project->project_type == 'walk_in') {
                $this->project->status_project = 'approved';
                // Mark as auto-approved with user_id = 0 to indicate system approval
                $this->project->approved_by_manager = 0; // 0 = Auto-Approved
                $this->project->approved_by_head = 0; // 0 = Auto-Approved  
                $this->project->approved_date_manager = date('Y-m-d H:i:s');
                $this->project->approved_date_head = date('Y-m-d H:i:s');
            } else {
                // On-Site projects follow normal approval flow
                $this->project->status_project = $needs_manager ? 'need_approval_manager' : 'need_approval_head';
            }
            
            $this->project->created_by = $_SESSION['user_id'];

            if ($this->project->create()) {
                $this->project->logAction($_POST['project_id'], 'Project Created', $_SESSION['user_id'], 'Project created manually.');
                
                // Email Notification (skip for Walk-In projects)
                if ($this->project->project_type != 'walk_in') {
                    try {
                        $userModel = $this->loadModel('User');
                        $projectData = $this->project->getProjectById($_POST['project_id']);
                        $salesName = $projectData['sales_name'] ?? '-';
                        $totalPeserta = $projectData['total_peserta'] ?? '-';
                        $tanggalMcu = isset($projectData['tanggal_mcu']) ? DateHelper::formatSmartDateIndonesian($projectData['tanggal_mcu']) : '-';

                        $role_to_notify = ($this->project->status_project == 'need_approval_manager') ? 'manager_ops' : 'head_ops';
                        $emails = $userModel->getEmailsByRole($role_to_notify);
                        
                        if (!empty($emails)) {
                            $subject = "[Action Required] Persetujuan Project: " . $_POST['nama_project'];
                            $content = "Ada project baru yang memerlukan persetujuan Anda:<br><br>";
                            $content .= "<b>Nama Project:</b> " . $_POST['nama_project'] . "<br>";
                            $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                            $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                            $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                            
                        $link = MailHelper::getBaseUrl() . "?page=manager_ops_kanban";
                        $html = MailHelper::getTemplate("Persetujuan Project Baru", $content, $link);
                        MailHelper::send($emails, $subject, $html);
                    }

                    // Notification to Procurement if Consumption is requested
                    if ($this->project->lunch == 'Ya' || $this->project->snack == 'Ya') {
                        $procEmails = $userModel->getEmailsByRole('procurement');
                        if (!empty($procEmails)) {
                            $subjectProc = "[Info] Request Konsumsi Proyek Baru: " . $_POST['nama_project'];
                            $contentProc = "Ada proyek baru yang memiliki permintaan konsumsi:<br><br>";
                            $contentProc .= "<b>Nama Project:</b> " . $_POST['nama_project'] . "<br>";
                            $contentProc .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                            $contentProc .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                            $contentProc .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                            $contentProc .= "<b>Lokasi:</b> " . $_POST['alamat'] . "<br>";
                            
                            $contentProc .= "<br><b>Detail Konsumsi:</b><br>";
                            if ($this->project->lunch == 'Ya') {
                                $qty = $this->project->procurement_lunch_qty;
                                $contentProc .= "- Lunch: Ya (" . $qty . " Pax)<br>";
                            }
                            if ($this->project->snack == 'Ya') {
                                $qty = $this->project->procurement_snack_qty;
                                $contentProc .= "- Snack: Ya (" . $qty . " Pax)<br>";
                            }

                            $linkProc = MailHelper::getBaseUrl() . "?page=all_projects&open_project_id=" . $_POST['project_id'];
                            $htmlProc = MailHelper::getTemplate("Request Konsumsi Baru", $contentProc, $linkProc);
                            MailHelper::send($procEmails, $subjectProc, $htmlProc);
                        }
                    }
                    } catch (Exception $e) {
                        error_log("Email notification failed on project creation: " . $e->getMessage());
                    }
                } // End of Walk-In email skip



                header("Location: index.php?page=all_projects&msg=project_created&t=" . time());
            } else {
                header("Location: index.php?page=projects_create&status=error");
            }
        }
    }
    


    
    public function edit() {
        if ($_SESSION['role'] != 'admin_sales' && $_SESSION['role'] != 'superadmin') {
            header("Location: index.php?page=login");
            exit;
        }

        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if (!$this->verifyProjectAccess($id)) {
                echo "<script>alert('Access Denied'); window.location.href='index.php?page=all_projects';</script>";
                return;
            }

            $project_data = $this->project->getProjectById($id);
            if ($project_data) {
                // Check if project is editable
                // If status is 'need_approval_head' and it was approved by manager (meaning it was promoted), it cannot be edited.
                // If status is 'need_approval_head' but approved_by_manager is null (meaning it went directly to head), it CAN be edited.
                // Exception: Superadmin can always edit
                if ($_SESSION['role'] != 'superadmin' && $project_data['status_project'] == 'need_approval_head' && !empty($project_data['approved_by_manager'])) {
                    echo "<script>alert('Project cannot be edited because it has already been approved by Manager.'); window.location.href='index.php?page=all_projects';</script>";
                    return;
                }

                $salesPerson = new SalesPerson($this->db);
                $sales_stmt = $salesPerson->readAll();
                $sales_users = $sales_stmt->fetchAll(PDO::FETCH_ASSOC);

                // Calculate Min Date
                // Relaxing minDate to allow urgent projects
                $nationalHoliday = new NationalHoliday($this->db);
                $holidays = $nationalHoliday->getHolidayDates();
                $minDate = 'today';

                // Generate CSRF Token for Edit Form
                $this->generateCsrfToken();

                $settingModel = $this->loadModel('SystemSetting');
                $lark_link = $settingModel->get('lark_link') ?? 'https://www.larksuite.com';

                include '../views/admin_sales/form.php';
            } else {
                echo "Project not found.";
            }
        }
    }

    public function update() {
        if ($_SESSION['role'] != 'admin_sales' && $_SESSION['role'] != 'superadmin') {
            die("Unauthorized");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                die("Invalid CSRF token. Possible CSRF attack detected!");
            }
            
            $project_id = $_POST['project_id']; 
            
            if (!$this->verifyProjectAccess($project_id)) {
                die("Access Denied");
            }

            $this->project->project_id = $project_id; 
            
            $this->project->nama_project = $_POST['nama_project'];
            $companies = isset($_POST['company_names']) ? $_POST['company_names'] : [];
            if ((empty($companies) || count(array_filter($companies)) === 0) && isset($_POST['company_name'])) {
                $companies = [$_POST['company_name']];
            }
            $companies = array_filter(array_map(function($s){ return trim($s); }, $companies));
            $this->project->company_name = implode(', ', $companies);
            $this->project->sales_person_id = $_POST['sales_person_id'];
            $this->project->jenis_pemeriksaan = $_POST['jenis_pemeriksaan'];
            $this->project->foto_peserta = $_POST['foto_peserta'];
            $this->project->lunch = $_POST['lunch'];
            $this->project->lunch_notes = $_POST['lunch_notes'] ?? '';
            $this->project->snack = $_POST['snack'];
            $this->project->snack_notes = $_POST['snack_notes'] ?? '';
            
            // Process Lunch Items
            $lunch_items = [];
            $total_lunch_qty = 0;
            if (isset($_POST['lunch_item_name']) && isset($_POST['lunch_item_qty'])) {
                for ($i = 0; $i < count($_POST['lunch_item_name']); $i++) {
                    $item = trim($_POST['lunch_item_name'][$i]);
                    $qty = (int)$_POST['lunch_item_qty'][$i];
                    if (!empty($item) || $qty > 0) {
                        $lunch_items[] = ['item' => $item, 'qty' => $qty];
                        $total_lunch_qty += $qty;
                    }
                }
            }
            $this->project->lunch_items = json_encode($lunch_items);
            $this->project->procurement_lunch_qty = $total_lunch_qty; // Auto-calculate total qty

            // Process Snack Items
            $snack_items = [];
            $total_snack_qty = 0;
            if (isset($_POST['snack_item_name']) && isset($_POST['snack_item_qty'])) {
                for ($i = 0; $i < count($_POST['snack_item_name']); $i++) {
                    $item = trim($_POST['snack_item_name'][$i]);
                    $qty = (int)$_POST['snack_item_qty'][$i];
                    if (!empty($item) || $qty > 0) {
                        $snack_items[] = ['item' => $item, 'qty' => $qty];
                        $total_snack_qty += $qty;
                    }
                }
            }
            $this->project->snack_items = json_encode($snack_items);
            $this->project->procurement_snack_qty = $total_snack_qty; // Auto-calculate total qty

            $this->project->lunch_budget = isset($_POST['lunch_budget']) ? str_replace('.', '', $_POST['lunch_budget']) : 0;
            $this->project->snack_budget = isset($_POST['snack_budget']) ? str_replace('.', '', $_POST['snack_budget']) : 0;
            
            $this->project->header_footer = $_POST['header_footer'];
            $this->project->total_peserta = $_POST['total_peserta'];
            
            $dates = explode(', ', $_POST['tanggal_mcu']);
            $this->project->tanggal_mcu = json_encode($dates); 
            
            $this->project->alamat = $_POST['alamat'];
            $this->project->sph_number = isset($_POST['sph_number']) ? trim($_POST['sph_number']) : '';
            $this->project->notes = $_POST['notes'];

            $this->project->project_type = $_POST['project_type'] ?? 'on_site';
            $this->project->clinic_location = ($this->project->project_type == 'walk_in') ? ($_POST['clinic_location'] ?? null) : null;

            $current_project = $this->project->getProjectById($_POST['project_id']);
            if ($current_project) {
                if ($current_project['status_project'] == 'cancelled') {
                    $this->project->status_project = 'cancelled';
                } elseif ($current_project['status_project'] == 'rejected' || $current_project['status_project'] == 're-nego') {
                    // Re-evaluate logic for Re-submission
                    $setting = $this->loadModel('SystemSetting');
                    $max_projects = $setting->get('max_projects_daily') ?? 5;
                    $min_days = $setting->get('min_days_notice') ?? 3;
                    
                    $nationalHoliday = $this->loadModel('NationalHoliday');
                    $holidays = $nationalHoliday->getHolidayDates();
        
                    $needs_manager = false;
                    foreach ($dates as $date) {
                        // Check daily project count
                        // Exclude current project from count? No, getProjectCountByDate just counts rows. 
                        // If we are updating, this project is already in DB? 
                        // Wait, getProjectCountByDate queries DB.
                        // If we are updating, the row exists.
                        // But we might be changing dates.
                        // If we change date to X, and X has 5 projects (excluding this one?).
                        // COUNT(*) counts ALL rows.
                        // If this project is already on date X, it counts as 1.
                        // If we move to date Y, date Y count increases.
                        // Ideally we should exclude current project ID from count.
                        // But for now, let's assume it's fine or I should update getProjectCountByDate to exclude ID.
                        
                        // Let's rely on strict count for safety (better safe than sorry).
                        // If count >= max (5), then Manager.
                        
                        $count = $this->project->getProjectCountByDate($date);
                        if ($count >= $max_projects) {
                            $needs_manager = true;
                            break;
                        }
                        
                        $daysDiff = DateHelper::countWorkingDays(date('Y-m-d'), $date, $holidays);
                        if ($daysDiff <= $min_days) {
                            $needs_manager = true;
                            break;
                        }
                    }

                    $this->project->status_project = $needs_manager ? 'need_approval_manager' : 'need_approval_head';
                    
                } else {
                    $this->project->status_project = $current_project['status_project'];
                }
            }
            
            if (isset($_POST['sph_file'])) {
                $link = trim($_POST['sph_file']);
                if (!empty($link) && preg_match('#^https?://#', $link)) {
                    $this->project->sph_file = $link;
                }
            }

            if ($this->project->update()) {
                $this->project->logAction($_POST['project_id'], 'Project Updated', $_SESSION['user_id'], 'Project details updated.');

                if ($current_project && $this->project->status_project != $current_project['status_project']) {
                    $this->project->logAction($_POST['project_id'], 'Status Changed to ' . ucfirst(str_replace('_', ' ', $this->project->status_project)), $_SESSION['user_id'], 'Status reset after edit.');
                }
                
                header("Location: index.php?page=projects_list&msg=project_updated");
            } else {
                header("Location: index.php?page=projects_edit&id=".$_POST['project_id']."&status=error");
            }
        }
    }

    public function allProjects() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?page=login");
            exit;
        }

        if ($_SESSION['role'] == 'procurement') {
            $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $stmt = $this->project->readForProcurement($limit, $offset);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total_rows = $this->project->countForProcurement();
            $total_pages = ceil($total_rows / $limit);
            
            // Initialize filter variables to empty as they are not yet supported in procurement view
            $search = '';
            $status = '';
            $date_from = '';
            $date_to = '';

            include '../views/projects/list.php';
            return;
        }

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
        $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

        $korlap_id = null;
        if ($_SESSION['role'] == 'korlap') {
            $korlap_id = $_SESSION['user_id'];
        }

        $role = null;
        $user_id = null;
        if ($_SESSION['role'] == 'sales' || $_SESSION['role'] == 'manager_sales') {
            $role = $_SESSION['role'];
            $user_id = $_SESSION['user_id'];
        }

        $stmt = $this->project->readWithFilters($search, $status, $date_from, $date_to, $limit, $offset, $korlap_id, $role, $user_id);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_rows = $this->project->countWithFilters($search, $status, $date_from, $date_to, $korlap_id, $role, $user_id);
        $total_pages = ceil($total_rows / $limit);

        $settingModel = $this->loadModel('SystemSetting');
        $lark_link = $settingModel->get('lark_link') ?? 'https://www.larksuite.com';

        include '../views/projects/list.php';
    }

    public function get_vendor_allocations_ajax() {
        if (!isset($_SESSION['role'])) {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             exit;
        }
        $project_id = $_GET['project_id'];
        
        if (!$this->verifyProjectAccess($project_id)) {
             echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
             exit;
        }
        
        $stmt = $this->project->getVendorAllocations($project_id);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    public function assign_vendor_ajax() {
        if ($_SESSION['role'] != 'admin_ops' && $_SESSION['role'] != 'korlap') {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
                 exit;
            }
            $project_id = $_POST['project_id'];
            
            if (!$this->verifyProjectAccess($project_id)) {
                 echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
                 exit;
            }

            $allocations = isset($_POST['allocations']) ? $_POST['allocations'] : [];
            
            if ($this->project->saveVendorAllocations($project_id, $allocations)) {
                 $this->project->logAction($project_id, 'Vendor Package Assigned', $_SESSION['user_id'], 'Allocations updated.');
                 
                 // Email Notification to Procurement
                 try {
                     $userModel = $this->loadModel('User');
                     $procEmails = $userModel->getEmailsByRole('procurement');
                     
                     if (!empty($procEmails) && !empty($allocations)) {
                         $projectData = $this->project->getProjectById($project_id);
                         $salesName = $projectData['sales_name'] ?? '-';
                         $totalPeserta = $projectData['total_peserta'] ?? '-';
                         $tanggalMcu = isset($projectData['tanggal_mcu']) ? DateHelper::formatSmartDateIndonesian($projectData['tanggal_mcu']) : '-';
                         
                         $requesterRole = ($_SESSION['role'] == 'korlap') ? 'Korlap' : 'Admin Ops';
                         $requesterName = $_SESSION['full_name'] ?? $_SESSION['username'];

                         $subject = "[Request] Permintaan Vendor: " . $projectData['nama_project'];
                         $content = "$requesterRole ($requesterName) telah mengajukan permintaan vendor untuk project berikut:<br><br>";
                         $content .= "<b>Nama Project:</b> " . $projectData['nama_project'] . "<br>";
                         $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                         $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                         $content .= "<b>Lokasi:</b> " . ($projectData['alamat'] ?? '-') . "<br>";
                         
                         $content .= "<br><b>Detail Permintaan Vendor:</b><br>";
                         $content .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%; border-color: #ddd;'>";
                         $content .= "<thead><tr style='background-color: #f8f9fa;'><th>Jenis Pemeriksaan</th><th>Jumlah Peserta</th><th>Catatan</th></tr></thead>";
                         $content .= "<tbody>";
                         foreach ($allocations as $alloc) {
                             $examType = htmlspecialchars($alloc['exam_type'] ?? '-');
                             $count = htmlspecialchars($alloc['participant_count'] ?? '-');
                             $notes = htmlspecialchars($alloc['notes'] ?? '-');
                             $content .= "<tr><td>$examType</td><td>$count</td><td>$notes</td></tr>";
                         }
                         $content .= "</tbody></table>";

                         $link = MailHelper::getBaseUrl() . "?page=all_projects&open_project_id=" . $project_id;
                         $html = MailHelper::getTemplate("Permintaan Vendor Baru", $content, $link);
                         MailHelper::send($procEmails, $subject, $html);
                     }
                 } catch (Exception $e) {
                     error_log("Email notification failed on vendor assignment: " . $e->getMessage());
                 }

                 echo json_encode(['status' => 'success']);
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Failed to save']);
            }
        }
    }

    public function get_korlaps_ajax() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin_ops', 'superadmin'])) {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             exit;
        }
        $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;
        
        if ($project_id && !$this->verifyProjectAccess($project_id)) {
             echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
             exit;
        }

        $data = $this->project->getKorlaps($project_id);
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    public function assign_korlap_ajax() {
         if ($_SESSION['role'] != 'admin_ops') {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             exit;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
                exit;
            }
            $project_id = $_POST['project_id'];
            
            if (!$this->verifyProjectAccess($project_id)) {
                 echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
                 exit;
            }

            $korlap_id = $_POST['korlap_id'];
            
            // Check if Korlap is already assigned
            // Allow reassignment (Edit Mode) instead of blocking
            $currentProject = $this->project->getProjectById($project_id);
            // if (!empty($currentProject['korlap_id'])) {
            //    echo json_encode(['status' => 'error', 'message' => 'Korlap already assigned. Cannot change.']);
            //    exit;
            // }

            if ($this->project->assignKorlap($project_id, $korlap_id)) {
                 $this->project->logAction($project_id, 'Korlap Assigned', $_SESSION['user_id'], 'Korlap ID: ' . $korlap_id);
                 
                 // Email Notification to Korlap
                 try {
                     $userModel = $this->loadModel('User');
                     $korlap = $userModel->getUserById($korlap_id);
                     if ($korlap && !empty($korlap['username'])) {
                        $projectData = $this->project->getProjectById($project_id);
                        $salesName = $projectData['sales_name'] ?? '-';
                        $totalPeserta = $projectData['total_peserta'] ?? '-';
                        $tanggalMcu = isset($projectData['tanggal_mcu']) ? DateHelper::formatSmartDateIndonesian($projectData['tanggal_mcu']) : '-';

                        $subject = "[Penugasan] Anda ditugaskan sebagai Korlap: " . $projectData['nama_project'];
                        $content = "Anda telah ditunjuk sebagai Koordinator Lapangan (Korlap) untuk project berikut:<br><br>";
                        $content .= "<b>Nama Project:</b> " . $projectData['nama_project'] . "<br>";
                        $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                        $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                        $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                        $content .= "<b>Lokasi:</b> " . $projectData['alamat'] . "<br>";
                        
                        $link = MailHelper::getBaseUrl() . "?page=all_projects&open_project_id=" . $project_id;
                        $html = MailHelper::getTemplate("Penugasan Korlap Baru", $content, $link);
                        MailHelper::send($korlap['username'], $subject, $html);
                    }
                 } catch (Exception $e) {
                     error_log("Email notification failed on Korlap assignment: " . $e->getMessage());
                 }

                 echo json_encode(['status' => 'success']);
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Failed to save']);
            }
        }
    }

    public function assign_vendor_procurement_ajax() {
        if ($_SESSION['role'] != 'procurement') {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
                 exit;
            }
            $project_id = $_POST['project_id'];
            
            if (!$this->verifyProjectAccess($project_id)) {
                 echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
                 exit;
            }

            $fulfillments = isset($_POST['fulfillments']) ? $_POST['fulfillments'] : [];
            
            if ($this->project->saveVendorFulfillment($project_id, $fulfillments)) {
                 $this->project->logAction($project_id, 'Vendor Fulfillment Updated', $_SESSION['user_id'], 'Procurement assigned vendors.');
                 echo json_encode(['status' => 'success']);
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Failed to save']);
            }
        }
    }

    public function mark_no_vendor_needed_ajax() {
        if ($_SESSION['role'] != 'admin_ops' && $_SESSION['role'] != 'korlap') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
                 exit;
            }
            $project_id = $_POST['project_id'] ?? null;
            if (!$project_id) {
                echo json_encode(['status' => 'error', 'message' => 'Missing project_id']);
                return;
            }
            
            if (!$this->verifyProjectAccess($project_id)) {
                 echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
                 exit;
            }

            if ($this->project->markNoVendorNeeded($project_id)) {
                $this->project->logAction($project_id, 'No Vendor Needed', $_SESSION['user_id'], 'Marked as no vendor needed.');
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
            }
        }
    }

    public function approve_consumption_ajax() {
        if ($_SESSION['role'] != 'procurement') {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
                 exit;
            }
            $project_id = $_POST['project_id'];
            
            if (!$this->verifyProjectAccess($project_id)) {
                 echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
                 exit;
            }

            $lunch_qty = isset($_POST['lunch_qty']) && $_POST['lunch_qty'] !== '' ? $_POST['lunch_qty'] : null;
            $snack_qty = isset($_POST['snack_qty']) && $_POST['snack_qty'] !== '' ? $_POST['snack_qty'] : null;
            
            if ($this->project->updateConsumptionStatus($project_id, 'approved', $lunch_qty, $snack_qty)) {
                 $this->project->logAction($project_id, 'Consumption Approved', $_SESSION['user_id'], "Lunch: $lunch_qty, Snack: $snack_qty");
                 echo json_encode(['status' => 'success']);
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Failed to approve']);
            }
        }
    }

    public function downloadProjectTemplate() {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="project_import_template.csv"');
        $output = fopen('php://output', 'w');
        
        // Headers matching the import logic
        fputcsv($output, [
            'Project ID', 
            'Project Name', 
            'Company Names (Comma Separated)', 
            'Sales Person ID', 
            'Project Type (on_site/walk_in)',
            'Clinic Location (Required for Walk-In)',
            'Jenis Pemeriksaan', 
            'Total Peserta', 
            'Tanggal MCU (e.g. 2023-10-25, 2023-10-26)', 
            'Alamat', 
            'Notes', 
            'Lunch (Ya/Tidak)', 
            'Snack (Ya/Tidak)',
            'SPH Link (GDrive)',
            'Referral No SPH',
            'Lunch Budget',
            'Snack Budget',
            'Lunch Items (Item:Qty|Item:Qty)',
            'Snack Items (Item:Qty|Item:Qty)'
        ]);
        
        // Sample Data Row
        fputcsv($output, [
            'PRJ-001', 
            'Annual MCU PT Example', 
            'PT Example Indonesia, PT Example Branch', 
            '1', // Sales Person ID example
            'on_site', // Project Type
            '', // Clinic Location (empty for on_site)
            'Paket Silver', 
            '100', 
            date('Y-m-d'), 
            'Jl. Sudirman No. 1, Jakarta', 
            'VIP handling required', 
            'Ya', 
            'Ya',
            'https://drive.google.com/file/d/example/view',
            'REF-123',
            '50000',
            '25000',
            'Nasi Padang:50|Ayam Bakar:50',
            'Risoles:100|Puding:100'
        ]);
        
        fclose($output);
        exit;
    }

    public function importProjectsCsv() {
        // Placeholder for CSV import functionality
        echo "Feature not implemented yet.";
    }

    public function get_ba_status_ajax() {
        if (!isset($_SESSION['role'])) {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             exit;
        }
        $project_id = $_GET['project_id'];
        
        if (!$this->verifyProjectAccess($project_id)) {
             echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
             exit;
        }
        
        // Get Project Dates
        $project = $this->project->getProjectById($project_id);
        if (!$project) {
            echo json_encode(['status' => 'error', 'message' => 'Project not found']);
            exit;
        }
        
        $dates = json_decode($project['tanggal_mcu'], true);
        if (!is_array($dates)) {
            $dates = [$project['tanggal_mcu']];
        }
        sort($dates);
        
        // Get BA Records
        $ba_records = $this->project->getBeritaAcara($project_id);
        $ba_map = [];
        if ($ba_records) {
            foreach ($ba_records as $ba) {
                $ba_map[$ba['tanggal_mcu']] = $ba;
            }
        }
        
        $result = [];
        foreach ($dates as $date) {
            $date = trim($date);
            $ba = isset($ba_map[$date]) ? $ba_map[$date] : null;
            $result[] = [
                'date' => $date,
                'formatted_date' => DateHelper::formatIndonesianDate($date),
                'status' => $ba ? $ba['status'] : 'pending',
                'file_path' => $ba ? $ba['file_path'] : null,
                'file_url' => $ba ? "index.php?page=download_ba&project_id=" . urlencode($project_id) . "&date=" . urlencode($date) : null,
                'cancel_reason' => $ba ? $ba['cancel_reason'] : null,
                'created_at' => $ba ? $ba['created_at'] : null
            ];
        }
        
        echo json_encode(['status' => 'success', 'data' => $result, 'project_status' => $project['status_project']]);
    }

    public function uploadSph() {
        if ($_SESSION['role'] != 'admin_sales' && $_SESSION['role'] != 'superadmin') {
            die("Unauthorized");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                 echo "<script>alert('Invalid CSRF token.'); window.history.back();</script>";
                 return;
            }

            $project_id = $_POST['project_id'];
            
            if (!$this->verifyProjectAccess($project_id)) {
                echo "<script>alert('Access Denied'); window.history.back();</script>";
                return;
            }

            $link = isset($_POST['sph_file']) ? trim($_POST['sph_file']) : '';
            if (empty($link) || !preg_match('#^https?://#', $link)) {
                echo "<script>alert('Harap isi link SPH yang valid (http/https).'); window.history.back();</script>";
                return;
            }
            if ($this->project->updateSph($project_id, $link)) {
                $this->project->logAction($project_id, 'SPH Link Set', $_SESSION['user_id'], 'SPH link updated.');
                header("Location: index.php?page=all_projects&msg=sph_link_updated&open_project_id=" . $project_id);
            } else {
                echo "<script>alert('Error updating SPH link.'); window.history.back();</script>";
            }
        }
    }

    public function download_ba() {
        if (!isset($_GET['project_id']) || !isset($_GET['date'])) {
            http_response_code(400);
            echo "Bad Request";
            return;
        }

        $project_id = $_GET['project_id'];

        // Security Check
        if (!$this->verifyProjectAccess($project_id)) {
            http_response_code(403);
            echo "Access Denied";
            exit;
        }

        $date = $_GET['date'];
        $records = $this->project->getBeritaAcara($project_id);
        if (!isset($records[$date]) || empty($records[$date]['file_path'])) {
            http_response_code(404);
            echo "Not Found";
            return;
        }
        $file = $records[$date]['file_path'];
        $paths = [
            __DIR__ . '/../public/uploads/ba/' . $file,
            __DIR__ . '/../uploads/ba/' . $file
        ];
        $found = null;
        foreach ($paths as $p) {
            if (is_file($p)) { $found = $p; break; }
        }
        if (!$found) {
            http_response_code(404);
            echo "Not Found";
            return;
        }
        
        // Detect MIME type
        $mime = mime_content_type($found);
        if ($mime === false) $mime = 'application/pdf'; // Fallback
        
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($found));
        readfile($found);
        exit;
    }

    public function download_sph() {
        if (!isset($_GET['project_id'])) {
            http_response_code(400);
            echo "Bad Request";
            return;
        }
        $project_id = $_GET['project_id'];

        if (!$this->verifyProjectAccess($project_id)) {
            http_response_code(403);
            echo "Access Denied";
            exit;
        }

        $project = $this->project->getProjectById($project_id);
        if (!$project || empty($project['sph_file'])) {
            http_response_code(404);
            echo "Not Found";
            return;
        }
        $file = $project['sph_file'];
        if (preg_match('#^https?://#', $file)) {
            header("Location: " . $file);
            exit;
        }

        // Legacy File Support
        $base_path = __DIR__ . '/../public/uploads/sph/';
        $file_path = $base_path . $file;
        
        // Check for potential Year/Month folders if not found directly
        if (!file_exists($file_path)) {
             // Sometimes paths might be stored with 'uploads/sph/' prefix? 
             // Or maybe just check if it exists relative to public
             $alt_path = __DIR__ . '/../public/' . $file;
             if (file_exists($alt_path)) {
                 $file_path = $alt_path;
             }
        }

        if (file_exists($file_path)) {
            $mime = mime_content_type($file_path);
            if ($mime === false) $mime = 'application/pdf'; // Default fallback
            
            header('Content-Type: ' . $mime);
            header('Content-Disposition: inline; filename="' . basename($file) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            http_response_code(404);
            echo "Not Found: File does not exist on server.";
        }
    }
}
