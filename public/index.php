<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/src/config/config.php';

use Helpers\Logger;
use Helpers\Toast;

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'); // pokud jsi za proxy

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    // 'domain' => 'vk-dev.cz', // nedávej, pokud nechceš řešit www vs non-www; default je bezpečnější
    'secure' => $https,        // v produkci by to mělo vycházet true
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Spuštění session
session_start();

// Vylepšený autoloader
spl_autoload_register(function ($className) {

    Logger::info("Attempting to load class: $className");

    $baseDir = ROOT_PATH . '/src/';

    $namespaceMap = [
        'Api\\V1\\Controllers\\' => 'Api/V1/Controllers/',
        'Api\\' => 'Api/',
        'Core\\' => 'classes/core/',
        'Controllers\\' => 'controllers/',
        'Models\\' => 'models/',
        'Helpers\\' => 'helpers/',
        'Middleware\\' => 'middleware/',
        'Services\\' => 'services/'
    ];

    foreach ($namespaceMap as $namespace => $path) {
        if (strpos($className, $namespace) === 0) {
            $relativeClass = substr($className, strlen($namespace));
            $file = $baseDir . $path . str_replace('\\', '/', $relativeClass) . '.php';

            Logger::info("Looking for file: " . $file);

            if (file_exists($file)) {
                require_once $file;
                Logger::info("Successfully loaded class: " . $className);
                return true;
            }
        }
    }

    Logger::error("File not found for class: " . $className);
    return false;
});

// Translation and localized URL helpers must exist before routes/controllers
// are loaded or any request-dependent redirect is evaluated.
require_once ROOT_PATH . '/src/helpers/language_helper.php';
require_once ROOT_PATH . '/src/helpers/url_helper.php';
require_once ROOT_PATH . '/src/helpers/csrf_helper.php';

$requestedRoute = trim((string) ($_GET['url'] ?? ''), '/');
if ($requestedRoute === '') {
    $requestPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
    $basePath = trim((string) (parse_url(config('app.base_url', ''), PHP_URL_PATH) ?? ''), '/');
    $requestPath = trim($requestPath, '/');
    if ($basePath !== '' && ($requestPath === $basePath || str_starts_with($requestPath, $basePath . '/'))) {
        $requestPath = ltrim(substr($requestPath, strlen($basePath)), '/');
    }
    $requestedRoute = $requestPath;
}

redirect_legacy_url($requestedRoute);
$url = initialize_localized_request($requestedRoute);

// Načtení konfiguračního souboru
use Core\Database;

Database::setConfig((array)config('database'));

use Core\Router;

$router = new Router();

require_once ROOT_PATH . '/src/routes/web.php'; // Uživatelské cesty
require_once ROOT_PATH . '/src/routes/admin.php'; // Admin cesty
require_once ROOT_PATH . '/src/routes/api.php'; // API cesty

// --------------------
// AI MODE lockdown
// --------------------
$aiMode = !empty($_SESSION['ai_mode']);

// Zjisti "path" bez query stringu.
// U tebe je hlavní routing přes ?url=..., ale současně můžeš mít REQUEST_URI.
// Uděláme kompromis: primárně použijeme ?url=, fallback REQUEST_URI.
$path = $url;

// Povolené cesty když je AI mode ON
$allowedWhenAi = [
    'chat',
    'ai-mode/toggle',
    // API – chat + telemetry
    'api/v1/chat',
    'api/v1/chat/status',
    'api/v1/chat/health',
    'api/v1/telemetry/collect',

    // bezpečně nech i logout (ať se uživatel může odhlásit)
    'logout',
];

// Assets (přizpůsob si podle svých cest)
$isAsset =
    str_starts_with($path, 'assets/') ||
    str_starts_with($path, 'public/assets/') ||
    str_ends_with($path, '.css') ||
    str_ends_with($path, '.js') ||
    str_ends_with($path, '.png') ||
    str_ends_with($path, '.jpg') ||
    str_ends_with($path, '.jpeg') ||
    str_ends_with($path, '.webp') ||
    str_ends_with($path, '.svg') ||
    str_ends_with($path, '.ico');

if ($aiMode && !$isAsset) {
    $isAllowed = false;

    foreach ($allowedWhenAi as $allowed) {
        $allowed = trim($allowed, '/');
        if ($path === $allowed || str_starts_with($path, $allowed . '/')) {
            $isAllowed = true;
            break;
        }
    }

    if (!$isAllowed) {
        header('Location: ' . locale_url('chat'));
        Toast::info(
            __('ai_mode_restriction_message', [], 'toast'),
            'Notify',
            'top-right'
        );
        exit;
    }
}


// Zpracování požadavku
$router->dispatch($url);
Database::getInstance()->closeConnection();
