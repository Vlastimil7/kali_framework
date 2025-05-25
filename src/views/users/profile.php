<?php
// src/views/users/profile.php
// Pohled pro zobrazení a úpravu profilu uživatele
?>

<div class="max-w-full mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Profil uživatele</h1>
        </div>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-100 border-l-4 border-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-500 text-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-700 p-4 mb-4" role="alert">
                <p><?= $_SESSION['flash_message'] ?></p>
            </div>
            <?php 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Informace o účtu</h2>
                    
                    <div class="mb-3">
                        <span class="block text-sm font-medium text-gray-500">Email</span>
                        <span class="text-gray-900"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <span class="block text-sm font-medium text-gray-500">Jméno</span>
                        <span class="text-gray-900"><?= htmlspecialchars($user['name']) ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <span class="block text-sm font-medium text-gray-500">Příjmení</span>
                        <span class="text-gray-900"><?= htmlspecialchars($user['surname']) ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <span class="block text-sm font-medium text-gray-500">Telefon</span>
                        <span class="text-gray-900"><?= htmlspecialchars($user['phone'] ?: '---') ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <span class="block text-sm font-medium text-gray-500">Kredit</span>
                        <span class="text-gray-900 font-medium"><?= number_format($user['credit_balance'], 2, ',', ' ') ?> Kč</span>
                    </div>
                    
                    <div class="mb-3">
                        <span class="block text-sm font-medium text-gray-500">Účet vytvořen</span>
                        <span class="text-gray-900"><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Poslední objednávky</h2>
                    
                    <?php if (isset($orders) && count($orders) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($orders as $order): ?>
                                <div class="border-b border-gray-200 pb-2">
                                    <div class="flex justify-between">
                                        <span class="font-medium"><?= date('d.m.Y', strtotime($order['created_at'])) ?></span>
                                        <span class="<?= $order['status'] === 'completed' ? 'text-green-600' : 'text-blue-600' ?>"><?= htmlspecialchars($order['status']) ?></span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Objednávka #<?= $order['id'] ?> - <?= number_format($order['total_price'], 2, ',', ' ') ?> Kč
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4">
                            <a href="<?= BASE_URL ?>/orders" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Zobrazit všechny objednávky →</a>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">Zatím nemáte žádné objednávky.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-lg mt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Upravit profil</h2>
                
                <form action="<?= BASE_URL ?>/profile/update" method="post" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Jméno</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div>
                            <label for="surname" class="block text-sm font-medium text-gray-700 mb-1">Příjmení</label>
                            <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($user['surname']) ?>" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Změna hesla</h3>
                        <p class="text-sm text-gray-600 mb-4">Ponechte prázdné, pokud nechcete měnit heslo.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nové heslo</label>
                                <input type="password" id="password" name="password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1">Potvrzení hesla</label>
                                <input type="password" id="password_confirm" name="password_confirm" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end mt-6">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer">
                            Uložit změny
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="flex justify-between">
        <a href="<?= BASE_URL ?>/dashboard" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Zpět na přehled
        </a>
        
        <a href="<?= BASE_URL ?>/logout" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            Odhlásit se
        </a>
    </div>
</div>

<script>
    // Klientská validace pro potvrzení hesla
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        
        if (password && password !== passwordConfirm) {
            e.preventDefault();
            alert('Hesla se neshodují!');
        }
    });
</script>