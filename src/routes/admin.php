<?php

// Admin cesty
$router->get('admin/dashboard', 'AdminController@dashboard');
$router->get('admin/users', 'AdminController@usersList');
$router->get('admin/users/edit/{id}', 'AdminController@editUser');
$router->post('admin/users/update/{id}', 'AdminController@updateUser');
$router->post('admin/users/delete/{id}', 'AdminController@deleteUser');

$router->get('admin/users/create', 'AdminController@addUser');
$router->post('admin/users/store', 'AdminController@storeUser');
$router->get('admin/users/credit/{id}', 'AdminController@addCredit');
$router->post('admin/users/credit/process/{id}', 'AdminController@processAddCredit');


// Správa překladů
$router->get('admin/translations', 'LanguageController@adminTranslations');
$router->get('admin/translations/edit/{key}', 'LanguageController@adminEditTranslation');
$router->get('admin/translations/edit/{key}/{category}', 'LanguageController@adminEditTranslation');
$router->post('admin/translations/edit/process', 'LanguageController@adminProcessEditTranslation');
$router->get('admin/translations/add', 'LanguageController@adminAddTranslation');
$router->post('admin/translations/add/process', 'LanguageController@adminProcessAddTranslation');
$router->get('admin/translations/import', 'LanguageController@adminImportTranslations');
$router->post('admin/translations/import/process', 'LanguageController@adminProcessImportTranslations');
$router->get('admin/translations/export', 'LanguageController@adminExportTranslations');
