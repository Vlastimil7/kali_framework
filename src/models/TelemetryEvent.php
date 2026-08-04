<?php

namespace Models;

use Core\Database;
use Helpers\Logger;

class TelemetryEvent
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): bool
    {
        try {
            $sql = 'INSERT INTO telemetry_events (
                        application_name,
                        page_path,
                        session_id,
                        event_name,
                        event_data_json,
                        client_ip,
                        user_agent,                       
                        device_hints_json
                    ) VALUES (
                        :application_name,
                        :page_path,
                        :session_id,
                        :event_name,
                        :event_data_json,
                        :client_ip,
                        :user_agent,                       
                        :device_hints_json
                    )';

            $statement = $this->db->prepare($sql);
            $statement->execute([
                ':application_name' => $data['application_name'] ?? null,
                ':page_path' => $data['page_path'] ?? null,
                ':session_id' => $data['session_id'] ?? null,
                ':event_name' => $data['event_name'] ?? null,
                ':event_data_json' => $data['event_data_json'] ?? null,
                ':client_ip' => $data['client_ip'] ?? null,
                ':user_agent' => $data['user_agent'] ?? null,
                ':device_hints_json' => $data['device_hints_json'] ?? null,
            ]);

            return true;
        } catch (\Throwable $exception) {
            Logger::exception($exception, [
                'model' => 'TelemetryEvent',
                'action' => 'create',
                'data' => $data,
            ]);

            return false;
        }
    }

    public function distinctEventNames(string $app): array
    {
        $sql = 'SELECT DISTINCT event_name
            FROM telemetry_events
            WHERE application_name = :app
            ORDER BY event_name';
        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app]);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        return array_map(fn ($r) => (string)$r['event_name'], $rows);
    }

    public function findByFilters(string $app, string $from, string $to, array $filters, int $limit, int $offset): array
    {
        $where = ['application_name = :app', 'received_at >= :from', 'received_at < :to'];
        $params = [':app' => $app, ':from' => $from, ':to' => $to];

        if (!empty($filters['event'])) {
            $where[] = 'event_name = :event';
            $params[':event'] = $filters['event'];
        }
        if (!empty($filters['path'])) {
            $where[] = 'page_path = :path';
            $params[':path']  = $filters['path'];
        }
        if (!empty($filters['sid'])) {
            $where[] = 'session_id = :sid';
            $params[':sid']   = $filters['sid'];
        }

        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);

        $sql = 'SELECT id, client_ip, received_at, page_path, event_name, session_id, event_data_json
            FROM telemetry_events
            WHERE ' . implode(' AND ', $where) . "
            ORDER BY received_at DESC, id DESC
            LIMIT {$limit} OFFSET {$offset}";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function countByFilters(string $app, string $from, string $to, array $filters): int
    {
        $where = ['application_name = :app', 'received_at >= :from', 'received_at < :to'];
        $params = [':app' => $app, ':from' => $from, ':to' => $to];

        if (!empty($filters['event'])) {
            $where[] = 'event_name = :event';
            $params[':event'] = $filters['event'];
        }
        if (!empty($filters['path'])) {
            $where[] = 'page_path = :path';
            $params[':path']  = $filters['path'];
        }
        if (!empty($filters['sid'])) {
            $where[] = 'session_id = :sid';
            $params[':sid']   = $filters['sid'];
        }

        $sql = 'SELECT COUNT(*) AS c
            FROM telemetry_events
            WHERE ' . implode(' AND ', $where);
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return (int)($st->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
    }

    public function findBySession(string $app, string $sid): array
    {
        $sql = 'SELECT id, received_at, page_path, event_name, event_data_json
            FROM telemetry_events
            WHERE application_name = :app AND session_id = :sid
            ORDER BY received_at ASC, id ASC';
        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app, ':sid' => $sid]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /* dashboard */
    public function countEvents(string $app, string $from, string $to): int
    {
        $sql = 'SELECT COUNT(*) AS c
            FROM telemetry_events
            WHERE application_name = :app AND received_at >= :from AND received_at < :to';
        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app, ':from' => $from, ':to' => $to]);
        return (int)($st->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
    }

    public function countSessions(string $app, string $from, string $to): int
    {
        $sql = 'SELECT COUNT(DISTINCT session_id) AS c
            FROM telemetry_events
            WHERE application_name = :app AND received_at >= :from AND received_at < :to';
        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app, ':from' => $from, ':to' => $to]);
        return (int)($st->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);
    }

    public function topPages(string $app, string $from, string $to, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $sql = "SELECT page_path, COUNT(*) AS c
            FROM telemetry_events
            WHERE application_name = :app AND received_at >= :from AND received_at < :to
            GROUP BY page_path
            ORDER BY c DESC
            LIMIT {$limit}";
        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app, ':from' => $from, ':to' => $to]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function topEvents(string $app, string $from, string $to): array
    {
        $sql = 'SELECT event_name, COUNT(*) AS c
            FROM telemetry_events
            WHERE application_name = :app AND received_at >= :from AND received_at < :to
            GROUP BY event_name
            ORDER BY c DESC';
        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app, ':from' => $from, ':to' => $to]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function eventsOverTime(string $app, string $from, string $to, string $bucket): array
    {
        // $bucket: 'hour' | 'day'
        if ($bucket === 'day') {
            $sql = 'SELECT DATE(received_at) AS bucket, COUNT(*) AS c
                FROM telemetry_events
                WHERE application_name = :app AND received_at >= :from AND received_at < :to
                GROUP BY bucket
                ORDER BY bucket';
        } else {
            $sql = "SELECT DATE_FORMAT(received_at, '%Y-%m-%d %H:00:00') AS bucket, COUNT(*) AS c
                FROM telemetry_events
                WHERE application_name = :app AND received_at >= :from AND received_at < :to
                GROUP BY bucket
                ORDER BY bucket";
        }

        $st = $this->db->prepare($sql);
        $st->execute([':app' => $app, ':from' => $from, ':to' => $to]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
