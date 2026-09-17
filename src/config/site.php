<?php

// Public project details. Empty values are simply hidden on the contact page.
return [
    'contact' => [
        'email' => trim((string) env('CONTACT_EMAIL', '')),
        'phone' => trim((string) env('CONTACT_PHONE', '')),
        'address' => trim((string) env('CONTACT_ADDRESS', '')),
        'hours' => trim((string) env('CONTACT_HOURS', '')),
    ],
    'social' => [
        'facebook' => trim((string) env('SOCIAL_FACEBOOK', '')),
        'instagram' => trim((string) env('SOCIAL_INSTAGRAM', '')),
        'linkedin' => trim((string) env('SOCIAL_LINKEDIN', '')),
        'github' => trim((string) env('SOCIAL_GITHUB', '')),
    ],
];
