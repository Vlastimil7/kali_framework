<section id="contact" class="py-20 px-6" data-telemetry-section='contact'>
    <div class="container mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4 text-gradient"><?= __('contact_title', [], 'contact') ?></h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                <?= __('contact_intro', [], 'contact') ?>
            </p>
        </div>

        <div class="flex flex-col lg:flex-row gap-10">
            <div class="lg:w-1/2">
                <div class="card-gradient rounded-xl p-8 h-full">
                    <h3 class="text-2xl font-bold mb-6 text-white"><?= __('contact_info_title', [], 'contact') ?></h3>
                    <p class="text-gray-300 mb-8">
                        <?= __('contact_info_description', [], 'contact') ?>
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="w-12 h-12 rounded-full bg-gradient-main flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold mb-1 text-white"><?= __('contact_phone_label', [], 'contact') ?></h4>
                                <a href="tel:+420604158245" class="text-gray-300 hover:text-cyan-400" data-track="callToActionClick" data-track-meta='{"location":"contact","label":"phone-link"}'>+420 604 158 245</a>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 rounded-full bg-gradient-main flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold mb-1 text-white"><?= __('contact_email_label', [], 'contact') ?></h4>
                                <a href="mailto:kalasekvyvoj@gmail.com" class="text-gray-300 hover:text-cyan-400" data-track="callToActionClick" data-track-meta='{"location":"contact","label":"email-link"}'>kalasekvyvoj@gmail.com</a>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 rounded-full bg-gradient-main flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold mb-1 text-white"><?= __('contact_location_label', [], 'contact') ?></h4>
                                <p class="text-gray-300"><?= __('contact_location_value', [], 'contact') ?></p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 rounded-full bg-gradient-main flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold mb-1 text-white"><?= __('contact_availability_label', [], 'contact') ?></h4>
                                <p class="text-gray-300"><?= __('contact_availability_value', ['time' => '15:00 - 22:00'], 'contact') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10">
                        <h4 class="text-lg font-bold mb-4 text-white"><?= __('contact_social_title', [], 'contact') ?></h4>
                        <div class="flex space-x-4">
                            <a href="https://www.facebook.com/profile.php?id=61586776062120" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow" data-track="callToActionClick" data-track-meta='{"location":"contact","label":"facebook-link"}'>
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                            <a href="https://github.com/Vlastimil7" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow" data-track="callToActionClick" data-track-meta='{"location":"contact","label":"github-link"}'>
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z" />
                                </svg>
                            </a>
                            <a href="https://www.instagram.com/_vk_dev/"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow"
                                title="Instagram"
                                data-track="callToActionClick" data-track-meta='{"location":"contact","label":"instagram-link"}'>
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7 2C4.239 2 2 4.239 2 7v10c0 2.761 2.239 5 5 5h10c2.761 0 5-2.239 5-5V7c0-2.761-2.239-5-5-5H7zm10 2c1.654 0 3 1.346 3 3v10c0 1.654-1.346 3-3 3H7c-1.654 0-3-1.346-3-3V7c0-1.654 1.346-3 3-3h10zm-5 3a5 5 0 100 10 5 5 0 000-10zm0 2a3 3 0 110 6 3 3 0 010-6zm4.75-.5a1.25 1.25 0 11-2.5 0 1.25 1.25 0 012.5 0z" />
                                </svg>
                            </a>
                            <a href="https://www.linkedin.com/in/vlastimil-kal%C3%A1%C5%A1ek-b26744148" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow"
                                data-track="callToActionClick" data-track-meta='{"location":"contact","label":"linkedin-link"}'>
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                </svg>
                            </a>
                            <a href="mailto:kalasekvyvoj@gmail.com" class="w-8 h-8 rounded-full bg-gradient-main flex items-center justify-center hover-glow"
                                title="Email"
                                data-track="callToActionClick" data-track-meta='{"location":"contact","label":"email-link"}'>
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
                                data-track="callToActionClick" data-track-meta='{"location":"contact","label":"whatsapp-link"}'>
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
                </div>
            </div>


            <?php
            $old = $_SESSION['old']['contact'] ?? [];
            $topicOptions = [
                'web'          => 'contact_topic_web',
                'eshop'        => 'contact_topic_eshop',
                'app'          => 'contact_topic_app',
                'reservation'  => 'contact_topic_reservation',
                'api'          => 'contact_topic_api',
                'maintenance'  => 'contact_topic_maintenance',
                'consultation' => 'contact_topic_consultation',
                'other'        => 'contact_topic_other',
            ];

            // value = CZ, label = překlad
            $budgetOptions = [
                'Do 30 000 Kč'         => 'contact_budget_under_30k',
                '30 000 - 100 000 Kč'  => 'contact_budget_30_100k',
                '100 000 - 250 000 Kč' => 'contact_budget_100_250k',
                '250 000+ Kč'          => 'contact_budget_250k_plus',
                'Zatím nevím'          => 'contact_budget_unknown',
            ];
            ?>

            <div class="lg:w-1/2">
                <div class="card-gradient rounded-xl p-8">
                    <h3 class="text-2xl font-bold mb-6 text-white"><?= __('contact_form_title', [], 'contact') ?></h3>

                    <form
                        action="<?= locale_url('contact/send') ?>"
                        method="POST"
                        enctype="multipart/form-data"
                        data-contact-form>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="first_name" class="block text-gray-300 mb-2"><?= __('contact_first_name_label', [], 'contact') ?></label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    value="<?= htmlspecialchars($old['first_name'] ?? '', ENT_QUOTES) ?>"
                                    class="w-full px-4 py-3 bg-black bg-opacity-50 border border-gray-700 rounded-lg focus:outline-none focus:border-cyan-400 transition-colors text-white"
                                    placeholder="<?= __('contact_first_name_placeholder', [], 'contact') ?>"
                                    autocomplete="given-name"
                                    required>
                            </div>

                            <div>
                                <label for="last_name" class="block text-gray-300 mb-2"><?= __('contact_last_name_label', [], 'contact') ?></label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    value="<?= htmlspecialchars($old['last_name'] ?? '', ENT_QUOTES) ?>"
                                    class="w-full px-4 py-3 bg-black bg-opacity-50 border border-gray-700 rounded-lg focus:outline-none focus:border-cyan-400 transition-colors text-white"
                                    placeholder="<?= __('contact_last_name_placeholder', [], 'contact') ?>"
                                    autocomplete="family-name"
                                    required>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="email" class="block text-gray-300 mb-2"><?= __('contact_email_field_label', [], 'contact') ?></label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>"
                                class="w-full px-4 py-3 bg-black bg-opacity-50 border border-gray-700 rounded-lg focus:outline-none focus:border-cyan-400 transition-colors text-white"
                                placeholder="<?= __('contact_email_field_placeholder', [], 'contact') ?>"
                                autocomplete="email"
                                required>
                        </div>

                        <div class="mb-6">
                            <label for="phone" class="block text-gray-300 mb-2"><?= __('contact_phone_field_label', [], 'contact') ?></label>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES) ?>"
                                class="w-full px-4 py-3 bg-black bg-opacity-50 border border-gray-700 rounded-lg focus:outline-none focus:border-cyan-400 transition-colors text-white"
                                placeholder="<?= __('contact_phone_field_placeholder', [], 'contact') ?>"
                                autocomplete="tel">
                        </div>

                        <div class="mb-6">
                            <label for="project_type" class="block text-gray-300 mb-2"><?= __('contact_project_type_label', [], 'contact') ?></label>
                            <select
                                id="project_type"
                                name="topic"
                                class="w-full px-4 py-3 bg-black bg-opacity-50 border border-gray-700 rounded-lg focus:outline-none focus:border-cyan-400 transition-colors text-white"
                                required>

                                <option value="" disabled <?= empty($old['topic']) ? 'selected' : '' ?>>
                                    <?= __('contact_project_type_placeholder', [], 'contact') ?>
                                </option>

                                <?php foreach ($topicOptions as $value => $labelKey): ?>
                                    <option value="<?= htmlspecialchars($value, ENT_QUOTES) ?>"
                                        <?= (($old['topic'] ?? '') === $value) ? 'selected' : '' ?>>
                                        <?= __($labelKey, [], 'contact') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="budget" class="block text-gray-300 mb-2"><?= __('contact_budget_label', [], 'contact') ?></label>
                            <select
                                id="budget"
                                name="budget"
                                class="w-full px-4 py-3 bg-black bg-opacity-50 border border-gray-700 rounded-lg focus:outline-none focus:border-cyan-400 transition-colors text-white"
                                required>

                                <option value="" disabled <?= empty($old['budget']) ? 'selected' : '' ?>>
                                    <?= __('contact_budget_placeholder', [], 'contact') ?>
                                </option>

                                <?php foreach ($budgetOptions as $value => $labelKey): ?>
                                    <option value="<?= htmlspecialchars($value, ENT_QUOTES) ?>"
                                        <?= (($old['budget'] ?? '') === $value) ? 'selected' : '' ?>>
                                        <?= __($labelKey, [], 'contact') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="message" class="block text-gray-300 mb-2"><?= __('contact_message_label', [], 'contact') ?></label>
                            <textarea
                                id="message"
                                name="message"
                                rows="5"
                                class="w-full px-4 py-3 bg-black bg-opacity-50 border border-gray-700 rounded-lg focus:outline-none focus:border-cyan-400 transition-colors text-white"
                                placeholder="<?= __('contact_message_placeholder', [], 'contact') ?>"
                                required><?= htmlspecialchars($old['message'] ?? '', ENT_QUOTES) ?></textarea>
                        </div>

                        <!-- DROPZONE -->
                        <div class="mb-6">
                            <label class="block text-gray-300 mb-2"><?= __('contact_attachments_label', [], 'contact') ?></label>

                            <div
                                data-dropzone
                                class="cursor-pointer w-full rounded-lg border border-gray-700 bg-black bg-opacity-50 px-4 py-6 transition hover:border-gray-500">
                                <p class="text-gray-300" data-track='callToActionClick' data-track-meta='{"location":"contact","label":"file-dropzone"}'>
                                    <?= __('contact_attachments_hint', [], 'contact') ?> <span class="underline"><?= __('contact_attachments_click', [], 'contact') ?></span>
                                </p>

                                <input
                                    type="file"
                                    name="attachments[]"
                                    multiple
                                    data-file-input
                                    class="hidden"
                                    accept=".pdf,.png,.jpg,.jpeg,.webp">
                            </div>

                            <div data-file-list-wrap class="mt-3 hidden">
                                <ul data-file-list class="space-y-2 text-sm text-gray-200"></ul>
                            </div>
                        </div>

                        <!-- GDPR -->
                        <div class="mb-6 flex items-start gap-2 text-sm text-gray-300">
                            <input
                                type="checkbox"
                                name="gdpr"
                                id="gdpr"
                                value="1"
                                <?= !empty($old['gdpr']) ? 'checked' : '' ?>
                                required>
                            <label for="gdpr" class="cursor-pointer" data-track="callToActionClick" data-track-meta='{"location":"contact","label":"gdpr-checkbox"}'><?= __('contact_gdpr_label', [], 'contact') ?></label>
                        </div>

                        <button type="submit" class="btn-gradient rounded-lg px-8 py-4 text-white font-bold w-full cursor-pointer hover:scale-[1.02] transition-transform" data-track="callToActionClick" data-track-meta='{"location":"contact","label":"submit-button"}'>
                            <?= __('contact_submit_button', [], 'contact') ?>
                        </button>

                        <!-- Overlay: sending -->
                        <div
                            data-contact-overlay
                            class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
                            role="dialog"
                            aria-modal="true"
                            aria-hidden="true">
                            <!-- Backdrop -->
                            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

                            <!-- Card -->
                            <div class="relative w-full max-w-md rounded-2xl border border-white/10 bg-black/60 p-6 shadow-2xl">
                                <div class="flex items-start gap-4">
                                    <!-- Spinner -->
                                    <div class="mt-1 shrink-0">
                                        <div class="h-10 w-10 rounded-full border-4 border-white/20 border-t-white animate-spin"></div>
                                    </div>

                                    <div class="min-w-0">
                                        <h4 class="text-lg font-bold text-white"><?= __('contact_overlay_title', [], 'contact') ?></h4>
                                        <p class="mt-1 text-sm text-gray-200">
                                            <?= __('contact_overlay_description', [], 'contact') ?>
                                        </p>

                                        <div class="mt-3 flex items-center gap-1">
                                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-white/60 animate-pulse"></span>
                                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-white/40 animate-pulse [animation-delay:150ms]"></span>
                                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-white/25 animate-pulse [animation-delay:300ms]"></span>
                                        </div>

                                        <p class="mt-4 text-xs text-gray-300">
                                            <?= __('contact_overlay_hint', [], 'contact') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</section>



