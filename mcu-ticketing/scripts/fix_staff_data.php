<?php
/**
 * Standalone Repair Script
 * Purpose: Fix casing inconsistency in man_powers table (Status & Skills)
 * Usage: Run once via browser or CLI: php scripts/fix_staff_data.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SystemSetting.php';
require_once __DIR__ . '/../models/ManPower.php';

// Prevent unauthenticated access if run via web (Optional but recommended)
// In this case, we keep it simple for the user to run.

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed. Please check config/database.php");
}

$setting = new SystemSetting($db);
$manPower = new ManPower($db);

echo "<h1>Man Power Data Repair Script</h1>";
echo "<pre>";

// 1. Get official skills from settings
$mappingStr = $setting->get('rab_personnel_codes');
$officialSkills = [];
$lines = explode("\n", str_replace("\r", "", $mappingStr));
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    $parts = explode('=', $line, 2);
    if (count($parts) == 2) {
        $officialSkills[] = trim($parts[1]);
    }
}

echo "Found " . count($officialSkills) . " official skills in settings.\n";

// 2. Fetch ALL records
$query = "SELECT * FROM man_powers";
$stmt = $db->prepare($query);
$stmt->execute();
$all_staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Processing " . count($all_staff) . " records...\n\n";

$updatedCount = 0;
foreach ($all_staff as $staff) {
    $needsUpdate = false;
    $logMsg = "[ID: {$staff['id']}] Name: {$staff['name']} - ";
    
    // Fix Status
    $currentStatus = $staff['status'];
    $newStatus = ucfirst(strtolower(trim($currentStatus)));
    if ($newStatus !== $currentStatus) {
        $logMsg .= "Status fixed ($currentStatus -> $newStatus). ";
        $needsUpdate = true;
    }

    // Fix Skills
    $skills = json_decode($staff['skills'], true) ?? [];
    $newSkills = [];
    $skillChanges = [];
    
    foreach ($skills as $s) {
        $s = trim($s);
        $matched = false;
        foreach ($officialSkills as $os) {
            if (strcasecmp($s, $os) == 0) {
                if ($s !== $os) {
                    $skillChanges[] = "$s -> $os";
                    $needsUpdate = true;
                }
                $newSkills[] = $os;
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            $newSkills[] = $s; // Keep as is if not found in official list
        }
    }

    if (!empty($skillChanges)) {
        $logMsg .= "Skills fixed (" . implode(", ", $skillChanges) . "). ";
    }

    if ($needsUpdate) {
        $updateQuery = "UPDATE man_powers SET status = :status, skills = :skills WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(":status", $newStatus);
        $skillsJson = json_encode($newSkills);
        $updateStmt->bindParam(":skills", $skillsJson);
        $updateStmt->bindParam(":id", $staff['id']);
        
        if ($updateStmt->execute()) {
            echo $logMsg . "SUCCESS\n";
            $updatedCount++;
        } else {
            echo $logMsg . "FAILED\n";
        }
    }
}

echo "\n------------------------------------------------\n";
echo "Repair Completed!\n";
echo "Total Records Processed: " . count($all_staff) . "\n";
echo "Total Records Updated: $updatedCount\n";
echo "------------------------------------------------\n";
echo "</pre>";
?>
