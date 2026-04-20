<?php
// e:\xampp\htdocs\kmi_v2\scratch\cleanup_database_v2.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    echo "Inspecting indexes on students...\n";
    $keys = $pdo->query("SHOW KEYS FROM students")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($keys as $key) {
        if ($key['Column_name'] === 'academic_year_id' || $key['Column_name'] === 'kelas_id') {
            $keyName = $key['Key_name'];
            echo "Dropping index $keyName...\n";
            try {
                $pdo->exec("ALTER TABLE students DROP INDEX $keyName");
            } catch (Exception $e) { echo "   - already dropped or error: " . $e->getMessage() . "\n"; }
        }
    }

    echo "Dropping old columns from students table...\n";
    $pdo->exec("ALTER TABLE students DROP COLUMN kelas_id, DROP COLUMN academic_year_id");
    echo "SUCCESS: columns dropped.\n";
} catch (Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
