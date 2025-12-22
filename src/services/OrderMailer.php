<?php

namespace Services;

use PHPMailer\PHPMailer\PHPMailer;

class OrderMailer
{
    private PHPMailer $mail;

    public function __construct(PHPMailer $mailer)
    {
        $this->mail = $mailer;
    }

    public function sendPaid(array $order, string $pdfPath): bool
    {
        $to = (string)($order['billing_email'] ?? '');
        if ($to === '') return false;

        $orderNo = (string)($order['order_number'] ?? '');
        $name = (string)($order['billing_name'] ?? '');

        $m = $this->cloneMailer();
        $m->addAddress($to, $name ?: $to);
        $m->Subject = "Voucher objednávka {$orderNo} – zaplaceno";
        $m->Body = "Dobrý den,\n\nobjednávka {$orderNo} byla zaplacena. V příloze zasíláme voucher(y) v PDF.\n\nDěkujeme.\n";
        $m->addAttachment($pdfPath, basename($pdfPath));

        return $m->send();
    }

    public function sendStatusChange(array $order, string $status, string $note = ''): bool
    {
        $to = (string)($order['billing_email'] ?? '');
        if ($to === '') return false;

        $orderNo = (string)($order['order_number'] ?? '');
        $name = (string)($order['billing_name'] ?? '');

        $m = $this->cloneMailer();
        $m->addAddress($to, $name ?: $to);
        $m->Subject = "Objednávka {$orderNo} – stav: {$status}";
        $m->Body = "Dobrý den,\n\nstav vaší objednávky {$orderNo} byl změněn na: {$status}.\n"
            . ($note !== '' ? "\nPoznámka: {$note}\n" : "")
            . "\nPokud máte dotaz, odpovězte na tento e-mail.\n";

        return $m->send();
    }

    private function cloneMailer(): PHPMailer
    {
        // PHPMailer se blbě resetuje -> klon je nejbezpečnější
        $m = clone $this->mail;
        $m->clearAllRecipients();
        $m->clearAttachments();
        return $m;
    }
}
