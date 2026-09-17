<?php if (cookie_preferences() === null && current_route_path() !== 'cookies'): ?>
<aside class="cookie-banner" aria-labelledby="cookie-banner-title">
    <div class="cookie-banner-inner">
        <div>
            <h2 id="cookie-banner-title"><?= htmlspecialchars(__('cookie_banner_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars(__('cookie_description'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="cookie-actions">
            <form action="<?= htmlspecialchars(locale_url('cookies'), ENT_QUOTES, 'UTF-8') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="choice" value="none">
                <input type="hidden" name="return_path" value="<?= htmlspecialchars(current_route_path(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="return_language" value="<?= htmlspecialchars(lang()->getCurrentLanguage(), ENT_QUOTES, 'UTF-8') ?>">
                <button class="button button-secondary" type="submit"><?= htmlspecialchars(__('cookie_reject'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
            <a class="button button-secondary" href="<?= htmlspecialchars(locale_url('cookies'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('cookie_customize'), ENT_QUOTES, 'UTF-8') ?></a>
            <form action="<?= htmlspecialchars(locale_url('cookies'), ENT_QUOTES, 'UTF-8') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="choice" value="all">
                <input type="hidden" name="return_path" value="<?= htmlspecialchars(current_route_path(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="return_language" value="<?= htmlspecialchars(lang()->getCurrentLanguage(), ENT_QUOTES, 'UTF-8') ?>">
                <button class="button" type="submit"><?= htmlspecialchars(__('cookie_accept'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        </div>
    </div>
</aside>
<?php endif; ?>
