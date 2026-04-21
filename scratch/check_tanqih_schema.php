<?php
require_once 'app/Core/Database.php';
try {
    $db = \App\Core\Database::getInstance()->getConnection();
    $stmt = $db->query("DESCRIBE tanqih");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
