<?php

namespace Helpers;

use Core\Database;

class RateLimiter
{
    private $db;
    private $ipAddress;
    private $action;
    
    // Limity pro různé akce
    private $limits = [
        'password_reset_request' => ['count' => 5, 'period' => 3600], // 5 pokusů za hodinu
        'password_reset_confirm' => ['count' => 10, 'period' => 3600], // 10 pokusů za hodinu
        'login' => ['count' => 10, 'period' => 3600] // 10 pokusů za hodinu
    ];
    
    public function __construct($action)
    {
        $this->db = Database::getInstance();
        $this->ipAddress = $_SERVER['REMOTE_ADDR'];
        $this->action = $action;
        
        // Kontrola, zda existuje tabulka rate_limits
        $this->ensureRateLimitTableExists();
    }
    
    /**
     * Vytvoření tabulky rate_limits, pokud neexistuje
     */
    private function ensureRateLimitTableExists()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                action VARCHAR(50) NOT NULL,
                attempt_count INT NOT NULL DEFAULT 1,
                last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX rate_limits_ip_action (ip_address, action)
            )";
            $this->db->execute($sql);
        } catch (\PDOException $e) {
            error_log('Chyba při vytváření tabulky rate_limits: ' . $e->getMessage());
        }
    }
    
    /**
     * Kontrola, zda IP adresa nepřekročila limit pro danou akci
     */
    public function check()
    {
        if (!isset($this->limits[$this->action])) {
            // Pokud akce nemá definovaný limit, povolíme ji
            return true;
        }
        
        $limit = $this->limits[$this->action];
        
        try {
            // Odstranění starých záznamů
            $this->cleanupOldRecords();
            
            // Získání aktuálního počtu pokusů
            $sql = "SELECT * FROM rate_limits 
                    WHERE ip_address = :ip AND action = :action 
                    AND last_attempt_at > DATE_SUB(NOW(), INTERVAL :period SECOND)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':ip' => $this->ipAddress,
                ':action' => $this->action,
                ':period' => $limit['period']
            ]);
            
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($record) {
                // Pokud záznam existuje, zkontrolujeme počet pokusů
                if ($record['attempt_count'] >= $limit['count']) {
                    return false; // Překročen limit
                }
                
                // Aktualizace počtu pokusů
                $updateSql = "UPDATE rate_limits 
                             SET attempt_count = attempt_count + 1, 
                                 last_attempt_at = NOW() 
                             WHERE id = :id";
                
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([':id' => $record['id']]);
            } else {
                // Vložení nového záznamu
                $insertSql = "INSERT INTO rate_limits (ip_address, action, attempt_count, last_attempt_at) 
                             VALUES (:ip, :action, 1, NOW())";
                
                $insertStmt = $this->db->prepare($insertSql);
                $insertStmt->execute([
                    ':ip' => $this->ipAddress,
                    ':action' => $this->action
                ]);
            }
            
            return true; // V pořádku, limit nebyl překročen
        } catch (\PDOException $e) {
            error_log('Chyba při kontrole rate limit: ' . $e->getMessage());
            return true; // V případě chyby povolíme akci
        }
    }
    
    /**
     * Odstranění starých záznamů
     */
    private function cleanupOldRecords()
    {
        try {
            // Odstraníme záznamy starší než 24 hodin
            $sql = "DELETE FROM rate_limits WHERE last_attempt_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $this->db->execute($sql);
        } catch (\PDOException $e) {
            error_log('Chyba při čištění starých rate limit záznamů: ' . $e->getMessage());
        }
    }
    
    /**
     * Získání zbývajícího času do resetování limitu
     */
    public function getTimeRemaining()
    {
        if (!isset($this->limits[$this->action])) {
            return 0;
        }
        
        try {
            $sql = "SELECT last_attempt_at FROM rate_limits 
                    WHERE ip_address = :ip AND action = :action 
                    ORDER BY last_attempt_at DESC LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':ip' => $this->ipAddress,
                ':action' => $this->action
            ]);
            
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($record) {
                $lastAttempt = strtotime($record['last_attempt_at']);
                $resetTime = $lastAttempt + $this->limits[$this->action]['period'];
                $remaining = $resetTime - time();
                
                return max(0, $remaining);
            }
            
            return 0;
        } catch (\PDOException $e) {
            error_log('Chyba při získávání zbývajícího času: ' . $e->getMessage());
            return 0;
        }
    }
}