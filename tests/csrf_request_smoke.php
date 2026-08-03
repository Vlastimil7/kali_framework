<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', '');
define('SITE_URL', 'https://example.test');

require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/src/helpers/language_helper.php';
require ROOT_PATH . '/src/helpers/url_helper.php';
require ROOT_PATH . '/src/helpers/csrf_helper.php';
require ROOT_PATH . '/tests/fixtures/RequestInjectionController.php';

use Core\Request;
use Core\Router;
use Helpers\Csrf;
use Helpers\Toast;
use Middleware\CsrfMiddleware;

$_SESSION = [];
$token = csrf_token();
if (strlen($token) !== 64 || !Csrf::validate($token)) {
    throw new RuntimeException('CSRF token was not generated or validated.');
}
if (!str_contains(csrf_field(), 'name="_token"')) {
    throw new RuntimeException('CSRF hidden field was not rendered.');
}

$request = new Request(
    ['page' => '2', 'filter' => ['status' => 'paid']],
    ['name' => ' Anna ', 'enabled' => '1', 'amount' => '15', '_token' => $token],
    ['document' => ['name' => 'invoice.pdf']],
    ['REQUEST_METHOD' => 'POST', 'HTTP_X_TEST' => 'yes', 'REMOTE_ADDR' => '127.0.0.1'],
    ['consent' => 'yes'],
);
$request->setRouteParams(['id' => '42']);

if (!$request->isMethod('post') || $request->string('name') !== 'Anna') {
    throw new RuntimeException('Request method or string input failed.');
}
if ($request->int('amount') !== 15 || !$request->boolean('enabled')) {
    throw new RuntimeException('Typed Request input failed.');
}
if ($request->query('filter.status') !== 'paid' || $request->route('id') !== '42') {
    throw new RuntimeException('Nested query or route input failed.');
}
if ($request->header('X-Test') !== 'yes' || $request->cookie('consent') !== 'yes') {
    throw new RuntimeException('Request header or cookie input failed.');
}
if (($request->file('document')['name'] ?? null) !== 'invoice.pdf' || $request->ip() !== '127.0.0.1') {
    throw new RuntimeException('Request file or IP input failed.');
}

$called = false;
$result = (new CsrfMiddleware())->handle($request, function () use (&$called): string {
    $called = true;
    return 'allowed';
});
if (!$called || $result !== 'allowed') {
    throw new RuntimeException('CSRF middleware blocked a valid request.');
}

$invalidRequest = new Request([], [], [], ['REQUEST_METHOD' => 'POST']);
$called = false;
$result = (new CsrfMiddleware())->handle($invalidRequest, function () use (&$called): void {
    $called = true;
});
if ($called || $result !== null || (Toast::all()[0]['type'] ?? null) !== 'error') {
    throw new RuntimeException('CSRF middleware allowed an invalid request.');
}

$html = csrf_protect_forms('<form method="post" action="/save"><button>Save</button></form>');
if (substr_count($html, 'name="_token"') !== 1) {
    throw new RuntimeException('Automatic CSRF form protection failed.');
}

$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = ['source' => 'router'];
$router = new Router();
$router->get('request-probe/{id}', 'Controllers\\Front\\RequestInjectionController@show');
$result = $router->dispatch('request-probe/42');

if ($result !== 'injected' || \Controllers\Front\RequestInjectionController::$receivedId !== 42) {
    throw new RuntimeException('Router did not invoke the Request-aware controller action.');
}
$injectedRequest = \Controllers\Front\RequestInjectionController::$receivedRequest;
if (!$injectedRequest instanceof Request || $injectedRequest->query('source') !== 'router' || $injectedRequest->route('id') !== '42') {
    throw new RuntimeException('Router did not inject Request with query and route data.');
}

echo "CSRF/Request smoke tests passed\n";
