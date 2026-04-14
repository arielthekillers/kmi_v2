<?php
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    $stmt = $db->query("DESCRIBE attendance_logs");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns in attendance_logs:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
