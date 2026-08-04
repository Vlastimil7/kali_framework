<?php

namespace Models;

final class Language
{
    private string $currentLanguage;
    private string $defaultLanguage;
    /** @var list<string> */
    private array $supportedLanguages;
    /** @var array<string, array<string, array<string, string>>> */
    private array $translations = [];
    /** @var array<string, bool> */
    private array $loaded = [];
    private string $translationRoot;

    public function __construct(?string $translationRoot = null)
    {
        $this->translationRoot = $translationRoot ?? dirname(__DIR__) . '/i18n';
        $configFile = $this->translationRoot . '/config.php';
        $config = is_file($configFile) ? require $configFile : [];

        $default = is_array($config) ? ($config['default'] ?? 'en') : 'en';
        $supported = is_array($config) ? ($config['supported'] ?? ['en']) : ['en'];
        $supported = array_values(array_filter(
            $supported,
            fn ($language) =>
            is_string($language) && preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/', $language),
        ));

        if (!is_string($default) || !in_array($default, $supported, true)) {
            $default = 'en';
        }
        if (!in_array($default, $supported, true)) {
            array_unshift($supported, $default);
        }

        $this->defaultLanguage = $default;
        $this->supportedLanguages = array_values(array_unique($supported));
        $sessionLanguage = $_SESSION['language'] ?? null;
        $this->currentLanguage = is_string($sessionLanguage) && $this->isValidLanguage($sessionLanguage)
            ? $sessionLanguage
            : $this->defaultLanguage;
    }

    public function translate(string $key, array $params = [], string $category = 'general'): string
    {
        if (!$this->isValidCategory($category)) {
            return $this->replaceParameters($key, $params);
        }

        $fallbacks = [
            [$this->currentLanguage, $category],
            [$this->currentLanguage, 'general'],
            [$this->defaultLanguage, $category],
            [$this->defaultLanguage, 'general'],
        ];

        foreach (array_unique($fallbacks, SORT_REGULAR) as [$language, $fallbackCategory]) {
            $this->ensureLoaded($language, $fallbackCategory);
            if (array_key_exists($key, $this->translations[$language][$fallbackCategory] ?? [])) {
                return $this->replaceParameters($this->translations[$language][$fallbackCategory][$key], $params);
            }
        }

        return $this->replaceParameters($key, $params);
    }

    public function setLanguage(string $language): bool
    {
        if (!$this->isValidLanguage($language)) {
            return false;
        }

        $this->currentLanguage = $language;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
        }
        return true;
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    /** @return list<string> */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    public function isValidLanguage(string $language): bool
    {
        return in_array($language, $this->supportedLanguages, true);
    }

    /**
     * @param string|list<string> $categories
     */
    public function loadTranslations(string|array $categories = ['general'], ?string $language = null): bool
    {
        $language ??= $this->currentLanguage;
        if (!$this->isValidLanguage($language)) {
            return false;
        }

        foreach ((array) $categories as $category) {
            if (!is_string($category) || !$this->isValidCategory($category)) {
                return false;
            }
            $this->ensureLoaded($language, $category);
        }
        return true;
    }

    public function ensureLoaded(string $language, string $category): void
    {
        if (!$this->isValidLanguage($language) || !$this->isValidCategory($category)) {
            return;
        }

        $cacheKey = $language . ':' . $category;
        if (isset($this->loaded[$cacheKey])) {
            return;
        }
        $this->loaded[$cacheKey] = true;
        $this->translations[$language][$category] = [];

        $file = $this->translationRoot . '/' . $language . '/' . $category . '.php';
        if (!is_file($file)) {
            return;
        }

        $items = require $file;
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $this->translations[$language][$category][$key] = $value;
            }
        }
    }

    private function isValidCategory(string $category): bool
    {
        return (bool) preg_match('/^[a-z0-9_]+$/', $category);
    }

    private function replaceParameters(string $translation, array $params): string
    {
        if ($params === []) {
            return $translation;
        }

        $replace = [];
        foreach ($params as $name => $value) {
            if (is_string($name) && (is_scalar($value) || $value instanceof \Stringable)) {
                $replace['{' . $name . '}'] = (string) $value;
            }
        }
        return strtr($translation, $replace);
    }
}
