<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', '');
define('SITE_URL', 'https://example.test');

require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/src/helpers/language_helper.php';
require ROOT_PATH . '/src/helpers/url_helper.php';

$router = new Core\Router();
require ROOT_PATH . '/src/routes/web.php';
require ROOT_PATH . '/src/routes/admin.php';
require ROOT_PATH . '/src/routes/api.php';

$property = new ReflectionProperty($router, 'routes');
$routes = $property->getValue($router);
$failures = [];

foreach ($routes as $method => $methodRoutes) {
    foreach ($methodRoutes as $path => $route) {
        [$controller, $action] = explode('@', $route['handler'], 2);
        if (str_starts_with($controller, 'Front\\') || str_starts_with($controller, 'Admin\\')) {
            $controller = 'Controllers\\' . $controller;
        }
        if (!class_exists($controller)) {
            $failures[] = "$method $path: missing $controller";
        } elseif (!method_exists($controller, $action)) {
            $failures[] = "$method $path: missing $controller::$action";
        }
    }
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo 'route audit passed: ' . array_sum(array_map('count', $routes)) . " routes\n";
