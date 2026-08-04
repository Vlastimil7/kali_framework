<?php

namespace Services\Mail\Mailables;

use Services\Mail\EmailMessage;
use Services\Mail\Mailable;

final class PasswordResetEmail extends Mailable
{
    public function __construct(
        private readonly string $name,
        private readonly string $resetUrl,
    ) {
    }

    public function build(): EmailMessage
    {
        $displayName = trim($this->name) !== '' ? $this->name : 'uživateli';

        return EmailMessage::make()
            ->subject('Reset hesla na VK-DEV.cz')
            ->template('password_reset', [
                'name' => $displayName,
                'resetUrl' => $this->resetUrl,
            ])
            ->text(
                "Dobrý den, {$displayName},\n\n"
                . "obdrželi jsme žádost o reset hesla. Odkaz je platný jednu hodinu:\n"
                . $this->resetUrl . "\n\n"
                . "Pokud jste o reset hesla nežádali, tento email ignorujte.\n",
            );
    }
}
