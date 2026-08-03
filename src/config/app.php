<?php

$environment = (string)env('APP_ENV', 'development');
$production = $environment === 'production';

return [
    'name' => (string)env('APP_NAME', 'VK-DEV'),
    'base_path' => dirname(dirname(__DIR__)),
    'env' => $environment,
    'debug' => filter_var(env('APP_DEBUG', true), FILTER_VALIDATE_BOOL),
    'timezone' => (string)env('APP_TIMEZONE', 'Europe/Prague'),
    'base_url' => $production
        ? (string)env('BASE_URL', '')
        : (string)env('BASE_URL_DEV', '/__Framework/1_v0/public'),
    'site_url' => $production
        ? (string)env('SITE_URL', 'https://web.kalasekvyvoj.cz')
        : (string)env('SITE_URL_DEV', 'http://localhost/__Framework/1_v0/public/'),
];
