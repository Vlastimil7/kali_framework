<?php

use Models\Language;

if (!function_exists('__')) {
    /**
     * Přeloží textový řetězec
     * 
     * @param string $key Klíč překladu
     * @param array $params Parametry pro nahrazení
     * @param string $category Kategorie překladu
     * @return string Přeložený text
     */
    function __($key, $params = [], $category = 'general')
    {
        static $language = null;
        
        if ($language === null) {
            $language = new Language();
        }
        
        return $language->translate($key, $params, $category);
    }
}

if (!function_exists('lang')) {
    /**
     * Vrátí instanci Language
     * 
     * @return Models\Language
     */
    function lang()
    {
        static $language = null;
        
        if ($language === null) {
            $language = new Language();
        }
        
        return $language;
    }
}