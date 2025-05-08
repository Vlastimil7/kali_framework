<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?= __('add_translation', [], 'admin') ?></h1>
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
        <form action="<?= BASE_URL ?>/admin/translations/add/process" method="post" class="space-y-6">
            <div>
                <label for="key" class="block text-sm font-medium text-gray-700"><?= __('key', [], 'admin') ?></label>
                <input type="text" name="key" id="key" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="<?= __('enter_translation_key', [], 'admin') ?>">
                <p class="mt-1 text-sm text-gray-500"><?= __('key_format_hint', [], 'admin') ?></p>
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700"><?= __('category', [], 'admin') ?></label>
                <input type="text" name="category" id="category" value="general"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="<?= __('enter_category', [], 'admin') ?>">
                <p class="mt-1 text-sm text-gray-500"><?= __('category_format_hint', [], 'admin') ?></p>
            </div>

            <div class="space-y-6 pt-4 border-t border-gray-200">
                <h2 class="text-lg font-medium text-gray-900"><?= __('translations', [], 'admin') ?></h2>
                
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
                        ></textarea>
                    </div>
                <?php endforeach; ?>
            </div>

            <div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <?= __('add_translation', [], 'admin') ?>
                </button>
            </div>
        </form>
    </div>
</div>