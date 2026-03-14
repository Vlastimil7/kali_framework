<?php

namespace Api\V1\Controllers;

use Api\BaseApiController;
use Api\ApiResponse;
use Helpers\Logger;
use Services\Telemetry\TelemetryService;

class TelemetryController extends BaseApiController
{
    private TelemetryService $telemetryService;

    public function __construct()
    {
        $this->telemetryService = new TelemetryService();
    }

    public function collect()
    {
        try {
            if (!$this->validateMethod('POST')) {
                return;
            }

            $requestData = $this->getRequestData();
            if (empty($requestData)) {
                ApiResponse::badRequest('Invalid or empty JSON payload.');
                return;
            }

            $result = $this->telemetryService->collect($requestData, [
                'clientIpAddress' => $_SERVER['REMOTE_ADDR'] ?? '',
                'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'referer' => $_SERVER['HTTP_REFERER'] ?? '',
                'isAdmin' => $this->isAdmin(),
            ]);

            if (!$result['success']) {
                ApiResponse::error(
                    $result['message'] ?? 'Telemetry rejected.',
                    (int)($result['statusCode'] ?? 400),
                    $result['data'] ?? null
                );
                return;
            }

            ApiResponse::success([
                'acceptedEventsCount' => $result['acceptedEventsCount'] ?? 0,
                'rejectedEventsCount' => $result['rejectedEventsCount'] ?? 0,
            ], 'OK', 200);
        } catch (\Throwable $exception) {
            Logger::exception($exception, [
                'controller' => 'TelemetryController',
                'action' => 'collect',
            ]);

            ApiResponse::error('Internal server error', 500);
        }
    }
}
