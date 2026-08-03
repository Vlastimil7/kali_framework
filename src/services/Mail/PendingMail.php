<?php

namespace Services\Mail;

final class PendingMail
{
    /** @var list<Address> */
    private array $to = [];
    /** @var list<Address> */
    private array $cc = [];
    /** @var list<Address> */
    private array $bcc = [];

    public function __construct(
        string|Address $address,
        string $name = '',
        private readonly SmtpMailer $mailer = new SmtpMailer(),
    ) {
        $this->to[] = $this->address($address, $name);
    }

    public function to(string|Address $address, string $name = ''): self
    {
        $this->to[] = $this->address($address, $name);
        return $this;
    }

    public function cc(string|Address $address, string $name = ''): self
    {
        $this->cc[] = $this->address($address, $name);
        return $this;
    }

    public function bcc(string|Address $address, string $name = ''): self
    {
        $this->bcc[] = $this->address($address, $name);
        return $this;
    }

    public function send(EmailMessage|Mailable $email): SendResult
    {
        $message = $email instanceof Mailable ? $email->build() : $email;
        return $this->mailer->send($message, $this->to, $this->cc, $this->bcc);
    }

    private function address(string|Address $address, string $name): Address
    {
        return $address instanceof Address ? $address : new Address($address, $name);
    }
}
