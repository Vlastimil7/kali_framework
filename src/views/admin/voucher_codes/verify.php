<?php /** @var array|null $result */ ?>

<div class="max-w-3xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-white mb-4">Ověření voucheru</h1>

    <!-- Form: Verify -->
    <form method="post" action="<?= BASE_URL ?>/admin/voucher-codes/verify"
        class="bg-white rounded-xl border-2 border-gray-200 p-6 space-y-4">

        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-1">Kód voucheru</label>
            <input
                name="code"
                autofocus
                placeholder="NAPR-AB12-CD34"
                class="w-full rounded-lg border-2 border-gray-300 px-3 py-2 font-mono
               focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
        </div>

        <button class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black text-sm font-semibold cursor-pointer
              focus:ring-2 focus:ring-gray-900/30 ">
            Ověřit kód
        </button>
    </form>

    <!-- Result -->
    <?php if (!empty($result)): ?>
        <?php
        $code        = (string)($result['code'] ?? '');
        $codeStatus  = (string)($result['status'] ?? '');
        $orderStatus = (string)($result['order_status'] ?? '');

        $isPaidOrder = ($orderStatus === 'paid');

        $isExpired = false;
        if (!empty($result['valid_to'])) {
            $isExpired = (strtotime($result['valid_to'] . ' 23:59:59') < time());
        }

        // pravidla pro akce (uprav si dle potřeby)
        $canRedeem   = $isPaidOrder && ($codeStatus === 'active') && !$isExpired;
        $canVoid     = $isPaidOrder && in_array($codeStatus, ['active', 'pending_payment'], true); // nebo jen ['active']
        $canExchange = $isPaidOrder && ($codeStatus === 'active') && !$isExpired;

        $disabledCls = 'opacity-50 cursor-not-allowed';
        ?>

        <div class="mt-6 bg-white rounded-xl border-2 border-gray-200 p-6 space-y-4">

            <!-- Voucher -->
            <div>
                <div class="text-sm text-gray-500">Voucher</div>
                <div class="text-lg font-bold text-gray-900">
                    <?= htmlspecialchars($result['voucher_name'] ?? '-') ?>
                </div>
                <div class="text-sm text-gray-700 mt-1">
                    Kód: <span class="font-mono"><?= htmlspecialchars($code) ?></span>
                </div>
            </div>

            <!-- Stav -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">Stav kódu</div>
                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($codeStatus ?: '-') ?></div>
                </div>
                <div>
                    <div class="text-gray-500">Stav objednávky</div>
                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($orderStatus ?: '-') ?></div>
                </div>
            </div>

            <!-- Platnost -->
            <div class="text-sm text-gray-700">
                Platnost:
                <b><?= htmlspecialchars($result['valid_from'] ?? '-') ?></b>
                –
                <b><?= htmlspecialchars($result['valid_to'] ?? '-') ?></b>
            </div>

            <?php if ($isExpired): ?>
                <div class="text-sm text-red-800 bg-red-50 border-2 border-red-200 rounded-lg p-3">
                    Pozor: voucher je <b>expirovaný</b>.
                </div>
            <?php endif; ?>

            <!-- Kupující -->
            <div class="rounded-lg border-2 border-gray-200 p-4">
                <div class="text-sm text-gray-500 mb-2">Kdo voucher koupil</div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500">Jméno</div>
                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($result['billing_name'] ?? '-') ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500">Email</div>
                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($result['billing_email'] ?? '-') ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500">Telefon</div>
                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($result['billing_phone'] ?? '-') ?></div>
                    </div>
                </div>
            </div>

            <!-- Objednávka -->
            <div class="flex items-center justify-between gap-3 text-sm text-gray-600">
                <div>
                    Objednávka:
                    <b><?= htmlspecialchars($result['order_number'] ?? '-') ?></b>
                </div>

                <?php if (!empty($result['order_id'])): ?>
                    <a
                        href="<?= BASE_URL ?>/admin/orders/<?= (int)$result['order_id'] ?>"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg
                   bg-gray-100 hover:bg-gray-200 text-gray-900
                   text-xs font-semibold border border-gray-300"
                        title="Otevřít detail objednávky">
                        📄 Detail objednávky
                    </a>
                <?php endif; ?>
            </div>


            <?php
            $code = (string)($result['code'] ?? '');
            $codeStatus  = (string)($result['status'] ?? '');
            $orderStatus = (string)($result['order_status'] ?? '');

            $isPaidOrder = ($orderStatus === 'paid');

            $isExpired = false;
            if (!empty($result['valid_to'])) {
                $isExpired = (strtotime($result['valid_to'] . ' 23:59:59') < time());
            }

            $canRedeem   = $isPaidOrder && ($codeStatus === 'active') && !$isExpired;
            $canVoid     = $isPaidOrder && in_array($codeStatus, ['active', 'pending_payment', 'expired'], true);
            $canExchange = $isPaidOrder && ($codeStatus === 'active') && !$isExpired;

            $disabledCls = 'opacity-50 cursor-not-allowed';
            ?>

            <div class="pt-4 border-t border-gray-200 space-y-4">

                <div class="text-sm font-semibold text-gray-900">Akce</div>

                <!-- Poznámka společná pro všechny akce -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-1">Poznámka</label>
                    <textarea id="actionNote" rows="3"
                        placeholder="Např. uplatněno na recepci, refund dle domluvy, výměna kvůli…"
                        class="w-full rounded-lg border-2 border-gray-300 px-3 py-2 text-sm
             focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20"></textarea>
                 
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                    <!-- UPLATNIT -->
                    <form method="post" action="<?= BASE_URL ?>/admin/voucher-codes/redeem" class="w-full"
                        onsubmit="this.note.value = document.getElementById('actionNote').value;">
                        <input type="hidden" name="code" value="<?= htmlspecialchars($code) ?>">
                        <input type="hidden" name="note" value="">
                        <button type="submit" <?= $canRedeem ? '' : 'disabled' ?>
                            class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 <?= $canRedeem ? '' : $disabledCls ?> cursor-pointer">
                            Uplatnit
                        </button>
                        <div class="text-xs text-gray-500 mt-1">
                            <?= $canRedeem ? 'Změní status na redeemed.' : 'Jen pro paid + active + neexpirovaný.' ?>
                        </div>
                    </form>

                    <!-- VRÁTIT (REFUND) -->
                    <form method="post" action="<?= BASE_URL ?>/admin/voucher-codes/void" class="w-full"
                        onsubmit="this.reason.value = document.getElementById('actionNote').value;">
                        <input type="hidden" name="code" value="<?= htmlspecialchars($code) ?>">
                        <input type="hidden" name="type" value="refunded">
                        <input type="hidden" name="reason" value="">
                        <button type="submit" <?= $canVoid ? '' : 'disabled' ?>
                            class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 <?= $canVoid ? '' : $disabledCls ?> cursor-pointer">
                            Vrátit (refund)
                        </button>
                        <div class="text-xs text-gray-500 mt-1">
                            <?= $canVoid ? 'Nastaví status na refunded.' : 'Nelze pro redeemed/canceled/refunded.' ?>
                        </div>
                    </form>

                    <!-- VYMĚNIT -->
                    <form method="post" action="<?= BASE_URL ?>/admin/voucher-codes/exchange" class="w-full"
                        onsubmit="this.reason.value = document.getElementById('actionNote').value;">
                        <input type="hidden" name="code" value="<?= htmlspecialchars($code) ?>">
                        <input type="hidden" name="reason" value="">
                        <button type="submit" <?= $canExchange ? '' : 'disabled' ?>
                            class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 <?= $canExchange ? '' : $disabledCls ?> cursor-pointer">
                            Vyměnit
                        </button>
                        <div class="text-xs text-gray-500 mt-1">
                            <?= $canExchange ? 'Zneplatní starý + vytvoří nový kód.' : 'Jen pro paid + active + neexpirovaný.' ?>
                        </div>
                    </form>

                </div>

                <?php if ($isExpired): ?>
                    <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3">
                        Pozor: voucher je <b>expirovaný</b> (valid_to: <?= htmlspecialchars($result['valid_to']) ?>).
                    </div>
                <?php endif; ?>

            </div>


        </div>
    <?php endif; ?>
    <!-- Zpět na dashboard -->
    <div class="mt-8 text-xs text-slate-500">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="hover:text-white">← Zpět na dashboard </a>
    </div>
</div>
