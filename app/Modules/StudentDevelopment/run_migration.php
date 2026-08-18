<?php
// Temporary script to execute the migration SQL.
require_once __DIR__ . '/../../Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    $sql = file_get_contents(__DIR__ . '/create_tables.sql');
    
    // Split by semicolons, but ignore semicolons inside quotes/parentheses. 
    // Since our file is clean, we can just split by standard statement boundaries,
    // or run the whole SQL as a multi-query.
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
    $db->exec($sql);
    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
