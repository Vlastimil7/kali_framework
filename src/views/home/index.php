<div class="home-page">
    <section class="home-hero" aria-labelledby="home-title">
        <div class="home-hero-copy">
            <p class="home-eyebrow"><span class="status-dot" aria-hidden="true"></span><?= htmlspecialchars(__('home_eyebrow'), ENT_QUOTES, 'UTF-8') ?></p>
            <h1 id="home-title">
                <?= htmlspecialchars(isset($name) ? __('welcome', ['name' => $name]) : __('home_headline'), ENT_QUOTES, 'UTF-8') ?>
                <span><?= htmlspecialchars(__('home_headline_accent'), ENT_QUOTES, 'UTF-8') ?></span>
            </h1>
            <p class="home-lead"><?= htmlspecialchars(__('home_intro'), ENT_QUOTES, 'UTF-8') ?></p>
            <div class="home-actions">
                <a class="button home-primary" href="#demo"><?= htmlspecialchars(__('home_explore'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">→</span></a>
                <a class="home-secondary" href="<?= htmlspecialchars(locale_url('docs'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('docs'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">↗</span></a>
            </div>
            <div class="home-tags" role="group" aria-label="<?= htmlspecialchars(__('home_tags_label'), ENT_QUOTES, 'UTF-8') ?>">
                <span>PHP 8.4+</span><span>CS / EN</span><span>API</span><span>Cookies</span>
            </div>
        </div>
        <div class="home-flow" role="group" aria-label="<?= htmlspecialchars(__('home_flow_label'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="home-flow-top"><span class="window-dots" aria-hidden="true"><i></i><i></i><i></i></span><span><?= htmlspecialchars((string) config('app.name'), ENT_QUOTES, 'UTF-8') ?></span></div>
            <div class="home-flow-body">
                <p class="flow-caption"><?= htmlspecialchars(__('home_flow_caption'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="flow-row"><span class="flow-number">01</span><span><?= htmlspecialchars(__('home_flow_request'), ENT_QUOTES, 'UTF-8') ?></span><code>GET /</code></div>
                <div class="flow-row"><span class="flow-number">02</span><span><?= htmlspecialchars(__('home_flow_route'), ENT_QUOTES, 'UTF-8') ?></span><code>web.php</code></div>
                <div class="flow-row"><span class="flow-number">03</span><span><?= htmlspecialchars(__('home_flow_controller'), ENT_QUOTES, 'UTF-8') ?></span><code>Controller</code></div>
                <div class="flow-row"><span class="flow-number">04</span><span><?= htmlspecialchars(__('home_flow_result'), ENT_QUOTES, 'UTF-8') ?></span><code>HTML / JSON</code></div>
                <p class="flow-note"><?= htmlspecialchars(__('home_flow_note'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </section>

    <section class="home-section" id="features" aria-labelledby="features-title">
        <div class="section-heading">
            <div><p class="section-kicker"><?= htmlspecialchars(__('home_features_kicker'), ENT_QUOTES, 'UTF-8') ?></p><h2 id="features-title"><?= htmlspecialchars(__('home_features_title'), ENT_QUOTES, 'UTF-8') ?></h2></div>
            <p><?= htmlspecialchars(__('home_features_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="feature-grid">
            <?php foreach (['routes', 'views', 'languages', 'api', 'cookies', 'services'] as $feature): ?>
                <article class="feature-card">
                    <span class="feature-mark" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($feature, 0, 2)), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars(__("home_feature_{$feature}_title"), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars(__("home_feature_{$feature}_text"), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-section interactive-demo" id="demo" aria-labelledby="demo-title">
        <div class="section-heading">
            <div><p class="section-kicker"><?= htmlspecialchars(__('demo_kicker'), ENT_QUOTES, 'UTF-8') ?></p><h2 id="demo-title"><?= htmlspecialchars(__('demo_title'), ENT_QUOTES, 'UTF-8') ?></h2></div>
            <p><?= htmlspecialchars(__('demo_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="interactive-demo-grid">
            <div class="interactive-demo-panel">
                <span class="interactive-demo-icon" aria-hidden="true">✦</span>
                <h3><?= htmlspecialchars(__('demo_toasts_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars(__('demo_toasts_text'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="demo-toast-buttons">
                    <?php foreach (['success', 'info', 'warning', 'error'] as $type): ?>
                        <button type="button" class="demo-toast-button demo-toast-button--<?= $type ?>" data-toast-demo data-toast-type="<?= $type ?>" data-toast-title="<?= htmlspecialchars(__("demo_{$type}_title"), ENT_QUOTES, 'UTF-8') ?>" data-toast-message="<?= htmlspecialchars(__("demo_{$type}_message"), ENT_QUOTES, 'UTF-8') ?>">
                            <span aria-hidden="true"><?= ['success' => '✓', 'info' => 'i', 'warning' => '!', 'error' => '×'][$type] ?></span>
                            <?= htmlspecialchars(__("demo_{$type}_button"), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="interactive-demo-panel demo-form-panel">
                <span class="interactive-demo-icon" aria-hidden="true">{ }</span>
                <h3><?= htmlspecialchars(__('demo_form_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars(__('demo_form_text'), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (($demoNotice ?? '') !== ''): ?>
                    <p class="demo-form-notice" role="status"><?= htmlspecialchars((string) $demoNotice, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <form class="demo-form" action="<?= htmlspecialchars(locale_url('demo/validate'), ENT_QUOTES, 'UTF-8') ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div>
                        <label for="demo-name"><?= htmlspecialchars(__('demo_name_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="demo-name" name="name" type="text" autocomplete="name" maxlength="60" value="<?= htmlspecialchars((string) ($demoOld['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars(__('demo_name_placeholder'), ENT_QUOTES, 'UTF-8') ?>"<?= !empty($demoErrors['name']) ? ' aria-invalid="true" aria-describedby="demo-name-error"' : '' ?>>
                        <?php if (!empty($demoErrors['name'])): ?><small id="demo-name-error" class="demo-field-error"><?= htmlspecialchars((string) $demoErrors['name'][0], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                    </div>
                    <div>
                        <label for="demo-email"><?= htmlspecialchars(__('demo_email_label'), ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="demo-email" name="email" type="email" autocomplete="email" maxlength="160" value="<?= htmlspecialchars((string) ($demoOld['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars(__('demo_email_placeholder'), ENT_QUOTES, 'UTF-8') ?>"<?= !empty($demoErrors['email']) ? ' aria-invalid="true" aria-describedby="demo-email-error"' : '' ?>>
                        <?php if (!empty($demoErrors['email'])): ?><small id="demo-email-error" class="demo-field-error"><?= htmlspecialchars((string) $demoErrors['email'][0], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                    </div>
                    <button class="button demo-submit" type="submit"><?= htmlspecialchars(__('demo_submit'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">→</span></button>
                </form>
                <p class="demo-form-footnote"><?= htmlspecialchars(__('demo_form_footnote'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </section>

    <section class="home-section home-start" id="start" aria-labelledby="start-title">
        <div class="start-copy">
            <p class="section-kicker"><?= htmlspecialchars(__('home_start_kicker'), ENT_QUOTES, 'UTF-8') ?></p>
            <h2 id="start-title"><?= htmlspecialchars(__('home_start_title'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars(__('home_start_intro'), ENT_QUOTES, 'UTF-8') ?></p>
            <ol class="start-steps">
                <li><span>1</span><div><strong><?= htmlspecialchars(__('home_step_config_title'), ENT_QUOTES, 'UTF-8') ?></strong><p><?= htmlspecialchars(__('home_step_config_text'), ENT_QUOTES, 'UTF-8') ?></p></div></li>
                <li><span>2</span><div><strong><?= htmlspecialchars(__('home_step_build_title'), ENT_QUOTES, 'UTF-8') ?></strong><p><?= htmlspecialchars(__('home_step_build_text'), ENT_QUOTES, 'UTF-8') ?></p></div></li>
                <li><span>3</span><div><strong><?= htmlspecialchars(__('home_step_run_title'), ENT_QUOTES, 'UTF-8') ?></strong><p><?= htmlspecialchars(__('home_step_run_text'), ENT_QUOTES, 'UTF-8') ?></p></div></li>
            </ol>
        </div>
        <div class="start-panel">
            <p class="start-panel-label"><?= htmlspecialchars(__('home_start_command'), ENT_QUOTES, 'UTF-8') ?></p>
            <code>php -S localhost:8000 -t public public/router.php</code>
            <p><?= htmlspecialchars(__('home_readme_hint'), ENT_QUOTES, 'UTF-8') ?> <strong>README.md</strong></p>
        </div>
    </section>

    <section class="home-demos" aria-labelledby="demos-title">
        <div><p class="section-kicker"><?= htmlspecialchars(__('home_demos_kicker'), ENT_QUOTES, 'UTF-8') ?></p><h2 id="demos-title"><?= htmlspecialchars(__('home_demos_title'), ENT_QUOTES, 'UTF-8') ?></h2></div>
        <div class="demo-links">
            <a href="<?= htmlspecialchars(locale_url('hello/Kali'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('example_route'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">↗</span></a>
            <a href="<?= htmlspecialchars(locale_url('api/v1/health'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('home_demo_api'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">↗</span></a>
            <a href="<?= htmlspecialchars(locale_url('cookies'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('cookie_settings_title'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">↗</span></a>
            <a href="<?= htmlspecialchars(locale_url('docs'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('docs'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">↗</span></a>
            <a href="<?= htmlspecialchars(locale_url('contact'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('contact'), ENT_QUOTES, 'UTF-8') ?> <span aria-hidden="true">↗</span></a>
        </div>
    </section>
</div>
<script src="<?= htmlspecialchars(rtrim((string) config('app.base_url', ''), '/') . '/assets/js/home/demo.js', ENT_QUOTES, 'UTF-8') ?>" defer></script>
