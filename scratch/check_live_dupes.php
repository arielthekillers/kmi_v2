<?php
$config = require 'app/config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
$db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

echo "DUPLICATE CHECK ON YOUR LIVE DATABASE:\n";
echo "--------------------------------------\n";

$stmt = $db->query("
    SELECT student_id, exam_id, COUNT(*) as row_count
    FROM grades 
    GROUP BY student_id, exam_id 
    HAVING row_count > 1
");
$dupes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($dupes)) {
    echo "No duplicate data found for (student_id, exam_id).\n";
} else {
    echo "YES! Found " . count($dupes) . " students with duplicate records.\n\n";
    echo "Example Duplicates:\n";
    foreach (array_slice($dupes, 0, 10) as $d) {
        echo "- Student ID {$d['student_id']} for Exam ID {$d['exam_id']} has {$d['row_count']} rows.\n";
    }
}
