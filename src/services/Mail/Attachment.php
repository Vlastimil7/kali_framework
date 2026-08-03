<?php

namespace Services\Mail;

use InvalidArgumentException;

final class Attachment
{
    public function __construct(
        public readonly string $path,
        public readonly ?string $name = null,
    ) {
        if (!is_file($path) || !is_readable($path)) {
            throw new InvalidArgumentException("Attachment is not readable: {$path}");
        }
    }
}
