<?php
/* ***production ****/
//define('BASE_URL', '');
//define('SITE_URL', 'https://web.kalasekvyvoj.cz');
//define('BASE_PATH', dirname(dirname(__FILE__)));

/* *** TEST  ****/
define('BASE_URL', '/__Framework/1_v0/public');
define('BASE_PATH', dirname(dirname(__FILE__)));
define('SITE_URL', 'http://localhost/05_WWW/__Framework/1_v0');

// SMTP nastavení pro PHPMailer
define('SMTP_HOST', 'smtp.gmail.com'); // Nahraď svým SMTP serverem
define('SMTP_USERNAME', 'kalasekvyvoj@gmail.com'); // Nahraď svým uživatelským jménem
define('SMTP_PASSWORD', ''); // Nahraď svým heslem
define('SMTP_PORT', 587); // Většinou 587 nebo 465
define('MAIL_FROM_ADDRESS', 'noreply@kalasekvyvoj.cz');
define('MAIL_FROM_NAME', 'Kali-Framework');

// Google reCAPTCHA v3
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// Error reporting

error_reporting(E_ALL);

ini_set('display_errors', 1);



// Načtení databázové konfigurace

$config = require_once ROOT_PATH . '/src/config/database.php';