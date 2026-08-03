<?php

namespace Services\Telemetry;

use Helpers\Logger;
use Models\TelemetryEvent;
use Models\TelemetrySession;

class TelemetryService
{
    private TelemetryEvent $telemetryEventModel;
    private TelemetrySession $telemetrySessionModel;

    private int $maximumEventsPerRequest = 25;
    private int $maximumJsonRequestBytes = 64_000; // soft kontrola, tvrdý limit řeš i na web serveru
    private int $maximumEventNameLength = 64;
    private int $maximumPagePathLength = 300;
    private int $maximumSessionIdLength = 80;
    private int $maximumSingleEventDataBytes = 2000;

    private array $allowedEventNames = [
        'callToActionClick',
        'scrollDepthReached',
        'heroDot',
        'heroSlideAuto',
        'filterGridOpen',
        'filterGridSelect',
        'sectionView',
        'sectionDwellTime',
        'activityPing',
        'cookiePreferenceChange',
        'aiToggle',
        'languageSwitch',
        '404BackToHome',
        'footerLeadFormSubmit',
    ];

    public function __construct()
    {
        $this->telemetryEventModel = new TelemetryEvent();
        $this->telemetrySessionModel = new TelemetrySession();
    }

    public function collect(array $requestData, array $requestContext = []): array
    {
        try {
            $clientIpAddress = (string)($requestContext['clientIpAddress'] ?? '');
            $userAgent = (string)($requestContext['userAgent'] ?? '');

            // 1) Soft kontrola velikosti raw requestu
            $rawBody = file_get_contents('php://input') ?: '';
            $rawBytes = strlen($rawBody);

            if ($rawBytes > $this->maximumJsonRequestBytes) {
                Logger::warning('Telemetry payload too large', [
                    'bytes' => $rawBytes,
                    'limit' => $this->maximumJsonRequestBytes,
                    'ip'    => $clientIpAddress,
                ]);

                return [
                    'success' => false,
                    'message' => 'Payload too large.',
                    'statusCode' => 413,
                ];
            }

            // 2) Schema
            $applicationName = $this->sanitizeShortText($requestData['applicationName'] ?? '', 60);
            $pagePath = $this->sanitizePath($requestData['pagePath'] ?? '');
            $sessionId = $this->sanitizeShortText($requestData['sessionId'] ?? '', $this->maximumSessionIdLength);

            $events = $requestData['events'] ?? null;
            if (!is_array($events)) {
                Logger::warning('Telemetry missing events array', [
                    'ip'   => $clientIpAddress,
                    'app'  => $applicationName,
                    'path' => $pagePath,
                ]);

                return [
                    'success' => false,
                    'message' => 'Missing events array.',
                    'statusCode' => 400,
                ];
            }

            if (!empty($requestContext['isAdmin'])) {
                return $this->acceptWithoutWrite($events);
            }

            if ($this->isAdminPath($pagePath)) {
                return $this->acceptWithoutWrite($events);
            }

            // 3) Rate limit (low-tech) – per IP + per minute
            if ($clientIpAddress !== '' && !$this->allowRequestByRateLimit($clientIpAddress)) {
                Logger::warning('Telemetry rate limit hit', [
                    'ip' => $clientIpAddress,
                ]);

                return [
                    'success' => false,
                    'message' => 'Too many requests.',
                    'statusCode' => 429,
                ];
            }

            $eventsCount = count($events);

            if ($eventsCount < 1 || $eventsCount > $this->maximumEventsPerRequest) {
                Logger::warning('Telemetry invalid events count', [
                    'ip'    => $clientIpAddress,
                    'count' => $eventsCount,
                    'limit' => $this->maximumEventsPerRequest,
                    'app'   => $applicationName,
                    'path'  => $pagePath,
                ]);

                return [
                    'success' => false,
                    'message' => 'Invalid events count.',
                    'statusCode' => 400,
                ];
            }

            if ($pagePath === '' || strlen($pagePath) > $this->maximumPagePathLength) {
                Logger::warning('Telemetry invalid pagePath', [
                    'ip'   => $clientIpAddress,
                    'app'  => $applicationName,
                    'path' => $pagePath,
                ]);

                return [
                    'success' => false,
                    'message' => 'Invalid pagePath.',
                    'statusCode' => 400,
                ];
            }

            // 4) Uložení
            $acceptedEventsCount = 0;
            $rejectedEventsCount = 0;
            $dbFailedCount = 0;

            $deviceHints = $requestData['deviceHints'] ?? null;
            $deviceHintsJson = null;
            if (is_array($deviceHints)) {
                $tmp = json_encode($deviceHints, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $deviceHintsJson = ($tmp !== false) ? $tmp : null;
            }

            // Drop statistika...
            $rejectReasons = [
                'invalidShape' => 0,
                'notAllowedName' => 0,
                'dataTooLarge' => 0,
            ];
           

            foreach ($events as $eventItem) {
                $validationResult = $this->validateSingleEvent($eventItem);

                if (!$validationResult['isValid']) {
                    $rejectedEventsCount++;
                    $reason = (string)($validationResult['reason'] ?? '');
                    if ($reason !== '' && isset($rejectReasons[$reason])) {
                        $rejectReasons[$reason]++;
                    }
                    continue;
                }

                $normalizedEvent = $validationResult['normalizedEvent'];
                $eventName = $normalizedEvent['eventName'];

                if ($eventName === 'activityPing') {
                    $ok = $this->telemetrySessionModel->touch([
                        'application_name' => $applicationName,
                        'session_id' => $sessionId,
                        'page_path' => $pagePath,
                        'client_ip' => $clientIpAddress,
                        'user_agent' => $userAgent,
                        'device_hints_json' => $deviceHintsJson,
                    ]);

                    if ($ok) $acceptedEventsCount++;
                    else $dbFailedCount++;
                    continue;
                }

                $ok = $this->telemetryEventModel->create([
                    'application_name' => $applicationName,
                    'page_path' => $pagePath,
                    'session_id' => $sessionId,
                    'event_name' => $eventName,
                    'event_data_json' => json_encode(
                        $normalizedEvent['eventData'],
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                    'client_ip' => $clientIpAddress,
                    'user_agent' => $userAgent,
                    'device_hints_json' => $deviceHintsJson,
                ]);

                if ($ok) $acceptedEventsCount++;
                else $dbFailedCount++;
            }


            if ($rejectedEventsCount > 0) {
                Logger::debug('Telemetry dropped some events', [
                    'ip' => $clientIpAddress,
                    'app' => $applicationName,
                    'path' => $pagePath,
                    'dropped' => $rejectedEventsCount,
                    'reasons' => $rejectReasons,
                ]);
            }

            Logger::info('Telemetry collect summary', [
                'ip'       => $clientIpAddress,
                'app'      => $applicationName,
                'path'     => $pagePath,
                'sessionId' => $sessionId,
                'received' => $eventsCount,
                'accepted' => $acceptedEventsCount,
                'rejected' => $rejectedEventsCount,
                'dbFailed' => $dbFailedCount,
            ]);

            return [
                'success' => true,
                'acceptedEventsCount' => $acceptedEventsCount,
                'rejectedEventsCount' => $rejectedEventsCount + $dbFailedCount,
            ];
        } catch (\Throwable $e) {
            Logger::exception($e, [
                'context' => 'TelemetryService::collect',
            ]);

            return [
                'success' => false,
                'message' => 'Internal server error.',
                'statusCode' => 500,
            ];
        }
    }

    private function validateSingleEvent($eventItem): array
    {
        if (!is_array($eventItem)) {
            return ['isValid' => false, 'reason' => 'invalidShape'];
        }

        $eventName = $this->sanitizeShortText($eventItem['eventName'] ?? '', $this->maximumEventNameLength);
        if ($eventName === '' || !in_array($eventName, $this->allowedEventNames, true)) {
            return ['isValid' => false, 'reason' => 'notAllowedName'];
        }

        $eventData = $eventItem['eventData'] ?? [];
        if (!is_array($eventData)) {
            $eventData = [];
        }

        // scalar + jednoduché array max hloubka 2
        $eventData = $this->sanitizeEventData($eventData, 2);

        // velikost eventData
        $eventDataJson = json_encode($eventData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($eventDataJson !== false && strlen($eventDataJson) > $this->maximumSingleEventDataBytes) {
            return ['isValid' => false, 'reason' => 'dataTooLarge'];
        }

        return [
            'isValid' => true,
            'normalizedEvent' => [
                'eventName' => $eventName,
                'eventData' => $eventData,
            ],
        ];
    }

    private function sanitizeShortText($value, int $maximumLength): string
    {
        $value = trim((string)$value);
        if ($value === '') return '';
        if (strlen($value) > $maximumLength) {
            $value = substr($value, 0, $maximumLength);
        }
        return $value;
    }

    private function sanitizePath(string $value): string
    {
        $value = trim($value);
        if ($value === '') return '';
        if ($value[0] !== '/') {
            $value = '/' . ltrim($value, '/');
        }
        $value = str_replace(["\r", "\n"], '', $value);
        return $value;
    }

    private function sanitizeEventData(array $data, int $maximumDepth, int $currentDepth = 1): array
    {
        if ($currentDepth > $maximumDepth) {
            return [];
        }

        $cleanData = [];

        foreach ($data as $key => $value) {
            $cleanKey = $this->sanitizeShortText((string)$key, 50);
            if ($cleanKey === '') continue;

            if (is_string($value)) {
                $cleanData[$cleanKey] = $this->sanitizeShortText($value, 300);
                continue;
            }

            if (is_int($value) || is_float($value) || is_bool($value) || $value === null) {
                $cleanData[$cleanKey] = $value;
                continue;
            }

            if (is_array($value)) {
                $cleanData[$cleanKey] = $this->sanitizeEventData($value, $maximumDepth, $currentDepth + 1);
                continue;
            }
        }

        return $cleanData;
    }

    private function allowRequestByRateLimit(string $clientIpAddress): bool
    {
        $maximumRequestsPerMinute = 300;

        $storageDirectory = config('app.base_path') . '/storage/cache/telemetry_rate_limit';
        if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0775, true)) {
            Logger::error('Cannot create telemetry rate limit directory');
            return true; // fail-open
        }

        $timeWindowKey = date('YmdHi');
        $fileName = $storageDirectory . '/' . sha1($clientIpAddress) . '.json';

        $state = [
            'timeWindowKey' => $timeWindowKey,
            'requestsCount' => 0,
        ];

        if (is_file($fileName)) {
            $existing = json_decode((string)file_get_contents($fileName), true);
            if (is_array($existing)) {
                $state = array_merge($state, $existing);
            }
        }

        if (($state['timeWindowKey'] ?? '') !== $timeWindowKey) {
            $state['timeWindowKey'] = $timeWindowKey;
            $state['requestsCount'] = 0;
        }

        $state['requestsCount']++;

        if (file_put_contents(
            $fileName,
            json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        ) === false) {
            Logger::error('Cannot write telemetry rate limit file');
            return true;
        }

        return $state['requestsCount'] <= $maximumRequestsPerMinute;
    }

    private function acceptWithoutWrite(array $events): array
    {
        return [
            'success' => true,
            'acceptedEventsCount' => 0,
            'rejectedEventsCount' => count($events),
        ];
    }

    private function isAdminPath(string $pagePath): bool
    {
        return (bool)preg_match('~(^|/)admin(/|$)~', $pagePath);
    }
}
