<?php

namespace Controllers\Front;

use Core\Controller;
use Core\Request;

class CookieController extends Controller
{


    public function index()
    {
        $data = [
            'title' => __('cookies_consent_title', [], 'cookies'),
        ];

        $this->view('cookie/index', $data);
    }


    /**
     * Zobrazí nastavení cookies
     */
    public function showSettings(Request $request)
    {
        // Získat aktuální nastavení cookies, pokud existuje
        $cookieConsent = $request->cookie('cookie_consent')
            ? json_decode((string)$request->cookie('cookie_consent'), true)
            : null;

        $data = [
            'title' => '' . __('cookies_settings_title', [], 'cookies') . ' | VK-DEV.cz',
            'preferences' => $cookieConsent ?? [
                'necessary' => true,
                'analytics' => false,
                'marketing' => false,
                'preferences' => false
            ]
        ];

        $this->view('cookie/settings', $data);
    }

    /**
     * Uloží nastavení cookies podle preferencí uživatele
     */
    public function saveConsent(Request $request)
    {
        $necessary = true; // Vždy povoleno
        $analytics = $request->has('analytics');
        $marketing = $request->has('marketing');
        $preferences = $request->has('preferences');

        // Vytvoření pole preferencí
        $cookiePreferences = [
            'necessary' => $necessary,
            'analytics' => $analytics,
            'marketing' => $marketing,
            'preferences' => $preferences,
            'timestamp' => time()
        ];

        // Uložení do cookie na 1 rok
        $this->setCookie('cookie_consent', json_encode($cookiePreferences), 365, $request->isSecure());

        // Přesměrování zpět na stránku, odkud byl požadavek odeslán
        $referer = $request->header('Referer', config('app.base_url', ''));
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Přijme všechny cookies
     */
    public function acceptAll(Request $request)
    {
        $cookiePreferences = [
            'necessary' => true,
            'analytics' => true,
            'marketing' => true,
            'preferences' => true,
            'timestamp' => time()
        ];

        // Uložení do cookie na 1 rok
        $this->setCookie('cookie_consent', json_encode($cookiePreferences), 365, $request->isSecure());

        // Přesměrování zpět na stránku, odkud byl požadavek odeslán
        $referer = $request->header('Referer', config('app.base_url', ''));
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Odmítne všechny volitelné cookies
     */
    public function rejectAll(Request $request)
    {
        $cookiePreferences = [
            'necessary' => true,
            'analytics' => false,
            'marketing' => false,
            'preferences' => false,
            'timestamp' => time()
        ];

        // Uložení do cookie na 1 rok
        $this->setCookie('cookie_consent', json_encode($cookiePreferences), 365, $request->isSecure());

        // Přesměrování zpět na stránku, odkud byl požadavek odeslán
        $referer = $request->header('Referer', config('app.base_url', ''));
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Pomocná metoda pro nastavení cookie
     */
    private function setCookie($name, $value, $days = 30, bool $secure = false)
    {
        $expiry = time() + ($days * 86400); // 86400 = 1 den v sekundách
        setcookie($name, $value, [
            'expires' => $expiry,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
    }
}
