<?php
$isServerError = ($errorCode ?? 404) >= 500;
$heading = $isServerError ? __('error_server_title') : __('error_not_found_title');
$message = $isServerError ? __('error_server_description') : __('error_not_found_description');
?>
<section class="error-page" aria-labelledby="error-title">
    <div class="error-visual" aria-hidden="true">
        <span class="error-orbit error-orbit-one"></span>
        <span class="error-orbit error-orbit-two"></span>
        <span class="error-number"><?= $isServerError ? '500' : '404' ?></span>
        <span class="error-spark error-spark-one">✳</span>
        <span class="error-spark error-spark-two">✦</span>
    </div>
    <div class="error-copy">
        <p class="error-eyebrow"><?= htmlspecialchars((string) config('app.name'), ENT_QUOTES, 'UTF-8') ?></p>
        <h1 id="error-title"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <div class="error-actions">
            <a class="button error-primary" href="<?= htmlspecialchars(locale_url(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('back_home'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">→</span></a>
            <a class="error-secondary" href="<?= htmlspecialchars(locale_url($isServerError ? 'contact' : 'docs'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__($isServerError ? 'contact' : 'docs'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">↗</span></a>
        </div>
    </div>
</section>
