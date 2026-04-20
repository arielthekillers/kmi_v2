<?php
// e:\xampp\htdocs\kmi_v2\scratch\research_refactor.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "--- Students Summary ---\n";
$total = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$unique = $pdo->query("SELECT COUNT(DISTINCT nis) FROM students")->fetchColumn();
echo "Total Records: $total\n";
echo "Unique NIS: $unique\n";

if ($total > $unique) {
    echo "DUPLICATES DETECTED: " . ($total - $unique) . "\n";
    $dupes = $pdo->query("SELECT nis, COUNT(*) as c FROM students GROUP BY nis HAVING c > 1 LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    print_r($dupes);
}

echo "\n--- Grades Table ---\n";
try {
    $stmt = $pdo->query("DESCRIBE grades");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) { echo "Grades table not found or error.\n"; }

echo "\n--- Attendance Table ---\n";
try {
    $stmt = $pdo->query("DESCRIBE attendance");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) { echo "Attendance table not found or error.\n"; }

echo "\n--- Classes Count ---\n";
$c = $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();
echo "Total Classes: $c\n";
