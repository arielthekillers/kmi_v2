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
        
        // Assuming the app is in a subdirectory (e.g. /kmi)
        // We need to find the root of the "kmi" folder relative to document root
        // If we are in /kmi/public/index.php, script_name is /kmi/public/index.php
        // If we are in /kmi/index.php, script_name is /kmi/index.php
        
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $baseDir = dirname($scriptName);
        
        // If we are in public, go up one level to get app root
        if (basename($baseDir) === 'public') {
            $baseDir = dirname($baseDir);
        }
        
        // Clean backslashes on Windows
        $baseDir = str_replace('\\', '/', $baseDir);
        
        // Ensure leading slash
        if (substr($baseDir, 0, 1) !== '/') {
            $baseDir = '/' . $baseDir;
        }
        
        // Remove trailing slash
        $baseDir = rtrim($baseDir, '/');
        
        // Clean input path
        $path = ltrim($path, '/');
        
        return $protocol . "://" . $host . $baseDir . '/' . $path;
    }
}
