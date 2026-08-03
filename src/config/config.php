<?php

use Core\Config;

$basePath = dirname(dirname(__DIR__));

require_once $basePath . '/src/helpers/env.php';
loadEnv($basePath . '/.env');

require_once $basePath . '/src/helpers/config_helper.php';

foreach (['app', 'database', 'mail', 'recaptcha', 'ai', 'payments', 'oauth', 'vouchers'] as $section) {
    $values = require $basePath . '/src/config/' . $section . '.php';
    if (!is_array($values)) {
        throw new RuntimeException("Configuration section {$section} must return an array.");
    }
    Config::set($section, $values);
}

date_default_timezone_set((string)config('app.timezone', 'Europe/Prague'));

if (config('app.env') === 'production' && !config('app.debug', false)) {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
