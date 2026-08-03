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
            <form action="<?= locale_url('login/process') ?>" method="post" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Heslo</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember_me"
                            name="remember_me"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700 cursor-pointer">
                            Zapamatovat si mě
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="<?= locale_url('password/reset') ?>" class="font-medium text-blue-600 hover:text-blue-500">
                            Zapomenuté heslo?
                        </a>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer">
                        Přihlásit se
                    </button>
                </div>
            </form>

            <div class="mt-4">
                <a
                    href="<?= locale_url('auth/google/redirect') ?>"
                    class="w-full inline-flex items-center justify-center gap-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-1.5 3.9-5.5 3.9-3.3 0-6-2.7-6-6s2.7-6 6-6c1.9 0 3.2.8 3.9 1.5l2.7-2.6C16.9 3.3 14.7 2.4 12 2.4 6.9 2.4 2.8 6.5 2.8 11.6S6.9 20.8 12 20.8c6.9 0 9.1-4.8 9.1-7.3 0-.5-.1-.9-.1-1.3H12z" />
                        <path fill="#34A853" d="M2.8 11.6c0 1.6.4 3.1 1.2 4.4l3.4-2.6c-.2-.5-.4-1.1-.4-1.8s.1-1.2.4-1.8L4 7.2c-.8 1.3-1.2 2.8-1.2 4.4z" />
                        <path fill="#FBBC05" d="M12 20.8c2.7 0 4.9-.9 6.6-2.4l-3.2-2.5c-.9.6-2 .9-3.4.9-2.6 0-4.9-1.8-5.7-4.2L3 15.1c1.8 3.5 5.1 5.7 9 5.7z" />
                        <path fill="#4285F4" d="M18.6 18.4c1.9-1.7 3-4.1 3-6.8 0-.7-.1-1.2-.2-1.8H12v3.9h5.5c-.3 1.5-1.2 2.8-2.3 3.7l3.4 2.6z" />
                    </svg>

                    <span>Přihlásit se přes Google</span>
                </a>
            </div>

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
                        <a href="<?= locale_url('register') ?>" class="font-medium text-blue-600 hover:text-blue-500">
                            Zaregistrujte se
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
