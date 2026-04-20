<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("DESCRIBE piket_schedule");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in piket_schedule:\n";
    print_r($cols);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
