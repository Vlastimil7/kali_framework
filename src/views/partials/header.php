<nav class="bg-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="<?= BASE_URL ?>/" class="text-2xl font-bold text-green-600">
                Counter.cz
            </a>

            <!-- Přepínač jazyků -->
            <!-- <div class="language-switcher flex space-x-2">
                <?php /* foreach (lang()->getSupportedLanguages() as $lang): ?>
                    <a href="<?= BASE_URL ?>/language/change/<?= $lang ?>"
                        class="<?= lang()->getCurrentLanguage() === $lang ? 'font-bold text-blue-600' : 'text-gray-600' ?>">
                        <?= strtoupper($lang) ?>
                    </a>
                <?php endforeach; */?>
            </div> -->

            <!-- Hamburger menu button (mobilní verze) -->
            <button id="menu-toggle" class="md:hidden text-gray-600 hover:text-green-600 focus:outline-none cursor-pointer">
                <svg id="menu-icon" class="w-8 h-8 transition-transform duration-300 ease-in-out" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg id="close-icon" class="w-8 h-8 hidden transition-transform duration-300 ease-in-out cursor-pointer" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Desktop menu -->
            <div class="hidden md:flex space-x-6 items-center">
                <a href="<?= BASE_URL ?>/" class="text-gray-600 hover:text-green-600 cursor-pointer">Domů</a>
                <a href="<?= BASE_URL ?>/menu" class="text-gray-600 hover:text-green-600 cursor-pointer">Menu</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Položky menu pro přihlášené uživatele -->
                    <a href="<?= BASE_URL ?>/dashboard" class="text-gray-600 hover:text-green-600 cursor-pointer">Dashboard</a>
                    <a href="<?= BASE_URL ?>/orders" class="text-gray-600 hover:text-green-600 cursor-pointer">Moje objednávky</a>
                    <a href="<?= BASE_URL ?>/profile" class="text-gray-600 hover:text-green-600 cursor-pointer">Profil</a>

                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <!-- Položky menu pouze pro adminy -->
                        <a href="<?= BASE_URL ?>/admin/dashboard" class="text-gray-600 hover:text-green-600 cursor-pointer">Admin</a>
                    <?php endif; ?>

                    <!-- Košík -->
                    <a href="<?= BASE_URL ?>/cart" class="relative text-gray-600 hover:text-green-600 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart']['count'])): ?>
                            <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                <?= $_SESSION['cart']['count'] ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Odhlášení pro přihlášené uživatele -->
                    <a href="<?= BASE_URL ?>/logout" class="text-gray-600 hover:text-green-600 cursor-pointer">Odhlásit se</a>
                <?php else: ?>
                    <!-- Položky pro nepřihlášené -->
                    <a href="<?= BASE_URL ?>/o-nas" class="text-gray-600 hover:text-green-600 cursor-pointer">O nás</a>
                    <a href="<?= BASE_URL ?>/kontakt" class="text-gray-600 hover:text-green-600 cursor-pointer">Kontakt</a>

                    <!-- Košík pro nepřihlášené -->
                    <a href="<?= BASE_URL ?>/cart" class="relative text-gray-600 hover:text-green-600 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart']['count'])): ?>
                            <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                <?= $_SESSION['cart']['count'] ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <a href="<?= BASE_URL ?>/login" class="text-gray-600 hover:text-green-600 cursor-pointer">Přihlásit</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile menu (skryté, zobrazí se kliknutím) -->
        <div id="mobile-menu"
            class="hidden md:hidden flex flex-col space-y-2 mt-4 px-4 pb-4 transition-all duration-300 ease-in-out">
            <a href="<?= BASE_URL ?>/" class="text-gray-600 hover:text-green-600 cursor-pointer block">Domů</a>
            <a href="<?= BASE_URL ?>/menu" class="text-gray-600 hover:text-green-600 cursor-pointer block">Menu</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Položky menu pro přihlášené uživatele - mobilní verze -->
                <a href="<?= BASE_URL ?>/dashboard" class="text-gray-600 hover:text-green-600 cursor-pointer block">Dashboard</a>
                <a href="<?= BASE_URL ?>/orders" class="text-gray-600 hover:text-green-600 cursor-pointer block">Moje objednávky</a>
                <a href="<?= BASE_URL ?>/profile" class="text-gray-600 hover:text-green-600 cursor-pointer block">Profil</a>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <!-- Položky menu pouze pro adminy - mobilní verze -->
                    <a href="<?= BASE_URL ?>/admin/dashboard" class="text-gray-600 hover:text-green-600 cursor-pointer block">Admin</a>
                <?php endif; ?>

                <!-- Košík - mobilní verze -->
                <a href="<?= BASE_URL ?>/cart" class="text-gray-600 hover:text-green-600 cursor-pointer block flex items-center">
                    <span>Košík</span>
                    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart']['count'])): ?>
                        <span class="ml-2 bg-green-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                            <?= $_SESSION['cart']['count'] ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Odhlášení pro přihlášené uživatele - mobilní verze -->
                <a href="<?= BASE_URL ?>/logout" class="text-gray-600 hover:text-green-600 cursor-pointer block">Odhlásit se</a>
            <?php else: ?>
                <!-- Položky pro nepřihlášené - mobilní verze -->
                <a href="<?= BASE_URL ?>/o-nas" class="text-gray-600 hover:text-green-600 cursor-pointer block">O nás</a>
                <a href="<?= BASE_URL ?>/kontakt" class="text-gray-600 hover:text-green-600 cursor-pointer block">Kontakt</a>

                <!-- Košík - mobilní verze -->
                <a href="<?= BASE_URL ?>/cart" class="text-gray-600 hover:text-green-600 cursor-pointer block flex items-center">
                    <span>Košík</span>
                    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart']['count'])): ?>
                        <span class="ml-2 bg-green-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                            <?= $_SESSION['cart']['count'] ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="<?= BASE_URL ?>/login" class="text-gray-600 hover:text-green-600 cursor-pointer block">Přihlásit</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- JavaScript pro togglování menu -->
<script>
    document.getElementById('menu-toggle').addEventListener('click', function() {
        let mobileMenu = document.getElementById('mobile-menu');
        let menuIcon = document.getElementById('menu-icon');
        let closeIcon = document.getElementById('close-icon');

        // Toggle visibility
        mobileMenu.classList.toggle('hidden');

        // Toggle icony
        menuIcon.classList.toggle('hidden');
        closeIcon.classList.toggle('hidden');
    });
</script>