<?php

/** @var array $vouchers */ ?>

<?php

$money = function (?int $cents, string $cur = 'CZK'): string {
    if ($cents === null) return '-';
    $val = $cents / 100;
    if ($cur === 'CZK') return number_format($val, 0, ',', ' ') . ' Kč';
    return number_format($val, 2, ',', ' ') . ' ' . htmlspecialchars($cur);
};

$badge = function (int $active): string {
    if ($active) {
        return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-green-50 text-green-800 border-2 border-green-200">active</span>';
    }
    return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-50 text-gray-800 border-2 border-gray-200">inactive</span>';
};
?>

<div class=" bg-gray-50 rounded-xl border">
    <div class="max-w-7xl mx-auto px-4 py-8">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
            <div>
                <div class="text-sm text-gray-500">Admin / Vouchery</div>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Vouchery</h1>
                <p class="text-sm text-gray-500 mt-1">Správa cen, aktivace a detail voucherů.</p>
            </div>

            <div class="flex items-center gap-2">
                <div class="text-sm text-gray-500 mr-2">Celkem: <?= (int)count($vouchers) ?></div>

                <a href="<?= config('app.base_url', '') ?>/admin/vouchers/create"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black text-sm font-semibold
                  focus:ring-2 focus:ring-gray-900/30">
                    + Nový voucher
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left px-4 py-3 font-semibold">ID</th>
                            <th class="text-left px-4 py-3 font-semibold">Název</th>
                            <th class="text-left px-4 py-3 font-semibold">Slug</th>
                            <th class="text-left px-4 py-3 font-semibold">Cena</th>
                            <th class="text-left px-4 py-3 font-semibold">Stav</th>
                            <th class="text-right px-4 py-3 font-semibold">Akce</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($vouchers)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                    Zatím žádné vouchery.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vouchers as $v): ?>
                                <?php $cur = $v['currency'] ?? 'CZK'; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-700"><?= (int)$v['id'] ?></td>

                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900">
                                            <?= htmlspecialchars($v['name'] ?? '-') ?>
                                        </div>
                                        <?php if (!empty($v['description'])): ?>
                                            <div class="text-xs text-gray-500 mt-1 line-clamp-1">
                                                <?= htmlspecialchars($v['description']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="inline-flex font-mono text-xs px-2 py-1 rounded-lg bg-gray-50 border-2 border-gray-200 text-gray-700">
                                            <?= htmlspecialchars($v['slug'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 font-semibold text-gray-900"><?= $money($v['price_cents'] ?? null, $cur) ?></td>

                                    <td class="px-4 py-3"><?= $badge((int)($v['is_active'] ?? 0)) ?></td>

                                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                        <a class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-semibold text-gray-800"
                                            href="<?= config('app.base_url', '') ?>/admin/vouchers/<?= (int)$v['id'] ?>">
                                            Detail →
                                        </a>

                                        <a class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-semibold text-gray-800"
                                            href="<?= config('app.base_url', '') ?>/admin/vouchers/edit/<?= (int)$v['id'] ?>">
                                            Upravit
                                        </a>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- Zpět na dashboard -->
<div class="mt-8 text-xs text-slate-500">
    <a href="<?= config('app.base_url', '') ?>/admin/dashboard" class="hover:text-white">← Zpět na dashboard </a>
</div>