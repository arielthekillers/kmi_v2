<?php

// Determine which prefix to use based on the environment
$env = getenv('APP_ENV') ?: 'production';
$prefix = ($env === 'production') ? 'PROD_' : 'DEV_';

return [
    'host' => getenv($prefix . 'DB_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1',
    'dbname' => getenv($prefix . 'DB_NAME') ?: getenv('DB_NAME') ?: 'kmi_db',
    'username' => getenv($prefix . 'DB_USER') ?: getenv('DB_USER') ?: 'root',
    'password' => getenv($prefix . 'DB_PASS') ?: getenv('DB_PASS') ?: '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00'",
    ]
];
