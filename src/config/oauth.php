<?php

return [
    'google' => [
        'client_id' => (string)env('GOOGLE_OAUTH_CLIENT_ID', ''),
        'client_secret' => (string)env('GOOGLE_OAUTH_CLIENT_SECRET', ''),
        'redirect_uri' => (string)env('GOOGLE_OAUTH_REDIRECT_URI', ''),
    ],
];
