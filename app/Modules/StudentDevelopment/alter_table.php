<?php
require_once __DIR__ . '/../../Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    // Add color column if not exists
    $stmt = $db->query("SHOW COLUMNS FROM student_observation_categories LIKE 'color'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE student_observation_categories ADD COLUMN color VARCHAR(20) DEFAULT '#64748b'");
        echo "Column 'color' added successfully to student_observation_categories.\n";
    } else {
        echo "Column 'color' already exists.\n";
    }
} catch (Exception $e) {
    echo "Alter failed: " . $e->getMessage() . "\n";
}
