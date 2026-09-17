# Kali Framework

Lehký PHP základ pro weby a menší aplikace. Výchozí aplikace je malá: úvodní stránka, dokumentace na `/docs`, kontakt na `/contact`, ukázka routy s parametrem, nastavení cookies a jednoduché JSON API. Další funkce se přidávají po částech do rout, kontrolerů, šablon a služeb.

## Co je v základu

| Oblast | Připraveno | Kde začít |
| --- | --- | --- |
| Routování | GET, POST, PUT, DELETE, parametry v URL, skupiny rout a middleware | `src/routes/web.php`, `src/routes/api.php` |
| Stránky | Kontrolery, PHP šablony, společný layout, header, footer a stránka 404 | `src/controllers/Front/`, `src/views/` |
| Kontakt | Připravená stránka bez cizích nebo ukázkových údajů; hodnoty z `.env` | `src/config/site.php`, `src/views/pages/contact.php` |
| Požadavek | Query, formulářová i JSON data, soubory, hlavičky a parametry routy přes `Core\Request` | `src/classes/core/Request.php` |
| Překlady | Čeština bez prefixu, angličtina na `/en`, překlady s parametry a fallback | `src/i18n/config.php`, `src/i18n/{cs,en}/` |
| API | Oddělené routy pod `/api/`, ukázková JSON odpověď a JSON 404 | `src/Api/V1/Controllers/` |
| Cookies | Lišta, přijetí, odmítnutí, vlastní výběr, pozdější změna a kontrola souhlasu v PHP | `src/views/cookie/`, `src/helpers/cookie_helper.php` |
| Zabezpečení formulářů | Session a CSRF token s middleware `csrf` | `src/helpers/Csrf.php`, `src/middleware/CsrfMiddleware.php` |
| Toasty a validace | Čtyři typy oznámení, krátký formulář a chyby u polí | `/` → Živá ukázka, `src/controllers/Front/HomeController.php` |
| Konfigurace | Hodnoty z `.env` přístupné přes `config('app.name')` | `src/config/` |
| CSS | Tailwind utility ve šablonách a vlastní CSS, sestavení přes npm | `src/assets/css/framework.css`, `package.json` |

Po instalaci můžeš otevřít `/`, `/docs`, `/en/docs`, `/contact`, `/hello/Kali`, `/cookies` a `/api/v1/health`. Na úvodní stránce je živá ukázka toastů a validace. Dokumentace na `/docs` obsahuje menu a praktické ukázky kódu. Úvodní stránku a ukázkové routy v novém projektu nahradíš vlastním obsahem.

## Co lze rovnou zapojit do nového projektu

Tyto části jsou v repozitáři a nepotřebují starý voucherový web. Pro jejich použití doplníš nastavení a vlastní logiku projektu:

| Součást | Co poskytuje | Co doplníš |
| --- | --- | --- |
| Databáze | PDO připojení k MySQL/MariaDB, připravené dotazy a transakce | Přístupové údaje v `.env`, tabulky a modely |
| Validace | Pravidla jako `required`, `email`, `min`, `max`, `integer` nebo `in`; seznam chyb | Pravidla konkrétního formuláře |
| E-mail | SMTP přes PHPMailer, text/HTML, přílohy, kopie a výsledek odeslání | SMTP údaje, adresy a vlastní obsah zpráv |
| Flash zprávy | Hodnoty a dříve zadané údaje pro další požadavek | Zobrazení ve vlastní šabloně |
| Logování | Zápis informací a chyb do `storage/logs/` | Volání tam, kde je v projektu potřebuješ |
| Vlastní middleware | Rozšíření routeru o další kontroly rout | Vlastní třídu implementující `MiddlewareInterface` |

`Core\Database` má nastavení připravené při startu, ale připojení vytvoří až při prvním dotazu. Databáze ani SMTP nejsou potřeba pro výchozí stránku.

