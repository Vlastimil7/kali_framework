<?php

$siteUrl = rtrim((string)config('app.site_url', ''), '/');

return [
    'comgate' => [
        'merchant' => (string)env('COMGATE_MERCHANT', ''),
        'secret' => (string)env('COMGATE_SECRET', ''),
        'test' => filter_var(env('COMGATE_TEST', true), FILTER_VALIDATE_BOOL),
        'currency' => (string)env('COMGATE_CURRENCY', 'CZK'),
        'language' => (string)env('COMGATE_LANG', 'cs'),
        'country' => (string)env('COMGATE_COUNTRY', 'CZ'),
        'return_url' => (string)env('COMGATE_RETURN_URL', $siteUrl . '/payment/comgate/return'),
        'notify_url' => (string)env('COMGATE_NOTIFY_URL', $siteUrl . '/payment/comgate/notify'),
    ],
];
