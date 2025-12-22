<?php

// ============================
// FRONT (veřejná část)
// namespace: Controllers\Front
// ============================

// Home / Vouchery
$router->get('', 'Front\\VoucherController@index');
$router->get('vouchers', 'Front\\VoucherController@index');

// Contact
$router->get('contact', 'Front\\ContactController@index');
$router->post('contact/send', 'Front\\ContactController@sendMessage');

// Auth
$router->get('login', 'Front\\UserController@showLogin');
$router->post('login/process', 'Front\\UserController@processLogin');
$router->get('logout', 'Front\\UserController@logout');

$router->get('register', 'Front\\UserController@showRegister');
$router->post('register/process', 'Front\\UserController@processRegister');

// Password reset
$router->get('password/reset', 'Front\\UserController@showPasswordResetRequest');
$router->post('password/email', 'Front\\UserController@sendPasswordResetEmail');
$router->get('password/reset/{token}', 'Front\\UserController@showPasswordReset');
$router->post('password/update', 'Front\\UserController@updatePassword');

// Profile
$router->get('profile', 'Front\\UserController@showProfile');
$router->post('profile/update', 'Front\\UserController@updateProfile');

// Cart
$router->get('cart', 'Front\\CartController@index');
$router->post('cart/add-voucher', 'Front\\CartController@addVoucher');
$router->post('cart/update', 'Front\\CartController@updateItem');
$router->post('cart/remove', 'Front\\CartController@removeItem');
$router->post('cart/clear', 'Front\\CartController@clearCart');

$router->get('cart/checkout', 'Front\\CartController@checkout');
$router->post('cart/create-order', 'Front\\CartController@createOrder');

// Order pages
$router->get('order/success', 'Front\\OrderController@success');

// Payments (Comgate)
$router->get('payment/comgate/return', 'Front\\PaymentController@comgateReturn');
$router->post('payment/comgate/notify', 'Front\\PaymentController@comgateNotify');

// Language
$router->get('language/change/{lang}', 'Front\\LanguageController@changeLanguage');

// Cookies / Consent
$router->get('cookies/settings', 'Front\\CookieController@showSettings');
$router->post('cookies/save', 'Front\\CookieController@saveConsent');
$router->get('cookies/accept-all', 'Front\\CookieController@acceptAll');
$router->get('cookies/reject', 'Front\\CookieController@rejectAll');


// GDPR / Privacy Policy
$router->get('gdpr', 'Front\\GdprController@show');

// Terms and Conditions
$router->get('terms', 'Front\\TermsController@show');
$router->get('terms/shipping-payment', 'Front\\TermsController@showShippingPayment');

// Chat
$router->get('chat', 'Front\\ChatController@index');