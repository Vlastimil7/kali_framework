<?php

namespace Services\Mail\Mailables;

use Services\Mail\EmailMessage;
use Services\Mail\Mailable;

final class LeadEmail extends Mailable
{
    public function __construct(private readonly string $email)
    {
    }

    public function build(): EmailMessage
    {
        return EmailMessage::make()
            ->subject('[Poptávka] ' . $this->email)
            ->template('lead', ['email' => $this->email])
            ->text("Nová poptávka z lead formuláře\nEmail: {$this->email}\n")
            ->replyTo($this->email);
    }
}
