<?php
require_once '../config/Database.php';

$db = (new Database())->getConnection();

// 1. Add settlement_proof_path column if not exists
$checkQuery = "SHOW COLUMNS FROM rabs LIKE 'settlement_proof_path'";
$res = $db->query($checkQuery);
if ($res->rowCount() == 0) {
    $alterQuery = "ALTER TABLE rabs ADD COLUMN settlement_proof_path VARCHAR(255) DEFAULT NULL AFTER transfer_proof_path";
    if ($db->exec($alterQuery) !== false) {
        echo "Column 'settlement_proof_path' added successfully.\n";
    } else {
        echo "Failed to add column 'settlement_proof_path'.\n";
    }
} else {
    echo "Column 'settlement_proof_path' already exists.\n";
}

// 2. Migrate existing settlement data from transfer_proof_path to settlement_proof_path
// Only if transfer_proof_path contains 'SETTLEMENT_'
$updateQuery = "UPDATE rabs SET settlement_proof_path = transfer_proof_path, transfer_proof_path = NULL 
                WHERE transfer_proof_path LIKE '%SETTLEMENT_%'";
$affectedRows = $db->exec($updateQuery);
echo "Migrated $affectedRows records to settlement_proof_path.\n";

?>
