<?php

use Core\Config;

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}
