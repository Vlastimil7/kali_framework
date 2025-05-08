<?php 
// API cesty - verze 1
$router->get('api/v1/users', 'Api\V1\Controllers\UserController@index');
$router->get('api/v1/users/{id}', 'Api\V1\Controllers\UserController@show');
$router->post('api/v1/users', 'Api\V1\Controllers\UserController@create');
$router->put('api/v1/users/{id}', 'Api\V1\Controllers\UserController@update');
$router->delete('api/v1/users/{id}', 'Api\V1\Controllers\UserController@delete');

// API cesty pro menu
$router->get('api/v1/menu', 'Api\V1\Controllers\MenuController@index');
$router->get('api/v1/menu/{id}', 'Api\V1\Controllers\MenuController@show');
$router->get('api/v1/menu/categories', 'Api\V1\Controllers\MenuController@categories');
$router->get('api/v1/menu/category/{id}', 'Api\V1\Controllers\MenuController@categoryItems');

$router->get('api/test', 'Api\TestController@index');
$router->get('api/v1/simple', 'Api\V1\Controllers\SimpleController@index');
$router->get('api/v1/random', 'Api\V1\Controllers\SimpleController@randomNumber');