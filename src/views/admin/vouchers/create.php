<?php
$old = \Helpers\Flash::old('admin_voucher');

$val = function (string $k, $fallback = '') use ($old) {
    return isset($old[$k]) ? (string)$old[$k] : (string)$fallback;
};
?>

<div class=" bg-gray-50 rounded-xl border">
  <div class="max-w-6xl mx-auto px-4 py-8">

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
      <div>
        <div class="text-sm text-gray-500">
          <a href="<?= config('app.base_url', '') ?>/admin/vouchers" class="hover:text-gray-900 hover:underline">Vouchery</a>
          <span class="mx-2">/</span>
          <span class="text-gray-700">Nový voucher</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mt-1">Vytvořit voucher</h1>
        <p class="text-sm text-gray-500 mt-1">Základní parametry, cenu a platnost nastavíš tady. Popis je volitelný.</p>
      </div>

      <div class="flex gap-2">
        <a href="<?= config('app.base_url', '') ?>/admin/vouchers"
          class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-semibold text-gray-800">
          Zpět
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Main form -->
      <div class="lg:col-span-2">
        <form action="<?= config('app.base_url', '') ?>/admin/vouchers/store" method="post"
          class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">

          <!-- Section: Basic -->
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Základní údaje</h2>
            <p class="text-sm text-gray-500 mt-1">Název je pro lidi, slug pro URL.</p>
          </div>

          <div class="p-6 space-y-5">

            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-1">Název</label>
              <input
                name="name"
                value="<?= htmlspecialchars($val('name')) ?>"
                placeholder="Např. Úprava vousů Hot Towel"
                required
                class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                       focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-1">Slug</label>
              <input
                name="slug"
                value="<?= htmlspecialchars($val('slug')) ?>"
                placeholder="napr-uprava-vousu-hot-towel"
                required
                spellcheck="false"
                autocomplete="off"
                class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 font-mono text-sm text-gray-900 placeholder-gray-400
                       focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
              <div class="text-xs text-gray-500 mt-1">Používá se v URL (a–z, 0–9, pomlčky).</div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-1">Popis</label>
              <textarea
                name="description"
                rows="5"
                placeholder="Krátký popis voucheru (co obsahuje, podmínky, …)"
                class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                       focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20"><?= htmlspecialchars($val('description')) ?></textarea>
            </div>

          </div>

          <!-- Section: Price -->
          <div class="p-6 border-t border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Cena a platnost</h2>
            <p class="text-sm text-gray-500 mt-1">Cena se pak promítne do objednávky jako snapshot.</p>
          </div>

          <div class="p-6 space-y-5">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-1">Cena (Kč)</label>

                <div class="relative">
                  <input
                    name="price"
                    type="number"
                    min="0"
                    step="1"
                    value="<?= htmlspecialchars($val('price')) ?>"
                    placeholder="499"
                    required
                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 pr-16 text-gray-900 placeholder-gray-400
                           focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-gray-600">
                    CZK
                  </div>
                </div>

                <div class="text-xs text-gray-500 mt-1">Zadávej v Kč (bez haléřů).</div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-1">Platnost (měsíce)</label>
                <input
                  name="validity_months"
                  type="number"
                  min="1"
                  step="1"
                  value="<?= htmlspecialchars($val('validity_months', '6')) ?>"
                  class="w-full rounded-lg border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400
                         focus:border-gray-900 focus:ring-2 focus:ring-gray-900/20">
                <div class="text-xs text-gray-500 mt-1">Použije se pro výpočet valid_to při generování kódů.</div>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
              <label class="inline-flex items-center gap-3 text-sm font-semibold text-gray-900">
                <input
                  id="is_active"
                  name="is_active"
                  type="checkbox"
                  value="1"
                  <?= (int)$val('is_active', 1) ? 'checked' : '' ?>
                  class="h-5 w-5 rounded border-2 border-gray-400 text-gray-900
                         focus:ring-2 focus:ring-gray-900/30">
                Aktivní
              </label>

              <div class="text-xs text-gray-500">
                Neaktivní voucher se nebude zobrazovat ve frontendu.
              </div>
            </div>

          </div>

          <!-- Footer actions -->
          <div class="p-6 border-t border-gray-200 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
            <div class="text-xs text-gray-500">
              Tip: Slug drž unikátní — ušetříš si kolize v URL.
            </div>

            <div class="flex gap-2">
              <button
                class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black text-sm font-semibold
                       focus:ring-2 focus:ring-gray-900/30">
                Uložit voucher
              </button>

              <a href="<?= config('app.base_url', '') ?>/admin/vouchers"
                class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50
                        text-sm font-semibold text-gray-800">
                Zpět
              </a>
            </div>
          </div>

        </form>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
          <h3 class="text-sm font-semibold text-gray-900">Co se stane po zaplacení</h3>
          <p class="text-sm text-gray-600 mt-2">
            Z objednávky se vygenerují kódy do <span class="font-mono text-xs">voucher_codes</span> a nastaví se
            platnost podle <b>validity_months</b>.
          </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-6">
          <h3 class="text-sm font-semibold text-gray-900">Doporučení</h3>
          <ul class="mt-2 text-sm text-gray-600 space-y-2 list-disc pl-5">
            <li>Název dej „lidský“, slug „technický“.</li>
            <li>Cenu měň jen adminem (ať máš kontrolu).</li>
            <li>Popis klidně stručně – stačí 2–3 věty.</li>
          </ul>
        </div>
      </div>

    </div>
  </div>
</div>
