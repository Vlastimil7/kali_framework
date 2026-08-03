<?php

namespace Controllers\Front;

use Core\Request;

class RequestInjectionController
{
    public static ?Request $receivedRequest = null;
    public static ?int $receivedId = null;

    public function show(Request $request, int $id): string
    {
        self::$receivedRequest = $request;
        self::$receivedId = $id;

        return 'injected';
    }
}
