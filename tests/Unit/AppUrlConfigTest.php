<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/helpers/env.php';

function appUrlConfigForTest(array $values): array
{
    $keys = ['APP_ENV', 'APP_URL_DEVELOPMENT', 'APP_URL_PRODUCTION', 'APP_URL', 'APP_SITE_URL', 'APP_BASE_URL'];
    $original = [];
    foreach ($keys as $key) {
        $original[$key] = $_ENV[$key] ?? null;
        $_ENV[$key] = $values[$key] ?? ($key === 'APP_ENV' ? 'development' : '');
    }

    try {
        return require dirname(__DIR__, 2) . '/src/config/app.php';
    } finally {
        foreach ($original as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $value;
            }
        }
    }
}

it('selects the development URL and derives its subdirectory', function (): void {
    $config = appUrlConfigForTest([
        'APP_ENV' => 'development',
        'APP_URL_DEVELOPMENT' => 'http://local.test/my-app/public/',
        'APP_URL_PRODUCTION' => 'https://example.test',
        'APP_URL' => 'https://old.test',
        'APP_BASE_URL' => '/old-path',
    ]);

    expect($config['site_url'])->toBe('http://local.test/my-app/public')
        ->and($config['base_url'])->toBe('/my-app/public');
});

it('selects the production URL independently of development', function (): void {
    $config = appUrlConfigForTest([
        'APP_ENV' => 'production',
        'APP_URL_DEVELOPMENT' => 'http://local.test/my-app/public',
        'APP_URL_PRODUCTION' => 'https://example.test',
    ]);

    expect($config['site_url'])->toBe('https://example.test')
        ->and($config['base_url'])->toBe('');
});

it('uses the visible production path when public is hidden by Apache', function (): void {
    $config = appUrlConfigForTest([
        'APP_ENV' => 'production',
        'APP_URL_DEVELOPMENT' => 'http://local.test/kali-framework/public',
        'APP_URL_PRODUCTION' => 'https://web.example.test/kali-framework',
    ]);

    expect($config['site_url'])->toBe('https://web.example.test/kali-framework')
        ->and($config['base_url'])->toBe('/kali-framework');
});

it('supports a legacy URL when environment URLs are absent', function (): void {
    $config = appUrlConfigForTest([
        'APP_URL' => 'https://old.test/subdir',
        'APP_BASE_URL' => '/subdir',
    ]);

    expect($config['site_url'])->toBe('https://old.test/subdir')
        ->and($config['base_url'])->toBe('/subdir');
});

it('requires the URL for the active environment', function (): void {
    expect(fn (): array => appUrlConfigForTest([
        'APP_ENV' => 'production',
        'APP_URL_DEVELOPMENT' => 'http://local.test',
    ]))->toThrow(InvalidArgumentException::class, 'Missing APP_URL_PRODUCTION');
});

it('rejects an unknown environment', function (): void {
    expect(fn (): array => appUrlConfigForTest([
        'APP_ENV' => 'productgion',
        'APP_URL_DEVELOPMENT' => 'http://local.test',
        'APP_URL_PRODUCTION' => 'https://example.test',
    ]))->toThrow(InvalidArgumentException::class, 'APP_ENV must be development or production');
});
