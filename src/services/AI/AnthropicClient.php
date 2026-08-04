<?php

namespace Services\AI;

use Helpers\Logger;

final class AnthropicClient implements AiClientInterface
{
    private string $apiKey = '';
    private string $model;
    private string $endpoint = 'https://api.anthropic.com/v1/messages';
    private string $version = '2023-06-01';

    public function __construct()
    {
        $this->apiKey = (string)config('ai.providers.anthropic.api_key', '');
        $this->model = (string)config('ai.providers.anthropic.model', 'claude-3-haiku-20240307');
    }

    public function name(): string
    {
        return 'anthropic';
    }

    public function getStatus(): array
    {
        return [
            'provider' => 'anthropic',
            'api_key_set' => $this->apiKey !== '',
            'model' => $this->model,
            'endpoint' => $this->endpoint,
            'anthropic_version' => $this->version,
        ];
    }

    public function testConnection(): bool
    {
        if ($this->apiKey === '') {
            return false;
        }

        $payload = [
            'model' => $this->model,
            'max_tokens' => 5,
            'system' => 'Test',
            'messages' => [['role' => 'user', 'content' => 'Test']],
        ];

        [$http, $body, $err] = HttpJsonClient::postJsonWithHeaders(
            $this->endpoint,
            $payload,
            [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . $this->version,
                'content-type: application/json',
            ],
            10,
            false,
        );

        return $err === '' && $http === 200;
    }
    private function truncateForLog(string $text, int $max = 250): string
    {
        $text = trim($text);
        return mb_strlen($text) > $max
            ? mb_substr($text, 0, $max) . '…'
            : $text;
    }

    public function ask(string $system, string $user): string
    {
        if ($this->apiKey === '') {
            return '⚠️ **Chyba:** Anthropic (Claude) API klíč není nastaven.';
        }

        $payload = [
            'model' => $this->model,
            'max_tokens' => 800,
            'system' => $system,
            'messages' => [['role' => 'user', 'content' => $user]],
        ];

        $t0 = microtime(true);

        [$http, $body, $err, $headers] = HttpJsonClient::postJsonWithHeaders(
            $this->endpoint,
            $payload,
            [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . $this->version,
                'content-type: application/json',
            ],
            30,
            false,
        );

        $ms = (int) round((microtime(true) - $t0) * 1000);
        $requestId = $headers['request-id'] ?? null;

        if ($err !== '') {
            Logger::error('Claude cURL error', [
                'ms' => $ms,
                'error' => $err,
                'request_id' => $requestId,
            ]);
            return "⚠️ **Chyba připojení:** {$err}";
        }

        $json = json_decode($body, true) ?: [];

        if ($http !== 200) {
            $msg = $json['error']['message'] ?? ($json['message'] ?? 'Neznámá chyba');

            Logger::error('Claude API error', [
                'ms' => $ms,
                'http' => $http,
                'request_id' => $requestId,
                'message' => $msg,
            ]);

            return "⚠️ **Claude API chyba ({$http}):** {$msg}";
        }

        // text
        $text = '';
        if (isset($json['content']) && is_array($json['content'])) {
            foreach ($json['content'] as $part) {
                if (($part['type'] ?? null) === 'text' && isset($part['text'])) {
                    $text .= (string)$part['text'];
                }
            }
        }

        // usage
        $usage = $json['usage'] ?? [];
        Logger::info('AI response OK', [
            'provider' => $this->name(),
            'ms' => $ms,
            'request_id' => $requestId,
            'input_tokens' => $usage['input_tokens'] ?? null,
            'output_tokens' => $usage['output_tokens'] ?? null,
            'answer_len' => mb_strlen($text),
            'answer_head' => $this->truncateForLog($text, 250),
        ]);

        return trim($text) !== ''
            ? $text
            : '⚠️ **Chyba:** Claude nevrátil text.';
    }
}
