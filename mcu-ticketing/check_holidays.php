<?php
require 'config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT * FROM national_holidays ORDER BY holiday_date ASC");
$holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Current Holidays:\n";
foreach ($holidays as $h) {
    echo $h['holiday_date'] . " - " . $h['description'] . "\n";
}
