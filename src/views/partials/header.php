<nav class="bg-black shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-20">
            <!-- Logo + info -->
            <div class="flex items-center gap-3">
                <!--      Logo -->
                <a href="<?= locale_url() ?>" class="block text-gradient font-bold text-2xl" data-track="callToActionClick" data-track-meta='{"location":"header","label":"logo-click"}'>
                    Vlastimil Kalášek
                </a>
            </div>

            <!-- Hamburger menu button (mobil + tablet, desktop až od xl) -->
            <button id="menu-toggle" class="xl:hidden text-gray-600 hover:text-blue-600 focus:outline-none cursor-pointer" data-track="callToActionClick" data-track-meta='{"location":"header","label":"menu-toggle"}'>
                <svg id="menu-icon" class="w-8 h-8 transition-transform duration-300 ease-in-out" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg id="close-icon" class="w-8 h-8 hidden transition-transform duration-300 ease-in-out cursor-pointer"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <?php
            /**
             * Social ikony – generují se z jednoho místa
             * $variant: 'desktop' / 'mobile'
             */
            function renderSocialIcons(string $variant = 'desktop'): void
            {
                $isMobile = ($variant === 'mobile');

                $wrapClass = $isMobile
                    ? 'flex items-center gap-3 mt-4'
                    : 'flex items-center gap-3';

                // sjednocené velikosti
                $btnClass = 'w-10 h-10 rounded-full bg-gradient-main flex items-center justify-center hover-glow';
                $iconClass = 'w-4 h-4 text-white';

            ?>
                <div class="<?= $wrapClass ?>">
                    <a href="https://www.facebook.com/profile.php?id=61586776062120" target="_blank" rel="noopener noreferrer"
                        class="<?= $btnClass ?>" title="Facebook" data-track="callToActionClick" data-track-meta='{"location":"header","label":"facebook-link"}'>
                        <svg class="<?= $iconClass ?>" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>

                    <a href="https://www.instagram.com/_vk_dev/" target="_blank" rel="noopener noreferrer"
                        class="<?= $btnClass ?>" title="Instagram" data-track="callToActionClick" data-track-meta='{"location":"header","label":"instagram-link"}'>
                        <svg class="<?= $iconClass ?>" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 2C4.239 2 2 4.239 2 7v10c0 2.761 2.239 5 5 5h10c2.761 0 5-2.239 5-5V7c0-2.761-2.239-5-5-5H7zm10 2c1.654 0 3 1.346 3 3v10c0 1.654-1.346 3-3 3H7c-1.654 0-3-1.346-3-3V7c0-1.654 1.346-3 3-3h10zm-5 3a5 5 0 100 10 5 5 0 000-10zm0 2a3 3 0 110 6 3 3 0 010-6zm4.75-.5a1.25 1.25 0 11-2.5 0 1.25 1.25 0 012.5 0z" />
                        </svg>
                    </a>

                    <a href="mailto:kalasekvyvoj@gmail.com"
                        class="<?= $btnClass ?>" title="Email" data-track="callToActionClick" data-track-meta='{"location":"header","label":"email-link"}'>
                        <svg class="<?= $iconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </a>

                    <a href="https://wa.me/420604158245" target="_blank" rel="noopener noreferrer"
                        class="<?= $btnClass ?>" title="WhatsApp" data-track="callToActionClick" data-track-meta='{"location":"header","label":"whatsapp-link"}'>
                        <svg class="<?= $iconClass ?>" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.52 3.48A11.8 11.8 0 0012.06 0C5.5 0 .16 5.34.16 11.9c0 2.1.55 4.16 1.6 5.98L0 24l6.3-1.65a11.86 11.86 0 005.76 1.47h.01c6.56 0 11.9-5.34 11.9-11.9a11.8 11.8 0 00-3.45-8.44zM12.07 21.4a9.4 9.4 0 01-4.78-1.3l-.34-.2-3.74.98 1-3.64-.22-.37a9.36 9.36 0 01-1.44-5c0-5.17 4.2-9.38 9.38-9.38a9.32 9.32 0 016.64 2.75 9.3 9.3 0 012.74 6.63c0 5.17-4.2 9.38-9.37 9.38zm5.14-7.03c-.28-.14-1.66-.82-1.92-.91-.26-.1-.45-.14-.64.14-.19.28-.73.9-.9 1.08-.16.19-.33.21-.61.07-.28-.14-1.2-.44-2.28-1.4-.84-.75-1.4-1.67-1.57-1.95-.16-.28-.02-.43.12-.57.12-.12.28-.33.42-.5.14-.16.19-.28.28-.47.1-.19.05-.35-.02-.5-.07-.14-.64-1.54-.88-2.11-.23-.55-.47-.48-.64-.49l-.55-.01c-.19 0-.5.07-.76.35-.26.28-1 1-1 2.44 0 1.44 1.03 2.83 1.18 3.02.14.19 2.03 3.1 4.92 4.35.69.3 1.23.48 1.65.62.69.22 1.32.19 1.81.12.55-.08 1.66-.68 1.9-1.34.24-.66.24-1.23.16-1.34-.07-.12-.26-.19-.55-.33z" />
                        </svg>
                    </a>
                </div>
            <?php
            }

            /**
             * Jedna funkcionalita pro generování menu – variant 'desktop' / 'mobile'
             */
            function renderMainNav(string $variant = 'desktop'): void
            {
                $isMobile = $variant === 'mobile';

                $linkBaseClasses = 'nav-item text-gray-700 hover:text-white font-medium cursor-pointer';
                $linkClasses = $isMobile ? $linkBaseClasses . ' block' : $linkBaseClasses;
            ?>

                <a href="<?= locale_url() ?>" class="<?= $linkClasses ?>" data-track="callToActionClick" data-track-meta='{"location":"header","label":"home-link"}'>
                    <?= __('header_nav_home', [], 'header') ?>
                </a>

                <a href="<?= locale_url('#services') ?>" class="<?= $linkClasses ?>" data-track="callToActionClick" data-track-meta='{"location":"header","label":"services-link"}'>
                    <?= __('header_nav_services', [], 'header') ?>
                </a>

                <a href="<?= locale_url('#pricing') ?>" class="<?= $linkClasses ?>" data-track="callToActionClick" data-track-meta='{"location":"header","label":"pricing-link"}'>
                    <?= __('header_nav_pricing', [], 'header') ?>
                </a>

                <a href="<?= locale_url('#references') ?>" class="<?= $linkClasses ?>" data-track="callToActionClick" data-track-meta='{"location":"header","label":"references-link"}'>
                    <?= __('header_nav_references', [], 'header') ?>
                </a>

                <a href="<?= locale_url('contact') ?>" class="<?= $linkClasses ?>" data-track="callToActionClick" data-track-meta='{"location":"header","label":"contact-link"}'>
                    <?= __('header_nav_contact', [], 'header') ?>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <a href="<?= locale_url('profile') ?>" class="<?= $linkClasses ?>" data-track="callToActionClick" data-track-meta='{"location":"header","label":"profile-link"}'>
                        <?= __('header_nav_profile', [], 'header') ?>
                    </a>

                    <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="<?= locale_url('admin/dashboard') ?>" class="<?= $linkClasses ?>" data-track="callToActionClick" data-track-meta='{"location":"header","label":"admin-link"}'>
                            <?= __('header_nav_admin', [], 'header') ?>
                        </a>
                       
                    <?php endif; ?>

                    <a href="<?= locale_url('logout') ?>" class="<?= $linkClasses ?>" data-track="callToActionClick" data-track-meta='{"location":"header","label":"logout-link"}'>
                        <?= __('header_nav_logout', [], 'header') ?>
                    </a>

                <?php else: ?>

                    <form action="<?= locale_url('ai-mode/toggle') ?>" method="POST" class="inline-flex items-center me-5 cursor-pointer text-white">
                        <label class="inline-flex items-center me-5 cursor-pointer text-white">
                            <input type="hidden" name="enabled" value="0">
                            <input type="checkbox" id="ai-toggle" name="enabled" value="1"
                                class="sr-only peer" <?= !empty($_SESSION['ai_mode']) ? 'checked' : '' ?>
                                onchange="this.form.submit()"
                                data-track="aiToggle" data-track-meta='{"location":"header","label":"ai-<?= !empty($_SESSION['ai_mode']) ? 'disabled' : 'enabled' ?>-toggle"}'>

                            <div class="relative w-9 h-5 bg-neutral-quaternary rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600 dark:peer-checked:bg-green-600"></div>

                            <span class="select-none ms-3 text-sm font-medium text-heading">
                                <?= __('header_ai_mode_label', [], 'header') ?>
                            </span>
                        </label>
                    </form>

            <?php endif; ?>

                <!-- The public switcher changes the URL and preserves this page. -->
                <div class="language-switcher flex space-x-2">
                    <?php $switchPaths = isset($localizedPaths) && is_array($localizedPaths) ? $localizedPaths : null; ?>
                    <?php foreach (lang()->getSupportedLanguages() as $languageCode): ?>
                        <?php if ($switchPaths !== null && !isset($switchPaths[$languageCode])) continue; ?>
                        <a href="<?= htmlspecialchars(locale_switch_url($languageCode, $switchPaths), ENT_QUOTES) ?>"
                            hreflang="<?= htmlspecialchars($languageCode, ENT_QUOTES) ?>"
                            class="<?= lang()->getCurrentLanguage() === $languageCode ? 'font-bold text-pink-500' : 'text-gray-600' ?>"
                            data-track="languageSwitch" data-track-meta='{"location":"header","label":"<?= $languageCode ?>-switch"}'>
                            <?= strtoupper($languageCode) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

            <?php
            }

            ?>

            <!-- Desktop menu – zobrazí se až od xl -->
            <div class="hidden xl:flex items-center space-x-6">
                <?php renderMainNav('desktop'); ?>
                <?php renderSocialIcons('desktop'); ?>
            </div>
        </div>

        <!-- Mobile menu (tablet + mobil) -->
        <div id="mobile-menu"
            class="hidden xl:hidden flex flex-col space-y-2 mt-4 px-0 pb-4 transition-all duration-300 ease-in-out">
            <?php renderMainNav('mobile'); ?>
            <?php renderSocialIcons('mobile'); ?>
        </div>
    </div>
</nav>

<!-- JavaScript pro togglování menu + mobilní akordeon -->
<script>
    const toggleBtn = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    const closeIcon = document.getElementById('close-icon');

    toggleBtn.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
        menuIcon.classList.toggle('hidden');
        closeIcon.classList.toggle('hidden');
    });

    const mobileInfoToggle = document.getElementById('mobile-info-toggle');
    const mobileInfoMenu = document.getElementById('mobile-info-menu');
    const mobileInfoIcon = document.getElementById('mobile-info-icon');

    if (mobileInfoToggle && mobileInfoMenu && mobileInfoIcon) {
        mobileInfoToggle.addEventListener('click', function() {
            mobileInfoMenu.classList.toggle('hidden');
            mobileInfoIcon.classList.toggle('rotate-180');
        });
    }
</script>
