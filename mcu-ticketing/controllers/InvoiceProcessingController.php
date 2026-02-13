<?php
class InvoiceProcessingController extends BaseController {
    private $invoice;

    public function __construct() {
        parent::__construct();
        // Finance, Superadmin & Sales Roles (View Only)
        $this->checkRole(['finance', 'superadmin', 'admin_sales', 'sales_support_supervisor', 'sales_performance_manager']);
        
        $this->invoice = $this->loadModel('Invoice');
    }

    public function index() {
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        
        $invoices = $this->invoice->getAll($_SESSION['role'], $page, $limit);
        
        // Count for pagination
        $total_rows = $this->invoice->countAll();
        $total_pages = ceil($total_rows / $limit);
        
        include '../views/invoices/index.php';
    }

    public function detail() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=invoice_processing_index');
            exit;
        }

        if (!$this->verifyInvoiceAccess($id)) {
            die("Unauthorized Access to this Invoice.");
        }

        $invoice = $this->invoice->getById($id);
        if (!$invoice) {
            die("Invoice not found.");
        }

        include '../views/invoices/view.php';
    }

    public function edit() {
        // Restricted to Finance/Superadmin
        if (!in_array($_SESSION['role'], ['finance', 'superadmin'])) {
            $_SESSION['error'] = "Unauthorized: Hanya Finance yang dapat mengedit invoice.";
            header('Location: index.php?page=invoice_processing_index');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=invoice_processing_index');
            exit;
        }

        if (!$this->verifyInvoiceAccess($id)) {
            die("Unauthorized Access to this Invoice.");
        }

        $invoice = $this->invoice->getById($id);
        if (!$invoice) {
            die("Invoice not found.");
        }
        
        // Prevent editing if PAID (except superadmin?)
        // Requirement: "Finance TIDAK boleh mengedit invoice setelah status Paid."
        if ($invoice['status'] == 'PAID' && $_SESSION['role'] != 'superadmin') {
            $_SESSION['error'] = "Invoice sudah PAID dan tidak dapat diedit.";
            header('Location: index.php?page=invoice_processing_index');
            exit;
        }

        include '../views/invoices/edit.php';
    }

    public function pay() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=invoice_processing_index');
            exit;
        }

        // Restrict to Finance/Superadmin
        if (!in_array($_SESSION['role'], ['finance', 'superadmin'])) {
            $_SESSION['error'] = "Unauthorized Action.";
            header('Location: index.php?page=invoice_processing_index');
            exit;
        }

        $id = $_POST['id'];

        if (!$this->verifyInvoiceAccess($id)) {
            die("Unauthorized Access.");
        }

        $payment_date = $_POST['payment_date'];
        $payment_notes = $_POST['payment_notes'];
        $user_id = $_SESSION['user_id'];

        if (empty($payment_date)) {
            $_SESSION['error'] = "Tanggal pembayaran wajib diisi.";
            header('Location: index.php?page=invoice_processing_edit&id=' . $id);
            exit;
        }

        $current = $this->invoice->getById($id);
        if ($current['status'] != 'SENT' && $_SESSION['role'] != 'superadmin') {
            $_SESSION['error'] = "Invoice harus berstatus SENT sebelum dapat diproses pembayaran.";
            header('Location: index.php?page=invoice_processing_edit&id=' . $id);
            exit;
        }

        if ($this->invoice->markAsPaid($id, $payment_date, $payment_notes, $user_id)) {
            $_SESSION['success'] = "Pembayaran berhasil dikonfirmasi. Invoice LUNAS.";
            header('Location: index.php?page=invoice_processing_index');
        } else {
            $_SESSION['error'] = "Gagal memproses pembayaran.";
            header('Location: index.php?page=invoice_processing_edit&id=' . $id);
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=invoice_processing_index');
            exit;
        }

        // Restrict to Finance/Superadmin
        if (!in_array($_SESSION['role'], ['finance', 'superadmin'])) {
            $_SESSION['error'] = "Unauthorized Action.";
            header('Location: index.php?page=invoice_processing_index');
            exit;
        }

        $id = $_POST['id'];

        if (!$this->verifyInvoiceAccess($id)) {
            die("Unauthorized Access.");
        }

        $current = $this->invoice->getById($id);

        if ($current['status'] == 'PAID' && $_SESSION['role'] != 'superadmin') {
             $_SESSION['error'] = "Invoice sudah PAID dan tidak dapat diedit.";
             header('Location: index.php?page=invoice_processing_index');
             exit;
        }

        $data = [
            'id' => $id,
            'invoice_number' => $_POST['invoice_number'],
            'invoice_date' => $_POST['invoice_date'],
            'delivery_receipt_number' => $_POST['delivery_receipt_number'],
            'is_hardcopy_sent' => isset($_POST['is_hardcopy_sent']) ? 1 : 0,
            'payment_date' => $_POST['payment_date'],
            'payment_notes' => $_POST['payment_notes'],
            'status' => 'DRAFT_FINANCE', // Default
            'user_id' => $_SESSION['user_id']
        ];
        
        $action = $_POST['action'] ?? 'draft';

        // Status Logic based on Action Button
        if ($current['status'] == 'PAID') {
             $data['status'] = 'PAID'; // Preserve PAID
        } else {
            if ($action == 'draft') {
                $data['status'] = 'DRAFT_FINANCE';
            } elseif ($action == 'process') {
                // Validation for Processing
                if (empty($data['invoice_number'])) {
                    $_SESSION['error'] = "Nomor Invoice wajib diisi untuk memproses invoice.";
                    header('Location: index.php?page=invoice_processing_edit&id=' . $id);
                    exit;
                }

                // Removed ISSUED status as per request. If invoice number exists, it is SENT.
                $data['status'] = 'SENT';
            }
        }
        
        // Validation Logic for Status Transitions
        // If Status is PAID, Payment Date is Mandatory.
        if ($data['status'] == 'PAID' && empty($data['payment_date'])) {
            $_SESSION['error'] = "Tanggal pembayaran wajib diisi untuk status PAID.";
            header('Location: index.php?page=invoice_processing_edit&id=' . $id);
            exit;
        }

        // If Status is SENT or PAID, Invoice Number is Mandatory
        if (($data['status'] == 'SENT' || $data['status'] == 'PAID') && empty($data['invoice_number'])) {
            $_SESSION['error'] = "Nomor Invoice wajib diisi untuk status SENT/PAID.";
            header('Location: index.php?page=invoice_processing_edit&id=' . $id);
            exit;
        }

        if ($this->invoice->update($data)) {
            $_SESSION['success'] = "Invoice berhasil diperbarui.";
            header('Location: index.php?page=invoice_processing_index');
        } else {
            $_SESSION['error'] = "Gagal memperbarui invoice.";
            header('Location: index.php?page=invoice_processing_edit&id=' . $id);
        }
    }
}
?>
