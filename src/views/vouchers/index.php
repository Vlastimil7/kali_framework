<?php

/** @var array $vouchers */

// najdeme nejdražší voucher = Doporučujeme
$maxPriceCents = 0;
foreach ($vouchers as $v) {
    $price = (int)($v['price_cents'] ?? 0);
    if ($price > $maxPriceCents) $maxPriceCents = $price;
}
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <!-- Nadpis -->
    <div class="mb-12 text-center">
        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">
            Dárkové vouchery Midobarbershop
        </h1>
        <p class="mt-4 mx-auto max-w-3xl text-base md:text-lg text-white leading-relaxed">
            Stylový dárek pro každou příležitost. Vyber si hodnotu voucheru, zaplať online
            a voucher ti pošleme e-mailem v PDF připraveném k vytisknutí.
        </p>
    </div>

    <!-- Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch">
        <?php foreach ($vouchers as $voucher): ?>
            <?php $isRecommended = ((int)$voucher['price_cents'] === $maxPriceCents && $maxPriceCents > 0); ?>

            <div
                class="
                    relative h-full flex flex-col rounded-3xl bg-white
                    transition-all duration-300
                    hover:-translate-y-1 hover:shadow-lg
                    <?= $isRecommended ? 'ring-2 ring-white shadow-md' : 'ring-1 ring-zinc-200 shadow-sm' ?>
                "
                <?= $isRecommended ? 'style="animation: borderPulse 3.2s ease-in-out infinite;"' : '' ?>>
                <?php if ($isRecommended): ?>
                    <style>
                        @keyframes borderPulse {
                            0% {
                                box-shadow: 0 0 0 0 rgba(234, 179, 8, .35);
                            }

                            50% {
                                box-shadow: 0 0 0 4px rgba(234, 179, 8, .14);
                            }

                            100% {
                                box-shadow: 0 0 0 0 rgba(234, 179, 8, .35);
                            }
                        }
                    </style>
                <?php endif; ?>

                <div class="p-8 flex flex-col h-full">
                    <!-- Header -->
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <?php if ($isRecommended): ?>
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-4 py-1 text-xs font-extrabold text-yellow-900 ring-1 ring-yellow-200">
                                    Doporučujeme
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($voucher['tag'])): ?>
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-4 py-1 text-xs font-semibold text-zinc-800 ring-1 ring-zinc-200">
                                    <?= htmlspecialchars($voucher['tag']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h2 class="mt-4 text-xl font-extrabold tracking-tight text-zinc-900 leading-snug">
                            <?= htmlspecialchars($voucher['name']) ?>
                        </h2>

                        <p class="mt-3 text-sm text-zinc-600 leading-relaxed">
                            <?= htmlspecialchars($voucher['description']) ?>
                        </p>
                    </div>

                    <!-- PRICE BOX (premium pro Doporučujeme) -->
                    <div class="mt-8">
                        <div class="
                            rounded-2xl px-5 py-5
                            <?= $isRecommended
                                ? 'bg-zinc-900 text-white shadow-sm'
                                : 'bg-zinc-50 text-zinc-900 ring-1 ring-zinc-200'
                            ?>
                        ">
                            <div class="text-xs font-semibold uppercase tracking-wide <?= $isRecommended ? 'text-white/70' : 'text-zinc-500' ?>">
                                Hodnota voucheru
                            </div>

                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-4xl font-black tracking-tight">
                                    <?= number_format($voucher['price_cents'] / 100, 0, ',', ' ') ?>
                                </span>
                                <span class="text-lg font-extrabold <?= $isRecommended ? 'text-white/90' : 'text-zinc-900' ?>">Kč</span>
                            </div>

                            <div class="mt-2 text-xs <?= $isRecommended ? 'text-white/70' : 'text-zinc-500' ?>">
                                PDF k vytištění • platnost 6 měsíců
                            </div>
                        </div>
                    </div>

                    <!-- Benefits -->
                    <ul class="mt-6 space-y-2 text-sm text-zinc-600">
                        <li class="flex gap-2"><span class="font-black text-zinc-900">✓</span><span>PDF voucher připravený k tisku</span></li>
                        <li class="flex gap-2"><span class="font-black text-zinc-900">✓</span><span>Platnost 6 měsíců od nákupu</span></li>
                        <li class="flex gap-2"><span class="font-black text-zinc-900">✓</span><span>Uplatnění v Midobarbershop</span></li>
                    </ul>

                    <!-- CTA -->
                    <div class="mt-auto pt-8">
                        <form action="<?= locale_url('cart/add-voucher') ?>" method="post">
                            <input type="hidden" name="voucher_id" value="<?= (int)$voucher['id'] ?>">
                            <input type="hidden" name="quantity" value="1">

                            <button type="submit"
                                class="
                                    w-full rounded-2xl px-5 py-3.5
                                    text-sm font-extrabold tracking-wide
                                    <?= $isRecommended
                                        ? 'bg-zinc-900 text-white hover:bg-zinc-800'
                                        : 'bg-zinc-900 text-white hover:bg-zinc-800'
                                    ?>
                                    focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 cursor-pointer
                                ">
                                Přidat do košíku
                            </button>
                        </form>

                        <p class="mt-3 text-center text-xs text-zinc-500">
                            Voucher obdržíš e-mailem po zaplacení
                        </p>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
