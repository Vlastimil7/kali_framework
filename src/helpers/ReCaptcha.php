<?php

namespace Helpers;

class ReCaptcha
{
    private string $secretKey;

    // Volitelné: povolené domény (uprav si podle reality)
    private array $allowedHostnames = [
        'pediaaz.cz',
        'www.pediaaz.cz',
        'detsky-doktor-albahri.cz',
        'www.detsky-doktor-albahri.cz',
        'web.kalasekvyvoj.cz',
        // případně i test doména:
        // 'web.kalasekvyvoj.cz',
    ];

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Ověření reCAPTCHA tokenu (v2 i v3)
     */
    public function verify(string $token, string $expectedAction = 'contact', float $minScore = 0.5): array
    {
        if ($token === '') {
            return [
                'success' => false,
                'error_type' => 'missing_token',
                'message' => 'Token nebyl zadán'
            ];
        }

        if ($this->secretKey === '') {
            return [
                'success' => false,
                'error_type' => 'configuration',
                'message' => 'Secret key není nastaven'
            ];
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret'   => $this->secretKey,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        // CURL / fallback
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
        } else {
            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                    'timeout' => 8,
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

        $result = json_decode($response, true);

        if (!is_array($result) || json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error_type' => 'invalid_response',
                'message' => 'Neplatná odpověď z reCAPTCHA API'
            ];
        }

        // Úspěch/Chyby od Googlu
        if (empty($result['success'])) {
            return [
                'success' => false,
                'error_type' => 'verification_failed',
                'message' => 'Ověření reCAPTCHA selhalo',
                'error_codes' => $result['error-codes'] ?? []
            ];
        }

        // Volitelné: kontrola hostname (doporučuji)
        if (!empty($result['hostname']) && !in_array($result['hostname'], $this->allowedHostnames, true)) {
            return [
                'success' => false,
                'error_type' => 'invalid_hostname',
                'message' => 'Neplatná doména reCAPTCHA',
                'hostname' => $result['hostname'],
            ];
        }

        // reCAPTCHA v3: kontrola action
        if (!empty($result['action']) && $result['action'] !== $expectedAction) {
            return [
                'success' => false,
                'error_type' => 'invalid_action',
                'message' => 'Neplatná akce reCAPTCHA',
                'action' => $result['action'],
                'expected' => $expectedAction,
            ];
        }

        // reCAPTCHA v3: kontrola score
        if (isset($result['score'])) {
            if ((float)$result['score'] < $minScore) {
                return [
                    'success' => false,
                    'error_type' => 'low_score',
                    'message' => 'Příliš nízké skóre reCAPTCHA',
                    'score' => (float)$result['score'],
                ];
            }

            return [
                'success' => true,
                'message' => 'OK',
                'score' => (float)$result['score'],
                'action' => $result['action'] ?? null,
            ];
        }

        // reCAPTCHA v2: když není score/action
        return [
            'success' => true,
            'message' => 'OK'
        ];
    }
}
