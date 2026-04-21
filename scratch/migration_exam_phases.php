<?php
require_once 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

try {
    // 1. Create exam_sessions
    echo "Creating exam_sessions table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS exam_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        academic_year_id INT NOT NULL,
        type ENUM('UUPT', 'UPT', 'UUAT', 'UAT') NOT NULL,
        is_open TINYINT DEFAULT 0,
        is_active TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(academic_year_id, type)
    )");

    // 2. Create exam_committees
    echo "Creating exam_committees table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS exam_committees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exam_session_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (exam_session_id) REFERENCES exam_sessions(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(exam_session_id, user_id)
    )");

    // 3. Update exams table
    echo "Updating exams table...\n";
    // Check if column exists first
    $res = $db->query("SHOW COLUMNS FROM exams LIKE 'exam_session_id'");
    if (!$res->fetch()) {
        $db->exec("ALTER TABLE exams ADD COLUMN exam_session_id INT AFTER academic_year_id");
    }

    // 4. Initialize sessions for active year
    $yearStmt = $db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
    $activeYear = $yearStmt->fetch();
    if ($activeYear) {
        $ayId = $activeYear['id'];
        $types = ['UUPT', 'UPT', 'UUAT', 'UAT'];
        foreach ($types as $type) {
            $check = $db->prepare("SELECT id FROM exam_sessions WHERE academic_year_id = ? AND type = ?");
            $check->execute([$ayId, $type]);
            if (!$check->fetch()) {
                $stmt = $db->prepare("INSERT INTO exam_sessions (academic_year_id, type, is_open, is_active) VALUES (?, ?, 0, 0)");
                $stmt->execute([$ayId, $type]);
                echo "Session $type initialized for AY $ayId.\n";
            }
        }
    }

    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
