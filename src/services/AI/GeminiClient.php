<?php
namespace Services\AI;

final class GeminiClient implements AiClientInterface
{
    private string $apiKey = '';
    private string $model = 'gemini-2.5-flash';
    private string $endpointBase = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = defined('GEMINI_API_KEY') ? (string)GEMINI_API_KEY : '';
    }

    public function name(): string { return 'gemini'; }

    public function getStatus(): array
    {
        return [
            'provider' => 'gemini',
            'api_key_set' => $this->apiKey !== '',
            'model' => $this->model,
        ];
    }

    public function testConnection(): bool
    {
        if ($this->apiKey === '') return false;

        $url = $this->buildUrl();

        $payload = [
            'contents' => [[ 'parts' => [[ 'text' => 'Test' ]] ]],
            'generationConfig' => ['maxOutputTokens' => 5, 'temperature' => 0.1],
        ];

        [$http, $body, $err] = HttpJsonClient::postJsonWithHeaders(
            $url,
            $payload,
            ['Content-Type: application/json'],
            10,
            false
        );

        return $err === '' && $http === 200;
    }

    public function ask(string $system, string $user): string
    {
        if ($this->apiKey === '') {
            return "⚠️ **Chyba:** Gemini API klíč není nastaven.";
        }

        $url = $this->buildUrl();

        $prompt = $system . "\n\nOtázka: " . $user;

        $payload = [
            'contents' => [[ 'parts' => [[ 'text' => $prompt ]] ]],
            'generationConfig' => ['maxOutputTokens' => 1000, 'temperature' => 0.7],
        ];

        [$http, $body, $err] = HttpJsonClient::postJsonWithHeaders(
            $url,
            $payload,
            ['Content-Type: application/json'],
            30,
            false
        );

        if ($err !== '') return "⚠️ **Chyba připojení:** {$err}";

        $json = json_decode($body, true) ?: [];
        if ($http !== 200) {
            $msg = $json['error']['message'] ?? 'Neznámá chyba';
            return "⚠️ **Gemini API chyba ({$http}):** {$msg}";
        }

        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return (is_string($text) && trim($text) !== '')
            ? $text
            : "⚠️ **Chyba:** Neočekávaná odpověď z Gemini API.";
    }

    private function buildUrl(): string
    {
        return $this->endpointBase . '/' . $this->model . ':generateContent?key=' . $this->apiKey;
    }
}
