<?php

namespace Models;

use Core\Database;

class Language
{
    private $db;
    private $currentLang;
    private $translations = [];
    private $supportedLanguages = ['cs', 'en', 'de', 'fr'];
    private $defaultLanguage = 'cs';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentLang = isset($_SESSION['language']) ? $_SESSION['language'] : $this->defaultLanguage;
    }

    /**
     * Načte překlady pro aktuální jazyk a kategorii
     * 
     * @param string|array $categories Kategorie nebo pole kategorií
     * @return bool
     */
    public function loadTranslations($categories = ['general'])
    {
        if (!is_array($categories)) {
            $categories = [$categories];
        }
        
        // Vždy přidáme obecnou kategorii, pokud tam není
        if (!in_array('general', $categories)) {
            $categories[] = 'general';
        }
        
        // Vytvoření otazníků pro prepared statement
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        
        $sql = "SELECT translation_key, translation_value, category 
                FROM translations 
                WHERE language_code = ? 
                AND category IN ($placeholders)";
        
        $params = array_merge([$this->currentLang], $categories);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->translations[$row['category']][$row['translation_key']] = $row['translation_value'];
        }
        
        return true;
    }

    /**
     * Přeloží text podle klíče
     * 
     * @param string $key Klíč překladu
     * @param array $params Parametry pro nahrazení
     * @param string $category Kategorie překladu
     * @return string Přeložený text
     */
    public function translate($key, $params = [], $category = 'general')
    {
        // Pokud kategorie není načtena, načteme ji
        if (!isset($this->translations[$category])) {
            $this->loadTranslations($category);
        }
        
        // Pokud klíč existuje v dané kategorii
        if (isset($this->translations[$category][$key])) {
            $translation = $this->translations[$category][$key];
        }
        // Zkusit najít v obecné kategorii
        elseif (isset($this->translations['general'][$key])) {
            $translation = $this->translations['general'][$key];
        }
        // Pokud klíč nenalezen, vrátíme samotný klíč
        else {
            return $key;
        }
        
        // Nahrazení parametrů
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace('{' . $param . '}', $value, $translation);
            }
        }
        
        return $translation;
    }

    /**
     * Nastaví aktuální jazyk
     * 
     * @param string $langCode Kód jazyka
     * @return bool
     */
    public function setLanguage($langCode)
    {
        if ($this->isValidLanguage($langCode)) {
            $this->currentLang = $langCode;
            $_SESSION['language'] = $langCode;
            // Vyčistíme načtené překlady
            $this->translations = [];
            return true;
        }
        return false;
    }

    /**
     * Získá aktuální jazyk
     * 
     * @return string Kód jazyka
     */
    public function getCurrentLanguage()
    {
        return $this->currentLang;
    }

    /**
     * Zkontroluje, zda je jazyk podporovaný
     * 
     * @param string $langCode Kód jazyka
     * @return bool
     */
    public function isValidLanguage($langCode)
    {
        return in_array($langCode, $this->supportedLanguages);
    }

    /**
     * Získá seznam podporovaných jazyků
     * 
     * @return array
     */
    public function getSupportedLanguages()
    {
        return $this->supportedLanguages;
    }

    /**
     * Vytvoří nebo aktualizuje překlad
     * 
     * @param string $langCode Kód jazyka
     * @param string $key Klíč překladu
     * @param string $value Přeložený text
     * @param string $category Kategorie překladu
     * @return array Výsledek operace
     */
    public function saveTranslation($langCode, $key, $value, $category = 'general')
    {
        try {
            // Kontrola, zda překlad již existuje
            $sql = "SELECT id FROM translations 
                    WHERE language_code = ? 
                    AND translation_key = ? 
                    AND category = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$langCode, $key, $category]);
            
            if ($stmt->rowCount() > 0) {
                // Aktualizace existujícího překladu
                $sql = "UPDATE translations 
                        SET translation_value = ?, updated_at = NOW() 
                        WHERE language_code = ? 
                        AND translation_key = ? 
                        AND category = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$value, $langCode, $key, $category]);
            } else {
                // Vytvoření nového překladu
                $sql = "INSERT INTO translations 
                        (language_code, translation_key, translation_value, category) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$langCode, $key, $value, $category]);
            }
            
            return [
                'success' => true,
                'message' => 'Překlad byl úspěšně uložen'
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Chyba při ukládání překladu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Získá všechny překlady pro daný jazyk a kategorii
     * 
     * @param string $langCode Kód jazyka
     * @param string $category Kategorie překladu
     * @return array
     */
    public function getTranslations($langCode, $category = null)
    {
        $sql = "SELECT translation_key, translation_value, category FROM translations WHERE language_code = ?";
        $params = [$langCode];
        
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $translations = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!isset($translations[$row['category']])) {
                $translations[$row['category']] = [];
            }
            $translations[$row['category']][$row['translation_key']] = $row['translation_value'];
        }
        
        return $translations;
    }

    /**
     * Získá všechny klíče překladů
     * 
     * @param string $category Kategorie překladu
     * @return array
     */
    public function getAllTranslationKeys($category = null)
    {
        $sql = "SELECT DISTINCT translation_key, category FROM translations";
        $params = [];
        
        if ($category) {
            $sql .= " WHERE category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY category, translation_key";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $keys = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!isset($keys[$row['category']])) {
                $keys[$row['category']] = [];
            }
            $keys[$row['category']][] = $row['translation_key'];
        }
        
        return $keys;
    }

    /**
     * Importuje překlady z pole
     * 
     * @param array $translations Pole překladů
     * @return array Výsledek operace
     */
    public function importTranslations($translations)
    {
        try {
            $this->db->beginTransaction();
            
            foreach ($translations as $langCode => $categories) {
                foreach ($categories as $category => $items) {
                    foreach ($items as $key => $value) {
                        $this->saveTranslation($langCode, $key, $value, $category);
                    }
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Překlady byly úspěšně importovány'
            ];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            
            return [
                'success' => false,
                'message' => 'Chyba při importu překladů: ' . $e->getMessage()
            ];
        }
    }
}

Database::getInstance()->closeConnection();