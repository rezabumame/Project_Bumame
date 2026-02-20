<?php
include_once 'BaseController.php';

class RabController extends BaseController {
    private $rab;
    private $project;
    private $setting;
    private $user;
    private $costCode;
    private $notification;
    private $chatParticipant;

    public function __construct() {
        parent::__construct();
        $curr_page = $_GET['page'] ?? '';
        if ($curr_page !== 'qr_verify_rab') {
            $this->checkAuth();
        }
        
        $this->rab = $this->loadModel('Rab');
        $this->project = $this->loadModel('Project');
        $this->setting = $this->loadModel('SystemSetting');
        $this->user = $this->loadModel('User');
        $this->costCode = $this->loadModel('CostCode');
        $this->notification = $this->loadModel('Notification');
        $this->chatParticipant = $this->loadModel('ChatParticipant');
    }

    private function verifyProjectAccessLocal($project_id) {
        return $this->verifyProjectAccess($project_id);
    }

    private function verifyRabAccessLocal($rab_id) {
        $rab = $this->rab->getById($rab_id);
        if (!$rab) {
            return false;
        }
        
        // Use unified project access check
        if ($this->verifyProjectAccess($rab['project_id'])) {
            return $rab;
        }
        
        return false;
    }

    public function index() {
        $page_title = "Daftar Pengajuan RAB";
        
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        
        // Filters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? 'all',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        // Fetch Data
        // Pagination
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $stmt = $this->rab->getFilteredRabs($role, $user_id, $filters, $limit, $offset);
        $rabs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_rows = $this->rab->countFilteredRabs($role, $user_id, $filters);
        $total_pages = ceil($total_rows / $limit);
        
        // Summary Stats
        $summary = $this->rab->getSummaryStats($role, $user_id);
        
        include '../views/rabs/index.php';
    }

    public function show() {
        if (!isset($_GET['id'])) {
            $this->redirect('rabs_list');
        }
        
        $id = $_GET['id'];
        $rab = $this->verifyRabAccessLocal($id);
        
        if (!$rab) {
            echo "RAB not found or Access Denied.";
            return;
        }
        
        $items = $this->rab->getItems($id);

        // Fetch Project Info for Days Calculation
        $project_info = $this->project->getById($rab['project_id']);
        $items = $this->rab->getItems($id);
        $project_dates = [];
        if (!empty($project_info['tanggal_mcu'])) {
            if (strpos($project_info['tanggal_mcu'], '[') === 0) {
                $decoded = json_decode($project_info['tanggal_mcu'], true);
                if (is_array($decoded)) $project_dates = $decoded;
            } else {
                $project_dates = array_map('trim', explode(',', $project_info['tanggal_mcu']));
            }
        }
        $project_total_days = count($project_dates);
        $page_title = "Detail RAB: " . $rab['rab_number'];

        $sph_file = $project_info['sph_file'] ?? null;

        // Get Cost Codes for mapping
        $stmtCodes = $this->costCode->getByCategory('RAB');
        $cost_codes = $stmtCodes->fetchAll(PDO::FETCH_ASSOC);
        $cost_code_map = [];
        foreach ($cost_codes as $cc) {
            if (!empty($cc['lookup_value'])) {
                $cost_code_map[$cc['lookup_value']] = $cc['code'];
            }
        }

        // Get Fee Settings for fallback display if price is 0
        $stmtSettings = $this->setting->getAll();
        $settings = $stmtSettings->fetchAll(PDO::FETCH_ASSOC);
        $fee_settings = [];
        foreach ($settings as $s) {
            if (strpos($s['setting_key'], 'fee_') === 0) {
                $fee_settings[$s['setting_key']] = $s['setting_value'];
            }
        }
        
        // Generate CSRF Token for approval forms
        $csrf_token = $this->generateCsrfToken();

        // Get Lark Link from System Configuration
        $lark_link = $this->setting->get('lark_link') ?? 'https://www.larksuite.com';

        include '../views/rabs/show.php';
    }

    public function create() {
        // Authorization
        $this->checkRole(['korlap', 'admin_ops', 'superadmin']);
        $page_title = "Create RAB";
        
        // Get Projects available for RAB
        // Logic: Projects that have MCU Date.
        $stmt = $this->rab->getAvailableProjects($_SESSION['role'], $_SESSION['user_id']);
        $raw_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $projects = [];

        $today = date('Y-m-d');

        // Optimization: Fetch all RABs for these projects in one query
        $project_ids = array_column($raw_projects, 'project_id');
        $all_project_rabs = $this->rab->getRabsByProjectIds($project_ids);
        
        // Group RABs by project_id
        $rabs_by_project = [];
        foreach ($all_project_rabs as $r) {
            $rabs_by_project[$r['project_id']][] = $r;
        }

        foreach ($raw_projects as $p) {
            // NEW: Check if project is in invoicing stage - if yes, skip it
            $invoicing_statuses = ['ready_for_invoicing', 'invoice_requested', 'invoiced', 'paid', 'completed'];
            if (in_array($p['status_project'], $invoicing_statuses)) {
                continue; // Skip project if already in invoicing or completed
            }

            $p_dates = [];
            if (!empty($p['tanggal_mcu'])) {
                if (strpos($p['tanggal_mcu'], '[') === 0) {
                    $decoded = json_decode($p['tanggal_mcu'], true);
                    if (is_array($decoded)) {
                        $p_dates = $decoded;
                    }
                } else {
                    $p_dates = array_map('trim', explode(',', $p['tanggal_mcu']));
                }
            }

            if (empty($p_dates)) continue;

            // Filter future dates
            $future_dates = [];
            foreach ($p_dates as $d) {
                if ($d >= $today) {
                    $future_dates[] = $d;
                }
            }
            
            // If no future dates, skip project
            if (empty($future_dates)) continue;

            // NEW: Always add project if it has future dates and not in invoicing
            // The filtering of used vs available dates will happen in AJAX call (get_project_dates)
            $projects[] = $p;
        }

        // Get Fee Settings
        $stmtSettings = $this->setting->getAll();
        $settings = $stmtSettings->fetchAll(PDO::FETCH_ASSOC);
        $fee_settings = [];
        foreach ($settings as $s) {
            if (strpos($s['setting_key'], 'fee_') === 0) {
                $fee_settings[$s['setting_key']] = $s['setting_value'];
            }
        }

        include '../views/rabs/create.php';
    }
    public function qr_verify() {
        $id = $_GET['id'] ?? null;
        $who = strtolower($_GET['who'] ?? 'creator');
        if (!$id) {
            die("Invalid RAB ID");
        }
        $rab = $this->rab->getById($id);
        if (!$rab) {
            die("RAB not found");
        }
        $project = $this->project->getById($rab['project_id']);
        $doc_title = "RAB";
        $doc_number = $rab['rab_number'] ?? '-';
        $project_name = $project['nama_project'] ?? '-';
        $company = $project['company_name'] ?? '-';

        $name = '-';
        $role = '-';
        $approved_at = null;
        $status_label = 'Belum Approved';

        if ($who === 'creator' || $who === 'sales') {
            $creator = !empty($rab['created_by']) ? $this->user->getUserById($rab['created_by']) : null;
            $name = $creator['full_name'] ?? ($rab['creator_name'] ?? '-');
            $role = !empty($rab['creator_jabatan']) ? $rab['creator_jabatan'] : 'Creator';
            $approved_at = $rab['created_at'] ?? null;
            $status_label = 'Dibuat';
        } elseif ($who === 'manager' || $who === 'mgr') {
            $name = $rab['manager_name'] ?? '-';
            $role = !empty($rab['manager_jabatan']) ? $rab['manager_jabatan'] : 'Manager Operation';
            $approved_at = $rab['approved_date_manager'] ?? null;
        } elseif ($who === 'head') {
            $name = $rab['head_name'] ?? '-';
            $role = !empty($rab['head_jabatan']) ? $rab['head_jabatan'] : 'Head Operation';
            $approved_at = $rab['approved_date_head'] ?? null;
        }

        if (!empty($approved_at) && $status_label !== 'Dibuat') {
            $status_label = 'Approved';
        }

        include '../views/rabs/qr_verify.php';
        exit;
    }

