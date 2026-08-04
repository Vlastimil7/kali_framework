<?php

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$filePath = __DIR__ . $uriPath;

if ($uriPath !== '/' && is_file($filePath)) {
    return false;
}

$_GET['url'] = ltrim($uriPath, '/');
$_REQUEST['url'] = $_GET['url'];

require __DIR__ . '/index.php';
