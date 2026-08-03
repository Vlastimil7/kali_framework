<?php

namespace Controllers\Front;

use Core\Controller;
use Models\User;
use Helpers\RateLimiter;
use Helpers\ReCaptcha;
use Helpers\Flash;
use Helpers\Toast;
use Services\Mail\Mail;
use Services\Mail\Mailables\PasswordResetEmail;

class UserController extends Controller
{
    private $userModel;
    private $orderModel;

    public function __construct()
    {
        $this->userModel = new User();
        // RateLimiter a ReCaptcha inicializujeme až při potřebě, protože potřebují parametry
    }

    // Zobrazení přihlašovacího formuláře
    public function showLogin()
    {
        $this->view('users/login', [
            'title' => 'Přihlášení | Midobarbershop.cz',
            'show_sidebar' => false,
        ]);
    }

    // Zpracování přihlášení
    public function processLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $result = $this->userModel->login($email, $password);

            if ($result['success']) {
                // Uložení údajů do session
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user_name'] = $result['user']['name'];
                $_SESSION['user_email'] = $result['user']['email'];
                $_SESSION['user_role'] = $result['user']['role'];

                // Přesměrování podle role
                if ($result['user']['role'] === 'admin') {
                    header('Location: ' . locale_url('admin/dashboard'));
                } else {
                    header('Location: ' . locale_url('profile'));
                }
                exit;
            } else {
                Toast::error($result['message']);
                Flash::withInput('login', ['email' => $email]);

                header('Location: ' . locale_url('login'));
                exit;
            }
        }
    }

    // Zobrazení registračního formuláře
    public function showRegister()
    {
        $this->view('users/register', [
            'title' => 'Registrace | Midobarbershop.cz',
            'show_sidebar' => false,
        ]);
    }

    // Zpracování registrace
    public function processRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'name' => $_POST['name'] ?? '',
                'surname' => $_POST['surname'] ?? '',
                'phone' => $_POST['phone'] ?? ''
            ];

            $result = $this->userModel->register($userData);

            if ($result['success']) {
                Toast::success('Registrace proběhla úspěšně! Nyní se můžete přihlásit.');

                header('Location: ' . locale_url('login'));
                exit;
            } else {
                Toast::error($result['message']);
                Flash::withInput('register', $userData);

                header('Location: ' . locale_url('register'));
                exit;
            }
        }
    }

    // Zobrazení profilu uživatele
    public function showProfile()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);


        $this->view('users/profile', [
            'title' => 'Můj profil | Midobarbershop.cz',
            'user' => $user,
            'show_sidebar' => false,
        ]);
    }

    // Aktualizace profilu
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];

            // Získání aktualizovaných údajů
            $userData = [
                'name' => $_POST['name'] ?? '',
                'surname' => $_POST['surname'] ?? '',
                'phone' => $_POST['phone'] ?? ''
            ];

            // Přidání hesla, pokud bylo vyplněno
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }

            $result = $this->userModel->updateProfile($userId, $userData);

            if ($result['success']) {
                Toast::success('Profil byl úspěšně aktualizován');

                // Aktualizace session proměnných
                $_SESSION['user_name'] = $userData['name'];
            } else {
                Toast::error($result['message']);
            }

            header('Location: ' . locale_url('profile'));
            exit;
        }
    }

    // Odhlášení uživatele
    public function logout()
    {
        // Zničení session
        session_unset();
        session_destroy();

        // Přesměrování na přihlašovací stránku
        header('Location: ' . locale_url('login'));
        exit;
    }

    /**
     * Zobrazení formuláře pro zadání emailu k resetování hesla
     */
    public function showPasswordResetRequest()
    {
        $this->view('users/password_request', [
            'title' => 'Zapomenuté heslo | Midobarbershop.cz',
            'show_sidebar' => false,
        ]);
    }

    /**
     * Zpracování odeslání emailu pro reset hesla
     */
    public function sendPasswordResetEmail()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $recaptchaToken = $_POST['recaptcha_token'] ?? '';

            // Inicializace RateLimiter pro tuto akci
            $rateLimiter = new RateLimiter('password_reset_request');
            if (!$rateLimiter->check()) {
                $timeRemaining = ceil($rateLimiter->getTimeRemaining() / 60); // Převod na minuty
                Toast::error("Překročili jste maximální počet pokusů. Zkuste to znovu za {$timeRemaining} minut.");
                Flash::withInput('password_request', ['email' => $email]);
                header('Location: ' . locale_url('password/reset'));
                exit;
            }

            // Validace vstupu
            if (empty($email)) {
                Toast::error('Zadejte prosím emailovou adresu');
                Flash::withInput('password_request', ['email' => $email]);
                header('Location: ' . locale_url('password/reset'));
                exit;
            }

            // Inicializace ReCaptcha
            $recaptcha = new ReCaptcha(RECAPTCHA_SECRET_KEY);
            $recaptchaResult = $recaptcha->verify($recaptchaToken);

            // Pokud ověření selhalo, zobrazíme vhodnou chybovou zprávu
            // if (!$recaptchaResult['success']) {
            //     $errorMessage = 'Ověření reCAPTCHA selhalo. ';

            //     // Přidáme specifický typ chyby pro lepší debugging
            //     switch ($recaptchaResult['error_type'] ?? '') {
            //         case 'configuration':
            //             $errorMessage .= 'Chyba konfigurace reCAPTCHA.';
            //             error_log('reCAPTCHA configuration error: ' . ($recaptchaResult['message'] ?? 'Unknown'));
            //             break;

            //         case 'connection':
            //             $errorMessage .= 'Nepodařilo se připojit k ověřovací službě.';
            //             error_log('reCAPTCHA connection error: ' . ($recaptchaResult['message'] ?? 'Unknown'));
            //             break;

            //         case 'expired_token':
            //             $errorMessage .= 'Vypršela platnost ověření, zkuste to znovu.';
            //             break;

            //         case 'low_score':
            //             $errorMessage .= 'Vaše aktivita byla vyhodnocena jako potenciálně nebezpečná.';
            //             break;

            //         default:
            //             $errorMessage .= 'Zkuste to prosím znovu.';
            //     }

            //     Toast::error($errorMessage);
            //     header('Location: ' . locale_url('password/reset'));
            //     exit;
            // }

            // Vytvoření tokenu pro reset hesla
            $result = $this->userModel->createPasswordResetToken($email);

            if ($result['success']) {
                $resetUrl = locale_site_url('password/reset/' . $result['token']);
                $mailResult = Mail::to($email, $result['userName'] ?? '')
                    ->send(new PasswordResetEmail($result['userName'] ?? '', $resetUrl));

                // Vždy zobrazíme stejnou zprávu, ať už byl email nalezen nebo ne - bezpečnostní opatření
                Toast::success('Pokud je zadaný email registrován v našem systému, odeslali jsme instrukce pro reset hesla.');

                if (!$mailResult->successful()) {
                    // Logování chyby, ale nezobrazování uživateli
                    error_log('Chyba při odesílání emailu: ' . $mailResult->message);
                }
            } else {
                // Stejná zpráva i v případě, že email neexistuje - ochrana proti enumeration útokům
                Toast::success('Pokud je zadaný email registrován v našem systému, odeslali jsme instrukce pro reset hesla.');
            }

            header('Location: ' . locale_url('password/reset'));
            exit;
        }
    }

    /**
     * Zobrazení formuláře pro zadání nového hesla
     */
    public function showPasswordReset($token)
    {
        // Ověření platnosti tokenu
        $result = $this->userModel->verifyPasswordResetToken($token);

        if (!$result['success']) {
            Toast::error($result['message']);
            header('Location: ' . locale_url('password/reset'));
            exit;
        }

        $this->view('users/password_reset', [
            'title' => 'Reset hesla | Midobarbershop.cz',
            'token' => $token,
            'show_sidebar' => false,
        ]);
    }

    /**
     * Zpracování resetování hesla
     */
    public function updatePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $recaptchaToken = $_POST['recaptcha_token'] ?? '';

            // Inicializace RateLimiter pro tuto akci
            $rateLimiter = new RateLimiter('password_reset_confirm');
            if (!$rateLimiter->check()) {
                $timeRemaining = ceil($rateLimiter->getTimeRemaining() / 60); // Převod na minuty
                Toast::error("Překročili jste maximální počet pokusů. Zkuste to znovu za {$timeRemaining} minut.");
                header('Location: ' . locale_url('password/reset/' . $token));
                exit;
            }

            // Validace
            if (empty($password) || empty($passwordConfirm)) {
                Toast::error('Vyplňte prosím všechna pole');
                header('Location: ' . locale_url('password/reset/' . $token));
                exit;
            }

            if ($password !== $passwordConfirm) {
                Toast::error('Hesla se neshodují');
                header('Location: ' . locale_url('password/reset/' . $token));
                exit;
            }

            if (strlen($password) < 6) {
                Toast::error('Heslo musí mít alespoň 6 znaků');
                header('Location: ' . locale_url('password/reset/' . $token));
                exit;
            }

            // Inicializace ReCaptcha
            $recaptcha = new ReCaptcha(RECAPTCHA_SECRET_KEY);
            $recaptchaResult = $recaptcha->verify($recaptchaToken);

            // if (!$recaptchaResult['success'] || $recaptchaResult['score'] < 0.5) {
            //     Toast::error('Ověření reCAPTCHA selhalo. Zkuste to prosím znovu.');
            //     header('Location: ' . locale_url('password/reset/' . $token));
            //     exit;
            // }

            // Reset hesla
            $result = $this->userModel->resetPassword($token, $password);

            if ($result['success']) {
                Toast::success('Vaše heslo bylo úspěšně změněno. Nyní se můžete přihlásit.');
                header('Location: ' . locale_url('login'));
            } else {
                Toast::error($result['message']);
                header('Location: ' . locale_url('password/reset/' . $token));
            }
            exit;
        }
    }
}
