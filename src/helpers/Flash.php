<?php
namespace Helpers;

class Flash
{
    public static function set(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        if (!isset($_SESSION['_flash'][$key])) return $default;
        $val = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}
