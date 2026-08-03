<?php

namespace Middleware;

use Core\MiddlewareInterface;
use Core\Request;
use Helpers\Csrf;
use Helpers\Toast;

class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, callable $next)
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return $next();
        }

        $token = $request->post('_token') ?: $request->header('X-CSRF-TOKEN');
        if (Csrf::validate(is_scalar($token) ? (string)$token : null)) {
            return $next();
        }

        Toast::error('Platnost formuláře vypršela. Obnovte stránku a zkuste to znovu.');
        header('Location: ' . $this->safeRedirect($request));

        return null;
    }

    private function safeRedirect(Request $request): string
    {
        $referer = (string)$request->header('Referer', '');
        $refererHost = strtolower((string)parse_url($referer, PHP_URL_HOST));
        $siteHost = strtolower((string)parse_url(
            (string)config('app.site_url', ''),
            PHP_URL_HOST
        ));

        if ($referer !== '' && $siteHost !== '' && $refererHost === $siteHost) {
            return $referer;
        }

        return locale_url('');
    }
}
