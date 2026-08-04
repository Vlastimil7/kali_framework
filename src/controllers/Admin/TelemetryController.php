<?php

namespace Controllers\Admin;

use Core\Request;
use Services\Telemetry\TelemetryAdminService;

class TelemetryController extends BaseAdminController
{
    private TelemetryAdminService $svc;

    public function __construct()
    {
        parent::__construct();
        $this->svc = new TelemetryAdminService();
    }

    public function dashboard(Request $request)
    {
        $app = $request->string('app', 'vk-dev');
        $range = $request->string('range', '24h'); // 24h|7d|30d
        [$from, $to] = $this->svc->rangeToDates($range);

        $data = $this->svc->getDashboard($app, $from, $to);

        $onlineWindow = 20;
        $onlineCount = $this->svc->getOnlineCount($app, $onlineWindow);

        $this->view('admin/telemetry/dashboard', compact('app', 'range', 'from', 'to', 'data', 'onlineCount', 'onlineWindow'));
    }

    public function online(Request $request)
    {
        $app = $request->string('app', 'vk-dev');
        $window = $request->int('window', 20);
        if (!in_array($window, [10, 20, 30, 60], true)) {
            $window = 20;
        }

        $rows = $this->svc->getOnline($app, $window, 200);
        $this->view('admin/telemetry/online', [
            'app' => $app,
            'window' => $window,
            'rows' => $rows,
        ]);
    }

    public function events(Request $request)
    {
        $app = $request->string('app', 'vk-dev');

        $range = $request->string('range', '24h');
        [$from, $to] = $this->svc->rangeToDates($range);

        $filters = [
            'event' => $request->string('event') ?: null,
            'path'  => $request->string('path') ?: null,
            'sid'   => $request->string('sid') ?: null,
        ];

        $page = max(1, $request->int('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $result = $this->svc->getEvents($app, $from, $to, $filters, $limit, $offset);
        $eventNames = $this->svc->getDistinctEventNames($app);

        $this->view('admin/telemetry/events', [
            'app' => $app,
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'filters' => $filters,
            'eventNames' => $eventNames,
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    public function session(Request $request, string $sessionId)
    {
        $app = $request->string('app', 'vk-dev');
        $detail = $this->svc->getSessionDetail($app, $sessionId);

        $this->view('admin/telemetry/session', [
            'app' => $app,
            'sessionId' => $sessionId,
            'presence' => $detail['presence'],
            'timeline' => $detail['timeline'],
        ]);
    }
}
