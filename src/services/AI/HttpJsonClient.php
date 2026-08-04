<?php

namespace Services\AI;

final class HttpJsonClient
{
    /**
     * @return array{0:int,1:string,2:string,3:array<string,string>} [httpCode, body, curlError, headers]
     */
    public static function postJsonWithHeaders(
        string $url,
        array $payload,
        array $headers,
        int $timeoutSeconds = 30,
        bool $verifySsl = false,
    ): array {
        $respHeaders = [];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSeconds);

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $headerLine) use (&$respHeaders) {
            $len = strlen($headerLine);
            $headerLine = trim($headerLine);
            if ($headerLine === '' || strpos($headerLine, ':') === false) {
                return $len;
            }

            [$name, $value] = explode(':', $headerLine, 2);
            $respHeaders[strtolower(trim($name))] = trim($value);
            return $len;
        });

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);

        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = (string)curl_error($ch);
        curl_close($ch);

        return [$httpCode, is_string($body) ? $body : '', $curlError, $respHeaders];
    }
}
