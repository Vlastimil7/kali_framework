<?php
// src/config/config.php


define('BASE_PATH', dirname(dirname(__DIR__)));

// Vytvoř helpers složku pokud neexistuje
if (!is_dir(BASE_PATH . '/src/helpers')) {
    mkdir(BASE_PATH . '/src/helpers', 0755, true);
}

// Načti env helper
require_once BASE_PATH . '/src/helpers/env.php';

// Načti .env soubor
loadEnv(BASE_PATH . '/.env');

// Environment based configuration
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_DEBUG', env('APP_DEBUG', 'true') === 'true');

// URLs podle prostředí
if (APP_ENV === 'production') {
    define('BASE_URL', env('BASE_URL', ''));
    define('SITE_URL', env('SITE_URL', 'https://web.kalasekvyvoj.cz'));
} else {
    define('BASE_URL', env('BASE_URL_DEV', '/__Framework/1_v0/public'));
    define('SITE_URL', env('SITE_URL_DEV', 'http://localhost/__Framework/1_v0/public/'));
}

// SMTP nastavení
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_USERNAME', env('SMTP_USERNAME'));
define('SMTP_PASSWORD', env('SMTP_PASSWORD'));
define('SMTP_PORT', env('SMTP_PORT', 587));
define('MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS'));
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME'));

// reCAPTCHA
define('RECAPTCHA_SITE_KEY', env('RECAPTCHA_SITE_KEY'));
define('RECAPTCHA_SECRET_KEY', env('RECAPTCHA_SECRET_KEY'));

define('AI_PROVIDER', env('AI_PROVIDER', 'openai'));

//GEMINI_API
define('GEMINI_API_KEY', env('GEMINI_API_KEY'));

//OPEN AI_API
define('OPENAI_API_KEY', env('OPENAI_API_KEY'));

//ANTHROPIC_API
define('ANTHROPIC_API_KEY', env('ANTHROPIC_API_KEY'));
define('APP_TIMEZONE', 'Europe/Prague');


// Comgate config
define('COMGATE_MERCHANT', env('COMGATE_MERCHANT'));
define('COMGATE_SECRET', env('COMGATE_SECRET'));
define('COMGATE_TEST', env('COMGATE_TEST', 'true') === 'true');

define('COMGATE_CURRENCY', env('COMGATE_CURRENCY', 'CZK'));
define('COMGATE_LANG', env('COMGATE_LANG', 'cs'));
define('COMGATE_COUNTRY', env('COMGATE_COUNTRY', 'CZ'));

// URL pro návrat a notifikace (doplníš podle domény projektu)
define('COMGATE_RETURN_URL',  env('COMGATE_RETURN_URL', SITE_URL . '/payment/comgate/return'));
define('COMGATE_NOTIFY_URL',  env('COMGATE_NOTIFY_URL', SITE_URL . '/payment/comgate/notify'));

// Prefix pro kódy voucherů (např. MIDO → 2025MIDO00001)
define('VOUCHER_CODE_PREFIX', env('VOUCHER_CODE_PREFIX', 'MIDO'));

// Google OAuth
define('GOOGLE_OAUTH_CLIENT_ID', env('GOOGLE_OAUTH_CLIENT_ID'));
define('GOOGLE_OAUTH_CLIENT_SECRET', env('GOOGLE_OAUTH_CLIENT_SECRET'));
define('GOOGLE_OAUTH_REDIRECT_URI', env('GOOGLE_OAUTH_REDIRECT_URI'));


// Error reporting
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Načtení databázové konfigurace
$config = require_once BASE_PATH . '/src/config/database.php';