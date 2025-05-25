<?php
// src/views/dashboard/index.php
// Přehledová stránka pro běžného uživatele po přihlášení

// Zajištění, že uživatel je přihlášen
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
?>

<div class="max-w-full mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Můj přehled</h1>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-100 border-l-4 border-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-500 text-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-700 p-4 mb-4" role="alert">
                <p><?= $_SESSION['flash_message'] ?></p>
            </div>
            <?php
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>

        <div class="p-6">
            <!-- Uvítací sekce a přehled -->
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Vítejte, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
                <p class="text-gray-600">Zde najdete přehled vašeho účtu a objednávek.</p>
            </div>

            <!-- Kreditní karta a rychlé akce -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <!-- Kreditní karta -->
                <div class="col-span-1 md:col-span-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h3 class="text-lg font-medium opacity-80">Váš kredit</h3>
                            <p class="text-3xl font-bold"><?= number_format($user['credit_balance'], 2, ',', ' ') ?> Kč</p>
                        </div>
                        <div class="p-2 bg-white bg-opacity-20 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm opacity-80"><?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?></p>
                        </div>
                        <div>
                            <a href="<?= BASE_URL ?>/credit/add" class="px-4 py-2 bg-white text-blue-600 rounded-md hover:bg-opacity-90 transition-colors font-medium text-sm">Dobít kredit</a>
                        </div>
                    </div>
                </div>

                <!-- Rychlé akce -->
                <div class="bg-gray-50 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Rychlé akce</h3>
                    <div class="space-y-3">


                        <a href="<?= BASE_URL ?>/menu" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                            <div class="p-2 bg-blue-500 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-700 group-hover:text-gray-900">Prohlížet menu</span>
                        </a>

                        <a href="<?= BASE_URL ?>/profile" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group">
                            <div class="p-2 bg-purple-500 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-700 group-hover:text-gray-900">Upravit profil</span>
                        </a>
                    </div>
                </div>
            </div>


            <!-- Sekce s menu v tabulce -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Aktuální menu</h3>
                    <a href="<?= BASE_URL ?>/menu" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Zobrazit celé menu →</a>
                </div>

                <?php if (isset($categories) && isset($menuItems) && !empty($categories)): ?>
                    <div class="bg-white shadow overflow-hidden rounded-lg">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Název</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategorie</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Popis</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cena</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Akce</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                    $itemCounter = 0;
                                    foreach ($categories as $category):
                                        if (!isset($menuItems[$category['id']]) || empty($menuItems[$category['id']])) continue;

                                        foreach ($menuItems[$category['id']] as $item):
                                            $itemCounter++;
                                            if ($itemCounter > 10) break 2; // Zobrazit max 10 položek
                                    ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <?php if (!empty($item['image_path'])): ?>
                                                            <div class="flex-shrink-0 h-10 w-10 mr-3">
                                                                <img class="h-10 w-10 rounded-full object-cover" src="<?= BASE_URL . $item['image_path'] ?>" alt="<?= htmlspecialchars($item['meal_name']) ?>">
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <a href="<?= BASE_URL ?>/menu/detail/<?= $item['id'] ?>" class="hover:text-green-600">
                                                                <?= htmlspecialchars($item['meal_name']) ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        <?= htmlspecialchars($category['name']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-500 max-w-xs truncate">
                                                        <?= htmlspecialchars($item['meal_description']) ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-green-600">
                                                        Od <?= number_format($item['meal_price'], 0) ?> Kč
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="<?= BASE_URL ?>/menu/detail/<?= $item['id'] ?>?source=dashboard" class="text-blue-600 hover:text-blue-900 mr-3">
                                                        Detail
                                                    </a>
                                                    <!-- Rychlé přidání do košíku -->
                                                    <form action="<?= BASE_URL ?>/cart/add" method="post" class="inline-block">
                                                        <input type="hidden" name="meal_id" value="<?= $item['meal_id'] ?>">
                                                        <?php if (isset($mealSizes) && !empty($mealSizes)): ?>
                                                            <input type="hidden" name="size_id" value="<?= $mealSizes[0]['id'] ?>">
                                                        <?php else: ?>
                                                            <input type="hidden" name="size_id" value="1"> <!-- Výchozí hodnota pro případ, že velikosti nejsou k dispozici -->
                                                        <?php endif; ?>
                                                        <input type="hidden" name="quantity" value="1">
                                                        <input type="hidden" name="source" value="dashboard">
                                                        <button type="submit" class="text-green-600 hover:text-green-900 font-medium">
                                                            Přidat do košíku
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                    <?php
                                        endforeach;
                                        if ($itemCounter > 10) break;
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <p class="text-gray-700">Menu není momentálně k dispozici.</p>
                        <a href="<?= BASE_URL ?>/menu" class="mt-2 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Prohlédnout nabídku
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>