<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?= __('edit_translation', [], 'admin') ?></h1>
        <a href="<?= BASE_URL ?>/admin/translations" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <?= __('back_to_list', [], 'admin') ?>
        </a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-100 border-l-4 border-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-500 text-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-700 p-4 mb-6" role="alert">
            <p><?= $_SESSION['flash_message'] ?></p>
        </div>
        <?php
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>

    <div class="bg-white rounded shadow p-6">
        <div class="mb-6">
            <div class="font-semibold text-gray-700"><?= __('key', [], 'admin') ?>:</div>
            <div class="mt-1 p-2 bg-gray-50 rounded"><?= htmlspecialchars($key) ?></div>
        </div>

        <div class="mb-6">
            <div class="font-semibold text-gray-700"><?= __('category', [], 'admin') ?>:</div>
            <div class="mt-1 p-2 bg-gray-50 rounded"><?= htmlspecialchars($category) ?></div>
        </div>

        <form action="<?= BASE_URL ?>/admin/translations/edit/process" method="post">
            <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">

            <div class="space-y-6">
                <?php foreach ($languages as $lang): ?>
                    <div>
                        <label for="translation_<?= $lang ?>" class="block text-sm font-medium text-gray-700">
                            <?= __('translation', [], 'admin') ?> (<?= strtoupper($lang) ?>)
                        </label>
                        <textarea 
                            name="translations[<?= $lang ?>]" 
                            id="translation_<?= $lang ?>" 
                            rows="3" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        ><?= htmlspecialchars($translations[$lang] ?? '') ?></textarea>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <?= __('save_changes', [], 'admin') ?>
                </button>
            </div>
        </form>
    </div>
</div>