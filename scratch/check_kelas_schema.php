<?php
// e:\xampp\htdocs\kmi_v2\scratch\check_kelas_schema.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "--- Schema: KELAS ---\n";
$stmt = $pdo->query("DESCRIBE kelas");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- Schema: ACADEMIC_YEARS ---\n";
$stmt = $pdo->query("DESCRIBE academic_years");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- Active Year ---\n";
$stmt = $pdo->query("SELECT * FROM academic_years WHERE is_active = 1 LIMIT 1");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
