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
