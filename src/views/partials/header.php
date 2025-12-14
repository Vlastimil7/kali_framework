<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-20">
            <!-- Logo + info -->
            <div class="flex items-center gap-3">

                <!--      Logo -->
                <a href="<?= BASE_URL ?>/" class="block">
                    <img src="<?= BASE_URL ?>/assets/images/logo/ivision_media_logo_new.png"
                        alt="Logo"
                        class="h-18 w-auto  rounded-4xl ">
                </a>

                <!-- Brand -->
                <a href="<?= BASE_URL ?>/" class="flex flex-col leading-tight max-w-[200px]">
                    <span class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">
                        Dětská ordinace v Jaroměři
                    </span>
                    <span class="text-[0.65rem] sm:text-xs text-slate-500 flex items-center gap-1">
                        <span class="text-indigo-500 font-medium">PEDIA s.r.o.</span>
                        <span>•</span>
                        <span class="text-emerald-500 font-medium">PEDIA AZ s.r.o.</span>
                    </span>

                    <!-- Doktor – viditelné pouze na mobilu -->
                    <span class="block md:hidden text-[0.65rem] text-slate-600 mt-1 leading-tight">
                        MUDr. Ziad Albahri, Ph.D.<br>
                        <span class="text-[0.6rem] text-slate-500">praktický lékař pro děti a dorost</span>
                    </span>
                </a>

                <!-- Doktor – verze pro větší displeje -->
                <div class="hidden md:block border-l border-slate-200 pl-3 text-[0.7rem] leading-tight">
                    <div class="font-semibold text-gray-800">MUDr. Ziad Albahri, Ph.D.</div>
                    <div class="text-gray-600">praktický lékař pro děti a dorost</div>
                </div>
            </div>


            <!-- Přepínač jazyků -->
            <!-- <div class="language-switcher flex space-x-2">
                <?php /* foreach (lang()->getSupportedLanguages() as $lang): ?>
                    <a href="<?= BASE_URL ?>/language/change/<?= $lang ?>"
                        class="<?= lang()->getCurrentLanguage() === $lang ? 'font-bold text-blue-600' : 'text-gray-600' ?>">
                        <?= strtoupper($lang) ?>
                    </a>
                <?php endforeach; */ ?>
            </div> -->

            <!-- Hamburger menu button (mobil + tablet, desktop až od xl) -->
            <button id="menu-toggle" class="xl:hidden text-gray-600 hover:text-blue-600 focus:outline-none cursor-pointer">
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
             * Jedna funkcionalita pro generování menu – variant 'desktop' / 'mobile'
             */
            function renderMainNav(string $variant = 'desktop'): void
            {
                $isMobile = $variant === 'mobile';

                $linkBaseClasses = 'nav-item text-gray-700 hover:text-blue-600 font-medium cursor-pointer';
                $linkClasses = $isMobile
                    ? $linkBaseClasses . ' block'
                    : $linkBaseClasses;
            ?>

                <!-- Vždy: Domů + Novinky -->
                <a href="<?= BASE_URL ?>/" class="<?= $linkClasses ?>">Úvod</a>

                <a href="<?= BASE_URL ?>/contact/#google-address" class="<?= $linkClasses ?>">Adresa</a>
                <a href="<?= BASE_URL ?>/#hours" class="<?= $linkClasses ?>">Ordinační hodiny</a>
                <a href="<?= BASE_URL ?>/news" class="<?= $linkClasses ?>">Novinky</a>
                <a href="<?= BASE_URL ?>/#vacations" class="<?= $linkClasses ?>">Dovolená / Zástup</a>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <!-- Přihlášený uživatel -->
                    <a href="<?= BASE_URL ?>/profile" class="<?= $linkClasses ?>">Profil</a>

                    <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/admin/dashboard" class="<?= $linkClasses ?>">Admin</a>
                    <?php endif; ?>

                    <!-- Odhlášení -->
                    <a href="<?= BASE_URL ?>/logout" class="<?= $linkClasses ?>">Odhlásit se</a>

                <?php else: ?>

                    <?php if ($isMobile): ?>
                        <!-- MOBILE: "Informace" jako rozbalovací akordeon -->
                        <button type="button"
                            id="mobile-info-toggle"
                            class="<?= $linkBaseClasses ?> flex items-center justify-between w-full">
                            <span>Informace</span>
                            <svg id="mobile-info-icon"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                class="h-4 w-4 transform transition-transform duration-200">
                                <path fill="currentColor"
                                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" />
                            </svg>
                        </button>

                        <div id="mobile-info-menu" class="ml-4 mt-1 space-y-1 hidden">
                            <a href="<?= BASE_URL ?>/information"
                                class="block text-sm text-gray-700 hover:text-blue-600 font-medium">
                                Informace pro rodiče
                            </a>
                            <a href="<?= BASE_URL ?>/information/cenik"
                                class="block text-sm text-gray-700 hover:text-blue-600 font-medium">
                                Ceník
                            </a>
                            <a href="<?= BASE_URL ?>/ambulance"
                                class="block text-sm text-gray-700 hover:text-blue-600 font-medium">
                                Kontakty na odborné ambulance
                            </a>
                            <a href="<?= BASE_URL ?>/tips"
                                class="block text-sm text-gray-700 hover:text-blue-600 font-medium">
                                Praktické rady
                            </a>
                            <a href="<?= BASE_URL ?>/about"
                                class="block text-sm text-gray-700 hover:text-blue-600 font-medium">
                                O mně
                            </a>
                            <a href="<?= BASE_URL ?>/videa"
                                class="block text-sm text-gray-700 hover:text-blue-600 font-medium">
                                Videa
                            </a>
                        </div>

                    <?php else: ?>
                        <!-- DESKTOP: dropdown "Informace" -->
                        <div class="relative group">
                            <button type="button"
                                class="<?= $linkBaseClasses ?> flex items-center gap-1">
                                Informace
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    class="h-4 w-4">
                                    <path fill="currentColor"
                                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" />
                                </svg>
                            </button>

                            <!-- FIX: padding-top + větší hover zóna -->
                            <div class="absolute right-0 w-56 pt-2
                                        opacity-0 invisible group-hover:opacity-100 group-hover:visible
                                        transition-all duration-150 z-50">

                                <div class="rounded-xl bg-white shadow-lg border border-slate-100 overflow-hidden">

                                    <a href="<?= BASE_URL ?>/information"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        Informace pro rodiče
                                    </a>
                                    <a href="<?= BASE_URL ?>/information/cenik"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        Ceník
                                    </a>
                                    <a href="<?= BASE_URL ?>/ambulance"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        Kontakty na odborné ambulance
                                    </a>
                                    <a href="<?= BASE_URL ?>/tips"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        Praktické rady
                                    </a>
                                    <a href="<?= BASE_URL ?>/about"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        O mně
                                    </a>

                                    <a href="<?= BASE_URL ?>/videa"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        Videa
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Zbytek odkazů je stejný pro mobil i desktop -->

                    <a href="<?= BASE_URL ?>/login" class="<?= $linkClasses ?>">Přihlásit</a>

            <?php endif;
            }
            ?>

            <!-- Desktop menu – zobrazí se až od xl -->
            <div class="hidden xl:flex items-center space-x-6">
                <?php renderMainNav('desktop'); ?>

                <!-- FB ikona vpravo v menu -->
                <a href="https://www.facebook.com/profile.php?id=61584071354442"
                    target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center justify-center rounded-full border border-blue-500 text-blue-500 hover:bg-blue-50 px-3 py-1.5 text-sm font-medium transition">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-1.5 fill-current"
                        viewBox="0 0 24 24">
                        <path
                            d="M22 12.06C22 6.51 17.52 2 12 2S2 6.51 2 12.06C2 17.08 5.66 21.22 10.44 22v-6.9H7.9v-3.03h2.54V9.74c0-2.5 1.48-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 3.03h-2.34V22C18.34 21.22 22 17.08 22 12.06Z" />
                    </svg>
                    <span>Facebook</span>
                </a>
            </div>
        </div>

        <!-- Mobile menu (tablet + mobil) -->
        <div id="mobile-menu"
            class="hidden xl:hidden flex flex-col space-y-2 mt-4 px-0 pb-4 transition-all duration-300 ease-in-out">
            <?php renderMainNav('mobile'); ?>

            <!-- FB sekce v mobilním menu -->
            <div class="mt-4 pt-4 border-t border-slate-200">
                <p class="px-2 mb-2 text-[0.75rem] font-semibold tracking-wide text-slate-500 uppercase">
                    Sledujte nás
                </p>

                <a href="https://www.facebook.com/profile.php?id=61584071354442"
                    target="_blank" rel="noopener noreferrer"
                    class="mx-2 inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 transition">

                    <!-- FB ikona -->
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 fill-current"
                        viewBox="0 0 24 24">
                        <path
                            d="M22 12.06C22 6.51 17.52 2 12 2S2 6.51 2 12.06C2 17.08 5.66 21.22 10.44 22v-6.9H7.9v-3.03h2.54V9.74c0-2.5 3.77-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 3.03h-2.34V22C18.34 21.22 22 17.08 22 12.06Z" />
                    </svg>

                    <span>Facebook profil</span>
                </a>
            </div>
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

    // 🔽 Mobilní akordeon "Informace"
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