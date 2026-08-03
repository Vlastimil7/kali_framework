<?php

/** @var array $voucher */ ?>

<?php
$old = \Helpers\Flash::old('admin_voucher');

$cur = $voucher['currency'] ?? 'CZK';

$priceDefault = isset($voucher['price_cents'])
  ? number_format(((int)$voucher['price_cents'] / 100), 0, ',', '')
  : '';

$val = function (string $k, $fallback = '') use ($old) {
  return isset($old[$k]) ? (string)$old[$k] : (string)$fallback;
};
?>

<div class=" bg-gray-50 rounded-xl border">
  <div class="max-w-3xl mx-auto px-4 py-8">

    <div class="flex items-start justify-between gap-4 mb-6">
      <div>
        <div class="text-sm text-gray-500">
          <a class="hover:underline" href="<?= config('app.base_url', '') ?>/admin/vouchers">Vouchery</a>
          <span class="mx-2">/</span>
          <span class="text-gray-700">Editace</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mt-1"><?= htmlspecialchars($voucher['name'] ?? '-') ?></h1>
      </div>

      <a href="<?= config('app.base_url', '') ?>/admin/vouchers/<?= (int)$voucher['id'] ?>"
        class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-semibold text-gray-800">
        Zpět
      </a>
    </div>

    <!-- FIX: action musí být /admin/vouchers/update/{id} -->
    <form action="<?= config('app.base_url', '') ?>/admin/vouchers/update/<?= (int)$voucher['id'] ?>" method="post"
      class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">

      <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Upravit voucher</h2>
        <p class="text-sm text-gray-500 mt-1">Změny se projeví u nových objednávek (order_items má cenu jako snapshot).</p>
      </div>

      <div class="p-6 space-y-5">

        <div>
          <label class="block text-sm font-semibold text-gray-900 mb-1">Název</label>
          <input name="name" required
            value="<?= htmlspecialchars($val('name', $voucher['name'] ?? '')) ?>"
            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                   focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20" />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-900 mb-1">Slug</label>
          <input name="slug" required spellcheck="false" autocomplete="off"
            value="<?= htmlspecialchars($val('slug', $voucher['slug'] ?? '')) ?>"
            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 font-mono text-sm text-gray-900 placeholder-gray-400
                   focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20" />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-900 mb-1">Popis</label>
          <input name="description" required spellcheck="false" autocomplete="off"
            value="<?= htmlspecialchars($val('description', $voucher['description'] ?? '')) ?>"
            class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 font-mono text-sm text-gray-900 placeholder-gray-400
                   focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-1">Cena (<?= htmlspecialchars($cur) ?>)</label>
            <input name="price" type="number" min="0" step="1" required
              value="<?= htmlspecialchars($val('price', $priceDefault)) ?>"
              class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                     focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20" />
            <div class="text-xs text-gray-500 mt-1">Zadávej v Kč (bez haléřů).</div>
          </div>

          <div class="flex items-end">
            <label class="inline-flex items-center gap-3 text-sm font-semibold text-gray-900">
              <input type="checkbox" name="is_active" value="1"
                <?= (int)$val('is_active', (int)($voucher['is_active'] ?? 0)) ? 'checked' : '' ?>
                class="h-5 w-5 rounded border-2 border-gray-400 text-gray-900 focus:ring-2 focus:ring-gray-900/30">
              Aktivní
            </label>
          </div>
        </div>

      </div>

      <div class="p-6 border-t border-gray-200 bg-gray-50 flex items-center justify-end gap-2">
        <button class="px-4 py-2 rounded-lg bg-gray-900 hover:bg-black text-white text-sm font-semibold focus:ring-2 focus:ring-gray-900/30">
          Uložit změny
        </button>
        <a href="<?= config('app.base_url', '') ?>/admin/vouchers/<?= (int)$voucher['id'] ?>"
          class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-semibold text-gray-800">
          Zrušit
        </a>
      </div>

    </form>
  </div>
</div>
