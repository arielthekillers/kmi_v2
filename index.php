<?php

// Set Default Timezone
date_default_timezone_set('Asia/Jakarta');

// Include Helpers
require_once __DIR__ . '/helpers/utilities.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/layout.php';

// Simple Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Run App
require_once __DIR__ . '/app/Core/App.php';
$app = new \App\Core\App();
$app->run();
