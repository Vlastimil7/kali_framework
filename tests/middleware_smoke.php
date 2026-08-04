<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/vendor/autoload.php';
Core\Config::set('app', ['base_url' => '', 'site_url' => 'https://example.test']);
require ROOT_PATH . '/src/helpers/language_helper.php';
require ROOT_PATH . '/src/helpers/url_helper.php';

use Core\Request;
use Core\Router;
use Helpers\Toast;
use Middleware\AdminMiddleware;
use Middleware\AuthMiddleware;

$_SESSION = [];
$request = new Request([], [], [], ['REQUEST_METHOD' => 'GET']);
$called = false;
$result = (new AuthMiddleware())->handle($request, function () use (&$called): string {
    $called = true;
    return 'allowed';
});

if ($called || $result !== null || Toast::all()[0]['type'] !== 'warning') {
    throw new RuntimeException('Auth middleware did not block an anonymous user.');
}

$_SESSION = ['user_id' => 10, 'user_role' => 'user'];
$result = (new AuthMiddleware())->handle($request, static fn (): string => 'allowed');
if ($result !== 'allowed') {
    throw new RuntimeException('Auth middleware blocked an authenticated user.');
}

$called = false;
$result = (new AdminMiddleware())->handle($request, function () use (&$called): string {
    $called = true;
    return 'allowed';
});
if ($called || $result !== null || Toast::all()[0]['type'] !== 'error') {
    throw new RuntimeException('Admin middleware did not block a non-admin user.');
}

$_SESSION['user_role'] = 'admin';
$result = (new AdminMiddleware())->handle($request, static fn (): string => 'allowed');
if ($result !== 'allowed') {
    throw new RuntimeException('Admin middleware blocked an administrator.');
}

$router = new Router();
$router->get('profile', 'Front\\UserController@showProfile')->middleware('auth');
$router->group(['middleware' => ['auth', 'admin']], function (Router $router): void {
    $router->get('admin/dashboard', 'Admin\\DashboardController@index');
    $router->get('admin/orders', 'Admin\\OrdersController@index')->middleware('audit');
});

$property = new ReflectionProperty($router, 'routes');
$routes = $property->getValue($router);

if (($routes['GET']['profile']['options']['middleware'] ?? []) !== ['auth']) {
    throw new RuntimeException('Fluent route middleware was not registered.');
}
if (($routes['GET']['admin/dashboard']['options']['middleware'] ?? []) !== ['auth', 'admin']) {
    throw new RuntimeException('Route group middleware was not registered.');
}
if (($routes['GET']['admin/orders']['options']['middleware'] ?? []) !== ['auth', 'admin', 'audit']) {
    throw new RuntimeException('Group and route middleware were not merged.');
}

echo "middleware smoke tests passed\n";
