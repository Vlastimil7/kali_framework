<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?= __('translations_management', [], 'admin') ?></h1>
        <div class="flex space-x-2">
            <a href="<?= BASE_URL ?>/admin/translations/add" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <?= __('add_translation', [], 'admin') ?>
            </a>
            <a href="<?= BASE_URL ?>/admin/translations/import" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                <?= __('import_translations', [], 'admin') ?>
            </a>
            <a href="<?= BASE_URL ?>/admin/translations/export" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                <?= __('export_translations', [], 'admin') ?>
            </a>
        </div>
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

    <!-- Filtry -->
    <div class="bg-white p-4 rounded shadow mb-6">
        <form action="<?= BASE_URL ?>/admin/translations" method="get" class="flex flex-wrap items-end space-x-4">
            <div>
                <label for="lang" class="block text-sm font-medium text-gray-700 mb-1"><?= __('language', [], 'admin') ?></label>
                <select name="lang" id="lang" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?= $lang ?>" <?= $selectedLang === $lang ? 'selected' : '' ?>><?= strtoupper($lang) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1"><?= __('category', [], 'admin') ?></label>
                <select name="category" id="category" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value=""><?= __('all_categories', [], 'admin') ?></option>
                    <?php
                    // Zobrazit seznam všech kategorií
                    $allCategories = [];
                    foreach ($keys as $category => $keyList) {
                        if (!in_array($category, $allCategories)) {
                            $allCategories[] = $category;
                        }
                    }
                    sort($allCategories);
                    foreach ($allCategories as $cat):
                    ?>
                        <option value="<?= $cat ?>" <?= $selectedCategory === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <?= __('filter', [], 'admin') ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Tabulka s překlady -->
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <?= __('key', [], 'admin') ?>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <?= __('category', [], 'admin') ?>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <?= __('translation', [], 'admin') ?> (<?= strtoupper($selectedLang) ?>)
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <?= __('actions', [], 'admin') ?>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $hasTranslations = false;
                foreach ($keys as $category => $keyList):
                    if ($selectedCategory && $category !== $selectedCategory) continue;

                    foreach ($keyList as $key):
                        $hasTranslations = true;
                        $translation = isset($translations[$selectedLang][$category][$key])
                            ? $translations[$selectedLang][$category][$key]
                            : '';
                ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($key) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($category) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-md truncate">
                                <?= htmlspecialchars($translation) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?= BASE_URL ?>/admin/translations/edit/<?= rawurlencode($key) ?>/<?= rawurlencode($category) ?>" class="text-blue-600 hover:text-blue-900">
                                    <?= __('edit', [], 'admin') ?>
                                </a>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                endforeach;

                if (!$hasTranslations):
                    ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                            <?= __('no_translations_found', [], 'admin') ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>