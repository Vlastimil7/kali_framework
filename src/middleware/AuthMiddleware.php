<?php

namespace Middleware;

use Core\MiddlewareInterface;
use Core\Request;
use Helpers\Toast;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next)
    {
        if (empty($_SESSION['user_id'])) {
            Toast::warning('Pro pokračování se musíte přihlásit.');
            header('Location: ' . locale_url('login'));

            return null;
        }

        return $next();
    }
}