## Jak funguje jeden požadavek

1. Webový server předá URL do `public/index.php`. Veřejně dostupný má být jen adresář `public/`.
2. Načte se Composer autoload, `.env` a konfigurace z `src/config/`. Spustí se session a pomocné funkce.
3. Z URL se určí jazyk. Výchozí čeština nemá prefix, angličtina používá `/en`. API pod `/api/` se nepřekládá v URL.
4. Router načte `src/routes/web.php` a `src/routes/api.php`, najde routu a spustí její middleware.
5. Router zavolá metodu kontroleru. Může jí předat `Core\Request` a hodnoty z URL.
6. Webový kontroler vykreslí PHP šablonu v layoutu; API kontroler pošle JSON. Neznámá URL vrátí 404.

Základní vazba je: **URL → routa → kontroler → šablona nebo JSON**.

## Založení nového projektu

Požadavky: PHP 8.4+, Composer 2. Node.js, databáze a SMTP jsou pro výchozí aplikaci volitelné.

1. Zkopíruj repozitář do nového adresáře a spusť `composer install`.
2. Zkopíruj `.env.example` na `.env`. Nastav `APP_NAME`, `APP_URL_DEVELOPMENT` a `APP_URL_PRODUCTION`. Do každé URL zahrň případný podadresář aplikace. Veřejné kontaktní údaje vyplň pomocí `CONTACT_*` a `SOCIAL_*`.
3. Spusť lokální server:

   ```bash
   php -S localhost:8000 -t public public/router.php
   ```

4. Otevři `http://localhost:8000` a ověř také `/en`, `/cookies` a `/api/v1/health`.
5. Nahraď ukázkový obsah vlastním. Začni v `src/routes/web.php`, `src/controllers/Front/HomeController.php`, `src/views/home/index.php` a `src/assets/css/framework.css`.

### URL pro vývoj a produkci

V `.env` měj dvě úplné adresy. `APP_ENV` určuje, která se použije:

```dotenv
APP_ENV=development
APP_URL_DEVELOPMENT=http://localhost:8000
APP_URL_PRODUCTION=https://example.com
```

Pokud lokální web běží v podadresáři, zapiš ho přímo do `APP_URL_DEVELOPMENT`, například `http://localhost/my-project/public`. Při změně na `APP_ENV=production` začne framework používat `APP_URL_PRODUCTION`. `config('app.site_url')` vrací aktivní úplnou adresu a `config('app.base_url')` její cestu (například `/my-project/public`); tu už nenastavuješ zvlášť. V šablonách vytvářej interní odkazy pomocí `locale_url('contact')` a úplné odkazy, například do e-mailů, pomocí `locale_site_url('contact')`. Starší projekty s `APP_URL` a případně `APP_BASE_URL` fungují dál, pokud nové proměnné nejsou vyplněné.

Při nasazení nastav kořen webu na `public/`, přesměrování neexistujících souborů na `public/index.php`, `APP_ENV=production` a `APP_DEBUG=false`. Ověř správnou produkční URL v `APP_URL_PRODUCTION`. Soubor `.env` nepatří do veřejného adresáře ani do Gitu.

#### Apache ve sdíleném kořeni webu

Pokud je projekt uložený jako `kali-framework/`, ale návštěvník má otevírat `/kali-framework/` bez `/public/`, nastav veřejnou adresu právě takto:

```dotenv
APP_ENV=production
APP_URL_PRODUCTION=https://web.kalasekvyvoj.cz/kali-framework
```

Do `.htaccess` v kořeni domény vlož před obecný fallback tato pravidla. První pravidlo zabrání tomu, aby se při dalším průchodu Apache přidávalo `/public/public/`:

```apache
RewriteEngine On
RewriteRule ^kali-framework/public(?:/|$) - [L]
RewriteRule ^kali-framework/?$ kali-framework/public/ [L]
RewriteRule ^kali-framework/(.*)$ kali-framework/public/$1 [L]
```

