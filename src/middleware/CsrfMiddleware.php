<?php

namespace Middleware;

use Core\MiddlewareInterface;
use Core\Request;
use Helpers\Csrf;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next)
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next();
        }

        $token = $request->post('_token') ?: $request->header('X-CSRF-TOKEN');
        if (Csrf::validate(is_scalar($token) ? (string) $token : null)) {
            return $next();
        }

        http_response_code(419);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'CSRF token is invalid or expired.';
        return null;
    }
}
