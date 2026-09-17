<?php

// API routes are language independent and always start with api/.
$router->get('api/v1/health', 'Api\V1\Controllers\HealthController@index');
