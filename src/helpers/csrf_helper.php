<?php

use Helpers\Csrf;

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_protect_forms')) {
    function csrf_protect_forms(string $html): string
    {
        return preg_replace_callback(
            '/<form\b(?=[^>]*\bmethod\s*=\s*(["\']?)post\1)[^>]*>/i',
            static fn (array $match): string => $match[0] . csrf_field(),
            $html
        ) ?? $html;
    }
}
