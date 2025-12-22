<!DOCTYPE html>
<html lang="<?= lang()->getCurrentLanguage() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/logo/favicon.ico">
    <?php
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    $stylePath     = __DIR__ . '/../../public/assets/css/style.css';
    $ownStylePath  = __DIR__ . '/../../public/assets/css/ownStyles.css';
    $styleVersion    = file_exists($stylePath) ? filemtime($stylePath) : time();
    $ownStyleVersion = file_exists($ownStylePath) ? filemtime($ownStylePath) : time();
    $hideSidebar = $data['show_sidebar'] ?? false;
    $currentUrl = rtrim(SITE_URL, '/') . ($_SERVER['REQUEST_URI'] ?? '');

    // SEO defaulty
    $seoTitle = $data['title'] ?? 'MUDr. Ziad Albahri, Ph.D. – Midobarbershop.cztr Jaroměř';
    $seoDesc  = $data['description'] ?? 'Praktický lékař pro děti a dorost – ordinace Midobarbershop.cz s.r.o. a Midobarbershop.cz AZ s.r.o. v Jaroměři (okolí Náchoda).';
    $seoKw    = $data['keywords'] ?? 'Midobarbershop.cztr Jaroměř, Midobarbershop.cztr Náchod, dětský lékař Jaroměř, praktický lékař pro děti a dorost, MUDr. Ziad Albahri, Midobarbershop.cz s.r.o., Midobarbershop.cz AZ s.r.o., očkování, preventivní prohlídky';
    $ogImage  = $data['og_image'] ?? (rtrim(SITE_URL, '/') . '/assets/images/logo/mido_barbershop_logo.png'); // dej si tam reálný obrázek 1200x630
    ?>
    <title><?= htmlspecialchars($seoTitle, ENT_QUOTES) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDesc, ENT_QUOTES) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl, ENT_QUOTES) ?>">
    <meta name="robots" content="<?= !empty($data['noindex']) ? 'noindex, nofollow' : 'index, follow' ?>">

    <meta name="keywords" content="<?= htmlspecialchars($seoKw, ENT_QUOTES) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="MUDr. Ziad Albahri – Midobarbershop.cztr">
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
            "@type": "MedicalOrganization",
            "name": "MUDr. Ziad Albahri – Midobarbershop.cztr",
            "medicalSpecialty": "Midobarbershop.cztrics",
            "url": "<?= SITE_URL ?>",
            "logo": "<?= SITE_URL ?>/assets/images/logo/logo.png",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Kostelní 39",
                "addressLocality": "Jaroměř",
                "postalCode": "55101",
                "addressCountry": "CZ"
            },
            "areaServed": ["Jaroměř", "Náchod"]
        }
    </script>


    <script>
        const BASE_URL = "<?= BASE_URL ?>";
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

        function getConsentFromCookie() {
            try {
                const row = document.cookie.split('; ').find(r => r.startsWith('cookie_consent='));
                if (!row) return {};
                const raw = decodeURIComponent(row.split('=')[1]);
                let parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object' && parsed.v) {
                    parsed = JSON.parse(parsed.v);
                }
                return parsed || {};
            } catch (e) {
                return {};
            }
        }

        function enableConsentTaggedItems(consent) {
            document.querySelectorAll('script[type="text/plain"][data-consent]').forEach(tag => {
                const type = tag.getAttribute('data-consent');
                if (!consent[type]) return;

                if (tag.hasAttribute('data-src')) {
                    const s = document.createElement('script');
                    s.async = true;
                    s.src = tag.getAttribute('data-src');
                    document.head.appendChild(s);
                } else if (tag.textContent.trim()) {
                    const s = document.createElement('script');
                    s.text = tag.textContent;
                    document.head.appendChild(s);
                }
            });

            document.querySelectorAll('iframe[data-consent][data-src]').forEach(ifr => {
                const type = ifr.getAttribute('data-consent');
                if (consent[type]) {
                    ifr.setAttribute('src', ifr.getAttribute('data-src'));
                    ifr.removeAttribute('data-src');
                }
            });
        }

        // Globální hook pro FE i server (použij po uložení souhlasu)
        window.__applyConsentEverywhere = function(newConsent) {
            applyGtagConsent(newConsent);
            enableConsentTaggedItems(newConsent);
        };
    </script>

    <!-- GA připravené, ale neaktivní do souhlasu -->
    <script type="text/plain" data-consent="analytics" data-src="https://www.googletagmanager.com/gtag/js?id=G-M95Q53XWQX"></script>
    <script type="text/plain" data-consent="analytics">
        gtag('js', new Date());
      gtag('config', 'G-M95Q53XWQX');
    </script>
</head>

<body class="bg-[#ffffff0d] min-h-screen flex flex-col">
    <?php include "../src/views/partials/header.php"; ?>

    <!-- Main content -->
    <div
        class="relative flex-grow px-4 lg:px-8 py-16 lg:py-20
         flex justify-center gap-8
        ">
         <!-- bg-center bg-cover bg-no-repeat"
        style="background-image: url('https://www.midobarbershop.com/wp-content/uploads/2019/08/Beard-Trimming.jpg'); -->

        <!-- overlay -->
        <!-- <div class="absolute inset-0 bg-black/20 pointer-events-none"></div> -->

        <!-- Sidebar (mobil + desktop) -->
        <?php if ($hideSidebar): ?>
            <?php include "../src/views/partials/sidebar.php"; ?>
        <?php endif; ?>

        <!-- Hlavní obsah, vždy max-width 7xl -->
        <main class="w-full max-w-7xl">
            <?= $data['content'] ?? '' ?>
        </main>
    </div>



    <?php include "../src/views/partials/footer.php"; ?>
    <?php include "../src/views/cookie/banner.php"; ?>

    <!-- Tvůj JS -->
    <script src="<?= BASE_URL ?>/assets/js/cookies.js"></script>

    <!-- Inicializace po načtení: promítni souhlas a aktivuj značky -->
    <script>
        (function() {
            const consent = getConsentFromCookie();
            if (typeof applyGtagConsent === 'function') applyGtagConsent(consent);
            // Když je prázdný objekt (třeba po chybě), loader nic neaktivuje => bezpečné
            if (consent && typeof consent === 'object') {
                if (typeof enableConsentTaggedItems === 'function') enableConsentTaggedItems(consent);
            }
        })();
    </script>
</body>

</html>