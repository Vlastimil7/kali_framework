<?php

$router->get('api/v1/chat/status', 'Api\V1\Controllers\ChatController@status');
$router->get('api/v1/chat/health', 'Api\V1\Controllers\ChatController@health');
$router->post('api/v1/chat', 'Api\V1\Controllers\ChatController@index');


$router->post('api/v1/telemetry/collect', 'Api\V1\Controllers\TelemetryController@collect');
