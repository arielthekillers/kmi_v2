<?php
require_once 'app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Check if column exists first
    $stmt = $db->query("SHOW COLUMNS FROM grades LIKE 'no_bayanat'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $db->exec("ALTER TABLE grades ADD COLUMN no_bayanat INT DEFAULT NULL AFTER exam_id");
        echo "Successfully added 'no_bayanat' column to 'grades' table.\n";
    } else {
        echo "Column 'no_bayanat' already exists.\n";
    }
} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
