<?php
class RabRealizationController extends BaseController {
    private $realization;
    private $rab;
    private $project;
    private $setting;
    // private $master;
    private $costCode;

    // Helper to verify RAB access
    private function verifyRabAccess($rab_id) {
        $rab = $this->rab->getById($rab_id);
        if (!$rab) {
            die("RAB not found.");
        }
        
        // Superadmin/CEO/Manager Ops/Finance can access all
        if (in_array($_SESSION['role'], ['superadmin', 'ceo', 'manager_ops', 'head_ops', 'admin_ops', 'finance'])) {
            return $rab;
        }
        
        // Korlap can only access if assigned or if it belongs to their project (logic can be refined)
        // For now, assume strict ownership check via Project
        $project = $this->project->getById($rab['project_id']);
        
        // If user is korlap, check if they are the assigned korlap?
        // Current DB structure might not link korlap directly to project in a simple way 
        // without checking assignments. 
        // For now, we rely on the fact that they can only see what's in their list (index).
        // But for direct ID access, we should at least check if the project is "visible" to them.
        
        // Check if user has access to this project
        if (!$this->project->hasAccess($_SESSION['user_id'], $_SESSION['role'], $rab['project_id'])) {
             die("Access Denied to this RAB.");
        }
        
        return $rab;
    }

    public function __construct() {
        parent::__construct();
        $this->checkRole(['manager_ops', 'head_ops', 'korlap', 'superadmin', 'admin_ops', 'ceo', 'finance']);
        
        $this->realization = $this->loadModel('RabRealization');
        $this->rab = $this->loadModel('Rab');
        $this->project = $this->loadModel('Project');
        $this->setting = $this->loadModel('SystemSetting');
        // $this->master = $this->loadModel('MasterData');
        $this->costCode = $this->loadModel('CostCode');
    }

    private function getRabItemsFromSettings($category) {
        if ($category === 'personnel') {
            $mappingStr = $this->setting->get('rab_personnel_codes');
            
            // Try parsing as JSON first (backward compatibility)
            $mapping = json_decode($mappingStr, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If not JSON, parse as key=value lines
                $mapping = [];
                $lines = explode("\n", str_replace("\r", "", $mappingStr));
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    $parts = explode('=', $line, 2);
                    if (count($parts) == 2) {
                        $mapping[trim($parts[0])] = trim($parts[1]);
                    }
                }
            }
            
            $items = [];
            foreach ($mapping as $role_key => $lookup_value) {
                // Determine expense code from lookup value
                $code = $this->costCode->getCodeByLookupValue($lookup_value) ?? '';
                
                $items[] = [
                    'category' => 'personnel',
                    'name' => $lookup_value, // Display Name
                    'role_key' => $role_key,
                    'expense_code' => $code
                ];
            }
            
            usort($items, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            return $items;
        } 
        elseif ($category === 'consumption') {
            $str = $this->setting->get('rab_items_consumption');
            $items = [];
            
            // Comma separated list
            $names = explode(',', $str);
            foreach ($names as $name) {
                $name = trim($name);
                if (empty($name)) continue;
                
                $code = $this->costCode->getCodeByLookupValue($name) ?? '';
                $items[] = [
                    'category' => 'consumption',
                    'name' => $name,
                    'expense_code' => $code
                ];
            }
            return $items;
        }
        elseif ($category === 'transport') {
            $str = $this->setting->get('rab_items_transport');
            $items = [];
            
            // Comma separated list
            $names = explode(',', $str);
            foreach ($names as $name) {
                $name = trim($name);
                if (empty($name)) continue;
                
                $code = $this->costCode->getCodeByLookupValue($name) ?? '';
                $items[] = [
                    'category' => 'transport',
                    'name' => $name,
                    'expense_code' => $code
                ];
            }
            return $items;
        }
        
        return [];
    }

    public function submit_realization() {
        if (!isset($_POST['rab_id'])) {
            header("Location: index.php?page=realization_list");
            exit;
        }

        $rab_id = $_POST['rab_id'];

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token.");
        }

        $this->verifyRabAccess($rab_id);
        
