<?php

namespace Helpers;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION["user_id"]);
    }

    public static function id(): ?int
    {
        return $_SESSION["user_id"] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::check() && ($_SESSION["user_role"] ?? null) === "admin";
    }

    public static function requireLogin(string $redirect = "/login"): void
    {
        if (!self::check()) {
            header("Location: " . locale_url($redirect));
            exit();
        }
    }

    public static function requireAdmin(string $redirect = "/login"): void
    {
        if (!self::isAdmin()) {
            header("Location: " . locale_url($redirect));
            exit();
        }
    }

    public static function userInfo (): array
    {
        return [
            "id" => $_SESSION["user_id"] ?? null,
            "name" => $_SESSION["user_name"] ?? null,
            "email" => $_SESSION["user_email"] ?? null,
            "role" => $_SESSION["user_role"] ?? null,
            "avatar" => $_SESSION["user_avatar"] ?? (BASE_URL . "/assets/avatars/default.png"),
        ];
    }

    // Odhlášení uživatele
    public function logout()
    {
        // Zničení session
        session_unset();
        session_destroy();

        // Přesměrování na přihlašovací stránku
        header("Location: " . locale_base_url() . "/login");
        exit();
    }
}
