<?php

namespace Services\Mail;

use InvalidArgumentException;

final class Address
{
    public function __construct(
        public readonly string $email,
        public readonly string $name = '',
    ) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$email}");
        }
    }
}
