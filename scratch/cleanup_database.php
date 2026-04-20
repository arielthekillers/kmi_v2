<?php
// e:\xampp\htdocs\kmi_v2\scratch\cleanup_database.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    echo "Dropping old columns from students table...\n";
    $pdo->exec("ALTER TABLE students DROP COLUMN kelas_id, DROP COLUMN academic_year_id");
    echo "SUCCESS: columns dropped.\n";
} catch (Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
