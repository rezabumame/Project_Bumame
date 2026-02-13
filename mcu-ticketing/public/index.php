<?php
session_start();

// Display errors for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../config/autoload.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'login';
$router = new Router();

// Public Routes
$router->add('login', 'AuthController', 'login');
$router->add('auth_login', 'AuthController', 'login');
$router->add('logout', 'AuthController', 'logout');
// QR Verify (Public)
$router->add('qr_verify_invoice_request', 'InvoiceRequestController', 'qr_verify');
$router->add('qr_verify_rab', 'RabController', 'qr_verify');
$router->add('qr_verify_lpum', 'RabRealizationController', 'qr_verify');

// Auth Guard
$public_pages = ['login', 'auth_login', 'qr_verify_invoice_request', 'qr_verify_rab', 'qr_verify_lpum'];
if (!in_array($page, $public_pages)) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: index.php?page=login");
        exit;
    }
}

// Routes Definition
// Dashboard
$router->add('dashboard', 'DashboardController', 'index');
$router->add('admin_sales_dashboard', 'DashboardController', 'index');
$router->add('get_calendar_events', 'DashboardController', 'getCalendarEvents');

// Kanban
$router->add('manager_ops_kanban', 'KanbanController', 'index');
$router->add('update_project_status', 'KanbanController', 'updateStatus');
$router->add('get_project_detail_ajax', 'KanbanController', 'getProjectDetail');

// Projects
$router->add('projects_list', 'ProjectController', 'index');
$router->add('all_projects', 'ProjectController', 'allProjects');
$router->add('projects_create', 'ProjectController', 'create');
$router->add('projects_store', 'ProjectController', 'store');
$router->add('projects_edit', 'ProjectController', 'edit');
$router->add('projects_update', 'ProjectController', 'update');
$router->add('project_history', 'ProjectController', 'history');
$router->add('get_vendor_allocations_ajax', 'ProjectController', 'get_vendor_allocations_ajax');
$router->add('assign_vendor_ajax', 'ProjectController', 'assign_vendor_ajax');
$router->add('get_korlaps_ajax', 'ProjectController', 'get_korlaps_ajax');
$router->add('assign_korlap_ajax', 'ProjectController', 'assign_korlap_ajax');
$router->add('assign_vendor_procurement_ajax', 'ProjectController', 'assign_vendor_procurement_ajax');
$router->add('mark_no_vendor_needed_ajax', 'ProjectController', 'mark_no_vendor_needed_ajax');
$router->add('approve_consumption_ajax', 'ProjectController', 'approve_consumption_ajax');
$router->add('upload_sph', 'ProjectController', 'uploadSph');
$router->add('upload_berita_acara', 'ProjectController', 'upload_berita_acara');
$router->add('get_ba_status_ajax', 'ProjectController', 'get_ba_status_ajax');
$router->add('cancel_berita_acara', 'ProjectController', 'cancel_berita_acara');
$router->add('import_projects_excel_json', 'ProjectController', 'importProjectsExcelJson');
$router->add('download_ba', 'ProjectController', 'download_ba');
$router->add('download_sph', 'ProjectController', 'download_sph');
$router->add('generate_vendor_memo', 'ProjectController', 'generate_vendor_memo');

// Technical Meeting
$router->add('technical_meeting_list', 'TechnicalMeetingController', 'index');
$router->add('technical_meeting_view', 'TechnicalMeetingController', 'detail');
$router->add('technical_meeting_create', 'TechnicalMeetingController', 'create');
$router->add('technical_meeting_store', 'TechnicalMeetingController', 'store');

// RAB
$router->add('rabs_create', 'RabController', 'create');
$router->add('rabs_store', 'RabController', 'store');
$router->add('rabs_edit', 'RabController', 'edit');
$router->add('rabs_update', 'RabController', 'update');
$router->add('rabs_get_project_dates', 'RabController', 'get_project_dates');
$router->add('rabs_list', 'RabController', 'index');
$router->add('rabs_show', 'RabController', 'show');
$router->add('rabs_auto_approve_submit', 'RabController', 'auto_approve_and_submit_finance');
$router->add('rabs_approve', 'RabController', 'approve');
$router->add('rabs_submit_finance', 'RabController', 'submit_to_finance');
$router->add('rabs_advance_paid', 'RabController', 'advance_paid');
$router->add('rabs_export_pdf', 'RabController', 'export_pdf');
$router->add('rabs_export_csv', 'RabController', 'export_csv');
$router->add('rabs_update_profit', 'RabController', 'update_profit');

