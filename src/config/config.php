<?php

use Core\Config;

$root = dirname(__DIR__, 2);
require_once $root . '/src/helpers/env.php';
loadEnv($root . '/.env');

foreach (['app', 'site', 'database', 'mail', 'cookies'] as $section) {
    Config::set($section, require __DIR__ . '/' . $section . '.php');
}

// Save connection settings now; PDO connects only when first used.
Core\Database::setConfig((array) config('database'));

date_default_timezone_set((string) config('app.timezone', 'Europe/Prague'));
ini_set('display_errors', config('app.debug', false) ? '1' : '0');
error_reporting(config('app.debug', false) ? E_ALL : 0);
