<!DOCTYPE html>
<html lang="<?= lang()->getCurrentLanguage() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= config('app.base_url', '') ?>/assets/images/logo/fav/vk-dev.ico">
    <?php
    header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$stylePath = ROOT_PATH . '/public/assets/css/style.css';
$ownStylePath = ROOT_PATH . '/public/assets/css/ownStyles.css';
$styleVersion    = file_exists($stylePath) ? filemtime($stylePath) : time();
$ownStyleVersion = file_exists($ownStylePath) ? filemtime($ownStylePath) : time();
$localizedPaths = isset($data['localizedPaths']) && is_array($data['localizedPaths'])
    ? $data['localizedPaths']
    : null;
$currentPath = $localizedPaths[lang()->getCurrentLanguage()] ?? current_route_path();
$pageQuery = request_query_parameters();
$currentUrl = locale_site_url($currentPath, lang()->getCurrentLanguage(), $pageQuery);
$ogLocales = ['en' => 'en_US', 'cs' => 'cs_CZ', 'de' => 'de_DE'];

// SEO defaulty
$seoTitle = $data['title'] ?? '';
$seoDesc  = $data['description'] ?? '';
$seoKw    = $data['keywords'] ?? 'vývoj webových aplikací, tvorba webových stránek, web na míru, firemní weby, vývoj informačních systémů, PHP vývojář, fullstack developer, REST API vývoj, systémové integrace, digitální řešení pro firmy, web developer Hradec Králové, programátor na míru, zakázkový software, vývoj e-commerce, webové portály, agilní vývoj, moderní webové technologie, optimalizace výkonu, bezpečnost webu, UX/UI design, správa a údržba webů';
$ogImage  = $data['og_image'] ?? (rtrim(config('app.site_url', ''), '/') . '/assets/images/logo/vk-dev.png');
?>
    <title><?= htmlspecialchars($seoTitle, ENT_QUOTES) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDesc, ENT_QUOTES) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl, ENT_QUOTES) ?>">
    <?php foreach (lang()->getSupportedLanguages() as $languageCode): ?>
        <?php
    if ($localizedPaths !== null && !isset($localizedPaths[$languageCode])) {
        continue;
    }
        $alternatePath = $localizedPaths[$languageCode] ?? current_route_path();
        ?>
        <link rel="alternate" hreflang="<?= htmlspecialchars($languageCode, ENT_QUOTES) ?>" href="<?= htmlspecialchars(locale_site_url($alternatePath, $languageCode, $pageQuery), ENT_QUOTES) ?>">
    <?php endforeach; ?>
    <?php if ($localizedPaths === null || isset($localizedPaths[lang()->getDefaultLanguage()])): ?>
        <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars(locale_site_url($localizedPaths[lang()->getDefaultLanguage()] ?? current_route_path(), lang()->getDefaultLanguage(), $pageQuery), ENT_QUOTES) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= !empty($data['noindex']) ? 'noindex, nofollow' : 'index, follow' ?>">

    <meta name="keywords" content="<?= htmlspecialchars($seoKw, ENT_QUOTES) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDesc, ENT_QUOTES) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($currentUrl, ENT_QUOTES) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="<?= $ogLocales[lang()->getCurrentLanguage()] ?? lang()->getCurrentLanguage() ?>">
    <?php foreach (lang()->getSupportedLanguages() as $languageCode): ?>
        <?php if ($languageCode !== lang()->getCurrentLanguage() && ($localizedPaths === null || isset($localizedPaths[$languageCode]))): ?>
            <meta property="og:locale:alternate" content="<?= $ogLocales[$languageCode] ?? $languageCode ?>">
        <?php endif; ?>
    <?php endforeach; ?>


    <link href="<?= config('app.base_url', '') ?>/assets/css/style.css?v=<?= $styleVersion ?>" rel="stylesheet">
    <link href="<?= config('app.base_url', '') ?>/assets/css/ownStyles.css?v=<?= $ownStyleVersion ?>" rel="stylesheet">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "",
            "medicalSpecialty": "",
            "url": "<?= config('app.site_url', '') ?>",
            "logo": "<?= config('app.site_url', '') ?>/assets/images/logo/logo.jpeg",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Dobrovského 718/6",
                "addressLocality": "Hradec Králové",
                "postalCode": "50002",
                "addressCountry": "CZ"
            },
            "areaServed": ["Hradec Králové"]
        }
    </script>


    <script>
        const APP_BASE_URL = "<?= rtrim(config('app.base_url', ''), '/') ?>";
        const API_BASE = APP_BASE_URL + "/api/v1/chat";
        const STATUS_URL = API_BASE + "/status";
        const CHAT_URL = API_BASE;
    </script>


    <script>
        window.APP_BASE_URL = "<?= rtrim(config('app.base_url', ''), '/') ?>";
        window.RECAPTCHA_SITE_KEY = '<?= config('recaptcha.site_key', '') ?>';
    </script>


    <!-- Consent Mode v2: default deny + helpery -->
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('consent', 'default', {
            ad_storage: 'denied',
            analytics_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            functionality_storage: 'granted',
            security_storage: 'granted'
        });

        function applyGtagConsent(consent) {
            try {
                gtag('consent', 'update', {
                    analytics_storage: consent.analytics ? 'granted' : 'denied',
                    ad_storage: consent.marketing ? 'granted' : 'denied',
                    ad_user_data: consent.marketing ? 'granted' : 'denied',
                    ad_personalization: consent.marketing ? 'granted' : 'denied'
                });
            } catch (e) {}
        }
    </script>

    <!-- GA připravené, ale neaktivní do souhlasu -->
    <script type="text/plain" data-consent="analytics" data-src="https://www.googletagmanager.com/gtag/js?id=G-EYVRF9F1NZ"></script>
    <script type="text/plain" data-consent="analytics">
        gtag('js', new Date());
      gtag('config', 'G-EYVRF9F1NZ');
    </script>

    <!--- Telemetry JS --->
    <script type="text/plain" data-consent="analytics" data-src="<?= config('app.base_url', '') ?>/assets/js/telemetry/telemetry.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= config('app.base_url', '') ?>/assets/js/telemetry/plugins/clicks.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= config('app.base_url', '') ?>/assets/js/telemetry/plugins/scroll.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= config('app.base_url', '') ?>/assets/js/telemetry/plugins/visibility.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= config('app.base_url', '') ?>/assets/js/telemetry/plugins/section-dwell.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= config('app.base_url', '') ?>/assets/js/telemetry/plugins/activity-ping.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= config('app.base_url', '') ?>/assets/js/telemetry/init.js"></script>


