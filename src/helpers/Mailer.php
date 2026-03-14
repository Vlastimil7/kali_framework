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
        $this->mail->SMTPOptions = [
            'socket' => [
                'bindto' => '0.0.0.0:0', // ⛔ zakáže IPv6
            ],
        ];

        $this->mail->Timeout = 15;
        $this->mail->Hostname = 'vk-dev.cz';
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
            $this->mail->Subject = 'Reset hesla na VK-DEV.cz';

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
                . "S pozdravem,\nTým VK-DEV.cz";

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

    public function sendContactMessage(array $formData, array $attachments = []): array
    {

        $to = WEB_MAIL ?? 'kalasekvyvoj@gmail.com'; // dej do configu

        $subject = '[Poptávka] '
            . ($formData['topic'] ?? 'Kontakt')
            . (!empty($formData['budget']) ? ' / ' . $formData['budget'] : '');

        try {
            $this->mail->clearAddresses();
            $this->mail->clearReplyTos();
            $this->mail->clearAttachments();

            // $this->mail->addAddress($to);
            $this->mail->addAddress('kalasekvyvoj@gmail.com'); // test

            $this->mail->addReplyTo(
                $formData['email'],
                trim(($formData['firstName'] ?? '') . ' ' . ($formData['lastName'] ?? ''))
            );

            // ✅ přílohy
            foreach ($attachments as $a) {
                $path = $a['path'] ?? null;
                if ($path && is_file($path)) {
                    $name = $a['name'] ?? basename($path);
                    $this->mail->addAttachment($path, $name);
                }
            }

            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $this->getContactMessageTemplate($formData);
            $this->mail->AltBody = $this->getContactMessagePlainText($formData);

            $this->mail->send();

            Logger::logEmail(true, [
                'to'      => $to,
                'subject' => $subject,
                'meta'    => [
                    'from'  => $formData['email'] ?? null,
                    'phone' => $formData['fullPhone'] ?? null,
                    'topic' => $formData['topic'] ?? null,
                    'budget' => $formData['budget'] ?? null,
                    'attachments_count' => count($attachments),
                ],
            ]);

            return [
                'success' => true,
                'message' => 'Kontaktní zpráva byla úspěšně odeslána',
            ];
        } catch (Exception $e) {

            Logger::logEmail(false, [
                'to'      => $to,
                'subject' => $subject,
                'error'   => $e->getMessage(),
                'meta'    => [
                    'from'  => $formData['email'] ?? null,
                    'phone' => $formData['fullPhone'] ?? null,
                    'topic' => $formData['topic'] ?? null,
                    'budget' => $formData['budget'] ?? null,
                    'attachments_count' => count($attachments),
                ],
            ]);

            Logger::error('PHPMailer sendContactMessage failed', [
                'to'        => $to ?? null,
                'from'      => MAIL_FROM_ADDRESS ?? null,
                'reply_to'  => $formData['email'] ?? null,
                'subject'   => $this->mail->Subject ?? null,
                'error'     => $e->getMessage(),
                'errorInfo' => $this->mail->ErrorInfo ?? null,
                'host'      => $this->mail->Host ?? null,
                'port'      => $this->mail->Port ?? null,
                'secure'    => $this->mail->SMTPSecure ?? null,
            ]);

            return [
                'success' => false,
                'message' => 'Nepodařilo se odeslat kontaktní zprávu',
            ];
        }
    }

    /**
     * Odeslání kontaktního emailu
     */

    public function sendLeadMessage(array $formData): array
    {

        $to = WEB_MAIL ?? 'kalasekvyvoj@gmail.com'; // dej do configu

        $subject = '[Poptávka] '
            . ($formData['email_lead'] ?? 'Kontakt');

        try {
            $this->mail->clearAddresses();
            $this->mail->clearReplyTos();
            $this->mail->clearAttachments();

            // $this->mail->addAddress($to);
            $this->mail->addAddress($to); // test


            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $this->getLeadMessageTemplate($formData);
            $this->mail->AltBody = $this->getLeadMessagePlainText($formData);

            $this->mail->send();

            Logger::logEmail(true, [
                'to'      => $to,
                'subject' => $subject,
                'meta'    => [
                    'from'  => $formData['email_lead'] ?? null,
                ],
            ]);

            return [
                'success' => true,
                'message' => 'Poptávka byla úspěšně odeslána',
            ];
        } catch (Exception $e) {

            Logger::logEmail(false, [
                'to'      => $to,
                'subject' => $subject,
                'error'   => $e->getMessage(),
                'meta'    => [
                    'from'  => $formData['email_lead'] ?? null,
                ],
            ]);

            Logger::error('PHPMailer sendContactMessage failed', [
                'to'        => $to ?? null,
                'from'      => MAIL_FROM_ADDRESS ?? null,
                'reply_to'  => $formData['email_lead'] ?? null,
                'subject'   => $this->mail->Subject ?? null,
                'error'     => $e->getMessage(),
                'errorInfo' => $this->mail->ErrorInfo ?? null,
                'host'      => $this->mail->Host ?? null,
                'port'      => $this->mail->Port ?? null,
                'secure'    => $this->mail->SMTPSecure ?? null,
            ]);

            return [
                'success' => false,
                'message' => 'Nepodařilo se odeslat poptávku',
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
                    <p>obdrželi jsme žádost o reset hesla pro váš účet na webu VK-DEV.cz.</p>
                    <p>Pro reset hesla klikněte na následující tlačítko:</p>
                    
                    <div style="text-align: center;">
                        <a href="' . $resetLink . '" class="button">Resetovat heslo</a>
                    </div>
                    
                    <p>Pokud tlačítko nefunguje, zkopírujte a vložte následující odkaz do svého prohlížeče:</p>
                    <p><a href="' . $resetLink . '">' . $resetLink . '</a></p>
                    
                    <p>Platnost tohoto odkazu je 1 hodina.</p>
                    
                    <p>Pokud jste o reset hesla nežádali, tento email můžete ignorovat.</p>
                    
                    <p>S pozdravem,<br>Tým VK-DEV.cz</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' VK-DEV.cz - Všechna práva vyhrazena</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * HTML šablona pro kontaktní zprávu (VK-DEV styl)
     */
    private function getContactMessageTemplate(array $formData): string
    {
        $first = htmlspecialchars((string)($formData['firstName'] ?? ''));
        $last  = htmlspecialchars((string)($formData['lastName'] ?? ''));
        $email = htmlspecialchars((string)($formData['email'] ?? ''));
        $phone = htmlspecialchars((string)($formData['phone'] ?? ''));
        $fullPhone = htmlspecialchars((string)($formData['fullPhone'] ?? ''));
        $topic = htmlspecialchars((string)($formData['topic'] ?? ''));
        $budget = htmlspecialchars((string)($formData['budget'] ?? ''));
        $message = htmlspecialchars((string)($formData['message'] ?? ''));

        $ip = htmlspecialchars((string)($formData['ip_address'] ?? ''));
        $ua = htmlspecialchars((string)($formData['user_agent'] ?? ''));

        $sentAt = date('d.m.Y H:i:s');

        // Přílohy (volitelné) – očekávám array stringů (názvy) nebo array položek s name
        $attachmentsHtml = '';
        $attachments = $formData['attachments'] ?? [];
        if (is_array($attachments) && count($attachments) > 0) {
            $items = [];
            foreach ($attachments as $a) {
                if (is_array($a)) {
                    $n = $a['name'] ?? $a['filename'] ?? '';
                } else {
                    $n = (string)$a;
                }
                $n = trim($n);
                if ($n !== '') {
                    $items[] = '<li style="margin:6px 0; color:#e5e7eb;">' . htmlspecialchars($n) . '</li>';
                }
            }

            if (count($items) > 0) {
                $attachmentsHtml = '
              <div style="margin-top:16px;">
                <div style="font-weight:700; color:#e5e7eb; margin-bottom:8px;">Přílohy:</div>
                <ul style="margin:0; padding-left:18px;">
                  ' . implode('', $items) . '
                </ul>
              </div>
            ';
            }
        }

        $phoneRow = '';
        if ($phone !== '') {
            $telHref = $fullPhone !== '' ? $fullPhone : preg_replace('/[^\d+]/', '', $phone);
            $telHref = htmlspecialchars((string)$telHref);

            $phoneRow = '
          <div style="margin-top:10px; padding:14px 14px; background:#0b0f18; border:1px solid rgba(148,163,184,.25); border-radius:14px;">
            <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">Telefon</div>
            <div style="font-size:15px; color:#e5e7eb;">
              <a href="tel:' . $telHref . '" style="color:#a78bfa; text-decoration:none;">' . $phone . '</a>
            </div>
          </div>
        ';
        }

        $topicRow = '';
        if ($topic !== '') {
            $topicRow = '
          <div style="margin-top:10px; padding:14px 14px; background:#0b0f18; border:1px solid rgba(148,163,184,.25); border-radius:14px;">
            <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">Typ projektu</div>
            <div style="font-size:15px; color:#e5e7eb;">' . $topic . '</div>
          </div>
        ';
        }

        $budgetRow = '';
        if ($budget !== '') {
            $budgetRow = '
          <div style="margin-top:10px; padding:14px 14px; background:#0b0f18; border:1px solid rgba(148,163,184,.25); border-radius:14px;">
            <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">Rozpočet</div>
            <div style="font-size:15px; color:#e5e7eb;">' . $budget . '</div>
          </div>
        ';
        }

        $metaRow = '';
        if ($ip !== '' || $ua !== '') {
            $metaRow = '
          <div style="margin-top:16px; padding:14px 14px; background:#0b0f18; border:1px dashed rgba(148,163,184,.25); border-radius:14px;">
            <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">Meta</div>
            ' . ($ip !== '' ? '<div style="font-size:13px; color:#cbd5e1; margin-bottom:6px;"><b style="color:#94a3b8;">IP:</b> ' . $ip . '</div>' : '') . '
            ' . ($ua !== '' ? '<div style="font-size:13px; color:#cbd5e1;"><b style="color:#94a3b8;">User-Agent:</b> ' . $ua . '</div>' : '') . '
          </div>
        ';
        }

        return '<!DOCTYPE html>
                <html>
                <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Nová poptávka</title>
                </head>
                <body style="margin:0; padding:0; background:#050814;">
                <div style="display:none; font-size:1px; color:#050814; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
                    Nová poptávka z webu VK-DEV.cz
                </div>

                <div style="max-width:680px; margin:0 auto; padding:28px 14px;">
                    <!-- Card -->
                    <div style="background:linear-gradient(180deg, rgba(167,139,250,.08), rgba(6,182,212,.06)); border:1px solid rgba(148,163,184,.18); border-radius:22px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,.35);">

                    <!-- Header -->
                    <div style="padding:22px 22px 14px 22px; background:rgba(2,6,23,.55); border-bottom:1px solid rgba(148,163,184,.15);">
                        <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                        <div style="flex:1 1 auto;">
                            <div style="font-size:12px; letter-spacing:.12em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">
                            Nová poptávka z webu
                            </div>
                            <div style="font-size:20px; font-weight:800; color:#e5e7eb; line-height:1.2;">
                            VK-DEV.cz — Kontaktní formulář
                            </div>
                        </div>
                        <div style="flex:0 0 auto;">
                            <img src="https://vk-dev.cz/assets/images/logo/vk-dev.png"
                                alt="VK-DEV.cz"
                                style="max-width:200px; height:auto; display:block; filter:drop-shadow(0 10px 20px rgba(0,0,0,.35));">
                        </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div style="padding:22px; background:rgba(2,6,23,.35);">
                        <!-- Accent line -->
                        <div style="height:4px; background:linear-gradient(90deg, #a78bfa, #06b6d4); border-radius:999px; margin-bottom:18px;"></div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px;">
                        <div style="font-size:12px; color:#94a3b8;">Odesláno:</div>
                        <div style="font-size:12px; color:#e5e7eb; font-weight:700;">' . $sentAt . '</div>
                        </div>

                        <!-- Sender cards -->
                        <div style="display:grid; grid-template-columns:1fr; gap:10px;">
                        <div style="padding:14px 14px; background:#0b0f18; border:1px solid rgba(148,163,184,.25); border-radius:14px;">
                            <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">Jméno a příjmení</div>
                            <div style="font-size:15px; color:#e5e7eb; font-weight:700;">' . $first . ' ' . $last . '</div>
                        </div>

                        <div style="margin-top:10px; padding:14px 14px; background:#0b0f18; border:1px solid rgba(148,163,184,.25); border-radius:14px;">
                            <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">Email</div>
                            <div style="font-size:15px; color:#e5e7eb;">
                            <a href="mailto:' . $email . '" style="color:#a78bfa; text-decoration:none;">' . $email . '</a>
                            </div>
                        </div>

                        ' . $phoneRow . '
                        ' . $topicRow . '
                        ' . $budgetRow . '
                        </div>

                        ' . $attachmentsHtml . '

                        <!-- Message -->
                        <div style="margin-top:18px; padding:18px; background:rgba(11,15,24,.9); border:1px solid rgba(148,163,184,.22); border-radius:16px;">
                        <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:10px;">
                            Zpráva
                        </div>
                        <div style="font-size:15px; color:#e5e7eb; white-space:pre-wrap; line-height:1.7;">' . $message . '</div>
                        </div>

                        ' . $metaRow . '

                        <!-- CTA -->
                        <div style="margin-top:18px; text-align:center;">
                        <a href="mailto:' . $email . '"
                            style="display:inline-block; padding:12px 18px; border-radius:12px; font-weight:800; color:#0b0f18; text-decoration:none;
                                    background:linear-gradient(90deg, #a78bfa, #06b6d4); box-shadow:0 12px 30px rgba(167,139,250,.25);">
                            Odpovědět na poptávku
                        </a>
                        <div style="margin-top:10px; font-size:12px; color:#94a3b8;">
                            Tip: stačí dát „Odpovědět“ v emailu — Reply-To je nastavené na odesílatele.
                        </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="padding:16px 22px; background:rgba(2,6,23,.55); border-top:1px solid rgba(148,163,184,.15);">
                        <div style="font-size:12px; color:#94a3b8; text-align:center;">
                        © ' . date('Y') . ' VK-DEV.cz • Automatická zpráva z kontaktního formuláře
                        </div>
                    </div>

                    </div>
                </div>
                </body>
                </html>';
    }

    /**
     * HTML šablona pro Lead zprávu
     */

    private function getLeadMessageTemplate(array $formData): string
    {
        $email = htmlspecialchars((string)($formData['email_lead'] ?? ''));

        $ip = htmlspecialchars((string)($formData['ip_address'] ?? ''));
        $ua = htmlspecialchars((string)($formData['user_agent'] ?? ''));

        $sentAt = date('d.m.Y H:i:s');

        $metaRow = '';
        if ($ip !== '' || $ua !== '') {
            $metaRow = '
          <div style="margin-top:16px; padding:14px 14px; background:#0b0f18; border:1px dashed rgba(148,163,184,.25); border-radius:14px;">
            <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">Meta</div>
            ' . ($ip !== '' ? '<div style="font-size:13px; color:#cbd5e1; margin-bottom:6px;"><b style="color:#94a3b8;">IP:</b> ' . $ip . '</div>' : '') . '
            ' . ($ua !== '' ? '<div style="font-size:13px; color:#cbd5e1;"><b style="color:#94a3b8;">User-Agent:</b> ' . $ua . '</div>' : '') . '
          </div>
        ';
        }

        return '<!DOCTYPE html>
                <html>
                <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Nová poptávka</title>
                </head>
                <body style="margin:0; padding:0; background:#050814;">
                <div style="display:none; font-size:1px; color:#050814; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
                    Nová poptávka z webu VK-DEV.cz
                </div>

                <div style="max-width:680px; margin:0 auto; padding:28px 14px;">
                    <!-- Card -->
                    <div style="background:linear-gradient(180deg, rgba(167,139,250,.08), rgba(6,182,212,.06)); border:1px solid rgba(148,163,184,.18); border-radius:22px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,.35);">         
                    <!-- Header -->
                    <div style="padding:22px 22px 14px 22px;
                        background:rgba(2,6,23,.55); border-bottom:1px solid rgba(148,163,184,.15);">
                        <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                        <div style="flex:1 1 auto;">
                            <div style="font-size:12px; letter-spacing:.12em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">
                            Nová poptávka z webu
                            </div>
                            <div style="font-size:20px; font-weight:800; color:#e5e7eb; line-height:1.2;">
                            VK-DEV.cz — Lead formulář
                            </div>
                        </div>
                        <div style="flex:0 0 auto;">
                            <img src="https://vk-dev.cz/assets/images/logo/vk-dev.png"
                                alt="VK-DEV.cz"
                                style="max-width:200px; height:auto; display:block; filter:drop-shadow(0 10px 20px rgba(0,0,0,.35));">
                        </div>
                        </div>
                    </div>
                    <!-- Content -->
                    <div style="padding:22px; background:rgba(2,6,23,.35);">
                        <!-- Accent line -->
                        <div style="height:4px; background:linear-gradient(90deg, #a78bfa, #06b6d4); border-radius:999px; margin-bottom:18px;"></div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px;">
                        <div style="font-size:12px; color:#94a3b8;">Odesláno:</div>
                        <div style="font-size:12px; color:#e5e7eb; font-weight:700;">' . $sentAt . '</div>
                        </div>

                        <!-- Sender cards -->
                        <div style="display:grid; grid-template-columns:1fr; gap:10px;">
                        <div style="margin-top:10px; padding:14px 14px; background:#0b0f18; border:1px solid rgba(148,163,184,.25); border-radius:14px;">
                            <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px;">Email</div>
                            <div style="font-size:15px; color:#e5e7eb;">
                            <a href="mailto:' . $email . '" style="color:#a78bfa; text-decoration:none;">' . $email . '</a>
                            </div>
                        </div>
                        </div>

                        ' . $metaRow . '

                        <!-- Footer -->
                        <div style="padding:16px 22px; background:rgba(2,6,23,.55); border-top:1px solid rgba(148,163,184,.15);">
                            <div style="font-size:12px; color:#94a3b8; text-align:center;">
                            © ' . date('Y') . ' VK-DEV.cz • Automatická zpráva z lead formuláře
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                </body>
                </html>';
    }

    /**
     * Prostý text pro kontaktní zprávu
     */
    private function getContactMessagePlainText(array $formData): string
    {
        $lines = [];
        $lines[] = "NOVÁ POPTÁVKA Z KONTAKTNÍHO FORMULÁŘE (VK-DEV.cz)";
        $lines[] = "================================================";
        $lines[] = "Datum: " . date('d.m.Y H:i:s');
        $lines[] = "";
        $lines[] = "Jméno: " . trim(($formData['firstName'] ?? '') . ' ' . ($formData['lastName'] ?? ''));
        $lines[] = "Email: " . ($formData['email'] ?? '');
        if (!empty($formData['phone'])) {
            $lines[] = "Telefon: " . ($formData['phone'] ?? '');
        }
        if (!empty($formData['topic'])) {
            $lines[] = "Typ projektu: " . ($formData['topic'] ?? '');
        }
        if (!empty($formData['budget'])) {
            $lines[] = "Rozpočet: " . ($formData['budget'] ?? '');
        }

        // Přílohy (volitelné)
        $attachments = $formData['attachments'] ?? [];
        if (is_array($attachments) && count($attachments) > 0) {
            $names = [];
            foreach ($attachments as $a) {
                if (is_array($a)) {
                    $n = $a['name'] ?? $a['filename'] ?? '';
                } else {
                    $n = (string)$a;
                }
                $n = trim($n);
                if ($n !== '') $names[] = $n;
            }
            if (count($names) > 0) {
                $lines[] = "Přílohy: " . implode(', ', $names);
            }
        }

        $lines[] = "";
        $lines[] = "ZPRÁVA:";
        $lines[] = "------";
        $lines[] = (string)($formData['message'] ?? '');
        $lines[] = "";

        // Meta (volitelné)
        if (!empty($formData['ip_address']) || !empty($formData['user_agent'])) {
            $lines[] = "META:";
            if (!empty($formData['ip_address'])) $lines[] = "IP: " . $formData['ip_address'];
            if (!empty($formData['user_agent'])) $lines[] = "User-Agent: " . $formData['user_agent'];
            $lines[] = "";
        }

        $lines[] = "© " . date('Y') . " VK-DEV.cz";

        return implode("\n", $lines);
    }

    /**
     * Prostý text pro Lead zprávu
     * 
     */

    private function getLeadMessagePlainText(array $formData): string
    {
        $lines = [];
        $lines[] = "NOVÁ POPTÁVKA Z LEAD FORMULÁŘE (VK-DEV.cz)";
        $lines[] = "================================================";
        $lines[] = "Datum: " . date('d.m.Y H:i:s');
        $lines[] = "";
        $lines[] = "Email: " . ($formData['email_lead'] ?? '');
        $lines[] = "";

        // Meta (volitelné)
        if (!empty($formData['ip_address']) || !empty($formData['user_agent'])) {
            $lines[] = "META:";
            if (!empty($formData['ip_address'])) $lines[] = "IP: " . $formData['ip_address'];
            if (!empty($formData['user_agent'])) $lines[] = "User-Agent: " . $formData['user_agent'];
            $lines[] = "";
        }

        $lines[] = "© " . date('Y') . " VK-DEV.cz";

        return implode("\n", $lines);
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
        <p>Hezký den,<br>VK-DEV.cz</p>
      </div>
    ";
    }

    private function getOrderPaidPlainText(array $order, array $meta = []): string
    {
        $orderNo = (string)($order['order_number'] ?? '');
        $name = (string)($order['billing_name'] ?? '');
        return "Dobrý den {$name},\n\nObjednávka {$orderNo} byla zaplacena. V příloze posíláme voucher(y) v PDF.\n\nVK-DEV.cz\n";
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
        <p>VK-DEV.cz</p>
      </div>
    ";
    }

    private function getOrderStatusPlainText(array $order, string $newStatus, string $note = ''): string
    {
        $orderNo = (string)($order['order_number'] ?? '');
        $name = (string)($order['billing_name'] ?? '');
        $txt = "Dobrý den {$name},\n\nStav objednávky {$orderNo} byl změněn na: {$newStatus}.\n";
        if ($note !== '') $txt .= "\nPoznámka: {$note}\n";
        $txt .= "\nVK-DEV.cz\n";
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
