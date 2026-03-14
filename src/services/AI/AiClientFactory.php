<?php

namespace Services\AI;

use InvalidArgumentException;

final class AiClientFactory
{
    public static function make(string $provider): AiClientInterface
    {
        $p = strtolower(trim($provider));

        return match ($p) {
            'openai' => new OpenAiClient(),
            'gemini' => new GeminiClient(),
            'anthropic' => new AnthropicClient(),
            default => throw new InvalidArgumentException("Unknown AI provider: {$provider}"),
        };
    }
}
