<?php

namespace Helpers;

class Flash
{
    private const SESSION_KEY = '_flash';
    private const OLD_PREFIX = 'old.';
    private const SENSITIVE_INPUTS = [
        'password',
        'password_confirm',
        'password_confirmation',
        'token',
        'csrf_token',
        '_token',
        'recaptcha_token',
        'g-recaptcha-response',
        'secret',
        'api_key',
        'gdpr',
        'privacy',
    ];

    public static function set(string $key, $value): void
    {
        $_SESSION[self::SESSION_KEY][$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        if (!self::has($key)) {
            return $default;
        }

        $value = $_SESSION[self::SESSION_KEY][$key];
        self::forget($key);

        return $value;
    }

    public static function pull(string $key, $default = null)
    {
        return self::get($key, $default);
    }

    public static function peek(string $key, $default = null)
    {
        return self::has($key)
            ? $_SESSION[self::SESSION_KEY][$key]
            : $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION[self::SESSION_KEY] ?? []);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[self::SESSION_KEY][$key]);

        if (empty($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        }
    }

    /**
     * Uloží vstup formuláře pro následující request.
     * Citlivá pole se nikdy neukládají, další lze předat v $except.
     */
    public static function withInput(string $form, array $input, array $except = []): void
    {
        $excluded = array_map(
            static fn ($key): string => strtolower((string)$key),
            array_merge(self::SENSITIVE_INPUTS, $except),
        );

        self::set(self::oldKey($form), self::sanitizeInput($input, $excluded));
    }

    /**
     * Vrátí a zároveň spotřebuje vstup formuláře z předchozího requestu.
     */
    public static function old(string $form, array $default = []): array
    {
        $input = self::get(self::oldKey($form), $default);

        return is_array($input) ? $input : $default;
    }

    public static function clearOld(string $form): void
    {
        self::forget(self::oldKey($form));
    }

    private static function oldKey(string $form): string
    {
        $form = trim($form);
        if ($form === '' || !preg_match('/^[a-zA-Z0-9._-]+$/', $form)) {
            throw new \InvalidArgumentException('Flash form key contains unsupported characters.');
        }

        return self::OLD_PREFIX . $form;
    }

    private static function sanitizeInput(array $input, array $excluded): array
    {
        $safe = [];

        foreach ($input as $key => $value) {
            if (in_array(strtolower((string)$key), $excluded, true)) {
                continue;
            }

            $safe[$key] = is_array($value)
                ? self::sanitizeInput($value, $excluded)
                : $value;
        }

        return $safe;
    }
}
