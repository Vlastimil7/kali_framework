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

// Reset hesla routes
$router->get('password/reset', 'UserController@showPasswordResetRequest');
$router->post('password/email', 'UserController@sendPasswordResetEmail');
$router->get('password/reset/{token}', 'UserController@showPasswordReset');
$router->post('password/update', 'UserController@updatePassword');

// Dashboard pro přihlášené uživatele (Uživatelská část)
$router->get('dashboard', 'DashboardController@index');

// Uživatelský profil (Uživatelská část)
$router->get('profile', 'UserController@showProfile');
$router->post('profile/update', 'UserController@updateProfile');

// Změna jazyka
$router->get('language/change/{lang}', 'LanguageController@changeLanguage');

// Definice cest - cookies 
$router->get('cookies/settings', 'CookieController@showSettings');
$router->post('cookies/save', 'CookieController@saveConsent');
$router->get('cookies/accept-all', 'CookieController@acceptAll');
$router->get('cookies/reject', 'CookieController@rejectAll');
