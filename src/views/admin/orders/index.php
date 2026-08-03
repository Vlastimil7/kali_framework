<?php

/** @var array $orders */

$money = function (?int $cents, string $cur = 'CZK'): string {
  if ($cents === null) return '-';
  $val = $cents / 100;
  if ($cur === 'CZK') return number_format($val, 0, ',', ' ') . ' Kč';
  return number_format($val, 2, ',', ' ') . ' ' . htmlspecialchars($cur);
};

$statusBadge = function (?string $status): string {
  $status = strtolower(trim((string)$status));

  $map = [
    'paid'             => ['bg-green-50 text-green-800 border-green-200', 'Zaplaceno'],
    'awaiting_payment' => ['bg-blue-50 text-blue-800 border-blue-200', 'Čeká na platbu'],
    'pending'          => ['bg-yellow-50 text-yellow-800 border-yellow-200', 'Pending'],
    'refunded'         => ['bg-purple-50 text-purple-800 border-purple-200', 'Refundováno'],
    'canceled'         => ['bg-red-50 text-red-800 border-red-200', 'Zrušeno'],
    'expired'          => ['bg-gray-50 text-gray-800 border-gray-200', 'Expirovalo'],
  ];

  $cfg = $map[$status] ?? ['bg-gray-50 text-gray-800 border-gray-200', $status ?: '-'];

  return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border ' . $cfg[0] . '">'
    . htmlspecialchars($cfg[1]) . '</span>';
};
?>

<div class="max-w-7xl mx-auto px-4 py-8">

  <!-- Header + Search -->
  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
    <div>
      <h1 class="text-2xl font-bold text-white">Objednávky</h1>
      <p class="text-sm text-gray-500 mt-1">Klikni na detail pro kódy a akce.</p>
    </div>

    <div class="flex items-center gap-4">
      <input
        id="orderSearch"
        type="text"
        placeholder="Hledat objednávku…"
        class="w-64 rounded-lg border border-gray-300 px-4 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-gray-900 text-white">

      <div class="text-sm text-white">
        Zobrazeno: <span id="visibleCount"></span>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="text-left px-4 py-3">ID</th>
            <th class="text-left px-4 py-3">Číslo</th>
            <th class="text-left px-4 py-3">Zákazník</th>
            <th class="text-left px-4 py-3">Email</th>
            <th class="text-left px-4 py-3">Status</th>
            <th class="text-left px-4 py-3">Celkem</th>
            <th class="text-right px-4 py-3">Akce</th>
          </tr>
        </thead>

        <tbody id="ordersTable" class="divide-y">
          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                Zatím žádné objednávky.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $o): ?>
              <?php
              $orderNo = $o['order_number'] ?? ('#' . ($o['id'] ?? ''));
              $search = strtolower(
                ($o['id'] ?? '') . ' ' .
                  $orderNo . ' ' .
                  ($o['billing_name'] ?? '') . ' ' .
                  ($o['billing_email'] ?? '') . ' ' .
                  ($o['status'] ?? '')
              );
              ?>
              <tr
                class="hover:bg-gray-50"
                data-search="<?= htmlspecialchars($search) ?>">
                <td class="px-4 py-3"><?= (int)($o['id'] ?? 0) ?></td>
                <td class="px-4 py-3 font-medium text-gray-900"><?= htmlspecialchars($orderNo) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($o['billing_name'] ?? '-') ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($o['billing_email'] ?? '-') ?></td>
                <td class="px-4 py-3"><?= $statusBadge($o['status'] ?? '') ?></td>
                <td class="px-4 py-3 font-medium text-gray-900">
                  <?= $money($o['total_amount_cents'] ?? null, $o['currency'] ?? 'CZK') ?>
                </td>
                <td class="px-4 py-3 text-right">
                  <a class="text-blue-700 hover:underline font-medium"
                    href="<?= config('app.base_url', '') ?>/admin/orders/<?= (int)$o['id'] ?>">
                    Detail →
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between px-4 py-3 text-sm">
      <div>
        Stránka <span id="currentPage"></span> z <span id="totalPages"></span>
      </div>
      <div class="flex gap-2">
        <button id="prevPage" class="rounded-md border px-3 py-1 hover:bg-gray-100 cursor-pointer">←</button>
        <button id="nextPage" class="rounded-md border px-3 py-1 hover:bg-gray-100 cursor-pointer">→</button>
      </div>
    </div>
  </div>

  <!-- Back -->
  <div class="mt-4 text-xs text-slate-500">
    <a href="<?= config('app.base_url', '') ?>/admin/dashboard" class="hover:text-white">← Zpět na dashboard</a>
  </div>
</div>

<!-- JS -->
<script src="<?= config('app.base_url', '') ?>/assets/js/orders-table.js"></script>