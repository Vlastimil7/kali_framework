<?php
// src/config/database.php

// Database setting using environment variables
return [
    'db' => [
        'host' => env('DB_HOST'),
        'dbname' => env('DB_NAME'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD')
    ],
    'app' => [
        'name' => env('MAIL_FROM_NAME'),
        'email' => env('MAIL_FROM_ADDRESS')
    ]
];
