<?php
// src/views/admin/users/edit.php
// Editace uživatele pro admina
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Úprava uživatele</h1>
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
            <form action="<?= BASE_URL ?>/admin/users/update/<?= $user['id'] ?>" method="post" class="space-y-6">
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
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select id="role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Uživatel</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="credit_balance" class="block text-sm font-medium text-gray-700 mb-1">Kredit (Kč)</label>
                        <input type="number" id="credit_balance" name="credit_balance" value="<?= htmlspecialchars($user['credit_balance']) ?>" 
                               step="0.01" min="0" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="allow_debit" name="allow_debit" value="1" <?= $user['allow_debit'] ? 'checked' : '' ?> 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="allow_debit" class="ml-2 block text-sm text-gray-900">
                        Povolit záporný kredit
                    </label>
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
                
                <div class="flex justify-between mt-8">
                    <a href="<?= BASE_URL ?>/admin/users" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Zpět na seznam
                    </a>
                    
                    <div class="flex space-x-4">
                        <button type="button" onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?>')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Smazat uživatele
                        </button>
                        
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Uložit změny
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pro potvrzení smazání -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-auto">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Potvrdit smazání</h3>
        <p class="text-gray-600 mb-6">Opravdu chcete smazat uživatele <span id="userName" class="font-semibold"></span>?</p>
        
        <div class="flex justify-end space-x-4">
            <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Zrušit
            </button>
            
            <form id="deleteForm" method="post" action="">
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Smazat
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDelete(userId, name) {
        document.getElementById('userName').textContent = name;
        document.getElementById('deleteForm').action = '<?= BASE_URL ?>/admin/users/delete/' + userId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
    
    // Zavřít modal při kliknutí mimo něj
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Validace formuláře
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        
        if (password && password !== passwordConfirm) {
            e.preventDefault();
            alert('Hesla se neshodují!');
        }
    });
</script>