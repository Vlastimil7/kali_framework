<?php

namespace Api\V1\Controllers;

use Api\BaseApiController;
use Services\AI\ChatService;
use Services\AI\AiClientFactory;
use Helpers\Logger;

class ChatController extends BaseApiController
{
    /**
     * Status API endpoint
     * GET /api/v1/chat/status
     */
   

    public function status()
    {
        try {
            $provider = defined('AI_PROVIDER') ? (string)AI_PROVIDER : 'openai';
            $client   = AiClientFactory::make($provider);

            $this->response([
                'status'    => 'ok',
                'timestamp' => time(),
                'version'   => '1.0',
                'service'   => 'Chat API',
                'provider'  => $client->name(),
            ]);
        } catch (\Throwable $e) {
            $this->response([
                'status'    => 'error',
                'message'   => $e->getMessage(),
                'timestamp' => time()
            ], 500);
        }
    }


    /**
     * Chat API endpoint
     * POST /api/v1/chat
     */

    public function index()
    {
        $t0 = microtime(true);
        $question = '';
        $provider = null;

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->response(['error' => 'Method not allowed'], 405);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $question = trim((string)($input['question'] ?? ''));

            if ($question === '') {
                $this->response(['error' => 'Question is required'], 400);
                return;
            }

            if (mb_strlen($question) > 1000) {
                $this->response(['error' => 'Question is too long (max 1000 characters)'], 400);
                return;
            }

            // provider primárně z configu/const (jak chceš ty)
            // (pokud nechceš vůbec session, tuhle řádku vyhoď)
            $provider = defined('AI_PROVIDER') ? (string)AI_PROVIDER : 'openai';

            Logger::info('Chat API request', [
                'provider' => $provider,
                'ip'       => $_SERVER['REMOTE_ADDR'] ?? null,
                'q_len'    => mb_strlen($question),
                'q_head'   => $this->truncateForLog($question, 300),
            ]);

            $chat = new ChatService($provider);

            $answer = $chat->ask($question);

            $ms = (int) round((microtime(true) - $t0) * 1000);

            Logger::info('Chat API response', [
                'provider'    => $chat->getProvider(),
                'ms'          => $ms,
                'answer_len'  => mb_strlen($answer),
                'answer_head' => $this->truncateForLog($answer, 350),
            ]);

            $this->response([
                'response'         => $answer,
                'timestamp'        => time(),
                'question_length'  => mb_strlen($question),
                'provider'         => $chat->getProvider(),
                'duration_ms'      => $ms, // <- můžeš poslat i do FE
            ]);
        } catch (\Throwable $e) {
            $ms = (int) round((microtime(true) - $t0) * 1000);

            Logger::error('Chat API error', [
                'provider' => $provider,
                'ms'       => $ms,
                'q_head'   => $this->truncateForLog($question, 300),
                'message'  => $e->getMessage(),
            ]);

            $this->response([
                'error'     => 'Internal server error',
                'message'   => $e->getMessage(),
                'timestamp' => time(),
                'duration_ms' => $ms,
            ], 500);
        }
    }

    /** aby logy nebyly 10MB */
    private function truncateForLog(string $text, int $maxLen): string
    {
        $text = trim($text);
        return (mb_strlen($text) <= $maxLen) ? $text : (mb_substr($text, 0, $maxLen) . '…');
    }


    /**
     * Health check endpoint
     * GET /api/v1/chat/health
     */
    public function health()
    {
        try {
            $provider = $_SESSION['ai_provider'] ?? null;
            $chat = new ChatService($provider);

            $modelStatus = $chat->getStatus();

            $health = [
                'status'    => 'healthy',
                'timestamp' => time(),
                'checks'    => array_merge([
                    'memory_usage' => $this->getMemoryUsage()
                ], $modelStatus),
                'provider' => $chat->getProvider(),
            ];

            $allHealthy = true;
            foreach ($modelStatus as $check => $status) {
                if ($status === false) {
                    $allHealthy = false;
                    $health['checks'][$check] = 'error';
                } else {
                    $health['checks'][$check] = 'ok';
                }
            }

            if (!$allHealthy) $health['status'] = 'degraded';

            $this->response($health);
        } catch (\Throwable $e) {
            $this->response([
                'status'    => 'unhealthy',
                'error'     => $e->getMessage(),
                'timestamp' => time()
            ], 503);
        }
    }

    /**
     * (Volitelné) změna providera přes API
     * POST /api/v1/chat/provider  JSON: {"provider":"openai|gemini|anthropic"}
     */
    public function provider()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->response(['error' => 'Method not allowed'], 405);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $provider = strtolower(trim((string)($input['provider'] ?? '')));

            if (!in_array($provider, ['openai', 'gemini', 'anthropic', 'claude'], true)) {
                $this->response(['error' => 'Invalid provider'], 400);
                return;
            }

            $_SESSION['ai_provider'] = ($provider === 'claude') ? 'anthropic' : $provider;

            $this->response([
                'ok'        => true,
                'provider'  => $_SESSION['ai_provider'],
                'timestamp' => time(),
            ]);
        } catch (\Throwable $e) {
            $this->response(['error' => $e->getMessage()], 500);
        }
    }

    // --- beze změn ---

    private function getMemoryUsage()
    {
        return [
            'used' => $this->formatBytes(memory_get_usage(true)),
            'peak' => $this->formatBytes(memory_get_peak_usage(true))
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
