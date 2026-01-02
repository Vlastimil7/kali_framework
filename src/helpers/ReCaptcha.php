<?php

namespace Helpers;

use Helpers\Logger;

class ReCaptcha
{
    private string $secretKey;

    private array $allowedHostnames = [
        'leanitnow.cz',
        'www.leanitnow.cz',
        'web.kalasekvyvoj.cz',
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
        $ctxBase = [
            'expected_action' => $expectedAction,
            'min_score'       => $minScore,
            'ip'              => $_SERVER['REMOTE_ADDR'] ?? null,
            'ua'              => $_SERVER['HTTP_USER_AGENT'] ?? null,
            // token neukládat celý – max. "otisk"
            'token_fprint'    => $token !== '' ? substr($token, -8) : null,
        ];

        if ($token === '') {
            Logger::warning('reCAPTCHA missing token', $ctxBase);
            return [
                'success' => false,
                'error_type' => 'missing_token',
                'message' => 'Token nebyl zadán'
            ];
        }

        if ($this->secretKey === '') {
            Logger::error('reCAPTCHA secret key missing', $ctxBase);
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

        $response = null;

        // CURL / fallback
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);

            $response = curl_exec($ch);
            $curlErr  = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                Logger::error('reCAPTCHA curl connection failed', $ctxBase + [
                    'curl_error' => $curlErr,
                    'http_code'  => $httpCode ?? null,
                ]);

                return [
                    'success' => false,
                    'error_type' => 'connection',
                    'message' => 'Nepodařilo se připojit k reCAPTCHA API: ' . $curlErr
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
                Logger::error('reCAPTCHA file_get_contents connection failed', $ctxBase);
                return [
                    'success' => false,
                    'error_type' => 'connection',
                    'message' => 'Nepodařilo se připojit k reCAPTCHA API'
                ];
            }
        }

        $result = json_decode($response, true);

        if (!is_array($result) || json_last_error() !== JSON_ERROR_NONE) {
            Logger::error('reCAPTCHA invalid JSON response', $ctxBase + [
                'json_error' => json_last_error_msg(),
                'resp_len'   => is_string($response) ? strlen($response) : null,
            ]);

            return [
                'success' => false,
                'error_type' => 'invalid_response',
                'message' => 'Neplatná odpověď z reCAPTCHA API'
            ];
        }

        // Úspěch/Chyby od Googlu
        if (empty($result['success'])) {
            Logger::warning('reCAPTCHA verification failed', $ctxBase + [
                'error_codes' => $result['error-codes'] ?? [],
                'hostname'    => $result['hostname'] ?? null,
                'action'      => $result['action'] ?? null,
                'score'       => $result['score'] ?? null,
            ]);

            return [
                'success' => false,
                'error_type' => 'verification_failed',
                'message' => 'Ověření reCAPTCHA selhalo',
                'error_codes' => $result['error-codes'] ?? []
            ];
        }

        // Volitelné: kontrola hostname
        $host = strtolower((string)($result['hostname'] ?? ''));
        if ($host !== '' && !in_array($host, $this->allowedHostnames, true)) {
            Logger::warning('reCAPTCHA invalid hostname', $ctxBase + [
                'hostname' => $host,
                'allowed'  => $this->allowedHostnames,
            ]);

            return [
                'success'    => false,
                'error_type' => 'invalid_hostname',
                'message'    => 'Neplatná doména reCAPTCHA',
                'hostname'   => $host,
            ];
        }

        // reCAPTCHA v3: kontrola action
        if (!empty($result['action']) && $result['action'] !== $expectedAction) {
            Logger::warning('reCAPTCHA invalid action', $ctxBase + [
                'action'   => $result['action'],
                'expected' => $expectedAction,
                'score'    => $result['score'] ?? null,
                'hostname' => $host ?: null,
            ]);

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
            $score = (float)$result['score'];

            if ($score < $minScore) {
                Logger::info('reCAPTCHA low score', $ctxBase + [
                    'score'    => $score,
                    'action'   => $result['action'] ?? null,
                    'hostname' => $host ?: null,
                ]);

                return [
                    'success' => false,
                    'error_type' => 'low_score',
                    'message' => 'Příliš nízké skóre reCAPTCHA',
                    'score' => $score,
                ];
            }

            Logger::debug('reCAPTCHA OK (v3)', $ctxBase + [
                'score'    => $score,
                'action'   => $result['action'] ?? null,
                'hostname' => $host ?: null,
            ]);

            return [
                'success' => true,
                'message' => 'OK',
                'score' => $score,
                'action' => $result['action'] ?? null,
            ];
        }

        // reCAPTCHA v2: když není score/action
        Logger::debug('reCAPTCHA OK (v2)', $ctxBase + [
            'hostname' => $host ?: null,
        ]);

        return [
            'success' => true,
            'message' => 'OK'
        ];
    }
}
