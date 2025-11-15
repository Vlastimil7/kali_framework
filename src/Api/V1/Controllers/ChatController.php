<?php

namespace Api\V1\Controllers;

use Api\BaseApiController;
use Models\ChatModel;

class ChatController extends BaseApiController {
    
    /**
     * Status API endpoint
     * GET /api/v1/chat/status
     */
    public function status() {
        try {
            $this->response([
                'status' => 'ok',
                'timestamp' => time(),
                'version' => '1.0',
                'service' => 'Chat API'
            ]);
        } catch (\Exception $e) {
            $this->response([
                'status' => 'error', 
                'message' => $e->getMessage(),
                'timestamp' => time()
            ], 500);
        }
    }
    
    /**
     * Chat API endpoint
     * POST /api/v1/chat
     */
    public function index() {
        try {
            // Kontrola HTTP metody
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->response(['error' => 'Method not allowed'], 405);
                return;
            }
            
            // Získání vstupních dat
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validace vstupu
            if (!isset($input['question']) || empty(trim($input['question']))) {
                $this->response(['error' => 'Question is required'], 400);
                return;
            }
            
            $question = trim($input['question']);
            
            // Validace délky otázky
            if (strlen($question) > 1000) {
                $this->response(['error' => 'Question is too long (max 1000 characters)'], 400);
                return;
            }
            
            // Zpracování otázky
            $chatModel = new ChatModel();
            $response = $chatModel->processQuestion($question);
            
            // Odpověď
            $this->response([
                'response' => $response,
                'timestamp' => time(),
                'question_length' => strlen($question)
            ]);
            
        } catch (\Exception $e) {
            $this->response([
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
                'timestamp' => time()
            ], 500);
        }
    }
    
    /**
     * Health check endpoint
     * GET /api/v1/chat/health
     */
    public function health() {
        try {
            $chatModel = new ChatModel();
            $modelStatus = $chatModel->getStatus();
            
            $health = [
                'status' => 'healthy',
                'timestamp' => time(),
                'checks' => array_merge([
                    'memory_usage' => $this->getMemoryUsage()
                ], $modelStatus)
            ];
            
            // Zkontroluj, jestli jsou všechny komponenty v pořádku
            $allHealthy = true;
            foreach ($modelStatus as $check => $status) {
                if ($status === false) {
                    $allHealthy = false;
                    $health['checks'][$check] = 'error';
                } else {
                    $health['checks'][$check] = 'ok';
                }
            }
            
            if (!$allHealthy) {
                $health['status'] = 'degraded';
            }
            
            $this->response($health);
            
        } catch (\Exception $e) {
            $this->response([
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'timestamp' => time()
            ], 503);
        }
    }
    
    /**
     * Test endpoint pro rychlé testování
     * GET /api/v1/chat/test
     */
    public function test() {
        $this->response([
            'message' => 'Chat API test successful',
            'timestamp' => time(),
            'version' => '1.0',
            'endpoints' => [
                'POST /api/v1/chat' => 'Send chat message',
                'GET /api/v1/chat/status' => 'Check API status',
                'GET /api/v1/chat/health' => 'Health check',
                'GET /api/v1/chat/test' => 'Test endpoint'
            ]
        ]);
    }
    
    /**
     * Získání využití paměti
     */
    private function getMemoryUsage() {
        return [
            'used' => $this->formatBytes(memory_get_usage(true)),
            'peak' => $this->formatBytes(memory_get_peak_usage(true))
        ];
    }
    
    /**
     * Formátování bytů
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}