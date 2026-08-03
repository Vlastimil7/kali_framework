<?php

/** @var array $order */

$old = \Helpers\Flash::old('admin_order');

$val = function (string $k, $fallback = '') use ($old) {
    return array_key_exists($k, $old) ? (string)$old[$k] : (string)$fallback;
};

$orderNo = $order['order_number'] ?? ('#' . ($order['id'] ?? ''));
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 py-8">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
            <div>
                <div class="text-sm text-gray-500">
                    <a href="<?= config('app.base_url', '') ?>/admin/orders" class="hover:text-gray-900 hover:underline">Objednávky</a>
                    <span class="mx-2">/</span>
                    <a href="<?= config('app.base_url', '') ?>/admin/orders/<?= (int)$order['id'] ?>" class="hover:text-gray-900 hover:underline">
                        <?= htmlspecialchars($orderNo) ?>
                    </a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-700">Editace</span>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mt-1">Editace objednávky</h1>
                <p class="text-sm text-gray-500 mt-1">Upravíš fakturační údaje. Položky a částky necháváme jako snapshot.</p>
            </div>

            <div class="flex gap-2">
                <a href="<?= config('app.base_url', '') ?>/admin/orders/<?= (int)$order['id'] ?>"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-semibold text-gray-800">
                    Zpět
                </a>
            </div>
        </div>

        <form action="<?= config('app.base_url', '') ?>/admin/orders/update/<?= (int)$order['id'] ?>" method="post"
            class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">

            <div class="p-6 border-b-2 border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Fakturační údaje</h2>
                <p class="text-sm text-gray-500 mt-1">Zákazník, kontakt a adresa.</p>
            </div>

            <div class="p-6 space-y-5">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1">Jméno</label>
                        <input name="billing_name" required
                            value="<?= htmlspecialchars($val('billing_name', $order['billing_name'] ?? '')) ?>"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                          focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1">Email</label>
                        <input name="billing_email" required
                            value="<?= htmlspecialchars($val('billing_email', $order['billing_email'] ?? '')) ?>"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                          focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Telefon</label>
                    <input name="billing_phone"
                        value="<?= htmlspecialchars($val('billing_phone', $order['billing_phone'] ?? '')) ?>"
                        class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                        focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1">Ulice</label>
                        <input name="billing_street"
                            value="<?= htmlspecialchars($val('billing_street', $order['billing_street'] ?? '')) ?>"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                          focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1">Číslo</label>
                        <input name="billing_house_no"
                            value="<?= htmlspecialchars($val('billing_house_no', $order['billing_house_no'] ?? '')) ?>"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                          focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1">Město</label>
                        <input name="billing_city"
                            value="<?= htmlspecialchars($val('billing_city', $order['billing_city'] ?? '')) ?>"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                          focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1">PSČ</label>
                        <input name="billing_zip"
                            value="<?= htmlspecialchars($val('billing_zip', $order['billing_zip'] ?? '')) ?>"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                          focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                    </div>
                </div>

            </div>

            <div class="p-6 border-t-2 border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Firma</h2>
                <p class="text-sm text-gray-500 mt-1">Volitelné.</p>
            </div>

            <div class="p-6 space-y-5">

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Název firmy</label>
                    <input name="billing_company"
                        value="<?= htmlspecialchars($val('billing_company', $order['billing_company'] ?? '')) ?>"
                        class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                        focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1">IČO</label>
                        <input name="billing_ico"
                            value="<?= htmlspecialchars($val('billing_ico', $order['billing_ico'] ?? '')) ?>"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                          focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-1">DIČ</label>
                        <input name="billing_dic"
                            value="<?= htmlspecialchars($val('billing_dic', $order['billing_dic'] ?? '')) ?>"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                          focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                    </div>
                </div>

            </div>

            <div class="p-6 border-t-2 border-gray-200 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <div class="text-xs text-gray-500">
                    Uložením se změní jen fakturační údaje, částky zůstávají stejné.
                </div>

                <div class="flex gap-2">
                    <button class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black text-sm font-semibold
                         focus:ring-2 focus:ring-gray-900/30">
                        Uložit změny
                    </button>

                    <a href="<?= config('app.base_url', '') ?>/admin/orders/<?= (int)$order['id'] ?>"
                        class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-semibold text-gray-800">
                        Zrušit
                    </a>
                </div>
            </div>

        </form>

    </div>
</div>
