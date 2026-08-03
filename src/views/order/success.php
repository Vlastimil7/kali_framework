<?php

/** @var array $order */
/** @var array $items */

$total = (int)($order['total_amount_cents'] ?? 0);
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 bg-gray-900">
            <h1 class="text-2xl md:text-3xl font-bold text-white">
                Děkujeme za objednávku 🎉
            </h1>
            <p class="text-gray-200 mt-2">
                Objednávku jsme přijali. Číslo objednávky:
                <span class="font-semibold"><?= htmlspecialchars($order['order_number']) ?></span>
            </p>
        </div>

        <div class="p-6 space-y-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Stav objednávky</h2>
                <p class="text-gray-700">
                    Aktuální stav:
                    <span class="font-semibold">
                        <?= htmlspecialchars($order['status']) ?>
                    </span>
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    Až bude platba potvrzená, voucher(y) dorazí na e-mail:
                    <span class="font-medium"><?= htmlspecialchars($order['billing_email']) ?></span>
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Rekapitulace objednávky</h2>

                <div class="divide-y border rounded-lg overflow-hidden">
                    <?php foreach ($items as $it): ?>
                        <div class="p-4 flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    <?= htmlspecialchars($it['voucher_name'] ?? ('Voucher #' . (int)$it['voucher_id'])) ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    Množství: <?= (int)$it['quantity'] ?>
                                </p>

                                <?php if (!empty($it['recipient_name'])): ?>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Pro: <?= htmlspecialchars($it['recipient_name']) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($it['note'])): ?>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Poznámka: <?= htmlspecialchars($it['note']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="text-right">
                                <p class="font-semibold text-gray-900">
                                    <?= number_format(((int)$it['total_price_cents']) / 100, 0, ',', ' ') ?> Kč
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?= number_format(((int)$it['unit_price_cents']) / 100, 0, ',', ' ') ?> Kč / ks
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex justify-between mt-4 text-lg">
                    <span class="font-bold text-gray-800">Celkem</span>
                    <span class="font-bold text-green-700">
                        <?= number_format($total / 100, 0, ',', ' ') ?> Kč
                    </span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="<?= locale_url('vouchers') ?>"
                    class="inline-flex justify-center items-center px-5 py-3 rounded-lg bg-gray-900 text-white hover:bg-gray-800">
                    Zpět na vouchery
                </a>
                <a href="<?= locale_url('cart') ?>"
                    class="inline-flex justify-center items-center px-5 py-3 rounded-lg bg-gray-100 text-gray-900 hover:bg-gray-200">
                    Košík
                </a>
            </div>


        </div>
    </div>
</div>


<?php if (!empty($codes)): ?>
    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
        <h2 class="text-lg font-semibold text-green-900 mb-2">Vaše vouchery</h2>
        <div class="space-y-3">
            <?php foreach ($codes as $c): ?>
                <div class="bg-white rounded-md border p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($c['voucher_name']) ?></div>
                        <div class="text-sm text-gray-600">
                            Platnost: <?= htmlspecialchars($c['valid_from']) ?> – <?= htmlspecialchars($c['valid_to']) ?>
                        </div>
                        <div class="text-xs text-gray-500">Stav: <?= htmlspecialchars($c['status']) ?></div>
                    </div>
                    <div class="font-mono text-lg font-bold tracking-wider">
                        <?= htmlspecialchars($c['code']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="text-xs text-green-800 mt-3">
            Doporučujeme si kódy uložit. Později přidáme i zaslání voucherů e-mailem v PDF.
        </p>
    </div>
<?php endif; ?>
