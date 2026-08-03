<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/vendor/autoload.php';
Core\Config::set('app', ['base_url' => '', 'site_url' => 'https://example.test']);
require ROOT_PATH . '/src/helpers/language_helper.php';

use Services\Mail\Mailables\WelcomeEmail;
use Services\Mail\Mailables\PasswordResetEmail;
use Services\Mail\Mailables\ContactEmail;
use Services\Mail\Mailables\LeadEmail;
use Services\Mail\Mailables\OrderPaidEmail;
use Services\Mail\Mailables\OrderStatusChangedEmail;
use Services\Mail\Mailable;
use Services\Mail\TemplateRenderer;

$message = (new WelcomeEmail('<Anna>', 'https://example.test/login?a=1&b=2'))->build();
$message->validate();

$renderer = new TemplateRenderer();
$html = $renderer->render(
    (string) $message->getTemplate(),
    $message->getData(),
    $message->getSubject(),
);

if (!str_contains($html, '&lt;Anna&gt;')) {
    throw new RuntimeException('Template data was not escaped.');
}
if (!str_contains($html, 'https://example.test/login?a=1&amp;b=2')) {
    throw new RuntimeException('Template URL was not escaped.');
}
if (!str_contains($html, 'VK-DEV.cz')) {
    throw new RuntimeException('Email layout was not applied.');
}

$mailables = [
    new PasswordResetEmail('Anna', 'https://example.test/password/reset/token'),
    new ContactEmail([
        'fullName' => '<Anna Novak>',
        'email' => 'anna@example.test',
        'phone' => '+420 123 456 789',
        'topic' => 'Web',
        'budget' => '100 000 Kč',
        'message' => '<script>alert(1)</script>',
    ]),
    new LeadEmail('lead@example.test'),
    new OrderPaidEmail([
        'id' => 10,
        'order_number' => 'ORD-10',
        'billing_name' => '<Anna>',
        'total_amount_cents' => 125000,
        'currency' => 'CZK',
    ], []),
    new OrderStatusChangedEmail([
        'id' => 10,
        'order_number' => 'ORD-10',
        'billing_name' => 'Anna',
    ], 'refunded', '<unsafe note>'),
];

foreach ($mailables as $mailable) {
    if (!$mailable instanceof Mailable) {
        throw new RuntimeException('Mail test contains an invalid mailable.');
    }
    $built = $mailable->build();
    $built->validate();
    $rendered = $renderer->render((string) $built->getTemplate(), $built->getData(), $built->getSubject());
    if (!str_contains($rendered, '<!DOCTYPE html>')) {
        throw new RuntimeException($mailable::class . ' did not use the email layout.');
    }
    if (str_contains($rendered, '<script>alert(1)</script>') || str_contains($rendered, '<unsafe note>')) {
        throw new RuntimeException($mailable::class . ' rendered unsafe template data.');
    }
}

$contactMessage = $mailables[1]->build();
if ($contactMessage->getReplyTo()?->email !== 'anna@example.test') {
    throw new RuntimeException('Contact email reply-to was not configured.');
}

try {
    $renderer->render('../config/config', []);
    throw new RuntimeException('Unsafe template name was accepted.');
} catch (InvalidArgumentException) {
    // Expected.
}

echo "mail smoke tests passed\n";
