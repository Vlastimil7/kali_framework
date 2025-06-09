<!-- Cookie Settings Section -->
<section id="cookie-settings" class="py-20 px-6 relative z-10 bg-gray-900">
    <div class="container mt-32 mx-auto">
        <div class="text-center mb-16 animate-on-scroll">
            <h2 class="text-4xl font-bold mb-4 text-white">Nastavení <span class="text-green-400">cookies</span></h2>
            <p class="text-xl text-white max-w-3xl mx-auto">
                Vyberte si, jaké cookies chcete povolit. Níže můžete změnit výchozí nastavení.
            </p>
        </div>

        <div class="max-w-4xl mx-auto animate-on-scroll">
            <div class="card p-8">
                <form action="<?= BASE_URL ?>/cookies/save" method="POST">
                    <div class="space-y-8">
                        <!-- Nezbytné cookies -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="mb-4 md:mb-0 md:pr-8">
                                    <h3 class="text-xl font-bold mb-2 text-gray-800">Nezbytné cookies</h3>
                                    <p class="text-gray-600">
                                        Tyto cookies jsou nezbytné pro správné fungování webových stránek a nemohou být vypnuty.
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="necessary" id="necessary" checked disabled
                                        class="w-5 h-5 text-green-500 focus:ring-green-400 border-gray-300 rounded">
                                    <label for="necessary" class="ml-2 text-gray-500 italic text-sm">Vždy aktivní</label>
                                </div>
                            </div>
                        </div>

                        <!-- Analytické cookies -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="mb-4 md:mb-0 md:pr-8">
                                    <h3 class="text-xl font-bold mb-2 text-gray-800">Analytické cookies</h3>
                                    <p class="text-gray-600">
                                        Pomáhají nám pochopit, jak návštěvníci používají naše stránky. Díky tomu můžeme vylepšovat funkčnost a obsah.
                                    </p>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="analytics" id="analytics" value="1"
                                            <?= $preferences['analytics'] ? 'checked' : '' ?>
                                            class="sr-only peer">
                                        <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Marketingové cookies -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="mb-4 md:mb-0 md:pr-8">
                                    <h3 class="text-xl font-bold mb-2 text-gray-800">Marketingové cookies</h3>
                                    <p class="text-gray-600">
                                        Používají se k sledování návštěvníků napříč webovými stránkami. Záměrem je zobrazit reklamy, které jsou relevantní a zajímavé pro jednotlivé uživatele.
                                    </p>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="marketing" id="marketing" value="1"
                                            <?= $preferences['marketing'] ? 'checked' : '' ?>
                                            class="sr-only peer">
                                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Preferenční cookies -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="mb-4 md:mb-0 md:pr-8">
                                    <h3 class="text-xl font-bold mb-2 text-gray-800">Preferenční cookies</h3>
                                    <p class="text-gray-600">
                                        Umožňují webové stránce zapamatovat si informace, které mění způsob, jakým se webová stránka chová nebo jak vypadá.
                                    </p>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="preferences" id="preferences" value="1"
                                            <?= $preferences['preferences'] ? 'checked' : '' ?>
                                            class="sr-only peer">
                                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-4 mt-12">
                        <a href="<?= BASE_URL ?>/cookies/reject-all" class="btn-outline px-8 py-3 rounded bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors">
                            Odmítnout vše
                        </a>
                        <a href="<?= BASE_URL ?>/cookies/accept-all" class="btn-outline px-8 py-3 rounded bg-green-500 border border-green-500 text-white hover:bg-green-600 transition-colors">
                            Přijmout vše
                        </a>
                        <button type="submit" class="btn-primary px-8 py-3 rounded cursor-pointer bg-blue-500 border border-blue-500 text-white hover:bg-blue-600 transition-colors">
                            Uložit nastavení
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>