<?php

namespace Controllers\Front;

use Core\Controller;
use Services\Auth\GoogleOAuthService;
use Models\User;

class AuthController extends Controller
{
    private GoogleOAuthService $googleOAuthService;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->googleOAuthService = new GoogleOAuthService();
        $this->userModel = new User();
    }

    public function redirectToGoogle(): void
    {
        $state = bin2hex(random_bytes(32));
        $_SESSION['google_oauth_state'] = $state;
        $_SESSION['google_oauth_language'] = lang()->getCurrentLanguage();

        $authUrl = $this->googleOAuthService->getAuthUrl($state);

        header('Location: ' . $authUrl);
        exit;
    }

    public function handleGoogleCallback(): void
    {
        http_response_code(200);

        try {
            $returnLanguage = (string) ($_SESSION['google_oauth_language'] ?? lang()->getDefaultLanguage());
            if (!lang()->isValidLanguage($returnLanguage)) {
                $returnLanguage = lang()->getDefaultLanguage();
            }
            $state = (string)($_GET['state'] ?? '');
            $code = (string)($_GET['code'] ?? '');
            $storedState = (string)($_SESSION['google_oauth_state'] ?? '');

            if ($state === '' || $storedState === '' || !hash_equals($storedState, $state)) {
                $_SESSION['flash_message'] = 'Neplatný OAuth state.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . locale_url('login', $returnLanguage));
                exit;
            }

            unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_language']);

            if ($code === '') {
                $_SESSION['flash_message'] = 'Google nevrátil autorizační kód.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . locale_url('login', $returnLanguage));
                exit;
            }

            $result = $this->googleOAuthService->fetchUserByCode($code);

            if (!$result['success']) {
                $_SESSION['flash_message'] = $result['message'] ?? 'Google přihlášení selhalo.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . locale_url('login', $returnLanguage));
                exit;
            }

            $googleUser = $result['google_user'];
            $user = $this->userModel->getUserByEmail($googleUser['email']);

            if (!$user) {
                $createResult = $this->userModel->createGoogleUser($googleUser);

                if (!$createResult['success']) {
                    $_SESSION['flash_message'] = $createResult['message'] ?? 'Nepodařilo se vytvořit účet přes Google.';
                    $_SESSION['flash_type'] = 'error';
                    header('Location: ' . locale_url('login', $returnLanguage));
                    exit;
                }

                $user = $this->userModel->getUserByEmail($googleUser['email']);
            }

            if (!$user) {
                $_SESSION['flash_message'] = 'Nepodařilo se načíst uživatele po přihlášení přes Google.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . locale_url('login', $returnLanguage));
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            $_SESSION['flash_message'] = 'Přihlášení přes Google proběhlo úspěšně.';
            $_SESSION['flash_type'] = 'success';

            if (($user['role'] ?? 'user') === 'admin') {
                header('Location: ' . locale_url('admin/dashboard', $returnLanguage));
            } else {
                header('Location: ' . locale_url('profile', $returnLanguage));
            }
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_message'] = 'Chyba při přihlášení přes Google: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
            $returnLanguage = isset($returnLanguage) ? $returnLanguage : lang()->getDefaultLanguage();
            header('Location: ' . locale_url('login', $returnLanguage));
            exit;
        }
    }
}
