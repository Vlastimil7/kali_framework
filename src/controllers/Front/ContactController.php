<?php

namespace Controllers\Front;

use Core\Controller;
use Helpers\RateLimiter;
use Helpers\ReCaptcha;
use Helpers\Mailer;
use Helpers\Logger;

class ContactController extends Controller
{
    private Mailer $mailer;

    public function __construct()
    {
        parent::__construct();
        $this->mailer = new Mailer();
    }

    public function index()
    {
        $this->view('contact/index', [
            'title' => 'Kontakty',
        ]);
    }

    private function splitName(string $fullName): array
    {
        $fullName = preg_replace('/\s+/', ' ', trim($fullName));
        if ($fullName === '') return ['', ''];

        $parts = explode(' ', $fullName);
        $first = array_shift($parts);
        $last  = trim(implode(' ', $parts));

        return [$first, $last];
    }

    /**
     * Udělá z clinic bezpečný "namespace" klíč pro session
     * Příklad: "Midobarbershop.cz s.r.o." -> "contact_Midobarbershop.cz_s_r_o_"
     */
    private function clinicNs(string $clinic): string
    {
        $clinic = trim($clinic);
        if ($clinic === '') $clinic = 'default';

        $slug = mb_strtolower($clinic);
        $slug = preg_replace('~[^a-z0-9]+~i', '_', $slug);
        $slug = trim($slug, '_');

        return 'contact_' . $slug;
    }

    /**
     * Uloží hlášku do session pro konkrétní formulář
     */
    private function flash(string $ns, string $type, string $message): void
    {
        $_SESSION['flash'][$ns] = [
            'type' => $type,      // "success" nebo "error"
            'message' => $message
        ];
    }

    /**
     * Uloží předvyplnění polí (old data) pro konkrétní formulář
     */
    private function setOld(string $ns, array $formData): void
    {
        $_SESSION['old'][$ns] = [
            'name'    => $formData['fullName'] ?? '',
            'email'   => $formData['email'] ?? '',
            'phone'   => $formData['phone'] ?? '',
            'topic'   => $formData['topic'] ?? '',
            'message' => $formData['message'] ?? '',
            // gdpr záměrně neukládám (většinou se po chybě musí znovu zaškrtnout)
        ];
    }

    private function clearOld(string $ns): void
    {
        unset($_SESSION['old'][$ns]);
    }

    public function sendMessage()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contact');
            exit;
        }

        $clinic = trim($_POST['clinic'] ?? '');
        $ns = $this->clinicNs($clinic);

        // Rate limit
        $rateLimiter = new RateLimiter('contact_form');
        if (!$rateLimiter->check()) {
            $timeRemaining = ceil($rateLimiter->getTimeRemaining() / 60);

            Logger::warning('Contact form rate limit exceeded', [
                'email'  => $_POST['email'] ?? null,
                'clinic' => $clinic ?: null,
                'wait_min' => $timeRemaining,
            ]);

            $this->flash($ns, 'error', "Překročili jste maximální počet pokusů. Zkuste to znovu za {$timeRemaining} minut.");
            header('Location: ' . BASE_URL . '/contact');
            exit;
        }

        // Data z formuláře
        $fullName = trim($_POST['name'] ?? '');
        [$firstName, $lastName] = $this->splitName($fullName);

        $topic = trim($_POST['topic'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // očistí telefon na tel: odkaz: nechá jen + a čísla
        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);

        $formData = [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'fullName'  => $fullName,

            'email'     => trim($_POST['email'] ?? ''),
            'phone'     => $phone,
            'fullPhone' => $cleanPhone,

            'topic'   => $topic,
            'subject' => $topic !== '' ? $topic : ('Kontakt – ' . ($clinic !== '' ? $clinic : 'ordinace')),
            'message' => trim($_POST['message'] ?? ''),

            'privacy' => isset($_POST['gdpr']) ? 1 : 0,
            'clinic'  => $clinic,
        ];

        // Validace
        $validationResult = $this->validateContactForm($formData);
        if (!$validationResult['success']) {
            Logger::info('Contact form validation failed', [
                'reason' => $validationResult['message'],
                'email'  => $formData['email'] ?? null,
                'clinic' => $clinic ?: null,
            ]);

            $this->flash($ns, 'error', $validationResult['message']);
            $this->setOld($ns, $formData); // ✅ uloží vyplněná pole
            header('Location: ' . BASE_URL . '/contact');
            exit;
        }

        // reCAPTCHA
        $recaptchaToken = $_POST['recaptcha_token'] ?? '';
        $recaptcha = new ReCaptcha(RECAPTCHA_SECRET_KEY);
        $recaptchaResult = $recaptcha->verify($recaptchaToken, 'contact', 0.5);

        if (!$recaptchaResult['success']) {
            Logger::warning('reCAPTCHA verification failed', [
                'email' => $formData['email'] ?? null,
                'clinic' => $clinic ?: null,
                'error' => $recaptchaResult['error_type'] ?? null,
                'score' => $recaptchaResult['score'] ?? null,
            ]);

            $this->flash($ns, 'error', 'Ověření reCAPTCHA selhalo. Zkuste to prosím znovu.');
            $this->setOld($ns, $formData); // ✅ ať nemusí psát znova
            header('Location: ' . BASE_URL . '/contact');
            exit;
        }

        // Odeslání emailu
        $result = $this->mailer->sendContactMessage($formData);

        if ($result['success']) {
            Logger::info('Contact message sent', [
                'email'  => $formData['email'],
                'clinic' => $clinic,
            ]);

            $this->flash($ns, 'success', 'Vaše zpráva byla úspěšně odeslána. Brzy vás budeme kontaktovat.');
            $this->clearOld($ns); // 
        } else {
            Logger::error('Contact message send failed', [
                'email'  => $formData['email'] ?? null,
                'clinic' => $clinic ?: null,
                'error'  => $result['message'] ?? null,
            ]);

            $this->flash($ns, 'error', 'Nepodařilo se odeslat zprávu. Zkuste to prosím později nebo nás kontaktujte telefonicky.');
            $this->setOld($ns, $formData);
        }

        header('Location: ' . BASE_URL . '/contact');
        exit;
    }

    private function validateContactForm(array $data): array
    {
        if (trim($data['fullName'] ?? '') === '') {
            return ['success' => false, 'message' => 'Prosím vyplňte své jméno a příjmení'];
        }

        if (empty($data['email'])) {
            return ['success' => false, 'message' => 'Prosím vyplňte emailovou adresu'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Prosím zadejte platnou emailovou adresu'];
        }

        if (empty($data['message'])) {
            return ['success' => false, 'message' => 'Prosím napište vaši zprávu'];
        }

        if (!$data['privacy']) {
            return ['success' => false, 'message' => 'Musíte souhlasit se zpracováním osobních údajů'];
        }

        $phone = trim($data['phone'] ?? '');
        if ($phone !== '' && !preg_match('/^[0-9+\s\-]{6,20}$/', $phone)) {
            return ['success' => false, 'message' => 'Telefon má neplatný formát'];
        }

        if (mb_strlen($data['fullName']) > 120) {
            return ['success' => false, 'message' => 'Jméno je příliš dlouhé'];
        }

        if (!empty($data['subject']) && mb_strlen($data['subject']) > 200) {
            return ['success' => false, 'message' => 'Předmět může mít maximálně 200 znaků'];
        }

        if (mb_strlen($data['message']) > 2000) {
            return ['success' => false, 'message' => 'Zpráva může mít maximálně 2000 znaků'];
        }

        return ['success' => true];
    }
}
