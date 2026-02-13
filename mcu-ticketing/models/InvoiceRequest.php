<?php
class InvoiceRequest {
    private $conn;
    private $table_name = "invoice_requests";
    private $table_items = "invoice_request_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data, $items, $project_ids = []) {
        try {
            $this->conn->beginTransaction();

            // 1. Insert Header
            $query = "INSERT INTO " . $this->table_name . " 
                      (request_number, request_date, pic_sales_id, partner_type, event_type, client_company, client_pic, client_phone, client_email, invoice_terms, shipping_address, notes, link_gdrive_npwp, link_gdrive_absensi, status) 
                      VALUES 
                      (:req_num, :req_date, :sales_id, :partner_type, :event_type, :company, :pic, :phone, :email, :terms, :addr, :notes, :link_npwp, :link_absensi, 'DRAFT')";
            
            $stmt = $this->conn->prepare($query);
            
            // Use provided Request Number or Generate
            if (!empty($data['request_number'])) {
                $req_num = $data['request_number'];
            } else {
                $req_num = $this->generateRequestNumber();
            }
            
            $stmt->bindParam(":req_num", $req_num);
            $stmt->bindParam(":req_date", $data['request_date']);
            $stmt->bindParam(":sales_id", $data['pic_sales_id']);
            $stmt->bindParam(":partner_type", $data['partner_type']);
            $stmt->bindParam(":event_type", $data['event_type']);
            $stmt->bindParam(":company", $data['client_company']);
            $stmt->bindParam(":pic", $data['client_pic']);
            $stmt->bindParam(":phone", $data['client_phone']);
            $stmt->bindParam(":email", $data['client_email']);
            $stmt->bindParam(":terms", $data['invoice_terms']);
            $stmt->bindParam(":addr", $data['shipping_address']);
            $stmt->bindParam(":notes", $data['notes']);
            $stmt->bindParam(":link_npwp", $data['link_gdrive_npwp']);
            $stmt->bindParam(":link_absensi", $data['link_gdrive_absensi']);
            
            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                throw new Exception("Failed to insert invoice request header. SQL Error: " . $error[2]);
            }
            
            $request_id = $this->conn->lastInsertId();

            // 2. Link Projects (New)
            if (!empty($project_ids)) {
                $queryProj = "INSERT INTO invoice_request_projects (invoice_request_id, project_id) VALUES (:rid, :pid)";
                $stmtProj = $this->conn->prepare($queryProj);
                foreach ($project_ids as $pid) {
                    $stmtProj->bindParam(":rid", $request_id);
                    $stmtProj->bindParam(":pid", $pid);
                    if (!$stmtProj->execute()) {
                        $error = $stmtProj->errorInfo();
                        throw new Exception("Failed to link project $pid. SQL Error: " . $error[2]);
                    }
                }
            }

            // 3. Insert Items (Updated: No project_id)
            $queryItem = "INSERT INTO " . $this->table_items . " 
                          (invoice_request_id, item_description, price, qty, remarks) 
                          VALUES (:rid, :desc, :price, :qty, :remarks)";
            $stmtItem = $this->conn->prepare($queryItem);

            foreach ($items as $item) {
                // $pid removed
                $stmtItem->bindParam(":rid", $request_id);
                $stmtItem->bindParam(":desc", $item['item_description']);
                $stmtItem->bindParam(":price", $item['price']);
                $stmtItem->bindParam(":qty", $item['qty']);
                $stmtItem->bindParam(":remarks", $item['remarks']);
                
                if (!$stmtItem->execute()) {
                    $error = $stmtItem->errorInfo();
                    throw new Exception("Failed to insert invoice request item. SQL Error: " . $error[2]);
                }
            }

