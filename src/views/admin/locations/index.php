<?php
// src/views/admin/locations/index.php
// Admin správa míst doručení
?>

<div class="max-w-full mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="bg-blue-600 px-6 py-4 flex justify-between items-center mb-6 rounded-t-lg">
            <h1 class="text-2xl font-bold text-white">Správa míst doručení</h1>
            <div class="flex space-x-2">
                <a href="<?= BASE_URL ?>/admin/dashboard" class="bg-white text-blue-600 px-4 py-2 rounded shadow-sm hover:bg-blue-50 transition-colors">
                    Zpět na dashboard
                </a>
                <a href="<?= BASE_URL ?>/admin/locations/add" class="bg-green-500 text-white px-4 py-2 rounded shadow-sm hover:bg-green-600 transition-colors">
                    Přidat nové místo
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
            <!-- Tabulka míst doručení -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Název</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresa</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stav</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akce</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($locations)): ?>
                            <?php foreach ($locations as $location): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($location['name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($location['address']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (isset($location['is_active']) && (int)$location['is_active'] === 1): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Aktivní
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Neaktivní
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?= BASE_URL ?>/admin/locations/edit/<?= $location['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-4">Upravit</a>
                                        <form action="<?= BASE_URL ?>/admin/locations/delete/<?= $location['id'] ?>" method="post" class="inline" onsubmit="return confirm('Opravdu chcete odstranit toto místo doručení?');">
                                            <button type="submit" class="text-red-600 hover:text-red-900">Odstranit</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Zatím nebyla přidána žádná místa doručení.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>