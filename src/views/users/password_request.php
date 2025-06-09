<?php
// Pohled pro žádost o reset hesla

// Získání případných dat z předchozího odeslání s chybami
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="max-w-md mx-auto">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Zapomenuté heslo</h1>
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
            <p class="text-gray-600">Zadejte svůj e-mail a my vám zašleme instrukce pro reset hesla.</p>
            
            <form id="reset-form" action="<?= BASE_URL ?>/password/email" method="post" class="space-y-4" onsubmit="return validateForm()">
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
                
                <!-- Skryté pole pro reCAPTCHA token -->
                <input type="hidden" id="recaptcha-token" name="recaptcha_token">
                
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer"
                    >
                        Odeslat odkaz pro reset hesla
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
        
        // Validace vstupu
        const email = document.getElementById('email').value;
        if (!email) {
            alert('Zadejte prosím emailovou adresu');
            return false;
        }
        
        // Získání reCAPTCHA tokenu
        grecaptcha.ready(function() {
            grecaptcha.execute('<?= RECAPTCHA_SITE_KEY ?>', {action: 'password_reset'}).then(function(token) {
                console.log('reCAPTCHA token:', token);
                document.getElementById('recaptcha-token').value = token;
                document.getElementById('reset-form').submit();
            });
        });
        
        return false; // Formulář bude odeslán až po získání tokenu
    }
</script>