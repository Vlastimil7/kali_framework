<?php

declare(strict_types=1);

use Core\Config;

beforeEach(function (): void {
    Config::clear();
});

afterEach(function (): void {
    Config::clear();
});

it('reads nested configuration with a fallback', function (): void {
    Config::set('mail', [
        'smtp' => [
            'host' => 'smtp.example.test',
        ],
    ]);

    expect(config('mail.smtp.host'))->toBe('smtp.example.test')
        ->and(config('mail.smtp.port', 587))->toBe(587)
        ->and(Config::has('mail.smtp.host'))->toBeTrue()
        ->and(Config::has('mail.smtp.port'))->toBeFalse();
});
