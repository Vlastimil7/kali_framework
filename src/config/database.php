<?php

// src/config/database.php

return [
    'driver' => (string)env('DB_DRIVER', 'mysql'),
    'host' => (string)env('DB_HOST', 'localhost'),
    'port' => (int)env('DB_PORT', 3306),
    'dbname' => (string)env('DB_NAME', ''),
    'username' => (string)env('DB_USERNAME', ''),
    'password' => (string)env('DB_PASSWORD', ''),
    'charset' => (string)env('DB_CHARSET', 'utf8mb4'),
];
