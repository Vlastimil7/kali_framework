<?php
// src/views/admin/locations/form.php
// Admin formulář pro přidání/úpravu místa doručení
?>

<div class="max-w-full mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="bg-blue-600 px-6 py-4 flex justify-between items-center mb-6 rounded-t-lg">
            <h1 class="text-2xl font-bold text-white"><?= $action === 'add' ? 'Přidat nové místo doručení' : 'Upravit místo doručení' ?></h1>
            <div class="flex space-x-2">
                <a href="<?= BASE_URL ?>/admin/dashboard" class="bg-white text-blue-600 px-4 py-2 rounded shadow-sm hover:bg-blue-50 transition-colors">
                    Zpět na dashboard
                </a>
                <a href="<?= BASE_URL ?>/admin/locations" class="bg-gray-200 text-gray-700 px-4 py-2 rounded shadow-sm hover:bg-gray-300 transition-colors">
                    Zpět na seznam
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-100 border-l-4 border-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-500 text-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-700 p-4 mb-4 mx-6" role="alert">
                <p><?= $_SESSION['flash_message'] ?></p>
            </div>
            <?php
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>

        <div class="p-6">
            <form action="<?= BASE_URL ?>/admin/locations/<?= $action === 'add' ? 'create' : 'update/' . $location['id'] ?>" method="post" class="space-y-6">
                <!-- Název místa -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Název místa *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($location['name'] ?? '') ?>" class="block w-full mt-1 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <p class="mt-1 text-xs text-gray-500">Např. "Budova A - Jídelna", "Filozofická fakulta"</p>
                </div>
                
                <!-- Adresa místa -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresa *</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($location['address'] ?? '') ?>" class="block w-full mt-1 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    <p class="mt-1 text-xs text-gray-500">Přesná adresa místa doručení</p>
                </div>
                
                <!-- Popis místa -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Popis</label>
                    <textarea id="description" name="description" rows="3" class="block w-full mt-1 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?= htmlspecialchars($location['description'] ?? '') ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">Další detaily pro zákazníky (např. "Vstup přes hlavní recepci", "Doručení možné mezi 8:00 - 17:00")</p>
                </div>
                
                <!-- Status aktivní/neaktivní -->
                <div>
                    <div class="flex items-center">
                        <input id="is_active" name="is_active" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= (isset($location['is_active']) && (int)$location['is_active'] === 1) ? 'checked' : '' ?>>
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Místo doručení je aktivní
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 ml-6">Neaktivní místa nebudou zobrazena zákazníkům při vytváření objednávky</p>
                </div>
                
                <!-- Tlačítka -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="<?= BASE_URL ?>/admin/locations" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Zrušit
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <?= $action === 'add' ? 'Přidat místo' : 'Uložit změny' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>