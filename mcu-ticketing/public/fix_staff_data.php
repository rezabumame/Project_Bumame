<?php
/**
 * Standalone Repair Script - PUBLIC VERSION
 * Purpose: Fix casing inconsistency in man_powers table based on Official Acuan
 * Usage: Run via browser: http://[domain]/fix_staff_data.php
 */

// 1. Enable Error Reporting for Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h1>Man Power Data Repair Script (Public Debug)</h1>";

if (!$db) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
    echo "<h3>Database connection failed!</h3>";
    echo "Please check <b>config/database.php</b>. ";
    echo "If you are on a live server, ensure the 'live' credentials match your hosting setup.";
    echo "</div>";
    exit;
}

echo "<pre>";

/**
 * ACUAN RESMI - Sesuai Screenshot User
 */
$officialSkills = [
    "Admin",
    "Audiometri",
    "Dokter",
    "Driver",
    "EKG",
    "Injeksi",
    "Nakes Feses",
    "Pap smear",
    "Petugas Loading",
    "Plebo",
    "Rectal",
    "Rontgen",
    "Spirometri",
    "TTV",
    "Treadmill",
    "USG Abdomen",
    "USG Mammae",
    "Visus"
];

echo "Menggunakan " . count($officialSkills) . " keahlian resmi sebagai acuan.\n";

// 1. Fetch ALL records
$query = "SELECT * FROM man_powers";
$stmt = $db->prepare($query);
$stmt->execute();
$all_staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Memproses " . count($all_staff) . " data petugas...\n\n";

$updatedCount = 0;
foreach ($all_staff as $staff) {
    $needsUpdate = false;
    $logMsg = "[ID: {$staff['id']}] Name: {$staff['name']} - ";
    
    // Fix Status
    $currentStatus = trim($staff['status']);
    $newStatus = ucfirst(strtolower($currentStatus));
    if ($newStatus !== $staff['status']) {
        $logMsg .= "Status diperbaiki ($currentStatus -> $newStatus). ";
        $needsUpdate = true;
    }

    // Fix Skills
    $currentSkills = json_decode($staff['skills'], true) ?? [];
    $newSkills = [];
    $skillChanges = [];
    
    foreach ($currentSkills as $s) {
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
            $newSkills[] = $s; 
        }
    }

    if (!empty($skillChanges)) {
        $logMsg .= "Skills diperbaiki (" . implode(", ", $skillChanges) . "). ";
    }

    if ($needsUpdate) {
        $updateQuery = "UPDATE man_powers SET status = :status, skills = :skills WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(":status", $newStatus);
        $skillsJson = json_encode($newSkills);
        $updateStmt->bindParam(":skills", $skillsJson);
        $updateStmt->bindParam(":id", $staff['id']);
        
        if ($updateStmt->execute()) {
            echo $logMsg . "HASIL: SUKSES\n";
            $updatedCount++;
        } else {
            echo $logMsg . "HASIL: GAGAL\n";
        }
    }
}

echo "\n------------------------------------------------\n";
echo "Perbaikan Selesai!\n";
echo "Total Data Diproses: " . count($all_staff) . "\n";
echo "Total Data Diupdate: $updatedCount\n";
echo "------------------------------------------------\n";
echo "</pre>";

/**
 * Update System Setting
 */
echo "<h3>Sinkronisasi Pengaturan Sistem...</h3>";
$codes = [];
$i = 1;
foreach ($officialSkills as $os) {
    $codes[] = "SKILL_$i=$os";
    $i++;
}
$newSettingValue = implode("\n", $codes);

$updateSettingQuery = "UPDATE system_settings SET value = :val WHERE `key` = 'rab_personnel_codes'";
$updateSettingStmt = $db->prepare($updateSettingQuery);
$updateSettingStmt->bindParam(":val", $newSettingValue);

if ($updateSettingStmt->execute()) {
    echo "<p style='color: green;'>Pengaturan RAB Personnel Codes telah disinkronkan!</p>";
} else {
    echo "<p style='color: red;'>Gagal sinkronisasi pengaturan sistem.</p>";
}
?>
