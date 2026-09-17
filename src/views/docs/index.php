<?php
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$sections = [
    ['id' => 'intro'],
    ['id' => 'start', 'code' => <<<'CODE'
composer install
cp .env.example .env
# Set APP_NAME and both environment URLs in .env
php -S localhost:8000 -t public public/router.php
CODE],
    ['id' => 'site', 'path' => '.env', 'code' => <<<'CODE'
APP_NAME="My website"
APP_ENV=development
APP_URL_DEVELOPMENT=http://localhost:8000
APP_URL_PRODUCTION=https://example.com
CONTACT_EMAIL=hello@example.com
CONTACT_PHONE="+420 123 456 789"
CONTACT_ADDRESS="Main Street 1, Prague"
CONTACT_HOURS="Mon–Fri 9:00–17:00"
SOCIAL_INSTAGRAM=https://instagram.com/your-profile
CODE],
    ['id' => 'structure', 'code' => <<<'CODE'
public/               # web root and compiled assets
src/routes/            # web.php and api.php
src/controllers/        # page controllers
src/views/              # layout, partials and pages
src/i18n/               # cs and en translations
src/config/             # app, database, mail, cookies
src/services/           # optional project services
storage/                # logs and application files
CODE],
    ['id' => 'routes', 'path' => 'src/routes/web.php', 'code' => <<<'CODE'
$router->get('about', 'Front\PageController@about');
$router->get('article/{slug}', 'Front\ArticleController@show');
$router->post('contact/send', 'Front\MessageController@send')
    ->middleware('csrf');

// In a view:
<a href="<?= htmlspecialchars(locale_url('about'), ENT_QUOTES, 'UTF-8') ?>">About</a>
CODE],
    ['id' => 'pages', 'path' => 'src/controllers/Front/PageController.php', 'code' => <<<'CODE'
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

// src/views/pages/about.php:
// <h1><?= htmlspecialchars(__('about_title'), ENT_QUOTES, 'UTF-8') ?></h1>
CODE],
    ['id' => 'i18n', 'path' => 'src/i18n/cs/general.php', 'code' => <<<'CODE'
// src/i18n/cs/general.php
return ['about_title' => 'O nás'];

// src/i18n/en/general.php
return ['about_title' => 'About us'];

__('about_title');
__('welcome', ['name' => $name]);
__('cart', [], 'shop'); // reads cs/shop.php or en/shop.php
CODE],
    ['id' => 'feedback', 'code' => <<<'CODE'
use Helpers\Toast;
use Helpers\Validator;

$validator = Validator::make($request->post(), [
    'name' => 'required|min:2',
    'email' => 'required|email',
]);

if ($validator->fails()) {
    $validator->flash('demo', $request->post());
} else {
    Toast::success('Form is valid', 'Done');
}

// For an event in JavaScript:
window.toast.info('An informational message', { title: 'Info' });
CODE],
    ['id' => 'forms', 'code' => <<<'CODE'
$router->post('contact/send', 'Front\MessageController@send')
    ->middleware('csrf');

// In the form: <?= csrf_field() ?>

use Helpers\Validator;
$validator = Validator::make($request->post(), [
    'email' => 'required|email',
]);
if ($validator->fails()) {
    $errors = $validator->errors();
}
CODE],
    ['id' => 'api', 'path' => 'src/routes/api.php', 'code' => <<<'CODE'
$router->get('api/v1/health',
    'Api\V1\Controllers\HealthController@index');

// In a controller action:
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
CODE],
    ['id' => 'cookies', 'path' => 'src/helpers/cookie_helper.php', 'code' => <<<'CODE'
<?php if (cookie_allowed('analytics')): ?>
    <script src="/assets/js/analytics.js" defer></script>
<?php endif; ?>

// Also available: cookie_allowed('marketing')
CODE],
    ['id' => 'tailwind', 'code' => <<<'CODE'
npm install
npm run watch   # rebuild while editing
npm run build   # production stylesheet

<!-- In any PHP view under src/views/ -->
<div class="rounded-xl bg-indigo-50 p-6 text-indigo-900">
    Your content
</div>
CODE],
    ['id' => 'services', 'code' => <<<'CODE'
use Core\Database;
use Services\Mail\EmailMessage;
use Services\Mail\Mail;

$statement = Database::getInstance()->execute(
    'SELECT id, title FROM posts WHERE id = :id',
    ['id' => $id],
);
$post = $statement->fetch();

// Add tables and models for your project, then configure SMTP:
$result = Mail::to('user@example.com')->send(
    EmailMessage::make()->subject('Hello')->text('Your message'),
);
if (!$result->successful()) {
    // Handle delivery failure.
}
CODE],
    ['id' => 'extend', 'code' => <<<'CODE'
1. Add a route in src/routes/web.php or api.php.
2. Add a controller in src/controllers/ or src/Api/.
3. Add a PHP view in src/views/ if the route returns HTML.
4. Add translation keys in src/i18n/cs and src/i18n/en.
5. Style it with Tailwind classes or framework.css.
6. Extract shared logic into src/services/ when needed.
CODE],
    ['id' => 'deploy', 'code' => <<<'CODE'
composer test
composer lint
npm run build

# Web root: public/
# APP_ENV=production
# APP_DEBUG=false
CODE],
];
?>
<div class="docs-page">
    <div class="docs-hero">
        <p class="docs-eyebrow text-sm font-semibold"><?= $escape(__('eyebrow', [], 'docs')) ?></p>
        <h1><?= $escape(__('page_title', [], 'docs')) ?></h1>
        <p><?= $escape(__('lead', [], 'docs')) ?></p>
    </div>

    <div class="docs-layout">
        <aside class="docs-sidebar">
            <nav aria-label="<?= $escape(__('menu', [], 'docs')) ?>">
                <p class="docs-sidebar-title"><?= $escape(__('menu', [], 'docs')) ?></p>
                <?php foreach ($sections as $section): ?>
                    <a href="#<?= $escape($section['id']) ?>"><?= $escape(__($section['id'] . '_title', [], 'docs')) ?></a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <article class="docs-content">
            <?php foreach ($sections as $index => $section): ?>
                <section class="docs-section" id="<?= $escape($section['id']) ?>" aria-labelledby="docs-<?= $escape($section['id']) ?>-title">
                    <div class="docs-section-heading">
                        <span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <h2 id="docs-<?= $escape($section['id']) ?>-title"><?= $escape(__($section['id'] . '_title', [], 'docs')) ?></h2>
                    </div>
                    <p><?= $escape(__($section['id'] . '_text', [], 'docs')) ?></p>
                    <?php if (isset($section['code'])): ?>
                        <div class="docs-code">
                            <div class="docs-code-label">
                                <span><?= $escape(__('code_label', [], 'docs')) ?></span>
                                <?php if (isset($section['path'])): ?><span><?= $escape(__('path_label', [], 'docs')) ?>: <?= $escape($section['path']) ?></span><?php endif; ?>
                            </div>
                            <pre><code><?= $escape($section['code']) ?></code></pre>
                        </div>
                    <?php endif; ?>
                    <?php if ($section['id'] === 'extend'): ?>
                        <p class="docs-note"><?= $escape(__('legacy_note', [], 'docs')) ?></p>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </article>
    </div>
</div>
