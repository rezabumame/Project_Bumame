<?php
class MedicalResultController extends BaseController {
    private $medicalResult;
    private $project;
    private $setting;
    private $holiday;

    // Helper to verify Project access using Unified Base Logic
    private function verifyProjectAccessLocal($project_id, $enforce_edit_lock = false) {
        $project = $this->verifyProjectAccess($project_id, $enforce_edit_lock);
        
        // Specific Lifecycle Check for Medical Results
        $allowed_statuses = [
            'approved', 
            'ongoing', 
            'completed',
            'in_progress_ops',
            'ready_for_invoicing',
            'invoice_requested',
            'invoiced',
            'paid'
        ];
        
        if (!in_array($project['status_project'], $allowed_statuses)) {
             die("Project status '{$project['status_project']}' is not eligible for Medical Results processing.");
        }
        
        return $project;
    }

    public function __construct() {
        parent::__construct();
        if ($_SESSION['role'] != 'dw_tim_hasil' && $_SESSION['role'] != 'surat_hasil' && $_SESSION['role'] != 'superadmin' && $_SESSION['role'] != 'manager_ops' && $_SESSION['role'] != 'admin_ops' && $_SESSION['role'] != 'head_ops') {
            die("Access Denied");
        }
        
        $this->medicalResult = $this->loadModel('MedicalResult');
        $this->project = $this->loadModel('Project');
        $this->setting = $this->loadModel('SystemSetting');
        $this->holiday = $this->loadModel('NationalHoliday');
    }

    public function index() {
        $page_title = "Medical Results (Surat Hasil)";
        
        // Get Eligible Projects
        // Status: approved, process_vendor, vendor_assigned, no_vendor_needed, ongoing, completed
        // NOT: pending, need_approval_manager, need_approval_head, cancelled, rejected
        
        $eligible_statuses = [
            'approved', 
            'in_progress_ops',
            'ongoing', 
            'completed',
            'ready_for_invoicing',
            'invoice_requested',
            'invoiced',
            'paid'
        ];
        
        $status_placeholders = implode(',', array_fill(0, count($eligible_statuses), '?'));
        
        // Pagination
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $db = (new Database())->getConnection();
        
        // 1. Count Total
        $countQuery = "SELECT COUNT(DISTINCT p.project_id) as total
                  FROM projects p
                  LEFT JOIN medical_results mr ON p.project_id = mr.project_id";
        
        $is_surat_hasil = ($_SESSION['role'] == 'dw_tim_hasil' || $_SESSION['role'] == 'surat_hasil');
        $view_mode = isset($_GET['view']) ? $_GET['view'] : 'my';
        
        // If view is 'all', we don't filter by assignment for surat_hasil
        // But we still filter by eligible statuses
        $filter_assignment = ($is_surat_hasil && $view_mode == 'my');
        
        if ($filter_assignment) {
            $countQuery .= " JOIN medical_result_items mri ON mr.id = mri.medical_result_id ";
        }

        $countQuery .= " WHERE p.status_project IN ($status_placeholders)";
        // $countQuery .= " AND (mr.status IS NULL OR mr.status != 'NOT_NEEDED')";
        
        if ($filter_assignment) {
            $countQuery .= " AND mri.assigned_to_user_id = ? ";
        }

        $stmtCount = $db->prepare($countQuery);
        // Bind status params
        foreach ($eligible_statuses as $k => $v) {
            $stmtCount->bindValue($k+1, $v);
        }
        if ($filter_assignment) {
            $stmtCount->bindValue(count($eligible_statuses)+1, $_SESSION['user_id']);
        }
        
        $stmtCount->execute();
        $total_rows = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total_rows / $limit);
        
