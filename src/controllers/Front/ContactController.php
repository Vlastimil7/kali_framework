<?php

namespace Controllers\Front;

use Core\Controller;
use Helpers\RateLimiter;
use Helpers\ReCaptcha;
use Helpers\Logger;
use Helpers\ContactAttachmentUpload;
use Helpers\Flash;
use Helpers\Toast;
use Services\Mail\Mail;
use Services\Mail\Mailables\ContactEmail;

class ContactController extends Controller
{
    public function __construct()
    {
        parent::__construct();
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

    public function sendMessage()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . locale_url('contact'));
            exit;
        }

        $clinic = trim($_POST['clinic'] ?? '');
        // Rate limit
        $rateLimiter = new RateLimiter('contact_form');
        if (!$rateLimiter->check()) {
            $timeRemaining = ceil($rateLimiter->getTimeRemaining() / 60);

            Logger::warning('Contact form rate limit exceeded', [
                'email'  => $_POST['email'] ?? null,
                'clinic' => $clinic ?: null,
                'wait_min' => $timeRemaining,
            ]);

            Toast::error("Překročili jste maximální počet pokusů. Zkuste to znovu za {$timeRemaining} minut.");
            header('Location: ' . locale_url('contact'));
            exit;
        }

        // Data z formuláře
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $fullName = trim($firstName . ' ' . $lastName);
        if ($fullName === '') {
            $fullName = trim((string) ($_POST['name'] ?? ''));
            [$firstName, $lastName] = $this->splitName($fullName);
        }

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
            'budget'  => trim((string) ($_POST['budget'] ?? '')),
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

            Toast::error($validationResult['message']);
            Flash::withInput('contact', $_POST);
            header('Location: ' . locale_url('contact'));
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

            Toast::error('Ověření reCAPTCHA selhalo. Zkuste to prosím znovu.');
            Flash::withInput('contact', $_POST);
            header('Location: ' . locale_url('contact'));
            exit;
        }

        $attachmentUpload = new ContactAttachmentUpload();
        $upload = $attachmentUpload->saveTmp(
            $_FILES['attachments'] ?? [],
            ROOT_PATH . '/storage/tmp/contact-attachments',
        );
        if (!$upload['success']) {
            $attachmentUpload->cleanup($upload['files']);
            Toast::error(implode(' ', $upload['errors']));
            Flash::withInput('contact', $_POST);
            header('Location: ' . locale_url('contact'));
            exit;
        }

        $result = Mail::to(MAIL_TO_ADDRESS, MAIL_TO_NAME)
            ->send(new ContactEmail($formData, $upload['files']));
        $attachmentUpload->cleanup($upload['files']);

        if ($result->successful()) {
            Logger::info('Contact message sent', [
                'email'  => $formData['email'],
                'clinic' => $clinic,
            ]);

            Toast::success('Vaše zpráva byla úspěšně odeslána. Brzy vás budeme kontaktovat.');
            Flash::clearOld('contact');
        } else {
            Logger::error('Contact message send failed', [
                'email'  => $formData['email'] ?? null,
                'clinic' => $clinic ?: null,
                'error'  => $result->message,
            ]);

            Toast::error('Nepodařilo se odeslat zprávu. Zkuste to prosím později nebo nás kontaktujte telefonicky.');
            Flash::withInput('contact', $_POST);
        }

        header('Location: ' . locale_url('contact'));
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