V `kali-framework/public/.htaccess` použij pravidla z tohoto repozitáře. Nezadávej tam `RewriteBase /kali-framework/`: při interním přepisu do adresáře `public` by relativní `index.php` mířilo na nesprávnou cestu. Ověř `/kali-framework/`, `/kali-framework/docs` a `/kali-framework/assets/css/style.css`. Pokud server umožňuje nastavit kořen webu přímo na `kali-framework/public/`, pravidla v kořeni domény nepotřebuješ.

### Přehled adresářů

| Cesta | Účel |
| --- | --- |
| `public/index.php` | Vstupní bod aplikace |
| `public/assets/` | Veřejné CSS, JavaScript a obrázky |
| `src/routes/` | Registrace webových a API rout |
| `src/controllers/Front/` | Kontrolery webových stránek |
| `src/Api/V1/Controllers/` | Kontrolery API |
| `src/views/layouts/main.php` | Společný HTML layout |
| `src/views/pages/contact.php` | Konfigurovatelná kontaktní stránka |
| `src/views/docs/index.php` | Dokumentace s menu a ukázkami |
| `src/views/partials/` | Header a footer |
| `src/i18n/` | Konfigurace jazyků a překladové soubory |
| `src/config/` | Nastavení aplikace, databáze, e-mailu a cookies |
| `src/classes/core/` | Router, požadavek, kontroler, konfigurace, databáze |
| `src/helpers/` a `src/middleware/` | Pomocné funkce, validace, CSRF |
| `src/assets/css/framework.css` | Zdroj vlastních stylů a Tailwindu |
| `src/services/Mail/` | Obecné odesílání e-mailů |
| `storage/` | Logy, cache a soubory aplikace |
| `tests/` | Testy |

## Běžná práce s frameworkem

### Údaje webu a kontakt

`APP_NAME` určuje název webu. Veřejné kontakty nastav v `.env` jako `CONTACT_EMAIL`, `CONTACT_PHONE`, `CONTACT_ADDRESS`, `CONTACT_HOURS` a volitelné `SOCIAL_FACEBOOK`, `SOCIAL_INSTAGRAM`, `SOCIAL_LINKEDIN`, `SOCIAL_GITHUB`. Hodnoty čte `src/config/site.php` a v PHP jsou dostupné například jako `config('site.contact.email')`. Kontaktní stránka ukáže jen vyplněné hodnoty; prázdná pole nevytvářejí neplatné odkazy. E-mail pro odesílání (`MAIL_FROM_ADDRESS`) a příjem formulářů (`MAIL_TO_ADDRESS`) jsou samostatné údaje pro SMTP.

Stránka `/contact` je výchozí vizitka. Neodesílá formulář, dokud pro daný web nepřidáš jeho routu, validaci a doručení. Chybové stránky 404 a 500 používají společný vzhled; serverové chyby se logují a návštěvníkům se nezobrazuje technický detail.

### Přidání stránky

Do `src/routes/web.php` přidej routu:

```php
$router->get('about', 'Front\PageController@about');
```

Vytvoř `src/controllers/Front/PageController.php`:

```php
<?php

namespace Controllers\Front;

use Core\Controller;

final class PageController extends Controller
{
    public function about(): void
    {
        $this->view('pages/about', [
            'title' => __('about_title') . ' | ' . config('app.name'),
        ]);
    }
}
```

Vytvoř `src/views/pages/about.php`:

```php
<h1><?= htmlspecialchars(__('about_title'), ENT_QUOTES, 'UTF-8') ?></h1>
<p><?= htmlspecialchars(__('about_text'), ENT_QUOTES, 'UTF-8') ?></p>
```

Klíče `about_title` a `about_text` přidej do českého a anglického překladového souboru. Odkaz na stránku vytvoř přes `locale_url('about')`, aby zůstal správný jazyk i případný podadresář.

