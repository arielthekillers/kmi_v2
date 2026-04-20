<?php
// e:\xampp\htdocs\kmi_v2\scratch\inspect_tables.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

function inspectTable($pdo, $table) {
    echo "\n--- Table: $table ---\n";
    try {
        $res = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $col) {
            echo "{$col['Field']} - {$col['Type']}\n";
        }
    } catch (Exception $e) {
        echo "Table does not exist.\n";
    }
}

inspectTable($pdo, 'schedules');
inspectTable($pdo, 'subjects');
inspectTable($pdo, 'users');
inspectTable($pdo, 'student_enrollments');
