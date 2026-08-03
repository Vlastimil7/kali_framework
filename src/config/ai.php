<?php

return [
    'provider' => (string)env('AI_PROVIDER', 'openai'),
    'providers' => [
        'openai' => [
            'api_key' => (string)env('OPENAI_API_KEY', ''),
            'model' => (string)env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'gemini' => [
            'api_key' => (string)env('GEMINI_API_KEY', ''),
            'model' => (string)env('GEMINI_MODEL', 'gemini-2.5-flash'),
        ],
        'anthropic' => [
            'api_key' => (string)env('ANTHROPIC_API_KEY', ''),
            'model' => (string)env('ANTHROPIC_MODEL', 'claude-3-haiku-20240307'),
        ],
    ],
];
