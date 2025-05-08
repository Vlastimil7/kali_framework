<?php

// Definice cest - hlavní stránky
$router->get('', 'HomeController@index');  // prázdná URL = homepage
$router->get('home', 'HomeController@index');

// Uživatelské cesty - přihlášení a registrace
$router->get('login', 'UserController@showLogin');
$router->post('login/process', 'UserController@processLogin');
$router->get('register', 'UserController@showRegister');
$router->post('register/process', 'UserController@processRegister');
$router->get('logout', 'UserController@logout');

// Dashboard pro přihlášené uživatele (Uživatelská část)
$router->get('dashboard', 'DashboardController@index');

// Uživatelský profil (Uživatelská část)
$router->get('profile', 'UserController@showProfile');
$router->post('profile/update', 'UserController@updateProfile');

// Změna jazyka
$router->get('language/change/{lang}', 'LanguageController@changeLanguage');