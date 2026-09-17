<header class="site-header">
    <div class="site-header-inner">
        <a class="brand" href="<?= htmlspecialchars(locale_url(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) config('app.name'), ENT_QUOTES, 'UTF-8') ?></a>
        <nav class="site-nav" aria-label="<?= htmlspecialchars(__('main_navigation'), ENT_QUOTES, 'UTF-8') ?>">
            <a href="<?= htmlspecialchars(locale_url(), ENT_QUOTES, 'UTF-8') ?>"<?= current_route_path() === '' ? ' aria-current="page"' : '' ?>><?= htmlspecialchars(__('home'), ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars(locale_url('docs'), ENT_QUOTES, 'UTF-8') ?>"<?= current_route_path() === 'docs' ? ' aria-current="page"' : '' ?>><?= htmlspecialchars(__('docs'), ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars(locale_url('contact'), ENT_QUOTES, 'UTF-8') ?>"<?= current_route_path() === 'contact' ? ' aria-current="page"' : '' ?>><?= htmlspecialchars(__('contact'), ENT_QUOTES, 'UTF-8') ?></a>
            <?php foreach (lang()->getSupportedLanguages() as $code): ?>
                <a href="<?= htmlspecialchars(locale_switch_url($code), ENT_QUOTES, 'UTF-8') ?>"<?= $code === lang()->getCurrentLanguage() ? ' aria-current="page"' : '' ?>><?= strtoupper($code) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
