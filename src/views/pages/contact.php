<?php
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$email = (string) config('site.contact.email', '');
$email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
$phone = (string) config('site.contact.phone', '');
$phoneHref = preg_replace('/[^+0-9]/', '', $phone) ?? '';
$address = (string) config('site.contact.address', '');
$hours = (string) config('site.contact.hours', '');

$socialLinks = [];
foreach (['facebook', 'instagram', 'linkedin', 'github'] as $network) {
    $url = (string) config('site.social.' . $network, '');
    if (filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true)) {
        $socialLinks[$network] = $url;
    }
}
$hasContactDetails = $email !== '' || $phoneHref !== '' || $address !== '' || $hours !== '';
$hasDetails = $hasContactDetails || $socialLinks !== [];
?>
<div class="contact-page">
    <section class="contact-hero" aria-labelledby="contact-title">
        <p class="contact-eyebrow"><?= $escape(__('contact_eyebrow')) ?></p>
        <h1 id="contact-title"><?= $escape(__('contact_page_title')) ?></h1>
        <p><?= $escape(__('contact_page_description')) ?></p>
        <?php if ($email !== ''): ?>
            <a class="button contact-primary" href="mailto:<?= $escape($email) ?>"><?= $escape(__('contact_write_email')) ?> <span aria-hidden="true">↗</span></a>
        <?php endif; ?>
    </section>

    <?php if ($hasDetails): ?>
        <?php if ($hasContactDetails): ?>
        <section class="contact-details" aria-label="<?= $escape(__('contact_details_title')) ?>">
            <?php if ($email !== ''): ?>
                <div class="contact-card">
                    <span class="contact-card-mark" aria-hidden="true">@</span>
                    <h2><?= $escape(__('contact_email')) ?></h2>
                    <a href="mailto:<?= $escape($email) ?>"><?= $escape($email) ?></a>
                </div>
            <?php endif; ?>
            <?php if ($phoneHref !== ''): ?>
                <div class="contact-card">
                    <span class="contact-card-mark" aria-hidden="true">☎</span>
                    <h2><?= $escape(__('contact_phone')) ?></h2>
                    <a href="tel:<?= $escape($phoneHref) ?>"><?= $escape($phone) ?></a>
                </div>
            <?php endif; ?>
            <?php if ($address !== ''): ?>
                <div class="contact-card">
                    <span class="contact-card-mark" aria-hidden="true">⌖</span>
                    <h2><?= $escape(__('contact_address')) ?></h2>
                    <p><?= $escape($address) ?></p>
                </div>
            <?php endif; ?>
            <?php if ($hours !== ''): ?>
                <div class="contact-card">
                    <span class="contact-card-mark" aria-hidden="true">◷</span>
                    <h2><?= $escape(__('contact_hours')) ?></h2>
                    <p><?= $escape($hours) ?></p>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
        <?php if ($socialLinks !== []): ?>
            <section class="contact-social" aria-label="<?= $escape(__('contact_social')) ?>">
                <h2><?= $escape(__('contact_social')) ?></h2>
                <div>
                    <?php foreach ($socialLinks as $network => $url): ?>
                        <a href="<?= $escape($url) ?>" target="_blank" rel="noopener noreferrer"><?= $escape(ucfirst($network)) ?> <span aria-hidden="true">↗</span></a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php else: ?>
        <div class="contact-empty">
            <span aria-hidden="true">✳</span>
            <p><?= $escape(__('contact_empty')) ?></p>
            <a href="<?= $escape(locale_url()) ?>"><?= $escape(__('back_home')) ?> <span aria-hidden="true">→</span></a>
        </div>
    <?php endif; ?>
</div>
