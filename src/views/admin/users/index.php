<?php
// src/views/admin/users/index.php
// Seznam uživatelů pro admina
?>

<div class="max-w-full mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-white">Správa uživatelů</h1>
            <a href="<?= BASE_URL ?>/admin/users/create" class="bg-white text-blue-600 px-4 py-2 rounded shadow-sm hover:bg-blue-50 transition-colors">
                Přidat uživatele
            </a>
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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jméno</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kredit</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vytvořeno</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akce</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $user['id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($user['phone'] ?: '---') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' ?>">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= number_format($user['credit_balance'], 2, ',', ' ') ?> Kč</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="<?= BASE_URL ?>/admin/users/edit/<?= $user['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Upravit</a>
                                    <a href="<?= BASE_URL ?>/credit/admin/add?user_id=<?= $user['id'] ?>" class="text-green-600 hover:text-green-800 mr-3">Dobít kredit</a>
                                    
                                    <button 
                                        onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?>')" 
                                        class="text-red-600 hover:text-red-900 cursor-pointer">
                                        Smazat
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="flex justify-between mb-8">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Zpět na dashboard
        </a>
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
</script>