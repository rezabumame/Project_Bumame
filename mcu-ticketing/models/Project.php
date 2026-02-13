<?php
class Project {
    private $conn;
    private $table_name = "projects";

    // Properties matching DB columns
    public $project_id;
    public $nama_project;
    public $company_name;
    public $sales_person_id;
    public $jenis_pemeriksaan;
    public $foto_peserta;
    public $lunch;
    public $lunch_notes;
    public $snack;
    public $snack_notes;
    public $header_footer;
    public $total_peserta;
    public $tanggal_mcu;
    public $alamat;
    public $sph_file;
    public $sph_number;
    public $notes;
    public $status_project;
    public $consumption_status;
    public $procurement_lunch_qty;
    public $procurement_snack_qty;
    public $realization_lunch_qty;
    public $realization_snack_qty;
    public $lunch_budget;
    public $snack_budget;
    public $lunch_items;
    public $snack_items;
    public $created_by;
    public $project_type;
    public $clinic_location;
    
    // Approval fields
    public $approved_date_manager;
    public $approved_by_manager;
    public $approved_date_head;
    public $approved_by_head;

    public function __construct($db) {
        $this->conn = $db;
}

    public function isAssignedToSales($project_id, $user_id) {
        $query = "SELECT 1 FROM " . $this->table_name . " p
                  JOIN sales_persons sp ON p.sales_person_id = sp.id
                  WHERE p.project_id = :pid AND sp.user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pid", $project_id);
        $stmt->bindParam(":uid", $user_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function isAssignedToSalesManager($project_id, $user_id) {
        $query = "SELECT 1 FROM " . $this->table_name . " p
                  JOIN sales_persons sp ON p.sales_person_id = sp.id
                  JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                  WHERE p.project_id = :pid AND sm.user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pid", $project_id);
        $stmt->bindParam(":uid", $user_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function checkIdExists($id) {
        $query = "SELECT project_id FROM " . $this->table_name . " WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function hasAccess($user_id, $role, $project_id) {
        // Superusers & Admin Roles
        if (in_array($role, ['superadmin', 'ceo', 'manager_ops', 'head_ops', 'admin_ops', 'admin_sales', 'dw_tim_hasil', 'surat_hasil'])) {
            return true;
        }

        // Korlap specific check
        if ($role == 'korlap') {
            $query = "SELECT project_id FROM " . $this->table_name . " WHERE project_id = :project_id AND korlap_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":project_id", $project_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        }

        return false;
    }

    public function getById($id) {
        $query = "SELECT p.*, sp.sales_name, k.full_name as korlap_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                  WHERE p.project_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBySph($sph) {
        $query = "SELECT p.*, sp.sales_name, k.full_name as korlap_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                  WHERE p.sph_number = :sph
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sph", $sph);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                (project_id, nama_project, company_name, sales_person_id, jenis_pemeriksaan, foto_peserta, lunch, lunch_notes, snack, snack_notes, header_footer, total_peserta, tanggal_mcu, alamat, sph_file, sph_number, notes, status_project, created_by, project_type, clinic_location, procurement_lunch_qty, procurement_snack_qty, lunch_budget, snack_budget, lunch_items, snack_items, approved_by_manager, approved_date_manager, approved_by_head, approved_date_head) 
                VALUES 
                (:project_id, :nama_project, :company_name, :sales_person_id, :jenis_pemeriksaan, :foto_peserta, :lunch, :lunch_notes, :snack, :snack_notes, :header_footer, :total_peserta, :tanggal_mcu, :alamat, :sph_file, :sph_number, :notes, :status_project, :created_by, :project_type, :clinic_location, :procurement_lunch_qty, :procurement_snack_qty, :lunch_budget, :snack_budget, :lunch_items, :snack_items, :approved_by_manager, :approved_date_manager, :approved_by_head, :approved_date_head)";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind
        $this->project_id = htmlspecialchars(strip_tags($this->project_id));
        $this->nama_project = htmlspecialchars(strip_tags($this->nama_project));
        
        $stmt->bindParam(":project_id", $this->project_id);
        $stmt->bindParam(":nama_project", $this->nama_project);
        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":sales_person_id", $this->sales_person_id);
        $stmt->bindParam(":jenis_pemeriksaan", $this->jenis_pemeriksaan);
        $stmt->bindParam(":foto_peserta", $this->foto_peserta);
        $stmt->bindParam(":lunch", $this->lunch);
        $stmt->bindParam(":lunch_notes", $this->lunch_notes);
        $stmt->bindParam(":snack", $this->snack);
        $stmt->bindParam(":snack_notes", $this->snack_notes);
        $stmt->bindParam(":header_footer", $this->header_footer);
        $stmt->bindParam(":total_peserta", $this->total_peserta);
        $stmt->bindParam(":tanggal_mcu", $this->tanggal_mcu);
        $stmt->bindParam(":alamat", $this->alamat);
        $stmt->bindParam(":sph_file", $this->sph_file);
        $stmt->bindParam(":sph_number", $this->sph_number);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":status_project", $this->status_project);
        $stmt->bindParam(":created_by", $this->created_by);
        $stmt->bindParam(":project_type", $this->project_type);
        $stmt->bindParam(":clinic_location", $this->clinic_location);
        $stmt->bindParam(":procurement_lunch_qty", $this->procurement_lunch_qty);
        $stmt->bindParam(":procurement_snack_qty", $this->procurement_snack_qty);
        $stmt->bindParam(":lunch_budget", $this->lunch_budget);
        $stmt->bindParam(":snack_budget", $this->snack_budget);
        $stmt->bindParam(":lunch_items", $this->lunch_items);
        $stmt->bindParam(":snack_items", $this->snack_items);
        $stmt->bindParam(":approved_by_manager", $this->approved_by_manager);
        $stmt->bindParam(":approved_date_manager", $this->approved_date_manager);
        $stmt->bindParam(":approved_by_head", $this->approved_by_head);
        $stmt->bindParam(":approved_date_head", $this->approved_date_head);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        // Validation: Prevent Manual Completion if Invoice Request exists
        if ($this->status_project == 'completed') {
             if ($this->hasInvoiceRequest($this->project_id)) {
                 return false;
             }
        }

        $query = "UPDATE " . $this->table_name . "
                SET
                    nama_project = :nama_project,
                    company_name = :company_name,
                    sales_person_id = :sales_person_id,
                    jenis_pemeriksaan = :jenis_pemeriksaan,
                    foto_peserta = :foto_peserta,
                    lunch = :lunch,
                    lunch_notes = :lunch_notes,
                    snack = :snack,
                    snack_notes = :snack_notes,
                    header_footer = :header_footer,
                    total_peserta = :total_peserta,
                    tanggal_mcu = :tanggal_mcu,
                    alamat = :alamat,
                    sph_number = :sph_number,
                    notes = :notes,
                    status_project = :status_project,
                    project_type = :project_type,
                    clinic_location = :clinic_location,
                    procurement_lunch_qty = :procurement_lunch_qty,
                    procurement_snack_qty = :procurement_snack_qty,
                    lunch_budget = :lunch_budget,
                    snack_budget = :snack_budget,
                    lunch_items = :lunch_items,
                    snack_items = :snack_items";
        
        if ($this->sph_file) {
            $query .= ", sph_file = :sph_file";
        }
        
        $query .= " WHERE project_id = :project_id";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $this->nama_project = htmlspecialchars(strip_tags($this->nama_project));
        $this->company_name = htmlspecialchars(strip_tags($this->company_name));
        
        // Handle nullable integers
        if (!empty($this->sales_person_id)) {
            $this->sales_person_id = htmlspecialchars(strip_tags($this->sales_person_id));
        } else {
            $this->sales_person_id = null;
        }

        $this->jenis_pemeriksaan = htmlspecialchars(strip_tags($this->jenis_pemeriksaan));
        $this->foto_peserta = htmlspecialchars(strip_tags($this->foto_peserta));
        $this->lunch = htmlspecialchars(strip_tags($this->lunch));
        $this->lunch_notes = htmlspecialchars(strip_tags($this->lunch_notes));
        $this->snack = htmlspecialchars(strip_tags($this->snack));
        $this->snack_notes = htmlspecialchars(strip_tags($this->snack_notes));
        $this->header_footer = htmlspecialchars(strip_tags($this->header_footer));
        $this->total_peserta = htmlspecialchars(strip_tags($this->total_peserta));
        $this->tanggal_mcu = $this->tanggal_mcu; // JSON encoded
        $this->alamat = htmlspecialchars(strip_tags($this->alamat));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->status_project = htmlspecialchars(strip_tags($this->status_project));
        $this->project_id = htmlspecialchars(strip_tags($this->project_id));
        $this->project_type = htmlspecialchars(strip_tags($this->project_type));
        $this->clinic_location = htmlspecialchars(strip_tags($this->clinic_location));
        
        $stmt->bindParam(":nama_project", $this->nama_project);
        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":sales_person_id", $this->sales_person_id);
        $stmt->bindParam(":jenis_pemeriksaan", $this->jenis_pemeriksaan);
        $stmt->bindParam(":foto_peserta", $this->foto_peserta);
        $stmt->bindParam(":lunch", $this->lunch);
        $stmt->bindParam(":lunch_notes", $this->lunch_notes);
        $stmt->bindParam(":snack", $this->snack);
        $stmt->bindParam(":snack_notes", $this->snack_notes);
        $stmt->bindParam(":header_footer", $this->header_footer);
        $stmt->bindParam(":total_peserta", $this->total_peserta);
        $stmt->bindParam(":tanggal_mcu", $this->tanggal_mcu);
        $stmt->bindParam(":alamat", $this->alamat);
        $stmt->bindParam(":sph_number", $this->sph_number);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":status_project", $this->status_project);
        $stmt->bindParam(":project_id", $this->project_id);
        $stmt->bindParam(":project_type", $this->project_type);
        $stmt->bindParam(":clinic_location", $this->clinic_location);
        $stmt->bindParam(":procurement_lunch_qty", $this->procurement_lunch_qty);
        $stmt->bindParam(":procurement_snack_qty", $this->procurement_snack_qty);
        $stmt->bindParam(":lunch_budget", $this->lunch_budget);
        $stmt->bindParam(":snack_budget", $this->snack_budget);
        $stmt->bindParam(":lunch_items", $this->lunch_items);
        $stmt->bindParam(":snack_items", $this->snack_items);
        
        if ($this->sph_file) {
             $stmt->bindParam(":sph_file", $this->sph_file);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateSph($project_id = null, $sph_file = null) {
        if ($project_id) $this->project_id = $project_id;
        if ($sph_file) $this->sph_file = $sph_file;

        $query = "UPDATE " . $this->table_name . " SET sph_file = :sph_file WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        
        $this->sph_file = htmlspecialchars(strip_tags($this->sph_file));
        $this->project_id = htmlspecialchars(strip_tags($this->project_id));
        
        $stmt->bindParam(":sph_file", $this->sph_file);
        $stmt->bindParam(":project_id", $this->project_id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll($role, $user_id = null) {
        $query = "SELECT p.*, sp.sales_name, k.full_name as korlap_name, tm.id as tm_id 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                  LEFT JOIN technical_meetings tm ON p.project_id = tm.project_id";
        
        $whereClause = "";
        
        if ($role == 'sales') {
            $whereClause = " WHERE sp.user_id = :user_id ";
        } elseif ($role == 'manager_sales') {
            $whereClause = " WHERE sm.user_id = :user_id ";
        } elseif ($role == 'dw_tim_hasil') {
            $whereClause = " WHERE EXISTS (SELECT 1 FROM medical_results mr JOIN medical_result_items mri ON mr.id = mri.medical_result_id WHERE mr.project_id = p.project_id AND mri.assigned_to_user_id = :user_id) ";
        }

        $query .= $whereClause;
        $query .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($role == 'sales' || $role == 'manager_sales' || $role == 'dw_tim_hasil') {
             $stmt->bindParam(':user_id', $user_id);
        }
        $stmt->execute();
        return $stmt;
    }

    public function readByStatus($status, $limit = null) {
        $role = $_SESSION['role'] ?? '';
        $user_id = $_SESSION['user_id'] ?? 0;

        $query = "SELECT p.*, sp.sales_name, tm.id as tm_id,
                     (SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                      FROM medical_result_items mri_sub
                      JOIN medical_results mr2 ON mri_sub.medical_result_id = mr2.id
                      JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                      WHERE mr2.project_id = p.project_id) as kohas_names 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                  LEFT JOIN technical_meetings tm ON p.project_id = tm.project_id
                  WHERE p.status_project = :status";
        
        $params = [':status' => $status];

        if ($role == 'sales') {
            $query .= " AND sp.user_id = :user_id";
            $params[':user_id'] = $user_id;
        } elseif ($role == 'manager_sales') {
            $query .= " AND sm.user_id = :user_id";
            $params[':user_id'] = $user_id;
        } elseif ($role == 'dw_tim_hasil') {
            $query .= " AND EXISTS (SELECT 1 FROM medical_results mr JOIN medical_result_items mri ON mr.id = mri.medical_result_id WHERE mr.project_id = p.project_id AND mri.assigned_to_user_id = :user_id) ";
            $params[':user_id'] = $user_id;
        }

        $query .= " ORDER BY p.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt;
    }



    public function getStatistics($korlap_id = null, $role = null, $user_id = null) {
        // Build base query
        $query_total = "SELECT COUNT(DISTINCT p.project_id) as count FROM " . $this->table_name . " p";
        $query_need_approval = "SELECT COUNT(DISTINCT p.project_id) as count FROM " . $this->table_name . " p WHERE p.status_project LIKE 'need_approval%'";
        $query_completed = "SELECT COUNT(DISTINCT p.project_id) as count FROM " . $this->table_name . " p WHERE p.status_project = 'completed'";
        
        $joinClause = "";
        $whereClause = "";

        // Add Joins for Role Filtering
        if ($role == 'sales') {
            $joinClause .= " JOIN sales_persons sp ON p.sales_person_id = sp.id ";
            $whereClause .= " AND sp.user_id = :user_id ";
        } elseif ($role == 'manager_sales') {
            $joinClause .= " JOIN sales_persons sp ON p.sales_person_id = sp.id ";
            $joinClause .= " JOIN sales_managers sm ON sp.sales_manager_id = sm.id ";
            $whereClause .= " AND sm.user_id = :user_id ";
        } elseif ($role == 'dw_tim_hasil') {
            $whereClause .= " AND EXISTS (SELECT 1 FROM medical_results mr JOIN medical_result_items mri ON mr.id = mri.medical_result_id WHERE mr.project_id = p.project_id AND mri.assigned_to_user_id = :user_id) ";
        }

        // Add Korlap Filter
        if ($korlap_id) {
            $whereClause .= " AND p.korlap_id = :korlap_id";
        }
        
        $query_total .= $joinClause . " WHERE 1=1 " . $whereClause;
        $query_need_approval .= $joinClause . $whereClause; // WHERE clause already starts with WHERE in base query
        $query_completed .= $joinClause . $whereClause;

        // Fix query_need_approval and query_completed to use AND correctly
        // Base: WHERE status...
        // We append AND ...
        
        $stats = [];

        // Total
        $stmt = $this->conn->prepare($query_total);
        if ($korlap_id) $stmt->bindParam(":korlap_id", $korlap_id);
        if (($role == 'sales' || $role == 'manager_sales' || $role == 'dw_tim_hasil') && $user_id) $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Need Approval
        // Reconstruct query correctly
        $query_need_approval = "SELECT COUNT(DISTINCT p.project_id) as count FROM " . $this->table_name . " p " . $joinClause . " WHERE p.status_project LIKE 'need_approval%'" . $whereClause;
        $stmt = $this->conn->prepare($query_need_approval);
        if ($korlap_id) $stmt->bindParam(":korlap_id", $korlap_id);
        if (($role == 'sales' || $role == 'manager_sales' || $role == 'dw_tim_hasil') && $user_id) $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $stats['need_approval'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Completed
        $query_completed = "SELECT COUNT(DISTINCT p.project_id) as count FROM " . $this->table_name . " p " . $joinClause . " WHERE p.status_project = 'completed'" . $whereClause;
        $stmt = $this->conn->prepare($query_completed);
        if ($korlap_id) $stmt->bindParam(":korlap_id", $korlap_id);
        if (($role == 'sales' || $role == 'manager_sales' || $role == 'dw_tim_hasil') && $user_id) $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $stats['completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Breakdown
        $query_breakdown = "SELECT p.status_project, COUNT(DISTINCT p.project_id) as count FROM " . $this->table_name . " p " . $joinClause . " WHERE 1=1 " . $whereClause . " GROUP BY p.status_project";
        
        $stmt = $this->conn->prepare($query_breakdown);
        if ($korlap_id) $stmt->bindParam(":korlap_id", $korlap_id);
        if (($role == 'sales' || $role == 'manager_sales' || $role == 'dw_tim_hasil') && $user_id) $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        $stats['status_breakdown'] = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['status_breakdown'][$row['status_project']] = $row['count'];
        }

        return $stats;
    }

    public function getProcurementStats() {
        $stats = [
            'process_vendor' => 0,
            'need_consumption' => 0
        ];
        
        // Process Vendor (Need Vendor Assignment)
        // New Logic: status_vendor = 'requested'
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE status_vendor = 'requested'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['process_vendor'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Need Consumption Approval
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE (lunch = 'Ya' OR snack = 'Ya') 
                  AND (consumption_status IS NULL OR consumption_status != 'approved')
                  AND status_project NOT IN ('rejected', 'cancelled')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['need_consumption'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return $stats;
    }

    public function getAllForCalendar($korlap_id = null, $role = null, $user_id = null) {
        $select = "SELECT p.project_id, p.nama_project, p.tanggal_mcu, p.status_project, p.lunch, p.lunch_notes, p.snack, p.snack_notes";
        
        if ($role == 'dw_tim_hasil') {
            $select = "SELECT DISTINCT p.project_id, p.nama_project, p.tanggal_mcu, p.status_project, p.lunch, p.lunch_notes, p.snack, p.snack_notes";
        }
        
        $query = $select . " FROM " . $this->table_name . " p";
        
        $joinClause = "";
        $whereClause = "";

        if ($role == 'sales') {
            $joinClause .= " JOIN sales_persons sp ON p.sales_person_id = sp.id ";
            $whereClause .= " AND sp.user_id = :user_id ";
        } elseif ($role == 'manager_sales') {
            $joinClause .= " JOIN sales_persons sp ON p.sales_person_id = sp.id ";
            $joinClause .= " JOIN sales_managers sm ON sp.sales_manager_id = sm.id ";
            $whereClause .= " AND sm.user_id = :user_id ";
        } elseif ($role == 'dw_tim_hasil') {
            $joinClause .= " JOIN medical_results mr ON p.project_id = mr.project_id ";
            $joinClause .= " JOIN medical_result_items mri ON mr.id = mri.medical_result_id ";
            $whereClause .= " AND mri.assigned_to_user_id = :user_id ";
        }

        if ($korlap_id) {
            $whereClause .= " AND p.korlap_id = :korlap_id";
        }
        
        $query .= $joinClause . " WHERE 1=1 " . $whereClause;

        $stmt = $this->conn->prepare($query);
        if ($korlap_id) $stmt->bindParam(":korlap_id", $korlap_id);
        if (($role == 'sales' || $role == 'manager_sales' || $role == 'dw_tim_hasil') && $user_id) $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt;
    }

    public function getVendorStats() {
        // Breakdown by status_vendor
        $query = "SELECT status_vendor, COUNT(*) as count FROM " . $this->table_name . " GROUP BY status_vendor";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $counts = [
            'pending' => 0,
            'requested' => 0,
            'assigned' => 0,
            'no_vendor_needed' => 0
        ];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['status_vendor'] ?: 'pending';
            $counts[$status] = $row['count'];
        }
        
        return [
            'assigned' => $counts['assigned'],
            'no_vendor_needed' => $counts['no_vendor_needed'],
            'requested' => $counts['requested'],
            'pending' => $counts['pending'],
            // Legacy support
            'with_vendor' => $counts['assigned'], 
            'without_vendor' => $counts['no_vendor_needed']
        ];
    }

    public function getMonthlyProjectStats($korlap_id = null, $role = null, $user_id = null) {
        $stats = array_fill(1, 12, 0); // Initialize Jan-Dec with 0
        $year = date('Y');
        
        $query = "SELECT MONTH(p.created_at) as month, COUNT(*) as count 
                  FROM " . $this->table_name . " p";

        $joinClause = "";
        $whereClause = "";

        if ($role == 'sales') {
            $joinClause .= " JOIN sales_persons sp ON p.sales_person_id = sp.id ";
            $whereClause .= " AND sp.user_id = :user_id ";
        } elseif ($role == 'manager_sales') {
            $joinClause .= " JOIN sales_persons sp ON p.sales_person_id = sp.id ";
            $joinClause .= " JOIN sales_managers sm ON sp.sales_manager_id = sm.id ";
            $whereClause .= " AND sm.user_id = :user_id ";
        } elseif ($role == 'dw_tim_hasil') {
            $whereClause .= " AND EXISTS (SELECT 1 FROM medical_results mr JOIN medical_result_items mri ON mr.id = mri.medical_result_id WHERE mr.project_id = p.project_id AND mri.assigned_to_user_id = :user_id) ";
        }

        $query .= $joinClause . " WHERE YEAR(p.created_at) = :year" . $whereClause;
        
        if ($korlap_id) {
            $query .= " AND p.korlap_id = :korlap_id";
        }
        
        $query .= " GROUP BY MONTH(p.created_at)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":year", $year);
        if ($korlap_id) $stmt->bindParam(":korlap_id", $korlap_id);
        if (($role == 'sales' || $role == 'manager_sales' || $role == 'dw_tim_hasil') && $user_id) $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['month']] = $row['count'];
        }
        
        return $stats;
    }

    // getAllProjects moved to line 1356 to support more roles


    public function updateStatus($id, $status, $user_id, $role, $reason = null) {
        // Validation: Prevent Manual Completion if Invoice Request exists
        if ($status == 'completed') {
             if ($this->hasInvoiceRequest($id)) {
                 // Cannot manually complete a project that has invoice requests
                 return false;
             }
        }

        $query = "UPDATE " . $this->table_name . " SET status_project = :status";
        
        if ($role == 'manager_ops' && $status != 'rejected' && $status != 'cancelled' && $status != 're-nego') {
            $query .= ", approved_date_manager = NOW(), approved_by_manager = :user_id";
        } elseif ($role == 'head_ops' && $status != 'rejected' && $status != 'cancelled' && $status != 're-nego') {
            $query .= ", approved_date_head = NOW(), approved_by_head = :user_id";
        }

        if (($status == 'rejected' || $status == 're-nego') && $reason) {
            $query .= ", reject_reason = :reason";
        }

        $query .= " WHERE project_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        if (strpos($query, ':user_id') !== false) {
            $stmt->bindParam(":user_id", $user_id);
        }
        if (strpos($query, ':reason') !== false) {
            $stmt->bindParam(":reason", $reason);
        }

        if ($stmt->execute()) {
            // Log the status change
            $action = "Status Changed to " . ucfirst(str_replace('_', ' ', $status));
            $this->logAction($id, $action, $user_id, $reason);
            return true;
        }
        return false;
    }

    public function checkAndSetInProgressOps($project_id, $reason = 'Auto-update to IN PROGRESS OPS triggered by activity') {
        $query = "SELECT status_project FROM " . $this->table_name . " WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $status = $row['status_project'];
            // Rule: Change if status is APPROVED
            // Triggered by: RAB, TM, Warehouse Request, Medical Result
            if ($status == 'approved') {
                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; 
                // Use updateStatus to log action
                $this->updateStatus($project_id, 'in_progress_ops', $user_id, 'system', $reason);
                return true;
            }
        }
        return false;
    }

    public function getBeritaAcara($project_id) {
        $query = "SELECT * FROM project_berita_acara WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['tanggal_mcu']] = $row;
        }
        return $results;
    }

    public function uploadBeritaAcara($project_id, $date, $file_path, $user_id) {
        // Upsert logic
        $query = "INSERT INTO project_berita_acara (project_id, tanggal_mcu, file_path, created_by, status, created_at) 
                  VALUES (:project_id, :date, :file_path, :user_id, 'uploaded', NOW())
                  ON DUPLICATE KEY UPDATE 
                  file_path = :file_path_update, 
                  created_by = :user_id_update, 
                  status = 'uploaded', 
                  created_at = NOW(),
                  cancel_reason = NULL";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":file_path", $file_path);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":file_path_update", $file_path);
        $stmt->bindParam(":user_id_update", $user_id);
        
        if ($stmt->execute()) {
            $this->logAction($project_id, 'Berita Acara Uploaded', $user_id, "Date: $date");
            return true;
        }
        return false;
    }

    public function cancelBeritaAcara($project_id, $date, $reason, $user_id) {
        $query = "INSERT INTO project_berita_acara (project_id, tanggal_mcu, status, cancel_reason, created_by, created_at) 
                  VALUES (:project_id, :date, 'cancelled', :reason, :user_id, NOW())
                  ON DUPLICATE KEY UPDATE 
                  status = 'cancelled', 
                  cancel_reason = :reason_update, 
                  created_by = :user_id_update,
                  created_at = NOW()";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":reason", $reason);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":reason_update", $reason);
        $stmt->bindParam(":user_id_update", $user_id);
        
        if ($stmt->execute()) {
            $this->logAction($project_id, 'Berita Acara Cancelled', $user_id, "Date: $date, Reason: $reason");
            return true;
        }
        return false;
    }

    public function deleteBeritaAcara($project_id, $date) {
        $query = "DELETE FROM project_berita_acara WHERE project_id = :project_id AND tanggal_mcu = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->bindParam(":date", $date);
        return $stmt->execute();
    }



    public function getTechnicalMeeting($project_id) {
        $query = "SELECT * FROM technical_meetings WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkCompletionStatus($project_id, $user_id) {
        // Logic to check if all dates have BA uploaded/cancelled
        
        // 1. Get all dates
        $project = $this->getProjectById($project_id);
        if (!$project) return false;

        $dates = json_decode($project['tanggal_mcu'], true);
        if (!is_array($dates)) {
            // Handle legacy single date or empty
            if (!empty($project['tanggal_mcu'])) {
                $dates = [$project['tanggal_mcu']];
            } else {
                return false;
            }
        }
        
        // 2. Get all BA statuses
        $ba_statuses = $this->getBeritaAcara($project_id);
        
        $all_done = true;
        foreach ($dates as $date) {
            $date = trim($date); // Trim whitespace
            if (!isset($ba_statuses[$date])) {
                $all_done = false;
                break;
            }
        }
        
        if (!$all_done) {
            return 'pending';
        }

        // All done (entry exists for every date)
        // Check if ANY date is 'cancelled' (unverified)
        $any_unverified_cancelled = false;
        foreach ($ba_statuses as $status) {
            if ($status['status'] == 'cancelled') {
                $any_unverified_cancelled = true;
                break;
            }
        }

        if ($any_unverified_cancelled) {
             // Logic changed: No approval needed for cancellations.
             // If all dates are accounted for (uploaded or cancelled), it is COMPLETED.
             // User Request: Don't auto-complete. Keep existing status.
             /*
             if ($project['status_project'] != 'completed') {
                 $this->updateStatus($project_id, 'completed', $user_id, 'korlap', 'Berita Acara finished (with cancellations)');
                 return 'completed';
             }
             */
        } else {
             // All uploaded OR cancelled_approved -> Completed
             // User Request: Don't auto-complete. Keep existing status.
             /*
             if ($project['status_project'] != 'completed') {
                 $this->updateStatus($project_id, 'completed', $user_id, 'korlap', 'All Berita Acara uploaded/verified successfully');
                 return 'completed';
             }
             */
        }

        return $project['status_project'];
    }

    public function getProjectCountByDate($date) {
        // Since tanggal_mcu is JSON array of strings ["2024-01-01", "2024-01-02"]
        // We need to check if $date exists in the JSON array
        // Optimized to use JSON_CONTAINS
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE JSON_CONTAINS(tanggal_mcu, :date_json)";
        
        $stmt = $this->conn->prepare($query);
        $date_json = '"' . $date . '"';
        $stmt->bindParam(":date_json", $date_json);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['count'];
        }
        return 0;
    }

    public function logAction($project_id, $action, $user_id, $notes = null, $role = null) {
        if ($role === null && isset($_SESSION['role'])) {
            $role = $_SESSION['role'];
        }
        
        // Fallback if role is still missing (e.g. cron job or system action)
        if ($role === null) {
            // Try to fetch from user_id
            $query_role = "SELECT role FROM users WHERE user_id = :user_id";
            $stmt_role = $this->conn->prepare($query_role);
            $stmt_role->bindParam(":user_id", $user_id);
            $stmt_role->execute();
            $row = $stmt_role->fetch(PDO::FETCH_ASSOC);
            $role = $row ? $row['role'] : 'system';
        }

        $query = "INSERT INTO project_logs (project_id, action, actor_id, role, notes) VALUES (:project_id, :action, :user_id, :role, :notes)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":notes", $notes);
        return $stmt->execute();
    }

    public function updateScheduleDate($project_id, $old_date, $new_date) {
        // 1. Get current dates
        $query = "SELECT tanggal_mcu FROM " . $this->table_name . " WHERE project_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $project_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) return false;
        
        $dates = json_decode($row['tanggal_mcu'], true);
        if (!$dates) $dates = [$row['tanggal_mcu']]; // Legacy single string
        
        // 2. Find and update
        $found = false;
        foreach ($dates as $key => $d) {
            if ($d == $old_date) {
                $dates[$key] = $new_date;
                $found = true;
                break;
            }
        }
        
        if (!$found) return false;
        
        // 3. Save back
        $new_dates_json = json_encode(array_values($dates)); // array_values to keep it list
        $query_update = "UPDATE " . $this->table_name . " SET tanggal_mcu = :dates WHERE project_id = :id";
        $stmt_update = $this->conn->prepare($query_update);
        $stmt_update->bindParam(":dates", $new_dates_json);
        $stmt_update->bindParam(":id", $project_id);
        
        return $stmt_update->execute();
    }

    public function getHistory($id) {
        $history = [];

        // 1. Fetch from project_logs
        $query = "SELECT l.*, u.full_name as user_name, l.role as user_role 
                  FROM project_logs l 
                  LEFT JOIN users u ON l.actor_id = u.user_id 
                  WHERE l.project_id = :id 
                  ORDER BY l.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $history[] = [
                'status_to' => $row['action'],
                'changed_at' => $row['created_at'],
                'changed_by_name' => ($row['user_name'] ?? 'Unknown') . ' (' . ucfirst(str_replace('_', ' ', $row['user_role'] ?? '')) . ')',
                'notes' => $row['notes']
            ];
        }

        // 2. Fetch Legacy Milestones
        $query = "SELECT created_at, approved_date_manager, approved_date_head, status_project, created_by FROM " . $this->table_name . " WHERE project_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            if (!empty($project['created_at'])) {
                $already_logged = false;
                foreach ($history as $h) {
                    if (strpos($h['status_to'], 'Created') !== false && abs(strtotime($h['changed_at']) - strtotime($project['created_at'])) < 60) {
                        $already_logged = true; 
                        break;
                    }
                }
                
                if (!$already_logged) {
                    $creator_name = 'System/Admin';
                    if (!empty($project['created_by'])) {
                         $q = "SELECT full_name, role FROM users WHERE user_id = :uid";
                         $s = $this->conn->prepare($q);
                         $s->bindParam(":uid", $project['created_by']);
                         $s->execute();
                         if ($u = $s->fetch(PDO::FETCH_ASSOC)) {
                             $creator_name = $u['full_name'] . ' (' . ucfirst(str_replace('_', ' ', $u['role'])) . ')';
                         }
                    }

                    $history[] = [
                        'status_to' => 'Project Created',
                        'changed_at' => $project['created_at'],
                        'changed_by_name' => $creator_name,
                        'notes' => 'Initial creation'
                    ];
                }
            }
        }
        
        usort($history, function($a, $b) {
            return strtotime($b['changed_at']) - strtotime($a['changed_at']);
        });

        return $history;
    }

    public function getUrgentProjects($role) {
        $status_filter = "";
        if ($role == 'manager_ops' || $role == 'admin_ops' || $role == 'superadmin') {
            $status_filter = "status_project = 'need_approval_manager'";
        } elseif ($role == 'head_ops') {
            $status_filter = "status_project = 'need_approval_head'";
        } else {
            return [];
        }

        $query = "SELECT project_id, nama_project, tanggal_mcu, status_project, created_at, approved_date_manager FROM " . $this->table_name . " WHERE " . $status_filter;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $urgent_projects = [];
        
        if (!class_exists('DateHelper')) {
            $helper_path = __DIR__ . '/../helpers/DateHelper.php';
            if (file_exists($helper_path)) {
                include_once $helper_path;
            } else {
                return []; 
            }
        }
        
        // Fetch SLA Setting
        $sla_days = 1; // Default 1 day
        if (class_exists('SystemSetting')) {
            $setting = new SystemSetting($this->conn);
            $val = $setting->get('approval_sla_days');
            if ($val !== null) {
                $sla_days = intval($val);
            }
        } else {
            // Try to include SystemSetting if not loaded
             $setting_path = __DIR__ . '/SystemSetting.php';
             if (file_exists($setting_path)) {
                 include_once $setting_path;
                 $setting = new SystemSetting($this->conn);
                 $val = $setting->get('approval_sla_days');
                 if ($val !== null) {
                     $sla_days = intval($val);
                 }
             }
        }

        // Fetch Holidays for SLA calculation
        $holidays = [];
        if (!class_exists('NationalHoliday')) {
             $holiday_path = __DIR__ . '/NationalHoliday.php';
             if (file_exists($holiday_path)) {
                 include_once $holiday_path;
             }
        }
        if (class_exists('NationalHoliday')) {
            $nh = new NationalHoliday($this->conn);
            $holidays = $nh->getHolidayDates();
        }

        foreach ($projects as $p) {
            $is_urgent = false;

            // 1. Existing H-1 Logic (Deadline based)
            $dates = json_decode($p['tanggal_mcu'], true);
            $target_date = null;
            if (is_array($dates) && !empty($dates)) {
                sort($dates);
                $target_date = $dates[0];
            } elseif (!empty($p['tanggal_mcu']) && !is_array($dates)) {
                $target_date = $p['tanggal_mcu'];
            }

            if ($target_date) {
                $working_days = DateHelper::countWorkingDays(date('Y-m-d'), $target_date, $holidays);
                if ($working_days <= 1) { 
                    $is_urgent = true;
                }
            }
            
            // 2. SLA Logic (Working Days based)
            if (!$is_urgent) {
                $start_time = null;
                if ($p['status_project'] == 'need_approval_manager') {
                    $start_time = $p['created_at'];
                } elseif ($p['status_project'] == 'need_approval_head') {
                    $start_time = !empty($p['approved_date_manager']) ? $p['approved_date_manager'] : $p['created_at'];
                }
                
                if ($start_time) {
                    $start_date = date('Y-m-d', strtotime($start_time));
                    $today = date('Y-m-d');
                    
                    // Only check if start date is not today
                    if ($start_date != $today) {
                        $working_days_elapsed = DateHelper::countWorkingDays($start_date, $today, $holidays);
                        
                        if ($working_days_elapsed > $sla_days) {
                            $is_urgent = true;
                        } elseif ($working_days_elapsed == $sla_days) {
                            // If exactly on the SLA day (e.g. 1 day later), check if time has passed
                            // This implements the "24 hours" logic
                            $start_time_only = date('H:i:s', strtotime($start_time));
                            $current_time_only = date('H:i:s');
                            if ($current_time_only >= $start_time_only) {
                                $is_urgent = true;
                            }
                        }
                    }
                }
            }

            if ($is_urgent) {
                $urgent_projects[] = $p;
            }
        }
        
        return $urgent_projects;
    }




    
    public function getProjectById($id) {
         $query = "SELECT p.*, sp.sales_name, k.full_name as korlap_name, k.jabatan as korlap_jabatan 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                  WHERE p.project_id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readWithFilters($search, $status, $date_from, $date_to, $limit, $offset, $korlap_id = null, $role = null, $user_id = null) {
        $query = "SELECT p.*, sp.sales_name, k.full_name as korlap_name, tm.id as tm_id,
                         (SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                          FROM medical_result_items mri_sub
                          JOIN medical_results mr2 ON mri_sub.medical_result_id = mr2.id
                          JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                          WHERE mr2.project_id = p.project_id) as kohas_names 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id 
                  LEFT JOIN technical_meetings tm ON p.project_id = tm.project_id
                  WHERE 1=1";
        
        if ($korlap_id) {
            $query .= " AND p.korlap_id = :korlap_id";
        }
        
        if ($role == 'sales' && $user_id) {
            $query .= " AND sp.user_id = :user_id";
        } elseif ($role == 'manager_sales' && $user_id) {
            $query .= " AND sm.user_id = :user_id";
        } elseif ($role == 'dw_tim_hasil' && $user_id) {
            $query .= " AND EXISTS (SELECT 1 FROM medical_results mr JOIN medical_result_items mri ON mr.id = mri.medical_result_id WHERE mr.project_id = p.project_id AND mri.assigned_to_user_id = :user_id) ";
        }

        if (!empty($search)) {
            $query .= " AND (p.nama_project LIKE :search OR p.company_name LIKE :search OR p.project_id LIKE :search)";
        }
        if (!empty($status)) {
            $query .= " AND p.status_project = :status";
        }
        if (!empty($date_from)) {
            $query .= " AND DATE(p.created_at) >= :date_from";
        }
        if (!empty($date_to)) {
            $query .= " AND DATE(p.created_at) <= :date_to";
        }

        $query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($korlap_id) {
            $stmt->bindParam(":korlap_id", $korlap_id);
        }
        
        if (($role == 'sales' || $role == 'manager_sales' || $role == 'dw_tim_hasil') && $user_id) {
            $stmt->bindParam(":user_id", $user_id);
        }

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(":search", $search_term);
        }
        if (!empty($status)) {
            $stmt->bindParam(":status", $status);
        }
        if (!empty($date_from)) {
            $stmt->bindParam(":date_from", $date_from);
        }
        if (!empty($date_to)) {
            $stmt->bindParam(":date_to", $date_to);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    public function countWithFilters($search, $status, $date_from, $date_to, $korlap_id = null, $role = null, $user_id = null) {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                  WHERE 1=1";
        
        if ($korlap_id) {
            $query .= " AND p.korlap_id = :korlap_id";
        }

        if ($role == 'sales' && $user_id) {
            $query .= " AND sp.user_id = :user_id";
        } elseif ($role == 'manager_sales' && $user_id) {
            $query .= " AND sm.user_id = :user_id";
        }

        if (!empty($search)) {
            $query .= " AND (p.nama_project LIKE :search OR p.company_name LIKE :search OR p.project_id LIKE :search)";
        }
        if (!empty($status)) {
            $query .= " AND p.status_project = :status";
        }
        if (!empty($date_from)) {
            $query .= " AND DATE(p.created_at) >= :date_from";
        }
        if (!empty($date_to)) {
            $query .= " AND DATE(p.created_at) <= :date_to";
        }

        $stmt = $this->conn->prepare($query);

        if ($korlap_id) {
            $stmt->bindParam(":korlap_id", $korlap_id);
        }
        
        if (($role == 'sales' || $role == 'manager_sales') && $user_id) {
            $stmt->bindParam(":user_id", $user_id);
        }

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(":search", $search_term);
        }
        if (!empty($status)) {
            $stmt->bindParam(":status", $status);
        }
        if (!empty($date_from)) {
            $stmt->bindParam(":date_from", $date_from);
        }
        if (!empty($date_to)) {
            $stmt->bindParam(":date_to", $date_to);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    // Admin Ops: Vendor and Korlap Assignments
    public function getKorlaps($project_id = null) {
        $korlaps = [];
        // Changed to use users table with role = 'korlap'
        $query = "SELECT user_id as korlap_id, full_name as name FROM users WHERE role = 'korlap' ORDER BY full_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $all_korlaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($project_id) {
            // Check availability
            // 1. Get current project dates
            $query = "SELECT tanggal_mcu FROM " . $this->table_name . " WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":project_id", $project_id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_dates = $row ? json_decode($row['tanggal_mcu'], true) : [];
            if (!is_array($current_dates)) $current_dates = [];

            foreach ($all_korlaps as &$k) {
                $k['is_available'] = true;
                $k['conflict_info'] = '';

                // Get other projects this korlap is assigned to
                $query = "SELECT project_id, nama_project, tanggal_mcu FROM " . $this->table_name . " 
                          WHERE korlap_id = :korlap_id 
                          AND project_id != :project_id 
                          AND status_project NOT IN ('rejected', 'cancelled', 'completed')";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":korlap_id", $k['korlap_id']);
                $stmt->bindParam(":project_id", $project_id);
                $stmt->execute();
                
                while ($p = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $p_dates = json_decode($p['tanggal_mcu'], true);
                    if (!is_array($p_dates)) continue;

                    $overlap = array_intersect($current_dates, $p_dates);
                    if (!empty($overlap)) {
                        $k['is_available'] = false;
                        $k['conflict_info'] = "Busy on " . implode(', ', $overlap) . " (Project: " . $p['nama_project'] . ")";
                        break; // Found conflict, no need to check more
                    }
                }
            }
        } else {
             foreach ($all_korlaps as &$k) {
                $k['is_available'] = true;
             }
        }

        return $all_korlaps;
    }

    public function assignKorlap($project_id, $korlap_id) {
        $query = "UPDATE " . $this->table_name . " SET korlap_id = :korlap_id WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":korlap_id", $korlap_id);
        $stmt->bindParam(":project_id", $project_id);
        return $stmt->execute();
    }

    public function getVendorAllocations($project_id) {
        $query = "SELECT * FROM project_vendor_requirements WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        return $stmt;
    }


    public function saveVendorAllocations($project_id, $allocations) {
        try {
            $this->conn->beginTransaction();

            // Handle updates and insertions smartly to preserve existing assignments
            // 1. Get existing IDs to know what to keep
            $existing_ids = [];
            foreach ($allocations as $row) {
                if (isset($row['id']) && !empty($row['id'])) {
                    $existing_ids[] = $row['id'];
                }
            }

            // 2. Delete rows not in the new list (if any were removed in UI)
            // If the list is empty, existing_ids is empty, so we might delete all? 
            // Only if allocations is NOT empty. If it is empty, maybe user deleted all.
            // CAUTION: If user adds new rows, they don't have IDs yet.
            // Safe approach: Delete ONLY if we are sure. 
            // For now, let's assume we don't delete unless explicit (UI doesn't support delete yet).
            // Actually, simply appending is safer for "masih bisa d tambahkan".
            
            // However, if we re-save the whole list from UI, we need to sync.
            // The UI currently sends EVERYTHING in the table.
            
            // Let's rely on the IDs sent from Frontend.
            if (!empty($existing_ids)) {
                 $placeholders = implode(',', array_fill(0, count($existing_ids), '?'));
                 // We only delete if we want to support deletion. 
                 // If we strictly want to SUPPORT ADDING, we can just UPSERT.
                 // But let's stick to: "don't lock".
            }

            foreach ($allocations as $row) {
                if (isset($row['id']) && !empty($row['id'])) {
                    // Update existing
                    $query = "UPDATE project_vendor_requirements SET exam_type = :exam_type, participant_count = :participant_count, notes = :notes WHERE id = :id";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(":exam_type", $row['exam_type']);
                    $stmt->bindParam(":participant_count", $row['participant_count']);
                    $stmt->bindParam(":notes", $row['notes']);
                    $stmt->bindParam(":id", $row['id']);
                    $stmt->execute();
                } else {
                    // Insert new
                    $query = "INSERT INTO project_vendor_requirements (project_id, exam_type, participant_count, notes) VALUES (:project_id, :exam_type, :participant_count, :notes)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(":project_id", $project_id);
                    $stmt->bindParam(":exam_type", $row['exam_type']);
                    $stmt->bindParam(":participant_count", $row['participant_count']);
                    $stmt->bindParam(":notes", $row['notes']);
                    $stmt->execute();
                }
            }

            // Update status_vendor to requested ONLY if not already further along?
            // If it's already 'requested' or 'assigned', it stays.
            
            $query = "SELECT status_vendor FROM " . $this->table_name . " WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":project_id", $project_id);
            $stmt->execute();
            $curr = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($curr && ($curr['status_vendor'] == 'pending')) {
                 $query = "UPDATE " . $this->table_name . " SET status_vendor = 'requested' WHERE project_id = :project_id";
                 $stmt = $this->conn->prepare($query);
                 $stmt->bindParam(":project_id", $project_id);
                 $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    public function saveVendorFulfillment($project_id, $fulfillments) {
        try {
            $this->conn->beginTransaction();

            // Update vendor names for existing requirements
            // $fulfillments should be array of [id => ..., assigned_vendor_name => ...]
            $query = "UPDATE project_vendor_requirements SET assigned_vendor_name = :vendor_name WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            foreach ($fulfillments as $row) {
                $stmt->bindParam(":vendor_name", $row['assigned_vendor_name']);
                $stmt->bindParam(":id", $row['id']);
                $stmt->execute();
            }

            // Update status_vendor to assigned (was status_project = vendor_assigned)
            $query = "UPDATE " . $this->table_name . " SET status_vendor = 'assigned' WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":project_id", $project_id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function markNoVendorNeeded($project_id) {
        try {
            $this->conn->beginTransaction();
            // Optional: clear any existing requirements
            $qDel = "DELETE FROM project_vendor_requirements WHERE project_id = :project_id";
            $stmtDel = $this->conn->prepare($qDel);
            $stmtDel->bindParam(":project_id", $project_id);
            $stmtDel->execute();
            // Set status_vendor to no_vendor_needed
            $qUpd = "UPDATE " . $this->table_name . " SET status_vendor = 'no_vendor_needed' WHERE project_id = :project_id";
            $stmtUpd = $this->conn->prepare($qUpd);
            $stmtUpd->bindParam(":project_id", $project_id);
            $stmtUpd->execute();
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getAllProjects($role, $user_id, $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        
        // Base query with role-based filtering
        if ($role == 'superadmin' || $role == 'ceo' || $role == 'manager_ops' || $role == 'head_ops' || $role == 'admin_ops' || $role == 'procurement' || $role == 'finance' || $role == 'admin_sales' || $role == 'surat_hasil') {
            // Global access
            $query = "SELECT p.*, k.full_name as korlap_name,
                         (SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                          FROM medical_result_items mri_sub
                          JOIN medical_results mr2 ON mri_sub.medical_result_id = mr2.id
                          JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                          WHERE mr2.project_id = p.project_id) as kohas_names
                  FROM " . $this->table_name . " p 
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                      ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
        } elseif ($role == 'sales') {
            // Sales: Only their projects
            $query = "SELECT p.*, k.full_name as korlap_name,
                         (SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                          FROM medical_result_items mri_sub
                          JOIN medical_results mr2 ON mri_sub.medical_result_id = mr2.id
                          JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                          WHERE mr2.project_id = p.project_id) as kohas_names
                  FROM " . $this->table_name . " p
                  JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                      WHERE sp.user_id = :uid 
                      ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $user_id);
        } elseif ($role == 'manager_sales') {
            // Manager Sales: Team projects
            $query = "SELECT p.*, k.full_name as korlap_name,
                         (SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                          FROM medical_result_items mri_sub
                          JOIN medical_results mr2 ON mri_sub.medical_result_id = mr2.id
                          JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                          WHERE mr2.project_id = p.project_id) as kohas_names
                  FROM " . $this->table_name . " p
                  JOIN sales_persons sp ON p.sales_person_id = sp.id
                  JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                      WHERE sm.user_id = :uid 
                      ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $user_id);
        } elseif ($role == 'korlap') {
            // Korlap: Only assigned projects
            $query = "SELECT p.*, k.full_name as korlap_name,
                         (SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                          FROM medical_result_items mri_sub
                          JOIN medical_results mr2 ON mri_sub.medical_result_id = mr2.id
                          JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                          WHERE mr2.project_id = p.project_id) as kohas_names
                  FROM " . $this->table_name . " p
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                      WHERE p.korlap_id = :uid 
                      ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $user_id);
        } elseif ($role == 'dw_tim_hasil') {
            $query = "SELECT DISTINCT p.*, k.full_name as korlap_name,
                         (SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                          FROM medical_result_items mri_sub
                          JOIN medical_results mr2 ON mri_sub.medical_result_id = mr2.id
                          JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                          WHERE mr2.project_id = p.project_id) as kohas_names
                  FROM " . $this->table_name . " p
                  JOIN medical_results mr ON p.project_id = mr.project_id
                  JOIN medical_result_items mri ON mr.id = mri.medical_result_id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                      WHERE mri.assigned_to_user_id = :uid 
                      ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $user_id);
        } else {
            // Default: No access
            $query = "SELECT * FROM " . $this->table_name . " WHERE 1=0";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    public function getTotalProjects($role, $user_id) {
        // Count total for pagination
        if ($role == 'superadmin' || $role == 'ceo' || $role == 'manager_ops' || $role == 'head_ops' || $role == 'admin_ops' || $role == 'procurement' || $role == 'finance' || $role == 'admin_sales' || $role == 'surat_hasil') {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
        } elseif ($role == 'sales') {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p
                      JOIN sales_persons sp ON p.sales_person_id = sp.id
                      WHERE sp.user_id = :uid";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $user_id);
        } elseif ($role == 'manager_sales') {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p
                      JOIN sales_persons sp ON p.sales_person_id = sp.id
                      JOIN sales_managers sm ON sp.sales_manager_id = sm.id
                      WHERE sm.user_id = :uid";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $user_id);
        } elseif ($role == 'korlap') {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                      WHERE korlap_id = :uid";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $user_id);
        } elseif ($role == 'dw_tim_hasil') {
            $query = "SELECT COUNT(DISTINCT p.project_id) as total FROM " . $this->table_name . " p
                      JOIN medical_results mr ON p.project_id = mr.project_id
                      JOIN medical_result_items mri ON mr.id = mri.medical_result_id
                      WHERE mri.assigned_to_user_id = :uid";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $user_id);
        } else {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=0";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // logAction removed


    public function updateConsumptionStatus($id, $status, $lunch_qty = null, $snack_qty = null) {
        $query = "UPDATE " . $this->table_name . " SET consumption_status = :status";
        
        if ($lunch_qty !== null) {
            $query .= ", procurement_lunch_qty = :lunch_qty";
        }
        if ($snack_qty !== null) {
            $query .= ", procurement_snack_qty = :snack_qty";
        }
        
        $query .= " WHERE project_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        
        if ($lunch_qty !== null) {
            $stmt->bindParam(":lunch_qty", $lunch_qty);
        }
        if ($snack_qty !== null) {
            $stmt->bindParam(":snack_qty", $snack_qty);
        }
        
        return $stmt->execute();
    }

    public function readForProcurement($limit = null, $offset = null) {
        $query = "SELECT p.*, sp.sales_name, k.full_name as korlap_name,
                         (SELECT GROUP_CONCAT(DISTINCT u_kohas.full_name SEPARATOR ', ')
                          FROM medical_result_items mri_sub
                          JOIN medical_results mr2 ON mri_sub.medical_result_id = mr2.id
                          JOIN users u_kohas ON mri_sub.assigned_to_user_id = u_kohas.user_id
                          WHERE mr2.project_id = p.project_id) as kohas_names
                  FROM " . $this->table_name . " p 
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  LEFT JOIN users k ON p.korlap_id = k.user_id
                  WHERE (
                      (p.status_vendor IN ('requested', 'assigned')) 
                      OR (p.lunch = 'Ya') 
                      OR (p.snack = 'Ya')
                      OR (p.status_vendor = 'pending' AND p.status_project IN ('approved', 'in_progress_ops'))
                  )
                  AND p.status_project NOT IN ('rejected', 'cancelled')
                  ORDER BY p.created_at DESC";
        
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);
        
        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt;
    }

    public function countForProcurement() {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table_name . " p 
                  WHERE (
                      (p.status_vendor IN ('requested', 'assigned')) 
                      OR (p.lunch = 'Ya') 
                      OR (p.snack = 'Ya')
                      OR (p.status_vendor = 'pending' AND p.status_project IN ('approved', 'in_progress_ops'))
                  )
                  AND p.status_project NOT IN ('rejected', 'cancelled')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getSalesLeaderboard() {
        $query = "SELECT sp.sales_name, 
                         COUNT(p.project_id) as total_projects,
                         SUM(p.total_peserta) as total_pax
                  FROM " . $this->table_name . " p
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  WHERE p.status_project NOT IN ('cancelled', 'rejected')
                  GROUP BY sp.id, sp.sales_name
                  ORDER BY total_projects DESC, total_pax DESC
                  LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getUpcomingProjects($limit = 5) {
        $query = "SELECT project_id, nama_project, tanggal_mcu, status_project, status_vendor 
                  FROM " . $this->table_name . " 
                  WHERE status_project NOT IN ('cancelled', 'rejected', 'completed')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $upcoming = [];
        $today = date('Y-m-d');
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dates = json_decode($row['tanggal_mcu'], true);
            if (!is_array($dates)) {
                 if (!empty($row['tanggal_mcu'])) {
                     $dates = [$row['tanggal_mcu']];
                 } else {
                     $dates = [];
                 }
            }
            
            // Find earliest future date
            $next_date = null;
            sort($dates);
            foreach ($dates as $date) {
                if ($date >= $today) {
                    $next_date = $date;
                    break;
                }
            }

            if ($next_date) {
                $upcoming[] = [
                    'project_id' => $row['project_id'],
                    'nama_project' => $row['nama_project'],
                    'date' => $next_date, // For sorting and "Next Date" display
                    'status_project' => $row['status_project'],
                    'status_vendor' => $row['status_vendor'] ?? 'pending',
                    'tanggal_mcu' => $row['tanggal_mcu']
                ];
            }
        }
        
        // Sort by date ASC
        usort($upcoming, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });
        
        $final_list = array_slice($upcoming, 0, $limit);

        // Self-healing: Check status for these projects
        // This ensures that if BA was uploaded but status stuck, it gets fixed here.
        $uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        foreach ($final_list as $key => &$p) {
            $new_stat = $this->checkCompletionStatus($p['project_id'], $uid);
            
            if ($new_stat === 'completed') {
                unset($final_list[$key]);
            } elseif ($new_stat !== 'pending' && $new_stat !== $p['status_project']) {
                $p['status_project'] = $new_stat;
            }
        }
        
        return array_values($final_list);
    }

    // Berita Acara Management methods are defined above (lines ~498)

    public function getUrgentCount($role) {
        $urgent_projects = $this->getUrgentProjects($role);
        return count($urgent_projects);
    }

    public function checkAndSetReadyForInvoicing($project_id) {
        // 1. Check Medical Result Status
        $query = "SELECT status FROM medical_results WHERE project_id = :pid LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pid", $project_id);
        $stmt->execute();
        $mr = $stmt->fetch(PDO::FETCH_ASSOC);

        // Allow COMPLETED or NOT_NEEDED
        if (!$mr || !in_array($mr['status'], ['COMPLETED', 'NOT_NEEDED'])) {
            return false;
        }

        // 2. Check RAB Realization Status
        // Get all RABs for this project
        $query = "SELECT id, status FROM rabs WHERE project_id = :pid AND status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pid", $project_id);
        $stmt->execute();
        $rabs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rabs)) {
            // No RABs? 
            // If Medical Result is done, and no RAB needed, maybe we can proceed?
            // But usually project needs RAB.
            // If empty, return false for safety unless we know for sure.
            return false; 
        }

        $all_completed = true;
        foreach ($rabs as $rab) {
            // Check if RAB status is completed (meaning settlement proof uploaded/verified)
            // Or if it's 'realization_approved' and maybe that's enough?
            // User requirement: "Status Realisasi jadi completed"
            if ($rab['status'] !== 'completed') {
                $all_completed = false;
                break;
            }
        }

        if ($all_completed) {
            // Check current status to prevent regression
            $currQuery = "SELECT status_project FROM " . $this->table_name . " WHERE project_id = :pid";
            $currStmt = $this->conn->prepare($currQuery);
            $currStmt->bindParam(":pid", $project_id);
            $currStmt->execute();
            $currStatus = $currStmt->fetchColumn();

            // Do not update if already in invoicing flow or completed
            $ignored_statuses = ['ready_for_invoicing', 'invoice_requested', 'invoiced', 'paid', 'completed'];
            if (in_array($currStatus, $ignored_statuses)) {
                return false;
            }

            // Update Project Status
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            return $this->updateStatus($project_id, 'ready_for_invoicing', $user_id, 'system', 'Auto-update: RAB Realized & Medical Result Completed');
        }

        return false;
    }

    public function getProjectsForInvoice($search = '') {
        $query = "SELECT p.project_id, p.nama_project, p.company_name, p.tanggal_mcu, p.status_project, sp.sales_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN sales_persons sp ON p.sales_person_id = sp.id
                  WHERE p.status_project = 'ready_for_invoicing'";
        
        if (!empty($search)) {
            $query .= " AND (p.nama_project LIKE :search OR p.company_name LIKE :search OR p.project_id LIKE :search)";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        
        if (!empty($search)) {
            $term = "%$search%";
            $stmt->bindParam(":search", $term);
        }
        
        $stmt->execute();
        return $stmt;
    }

    private function hasInvoiceRequest($project_id) {
        // Check if there are any invoice requests for this project
        // Updated to check invoice_request_projects table
        $query = "SELECT 1 FROM invoice_request_projects WHERE project_id = :pid LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pid", $project_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
