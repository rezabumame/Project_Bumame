<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Starting DB Test...\n";

try {
    require_once __DIR__ . '/../config/autoload.php';
    echo "Autoload loaded.\n";

    $database = new Database();
    echo "Database object created.\n";

    $db = $database->getConnection();
    if ($db) {
        echo "Database connection successful!\n";
        $stmt = $db->query("SELECT DATABASE()");
        echo "Current DB: " . $stmt->fetchColumn() . "\n";
    } else {
        echo "Database connection returned NULL.\n";
    }
} catch (Throwable $e) {
    echo "Caught: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
