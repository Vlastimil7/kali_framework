<?php

namespace Helpers;

class Toast
{
    private const FLASH_KEY = 'toasts';

    public static function push(
        string $type,
        string $message,
        string $title = '',
        string $position = 'top-right'
    ): void {
        $toasts = Flash::peek(self::FLASH_KEY, []);
        if (!is_array($toasts)) {
            $toasts = [];
        }
        $toasts[] = [
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'position' => $position,
        ];

        Flash::set(self::FLASH_KEY, $toasts);
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

    public static function all(): array
    {
        $toasts = Flash::get(self::FLASH_KEY, []);
        if (!is_array($toasts)) {
            $toasts = [];
        }

        // Zpětná kompatibilita pro zprávu uloženou starší verzí helperu.
        $legacyToast = Flash::get('toast');
        if (is_array($legacyToast)) {
            $toasts[] = $legacyToast;
        }

        return $toasts;
    }
}
