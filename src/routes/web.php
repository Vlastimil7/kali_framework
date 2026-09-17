<?php

// Public pages. Add new page routes here.
$router->get('', 'Front\HomeController@index');
$router->get('docs', 'Front\DocsController@index');
$router->get('contact', 'Front\ContactPageController@index');
$router->get('hello/{name}', 'Front\HomeController@hello');
$router->post('demo/validate', 'Front\HomeController@validateDemo')->middleware('csrf');

// Cookie preferences are available on every website built from this starter.
$router->get('cookies', 'Front\CookieController@showSettings');
$router->post('cookies', 'Front\CookieController@save')->middleware('csrf');
