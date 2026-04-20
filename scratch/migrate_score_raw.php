<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
try {
    $db->exec("ALTER TABLE grades MODIFY score_raw VARCHAR(10) DEFAULT NULL");
    echo "Success: score_raw changed to VARCHAR(10)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
