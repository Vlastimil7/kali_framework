<?php

/** @var array $order */
/** @var array $codes */

?>


<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">

        <div class="bg-green-600 px-6 py-5">
            <h1 class="text-2xl font-bold text-white">
                ✅ Objednávka byla úspěšně zaplacena
            </h1>
            <p class="text-green-100 mt-1">
                Číslo objednávky: <strong><?= htmlspecialchars($order['order_number']) ?></strong>
            </p>
        </div>

        <div class="p-6 space-y-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-800 font-medium">
                    Platba byla potvrzena a všechny vouchery byly úspěšně aktivovány.
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-3">
                    Vygenerované vouchery
                </h2>

                <div class="space-y-3">
                    <?php foreach ($codes as $code): ?>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                                    border rounded-lg p-4 bg-gray-50">
                            <div>
                                <div class="font-semibold text-gray-900">
                                    <?= htmlspecialchars($code['voucher_name'] ?? '') ?>
                                </div>
                                <div class="text-sm text-gray-600">
                                    Platnost:
                                    <?= htmlspecialchars($code['valid_from']) ?>
                                    –
                                    <?= htmlspecialchars($code['valid_to']) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Stav: <?= htmlspecialchars($code['status']) ?>
                                </div>
                            </div>

                            <div class="mt-3 sm:mt-0 font-mono text-lg font-bold tracking-wider">
                                <?= htmlspecialchars($code['code']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <a href="<?= locale_url('order/success', null, ['order' => $order['order_number']]) ?>"
                    class="inline-flex justify-center px-5 py-3 rounded-lg bg-gray-900 text-white hover:bg-gray-800">
                    Zobrazit stránku objednávky
                </a>

                <a href="<?= locale_url('vouchers') ?>"
                    class="inline-flex justify-center px-5 py-3 rounded-lg bg-gray-100 hover:bg-gray-200">
                    Zpět na vouchery
                </a>
            </div>
        </div>
    </div>
</div>
