<?php
// e:\xampp\htdocs\kmi_v2\scratch\check_schedules_year.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "--- Academic Years ---\n";
$years = $pdo->query("SELECT id, name, is_active FROM academic_years")->fetchAll(PDO::FETCH_ASSOC);
print_r($years);

echo "\n--- Schedules Distribution ---\n";
$stats = $pdo->query("SELECT academic_year_id, COUNT(*) as total FROM schedules GROUP BY academic_year_id")->fetchAll(PDO::FETCH_ASSOC);
print_r($stats);

echo "\n--- Sample Schedule Row ---\n";
$sample = $pdo->query("SELECT * FROM schedules LIMIT 1")->fetch(PDO::FETCH_ASSOC);
print_r($sample);
