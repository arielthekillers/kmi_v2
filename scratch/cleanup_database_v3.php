<?php
// e:\xampp\htdocs\kmi_v2\scratch\cleanup_database_v3.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    echo "1. Finding Foreign Keys on students table...\n";
    $sql = "SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'students' 
              AND (COLUMN_NAME = 'kelas_id' OR COLUMN_NAME = 'academic_year_id')";
    $fks = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($fks as $fk) {
        $name = $fk['CONSTRAINT_NAME'];
        if ($name === 'PRIMARY') continue;
        echo "   - Dropping FK $name...\n";
        try {
            $pdo->exec("ALTER TABLE students DROP FOREIGN KEY $name");
        } catch (Exception $e) { 
            echo "   - trying as Index...\n";
            try { $pdo->exec("ALTER TABLE students DROP INDEX $name"); } catch (Exception $e2) {}
        }
    }

    echo "2. Dropping indexes again just in case...\n";
    $keys = $pdo->query("SHOW KEYS FROM students")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($keys as $key) {
        $col = $key['Column_name'];
        if ($col === 'kelas_id' || $col === 'academic_year_id') {
            $name = $key['Key_name'];
            echo "   - Dropping index $name...\n";
            try { $pdo->exec("ALTER TABLE students DROP INDEX $name"); } catch (Exception $e) {}
        }
    }

    echo "3. Final attempt to drop columns...\n";
    $pdo->exec("ALTER TABLE students DROP COLUMN kelas_id, DROP COLUMN academic_year_id");
    echo "SUCCESS: columns dropped.\n";
    
} catch (Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
