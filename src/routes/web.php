<?php

// ============================
// FRONT (veřejná část)
// namespace: Controllers\Front
// ============================

// Home / Vouchery
$router->get('', 'Front\\VoucherController@index');
$router->get('vouchers', 'Front\\VoucherController@index');
$router->get('voucher/{slug}', 'Front\\VoucherController@detail');
$router->get('voucher/{slug}/order', 'Front\\VoucherController@orderForm');
$router->post('voucher/{slug}/order/process', 'Front\\VoucherController@processOrder')->middleware('csrf');

// Contact
$router->get('contact', 'Front\\ContactController@index');
$router->post('contact/send', 'Front\\ContactController@sendMessage')->middleware('csrf');

// Auth
$router->get('login', 'Front\\UserController@showLogin');
$router->post('login/process', 'Front\\UserController@processLogin')->middleware('csrf');
$router->post('logout', 'Front\\UserController@logout')->middleware(['auth', 'csrf']);

$router->get('register', 'Front\\UserController@showRegister');
$router->post('register/process', 'Front\\UserController@processRegister')->middleware('csrf');

// Password reset
$router->get('password/reset', 'Front\\UserController@showPasswordResetRequest');
$router->post('password/email', 'Front\\UserController@sendPasswordResetEmail')->middleware('csrf');
$router->get('password/reset/{token}', 'Front\\UserController@showPasswordReset');
$router->post('password/update', 'Front\\UserController@updatePassword')->middleware('csrf');

// Profile
$router->get('profile', 'Front\\UserController@showProfile')->middleware('auth');
$router->post('profile/update', 'Front\\UserController@updateProfile')->middleware(['auth', 'csrf']);

// Cart
$router->get('cart', 'Front\\CartController@index');
$router->post('cart/add-voucher', 'Front\\CartController@addVoucher')->middleware('csrf');
$router->post('cart/update', 'Front\\CartController@updateItem')->middleware('csrf');
$router->post('cart/remove', 'Front\\CartController@removeItem')->middleware('csrf');
$router->post('cart/clear', 'Front\\CartController@clearCart')->middleware('csrf');

$router->get('cart/checkout', 'Front\\CartController@checkout');
$router->post('cart/create-order', 'Front\\CartController@createOrder')->middleware('csrf');

// Order pages
$router->get('order/success', 'Front\\OrderController@success');

// Payments (Comgate)
$router->get('payment/comgate/return', 'Front\\PaymentController@comgateReturn');
$router->post('payment/comgate/notify', 'Front\\PaymentController@comgateNotify');

// Cookies / Consent
$router->get('cookies/settings', 'Front\\CookieController@showSettings');
$router->post('cookies/save', 'Front\\CookieController@saveConsent')->middleware('csrf');
$router->post('cookies/accept-all', 'Front\\CookieController@acceptAll')->middleware('csrf');
$router->post('cookies/reject', 'Front\\CookieController@rejectAll')->middleware('csrf');


// GDPR / Privacy Policy
$router->get('gdpr', 'Front\\GdprController@show');

// Terms and Conditions
$router->get('terms', 'Front\\TermsController@show');
$router->get('terms/shipping-payment', 'Front\\TermsController@showShippingPayment');

// Chat
$router->get('chat', 'Front\\ChatController@index');
$router->post('ai-mode/toggle', 'Front\\AiModeController@toggle')->middleware('csrf');

// Google OAuth
$router->get('auth/google/redirect', 'Front\\AuthController@redirectToGoogle');
$router->get('auth/google/callback', 'Front\\AuthController@handleGoogleCallback');
