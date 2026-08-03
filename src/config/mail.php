<?php

$fromAddress = (string)env('MAIL_FROM_ADDRESS', '');
$fromName = (string)env('MAIL_FROM_NAME', config('app.name', 'VK-DEV'));

return [
    'mailer' => (string)env('MAIL_MAILER', 'smtp'),
    'smtp' => [
        'host' => (string)env('SMTP_HOST', 'smtp.gmail.com'),
        'port' => (int)env('SMTP_PORT', 587),
        'username' => (string)env('SMTP_USERNAME', ''),
        'password' => (string)env('SMTP_PASSWORD', ''),
        'encryption' => (string)env('SMTP_ENCRYPTION', 'tls'),
        'timeout' => (int)env('SMTP_TIMEOUT', 15),
    ],
    'from' => [
        'address' => $fromAddress,
        'name' => $fromName,
    ],
    'to' => [
        'address' => (string)env('MAIL_TO_ADDRESS', $fromAddress),
        'name' => (string)env('MAIL_TO_NAME', $fromName),
    ],
];
