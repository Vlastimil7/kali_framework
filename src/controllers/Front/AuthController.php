<?php

namespace Controllers\Front;

use Core\Controller;
use Core\Request;
use Helpers\Toast;
use Models\User;
use Services\Auth\GoogleOAuthService;

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

    public function handleGoogleCallback(Request $request): void
    {
        http_response_code(200);

        try {
            $returnLanguage = (string) ($_SESSION['google_oauth_language'] ?? lang()->getDefaultLanguage());
            if (!lang()->isValidLanguage($returnLanguage)) {
                $returnLanguage = lang()->getDefaultLanguage();
            }
            $state = $request->string('state');
            $code = $request->string('code');
            $storedState = (string)($_SESSION['google_oauth_state'] ?? '');

            if ($state === '' || $storedState === '' || !hash_equals($storedState, $state)) {
                Toast::error('Neplatný OAuth state.');
                header('Location: ' . locale_url('login', $returnLanguage));
                exit;
            }

            unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_language']);

            if ($code === '') {
                Toast::error('Google nevrátil autorizační kód.');
                header('Location: ' . locale_url('login', $returnLanguage));
                exit;
            }

            $result = $this->googleOAuthService->fetchUserByCode($code);

            if (!$result['success']) {
                Toast::error($result['message'] ?? 'Google přihlášení selhalo.');
                header('Location: ' . locale_url('login', $returnLanguage));
                exit;
            }

            $googleUser = $result['google_user'];
            $user = $this->userModel->getUserByEmail($googleUser['email']);

            if (!$user) {
                $createResult = $this->userModel->createGoogleUser($googleUser);

                if (!$createResult['success']) {
                    Toast::error($createResult['message'] ?? 'Nepodařilo se vytvořit účet přes Google.');
                    header('Location: ' . locale_url('login', $returnLanguage));
                    exit;
                }

                $user = $this->userModel->getUserByEmail($googleUser['email']);
            }

            if (!$user) {
                Toast::error('Nepodařilo se načíst uživatele po přihlášení přes Google.');
                header('Location: ' . locale_url('login', $returnLanguage));
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            Toast::success('Přihlášení přes Google proběhlo úspěšně.');

            if (($user['role'] ?? 'user') === 'admin') {
                header('Location: ' . locale_url('admin/dashboard', $returnLanguage));
            } else {
                header('Location: ' . locale_url('profile', $returnLanguage));
            }
            exit;
        } catch (\Throwable $e) {
            Toast::error('Chyba při přihlášení přes Google: ' . $e->getMessage());
            $returnLanguage = isset($returnLanguage) ? $returnLanguage : lang()->getDefaultLanguage();
            header('Location: ' . locale_url('login', $returnLanguage));
            exit;
        }
    }
}