// RAB Realization
$router->add('realization_list', 'RabRealizationController', 'index');
$router->add('realization_create', 'RabRealizationController', 'create');
$router->add('realization_store', 'RabRealizationController', 'store');
$router->add('realization_edit', 'RabRealizationController', 'edit');
$router->add('realization_update', 'RabRealizationController', 'update');
$router->add('realization_comparison', 'RabRealizationController', 'comparison');
$router->add('realization_submit', 'RabRealizationController', 'submit_realization');
$router->add('realization_approve', 'RabRealizationController', 'approve_realization');
$router->add('realization_reject', 'RabRealizationController', 'reject_realization');
$router->add('realization_export_lpum', 'RabRealizationController', 'export_lpum');
$router->add('realization_export_csv', 'RabRealizationController', 'export_csv');
$router->add('realization_upload_settlement', 'RabRealizationController', 'upload_settlement');

// RAB Medical Result
$router->add('rab_medical_index', 'RabMedicalResultController', 'index');
$router->add('rab_medical_create', 'RabMedicalResultController', 'create');
$router->add('rab_medical_store', 'RabMedicalResultController', 'store');
$router->add('rab_medical_edit', 'RabMedicalResultController', 'edit');
$router->add('rab_medical_update', 'RabMedicalResultController', 'update');
$router->add('rab_medical_view', 'RabMedicalResultController', 'view_rab');
$router->add('rab_medical_submit', 'RabMedicalResultController', 'submit');
$router->add('rab_medical_approve', 'RabMedicalResultController', 'approve');
$router->add('rab_medical_realization', 'RabMedicalResultController', 'realization');
$router->add('rab_medical_store_realization', 'RabMedicalResultController', 'store_realization');
$router->add('rab_medical_delete_realization', 'RabMedicalResultController', 'delete_realization');

// Cost Codes
$router->add('cost_codes_index', 'CostCodeController', 'index');
$router->add('cost_codes_create', 'CostCodeController', 'create');
$router->add('cost_codes_delete', 'CostCodeController', 'delete');
$router->add('cost_codes_update', 'CostCodeController', 'update');

// Invoice Requests (Admin Sales)
$router->add('invoice_requests_index', 'InvoiceRequestController', 'index');
$router->add('invoice_requests_create', 'InvoiceRequestController', 'create');
$router->add('invoice_requests_store', 'InvoiceRequestController', 'store');
$router->add('invoice_requests_view', 'InvoiceRequestController', 'show');
$router->add('invoice_requests_print', 'InvoiceRequestController', 'print');
$router->add('invoice_requests_submit', 'InvoiceRequestController', 'submit');
$router->add('invoice_requests_approve', 'InvoiceRequestController', 'approve');
$router->add('invoice_requests_export_csv', 'InvoiceRequestController', 'export_csv');
$router->add('invoice_requests_edit', 'InvoiceRequestController', 'edit');
$router->add('invoice_requests_update_action', 'InvoiceRequestController', 'update_action');
$router->add('invoice_requests_delete', 'InvoiceRequestController', 'delete');

// Invoice Processing (Finance)
$router->add('invoice_processing_index', 'InvoiceProcessingController', 'index');
$router->add('invoice_processing_export_csv', 'InvoiceProcessingController', 'export_csv');
$router->add('invoice_processing_view', 'InvoiceProcessingController', 'detail');
$router->add('invoice_processing_edit', 'InvoiceProcessingController', 'edit');
$router->add('invoice_processing_update', 'InvoiceProcessingController', 'update');
$router->add('invoice_processing_pay', 'InvoiceProcessingController', 'pay');
$router->add('invoice_processing_get_sent_json', 'InvoiceProcessingController', 'get_sent_json');
$router->add('invoice_processing_bulk_update_json', 'InvoiceProcessingController', 'bulk_update_payment_json');

// Superadmin
$router->add('superadmin_users', 'SuperadminController', 'manageUsers');
$router->add('superadmin_save_user', 'SuperadminController', 'saveUser');
$router->add('superadmin_delete_user', 'SuperadminController', 'deleteUser');

// National Holidays
$router->add('holidays', 'NationalHolidayController', 'index');
$router->add('holidays_store', 'NationalHolidayController', 'store');
$router->add('holidays_delete', 'NationalHolidayController', 'delete');

// Vendors
$router->add('vendors_list', 'VendorController', 'index');
$router->add('vendors_create', 'VendorController', 'create');
$router->add('vendors_edit', 'VendorController', 'edit');
$router->add('vendors_delete', 'VendorController', 'delete');
$router->add('get_all_vendors_ajax', 'VendorController', 'get_all_ajax');

// Sales Persons
$router->add('sales_persons_index', 'SalesPersonController', 'index');
$router->add('sales_persons_create', 'SalesPersonController', 'create');
$router->add('sales_persons_store', 'SalesPersonController', 'store');
$router->add('sales_persons_edit', 'SalesPersonController', 'edit');
$router->add('sales_persons_update', 'SalesPersonController', 'update');
$router->add('sales_persons_delete', 'SalesPersonController', 'delete');
$router->add('sales_person_projects', 'SalesPersonController', 'get_projects');

