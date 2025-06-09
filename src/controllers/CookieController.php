<?php
namespace Controllers;

use Core\Controller;

class CookieController extends Controller {
    /**
     * Zobrazí nastavení cookies
     */
    public function showSettings() {
        // Získat aktuální nastavení cookies, pokud existuje
        $cookieConsent = isset($_COOKIE['cookie_consent']) ? json_decode($_COOKIE['cookie_consent'], true) : null;
        
        $data = [
            'title' => 'Nastavení cookies',
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
    public function saveConsent() {
        $necessary = true; // Vždy povoleno
        $analytics = isset($_POST['analytics']) ? true : false;
        $marketing = isset($_POST['marketing']) ? true : false;
        $preferences = isset($_POST['preferences']) ? true : false;
        
        // Vytvoření pole preferencí
        $cookiePreferences = [
            'necessary' => $necessary,
            'analytics' => $analytics,
            'marketing' => $marketing,
            'preferences' => $preferences,
            'timestamp' => time()
        ];
        
        // Uložení do cookie na 1 rok
        $this->setCookie('cookie_consent', json_encode($cookiePreferences), 365);
        
        // Přesměrování zpět na stránku, odkud byl požadavek odeslán
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Přijme všechny cookies
     */
    public function acceptAll() {
        $cookiePreferences = [
            'necessary' => true,
            'analytics' => true,
            'marketing' => true,
            'preferences' => true,
            'timestamp' => time()
        ];
        
        // Uložení do cookie na 1 rok
        $this->setCookie('cookie_consent', json_encode($cookiePreferences), 365);
        
        // Přesměrování zpět na stránku, odkud byl požadavek odeslán
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Odmítne všechny volitelné cookies
     */
    public function rejectAll() {
        $cookiePreferences = [
            'necessary' => true,
            'analytics' => false,
            'marketing' => false,
            'preferences' => false,
            'timestamp' => time()
        ];
        
        // Uložení do cookie na 1 rok
        $this->setCookie('cookie_consent', json_encode($cookiePreferences), 365);
        
        // Přesměrování zpět na stránku, odkud byl požadavek odeslán
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Pomocná metoda pro nastavení cookie
     */
    private function setCookie($name, $value, $days = 30) {
        $expiry = time() + ($days * 86400); // 86400 = 1 den v sekundách
        setcookie($name, $value, [
            'expires' => $expiry,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
    }
}