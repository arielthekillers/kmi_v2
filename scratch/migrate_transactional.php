<?php
// e:\xampp\htdocs\kmi_v2\scratch\migrate_transactional.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    $pdo->beginTransaction();

    echo "1. Linking KELAS to ACADEMIC YEAR...\n";
    // Get active year
    $activeYear = $pdo->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetchColumn();
    if (!$activeYear) throw new Exception("No active academic year found.");

    // Add column if not exists
    $cols = $pdo->query("DESCRIBE kelas")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('academic_year_id', $cols)) {
        $pdo->exec("ALTER TABLE kelas ADD COLUMN academic_year_id INT AFTER id");
        $pdo->exec("UPDATE kelas SET academic_year_id = $activeYear");
        echo "   - Column added and set to year ID $activeYear\n";
    }

    echo "2. Creating STUDENT_ENROLLMENTS table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        academic_year_id INT NOT NULL,
        kelas_id INT NOT NULL,
        status ENUM('Active', 'Moved', 'Graduated', 'Out') DEFAULT 'Active',
        start_date DATE NULL,
        end_date DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (student_id),
        INDEX (academic_year_id),
        INDEX (kelas_id)
    )");
    echo "   - Table created.\n";

    echo "3. Migrating current Student-Class relations...\n";
    // Important: Prevent double migration
    $count = $pdo->query("SELECT COUNT(*) FROM student_enrollments")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO student_enrollments (student_id, academic_year_id, kelas_id, status, start_date)
                    SELECT id, academic_year_id, kelas_id, 'Active', CURDATE() 
                    FROM students 
                    WHERE kelas_id IS NOT NULL AND academic_year_id IS NOT NULL");
        $inserted = $pdo->lastInsertId() ? $pdo->query("SELECT COUNT(*) FROM student_enrollments")->fetchColumn() : 0;
        echo "   - Successfully migrated $inserted enrollment records.\n";
    } else {
        echo "   - Skipping migration: Table already has data.\n";
    }

    $pdo->commit();
    echo "\nSUCCESS: Database architecture updated.\n";
    echo "NEXT STEP: Refactor Model and Controller to use student_enrollments table.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "FAILURE: " . $e->getMessage() . "\n";
}
