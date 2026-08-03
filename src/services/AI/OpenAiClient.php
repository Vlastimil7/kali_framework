<?php
namespace Services\AI;

use Helpers\Logger;

final class OpenAiClient implements AiClientInterface
{
    private string $apiKey = '';
    private string $model;
    private string $endpoint = 'https://api.openai.com/v1/responses';

    public function __construct()
    {
        $this->apiKey = (string)config('ai.providers.openai.api_key', '');
        $this->model = (string)config('ai.providers.openai.model', 'gpt-4o-mini');
    }

    public function name(): string { return 'openai'; }

    public function getStatus(): array
    {
        return [
            'provider' => 'openai',
            'api_key_set' => $this->apiKey !== '',
            'model' => $this->model,
            'endpoint' => $this->endpoint,
        ];
    }

    public function testConnection(): bool
    {
        if ($this->apiKey === '') return false;

        $payload = [
            'model' => $this->model,
            'input' => [['role' => 'user', 'content' => 'Test']],
            'max_output_tokens' => 5,
        ];

        [$http, $body, $err] = $this->post($payload, 10);

        if ($err !== '') return false;
        return $http === 200;
    }

    public function ask(string $system, string $user): string
    {
        
        if ($this->apiKey === '') {
            return "⚠️ **Chyba:** OpenAI API klíč není nastaven.";
        }

        $payload = [
            'model' => $this->model,
            'input' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
            'max_output_tokens' => 1000,
            'temperature' => 0.7,
            'store' => false,
        ];

        [$http, $body, $err] = $this->post($payload, 30);

        if ($err !== '') return "⚠️ **Chyba připojení:** {$err}";

        $json = json_decode($body, true) ?: [];
        if ($http !== 200) {
            $msg = $json['error']['message'] ?? ($json['message'] ?? 'Neznámá chyba');
            return "⚠️ **OpenAI API chyba ({$http}):** {$msg}";
        }

        $text = $this->extractText($json);
        return trim($text) !== '' ? $text : "⚠️ **Chyba:** OpenAI nevrátil text.";
    }

    private function extractText(array $result): string
    {
        if (!isset($result['output']) || !is_array($result['output'])) {
            return is_string($result['output_text'] ?? null) ? $result['output_text'] : '';
        }

        foreach ($result['output'] as $item) {
            if (($item['type'] ?? null) !== 'message') continue;
            foreach (($item['content'] ?? []) as $c) {
                if (($c['type'] ?? null) === 'output_text' && is_string($c['text'] ?? null)) {
                    return $c['text'];
                }
            }
        }

        return is_string($result['output_text'] ?? null) ? $result['output_text'] : '';
    }

    private function post(array $payload, int $timeout): array
    {
        [$http, $body, $err] = HttpJsonClient::postJsonWithHeaders(
            $this->endpoint,
            $payload,
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            $timeout,
            false
        );

        return [$http, $body, $err];
    }
}
