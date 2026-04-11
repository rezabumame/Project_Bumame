<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

try {
    require_once __DIR__ . '/../config/autoload.php';
    $db = (new Database())->getConnection();
    if (!$db) {
        echo "Database connection failed. Check .env or config.";
        exit(1);
    }

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

    $updateQuery = "UPDATE rabs SET settlement_proof_path = transfer_proof_path, transfer_proof_path = NULL 
                    WHERE transfer_proof_path LIKE '%SETTLEMENT_%'";
    $affectedRows = $db->exec($updateQuery);
    echo "Migrated $affectedRows records to settlement_proof_path.\n";
    echo "Done.";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine();
}
