<section class="cookie-settings">
    <p class="cookie-settings-eyebrow"><?= htmlspecialchars(__('cookie_settings_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
    <h1><?= htmlspecialchars(__('cookie_settings_title'), ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars(__('cookie_description'), ENT_QUOTES, 'UTF-8') ?></p>
    <form action="<?= htmlspecialchars(locale_url('cookies'), ENT_QUOTES, 'UTF-8') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="choice" value="selected">
        <input type="hidden" name="return_path" value="cookies">
        <input type="hidden" name="return_language" value="<?= htmlspecialchars(lang()->getCurrentLanguage(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="cookie-option">
            <div>
                <strong><?= htmlspecialchars(__('cookie_necessary'), ENT_QUOTES, 'UTF-8') ?></strong>
                <p><?= htmlspecialchars(__('cookie_necessary_description'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <span><?= htmlspecialchars(__('cookie_always_on'), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <label class="cookie-option">
            <span>
                <strong><?= htmlspecialchars(__('cookie_analytics'), ENT_QUOTES, 'UTF-8') ?></strong>
                <span class="option-description"><?= htmlspecialchars(__('cookie_analytics_description'), ENT_QUOTES, 'UTF-8') ?></span>
            </span>
            <input type="checkbox" name="analytics" value="1"<?= !empty($preferences['analytics']) ? ' checked' : '' ?>>
        </label>
        <label class="cookie-option">
            <span>
                <strong><?= htmlspecialchars(__('cookie_marketing'), ENT_QUOTES, 'UTF-8') ?></strong>
                <span class="option-description"><?= htmlspecialchars(__('cookie_marketing_description'), ENT_QUOTES, 'UTF-8') ?></span>
            </span>
            <input type="checkbox" name="marketing" value="1"<?= !empty($preferences['marketing']) ? ' checked' : '' ?>>
        </label>
        <div class="cookie-actions">
            <button class="button" type="submit"><?= htmlspecialchars(__('cookie_save'), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
    </form>
</section>
