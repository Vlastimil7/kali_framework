<?php

namespace Services\Mail;

final class SendResult
{
    private function __construct(
        public readonly bool $success,
        public readonly string $message,
    ) {
    }

    public static function sent(): self
    {
        return new self(true, 'Email sent.');
    }

    public static function failed(string $message): self
    {
        return new self(false, $message);
    }

    public function successful(): bool
    {
        return $this->success;
    }
}
