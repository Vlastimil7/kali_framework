<footer class="w-full bg-[black] text-slate-200">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="grid gap-10 lg:grid-cols-3 items-start">

            <!-- LEFT: Logo + CTA -->
            <div
                class="space-y-6
         flex flex-col
         items-start
         text-left
         min-[1123px]:items-start">
                <a href="<?= BASE_URL ?>/" class="inline-block">
                    <img
                        src="<?= BASE_URL ?>/assets/images/logo/mido_barbershop_01_logo.png"
                        alt="Mido Barbershop"
                        class="h-24 w-auto"
                        loading="lazy">
                </a>

                <a
                    href="tel:+420777711135"
                    class="inline-flex items-center gap-3 rounded-md bg-[#b38700] px-6 py-4 font-semibold text-black hover:bg-[#c59600] transition">
                    <span aria-hidden="true"></span>
                    Rezervujte si místo!
                </a>
            </div>

            <!-- MIDDLE: Contacts + Hours + Social -->
            <div class="space-y-8 text-sm">
                <!-- Contacts -->
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold text-white">Kontaktujte nás</h3>

                    <div class="text-slate-300 space-y-1">
                        <p class="font-medium text-[#b38700]">Mido Barbershop</p>
                        <p class="text-slate-400">
                            nám. Svobody 372/3<br>
                            500 02 Hradec Králové<br>
                            Czech Republic
                        </p>
                    </div>

                    <div class="text-slate-400 space-y-1 pt-2">
                        <p>
                            tel:
                            <a href="tel:+420777711135" class="text-[#b38700] hover:text-[#d2a400] transition">
                                +420 7777 111 35
                            </a>
                        </p>
                        <p>
                            email:
                            <a href="mailto:reception@midobarbershop.com" class="text-[#b38700] hover:text-[#d2a400] transition">
                                reception@midobarbershop.com
                            </a>
                        </p>
                        <p>
                            La Mido invest s.r.o.
                        <p>
                            ICO: 10779990
                        </p>
                    </div>
                </div>

                <!-- Opening Hours -->
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-white">Otevírací Doba:</h3>
                    <div class="space-y-1 text-slate-400">
                        <p><span class="text-[#b38700]">PO- PÁ:</span> 8:00 – 20:00</p>
                        <p><span class="text-[#b38700]">SO:</span> 10:00 – 19:00</p>
                        <p><span class="text-[#b38700]">NE:</span> volání rezervovat</p>
                    </div>
                </div>

                <!-- Social -->
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold text-white">Sledujte nás!</h3>

                    <div class="flex items-center gap-4">
                        <!-- Facebook -->
                        <a href="https://www.facebook.com/profile.php?id=100063520150213" target="_blank" rel="noopener noreferrer"
                            class="h-11 w-11 grid place-items-center rounded-md bg-[#1e3a8a] hover:opacity-90 transition"
                            aria-label="Facebook">
                            <!-- simple icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5 fill-white">
                                <path d="M22 12.06C22 6.51 17.52 2 12 2S2 6.51 2 12.06C2 17.08 5.66 21.22 10.44 22v-6.9H7.9v-3.03h2.54V9.74c0-2.5 1.48-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 3.03h-2.34V22C18.34 21.22 22 17.08 22 12.06Z" />
                            </svg>
                        </a>

                        <!-- Youtube -->
                        <a href="https://www.youtube.com/channel/UCK1atG4tM3-fqu7BrwZEblA" target="_blank" rel="noopener noreferrer"
                            class="h-11 w-11 grid place-items-center rounded-md bg-[#dc2626] hover:opacity-90 transition"
                            aria-label="YouTube">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5 fill-white">
                                <path d="M21.6 7.2a3 3 0 0 0-2.1-2.1C17.8 4.6 12 4.6 12 4.6s-5.8 0-7.5.5A3 3 0 0 0 2.4 7.2 31.6 31.6 0 0 0 2 12a31.6 31.6 0 0 0 .4 4.8 3 3 0 0 0 2.1 2.1c1.7.5 7.5.5 7.5.5s5.8 0 7.5-.5a3 3 0 0 0 2.1-2.1A31.6 31.6 0 0 0 22 12a31.6 31.6 0 0 0-.4-4.8ZM10 15.5v-7l6 3.5-6 3.5Z" />
                            </svg>
                        </a>

                        <!-- WhatsApp -->
                        <a href="https://api.whatsapp.com/send?phone=420777711135" target="_blank" rel="noopener noreferrer"
                            class="h-11 w-11 grid place-items-center rounded-md bg-[#16a34a] hover:opacity-90 transition"
                            aria-label="WhatsApp">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5 fill-white">
                                <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.8 14.4c-.3.8-1.6 1.5-2.3 1.6-.5.1-1.1.1-1.8-.1-1.6-.5-3.6-1.8-5.1-3.3-1.4-1.4-2.8-3.5-3.3-5.1-.2-.7-.2-1.3-.1-1.8.1-.7.8-2 1.6-2.3.4-.2.7-.2 1 0l.7 1.7c.1.3.1.6 0 .9-.1.2-.2.4-.4.6l-.3.3c-.2.2-.3.4-.2.6.3 1 1.7 2.9 3.5 4 .2.1.5 0 .7-.2l.3-.3c.2-.2.4-.3.6-.4.3-.1.6-.1.9 0l1.7.7c.2.3.2.6 0 1Z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Map -->
            <div class="w-full">
                <div class="overflow-hidden rounded-md border border-white/10">
                    <iframe
                        title="Mido Barbershop mapa"
                        src="https://www.google.com/maps?q=n%C3%A1m.%20Svobody%20372/3%20Hradec%20Kr%C3%A1lov%C3%A9&output=embed"
                        class="w-full h-[320px] lg:h-[360px]"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <a href="<?= BASE_URL ?>/terms/shipping-payment" target="_blank" rel="noopener noreferrer">
                    <img
                        src="<?= BASE_URL ?>/assets/images/comgate/comgate_footer.png"
                        alt="Platebni podminky Comgate"
                        title="Platebni podminky Comgate"
                        class="mt-6 h-8 w-auto"
                        loading="lazy">
                </a>
            </div>

        </div>
    </div>
    <!-- FOOTER BOTTOM: Legal -->
    <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 py-4
                flex flex-col sm:flex-row items-center justify-between
                text-xs text-slate-400 gap-2">

            <div>
                © <?= date('Y') ?> Mido Barbershop. Všechna práva vyhrazena.
            </div>

            <div class="flex items-center gap-4">
                <a href="<?= BASE_URL ?>/cookies/settings"
                    class="hover:text-[#b38700] transition underline-offset-4 hover:underline">
                    Cookies
                </a>

                <a href="<?= BASE_URL ?>/gdpr"
                    class="hover:text-[#b38700] transition underline-offset-4 hover:underline">
                    Ochrana osobních údajů
                </a>

                <a href="<?= BASE_URL ?>/terms"
                    class="hover:text-[#b38700] transition underline-offset-4 hover:underline">
                    Obchodní podmínky
                </a>
            </div>
        </div>
    </div>
</footer>