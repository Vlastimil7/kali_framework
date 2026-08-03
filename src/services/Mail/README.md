# Mail service

Celý framework odesílá emaily přes tuto vrstvu. PHPMailer je zapouzdřený pouze
v `SmtpMailer`; controllery ani doménové služby jej přímo nepoužívají.

## Použití existujícího emailu

```php
use Services\Mail\Mail;
use Services\Mail\Mailables\PasswordResetEmail;

$email = new PasswordResetEmail($userName, $resetUrl);
$result = Mail::to($userEmail, $userName)->send($email);

if (!$result->successful()) {
    error_log($result->message);
}
```

Aktuální opakovaně používané emaily:

- `PasswordResetEmail`
- `ContactEmail`
- `LeadEmail`
- `OrderPaidEmail`
- `OrderStatusChangedEmail`
- `WelcomeEmail` jako jednoduchý příklad

## Jednorázový email

```php
use Services\Mail\EmailMessage;
use Services\Mail\Mail;

$message = EmailMessage::make()
    ->subject('Předmět')
    ->template('notice', ['name' => 'Anna'])
    ->text('Textová alternativa emailu.')
    ->replyTo('support@example.com', 'Podpora')
    ->attach($pdfPath, 'dokument.pdf');

$result = Mail::to('anna@example.com', 'Anna')
    ->cc('copy@example.com')
    ->bcc('audit@example.com')
    ->send($message);
```

## Přidání emailu

1. Přidejte `src/views/emails/nazev.php`.
2. Přidejte třídu do `Services\Mail\Mailables`, která dědí z `Mailable`.
3. V metodě `build()` sestavte `EmailMessage`.
4. Odešlete ji přes `Mail::to(...)->send(new VasEmail(...))`.

```php
final class InvoiceEmail extends Mailable
{
    public function __construct(private array $invoice) {}

    public function build(): EmailMessage
    {
        return EmailMessage::make()
            ->subject('Faktura ' . $this->invoice['number'])
            ->template('invoice', ['invoice' => $this->invoice])
            ->text('Fakturu najdete v příloze.')
            ->attach($this->invoice['pdf']);
    }
}
```

Data z `template()` jsou v PHP šabloně dostupná jako proměnné. Vždy je
vypisujte přes `$e()`:

```php
<h1>Dobrý den, <?= $e($name) ?></h1>
```

Společný vzhled je v `src/views/emails/layout.php`. Název
`template('orders/invoice')` odpovídá souboru
`src/views/emails/orders/invoice.php`.

## `.env`

```dotenv
SMTP_HOST=smtp.example.com
SMTP_USERNAME=user
SMTP_PASSWORD=secret
SMTP_PORT=587
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Example App"
MAIL_TO_ADDRESS=contact@example.com
MAIL_TO_NAME="Contact inbox"
```

`MAIL_TO_ADDRESS` je interní příjemce kontaktních a lead formulářů. Port 465
používá SMTPS, ostatní porty STARTTLS.
