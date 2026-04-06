<?php

require_once __DIR__ . '/../../Core/Database.php';

use App\Core\Database;

$jsonFile = __DIR__ . '/../../../data/users.json';
if (!file_exists($jsonFile)) {
    die("users.json not found");
}

$users = json_decode(file_get_contents($jsonFile), true);
$db = Database::getInstance();

foreach ($users as $key => $user) {
    // The key might be the username or just a generic key. 
    // The inner 'username' seems to be the actual username.
    $username = $user['username'] ?? $key;
    $password = $user['password'];
    $nama = $user['nama'];
    
    // Check if exists
    $stmt = $db->query("SELECT id FROM users WHERE username = ?", [$username]);
    if ($stmt->fetch()) {
        echo "User $username already exists.\n";
        continue;
    }

    $db->query("INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)", [
        $username, $password, $nama, 'admin'
    ]);
    echo "Migrated user: $username\n";
}
