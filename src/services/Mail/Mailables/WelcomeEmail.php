<?php

namespace Services\Mail\Mailables;

use Services\Mail\EmailMessage;
use Services\Mail\Mailable;

final class WelcomeEmail extends Mailable
{
    public function __construct(
        private readonly string $name,
        private readonly string $loginUrl,
    ) {
    }

    public function build(): EmailMessage
    {
        return EmailMessage::make()
            ->subject('Vítejte na VK-DEV.cz')
            ->template('welcome', [
                'name' => $this->name,
                'loginUrl' => $this->loginUrl,
            ])
            ->text(
                "Dobrý den {$this->name},\n\n"
                . "váš účet je připraven. Přihlášení: {$this->loginUrl}\n"
            );
    }
}