        // 2. Get Data
        $query = "SELECT DISTINCT p.project_id, p.nama_project, p.company_name, p.tanggal_mcu, p.jenis_pemeriksaan, p.project_type, p.status_project, p.total_peserta,
                         mr.status as result_status,
                         (SELECT status FROM rab_medical_results WHERE project_id = p.project_id ORDER BY created_at DESC LIMIT 1) as rab_status,
                         (SELECT GROUP_CONCAT(DISTINCT u.full_name SEPARATOR ', ') 
                          FROM medical_result_items mri2 
                          JOIN medical_results mr2 ON mri2.medical_result_id = mr2.id 
                          JOIN users u ON mri2.assigned_to_user_id = u.user_id 
                          WHERE mr2.project_id = p.project_id) as assigned_names
                  FROM projects p
                  LEFT JOIN medical_results mr ON p.project_id = mr.project_id";
        
        if ($filter_assignment) {
            $query .= " JOIN medical_result_items mri ON mr.id = mri.medical_result_id ";
        }

        $query .= " WHERE p.status_project IN ($status_placeholders)";
        // $query .= " AND (mr.status IS NULL OR mr.status != 'NOT_NEEDED')";
        
        if ($filter_assignment) {
            $query .= " AND mri.assigned_to_user_id = ? ";
        }

        $query .= " ORDER BY p.created_at DESC
                  LIMIT $limit OFFSET $offset";
                  
        $stmt = $db->prepare($query);
        // Bind status params
        foreach ($eligible_statuses as $k => $v) {
            $stmt->bindValue($k+1, $v);
        }
        if ($filter_assignment) {
            $stmt->bindValue(count($eligible_statuses)+1, $_SESSION['user_id']);
        }

        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get Surat Hasil Users for assignment dropdown (for Modal)
        $kohas_users = [];
        if (in_array($_SESSION['role'], ['admin_ops', 'manager_ops', 'superadmin'])) {
            $userModel = $this->loadModel('User');
            $kohas_stmt = $userModel->getUsersByRole('surat_hasil');
            $kohas_users = $kohas_stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->view('medical_results/index', [
            'page_title' => $page_title,
            'projects' => $projects,
            'page' => $page,
            'total_pages' => $total_pages,
            'total_rows' => $total_rows,
            'kohas_users' => $kohas_users,
            'view_mode' => $view_mode
        ]);
    }

    private function getTatInfo($project) {
        $normal_days = $this->setting->get('tat_normal_days') ?? 3;
        $mode = $this->setting->get('tat_calculation_mode') ?? 'calendar';
        
        // Fetch rules from JSON config
        $rules_json = $this->setting->get('tat_config_rules');
        $rules = $rules_json ? json_decode($rules_json, true) : [];
        if (!is_array($rules)) $rules = [];

        $max_days = $normal_days;
        $applied_rule = null;

        // Check against rules - find MAX days among matching keywords
        foreach ($rules as $rule) {
            $keyword = trim($rule['keyword'] ?? '');
            $days = intval($rule['days'] ?? 0);
            
            if ($keyword && stripos($project['jenis_pemeriksaan'], $keyword) !== false) {
                if ($days > $max_days) {
                    $max_days = $days;
                    $applied_rule = $rule;
                }
            }
        }
        
        return [
            'type' => $applied_rule ? 'Custom TAT (' . $applied_rule['keyword'] . ')' : 'Normal TAT',
            'days' => $max_days,
            'mode' => $mode
        ];
    }

