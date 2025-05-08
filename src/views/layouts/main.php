<!DOCTYPE html>
<html lang="<?= lang()->getCurrentLanguage() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?? 'SuperKrabicky.cz' ?></title>
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php include "../src/views/partials/header.php"; ?>

    <main class="flex-grow container mx-auto px-4 py-8">
        <?= $data['content'] ?? '' ?>
    </main>

    <?php include "../src/views/partials/footer.php"; ?>
</body>
</html>