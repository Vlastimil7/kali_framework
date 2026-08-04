<?php

namespace Services\Mail\Mailables;

use Services\Mail\EmailMessage;
use Services\Mail\Mailable;

final class ContactEmail extends Mailable
{
    /**
     * @param array<string, mixed> $formData
     * @param list<array{path: string, name?: string}> $attachments
     */
    public function __construct(
        private readonly array $formData,
        private readonly array $attachments = [],
    ) {
    }

    public function build(): EmailMessage
    {
        $topic = trim((string) ($this->formData['topic'] ?? '')) ?: 'Kontakt';
        $budget = trim((string) ($this->formData['budget'] ?? ''));
        $subject = '[Poptávka] ' . $topic . ($budget !== '' ? ' / ' . $budget : '');

        $message = EmailMessage::make()
            ->subject($subject)
            ->template('contact', ['form' => $this->formData])
            ->text($this->plainText());

        $replyEmail = trim((string) ($this->formData['email'] ?? ''));
        if (filter_var($replyEmail, FILTER_VALIDATE_EMAIL)) {
            $replyName = trim((string) ($this->formData['fullName'] ?? ''));
            $message->replyTo($replyEmail, $replyName);
        }

        foreach ($this->attachments as $attachment) {
            $path = (string) ($attachment['path'] ?? '');
            if ($path !== '' && is_file($path)) {
                $message->attach($path, $attachment['name'] ?? null);
            }
        }

        return $message;
    }

    private function plainText(): string
    {
        return "NOVÁ POPTÁVKA Z KONTAKTNÍHO FORMULÁŘE\n"
            . 'Jméno: ' . (string) ($this->formData['fullName'] ?? '') . "\n"
            . 'Email: ' . (string) ($this->formData['email'] ?? '') . "\n"
            . 'Telefon: ' . (string) ($this->formData['phone'] ?? '') . "\n"
            . 'Typ projektu: ' . (string) ($this->formData['topic'] ?? '') . "\n"
            . 'Rozpočet: ' . (string) ($this->formData['budget'] ?? '') . "\n\n"
            . "Zpráva:\n" . (string) ($this->formData['message'] ?? '') . "\n";
    }
}
