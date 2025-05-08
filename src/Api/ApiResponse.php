<?php

namespace Api;

class ApiResponse
{
    public static function send($data = null, $success = true, $message = '', $statusCode = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

        http_response_code($statusCode);

        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'timestamp' => time(),
            'api_version' => '1.0'
        ];

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = null, $message = '', $statusCode = 200)
    {
        self::send($data, true, $message, $statusCode);
    }

    public static function error($message = '', $statusCode = 400, $data = null)
    {
        self::send($data, false, $message, $statusCode);
    }

    public static function notFound($message = 'Zdroj nebyl nalezen')
    {
        self::send(null, false, $message, 404);
    }


    public static function unauthorized($message = 'Neoprávněný přístup')
    {
        self::send(null, false, $message, 401);
    }

    public static function forbidden($message = 'Zakázáno')
    {
        self::send(null, false, $message, 403);
    }

    public static function badRequest($message = 'Špatný požadavek')
    {
        self::send(null, false, $message, 400);
    }
}
