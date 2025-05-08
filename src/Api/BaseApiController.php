<?php

namespace Api;

class BaseApiController
{
    /**
     * Standardní API odpověď
     */
    protected function response($data = null, $success = true, $message = '', $statusCode = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        
        // Přidejte tyto řádky pro správné kódování
        mb_internal_encoding('UTF-8');
        
        http_response_code($statusCode);
        
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $this->utf8EncodeRecursive($data),
            'timestamp' => time(),
            'api_version' => '1.0'
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // Pomocná metoda pro rekurzivní enkódování UTF-8
    private function utf8EncodeRecursive($input)
    {
        if (is_array($input)) {
            return array_map([$this, 'utf8EncodeRecursive'], $input);
        }
        
        if (is_string($input)) {
            return mb_convert_encoding($input, 'UTF-8', 'UTF-8');
        }
        
        return $input;
    }
    
    /**
     * Získání dat z requestu
     */
    protected function getRequestData()
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }
            
            return $data;
        }
        
        return $_POST;
    }
    
    /**
     * Kontrola HTTP metody
     */
    protected function validateMethod($allowedMethods)
    {
        if (!is_array($allowedMethods)) {
            $allowedMethods = [$allowedMethods];
        }
        
        if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
            $this->response(
                null,
                false,
                'Metoda ' . $_SERVER['REQUEST_METHOD'] . ' není povolena. Povolené metody: ' . implode(', ', $allowedMethods),
                405
            );
            return false;
        }
        
        return true;
    }
}