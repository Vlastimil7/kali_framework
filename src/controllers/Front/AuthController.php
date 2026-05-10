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

        $authUrl = $this->googleOAuthService->getAuthUrl($state);

        header('Location: ' . $authUrl);
        exit;
    }

    public function handleGoogleCallback(): void
    {
        http_response_code(200);

        try {
            $state = (string)($_GET['state'] ?? '');
            $code = (string)($_GET['code'] ?? '');
            $storedState = (string)($_SESSION['google_oauth_state'] ?? '');

            if ($state === '' || $storedState === '' || !hash_equals($storedState, $state)) {
                $_SESSION['flash_message'] = 'Neplatný OAuth state.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            unset($_SESSION['google_oauth_state']);

            if ($code === '') {
                $_SESSION['flash_message'] = 'Google nevrátil autorizační kód.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            $result = $this->googleOAuthService->fetchUserByCode($code);

            if (!$result['success']) {
                $_SESSION['flash_message'] = $result['message'] ?? 'Google přihlášení selhalo.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            $googleUser = $result['google_user'];
            $user = $this->userModel->getUserByEmail($googleUser['email']);

            if (!$user) {
                $createResult = $this->userModel->createGoogleUser($googleUser);

                if (!$createResult['success']) {
                    $_SESSION['flash_message'] = $createResult['message'] ?? 'Nepodařilo se vytvořit účet přes Google.';
                    $_SESSION['flash_type'] = 'error';
                    header('Location: ' . BASE_URL . '/login');
                    exit;
                }

                $user = $this->userModel->getUserByEmail($googleUser['email']);
            }

            if (!$user) {
                $_SESSION['flash_message'] = 'Nepodařilo se načíst uživatele po přihlášení přes Google.';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            $_SESSION['flash_message'] = 'Přihlášení přes Google proběhlo úspěšně.';
            $_SESSION['flash_type'] = 'success';

            if (($user['role'] ?? 'user') === 'admin') {
                header('Location: ' . BASE_URL . '/admin/dashboard');
            } else {
                header('Location: ' . BASE_URL . '/profile');
            }
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_message'] = 'Chyba při přihlášení přes Google: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}
