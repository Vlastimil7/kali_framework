<?php 
namespace Core;

use Models\Language;

class Controller {
    protected $language;
    
    public function __construct() {
        // Inicializace jazyka
        $this->language = new Language();
        
        // Určení kategorie podle aktuálního controlleru
        $className = get_class($this);
        $category = $this->detectCategory($className);
        
        // Načtení překladů pro danou kategorii
        $this->language->loadTranslations([$category, 'general']);
    }
    
    /**
     * Detekce kategorie podle názvu controlleru
     */
    protected function detectCategory($className) {
        // Odstranění namespace a "Controller" z názvu třídy
        $parts = explode('\\', $className);
        $controllerName = end($parts);
        $category = strtolower(str_replace('Controller', '', $controllerName));
        
        // Pro AdminController a podobné použijeme 'admin'
        if (strpos($controllerName, 'Admin') === 0) {
            $category = 'admin';
        }
        
        return $category;
    }
    
    protected function view($view, $data = []) {
        // Přidání instance jazyka do dat pro šablonu
        $data['lang'] = $this->language;
        
        // Extrahování proměnných z $data
        extract($data);
        
        // Načtení view do content
        ob_start();
        include "../src/views/{$view}.php";
        $data['content'] = ob_get_clean();
        
        // Načtení layoutu
        include "../src/views/layouts/main.php";
    }
    
    protected function renderView($view, $data = []) {
        // Přidání instance jazyka do dat pro šablonu
        $data['lang'] = $this->language;
        
        extract($data);
        ob_start();
        require_once "../src/views/{$view}.php";
        return ob_get_clean();
    }
    
    protected function show404() {
        $data = [
            'title' => __('page_not_found') . ' | Kali-framework',
            'content' => $this->renderView('errors/404')
        ];
        
        require_once "../src/views/layouts/main.php";
        exit();
    }
}