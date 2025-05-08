<?php
// src/views/admin/users/add_credit.php
// Dobití kreditu uživateli (admin sekce)
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Dobít kredit</h1>
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
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?></h2>
                <p class="text-gray-600">Aktuální kredit: <span class="font-semibold"><?= number_format($user['credit_balance'], 2, ',', ' ') ?> Kč</span></p>
            </div>
            
            <form action="<?= BASE_URL ?>/admin/users/credit/process/<?= $user['id'] ?>" method="post" class="space-y-6">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Částka k dobití (Kč)</label>
                    <input type="number" id="amount" name="amount" value="0" 
                           step="0.01" min="0.01" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <div class="flex justify-between mt-8">
                    <a href="<?= BASE_URL ?>/admin/users/edit/<?= $user['id'] ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Zpět
                    </a>
                    
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Dobít kredit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>