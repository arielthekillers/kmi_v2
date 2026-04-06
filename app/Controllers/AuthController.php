<?php

namespace App\Controllers;

use App\Core\Controller;

class AuthController extends Controller {
    public function login() {
        require_once __DIR__ . '/../../helpers/auth.php';
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        $flash = $_SESSION['flash'] ?? null;
        
        if (is_logged_in()) {
            $this->redirect('/');
        }
        
        // Pass flash to view if the view expects it
        // Or the view handles $_SESSION['flash'] directly as in login.php
        $this->view('auth/login');
    }

    public function authenticate() {
        require_once __DIR__ . '/../../helpers/auth.php';
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate CSRF
        csrf_validate_token();

        if (login_user($username, $password)) {
            auth_start_session();
            add_flash('Selamat datang kembali! Anda telah berhasil login.', 'success');
            
            $redirect = $_SESSION['redirect_after_login'] ?? '/';
            if ($redirect === 'index.php') $redirect = '/';
            unset($_SESSION['redirect_after_login']);
            
            $this->redirect($redirect);
        } else {
            add_flash('Username atau password salah.');
            $this->redirect('/login');
        }
    }

    public function logout() {
        require_once __DIR__ . '/../../helpers/auth.php';
        logout_user();
        $this->redirect('/login');
    }
}
