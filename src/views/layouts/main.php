<!DOCTYPE html>
<html lang="<?= lang()->getCurrentLanguage() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/logo/fav/vk-dev.ico">
    <?php
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    $stylePath     = __DIR__ . '/../../public/assets/css/style.css';
    $ownStylePath  = __DIR__ . '/../../public/assets/css/ownStyles.css';
    $styleVersion    = file_exists($stylePath) ? filemtime($stylePath) : time();
    $ownStyleVersion = file_exists($ownStylePath) ? filemtime($ownStylePath) : time();
    $currentUrl = rtrim(SITE_URL, '/') . ($_SERVER['REQUEST_URI'] ?? '');

    // SEO defaulty
    $seoTitle = $data['title'] ?? '';
    $seoDesc  = $data['description'] ?? '';
    $seoKw    = $data['keywords'] ?? 'vývoj webových aplikací, tvorba webových stránek, web na míru, firemní weby, vývoj informačních systémů, PHP vývojář, fullstack developer, REST API vývoj, systémové integrace, digitální řešení pro firmy, web developer Hradec Králové, programátor na míru, zakázkový software, vývoj e-commerce, webové portály, agilní vývoj, moderní webové technologie, optimalizace výkonu, bezpečnost webu, UX/UI design, správa a údržba webů';
    $ogImage  = $data['og_image'] ?? (rtrim(SITE_URL, '/') . '/assets/images/logo/vk-dev.png');
    ?>
    <title><?= htmlspecialchars($seoTitle, ENT_QUOTES) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDesc, ENT_QUOTES) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl, ENT_QUOTES) ?>">
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
    <meta property="og:locale" content="cs_CZ">


    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= $styleVersion ?>" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/ownStyles.css?v=<?= $ownStyleVersion ?>" rel="stylesheet">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "",
            "medicalSpecialty": "",
            "url": "<?= SITE_URL ?>",
            "logo": "<?= SITE_URL ?>/assets/images/logo/logo.jpeg",
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
        const BASE_URL = "<?= rtrim(BASE_URL, '/') ?>";
        const API_BASE = BASE_URL + "/api/v1/chat";
        const STATUS_URL = API_BASE + "/status";
        const CHAT_URL = API_BASE;
    </script>


    <script>
        window.APP_BASE_URL = "<?= rtrim(BASE_URL, '/') ?>";
        window.RECAPTCHA_SITE_KEY = '<?= RECAPTCHA_SITE_KEY ?>';
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
    <script type="text/plain" data-consent="analytics" data-src="<?= BASE_URL ?>/assets/js/telemetry/telemetry.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= BASE_URL ?>/assets/js/telemetry/plugins/clicks.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= BASE_URL ?>/assets/js/telemetry/plugins/scroll.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= BASE_URL ?>/assets/js/telemetry/plugins/visibility.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= BASE_URL ?>/assets/js/telemetry/plugins/section-dwell.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= BASE_URL ?>/assets/js/telemetry/plugins/activity-ping.js"></script>
    <script type="text/plain" data-consent="analytics" data-src="<?= BASE_URL ?>/assets/js/telemetry/init.js"></script>


</head>

<body class="bg-black min-h-screen flex flex-col">
    <?php include "../src/views/partials/header.php"; ?>

    <!-- Main content -->
    <div class="relative flex-grow flex justify-center gap-8">

        <!-- Hlavní obsah, vždy max-width 7xl -->
        <main class="w-full">
            <?= $data['content'] ?? '' ?>
        </main>


    </div>
    <!-- Footer -->

    <?php include "../src/views/partials/footer.php"; ?>
    <?php include "../src/views/cookie/banner.php"; ?>

    <!-- Tvůj JS -->
    <script src="<?= BASE_URL ?>/assets/js/cookies.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/ui/toast.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/recaptcha.js" defer></script>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= RECAPTCHA_SITE_KEY ?>" async defer></script>



    <?php

    use Helpers\Flash;

    $toast = Flash::get('toast');
    ?>
    <?php if ($toast): ?>
        <script>
            window.__toastQueue = window.__toastQueue || [];
            window.__toastQueue.push(<?= json_encode($toast) ?>);
        </script>
    <?php endif; ?>

</body>

</html>