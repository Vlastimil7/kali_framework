<?php

namespace Api;

class BaseApiController
{
    /**
     * Nastavení CORS.
     *
     * Režimy:
     * - disabled: nic neposílej (same-origin, telemetry apod.)
     * - allowAll: Access-Control-Allow-Origin: *
     * - restricted: allowlist originů
     */
    protected function applyCorsHeaders(array $corsOptions = []): void
    {
        $defaultCorsOptions = [
            'mode' => 'restricted', // disabled | allowAll | restricted

            // pro restricted režim
            'allowedOrigins' => [],

            // metody a hlavičky pro preflight
            'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowedHeaders' => ['Content-Type', 'Authorization', 'X-API-Key', 'X-Telemetry-Token'],

            // pokud budeš chtít posílat cookies/Authorization cross-site
            'allowCredentials' => false,

            // caching preflightu
            'maxAgeSeconds' => 600,
        ];

        $corsOptions = array_replace_recursive($defaultCorsOptions, $corsOptions);

        if ($corsOptions['mode'] === 'disabled') {
            return;
        }

        $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // V allowAll režimu NIKDY nepoužívej credentials
        if ($corsOptions['mode'] === 'allowAll') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: ' . implode(', ', $corsOptions['allowedMethods']));
            header('Access-Control-Allow-Headers: ' . implode(', ', $corsOptions['allowedHeaders']));
            header('Access-Control-Max-Age: ' . (int)$corsOptions['maxAgeSeconds']);

            if ($this->isPreflightRequest()) {
                http_response_code(204);
                exit;
            }

            return;
        }

        // restricted režim: povol jen allowlist
        if ($corsOptions['mode'] === 'restricted') {
            $allowedOrigins = $corsOptions['allowedOrigins'];

            if ($requestOrigin !== '' && in_array($requestOrigin, $allowedOrigins, true)) {
                header('Access-Control-Allow-Origin: ' . $requestOrigin);
                header('Vary: Origin');

                header('Access-Control-Allow-Methods: ' . implode(', ', $corsOptions['allowedMethods']));
                header('Access-Control-Allow-Headers: ' . implode(', ', $corsOptions['allowedHeaders']));
                header('Access-Control-Max-Age: ' . (int)$corsOptions['maxAgeSeconds']);

                if (!empty($corsOptions['allowCredentials'])) {
                    header('Access-Control-Allow-Credentials: true');
                }
            }

            // Preflight: pokud origin není povolen, nech to spadnout (403 nebo bez hlaviček)
            if ($this->isPreflightRequest()) {
                if ($requestOrigin === '' || !in_array($requestOrigin, $allowedOrigins, true)) {
                    http_response_code(403);
                    exit;
                }

                http_response_code(204);
                exit;
            }
        }
    }

    protected function isPreflightRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS';
    }

    /**
     * Standardní API odpověď
     */
    protected function response($data = null, $success = true, $message = '', $statusCode = 200, array $corsOptions = []): void
    {
        // CORS (volitelně)
        $this->applyCorsHeaders($corsOptions);

        header('Content-Type: application/json; charset=utf-8');

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

    protected function getRequestData()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

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

    protected function validateMethod($allowedMethods)
    {
        if (!is_array($allowedMethods)) {
            $allowedMethods = [$allowedMethods];
        }

        if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods, true)) {
            $this->response(
                null,
                false,
                'Metoda ' . ($_SERVER['REQUEST_METHOD'] ?? '') . ' není povolena. Povolené metody: ' . implode(', ', $allowedMethods),
                405
            );
            return false;
        }

        return true;
    }

    protected function isAdmin(): bool
    {
        return !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}
