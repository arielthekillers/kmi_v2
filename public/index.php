<?php

// Set Default Timezone to WIB (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../helpers/utilities.php';

// Simple autoloader
spl_autoload_register(function ($class) {
    // Prefix "App\" maps to "../app/"
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

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

require_once __DIR__ . '/../app/Core/App.php';

$app = new \App\Core\App();
$app->run();
