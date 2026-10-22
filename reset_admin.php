<?php
/**
 * CLI Script: Reset Password Admin
 * Usage: php reset_admin.php [password_baru]
 */

if (php_sapi_name() !== 'cli') {
    die("Script ini hanya dapat dijalankan melalui Command Line (CLI).\n");
}

require_once __DIR__ . '/app/Core/Database.php';

$newPassword = $argv[1] ?? 'admin123';

try {
    $db = \App\Core\Database::getInstance();
    
    // Cari akun admin
    $stmt = $db->query("SELECT id, username, nama, role FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$admin) {
        echo "[ERROR] Akun dengan role 'admin' tidak ditemukan di database.\n";
        exit(1);
    }

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $upd = $db->query("UPDATE users SET password = ?, password_plain = ? WHERE id = ?", [
        $hashed,
        $newPassword,
        $admin['id']
    ]);

    echo "===========================================\n";
    echo "  RESET PASSWORD ADMIN BERHASIL\n";
    echo "===========================================\n";
    echo "Username      : " . $admin['username'] . "\n";
    echo "Nama          : " . $admin['nama'] . "\n";
    echo "Password Baru : " . $newPassword . "\n";
    echo "===========================================\n";
    echo "Silakan login di web dengan kredensial di atas.\n";

} catch (\Exception $e) {
    echo "[ERROR] Gagal mereset password: " . $e->getMessage() . "\n";
    exit(1);
}
