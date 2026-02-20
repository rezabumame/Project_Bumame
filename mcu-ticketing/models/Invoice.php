<?php
class Invoice {
    private $conn;
    private $table_name = "invoices";
    private $table_items = "invoice_items";
    private $table_requests = "invoice_requests";
    private $table_request_items = "invoice_request_items";
    private $table_request_projects = "invoice_request_projects";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function generateInvoicesFromRequest($request_id, $user_id = null) {
        try {
            $this->conn->beginTransaction();

            // 1. Fetch Request Header
            $queryReq = "SELECT * FROM " . $this->table_requests . " WHERE id = :rid";
            $stmtReq = $this->conn->prepare($queryReq);
            $stmtReq->bindParam(":rid", $request_id);
            $stmtReq->execute();
            $request = $stmtReq->fetch(PDO::FETCH_ASSOC);
            
            if (!$request) throw new Exception("Request not found.");

            // 2. Fetch Items
            $queryItems = "SELECT * FROM " . $this->table_request_items . " WHERE invoice_request_id = :rid";
            $stmtItems = $this->conn->prepare($queryItems);
            $stmtItems->bindParam(":rid", $request_id);
            $stmtItems->execute();
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items)) {
                throw new Exception("No items found in request.");
            }

            // 3. Create Invoice (One Invoice per Request)
            $total_amount = 0;
            foreach ($items as $item) {
                $total_amount += ($item['price'] * $item['qty']);
            }

            $queryInv = "INSERT INTO " . $this->table_name . " 
                         (invoice_request_id, company_name, status, total_amount, created_at) 
                         VALUES (:rid, :comp, 'DRAFT_FINANCE', :total, NOW())";
            $stmtInv = $this->conn->prepare($queryInv);
            $stmtInv->bindParam(":rid", $request_id);
            $stmtInv->bindParam(":comp", $request['client_company']);
            $stmtInv->bindParam(":total", $total_amount);
            
            if (!$stmtInv->execute()) {
                throw new Exception("Failed to create invoice draft.");
            }
            
            $invoice_id = $this->conn->lastInsertId();

            // 4. Insert Invoice Items
            $queryInvItem = "INSERT INTO " . $this->table_items . " 
                             (invoice_id, request_item_id, description, price, qty, total) 
                             VALUES (:iid, :riid, :desc, :price, :qty, :total)";
            $stmtInvItem = $this->conn->prepare($queryInvItem);

            foreach ($items as $item) {
                $total_line = $item['price'] * $item['qty'];
                $stmtInvItem->bindParam(":iid", $invoice_id);
                $stmtInvItem->bindParam(":riid", $item['id']);
                $stmtInvItem->bindParam(":desc", $item['item_description']);
                $stmtInvItem->bindParam(":price", $item['price']);
                $stmtInvItem->bindParam(":qty", $item['qty']);
                $stmtInvItem->bindParam(":total", $total_line);
                
                if (!$stmtInvItem->execute()) {
                    throw new Exception("Failed to insert invoice item.");
                }
            }

            // 5. Update Request Status
            $queryUpd = "UPDATE " . $this->table_requests . " SET status = 'PROCESSED' WHERE id = :rid";
            $stmtUpd = $this->conn->prepare($queryUpd);
            $stmtUpd->bindParam(":rid", $request_id);
            $stmtUpd->execute();

