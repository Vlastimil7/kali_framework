<?php

$environment = strtolower(trim((string) env('APP_ENV', 'development')));
if (!in_array($environment, ['development', 'production'], true)) {
    throw new InvalidArgumentException('APP_ENV must be development or production');
}

$urlKey = $environment === 'production' ? 'APP_URL_PRODUCTION' : 'APP_URL_DEVELOPMENT';
$environmentUrl = trim((string) env($urlKey, ''));
$hasEnvironmentUrls = trim((string) env('APP_URL_DEVELOPMENT', '')) !== ''
    || trim((string) env('APP_URL_PRODUCTION', '')) !== '';
$legacyUrl = trim((string) env('APP_URL', env('APP_SITE_URL', '')));
$missingUrl = ($hasEnvironmentUrls && $environmentUrl === '')
    || ($environment === 'production' && $environmentUrl === '' && $legacyUrl === '');
if ($missingUrl) {
    throw new InvalidArgumentException("Missing {$urlKey} in .env");
}

$siteUrl = rtrim($environmentUrl !== '' ? $environmentUrl : ($legacyUrl !== '' ? $legacyUrl : 'http://localhost:8000'), '/');
if (filter_var($siteUrl, FILTER_VALIDATE_URL) === false
    || !in_array(parse_url($siteUrl, PHP_URL_SCHEME), ['http', 'https'], true)
    || parse_url($siteUrl, PHP_URL_QUERY) !== null
    || parse_url($siteUrl, PHP_URL_FRAGMENT) !== null) {
    throw new InvalidArgumentException("{$urlKey} must be a full http:// or https:// URL");
}
$baseUrl = $environmentUrl !== ''
    ? (string) parse_url($siteUrl, PHP_URL_PATH)
    : (string) env('APP_BASE_URL', (string) parse_url($siteUrl, PHP_URL_PATH));

return [
    'name' => (string) env('APP_NAME', 'Kali project'),
    'env' => $environment,
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    'timezone' => (string) env('APP_TIMEZONE', 'Europe/Prague'),
    'base_url' => rtrim($baseUrl, '/'),
    'site_url' => $siteUrl,
];
