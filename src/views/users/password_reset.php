<?php
// Pohled pro reset hesla
?>

<div class="max-w-md mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Reset hesla</h1>
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
            <p class="text-gray-600">Zadejte nové heslo.</p>
            
            <form id="reset-form" action="<?= BASE_URL ?>/password/update" method="post" class="space-y-4" onsubmit="return validateForm()">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <!-- Skryté pole pro reCAPTCHA token -->
                <input type="hidden" id="recaptcha-token" name="recaptcha_token">
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nové heslo</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                        required
                        minlength="6"
                    >
                    <p class="text-xs text-gray-500 mt-1">Heslo musí mít alespoň 6 znaků</p>
                </div>
                
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1">Potvrzení hesla</label>
                    <input 
                        type="password" 
                        id="password_confirm" 
                        name="password_confirm" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                        required
                    >
                </div>
                
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer"
                    >
                        Resetovat heslo
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
                        <a href="<?= BASE_URL ?>/login" class="font-medium text-blue-600 hover:text-blue-500">
                            Zpět na přihlášení
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google reCAPTCHA v3 -->
<script src="https://www.google.com/recaptcha/api.js?render=<?= RECAPTCHA_SITE_KEY ?>"></script>
<script>
    function validateForm() {
        event.preventDefault(); // Zastaví výchozí akci formuláře
        
        // Kontrola shodnosti hesel
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('password_confirm').value;
        
        if (password !== confirmPassword) {
            alert('Hesla se neshodují!');
            return false;
        }
        
        // Kontrola minimální délky
        if (password.length < 6) {
            alert('Heslo musí mít alespoň 6 znaků!');
            return false;
        }
        
        // Získání reCAPTCHA tokenu
        grecaptcha.ready(function() {
            grecaptcha.execute('<?= RECAPTCHA_SITE_KEY ?>', {action: 'password_reset_confirm'}).then(function(token) {
                document.getElementById('recaptcha-token').value = token;
                document.getElementById('reset-form').submit();
            });
        });
        
        return false; // Formulář bude odeslán až po získání tokenu
    }
</script>