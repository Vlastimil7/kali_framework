<?php

namespace Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Helpers\Logger;

class Mailer
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // SMTP
        $this->mail->isSMTP();
        $this->mail->Host       = SMTP_HOST;
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = SMTP_USERNAME;
        $this->mail->Password   = SMTP_PASSWORD;

        $port = (int) SMTP_PORT;
        $this->mail->Port = $port;

        // Secure podle portu
        $this->mail->SMTPSecure = ($port === 465)
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;


        // Odesílatel
        $this->mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

        // Kódování
        $this->mail->CharSet  = 'UTF-8';
        $this->mail->Encoding = 'base64';
    }


    /**
     * Odeslání emailu pro reset hesla
     */
    public function sendPasswordReset($email, $token, $userName = '')
    {
        try {
            // Vyčistit předchozí nastavení
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            // Nastavení příjemce
            $this->mail->addAddress($email);

            // Nastavení formátu emailu (HTML)
            $this->mail->isHTML(true);

            // Předmět
            $this->mail->Subject = 'Reset hesla na Midobarbershop.cz';

            // Vytvoření odkazu pro reset
            $resetLink = BASE_URL . '/password/reset/' . $token;

            // HTML obsah
            $message = $this->getPasswordResetTemplate($resetLink, $userName);
            $this->mail->Body = $message;

            // Alternativní text pro email klienty, které nepodporují HTML
            $this->mail->AltBody = "Dobrý den,\n\n"
                . "obdrželi jsme žádost o reset hesla pro váš účet.\n"
                . "Pro reset hesla klikněte na následující odkaz:\n\n"
                . $resetLink . "\n\n"
                . "Pokud jste o reset hesla nežádali, tento email můžete ignorovat.\n\n"
                . "S pozdravem,\nTým Midobarbershop.cz";

            // Odeslání emailu
            $this->mail->send();
            return [
                'success' => true,
                'message' => 'Email byl úspěšně odeslán'
            ];
        } catch (Exception $e) {
            Logger::error('Chyba při odesílání emailu: ' . $e->getMessage());;

            return [
                'success' => false,
                'message' => 'Nepodařilo se odeslat email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Odeslání kontaktního emailu
     */
    public function sendContactMessage(array $formData): array
    {
        // komu se má mail poslat
        $to = match ($formData['clinic'] ?? '') {
            'Midobarbershop.cz s.r.o.'    => 'Midobarbershop.cz@post.cz',
            'Midobarbershop.cz AZ s.r.o.' => 'Midobarbershop.czAZ@post.cz',
            default           => 'Midobarbershop.cz@post.cz',
        };

        $subject = '[Kontaktní formulář] '
            . ($formData['subject'] ?? 'Kontakt')
            . ' – '
            . ($formData['clinic'] ?? '');

        try {
            $this->mail->clearAddresses();
            $this->mail->clearReplyTos();
            $this->mail->clearAttachments();

            //$this->mail->addAddress($to);
            $this->mail->addAddress('kalasekvyvoj@gmail.com'); // pro testování
            $this->mail->addReplyTo(
                $formData['email'],
                trim(($formData['firstName'] ?? '') . ' ' . ($formData['lastName'] ?? ''))
            );

            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $this->getContactMessageTemplate($formData);
            $this->mail->AltBody = $this->getContactMessagePlainText($formData);

            $this->mail->send();

            // ✅ LOG ÚSPĚCHU
            Logger::logEmail(true, [
                'to'      => $to,
                'subject' => $subject,
                'body'    => null,
                'meta'    => [
                    'clinic' => $formData['clinic'] ?? null,
                    'from'   => $formData['email'] ?? null,
                    'phone'  => $formData['fullPhone'] ?? null,
                ],
            ]);

            return [
                'success' => true,
                'message' => 'Kontaktní zpráva byla úspěšně odeslána',
            ];
        } catch (Exception $e) {

            // ❌ LOG CHYBY
            Logger::logEmail(false, [
                'to'      => $to,
                'subject' => $subject,
                'error'   => $e->getMessage(),
                'meta'    => [
                    'clinic' => $formData['clinic'] ?? null,
                    'from'   => $formData['email'] ?? null,
                    'phone'  => $formData['fullPhone'] ?? null,
                ],
            ]);

            Logger::error('PHPMailer sendContactMessage failed', [
                'to'       => $to ?? null,
                'from'     => MAIL_FROM_ADDRESS ?? null,
                'reply_to' => $formData['email'] ?? null,
                'subject'  => $this->mail->Subject ?? null,
                'error'    => $e->getMessage(),
                'errorInfo' => $this->mail->ErrorInfo ?? null,
                'host'     => $this->mail->Host ?? null,
                'port'     => $this->mail->Port ?? null,
                'secure'   => $this->mail->SMTPSecure ?? null,
            ]);

            return [
                'success' => false,
                'message' => 'Nepodařilo se odeslat kontaktní zprávu',
            ];
        }
    }


    /**
     * HTML šablona pro email reset hesla
     */
    private function getPasswordResetTemplate($resetLink, $userName)
    {
        $name = !empty($userName) ? $userName : 'uživateli';

        return '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reset hesla</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #3182ce;
                    color: white;
                    padding: 20px;
                    text-align: center;
                }
                .content {
                    padding: 20px;
                    background-color: #f9f9f9;
                }
                .button {
                    display: inline-block;
                    background-color: #3182ce;
                    color: white;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Reset hesla</h1>
                </div>
                <div class="content">
                    <p>Dobrý den, ' . $name . ',</p>
                    <p>obdrželi jsme žádost o reset hesla pro váš účet na webu Midobarbershop.cz.</p>
                    <p>Pro reset hesla klikněte na následující tlačítko:</p>
                    
                    <div style="text-align: center;">
                        <a href="' . $resetLink . '" class="button">Resetovat heslo</a>
                    </div>
                    
                    <p>Pokud tlačítko nefunguje, zkopírujte a vložte následující odkaz do svého prohlížeče:</p>
                    <p><a href="' . $resetLink . '">' . $resetLink . '</a></p>
                    
                    <p>Platnost tohoto odkazu je 1 hodina.</p>
                    
                    <p>Pokud jste o reset hesla nežádali, tento email můžete ignorovat.</p>
                    
                    <p>S pozdravem,<br>Tým Midobarbershop.cz</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' Midobarbershop.cz - Všechna práva vyhrazena</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * HTML šablona pro kontaktní zprávu
     */
    private function getContactMessageTemplate($formData)
    {
        return '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Nová zpráva z kontaktního formuláře</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #432dd7;
                    color: white;
                    padding: 20px;
                    text-align: center;
                }
                .content {
                    padding: 20px;
                    background-color: #f9f9f9;
                }
                .info-row {
                    margin-bottom: 15px;
                    padding: 10px;
                    background-color: white;
                    border-left: 4px solid #432dd7;
                }
                .label {
                    font-weight: bold;
                    color: #333;
                }
                .message-content {
                    background-color: white;
                    padding: 20px;
                    border-radius: 5px;
                    margin-top: 20px;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    padding: 20px;
                    background-color: #f0f0f0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header" style="text-align: center; padding: 20px; background-color: white; color: black;">
                  <img src="https://web.kalasekvyvoj.cz/ziad_Midobarbershop.cz/public/assets/images/home/loga/Midobarbershop.cz_logo.png" alt="Midobarbershop.cz s.r.o. a Midobarbershop.czAZ s.r.o." style="max-width: 250px; height: auto; display: block; margin: 0 auto 10px;">
                   <h1 style="color: white; margin-top: 0; font-size: 24px;">Nová zpráva z webu</h1>
                 </div>
                <div class="content">
                    <h2>Informace o odesílateli:</h2>
                    
                    <div class="info-row">
                        <span class="label">Jméno:</span> ' . htmlspecialchars($formData['firstName']) . '
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Příjmení:</span> ' . htmlspecialchars($formData['lastName']) . '
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Email:</span> <a href="mailto:' . htmlspecialchars($formData['email']) . '">' . htmlspecialchars($formData['email']) . '</a>
                    </div>
                    
                    ' . (!empty($formData['phone']) ? '<div class="info-row"><span class="label">Telefon:</span> <a href="tel:' . htmlspecialchars($formData['fullPhone']) . '">' . htmlspecialchars($formData['fullPhone']) . '</a></div>' : '') . '
                    
                    <div class="info-row">
                        <span class="label">Předmět:</span> ' . htmlspecialchars($formData['subject']) . '
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Datum odeslání:</span> ' . date('d.m.Y H:i:s') . '
                    </div>
                    
                    <div class="message-content">
                        <h3 style="margin-top: 0; color: #432dd7;">💬 Zpráva:</h3>
                        <p style="white-space: pre-wrap; margin: 0;">' . htmlspecialchars($formData['message']) . '</p>
                    </div>
                </div>
                <div class="footer">
                    <p><strong>💡 Tip:</strong> Pro odpověď můžete použít tlačítko "Odpovědět" - email se automaticky odešle na adresu odesílatele.</p>
                    <p>© ' . date('Y') . ' Midobarbershop.cz - Automatická zpráva z kontaktního formuláře</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Prostý text pro kontaktní zprávu
     */
    private function getContactMessagePlainText($formData)
    {
        $text = "NOVÁ ZPRÁVA Z KONTAKTNÍHO FORMULÁŘE\n";
        $text .= "=====================================\n\n";
        $text .= "Jméno: " . $formData['firstName'] . "\n";
        $text .= "Příjmení: " . $formData['lastName'] . "\n";
        $text .= "Email: " . $formData['email'] . "\n";

        if (!empty($formData['phone'])) {
            $text .= "Telefon: " . $formData['phone'] . "\n";
        }

        $text .= "Předmět: " . $formData['subject'] . "\n";
        $text .= "Datum: " . date('d.m.Y H:i:s') . "\n\n";
        $text .= "ZPRÁVA:\n";
        $text .= "-------\n";
        $text .= $formData['message'] . "\n\n";
        $text .= "© " . date('Y') . " Midobarbershop.cz";

        return $text;
    }

    public function sendOrderPaidWithVouchers(array $order, string $pdfPath, array $meta = []): array
    {
        try {
            $email = trim((string)($order['billing_email'] ?? ''));
            if ($email === '') {
                return ['success' => false, 'message' => 'Objednávka nemá billing_email.'];
            }

            $orderNo = (string)($order['order_number'] ?? ('#' . ($order['id'] ?? '')));
            $name    = (string)($order['billing_name'] ?? '');

            $this->mail->clearAddresses();
            $this->mail->clearReplyTos();
            $this->mail->clearAttachments();

            $this->mail->addAddress($email, $name ?: $email);
            $this->mail->isHTML(true);

            $this->mail->Subject = "Vaše objednávka {$orderNo} – voucher(y) v příloze";

            $this->mail->Body = $this->getOrderPaidTemplate($order, $meta);
            $this->mail->AltBody = $this->getOrderPaidPlainText($order, $meta);

            if (is_file($pdfPath)) {
                $this->mail->addAttachment($pdfPath, basename($pdfPath));
            }

            $this->mail->send();

            return ['success' => true, 'message' => 'Email s vouchery odeslán.'];
        } catch (Exception $e) {
            Logger::error('PHPMailer sendOrderPaidWithVouchers failed', [
                'order_id' => $order['id'] ?? null,
                'order_no' => $order['order_number'] ?? null,
                'to'       => $order['billing_email'] ?? null,
                'error'    => $e->getMessage(),
                'errorInfo' => $this->mail->ErrorInfo ?? null,
            ]);

            return ['success' => false, 'message' => 'Nepodařilo se odeslat email: ' . $e->getMessage()];
        }
    }

    public function sendOrderStatusChanged(array $order, string $newStatus, string $note = ''): array
    {
        try {
            $email = trim((string)($order['billing_email'] ?? ''));
            if ($email === '') {
                return ['success' => false, 'message' => 'Objednávka nemá billing_email.'];
            }

            $orderNo = (string)($order['order_number'] ?? ('#' . ($order['id'] ?? '')));
            $name    = (string)($order['billing_name'] ?? '');

            $this->mail->clearAddresses();
            $this->mail->clearReplyTos();
            $this->mail->clearAttachments();

            $this->mail->addAddress($email, $name ?: $email);
            $this->mail->isHTML(true);

            $this->mail->Subject = "Stav objednávky {$orderNo}: {$newStatus}";
            $this->mail->Body    = $this->getOrderStatusTemplate($order, $newStatus, $note);
            $this->mail->AltBody = $this->getOrderStatusPlainText($order, $newStatus, $note);

            $this->mail->send();

            return ['success' => true, 'message' => 'Email o změně stavu odeslán.'];
        } catch (Exception $e) {
            Logger::error('PHPMailer sendOrderStatusChanged failed', [
                'order_id' => $order['id'] ?? null,
                'order_no' => $order['order_number'] ?? null,
                'to'       => $order['billing_email'] ?? null,
                'status'   => $newStatus,
                'error'    => $e->getMessage(),
                'errorInfo' => $this->mail->ErrorInfo ?? null,
            ]);

            return ['success' => false, 'message' => 'Nepodařilo se odeslat email: ' . $e->getMessage()];
        }
    }

    private function getOrderPaidTemplate(array $order, array $meta = []): string
    {
        $orderNo = htmlspecialchars((string)($order['order_number'] ?? ''));
        $name = htmlspecialchars((string)($order['billing_name'] ?? ''));
        return "
      <div style='font-family:Arial,sans-serif;line-height:1.5'>
        <h2>Děkujeme za objednávku {$orderNo}</h2>
        <p>Dobrý den {$name},</p>
        <p>Vaše objednávka byla <b>zaplacena</b>. V příloze posíláme voucher(y) v PDF.</p>
        <p>Hezký den,<br>Midobarbershop.cz</p>
      </div>
    ";
    }

    private function getOrderPaidPlainText(array $order, array $meta = []): string
    {
        $orderNo = (string)($order['order_number'] ?? '');
        $name = (string)($order['billing_name'] ?? '');
        return "Dobrý den {$name},\n\nObjednávka {$orderNo} byla zaplacena. V příloze posíláme voucher(y) v PDF.\n\nMidobarbershop.cz\n";
    }

    private function getOrderStatusTemplate(array $order, string $newStatus, string $note = ''): string
    {
        $orderNo = htmlspecialchars((string)($order['order_number'] ?? ''));
        $name = htmlspecialchars((string)($order['billing_name'] ?? ''));
        $st = htmlspecialchars($newStatus);
        $noteHtml = $note !== '' ? "<p><b>Poznámka:</b> " . nl2br(htmlspecialchars($note)) . "</p>" : "";
        return "
      <div style='font-family:Arial,sans-serif;line-height:1.5'>
        <h2>Objednávka {$orderNo}</h2>
        <p>Dobrý den {$name},</p>
        <p>Stav objednávky byl změněn na: <b>{$st}</b>.</p>
        {$noteHtml}
        <p>Midobarbershop.cz</p>
      </div>
    ";
    }

    private function getOrderStatusPlainText(array $order, string $newStatus, string $note = ''): string
    {
        $orderNo = (string)($order['order_number'] ?? '');
        $name = (string)($order['billing_name'] ?? '');
        $txt = "Dobrý den {$name},\n\nStav objednávky {$orderNo} byl změněn na: {$newStatus}.\n";
        if ($note !== '') $txt .= "\nPoznámka: {$note}\n";
        $txt .= "\nMidobarbershop.cz\n";
        return $txt;
    }

    public function sendOrderPaidWithVoucherAttachments(array $order, array $pdfPaths): array
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            $to = (string)($order['billing_email'] ?? '');
            if ($to === '') {
                return ['success' => false, 'message' => 'Objednávka nemá billing_email.'];
            }

            $this->mail->addAddress($to, (string)($order['billing_name'] ?? ''));

            $orderNo = (string)($order['order_number'] ?? '');
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Vouchery k objednávce ' . $orderNo;

            $this->mail->Body = '<p>Dobrý den,</p><p>v příloze posíláme vaše vouchery k objednávce <b>'
                . htmlspecialchars($orderNo) . '</b>.</p><p>Děkujeme,<br>Midobarbershop</p>';

            $this->mail->AltBody = "Dobrý den,\n\nV příloze posíláme vaše vouchery k objednávce {$orderNo}.\n\nMidobarbershop";

            foreach ($pdfPaths as $path) {
                if ($path && is_file($path)) {
                    $this->mail->addAttachment($path);
                }
            }

            $this->mail->send();
            return ['success' => true];
        } catch (\Throwable $e) {
            Logger::error('sendOrderPaidWithVoucherAttachments failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
