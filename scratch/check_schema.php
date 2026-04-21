<?php
require_once 'app/Core/Database.php';
require_once 'app/Models/GradeModel.php';

// Mock Config if needed or just use Database::getInstance()
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE grades");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "--- GRADES TABLE ---\n";
foreach ($cols as $col) {
    echo "{$col['Field']} - {$col['Type']}\n";
}

$stmt = $db->query("DESCRIBE exams");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n--- EXAMS TABLE ---\n";
foreach ($cols as $col) {
    echo "{$col['Field']} - {$col['Type']}\n";
}
