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
        $query = "SELECT r.id as rab_id_pk, r.rab_number, r.status, r.location_type, r.grand_total, r.cost_value as budget_ops, r.cost_percentage as budget_percentage, r.total_participants,
                         r.total_personnel, r.total_transport, r.total_consumption,
                         (SELECT SUM(subtotal) FROM rab_items WHERE rab_id = r.id AND category = 'vendor') as total_vendor,
                         r.created_at as tgl_pengajuan, r.approved_date_manager, r.approved_date_head, r.submitted_to_finance_at, r.finance_paid_at,
                         p.project_id, p.nama_project, p.company_name, p.tanggal_mcu,
                         sp.sales_name, u_korlap.full_name as korlap_name
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

        // Post-process for readability
        foreach ($data as &$row) {
            // Fetch Details separately to avoid SQL complexity/locale issues
            $row['personnel_details'] = $this->getItemDetails($row['rab_id_pk'], 'personnel');
            $row['transport_details'] = $this->getItemDetails($row['rab_id_pk'], 'transport');
            $row['consumption_details'] = $this->getItemDetails($row['rab_id_pk'], 'consumption');
            $row['vendor_details'] = $this->getItemDetails($row['rab_id_pk'], 'vendor');

            // Format MCU Date using Smart Helper
            if (class_exists('DateHelper')) {
                $row['tanggal_mcu'] = DateHelper::formatSmartDateIndonesian($row['tanggal_mcu']);
            }
            
            // Format Timeline Dates
            $row['tgl_pengajuan'] = $row['tgl_pengajuan'] ? date('d M Y H:i', strtotime($row['tgl_pengajuan'])) : '-';
            $row['approved_date_manager'] = $row['approved_date_manager'] ? date('d M Y H:i', strtotime($row['approved_date_manager'])) : '-';
            $row['approved_date_head'] = $row['approved_date_head'] ? date('d M Y H:i', strtotime($row['approved_date_head'])) : '-';
            $row['submitted_to_finance_at'] = $row['submitted_to_finance_at'] ? date('d M Y H:i', strtotime($row['submitted_to_finance_at'])) : '-';
            $row['finance_paid_at'] = $row['finance_paid_at'] ? date('d M Y H:i', strtotime($row['finance_paid_at'])) : '-';

            // Format Currency columns with Dot
            $row['grand_total'] = number_format($row['grand_total'], 0, ',', '.');
            $row['budget_ops'] = number_format($row['budget_ops'], 0, ',', '.');
            $row['budget_percentage'] = number_format($row['budget_percentage'], 2, ',', '.') . '%';
            
            $row['total_personnel'] = number_format($row['total_personnel'], 0, ',', '.');
            $row['total_transport'] = number_format($row['total_transport'], 0, ',', '.');
            $row['total_consumption'] = number_format($row['total_consumption'], 0, ',', '.');
            $row['total_vendor'] = number_format($row['total_vendor'] ?? 0, 0, ',', '.');

            // Remove internal PK
            unset($row['rab_id_pk']);
        }
        
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
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
                         rr.created_at as tgl_input_realisasi,
                         r.rab_number, r.grand_total as rab_total, r.cost_value as budget_ops,
                         (r.cost_value - rr.total_amount) as variance,
                         (rr.total_amount / r.cost_value * 100) as realization_percentage,
                         p.nama_project, p.company_name
                  FROM rab_realizations rr
                  LEFT JOIN rabs r ON rr.rab_id = r.id
                  LEFT JOIN projects p ON rr.project_id = p.project_id
                  ORDER BY rr.date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            // Fetch Details separately
            $row['personnel_realization'] = $this->getRealizationItemDetails($row['real_id_pk'], 'personnel');
            $row['transport_realization'] = $this->getRealizationItemDetails($row['real_id_pk'], 'transport');
            $row['consumption_realization'] = $this->getRealizationItemDetails($row['real_id_pk'], 'consumption');
            $row['vendor_realization'] = $this->getRealizationItemDetails($row['real_id_pk'], 'vendor');

            // Format Dates
            $row['realization_date'] = date('d M Y', strtotime($row['realization_date']));
            $row['tgl_input_realisasi'] = $row['tgl_input_realisasi'] ? date('d M Y H:i', strtotime($row['tgl_input_realisasi'])) : '-';

            // Format Currency
            $row['realization_total'] = number_format($row['realization_total'], 0, ',', '.');
            $row['rab_total'] = number_format($row['rab_total'], 0, ',', '.');
            $row['budget_ops'] = number_format($row['budget_ops'], 0, ',', '.');
            $row['variance'] = number_format($row['variance'], 0, ',', '.');
            $row['realization_percentage'] = number_format($row['realization_percentage'], 2, ',', '.') . '%';
            
            // Add Under/Over Budget label
            $row['budget_status'] = ($row['variance'] >= 0) ? 'Under Budget' : 'Over Budget';

            // Remove internal PK
            unset($row['real_id_pk']);
        }
        
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
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
