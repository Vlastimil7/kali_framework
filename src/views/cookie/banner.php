<?php
// Zkontrolujeme, zda uživatel již udělil souhlas s cookies
$cookieConsent = isset($_COOKIE['cookie_consent']) ? json_decode($_COOKIE['cookie_consent'], true) : null;
$showBanner = $cookieConsent === null;
?>

<!-- Cookie banner -->
<div id="cookie-banner" class="fixed bottom-0 left-0 w-full bg-gray-800 text-white py-4 px-6 z-50 shadow-lg transition-transform duration-300 <?= $showBanner ? '' : 'translate-y-full hidden' ?>">
    <div class="container mx-auto">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex-1">
                <h3 class="text-xl font-bold mb-2">Používáme cookies</h3>
                <p class="text-sm md:text-base">
                    Tato stránka používá cookies pro zlepšení vašeho zážitku, analýzu návštěvnosti a personalizaci obsahu.
                    Kliknutím na "Přijmout vše" souhlasíte s používáním všech cookies.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <!-- Odstraněna třída cookie-settings-btn, která způsobovala problém -->
                <a href="<?= BASE_URL ?>/cookies/settings" class="text-sm border border-white text-white px-4 py-2 rounded hover:bg-white hover:text-gray-800 transition">
                    Nastavení
                </a>
                <a href="<?= BASE_URL ?>/cookies/reject" class="text-sm border border-white text-white px-4 py-2 rounded hover:bg-white hover:text-gray-800 transition">
                    Odmítnout
                </a>
                <a href="<?= BASE_URL ?>/cookies/accept-all" class="text-sm bg-gold border border-gold text-white px-4 py-2 rounded hover:bg-opacity-90 transition">
                    Přijmout vše
                </a>
            </div>
        </div>
    </div>
</div>
