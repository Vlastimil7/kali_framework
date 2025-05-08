<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?= __('import_translations', [], 'admin') ?></h1>
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
        <div class="mb-6 bg-blue-50 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800"><?= __('import_instructions', [], 'admin') ?></h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p><?= __('import_file_format', [], 'admin') ?></p>
                        <pre class="mt-2 bg-gray-800 text-white p-4 rounded overflow-x-auto">
{
  "cs": {
    "general": {
      "welcome": "Vítejte",
      "save": "Uložit"
    },
    "admin": {
      "dashboard": "Nástěnka"
    }
  },
  "en": {
    "general": {
      "welcome": "Welcome",
      "save": "Save"
    },
    "admin": {
      "dashboard": "Dashboard"
    }
  }
}
                        </pre>
                    </div>
                </div>
            </div>
        </div>

        <form action="<?= BASE_URL ?>/admin/translations/import/process" method="post" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="import_file" class="block text-sm font-medium text-gray-700">
                    <?= __('import_file', [], 'admin') ?>
                </label>
                <input type="file" name="import_file" id="import_file" required accept=".json"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-sm text-gray-500"><?= __('import_file_help', [], 'admin') ?></p>
            </div>

            <div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <?= __('import', [], 'admin') ?>
                </button>
            </div>
        </form>
    </div>
</div>