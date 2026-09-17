<?php

return [
    'name' => (string) env('APP_NAME', 'Kali project'),
    'env' => (string) env('APP_ENV', 'development'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    'timezone' => (string) env('APP_TIMEZONE', 'Europe/Prague'),
    'base_url' => rtrim((string) env('APP_BASE_URL', env('BASE_URL_DEV', '')), '/'),
    'site_url' => rtrim((string) env('APP_URL', env('SITE_URL_DEV', 'http://localhost:8000')), '/'),
];