### Parametry v URL a požadavek

```php
$router->get('article/{slug}', 'Front\ArticleController@show');
```

Metoda `show(string $slug)` dostane hodnotu z URL. Pokud jako první argument uvedeš `Core\Request`, router vloží i aktuální požadavek: `show(Request $request, string $slug)`. Z něj čti například `$request->query('page')`, `$request->post('email')`, `$request->file('image')` nebo `$request->header('Accept')`. JSON tělo se u požadavku s `Content-Type: application/json` načte do vstupních dat.

### POST formulář a validace

Routu s formulářem chraň middleware:

```php
$router->post('contact/send', 'Front\MessageController@send')->middleware('csrf');
```

Do každého takového formuláře vlož token:

```php
<form action="<?= htmlspecialchars(locale_url('contact/send'), ENT_QUOTES, 'UTF-8') ?>" method="post">
    <?= csrf_field() ?>
    <input name="email" type="email" required>
    <button type="submit">Odeslat</button>
</form>
```

V kontroleru použij `Helpers\Validator`:

```php
use Helpers\Validator;

$validator = Validator::make($request->post(), [
    'email' => 'required|email',
]);

if ($validator->fails()) {
    $errors = $validator->errors();
    // Zobraz chyby ve své šabloně nebo je vrať jako JSON.
}
```

CSRF kontroluje token odeslaného formuláře. Validace samostatně ověřuje obsah polí; obě části mají v aplikaci jinou úlohu.
`MessageController` je příklad kontroleru, který vytvoříš pro vlastní projekt; výchozí kontaktní stránka formulář neodesílá.

### Toasty a živá ukázka

Na úvodní stránce otevři sekci **Živá ukázka**. Čtyři tlačítka vyvolají úspěch, informaci, upozornění a chybu. Formulář na `POST /demo/validate` používá skutečný `Helpers\Validator` a middleware `csrf`. Po odeslání se vrátí na úvod, zobrazí toast a při chybě také zprávu u pole. Jméno a e-mail slouží jen k ukázce; neodesílají se e-mailem ani neukládají do databáze.

Vlastní zprávu ze serveru přidej přes `Helpers\Toast::success('Uloženo')`, `::info()`, `::warning()` nebo `::error()`. Na následující stránce se vykreslí automaticky v layoutu. Pro událost v prohlížeči použij `window.toast.success('Uloženo', { title: 'Hotovo' })`; další typy mají stejné názvy. Vzhled je v `src/assets/css/framework.css`, skript v `public/assets/js/ui/toast.js`. Příklad formuláře je v `src/controllers/Front/HomeController.php` a `src/views/home/index.php`.

### API

API routy definuj v `src/routes/api.php` pod `api/`. Ukázka `/api/v1/health` je v `src/Api/V1/Controllers/HealthController.php`. Pro vlastní odpověď nastav `Content-Type: application/json`, odpovídající HTTP status a odešli data pomocí `json_encode()`. `Core\Request` umí číst také JSON vstup.

### Překlady a URL

`src/i18n/config.php` určuje výchozí a podporované jazyky. Překlady jsou PHP pole v `src/i18n/cs/` a `src/i18n/en/`:

```php
__('home_title');
__('welcome', ['name' => $name]);
__('cart', [], 'shop');
locale_url('about');
```

Třetí argument `__('cart', [], 'shop')` znamená soubory `cs/shop.php` a `en/shop.php`. Chybějící překlad se hledá ve výchozím jazyce; pokud není ani tam, zobrazí se klíč. Header obsahuje přepínač jazyků.

### Header, footer a cookies

`src/views/layouts/main.php` načítá `src/views/partials/header.php`, `footer.php` a cookie lištu. V headeru a footeru si pro nový web uprav navigaci, značku a odkazy. Patička vždy nabízí návrat do nastavení cookies.

