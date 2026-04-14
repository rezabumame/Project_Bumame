<?php
class ProductivityOpsController {
    private $db;
    private $project;
    private $rab;
    private $realization;
    private $medicalResult;
    private $user;
    private $salesPerson;
    private $nationalHoliday;

    private $systemSetting;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Access Control
        $allowed_roles = ['superadmin', 'manager_ops', 'head_ops', 'admin_ops', 'spv_ops', 'korlap'];
        
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, $allowed_roles)) {
             header("Location: index.php?page=unauthorized");
             exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->project = new Project($this->db);
        $this->rab = new Rab($this->db);
        $this->realization = new RabRealization($this->db);
        $this->medicalResult = new MedicalResult($this->db);
        $this->user = new User($this->db);
        $this->salesPerson = new SalesPerson($this->db);
        require_once '../models/SystemSetting.php';
        $this->systemSetting = new SystemSetting($this->db);
        
        require_once '../helpers/DateHelper.php';
        require_once '../models/NationalHoliday.php';
        $this->nationalHoliday = new NationalHoliday($this->db);
    }

    public function index() {
        // 1. Get Filters
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        $filter_project_id = $_GET['project_id'] ?? '';
        $filter_sales_id = $_GET['sales_id'] ?? ''; // sales_persons.id
        $filter_korlap_id = $_GET['korlap_id'] ?? ''; // users.user_id
        $filter_kohas_id = $_GET['kohas_id'] ?? ''; // users.user_id
        if ($this->isKorlapScope()) {
            $filter_korlap_id = $_SESSION['user_id'] ?? '';
        }

        // Comparison Filters
        $compare_mode = isset($_GET['compare_mode']) && $_GET['compare_mode'] == '1';
        $compare_start = $_GET['compare_start'] ?? '';
        $compare_end = $_GET['compare_end'] ?? '';

        // 2. Fetch Master Data for Filters
        // Sales
        $query_sales = "SELECT id, sales_name FROM sales_persons ORDER BY sales_name";
        $stmt_sales = $this->db->prepare($query_sales);
        $stmt_sales->execute();
        $sales_list = $stmt_sales->fetchAll(PDO::FETCH_ASSOC);

        // Korlap (Role 'korlap')
        $query_korlap = "SELECT user_id, full_name FROM users WHERE role = 'korlap' ORDER BY full_name";
        $stmt_korlap = $this->db->prepare($query_korlap);
        $stmt_korlap->execute();
        $korlap_list = $stmt_korlap->fetchAll(PDO::FETCH_ASSOC);

        // Kohas (Role 'surat_hasil' as per request)
        $query_kohas = "SELECT user_id, full_name FROM users WHERE role = 'surat_hasil' ORDER BY full_name";
        $stmt_kohas = $this->db->prepare($query_kohas);
        $stmt_kohas->execute();
        $kohas_list = $stmt_kohas->fetchAll(PDO::FETCH_ASSOC);
        
        // Projects List for Filter (Only Ops In Progress or later)
        $project_scope_clause = $this->getProjectScopeSqlClause();
        $query_projects = "SELECT project_id, nama_project FROM projects 
                           WHERE status_project NOT IN ('need_approval_manager', 'need_approval_head', 'rejected', 're-nego', 'cancelled', 'DRAFT')
                           $project_scope_clause
                           ORDER BY created_at DESC";
        $stmt_projects = $this->db->prepare($query_projects);
        if ($this->isKorlapScope()) {
            $stmt_projects->bindValue(':scope_korlap_id', $_SESSION['user_id'] ?? '');
        }
        $stmt_projects->execute();
        $project_list = $stmt_projects->fetchAll(PDO::FETCH_ASSOC);


        // 3. Fetch Dashboard Data
        $korlap_tat_days = (int)($this->systemSetting->get('korlap_tat_days') ?? 3);
        
        // Fetch Holidays
        $holidays = $this->nationalHoliday->getHolidayDates();

        // We need to fetch projects and join with other tables.
        // Since filtering by JSON date is hard in SQL, we'll fetch broader range or all active projects and filter in PHP.
        // However, for performance, we should try to filter as much as possible.
        
        $query = "SELECT 
                    p.project_id, p.nama_project, p.tanggal_mcu, p.status_project,
                    p.sales_person_id, sp.sales_name,
                    p.korlap_id, k.full_name as korlap_name,
                    (
                        SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                        FROM medical_results mr_sub
                        JOIN medical_result_items mri_sub ON mr_sub.id = mri_sub.medical_result_id
                        JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                        WHERE mr_sub.project_id = p.project_id
                    ) as kohas_names,
                    (
                        SELECT MIN(rr_sub.created_at)
                        FROM rab_realizations rr_sub
                        WHERE rr_sub.project_id = p.project_id
                    ) as first_realization_created_at,
                    r.grand_total as rab_total,
                    r.cost_value,
                    (SELECT SUM(ri.qty) FROM rab_items ri WHERE ri.rab_id = r.id AND ri.category = 'personnel') as personnel_qty,
                    rr.total_amount as realization_total,
                    rr.actual_participants as realization_pax,
                    mr.id as medical_result_id
                  FROM projects p
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                  LEFT JOIN rabs r ON p.project_id = r.project_id AND r.status != 'rejected'
                  LEFT JOIN (
                      SELECT project_id, SUM(total_amount) as total_amount, SUM(actual_participants) as actual_participants 
                      FROM rab_realizations 
                      GROUP BY project_id
                  ) rr ON p.project_id = rr.project_id
                  LEFT JOIN medical_results mr ON p.project_id = mr.project_id
                  WHERE 1=1
                  AND p.status_project NOT IN ('need_approval_manager', 'need_approval_head', 'rejected', 're-nego', 'cancelled', 'DRAFT')
                  " . $this->getProjectScopeSqlClause() . "
                  ";
        
        $params = [];

        if ($filter_project_id) {
            $query .= " AND p.project_id = :pid";
            $params[':pid'] = $filter_project_id;
        }

        if ($filter_sales_id) {
            $query .= " AND p.sales_person_id = :sid";
            $params[':sid'] = $filter_sales_id;
        }

        if ($filter_korlap_id) {
            $query .= " AND p.korlap_id = :kid";
            $params[':kid'] = $filter_korlap_id;
        }
        
        // Kohas filter needs to check medical_result_items
        if ($filter_kohas_id) {
             $query .= " AND EXISTS (
                SELECT 1 FROM medical_results mr2 
                JOIN medical_result_items mri ON mr2.id = mri.medical_result_id
                WHERE mr2.project_id = p.project_id AND mri.assigned_to_user_id = :kohas_id
             )";
             $params[':kohas_id'] = $filter_kohas_id;
        }
        if ($this->isKorlapScope()) {
            $params[':scope_korlap_id'] = $_SESSION['user_id'] ?? '';
        }

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $raw_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Process Data (Refactored)
        // Main Period
        $current_stats = $this->calculatePeriodStats($start_date, $end_date, $raw_projects, $holidays, $korlap_tat_days);
        
        // Extract vars for view
        extract($current_stats);

        // Comparison Logic
        $comparison_stats = null;
        $deltas = [];
        
        if ($compare_mode && !empty($compare_start) && !empty($compare_end)) {
            $comparison_stats = $this->calculatePeriodStats($compare_start, $compare_end, $raw_projects, $holidays, $korlap_tat_days);
            
            // Calculate Deltas
            $metrics_to_compare = [
                'total_projects', 'total_anggaran', 'total_realisasi', 
                'total_pax', 'total_hari_ops', 'total_petugas'
            ];
            
            foreach ($metrics_to_compare as $metric) {
                $curr = $current_stats[$metric];
                $prev = $comparison_stats[$metric];
                $diff = $curr - $prev;
                $pct = ($prev > 0) ? ($diff / $prev * 100) : (($curr > 0) ? 100 : 0);
                
                $deltas[$metric] = [
                    'val' => $diff,
                    'pct' => $pct,
                    'sign' => ($diff > 0) ? '+' : '',
                    'color' => ($diff > 0) ? 'text-success' : (($diff < 0) ? 'text-danger' : 'text-muted')
                ];
                
                // Inverse color for budget/realization?
                // Usually higher realization is "bad" if budget is constant, but here it's total realization.
                // Higher realization usually means more business, which is good.
                // But Utilization > 100% is bad.
            }
        }

        // 5. Table Data (RAB & Realization style for App Script cross-check)
        $project_ids = array_values(array_filter(array_map(function ($project) {
            return $project['project_id'] ?? null;
        }, $filtered_projects)));
        $rab_table_rows = $this->getRabOpsTableData($project_ids);
        $realization_table_rows = $this->getRealizationOpsTableData($project_ids);

        // Render View
        include '../views/productivity_ops/index.php';
    }

    public function export_excel() {
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        $filter_project_id = $_GET['project_id'] ?? '';
        $filter_sales_id = $_GET['sales_id'] ?? '';
        $filter_korlap_id = $_GET['korlap_id'] ?? '';
        $filter_kohas_id = $_GET['kohas_id'] ?? '';
        if ($this->isKorlapScope()) {
            $filter_korlap_id = $_SESSION['user_id'] ?? '';
        }
        $export_type = $_GET['type'] ?? 'rab';

        $query = "SELECT 
                    p.project_id, p.nama_project, p.tanggal_mcu, p.status_project,
                    p.sales_person_id, sp.sales_name,
                    p.korlap_id, k.full_name as korlap_name,
                    (
                        SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                        FROM medical_results mr_sub
                        JOIN medical_result_items mri_sub ON mr_sub.id = mri_sub.medical_result_id
                        JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                        WHERE mr_sub.project_id = p.project_id
                    ) as kohas_names,
                    (
                        SELECT MIN(rr_sub.created_at)
                        FROM rab_realizations rr_sub
                        WHERE rr_sub.project_id = p.project_id
                    ) as first_realization_created_at,
                    r.grand_total as rab_total,
                    r.cost_value,
                    (SELECT SUM(ri.qty) FROM rab_items ri WHERE ri.rab_id = r.id AND ri.category = 'personnel') as personnel_qty,
                    rr.total_amount as realization_total,
                    rr.actual_participants as realization_pax,
                    mr.id as medical_result_id
                  FROM projects p
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                  LEFT JOIN rabs r ON p.project_id = r.project_id AND r.status != 'rejected'
                  LEFT JOIN (
                      SELECT project_id, SUM(total_amount) as total_amount, SUM(actual_participants) as actual_participants 
                      FROM rab_realizations 
                      GROUP BY project_id
                  ) rr ON p.project_id = rr.project_id
                  LEFT JOIN medical_results mr ON p.project_id = mr.project_id
                  WHERE 1=1
                  AND p.status_project NOT IN ('need_approval_manager', 'need_approval_head', 'rejected', 're-nego', 'cancelled', 'DRAFT')
                  " . $this->getProjectScopeSqlClause() . "
                  ";

        $params = [];
        if ($filter_project_id) {
            $query .= " AND p.project_id = :pid";
            $params[':pid'] = $filter_project_id;
        }
        if ($filter_sales_id) {
            $query .= " AND p.sales_person_id = :sid";
            $params[':sid'] = $filter_sales_id;
        }
        if ($filter_korlap_id) {
            $query .= " AND p.korlap_id = :kid";
            $params[':kid'] = $filter_korlap_id;
        }
        if ($filter_kohas_id) {
            $query .= " AND EXISTS (
                SELECT 1 FROM medical_results mr2 
                JOIN medical_result_items mri ON mr2.id = mri.medical_result_id
                WHERE mr2.project_id = p.project_id AND mri.assigned_to_user_id = :kohas_id
            )";
            $params[':kohas_id'] = $filter_kohas_id;
        }
        if ($this->isKorlapScope()) {
            $params[':scope_korlap_id'] = $_SESSION['user_id'] ?? '';
        }

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $raw_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $holidays = $this->nationalHoliday->getHolidayDates();
        $korlap_tat_days = (int)($this->systemSetting->get('korlap_tat_days') ?? 3);
        $stats = $this->calculatePeriodStats($start_date, $end_date, $raw_projects, $holidays, $korlap_tat_days);
        $filtered_projects = $stats['filtered_projects'] ?? [];

        $project_ids = array_values(array_filter(array_map(function ($project) {
            return $project['project_id'] ?? null;
        }, $filtered_projects)));

        $xlsxAvailable = true;
        if (!class_exists('XLSXWriter')) {
            $manualXlsxWriterPath = dirname(__DIR__) . '/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
            if (file_exists($manualXlsxWriterPath)) {
                require_once $manualXlsxWriterPath;
            }
        }
        if (!class_exists('XLSXWriter')) {
            $xlsxAvailable = false;
        }

        if ($export_type === 'realisasi') {
            $rows = $this->getRealizationOpsTableData($project_ids);
            $headers = [
                'Tanggal Realisasi', 'RAB Number', 'Project ID', 'Nama Project', 'Company',
                'Actual Participants', 'Status Realisasi', 'Total Realisasi',
                'Personnel Realisasi', 'Transport Realisasi', 'Consumption Realisasi', 'Vendor Realisasi',
                'RAB Total', 'Budget Ops', 'Variance', 'Realisasi %', 'Budget Status', 'Tgl Input Realisasi'
            ];
            $filenameXlsx = 'productivity_ops_realisasi_rab_' . date('Ymd_His') . '.xlsx';
            $filenameCsv = 'productivity_ops_realisasi_rab_' . date('Ymd_His') . '.csv';
        } else {
            $rows = $this->getRabOpsTableData($project_ids);
            $headers = [
                'RAB Number', 'Status', 'Project ID', 'Nama Project', 'Company', 'Tanggal MCU',
                'Sales', 'Korlap', 'Total Personnel', 'Personnel Detail', 'Total Transport', 'Transport Detail',
                'Total Consumption', 'Consumption Detail', 'Total Vendor', 'Vendor Detail',
                'Grand Total', 'Budget Ops', 'Budget %', 'Tgl Pengajuan', 'Approved Manager',
                'Approved Head', 'Submitted Finance', 'Finance Paid'
            ];
            $filenameXlsx = 'productivity_ops_rab_' . date('Ymd_His') . '.xlsx';
            $filenameCsv = 'productivity_ops_rab_' . date('Ymd_His') . '.csv';
        }

        if ($xlsxAvailable) {
            $writer = new XLSXWriter();
            $sheet_name = ($export_type === 'realisasi') ? 'Realisasi RAB' : 'RAB';
            $sheet_header = [];
            foreach ($headers as $header) {
                $sheet_header[$header] = 'string';
            }
            $writer->writeSheetHeader($sheet_name, $sheet_header);

            foreach ($rows as $row) {
                if ($export_type === 'realisasi') {
                    $writer->writeSheetRow($sheet_name, [
                        $row['realization_date'] ?? '',
                        $row['rab_number'] ?? '',
                        $row['project_id'] ?? '',
                        $row['nama_project'] ?? '',
                        $row['company_name'] ?? '',
                        $row['actual_participants'] ?? 0,
                        $row['realization_status'] ?? '',
                        $row['realization_total'] ?? 0,
                        $row['personnel_realization'] ?? '',
                        $row['transport_realization'] ?? '',
                        $row['consumption_realization'] ?? '',
                        $row['vendor_realization'] ?? '',
                        $row['rab_total'] ?? 0,
                        $row['budget_ops'] ?? 0,
                        $row['variance'] ?? 0,
                        $row['realization_percentage'] ?? 0,
                        $row['budget_status'] ?? '',
                        $row['tgl_input_realisasi'] ?? ''
                    ]);
                } else {
                    $writer->writeSheetRow($sheet_name, [
                        $row['rab_number'] ?? '',
                        $row['status'] ?? '',
                        $row['project_id'] ?? '',
                        $row['nama_project'] ?? '',
                        $row['company_name'] ?? '',
                        $row['tanggal_mcu'] ?? '',
                        $row['sales_name'] ?? '',
                        $row['korlap_name'] ?? '',
                        $row['total_personnel'] ?? 0,
                        $row['personnel_details'] ?? '',
                        $row['total_transport'] ?? 0,
                        $row['transport_details'] ?? '',
                        $row['total_consumption'] ?? 0,
                        $row['consumption_details'] ?? '',
                        $row['total_vendor'] ?? 0,
                        $row['vendor_details'] ?? '',
                        $row['grand_total'] ?? 0,
                        $row['budget_ops'] ?? 0,
                        $row['budget_percentage'] ?? 0,
                        $row['tgl_pengajuan'] ?? '',
                        $row['approved_date_manager'] ?? '',
                        $row['approved_date_head'] ?? '',
                        $row['submitted_to_finance_at'] ?? '',
                        $row['finance_paid_at'] ?? ''
                    ]);
                }
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filenameXlsx . '"');
            header('Cache-Control: max-age=0');
            $writer->writeToStdOut();
        } else {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filenameCsv . '"');
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                if ($export_type === 'realisasi') {
                    fputcsv($output, [
                        $row['realization_date'] ?? '',
                        $row['rab_number'] ?? '',
                        $row['project_id'] ?? '',
                        $row['nama_project'] ?? '',
                        $row['company_name'] ?? '',
                        $row['actual_participants'] ?? 0,
                        $row['realization_status'] ?? '',
                        $row['realization_total'] ?? 0,
                        $row['personnel_realization'] ?? '',
                        $row['transport_realization'] ?? '',
                        $row['consumption_realization'] ?? '',
                        $row['vendor_realization'] ?? '',
                        $row['rab_total'] ?? 0,
                        $row['budget_ops'] ?? 0,
                        $row['variance'] ?? 0,
                        $row['realization_percentage'] ?? 0,
                        $row['budget_status'] ?? '',
                        $row['tgl_input_realisasi'] ?? ''
                    ]);
                } else {
                    fputcsv($output, [
                        $row['rab_number'] ?? '',
                        $row['status'] ?? '',
                        $row['project_id'] ?? '',
                        $row['nama_project'] ?? '',
                        $row['company_name'] ?? '',
                        $row['tanggal_mcu'] ?? '',
                        $row['sales_name'] ?? '',
                        $row['korlap_name'] ?? '',
                        $row['total_personnel'] ?? 0,
                        $row['personnel_details'] ?? '',
                        $row['total_transport'] ?? 0,
                        $row['transport_details'] ?? '',
                        $row['total_consumption'] ?? 0,
                        $row['consumption_details'] ?? '',
                        $row['total_vendor'] ?? 0,
                        $row['vendor_details'] ?? '',
                        $row['grand_total'] ?? 0,
                        $row['budget_ops'] ?? 0,
                        $row['budget_percentage'] ?? 0,
                        $row['tgl_pengajuan'] ?? '',
                        $row['approved_date_manager'] ?? '',
                        $row['approved_date_head'] ?? '',
                        $row['submitted_to_finance_at'] ?? '',
                        $row['finance_paid_at'] ?? ''
                    ]);
                }
            }
            fclose($output);
        }
        exit;
    }

    private function calculatePeriodStats($start_date, $end_date, $raw_projects, $holidays, $korlap_tat_days) {
        $filtered_projects = [];
        $total_projects = 0;
        $total_anggaran = 0;
        $total_realisasi = 0;
        $total_pax = 0;
        $total_hari_ops = 0;
        $total_petugas = 0;
        $total_rab_submission = 0;
        
        $sales_stats = [];
        $korlap_stats = [];
        $trend_data = [];

        // Fetch MR Stats for this specific period
        // Let's get all medical result items and map by project_id for efficiency
        $query_mr_items = "SELECT mr.project_id, SUM(mri.actual_pax_checked) as total_pax, COUNT(DISTINCT mri.date_mcu) as days_worked
                           FROM medical_results mr
                           JOIN medical_result_items mri ON mr.id = mri.medical_result_id
                           WHERE mri.date_mcu BETWEEN :start_date AND :end_date
                           GROUP BY mr.project_id";
        $stmt_mr = $this->db->prepare($query_mr_items);
        $stmt_mr->bindValue(':start_date', $start_date);
        $stmt_mr->bindValue(':end_date', $end_date);
        $stmt_mr->execute();
        
        $mr_rows = $stmt_mr->fetchAll(PDO::FETCH_ASSOC);
        $mr_stats = [];
        foreach ($mr_rows as $row) {
            $mr_stats[$row['project_id']] = $row;
        }

        $project_ids_in_period = [];

        foreach ($raw_projects as $p) {
            // Parse Dates
            $dates = json_decode($p['tanggal_mcu'], true);
            if (!is_array($dates)) $dates = [];
            
            // Filter by Date Range
            $in_range = false;
            $project_days_in_range = 0;
            
            if (empty($dates)) {
                // If no dates, skip
            } else {
                foreach ($dates as $d) {
                    if ($d >= $start_date && $d <= $end_date) {
                        $in_range = true;
                        $project_days_in_range++;
                    }
                }
            }
            
            if (!$in_range) continue;

            $project_ids_in_period[] = $p['project_id'];

            // Add Start/End Date
            if (!empty($dates)) {
                $sorted_dates = $dates;
                sort($sorted_dates);
                $p['start_date'] = $sorted_dates[0];
                $p['end_date'] = end($sorted_dates);
            } else {
                $p['start_date'] = null;
                $p['end_date'] = null;
            }

            // Calculate Metrics
            $p_budget = $p['cost_value'] ?? 0;
            $p_realization = $p['realization_total'] ?? 0;
            $p_pax = $p['realization_pax'] ?? 0;
            $p_petugas = $p['personnel_qty'] ?? 0;
            $p_rab_total = $p['rab_total'] ?? 0;
            
            // TAT Logic
            $p_tat_days = null;
            if ($p['end_date'] && !empty($p['first_realization_created_at'])) {
                $days_diff = DateHelper::countWorkingDays($p['end_date'], $p['first_realization_created_at'], $holidays);
                $p_tat_days = $days_diff;
            }
            
            // Add to totals
            $total_projects++;
            $total_anggaran += $p_budget;
            $total_realisasi += $p_realization;
            $total_pax += $p_pax;
            $total_hari_ops += $project_days_in_range;
            $total_petugas += $p_petugas;
            $total_rab_submission += $p_rab_total;
            
            // Build Enriched Project Data
            $p['metrics'] = [
                'budget' => $p_budget,
                'realization' => $p_realization,
                'pax' => $p_pax,
                'days' => $project_days_in_range,
                'utilization' => ($p_budget > 0) ? ($p_realization / $p_budget * 100) : 0,
                'tat_days' => $p_tat_days ?? 0
            ];
            $filtered_projects[] = $p;

            // Sales Stats
            $s_id = $p['sales_name'] ?? 'Unknown';
            if (!isset($sales_stats[$s_id])) {
                $sales_stats[$s_id] = ['name' => $s_id, 'projects' => 0, 'pax' => 0, 'budget' => 0];
            }
            $sales_stats[$s_id]['projects']++;
            $sales_stats[$s_id]['pax'] += $p_pax;
            $sales_stats[$s_id]['budget'] += $p_budget;

            // Korlap Stats
            $k_id = $p['korlap_name'] ?? 'Unassigned';
            if (!isset($korlap_stats[$k_id])) {
                $korlap_stats[$k_id] = [
                    'name' => $k_id, 
                    'projects' => 0, 
                    'days' => 0, 
                    'pax' => 0, 
                    'total_budget' => 0, 
                    'total_realization' => 0,
                    'tat_sum' => 0,
                    'tat_count' => 0
                ];
            }
            $korlap_stats[$k_id]['projects']++;
            $korlap_stats[$k_id]['days'] += $project_days_in_range;
            $korlap_stats[$k_id]['pax'] += $p_pax;
            $korlap_stats[$k_id]['total_budget'] += $p_budget;
            $korlap_stats[$k_id]['total_realization'] += $p_realization;
            
            if ($p_tat_days !== null) {
                $korlap_stats[$k_id]['tat_sum'] += $p_tat_days;
                $korlap_stats[$k_id]['tat_count']++;
            }

            // Trend Data
            $project_month = '';
            foreach ($dates as $d) {
                if ($d >= $start_date && $d <= $end_date) {
                    $project_month = date('Y-m', strtotime($d));
                    break; 
                }
            }
            if ($project_month) {
                if (!isset($trend_data[$project_month])) {
                    $trend_data[$project_month] = ['projects' => 0, 'pax' => 0, 'realization' => 0];
                }
                $trend_data[$project_month]['projects']++;
                $trend_data[$project_month]['pax'] += $p_pax;
                $trend_data[$project_month]['realization'] += $p_realization;
            }
        }

        ksort($trend_data);

        // Post-process Korlap
        foreach ($korlap_stats as &$k) {
            $k['utilization'] = ($k['total_budget'] > 0) ? ($k['total_realization'] / $k['total_budget'] * 100) : 0;
            $k['avg_tat'] = ($k['tat_count'] > 0) ? ($k['tat_sum'] / $k['tat_count']) : 0;
            $k['score'] = $k['utilization'];
        }
        unset($k);

        // Kohas Stats
        $kohas_stats_data = [];
        if (!empty($project_ids_in_period)) {
            $in_clause = implode("','", $project_ids_in_period);
            $query_kohas_perf = "SELECT u.user_id, u.full_name, 
                                        SUM(mri.actual_pax_checked) as total_surat_hasil, 
                                        COUNT(DISTINCT mr.project_id) as total_projects,
                                        SUM(CASE WHEN mri.tat_overdue = 0 THEN 1 ELSE 0 END) as on_time_count,
                                        COUNT(mri.id) as total_items,
                                        (
                                            SELECT COUNT(*) 
                                            FROM medical_result_realizations mrr 
                                            JOIN rab_medical_results rmr ON mrr.rab_id = rmr.id 
                                            WHERE mrr.date BETWEEN :start_date AND :end_date
                                            AND rmr.project_id IN (
                                                SELECT DISTINCT mr_sub.project_id
                                                FROM medical_result_items mri_sub
                                                JOIN medical_results mr_sub ON mri_sub.medical_result_id = mr_sub.id
                                                WHERE mri_sub.assigned_to_user_id = u.user_id
                                                AND mri_sub.date_mcu BETWEEN :start_date AND :end_date
                                                AND mr_sub.project_id IN ('$in_clause')
                                            )
                                        ) as total_petugas_realized
                                    FROM medical_result_items mri
                                    JOIN medical_results mr ON mri.medical_result_id = mr.id
                                    JOIN users u ON mri.assigned_to_user_id = u.user_id
                                    WHERE mr.project_id IN ('$in_clause')
                                    AND mri.date_mcu BETWEEN :start_date AND :end_date
                                    GROUP BY u.user_id, u.full_name";
                                    
            $stmt_kp = $this->db->prepare($query_kohas_perf);
            $stmt_kp->bindValue(':start_date', $start_date);
            $stmt_kp->bindValue(':end_date', $end_date);
            $stmt_kp->execute();
            $kohas_stats_data = $stmt_kp->fetchAll(PDO::FETCH_ASSOC);
        }

        return compact(
            'filtered_projects',
            'total_projects',
            'total_anggaran',
            'total_realisasi',
            'total_pax',
            'total_hari_ops',
            'total_petugas',
            'total_rab_submission',
            'sales_stats',
            'korlap_stats',
            'kohas_stats_data',
            'trend_data'
        );
    }

    private function getRabOpsTableData($project_ids) {
        if (empty($project_ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($project_ids), '?'));
        $query = "SELECT r.id as rab_id_pk, r.rab_number, r.status, r.location_type, r.total_participants,
                         p.project_id, p.nama_project, p.company_name, p.tanggal_mcu,
                         sp.sales_name, u_korlap.full_name as korlap_name,
                         r.total_personnel, r.total_transport, r.total_consumption,
                         (SELECT SUM(subtotal) FROM rab_items WHERE rab_id = r.id AND category = 'vendor') as total_vendor,
                         r.grand_total, r.cost_value as budget_ops, r.cost_percentage as budget_percentage,
                         r.created_at as tgl_pengajuan, r.approved_date_manager, r.approved_date_head, r.submitted_to_finance_at, r.finance_paid_at
                  FROM rabs r
                  LEFT JOIN projects p ON r.project_id = p.project_id
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN users u_korlap ON p.korlap_id = u_korlap.user_id
                  WHERE p.project_id IN ($placeholders)
                  ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($project_ids);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rows = [];
        foreach ($data as $row) {
            $rows[] = [
                'rab_number' => $row['rab_number'],
                'status' => $row['status'],
                'project_id' => $row['project_id'],
                'nama_project' => $row['nama_project'],
                'company_name' => $row['company_name'],
                'tanggal_mcu' => $this->formatTanggalMcuForTable($row['tanggal_mcu']),
                'sales_name' => $row['sales_name'],
                'korlap_name' => $row['korlap_name'],
                'total_personnel' => (float)($row['total_personnel'] ?? 0),
                'personnel_details' => $this->getRabItemDetailsForTable($row['rab_id_pk'], 'personnel'),
                'total_transport' => (float)($row['total_transport'] ?? 0),
                'transport_details' => $this->getRabItemDetailsForTable($row['rab_id_pk'], 'transport'),
                'total_consumption' => (float)($row['total_consumption'] ?? 0),
                'consumption_details' => $this->getRabItemDetailsForTable($row['rab_id_pk'], 'consumption'),
                'total_vendor' => (float)($row['total_vendor'] ?? 0),
                'vendor_details' => $this->getRabItemDetailsForTable($row['rab_id_pk'], 'vendor'),
                'grand_total' => (float)($row['grand_total'] ?? 0),
                'budget_ops' => (float)($row['budget_ops'] ?? 0),
                'budget_percentage' => (float)($row['budget_percentage'] ?? 0),
                'tgl_pengajuan' => $row['tgl_pengajuan'],
                'approved_date_manager' => $row['approved_date_manager'],
                'approved_date_head' => $row['approved_date_head'],
                'submitted_to_finance_at' => $row['submitted_to_finance_at'],
                'finance_paid_at' => $row['finance_paid_at']
            ];
        }

        return $rows;
    }

    private function getRealizationOpsTableData($project_ids) {
        if (empty($project_ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($project_ids), '?'));
        $query = "SELECT rr.id as real_id_pk, rr.date as realization_date, rr.actual_participants, rr.total_amount as realization_total, rr.status as realization_status,
                         r.rab_number, r.grand_total as rab_total, r.cost_value as budget_ops,
                         (r.cost_value - rr.total_amount) as variance,
                         (CASE WHEN r.cost_value > 0 THEN (rr.total_amount / r.cost_value * 100) ELSE 0 END) as realization_percentage,
                         p.project_id, p.nama_project, p.company_name,
                         rr.created_at as tgl_input_realisasi
                  FROM rab_realizations rr
                  LEFT JOIN rabs r ON rr.rab_id = r.id
                  LEFT JOIN projects p ON rr.project_id = p.project_id
                  WHERE p.project_id IN ($placeholders)
                  ORDER BY rr.date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($project_ids);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rows = [];
        foreach ($data as $row) {
            $variance = (float)($row['variance'] ?? 0);
            $rows[] = [
                'realization_date' => $row['realization_date'],
                'rab_number' => $row['rab_number'],
                'project_id' => $row['project_id'],
                'nama_project' => $row['nama_project'],
                'company_name' => $row['company_name'],
                'actual_participants' => (float)($row['actual_participants'] ?? 0),
                'realization_status' => $row['realization_status'],
                'realization_total' => (float)($row['realization_total'] ?? 0),
                'personnel_realization' => $this->getRealizationItemDetailsForTable($row['real_id_pk'], 'personnel'),
                'transport_realization' => $this->getRealizationItemDetailsForTable($row['real_id_pk'], 'transport'),
                'consumption_realization' => $this->getRealizationItemDetailsForTable($row['real_id_pk'], 'consumption'),
                'vendor_realization' => $this->getRealizationItemDetailsForTable($row['real_id_pk'], 'vendor'),
                'rab_total' => (float)($row['rab_total'] ?? 0),
                'budget_ops' => (float)($row['budget_ops'] ?? 0),
                'variance' => $variance,
                'realization_percentage' => (float)($row['realization_percentage'] ?? 0),
                'budget_status' => ($variance >= 0) ? 'Under Budget' : 'Over Budget',
                'tgl_input_realisasi' => $row['tgl_input_realisasi']
            ];
        }

        return $rows;
    }

    private function getRabItemDetailsForTable($rab_id, $category) {
        $query = "SELECT item_name, qty, days, price
                  FROM rab_items
                  WHERE rab_id = :rab_id AND category = :category";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':rab_id', $rab_id);
        $stmt->bindValue(':category', $category);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lines = [];
        foreach ($items as $item) {
            $line = $item['item_name'] . ': ' . (float)$item['qty'];
            if ((float)$item['days'] > 0) {
                $line .= ' x ' . (float)$item['days'];
            }
            $line .= ' @ ' . number_format((float)$item['price'], 0, ',', '.');
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    private function getRealizationItemDetailsForTable($realization_id, $category) {
        $query = "SELECT item_name, qty, price
                  FROM rab_realization_items
                  WHERE realization_id = :realization_id AND category = :category";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':realization_id', $realization_id);
        $stmt->bindValue(':category', $category);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lines = [];
        foreach ($items as $item) {
            $lines[] = $item['item_name'] . ': ' . (float)$item['qty'] . ' @ ' . number_format((float)$item['price'], 0, ',', '.');
        }

        return implode("\n", $lines);
    }

    private function formatTanggalMcuForTable($tanggal_mcu_json) {
        if (empty($tanggal_mcu_json)) {
            return '-';
        }

        $dates = json_decode($tanggal_mcu_json, true);
        if (!is_array($dates) || empty($dates)) {
            return $tanggal_mcu_json;
        }

        $formatted = [];
        foreach ($dates as $date) {
            $timestamp = strtotime($date);
            $formatted[] = $timestamp ? date('d M Y', $timestamp) : $date;
        }

        return implode(', ', $formatted);
    }

    private function isKorlapScope() {
        return (($_SESSION['role'] ?? '') === 'korlap');
    }

    private function getProjectScopeSqlClause() {
        if ($this->isKorlapScope()) {
            return " AND p.korlap_id = :scope_korlap_id ";
        }
        return "";
    }
}
?>