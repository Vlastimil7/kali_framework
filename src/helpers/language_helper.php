<?php

use Models\Language;

if (!function_exists('lang')) {
    function lang(): Language
    {
        static $language;
        return $language ??= new Language();
    }
}

if (!function_exists('__')) {
    function __(string $key, array $params = [], string $category = 'general'): string
    {
        return lang()->translate($key, $params, $category);
    }
}
