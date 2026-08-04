<?php

namespace Helpers;

use Core\Database;

class AbuseLimiter
{
    private Database $database;
    private string $ipAddress;

    public function __construct(?string $ipAddress = null)
    {
        $this->database = Database::getInstance();
        $this->ipAddress = $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS abuse_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                reason VARCHAR(80) NOT NULL,
                attempt_count INT NOT NULL DEFAULT 1,
                blocked_until DATETIME NULL,
                last_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

                UNIQUE KEY abuse_limits_ip_reason (ip_address, reason),
                INDEX abuse_limits_blocked_until (blocked_until)
            )
        ';

        $this->database->execute($sql);
    }

    public function isBlocked(string $reason): bool
    {
        $sql = '
            SELECT blocked_until
            FROM abuse_limits
            WHERE ip_address = :ip_address
              AND reason = :reason
              AND blocked_until IS NOT NULL
              AND blocked_until > NOW()
            LIMIT 1
        ';

        $statement = $this->database->prepare($sql);
        $statement->execute([
            ':ip_address' => $this->ipAddress,
            ':reason' => $reason,
        ]);

        return (bool) $statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function registerAttempt(
        string $reason,
        int $maxAttempts = 5,
        int $blockSeconds = 3600,
    ): void {
        $sql = '
            INSERT INTO abuse_limits (
                ip_address,
                reason,
                attempt_count,
                blocked_until,
                last_attempt_at,
                created_at
            )
            VALUES (
                :ip_address,
                :reason,
                1,
                NULL,
                NOW(),
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                attempt_count = attempt_count + 1,
                last_attempt_at = NOW(),
                blocked_until = CASE
                    WHEN attempt_count + 1 >= :max_attempts
                    THEN DATE_ADD(NOW(), INTERVAL :block_seconds SECOND)
                    ELSE blocked_until
                END
        ';

        $statement = $this->database->prepare($sql);
        $statement->execute([
            ':ip_address' => $this->ipAddress,
            ':reason' => $reason,
            ':max_attempts' => $maxAttempts,
            ':block_seconds' => $blockSeconds,
        ]);
    }

    public function getRemainingBlockSeconds(string $reason): int
    {
        $sql = '
            SELECT TIMESTAMPDIFF(SECOND, NOW(), blocked_until) AS remaining_seconds
            FROM abuse_limits
            WHERE ip_address = :ip_address
              AND reason = :reason
              AND blocked_until IS NOT NULL
              AND blocked_until > NOW()
            LIMIT 1
        ';

        $statement = $this->database->prepare($sql);
        $statement->execute([
            ':ip_address' => $this->ipAddress,
            ':reason' => $reason,
        ]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return max(0, (int)($row['remaining_seconds'] ?? 0));
    }
}
