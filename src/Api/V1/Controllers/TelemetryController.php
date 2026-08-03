<?php

namespace Api\V1\Controllers;

use Api\BaseApiController;
use Api\ApiResponse;
use Core\Request;
use Helpers\Logger;
use Services\Telemetry\TelemetryService;

class TelemetryController extends BaseApiController
{
    private TelemetryService $telemetryService;

    public function __construct()
    {
        $this->telemetryService = new TelemetryService();
    }

    public function collect(Request $request)
    {
        $request = $this->useRequest($request);
        try {
            if (!$this->validateMethod('POST')) {
                return;
            }

            $requestData = $request->post();
            if (empty($requestData)) {
                ApiResponse::badRequest('Invalid or empty JSON payload.');
                return;
            }

            $result = $this->telemetryService->collect($requestData, [
                'clientIpAddress' => $request->ip(),
                'userAgent' => (string)$request->header('User-Agent', ''),
                'referer' => (string)$request->header('Referer', ''),
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
