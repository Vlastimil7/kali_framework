<?php
function clinicNs(string $clinic): string
{
    $clinic = trim($clinic);
    if ($clinic === '') $clinic = 'default';
    $slug = mb_strtolower($clinic);
    $slug = preg_replace('~[^a-z0-9]+~i', '_', $slug);
    $slug = trim($slug, '_');
    return 'contact_' . $slug;
}

function flashTake(string $ns): ?array
{
    if (!empty($_SESSION['flash'][$ns])) {
        $f = $_SESSION['flash'][$ns];
        unset($_SESSION['flash'][$ns]); // spotřebuj jen jednou
        return $f;
    }
    return null;
}

function oldVal(string $ns, string $key): string
{
    return htmlspecialchars($_SESSION['old'][$ns][$key] ?? '', ENT_QUOTES, 'UTF-8');
}

function oldSelected(string $ns, string $key, string $value): string
{
    return (($_SESSION['old'][$ns][$key] ?? '') === $value) ? 'selected' : '';
}
?>


<section class="pt-10 pb-20">
    <div class="max-w-7xl mx-auto px-6 space-y-12">

        <!-- Nadpis + úvod -->
        <header class="space-y-3 text-center">
            <p class="text-xs font-semibold tracking-wide text-indigo-600 uppercase">
                Potřebujete poradit?
            </p>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                Kontakty a kontaktovací formuláře
            </h1>
            <p class="text-sm text-slate-600 max-w-2xl mx-auto">
                Vyberte ordinaci, které chcete napsat. Formulář prosím využívejte pro neurgentní dotazy.
                V akutních případech vždy volejte nebo využijte pohotovost.
            </p>
        </header>

        <!-- DVA BLOKY – PEDIA / PEDIA AZ -->
        <div class="grid gap-10 lg:grid-cols-1 items-start">

            <!-- PEDIA s.r.o. -->
            <div class="rounded-3xl bg-gradient-to-b from-white via-slate-50 to-white border border-indigo-100 shadow-md p-7 sm:p-8 space-y-6">
                <!-- Hlavička -->
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1.5">
                        <p class="inline-flex items-center gap-2 text-[1rem] font-bold tracking-wide text-indigo-500 uppercase">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 text-xs">
                                🩺
                            </span>
                            Ordinace PEDIA s.r.o.
                        </p>
                        <h2 class="text-base font-semibold text-slate-900">
                            MUDr. Ziad Albahri – praktický lékař pro děti a dorost
                        </h2>
                        <div class="mt-2 grid gap-3 text-xs text-slate-700">
                            <div>
                                <p class="font-semibold text-slate-900 flex items-center gap-2">
                                    <span class="text-sm">📍</span>
                                    Adresa
                                </p>
                                <p class="leading-relaxed">
                                    PEDIA s.r.o.<br>
                                    Kostelní 39<br>
                                    551 01 Jaroměř
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900 flex items-center gap-2">
                                    <span class="text-sm">📞</span>
                                    Kontaktní údaje
                                </p>
                                <!-- IČO -->
                                <p class="text-[0.75rem] text-slate-800 mb-1 mt-1 font-medium">
                                    IČO&nbsp;28829018
                                </p>
                                <p class="leading-relaxed">
                                    <a href="tel:491815150"
                                        class="font-medium text-slate-800 hover:text-indigo-600 underline-offset-2 hover:underline">
                                        491 815 150
                                    </a><br>

                                    <a href="tel:721001600"
                                        class="font-medium text-slate-800 hover:text-indigo-600 underline-offset-2 hover:underline">
                                        721 001 600
                                    </a>
                                    <span class="text-slate-500">(tel / SMS / WhatsApp)</span><br>

                                    <a href="https://wa.me/420721001600"
                                        target="_blank"
                                        class="text-green-600 hover:text-green-700 underline-offset-2 hover:underline">
                                        WhatsApp zpráva
                                    </a><br>

                                    <a href="mailto:pedia@post.cz"
                                        class="text-indigo-600 hover:text-indigo-700 underline underline-offset-2">
                                        pedia@post.cz
                                    </a>
                                </p>


                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-gradient-to-r from-transparent via-indigo-100 to-transparent"></div>

                <!-- FORMULÁŘ PEDIA s.r.o. -->
                <?php
                $nsPedia = clinicNs('PEDIA s.r.o.');
                $flashPedia = flashTake($nsPedia);
                ?>
                <?php if ($flashPedia): ?>
                    <div class="mb-4 rounded-xl px-4 py-3 text-sm
             <?= ($flashPedia['type'] ?? '') === 'success'
                        ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                        : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
                        <?= htmlspecialchars($flashPedia['message'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                <form action="<?= BASE_URL ?>/contact/send" method="post" class="space-y-6">
                    <input type="hidden" name="clinic" value="PEDIA s.r.o.">

                    <!-- Jméno + e-mail -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="name_pedia" class="text-[0.75rem] font-medium text-slate-800 flex items-center justify-between">
                                <span>Jméno a příjmení <span class="text-red-500">*</span></span>
                            </label>
                            <input
                                type="text"
                                id="name_pedia"
                                name="name"
                                value="<?= oldVal($nsPedia, 'name') ?>"
                                required
                                class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                                placeholder="Např. Jana Nováková">
                        </div>

                        <div class="space-y-1.5">
                            <label for="email_pedia" class="text-[0.75rem] font-medium text-slate-800 flex items-center justify-between">
                                <span>E-mail <span class="text-red-500">*</span></span>
                            </label>
                            <input
                                type="email"
                                id="email_pedia"
                                name="email"
                                value="<?= oldVal($nsPedia, 'email') ?>"
                                required
                                class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                                placeholder="vas@email.cz">
                        </div>
                    </div>

                    <!-- Telefon + typ zprávy -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="phone_pedia" class="text-[0.75rem] font-medium text-slate-800">
                                Telefon
                                <span class="text-slate-400 font-normal">(doporučeno)</span>
                            </label>
                            <input
                                type="tel"
                                id="phone_pedia"
                                name="phone"
                                value="<?= oldVal($nsPedia, 'phone') ?>"
                                class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                                placeholder="např. 721 001 600">
                        </div>

                        <div class="space-y-1.5">
                            <label for="topic_pedia" class="text-[0.75rem] font-medium text-slate-800">
                                Důvod zprávy
                            </label>
                            <select
                                id="topic_pedia"
                                name="topic"
                                class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                                <option value="" <?= oldSelected($nsPedia, 'topic', '') ?>>Vyberte možnost…</option>
                                <option value="dotaz" <?= oldSelected($nsPedia, 'topic', 'dotaz') ?>>Dotaz k onemocnění / léčbě</option>
                                <option value="recept" <?= oldSelected($nsPedia, 'topic', 'recept') ?>>Žádost o recept / léky</option>
                                <option value="administrativa" <?= oldSelected($nsPedia, 'topic', 'administrativa') ?>>Formuláře / potvrzení / administrativa</option>
                                <option value="jine" <?= oldSelected($nsPedia, 'topic', 'jine') ?>>Jiné</option>
                            </select>
                        </div>
                    </div>

                    <!-- Zpráva -->
                    <div class="space-y-1.5">
                        <label for="message_pedia" class="text-[0.75rem] font-medium text-slate-800">
                            Zpráva <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="message_pedia"
                            name="message"
                            rows="6"
                            required
                            class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                            placeholder="Popište prosím stručně důvod kontaktu a případně věk dítěte."><?= oldVal($nsPedia, 'message') ?></textarea>
                    </div>

                    <!-- Souhlas -->
                    <div class="space-y-1">
                        <label class="inline-flex items-start gap-2 text-[0.7rem] text-slate-600 leading-relaxed">
                            <input
                                type="checkbox"
                                name="gdpr"
                                required
                                class="mt-[3px] h-3 w-3 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <span>
                                Souhlasím se zpracováním osobních údajů pro účely zodpovězení mého dotazu
                                a beru na vědomí, že formulář není určen pro urgentní stavy.
                            </span>
                        </label>
                    </div>

                    <div class="h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

                    <!-- Tlačítko -->
                    <div class="pt-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <p class="text-[0.7rem] text-slate-500 max-w-xs leading-relaxed">
                            Odpověď zašleme dle možností ordinace PEDIA s.r.o. V akutních případech využijte telefon nebo pohotovost.
                        </p>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-6 py-2.5 text-[0.8rem] font-semibold text-white shadow-md hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:ring-offset-1 focus:ring-offset-slate-50 transition cursor-pointer">
                            <span>Odeslat zprávu</span>
                            <span>→</span>
                        </button>
                    </div>
                    <input type="hidden" name="recaptcha_token" class="recaptcha_token">

                </form>
            </div>

            <!-- PEDIA AZ s.r.o. -->
            <div class="rounded-3xl bg-gradient-to-b from-white via-slate-50 to-white border border-indigo-100 shadow-md p-7 sm:p-8 space-y-6">
                <!-- Hlavička -->
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1.5">
                        <p class="inline-flex items-center gap-2 text-[1rem] font-bold tracking-wide text-emerald-500 uppercase">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 text-xs">
                                🩺
                            </span>
                            Ordinace PEDIA AZ s.r.o.
                        </p>
                        <h2 class="text-base font-semibold text-slate-900">
                            MUDr. Ziad Albahri – praktický lékař pro děti a dorost
                        </h2>
                        <div class="mt-2 grid gap-3 text-xs text-slate-700">
                            <div>
                                <p class="font-semibold text-slate-900 flex items-center gap-2">
                                    <span class="text-sm">📍</span>
                                    Adresa
                                </p>
                                <p class="leading-relaxed">
                                    PEDIA AZ s.r.o.<br>
                                    Kostelní 39<br>
                                    551 01 Jaroměř
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900 flex items-center gap-2">
                                    <span class="text-sm">📞</span>
                                    Kontaktní údaje
                                </p>
                                <!-- IČO -->
                                <p class="text-[0.75rem] text-slate-800 mb-1 mt-1 font-medium">
                                    IČO&nbsp;04087216
                                </p>
                                <p class="leading-relaxed">
                                    <a href="tel:491813810"
                                        class="font-medium text-slate-800 hover:text-indigo-600 underline-offset-2 hover:underline">
                                        491 813 810
                                    </a><br>

                                    <a href="tel:+420721001180"
                                        class="font-medium text-slate-800 hover:text-indigo-600 underline-offset-2 hover:underline">
                                        +420 721 001 180
                                    </a>
                                    <span class="text-slate-500">(tel / SMS / WhatsApp)</span><br>

                                    <a href="https://wa.me/420721001180"
                                        target="_blank"
                                        class="text-green-600 hover:text-green-700 underline underline-offset-2">
                                        WhatsApp zpráva
                                    </a><br>

                                    <a href="mailto:PediaAZ@post.cz"
                                        class="text-indigo-600 hover:text-indigo-700 underline underline-offset-2">
                                        PediaAZ@post.cz
                                    </a>
                                </p>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-gradient-to-r from-transparent via-indigo-100 to-transparent"></div>
                <?php
                $nsAz = clinicNs('PEDIA AZ s.r.o.');
                $flashAz = flashTake($nsAz);
                ?>
                <?php if ($flashAz): ?>
                    <div class="mb-4 rounded-xl px-4 py-3 text-sm
             <?= ($flashAz['type'] ?? '') === 'success'
                        ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                        : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
                        <?= htmlspecialchars($flashAz['message'], ENT_QUOTES, 'UTF-8') ?>

                    </div>
                <?php endif; ?>

                <!-- FORMULÁŘ PEDIA AZ s.r.o. -->
                <form action="<?= BASE_URL ?>/contact/send" method="post" class="space-y-6">
                    <input type="hidden" name="clinic" value="PEDIA AZ s.r.o.">

                    <!-- Jméno + e-mail -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="name_pediaaz" class="text-[0.75rem] font-medium text-slate-800 flex items-center justify-between">
                                <span>Jméno a příjmení <span class="text-red-500">*</span></span>
                            </label>
                            <input
                                type="text"
                                id="name_pediaaz"
                                name="name"
                                value="<?= oldVal($nsAz, 'name') ?>"
                                required
                                class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                                placeholder="Např. Jana Nováková">
                        </div>

                        <div class="space-y-1.5">
                            <label for="email_pediaaz" class="text-[0.75rem] font-medium text-slate-800 flex items-center justify-between">
                                <span>E-mail <span class="text-red-500">*</span></span>
                            </label>
                            <input
                                type="email"
                                id="email_pediaaz"
                                name="email"
                                value="<?= oldVal($nsAz, 'email') ?>"
                                required
                                class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                                placeholder="vas@email.cz">
                        </div>
                    </div>

                    <!-- Telefon + typ zprávy -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="phone_pediaaz" class="text-[0.75rem] font-medium text-slate-800">
                                Telefon
                                <span class="text-slate-400 font-normal">(doporučeno)</span>
                            </label>
                            <input
                                type="tel"
                                id="phone_pediaaz"
                                name="phone"
                                value="<?= oldVal($nsAz, 'phone') ?>"
                                class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                                placeholder="např. +420 721 001 180">
                        </div>

                        <div class="space-y-1.5">
                            <label for="topic_pediaaz" class="text-[0.75rem] font-medium text-slate-800">
                                Důvod zprávy
                            </label>
                            <select
                                id="topic_pediaaz"
                                name="topic"
                                class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                                <option value="" <?= oldSelected($nsAz, 'topic', '') ?>>Vyberte možnost…</option>
                                <option value="dotaz" <?= oldSelected($nsAz, 'topic', 'dotaz') ?>>Dotaz k onemocnění / léčbě</option>
                                <option value="recept" <?= oldSelected($nsAz, 'topic', 'recept') ?>>Žádost o recept / léky</option>
                                <option value="administrativa" <?= oldSelected($nsAz, 'topic', 'administrativa') ?>>Formuláře / potvrzení / administrativa</option>
                                <option value="jine" <?= oldSelected($nsAz, 'topic', 'jine') ?>>Jiné</option>
                            </select>
                        </div>
                    </div>

                    <!-- Zpráva -->
                    <div class="space-y-1.5">
                        <label for="message_pediaaz" class="text-[0.75rem] font-medium text-slate-800">
                            Zpráva <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="message_pediaaz"
                            name="message"
                            rows="6"
                            required
                            class="block w-full rounded-2xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                            placeholder="Popište prosím stručně důvod kontaktu a případně věk dítěte."><?= oldVal($nsAz, 'message') ?></textarea>
                    </div>

                    <!-- Souhlas -->
                    <div class="space-y-1">
                        <label class="inline-flex items-start gap-2 text-[0.7rem] text-slate-600 leading-relaxed">
                            <input
                                type="checkbox"
                                name="gdpr"
                                required
                                class="mt-[3px] h-3 w-3 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <span>
                                Souhlasím se zpracováním osobních údajů pro účely zodpovězení mého dotazu
                                a beru na vědomí, že formulář není určen pro urgentní stavy.
                            </span>
                        </label>
                    </div>

                    <div class="h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

                    <!-- Tlačítko -->
                    <div class="pt-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <p class="text-[0.7rem] text-slate-500 max-w-xs leading-relaxed">
                            Odpověď zašleme dle možností ordinace PEDIA AZ s.r.o. V akutních případech využijte telefon nebo pohotovost.
                        </p>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-6 py-2.5 text-[0.8rem] font-semibold text-white shadow-md hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:ring-offset-1 focus:ring-offset-slate-50 transition cursor-pointer">
                            <span>Odeslat zprávu</span>
                            <span>→</span>
                        </button>
                    </div>
                    <input type="hidden" name="recaptcha_token" class="recaptcha_token">


                </form>
            </div>

        </div>



        <!-- SPOLEČNÁ POHOTOVOST -->
        <div class="space-y-4">

            <div class="grid gap-4 md:grid-cols-2 text-xs">
                <!-- Náchod -->
                <div class="rounded-2xl bg-slate-50 border border-rose-200 p-5 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold text-sm text-slate-900 flex items-center gap-2">
                            <span class="text-base">🚨</span>
                            Pohotovost Náchod
                        </p>
                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-[0.65rem] font-semibold text-rose-700 border border-rose-100">
                            Akutní péče
                        </span>
                    </div>
                    <p class="text-[0.75rem] text-slate-700 leading-relaxed">
                        Tel.:
                        <a href="tel:841155155"
                            class="font-semibold hover:text-indigo-600 underline-offset-2 hover:underline">
                            841 155 155
                        </a><br>

                        Tel.:
                        <a href="tel:491601771"
                            class="font-semibold hover:text-indigo-600 underline-offset-2 hover:underline">
                            491 601 771
                        </a>
                    </p>

                    <p class="text-[0.7rem] text-slate-500 mt-1 leading-relaxed">
                        Po–Pá <strong>16:00–22:00</strong><br>
                        So, Ne, svátky <strong>8:00–22:00</strong>
                    </p>
                </div>

                <!-- Hradec Králové -->
                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 space-y-2">
                    <p class="font-semibold text-sm text-slate-900 flex items-center gap-2">
                        <span class="text-base">🚨</span>
                        Pohotovost Hradec Králové
                    </p>
                    <p class="text-[0.75rem] text-slate-700 leading-relaxed">
                        Tel.:
                        <a href="tel:495832826"
                            class="font-semibold hover:text-indigo-600 underline-offset-2 hover:underline">
                            495 832 826
                        </a>
                    </p>

                    <p class="text-[0.7rem] text-slate-500 mt-1 leading-relaxed">
                        Po–Pá <strong>15:30–22:00</strong><br>
                        So, Ne, svátky <strong>8:00–22:00</strong>
                    </p>
                </div>
            </div>

            <p class="text-[0.7rem] text-slate-500 leading-relaxed">
                V život ohrožujících stavech vždy volejte
                <strong><a href="tel:155" class="hover:text-indigo-600 underline-offset-2 hover:underline">155</a></strong>
                nebo
                <strong><a href="tel:112" class="hover:text-indigo-600 underline-offset-2 hover:underline">112</a></strong>.
            </p>

        </div>


        <div class="shadow-sm rounded-3xl overflow-hidden" id="google-address">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2553.364785271197!2d15.920256276158428!3d50.355592499999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x470e7d9b6279f17f%3A0x891df389833c8ad1%20!2sKosteln%C3%AD%2039%2C%20551%2001%20Jarom%C4%9B%C5%99!5e0!3m2!1scs!2scz!4v1734021847293!5m2!1scs!2scz"
                width="100%"
                height="450"
                style="border:0;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
        <!-- Zpět na úvod -->
        <div class="text-xs text-slate-500">
            <a href="<?= BASE_URL ?>/" class="hover:text-indigo-600">← Zpět na hlavní stránku</a>
        </div>
    </div>
</section>

<script src="https://www.google.com/recaptcha/api.js?render=<?= RECAPTCHA_SITE_KEY ?>" async defer></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const forms = document.querySelectorAll('form[action$="/contact/send"]');

        forms.forEach((form) => {
            form.addEventListener('submit', (e) => {
                const hidden = form.querySelector('input[name="recaptcha_token"]');
                if (hidden && hidden.value) return; // už máme token -> nech odeslat

                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                grecaptcha.ready(() => {
                    grecaptcha.execute('<?= RECAPTCHA_SITE_KEY ?>', {
                            action: 'contact'
                        })
                        .then((token) => {
                            if (hidden) hidden.value = token;
                            form.submit();
                        })
                        .catch(() => {
                            if (submitBtn) submitBtn.disabled = false;
                            alert('Nepodařilo se ověřit reCAPTCHA. Zkuste to prosím znovu.');
                        });
                });
            });
        });
    });
</script>