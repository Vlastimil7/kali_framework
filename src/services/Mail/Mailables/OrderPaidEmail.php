<?php

namespace Services\Mail\Mailables;

use Services\Mail\EmailMessage;
use Services\Mail\Mailable;

final class OrderPaidEmail extends Mailable
{
    /**
     * @param array<string, mixed> $order
     * @param list<string> $pdfPaths
     */
    public function __construct(
        private readonly array $order,
        private readonly array $pdfPaths,
    ) {
    }

    public function build(): EmailMessage
    {
        $orderNumber = (string) ($this->order['order_number'] ?? ('#' . ($this->order['id'] ?? '')));
        $name = (string) ($this->order['billing_name'] ?? '');

        $message = EmailMessage::make()
            ->subject("Vaše objednávka {$orderNumber} – vouchery v příloze")
            ->template('order_paid', [
                'order' => $this->order,
                'orderNumber' => $orderNumber,
                'name' => $name,
            ])
            ->text(
                "Dobrý den {$name},\n\n"
                . "objednávka {$orderNumber} byla zaplacena. V příloze posíláme vaše vouchery v PDF.\n\n"
                . "Děkujeme.\n"
            );

        foreach ($this->pdfPaths as $path) {
            if ($path !== '' && is_file($path)) {
                $message->attach($path, basename($path));
            }
        }

        return $message;
    }
}
