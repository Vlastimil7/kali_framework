<footer class=" w-full bg-slate-900 text-slate-200">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4 mb-10">
            <!-- Logo + krátké info -->
            <div class="space-y-4">
                <a href="<?= BASE_URL ?>/" class="flex flex-col leading-tight">
                    <span class="text-sm sm:text-base font-bold text-slate-100 tracking-tight">
                        Dětská ordinace v Jaroměři
                    </span>
                    <span class="text-xs text-slate-400">
                        PEDIA s.r.o. • PEDIA AZ s.r.o.
                    </span>
                    <span class="mt-2 text-[0.75rem] text-slate-300">
                        MUDr. Ziad Albahri, Ph.D.<br>
                        <span class="text-[0.7rem] text-slate-400">praktický lékař pro děti a dorost</span>
                    </span>
                </a>

                <p class="text-sm text-slate-300">
                    Dětská ordinace v Jaroměři – PEDIA s.r.o. a PEDIA AZ s.r.o. poskytují
                    péči pro děti a dorost od narození až do dospělosti.
                </p>
                <p class="text-[0.7rem] text-slate-500">
                    Čekárna a vnější prostory zdravotnického zařízení jsou monitorovány
                    kamerovým systémem. Záběry nejsou archivovány, jedná se pouze o online přenos.
                </p>
            </div>

            <!-- Adresa + kontakty obou ordinací -->
            <div class="space-y-5 text-sm">
                <h3 class="text-base font-semibold text-white">Adresa &amp; kontakty</h3>

                <!-- Společná adresa -->
                <div class="space-y-1">
                    <p class="text-slate-300 font-medium">
                        <span class="text-indigo-500 hover:text-indigo-400">PEDIA s.r.o.</span> &amp;
                        <span class="text-emerald-500 hover:text-emerald-400">PEDIA AZ s.r.o.</span>
                    </p>
                    <p class="text-slate-400">
                        Kostelní 39<br>
                        551 01&nbsp;Jaroměř
                    </p>
                </div>

                <!-- Kontakty PEDIA -->
                <div class="pt-4 border-t border-slate-800 space-y-1">
                    <p class="text-indigo-500 font-medium hover:text-indigo-400 transition cursor-default">
                        PEDIA s.r.o.
                    </p>
                    <p class="text-[0.7rem] text-slate-500">
                        IČO&nbsp;28829018, Jaroměř – zapsáno v Obchodním rejstříku firem
                    </p>

                    <p class="text-slate-300 font-medium mt-1">Telefon</p>
                    <p class="text-slate-400">
                        📱 <a href="tel:+420721001600" class="hover:text-indigo-300">+420 721 001 600</a>
                        <span class="text-[0.7rem] text-slate-500">&nbsp;(tel / SMS)</span><br>
                        ☎️ <a href="tel:+420491815150" class="hover:text-indigo-300">+420 491 815 150</a>
                    </p>

                    <p class="text-slate-300 font-medium mt-1">E-mail</p>
                    <p class="text-slate-400">
                        ✉️ <a href="mailto:pedia@post.cz" class="hover:text-indigo-300">pedia@post.cz</a>
                    </p>
                </div>

                <!-- Kontakty PEDIA AZ -->
                <div class="pt-4 border-t border-slate-800 space-y-1">
                    <p class="text-emerald-500 font-medium hover:text-emerald-400 transition cursor-default">
                        PEDIA AZ s.r.o.
                    </p>
                    <p class="text-[0.7rem] text-slate-500">
                        IČO&nbsp;04087216, Jaroměř – zapsáno v Obchodním rejstříku firem
                    </p>

                    <p class="text-slate-300 font-medium mt-1">Telefon</p>
                    <p class="text-slate-400">
                        📱 <a href="tel:+420721001180" class="hover:text-indigo-300">+420 721 001 180</a>
                        <span class="text-[0.7rem] text-slate-500">&nbsp;(tel / SMS / WhatsApp)</span><br>
                        ☎️ <a href="tel:+420491813810" class="hover:text-indigo-300">+420 491 813 810</a>
                    </p>

                    <p class="text-slate-300 font-medium mt-1">E-mail</p>
                    <p class="text-slate-400">
                        ✉️ <a href="mailto:PediaAZ@post.cz" class="hover:text-indigo-300">PediaAZ@post.cz</a>
                    </p>
                </div>

            </div>



            <!-- Pohotovost -->
            <div class="space-y-4 text-sm">
                <h3 class="text-base font-semibold text-white">Pohotovost pro děti</h3>

                <div class="space-y-1">
                    <p class="text-slate-300 font-medium">Náchod</p>
                    <p class="text-slate-400">
                        📞 <a href="tel:+420841155155" class="hover:text-indigo-400 transition">841 155 155</a><br>
                        📞 <a href="tel:+420491601771" class="hover:text-indigo-400 transition">491 601 771</a>
                    </p>
                    <p class="text-[0.7rem] text-slate-500">
                        Po–Pá 16:00–22:00<br>
                        So, Ne, svátky 8:00–22:00
                    </p>
                </div>

                <div class="space-y-1 pt-2">
                    <p class="text-slate-300 font-medium">Hradec Králové</p>
                    <p class="text-slate-400">
                        📞 <a href="tel:+420495832826" class="hover:text-indigo-400 transition">495 832 826</a>
                    </p>
                    <p class="text-[0.7rem] text-slate-500">
                        Po–Pá 15:30–22:00<br>
                        So, Ne, svátky 8:00–22:00
                    </p>
                </div>
            </div>

            <!-- Rychlé odkazy -->
            <div class="space-y-4 text-sm">
                <h3 class="text-base font-semibold text-white">Rychlé odkazy</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="<?= BASE_URL ?>/#hours" class="flex items-center text-slate-300 hover:text-indigo-300 transition">
                            <span class="mr-2 inline-block h-4 w-4 border border-slate-500 rounded-full"></span>
                            Ordinační hodiny
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/#services" class="flex items-center text-slate-300 hover:text-indigo-300 transition">
                            <span class="mr-2 inline-block h-4 w-4 border border-slate-500 rounded-full"></span>
                            Naše služby
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/#booking" class="flex items-center text-slate-300 hover:text-indigo-300 transition">
                            <span class="mr-2 inline-block h-4 w-4 border border-slate-500 rounded-full"></span>
                            Jak se objednat
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/#contact" class="flex items-center text-slate-300 hover:text-indigo-300 transition">
                            <span class="mr-2 inline-block h-4 w-4 border border-slate-500 rounded-full"></span>
                            Kontakt &amp; registrace
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/#galerie" class="flex items-center text-slate-300 hover:text-indigo-300 transition">
                            <span class="mr-2 inline-block h-4 w-4 border border-slate-500 rounded-full"></span>
                            Galerie ordinace
                        </a>
                    </li>
                    <li>
                        <a href="https://my.medevio.cz/mudr-albahri"
                            target="_blank" rel="noopener noreferrer"
                            class="flex items-center text-indigo-300 hover:text-indigo-200 transition">
                            <span class="mr-2 inline-block h-4 w-4 border border-indigo-400 rounded-full"></span>
                            Objednání přes Medevio Pedia
                        </a>
                    </li>
                    <li>
                        <a href="https://my.medevio.cz/pedia-az"
                            target="_blank" rel="noopener noreferrer"
                            class="flex items-center text-indigo-300 hover:text-indigo-200 transition">
                            <span class="mr-2 inline-block h-4 w-4 border border-indigo-400 rounded-full"></span>
                            Objednání přes Medevio Pedia AZ
                        </a>
                    </li>
                </ul>
                <!-- Facebook Link (samostatný blok mimo seznam) -->
                <div class="pt-4 border-t border-slate-800">
                    <a href="https://www.facebook.com/profile.php?id=61584071354442"
                        target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-3 text-blue-300 hover:text-blue-200 transition">

                        <!-- Ikona Facebook -->
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 fill-current"
                            viewBox="0 0 24 24">
                            <path d="M22 12.06C22 6.51 17.52 2 12 2S2 6.51 2 12.06C2 17.08 5.66 21.22 10.44 22v-6.9H7.9v-3.03h2.54V9.74c0-2.5 1.48-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 3.03h-2.34V22C18.34 21.22 22 17.08 22 12.06Z" />
                        </svg>

                        <span class="text-sm">Navštivte nás na Facebooku</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Spodní lišta -->
        <div class="border-t border-slate-800 pt-4 mt-2">
            <div class="flex flex-col gap-3 items-center justify-between text-xs text-slate-500 md:flex-row">
                <p>
                    &copy; <?= date('Y'); ?> PEDIA s.r.o. &amp; PEDIA AZ s.r.o. – MUDr. Ziad Albahri, Ph.D. Všechna práva vyhrazena.
                </p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="<?= BASE_URL ?>/gdpr" class="hover:text-indigo-300 transition">
                        Ochrana osobních údajů
                    </a>
                    <a href="<?= BASE_URL ?>/gdpr/tos" class="hover:text-indigo-300 transition">
                        Podmínky použití
                    </a>
                    <a href="<?= BASE_URL ?>/cookies/settings" class="hover:text-indigo-300 transition">
                        Nastavení cookies
                    </a>
                    <!-- Vyrobila iVision Media -->
                    <span class="hidden md:inline text-slate-700">|</span>
                    <a
                        href="https://ivisionmedia.cz/"
                        target="_blank"
                        rel="nofollow sponsored noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-full"
                        aria-label="Web vytvořila iVision Media"
                        title="Web vytvořila iVision Media">
                        <img
                            src="https://www.ivisionmedia.cz/assets/images/logo/ivision_media_logo_new.png"
                            alt="iVision Media"
                            class="h-4 w-auto "
                            loading="lazy">
                        <span class="text-[0.7rem] text-slate-300/90">
                            Web vytvořila iVision Media
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>