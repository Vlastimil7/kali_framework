<section class="py-16 bg-slate-50 rounded-2xl">
    <div class="max-w-4xl mx-auto px-6 space-y-10 text-sm text-slate-700 leading-relaxed">

        <header class="space-y-3">
            <h1 class="text-3xl font-bold text-slate-900">
                Obchodní podmínky
            </h1>
            <p class="text-slate-500">
                Tyto obchodní podmínky upravují nákup dárkových voucherů prostřednictvím webových stránek.
            </p>
        </header>

        <!-- 1 -->
        <section>
            <h2 class="text-xl font-semibold text-slate-900 mb-2">
                1. Identifikace provozovatele
            </h2>
            <p>
                Provozovatelem e-shopu a poskytovatelem služeb je:
            </p>
            <p class="mt-2">
                <strong>Mido Barbershop</strong><br>
                nám. Svobody 372/3<br>
                500 02 Hradec Králové<br>
                Česká republika<br>
                E-mail:
                <a href="mailto:reception@midobarbershop.com" class="text-indigo-600 hover:underline">
                    reception@midobarbershop.com
                </a><br>
                Telefon: +420 777 711 135<br>
                La Mido invest s.r.o.<br>
                ICO: 10779990
            </p>
        </section>

        <!-- 2 -->
        <section>
            <h2 class="text-xl font-semibold text-slate-900 mb-2">
                2. Předmět smlouvy
            </h2>
            <p>
                Předmětem smlouvy je prodej dárkových voucherů, které lze uplatnit
                na služby poskytované provozovatelem Mido Barbershop.
            </p>
            <p class="mt-2">
                Voucher není platebním prostředkem, ale poukazem na poskytnutí služby
                v uvedené hodnotě.
            </p>
        </section>

        <!-- 3 -->
        <section>
            <h2 class="text-xl font-semibold text-slate-900 mb-2">
                3. Objednávka a uzavření smlouvy
            </h2>
            <p>
                Odesláním objednávky zákazník potvrzuje, že se seznámil s těmito
                obchodními podmínkami a souhlasí s nimi.
            </p>
            <p class="mt-2">
                Smlouva je uzavřena okamžikem přijetí platby.
            </p>
        </section>

        <!-- 4 -->
        <section>
            <h2 class="text-xl font-semibold text-slate-900 mb-2">
                4. Cena a platební podmínky
            </h2>
            <ul class="list-disc pl-6 space-y-1">
                <li>Všechny ceny jsou uvedeny v českých korunách (CZK).</li>
                <li>Platba probíhá prostřednictvím platební brány Comgate.</li>
                <li>Objednávka musí být uhrazena do <strong>3 dnů</strong>, jinak je automaticky zrušena.</li>
            </ul>
        </section>

        <!-- 5 -->
        <section>
            <h2 class="text-xl font-semibold text-slate-900 mb-2">
                5. Dodání voucheru
            </h2>
            <p>
                Voucher je doručen elektronicky na e-mailovou adresu zadanou při objednávce,
                a to bez zbytečného odkladu po přijetí platby.
            </p>
            <p class="mt-2">
                Provozovatel nenese odpovědnost za chybně zadanou e-mailovou adresu.
            </p>
        </section>

        <!-- 6 -->
        <section>
            <h2 class="text-xl font-semibold text-slate-900 mb-2">
                6. Platnost voucheru
            </h2>
            <p>
                Každý voucher má omezenou dobu platnosti, která je uvedena přímo na voucheru.
            </p>
            <p class="mt-2">
                Po uplynutí doby platnosti voucher propadá a nelze jej dále uplatnit.
            </p>
        </section>

        <!-- 7 -->
        <section>
            <h2 class="text-xl font-semibold text-slate-900 mb-2">
                7. Odstoupení od smlouvy a refundace
            </h2>
            <p>
                Vzhledem k tomu, že se jedná o digitální obsah dodaný bezprostředně po zaplacení,
                nemá zákazník právo na odstoupení od smlouvy podle § 1837 občanského zákoníku.
            </p>
            <p class="mt-2">
                Refundace je možná pouze:
            </p>
            <ul class="list-disc pl-6 space-y-1">
                <li>osobně na provozovně Mido Barbershop</li>
                <li>nebo po předchozí telefonické domluvě</li>
            </ul>
        </section>

        <!-- 8 -->
        <section>
            <h2 class="text-xl font-semibold text-slate-900 mb-2">
                8. Reklamace
            </h2>
            <p>
                Reklamace je možné uplatnit e-mailem nebo telefonicky.
                Každá reklamace bude vyřízena bez zbytečného odkladu.
            </p>
        </section>

        <!-- 9 -->
        <section>
            <h2 class="text-xl font-semibold text-slate-900 mb-2">
                9. Závěrečná ustanovení
            </h2>
            <p>
                Tyto obchodní podmínky se řídí právním řádem České republiky.
            </p>
            <p class="mt-2">
                Provozovatel si vyhrazuje právo tyto podmínky kdykoliv změnit.
            </p>
        </section>

        <footer class="pt-6 border-t border-slate-200 text-xs text-slate-500">
            Poslední aktualizace: <?= date('d.m.Y') ?>
        </footer>

        <div>
            <a href="<?= locale_url() ?>"
                class="text-xs text-slate-500 hover:text-indigo-600 hover:underline">
                ← Zpět na hlavní stránku
            </a>
        </div>

    </div>
</section>

<div>
    <hr class="my-16 border-slate-200">
</div>

<!-- Obchodni podminky -->
<?php include '../src/views/terms/shipping_payment.php'; ?>
