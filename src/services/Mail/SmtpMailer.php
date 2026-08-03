<?php

namespace Services\Mail;

use Helpers\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

final class SmtpMailer
{
    public function __construct(
        private readonly TemplateRenderer $renderer = new TemplateRenderer(),
    ) {
    }

    /**
     * @param list<Address> $to
     * @param list<Address> $cc
     * @param list<Address> $bcc
     */
    public function send(EmailMessage $message, array $to, array $cc = [], array $bcc = []): SendResult
    {
        try {
            $message->validate();
            if ($to === []) {
                throw new \InvalidArgumentException('Email must have at least one recipient.');
            }

            $mailer = $this->createClient();
            foreach ($to as $address) {
                $mailer->addAddress($address->email, $address->name);
            }
            foreach ($cc as $address) {
                $mailer->addCC($address->email, $address->name);
            }
            foreach ($bcc as $address) {
                $mailer->addBCC($address->email, $address->name);
            }

            if ($message->getReplyTo() !== null) {
                $replyTo = $message->getReplyTo();
                $mailer->addReplyTo($replyTo->email, $replyTo->name);
            }

            foreach ($message->getAttachments() as $attachment) {
                $mailer->addAttachment($attachment->path, $attachment->name ?? basename($attachment->path));
            }

            $html = $message->getHtml();
            if ($message->getTemplate() !== null) {
                $html = $this->renderer->render(
                    $message->getTemplate(),
                    $message->getData(),
                    $message->getSubject(),
                );
            }

            $mailer->Subject = $message->getSubject();
            if ($html !== null) {
                $mailer->isHTML(true);
                $mailer->Body = $html;
                $mailer->AltBody = $message->getText() ?? trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            } else {
                $mailer->isHTML(false);
                $mailer->Body = (string) $message->getText();
            }

            $mailer->send();
            Logger::logEmail(true, $this->logContext($message, $to, $cc, $bcc));
            return SendResult::sent();
        } catch (Throwable $exception) {
            Logger::logEmail(false, array_merge(
                $this->logContext($message, $to, $cc, $bcc),
                ['error' => $exception->getMessage()],
            ));
            Logger::exception($exception, ['service' => self::class]);
            return SendResult::failed($exception->getMessage());
        }
    }

    private function createClient(): PHPMailer
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = (string) SMTP_HOST;
        $mailer->SMTPAuth = true;
        $mailer->Username = (string) SMTP_USERNAME;
        $mailer->Password = (string) SMTP_PASSWORD;
        $mailer->Port = (int) SMTP_PORT;
        $mailer->SMTPSecure = $mailer->Port === 465
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Timeout = 15;
        $mailer->CharSet = 'UTF-8';
        $mailer->Encoding = 'base64';
        $mailer->setFrom((string) MAIL_FROM_ADDRESS, (string) MAIL_FROM_NAME);
        return $mailer;
    }

    /**
     * @param list<Address> $to
     * @param list<Address> $cc
     * @param list<Address> $bcc
     */
    private function logContext(EmailMessage $message, array $to, array $cc, array $bcc): array
    {
        return [
            'to' => array_map(static fn (Address $address) => $address->email, $to),
            'cc' => array_map(static fn (Address $address) => $address->email, $cc),
            'bcc' => array_map(static fn (Address $address) => $address->email, $bcc),
            'subject' => $message->getSubject(),
            'attachments' => array_map(
                static fn (Attachment $attachment) => ['path' => $attachment->path, 'name' => $attachment->name],
                $message->getAttachments(),
            ),
        ];
    }
}
