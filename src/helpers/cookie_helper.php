<?php

function cookie_preferences(): ?array
{
    $raw = $_COOKIE[(string) config('cookies.name', 'kali_consent')] ?? null;
    if (!is_string($raw)) {
        return null;
    }

    $value = json_decode($raw, true);
    if (!is_array($value) || ($value['version'] ?? null) !== 1) {
        return null;
    }

    foreach (['analytics', 'marketing'] as $category) {
        if (!isset($value[$category]) || !is_bool($value[$category])) {
            return null;
        }
    }

    return [
        'necessary' => true,
        'analytics' => $value['analytics'],
        'marketing' => $value['marketing'],
    ];
}

function cookie_allowed(string $category): bool
{
    if ($category === 'necessary') {
        return true;
    }

    if (!in_array($category, ['analytics', 'marketing'], true)) {
        return false;
    }

    return cookie_preferences()[$category] ?? false;
}
