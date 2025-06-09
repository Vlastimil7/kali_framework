<?php

namespace Controllers;

use Core\Controller;
use Models\User;
use Helpers\RateLimiter;
use Helpers\ReCaptcha;
use Helpers\Mailer;

class UserController extends Controller
{
    private $userModel;
    private $orderModel;
    private $mailer;

    public function __construct()
    {
        $this->userModel = new User();
        $this->mailer = new Mailer();
        
        // RateLimiter a ReCaptcha inicializujeme až při potřebě, protože potřebují parametry
    }

    // Zobrazení přihlašovacího formuláře
    public function showLogin()
    {
        $this->view('users/login', [
            'title' => 'Přihlášení | Kali-Framework'
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
                    header('Location: ' . BASE_URL . '/admin/dashboard');
                } else {
                    header('Location: ' . BASE_URL . '/dashboard');
                }
                exit;
            } else {
                // Nastavení flash zprávy
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';

                // Zachování emailu pro pohodlí
                $_SESSION['form_data'] = ['email' => $email];

                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }
    }

    // Zobrazení registračního formuláře
    public function showRegister()
    {
        $this->view('users/register', [
            'title' => 'Registrace | Kali-Framework'
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
                $_SESSION['flash_message'] = 'Registrace proběhla úspěšně! Nyní se můžete přihlásit.';
                $_SESSION['flash_type'] = 'success';

                header('Location: ' . BASE_URL . '/login');
                exit;
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';

                // Zachování zadaných údajů
                $_SESSION['form_data'] = $userData;

                header('Location: ' . BASE_URL . '/register');
                exit;
            }
        }
    }

    // Zobrazení profilu uživatele
    public function showProfile()
    {
        // Kontrola, zda je uživatel přihlášen
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);


        $this->view('users/profile', [
            'title' => 'Můj profil | Kali-Framework',
            'user' => $user
        ]);
    }

    // Aktualizace profilu
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Kontrola, zda je uživatel přihlášen
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

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
                $_SESSION['flash_message'] = 'Profil byl úspěšně aktualizován';
                $_SESSION['flash_type'] = 'success';

                // Aktualizace session proměnných
                $_SESSION['user_name'] = $userData['name'];
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }

            header('Location: ' . BASE_URL . '/profile');
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
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    /**
     * Zobrazení formuláře pro zadání emailu k resetování hesla
     */
    public function showPasswordResetRequest()
    {
        $this->view('users/password_request', [
            'title' => 'Zapomenuté heslo | Kali-Framework'
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
                $_SESSION['flash_message'] = "Překročili jste maximální počet pokusů. Zkuste to znovu za {$timeRemaining} minut.";
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/password/reset');
                exit;
            }

            // Validace vstupu
            if (empty($email)) {
                $_SESSION['flash_message'] = 'Zadejte prosím emailovou adresu';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/password/reset');
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
                
            //     $_SESSION['flash_message'] = $errorMessage;
            //     $_SESSION['flash_type'] = 'error';
            //     header('Location: ' . BASE_URL . '/password/reset');
            //     exit;
            // }

            // Vytvoření tokenu pro reset hesla
            $result = $this->userModel->createPasswordResetToken($email);

            if ($result['success']) {
                // Odeslání emailu pomocí PHPMailer
                $mailResult = $this->mailer->sendPasswordReset($email, $result['token'], $result['userName'] ?? '');

                // Vždy zobrazíme stejnou zprávu, ať už byl email nalezen nebo ne - bezpečnostní opatření
                $_SESSION['flash_message'] = 'Pokud je zadaný email registrován v našem systému, odeslali jsme instrukce pro reset hesla.';
                $_SESSION['flash_type'] = 'success';

                if (!$mailResult['success']) {
                    // Logování chyby, ale nezobrazování uživateli
                    error_log('Chyba při odesílání emailu: ' . $mailResult['message']);
                }
            } else {
                // Stejná zpráva i v případě, že email neexistuje - ochrana proti enumeration útokům
                $_SESSION['flash_message'] = 'Pokud je zadaný email registrován v našem systému, odeslali jsme instrukce pro reset hesla.';
                $_SESSION['flash_type'] = 'success';
            }

            header('Location: ' . BASE_URL . '/password/reset');
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
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/password/reset');
            exit;
        }

        $this->view('users/password_reset', [
            'title' => 'Reset hesla | Kali-Framework',
            'token' => $token
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
                $_SESSION['flash_message'] = "Překročili jste maximální počet pokusů. Zkuste to znovu za {$timeRemaining} minut.";
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/password/reset/' . $token);
                exit;
            }

            // Validace
            if (empty($password) || empty($passwordConfirm)) {
                $_SESSION['flash_message'] = 'Vyplňte prosím všechna pole';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/password/reset/' . $token);
                exit;
            }

            if ($password !== $passwordConfirm) {
                $_SESSION['flash_message'] = 'Hesla se neshodují';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/password/reset/' . $token);
                exit;
            }

            if (strlen($password) < 6) {
                $_SESSION['flash_message'] = 'Heslo musí mít alespoň 6 znaků';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/password/reset/' . $token);
                exit;
            }

            // Inicializace ReCaptcha
            $recaptcha = new ReCaptcha(RECAPTCHA_SECRET_KEY);
            $recaptchaResult = $recaptcha->verify($recaptchaToken);

            // if (!$recaptchaResult['success'] || $recaptchaResult['score'] < 0.5) {
            //     $_SESSION['flash_message'] = 'Ověření reCAPTCHA selhalo. Zkuste to prosím znovu.';
            //     $_SESSION['flash_type'] = 'error';
            //     header('Location: ' . BASE_URL . '/password/reset/' . $token);
            //     exit;
            // }

            // Reset hesla
            $result = $this->userModel->resetPassword($token, $password);

            if ($result['success']) {
                $_SESSION['flash_message'] = 'Vaše heslo bylo úspěšně změněno. Nyní se můžete přihlásit.';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/login');
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/password/reset/' . $token);
            }
            exit;
        }
    }
}