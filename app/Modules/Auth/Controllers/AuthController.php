<?php

namespace App\Modules\Auth\Controllers;

use App\Core\Controller;
use App\Modules\Auth\Models\User;

class AuthController extends Controller {
    public function login() {
        // If already logged in, redirect to dashboard
        session_start();
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }

        $this->view('Auth/Views/login');
    }

    public function attemptLogin() {
        session_start();
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user) {
            if (isset($user['is_active']) && (int)$user['is_active'] !== 1) {
                $_SESSION['error'] = 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.';
                $this->redirect('/login');
                return;
            }
            if (!empty($user['deleted_at'])) {
                $_SESSION['error'] = 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.';
                $this->redirect('/login');
                return;
            }

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                
                $this->redirect('/');
                return;
            }
        }

        $_SESSION['error'] = 'Invalid username or password';
        $this->redirect('/login');
    }

    public function logout() {
        session_start();
        session_destroy();
        $this->redirect('/login');
    }
}
