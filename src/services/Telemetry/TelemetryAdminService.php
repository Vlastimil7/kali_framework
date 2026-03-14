<?php

namespace Services\Telemetry;

use Models\TelemetryEvent;
use Models\TelemetrySession;

class TelemetryAdminService
{
    private TelemetryEvent $events;
    private TelemetrySession $sessions;

    public function __construct()
    {
        $this->events = new TelemetryEvent();
        $this->sessions = new TelemetrySession();
    }

    public function rangeToDates(string $range): array
    {
        $to = date('Y-m-d H:i:s');
        if ($range === '7d')  return [date('Y-m-d H:i:s', time() - 7 * 86400), $to];
        if ($range === '30d') return [date('Y-m-d H:i:s', time() - 30 * 86400), $to];
        return [date('Y-m-d H:i:s', time() - 24 * 3600), $to];
    }

    public function getOnline(string $app, int $windowSeconds, int $limit): array
    {
        return $this->sessions->findOnline($app, $windowSeconds, $limit);
    }

    public function getDistinctEventNames(string $app): array
    {
        return $this->events->distinctEventNames($app);
    }

    public function getEvents(string $app, string $from, string $to, array $filters, int $limit, int $offset): array
    {
        return [
            'rows' => $this->events->findByFilters($app, $from, $to, $filters, $limit, $offset),
            'total' => $this->events->countByFilters($app, $from, $to, $filters),
        ];
    }

    public function getSessionDetail(string $app, string $sessionId): array
    {
        return [
            'presence' => $this->sessions->findOne($app, $sessionId),
            'timeline' => $this->events->findBySession($app, $sessionId),
        ];
    }

    public function getDashboard(string $app, string $from, string $to): array
    {
        return [
            'events' => $this->events->countEvents($app, $from, $to),
            'sessions' => $this->events->countSessions($app, $from, $to),
            'topPages' => $this->events->topPages($app, $from, $to, 20),
            'topEvents' => $this->events->topEvents($app, $from, $to),
        ];
    }

    public function getOnlineCount(string $app, int $windowSeconds): int
    {
        return $this->sessions->countOnline($app, $windowSeconds);
    }


}
