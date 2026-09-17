<?php

namespace Api\V1\Controllers;

use Core\Request;

final class HealthController
{
    public function index(Request $request): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'app' => config('app.name'),
            'version' => 'v1',
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