    public function detail() {
        if (!isset($_GET['id'])) {
            header("Location: index.php?page=medical_results_index");
            exit;
        }

        $project_id = $_GET['id'];
        $project = $this->verifyProjectAccessLocal($project_id);
        
        $medical_result = $this->medicalResult->getByProjectId($project_id);
        
        // Initialize if not exists
        if (!$medical_result) {
            $this->medicalResult->createOrUpdate([
                'project_id' => $project_id,
                'link_summary_excel' => '',
                'link_summary_dashboard' => ''
            ]);
            $medical_result = $this->medicalResult->getByProjectId($project_id);
        }

        $items = $this->medicalResult->getItemsByResultId($medical_result['id']);
        
        // Get Dates from Project
        $mcu_dates = json_decode($project['tanggal_mcu'], true);
        if (!$mcu_dates) $mcu_dates = [];
        sort($mcu_dates);

        // Map Items by Date
        $items_by_date = [];
        foreach ($items as $item) {
            $item['followups'] = $this->medicalResult->getFollowupsByItemId($item['id']);
            $items_by_date[$item['date_mcu']] = $item;
        }

        // Get TAT Info
        $tat_info = $this->getTatInfo($project);
        $tat_days = $tat_info['days'];
        $tat_mode = $tat_info['mode'];

        // Prepare View Data
        $dates_data = [];
        foreach ($mcu_dates as $date) {
            $deadline = $this->calculateDeadline($date, $tat_days, $tat_mode);
            
            if (isset($items_by_date[$date])) {
                $item = $items_by_date[$date];
                $item['deadline_date'] = $deadline;
            } else {
                $item = [
                    'id' => null,
                    'date_mcu' => $date,
                    'actual_pax_checked' => 0,
                    'actual_pax_released' => 0,
                    'release_date' => null,
                    'link_pdf' => '',
                    'notes' => '',
                    'has_difference' => 0,
                    'difference_names' => '',
                    'difference_reason' => '',
                    'tat_overdue' => 0,
                    'tat_issue' => '',
                    'tat_issue_notes' => '',
                    'status' => 'PENDING',
                    'deadline_date' => $deadline,
                    'followups' => [],
                    'assigned_to_user_id' => null // Default null
                ];
            }
            $dates_data[] = $item;
        }

        // Get Surat Hasil Users for assignment dropdown
        $userModel = $this->loadModel('User');
        $kohas_stmt = $userModel->getUsersByRole('surat_hasil');
        $kohas_users = $kohas_stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('medical_results/detail', [
            'project' => $project,
            'result' => $medical_result,
            'dates_data' => $dates_data,
            'tat_info' => $tat_info,
            'kohas_users' => $kohas_users
        ]);
    }

