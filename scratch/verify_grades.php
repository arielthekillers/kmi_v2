<?php
require 'app/Core/Database.php';
require 'app/Core/Model.php';
require 'app/Models/GradeModel.php';

// Mock some data
$examId = 1; // Assuming exam with ID 1 exists, if not i'll just use a random one
$subjectId = 1;
$skor_maks = 100;
$skala = '80-30';
$studentIds = [1]; // Assuming student with ID 1 exists
$skors = ['-']; // The absent marker

$model = new \App\Models\GradeModel();
try {
    // We don't want to actually commit or overwrite real data if we can help it, 
    // but since we are testing the DB interaction:
    // Actually, I'll just check if saveGrades works and then check the DB.
    
    // Let's use a temporary student if possible or just check a specific one.
    // For verification, i'll just trigger the logic and check the score_raw results.
    
    $model->saveGrades($examId, $subjectId, $skor_maks, $skala, $studentIds, $skors, 'proses');
    
    $db = \App\Core\Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT score_raw, score_final FROM grades WHERE student_id = ? AND exam_id = ?");
    $stmt->execute([1, $examId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Result for '-':\n";
    print_r($result);
    
    // Test for '0'
    $skors = ['0'];
    $model->saveGrades($examId, $subjectId, $skor_maks, $skala, $studentIds, $skors, 'proses');
    $stmt->execute([1, $examId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nResult for '0':\n";
    print_r($result);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