// Sales Managers
$router->add('sales_managers_index', 'SalesManagerController', 'index');
$router->add('sales_managers_create', 'SalesManagerController', 'create');
$router->add('sales_managers_store', 'SalesManagerController', 'store');
$router->add('sales_managers_edit', 'SalesManagerController', 'edit');
$router->add('sales_managers_update', 'SalesManagerController', 'update');
$router->add('sales_managers_delete', 'SalesManagerController', 'delete');

// Profile
$router->add('profile_edit', 'ProfileController', 'edit');
$router->add('profile_update', 'ProfileController', 'update');

// System Settings
$router->add('settings', 'SystemSettingController', 'index');
$router->add('settings_update', 'SystemSettingController', 'update');
$router->add('settings_add_role', 'SystemSettingController', 'addRabRole');
$router->add('settings_delete_role', 'SystemSettingController', 'deleteRabRole');

// Audit
$router->add('audit_index', 'AuditController', 'index');

// Productivity Ops
$router->add('productivity_ops', 'ProductivityOpsController', 'index');

// Chatter / Notifications
$router->add('get_comments', 'ProjectController', 'get_comments');
$router->add('add_comment', 'ProjectController', 'add_comment');
$router->add('search_users', 'ProjectController', 'search_users');
$router->add('toggle_chat_mute', 'ProjectController', 'toggle_chat_mute');
$router->add('get_unread_chat_count', 'ProjectController', 'get_unread_chat_count');
$router->add('get_notifications', 'NotificationController', 'getUnread');
$router->add('mark_notification_read', 'NotificationController', 'markRead');
$router->add('mark_all_notifications_read', 'NotificationController', 'markAllRead');

// Medical Results
$router->add('medical_results_index', 'MedicalResultController', 'index');
$router->add('medical_results_detail', 'MedicalResultController', 'detail');
$router->add('medical_results_save_project', 'MedicalResultController', 'save_project_info');
$router->add('medical_results_assign_project_batch', 'MedicalResultController', 'assign_project_batch');
$router->add('medical_results_save_item', 'MedicalResultController', 'save_item');
$router->add('medical_results_save_followup', 'MedicalResultController', 'save_followup');
$router->add('medical_results_complete', 'MedicalResultController', 'mark_completed');
$router->add('medical_results_assign_user', 'MedicalResultController', 'assign_user');
$router->add('medical_results_mark_not_needed', 'MedicalResultController', 'mark_not_needed');

// Inventory Request (Korlap)
$router->add('inventory_request_index', 'InventoryRequestController', 'index');
$router->add('inventory_request_create', 'InventoryRequestController', 'create');
$router->add('inventory_request_store', 'InventoryRequestController', 'store');
$router->add('inventory_request_edit', 'InventoryRequestController', 'edit');
$router->add('inventory_request_update', 'InventoryRequestController', 'update');
$router->add('inventory_request_detail', 'InventoryRequestController', 'detail');

// Inventory Master (Superadmin & Warehouse Admins)
$router->add('inventory_master_index', 'InventoryMasterController', 'index');
$router->add('inventory_master_create', 'InventoryMasterController', 'create');
$router->add('inventory_master_store', 'InventoryMasterController', 'store');
$router->add('inventory_master_edit', 'InventoryMasterController', 'edit');
$router->add('inventory_master_update', 'InventoryMasterController', 'update');
$router->add('inventory_master_delete', 'InventoryMasterController', 'delete');

// Warehouse (Admin Gudang)
$router->add('warehouse_dashboard', 'WarehouseController', 'index');
$router->add('warehouse_detail', 'WarehouseController', 'detail');
$router->add('warehouse_update_status', 'WarehouseController', 'update_status');
$router->add('warehouse_print', 'WarehouseController', 'print_pdf');
$router->add('warehouse_qr_verify', 'WarehouseController', 'qr_verify');

// Man Power Management (Administration)
$router->add('man_power_management', 'ManPowerController', 'index');
$router->add('man_power_create', 'ManPowerController', 'create');
$router->add('man_power_store', 'ManPowerController', 'store');
$router->add('man_power_edit', 'ManPowerController', 'edit');
$router->add('man_power_update', 'ManPowerController', 'update');

// Man Power MCU (Project Execution)
$router->add('man_power_mcu', 'ProjectManPowerController', 'index');
$router->add('man_power_heatmap', 'ProjectManPowerController', 'heatmap');
$router->add('man_power_detail', 'ProjectManPowerController', 'detail');
$router->add('man_power_assign_store', 'ProjectManPowerController', 'store');
$router->add('man_power_assignment_delete', 'ProjectManPowerController', 'delete');
$router->add('project_man_power_export', 'ProjectManPowerController', 'export');

// Dispatch
// DEBUG: Check routes
if ($page === 'rab_medical_index') {
    // echo "<pre>";
    // print_r(array_keys(get_object_vars($router)['routes'])); // Router->routes is private, can't access directly unless reflection or exposed
    // echo "Page: '$page'<br>";
    // echo "</pre>";
    // Since routes is private, I can't easily dump it without modifying Router.
    // But I can check if the line above executed.
}

$router->dispatch($page);
?>
