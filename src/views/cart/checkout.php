<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-white mb-6">Dokončení objednávky</h1>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-100 border-l-4 border-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-500 text-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-700 p-4 mb-6">
            <p><?= $_SESSION['flash_message'] ?></p>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 bg-gray-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Fakturační údaje</h2>
                </div>

                <form action="<?= BASE_URL ?>/cart/create-order" method="post" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jméno a příjmení *</label>
                            <input name="billing_name" required class="w-full border rounded-md px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input name="billing_email" type="email" required class="w-full border rounded-md px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                            <input name="billing_phone" required class="w-full border rounded-md px-3 py-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ulice *</label>
                            <input name="billing_street" required class="w-full border rounded-md px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Číslo popisné *</label>
                            <input name="billing_house_no" required class="w-full border rounded-md px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Město *</label>
                            <input name="billing_city" required class="w-full border rounded-md px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PSČ *</label>
                            <input name="billing_zip" required class="w-full border rounded-md px-3 py-2" />
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Firma (volitelné)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Název firmy</label>
                                <input name="billing_company" class="w-full border rounded-md px-3 py-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">IČO</label>
                                <input name="billing_ico" class="w-full border rounded-md px-3 py-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">DIČ</label>
                                <input name="billing_dic" class="w-full border rounded-md px-3 py-2" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Informace pro voucher (volitelné)</h3>

                        <?php foreach ($cart['items'] as $key => $item): ?>
                            <div class="p-4 mb-4 bg-gray-50 rounded-md">
                                <div class="font-medium text-gray-900">
                                    <?= htmlspecialchars($item['name']) ?> (<?= (int)$item['quantity'] ?>×)
                                </div>

                                <input type="hidden" name="item_key[]" value="<?= htmlspecialchars($key) ?>">

                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pro koho je voucher</label>
                                        <input name="recipient_name[<?= htmlspecialchars($key) ?>]" class="w-full border rounded-md px-3 py-2" placeholder="např. Petr Novák" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Vzkaz do voucheru</label>
                                        <input name="note[<?= htmlspecialchars($key) ?>]" class="w-full border rounded-md px-3 py-2" placeholder="např. Všechno nejlepší!" />
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <P class="block text-center">Po vytvoření objednávky budete přesměrováni na platební bránu Comgate.</P>
                    <a href="<?= BASE_URL ?>/terms/shipping-payment" target="_blank" rel="noopener noreferrer">
                        <img
                            src="<?= BASE_URL ?>/assets/images/comgate/comgate_footer_white.png"
                            alt="Platebni podminky Comgate"
                            title="Platebni podminky Comgate"
                            class="mt-6 mb-6 h-8 w-auto block mx-auto"
                            loading="lazy">
                    </a>

                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg cursor-pointer">
                        Vytvořit objednávku
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden sticky top-6">
                <div class="p-6 bg-gray-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Souhrn</h2>
                </div>
                <div class="p-6 space-y-3">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="flex justify-between text-sm">
                            <span><?= htmlspecialchars($item['name']) ?> × <?= (int)$item['quantity'] ?></span>
                            <span class="font-medium">
                                <?= number_format(((int)$item['unit_price_cents'] * (int)$item['quantity']) / 100, 0, ',', ' ') ?> Kč
                            </span>
                        </div>
                    <?php endforeach; ?>

                    <div class="border-t pt-4 flex justify-between">
                        <span class="font-bold">Celkem</span>
                        <span class="font-bold text-green-600">
                            <?= number_format(((int)$cart['total_amount_cents']) / 100, 0, ',', ' ') ?> Kč
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // ===== helpers =====
        const setInvalid = (el, isInvalid) => {
            el.classList.toggle('border-red-500', isInvalid);
            el.classList.toggle('ring-2', isInvalid);
            el.classList.toggle('ring-red-200', isInvalid);
        };

        // jen čísla
        const onlyDigits = (el) => {
            el.setAttribute('inputmode', 'numeric');
            el.addEventListener('input', () => {
                el.value = el.value.replace(/\D+/g, '');
                setInvalid(el, false);
            });
        };

        // jméno/město: písmena (vč. CZ), mezera, tečka, pomlčka
        const onlyLettersBasic = (el) => {
            el.addEventListener('input', () => {
                el.value = el.value.replace(/[^a-zA-Zá-žÁ-Ž .\-]/g, '');
                setInvalid(el, false);
            });
        };

        // číslo popisné: čísla + max 1 lomítko (718/6)
        const houseNumber = (el) => {
            el.setAttribute('inputmode', 'text');
            el.addEventListener('input', () => {
                // povol jen čísla a lomítko
                let v = el.value.replace(/[^0-9/]/g, '');

                // jen jedno lomítko
                const firstSlash = v.indexOf('/');
                if (firstSlash !== -1) {
                    v = v.slice(0, firstSlash + 1) + v.slice(firstSlash + 1).replace(/\//g, '');
                }

                // nepovol začínat lomítkem
                v = v.replace(/^\/+/, '');

                el.value = v;
                setInvalid(el, false);
            });
        };

        // musí obsahovat aspoň jedno písmeno
        const mustContainLetter = (el) => /[a-zA-Zá-žÁ-Ž]/.test((el.value || '').trim());

        // ===== apply sanitizers =====
        const billingName = document.querySelector('input[name="billing_name"]');
        if (billingName) onlyLettersBasic(billingName);

        const billingCity = document.querySelector('input[name="billing_city"]');
        if (billingCity) onlyLettersBasic(billingCity);

        document.querySelectorAll('input[name="billing_phone"]').forEach(onlyDigits);
        document.querySelectorAll('input[name="billing_zip"]').forEach(onlyDigits);
        document.querySelectorAll('input[name="billing_ico"]').forEach(onlyDigits);
        document.querySelectorAll('input[name="billing_dic"]').forEach(onlyDigits);

        const houseNo = document.querySelector('input[name="billing_house_no"]');
        if (houseNo) houseNumber(houseNo);

        // ===== note max 500 + counter =====
        const MAX_NOTE = 500;
        const noteInputs = Array.from(document.querySelectorAll('input[name^="note"]'));

        noteInputs.forEach(input => {
            const counter = document.createElement('div');
            counter.className = 'text-xs text-gray-500 mt-1';
            input.after(counter);

            const update = () => {
                if (input.value.length > MAX_NOTE) {
                    input.value = input.value.substring(0, MAX_NOTE);
                }
                counter.textContent = `${input.value.length} / ${MAX_NOTE} znaků`;
                setInvalid(input, false);
            };

            input.addEventListener('input', update);
            update();
        });

        // ===== final validation on submit =====
        const form = document.querySelector('form[action$="/cart/create-order"]');
        if (!form) return;

        form.addEventListener('submit', (e) => {

            // Jméno: musí obsahovat písmeno
            if (billingName && !mustContainLetter(billingName)) {
                e.preventDefault();
                setInvalid(billingName, true);
                alert('Jméno a příjmení musí obsahovat alespoň jedno písmeno.');
                billingName.focus();
                return;
            }

            // Město: musí obsahovat písmeno
            if (billingCity && !mustContainLetter(billingCity)) {
                e.preventDefault();
                setInvalid(billingCity, true);
                alert('Město musí obsahovat alespoň jedno písmeno.');
                billingCity.focus();
                return;
            }

            // Číslo popisné: musí být např. 718 nebo 718/6 (ne /6, ne 718//6)
            if (houseNo) {
                const v = (houseNo.value || '').trim();
                const ok = /^\d+(\/\d+)?$/.test(v);
                if (!ok) {
                    e.preventDefault();
                    setInvalid(houseNo, true);
                    alert('Číslo popisné musí být ve tvaru např. 718 nebo 718/6.');
                    houseNo.focus();
                    return;
                }
            }

            // Vzkaz max 500
            for (const input of noteInputs) {
                if ((input.value || '').length > MAX_NOTE) {
                    e.preventDefault();
                    setInvalid(input, true);
                    alert('Vzkaz do voucheru může mít maximálně 500 znaků.');
                    input.focus();
                    return;
                }
            }
        });

    });
</script>