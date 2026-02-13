<?php
require 'config/database.php';
$db = (new Database())->getConnection();

$holidays = [
    ['2026-01-01', 'Tahun Baru 2026 Masehi'],
    ['2026-01-16', 'Isra Mikraj Nabi Muhammad SAW'],
    ['2026-03-19', 'Hari Suci Nyepi (Tahun Baru Saka 1948)'],
    ['2026-04-03', 'Wafat Yesus Kristus'],
    ['2026-05-01', 'Hari Buruh Internasional'],
    ['2026-05-14', 'Kenaikan Yesus Kristus'],
    ['2026-05-27', 'Hari Raya Idul Adha 1447 H'],
    ['2026-06-01', 'Hari Lahir Pancasila'],
    ['2026-06-16', 'Tahun Baru Islam 1448 H'],
    ['2026-08-17', 'Hari Kemerdekaan Republik Indonesia'],
    ['2026-08-25', 'Maulid Nabi Muhammad SAW'],
    ['2026-12-25', 'Hari Raya Natal']
];

foreach ($holidays as $h) {
    $check = $db->prepare("SELECT id FROM national_holidays WHERE holiday_date = ?");
    $check->execute([$h[0]]);
    if (!$check->fetch()) {
        $stmt = $db->prepare("INSERT INTO national_holidays (holiday_date, description) VALUES (?, ?)");
        if ($stmt->execute($h)) {
            echo "Inserted: " . $h[0] . " - " . $h[1] . "\n";
        } else {
            echo "Failed: " . $h[0] . "\n";
        }
    } else {
        echo "Skipped (Exists): " . $h[0] . "\n";
    }
}
echo "Done.\n";
