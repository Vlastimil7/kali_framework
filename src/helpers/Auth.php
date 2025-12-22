<?php

namespace Helpers;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::check() && (($_SESSION['user_role'] ?? null) === 'admin');
    }

    public static function requireAdmin(string $redirect = '/login'): void
    {
        if (!self::isAdmin()) {
            header('Location: ' . BASE_URL . $redirect);
            exit;
        }
    }
}
