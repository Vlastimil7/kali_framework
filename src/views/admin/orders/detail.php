<?php

/** @var array $order */
/** @var array $items */
/** @var array $codes */

$money = function (?int $cents, string $cur = 'CZK'): string {
  if ($cents === null) return '-';
  $val = $cents / 100;
  if ($cur === 'CZK') return number_format($val, 0, ',', ' ') . ' Kč';
  return number_format($val, 2, ',', ' ') . ' ' . htmlspecialchars($cur);
};

$dt = function (?string $s): string {
  if (!$s) return '-';
  return htmlspecialchars(str_replace('T', ' ', $s));
};

$statusBadge = function (string $status): string {
  $status = strtolower(trim($status));

  $map = [
    'paid'             => 'bg-green-50 text-green-800 border-2 border-green-200',
    'pending'          => 'bg-yellow-50 text-yellow-800 border-2 border-yellow-200',
    'awaiting_payment' => 'bg-yellow-50 text-yellow-800 border-2 border-yellow-200',
    'canceled'         => 'bg-red-50 text-red-800 border-2 border-red-200',
    'refunded'         => 'bg-purple-50 text-purple-800 border-2 border-purple-200',
    'expired'          => 'bg-gray-50 text-gray-800 border-2 border-gray-200',
  ];

  $cls = $map[$status] ?? 'bg-gray-50 text-gray-800 border-2 border-gray-200';

  return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold ' . $cls . '">'
    . htmlspecialchars($status ?: '-') . '</span>';
};

$canMarkPaid = function (string $status): bool {
  $status = strtolower(trim($status));
  // smysl dává jen u pending/awaiting_payment
  return in_array($status, ['pending', 'awaiting_payment'], true);
};

$orderNo = $order['order_number'] ?? ('#' . ($order['id'] ?? ''));
$cur = $order['currency'] ?? 'CZK';
$status = $order['status'] ?? '';

// flash
$err = $_SESSION['_flash_error'] ?? null;
$ok  = $_SESSION['_flash_success'] ?? null;
unset($_SESSION['_flash_error'], $_SESSION['_flash_success']);

// --- Akce pravidla ---
$st = strtolower(trim((string)$status));
$canCancel = in_array($st, ['pending', 'awaiting_payment'], true);
$canExpire = in_array($st, ['pending', 'awaiting_payment'], true);
$canRefund = ($st === 'paid'); // detailní kontrolu (např. redeemed kódy) udělej v controlleru

$disabledCls = 'opacity-50 cursor-not-allowed';
?>

