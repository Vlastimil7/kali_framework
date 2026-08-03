<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-white mb-6">Váš košík</h1>

    <?php if (empty($cart['items'])): ?>
        <div class="bg-[#ffffffe6] rounded-lg shadow-lg p-8 text-center">
            <div class="mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Váš košík je prázdný</h2>
            <p class="text-gray-600 mb-6">Vyberte si dárkový voucher a přidejte ho do košíku.</p>
            <a href="<?= locale_url('vouchers') ?>" class="inline-block bg-gray-900 hover:bg-gray-800 text-white font-medium py-3 px-6 rounded-lg transition duration-200">
                Zobrazit vouchery
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Položky košíku -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b">
                        <h2 class="text-xl font-semibold text-gray-800">Položky v košíku (<?= (int)$cart['count'] ?>)</h2>
                    </div>

                    <div class="divide-y divide-gray-200">
                        <?php foreach ($cart['items'] as $key => $item): ?>
                            <div class="p-6 flex flex-col sm:flex-row sm:items-center">
                                <div class="sm:w-3/4">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </h3>

                                    <p class="text-sm text-gray-600">
                                        <?= number_format(((int)$item['unit_price_cents']) / 100, 0, ',', ' ') ?> Kč / ks
                                    </p>

                                    <p class="text-sm text-gray-700 mt-1">
                                        Celkem:
                                        <?= number_format((((int)$item['unit_price_cents']) * ((int)$item['quantity'])) / 100, 0, ',', ' ') ?> Kč
                                    </p>
                                </div>

                                <div class="sm:w-1/4 mt-4 sm:mt-0 flex flex-col items-end justify-between">
                                    <!-- Aktualizace množství -->
                                    <form action="<?= locale_url('cart/update') ?>" method="post" class="flex items-center mb-4">
                                        <input type="hidden" name="item_key" value="<?= htmlspecialchars($key) ?>">

                                        <div class="flex border border-gray-300 rounded-md">
                                            <button type="button" class="px-3 py-1 bg-gray-100 cursor-pointer" onclick="decrementQuantity('<?= htmlspecialchars($key) ?>')">-</button>
                                            <input
                                                type="number"
                                                id="quantity_<?= htmlspecialchars($key) ?>"
                                                name="quantity"
                                                value="<?= (int)$item['quantity'] ?>"
                                                min="1"
                                                max="10"
                                                class="w-14 text-center border-x border-gray-300 py-1 focus:ring-0 focus:outline-none">
                                            <button type="button" class="px-3 py-1 bg-gray-100 cursor-pointer" onclick="incrementQuantity('<?= htmlspecialchars($key) ?>')">+</button>
                                        </div>

                                        <button type="submit" class="ml-2 text-sm text-blue-600 hover:text-blue-800 cursor-pointer">
                                            Aktualizovat
                                        </button>
                                    </form>

                                    <!-- Odstranění -->
                                    <form action="<?= locale_url('cart/remove') ?>" method="post">
                                        <input type="hidden" name="item_key" value="<?= htmlspecialchars($key) ?>">
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 cursor-pointer">
                                            Odstranit
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="p-6 bg-gray-50 border-t">
                        <form action="<?= locale_url('cart/clear') ?>" method="post" class="text-right">
                            <button type="submit" class="text-sm text-gray-600 hover:text-gray-800 cursor-pointer">
                                Vyprázdnit košík
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Souhrn objednávky -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b">
                        <h2 class="text-xl font-semibold text-gray-800">Souhrn objednávky</h2>
                    </div>

                    <div class="p-6">
                        <div class="mb-6">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Mezisoučet:</span>
                                <span class="font-medium">
                                    <?= number_format(((int)$cart['total_amount_cents']) / 100, 0, ',', ' ') ?> Kč
                                </span>
                            </div>
                        </div>

                        <div class="border-t pt-4 mt-4">
                            <div class="flex justify-between mb-2">
                                <span class="text-lg font-bold text-gray-800">Celková částka:</span>
                                <span class="text-lg font-bold text-green-600">
                                    <?= number_format(((int)$cart['total_amount_cents']) / 100, 0, ',', ' ') ?> Kč
                                </span>
                            </div>

                            <a href="<?= locale_url('cart/checkout') ?>"
                                class="block w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg text-center transition duration-200">
                                Pokračovat k pokladně
                            </a>

                                <!-- <div class="mt-4 text-center">
                                    <p class="text-sm text-gray-600">
                                        Pro dokončení objednávky se prosím
                                        <a href="<?= locale_url('login') ?>" class="text-blue-600 hover:underline">přihlaste</a>
                                        nebo
                                        <a href="<?= locale_url('register') ?>" class="text-blue-600 hover:underline">zaregistrujte</a>.
                                    </p>
                                </div> -->
                           
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="<?= locale_url('vouchers') ?>" class="text-blue-600 hover:text-blue-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Pokračovat ve výběru voucherů
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function decrementQuantity(key) {
        const input = document.getElementById('quantity_' + key);
        const currentValue = parseInt(input.value || "1", 10);
        if (currentValue > 1) input.value = currentValue - 1;
    }

    function incrementQuantity(key) {
        const input = document.getElementById('quantity_' + key);
        const currentValue = parseInt(input.value || "1", 10);
        if (currentValue < 10) input.value = currentValue + 1;
    }
</script>
