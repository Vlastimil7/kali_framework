<div class="max-w-full mx-auto ">
    <div class="bg-blue-600 px-6 py-4 flex justify-between items-center  mb-6 rounded-t-lg">
        <h1 class="text-2xl font-bold text-white">Detail objednávky #<?= $order['id'] ?></h1>
        <div class="flex space-x-2">
            <a href="<?= BASE_URL ?>/orders" class="inline-flex items-center px-4 py-2 bg-white hover:bg-blue-50 text-blue-600 text-sm  rounded-md transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Zpět na seznam
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informace o objednávce -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="p-6 bg-gray-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Informace o objednávce</h2>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium  text-gray-500">Stav objednávky</h3>
                            <div class="mt-1">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php
                                    switch ($order['status']) {
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'confirmed':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'preparing':
                                            echo 'bg-purple-100 text-purple-800';
                                            break;
                                        case 'delivering':
                                            echo 'bg-indigo-100 text-indigo-800';
                                            break;
                                        case 'completed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'cancelled':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php
                                    switch ($order['status']) {
                                        case 'pending':
                                            echo 'Čeká na potvrzení';
                                            break;
                                        case 'confirmed':
                                            echo 'Potvrzeno';
                                            break;
                                        case 'preparing':
                                            echo 'Příprava';
                                            break;
                                        case 'delivering':
                                            echo 'Doručování';
                                            break;
                                        case 'completed':
                                            echo 'Dokončeno';
                                            break;
                                        case 'cancelled':
                                            echo 'Zrušeno';
                                            break;
                                        default:
                                            echo $order['status'];
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Číslo objednávky</h3>
                            <p class="mt-1 text-sm text-gray-900">#<?= $order['id'] ?></p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Datum vytvoření</h3>
                            <p class="mt-1 text-sm text-gray-900"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Datum doručení</h3>
                            <p class="mt-1 text-sm text-gray-900"><?= date('d.m.Y', strtotime($order['serving_date'])) ?></p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Celková částka</h3>
                            <p class="mt-1 text-sm font-medium text-gray-900"><?= number_format($order['total_price'], 0) ?> Kč</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Zákazník</h3>
                            <p class="mt-1 text-sm text-gray-900">
                                <?= htmlspecialchars($order['user_name'] . ' ' . $order['user_surname']) ?><br>
                                <span class="text-gray-500"><?= htmlspecialchars($order['user_email']) ?></span>
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Doručovací adresa</h3>
                            <div class="mt-1 p-3 bg-gray-50 rounded-md text-sm text-gray-700">
                                <p class="mt-1 text-sm text-gray-900">
                                    <?php
                                    if ($order['location_id'] === NULL) {
                                        echo 'Doručovací adresa nebyla specifikována';
                                    } else {
                                        echo htmlspecialchars($order['location_name'] ?? 'Nespecifikováno') . '<br>';
                                        echo htmlspecialchars($order['location_address'] ?? 'Nespecifikováno') . '<br>';
                                        echo htmlspecialchars($order['location_description'] ?? '') . '<br>';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>

                    </div>

                    <?php if (!empty($order['notes'])): ?>
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500">Poznámka k objednávce</h3>
                            <div class="mt-1 p-3 bg-gray-50 rounded-md text-sm text-gray-700">
                                <?= nl2br(htmlspecialchars($order['notes'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Položky objednávky -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 bg-gray-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Položky objednávky</h2>
                </div>

                <ul class="divide-y divide-gray-200">
                    <?php foreach ($order['items'] as $item): ?>
                        <li class="p-6">
                            <div class="flex items-center space-x-4">
                                <?php if (!empty($item['image_path'])): ?>
                                    <div class="flex-shrink-0 h-16 w-16 bg-gray-100 rounded-md overflow-hidden">
                                        <img src="<?= BASE_URL . $item['image_path'] ?>" alt="<?= htmlspecialchars($item['meal_name']) ?>" class="h-full w-full object-cover">
                                    </div>
                                <?php else: ?>
                                    <div class="flex-shrink-0 h-16 w-16 bg-gray-100 rounded-md flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        <?= htmlspecialchars($item['meal_name']) ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?= htmlspecialchars(ucfirst($item['size_name'])) ?> porce
                                    </p>
                                    <?php if (!empty($item['meal_description'])): ?>
                                        <p class="mt-1 text-sm text-gray-500 line-clamp-2">
                                            <?= htmlspecialchars($item['meal_description']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= number_format($item['price_per_item'], 0) ?> Kč
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Množství: <?= $item['quantity'] ?>
                                    </p>
                                    <p class="text-sm font-medium text-gray-900 mt-1">
                                        Celkem: <?= number_format($item['price_per_item'] * $item['quantity'], 0) ?> Kč
                                    </p>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Stav objednávky a akce -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden sticky top-6">
                <div class="p-6 bg-gray-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Stav objednávky</h2>
                </div>

                <div class="p-6">
                    <div class="space-y-8">
                        <div class="relative pb-8">
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <div class="relative flex items-start">
                                <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1 ml-4">
                                    <p class="text-sm font-medium text-gray-900">Objednávka vytvořena</p>
                                    <p class="text-sm text-gray-500"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="relative pb-8">
                            <?php if (in_array($order['status'], ['confirmed', 'preparing', 'delivering', 'completed'])): ?>
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <?php endif; ?>
                            <div class="relative flex items-start">
                                <?php if (in_array($order['status'], ['confirmed', 'preparing', 'delivering', 'completed'])): ?>
                                    <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center ring-8 ring-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0 flex-1 ml-4">
                                    <p class="text-sm font-medium text-gray-900">Objednávka potvrzena</p>
                                    <?php if (in_array($order['status'], ['confirmed', 'preparing', 'delivering', 'completed'])): ?>
                                        <p class="text-sm text-gray-500">Potvrzeno administrátorem</p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500">Čeká na potvrzení</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="relative pb-8">
                            <?php if (in_array($order['status'], ['preparing', 'delivering', 'completed'])): ?>
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <?php endif; ?>
                            <div class="relative flex items-start">
                                <?php if (in_array($order['status'], ['preparing', 'delivering', 'completed'])): ?>
                                    <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center ring-8 ring-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0 flex-1 ml-4">
                                    <p class="text-sm font-medium text-gray-900">Příprava</p>
                                    <?php if (in_array($order['status'], ['preparing', 'delivering', 'completed'])): ?>
                                        <p class="text-sm text-gray-500">Vaše jídlo se připravuje</p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500">Čeká na přípravu</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="relative pb-8">
                            <?php if (in_array($order['status'], ['delivering', 'completed'])): ?>
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <?php endif; ?>
                            <div class="relative flex items-start">
                                <?php if (in_array($order['status'], ['delivering', 'completed'])): ?>
                                    <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center ring-8 ring-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0 flex-1 ml-4">
                                    <p class="text-sm font-medium text-gray-900">Doručování</p>
                                    <?php if (in_array($order['status'], ['delivering', 'completed'])): ?>
                                        <p class="text-sm text-gray-500">Vaše objednávka je na cestě</p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500">Čeká na doručení</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="relative flex items-start">
                                <?php if ($order['status'] === 'completed'): ?>
                                    <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                <?php elseif ($order['status'] === 'cancelled'): ?>
                                    <div class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center ring-8 ring-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0 flex-1 ml-4">
                                    <?php if ($order['status'] === 'cancelled'): ?>
                                        <p class="text-sm font-medium text-red-600">Objednávka zrušena</p>
                                        <p class="text-sm text-gray-500">Vaše objednávka byla zrušena</p>
                                    <?php else: ?>
                                        <p class="text-sm font-medium text-gray-900">Dokončeno</p>
                                        <?php if ($order['status'] === 'completed'): ?>
                                            <p class="text-sm text-gray-500">Vaše objednávka byla úspěšně doručena</p>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-500">Čeká na dokončení</p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($order['status'] === 'pending' && $_SESSION['user_id'] === $order['user_id']): ?>
                    <div class="p-6 bg-gray-50 border-t">
                        <form action="<?= BASE_URL ?>/orders/cancel/<?= $order['id'] ?>" method="post" onsubmit="return confirm('Opravdu chcete zrušit tuto objednávku?');">
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                Zrušit objednávku
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>