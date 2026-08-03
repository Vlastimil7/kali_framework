<?php

namespace Core;

interface MiddlewareInterface
{
    /**
     * Middleware buď zavolá $next, nebo požadavek ukončí vlastní odpovědí.
     */
    public function handle(callable $next);
}