</head>

<body class="bg-black min-h-screen flex flex-col">
    <?php include ROOT_PATH . '/src/views/partials/header.php'; ?>

    <!-- Main content -->
    <div class="relative flex-grow flex justify-center gap-8">

        <!-- Hlavní obsah, vždy max-width 7xl -->
        <main class="w-full">
            <?= csrf_protect_forms((string)($data['content'] ?? '')) ?>
        </main>


    </div>
    <!-- Footer -->

    <?php include ROOT_PATH . '/src/views/partials/footer.php'; ?>
    <?php include ROOT_PATH . '/src/views/cookie/banner.php'; ?>

    <!-- Tvůj JS -->
    <script src="<?= config('app.base_url', '') ?>/assets/js/cookies.js" defer></script>
    <script src="<?= config('app.base_url', '') ?>/assets/js/ui/toast.js" defer></script>
    <script src="<?= config('app.base_url', '') ?>/assets/js/recaptcha.js" defer></script>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= config('recaptcha.site_key', '') ?>" async defer></script>



    <?php

    use Helpers\Toast;

$toasts = Toast::all();
?>
    <?php if ($toasts): ?>
        <script>
            window.__toastQueue = window.__toastQueue || [];
            window.__toastQueue.push(...<?= json_encode(
                $toasts,
                JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
            ) ?>);
        </script>
    <?php endif; ?>

</body>

</html>
