<?php

require_once __DIR__ . '/../../Core/Database.php';

use App\Core\Database;

$jsonFile = __DIR__ . '/../../../data/pengajar.json';
if (!file_exists($jsonFile)) {
    die("pengajar.json not found at $jsonFile");
}

$pengajarData = json_decode(file_get_contents($jsonFile), true);
$db = Database::getInstance();

$count = 0;
$duplicates = 0;

foreach ($pengajarData as $id => $data) {
    $username = $data['hp']; // Use HP as username
    $password = $data['password']; // Hashed password
    $nama = $data['nama'];
    $role = 'guru';

    // Check if user exists (by username/hp)
    $stmt = $db->query("SELECT id FROM users WHERE username = ?", [$username]);
    if ($stmt->fetch()) {
        echo "User $username ($nama) already exists. Skipping.\n";
        $duplicates++;
        continue;
    }

    // Insert
    $db->query(
        "INSERT INTO users (legacy_id, username, password, nama, role) VALUES (?, ?, ?, ?, ?)",
        [$id, $username, $password, $nama, $role]
    );
    echo "Migrated: $nama ($role)\n";
    $count++;
}

echo "\nMigration Complete.\n";
echo "Imported: $count\n";
echo "Skipped (Duplicate): $duplicates\n";
