<?php
require_once 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$sessions = $db->query("SELECT * FROM exam_sessions")->fetchAll();
$sessionMap = [];
foreach ($sessions as $s) {
    if ($s['type'] === 'UUPT') $sessionMap[1] = $s['id'];
    if ($s['type'] === 'UUAT') $sessionMap[2] = $s['id'];
}

// Update exams that have no session_id
$stmt = $db->prepare("UPDATE exams SET exam_session_id = ? WHERE semester = ? AND exam_session_id IS NULL");

foreach ($sessionMap as $semester => $sessionId) {
    $stmt->execute([$sessionId, $semester]);
    echo "Updated semester $semester exams to use session $sessionId.\n";
}

// Set UUPT as active by default for AY 1
$db->exec("UPDATE exam_sessions SET is_active = 1, is_open = 1 WHERE type = 'UUPT' AND academic_year_id = 1");
echo "UUPT session activated and opened.\n";
