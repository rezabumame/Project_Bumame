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
        
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        $invoices = $this->invoice->getAll($_SESSION['role'], $page, $limit, $filters);
        
        // Count for pagination
        $total_rows = $this->invoice->countAll($filters);
        $total_pages = ceil($total_rows / $limit);
        
        include '../views/invoices/index.php';
    }

    public function export_csv() {
        $this->checkRole(['finance', 'superadmin']);
        
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        // Fetch all invoices with filters
        $invoices = $this->invoice->getAll($_SESSION['role'], null, null, $filters);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="export_invoice_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['No Invoice', 'No Request', 'Tanggal Invoice', 'Client', 'Total Amount', 'Status', 'Tanggal Bayar']);

        foreach ($invoices as $inv) {
            fputcsv($output, [
                $inv['invoice_number'] ?? '-',
                $inv['request_number'] ?? '-',
                $inv['invoice_date'] ?? '-',
                $inv['client_company'],
                $inv['total_amount'],
                strtoupper($inv['status']),
                $inv['payment_date'] ?? '-'
            ]);
        }

        fclose($output);
        exit;
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

    public function get_sent_json() {
        if (!in_array($_SESSION['role'], ['finance', 'superadmin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $invoices = $this->invoice->getSentInvoices();
        echo json_encode(['status' => 'success', 'data' => $invoices]);
        exit;
    }

    public function bulk_update_payment_json() {
        if (!in_array($_SESSION['role'], ['finance', 'superadmin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['data']) || !is_array($data['data'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data format']);
            exit;
        }

        $success_count = 0;
        $error_count = 0;
        $errors = [];
        $user_id = $_SESSION['user_id'];

        foreach ($data['data'] as $item) {
            $invoice_number = trim($item['invoice_number'] ?? '');
            $payment_date = trim($item['payment_date'] ?? '');

            if (empty($invoice_number) || empty($payment_date)) {
                continue;
            }

            // Basic date validation
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $payment_date)) {
                // Try to handle ISO date format if it comes as YYYY-MM-DDTHH:mm:ss.sssZ
                if (strpos($payment_date, 'T') !== false) {
                    $payment_date = explode('T', $payment_date)[0];
                }
                
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $payment_date)) {
                    $errors[] = "Invoice $invoice_number: Format tanggal salah ($payment_date). Harus YYYY-MM-DD.";
                    $error_count++;
                    continue;
                }
            }

            $invoice = $this->invoice->getByInvoiceNumber($invoice_number);
            if ($invoice) {
                if ($invoice['status'] == 'SENT' || $_SESSION['role'] == 'superadmin') {
                    if ($this->invoice->markAsPaid($invoice['id'], $payment_date, "Bulk payment update via Excel", $user_id)) {
                        $success_count++;
                    } else {
                        $errors[] = "Invoice $invoice_number: Gagal update ke database.";
                        $error_count++;
                    }
                } else {
                    $errors[] = "Invoice $invoice_number: Status bukan SENT (Status saat ini: " . $invoice['status'] . ").";
                    $error_count++;
                }
            } else {
                $errors[] = "Invoice $invoice_number: Tidak ditemukan.";
                $error_count++;
            }
        }

        $message = "$success_count invoice berhasil diupdate menjadi PAID.";
        if ($error_count > 0) {
            $message .= "\n\nErrors:\n" . implode("\n", $errors);
        }

        if ($success_count > 0 || $error_count > 0) {
            echo json_encode(['status' => 'success', 'message' => $message]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang diproses.']);
        }
        exit;
    }
}
?>
