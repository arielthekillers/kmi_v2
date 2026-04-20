<?php
// e:\xampp\htdocs\kmi_v2\scratch\debug_teachers.php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "User Roles in DB:\n";
$roles = $pdo->query("SELECT DISTINCT role FROM users")->fetchAll(PDO::FETCH_COLUMN);
print_r($roles);

echo "\nTeachers found (role='guru'):\n";
$teachers = $pdo->query("SELECT id, nama FROM users WHERE role = 'guru'")->fetchAll(PDO::FETCH_ASSOC);
print_r($teachers);

echo "\nTeachers found (role='teacher'):\n";
$teachers2 = $pdo->query("SELECT id, nama FROM users WHERE role = 'teacher'")->fetchAll(PDO::FETCH_ASSOC);
print_r($teachers2);
