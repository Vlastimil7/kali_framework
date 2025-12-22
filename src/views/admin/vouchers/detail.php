<?php

/** @var array $voucher */ ?>

<?php
$money = function (?int $cents, string $cur = 'CZK'): string {
    if ($cents === null) return '-';
    $val = $cents / 100;
    if ($cur === 'CZK') return number_format($val, 0, ',', ' ') . ' Kč';
    return number_format($val, 2, ',', ' ') . ' ' . htmlspecialchars($cur);
};
$cur = $voucher['currency'] ?? 'CZK';

$status = !empty($voucher['is_active'])
    ? '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-green-50 text-green-800 border-2 border-green-200">active</span>'
    : '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-50 text-gray-800 border-2 border-gray-200">inactive</span>';
?>
<?php
$err = $_SESSION['_flash_error'] ?? null;
$ok  = $_SESSION['_flash_success'] ?? null;
unset($_SESSION['_flash_error'], $_SESSION['_flash_success']);
?>
<div class="bg-gray-50 rounded-xl border">
    <div class="max-w-7xl mx-auto px-4 py-8">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
            <div>
                <div class="text-sm text-gray-500">
                    <a href="<?= BASE_URL ?>/admin/vouchers" class="hover:text-gray-900 hover:underline">Vouchery</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-700">Detail</span>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mt-1"><?= htmlspecialchars($voucher['name'] ?? '-') ?></h1>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <?= $status ?>
                    <span class="inline-flex font-mono text-xs px-2 py-1 rounded-lg bg-white border-2 border-gray-200 text-gray-700">
                        <?= htmlspecialchars($voucher['slug'] ?? '-') ?>
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="<?= BASE_URL ?>/admin/vouchers/edit/<?= (int)$voucher['id'] ?>"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black text-sm font-semibold
                  focus:ring-2 focus:ring-gray-900/30">
                    Upravit
                </a>

                <a href="<?= BASE_URL ?>/admin/vouchers"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-semibold text-gray-800">
                    Zpět
                </a>
            </div>
        </div>
        <?php if ($err): ?>
            <div class="mb-4 rounded-xl bg-red-50 border-2 border-red-200 p-4 text-sm text-red-900">
                <div class="font-semibold mb-1">Chyba</div>
                <div><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <?php if ($ok): ?>
            <div class="mb-4 rounded-xl bg-green-50 border-2 border-green-200 p-4 text-sm text-green-900">
                <div class="font-semibold mb-1">OK</div>
                <div><?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
                <div class="text-sm text-gray-500 mb-1">Cena</div>
                <div class="text-3xl font-bold text-gray-900"><?= $money($voucher['price_cents'] ?? null, $cur) ?></div>
                <div class="text-xs text-gray-500 mt-2">Měna: <?= htmlspecialchars($cur) ?></div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
                <div class="text-sm text-gray-500 mb-1">Stav</div>
                <div class="text-3xl font-bold text-gray-900"><?= !empty($voucher['is_active']) ? 'active' : 'inactive' ?></div>
                <div class="text-xs text-gray-500 mt-2">ID: <?= (int)$voucher['id'] ?></div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
                <div class="text-sm text-gray-500 mb-1">Meta</div>
                <div class="text-gray-700 mt-2 text-sm">
                    Vytvořeno: <?= htmlspecialchars($voucher['created_at'] ?? '-') ?><br>
                    Upraveno: <?= htmlspecialchars($voucher['updated_at'] ?? '-') ?>
                </div>
            </div>
        </div>

        <?php if (!empty($voucher['description'])): ?>
            <div class="mt-6 bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900">Popis</h2>
                <div class="mt-3 text-gray-700 whitespace-pre-line"><?= htmlspecialchars($voucher['description']) ?></div>
            </div>
        <?php endif; ?>

    </div>
</div>