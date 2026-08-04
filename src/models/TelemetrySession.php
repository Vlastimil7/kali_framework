<?php

namespace Models;

use Core\Database;
use Helpers\Logger;

class TelemetrySession
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function touch(array $data): bool
    {
        try {
            $sql = 'INSERT INTO telemetry_sessions (
                        application_name, session_id, page_path,  client_ip, user_agent, device_hints_json
                    ) VALUES (
                        :application_name, :session_id, :page_path, :client_ip, :user_agent, :device_hints_json
                    )
                    ON DUPLICATE KEY UPDATE
                        page_path = VALUES(page_path),                       
                        client_ip = VALUES(client_ip),
                        user_agent = VALUES(user_agent),
                        device_hints_json = VALUES(device_hints_json),
                        last_seen = NOW();';

            $st = $this->db->prepare($sql);
            $st->execute([
                ':application_name' => $data['application_name'] ?? null,
                ':session_id' => $data['session_id'] ?? null,
                ':page_path' => $data['page_path'] ?? null,
                ':client_ip' => $data['client_ip'] ?? null,
                ':user_agent' => $data['user_agent'] ?? null,
                ':device_hints_json' => $data['device_hints_json'] ?? null,
            ]);

            return true;
        } catch (\Throwable $e) {
            Logger::exception($e, ['model' => 'TelemetrySession', 'action' => 'touch', 'data' => $data]);
            return false;
        }
    }

    public function findOnline(string $app, int $windowSeconds, int $limit = 200): array
    {
        $windowSeconds = in_array($windowSeconds, [10, 20, 30, 60], true) ? $windowSeconds : 20;
        $limit = max(1, min(500, $limit));

        $sql = "SELECT application_name, session_id, page_path, last_seen, device_hints_json, user_agent
            FROM telemetry_sessions
            WHERE application_name = :app
              AND last_seen >= DATE_SUB(NOW(), INTERVAL {$windowSeconds} SECOND)
            ORDER BY last_seen DESC
            LIMIT {$limit}";
        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function findOne(string $app, string $sid): ?array
    {
        $sql = 'SELECT application_name, session_id, page_path, last_seen, device_hints_json, user_agent, client_ip
            FROM telemetry_sessions
            WHERE application_name = :app AND session_id = :sid
            LIMIT 1';
        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app, ':sid' => $sid]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function countOnline(string $app, int $windowSeconds): int
    {
        $windowSeconds = in_array($windowSeconds, [10, 20, 30, 60], true) ? $windowSeconds : 20;

        $sql = "SELECT COUNT(*) AS c
            FROM telemetry_sessions
            WHERE application_name = :app
              AND last_seen >= DATE_SUB(NOW(), INTERVAL {$windowSeconds} SECOND)";
        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app]);

        return (int)($st->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
    }
}
