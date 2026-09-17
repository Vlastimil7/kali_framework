<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/src/config/config.php';
require ROOT_PATH . '/src/helpers/language_helper.php';
require ROOT_PATH . '/src/helpers/url_helper.php';
require ROOT_PATH . '/src/helpers/cookie_helper.php';

session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();

$path = trim((string) ($_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)), '/');
$basePath = trim((string) config('app.base_url', ''), '/');
if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
    $path = ltrim(substr($path, strlen($basePath)), '/');
}
$path = initialize_localized_request($path);

$router = new Core\Router();
require ROOT_PATH . '/src/routes/web.php';
require ROOT_PATH . '/src/routes/api.php';
$router->dispatch($path);
