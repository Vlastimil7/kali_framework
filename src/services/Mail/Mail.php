<?php

namespace Services\Mail;

final class Mail
{
    public static function to(string|Address $address, string $name = ''): PendingMail
    {
        return new PendingMail($address, $name);
    }
}
