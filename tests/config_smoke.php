<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/src/config/config.php';

use Core\Config;
use Core\Database;

foreach (['app', 'database', 'mail', 'recaptcha', 'ai', 'payments', 'oauth', 'vouchers'] as $section) {
    if (!is_array(config($section))) {
        throw new RuntimeException("Missing configuration section: {$section}");
    }
}

if (config('missing.value', 'fallback') !== 'fallback') {
    throw new RuntimeException('Configuration fallback failed.');
}

if (!Config::has('mail.smtp.host') || config('mail.smtp.port') !== (int)env('SMTP_PORT', 587)) {
    throw new RuntimeException('Nested mail configuration failed.');
}

if (defined('BASE_PATH') || defined('BASE_URL') || defined('SMTP_HOST') || defined('COMGATE_SECRET')) {
    throw new RuntimeException('Legacy configuration constants must not be defined.');
}

Database::setConfig((array)config('database'));

echo "config smoke tests passed\n";
