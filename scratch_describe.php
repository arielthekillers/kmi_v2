<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

echo "EXAMS TABLE:\n";
$stmt = $db->query('DESCRIBE exams');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\nGRADES TABLE:\n";
$stmt = $db->query('DESCRIBE grades');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
