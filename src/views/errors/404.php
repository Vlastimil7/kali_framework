<div class="flex items-center justify-center min-h-[400px]">
    <div class="text-center p-8">
        <h1 class="text-6xl font-bold text-white mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-white mb-4"><?= htmlspecialchars(__('page_not_found', [], '404'), ENT_QUOTES) ?></h2>
        <p class="text-white mb-8"><?= htmlspecialchars(__('description', [], '404'), ENT_QUOTES) ?></p>
        <a href="<?= htmlspecialchars(locale_url(), ENT_QUOTES) ?>"
           class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200">
            <?= htmlspecialchars(__('back_to_home', [], '404'), ENT_QUOTES) ?>
        </a>
    </div>
</div>
