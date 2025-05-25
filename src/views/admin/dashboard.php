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




</div>





<div class="bg-white rounded-lg shadow-md border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Rychlé akce</h2>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">




        <a href="<?= BASE_URL ?>/admin/users/create" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Přidat uživatele</span>
        </a>



    </div>


</div>