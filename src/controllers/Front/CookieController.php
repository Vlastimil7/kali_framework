<?php

namespace Controllers\Front;

use Core\Controller;
use Core\Request;

final class CookieController extends Controller
{
    public function showSettings(): void
    {
        $this->view('cookie/settings', [
            'title' => __('cookie_settings_title') . ' | ' . config('app.name'),
            'description' => __('cookie_description'),
            'preferences' => cookie_preferences() ?? [
                'necessary' => true,
                'analytics' => false,
                'marketing' => false,
            ],
        ]);
    }

    public function save(Request $request): void
    {
        $choice = (string) $request->post('choice', 'selected');
        if (!in_array($choice, ['all', 'none', 'selected'], true)) {
            http_response_code(400);
            return;
        }

        $preferences = [
            'version' => 1,
            'analytics' => $choice === 'all' || ($choice === 'selected' && $request->post('analytics') === '1'),
            'marketing' => $choice === 'all' || ($choice === 'selected' && $request->post('marketing') === '1'),
        ];

        $cookiePath = rtrim((string) config('app.base_url', ''), '/') . '/';
        setcookie((string) config('cookies.name', 'kali_consent'), json_encode($preferences, JSON_THROW_ON_ERROR), [
            'expires' => time() + (int) config('cookies.lifetime_days', 180) * 86400,
            'path' => $cookiePath,
            'secure' => $request->isSecure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $route = trim((string) $request->post('return_path', ''), '/');
        if (!preg_match('~^[a-zA-Z0-9/_-]*$~', $route)) {
            $route = '';
        }
        $language = (string) $request->post('return_language', lang()->getDefaultLanguage());
        header('Location: ' . locale_url($route, $language), true, 303);
    }
}
