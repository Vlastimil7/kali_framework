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

// Načtení konfiguračního souboru
use Core\Database;

Database::setConfig($config);

use Core\Router;

$router = new Router();

require_once ROOT_PATH . '/src/routes/web.php'; // Uživatelské cesty
require_once ROOT_PATH . '/src/routes/admin.php'; // Admin cesty
require_once ROOT_PATH . '/src/routes/api.php'; // API cesty

// Načtení language helperu
require_once ROOT_PATH . '/src/helpers/language_helper.php';


// Získání URL z požadavku
$url = $_GET['url'] ?? '';

// --------------------
// AI MODE lockdown
// --------------------
$aiMode = !empty($_SESSION['ai_mode']);

// Zjisti "path" bez query stringu.
// U tebe je hlavní routing přes ?url=..., ale současně můžeš mít REQUEST_URI.
// Uděláme kompromis: primárně použijeme ?url=, fallback REQUEST_URI.
$rawPath = $_GET['url'] ?? null;

if ($rawPath === null || $rawPath === '') {
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $rawPath = trim($reqPath, '/');
}

$path = trim((string)$rawPath, '/');

// Povolené cesty když je AI mode ON
$allowedWhenAi = [
    'chat',
    'ai-mode/toggle',
    'language/change',

    // API – chat + telemetry
    'api/v1/chat',
    'api/v1/chat/status',
    'api/v1/chat/health',
    'api/v1/chat/test',
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
        header('Location: ' . BASE_URL . '/?url=chat');
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
