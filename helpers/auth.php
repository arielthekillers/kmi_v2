<?php
// helpers/auth.php

if (defined('NILAIUJIAN_AUTH_LOADED')) return;
define('NILAIUJIAN_AUTH_LOADED', true);

require_once __DIR__ . '/../app/Core/Database.php';

// Set default timezone to match user location (WIB / +07:00)
date_default_timezone_set('Asia/Jakarta');

// Lightweight auth using JSON file and PHP sessions

if (!function_exists('auth_start_session')) {
    function auth_start_session()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session params
            $cookieParams = session_get_cookie_params();
            session_set_cookie_params([
                'lifetime' => $cookieParams['lifetime'],
                'path' => $cookieParams['path'],
                'domain' => $cookieParams['domain'],
                'secure' => isset($_SERVER['HTTPS']), // True if HTTPS
                'httponly' => true, // Check against XSS
                'samesite' => 'Strict' // Checks against CSRF
            ]);
            session_start();
        }
    }
}

if (!function_exists('auth_users_file')) {
    function auth_users_file()
    {
        return __DIR__ . '/../data/users.json';
    }
}

if (!function_exists('auth_ensure_users')) {
    function auth_ensure_users()
    {
        $file = auth_users_file();
        if (!file_exists($file) || filesize($file) === 0) {
            $default = [
                // default admin user: username=admin, password=admin
                'admin' => [
                    'username' => 'admin',
                    'nama' => 'Administrator',
                    'password' => password_hash('admin', PASSWORD_DEFAULT)
                ]
            ];
            file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $default;
        }

        $data = json_decode(file_get_contents($file), true) ?? [];

        // If file contains an empty list (e.g., []) or non-associative array, reinitialize with default
        if (empty($data) || array_values($data) === $data) {
            $default = [
                'admin' => [
                    'username' => 'admin',
                    'nama' => 'Administrator',
                    'password' => password_hash('admin', PASSWORD_DEFAULT)
                ]
            ];
            file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $default;
        }

        return $data;
    }
}

if (!function_exists('auth_get_users')) {
    function auth_get_users()
    {
        auth_start_session();
        return auth_ensure_users();
    }
}

if (!function_exists('find_user_by_username')) {
    function find_user_by_username($username)
    {
        // Use Database
        $db = \App\Core\Database::getInstance();
        $stmt = $db->query("SELECT * FROM users WHERE username = ?", [$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            return $user;
        }
        
        return null;
    }
}

if (!function_exists('login_user')) {
    function login_user($username, $password)
    {
        auth_start_session();
        $user = find_user_by_username($username);
        
        if (!$user) return false;
        
        // Check password
        if (password_verify($password, $user['password'])) {
            // Prevent session fixation
            session_regenerate_id(true);
            
            // Store user info in session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'nama' => $user['nama'],
                'role' => $user['role'],
                'legacy_id' => $user['legacy_id'] ?? null
            ];
            return true;
        }
        return false;
    }
}

