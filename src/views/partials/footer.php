<!-- Footer -->
<footer class="py-12 px-6 bg-black bg-opacity-50 w-full" data-telemetry-section="footer">
    <div class="container mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            <div>
                <div class="flex items-center space-x-2 mt-10">
                    <!-- Logo -->
                    <img src="<?= config('app.base_url', '') ?>/assets/images/logo/vk-dev.png" alt="VK-DEV" class="w-40 h-40" />

                </div>
                <p class="text-gray-400 mb-6">
                    <?= __('footer_about_text', [], 'footer') ?>
                </p>
                <div class="flex space-x-4">
                    <a href="https://www.facebook.com/profile.php?id=61586776062120" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow" data-track="callToActionClick" data-track-meta='{"location":"footer","label":"facebook-link"}'>
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956             1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="https://github.com/Vlastimil7" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow" data-track="callToActionClick" data-track-meta='{"location":"footer","label":"github-link"}'>
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z" />
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/_vk_dev/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow"
                        title="Instagram"
                        data-track="callToActionClick"
                        data-track-meta='{"location":"footer","label":"instagram-link"}'>
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 2C4.239 2 2 4.239 2 7v10c0 2.761 2.239 5 5 5h10c2.761 0 5-2.239 5-5V7c0-2.761-2.239-5-5-5H7zm10 2c1.654 0 3 1.346 3 3v10c0 1.654-1.346 3-3 3H7c-1.654 0-3-1.346-3-3V7c0-1.654 1.346-3 3-3h10zm-5 3a5 5 0 100 10 5 5 0 000-10zm0 2a3 3 0 110 6 3 3 0 010-6zm4.75-.5a1.25 1.25 0 11-2.5 0 1.25 1.25 0 012.5 0z" />
                        </svg>
                    </a>
                    <a href="https://www.linkedin.com/in/vlastimil-kal%C3%A1%C5%A1ek-b26744148" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow" data-track="callToActionClick" data-track-meta='{"location":"footer","label":"linkedin-link"}'>
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                    <a href="mailto:kalasekvyvoj@gmail.com" class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow"
                        title="Email" data-track="callToActionClick" data-track-meta='{"location":"footer","label":"email-link"}'>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </a>
                    <a
                        href="https://wa.me/420604158245"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow"
                        title="WhatsApp"
                        data-track="callToActionClick"
                        data-track-meta='{"location":"footer","label":"whatsapp-link"}'>
                        <svg
                            class="w-4 h-4 text-white"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.52 3.48A11.8 11.8 0 0012.06 0C5.5 0 .16 5.34.16 11.9c0 2.1.55 4.16 1.6 5.98L0 24l6.3-1.65a11.86 11.86 0 005.76 1.47h.01c6.56 0 11.9-5.34 11.9-11.9a11.8 11.8 0 00-3.45-8.44zM12.07 21.4a9.4 9.4 0 01-4.78-1.3l-.34-.2-3.74.98 1-3.64-.22-.37a9.36 9.36 0 01-1.44-5c0-5.17 4.2-9.38 9.38-9.38a9.32 9.32 0 016.64 2.75 9.3 9.3 0 012.74 6.63c0 5.17-4.2 9.38-9.37 9.38zm5.14-7.03c-.28-.14-1.66-.82-1.92-.91-.26-.1-.45-.14-.64.14-.19.28-.73.9-.9 1.08-.16.19-.33.21-.61.07-.28-.14-1.2-.44-2.28-1.4-.84-.75-1.4-1.67-1.57-1.95-.16-.28-.02-.43.12-.57.12-.12.28-.33.42-.5.14-.16.19-.28.28-.47.1-.19.05-.35-.02-.5-.07-.14-.64-1.54-.88-2.11-.23-.55-.47-.48-.64-.49l-.55-.01c-.19 0-.5.07-.76.35-.26.28-1 1-1 2.44 0 1.44 1.03 2.83 1.18 3.02.14.19 2.03 3.1 4.92 4.35.69.3 1.23.48 1.65.62.69.22 1.32.19 1.81.12.55-.08 1.66-.68 1.9-1.34.24-.66.24-1.23.16-1.34-.07-.12-.26-.19-.55-.33z" />
                        </svg>
                    </a>

                </div>
            </div>

            <div>
                <h3 class="text-xl font-bold mb-6 text-white"><?= __('footer_nav_title', [], 'footer') ?></h3>
                <ul class="space-y-4">
                    <li>
                        <a href="<?= locale_url() ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"home-link"}'>
                            <?= __('footer_nav_home', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#services') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"services-link"}'>
                            <?= __('footer_nav_services', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#how-it-works') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"how-it-works-link"}'>
                            <?= __('footer_nav_how_it_works', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#pricing') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"pricing-link"}'>
                            <?= __('footer_nav_pricing', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#references') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"references-link"}'>
                            <?= __('footer_nav_references', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('contact') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"contact-link"}'>
                            <?= __('footer_nav_contact', [], 'footer') ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="text-xl font-bold mb-6 text-white"><?= __('footer_services_title', [], 'footer') ?></h3>
                <ul class="space-y-4">
                    <li>
                        <a href="<?= locale_url('#services') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"web-link"}'>
                            <?= __('footer_services_websites', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#services') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"eshop-link"}'>
                            <?= __('footer_services_eshops', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#services') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"web-apps-link"}'>
                            <?= __('footer_services_web_apps', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#services') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"rest-api-link"}'>
                            <?= __('footer_services_rest_api', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#services') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"system-integrations-link"}'>
                            <?= __('footer_services_system_integrations', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#services') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"technical-consulting-link"}'>
                            <?= __('footer_services_technical_consulting', [], 'footer') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= locale_url('#services') ?>" class="text-gray-400 hover:text-cyan-400 transition-colors"
                            data-track="callToActionClick" data-track-meta='{"location":"footer","label":"support-and-maintenance-link"}'>
                            <?= __('footer_services_support_maintenance', [], 'footer') ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="text-xl font-bold mb-6 text-white"><?= __('footer_contact_title', [], 'footer') ?></h3>
                <p class="text-gray-400 mb-6">
                    <?= __('footer_contact_text', [], 'footer') ?>
                </p>

                <a href="<?= locale_url('contact') ?>"
                    class="inline-block bg-gradient-main px-5 py-3 rounded-lg text-white"
                    data-track="callToActionClick" data-track-meta='{"location":"footer","label":"contact-cta"}'>
                    <?= __('footer_nav_contact', [], 'footer') ?>
                </a>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 mb-4 md:mb-0">
                    &copy; <?= date('Y'); ?> <?= __('footer_copyright', [], 'footer') ?>
                </p>

                <div class="flex space-x-6">
                    <a href="<?= locale_url('gdpr') ?>" class="text-gray-500 hover:text-cyan-400 transition-colors"
                        data-track="callToActionClick" data-track-meta='{"location":"footer","label":"gdpr-link"}'>
                        <?= __('footer_link_privacy', [], 'footer') ?>
                    </a>
                    <a href="<?= locale_url('cookies/settings') ?>" class="text-gray-500 hover:text-cyan-400 transition-colors"
                        data-track="callToActionClick" data-track-meta='{"location":"footer","label":"cookies-link"}'>
                        <?= __('footer_link_cookies', [], 'footer') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
