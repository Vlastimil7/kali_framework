<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Stat: Users -->
    <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 p-6">
        <div class="pointer-events-none absolute -top-10 -right-10 h-32 w-32 rounded-full bg-zinc-900/5 blur-2xl"></div>

        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Počet uživatelů</div>
                <div class="mt-2 text-4xl font-black tracking-tight text-zinc-900"><?= (int)$userCount ?></div>
                <div class="mt-1 text-sm text-zinc-500">Celkem registrovaných v systému</div>
            </div>

            <div class="grid place-items-center h-12 w-12 rounded-2xl bg-zinc-900 text-white shadow-sm">
                <span class="text-base font-black">👤</span>
            </div>
        </div>

     
    </div>

    <!-- Quick links -->
    <div class="md:col-span-2 rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Rychlé odkazy</div>
                <div class="mt-2 text-xl font-extrabold tracking-tight text-zinc-900">Správa administrace</div>
                <div class="mt-1 text-sm text-zinc-500">Nejčastější sekce na jedno kliknutí.</div>
            </div>

            <div class="grid place-items-center h-12 w-12 rounded-2xl bg-zinc-50 ring-1 ring-zinc-200 text-zinc-900">
                <span class="text-base font-black">⚡</span>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="<?= BASE_URL ?>/admin/orders"
                class="group flex items-center justify-between rounded-xl px-4 py-3 bg-zinc-50 ring-1 ring-zinc-200
                      hover:bg-white hover:shadow-sm transition">
                <div class="flex items-center gap-3">
                    <span class="grid place-items-center h-9 w-9 rounded-xl bg-white ring-1 ring-zinc-200">📦</span>
                    <div>
                        <div class="text-sm font-extrabold text-zinc-900">Objednávky</div>
                        <div class="text-xs text-zinc-500">přehled a detail</div>
                    </div>
                </div>
                <span class="text-zinc-400 group-hover:text-zinc-900 transition">→</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/vouchers"
                class="group flex items-center justify-between rounded-xl px-4 py-3 bg-zinc-50 ring-1 ring-zinc-200
                      hover:bg-white hover:shadow-sm transition">
                <div class="flex items-center gap-3">
                    <span class="grid place-items-center h-9 w-9 rounded-xl bg-white ring-1 ring-zinc-200">🎟️</span>
                    <div>
                        <div class="text-sm font-extrabold text-zinc-900">Vouchery</div>
                        <div class="text-xs text-zinc-500">produkty a ceny</div>
                    </div>
                </div>
                <span class="text-zinc-400 group-hover:text-zinc-900 transition">→</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/voucher-codes/verify"
                class="group flex items-center justify-between rounded-xl px-4 py-3 bg-zinc-50 ring-1 ring-zinc-200
                      hover:bg-white hover:shadow-sm transition">
                <div class="flex items-center gap-3">
                    <span class="grid place-items-center h-9 w-9 rounded-xl bg-white ring-1 ring-zinc-200">✅</span>
                    <div>
                        <div class="text-sm font-extrabold text-zinc-900">Ověřit kód voucheru</div>
                        <div class="text-xs text-zinc-500">rychlá kontrola</div>
                    </div>
                </div>
                <span class="text-zinc-400 group-hover:text-zinc-900 transition">→</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/users"
                class="group flex items-center justify-between rounded-xl px-4 py-3 bg-zinc-50 ring-1 ring-zinc-200
                      hover:bg-white hover:shadow-sm transition">
                <div class="flex items-center gap-3">
                    <span class="grid place-items-center h-9 w-9 rounded-xl bg-white ring-1 ring-zinc-200">👥</span>
                    <div>
                        <div class="text-sm font-extrabold text-zinc-900">Uživatelé</div>
                        <div class="text-xs text-zinc-500">role a přístupy</div>
                    </div>
                </div>
                <span class="text-zinc-400 group-hover:text-zinc-900 transition">→</span>
            </a>
        </div>
    </div>

</div>