<?php

namespace Controllers\Front;

use Core\Controller;
use Core\Request;
use Helpers\RateLimiter;
use Helpers\ReCaptcha;
use Helpers\Logger;
use Helpers\ContactAttachmentUpload;
use Helpers\Flash;
use Helpers\Toast;
use Helpers\Validator;
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

    public function sendMessage(Request $request)
    {
        if (!$request->isMethod('POST')) {
            header('Location: ' . locale_url('contact'));
            exit;
        }

        $clinic = $request->string('clinic');
        // Rate limit
        $rateLimiter = new RateLimiter('contact_form');
        if (!$rateLimiter->check()) {
            $timeRemaining = ceil($rateLimiter->getTimeRemaining() / 60);

            Logger::warning('Contact form rate limit exceeded', [
                'email'  => $request->input('email'),
                'clinic' => $clinic ?: null,
                'wait_min' => $timeRemaining,
            ]);

            Toast::error("Překročili jste maximální počet pokusů. Zkuste to znovu za {$timeRemaining} minut.");
            header('Location: ' . locale_url('contact'));
            exit;
        }

        // Data z formuláře
        $firstName = $request->string('first_name');
        $lastName = $request->string('last_name');
        $fullName = trim($firstName . ' ' . $lastName);
        if ($fullName === '') {
            $fullName = $request->string('name');
            [$firstName, $lastName] = $this->splitName($fullName);
        }

        $topic = $request->string('topic');
        $phone = $request->string('phone');

        // očistí telefon na tel: odkaz: nechá jen + a čísla
        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);

        $formData = [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'fullName'  => $fullName,

            'email'     => $request->string('email'),
            'phone'     => $phone,
            'fullPhone' => $cleanPhone,

            'topic'   => $topic,
            'budget'  => $request->string('budget'),
            'subject' => $topic !== '' ? $topic : ('Kontakt – ' . ($clinic !== '' ? $clinic : 'ordinace')),
            'message' => $request->string('message'),

            'privacy' => $request->has('gdpr') ? 1 : 0,
            'clinic'  => $clinic,
        ];

        $validator = Validator::make($formData, [
            'fullName' => 'bail|required|string|max:120',
            'email' => 'bail|required|email|max:254',
            'phone' => ['nullable', 'regex:/^[0-9+\s\-]{6,20}$/'],
            'subject' => 'nullable|string|max:200',
            'message' => 'bail|required|string|max:2000',
            'privacy' => 'accepted',
        ], [
            'fullName.required' => 'Prosím vyplňte své jméno a příjmení.',
            'email.required' => 'Prosím vyplňte e-mailovou adresu.',
            'email.email' => 'Prosím zadejte platnou e-mailovou adresu.',
            'phone.regex' => 'Telefon má neplatný formát.',
            'message.required' => 'Prosím napište vaši zprávu.',
            'privacy.accepted' => 'Musíte souhlasit se zpracováním osobních údajů.',
        ], [
            'fullName' => 'jméno',
            'email' => 'e-mail',
            'phone' => 'telefon',
            'subject' => 'předmět',
            'message' => 'zpráva',
            'privacy' => 'souhlas se zpracováním údajů',
        ]);

        if ($validator->fails()) {
            Logger::info('Contact form validation failed', [
                'reason' => $validator->first(),
                'email'  => $formData['email'] ?? null,
                'clinic' => $clinic ?: null,
            ]);

            $validator->flash('contact', $request->post());
            header('Location: ' . locale_url('contact'));
            exit;
        }

        // reCAPTCHA
        $recaptchaToken = $request->string('recaptcha_token');
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
            Flash::withInput('contact', $request->post());
            header('Location: ' . locale_url('contact'));
            exit;
        }

        $attachmentUpload = new ContactAttachmentUpload();
        $upload = $attachmentUpload->saveTmp(
            $request->file('attachments', []),
            ROOT_PATH . '/storage/tmp/contact-attachments',
        );
        if (!$upload['success']) {
            $attachmentUpload->cleanup($upload['files']);
            Toast::error(implode(' ', $upload['errors']));
            Flash::withInput('contact', $request->post());
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
            Flash::withInput('contact', $request->post());
        }

        header('Location: ' . locale_url('contact'));
        exit;
    }

}
