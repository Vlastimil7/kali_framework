<?php

namespace Controllers;

use Core\Controller;
use Models\Language;

class LanguageController extends Controller
{
    private $languageModel;
    
    public function __construct()
    {
        $this->languageModel = new Language();
    }
    
    /**
     * Změna jazyka
     * 
     * @param string $lang Kód jazyka
     */
    public function changeLanguage($lang = 'cs')
    {
        // Sanitizace vstupu
        $lang = strtolower(trim($lang));
        
        // Nastavení jazyka
        $this->languageModel->setLanguage($lang);
        
        // Přesměrování zpět na předchozí stránku
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL;
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Admin: Seznam překladů
     */
    public function adminTranslations()
    {
        // Kontrola, zda je uživatel admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $selectedLang = $_GET['lang'] ?? 'cs';
        $selectedCategory = $_GET['category'] ?? null;
        
        // Získání všech klíčů a překladů
        $keys = $this->languageModel->getAllTranslationKeys($selectedCategory);
        $translations = [];
        
        foreach ($this->languageModel->getSupportedLanguages() as $langCode) {
            $translations[$langCode] = $this->languageModel->getTranslations($langCode, $selectedCategory);
        }
        
        $this->view('admin/translations/index', [
            'title' => 'Správa překladů | SuperKrabicky.cz - Admin',
            'keys' => $keys,
            'translations' => $translations,
            'languages' => $this->languageModel->getSupportedLanguages(),
            'selectedLang' => $selectedLang,
            'selectedCategory' => $selectedCategory
        ]);
    }
    
    /**
     * Admin: Editace překladu
     */
    public function adminEditTranslation($key = null, $category = 'general')
    {
        // Kontrola, zda je uživatel admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        if (!$key) {
            $_SESSION['flash_message'] = 'Neplatný klíč překladu.';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/admin/translations');
            exit;
        }
        
        
        // Získání překladu pro všechny jazyky
        $translations = [];
        foreach ($this->languageModel->getSupportedLanguages() as $langCode) {
            $langTranslations = $this->languageModel->getTranslations($langCode, $category);
            $translations[$langCode] = $langTranslations[$category][$key] ?? '';
        }
        
        $this->view('admin/translations/edit', [
            'title' => 'Editace překladu | SuperKrabicky.cz - Admin',
            'key' => $key,
            'category' => $category,
            'translations' => $translations,
            'languages' => $this->languageModel->getSupportedLanguages()
        ]);
    }
    
    /**
     * Admin: Zpracování editace překladu
     */
    public function adminProcessEditTranslation()
    {
        // Kontrola, zda je uživatel admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/translations');
            exit;
        }
        
        $key = $_POST['key'] ?? '';
        $category = $_POST['category'] ?? 'general';
        $translations = $_POST['translations'] ?? [];
        
        if (!$key || empty($translations)) {
            $_SESSION['flash_message'] = 'Neplatný požadavek.';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/admin/translations');
            exit;
        }
        
        $success = true;
        $errors = [];
        
        // Uložení překladů pro všechny jazyky
        foreach ($translations as $langCode => $value) {
            $result = $this->languageModel->saveTranslation($langCode, $key, $value, $category);
            
            if (!$result['success']) {
                $success = false;
                $errors[] = "Jazyk $langCode: " . $result['message'];
            }
        }
        
        if ($success) {
            $_SESSION['flash_message'] = 'Překlad byl úspěšně aktualizován.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Při ukládání překladu došlo k chybám: ' . implode(', ', $errors);
            $_SESSION['flash_type'] = 'error';
        }
        
        header('Location: ' . BASE_URL . '/admin/translations');
        exit;
    }
    
    /**
     * Admin: Přidání nového překladu
     */
    public function adminAddTranslation()
    {
        // Kontrola, zda je uživatel admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $this->view('admin/translations/add', [
            'title' => 'Přidání překladu | SuperKrabicky.cz - Admin',
            'languages' => $this->languageModel->getSupportedLanguages()
        ]);
    }
    
    /**
     * Admin: Zpracování přidání nového překladu
     */
    public function adminProcessAddTranslation()
    {
        // Kontrola, zda je uživatel admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/translations');
            exit;
        }
        
        $key = $_POST['key'] ?? '';
        $category = $_POST['category'] ?? 'general';
        $translations = $_POST['translations'] ?? [];
        
        if (!$key || empty($translations)) {
            $_SESSION['flash_message'] = 'Neplatný požadavek.';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/admin/translations/add');
            exit;
        }
        
        $success = true;
        $errors = [];
        
        // Uložení překladů pro všechny jazyky
        foreach ($translations as $langCode => $value) {
            $result = $this->languageModel->saveTranslation($langCode, $key, $value, $category);
            
            if (!$result['success']) {
                $success = false;
                $errors[] = "Jazyk $langCode: " . $result['message'];
            }
        }
        
        if ($success) {
            $_SESSION['flash_message'] = 'Překlad byl úspěšně přidán.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . '/admin/translations');
        } else {
            $_SESSION['flash_message'] = 'Při ukládání překladu došlo k chybám: ' . implode(', ', $errors);
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/admin/translations/add');
        }
        exit;
    }
    
    /**
     * Admin: Import překladů
     */
    public function adminImportTranslations()
    {
        // Kontrola, zda je uživatel admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $this->view('admin/translations/import', [
            'title' => 'Import překladů | SuperKrabicky.cz - Admin'
        ]);
    }
    
    /**
     * Admin: Zpracování importu překladů
     */
    public function adminProcessImportTranslations()
    {
        // Kontrola, zda je uživatel admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/translations');
            exit;
        }
        
        // Zpracování nahraného souboru
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_message'] = 'Chyba při nahrávání souboru.';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/admin/translations/import');
            exit;
        }
        
        $fileContent = file_get_contents($_FILES['import_file']['tmp_name']);
        $translations = json_decode($fileContent, true);
        
        if (!$translations) {
            $_SESSION['flash_message'] = 'Neplatný formát souboru. Musí být ve formátu JSON.';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/admin/translations/import');
            exit;
        }
        
        $result = $this->languageModel->importTranslations($translations);
        
        if ($result['success']) {
            $_SESSION['flash_message'] = 'Překlady byly úspěšně importovány.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . '/admin/translations');
        } else {
            $_SESSION['flash_message'] = 'Při importu překladů došlo k chybě: ' . $result['message'];
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/admin/translations/import');
        }
        exit;
    }
    
    /**
     * Admin: Export překladů
     */
    public function adminExportTranslations()
    {
        // Kontrola, zda je uživatel admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $category = $_GET['category'] ?? null;
        
        $exports = [];
        foreach ($this->languageModel->getSupportedLanguages() as $langCode) {
            $exports[$langCode] = $this->languageModel->getTranslations($langCode, $category);
        }
        
        // Nastavení hlaviček pro stažení souboru
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="translations_export_' . date('Y-m-d') . '.json"');
        
        echo json_encode($exports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }





    
}