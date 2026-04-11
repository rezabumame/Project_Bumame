<?php

class ApiController extends BaseController {
    private $api_key = "277b03b07f2b7f9a7924929d33129799"; // Default API Key for AppScript

    public function __construct() {
        // Clear any previous output (headers, notices)
        while (ob_get_level()) ob_end_clean();
        
        // Skip session_start if called from constructor but BaseController handles it
        // We need to bypass checkAuth if we want public access
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Check API Key
        $header_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        if ($header_key !== $this->api_key) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized API Access']);
            exit;
        }
    }

    public function fetch_data() {
        try {
            $type = $_GET['type'] ?? 'rabs';
            
            switch ($type) {
                case 'rabs':
                    $this->export_rabs();
                    break;
                case 'realizations':
                    $this->export_realizations();
                    break;
                case 'projects':
                    $this->export_projects();
                    break;
                case 'inventory':
                    $this->export_inventory();
                    break;
                case 'medical':
                    $this->export_medical();
                    break;
                case 'invoices':
                    $this->export_invoices();
                    break;
                default:
                    $this->jsonResponse(['status' => 'error', 'message' => 'Invalid data type requested'], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    protected function jsonResponse($data, $statusCode = 200) {
        // Clear buffer again just in case
        while (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    private function export_rabs() {
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
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $helperPath = dirname(__DIR__) . '/helpers/DateHelper.php';
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }

        $finalData = [];
        // Post-process for readability and reordering
        foreach ($data as $row) {
            $formattedRow = [];
            
            // 1. Basic Info
            $formattedRow['rab_number'] = $row['rab_number'];
            $formattedRow['status'] = $row['status'];
            $formattedRow['location_type'] = $row['location_type'];
            $formattedRow['total_participants'] = $row['total_participants'];
            $formattedRow['project_id'] = $row['project_id'];
            $formattedRow['nama_project'] = $row['nama_project'];
            $formattedRow['company_name'] = $row['company_name'];
            
            if (class_exists('DateHelper')) {
                $formattedRow['tanggal_mcu'] = DateHelper::formatSmartDateIndonesian($row['tanggal_mcu']);
            } else {
                $formattedRow['tanggal_mcu'] = $row['tanggal_mcu'];
            }
            
            $formattedRow['sales_name'] = $row['sales_name'];
            $formattedRow['korlap_name'] = $row['korlap_name'];

            // 2. Details and Totals (Side by Side)
            $formattedRow['total_personnel'] = number_format($row['total_personnel'], 0, ',', '.');
            $formattedRow['personnel_details'] = $this->getItemDetails($row['rab_id_pk'], 'personnel');
            
            $formattedRow['total_transport'] = number_format($row['total_transport'], 0, ',', '.');
            $formattedRow['transport_details'] = $this->getItemDetails($row['rab_id_pk'], 'transport');
            
            $formattedRow['total_consumption'] = number_format($row['total_consumption'], 0, ',', '.');
            $formattedRow['consumption_details'] = $this->getItemDetails($row['rab_id_pk'], 'consumption');
            
            $formattedRow['total_vendor'] = number_format($row['total_vendor'] ?? 0, 0, ',', '.');
            $formattedRow['vendor_details'] = $this->getItemDetails($row['rab_id_pk'], 'vendor');

            // 3. Grand Totals & Budget
            $formattedRow['grand_total'] = number_format($row['grand_total'], 0, ',', '.');
            $formattedRow['budget_ops'] = number_format($row['budget_ops'], 0, ',', '.');
            $formattedRow['budget_percentage'] = number_format($row['budget_percentage'], 2, ',', '.') . '%';

            // 4. Timestamps (Far Right)
            $formattedRow['tgl_pengajuan'] = $row['tgl_pengajuan'] ? date('d M Y H:i', strtotime($row['tgl_pengajuan'])) : '-';
            $formattedRow['approved_date_manager'] = $row['approved_date_manager'] ? date('d M Y H:i', strtotime($row['approved_date_manager'])) : '-';
            $formattedRow['approved_date_head'] = $row['approved_date_head'] ? date('d M Y H:i', strtotime($row['approved_date_head'])) : '-';
            $formattedRow['submitted_to_finance_at'] = $row['submitted_to_finance_at'] ? date('d M Y H:i', strtotime($row['submitted_to_finance_at'])) : '-';
            $formattedRow['finance_paid_at'] = $row['finance_paid_at'] ? date('d M Y H:i', strtotime($row['finance_paid_at'])) : '-';

            $finalData[] = $formattedRow;
        }
        
        $this->jsonResponse(['status' => 'success', 'data' => $finalData]);
    }

    private function getItemDetails($rab_id, $category) {
        $query = "SELECT item_name, qty, days, price FROM rab_items WHERE rab_id = :rab_id AND category = :category";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':rab_id', $rab_id);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lines = [];
        foreach ($items as $item) {
            $priceStr = number_format($item['price'], 0, ',', '.');
            $line = $item['item_name'] . ': ' . (float)$item['qty'];
            if ($item['days'] > 0) {
                $line .= ' x ' . (float)$item['days'];
            }
            $line .= ' @ ' . $priceStr;
            $lines[] = $line;
        }
        return implode("\n", $lines);
    }

    private function export_realizations() {
        $query = "SELECT rr.id as real_id_pk, rr.date as realization_date, rr.actual_participants, rr.total_amount as realization_total, rr.status as realization_status,
                         r.rab_number, r.grand_total as rab_total, r.cost_value as budget_ops,
                         (r.cost_value - rr.total_amount) as variance,
                         (rr.total_amount / r.cost_value * 100) as realization_percentage,
                         p.nama_project, p.company_name,
                         rr.created_at as tgl_input_realisasi
                  FROM rab_realizations rr
                  LEFT JOIN rabs r ON rr.rab_id = r.id
                  LEFT JOIN projects p ON rr.project_id = p.project_id
                  ORDER BY rr.date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $finalData = [];
        foreach ($data as $row) {
            $formattedRow = [];

            // 1. Basic Info
            $formattedRow['realization_date'] = date('d M Y', strtotime($row['realization_date']));
            $formattedRow['rab_number'] = $row['rab_number'];
            $formattedRow['nama_project'] = $row['nama_project'];
            $formattedRow['company_name'] = $row['company_name'];
            $formattedRow['actual_participants'] = $row['actual_participants'];
            $formattedRow['realization_status'] = $row['realization_status'];

            // 2. Totals and Details
            $formattedRow['realization_total'] = number_format($row['realization_total'], 0, ',', '.');
            $formattedRow['personnel_realization'] = $this->getRealizationItemDetails($row['real_id_pk'], 'personnel');
            $formattedRow['transport_realization'] = $this->getRealizationItemDetails($row['real_id_pk'], 'transport');
            $formattedRow['consumption_realization'] = $this->getRealizationItemDetails($row['real_id_pk'], 'consumption');
            $formattedRow['vendor_realization'] = $this->getRealizationItemDetails($row['real_id_pk'], 'vendor');

            // 3. Comparison & Variance
            $formattedRow['rab_total'] = number_format($row['rab_total'], 0, ',', '.');
            $formattedRow['budget_ops'] = number_format($row['budget_ops'], 0, ',', '.');
            $formattedRow['variance'] = number_format($row['variance'], 0, ',', '.');
            $formattedRow['realization_percentage'] = number_format($row['realization_percentage'], 2, ',', '.') . '%';
            $formattedRow['budget_status'] = ($row['variance'] >= 0) ? 'Under Budget' : 'Over Budget';

            // 4. Timestamps
            $formattedRow['tgl_input_realisasi'] = $row['tgl_input_realisasi'] ? date('d M Y H:i', strtotime($row['tgl_input_realisasi'])) : '-';

            $finalData[] = $formattedRow;
        }
        
        $this->jsonResponse(['status' => 'success', 'data' => $finalData]);
    }

    private function getRealizationItemDetails($realization_id, $category) {
        $query = "SELECT item_name, qty, price FROM rab_realization_items WHERE realization_id = :realization_id AND category = :category";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':realization_id', $realization_id);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lines = [];
        foreach ($items as $item) {
            $priceStr = number_format($item['price'], 0, ',', '.');
            $lines[] = $item['item_name'] . ': ' . (float)$item['qty'] . ' @ ' . $priceStr;
        }
        return implode("\n", $lines);
    }

    private function export_projects() {
        $query = "SELECT p.*, sp.sales_name, u_korlap.full_name as korlap_name, u_creator.full_name as creator_name,
                         sm.name as sales_manager_name
                  FROM projects p
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                  LEFT JOIN users u_korlap ON p.korlap_id = u_korlap.user_id
                  LEFT JOIN users u_creator ON p.created_by = u_creator.user_id
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
    }

    private function export_inventory() {
        $query = "SELECT ir.*, p.nama_project, u_creator.full_name as creator_name,
                         (SELECT GROUP_CONCAT(CONCAT(ii.item_name, ' (', iri.qty_request, ' ', ii.unit, ')') SEPARATOR ' | ')
                          FROM inventory_request_items iri
                          JOIN inventory_items ii ON iri.item_id = ii.id
                          WHERE iri.request_id = ir.id) as requested_items
                  FROM inventory_requests ir
                  LEFT JOIN projects p ON ir.project_id = p.project_id
                  LEFT JOIN users u_creator ON ir.created_by = u_creator.user_id
                  ORDER BY ir.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
    }

    private function export_medical() {
        $query = "SELECT mr.*, p.nama_project, p.company_name, p.tanggal_mcu,
                         (SELECT GROUP_CONCAT(CONCAT(u.full_name, ': ', mri.actual_pax_checked, '/', mri.actual_pax_released) SEPARATOR ' | ')
                          FROM medical_result_items mri
                          JOIN users u ON mri.assigned_to_user_id = u.user_id
                          WHERE mri.medical_result_id = mr.id) as assignment_details
                  FROM medical_results mr
                  LEFT JOIN projects p ON mr.project_id = p.project_id
                  ORDER BY mr.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
    }

    private function export_invoices() {
        $query = "SELECT i.*, ir.request_number, p.nama_project, p.company_name,
                         (SELECT GROUP_CONCAT(CONCAT(item_description, ' (', qty, ' x ', price, ')') SEPARATOR ' | ')
                          FROM invoice_items WHERE invoice_id = i.id) as invoice_details
                  FROM invoices i
                  LEFT JOIN invoice_requests ir ON i.invoice_request_id = ir.id
                  LEFT JOIN projects p ON ir.project_id = p.project_id
                  ORDER BY i.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
    }
}
