<?php

class ApiController extends BaseController {
    private $api_key = "277b03b07f2b7f9a7924929d33129799"; // Default API Key for AppScript

    public function __construct() {
        parent::__construct();
        // Check API Key
        $header_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        if ($header_key !== $this->api_key) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized API Access'], 401);
        }
    }

    public function fetch_data() {
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
    }

    private function export_rabs() {
        $query = "SELECT r.rab_number, r.status, r.location_type, r.grand_total, r.total_participants,
                         p.project_id, p.nama_project, p.company_name, p.tanggal_mcu,
                         sp.sales_name, u_korlap.full_name as korlap_name, u_creator.full_name as creator_name,
                         (SELECT GROUP_CONCAT(CONCAT(item_name, ': ', qty, ' x ', days, ' @ ', price) SEPARATOR '\n') 
                          FROM rab_items WHERE rab_id = r.id AND category = 'personnel') as personnel_details,
                         (SELECT GROUP_CONCAT(CONCAT(item_name, ': ', qty, ' @ ', price) SEPARATOR '\n') 
                          FROM rab_items WHERE rab_id = r.id AND category = 'transport') as transport_details,
                         (SELECT GROUP_CONCAT(CONCAT(item_name, ': ', qty, ' @ ', price) SEPARATOR '\n') 
                          FROM rab_items WHERE rab_id = r.id AND category = 'consumption') as consumption_details,
                         (SELECT GROUP_CONCAT(CONCAT(item_name, ': ', qty, ' @ ', price) SEPARATOR '\n') 
                          FROM rab_items WHERE rab_id = r.id AND category = 'vendor') as vendor_details
                  FROM rabs r
                  LEFT JOIN projects p ON r.project_id = p.project_id
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN users u_korlap ON p.korlap_id = u_korlap.user_id
                  LEFT JOIN users u_creator ON r.created_by = u_creator.user_id
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Post-process JSON dates for readability
        foreach ($data as &$row) {
            if (!empty($row['tanggal_mcu'])) {
                $dates = json_decode($row['tanggal_mcu'], true);
                if (is_array($dates)) {
                    $row['tanggal_mcu'] = implode(', ', $dates);
                }
            }
        }
        
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
    }

    private function export_realizations() {
        $query = "SELECT rr.date as realization_date, rr.actual_participants, rr.total_amount as realization_total, rr.status as realization_status,
                         r.rab_number, r.grand_total as rab_total,
                         p.nama_project, p.company_name,
                         u_creator.full_name as creator_name,
                         (SELECT GROUP_CONCAT(CONCAT(item_name, ': ', qty, ' @ ', price) SEPARATOR '\n') 
                          FROM rab_realization_items WHERE realization_id = rr.id AND category = 'personnel') as personnel_realization,
                         (SELECT GROUP_CONCAT(CONCAT(item_name, ': ', qty, ' @ ', price) SEPARATOR '\n') 
                          FROM rab_realization_items WHERE realization_id = rr.id AND category = 'transport') as transport_realization,
                         (SELECT GROUP_CONCAT(CONCAT(item_name, ': ', qty, ' @ ', price) SEPARATOR '\n') 
                          FROM rab_realization_items WHERE realization_id = rr.id AND category = 'consumption') as consumption_realization,
                         (SELECT GROUP_CONCAT(CONCAT(item_name, ': ', qty, ' @ ', price) SEPARATOR '\n') 
                          FROM rab_realization_items WHERE realization_id = rr.id AND category = 'vendor') as vendor_realization
                  FROM rab_realizations rr
                  LEFT JOIN rabs r ON rr.rab_id = r.id
                  LEFT JOIN projects p ON rr.project_id = p.project_id
                  LEFT JOIN users u_creator ON rr.created_by = u_creator.user_id
                  ORDER BY rr.date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
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
