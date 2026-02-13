<?php
class InvoiceRequestController extends BaseController {
    private $invoiceRequest;
    private $project;
    private $company;
    private $user;

    public function __construct() {
        parent::__construct();
        $curr_page = $_GET['page'] ?? '';
        if ($curr_page !== 'qr_verify_invoice_request') {
            $this->checkRole(['admin_sales', 'superadmin', 'sales_support_supervisor', 'sales_performance_manager', 'sales', 'manager_sales', 'finance']);
        }
        
        $this->invoiceRequest = $this->loadModel('InvoiceRequest');
        $this->project = $this->loadModel('Project');
        $this->company = $this->loadModel('Company');
        $this->user = $this->loadModel('User');
    }

    public function index() {
        $requests = $this->invoiceRequest->getAll($_SESSION['role'], $_SESSION['user_id']);
        include '../views/invoice_requests/index.php';
    }

    public function create() {
        $step = $_GET['step'] ?? 1;

        if ($step == 1) {
            $projects = $this->project->getProjectsForInvoice();
            include '../views/invoice_requests/select_projects.php';
        } elseif ($step == 2) {
            $project_ids = $_POST['project_ids'] ?? [];
            if (empty($project_ids)) {
                $_SESSION['error'] = "Pilih minimal satu project.";
                header('Location: index.php?page=invoice_requests_create');
                exit;
            }

            // Fetch details
            $selected_projects = [];
            foreach ($project_ids as $pid) {
                $p = $this->project->getById($pid);
                if ($p) $selected_projects[] = $p;
            }

            // Group by Company
            $grouped = [];
            foreach ($selected_projects as $p) {
                $company = trim($p['company_name']);
                if (!isset($grouped[$company])) $grouped[$company] = [];
                $grouped[$company][] = $p;
            }

            // Pick Primary (First Group)
            $companies = array_keys($grouped);
            $primary_company = $companies[0];
            $primary_projects = $grouped[$primary_company];
            
            // Secondary (Drafts)
            $secondary_groups = [];
            for($i=1; $i<count($companies); $i++) {
                $comp = $companies[$i];
                $secondary_groups[$comp] = $grouped[$comp];
            }
            
            // Data for Form
            // Fix: pic_sales_id must be a valid User ID (from users table).
            // If admin_sales/superadmin, they can choose the Sales PIC.
            $isAdmin = in_array($_SESSION['role'], ['admin_sales', 'superadmin', 'sales_support_supervisor', 'sales_performance_manager']);
            $sales_users = [];
            if ($isAdmin) {
                $sales_users = $this->user->getUsersByRole('sales')->fetchAll(PDO::FETCH_ASSOC);
            }

            $currentUser = $this->user->getUserById($_SESSION['user_id']);
            $sales_id = $currentUser['user_id'];
            $sales_name = $currentUser['full_name'];

            $client_company = $primary_company;

            // Extract IDs for Primary
            $primary_project_ids = array_column($primary_projects, 'project_id');

            include '../views/invoice_requests/create.php';
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=invoice_requests_index');
            exit;
        }

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        // Validate Header
        $data = [
            'request_number' => $_POST['request_number'] ?? null,
            'request_date' => $_POST['request_date'],
            'pic_sales_id' => $_POST['pic_sales_id'],
            'partner_type' => $_POST['partner_type'] ?? 'Corporate',
            'event_type' => $_POST['event_type'] ?? 'On Site',
            'client_company' => $_POST['client_company'],
            'client_pic' => $_POST['client_pic'],
            'client_phone' => $_POST['client_phone'],
            'client_email' => $_POST['client_email'],
            'invoice_terms' => $_POST['invoice_terms'],
            'shipping_address' => $_POST['shipping_address'],
            'notes' => $_POST['notes'],
            'link_gdrive_npwp' => $_POST['link_gdrive_npwp'] ?? '',
            'link_gdrive_absensi' => $_POST['link_gdrive_absensi'] ?? ''
        ];

        // Process Items
        $items = [];
        if (isset($_POST['item_description'])) {
            $count = count($_POST['item_description']);
            for ($i = 0; $i < $count; $i++) {
                if (!empty($_POST['item_description'][$i])) {
                    $items[] = [
                        'item_description' => $_POST['item_description'][$i],
                        'price' => empty($_POST['price'][$i]) ? 0 : str_replace('.', '', $_POST['price'][$i]), // Remove dots from formatted currency
                        'qty' => $_POST['qty'][$i],
                        'remarks' => $_POST['remarks'][$i]
                    ];
                }
            }
        }

        if (empty($items)) {
            $_SESSION['error'] = "Detail item tidak boleh kosong.";
            // If failed, we need to handle redirect logic carefully, 
            // but for now redirect to index as we can't easily restore state without re-selecting
            header('Location: index.php?page=invoice_requests_create'); 
            exit;
        }

        // Primary Projects
        $primary_project_ids = json_decode($_POST['primary_project_ids'], true);
        
        // Handle Merge Mode
        $split_mode = $_POST['split_mode'] ?? 'split';
        
        // Scenario 1: Merge Secondary Groups (Different Companies)
        if ($split_mode === 'merge' && !empty($_POST['secondary_groups'])) {
            $secondary_groups_data = json_decode($_POST['secondary_groups'], true);
            foreach ($secondary_groups_data as $comp => $projects) {
                $sec_ids = array_column($projects, 'project_id');
                $primary_project_ids = array_merge($primary_project_ids, $sec_ids);
            }
            // Clear secondary groups so they aren't processed as drafts
            $_POST['secondary_groups'] = ''; 
        }

        // Scenario 2: Split Primary Projects (Same Company)
        $extra_draft_ids = [];
        if ($split_mode === 'split' && count($primary_project_ids) > 1) {
             // Keep the first project for the Main Request
             $main_id = array_shift($primary_project_ids);
             
             // The rest will be drafts
             $extra_draft_ids = $primary_project_ids;
             
             // Reset primary to just the first one
             $primary_project_ids = [$main_id];

             // Fix: Ensure Main Request uses the specific project's company name
             $mainProject = $this->project->getById($main_id);
             if ($mainProject && !empty($mainProject['company_name'])) {
                 $data['client_company'] = $mainProject['company_name'];
             }
        }

        // Create Primary Request
        $id = $this->invoiceRequest->create($data, $items, $primary_project_ids);
        
        // Handle Extra Drafts from Split Primary (Split Mode = split)
        if (!empty($extra_draft_ids)) {
             foreach ($extra_draft_ids as $pid) {
                $sec_sales_id = $_SESSION['user_id']; 
                $draftData = $data; // Copy all data from form
                
                // Fix: Fetch specific company name for this project
                $draftProject = $this->project->getById($pid);
                if ($draftProject && !empty($draftProject['company_name'])) {
                    $draftData['client_company'] = $draftProject['company_name'];
                }
                
                // Reset fields for draft (Keep only Projects, Company, Date, Sales, NPWP)
                $draftData['request_number'] = '-'; 
                $draftData['client_pic'] = '';
                $draftData['client_phone'] = '';
                $draftData['client_email'] = '';
                $draftData['invoice_terms'] = '';
                $draftData['shipping_address'] = '';
                $draftData['link_gdrive_npwp'] = '';
                $draftData['link_gdrive_absensi'] = '';
                $draftData['notes'] = 'Split from Project ' . $pid;
                
                // Create with empty items
                $this->invoiceRequest->create($draftData, [], [$pid]);
             }
        }

        // Handle Manual Split / Extra Draft Checkbox (e.g. Split NPWP)
        if (!empty($_POST['create_extra_draft'])) {
            // Check if client_company contains comma for auto-splitting names
            $original_company_string = $_POST['client_company']; // Use original input
            $company_names = array_map('trim', explode(',', $original_company_string));
            
            // If comma detected, we split the names
            if (count($company_names) > 1) {
                // 1. Update Main Request to use the first name
                $mainUpdateData = $data; // Start with current data
                $mainUpdateData['client_company'] = $company_names[0];
                
                // We need to use update() which requires items. 
                // We use the same items as created.
                $this->invoiceRequest->update($id, $mainUpdateData, $items);
                
                // 2. Create Drafts for the rest
                for ($i = 1; $i < count($company_names); $i++) {
                    $draftData = $data;
                    $draftData['client_company'] = $company_names[$i];
                    
                    // Reset fields for draft
                    $draftData['request_number'] = '-';
                    $draftData['client_pic'] = '';
                    $draftData['client_phone'] = '';
                    $draftData['client_email'] = '';
                    $draftData['invoice_terms'] = '';
                    $draftData['shipping_address'] = '';
                    $draftData['link_gdrive_npwp'] = '';
                    $draftData['link_gdrive_absensi'] = '';
                    $draftData['notes'] = 'Split NPWP ' . ($i+1);
                    
                    $this->invoiceRequest->create($draftData, [], $primary_project_ids);
                }
            } else {
                // Fallback: No comma, just clone as requested
                $draftData = $data;
                
                // Reset fields for draft
                $draftData['request_number'] = '-';
                $draftData['client_pic'] = '';
                $draftData['client_phone'] = '';
                $draftData['client_email'] = '';
                $draftData['invoice_terms'] = '';
                $draftData['shipping_address'] = '';
                $draftData['link_gdrive_npwp'] = '';
                $draftData['link_gdrive_absensi'] = '';
                $draftData['notes'] = 'Copy for Split NPWP';

                $this->invoiceRequest->create($draftData, [], $primary_project_ids);
            }
        }
        
        // Handle Secondary Groups (Split Logic - Different Companies)
        if (!empty($_POST['secondary_groups'])) {
            $secondary_groups = json_decode($_POST['secondary_groups'], true);
            foreach ($secondary_groups as $comp => $projects) {
                // Determine Sales ID (Use current user as PIC for consistency)
                $sec_sales_id = $_SESSION['user_id']; 
                
                $draftData = [
                    'request_date' => date('Y-m-d'),
                    'pic_sales_id' => $sec_sales_id,
                    'partner_type' => 'Corporate',
                    'event_type' => 'On Site',
                    'client_company' => $comp,
                    'client_pic' => '',
                    'client_phone' => '',
                    'client_email' => '',
                    'invoice_terms' => '',
                    'shipping_address' => '',
                    'link_gdrive_npwp' => '',
                    'link_gdrive_absensi' => '',
                    'notes' => 'Auto-generated draft from split request.'
                ];
                
                $sec_project_ids = array_column($projects, 'project_id');
                // Create with empty items? The model expects items. 
                // We should modify model or pass dummy item or allow empty items.
                // Or better: Create a dummy item "Pending Details"
                $dummyItems = [[
                    'item_description' => 'Pending Details (Split Request)',
                    'price' => 0,
                    'qty' => 1,
                    'remarks' => 'Auto-generated'
                ]];
                
                $this->invoiceRequest->create($draftData, $dummyItems, $sec_project_ids);
            }
        }

        if ($id) {
            $_SESSION['success'] = "Invoice Request berhasil dibuat.";
            if (!empty($_POST['secondary_groups'])) {
                $_SESSION['success'] .= " (Draft tambahan telah dibuat untuk company lain)";
            }
            
            if (isset($_POST['action']) && $_POST['action'] == 'submit') {
                header("Location: index.php?page=invoice_requests_submit&id=" . $id);
                exit;
            }
            
            header('Location: index.php?page=invoice_requests_view&id=' . $id);
        } else {
            $debug_msg = $_SESSION['error_debug'] ?? 'Unknown error';
            $_SESSION['error'] = "Gagal membuat Invoice Request. Error: " . $debug_msg;
            unset($_SESSION['error_debug']);
            header('Location: index.php?page=invoice_requests_create');
        }
        exit;
    }

    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=invoice_requests_index');
            exit;
        }

        // IDOR Protection
        if (!$this->verifyInvoiceAccess($id, true)) {
            die("Unauthorized Access to this Invoice Request.");
        }

        $request = $this->invoiceRequest->getById($id);
        if (!$request) {
            die("Invoice Request not found.");
        }
        include '../views/invoice_requests/view.php';
    }

    public function submit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = "Invalid Request ID.";
            header('Location: index.php?page=invoice_requests_index');
            exit;
        }

        // CSRF Protection
        if (!$this->validateCsrfToken($_GET['csrf_token'] ?? '')) {
            die("Invalid CSRF token.");
        }

        // IDOR Protection
        if (!$this->verifyInvoiceAccess($id, true)) {
            die("Unauthorized Access.");
        }

        // Logic changed: Submit only updates status to SUBMITTED
        // Approvals will follow.
        $this->invoiceRequest->updateStatus($id, 'SUBMITTED');
        
        $_SESSION['success'] = "Invoice Request submitted. Waiting for Sales Approval.";

        // Email Notification: Submit -> Sales PIC
        try {
            $request = $this->invoiceRequest->getById($id);
            // Only notify if submitter is NOT the Sales PIC (e.g. Admin Sales submitted it)
            if ($request['pic_sales_id'] != $_SESSION['user_id']) {
                $userModel = $this->loadModel('User');
                $salesPic = $userModel->getUserById($request['pic_sales_id']);
                
                if ($salesPic && !empty($salesPic['username'])) {
                    $subject = "[Action Required] Approval Invoice Request: " . ($request['request_number'] ?? $id);
                    $content = "Invoice Request baru telah diajukan dan memerlukan persetujuan Anda (Sales).<br><br>";
                    $content .= "<b>Client:</b> " . $request['client_company'] . "<br>";
                    $content .= "<b>Diajukan Oleh:</b> " . $_SESSION['full_name'] . "<br>";
                    
                    $link = MailHelper::getBaseUrl() . "?page=invoice_requests_view&id=" . $id;
                    $html = MailHelper::getTemplate("Approval Invoice Request", $content, $link);
                    MailHelper::send($salesPic['username'], $subject, $html);
                }
            }
        } catch (Exception $e) {
            error_log("Email notification failed on Invoice Request submit: " . $e->getMessage());
        }
        
        // Redirect back to view
        header('Location: index.php?page=invoice_requests_view&id=' . $id);
        exit;
    }

    public function approve() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = "Invalid Request ID.";
            header('Location: index.php?page=invoice_requests_index');
            exit;
        }

        // CSRF Protection
        if (!$this->validateCsrfToken($_GET['csrf_token'] ?? '')) {
            die("Invalid CSRF token.");
        }

        // IDOR Protection
        if (!$this->verifyInvoiceAccess($id, true)) {
            die("Unauthorized Access.");
        }

        $request = $this->invoiceRequest->getById($id);
        if (!$request) {
            die("Invoice Request not found.");
        }
        
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        
        // 1. Sales Approval (Admin Sales / Sales PIC)
        if ($role == 'admin_sales' || $role == 'sales' || $role == 'superadmin') {
            if ($request['status'] == 'SUBMITTED') {
                $can_approve = false;
                if ($role == 'superadmin' || $role == 'admin_sales') $can_approve = true;
                if ($role == 'sales' && $request['pic_sales_id'] == $user_id) $can_approve = true;
                
                if ($can_approve) {
                    $this->invoiceRequest->approveBySales($id, $user_id);
                    $_SESSION['success'] = "Approved by Sales.";

                    // Email: Sales -> Sales Support SPV
                    try {
                        $userModel = $this->loadModel('User');
                        $emails = $userModel->getEmailsByRole('sales_support_supervisor');
                        if (!empty($emails)) {
                            $subject = "[Action Required] Approval Invoice Request: " . ($request['request_number'] ?? $id);
                            $content = "Invoice Request telah disetujui Sales dan memerlukan persetujuan Anda (Sales Support SPV).<br><br>";
                            $content .= "<b>Client:</b> " . $request['client_company'] . "<br>";
                            $content .= "<b>Sales:</b> " . $request['sales_name'] . "<br>";
                            
                            $link = MailHelper::getBaseUrl() . "?page=invoice_requests_view&id=" . $id;
                            $html = MailHelper::getTemplate("Approval Invoice Request", $content, $link);
                            MailHelper::send($emails, $subject, $html);
                        }
                    } catch (Exception $e) {
                        error_log("Email failed Sales -> SPV: " . $e->getMessage());
                    }
                }
            }
        }
        
        // 2. Supervisor Approval
        if ($role == 'sales_support_supervisor' || $role == 'superadmin') {
            if ($request['status'] == 'APPROVED_SALES') {
                $this->invoiceRequest->approveBySupervisor($id, $user_id);
                $_SESSION['success'] = "Approved by Supervisor.";

                // Email: SPV -> Performance Manager Sales
                try {
                    $userModel = $this->loadModel('User');
                    $emails = $userModel->getEmailsByRole('sales_performance_manager');
                    if (!empty($emails)) {
                        $subject = "[Action Required] Approval Invoice Request: " . ($request['request_number'] ?? $id);
                        $content = "Invoice Request telah disetujui SPV dan memerlukan persetujuan Anda (Performance Manager).<br><br>";
                        $content .= "<b>Client:</b> " . $request['client_company'] . "<br>";
                        $content .= "<b>Sales:</b> " . $request['sales_name'] . "<br>";

                        $link = MailHelper::getBaseUrl() . "?page=invoice_requests_view&id=" . $id;
                        $html = MailHelper::getTemplate("Approval Invoice Request", $content, $link);
                        MailHelper::send($emails, $subject, $html);
                    }
                } catch (Exception $e) {
                    error_log("Email failed SPV -> Manager: " . $e->getMessage());
                }
            }
        }
        
        // 3. Manager Approval
        if ($role == 'sales_performance_manager' || $role == 'superadmin') {
            if ($request['status'] == 'APPROVED_SPV') {
                $this->invoiceRequest->approveByManager($id, $user_id);
                
                // Generate Invoices Drafts for Finance Processing
                $invoiceModel = $this->loadModel('Invoice');
                $invoiceModel->generateInvoicesFromRequest($id, $user_id);
                
                $_SESSION['success'] = "Approved by Manager. Invoice Request has been processed to Finance.";

                // Email: Manager -> Finance (AR)
                try {
                    $userModel = $this->loadModel('User');
                    // Filter Finance users who have 'AR' in their jabatan (Accounts Receivable)
                    $finance_users_stmt = $userModel->getUsersByRole('finance');
                    $finance_users = $finance_users_stmt->fetchAll(PDO::FETCH_ASSOC);
                    $finance_emails = [];
                    
                    foreach ($finance_users as $u) {
                        if (stripos($u['jabatan'], 'AR') !== false) {
                            if (!empty($u['username'])) $finance_emails[] = $u['username'];
                        }
                    }

                    if (!empty($finance_emails)) {
                        $subject = "[Action Required] Proses Invoice: " . ($request['request_number'] ?? $id);
                        $content = "Invoice Request telah disetujui Manager Sales. Silakan proses Invoice (AR).<br><br>";
                        $content .= "<b>Client:</b> " . $request['client_company'] . "<br>";
                        $content .= "<b>Sales:</b> " . $request['sales_name'] . "<br>";
                        $content .= "<b>Status:</b> Siap diproses Finance<br>";

                        $link = MailHelper::getBaseUrl() . "?page=invoice_requests_view&id=" . $id;
                        $html = MailHelper::getTemplate("Proses Invoice Baru", $content, $link);
                        MailHelper::send($finance_emails, $subject, $html);
                    }
                } catch (Exception $e) {
                    error_log("Email failed Manager -> Finance AR: " . $e->getMessage());
                }
            }
        }
        
        header('Location: index.php?page=invoice_requests_view&id=' . $id);
        exit;
    }

    public function print() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            die("Invalid Request ID");
        }
        
        $request = $this->invoiceRequest->getById($id);
        if (!$request) {
            die("Invoice Request not found.");
        }
        
        // Prepare data for view
        $grand_total = 0;
        foreach ($request['items'] as $item) {
            $grand_total += $item['price'] * $item['qty'];
        }
        
        include '../views/invoice_requests/print.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=invoice_requests_index');
            exit;
        }

        if (!$this->verifyInvoiceAccess($id, true)) {
            die("Unauthorized Access.");
        }

        $request = $this->invoiceRequest->getById($id);
        if (!$request) {
            die("Invoice Request not found.");
        }

        if ($request['status'] !== 'DRAFT') {
            $_SESSION['error'] = "Hanya Invoice Request dengan status DRAFT yang dapat diedit.";
            header('Location: index.php?page=invoice_requests_view&id=' . $id);
            exit;
        }

        // Data for Form
        // Get Sales Users for dropdown
        $isAdmin = in_array($_SESSION['role'], ['admin_sales', 'superadmin', 'sales_support_supervisor', 'sales_performance_manager']);
        $sales_users = [];
        if ($isAdmin) {
            $sales_users = $this->user->getUsersByRole('sales')->fetchAll(PDO::FETCH_ASSOC);
        }

        // Current User Info
        $currentUser = $this->user->getUserById($_SESSION['user_id']);
        
        include '../views/invoice_requests/edit.php';
    }

    public function update_action() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=invoice_requests_index');
            exit;
        }

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token. Possible CSRF attack detected!");
        }

        $id = $_POST['id'];
        $request = $this->invoiceRequest->getById($id);
        
        if (!$request || $request['status'] !== 'DRAFT') {
             $_SESSION['error'] = "Invalid Request or Status.";
             header('Location: index.php?page=invoice_requests_index');
             exit;
        }

        $data = [
            'request_number' => $_POST['request_number'],
            'request_date' => $_POST['request_date'],
            'pic_sales_id' => $_POST['pic_sales_id'],
            'client_company' => $_POST['client_company'],
            'client_pic' => $_POST['client_pic'],
            'client_phone' => $_POST['client_phone'],
            'client_email' => $_POST['client_email'],
            'invoice_terms' => $_POST['invoice_terms'],
            'shipping_address' => $_POST['shipping_address'],
            'notes' => $_POST['notes'],
            'link_gdrive_npwp' => $_POST['link_gdrive_npwp'] ?? '',
            'link_gdrive_absensi' => $_POST['link_gdrive_absensi'] ?? ''
        ];

        // Items
        $items = [];
        if (isset($_POST['item_description'])) {
            $count = count($_POST['item_description']);
            for ($i = 0; $i < $count; $i++) {
                if (!empty($_POST['item_description'][$i])) {
                    $items[] = [
                        'item_description' => $_POST['item_description'][$i],
                        'price' => empty($_POST['price'][$i]) ? 0 : str_replace('.', '', $_POST['price'][$i]),
                        'qty' => $_POST['qty'][$i],
                        'remarks' => $_POST['remarks'][$i]
                    ];
                }
            }
        }

        if (empty($items)) {
             $_SESSION['error'] = "Item tidak boleh kosong.";
             header('Location: index.php?page=invoice_requests_edit&id=' . $id);
             exit;
        }

        if ($this->invoiceRequest->update($id, $data, $items)) {
            $_SESSION['success'] = "Invoice Request updated successfully.";
            header('Location: index.php?page=invoice_requests_view&id=' . $id);
        } else {
            $_SESSION['error'] = "Failed to update Invoice Request.";
            header('Location: index.php?page=invoice_requests_edit&id=' . $id);
        }
        exit;
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = "Invalid Request ID.";
            header('Location: index.php?page=invoice_requests_index');
            exit;
        }

        // CSRF Protection
        if (!$this->validateCsrfToken($_GET['csrf_token'] ?? '')) {
            die("Invalid CSRF token.");
        }

        if (!$this->verifyInvoiceAccess($id, true)) {
            die("Unauthorized Access.");
        }

        $request = $this->invoiceRequest->getById($id);
        if (!$request || $request['status'] !== 'DRAFT') {
            $_SESSION['error'] = "Hanya Invoice Request dengan status DRAFT yang dapat dihapus.";
            header('Location: index.php?page=invoice_requests_index');
            exit;
        }

        if ($this->invoiceRequest->delete($id)) {
            $_SESSION['success'] = "Invoice Request deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete Invoice Request.";
        }
        header('Location: index.php?page=invoice_requests_index');
        exit;
    }

    public function qr_verify() {
        $id = $_GET['id'] ?? null;
        $who = strtolower($_GET['who'] ?? 'sales');
        if (!$id) {
            die("Invalid Request ID");
        }
        $request = $this->invoiceRequest->getById($id);
        if (!$request) {
            die("Invoice Request not found");
        }

        $doc_title = "Formulir Pengajuan Invoice";
        $doc_number = $request['request_number'] ?? '-';
        $company = $request['client_company'] ?? '-';

        $name = '-';
        $role = '-';
        $approved_at = null;
        $status_label = 'Belum Approved';

        if ($who === 'sales' || $who === 'creator') {
            $name = $request['sales_name'] ?? '-';
            $role = !empty($request['sales_jabatan']) ? $request['sales_jabatan'] : 'Sales PIC';
            $approved_at = $request['approved_by_sales_at'] ?? null;
        } elseif ($who === 'spv' || $who === 'supervisor') {
            $name = $request['approver_spv_name'] ?? '-';
            $role = !empty($request['approver_spv_jabatan']) ? $request['approver_spv_jabatan'] : 'Sales Support SPV';
            $approved_at = $request['approved_by_supervisor_at'] ?? null;
        } elseif ($who === 'manager' || $who === 'mgr') {
            $name = $request['approver_mgr_name'] ?? '-';
            $role = !empty($request['approver_mgr_jabatan']) ? $request['approver_mgr_jabatan'] : 'Sales Performance Manager';
            $approved_at = $request['approved_by_manager_at'] ?? null;
        }

        if (!empty($approved_at)) {
            $status_label = 'Approved';
        }

        $projects = [];
        if (!empty($request['linked_projects'])) {
            foreach ($request['linked_projects'] as $lp) {
                if (!empty($lp['nama_project'])) {
                    $projects[] = trim($lp['nama_project']);
                }
            }
        }
        if (empty($projects) && !empty($request['items'])) {
            foreach ($request['items'] as $it) {
                if (!empty($it['nama_project'])) {
                    $projects[] = trim($it['nama_project']);
                }
            }
        }
        $projects = array_values(array_unique($projects));
        $projects_text = !empty($projects) ? implode(', ', $projects) : '-';

        include '../views/invoice_requests/qr_verify.php';
        exit;
    }
}
?>
