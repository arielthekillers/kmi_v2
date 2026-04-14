<?php
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Check if column exists first
    $stmt = $db->query("DESCRIBE attendance_logs");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('petugas_id', $columns)) {
        echo "Adding petugas_id column...\n";
        $db->exec("ALTER TABLE attendance_logs ADD COLUMN petugas_id INT AFTER note");
        echo "Successfully added petugas_id column.\n";
    } else {
        echo "Column petugas_id already exists.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
