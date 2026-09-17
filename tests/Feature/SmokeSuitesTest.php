<?php

declare(strict_types=1);

use Core\Router;
use Core\Config;
use Models\Language;

it('loads both starter translations', function (): void {
    $language = new Language(dirname(__DIR__, 2) . '/src/i18n');
    expect($language->getDefaultLanguage())->toBe('cs')
        ->and($language->translate('home_title'))->toBe('Vítejte v Kali Frameworku');
    $language->setLanguage('en');
    expect($language->translate('welcome', ['name' => 'Kali']))->toBe('Hello, Kali!');
});

it('registers the starter web and API routes', function (): void {
    $router = new Router();
    require dirname(__DIR__, 2) . '/src/routes/web.php';
    require dirname(__DIR__, 2) . '/src/routes/api.php';

    $routes = (new ReflectionProperty(Router::class, 'routes'))->getValue($router);
    expect($routes['GET']['']['handler'])->toBe('Front\\HomeController@index')
        ->and($routes['GET']['hello/{name}']['handler'])->toBe('Front\\HomeController@hello')
        ->and($routes['GET']['api/v1/health']['handler'])->toBe('Api\\V1\\Controllers\\HealthController@index');
});

it('requires explicit consent for optional cookies', function (): void {
    require_once dirname(__DIR__, 2) . '/src/helpers/cookie_helper.php';
    Config::set('cookies', ['name' => 'test_consent']);

    unset($_COOKIE['test_consent']);
    expect(cookie_allowed('necessary'))->toBeTrue()
        ->and(cookie_allowed('analytics'))->toBeFalse();

    $_COOKIE['test_consent'] = json_encode(['version' => 1, 'analytics' => true, 'marketing' => false]);
    expect(cookie_allowed('analytics'))->toBeTrue()
        ->and(cookie_allowed('marketing'))->toBeFalse()
        ->and(cookie_allowed('other'))->toBeFalse();

    unset($_COOKIE['test_consent']);
    Config::clear();
});
