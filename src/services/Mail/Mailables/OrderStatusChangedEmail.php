<?php

namespace Services\Mail\Mailables;

use Services\Mail\EmailMessage;
use Services\Mail\Mailable;

final class OrderStatusChangedEmail extends Mailable
{
    /** @param array<string, mixed> $order */
    public function __construct(
        private readonly array $order,
        private readonly string $status,
        private readonly string $note = '',
    ) {
    }

    public function build(): EmailMessage
    {
        $orderNumber = (string) ($this->order['order_number'] ?? ('#' . ($this->order['id'] ?? '')));
        $name = (string) ($this->order['billing_name'] ?? '');
        $statusLabel = $this->statusLabel();
        $plain = "Dobrý den {$name},\n\nstav objednávky {$orderNumber} byl změněn na: {$statusLabel}.\n";
        if ($this->note !== '') {
            $plain .= "\nPoznámka: {$this->note}\n";
        }

        return EmailMessage::make()
            ->subject("Objednávka {$orderNumber} – {$statusLabel}")
            ->template('order_status_changed', [
                'order' => $this->order,
                'orderNumber' => $orderNumber,
                'name' => $name,
                'status' => $this->status,
                'statusLabel' => $statusLabel,
                'note' => $this->note,
            ])
            ->text($plain);
    }

    private function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'zaplacena',
            'canceled' => 'stornována',
            'expired' => 'vypršela',
            'refunded' => 'refundována',
            default => $this->status,
        };
    }
}
