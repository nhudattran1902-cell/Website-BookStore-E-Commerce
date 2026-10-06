<?php

use App\Http\Controllers\Api\ShippingWebhookController;
use App\Http\Controllers\Customer\PaymentGatewayController;

Route::match(['get', 'post'], '/v1/payments/vnpay/ipn', [PaymentGatewayController::class, 'vnpayIpn'])
    ->middleware('throttle:60,1')
    ->name('api.payments.vnpay.ipn');

Route::post('/v1/payments/momo/ipn', [PaymentGatewayController::class, 'momoIpn'])
    ->middleware('throttle:60,1')
    ->name('api.payments.momo.ipn');

Route::post('/v1/webhooks/shipping/{provider}', [ShippingWebhookController::class, 'handleStatusUpdate'])
    ->whereIn('provider', ['ghn', 'ghtk'])
    ->middleware('throttle:120,1')
    ->name('api.webhooks.shipping');
