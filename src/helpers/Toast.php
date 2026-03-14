<?php

namespace Helpers;

use Helpers\Flash;

class Toast
{
    public static function push(
        string $type,
        string $message,
        string $title = '',
        string $position = 'top-right'
    ): void {
        Flash::set('toast', [
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'position' => $position,
        ]);
    }

    public static function success(string $message, string $title = 'Hotovo', string $position = 'top-right'): void
    {
        self::push('success', $message, $title, $position);
    }

    public static function error(string $message, string $title = 'Chyba', string $position = 'top-right'): void
    {
        self::push('error', $message, $title, $position);
    }

    public static function info(string $message, string $title = '', string $position = 'top-right'): void
    {
        self::push('info', $message, $title, $position);
    }

    public static function warning(string $message, string $title = 'Upozornění', string $position = 'top-right'): void
    {
        self::push('warning', $message, $title, $position);
    }
}
