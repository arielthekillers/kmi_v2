<?php
require_once 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$tables = ['exams', 'grades', 'academic_years', 'subjects', 'kelas', 'users'];

foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    try {
        $stmt = $db->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']} | {$row['Extra']}\n";
        }
    } catch (Exception $e) {
        echo "Error or table does not exist: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