        // Update status to need_approval_realization
        if ($this->rab->updateStatus($rab_id, 'need_approval_realization')) {
            header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&msg=Realisasi berhasil diajukan untuk approval");
        } else {
            header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&err=Gagal mengajukan realisasi");
        }
        exit;
    }

    public function approve_realization() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        if ($_SESSION['role'] != 'manager_ops') {
            header("Location: index.php?page=realization_list&err=Access Denied");
            exit;
        }
        
        $rab_id = $_POST['rab_id'];

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token.");
        }

        $rab = $this->verifyRabAccess($rab_id);
        
        // Calculate Variance to determine next status
        // Logic: If Total Variance is 0 (Realized == RAB), then auto-complete.
        // Otherwise, need to upload proof (settlement).
        
        $total_rab = $rab['grand_total']; // This is the budget/advance
        $total_realization = $rab['total_realization']; // This is the actual
        
        // If total_realization is 0, it might mean it wasn't updated yet?
        // But approve_realization is called AFTER submit, so it should be updated.
        // Let's ensure we have the latest total.
        $this->rab->updateRealizationTotal($rab_id);
        $rab = $this->rab->getById($rab_id); // Reload
        $total_realization = $rab['total_realization'];
        
        $variance = $total_rab - $total_realization;
        
        // Tolerance for float comparison
        if (abs($variance) < 1) {
             $new_status = 'completed';
             $msg = "Realisasi berhasil disetujui. Status: Completed (Tidak ada selisih).";
        } else {
             $new_status = 'realization_approved';
             $msg = "Realisasi berhasil disetujui. Silakan upload bukti penyelesaian.";
        }
        
        if ($this->rab->updateStatus($rab_id, $new_status)) {
            // Update all daily realizations to approved
            $this->realization->updateStatusByRabId($rab_id, 'approved');
            
            // If completed, we should also trigger project status check
            if ($new_status == 'completed') {
                $this->project->checkAndSetReadyForInvoicing($rab['project_id']);
            }
            
            header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&msg=" . urlencode($msg));
        } else {
            header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&err=Gagal menyetujui realisasi");
        }
        exit;
    }

    public function reject_realization() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        if ($_SESSION['role'] != 'manager_ops') {
            header("Location: index.php?page=realization_list&err=Access Denied");
            exit;
        }

        $rab_id = $_POST['rab_id'];

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token.");
        }

        $this->verifyRabAccess($rab_id);
        
        if ($this->rab->updateStatus($rab_id, 'realization_rejected')) {
            // Update all daily realizations to rejected
            $this->realization->updateStatusByRabId($rab_id, 'rejected');
            header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&msg=Realisasi ditolak");
        } else {
            header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&err=Gagal menolak realisasi");
        }
        exit;
    }

    public function export_lpum() {
        if (!isset($_GET['rab_id'])) {
            die("RAB ID Required");
        }
        
        $rab_id = $_GET['rab_id'];
        $rab = $this->verifyRabAccess($rab_id);
        
        // Get Realized Items
        $realized_items = $this->realization->getRealizedItemsByRab($rab_id);

        // Get Cost Codes Map
        $all_codes = $this->costCode->readAll()->fetchAll(PDO::FETCH_ASSOC);
        $cost_code_map = [];
        foreach ($all_codes as $c) {
            $cost_code_map[strtolower(trim($c['lookup_value']))] = $c['code'];
        }
        
        // Group items for PDF
        // User Request: "yang di realisai hanya bagian AKOMODASI, jadi cek uang muka AKOMODASI dan realisasi AKOMODASI"
        // Target categories: Transport (mapped to Akomodasi) and Accommodation
        $target_cats = ['transport', 'accommodation'];
        
        $grouped_items = [];
        $total_realized = 0;
        
        foreach ($realized_items as $item) {
            $cat = strtolower($item['category']);
            $name = trim($item['item_name']);
            
            // Map Cost Code
            $item_name_key = strtolower(trim($name));
            
            // Logic to handle "Emergency: Item Name" or just "Item Name"
            $lookup_keys = [$item_name_key];
            
            // If it starts with 'emergency', add the stripped version to lookup keys
            if (stripos($item_name_key, 'emergency') === 0) {
                // Remove 'emergency'
                $stripped = trim(str_ireplace('emergency', '', $item_name_key));
                // Remove potential colon or hyphen at start
                $stripped = trim(ltrim($stripped, ':- '));
                if (!empty($stripped)) {
                    $lookup_keys[] = $stripped;
                }
            }
            
            $found_code = '-';
            foreach ($lookup_keys as $key) {
                if (isset($cost_code_map[$key])) {
                    $found_code = $cost_code_map[$key];
                    break;
                }
            }
            $item['cost_code'] = $found_code;

            // Filter: Only include target categories
            if (!in_array($cat, $target_cats)) {
                continue;
            }
            
            // Map categories if needed. 
            // "Transport" -> "TRANSPORTASI & AKOMODASI"
            
            if ($cat == 'transport' || $cat == 'accommodation') {
                $display_cat = 'TRANSPORTASI & AKOMODASI';
            } elseif ($cat == 'personnel') {
                $display_cat = 'PETUGAS MEDIS & LAPANGAN';
            } elseif ($cat == 'consumption') {
                $display_cat = 'KONSUMSI & LAINNYA';
            } else {
                $display_cat = strtoupper($cat);
            }
            
            if (!isset($grouped_items[$display_cat])) {
                $grouped_items[$display_cat] = [];
            }
            
            // Handle Emergency Cost Details
            if (stripos($name, 'Emergency') === 0) {
                 if ($cat == 'transport' || $cat == 'accommodation') {
                    // Check if it is emergency
                    if (stripos($name, 'Emergency') !== false) {
                        $grouped_items[$display_cat]['emergency'][] = $item;
                    } else {
                        $grouped_items[$display_cat]['main'][] = $item;
                    }
                } else {
                    $grouped_items[$display_cat]['items'][] = $item;
                }
            } else {
                if ($cat == 'transport' || $cat == 'accommodation') {
                     $grouped_items[$display_cat]['main'][] = $item;
                } else {
                    $grouped_items[$display_cat]['items'][] = $item;
                }
            }
            
            $total_realized += $item['total_amount'];
        }
        
        // Recalculate RAB Advance (Uang Muka) for ONLY target categories
        $rab_items = $this->rab->getItems($rab_id);
        $rab_akomodasi_total = 0;
        foreach ($rab_items as $ritem) {
            if (in_array(strtolower($ritem['category']), $target_cats)) {
                $rab_akomodasi_total += $ritem['subtotal'];
            }
        }
        
        // Override grand_total so the view displays the correct filtered advance amount
        $rab['grand_total'] = $rab_akomodasi_total;
        
        $company_address = $this->setting->get('company_address') ?? 'JL. TB SIMATUPANG NO.33 RT.01/ RW.05, RAGUNAN, PS MINGGU, JAKARTA SELATAN, DKI JAKARTA 12550';

        include '../views/realization/pdf_lpum.php';
    }

    public function upload_settlement() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;
        
        $rab_id = $_POST['rab_id'];

        // CSRF Protection
        if (!$this->validateCsrfToken()) {
            die("Invalid CSRF token.");
        }

        $this->verifyRabAccess($rab_id);
        
        // Check file
        if (!isset($_FILES['transfer_proof']) || $_FILES['transfer_proof']['error'] != 0) {
            header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&err=File bukti wajib diupload");
            exit;
        }
        
        $file = $_FILES['transfer_proof'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (!in_array($ext, $allowed)) {
            header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&err=Format file tidak valid (jpg, png, pdf)");
            exit;
        }
        
        // Upload
        $upload_dir = '../uploads/settlements/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = 'SETTLEMENT_' . $rab_id . '_' . time() . '.' . $ext;
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Update DB
            // We need to update RAB status to 'completed' and save file path
            // Using transfer_proof_path as the common column for proofs
            
            $query = "UPDATE rabs SET settlement_proof_path = :proof, status = 'completed' WHERE id = :id";
            $db = (new Database())->getConnection();
            $stmt = $db->prepare($query);
            $stmt->bindValue(':proof', 'uploads/settlements/' . $filename);
            $stmt->bindParam(':id', $rab_id);
            
            if ($stmt->execute()) {
                 // Log Action
                 $rab_info = $this->rab->getById($rab_id);
                 $this->project->logAction(
                    $rab_info['project_id'], 
                    'Bukti Penyelesaian Diupload', 
                    $_SESSION['user_id'], 
                    "RAB Number: " . ($rab_info['rab_number'] ?? 'Unknown') . " - Status: Completed. Proof: " . $filename
                 );

                 // Trigger Project Status Update
                 $this->project->checkAndSetReadyForInvoicing($rab_info['project_id']);

                 header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&msg=Bukti penyelesaian berhasil diupload. Status Completed.");
            } else {
                 header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&err=Gagal update database");
            }
        } else {
            header("Location: index.php?page=realization_comparison&rab_id=" . $rab_id . "&err=Gagal upload file");
        }
        exit;
    }

    public function index() {
        $page_title = "Realisasi Harian";
        
        // List approved RABs for selection or existing realizations
        // For now, let's list approved RABs that can be realized
        // And also show history of realizations
        
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        
        // Get RABs available for realization (Approved / Advance Paid)
        $filters = ['status' => 'approved']; // Or advance_paid, depending on workflow. Let's assume approved is enough to start.
        // Actually user said "Pilih Project / RAB" then "Pilih tanggal MCU yang belum direalisasi"
        
        // We can reuse getFilteredRabs but we might want to filter only approved ones.
        // Let's get all approved RABs first.
        // Ideally we should have a custom method to get "Active RABs for Realization"
        
        $stmt = $this->rab->getFilteredRabs($role, $user_id, ['status' => 'all']); // We'll filter in PHP or improve query later
        $all_rabs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $candidate_rabs = [];
        foreach ($all_rabs as $r) {
            if ($role == 'manager_ops') {
                if ($r['status'] == 'need_approval_realization') {
                    $candidate_rabs[] = $r;
                }
            } else {
                // For others, show only advance_paid RABs + those in realization workflow
                // Exclude 'approved' - only show after advance is paid
                if (in_array($r['status'], ['advance_paid', 'submitted_to_finance', 'need_approval_realization'])) {
                    $candidate_rabs[] = $r;
                }
            }
        }

        // Filter candidates: Must have unrealized dates to be selectable for input
        // Unless we want to allow viewing them? But the UI says "Pilih RAB untuk Realisasi" -> Input
        // So we should only show those that CAN be input.
        $candidate_ids = array_column($candidate_rabs, 'id');
        $realized_map = $this->realization->getRealizedDatesByRabIds($candidate_ids);
        
        $active_rabs = [];
        foreach ($candidate_rabs as $r) {
            // For manager_ops approving, we don't check available dates, we check status (handled above)
            // But actually manager_ops uses this list to approve? No, index usually shows "Input" button.
            // If manager_ops needs to approve, they likely go to a different view or this view handles it?
            // Line 55 in view: <a href="...&rab_id=...">Input</a>
            // So this is for CREATING realization.
            // Manager Ops might need to create too? Or just approve?
            // If Manager Ops is just approving, they shouldn't be "Inputting".
            // But let's stick to the user request: "Pilih Project / RAB" -> "Pilih tanggal MCU yang belum direalisasi"
            // This implies the CREATION flow.
            
            $selected_dates = [];
            if (!empty($r['selected_dates'])) {
                $decoded = json_decode($r['selected_dates'], true);
                if (is_array($decoded)) {
                    $selected_dates = $decoded;
                }
            }
            
            $realized_dates = $realized_map[$r['id']] ?? [];
            
            // Check diff
            $available = array_diff($selected_dates, $realized_dates);
            if (!empty($available)) {
                $active_rabs[] = $r;
            }
        }

        // Also get recent realizations for history
            // Pagination
            $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;

            $filters = [
                'start_date' => $_GET['start_date'] ?? '',
                'end_date' => $_GET['end_date'] ?? ''
            ];

            $stmtHistory = $this->realization->getAll(null, $role, $user_id, $limit, $offset, $filters);
            $history = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
            
            $total_history_rows = $this->realization->countAll(null, $role, $user_id, $filters);
            $total_history_pages = ceil($total_history_rows / $limit);

            // Get Correct Totals for displayed RABs
            $rab_ids = array_unique(array_column($history, 'rab_id'));
            $true_totals = [];
            if (!empty($rab_ids)) {
                $true_totals = $this->realization->getRealizationTotalsByRabIds($rab_ids);
            }

            // Get Category Totals for displayed Realizations
            $realization_ids = array_column($history, 'id');
            $category_totals_map = [];
            if (!empty($realization_ids)) {
                $category_totals_map = $this->realization->getCategoryTotalsByRealizationIds($realization_ids);
            }

            // Group history by RAB for Parent-Child View
            $grouped_history = [];
            foreach ($history as $h) {
                $rab_id = $h['rab_id'];
                if (!isset($grouped_history[$rab_id])) {
                    $grouped_history[$rab_id] = [
                        'rab_id' => $rab_id,
                        'rab_number' => $h['rab_number'] ?? 'Unknown RAB',
                        'project_name' => $h['nama_project'],
                        'korlap_name' => $h['korlap_name'] ?? '-',
                        'rab_grand_total' => $h['rab_grand_total'] ?? 0,
                        'rab_status' => $h['rab_status'] ?? 'unknown',
                        'total_realized_sum' => $true_totals[$rab_id] ?? 0, // Use True Total
                        'items' => []
                    ];
                }
                
                // Inject category totals
                $h['category_totals'] = $category_totals_map[$h['id']] ?? [];
                
                $grouped_history[$rab_id]['items'][] = $h;
                // Note: We don't sum here anymore for the parent total, we use true_totals
            }

            $this->view('realization/index', [
                'active_rabs' => $active_rabs,
                'grouped_history' => $grouped_history,
                'page_title' => $page_title,
                'page' => $page,
                'total_pages' => $total_history_pages,
                'total_rows' => $total_history_rows
            ]);
        }

    public function export_csv() {
        $this->checkRole(['finance', 'superadmin']);
        
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        // Fetch all realizations with filters
        $stmt = $this->realization->getAll(null, $_SESSION['role'], $_SESSION['user_id'], null, null, $filters);
        $realizations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="export_realisasi_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Tanggal Realisasi', 'No RAB', 'Project', 'Kategori', 'Item', 'Qty', 'Amount', 'Total', 'Status']);

        foreach ($realizations as $r) {
            // Fetch items for each realization
            $items = $this->realization->getItems($r['id']);
            
            if (empty($items)) {
                // Export header only if no items (shouldn't happen usually)
                fputcsv($output, [
                    $r['date'],
                    $r['rab_number'] ?? '-',
                    $r['nama_project'] ?? '-',
                    '-',
                    '-',
                    0,
                    0,
                    $r['total_amount'],
                    strtoupper($r['status'])
                ]);
            } else {
                foreach ($items as $item) {
                    fputcsv($output, [
                        $r['date'],
                        $r['rab_number'] ?? '-',
                        $r['nama_project'] ?? '-',
                        strtoupper($item['category']),
                        $item['item_name'],
                        $item['qty'],
                        $item['price'],
                        $item['subtotal'],
                        strtoupper($r['status'])
                    ]);
                }
            }
        }

        fclose($output);
        exit;
    }

    public function comparison() {
        if (!isset($_GET['rab_id'])) {
            header("Location: index.php?page=realization_list");
            exit;
        }

        $rab_id = $_GET['rab_id'];
        $rab = $this->verifyRabAccess($rab_id);

        // 1. Get RAB Items (Proposed)
        $rab_items = $this->rab->getItems($rab_id);
        
        // 2. Get Realized Items (Realized All Day)
        $realized_items = $this->realization->getRealizedItemsByRab($rab_id);

        // 3. Process and Group Comparison Data
        $comparison_data = [];
        $categories = ['personnel', 'vendor', 'transport', 'consumption', 'accommodation', 'other'];
        
        // Helper to normalize category keys
        $normalizeCat = function($cat) {
            return strtolower(trim($cat));
        };

        // Populate with RAB items
        foreach ($rab_items as $item) {
            $cat = $normalizeCat($item['category']);
            $name = trim($item['item_name']);
            $key = $cat . '|' . $name;
            
            if (!isset($comparison_data[$cat])) {
                $comparison_data[$cat] = [];
            }
            
            if (!isset($comparison_data[$cat][$name])) {
                $comparison_data[$cat][$name] = [
                    'item_name' => $name,
                    'rab_qty' => 0,
                    'rab_total' => 0,
                    'realized_qty' => 0,
                    'realized_total' => 0,
                    'realized_details' => [] // Store individual realized items
                ];
            }
            
            $comparison_data[$cat][$name]['rab_qty'] += $item['qty'];
            $comparison_data[$cat][$name]['rab_total'] += $item['subtotal'];
        }

        // Populate/Merge with Realized items
        foreach ($realized_items as $item) {
            $cat = $normalizeCat($item['category']);
            $name = trim($item['item_name']);
            
            // Special handling for Emergency items: merge "EMERGENCY: ..." into "Emergency Cost"
            if (stripos($name, 'EMERGENCY:') === 0) {
                $target_name = 'Emergency Cost';
            } else {
                $target_name = $name;
            }

            // Use target_name if it exists in RAB (comparison_data), otherwise use target_name (new item)
            // This ensures all "EMERGENCY: ..." items are grouped under "Emergency Cost"
            $name_key = $target_name;
            
            if (!isset($comparison_data[$cat])) {
                $comparison_data[$cat] = [];
            }
            
            if (!isset($comparison_data[$cat][$name_key])) {
                $comparison_data[$cat][$name_key] = [
                    'item_name' => $name_key, // Use the grouped name
                    'rab_qty' => 0,
                    'rab_total' => 0,
                    'realized_qty' => 0,
                    'realized_total' => 0,
                    'realized_details' => []
                ];
            }
            
            $comparison_data[$cat][$name_key]['realized_qty'] += $item['total_qty'];
            $comparison_data[$cat][$name_key]['realized_total'] += $item['total_amount'];
            
            // Add detail to realized_details if it's a merged item or if we just want to show breakdown
            // For now, always add to details to allow drill-down
            $comparison_data[$cat][$name_key]['realized_details'][] = [
                'item_name' => $name, // Original name (e.g., EMERGENCY: Tambal Ban)
                'qty' => $item['total_qty'],
                'total' => $item['total_amount'],
                'notes' => $item['notes'] ?? ''
            ];
        }

        // Calculate Totals per Category
        $category_summary = [];
        foreach ($comparison_data as $cat => $items) {
            $category_summary[$cat] = [
                'rab_total' => 0,
                'realized_total' => 0,
                'variance' => 0
            ];
            foreach ($items as $i) {
                $category_summary[$cat]['rab_total'] += $i['rab_total'];
                $category_summary[$cat]['realized_total'] += $i['realized_total'];
            }
            $category_summary[$cat]['variance'] = $category_summary[$cat]['rab_total'] - $category_summary[$cat]['realized_total'];
        }

        // Accommodation Reconciliation Data
        // Fix: Use RAB Subtotal for Transport + Accommodation as the "Advance" amount
        // This assumes 100% of the budget was released as advance.
        $accommodation_advance_total = 0;
        if (isset($category_summary['accommodation'])) $accommodation_advance_total += $category_summary['accommodation']['rab_total'];
        if (isset($category_summary['transport'])) $accommodation_advance_total += $category_summary['transport']['rab_total'];
        
        // Calculate Realized Total for Accommodation Context (Transport + Accommodation)
        $acc_realized_total = 0;
        if (isset($category_summary['accommodation'])) $acc_realized_total += $category_summary['accommodation']['realized_total'];
        if (isset($category_summary['transport'])) $acc_realized_total += $category_summary['transport']['realized_total'];

        $this->view('realization/comparison', [
            'rab' => $rab,
            'comparison_data' => $comparison_data,
            'category_summary' => $category_summary,
            'accommodation_advance_total' => $accommodation_advance_total,
            'accommodation_realized_total' => $acc_realized_total
        ]);
    }

    public function create() {
        if (!isset($_GET['rab_id'])) {
            header("Location: index.php?page=realization_list");
            exit;
        }

        $rab_id = $_GET['rab_id'];
        $rab = $this->verifyRabAccess($rab_id);
        
        // Get RAB Items as template
        $rab_items = $this->rab->getItems($rab_id);
        
        // Parse dates to find unrealized dates
        $dates = [];
        if (!empty($rab['selected_dates'])) {
            $dates = json_decode($rab['selected_dates'], true);
        }
        
        // Filter out realized dates
        $available_dates = [];
        foreach ($dates as $date) {
            if (!$this->realization->isDateRealized($rab_id, $date)) {
                $available_dates[] = $date;
            }
        }

        // Get System Settings for Doctor Rule
        $doctor_max_patient = $this->setting->get('doctor_max_patient') ?? 50;
        $doctor_extra_fee = $this->setting->get('doctor_extra_fee') ?? 15000;
        
        // Get All Fee Settings for Dynamic Pricing
        $stmtSettings = $this->setting->getAll();
        $all_settings = $stmtSettings->fetchAll(PDO::FETCH_ASSOC);
        $fee_settings = [];
        foreach ($all_settings as $s) {
            $fee_settings[$s['setting_key']] = $s['setting_value'];
        }

        // Get Master Data
        $emergency_types = $this->costCode->getEmergencyTypes();
        $master_personnel = $this->getRabItemsFromSettings('personnel');
        $master_consumption = $this->getRabItemsFromSettings('consumption');
        $master_transport = $this->getRabItemsFromSettings('transport');

        // Get Existing BAs
        $existing_bas = $this->project->getBeritaAcara($rab['project_id']);

        // Group RAB Items
        $grouped_items = [
            'personnel' => [],
            'vendor' => [],
            'transport' => [],
            'consumption' => []
        ];

        foreach ($rab_items as $item) {
            $cat = $item['category'];
            if (isset($grouped_items[$cat])) {
                $grouped_items[$cat][] = $item;
            } else {
                // Fallback for unknown categories or just append
                $grouped_items['other'][] = $item;
            }
        }

        // Calculate RAB Accommodation Budget for Auto-fill
        $rab_accommodation_budget = 0;
        foreach ($rab_items as $item) {
            if ($item['category'] == 'accommodation') {
                $rab_accommodation_budget += $item['subtotal'];
            }
        }

        // Get Previous Realization Total
        $previous_realization_total = $this->realization->getRealizedTotalByRab($rab_id);

        $this->view('realization/create', [
            'rab' => $rab,
            'grouped_items' => $grouped_items,
            'available_dates' => $available_dates,
            'doctor_max_patient' => $doctor_max_patient,
            'doctor_extra_fee' => $doctor_extra_fee,
            'fee_settings' => $fee_settings,
            'emergency_types' => $emergency_types,
            'master_personnel' => $master_personnel,
            'master_consumption' => $master_consumption,
            'master_transport' => $master_transport,
            'existing_bas' => $existing_bas,
            'rab_accommodation_budget' => $rab_accommodation_budget,
            'previous_realization_total' => $previous_realization_total
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;

        try {
            $rab_id = $_POST['rab_id'];

            // CSRF Protection
            if (!$this->validateCsrfToken()) {
                die("Invalid CSRF token.");
            }

            $project_id = $_POST['project_id'];
            $date = $_POST['date'];
            $actual_participants = $_POST['actual_participants'] ?? 0;
            
            // Auto-calculate Accommodation Advance from RAB (Transport + Accommodation)
            // User requested: "harusnya kan dari rab bagian TRANSPORTASI & AKOMODASI"
            $rab_items = $this->rab->getItems($rab_id);
            $accommodation_advance = 0;
            foreach ($rab_items as $item) {
                if ($item['category'] == 'transport' || $item['category'] == 'accommodation') {
                    $accommodation_advance += $item['subtotal'];
                }
            }

            // Verify Access
            $this->verifyRabAccess($rab_id);
            
            // Check if date already realized (double check)
            if ($this->realization->isDateRealized($rab_id, $date)) {
                throw new Exception("Date $date already realized for this RAB.");
            }

            // Prepare Header Data
            $doctor_max_patient = $this->setting->get('doctor_max_patient') ?? 50;
            $doctor_extra_fee = $this->setting->get('doctor_extra_fee') ?? 15000;
            
            // Calculate Doctor Extra Fee
            // Assuming we need to know how many doctors? Or is it per doctor?
            // "Hitung otomatis tambahan biaya jika peserta > kapasitas dokter"
            // Usually this means Total Participants vs (Total Doctors * Capacity)
            // But we don't have "Total Doctors" input yet. We need to check if we can get it from RAB items or user input.
            // Let's assume we need to count "Doctor" items in the realized items.
            
            // Collect Items
            $items = [];
            $grand_total = 0;
            $doctor_count = 0;
            
            // Process submitted items
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $i) {
                    $qty = str_replace('.', '', $i['qty']);
                    $price = str_replace('.', '', $i['price']);
                    $subtotal = $qty * $price;
                    
                    $items[] = [
                        'category' => $i['category'],
                        'item_name' => $i['item_name'],
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'notes' => $i['notes'],
                        'is_extra_item' => 0
                    ];
                    
                    $grand_total += $subtotal;

                    // Naive check for doctor count based on item name or category
                    // Ideally we should have a 'role' or 'type' column, but for now let's look for "Dokter" in item name
                    if (stripos($i['item_name'], 'Dokter') !== false) {
                        $doctor_count += $qty;
                    }
                }
            }

            // Process Extra Items
             if (isset($_POST['extra_items']) && is_array($_POST['extra_items'])) {
                foreach ($_POST['extra_items'] as $i) {
                    // Skip empty rows
                    if (empty($i['item_name'])) continue;

                    $qty = str_replace('.', '', $i['qty']);
                    $price = str_replace('.', '', $i['price']);
                    $subtotal = $qty * $price;
                    
                    $items[] = [
                        'category' => $i['category'],
                        'item_name' => $i['item_name'],
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'notes' => $i['notes'],
                        'is_extra_item' => 1
                    ];
                    
                    $grand_total += $subtotal;
                    
                    if (stripos($i['item_name'], 'Dokter') !== false) {
                        $doctor_count += $qty;
                    }
                }
            }

            // Calculate Doctor Overload Fee
            $doctor_total_fee = 0;
            
            // Determine participants count for doctor rule (Prioritize specific input)
            $participants_for_rule = $actual_participants;
            if (isset($_POST['doctor_participants']) && $_POST['doctor_participants'] !== '') {
                if (!is_numeric($_POST['doctor_participants'])) {
                    throw new Exception("Jumlah peserta dokter harus berupa angka valid.");
                }
                $participants_for_rule = floatval($_POST['doctor_participants']);
            }

            if ($doctor_count > 0) {
                $total_capacity = $doctor_count * $doctor_max_patient;
                if ($participants_for_rule > $total_capacity) {
                    $excess_patients = $participants_for_rule - $total_capacity;
                    $doctor_total_fee = $excess_patients * $doctor_extra_fee;
                    
                    // Add this as an item? Or just store in header?
                    // User requirement: "Hitung otomatis tambahan biaya"
                    // Usually this is added as a cost item so it sums up to Total Realization.
                    $items[] = [
                        'category' => 'personnel',
                        'item_name' => 'Overload Fee Dokter (' . $excess_patients . ' pax)',
                        'qty' => 1,
                        'price' => $doctor_total_fee,
                        'subtotal' => $doctor_total_fee,
                        'notes' => "Auto-calculated: $participants_for_rule pax > $total_capacity cap ($doctor_count dr x $doctor_max_patient)",
                        'is_extra_item' => 1
                    ];
                    $grand_total += $doctor_total_fee;
                }
            }

            // Handle Status and BA Upload
            $status = (isset($_POST['save_as']) && $_POST['save_as'] == 'draft') ? 'draft' : 'submitted';
            
            // Handle BA Upload
            $ba_filename = $this->handleBaUpload($project_id, $date);

            if ($ba_filename) {
                // Save to project_berita_acara
                $this->project->uploadBeritaAcara($project_id, $date, $ba_filename, $_SESSION['user_id']);
            }

            // Check existing BA in project_berita_acara
            $existing_bas = $this->project->getBeritaAcara($project_id);
            $ba_exists = isset($existing_bas[$date]) && !empty($existing_bas[$date]['file_path']) && $existing_bas[$date]['status'] == 'uploaded';

            if ($status == 'submitted') {
                if (!$ba_filename && !$ba_exists) {
                     throw new Exception("BA File is required for submission. Please upload BA or save as Draft.");
                }
            }
            
            $headerData = [
                'rab_id' => $rab_id,
                'project_id' => $project_id,
                'date' => $date,
                'total_amount' => $grand_total,
                'actual_participants' => $actual_participants,
                'doctor_participants' => $participants_for_rule,
                'doctor_fee_per_patient' => $doctor_extra_fee,
                'doctor_total_fee' => $doctor_total_fee,
                'accommodation_advance' => $accommodation_advance,
                'notes' => $_POST['notes'] ?? '',
                'created_by' => $_SESSION['user_id'],
                'status' => $status
            ];

            if ($this->realization->create($headerData, $items)) {
                // Update RAB Total Realization
                $this->rab->updateRealizationTotal($rab_id);

                // Log Action
                $this->project->logAction(
                    $project_id, 
                    'Realization Created', 
                    $_SESSION['user_id'], 
                    "Date: $date. Total: " . number_format($grand_total)
                );

                // Trigger Project Status Update (Ready for Invoicing Check)
                $this->project->checkAndSetReadyForInvoicing($project_id);
                
                header("Location: index.php?page=realization_list&msg=Realisasi berhasil disimpan");
            } else {
                throw new Exception("Failed to save realization.");
            }

        } catch (Exception $e) {
            // Handle error (redirect back with error)
            echo "Error: " . $e->getMessage();
        }
    }
    public function edit() {
        if (!isset($_GET['id'])) {
            header("Location: index.php?page=realization_list");
            exit;
        }

        $id = $_GET['id'];
        $realization = $this->realization->getById($id);
        
        if (!$realization) {
            echo "Realization not found.";
            return;
        }

        // Permission Check
        $this->verifyRabAccess($realization['rab_id']);
        
        // ALLOW EDIT for Manager Ops even if approved
        // This addresses user request: "manager ga bisa edit realisasi ya"
        // And also previous issue where manager couldn't edit if status != submitted
        // We will allow edit if role is manager_ops OR status is submitted/rejected/draft
        
        $can_edit = false;
        if ($_SESSION['role'] == 'manager_ops' || $_SESSION['role'] == 'superadmin') {
            $can_edit = true;
        } elseif (in_array($realization['status'], ['submitted', 'draft', 'rejected'])) {
            $can_edit = true;
        }
        
        if (!$can_edit) {
            die("Editing is locked for this realization status (" . $realization['status'] . "). Contact Manager Ops.");
        }
        
        $items = $this->realization->getItems($id);
        
        // Group Items
        $grouped_items = [
            'personnel' => [],
            'vendor' => [],
            'transport' => [],
            'consumption' => [],
            'accommodation' => [],
            'other' => [],
            'extra' => [] // For extra items added during realization
        ];

        foreach ($items as $item) {
            $cat = $item['category'];
            if (isset($grouped_items[$cat])) {
                $grouped_items[$cat][] = $item;
            } else {
                $grouped_items['extra'][] = $item;
            }
        }

        // Get System Settings
        $doctor_max_patient = $this->setting->get('doctor_max_patient') ?? 50;
        $doctor_extra_fee = $this->setting->get('doctor_extra_fee') ?? 15000;
        
        // Get Master Data
        $emergency_types = $this->costCode->getEmergencyTypes();
        $master_personnel = $this->getRabItemsFromSettings('personnel');
        $master_consumption = $this->getRabItemsFromSettings('consumption');
        $master_transport = $this->getRabItemsFromSettings('transport');

        // Fetch BA Status from project_berita_acara
        $project_bas = $this->project->getBeritaAcara($realization['project_id']);
        $current_ba = isset($project_bas[$realization['date']]) ? $project_bas[$realization['date']] : null;
        
        // Inject into realization array for view compatibility
        if ($current_ba && !empty($current_ba['file_path'])) {
            $realization['ba_file_url'] = 'uploads/ba/' . $current_ba['file_path']; // Public path
            $realization['ba_status'] = $current_ba['status'];
        } else {
            $realization['ba_file_url'] = null;
            $realization['ba_status'] = null;
        }

        $this->view('realization/edit', [
            'realization' => $realization,
            'grouped_items' => $grouped_items,
            'doctor_max_patient' => $doctor_max_patient,
            'doctor_extra_fee' => $doctor_extra_fee,
            'emergency_types' => $emergency_types,
            'master_personnel' => $master_personnel,
            'master_consumption' => $master_consumption,
            'master_transport' => $master_transport
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return;

        try {
            $id = $_POST['id'];
            $realization = $this->realization->getById($id);
            
            if (!$realization) {
                throw new Exception("Realization not found.");
            }

            // Permission Check
            $this->verifyRabAccess($realization['rab_id']);

            // Prepare Header Data
            $doctor_max_patient = $this->setting->get('doctor_max_patient') ?? 50;
            $doctor_extra_fee = $this->setting->get('doctor_extra_fee') ?? 15000;
            
            $items = [];
            $grand_total = 0;
            $doctor_count = 0;
            
            // Process submitted items (Normal items)
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $i) {
                    $qty = str_replace('.', '', $i['qty']);
                    $price = str_replace('.', '', $i['price']);
                    $subtotal = $qty * $price;
                    
                    $items[] = [
                        'category' => $i['category'],
                        'item_name' => $i['item_name'],
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'notes' => $i['notes'],
                        'is_extra_item' => 0
                    ];
                    
                    $grand_total += $subtotal;
                    
                    if (stripos($i['item_name'], 'Dokter') !== false) {
                        $doctor_count += $qty;
                    }
                }
            }

            // Process Extra Items
             if (isset($_POST['extra_items']) && is_array($_POST['extra_items'])) {
                foreach ($_POST['extra_items'] as $i) {
                    if (empty($i['item_name'])) continue;

                    $qty = str_replace('.', '', $i['qty']);
                    $price = str_replace('.', '', $i['price']);
                    $subtotal = $qty * $price;
                    
                    $items[] = [
                        'category' => $i['category'],
                        'item_name' => $i['item_name'],
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'notes' => $i['notes'],
                        'is_extra_item' => 1
                    ];
                    
                    $grand_total += $subtotal;
                    
                    if (stripos($i['item_name'], 'Dokter') !== false) {
                        $doctor_count += $qty;
                    }
                }
            }

            // Calculate Doctor Overload
            $actual_participants = $_POST['actual_participants'] ?? 0;
            $accommodation_advance = isset($_POST['accommodation_advance']) ? str_replace('.', '', $_POST['accommodation_advance']) : 0;
            
            // Use user input for doctor_participants if provided, otherwise default to actual
            $participants_for_rule = $actual_participants;
            if (isset($_POST['doctor_participants']) && $_POST['doctor_participants'] !== '') {
                if (!is_numeric($_POST['doctor_participants'])) {
                    throw new Exception("Jumlah peserta dokter harus berupa angka valid.");
                }
                $participants_for_rule = floatval($_POST['doctor_participants']);
            }
            
            $doctor_total_fee = 0;
            $doctor_capacity = $doctor_count * $doctor_max_patient;
            
            if ($doctor_count > 0 && $participants_for_rule > $doctor_capacity) {
                $overload = $participants_for_rule - $doctor_capacity;
                $doctor_total_fee = $overload * $doctor_extra_fee;
                
                // Add Overload Item
                $items[] = [
                    'category' => 'personnel', // Or separate category
                    'item_name' => 'Fee Dokter (Overload)',
                    'qty' => 1,
                    'price' => $doctor_total_fee,
                    'subtotal' => $doctor_total_fee,
                    'notes' => "Overload: $overload pax (Total: $participants_for_rule, Cap: $doctor_capacity)",
                    'is_extra_item' => 1
                ];
                $grand_total += $doctor_total_fee;
            }

            // Handle Status and BA Upload
            $status = (isset($_POST['save_as']) && $_POST['save_as'] == 'draft') ? 'draft' : 'submitted';
            
            // Handle BA Upload to project_berita_acara
            $ba_filename = $this->handleBaUpload($realization['project_id'], $realization['date']);
            
            // Check requirement if submitting
            $existing_bas = $this->project->getBeritaAcara($realization['project_id']);
            $ba_exists = isset($existing_bas[$realization['date']]) && !empty($existing_bas[$realization['date']]['file_path']) && $existing_bas[$realization['date']]['status'] == 'uploaded';

            if ($status == 'submitted') {
                if (!$ba_filename && !$ba_exists) {
                    throw new Exception("BA File is required for submission. Please upload BA or save as Draft.");
                }
            }
            
            // Determine path for rab_realizations table (Legacy/Cache)
            $ba_file_path_for_realization = null;

            if ($ba_filename) {
                // New file uploaded
                $this->project->uploadBeritaAcara($realization['project_id'], $realization['date'], $ba_filename, $_SESSION['user_id']);
                $ba_file_path_for_realization = 'uploads/ba/' . $ba_filename;
            } elseif ($ba_exists) {
                // Use existing file from project_berita_acara
                $ba_file_path_for_realization = 'uploads/ba/' . $existing_bas[$realization['date']]['file_path'];
            }

            $updateData = [
                'actual_participants' => $actual_participants,
                'doctor_participants' => $participants_for_rule,
                'doctor_total_fee' => $doctor_total_fee,
                'accommodation_advance' => $accommodation_advance,
                'total_amount' => $grand_total,
                'notes' => $_POST['notes'] ?? '',
                'status' => $status
            ];

            if ($this->realization->update($id, $updateData, $items)) {
                // Update RAB Total Realization
                $this->rab->updateRealizationTotal($realization['rab_id']);

                // Log Action
                $this->project->logAction(
                    $realization['project_id'], 
                    'Realization Updated', 
                    $_SESSION['user_id'], 
                    "Date: {$realization['date']}. Total: " . number_format($grand_total)
                );
                
                header("Location: index.php?page=realization_list&msg=Realisasi berhasil diupdate");
            } else {
                throw new Exception("Failed to update realization.");
            }

        } catch (Exception $e) {
            header("Location: index.php?page=realization_edit&id=$id&error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    private function handleBaUpload($rab_id, $date) {
        if (!isset($_FILES['ba_file'])) {
            return null;
        }

        // Use standard project BA directory
        $uploadDir = '../public/uploads/ba/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

        // Check if multiple files (array)
        if (is_array($_FILES['ba_file']['name'])) {
            // Multiple files logic
            $fileCount = count($_FILES['ba_file']['name']);
            $hasFile = false;
            
            // Check if at least one file is valid
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['ba_file']['error'][$i] == UPLOAD_ERR_OK) {
                    $hasFile = true;
                    break;
                }
            }

            if (!$hasFile) return null;

            // If only 1 file is uploaded via multiple input, treating it as single file might be better for preview?
            // But consistency matters. Let's ZIP if > 1, or if array structure.
            // Actually, if I zip 1 file, it's annoying to preview.
            // Let's check count of valid files.
            
            $validFiles = [];
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['ba_file']['error'][$i] == UPLOAD_ERR_OK) {
                    $validFiles[] = $i;
                }
            }

            if (count($validFiles) === 0) return null;

            if (count($validFiles) === 1) {
                // Handle as single file
                $i = $validFiles[0];
                $fileInfo = pathinfo($_FILES['ba_file']['name'][$i]);
                $ext = strtolower($fileInfo['extension']);
                
                if (!in_array($ext, $allowed)) {
                    throw new Exception("Invalid file type. Only PDF, JPG, PNG allowed.");
                }

                $filename = "BA_{$rab_id}_" . str_replace('-', '', $date) . "_" . time() . ".{$ext}";
                $targetPath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['ba_file']['tmp_name'][$i], $targetPath)) {
                    return $filename;
                }
            } else {
                // Zip multiple files
                $zip = new ZipArchive();
                $zipFilename = "BA_{$rab_id}_" . str_replace('-', '', $date) . "_" . time() . ".zip";
                $zipPath = $uploadDir . $zipFilename;

                if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                    throw new Exception("Could not create ZIP file.");
                }

                foreach ($validFiles as $i) {
                    $fileInfo = pathinfo($_FILES['ba_file']['name'][$i]);
                    $ext = strtolower($fileInfo['extension']);
                    
                    if (in_array($ext, $allowed)) {
                        $zip->addFile($_FILES['ba_file']['tmp_name'][$i], $_FILES['ba_file']['name'][$i]);
                    }
                }
                $zip->close();
                return $zipFilename;
            }

        } else {
            // Single file input logic (legacy support)
            if ($_FILES['ba_file']['error'] != UPLOAD_ERR_OK) {
                return null;
            }

            $fileInfo = pathinfo($_FILES['ba_file']['name']);
            $ext = strtolower($fileInfo['extension']);
            
            if (!in_array($ext, $allowed)) {
                throw new Exception("Invalid file type. Only PDF, JPG, PNG allowed.");
            }

            $filename = "BA_{$rab_id}_" . str_replace('-', '', $date) . "_" . time() . ".{$ext}";
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['ba_file']['tmp_name'], $targetPath)) {
                return $filename;
            }
        }
        
        throw new Exception("Failed to upload BA file.");
    }

    public function qr_verify() {
        $rab_id = $_GET['rab_id'] ?? null;
        $who = strtolower($_GET['who'] ?? 'manager');
        if (!$rab_id) {
            die("Invalid RAB ID");
        }
        $rab = $this->rab->getById($rab_id);
        if (!$rab) {
            die("RAB not found");
        }
        $project = $this->project->getById($rab['project_id']);

        $doc_title = "LPUM (Laporan Pertanggungjawaban Uang Muka)";
        $doc_number = $rab['rab_number'] ?? '-';
        $company = $project['company_name'] ?? '-';
        $project_name = $project['nama_project'] ?? '-';

        $name = '-';
        $role = '-';
        $approved_at = null;
        $status_label = 'Belum Approved';

        if ($who === 'creator') {
            $name = $rab['creator_name'] ?? '-';
            $role = !empty($rab['creator_jabatan']) ? $rab['creator_jabatan'] : 'Operation Support';
            $approved_at = !empty($rab['approved_date_manager']) ? $rab['approved_date_manager'] : ($rab['created_at'] ?? null);
            $status_label = 'Dibuat';
        } elseif ($who === 'manager') {
            $name = $rab['manager_name'] ?? '-';
            $role = !empty($rab['manager_jabatan']) ? $rab['manager_jabatan'] : 'Manager Operations';
            $approved_at = $rab['approved_date_manager'] ?? ($rab['updated_at'] ?? null);
        }

        if (in_array($rab['status'], ['realization_approved', 'completed'])) {
            $status_label = $rab['status'] === 'completed' ? 'Completed' : 'Approved';
        }

        include '../views/realization/qr_verify.php';
        exit;
    }
}
?>
