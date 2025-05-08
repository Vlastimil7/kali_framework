<?php
// src/views/users/login.php
// Pohled pro přihlášení uživatele

// Získání případných dat z předchozího odeslání s chybami
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="max-w-md mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Přihlášení</h1>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-100 border-l-4 border-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-500 text-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-700 p-4" role="alert">
                <p><?= $_SESSION['flash_message'] ?></p>
            </div>
            <?php 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>

        <div class="p-6 space-y-6">
            <form action="<?= BASE_URL ?>/login/process" method="post" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars($formData['email'] ?? '') ?>" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                        required
                    >
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Heslo</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                        required
                    >
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember_me" 
                            name="remember_me" 
                            type="checkbox" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer"
                        >
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700 cursor-pointer">
                            Zapamatovat si mě
                        </label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="<?= BASE_URL ?>/password/reset" class="font-medium text-blue-600 hover:text-blue-500">
                            Zapomenuté heslo?
                        </a>
                    </div>
                </div>
                
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer"
                    >
                        Přihlásit se
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Nebo
                        </span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <p class="text-center text-sm text-gray-600">
                        Nemáte ještě účet? 
                        <a href="<?= BASE_URL ?>/register" class="font-medium text-blue-600 hover:text-blue-500">
                            Zaregistrujte se
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>