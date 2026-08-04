<?php

// ============================
// ADMIN (chráněné middleware pipeline)
// namespace: Controllers\Admin
// ============================

$router->group(['middleware' => ['auth', 'admin', 'csrf']], function (Core\Router $router): void {

    $router->get('admin', 'Admin\\DashboardController@index');
    $router->get('admin/dashboard', 'Admin\\DashboardController@index');

    // Users
    $router->get('admin/users', 'Admin\\UserController@index');
    $router->get('admin/users/create', 'Admin\\UserController@create');
    $router->post('admin/users/store', 'Admin\\UserController@store');
    $router->get('admin/users/edit/{id}', 'Admin\\UserController@edit');
    $router->post('admin/users/update/{id}', 'Admin\\UserController@update');
    $router->post('admin/users/delete/{id}', 'Admin\\UserController@delete');

    // Orders
    $router->get('admin/orders', 'Admin\\OrdersController@index');
    $router->get('admin/orders/{id}', 'Admin\\OrdersController@detail');

    $router->post('admin/orders/{id}/mark-paid', 'Admin\\OrdersController@markPaid');
    $router->post('admin/orders/{id}/cancel', 'Admin\\OrdersController@cancel');
    $router->post('admin/orders/{id}/refund', 'Admin\\OrdersController@refund');
    $router->post('admin/orders/{id}/expire', 'Admin\\OrdersController@expire');

    $router->get('admin/orders/edit/{id}', 'Admin\\OrdersController@edit');
    $router->post('admin/orders/update/{id}', 'Admin\\OrdersController@update');

    // Vouchers
    $router->get('admin/vouchers', 'Admin\\VouchersController@index');
    $router->get('admin/vouchers/{id}', 'Admin\\VouchersController@detail');
    $router->get('admin/vouchers/create', 'Admin\\VouchersController@create');
    $router->post('admin/vouchers/store', 'Admin\\VouchersController@store');
    $router->get('admin/vouchers/edit/{id}', 'Admin\\VouchersController@edit');
    $router->post('admin/vouchers/update/{id}', 'Admin\\VouchersController@update');
    $router->post('admin/vouchers/deactivate/{id}', 'Admin\\VouchersController@deactivate');

    // Voucher codes verify / actions
    $router->get('admin/voucher-codes/verify', 'Admin\\VoucherCodesController@verifyForm');
    $router->post('admin/voucher-codes/verify', 'Admin\\VoucherCodesController@verify');
    $router->post('admin/voucher-codes/redeem', 'Admin\\VoucherCodesController@redeem');
    $router->post('admin/voucher-codes/void', 'Admin\\VoucherCodesController@void');
    $router->post('admin/voucher-codes/exchange', 'Admin\\VoucherCodesController@exchange');

    // Telemetry cesty
    $router->get('admin/telemetry', 'Admin\\TelemetryController@dashboard');
    $router->get('admin/telemetry/online', 'Admin\\TelemetryController@online');
    $router->get('admin/telemetry/events', 'Admin\\TelemetryController@events');
    $router->get('admin/telemetry/session/{sessionId}', 'Admin\\TelemetryController@session');
});
