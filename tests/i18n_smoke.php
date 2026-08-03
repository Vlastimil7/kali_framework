<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', '/subdir');
define('SITE_URL', 'https://example.test/subdir');
define('RECAPTCHA_SITE_KEY', 'test-key');

require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/src/helpers/language_helper.php';
require ROOT_PATH . '/src/helpers/url_helper.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

// Every translation file (except config) must be a string => string array.
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT_PATH . '/src/i18n'));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php' || $file->getFilename() === 'config.php') {
        continue;
    }
    $translations = require $file->getPathname();
    $assert(is_array($translations), $file->getPathname() . ' must return an array');
    foreach ($translations as $key => $value) {
        $assert(is_string($key) && is_string($value), $file->getPathname() . ' must contain string => string only');
    }
}

$assert(lang() === lang(), 'lang() must return one shared instance');
$assert(__('home') === lang()->translate('home'), '__() and lang() must share the translator');
$assert(lang()->getDefaultLanguage() === 'en', 'English must be the default language');
$assert(lang()->getSupportedLanguages() === ['en', 'cs', 'de'], 'Supported languages mismatch');
$assert(__('welcome', ['name' => 'Anna']) === 'Welcome, Anna!', 'Parameter replacement failed');
$assert(__('missing.translation.key') === 'missing.translation.key', 'Missing key fallback failed');

lang()->setLanguage('de');
$assert(__('title', [], 'about_us') === 'About Us', 'Fallback to English category failed');
$assert(__('send', [], 'forms') === 'Send', 'English category fallback failed');

$assert(locale_url('contact', 'en') === '/subdir/contact', 'English URL mismatch');
$assert(locale_url('contact', 'cs') === '/subdir/cs/contact', 'Czech URL mismatch');
$assert(locale_url('contact', 'de') === '/subdir/de/contact', 'German URL mismatch');
$assert(locale_url('assets/app.css', 'cs') === '/subdir/assets/app.css', 'Assets must not be localized');
$assert(locale_url('api/v1/test', 'de') === '/subdir/api/v1/test', 'API must not be localized');
$assert(locale_url('contact?filter=a#form', 'cs', ['page' => 2]) === '/subdir/cs/contact?filter=a&page=2#form', 'Query/fragment preservation failed');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = ['url' => 'cs/contact', 'page' => '2'];
unset($GLOBALS['localized_route_path'], $GLOBALS['localized_public_request']);
$assert(initialize_localized_request('cs/contact') === 'contact', 'Czech route prefix stripping failed');
$assert(lang()->getCurrentLanguage() === 'cs', 'Czech URL did not select Czech');
$assert(current_route_path() === 'contact', 'Current route path mismatch');
$assert(locale_switch_url('de') === '/subdir/de/contact?page=2', 'Language switch URL mismatch');

$data = [
    'title' => 'Contact',
    'description' => 'Contact page',
    'content' => '<p>Contact</p>',
];
ob_start();
include ROOT_PATH . '/src/views/layouts/main.php';
$html = ob_get_clean();

$assert(str_contains($html, '<html lang="cs">'), 'HTML lang mismatch');
$assert(str_contains($html, 'rel="canonical" href="https://example.test/subdir/cs/contact?page=2"'), 'Self canonical mismatch');
$assert(str_contains($html, 'hreflang="en" href="https://example.test/subdir/contact?page=2"'), 'English hreflang missing');
$assert(str_contains($html, 'hreflang="cs" href="https://example.test/subdir/cs/contact?page=2"'), 'Czech hreflang missing');
$assert(str_contains($html, 'hreflang="de" href="https://example.test/subdir/de/contact?page=2"'), 'German hreflang missing');
$assert(str_contains($html, 'hreflang="x-default" href="https://example.test/subdir/contact?page=2"'), 'x-default hreflang missing');
$assert(str_contains($html, 'property="og:locale" content="cs_CZ"'), 'Open Graph locale mismatch');
$assert(str_contains($html, 'href="/subdir/cs/contact"'), 'Localized public link missing');
$assert(!str_contains($html, '/cs/assets/'), 'Asset URL was incorrectly localized');
$assert(!str_contains($html, '/cs/api/'), 'API URL was incorrectly localized');

echo "i18n smoke tests passed\n";
