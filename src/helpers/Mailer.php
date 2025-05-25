<?php

namespace Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private $mail;
    
    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        
        // Nastavení SMTP serveru
        $this->mail->isSMTP();
        $this->mail->Host = SMTP_HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = SMTP_USERNAME;
        $this->mail->Password = SMTP_PASSWORD;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // nebo PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = SMTP_PORT;
        
        // Nastavení odesílatele
        $this->mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        
        // Nastavení kódování
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
    }
    
    /**
     * Odeslání emailu pro reset hesla
     */
    public function sendPasswordReset($email, $token, $userName = '')
    {
        try {
            // Nastavení příjemce
            $this->mail->addAddress($email);
            
            // Nastavení formátu emailu (HTML)
            $this->mail->isHTML(true);
            
            // Předmět
            $this->mail->Subject = 'Reset hesla na Counter.cz';
            
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
                . "S pozdravem,\nTým Counter.cz";
            
            // Odeslání emailu
            $this->mail->send();
            return [
                'success' => true,
                'message' => 'Email byl úspěšně odeslán'
            ];
        } catch (Exception $e) {
            error_log('Chyba při odesílání emailu: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Nepodařilo se odeslat email: ' . $e->getMessage()
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
                    <p>obdrželi jsme žádost o reset hesla pro váš účet na webu Counter.cz.</p>
                    <p>Pro reset hesla klikněte na následující tlačítko:</p>
                    
                    <div style="text-align: center;">
                        <a href="' . $resetLink . '" class="button">Resetovat heslo</a>
                    </div>
                    
                    <p>Pokud tlačítko nefunguje, zkopírujte a vložte následující odkaz do svého prohlížeče:</p>
                    <p><a href="' . $resetLink . '">' . $resetLink . '</a></p>
                    
                    <p>Platnost tohoto odkazu je 1 hodina.</p>
                    
                    <p>Pokud jste o reset hesla nežádali, tento email můžete ignorovat.</p>
                    
                    <p>S pozdravem,<br>Tým Counter.cz</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' Counter.cz - Všechna práva vyhrazena</p>
                </div>
            </div>
        </body>
        </html>';
    }
}