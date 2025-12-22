<nav class="bg-black shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-20">
            <!-- Logo + info -->
            <div class="flex items-center gap-3">

                <!--      Logo -->
                <a href="<?= BASE_URL ?>/" class="block">
                    <img src="<?= BASE_URL ?>/assets/images/logo/mido_barbershop_logo.png"
                        alt="Logo"
                        class="h-14 w-auto ">
                </a>

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

                <a href="<?= BASE_URL ?>/vouchers" class="<?= $linkClasses ?>">Vouchery</a>

                <!-- Košík -->
                <a href="<?= BASE_URL ?>/cart" class="relative text-gray-600 hover:text-blue-600 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart']['count'])): ?>
                        <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                            <?= $_SESSION['cart']['count'] ?>
                        </span>
                    <?php endif; ?>
                </a>


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




                    <?php else: ?>
                        <!-- DESKTOP: dropdown "Informace" -->

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
                <a href="https://www.facebook.com/profile.php?id=100063520150213"
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
                <p class="px-2 mb-4 text-[0.75rem] font-semibold tracking-wide text-slate-500 uppercase">
                    Sledujte nás
                </p>

                <a href="https://www.facebook.com/profile.php?id=100063520150213"
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