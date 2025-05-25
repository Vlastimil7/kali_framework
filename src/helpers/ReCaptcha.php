<?php

namespace Helpers;

class ReCaptcha
{
    private $secretKey;
    
    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }
    
    /**
     * Ověření reCAPTCHA tokenu
     *
     * @param string $token Token z formuláře
     * @return array Výsledek ověření s informacemi o případných chybách
     */
    public function verify($token)
    {
        if (empty($token)) {
            return [
                'success' => false,
                'error_type' => 'missing_token',
                'message' => 'Token nebyl zadán'
            ];
        }
        
        if (empty($this->secretKey)) {
            return [
                'success' => false,
                'error_type' => 'configuration',
                'message' => 'Secret key není nastaven'
            ];
        }
        
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $this->secretKey,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        // Preferujeme CURL, pokud je dostupný
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($response === false) {
                return [
                    'success' => false,
                    'error_type' => 'connection',
                    'message' => 'Nepodařilo se připojit k reCAPTCHA API: ' . $error
                ];
            }
        } 
        // Záložní metoda pomocí file_get_contents
        else {
            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                ]
            ];
            
            $context = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return [
                    'success' => false,
                    'error_type' => 'connection',
                    'message' => 'Nepodařilo se připojit k reCAPTCHA API'
                ];
            }
        }
        
        // Zpracování JSON odpovědi
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error_type' => 'invalid_response',
                'message' => 'Neplatná odpověď z reCAPTCHA API'
            ];
        }
        
        // Kontrola výsledku
        if (!isset($result['success']) || !$result['success']) {
            $errorType = 'verification_failed';
            $errorMessage = 'Ověření reCAPTCHA selhalo';
            
            // Specifičtější chybové zprávy podle chybových kódů
            if (isset($result['error-codes']) && !empty($result['error-codes'])) {
                $errorCode = $result['error-codes'][0];
                
                switch ($errorCode) {
                    case 'missing-input-secret':
                        $errorType = 'configuration';
                        $errorMessage = 'Chybí secret key';
                        break;
                        
                    case 'invalid-input-secret':
                        $errorType = 'configuration';
                        $errorMessage = 'Neplatný secret key';
                        break;
                        
                    case 'missing-input-response':
                        $errorType = 'missing_token';
                        $errorMessage = 'Chybí token';
                        break;
                        
                    case 'invalid-input-response':
                        $errorType = 'invalid_token';
                        $errorMessage = 'Neplatný token';
                        break;
                        
                    case 'timeout-or-duplicate':
                        $errorType = 'expired_token';
                        $errorMessage = 'Token vypršel nebo je duplikovaný';
                        break;
                        
                    case 'bad-request':
                        $errorType = 'bad_request';
                        $errorMessage = 'Neplatný požadavek';
                        break;
                }
            }
            
            return [
                'success' => false,
                'error_type' => $errorType,
                'message' => $errorMessage,
                'error_codes' => $result['error-codes'] ?? []
            ];
        }
        
        // Kontrola skóre (pouze pro reCAPTCHA v3)
        if (isset($result['score'])) {
            if ($result['score'] < 0.5) {
                return [
                    'success' => false,
                    'error_type' => 'low_score',
                    'message' => 'Příliš nízké skóre reCAPTCHA',
                    'score' => $result['score']
                ];
            }
            
            return [
                'success' => true,
                'score' => $result['score'],
                'action' => $result['action'] ?? null,
                'message' => 'OK'
            ];
        }
        
        // Základní úspěšný výsledek
        return [
            'success' => true,
            'message' => 'OK'
        ];
    }
}