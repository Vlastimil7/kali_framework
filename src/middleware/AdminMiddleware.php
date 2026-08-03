<?php

namespace Middleware;

use Core\MiddlewareInterface;
use Core\Request;
use Helpers\Toast;

class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next)
    {
        if (empty($_SESSION['user_id'])) {
            Toast::warning('Pro pokračování se musíte přihlásit.');
            header('Location: ' . locale_url('login'));

            return null;
        }

        if (($_SESSION['user_role'] ?? null) !== 'admin') {
            Toast::error('Do administrace nemáte oprávnění.');
            header('Location: ' . locale_url('profile'));

            return null;
        }

        return $next();
    }
}
