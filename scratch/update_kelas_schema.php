<?php
// e:\xampp\htdocs\kmi_v2\scratch\update_kelas_schema.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    $pdo->beginTransaction();

    echo "Updating KELAS table schema...\n";

    // Add location and teacher_id
    $cols = $pdo->query("DESCRIBE kelas")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('location', $cols)) {
        $pdo->exec("ALTER TABLE kelas ADD COLUMN location VARCHAR(100) NULL AFTER gender");
        echo "   - Added 'location' column.\n";
    }

    if (!in_array('teacher_id', $cols)) {
        $pdo->exec("ALTER TABLE kelas ADD COLUMN teacher_id INT NULL AFTER location");
        echo "   - Added 'teacher_id' column.\n";
    }

    // Drop jumlah_murid if exists
    if (in_array('jumlah_murid', $cols)) {
        $pdo->exec("ALTER TABLE kelas DROP COLUMN jumlah_murid");
        echo "   - Dropped 'jumlah_murid' column.\n";
    }

    $pdo->commit();
    echo "\nSUCCESS: Kelas schema updated.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "FAILURE: " . $e->getMessage() . "\n";
}
