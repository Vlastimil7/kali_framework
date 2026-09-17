<footer class="site-footer">
    <div class="site-footer-inner">
        <span>&copy; <?= date('Y') ?> <?= htmlspecialchars((string) config('app.name'), ENT_QUOTES, 'UTF-8') ?></span>
        <nav class="site-footer-nav" aria-label="<?= htmlspecialchars(__('footer_navigation'), ENT_QUOTES, 'UTF-8') ?>">
            <a href="<?= htmlspecialchars(locale_url('docs'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('docs'), ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars(locale_url('contact'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('contact'), ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars(locale_url('cookies'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('cookie_settings_title'), ENT_QUOTES, 'UTF-8') ?></a>
        </nav>
    </div>
</footer>