Lišta nabízí přijmout, odmítnout a upravit. Stránka `/cookies` umožňuje volbu změnit kdykoli. Nastavení se ukládá na 180 dní; název a dobu mění `src/config/cookies.php`. Formuláře fungují bez JavaScriptu a jsou chráněné CSRF.

Výchozí projekt nespouští žádnou analytickou ani marketingovou integraci. Volitelný skript přidej jen po příslušném souhlasu:

```php
<?php if (cookie_allowed('analytics')): ?>
    <script src="<?= htmlspecialchars(locale_url('assets/js/analytics.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<?php endif; ?>
```

Pro marketing použij `cookie_allowed('marketing')`. Když přidáš externí službu, uprav texty souhlasu podle skutečně použitých cookies a vyřeš také odstranění jejích již uložených cookies po odvolání souhlasu.

### Databáze a e-mail

Databázové údaje patří do `.env`, jejich mapování je v `src/config/database.php`. Příklad připraveného dotazu:

```php
use Core\Database;

$statement = Database::getInstance()->execute(
    'SELECT id, title FROM posts WHERE id = :id',
    ['id' => $id],
);
$post = $statement->fetch();
```

Tabulku `posts` si musíš vytvořit v konkrétním projektu. Framework zatím nemá migrace ani ORM.

SMTP nastav přes hodnoty v `.env.example`. Jednoduchý textový e-mail:

```php
use Services\Mail\EmailMessage;
use Services\Mail\Mail;

$result = Mail::to('uzivatel@example.com')->send(
    EmailMessage::make()->subject('Zpráva')->text('Dobrý den.'),
);

if (!$result->successful()) {
    // Zpracuj neúspěšné odeslání.
}
```

Podrobnosti k HTML šablonám, přílohám a kopiím jsou v `src/services/Mail/README.md`. Staré e-mailové šablony obsahují texty původního projektu; pro nový web si napiš vlastní.

## Co v repozitáři zůstalo ze starého projektu

Soubory pro uživatele a administraci, vouchery, košík, objednávky, platby Comgate, AI chat, telemetrii, Google OAuth, reCAPTCHA a další specifické funkce jsou stále fyzicky přítomné. `public/index.php` načítá jen `web.php` a `api.php`; starý `admin.php` nenačítá. Tyto moduly tedy nejsou součástí výchozího běhu a jejich šablony či nastavení je před použitím nutné projít a přizpůsobit. Původní `src/views/contact/`, `src/views/gdpr/` a `src/views/terms/` obsahují údaje a texty předchozích projektů. Pro nový web používej `src/views/pages/contact.php` a právní stránky napiš podle skutečného projektu. Německé překladové soubory existují, ale jazyk `de` není zapnutý.

Závislosti těchto starých modulů jsou zatím také v `composer.json`. Repozitář ještě čeká na úplný úklid, takže pro nový projekt nekopíruj staré moduly jako hotové obecné funkce.

## Tailwind CSS a ověření

Tailwind je napojený přes `package.json`. Utility třídy můžeš psát přímo do PHP šablon v `src/views/`; Tailwind je při sestavení projde. Pro vlastní pravidla upravuj čitelný zdroj `src/assets/css/framework.css`. Výsledný soubor `public/assets/css/style.css` se generuje automaticky, takže ho neupravuj ručně.

```bash
npm install
npm run watch  # během úprav
npm run build  # finální CSS
```

Používej úplné názvy Tailwind tříd, například `bg-indigo-50`, protože sestavení hledá třídy v textu šablon. Nové soubory pod `src/views/` jsou zahrnuté automaticky. Starý soubor `src/assets/css/main.css` je pozůstatek původního projektu a aktuální build ho nepoužívá.

`composer test` spouští aktuální testy základu a `composer lint` kontroluje PHP syntaxi. Před nasazením ověř úvodní stránku, `/docs`, `/en/docs`, `/cookies`, neznámou URL a `/api/v1/health`.
