<?php

if (!function_exists('debug_dump')) {
    function debug_dump($var, $die = true)
    {
        echo '<pre class="bg-gray-100 p-4 rounded border border-gray-300 overflow-auto text-sm">';
        var_dump($var);
        echo '</pre>';
        if ($die) die();
    }
}

if (!function_exists('url')) {
    function url($path = '') {
        // Simple base URL detection
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        // If path is a full URL, return it as is
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'];
        $baseDir = dirname($scriptName);
        
        // If we are in public, go up one level to get app root
        if (basename($baseDir) === 'public') {
            $baseDir = dirname($baseDir);
        }
        
        // Clean backslashes on Windows
        $baseDir = str_replace('\\', '/', $baseDir);
        
        // Ensure leading slash if not root
        if ($baseDir !== '/' && substr($baseDir, 0, 1) !== '/' && $baseDir !== '.') {
            $baseDir = '/' . $baseDir;
        }
        
        // Remove trailing slash
        $baseDir = rtrim($baseDir, '/');
        
        // Check if path already starts with baseDir (to avoid double prepending like /kmi/kmi)
        // We check against the path with a leading slash for a robust match
        $pathWithSlash = '/' . ltrim($path, '/');
        if ($baseDir !== '' && strpos($pathWithSlash, $baseDir . '/') === 0) {
            return $protocol . "://" . $host . $pathWithSlash;
        }
        
        // Exact match for baseDir
        if ($baseDir !== '' && $pathWithSlash === $baseDir) {
            return $protocol . "://" . $host . $baseDir . '/';
        }

        // Clean input path for normal relative usage
        $path = ltrim($path, '/');
        
        return $protocol . "://" . $host . $baseDir . '/' . $path;
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = null) {
        $model = new \App\Models\SettingModel();
        return $model->get($key, $default);
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        if (function_exists('url')) {
            header("Location: " . url($url));
        } else {
            header("Location: " . $url);
        }
        exit;
    }
}
if (!function_exists('get_active_academic_year')) {
    function get_active_academic_year() {
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT name FROM academic_years WHERE is_active = 1 LIMIT 1");
            return $stmt->fetchColumn() ?: 'None';
        } catch (\Exception $e) {
            return 'None';
        }
    }
}

if (!function_exists('get_active_academic_year_id')) {
    function get_active_academic_year_id() {
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
            $id = $stmt->fetchColumn();
            return $id ? (int)$id : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
if (!function_exists('get_piket_session_from_hour')) {
    /**
     * Maps lesson hour (1-7) to picket session (1-4).
     * @param int|string $hour The lesson hour
     * @return int|null The session ID or null if not mapped
     */
    function get_piket_session_from_hour($hour) {
        $hour = (int)$hour;
        if ($hour >= 1 && $hour <= 2) return 1;
        if ($hour >= 3 && $hour <= 4) return 2;
        if ($hour >= 5 && $hour <= 6) return 3;
        if ($hour >= 7) return 4;
        return null;
    }
}

if (!function_exists('terbilang_arab')) {
    /**
     * Converts a number (0-100) to its feminine Arabic words spelling
     * since grade (الدرجة) is feminine.
     */
    function terbilang_arab($score) {
        $num = (int)round($score);
        if ($num < 0 || $num > 100) return '';
        if ($num === 0) return 'صفر';
        if ($num === 100) return 'مائة';
        
        $tens = [
            10 => 'عشر',
            20 => 'عشرون',
            30 => 'ثلاثون',
            40 => 'أربعون',
            50 => 'خمسون',
            60 => 'ستون',
            70 => 'سبعون',
            80 => 'ثمانون',
            90 => 'تسعون'
        ];
        
        $units = [
            1 => 'واحدة',
            2 => 'اثنتان',
            3 => 'ثلاث',
            4 => 'أربع',
            5 => 'خمس',
            6 => 'ست',
            7 => 'سبع',
            8 => 'ثمان',
            9 => 'تسع'
        ];
        
        if ($num < 10) {
            return $units[$num];
        }
        
        if ($num % 10 === 0) {
            return $tens[$num];
        }
        
        $unit = $num % 10;
        $ten = (int)(floor($num / 10) * 10);
        
        if ($ten === 10) {
            if ($unit === 1) return 'إحدى عشرة';
            if ($unit === 2) return 'اثنتا عشرة';
            return $units[$unit] . ' عشرة';
        }
        
        return $units[$unit] . ' و' . $tens[$ten];
    }
}

if (!function_exists('log_activity')) {
    /**
     * Mencatat aktivitas pengguna ke dalam database.
     * 
     * @param string $action Deskripsi tindakan yang dilakukan pengguna
     */
    function log_activity($action)
    {
        if (!function_exists('auth_get_current_user')) {
            require_once __DIR__ . '/auth.php';
        }
        
        $user = auth_get_current_user();
        
        $userId = $user['id'] ?? null;
        $username = $user['username'] ?? 'guest';
        $nama = $user['nama'] ?? 'Guest';
        $role = $user['role'] ?? 'guest';
        
        $page = $_SERVER['REQUEST_URI'] ?? '/';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        try {
            $db = \App\Core\Database::getInstance();
            $db->query(
                "INSERT INTO activity_logs (user_id, username, nama, role, action, page, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$userId, $username, $nama, $role, $action, $page, $ipAddress, $userAgent]
            );
        } catch (\Exception $e) {
            error_log("Gagal mencatat log aktifitas: " . $e->getMessage());
        }
    }
}

if (!function_exists('calculate_merged_grade')) {
    /**
     * Menggabungkan nilai ujian tulis (score_final) dan lisan (score_oral) berdasarkan aturan pondok.
     * 
     * @param float|int|string|null $tulis Nilai ujian tulis (score_final)
     * @param float|int|string|null $lisan Nilai ujian lisan (score_oral)
     * @param int $hasOral Pengaturan lisan ujian (0 = tulis saja, 1 = tulis & lisan, 2 = lisan saja)
     * @return int|null Nilai akhir rapor/leger
     */
    function calculate_merged_grade($tulis, $lisan, $hasOral) {
        $tVal = ($tulis !== null && $tulis !== '' && $tulis !== '-') ? $tulis : null;
        $lVal = ($lisan !== null && $lisan !== '' && $lisan !== '-') ? $lisan : null;

        if ($hasOral == 0) {
            return ($tVal !== null) ? (int)round($tVal) : null;
        }
        if ($hasOral == 2) {
            return ($lVal !== null) ? (int)round($lVal) : null;
        }
        if ($hasOral == 1) {
            $hasTulis = ($tVal !== null);
            $hasLisan = ($lVal !== null);
            
            if ($hasTulis && $hasLisan) {
                $T = (int)round($tVal);
                $S = (int)round($lVal);
                if ($S < $T) {
                    return $T;
                } else {
                    return (int)ceil(($T + $S) / 2);
                }
            } elseif ($hasTulis) {
                return (int)round($tVal);
            } elseif ($hasLisan) {
                return (int)round($lVal);
            }
        }
        return null;
    }
}



