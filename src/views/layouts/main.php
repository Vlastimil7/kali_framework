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
    ?>
    <title><?= $data['title'] ?? 'kali-framework.cz' ?></title>
    <meta name="description" content="<?= $data['description'] ?? 'kali-framework.cz' ?>">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">

    <!-- Definování BASE_URL pro JavaScript -->
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <?php
    // Zkontroluj souhlas s cookies
    $cookieConsent = isset($_COOKIE['cookie_consent']) ? json_decode($_COOKIE['cookie_consent'], true) : null;
    $analyticsConsent = $cookieConsent && isset($cookieConsent['analytics']) && $cookieConsent['analytics'] === true;
    ?>

    <?php if ($analyticsConsent): ?>
        <!-- Google Analytics - načte se pouze se souhlasem -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-M95Q53XWQX"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', 'G-M95Q53XWQX');
            console.log('Google Analytics načten - souhlas udělen');
        </script>
    <?php else: ?>
        <script>
            console.log('Google Analytics nenačten - chybí souhlas s analytickými cookies');
            // Příprava pro případné dynamické načtení
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            window.gtag = gtag;
        </script>
    <?php endif; ?>



</head>

<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Header na celou šířku -->
    <?php include "../src/views/partials/header.php"; ?>

    <!-- Main content -->
   <main class="flex-grow container mx-auto px-4 py-8">
        <?= $data['content'] ?? '' ?>
    </main>

    <!-- Footer na celou šířku -->
    <?php include "../src/views/partials/footer.php"; ?>

    <!-- Cookie Banner -->
    <?php include "../src/views/cookie/banner.php"; ?>

    <!-- Cookie JS -->
    <script src="<?= BASE_URL ?>/assets/js/cookies.js"></script>

</body>

</html>