if (!function_exists('logout_user')) {
    function logout_user()
    {
        auth_start_session();
        unset($_SESSION['user']);
        // keep session for flash messages, do not destroy entire session
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in()
    {
        auth_start_session();
        $u = $_SESSION['user'] ?? null;
        if (is_null($u)) return false;
        if (is_string($u)) {
            // Only treat as logged-in if the string matches a known username
            return (bool) find_user_by_username($u);
        }
        if (is_array($u)) {
            $username = $u['username'] ?? null;
            if ($username) return (bool) find_user_by_username($username);
            return false;
        }
        return false;
    }
}

if (!function_exists('get_current_user')) {
    function get_current_user()
    {
        auth_start_session();
        $u = $_SESSION['user'] ?? null;
        if (is_null($u)) return null;

        // If session stores a username string, resolve it to the user record
        if (is_string($u)) {
            $found = find_user_by_username($u);
            if ($found) {
                // Normalize session to array for future usage
                $_SESSION['user'] = [
                    'username' => $found['username'],
                    'nama' => $found['nama'] ?? $found['username']
                ];
                return $_SESSION['user'];
            }
            // Unknown username string -> not a valid user
            return null;
        }

        if (is_array($u)) {
            // Ensure array contains username and nama, attempt to normalize from store
            $username = $u['username'] ?? null;
            if ($username) {
                $found = find_user_by_username($username);
                if ($found) {
                    // return canonical record
                    return [
                        'username' => $found['username'],
                        'nama' => $found['nama'] ?? $found['username']
                    ];
                }
                // if username not found, but array has nama, return as-is
                return $u;
            }
            return $u;
        }

        return null;
    }
}

if (!function_exists('require_login')) {
    function require_login()
    {
        auth_start_session();
        if (empty($_SESSION['user'])) {
            // store requested page to return later (optional)
            $_SESSION['redirect_after_login'] = basename($_SERVER['PHP_SELF']);
            header('Location: login.php');
            exit;
        }
    }
}

if (!function_exists('add_flash')) {
    function add_flash($msg, $type = 'error')
    {
        auth_start_session();
        $_SESSION['flash'] = [
            'message' => $msg,
            'type' => $type
        ];
    }
}

if (!function_exists('get_flash')) {
    function get_flash()
    {
        auth_start_session();
        if (!empty($_SESSION['flash'])) {
            $m = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $m;
        }
        return null;
    }
}

// New: avoid collision with PHP built-in get_current_user()
if (!function_exists('auth_get_current_user')) {
    function auth_get_current_user()
    {
        auth_start_session();
        $u = $_SESSION['user'] ?? null;
        if (is_null($u)) return null;

        if (is_string($u)) {
            $found = find_user_by_username($u);
            if ($found) {
                // Normalize to canonical array
                $_SESSION['user'] = [
                    'username' => $found['username'],
                    'nama' => $found['nama'] ?? $found['username'],
                    'role' => $found['role'] ?? 'admin',
                    'id' => $found['id'] ?? null
                ];
                return $_SESSION['user'];
            }
            return null;
        }

        if (is_array($u)) {
            $username = $u['username'] ?? null;
            if ($username) {
                $found = find_user_by_username($username);
                if ($found) {
                    // Update session with full details (role/id) so next call is faster
                    $_SESSION['user'] = [
                        'username' => $found['username'],
                        'nama' => $found['nama'] ?? $found['username'],
                        'role' => $found['role'] ?? 'admin',
                        'id' => $found['id'] ?? null
                    ];
                    return $_SESSION['user'];
                }
                return $u;
            }
            return $u;
        }

        return null;
    }
}

if (!function_exists('auth_get_display_name')) {
    function auth_get_display_name()
    {
        $u = auth_get_current_user();
        if (!$u) return '';
        return $u['nama'] ?? $u['username'] ?? '';
    }
}

if (!function_exists('auth_get_role')) {
    function auth_get_role()
    {
        $u = auth_get_current_user();
        if (!$u) return null;
        return $u['role'] ?? 'admin'; // Default to admin for existing sessions without role? Or strict 'guest'? 
        // Existing admin users in session might not have 'role' set until re-login. 
        // Let's assume 'admin' if role matches defined admin users, or fallback.
        // Actually, best to force re-login if structure changes, but for now default 'admin' is safer for back-compat.
    }
}

if (!function_exists('auth_get_user_id')) {
    function auth_get_user_id()
    {
        $u = auth_get_current_user();
        return $u['id'] ?? null;
    }
}

    if (!function_exists('auth_is_syeikh_diwan_today')) {
    /**
     * Check if current user is Syeikh Diwan today
     * @param string|null $date Specific date to check, or null for today
     * @return bool
     */
    function auth_is_syeikh_diwan_today($date = null)
    {
        $role = auth_get_role();
        if ($role === 'admin') return true; 
        if ($role !== 'pengajar') return false;

        $userId = auth_get_user_id();
        if (!$userId) return false;

        $timestamp = $date ? strtotime($date) : time();
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        $dayNameEnglish = date('D', $timestamp);
        $dayName = $dayMap[$dayNameEnglish] ?? '';

        // Use Database instead of JSON
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $yearId = get_active_academic_year_id();
            $stmt = $db->prepare("SELECT id FROM piket_schedule WHERE user_id = ? AND day = ? AND type = 'syeikh' AND academic_year_id = ?");
            $stmt->execute([$userId, $dayName, $yearId]);
            return (bool) $stmt->fetch();
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('auth_is_piket_keliling_today')) {
    /**
     * Check if current user is Piket Keliling today
     * @param string|null $date Specific date to check, or null for today
     * @return bool
     */
    function auth_is_piket_keliling_today($date = null)
    {
        $role = auth_get_role();
        if ($role === 'admin') return true; 
        if ($role !== 'pengajar') return false;

        $userId = auth_get_user_id();
        if (!$userId) return false;

        $timestamp = $date ? strtotime($date) : time();
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        $dayNameEnglish = date('D', $timestamp);
        $dayName = $dayMap[$dayNameEnglish] ?? '';

        // Use Database instead of JSON
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $yearId = get_active_academic_year_id();
            $stmt = $db->prepare("SELECT id FROM piket_schedule WHERE user_id = ? AND day = ? AND type = 'keliling' AND academic_year_id = ?");
            $stmt->execute([$userId, $dayName, $yearId]);
            return (bool) $stmt->fetch();
        } catch (\Exception $e) {
            return false;
        }
    }
}

// CSRF Protection Functions
if (!function_exists('csrf_generate_token')) {
    /**
     * Generate and store CSRF token in session
     * @return string The generated token
     */
    function csrf_generate_token()
    {
        auth_start_session();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_get_token')) {
    /**
     * Get existing CSRF token or generate new one
     * @return string The CSRF token
     */
    function csrf_get_token()
    {
        return csrf_generate_token();
    }
}

if (!function_exists('csrf_validate_token')) {
    /**
     * Validate CSRF token from POST request
     * @param bool $die If true, die with error message on failure
     * @return bool True if valid, false otherwise
     */
    function csrf_validate_token($die = true)
    {
        auth_start_session();
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        $valid = !empty($token) && !empty($sessionToken) && hash_equals($sessionToken, $token);
        
        if (!$valid && $die) {
            http_response_code(403);
            die('Invalid CSRF token. Please refresh the page and try again.');
        }
        
        return $valid;
    }
}

if (!function_exists('csrf_token_field')) {
    /**
     * Generate hidden input field with CSRF token
     * @return string HTML hidden input field
     */
    function csrf_token_field()
    {
        $token = csrf_get_token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('csrf_input')) {
    /**
     * Alias for csrf_token_field()
     */
    function csrf_input()
    {
        return csrf_token_field();
    }
}


if (!function_exists('require_admin')) {
    function require_admin()
    {
        require_login();
        if (auth_get_role() !== 'admin') {
            header('Location: index.php');
            exit;
        }
    }
}

if (!function_exists('auth_is_panitia')) {
    /**
     * Check if user is a committee member for a specific session or any active session
     */
    function auth_is_panitia($sessionId = null) {
        $role = auth_get_role();
        if ($role === 'admin') return true;
        
        $userId = auth_get_user_id();
        if (!$userId) return false;

        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            if ($sessionId) {
                $stmt = $db->prepare("SELECT id FROM exam_committees WHERE user_id = ? AND exam_session_id = ?");
                $stmt->execute([$userId, $sessionId]);
            } else {
                // Check if panitia for CURRENT active session
                $stmt = $db->prepare("SELECT ec.id FROM exam_committees ec 
                                     JOIN exam_sessions es ON ec.exam_session_id = es.id 
                                     WHERE ec.user_id = ? AND es.is_active = 1");
                $stmt->execute([$userId]);
            }
            return (bool) $stmt->fetch();
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('auth_can_manage_grades')) {
    /**
     * Higher level check for Grade module administration
     */
    function auth_can_manage_grades($sessionId = null) {
        return auth_get_role() === 'admin' || auth_is_panitia($sessionId);
    }
}
