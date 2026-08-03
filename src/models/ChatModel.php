<?php

namespace Models;

require_once __DIR__ . '/../../vendor/autoload.php';

use Smalot\PdfParser\Parser;
use Exception;

class ChatModel
{
    private $personalData;
    private $geminiApiKey;
    private $pdfParser;
    private $documentsPath;

    public function __construct()
    {
        $this->geminiApiKey = (string)config('ai.providers.gemini.api_key', '');
        $this->pdfParser = new Parser();
        $this->documentsPath = __DIR__ . '/../data/documents/';
        $this->loadPersonalData();
    }

    /**
     * Test připojení k Gemini API
     */
    public function testConnection()
    {
        try {
            // Jednoduchý test na Gemini API
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $this->geminiApiKey;

            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Test']
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 10,
                    'temperature' => 0.1
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Kratší timeout pro test

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode === 200;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Získá status různých komponent
     */
    public function getStatus()
    {
        $status = [
            'gemini_api' => $this->testConnection(),
            'api_key_set' => !empty($this->geminiApiKey),
            'personal_data_loaded' => !empty($this->personalData),
            'documents_path_exists' => is_dir($this->documentsPath),
            'pdf_parser_ready' => $this->pdfParser !== null
        ];

        return $status;
    }

    private function loadPersonalData()
    {
        $jsonPath = __DIR__ . '/../data/personal_data.json';
        if (file_exists($jsonPath)) {
            $this->personalData = json_decode(file_get_contents($jsonPath), true);
        } else {
            $this->personalData = [];
        }
    }

    public function processQuestion($question)
    {
        $relevantData = $this->findRelevantData($question);

        // Pokud se ptá na dokumenty/certifikáty/CV, přidej PDF data
        if ($this->needsPdfData($question)) {
            $pdfData = $this->loadRelevantPdfs($question);
            $relevantData['documents'] = $pdfData;
        }

        return $this->callGeminiAPI($question, $relevantData);
    }

    private function needsPdfData($question)
    {
        $pdfKeywords = [
            'cv',
            'životopis',
            'certifikát',
            'osvědčení',
            'diplom',
            'dokument',
            'detaily',
            'více informací',
            'podrobně',
            'projekty',
            'portfolio',
            'reference',
            'zkušenosti'
        ];

        $question = strtolower($question);
        foreach ($pdfKeywords as $keyword) {
            if (strpos($question, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    private function loadRelevantPdfs($question)
    {
        $pdfData = [];
        $question = strtolower($question);

        // Mapování klíčových slov na PDF soubory
        $pdfMapping = [
            'cv.pdf' => ['cv', 'životopis', 'kariéra', 'pracovní', 'zkušenosti'],
            'certificates.pdf' => ['certifikát', 'osvědčení', 'kurz', 'školení'],
            'projects.pdf' => ['projekt', 'portfolio', 'reference', 'práce']
        ];

        foreach ($pdfMapping as $filename => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($question, $keyword) !== false) {
                    $pdfPath = $this->documentsPath . $filename;
                    if (file_exists($pdfPath)) {
                        $content = $this->extractPdfContent($pdfPath);
                        if ($content) {
                            $pdfData[$filename] = $content;
                        }
                    }
                    break;
                }
            }
        }

        // Pokud nic specifického nenašel, načti všechny PDFs
        if (empty($pdfData) && $this->needsPdfData($question)) {
            $pdfData = $this->loadAllPdfs();
        }

        return $pdfData;
    }

    private function extractPdfContent($pdfPath)
    {
        try {
            $pdf = $this->pdfParser->parseFile($pdfPath);
            $text = $pdf->getText();

            // Omeз text na rozumnou délku (pro Gemini API)
            return $this->truncateText($text, 2000);
        } catch (Exception $e) {
            return "Chyba při čtení PDF: " . basename($pdfPath);
        }
    }

    private function loadAllPdfs()
    {
        $allPdfData = [];

        if (is_dir($this->documentsPath)) {
            $files = glob($this->documentsPath . '*.pdf');

            foreach ($files as $file) {
                $filename = basename($file);
                $content = $this->extractPdfContent($file);
                if ($content) {
                    $allPdfData[$filename] = $content;
                }
            }
        }

        return $allPdfData;
    }

    private function truncateText($text, $maxLength = 2000)
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        return substr($text, 0, $maxLength) . '...';
    }

    private function findRelevantData($question)
    {
        $question = strtolower($question);
        $relevant = [];

        $keywords = [
            'skills' => ['dovednost', 'umí', 'znalost', 'technologie', 'programování', 'php', 'javascript', 'mes'],
            'experience' => ['práce', 'zkušenost', 'pracoval', 'pozice', 'firma', 'milacron', 'hartmann', 'technistone', 'apag'],
            'projects' => ['projekt', 'vytvořil', 'framework', 'kali', 'github'],
            'personal' => ['jméno', 'kdo', 'kde', 'bydlí', 'hradec', 'kalášek', 'vlastimil'],
            'education' => ['škola', 'studium', 'vzdělání', 'univerzita', 'pardubice', 'citroën'],
            'specialties' => ['digitalizace', 'průmysl', 'optimalizace', 'mes systém', 'manufacturing'],
            'philosophy' => ['motto', 'filozofie', 'přístup', 'debugger', 'bug'],
            'contact' => ['kontakt', 'email', 'napsat', 'spojit', 'github']
        ];

        foreach ($keywords as $category => $words) {
            foreach ($words as $word) {
                if (strpos($question, $word) !== false) {
                    if (isset($this->personalData[$category])) {
                        $relevant[$category] = $this->personalData[$category];
                    }
                }
            }
        }

        // Pokud nic nenašel, vrať základní info
        if (empty($relevant)) {
            $relevant['personal'] = $this->personalData['personal'] ?? [];
            $relevant['skills'] = $this->personalData['skills'] ?? [];
        }

        return $relevant;
    }

private function callGeminiAPI($question, $relevantData)
{
    // Zkontroluj API klíč
    if (empty($this->geminiApiKey)) {
        return "⚠️ **Chyba:** Gemini API klíč není nastaven.";
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $this->geminiApiKey;

    $context = "Jsi chatbot reprezentující Vlastimila Kaláška. Odpovídáš v češtině jako on sám.\n";
    $context .= "Zde jsou informace o něm:\n\n";

    // JSON data
    if (!empty($relevantData)) {
        $context .= "ZÁKLADNÍ INFORMACE:\n";
        $context .= json_encode($relevantData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $context .= "\n\n";
    }

    // PDF data (pokud jsou)
    if (isset($relevantData['documents']) && !empty($relevantData['documents'])) {
        $context .= "DOKUMENTY A DETAILY:\n";
        foreach ($relevantData['documents'] as $filename => $content) {
            $context .= "=== " . strtoupper(str_replace('.pdf', '', $filename)) . " ===\n";
            $context .= $content . "\n\n";
        }
    }

    $context .= "Odpověz přátelsky a osobně na otázku. Používej informace z dokumentů pokud jsou relevantní. Můžeš používat markdown formátování (**tučný text**, *kurzíva*, `kód`).";

    $prompt = $context . "\n\nOtázka: " . $question;

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 1000,
            'temperature' => 0.7
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pro případné SSL problémy

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Debug logování
    error_log("Gemini API Response Code: " . $httpCode);
    error_log("Gemini API Response: " . $response);
    error_log("cURL Error: " . $curlError);

    if ($curlError) {
        return "⚠️ **Chyba připojení:** " . $curlError;
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        } else {
            error_log("Unexpected Gemini response structure: " . json_encode($result));
            return "⚠️ **Chyba:** Neočekávaná struktura odpovědi z Gemini API.";
        }
    } else {
        $errorResponse = json_decode($response, true);
        $errorMessage = isset($errorResponse['error']['message']) ? $errorResponse['error']['message'] : 'Neznámá chyba';
        return "⚠️ **Gemini API chyba (" . $httpCode . "):** " . $errorMessage;
    }
}
}
