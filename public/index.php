<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/src/config/config.php';




// Spuštění session
session_start();

// Vylepšený autoloader
spl_autoload_register(function ($className) {
    error_log("Attempting to load class: $className");

    $baseDir = ROOT_PATH . '/src/';

    $namespaceMap = [
       'Api\\V1\\Controllers\\' => 'Api/V1/Controllers/',
        'Api\\' => 'Api/',
        'Core\\' => 'classes/core/',
        'Controllers\\' => 'controllers/',
        'Models\\' => 'models/'
    ];

    foreach ($namespaceMap as $namespace => $path) {
        if (strpos($className, $namespace) === 0) {
            $relativeClass = substr($className, strlen($namespace));
            $file = $baseDir . $path . str_replace('\\', '/', $relativeClass) . '.php';
            
            error_log("Looking for file: " . $file);

            if (file_exists($file)) {
                require_once $file;
                error_log("File loaded: " . $file);
                return true;
            }
        }
    }

    error_log("File not found for class: " . $className);
    return false;
});
// Načtení konfiguračního souboru
use Core\Database;
Database::setConfig($config);

use Core\Router;
use Controllers\HomeController;

$router = new Router();

require_once ROOT_PATH . '/src/routes/web.php'; // Uživatelské cesty
require_once ROOT_PATH . '/src/routes/admin.php'; // Admin cesty
require_once ROOT_PATH . '/src/routes/api.php'; // API cesty

// Načtení language helperu
require_once ROOT_PATH . '/src/helpers/language_helper.php';


// Získání URL z požadavku
$url = $_GET['url'] ?? '';

// Zpracování požadavku
$router->dispatch($url);
Database::getInstance()->closeConnection();