    public function get_project_dates() {
        if (!class_exists('DateHelper')) {
            include_once __DIR__ . '/../helpers/DateHelper.php';
        }

        if (!isset($_GET['project_id'])) {
            echo json_encode(['dates' => [], 'total_participants' => 0, 'total_days' => 0]);
            return;
        }

        $project_id = $_GET['project_id'];

        if (!$this->verifyProjectAccess($project_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Access Denied']);
            exit;
        }

        $exclude_rab_id = $_GET['exclude_rab_id'] ?? null;
        
        // Get Project Dates
        $query = "SELECT tanggal_mcu, total_peserta, sph_file, lunch, snack, procurement_lunch_qty, procurement_snack_qty, lunch_items, snack_items FROM projects WHERE project_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $project_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $dates = [];
        if ($row && !empty($row['tanggal_mcu'])) {
            $dates = DateHelper::parseDateArray($row['tanggal_mcu']);
        }
        
        $total_participants = $row['total_peserta'] ?? 0;
        $total_days = count($dates);

        // Filter out dates that already have RAB
        $existing_rabs = $this->rab->isProjectHasRab($project_id);
        $used_dates = [];
        foreach ($existing_rabs as $r) {
            if ($exclude_rab_id && $r['id'] == $exclude_rab_id) continue;

            if (!empty($r['selected_dates'])) {
                $r_dates = json_decode($r['selected_dates'], true);
                if (is_array($r_dates)) {
                    $used_dates = array_merge($used_dates, $r_dates);
                }
            }
        }
        
        $available_dates = array_diff($dates, $used_dates);
        
        // Filter past dates
        $today = date('Y-m-d');
        $valid_available_dates = [];
        foreach($available_dates as $d) {
            if($d >= $today) {
                $valid_available_dates[] = $d;
            }
        }

        // Fetch Vendor Allocations
        $vendor_allocations = [];
        $stmtVendor = $this->project->getVendorAllocations($project_id);
        while($v = $stmtVendor->fetch(PDO::FETCH_ASSOC)) {
            $vendor_allocations[] = $v;
        }

        $formatted_dates = [];
        foreach ($valid_available_dates as $d) {
            $formatted_dates[] = [
                'raw' => $d,
                'formatted' => DateHelper::formatIndonesianDate($d)
            ];
        }

        echo json_encode([
            'dates' => $formatted_dates,
            'total_participants' => $total_participants,
            'total_days' => $total_days,
            'sph_file' => $row['sph_file'] ?? null,
            'lunch' => $row['lunch'] ?? 'Tidak',
            'snack' => $row['snack'] ?? 'Tidak',
            'procurement_lunch_qty' => $row['procurement_lunch_qty'] ?? 0,
            'procurement_snack_qty' => $row['procurement_snack_qty'] ?? 0,
            'lunch_items' => $row['lunch_items'] ?? '[]',
            'snack_items' => $row['snack_items'] ?? '[]',
            'vendor_allocations' => $vendor_allocations
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        $project_id = $_POST['project_id'];
        
        // Basic Role Check
        $this->checkRole(['korlap', 'superadmin']);

        // Verify Project Access
        if (!$this->verifyProjectAccess($project_id)) {
            $this->redirect('projects_show', ['id' => $project_id, 'error' => 'Access Denied to Project.']);
            return;
        }

        // Check if project already has an approved RAB for the selected dates
        $selected_dates_raw = $_POST['selected_dates'] ?? '[]';
        $selected_dates = json_decode($selected_dates_raw, true);
        if (!is_array($selected_dates)) {
            $selected_dates = [];
        }
        sort($selected_dates); // Ensure consistent order for comparison

        if ($this->rab->isProjectHasApprovedRabForDates($project_id, $selected_dates)) {
            $this->redirect('rabs_create', ['project_id' => $project_id, 'error' => 'RAB for selected dates already exists and is approved.']);
            return;
        }

        $this->rab->project_id = $project_id;
        $this->rab->rab_number = $this->rab->generateRabNumber($project_id);
        $this->rab->status = $_POST['status'] ?? 'draft'; // 'draft' or 'need_approval_manager'
        $this->rab->created_by = $_SESSION['user_id'];
        // $this->rab->created_date = date('Y-m-d H:i:s'); // No longer needed, DB handles created_at
        $this->rab->selected_dates = json_encode($selected_dates);
        $this->rab->total_participants = str_replace('.', '', $_POST['total_participants'] ?? 0);
        $this->rab->total_days = str_replace('.', '', $_POST['total_days'] ?? 0);
        $this->rab->location_type = $_POST['location_type'] ?? 'dalam_kota';
        $this->rab->sph_file = $_POST['sph_file'] ?? null;
        $this->rab->lunch_status = $_POST['lunch_status'] ?? 'Tidak';
        $this->rab->snack_status = $_POST['snack_status'] ?? 'Tidak';
        $this->rab->procurement_lunch_qty = str_replace('.', '', $_POST['procurement_lunch_qty'] ?? 0);
        $this->rab->procurement_snack_qty = str_replace('.', '', $_POST['procurement_snack_qty'] ?? 0);
        $this->rab->personnel_notes = $_POST['notes_personnel'] ?? '';

        $items = [];
        $total_personnel = 0;
        $total_transport = 0;
        $total_consumption = 0;
        $total_vendor = 0;

        // Personnel Items
        if (isset($_POST['personnel']) && is_array($_POST['personnel'])) {
            foreach ($_POST['personnel'] as $p_item) {
                $qty = str_replace('.', '', $p_item['qty']);
                $days = str_replace('.', '', $p_item['days']);
                $price = str_replace('.', '', $p_item['price']);
                $subtotal = $qty * $days * $price;
                $total_personnel += $subtotal;
                $items[] = [
                    'category' => 'personnel',
                    'item_name' => $p_item['role'],
                    'qty' => $qty,
                    'days' => $days,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'notes' => $p_item['notes']
                ];
            }
        }

        // Transport Items
        // BBM
        if (isset($_POST['transport_bbm_check'])) {
            $nominal = str_replace('.', '', $_POST['transport_bbm_nominal'] ?? 0);
            $cars = str_replace('.', '', $_POST['transport_bbm_cars'] ?? 0);
            $days = str_replace('.', '', $_POST['transport_bbm_days'] ?? 0);
            $subtotal = $nominal * $cars * $days;
            $total_transport += $subtotal;
            $items[] = [
                'category' => 'transport',
                'item_name' => 'BBM (Bahan Bakar)',
                'qty' => $cars,
                'days' => $days,
                'price' => $nominal,
                'subtotal' => $subtotal,
                'notes' => ''
            ];
        }

        // Tol
        if (isset($_POST['transport_tol_check'])) {
            $nominal = str_replace('.', '', $_POST['transport_tol_nominal'] ?? 0);
            $cars = str_replace('.', '', $_POST['transport_tol_cars'] ?? 0);
            $days = str_replace('.', '', $_POST['transport_tol_days'] ?? 0);
            $subtotal = $nominal * $cars * $days;
            $total_transport += $subtotal;
            $items[] = [
                'category' => 'transport',
                'item_name' => 'Biaya Tol',
                'qty' => $cars,
                'days' => $days,
                'price' => $nominal,
                'subtotal' => $subtotal,
                'notes' => ''
            ];
        }

        // Emergency
        if (isset($_POST['transport_emergency_check'])) {
            $nominal = str_replace('.', '', $_POST['transport_emergency_nominal'] ?? 0);
            $notes = $_POST['transport_emergency_notes'] ?? '';
            $subtotal = $nominal; // Flat amount
            $total_transport += $subtotal;
            $items[] = [
                'category' => 'transport',
                'item_name' => 'Emergency Cost',
                'qty' => 1,
                'days' => 1,
                'price' => $nominal,
                'subtotal' => $subtotal,
                'notes' => $notes
            ];
        }

        // Vendor Items
        if (isset($_POST['vendor']) && is_array($_POST['vendor'])) {
            foreach ($_POST['vendor'] as $v_item) {
                $qty = str_replace('.', '', $v_item['qty']);
                $days = str_replace('.', '', $v_item['days']);
                $price = str_replace('.', '', $v_item['price']);
                $subtotal = $qty * $days * $price;
                $total_vendor += $subtotal;
                $items[] = [
                    'category' => 'vendor',
                    'item_name' => $v_item['item_name'],
                    'qty' => $qty,
                    'days' => $days,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'notes' => $v_item['notes']
                ];
            }
        }

        // Consumption Items (from checkboxes)
        // Air Mineral Petugas
        if (isset($_POST['cons_mineral_check'])) {
            $qty = str_replace('.', '', $_POST['cons_mineral_qty']);
            $days = str_replace('.', '', $_POST['cons_mineral_days']);
            $price = str_replace('.', '', $_POST['cons_mineral_price']);
            $subtotal = $qty * $days * $price;
            $total_consumption += $subtotal;
            $items[] = [
                'category' => 'consumption',
                'item_name' => 'Air Mineral Petugas',
                'qty' => $qty,
                'days' => $days,
                'price' => $price,
                'subtotal' => $subtotal,
                'notes' => ''
            ];
        }

        // Makan Siang Petugas
        if (isset($_POST['cons_lunch_staff_check'])) {
            $qty = str_replace('.', '', $_POST['cons_lunch_staff_qty']);
            $days = str_replace('.', '', $_POST['cons_lunch_staff_days']);
            $price = str_replace('.', '', $_POST['cons_lunch_staff_price']);
            $subtotal = $qty * $days * $price;
            $total_consumption += $subtotal;
            $items[] = [
                'category' => 'consumption',
                'item_name' => 'Makan Siang Petugas',
                'qty' => $qty,
                'days' => $days,
                'price' => $price,
                'subtotal' => $subtotal,
                'notes' => ''
            ];
        }

        // Consumption Items (from checkboxes)
        // Snack Participant
        if (isset($_POST['cons_snack_participant_check'])) {
            $qty = str_replace('.', '', $_POST['cons_snack_participant_qty']);
            $days = str_replace('.', '', $_POST['cons_snack_participant_days']);
            $items[] = [
                'category' => 'consumption',
                'item_name' => 'Snack Peserta',
                'qty' => $qty,
                'days' => $days,
                'price' => 0,
                'subtotal' => 0,
                'notes' => ''
            ];
        }
        // Lunch Participant
        if (isset($_POST['cons_lunch_participant_check'])) {
            $qty = str_replace('.', '', $_POST['cons_lunch_participant_qty']);
            $days = str_replace('.', '', $_POST['cons_lunch_participant_days']);
            $items[] = [
                'category' => 'consumption',
                'item_name' => 'Makan Siang Peserta',
                'qty' => $qty,
                'days' => $days,
                'price' => 0,
                'subtotal' => 0,
                'notes' => ''
            ];
        }
        $this->rab->total_consumption = $total_consumption;
        $this->rab->total_personnel = $total_personnel;
        $this->rab->total_transport = $total_transport;
        $this->rab->total_vendor = $total_vendor;
        
        $this->rab->grand_total = $total_personnel + $total_transport + $total_consumption + $total_vendor;

        if ($this->rab->create()) {
            $this->rab->addItems($items);
            
            // Log Action
            $action_log = ($this->rab->status == 'draft') ? 'RAB Draft Created' : 'RAB Submitted';
            $this->project->logAction($project_id, $action_log, $_SESSION['user_id'], "RAB Number: " . $this->rab->rab_number);

            // Trigger Project Status Update
            if ($this->rab->status != 'draft') {
                $this->project->checkAndSetInProgressOps($project_id, 'Auto-update triggered by RAB creation');
                
                // Email Notification to Manager
                try {
                    $emails = $this->user->getEmailsByRole('manager_ops');
                    if (!empty($emails)) {
                        $projectData = $this->project->getProjectById($project_id);
                        $salesName = $projectData['sales_name'] ?? '-';
                        $totalPeserta = $projectData['total_peserta'] ?? '-';
                        $tanggalMcu = isset($projectData['tanggal_mcu']) ? DateHelper::formatSmartDateIndonesian($projectData['tanggal_mcu']) : '-';

                        $subject = "[Action Required] Approval RAB: " . $this->rab->rab_number;
                        $content = "RAB baru telah diajukan dan memerlukan persetujuan Anda.<br><br>";
                        $content .= "<b>No. RAB:</b> " . $this->rab->rab_number . "<br>";
                        $content .= "<b>Nama Project:</b> " . $projectData['nama_project'] . "<br>";
                        $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                        $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                        $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                        $content .= "<b>Total:</b> Rp " . number_format($this->rab->grand_total, 0, ',', '.') . "<br>";
                        
                        $link = MailHelper::getBaseUrl() . "?page=rabs_show&id=" . $this->rab->id;
                        $html = MailHelper::getTemplate("Pengajuan RAB Baru", $content, $link);
                        MailHelper::send($emails, $subject, $html);
                    }
                } catch (Exception $e) {
                    error_log("Email notification failed on RAB creation: " . $e->getMessage());
                }
            }

            $msg = ($this->rab->status == 'draft') ? 'RAB berhasil disimpan sebagai draft.' : 'RAB berhasil diajukan.';
            $this->redirect('rabs_show', ['id' => $this->rab->id, 'msg' => $msg]);
        } else {
            die("Error creating RAB.");
        }
    }

    public function approve() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }
        
        // Basic Role Check
        $this->checkRole(['manager_ops', 'head_ops', 'ceo', 'superadmin']);

        $id = $_POST['id'];
        $action = $_POST['action'];
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        
        $this->rab->id = $id;
        $rabData = $this->verifyRabAccessLocal($id);
        
        if (!$rabData) {
            echo "RAB not found or Access Denied.";
            return;
        }

        // Fetch User Jabatan
        $userData = $this->user->getUserById($user_id);
        $user_jabatan = $userData['jabatan'] ?? '';
        
        $data = [];
        $msg = "";
        
        if ($action == 'reject') {
            $data['status'] = 'draft';
            
            $rejector_name = $_SESSION['full_name'] ?? 'Unknown';
            $rejector_role = ucwords(str_replace('_', ' ', $_SESSION['role'] ?? ''));
            $timestamp = date('d/m/Y H:i');
            
            $reason_with_info = htmlspecialchars($_POST['reason']) . " (Oleh: {$rejector_name} - {$rejector_role} pada {$timestamp})";
            
            $current_history = $rabData['rejection_reason'] ?? '';
            if (!empty($current_history)) {
                $data['rejection_reason'] = $current_history . "||" . $reason_with_info;
            } else {
                $data['rejection_reason'] = $reason_with_info;
            }
            
            $msg = "RAB dikembalikan ke Draft. Catatan: " . htmlspecialchars($_POST['reason']);
        } elseif ($action == 'approve') {
            // Superadmin can act as any approver, but must still follow status sequence
            if ($role == 'superadmin') {
                // Superadmin acts based on current status
                if ($rabData['status'] == 'need_approval_manager') {
                    $data['status'] = 'need_approval_head';
                    $data['approved_by_manager'] = $user_id;
                    $data['approved_date_manager'] = date('Y-m-d H:i:s');
                    $data['manager_name'] = $_SESSION['full_name'];
                    $data['manager_jabatan'] = $user_jabatan;
                    
                    $cost_value = $_POST['cost_value'] ?? 0;
                    $data['cost_value'] = str_replace('.', '', $cost_value);
                    $data['cost_percentage'] = $_POST['cost_percentage'] ?? 0;
                } elseif ($rabData['status'] == 'need_approval_head') {
                    $data['approved_by_head'] = $user_id;
                    $data['approved_date_head'] = date('Y-m-d H:i:s');
                    $data['head_name'] = $_SESSION['full_name'];
                    $data['head_jabatan'] = $user_jabatan;

                    if ($rabData['grand_total'] >= 20000000) {
                        $data['status'] = 'need_approval_ceo';
                    } else {
                        $data['status'] = 'approved';
                    }
                } elseif ($rabData['status'] == 'need_approval_ceo') {
                    $data['status'] = 'approved';
                    $data['approved_by_ceo'] = $user_id;
                    $data['approved_date_ceo'] = date('Y-m-d H:i:s');
                    $data['ceo_name'] = $_SESSION['full_name'];
                    $data['ceo_jabatan'] = $user_jabatan;
                } else {
                    echo "Invalid status for approval. Current status: " . $rabData['status'];
                    return;
                }
            } elseif ($role == 'manager_ops' && $rabData['status'] == 'need_approval_manager') {
                $data['status'] = 'need_approval_head';
                $data['approved_by_manager'] = $user_id;
                $data['approved_date_manager'] = date('Y-m-d H:i:s');
                
                $cost_value = $_POST['cost_value'] ?? 0;
                $data['cost_value'] = str_replace('.', '', $cost_value);
                
                $data['cost_percentage'] = $_POST['cost_percentage'] ?? 0;
            } elseif ($role == 'head_ops' && $rabData['status'] == 'need_approval_head') {
                $data['approved_by_head'] = $user_id;
                $data['approved_date_head'] = date('Y-m-d H:i:s');

                if ($rabData['grand_total'] >= 20000000) {
                    $data['status'] = 'need_approval_ceo';
                } else {
                    $data['status'] = 'approved';
                }
            } elseif ($role == 'ceo' && $rabData['status'] == 'need_approval_ceo') {
                $data['status'] = 'approved';
                $data['approved_by_ceo'] = $user_id;
                $data['approved_date_ceo'] = date('Y-m-d H:i:s');
            } else {
                echo "Unauthorized action or invalid status transition. Role: $role, Status: " . $rabData['status'];
                return;
            }
            $msg = "RAB berhasil disetujui.";
        }
        
        if ($this->rab->updateApproval($data)) {
            // Log Action
            $action_log = ($action == 'approve') ? 'RAB Approved' : 'RAB Rejected';
            $notes = "RAB Number: " . $rabData['rab_number'];
            if ($action == 'reject') $notes .= " - Reason: " . $_POST['reason'];
            
            $this->project->logAction($rabData['project_id'], $action_log, $_SESSION['user_id'], $notes);

            // Trigger Project Status Update
            if ($action == 'approve') {
                $this->project->checkAndSetInProgressOps($rabData['project_id'], 'Auto-update triggered by RAB Approval');
            }

            // Notify Korlap on sendback/reject
            if ($action == 'reject' && !empty($rabData['created_by'])) {
                $this->notification->user_id = $rabData['created_by'];
                $this->notification->type = 'rab_sendback';
                $this->notification->message = "RAB dikembalikan ke Draft: " . $rabData['rab_number'];
                $this->notification->link = "index.php?page=rabs_show&id=" . $id;
                $this->notification->create();

                // Email Notification on Reject
                try {
                    $creator = $this->user->getUserById($rabData['created_by']);
                    if ($creator && !empty($creator['username'])) {
                        $projectData = $this->project->getProjectById($rabData['project_id']);
                        $salesName = $projectData['sales_name'] ?? '-';
                        $totalPeserta = $projectData['total_peserta'] ?? '-';
                        $tanggalMcu = isset($projectData['tanggal_mcu']) ? DateHelper::formatSmartDateIndonesian($projectData['tanggal_mcu']) : '-';

                        $subject = "[Revisi] RAB Dikembalikan: " . $rabData['rab_number'];
                        $content = "RAB Anda dikembalikan ke Draft untuk direvisi.<br><br>";
                        $content .= "<b>No. RAB:</b> " . $rabData['rab_number'] . "<br>";
                        $content .= "<b>Nama Project:</b> " . $projectData['nama_project'] . "<br>";
                        $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                        $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                        $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                        $content .= "<b>Alasan:</b> " . $_POST['reason'] . "<br>";
                        
                        $link = MailHelper::getBaseUrl() . "?page=rabs_show&id=" . $id;
                        $html = MailHelper::getTemplate("RAB Dikembalikan", $content, $link);
                        MailHelper::send($creator['username'], $subject, $html);
                    }
                } catch (Exception $e) {
                    error_log("Email notification failed on RAB rejection: " . $e->getMessage());
                }
            }

            // Email Notification on Approve
            if ($action == 'approve') {
                try {
                    $new_status = $data['status'];
                    $emails = [];
                    $subject = "";
                    $content = "";
                    
                    if ($new_status == 'need_approval_head') {
                        $emails = $this->user->getEmailsByRole('head_ops');
                        $subject = "[Action Required] Approval RAB: " . $rabData['rab_number'];
                        $content = "RAB telah disetujui Manager Ops dan memerlukan persetujuan Anda.<br><br>";
                    } elseif ($new_status == 'need_approval_ceo') {
                        $emails = $this->user->getEmailsByRole('ceo');
                        $subject = "[Action Required] Approval RAB Besar: " . $rabData['rab_number'];
                        $content = "Ada pengajuan RAB di atas limit (20jt) yang memerlukan persetujuan CEO.<br><br>";
                    } elseif ($new_status == 'approved') {
                        // Filter Finance users who have 'AP' in their jabatan (Accounts Payable)
                        $finance_users_stmt = $this->user->getUsersByRole('finance');
                        $finance_users = $finance_users_stmt->fetchAll(PDO::FETCH_ASSOC);
                        $finance_emails = [];
                        
                        foreach ($finance_users as $u) {
                            if (stripos($u['jabatan'], 'AP') !== false) {
                                if (!empty($u['username'])) $finance_emails[] = $u['username']; // username is email
                            }
                        }
                        
                        $emails = $finance_emails;
                        $creator = $this->user->getUserById($rabData['created_by']);
                        if ($creator && !empty($creator['username'])) {
                            $emails[] = $creator['username'];
                        }
                        $subject = "[Info] RAB Disetujui: " . $rabData['rab_number'];
                        $content = "RAB telah disetujui sepenuhnya. Finance (AP) dapat memproses pembayaran.<br><br>";
                    }

                    if (!empty($emails)) {
                        $projectData = $this->project->getProjectById($rabData['project_id']);
                        $salesName = $projectData['sales_name'] ?? '-';
                        $totalPeserta = $projectData['total_peserta'] ?? '-';
                        $tanggalMcu = isset($projectData['tanggal_mcu']) ? DateHelper::formatSmartDateIndonesian($projectData['tanggal_mcu']) : '-';

                        $content .= "<b>No. RAB:</b> " . $rabData['rab_number'] . "<br>";
                        $content .= "<b>Nama Project:</b> " . $projectData['nama_project'] . "<br>";
                        $content .= "<b>Total Peserta:</b> " . $totalPeserta . " Peserta<br>";
                        $content .= "<b>Tanggal MCU:</b> " . $tanggalMcu . "<br>";
                        $content .= "<b>Nama Sales:</b> " . $salesName . "<br>";
                        $content .= "<b>Total:</b> Rp " . number_format($rabData['grand_total'], 0, ',', '.') . "<br>";
                        $link = MailHelper::getBaseUrl() . "?page=rabs_show&id=" . $id;
                        $html = MailHelper::getTemplate("Update Approval RAB", $content, $link);
                        MailHelper::send($emails, $subject, $html);
                    }
                } catch (Exception $e) {
                    error_log("Email notification failed on RAB approval: " . $e->getMessage());
                }
            }

            $this->redirect('rabs_show', ['id' => $id, 'msg' => $msg]);
        } else {
            echo "Error updating RAB.";
        }
    }

    public function update_profit() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }
        