    public function save_project_info() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token.");
        }

        $project_id = $_POST['project_id'];
        $this->verifyProjectAccessLocal($project_id);
        
        // Fetch old data for logging
        $old_data = $this->medicalResult->getByProjectId($project_id);
        
        $data = [
            'project_id' => $project_id,
            'link_summary_excel' => $_POST['link_summary_excel'],
            'link_summary_dashboard' => $_POST['link_summary_dashboard']
        ];
        
        if ($this->medicalResult->createOrUpdate($data)) {
            // Log Changes
            $changes = [];
            if ($old_data) {
                if ($old_data['link_summary_excel'] != $data['link_summary_excel']) {
                    $changes[] = "Summary Excel Link updated";
                }
                if ($old_data['link_summary_dashboard'] != $data['link_summary_dashboard']) {
                    $changes[] = "Summary Dashboard Link updated";
                }
            } else {
                $changes[] = "Medical Result Info Created";
            }
            
            if (!empty($changes)) {
                $this->project->logAction($project_id, 'Medical Result Summary Updated', $_SESSION['user_id'], implode(', ', $changes));
            }

            header("Location: index.php?page=medical_results_detail&id=$project_id&msg=Project info updated");
        } else {
            header("Location: index.php?page=medical_results_detail&id=$project_id&err=Failed to update");
        }
    }

    public function assign_project_batch() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        // Role Check
        if (!in_array($_SESSION['role'], ['admin_ops', 'manager_ops', 'superadmin'])) {
            die("Access Denied");
        }

        $project_id = $_POST['project_id'];
        $user_id = !empty($_POST['assigned_to_user_id']) ? $_POST['assigned_to_user_id'] : null;
        
        $this->verifyProjectAccessLocal($project_id);
        $project = $this->project->getById($project_id);
        
        // Ensure Medical Result exists
        $medical_result = $this->medicalResult->getByProjectId($project_id);
        if (!$medical_result) {
            $this->medicalResult->createOrUpdate([
                'project_id' => $project_id,
                'link_summary_excel' => '',
                'link_summary_dashboard' => ''
            ]);
            $medical_result = $this->medicalResult->getByProjectId($project_id);
        }
        
        // Get Project Dates
        $mcu_dates = json_decode($project['tanggal_mcu'], true);
        if (!$mcu_dates) $mcu_dates = [];
        
        // Get Existing Items to map
        $existing_items = $this->medicalResult->getItemsByResultId($medical_result['id']);
        $items_by_date = [];
        foreach ($existing_items as $item) {
            $items_by_date[$item['date_mcu']] = $item;
        }
        
        $count_updated = 0;
        $count_created = 0;
        
        // Determine TAT defaults for new items
        $tat_info = $this->getTatInfo($project);
        $tat_days = $tat_info['days'];
        $tat_mode = $tat_info['mode'];
        
        foreach ($mcu_dates as $date) {
            if (isset($items_by_date[$date])) {
                // Update existing
                $item_id = $items_by_date[$date]['id'];
                // Only update if different
                if ($items_by_date[$date]['assigned_to_user_id'] != $user_id) {
                    $this->medicalResult->updateAssignment($item_id, $user_id);
                    $count_updated++;
                }
            } else {
                // Create new
                $deadline = $this->calculateDeadline($date, $tat_days, $tat_mode);
                $data = [
                    'medical_result_id' => $medical_result['id'],
                    'date_mcu' => $date,
                    'actual_pax_checked' => 0,
                    'actual_pax_released' => 0,
                    'release_date' => null,
                    'link_pdf' => '',
                    'notes' => '',
                    'has_difference' => 0,
                    'difference_names' => '',
                    'difference_reason' => '',
                    'tat_overdue' => 0,
                    'tat_issue' => null,
                    'tat_issue_notes' => '',
                    'status' => 'PENDING',
                    'assigned_to_user_id' => $user_id
                ];
                $this->medicalResult->saveItem($data);
                $count_created++;
            }
        }
        
        // Log Action
        if ($count_updated > 0 || $count_created > 0) {
            $msg = "Batch Assignment: Updated $count_updated dates, Created $count_created dates.";
            $this->project->logAction($project_id, 'Medical Result Batch Assignment', $_SESSION['user_id'], $msg);
            
            // Email Notification to Kohas
            if ($user_id) {
                try {
                    $userModel = $this->loadModel('User');
                    $kohas = $userModel->getUserById($user_id);
                    if ($kohas && !empty($kohas['username'])) {
                        $salesName = $project['sales_name'] ?? '-';
                        $totalPeserta = $project['total_peserta'] ?? '-';
                        $tanggalMcu = isset($project['tanggal_mcu']) ? DateHelper::formatSmartDateIndonesian($project['tanggal_mcu']) : '-';

                        $subject = "[Penugasan] Anda ditugaskan sebagai Kohas: " . $project['nama_project'];
                        $content = "Anda telah ditunjuk sebagai Koordinator Hasil (Kohas) untuk project berikut:<br><br>";
                        $content .= "<b>Nama Project:</b> " . $project['nama_project'] . "<br>";
                        $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                        $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                        $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                        $content .= "<b>Total Tanggal:</b> " . count($mcu_dates) . "<br>";
                        
                        $link = MailHelper::getBaseUrl() . "?page=medical_results_detail&id=" . $project_id;
                        $html = MailHelper::getTemplate("Penugasan Kohas Baru", $content, $link);
                        MailHelper::send($kohas['username'], $subject, $html);
                    }
                } catch (Exception $e) {
                    error_log("Email notification failed on Kohas batch assignment: " . $e->getMessage());
                }
            }

            header("Location: index.php?page=medical_results_index&msg=Project assigned successfully ($count_updated updated, $count_created created)");
        } else {
            header("Location: index.php?page=medical_results_index&msg=No changes made (already assigned)");
        }
    }

    public function assign_user() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }
        
        $project_id = $_POST['project_id'];
        $this->verifyProjectAccessLocal($project_id, true);
        
        $item_id = $_POST['item_id'];
        $user_id = !empty($_POST['assigned_to_user_id']) ? $_POST['assigned_to_user_id'] : null;
        $date_mcu = $_POST['date_mcu'];
        
        $success = false;
        $old_assigned = null;
        
        if ($item_id) {
            // Update existing item
            $old_item = $this->medicalResult->getItemById($item_id);
            $old_assigned = $old_item['assigned_to_user_id'];
            
            if ($this->medicalResult->updateAssignment($item_id, $user_id)) {
                $success = true;
            }
        } else {
            // Create new item with assignment
            $medical_result_id = $_POST['medical_result_id'];
            $data = [
                'medical_result_id' => $medical_result_id,
                'date_mcu' => $date_mcu,
                'actual_pax_checked' => 0,
                'actual_pax_released' => 0,
                'release_date' => null,
                'link_pdf' => '',
                'notes' => '',
                'has_difference' => 0,
                'difference_names' => '',
                'difference_reason' => '',
                'tat_overdue' => 0,
                'tat_issue' => null,
                'tat_issue_notes' => '',
                'status' => 'PENDING',
                'assigned_to_user_id' => $user_id
            ];
            if ($this->medicalResult->saveItem($data)) {
                $success = true;
            }
        }
        
        if ($success) {
            // Log if changed
            if ($old_assigned != $user_id) {
                $this->project->logAction($project_id, 'Medical Result Assignment Updated', $_SESSION['user_id'], "Date $date_mcu: Assigned User Updated");

                // Email Notification to Kohas
                if ($user_id) {
                    try {
                        $userModel = $this->loadModel('User');
                        $kohas = $userModel->getUserById($user_id);
                        if ($kohas && !empty($kohas['username'])) {
                            $project = $this->project->getProjectById($project_id);
                            $salesName = $project['sales_name'] ?? '-';
                            $totalPeserta = $project['total_peserta'] ?? '-';
                            $formattedDate = DateHelper::formatSmartDateIndonesian($date_mcu);

                            $subject = "[Penugasan] Anda ditugaskan sebagai Kohas: " . $project['nama_project'];
                            $content = "Anda telah ditunjuk sebagai Koordinator Hasil (Kohas) untuk tanggal <b>$formattedDate</b> pada project berikut:<br><br>";
                            $content .= "<b>Nama Project:</b> " . $project['nama_project'] . "<br>";
                            $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                            $content .= "<b>Tanggal MCU:</b> " . $formattedDate . "<br>";
                            $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                            
                            $link = MailHelper::getBaseUrl() . "?page=medical_results_detail&id=" . $project_id;
                            $html = MailHelper::getTemplate("Penugasan Kohas Baru", $content, $link);
                            MailHelper::send($kohas['username'], $subject, $html);
                        }
                    } catch (Exception $e) {
                        error_log("Email notification failed on Kohas assignment: " . $e->getMessage());
                    }
                }
            }
            header("Location: index.php?page=medical_results_detail&id=$project_id&msg=Assignment updated");
        } else {
            header("Location: index.php?page=medical_results_detail&id=$project_id&err=Failed to update assignment");
        }
    }

    public function save_item() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        // CSRF Protection
        if (!$this->validateCsrfToken()) {
             die("Invalid CSRF token.");
        }

        $project_id = $_POST['project_id'];
        $this->verifyProjectAccessLocal($project_id, true);
        
        $medical_result_id = $_POST['medical_result_id'];
        $item_id = $_POST['item_id'] ?? null;
        
        // Fetch old item for logging
        $old_item = null;
        if ($item_id) {
            $old_item = $this->medicalResult->getItemById($item_id);
        }

        // TAT Check
        $release_date = $_POST['release_date'];
        $deadline_date = $_POST['deadline_date'];
        
        $tat_overdue = 0;
        if (!empty($release_date) && $release_date > $deadline_date) {
            $tat_overdue = 1;
        }
        
        // Handle Difference Names & Reasons (JSON)
        $difference_names_json = '';
        $difference_reason_summary = '';
        
        // Determine if there is a difference based on Checked vs Released
        // User Request: "ketika Actual Checked = Actual Released <> Ada selisih hasil? dan ga usah ceklisbox buatkan otomatis aja"
        // So we should calculate has_difference automatically if possible, or trust the inputs.
        // The view will handle the UI, but here we should trust the posted data or recalculate.
        // Let's rely on the POST logic but maybe enforce consistency.
        
        $has_difference = 0;
        if (isset($_POST['actual_pax_checked']) && isset($_POST['actual_pax_released'])) {
             if ($_POST['actual_pax_released'] < $_POST['actual_pax_checked']) {
                 $has_difference = 1;
             }
        }

        // However, the user might have manually added difference data even if numbers match (unlikely but possible edge case)
        // Or if the form sends 'has_difference' checkbox (which we are hiding/automating).
        // Let's check if difference data is provided.
        
        $difference_data = [];

        // NEW: Group-based Input
        if (isset($_POST['diff_group_reason']) && isset($_POST['diff_group_names'])) {
            $group_reasons = $_POST['diff_group_reason'];
            $group_names = $_POST['diff_group_names'];
            
            for ($i = 0; $i < count($group_reasons); $i++) {
                $reason = trim($group_reasons[$i]);
                $names_block = $group_names[$i];
                
                // Split names by newline
                $names_list = preg_split('/\r\n|\r|\n/', $names_block);
                
                foreach ($names_list as $name) {
                    $name = trim($name);
                    if (!empty($name)) {
                        $difference_data[] = [
                            'name' => $name,
                            'reason' => $reason
                        ];
                    }
                }
            }
        } 
        // FALLBACK: Old Per-Person Input (for backward compatibility or JS fail)
        elseif (isset($_POST['diff_name'])) {
            $diff_names_arr = $_POST['diff_name'] ?? [];
            $diff_reasons_arr = $_POST['diff_reason'] ?? [];
            
            for ($i = 0; $i < count($diff_names_arr); $i++) {
                if (!empty(trim($diff_names_arr[$i]))) {
                    $difference_data[] = [
                        'name' => trim($diff_names_arr[$i]),
                        'reason' => trim($diff_reasons_arr[$i] ?? '')
                    ];
                }
            }
        }
        
        if (!empty($difference_data)) {
            $difference_names_json = json_encode($difference_data);
            
            // Summarize reasons for the display column
            $unique_reasons = array_unique(array_column($difference_data, 'reason'));
            $difference_reason_summary = implode(', ', $unique_reasons);
            if (empty($difference_reason_summary)) {
                    $difference_reason_summary = count($difference_data) . " participants";
            }
            // Force has_difference = 1 if data exists
            $has_difference = 1;
        }
        $assigned_user_id = (array_key_exists('assigned_to_user_id', $_POST))
            ? (!empty($_POST['assigned_to_user_id']) ? $_POST['assigned_to_user_id'] : null)
            : ($old_item['assigned_to_user_id'] ?? null);
        
        $data = [
            'id' => $item_id,
            'medical_result_id' => $medical_result_id,
            'date_mcu' => $_POST['date_mcu'],
            'actual_pax_checked' => $_POST['actual_pax_checked'],
            'actual_pax_released' => $_POST['actual_pax_released'],
            'release_date' => !empty($release_date) ? $release_date : null,
            'link_pdf' => $_POST['link_pdf'],
            'notes' => $_POST['notes'],
            'has_difference' => $has_difference,
            'difference_names' => $difference_names_json,
            'difference_reason' => $difference_reason_summary,
            'tat_overdue' => $tat_overdue,
            'tat_issue' => $tat_overdue ? ($_POST['tat_issue'] ?? null) : null,
            'tat_issue_notes' => $tat_overdue ? ($_POST['tat_issue_notes'] ?? '') : '',
            'status' => 'RELEASED',
            'assigned_to_user_id' => $assigned_user_id
        ];
        
        if (empty($data['release_date'])) {
            $data['status'] = 'PENDING';
        }

        if ($this->medicalResult->saveItem($data)) {
            // Log Changes
            $changes = [];
            $date_mcu = $_POST['date_mcu'];
            
            if ($old_item) {
                if ($old_item['actual_pax_checked'] != $data['actual_pax_checked']) 
                    $changes[] = "Checked: {$old_item['actual_pax_checked']} -> {$data['actual_pax_checked']}";
                if ($old_item['actual_pax_released'] != $data['actual_pax_released']) 
                    $changes[] = "Released: {$old_item['actual_pax_released']} -> {$data['actual_pax_released']}";
                if ($old_item['release_date'] != $data['release_date']) 
                    $changes[] = "Release Date: " . ($old_item['release_date'] ?? 'N/A') . " -> " . ($data['release_date'] ?? 'N/A');
                if ($old_item['link_pdf'] != $data['link_pdf']) 
                    $changes[] = "Link PDF updated";
                if ($old_item['difference_names'] != $data['difference_names']) 
                    $changes[] = "Difference Data updated";
                if (($old_item['assigned_to_user_id'] ?? null) != $data['assigned_to_user_id'])
                    $changes[] = "Assigned User updated";
            } else {
                $changes[] = "Initial Entry for $date_mcu";
            }
            
            if (!empty($changes)) {
                $note = "Date $date_mcu: " . implode(', ', $changes);
                $this->project->logAction($project_id, 'Medical Result Item Updated', $_SESSION['user_id'], $note);
            }

            // Trigger Project Status Update
            $this->project->checkAndSetInProgressOps($project_id);

            header("Location: index.php?page=medical_results_detail&id=$project_id&msg=Item saved&saved_date=$date_mcu");
        } else {
            header("Location: index.php?page=medical_results_detail&id=$project_id&err=Failed to save item");
        }
    }

    public function save_followup() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }
        
        $project_id = $_POST['project_id'];
        $this->verifyProjectAccessLocal($project_id, true);
        
        $item_id = $_POST['item_id'];
        $followup_id = $_POST['followup_id'] ?? null;
        
        // Fetch old data
        $old_followup = null;
        if ($followup_id) {
            $old_followup = $this->medicalResult->getFollowupById($followup_id);
        }
        
        $release_date = $_POST['release_date_susulan'];

        // Handle Pax Names (Selection)
        $selected_names = $_POST['followup_names'] ?? [];
        $pax_susulan = $_POST['pax_susulan'];
        $pax_names_json = '';

        if (!empty($selected_names)) {
             $pax_susulan = count($selected_names);
             $pax_names_json = json_encode($selected_names);
        }

        $data = [
            'id' => $followup_id,
            'medical_result_item_id' => $item_id,
            'pax_susulan' => $pax_susulan,
            'pax_names' => $pax_names_json,
            'release_date_susulan' => !empty($release_date) ? $release_date : null,
            'reason' => $_POST['reason'],
            'tat_overdue' => 0,
            'tat_issue' => null,
            'tat_issue_notes' => ''
        ];

        if ($this->medicalResult->saveFollowup($data)) {
            // Log Changes
            $changes = [];
            
            if ($old_followup) {
                if ($old_followup['pax_susulan'] != $data['pax_susulan']) 
                    $changes[] = "Pax Susulan: {$old_followup['pax_susulan']} -> {$data['pax_susulan']}";
                if ($old_followup['release_date_susulan'] != $data['release_date_susulan']) 
                    $changes[] = "Date Susulan: " . ($old_followup['release_date_susulan'] ?? 'N/A') . " -> " . ($data['release_date_susulan'] ?? 'N/A');
                if ($old_followup['reason'] != $data['reason']) 
                    $changes[] = "Reason updated";
                if ($old_followup['pax_names'] != $data['pax_names']) 
                    $changes[] = "Susulan Participants updated";
            } else {
                $changes[] = "Follow-up Created (Pax: {$data['pax_susulan']})";
            }
            
            if (!empty($changes)) {
                $this->project->logAction($project_id, 'Medical Result Follow-up Updated', $_SESSION['user_id'], implode(', ', $changes));
            }

            // Trigger Project Status Update
            $this->project->checkAndSetInProgressOps($project_id);

            header("Location: index.php?page=medical_results_detail&id=$project_id&msg=Follow-up saved");
        } else {
            header("Location: index.php?page=medical_results_detail&id=$project_id&err=Failed to save follow-up");
        }
    }

    public function mark_completed() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        $project_id = $_POST['project_id'];
        $this->verifyProjectAccessLocal($project_id, true);
        
        // Check for Follow-up / Susulan
        $has_followup = isset($_POST['has_followup']) && $_POST['has_followup'] == 'yes';
        
        if ($has_followup) {
            $count = $_POST['pending_count'] ?? 0;
            $notes = $_POST['pending_notes'] ?? '';
            
            if ($this->medicalResult->setPendingParticipants($project_id, $count, $notes)) {
                 $this->project->logAction($project_id, 'Medical Result Status', $_SESSION['user_id'], "Marked as PENDING PARTICIPANTS (Susulan: $count pax. Notes: $notes)");
                 header("Location: index.php?page=medical_results_detail&id=$project_id&msg=Project marked as PENDING PARTICIPANTS");
                 return;
            } else {
                 header("Location: index.php?page=medical_results_detail&id=$project_id&err=Failed to update status");
                 return;
            }
        }

        // Validation: All dates must be RELEASED
        $result = $this->medicalResult->getByProjectId($project_id);
        $items = $this->medicalResult->getItemsByResultId($result['id']);
        
        $project = $this->project->getById($project_id);
        $mcu_dates = json_decode($project['tanggal_mcu'], true);
        
        // Check if all project dates exist in items and are RELEASED or CANCELLED
        $all_released = true;
        $existing_dates = [];
        foreach ($items as $item) {
            $existing_dates[] = $item['date_mcu'];
            
            // Allow RELEASED, CANCELLED, or other final statuses
            // User Request: "tidak harus released semua... bisa jadi ada 1 tanggal yang cancel"
            $status = strtoupper($item['status']);
            $allowed_statuses = ['RELEASED', 'CANCELLED', 'COMPLETED', 'NOT_NEEDED', 'NO_RESULT'];
            
            if (!in_array($status, $allowed_statuses)) {
                // Also allow if it contains 'CANCEL' (e.g. pending_cancellation if that's considered final/no-approval)
                if (strpos($status, 'CANCEL') !== false) {
                    continue;
                }
                
                $all_released = false;
                break;
            }
        }
        
        // Also check coverage
        foreach ($mcu_dates as $date) {
            if (!in_array($date, $existing_dates)) {
                $all_released = false;
                break;
            }
        }
        
        if ($all_released) {
            $this->medicalResult->updateStatus($project_id, 'COMPLETED');
            
            // Trigger Project Status Update (Ready for Invoicing Check)
            $this->project->checkAndSetReadyForInvoicing($project_id);
            
            header("Location: index.php?page=medical_results_detail&id=$project_id&msg=Project marked as COMPLETED");
        } else {
            header("Location: index.php?page=medical_results_detail&id=$project_id&err=Cannot complete. All dates must be processed (RELEASED or CANCELLED).");
        }
    }

    private function calculateDeadline($start_date, $days, $mode) {
        if ($mode == 'working_days') {
            $current_date = strtotime($start_date);
            $added = 0;
            while ($added < $days) {
                $current_date = strtotime("+1 day", $current_date);
                $day_of_week = date('N', $current_date);
                
                // Check Weekend (Sat/Sun)
                if ($day_of_week >= 6) continue;
                
                // Check Holiday
                $date_str = date('Y-m-d', $current_date);
                if ($this->holiday->isHoliday($date_str)) continue;
                
                $added++;
            }
            return date('Y-m-d', $current_date);
        } else {
            return date('Y-m-d', strtotime("+$days days", strtotime($start_date)));
        }
    }

    public function mark_not_needed() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_POST['project_id'])) {
            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                die("Invalid CSRF token. Possible CSRF attack detected!");
            }
            
            $project_id = $_POST['project_id'];
            
            // Verify Access
            $this->verifyProjectAccessLocal($project_id, true);
            
            if ($this->medicalResult->setStatus($project_id, 'NOT_NEEDED')) {
                // Log Action
                $this->project->logAction($project_id, 'Medical Result Status', $_SESSION['user_id'], 'Marked as Not Needed (Rejected from List)');
                
                // Trigger Project Status Update
                $this->project->checkAndSetReadyForInvoicing($project_id);

                header("Location: index.php?page=medical_results_index&msg=marked_not_needed");
                exit;
            } else {
                echo "Error updating status.";
            }
        }
    }
}
?>
