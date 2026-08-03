<?php

namespace Core;

final class Config
{
    private static array $items = [];

    public static function set(string $key, mixed $value): void
    {
        self::$items[$key] = $value;
    }

    public static function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null || $key === '') {
            return self::$items;
        }

        $value = self::$items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public static function has(string $key): bool
    {
        $sentinel = new \stdClass();

        return self::get($key, $sentinel) !== $sentinel;
    }

    public static function all(): array
    {
        return self::$items;
    }

    public static function clear(): void
    {
        self::$items = [];
    }
}
