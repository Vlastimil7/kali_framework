<?php

namespace Services\Mail;

use InvalidArgumentException;

final class EmailMessage
{
    private string $subject = '';
    private ?string $template = null;
    private array $data = [];
    private ?string $html = null;
    private ?string $text = null;
    private ?Address $replyTo = null;
    /** @var list<Attachment> */
    private array $attachments = [];

    public static function make(): self
    {
        return new self();
    }

    public function subject(string $subject): self
    {
        $this->subject = trim($subject);
        return $this;
    }

    public function template(string $template, array $data = []): self
    {
        $this->template = $template;
        $this->data = $data;
        return $this;
    }

    public function html(string $html): self
    {
        $this->html = $html;
        return $this;
    }

    public function text(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function replyTo(string|Address $address, string $name = ''): self
    {
        $this->replyTo = $address instanceof Address ? $address : new Address($address, $name);
        return $this;
    }

    public function attach(string $path, ?string $name = null): self
    {
        $this->attachments[] = new Attachment($path, $name);
        return $this;
    }

    public function validate(): void
    {
        if ($this->subject === '') {
            throw new InvalidArgumentException('Email subject cannot be empty.');
        }
        if ($this->template === null && $this->html === null && $this->text === null) {
            throw new InvalidArgumentException('Email must have a template, HTML, or plain-text body.');
        }
    }

    public function getSubject(): string { return $this->subject; }
    public function getTemplate(): ?string { return $this->template; }
    public function getData(): array { return $this->data; }
    public function getHtml(): ?string { return $this->html; }
    public function getText(): ?string { return $this->text; }
    public function getReplyTo(): ?Address { return $this->replyTo; }
    /** @return list<Attachment> */
    public function getAttachments(): array { return $this->attachments; }
}
