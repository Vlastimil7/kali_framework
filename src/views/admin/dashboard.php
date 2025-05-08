<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Statistika uživatelů -->
    <div class="bg-blue-50 p-6 rounded-lg shadow border border-blue-100">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-600 bg-opacity-25">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="ml-6">
                <h2 class="text-xl font-bold text-gray-900">Uživatelé</h2>
                <p class="text-3xl font-bold text-blue-600"><?= $userCount ?></p>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?= BASE_URL ?>/admin/users" class="text-blue-600 hover:text-blue-800 font-medium">
                Spravovat uživatele →
            </a>
        </div>
    </div>

    <!-- Statistika objednávek -->
    <div class="bg-green-50 p-6 rounded-lg shadow border border-green-100">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-600 bg-opacity-25">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <div class="ml-6">
                <h2 class="text-xl font-bold text-gray-900">Objednávky</h2>
                <p class="text-3xl font-bold text-green-600"><?= $orderCount ?></p>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?= BASE_URL ?>/admin/orders" class="text-green-600 hover:text-green-800 font-medium">
                Spravovat objednávky →
            </a>
        </div>
    </div>

    <!-- Přehled transakcí -->
    <div class="bg-purple-50 p-6 rounded-lg shadow border border-purple-100">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-600 bg-opacity-25">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div class="ml-6">
                <h2 class="text-xl font-bold text-gray-900">Transakce</h2>
                <p class="text-3xl font-bold text-purple-600"><?= $totalTransactionCount ?? 0 ?></p>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?= BASE_URL ?>/admin/transactions" class="text-purple-600 hover:text-purple-800 font-medium">
                Zobrazit transakce →
            </a>
        </div>
    </div>

</div>

<!-- Druhá řada statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Čekající transakce -->
    <div class="bg-orange-50 p-6 rounded-lg shadow border border-orange-100">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-orange-600 bg-opacity-25">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-6">
                <h2 class="text-xl font-bold text-gray-900">Čekající platby</h2>
                <p class="text-3xl font-bold text-orange-600"><?= $waitingOrdersCount ?? 0 ?></p>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?= BASE_URL ?>/admin/transactions/pending" class="text-orange-600 hover:text-orange-800 font-medium">
                Schválit platby →
            </a>
        </div>
    </div>

    <!-- Nastavení plateb -->
    <div class="bg-indigo-50 p-6 rounded-lg shadow border border-indigo-100">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-600 bg-opacity-25">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="ml-6">
                <h2 class="text-xl font-bold text-gray-900">Bankovní účet</h2>
                <p class="text-3xl font-bold text-indigo-600"><?= $paymentSettingsCount ?? 0 ?></p>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?= BASE_URL ?>/admin/payment-settings" class="text-indigo-600 hover:text-indigo-800 font-medium">
                Spravovat účet →
            </a>
        </div>
    </div>

    <!-- Správa míst doručení -->
    <div class="bg-indigo-50 p-6 rounded-lg shadow border border-indigo-100">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-600 bg-opacity-25">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="ml-6">
                <h2 class="text-xl font-bold text-gray-900">Doručovací adresy</h2>
                <p class="text-3xl font-bold text-indigo-600"><?= $locationCount ?? 0 ?></p>
            </div>
        </div>
        <div class="mt-4">
            <!-- Odkaz na správu míst doručení -->
            <a href="<?= BASE_URL ?>/admin/locations" class="text-indigo-600 hover:text-indigo-800 font-medium">

                Spravovat doručení →
            </a>
        </div>
    </div>

    <!-- Další statistiky můžete přidat podle potřeby -->
    <div class="bg-purple-50 p-6 rounded-lg shadow border border-purple-100">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-600 bg-opacity-25">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div class="ml-6">
                <h2 class="text-xl font-bold text-gray-900">Menu</h2>
                <p class="text-3xl font-bold text-indigo-600"><?= $menuCount ?? 0 ?></p>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?= BASE_URL ?>/admin/menu/items" class="text-purple-600 hover:text-purple-800 font-medium">
                Spravovat menu →
            </a>
        </div>
    </div>
</div>

</div>

<div class="bg-white rounded-lg shadow-md border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Rychlé akce</h2>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">


        <a href="<?= BASE_URL ?>/admin/meals/create" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Přidat jídlo</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/meals/" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Správa jídel</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/menu/categories" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Správa kategorii</span>
        </a>

        <a href="<?= BASE_URL ?>/admin/users/create" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Přidat uživatele</span>
        </a>


        <a href="<?= BASE_URL ?>/admin/payment-settings/add" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Přidat platební nastavení</span>
        </a>

    </div>


</div>