            // 6. Update Project Statuses (Fetch from linked projects table)
            $queryProjs = "SELECT project_id FROM invoice_request_projects WHERE invoice_request_id = :rid";
            $stmtProjs = $this->conn->prepare($queryProjs);
            $stmtProjs->bindParam(":rid", $request_id);
            $stmtProjs->execute();
            $involved_projects = $stmtProjs->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($involved_projects)) {
                // Load Project Model for Logging
                if (!class_exists('Project')) include_once 'Project.php';
                $projectModel = new Project($this->conn);

                $unique_projects = array_unique($involved_projects);
                
                // Only update if current status is NOT already invoiced/paid
                $placeholders = implode(',', array_fill(0, count($unique_projects), '?'));
                $queryProj = "UPDATE projects SET status_project = 'invoice_requested' 
                              WHERE project_id IN ($placeholders) 
                              AND status_project NOT IN ('invoiced', 'paid', 'completed')";
                $stmtProj = $this->conn->prepare($queryProj);
                $stmtProj->execute(array_values($unique_projects));

                // Log Actions
                foreach ($unique_projects as $pid) {
                    if ($user_id) {
                        $projectModel->logAction($pid, 'INVOICE_REQUESTED', $user_id, "Invoice Request #$request_id Submitted");
                    }
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Invoice Generation Error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($role, $page = null, $limit = null, $filters = []) {
        $query = "SELECT i.*, ir.request_number, ir.request_date, ir.client_company 
                  FROM " . $this->table_name . " i
                  JOIN " . $this->table_requests . " ir ON i.invoice_request_id = ir.id";
        
        $where_clauses = [];
        $params = [];

        // Date Range Filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where_clauses[] = "i.invoice_date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $query .= " ORDER BY i.created_at DESC";

        if ($limit !== null && $page !== null) {
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        if ($limit !== null && $page !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt;
    }

    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " i";
        
        $where_clauses = [];
        $params = [];

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where_clauses[] = "i.invoice_date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getById($id) {
        $query = "SELECT i.*, ir.request_number, ir.request_date, ir.client_company, ir.client_pic, ir.client_phone, ir.invoice_terms, ir.shipping_address, ir.notes, ir.link_gdrive_npwp, ir.link_gdrive_absensi 
                  FROM " . $this->table_name . " i
                  JOIN " . $this->table_requests . " ir ON i.invoice_request_id = ir.id
                  WHERE i.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) return false;

        // Get Items
        $queryItems = "SELECT * FROM " . $this->table_items . " WHERE invoice_id = :id";
        $stmtItems = $this->conn->prepare($queryItems);
        $stmtItems->bindParam(":id", $id);
        $stmtItems->execute();
        $invoice['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // Linked Projects & Documents (SPH & BA)
        $queryProjects = "SELECT p.project_id, p.nama_project, p.sph_file, p.tanggal_mcu 
                          FROM " . $this->table_request_projects . " irp
                          JOIN projects p ON irp.project_id = p.project_id
                          WHERE irp.invoice_request_id = :rid";
        $stmtProjects = $this->conn->prepare($queryProjects);
        $stmtProjects->bindParam(":rid", $invoice['invoice_request_id']);
        $stmtProjects->execute();
        $linked_projects = $stmtProjects->fetchAll(PDO::FETCH_ASSOC);

        foreach ($linked_projects as &$proj) {
            // Latest BA
            $stmtBA = $this->conn->prepare("SELECT file_path, tanggal_mcu FROM project_berita_acara WHERE project_id = :pid AND status = 'uploaded' ORDER BY created_at DESC LIMIT 1");
            $stmtBA->bindParam(":pid", $proj['project_id']);
            $stmtBA->execute();
            $ba = $stmtBA->fetch(PDO::FETCH_ASSOC);
            $proj['ba_file'] = $ba ? $ba['file_path'] : null;
            $proj['ba_date'] = $ba ? $ba['tanggal_mcu'] : null;

            // All BA records
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

        $invoice['linked_projects'] = $linked_projects;

        return $invoice;
    }

    public function markAsPaid($id, $payment_date, $payment_notes, $user_id = null) {
        $query = "UPDATE " . $this->table_name . " SET 
                  payment_date = :pay_date,
                  payment_notes = :pay_notes,
                  status = 'PAID',
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pay_date", $payment_date);
        $stmt->bindParam(":pay_notes", $payment_notes);
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            $this->updateProjectStatusFromInvoice($id, 'PAID', $user_id);
            return true;
        }
        return false;
    }

    public function update($data) {
        // Only Finance can update (Invoice Number, Date, Receipt, Status)
        $query = "UPDATE " . $this->table_name . " SET 
                  invoice_number = :inv_num,
                  invoice_date = :inv_date,
                  delivery_receipt_number = :receipt,
                  is_hardcopy_sent = :sent,
                  payment_date = :pay_date,
                  payment_notes = :pay_notes,
                  status = :status,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Handle nulls
        $inv_date = !empty($data['invoice_date']) ? $data['invoice_date'] : null;
        $pay_date = !empty($data['payment_date']) ? $data['payment_date'] : null;
        
        $stmt->bindParam(":inv_num", $data['invoice_number']);
        $stmt->bindParam(":inv_date", $inv_date);
        $stmt->bindParam(":receipt", $data['delivery_receipt_number']);
        $stmt->bindParam(":sent", $data['is_hardcopy_sent']);
        $stmt->bindParam(":pay_date", $pay_date);
        $stmt->bindParam(":pay_notes", $data['payment_notes']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":id", $data['id']);
        
        if ($stmt->execute()) {
            // Trigger Project Status Update if Status Changed
            $user_id = isset($data['user_id']) ? $data['user_id'] : null;
            $this->updateProjectStatusFromInvoice($data['id'], $data['status'], $user_id);
            return true;
        }
        return false;
    }

    public function getSentInvoices() {
        $query = "SELECT i.invoice_number, ir.client_company 
                  FROM " . $this->table_name . " i
                  JOIN " . $this->table_requests . " ir ON i.invoice_request_id = ir.id
                  WHERE i.status = 'SENT' AND i.invoice_number IS NOT NULL AND i.invoice_number != ''
                  ORDER BY i.invoice_number ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByInvoiceNumber($invoice_number) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE invoice_number = :inv_num LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":inv_num", $invoice_number);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProjectStatusFromInvoice($invoice_id, $new_status, $user_id = null) {
        // Logic:
        // If ISSUED/SENT -> Project INVOICED
        // If PAID -> Check if ALL invoices for this project are PAID -> Project COMPLETED
        
        // Load Project Model
        if (!class_exists('Project')) include_once 'Project.php';
        $projectModel = new Project($this->conn);

        // 1. Get Involved Projects (Updated to use invoice_request_projects)
        $query = "SELECT DISTINCT irp.project_id 
                  FROM " . $this->table_name . " i
                  JOIN " . $this->table_request_projects . " irp ON i.invoice_request_id = irp.invoice_request_id
                  WHERE i.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $invoice_id);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($projects)) return;

        foreach ($projects as $pid) {
            if ($new_status == 'ISSUED' || $new_status == 'SENT') {
                // Update to INVOICED
                // Requirement: "statusnya udah sent ... berubah jadi invoced"
                $q = "UPDATE projects SET status_project = 'invoiced' WHERE project_id = :pid AND status_project != 'completed'";
                $s = $this->conn->prepare($q);
                $s->bindParam(":pid", $pid);
                if ($s->execute() && $s->rowCount() > 0 && $user_id) {
                     $projectModel->logAction($pid, 'INVOICED', $user_id, "Invoice #$invoice_id Issued/Sent");
                }
            } elseif ($new_status == 'PAID') {
                // Check if all invoices for this project are PAID
                if ($this->areAllInvoicesPaidForProject($pid)) {
                     // Requirement: "jika 1 id project itu ... sudah paid semua maka statusnya jadi Compeleted"
                     $q = "UPDATE projects SET status_project = 'completed' WHERE project_id = :pid";
                     $s = $this->conn->prepare($q);
                     $s->bindParam(":pid", $pid);
                     if ($s->execute() && $user_id) {
                         $projectModel->logAction($pid, 'COMPLETED', $user_id, "All Invoices Paid. Project Completed.");
                     }
                } else {
                     // Just Log Payment
                     if ($user_id) {
                         $projectModel->logAction($pid, 'PARTIAL_PAYMENT', $user_id, "Invoice #$invoice_id Paid. Pending other invoices.");
                     }
                }
            }
        }
    }

    private function areAllInvoicesPaidForProject($project_id) {
        // Find all invoices linked to this project via invoice_request_projects
        $query = "SELECT COUNT(*) as unpaid_count
                  FROM " . $this->table_name . " i
                  JOIN " . $this->table_request_projects . " irp ON i.invoice_request_id = irp.invoice_request_id
                  WHERE irp.project_id = :pid
                  AND i.status != 'PAID'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pid", $project_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['unpaid_count'] == 0;
    }
}
?>
