<?php
/** @var string|null $status */
/** @var string|null $refId */
/** @var string|null $transId */
/** @var string $title */

$statusRaw = strtolower(trim((string)($status ?? '')));
$refIdSafe = htmlspecialchars((string)($refId ?? ''));
$transSafe = htmlspecialchars((string)($transId ?? ''));

$badge = function (string $st): array {
    // default
    $cfg = ['bg-gray-50 text-gray-800 border-gray-200', 'Platba se ověřuje'];

    // mapuj si podle toho, co reálně dostáváš
    if (in_array($st, ['paid', 'ok', 'success'], true)) {
        $cfg = ['bg-green-50 text-green-800 border-green-200', 'Zaplaceno'];
    } elseif (in_array($st, ['pending', 'awaiting_payment'], true)) {
        $cfg = ['bg-blue-50 text-blue-800 border-blue-200', 'Čeká se na potvrzení'];
    } elseif (in_array($st, ['cancelled', 'canceled', 'cancel', 'failed'], true)) {
        $cfg = ['bg-red-50 text-red-800 border-red-200', 'Platba zrušena / neproběhla'];
    } elseif (in_array($st, ['refunded'], true)) {
        $cfg = ['bg-purple-50 text-purple-800 border-purple-200', 'Refundováno'];
    }

    return $cfg;
};

[$cls, $label] = $badge($statusRaw);

// pro user-friendly text
$hint = "Pokud jste právě zaplatil(a), potvrzení může chvíli trvat. "
      . "Voucher dorazí e-mailem po přijetí notifikace z platební brány.";

if ($label === 'Zaplaceno') {
    $hint = "Platba je označená jako zaplacená. Voucher(y) vám dorazí e-mailem (obvykle během chvilky).";
}

if ($label === 'Platba zrušena / neproběhla') {
    $hint = "Platba nebyla dokončena. Můžete to zkusit znovu.";
}
?>

<div class="max-w-3xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Návrat z platební brány</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Objednávka: <span class="font-semibold">#<?= $refIdSafe ?: '-' ?></span>
                    <?php if ($transSafe !== ''): ?>
                        <span class="text-gray-400">•</span>
                        Platba: <span class="font-semibold"><?= $transSafe ?></span>
                    <?php endif; ?>
                </p>
            </div>

            <span class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold border-2 <?= $cls ?>">
                <?= htmlspecialchars($label) ?>
            </span>
        </div>

        <div class="mt-6 text-gray-700 leading-relaxed">
            <p><?= htmlspecialchars($hint) ?></p>

            <div class="mt-4 text-sm text-gray-500">
                <p>
                    Pokud e-mail nepřijde ani po několika minutách, zkontrolujte spam,
                    případně nás kontaktujte.
                </p>
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-3">
            <a href="<?= BASE_URL ?>/vouchers"
               class="inline-flex justify-center items-center px-4 py-2 rounded-lg bg-gray-900 text-white font-semibold hover:bg-gray-800">
                Zpět na vouchery
            </a>

            <a href="<?= BASE_URL ?>/cart"
               class="inline-flex justify-center items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-900 font-semibold hover:bg-gray-200">
                Košík
            </a>

            <a href="<?= BASE_URL ?>/contact"
               class="inline-flex justify-center items-center px-4 py-2 rounded-lg border border-gray-200 text-gray-700 font-semibold hover:bg-gray-50">
                Kontakt
            </a>
        </div>
    </div>

    <div class="text-xs text-gray-400 mt-4">
        <p>Tip: Stav se může aktualizovat po NOTIFY z brány (automaticky). Tato stránka slouží hlavně jako potvrzení návratu.</p>
    </div>
</div>
