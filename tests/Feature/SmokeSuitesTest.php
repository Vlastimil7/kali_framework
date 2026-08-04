<?php

declare(strict_types=1);

dataset('framework smoke suites', [
    'configuration' => 'config_smoke.php',
    'validator' => 'validator_smoke.php',
    'CSRF and request' => 'csrf_request_smoke.php',
    'middleware' => 'middleware_smoke.php',
    'flash and toast' => 'flash_toast_smoke.php',
    'mail' => 'mail_smoke.php',
    'i18n' => 'i18n_smoke.php',
    'route audit' => 'route_audit.php',
]);

it('passes the :dataset smoke suite', function (string $script): void {
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $script;
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($path) . ' 2>&1';
    $output = [];
    $exitCode = 0;

    exec($command, $output, $exitCode);

    expect($exitCode)
        ->toBe(0, implode(PHP_EOL, $output));
})->with('framework smoke suites');