            $this->conn->commit();
            return $request_id;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("InvoiceRequest Create Error: " . $e->getMessage());
            $_SESSION['error_debug'] = $e->getMessage(); // Store error for debugging
            return false;
        }
    }

    private function generateRequestNumber() {
        $prefix = "INVREQ/" . date('Ym') . "/";
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE request_number LIKE :prefix";
        $stmt = $this->conn->prepare($query);
        $param = $prefix . "%";
        $stmt->bindParam(":prefix", $param);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $next = $row['total'] + 1;
        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function getAll($role, $user_id) {
        $query = "SELECT ir.*, 
                  (SELECT GROUP_CONCAT(DISTINCT sp.sales_name SEPARATOR ', ')
                   FROM invoice_request_projects irp
                   JOIN projects p ON irp.project_id = p.project_id
                   JOIN sales_persons sp ON p.sales_person_id = sp.id
                   WHERE irp.invoice_request_id = ir.id
                  ) as sales_name,
                  u.full_name as creator_name,
                  us.full_name as approver_sales_name,
                  us.jabatan as approver_sales_jabatan,
                  ir.approved_by_sales_at,
                  uspv.full_name as approver_spv_name,
                  uspv.jabatan as approver_spv_jabatan,
                  ir.approved_by_supervisor_at,
                  umgr.full_name as approver_mgr_name,
                  umgr.jabatan as approver_mgr_jabatan,
                  ir.approved_by_manager_at
                  FROM " . $this->table_name . " ir
                  JOIN users u ON ir.pic_sales_id = u.user_id
                  LEFT JOIN users us ON ir.approved_by_sales = us.user_id
                  LEFT JOIN users uspv ON ir.approved_by_supervisor = uspv.user_id
                  LEFT JOIN users umgr ON ir.approved_by_manager = umgr.user_id
";
        
        $where_clauses = [];
        
        // 1. Sales only sees their own
        if ($role == 'sales') {
            $where_clauses[] = "ir.pic_sales_id = :uid";
        }
        
        // 2. Hide Drafts for everyone except Admin Sales & Superadmin
        if ($role !== 'admin_sales' && $role !== 'superadmin') {
            $where_clauses[] = "ir.status != 'DRAFT'";
        }
        
        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        $query .= " ORDER BY ir.created_at DESC";
        $stmt = $this->conn->prepare($query);
        
        if ($role == 'sales') {
            $stmt->bindParam(":uid", $user_id);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT ir.*, u.full_name as sales_name, u.jabatan as sales_jabatan,
                  us.full_name as approver_sales_name,
                  us.jabatan as approver_sales_jabatan,
                  ir.approved_by_sales_at,
                  uspv.full_name as approver_spv_name,
                  uspv.jabatan as approver_spv_jabatan,
                  ir.approved_by_supervisor_at,
                  umgr.full_name as approver_mgr_name,
                  umgr.jabatan as approver_mgr_jabatan,
                  ir.approved_by_manager_at
                  FROM " . $this->table_name . " ir
                  JOIN users u ON ir.pic_sales_id = u.user_id
                  LEFT JOIN users us ON ir.approved_by_sales = us.user_id
                  LEFT JOIN users uspv ON ir.approved_by_supervisor = uspv.user_id
                  LEFT JOIN users umgr ON ir.approved_by_manager = umgr.user_id
                  WHERE ir.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$header) return false;

        // Get Items
        $queryItems = "SELECT iri.* 
                       FROM " . $this->table_items . " iri
                       WHERE iri.invoice_request_id = :id";
        $stmtItems = $this->conn->prepare($queryItems);
        $stmtItems->bindParam(":id", $id);
        $stmtItems->execute();
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        $header['items'] = $items;

        // Fetch Linked Projects & Documents (SPH & BA)
        $queryProjects = "SELECT p.project_id, p.nama_project, p.sph_file, p.tanggal_mcu 
                          FROM invoice_request_projects irp
                          JOIN projects p ON irp.project_id = p.project_id
                          WHERE irp.invoice_request_id = :id";
        $stmtProjects = $this->conn->prepare($queryProjects);
        $stmtProjects->bindParam(":id", $id);
        $stmtProjects->execute();
        $linked_projects = $stmtProjects->fetchAll(PDO::FETCH_ASSOC);

        foreach ($linked_projects as &$proj) {
            // Fetch latest BA file
            $queryBA = "SELECT file_path, tanggal_mcu FROM project_berita_acara 
                        WHERE project_id = :pid AND status = 'uploaded' 
                        ORDER BY created_at DESC LIMIT 1";
            $stmtBA = $this->conn->prepare($queryBA);
            $stmtBA->bindParam(":pid", $proj['project_id']);
            $stmtBA->execute();
            $ba = $stmtBA->fetch(PDO::FETCH_ASSOC);
            $proj['ba_file'] = $ba ? $ba['file_path'] : null;
            $proj['ba_date'] = $ba ? $ba['tanggal_mcu'] : null;

            // Fetch all BA records
            $stmtAllBA = $this->conn->prepare("SELECT tanggal_mcu, status, file_path FROM project_berita_acara WHERE project_id = :pid ORDER BY tanggal_mcu ASC");
            $stmtAllBA->bindParam(":pid", $proj['project_id']);
            $stmtAllBA->execute();
            $proj['ba_records'] = $stmtAllBA->fetchAll(PDO::FETCH_ASSOC);

            // Decode schedule dates
            $sched = $proj['tanggal_mcu'];
            $dates = [];
            if (!empty($sched)) {
                $decoded = json_decode($sched, true);
                if (is_array($decoded)) {
                    $dates = array_map(function($d){ return trim($d); }, $decoded);
                } else {
                    $dates = [trim($sched)];
                }
            }
            $proj['schedule_dates'] = $dates;
        }
        unset($proj);

        $header['linked_projects'] = $linked_projects;

        return $header;
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function update($id, $data, $items) {
        try {
            $this->conn->beginTransaction();

            // 1. Update Header
            $query = "UPDATE " . $this->table_name . " 
                      SET request_number = :req_num,
                          request_date = :req_date, 
                          pic_sales_id = :sales_id,
                          client_company = :company,
                          client_pic = :pic,
                          client_phone = :phone,
                          client_email = :email,
                          invoice_terms = :terms,
                          shipping_address = :addr,
                          notes = :notes,
                          link_gdrive_npwp = :link_npwp,
                          link_gdrive_absensi = :link_absensi
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":req_num", $data['request_number']);
            $stmt->bindParam(":req_date", $data['request_date']);
            $stmt->bindParam(":sales_id", $data['pic_sales_id']);
            $stmt->bindParam(":company", $data['client_company']);
            $stmt->bindParam(":pic", $data['client_pic']);
            $stmt->bindParam(":phone", $data['client_phone']);
            $stmt->bindParam(":email", $data['client_email']);
            $stmt->bindParam(":terms", $data['invoice_terms']);
            $stmt->bindParam(":addr", $data['shipping_address']);
            $stmt->bindParam(":notes", $data['notes']);
            $stmt->bindParam(":link_npwp", $data['link_gdrive_npwp']);
            $stmt->bindParam(":link_absensi", $data['link_gdrive_absensi']);
            $stmt->bindParam(":id", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update invoice request header.");
            }

            // 2. Update Items (Replace All)
            // Delete existing items
            $delQuery = "DELETE FROM " . $this->table_items . " WHERE invoice_request_id = :rid";
            $delStmt = $this->conn->prepare($delQuery);
            $delStmt->bindParam(":rid", $id);
            $delStmt->execute();

            // Insert new items
            $queryItem = "INSERT INTO " . $this->table_items . " 
                          (invoice_request_id, item_description, price, qty, remarks) 
                          VALUES (:rid, :desc, :price, :qty, :remarks)";
            $stmtItem = $this->conn->prepare($queryItem);

            foreach ($items as $item) {
                $stmtItem->bindParam(":rid", $id);
                $stmtItem->bindParam(":desc", $item['item_description']);
                $stmtItem->bindParam(":price", $item['price']);
                $stmtItem->bindParam(":qty", $item['qty']);
                $stmtItem->bindParam(":remarks", $item['remarks']);
                
                if (!$stmtItem->execute()) {
                    throw new Exception("Failed to insert invoice request item.");
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("InvoiceRequest Update Error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            // Delete Items
            $delItems = "DELETE FROM " . $this->table_items . " WHERE invoice_request_id = :id";
            $stmtItems = $this->conn->prepare($delItems);
            $stmtItems->bindParam(":id", $id);
            $stmtItems->execute();
            
            // Delete Project Links
            $delProj = "DELETE FROM invoice_request_projects WHERE invoice_request_id = :id";
            $stmtProj = $this->conn->prepare($delProj);
            $stmtProj->bindParam(":id", $id);
            $stmtProj->execute();

            // Delete Header
            $delHeader = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmtHeader = $this->conn->prepare($delHeader);
            $stmtHeader->bindParam(":id", $id);
            $stmtHeader->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("InvoiceRequest Delete Error: " . $e->getMessage());
            return false;
        }
    }

    public function approveBySales($id, $user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'APPROVED_SALES', 
                      approved_by_sales = :uid, 
                      approved_by_sales_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $user_id);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function approveBySupervisor($id, $user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'APPROVED_SPV', 
                      approved_by_supervisor = :uid, 
                      approved_by_supervisor_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $user_id);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function approveByManager($id, $user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'APPROVED_MANAGER', 
                      approved_by_manager = :uid, 
                      approved_by_manager_at = NOW() 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $user_id);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>
