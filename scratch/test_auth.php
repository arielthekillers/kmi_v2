<?php
// scratch/test_auth.php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../helpers/auth.php';

// Mock session/auth if needed, but the functions use auth_get_role() and auth_get_user_id()
// Let's create a test function that we can call with different user contexts

function test_piket($userId, $role, $dayName, $type = 'syeikh') {
    $db = \App\Core\Database::getInstance();
    
    // Simulate user session
    $_SESSION['user'] = [
        'id' => $userId,
        'role' => $role,
        'username' => 'testuser'
    ];
    
    // The date that corresponds to $dayName
    // Let's assume Saturday 2026-04-11, Sunday 2026-04-12, etc.
    $dayMap = [
        'Sabtu' => '2026-04-11',
        'Ahad' => '2026-04-12',
        'Senin' => '2026-04-13',
        'Selasa' => '2026-04-14',
        'Rabu' => '2026-04-15',
        'Kamis' => '2026-04-16'
    ];
    $date = $dayMap[$dayName] ?? null;
    
    $isSyeikh = auth_is_syeikh_diwan_today($date);
    $isKeliling = auth_is_piket_keliling_today($date);
    
    echo "User $userId ($role) on $dayName ($date):\n";
    echo "  Syeikh: " . ($isSyeikh ? "YES" : "NO") . "\n";
    echo "  Keliling: " . ($isKeliling ? "YES" : "NO") . "\n\n";
}

// 1. Manually check what's in the DB
$db = \App\Core\Database::getInstance()->getConnection();
// Find all rows for user 81
$stmt = $db->prepare("SELECT * FROM piket_schedule WHERE user_id = ?");
$stmt->execute([81]);
echo "Full schedule for User 81:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "-------------------\n";

// 2. Perform tests
test_piket(1, 'admin', 'Senin'); // Should be YES always
// 2. Perform tests
test_piket(1, 'admin', 'Senin'); // Should be YES always
test_piket(999, 'pengajar', 'Senin'); // Should be NO

echo "Found real piket: User 81 on Sabtu\n";
test_piket(81, 'pengajar', 'Sabtu'); // Should be YES
test_piket(81, 'pengajar', 'Senin'); // Should be NO (unless it's in the list above)