<div class=" bg-gray-50 rounded-xl border">
  <div class="max-w-7xl mx-auto px-4 py-8 ">

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <div class="text-sm text-gray-500">
          <a class="hover:text-gray-900 hover:underline" href="<?= BASE_URL ?>/admin/orders">Objednávky</a>
          <span class="mx-2">/</span>
          <span class="text-gray-700">Detail</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mt-1">
          Objednávka <?= htmlspecialchars($orderNo) ?>
        </h1>

        <div class="mt-2 flex flex-wrap items-center gap-2">
          <?= $statusBadge($status) ?>
          <span class="text-sm text-gray-500">Vytvořeno: <?= $dt($order['created_at'] ?? null) ?></span>
          <?php if (!empty($order['paid_at'])): ?>
            <span class="text-sm text-gray-500">Zaplaceno: <?= $dt($order['paid_at']) ?></span>
          <?php endif; ?>
          <?php if (!empty($order['canceled_at'])): ?>
            <span class="text-sm text-gray-500">Zrušeno: <?= $dt($order['canceled_at']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="<?= BASE_URL ?>/admin/orders/edit/<?= (int)$order['id'] ?>"
          class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black text-sm font-semibold
                  focus:ring-2 focus:ring-gray-900/30">
          Upravit
        </a>

        <?php if ($canMarkPaid($status)): ?>
          <form action="<?= BASE_URL ?>/admin/orders/<?= (int)$order['id'] ?>/mark-paid" method="post">
            <button class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold
                           focus:ring-2 focus:ring-green-600/30 cursor-pointer">
              Označit jako zaplaceno
            </button>
          </form>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/admin/orders"
          class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50
                  text-sm font-semibold text-gray-800">
          Zpět na seznam
        </a>
      </div>
    </div>

    <!-- ✅ Admin akce panel (přidané) -->
    <div class="mt-4 bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
      <div class="flex flex-col gap-4">

        <div>
          <div class="text-sm font-semibold text-gray-900">Admin akce</div>
          <div class="text-xs text-gray-500 mt-1">
            Poznámka se pošle s akcí (bez modalu).
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-900 mb-1">Poznámka k akci</label>
          <textarea id="orderActionNote" rows="3"
            placeholder="Např. storno po tel., refund dle domluvy, expirováno…"
            class="w-full rounded-lg border-2 border-gray-300 px-3 py-2 text-sm
                   focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">

          <!-- Mark paid -->
          <form method="post" action="<?= BASE_URL ?>/admin/orders/<?= (int)$order['id'] ?>/mark-paid"
            class="w-full"
            onsubmit="this.note.value = document.getElementById('orderActionNote').value;">
            <input type="hidden" name="note" value="">
            <button type="submit"
              <?= $canMarkPaid($st) ? '' : 'disabled' ?>
              class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700
                     <?= $canMarkPaid($st) ? '' : $disabledCls ?> cursor-pointer">
              Paid + generovat
            </button>
            <div class="text-xs text-gray-500 mt-1">
              <?= $canMarkPaid($st) ? 'paid + vygeneruje kódy' : 'jen pending/awaiting' ?>
            </div>
          </form>

          <!-- Cancel -->
          <form method="post" action="<?= BASE_URL ?>/admin/orders/<?= (int)$order['id'] ?>/cancel"
            class="w-full"
            onsubmit="this.note.value = document.getElementById('orderActionNote').value;">
            <input type="hidden" name="note" value="">
            <button type="submit"
              <?= $canCancel ? '' : 'disabled' ?>
              class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700
                     <?= $canCancel ? '' : $disabledCls ?> cursor-pointer">
              Storno
            </button>
            <div class="text-xs text-gray-500 mt-1">
              <?= $canCancel ? 'canceled + zneplatní kódy' : 'typicky jen nezaplacené' ?>
            </div>
          </form>

          <!-- Refund -->
          <form method="post" action="<?= BASE_URL ?>/admin/orders/<?= (int)$order['id'] ?>/refund"
            class="w-full"
            onsubmit="this.note.value = document.getElementById('orderActionNote').value;">
            <input type="hidden" name="note" value="">
            <button type="submit"
              <?= $canRefund ? '' : 'disabled' ?>
              class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700
                     <?= $canRefund ? '' : $disabledCls ?> cursor-pointer">
              Refund
            </button>
            <div class="text-xs text-gray-500 mt-1">
              <?= $canRefund ? 'refunded + zruší kódy' : 'jen pro paid' ?>
            </div>
          </form>

          <!-- Expire -->
          <form method="post" action="<?= BASE_URL ?>/admin/orders/<?= (int)$order['id'] ?>/expire"
            class="w-full"
            onsubmit="this.note.value = document.getElementById('orderActionNote').value;">
            <input type="hidden" name="note" value="">
            <button type="submit"
              <?= $canExpire ? '' : 'disabled' ?>
              class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gray-800 hover:bg-black
                     <?= $canExpire ? '' : $disabledCls ?> cursor-pointer">
              Expired
            </button>
            <div class="text-xs text-gray-500 mt-1">
              <?= $canExpire ? 'expired + kódy expirovat/void' : 'jen nezaplacené' ?>
            </div>
          </form>

        </div>

      </div>
    </div>

    <!-- Alerts -->
    <?php if ($err): ?>
      <div class="mt-4 rounded-xl bg-red-50 border-2 border-red-200 p-4 text-sm text-red-900">
        <div class="font-semibold mb-1">Chyba</div>
        <div><?= htmlspecialchars($err) ?></div>
      </div>
    <?php endif; ?>

    <?php if ($ok): ?>
      <div class="mt-4 rounded-xl bg-green-50 border-2 border-green-200 p-4 text-sm text-green-900">
        <div class="font-semibold mb-1">OK</div>
        <div><?= htmlspecialchars($ok) ?></div>
      </div>
    <?php endif; ?>

    <!-- Content -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left -->
      <div class="lg:col-span-2 space-y-6">

        <!-- Summary cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
            <div class="text-sm text-gray-500 mb-1">Celkem</div>
            <div class="text-3xl font-bold text-gray-900">
              <?= $money($order['total_amount_cents'] ?? null, $cur) ?>
            </div>
            <div class="text-xs text-gray-500 mt-2">Měna: <?= htmlspecialchars($cur) ?></div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
            <div class="text-sm text-gray-500 mb-1">Položky</div>
            <div class="text-3xl font-bold text-gray-900"><?= (int)count($items) ?></div>
            <div class="text-xs text-gray-500 mt-2">
              Metoda: <?= htmlspecialchars($order['payment_method'] ?? '-') ?>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
            <div class="text-sm text-gray-500 mb-1">Kódy</div>
            <div class="text-3xl font-bold text-gray-900"><?= (int)count($codes) ?></div>
            <div class="text-xs text-gray-500 mt-2">
              Comgate: <?= htmlspecialchars($order['comgate_ref_id'] ?? '-') ?>
            </div>
          </div>
        </div>

        <!-- Billing -->
        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900">Fakturační údaje</h2>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
              <div class="text-gray-500">Zákazník</div>
              <div class="font-semibold text-gray-900"><?= htmlspecialchars($order['billing_name'] ?? '-') ?></div>
              <div class="text-gray-700"><?= htmlspecialchars($order['billing_email'] ?? '-') ?></div>
              <div class="text-gray-700"><?= htmlspecialchars($order['billing_phone'] ?? '-') ?></div>
            </div>

            <div>
              <div class="text-gray-500">Adresa</div>
              <div class="text-gray-900 font-semibold">
                <?= htmlspecialchars(trim(($order['billing_street'] ?? '-') . ' ' . ($order['billing_house_no'] ?? ''))) ?>
              </div>
              <div class="text-gray-700">
                <?= htmlspecialchars(trim(($order['billing_zip'] ?? '-') . ' ' . ($order['billing_city'] ?? '-'))) ?>
              </div>

              <?php if (!empty($order['billing_company']) || !empty($order['billing_ico']) || !empty($order['billing_dic'])): ?>
                <div class="mt-3 text-gray-500">Firma</div>
                <div class="text-gray-900 font-semibold"><?= htmlspecialchars($order['billing_company'] ?? '-') ?></div>
                <div class="text-gray-700">
                  IČO: <?= htmlspecialchars($order['billing_ico'] ?? '-') ?>,
                  DIČ: <?= htmlspecialchars($order['billing_dic'] ?? '-') ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Items -->
        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
          <div class="p-6 border-b-2 border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Položky</h2>
            <div class="text-sm text-gray-500">Součet: <?= $money($order['total_amount_cents'] ?? null, $cur) ?></div>
          </div>

          <?php if (empty($items)): ?>
            <div class="p-6 text-gray-500">Bez položek (zkontroluj `order_items`).</div>
          <?php else: ?>
            <div class="divide-y divide-gray-200">
              <?php foreach ($items as $it): ?>
                <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                  <div class="min-w-0">
                    <div class="font-semibold text-gray-900 truncate">
                      <?= htmlspecialchars($it['voucher_name_snapshot'] ?? $it['voucher_name'] ?? ('Voucher ID ' . ($it['voucher_id'] ?? '-'))) ?>
                    </div>
                    <div class="mt-1 text-sm text-gray-600 flex flex-wrap gap-x-4 gap-y-1">
                      <span>Množství: <b><?= (int)($it['quantity'] ?? 0) ?></b></span>
                      <?php if (!empty($it['recipient_name'])): ?>
                        <span>Příjemce: <b><?= htmlspecialchars($it['recipient_name']) ?></b></span>
                      <?php endif; ?>
                      <?php if (!empty($it['note'])): ?>
                        <span>Pozn.: <?= htmlspecialchars($it['note']) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="text-right text-sm shrink-0">
                    <div class="text-gray-500">Cena/ks</div>
                    <div class="font-semibold text-gray-900"><?= $money((int)($it['unit_price_cents'] ?? 0), $cur) ?></div>
                    <div class="text-gray-500 mt-2">Cena celkem</div>
                    <div class="font-semibold text-gray-900"><?= $money((int)($it['total_price_cents'] ?? 0), $cur) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>

      <!-- Right -->
      <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">Vygenerované kódy</h2>
              <p class="text-sm text-gray-500 mt-1">
                <?= (($order['status'] ?? '') === 'paid') ? 'Kódy k této objednávce:' : 'Zobrazí se po zaplacení.' ?>
              </p>
            </div>
            <div class="text-sm text-gray-500"><?= (int)count($codes) ?> ks</div>
          </div>

          <div class="mt-4 space-y-3">
            <?php if (empty($codes)): ?>
              <div class="text-gray-500 text-sm">Zatím žádné kódy.</div>
            <?php else: ?>
              <?php foreach ($codes as $c): ?>
                <div class="border-2 border-gray-200 rounded-xl p-4 bg-white">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <div class="text-sm font-semibold text-gray-900 truncate">
                        <?= htmlspecialchars($c['voucher_name'] ?? 'Voucher') ?>
                      </div>
                      <div class="text-xs text-gray-600 mt-1">
                        Platnost: <?= htmlspecialchars($c['valid_from'] ?? '-') ?> – <?= htmlspecialchars($c['valid_to'] ?? '-') ?>
                      </div>
                    </div>

                    <div class="shrink-0">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-50 border-2 border-gray-200 text-gray-800 text-xs font-semibold">
                        <?= htmlspecialchars($c['status'] ?? '-') ?>
                      </span>
                    </div>
                  </div>

                  <div class="mt-3 font-mono text-sm bg-gray-50 border-2 border-gray-200 rounded-lg p-2 break-all">
                    <?= htmlspecialchars($c['code'] ?? '-') ?>
                  </div>

                  <?php if (!empty($c['redeemed_at'])): ?>
                    <div class="text-xs text-gray-500 mt-2">
                      Uplatněno: <?= $dt($c['redeemed_at']) ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>