<script src="<?= BASE_URL ?>/assets/js/home/contact-form.js" defer></script>

<script id="i18n-contact" type="application/json">
    <?= json_encode([
        'attachments' => [
            'title' => __('contact_js_attachments_title', [], 'contact'),
            'clear_all' => __('contact_js_clear_all', [], 'contact'),
            'remove' => __('contact_js_remove', [], 'contact'),
            'remove_aria' => __('contact_js_remove_aria', ['file' => '{file}'], 'contact'),

            'toast_total_exceeded_title' => __('contact_js_toast_attachments_title', [], 'contact'),
            'toast_total_exceeded_text' => __('contact_js_toast_total_exceeded_text', [], 'contact'),
            'toast_total_exceeded_msg' => __('contact_js_toast_total_exceeded_msg', ['total' => '{total}', 'max' => '{max}'], 'contact'),

            'toast_duplicate_text' => __('contact_js_toast_duplicate_text', [], 'contact'),
            'toast_duplicate_msg' => __('contact_js_toast_duplicate_msg', ['file' => '{file}'], 'contact'),

            'toast_removed_text' => __('contact_js_toast_removed_text', [], 'contact'),
            'toast_removed_msg' => __('contact_js_toast_removed_msg', ['file' => '{file}'], 'contact'),

            'summary' => __('contact_js_summary', ['count' => '{count}', 'total' => '{total}', 'max' => '{max}'], 'contact'),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