        $this->checkRole('manager_ops');
        
        $id = $_POST['id'];
        $cost_value = str_replace('.', '', $_POST['cost_value'] ?? 0);
        $cost_percentage = $_POST['cost_percentage'] ?? 0;
        
        $this->rab->id = $id;
        
        $rabData = $this->verifyRabAccessLocal($id);
        if (!$rabData) {
            die("RAB not found or Access Denied.");
        }
        
        if ($rabData['status'] == 'need_approval_head') {
            if ($this->rab->updateCostAnalysis($cost_value, $cost_percentage)) {
                $msg = "Budget Operasional berhasil diperbarui.";
                $this->redirect('rabs_show', ['id' => $id, 'msg' => $msg]);
            } else {
                echo "Error updating Budget Analysis.";
            }
        } else {
             $msg = "Status RAB tidak valid untuk update budget.";
             $this->redirect('rabs_show', ['id' => $id, 'msg' => $msg]);
        }
    }

    public function submit_to_finance() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        // Anyone involved in the ops workflow should be able to submit if approved
        $this->checkRole(['korlap', 'admin_ops', 'manager_ops', 'head_ops', 'superadmin']);

        $id = $_POST['id'];

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        $this->rab->id = $id;
        $rabData = $this->verifyRabAccessLocal($id);
        
        if (!$rabData) die("RAB not found or Access Denied.");
        
        if ($rabData['status'] != 'approved') {
            die("Invalid status for submission to finance.");
        }
        
        if ($this->rab->submitToFinance($_SESSION['user_id'])) {
            $this->project->logAction(
                $rabData['project_id'], 
                'Submit to Finance', 
                $_SESSION['user_id'], 
                "RAB Number: " . $rabData['rab_number'] . ". Status: Approved -> Submitted to Finance"
            );
            
            $msg = "RAB berhasil disubmit ke Finance.";
            $this->redirect('rabs_show', ['id' => $id, 'msg' => $msg]);
        } else {
            echo "Error submitting to finance.";
        }
    }

    public function auto_approve_and_submit_finance() {
        $this->checkRole(['korlap', 'admin_ops', 'superadmin']);
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        $id = $_POST['id'];
        $rabData = $this->verifyRabAccessLocal($id);
        if (!$rabData) die("RAB not found or Access Denied.");

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        // 1. Auto Approve (Force status to approved)
        // We set approvers to system or current user for audit trail
        $this->rab->id = $id;
        $data = [
            'status' => 'approved',
            'approved_by_manager' => $_SESSION['user_id'],
            'approved_date_manager' => date('Y-m-d H:i:s'),
            'approved_by_head' => $_SESSION['user_id'],
            'approved_date_head' => date('Y-m-d H:i:s'),
            'manager_name' => $_SESSION['full_name'] . " (Auto)",
            'head_name' => $_SESSION['full_name'] . " (Auto)"
        ];
        
        // If grand total >= 20jt, also auto-approve by CEO
        if ($rabData['grand_total'] >= 20000000) {
            $data['approved_by_ceo'] = $_SESSION['user_id'];
            $data['approved_date_ceo'] = date('Y-m-d H:i:s');
            $data['ceo_name'] = $_SESSION['full_name'] . " (Auto)";
        }

        if ($this->rab->updateApproval($data)) {
            $this->project->logAction($rabData['project_id'], 'RAB Auto-Approved (Consumption)', $_SESSION['user_id'], "RAB Number: " . $rabData['rab_number']);
            
            // 2. Submit to Finance
            if ($this->rab->submitToFinance($_SESSION['user_id'])) {
                $this->project->logAction(
                    $rabData['project_id'], 
                    'Submit to Finance', 
                    $_SESSION['user_id'], 
                    "RAB Number: " . $rabData['rab_number'] . ". Status: Auto-Approved -> Submitted to Finance"
                );
                
                $msg = "RAB berhasil diapprove otomatis dan disubmit ke Finance.";
                $this->redirect('rabs_show', ['id' => $id, 'msg' => $msg]);
            } else {
                echo "Error submitting to finance after auto-approval.";
            }
        } else {
            echo "Error during auto-approval.";
        }
    }

    public function advance_paid() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        $this->checkRole('finance');

        $id = $_POST['id'];

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        $this->rab->id = $id;
        $rabData = $this->verifyRabAccessLocal($id);
        
        if (!$rabData) die("RAB not found or Access Denied.");
        
        if ($rabData['status'] != 'submitted_to_finance') {
             die("Invalid status for payment processing.");
        }
        
        $proof_path = null;
        if (isset($_FILES['transfer_proof']) && $_FILES['transfer_proof']['error'] == 0) {
            $target_dir = "../public/uploads/finance_proofs/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES["transfer_proof"]["name"], PATHINFO_EXTENSION);
            $new_filename = "proof_" . $id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["transfer_proof"]["tmp_name"], $target_file)) {
                $proof_path = "uploads/finance_proofs/" . $new_filename;
            }
        }
        
        $data = [
            'user_id' => $_SESSION['user_id'],
            'proof_path' => $proof_path,
            'note' => $_POST['finance_note'] ?? ''
        ];
        
        if ($this->rab->markAdvancePaid($data)) {
            $notes = "RAB Number: " . $rabData['rab_number'] . ". Status: Submitted to Finance -> Advance Paid";
            if (!empty($data['note'])) $notes .= " Note: " . $data['note'];
            if ($proof_path) $notes .= " Proof: " . $proof_path;
            
            $this->project->logAction(
                $rabData['project_id'], 
                'Advance Paid', 
                $_SESSION['user_id'], 
                $notes
            );
            
            $msg = "Pembayaran Advance berhasil diproses.";
            $this->redirect('rabs_show', ['id' => $id, 'msg' => $msg]);
        } else {
            echo "Error processing payment.";
        }
    }

    public function edit() {
        if (!isset($_GET['id'])) {
            $this->redirect('rabs_list');
        }

        $id = $_GET['id'];
        $rab = $this->verifyRabAccessLocal($id);
        
        if (!$rab) {
            echo "RAB not found or Access Denied.";
            return;
        }

        $allowed_status = ['draft', 'need_approval_manager', 'rejected'];
        if (!in_array($rab['status'], $allowed_status)) {
            echo "RAB cannot be edited in current status.";
            return;
        }
        
        if ($_SESSION['role'] != 'superadmin' && $_SESSION['role'] != 'admin_ops' && $_SESSION['user_id'] != $rab['created_by']) {
            echo "Access Denied.";
            return;
        }

        $items = $this->rab->getItems($id);
        $page_title = "Edit RAB: " . $rab['rab_number'];
        
        $project_info = $this->project->getById($rab['project_id']);
        
        $stmtSettings = $this->setting->getAll();
        $settings = $stmtSettings->fetchAll(PDO::FETCH_ASSOC);
        $fee_settings = [];
        foreach ($settings as $s) {
            if (strpos($s['setting_key'], 'fee_') === 0) {
                $fee_settings[$s['setting_key']] = $s['setting_value'];
            }
        }
        
        $project_dates = [];
        if (!empty($project_info['tanggal_mcu'])) {
            if (strpos($project_info['tanggal_mcu'], '[') === 0) {
                $decoded = json_decode($project_info['tanggal_mcu'], true);
                if (is_array($decoded)) $project_dates = $decoded;
            } else {
                $project_dates = array_map('trim', explode(',', $project_info['tanggal_mcu']));
            }
        }
        $total_days = count($project_dates);
        
        $vendor_allocations = [];
        $stmtVendor = $this->project->getVendorAllocations($rab['project_id']);
        while($v = $stmtVendor->fetch(PDO::FETCH_ASSOC)) {
            $vendor_allocations[] = $v;
        }

        // Initialize Default Values for Transport & Consumption
        $transport_bbm = ['check' => false, 'nominal' => 0, 'cars' => 0, 'days' => 0];
        $transport_tol = ['check' => false, 'nominal' => 0, 'cars' => 0, 'days' => 0];
        $transport_emergency = ['check' => false, 'nominal' => 0, 'notes' => ''];
        
        $cons_mineral = ['check' => false, 'qty' => 0, 'price' => 0, 'days' => 0];
        $cons_lunch_staff = ['check' => false, 'qty' => 0, 'price' => 0, 'days' => 0];
        $cons_snack_participant = ['check' => false, 'qty' => 0, 'days' => 0];
        $cons_lunch_participant = ['check' => false, 'qty' => 0, 'days' => 0];

        // Populate from Saved Items
        foreach ($items as $item) {
            if ($item['category'] == 'transport') {
                if ($item['item_name'] == 'BBM (Bahan Bakar)') {
                    $transport_bbm = [
                        'check' => true,
                        'nominal' => $item['price'],
                        'cars' => $item['qty'],
                        'days' => $item['days']
                    ];
                } elseif ($item['item_name'] == 'Biaya Tol') {
                    $transport_tol = [
                        'check' => true,
                        'nominal' => $item['price'],
                        'cars' => $item['qty'],
                        'days' => $item['days']
                    ];
                } elseif ($item['item_name'] == 'Emergency Cost') {
                    $transport_emergency = [
                        'check' => true,
                        'nominal' => $item['price'],
                        'notes' => $item['notes']
                    ];
                }
            } elseif ($item['category'] == 'consumption') {
                if ($item['item_name'] == 'Air Mineral Petugas') {
                    $cons_mineral = [
                        'check' => true,
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'days' => $item['days']
                    ];
                } elseif ($item['item_name'] == 'Makan Siang Petugas') {
                    $cons_lunch_staff = [
                        'check' => true,
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'days' => $item['days']
                    ];
                } elseif ($item['item_name'] == 'Snack Peserta') {
                    $cons_snack_participant = [
                        'check' => true,
                        'qty' => $item['qty'],
                        'days' => $item['days']
                    ];
                } elseif ($item['item_name'] == 'Makan Siang Peserta') {
                    $cons_lunch_participant = [
                        'check' => true,
                        'qty' => $item['qty'],
                        'days' => $item['days']
                    ];
                }
            }
        }

        // Pass current project for the selector
        $projects = [$project_info];

        include '../views/rabs/edit.php';
    }

    public function update() {
        $this->checkRole(['korlap', 'admin_ops', 'superadmin']);
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('rabs_list');
        }

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        $id = $_POST['id'];
        $rab = $this->verifyRabAccessLocal($id);
        
        if (!$rab) {
            die("RAB not found or Access Denied.");
        }
        
        $allowed_status = ['draft', 'need_approval_manager', 'rejected'];
        if (!in_array($rab['status'], $allowed_status)) {
            die("RAB cannot be edited in current status.");
        }

        $this->rab->id = $id;
        $this->rab->location_type = $_POST['location_type'];
        if (isset($_POST['dates'])) {
            $this->rab->selected_dates = json_encode($_POST['dates']);
        } else {
            $this->rab->selected_dates = $rab['selected_dates'];
        }
        $this->rab->total_participants = str_replace('.', '', $_POST['total_participants']);
        $this->rab->personnel_notes = $_POST['notes_personnel'] ?? '';
        
        $items = [];
        
        $personnel_data = $_POST['personnel'] ?? [];
        $total_personnel = 0;
        foreach ($personnel_data as $p) {
            if (isset($p['selected']) && $p['selected'] == 1) {
                $qty = $p['qty'];
                $price = str_replace('.', '', $p['price']);
                $days = $p['days'];
                $subtotal = $qty * $price * $days;
                
                $items[] = [
                    'category' => 'personnel',
                    'item_name' => $p['role'],
                    'qty' => $qty,
                    'days' => $days,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'notes' => $p['notes']
                ];
                $total_personnel += $subtotal;
            }
        }
        $this->rab->total_personnel = $total_personnel;

        $vendor_data = $_POST['vendor'] ?? [];
        $total_vendor = 0;
        foreach ($vendor_data as $v) {
            $qty = $v['qty'];
            $price = str_replace('.', '', $v['price']);
            $days = $v['days'];
            $subtotal = $qty * $price * $days;
            
            $items[] = [
                'category' => 'vendor',
                'item_name' => $v['item_name'],
                'qty' => $qty,
                'days' => $days,
                'price' => $price,
                'subtotal' => $subtotal,
                'notes' => $v['notes']
            ];
            $total_vendor += $subtotal;
        }
        $this->rab->total_vendor = $total_vendor;

        $total_transport = 0;
        if (isset($_POST['transport_bbm_check'])) {
            $nom = (int)preg_replace('/[^0-9]/', '', $_POST['transport_bbm_nominal']);
            $cars = (int)preg_replace('/[^0-9]/', '', $_POST['transport_bbm_cars']);
            $days = (int)preg_replace('/[^0-9]/', '', $_POST['transport_bbm_days']);
            $subtotal = $nom * $cars * $days;
            $items[] = ['category' => 'transport', 'item_name' => 'BBM (Bahan Bakar)', 'qty' => $cars, 'days' => $days, 'price' => $nom, 'subtotal' => $subtotal, 'notes' => ''];
            $total_transport += $subtotal;
        }
        if (isset($_POST['transport_tol_check'])) {
            $nom = (int)preg_replace('/[^0-9]/', '', $_POST['transport_tol_nominal']);
            $cars = (int)preg_replace('/[^0-9]/', '', $_POST['transport_tol_cars']);
            $days = (int)preg_replace('/[^0-9]/', '', $_POST['transport_tol_days']);
            $subtotal = $nom * $cars * $days;
            $items[] = ['category' => 'transport', 'item_name' => 'Biaya Tol', 'qty' => $cars, 'days' => $days, 'price' => $nom, 'subtotal' => $subtotal, 'notes' => ''];
            $total_transport += $subtotal;
        }
        if (isset($_POST['transport_emergency_check'])) {
            $nom = (int)preg_replace('/[^0-9]/', '', $_POST['transport_emergency_nominal']);
            $items[] = ['category' => 'transport', 'item_name' => 'Emergency Cost', 'qty' => 1, 'days' => 1, 'price' => $nom, 'subtotal' => $nom, 'notes' => $_POST['transport_emergency_notes'] ?? ''];
            $total_transport += $nom;
        }
        $this->rab->total_transport = $total_transport;

        $total_consumption = 0;
        if (isset($_POST['cons_mineral_check'])) {
            $qty = str_replace('.', '', $_POST['cons_mineral_qty']);
            $price = str_replace('.', '', $_POST['cons_mineral_price']);
            $days = str_replace('.', '', $_POST['cons_mineral_days']);
            $subtotal = $qty * $price * $days;
            $items[] = ['category' => 'consumption', 'item_name' => 'Air Mineral Petugas', 'qty' => $qty, 'days' => $days, 'price' => $price, 'subtotal' => $subtotal, 'notes' => ''];
            $total_consumption += $subtotal;
        }
        if (isset($_POST['cons_lunch_staff_check'])) {
            $qty = str_replace('.', '', $_POST['cons_lunch_staff_qty']);
            $price = str_replace('.', '', $_POST['cons_lunch_staff_price']);
            $days = str_replace('.', '', $_POST['cons_lunch_staff_days']);
            $subtotal = $qty * $price * $days;
            $items[] = ['category' => 'consumption', 'item_name' => 'Makan Siang Petugas', 'qty' => $qty, 'days' => $days, 'price' => $price, 'subtotal' => $subtotal, 'notes' => ''];
            $total_consumption += $subtotal;
        }
        if (isset($_POST['cons_snack_participant_check'])) {
            $qty = str_replace('.', '', $_POST['cons_snack_participant_qty']);
            $days = str_replace('.', '', $_POST['cons_snack_participant_days']);
            $items[] = ['category' => 'consumption', 'item_name' => 'Snack Peserta', 'qty' => $qty, 'days' => $days, 'price' => 0, 'subtotal' => 0, 'notes' => ''];
        }
        if (isset($_POST['cons_lunch_participant_check'])) {
            $qty = str_replace('.', '', $_POST['cons_lunch_participant_qty']);
            $days = str_replace('.', '', $_POST['cons_lunch_participant_days']);
            $items[] = ['category' => 'consumption', 'item_name' => 'Makan Siang Peserta', 'qty' => $qty, 'days' => $days, 'price' => 0, 'subtotal' => 0, 'notes' => ''];
        }
        $this->rab->total_consumption = $total_consumption;
        
        $this->rab->grand_total = $total_personnel + $total_transport + $total_consumption + $total_vendor;

        $action = $_POST['action'] ?? 'submit';
        if ($action == 'submit') {
            $this->rab->status = 'need_approval_manager';
        } else {
             $this->rab->status = 'draft';
        }
        
        $this->rab->approved_by_manager = null;
        $this->rab->approved_date_manager = null;
        $this->rab->approved_by_head = null;
        $this->rab->approved_date_head = null;
        $this->rab->approved_by_ceo = null;
        $this->rab->approved_date_ceo = null;
        $this->rab->rejection_reason = null;

        if ($this->rab->update()) {
            $this->rab->deleteItems($id);
            $this->rab->addItems($items);
            
            $this->project->logAction($rab['project_id'], "RAB Updated", $_SESSION['user_id'], "RAB Number: " . $rab['rab_number']);

            // Auto update project status to in_progress_ops if needed
            if ($this->rab->status != 'draft') {
                $this->project->checkAndSetInProgressOps($rab['project_id']);
            }

            $msg = "RAB berhasil diperbarui.";
            $this->redirect('rabs_show', ['id' => $id, 'msg' => $msg]);
        } else {
            die("Error updating RAB.");
        }
    }

    public function export_pdf() {
        if (!isset($_GET['id'])) {
            die("ID Required");
        }
        
        $id = $_GET['id'];
        $rab = $this->verifyRabAccessLocal($id);
        
        if (!$rab) {
            die("RAB not found or Access Denied.");
        }
        
        $items = $this->rab->getItems($id);

        // Fetch Project Info for Days Calculation
        $project_info = $this->project->getById($rab['project_id']);
        $project_dates = [];
        if (!empty($project_info['tanggal_mcu'])) {
            if (strpos($project_info['tanggal_mcu'], '[') === 0) {
                $decoded = json_decode($project_info['tanggal_mcu'], true);
                if (is_array($decoded)) $project_dates = $decoded;
            } else {
                $project_dates = array_map('trim', explode(',', $project_info['tanggal_mcu']));
            }
        }
        $project_total_days = count($project_dates);

        // Fetch current creator jabatan
        if (!empty($rab['created_by'])) {
            $creator = $this->user->getUserById($rab['created_by']);
            if ($creator && !empty($creator['jabatan'])) {
                $rab['creator_jabatan'] = $creator['jabatan'];
            }
        }

        // Fetch current approvers jabatan
        if (!empty($rab['approved_by_manager'])) {
            $manager = $this->user->getUserById($rab['approved_by_manager']);
            if ($manager && !empty($manager['jabatan'])) {
                $rab['manager_jabatan'] = $manager['jabatan'];
            }
        }

        if (!empty($rab['approved_by_head'])) {
            $head = $this->user->getUserById($rab['approved_by_head']);
            if ($head && !empty($head['jabatan'])) {
                $rab['head_jabatan'] = $head['jabatan'];
            }
        }

        if (!empty($rab['approved_by_ceo'])) {
            $ceo = $this->user->getUserById($rab['approved_by_ceo']);
            if ($ceo && !empty($ceo['jabatan'])) {
                $rab['ceo_jabatan'] = $ceo['jabatan'];
            }
        }
        
        $stmtCodes = $this->costCode->getByCategory('RAB');
        $cost_codes = $stmtCodes->fetchAll(PDO::FETCH_ASSOC);
        $cost_code_map = [];
        foreach ($cost_codes as $cc) {
            if (!empty($cc['lookup_value'])) {
                $cost_code_map[$cc['lookup_value']] = $cc['code'];
            }
        }

        $stmtSettings = $this->setting->getAll();
        $settings = $stmtSettings->fetchAll(PDO::FETCH_ASSOC);
        $fee_settings = [];
        $company_address = 'JL. TB SIMATUPANG NO.33 RT.01/ RW.05, RAGUNAN, PS MINGGU, JAKARTA SELATAN, DKI JAKARTA 12550';
        
        foreach ($settings as $s) {
            if (strpos($s['setting_key'], 'fee_') === 0) {
                $fee_settings[$s['setting_key']] = $s['setting_value'];
            }
            if ($s['setting_key'] === 'company_address') {
                $company_address = $s['setting_value'];
            }
        }
        
        include '../views/rabs/pdf.php';
    }

    public function export_csv() {
        $this->checkRole(['finance', 'superadmin']);
        
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? 'all',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        $stmt = $this->rab->getFilteredRabs($role, $user_id, $filters);
        $rabs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="export_rab_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['No RAB', 'Project', 'Creator', 'Sales', 'Total Grand', 'Status', 'Tanggal Dibuat']);

        foreach ($rabs as $r) {
            fputcsv($output, [
                $r['rab_number'],
                $r['nama_project'],
                $r['creator_name'],
                $r['sales_name'],
                $r['grand_total'],
                strtoupper($r['status']),
                $r['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
?>
