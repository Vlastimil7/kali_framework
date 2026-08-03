<?php

/**
 * Reads the public locale from the URL and returns the route without its prefix.
 * English is canonical without a prefix; /en/... permanently redirects to /....
 */
function initialize_localized_request(string $rawPath): string
{
    $path = trim((string) parse_url($rawPath, PHP_URL_PATH), '/');
    $segments = $path === '' ? [] : explode('/', $path);
    $firstSegment = strtolower($segments[0] ?? '');

    if (is_technical_path($path)) {
        $GLOBALS['localized_route_path'] = $path;
        $GLOBALS['localized_public_request'] = false;
        return $path;
    }

    $GLOBALS['localized_public_request'] = true;

    if ($firstSegment === 'en') {
        array_shift($segments);
        $routePath = implode('/', $segments);
        $GLOBALS['localized_route_path'] = $routePath;
        $statusCode = in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true) ? 301 : 308;
        header('Location: ' . locale_url($routePath, 'en', request_query_parameters()), true, $statusCode);
        exit;
    }

    if (in_array($firstSegment, ['cs', 'de'], true)) {
        lang()->setLanguage($firstSegment);
        array_shift($segments);
        $path = implode('/', $segments);
    } else {
        lang()->setLanguage('en');
    }

    $GLOBALS['localized_route_path'] = $path;
    return $path;
}

/**
 * Applies exact permanent redirects configured in src/config/redirects.php.
 */
function redirect_legacy_url(string $rawPath): void
{
    $path = trim((string) parse_url($rawPath, PHP_URL_PATH), '/');
    $redirectFile = dirname(__DIR__) . '/config/redirects.php';
    $redirects = is_file($redirectFile) ? require $redirectFile : [];

    if (!is_array($redirects) || !isset($redirects[$path])) {
        return;
    }

    $targetPath = trim((string) $redirects[$path], '/');
    if ($targetPath === $path) {
        return;
    }

    $target = rtrim(config('app.base_url', ''), '/') . ($targetPath !== '' ? '/' . $targetPath : '');
    if ($target === '') {
        $target = '/';
    }
    $query = request_query_parameters();
    if ($query !== []) {
        $target .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    $statusCode = in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true) ? 301 : 308;
    header('Location: ' . $target, true, $statusCode);
    exit;
}

function current_route_path(): string
{
    if (isset($GLOBALS['localized_route_path'])) {
        return trim((string) $GLOBALS['localized_route_path'], '/');
    }

    $path = trim((string) ($_GET['url'] ?? ''), '/');
    $segments = $path === '' ? [] : explode('/', $path);
    if (in_array(strtolower($segments[0] ?? ''), lang()->getSupportedLanguages(), true)) {
        array_shift($segments);
    }
    return implode('/', $segments);
}

function is_localized_public_request(): bool
{
    return (bool) ($GLOBALS['localized_public_request'] ?? true);
}

function locale_base_url(?string $language = null): string
{
    $language ??= lang()->getCurrentLanguage();
    $baseUrl = rtrim(config('app.base_url', ''), '/');
    return $language === lang()->getDefaultLanguage()
        ? $baseUrl
        : $baseUrl . '/' . rawurlencode($language);
}

function locale_url(string $path = '', ?string $language = null, array $query = []): string
{
    $language ??= lang()->getCurrentLanguage();
    if (!lang()->isValidLanguage($language)) {
        $language = lang()->getDefaultLanguage();
    }

    $fragment = parse_url($path, PHP_URL_FRAGMENT);
    $inlineQuery = parse_url($path, PHP_URL_QUERY);
    $routePath = trim((string) parse_url($path, PHP_URL_PATH), '/');
    if ($inlineQuery !== null && $inlineQuery !== '') {
        parse_str($inlineQuery, $parsedQuery);
        $query = array_merge($parsedQuery, $query);
    }

    $isTechnical = is_technical_path($routePath);
    if (!$isTechnical) {
        $routePath = unprefixed_route_path($routePath);
    }
    $base = $isTechnical ? rtrim(config('app.base_url', ''), '/') : locale_base_url($language);
    $url = $base . ($routePath !== '' ? '/' . $routePath : '');
    if ($url === '') {
        $url = '/';
    }

    if ($query !== []) {
        $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
    if ($fragment !== null && $fragment !== '') {
        $url .= '#' . rawurlencode($fragment);
    }
    return $url;
}

function locale_switch_url(string $language, ?array $localizedPaths = null): string
{
    $routePath = is_array($localizedPaths) && isset($localizedPaths[$language])
        ? (string) $localizedPaths[$language]
        : current_route_path();

    return locale_url($routePath, $language, request_query_parameters());
}

function locale_site_url(string $path = '', ?string $language = null, array $query = []): string
{
    $language ??= lang()->getCurrentLanguage();
    if (!lang()->isValidLanguage($language)) {
        $language = lang()->getDefaultLanguage();
    }
    $fragment = parse_url($path, PHP_URL_FRAGMENT);
    $inlineQuery = parse_url($path, PHP_URL_QUERY);
    $routePath = trim((string) parse_url($path, PHP_URL_PATH), '/');
    if ($inlineQuery !== null && $inlineQuery !== '') {
        parse_str($inlineQuery, $parsedQuery);
        $query = array_merge($parsedQuery, $query);
    }
    $isTechnical = is_technical_path($routePath);
    if (!$isTechnical) {
        $routePath = unprefixed_route_path($routePath);
    }
    $base = rtrim(config('app.site_url', ''), '/');
    if (!$isTechnical && $language !== lang()->getDefaultLanguage()) {
        $base .= '/' . rawurlencode($language);
    }
    $url = $base . ($routePath !== '' ? '/' . $routePath : '');
    if ($query !== []) {
        $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
    if ($fragment !== null && $fragment !== '') {
        $url .= '#' . rawurlencode($fragment);
    }
    return $url;
}

function request_query_parameters(): array
{
    $query = $_GET;
    unset($query['url']);
    return $query;
}

function is_technical_path(string $path): bool
{
    $firstSegment = strtolower(explode('/', trim($path, '/'))[0] ?? '');
    return in_array($firstSegment, ['admin', 'api', 'assets', 'uploads', 'vendor'], true);
}

function unprefixed_route_path(string $path): string
{
    $segments = $path === '' ? [] : explode('/', trim($path, '/'));
    if (in_array(strtolower($segments[0] ?? ''), lang()->getSupportedLanguages(), true)) {
        array_shift($segments);
    }
    return implode('/', $segments);
}
