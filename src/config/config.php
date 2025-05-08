<?php
/* ***production ****/
//define('BASE_URL', '');
//define('SITE_URL', 'https://web.kalasekvyvoj.cz');
//define('BASE_PATH', dirname(dirname(__FILE__)));

/* *** TEST  ****/
define('BASE_URL', '/999_Formedia/superkrabicky/public');
define('BASE_PATH', dirname(dirname(__FILE__)));
define('SITE_URL', 'http://localhost/999_Formedia/superkrabicky');

// Error reporting

error_reporting(E_ALL);

ini_set('display_errors', 1);



// Načtení databázové konfigurace

$config = require_once ROOT_PATH . '/src/config/database.php';