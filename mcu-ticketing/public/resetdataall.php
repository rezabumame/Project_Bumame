<?php
// resetdataall.php
// Pastikan file ini berada di dalam folder public/
// URL akses: domain.com/resetdataall.php

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$tables_to_truncate = [
    'inventory_request_items',
    'inventory_requests',
    'invoice_items',
    'invoice_request_items',
    'invoice_request_projects',
    'invoice_requests',
    'invoices',
    'medical_result_followups',
    'medical_result_items',
    'medical_result_realizations',
    'medical_results',
    'notifications',
    'project_berita_acara',
    'project_chat_participants',
    'project_comments',
    'project_logs',
    'project_man_power',
    'project_vendor_requirements',
    'projects',
    'rab_items',
    'rab_medical_result_dates',
    'rab_medical_results',
    'rab_realization_items',
    'rab_realizations',
    'rabs',
    'technical_meetings',
    'warehouse_requests'
];

try {
    // Disable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "Foreign Key Checks Disabled.<br>";

    foreach ($tables_to_truncate as $table) {
        $sql = "TRUNCATE TABLE " . $table;
        $db->exec($sql);
        echo "Table <strong>$table</strong> truncated successfully.<br>";
    }

    // Enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Foreign Key Checks Enabled.<br>";
    
    echo "<br><strong>All transactional data (Ticketing, RAB, Projects, etc.) has been reset.</strong><br>";
    echo "<strong>Administration data (Users, Masters, etc.) has been preserved.</strong>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
