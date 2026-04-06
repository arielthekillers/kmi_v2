<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$result = $_SESSION['password_change_result'] ?? null;
unset($_SESSION['password_change_result']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Admin Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Ganti Password Admin</h1>
            <p class="text-sm text-gray-600 mb-6">Utility untuk mengubah password administrator</p>
            
            <?php if ($result): ?>
                <div class="mb-4 p-4 rounded-lg <?= $result['success'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= htmlspecialchars($result['message']) ?>
                </div>
            <?php endif; ?>
            
            <form action="<?= url('/change-password/update') ?>" method="POST" class="space-y-4">
                <?= csrf_token_field() ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="admin">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="new_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Minimal 4 karakter">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ketik ulang password">
                </div>
                
                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                    Ubah Password
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <a href="<?= url('/') ?>" class="text-sm text-indigo-600 hover:text-indigo-800">← Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
