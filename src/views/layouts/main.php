<?php
$title = (string) ($data['title'] ?? config('app.name'));
$description = (string) ($data['description'] ?? '');
?>
<!doctype html>
<html lang="<?= htmlspecialchars(lang()->getCurrentLanguage(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
    <?php if (!empty($data['noindex'])): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(rtrim((string) config('app.base_url', ''), '/') . '/assets/css/style.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body data-toast-close="<?= htmlspecialchars(__('toast_close'), ENT_QUOTES, 'UTF-8') ?>" data-toast-region="<?= htmlspecialchars(__('toast_region'), ENT_QUOTES, 'UTF-8') ?>">
    <?php require ROOT_PATH . '/src/views/partials/header.php'; ?>
    <main class="container"><?= $data['content'] ?? '' ?></main>
    <?php require ROOT_PATH . '/src/views/partials/footer.php'; ?>
    <?php require ROOT_PATH . '/src/views/cookie/banner.php'; ?>
    <script type="application/json" id="server-toasts"><?= json_encode(\Helpers\Toast::all(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
    <script src="<?= htmlspecialchars(rtrim((string) config('app.base_url', ''), '/') . '/assets/js/ui/toast.js', ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
