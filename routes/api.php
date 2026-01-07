<?php

use App\Http\Controllers\DetrackWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyWebhookController;
use App\Http\Controllers\LalamoveWebhookController;

Route::post('/webhooks/shopify/createOrder', [ShopifyWebhookController::class, 'createOrder']);
Route::post('/webhooks/shopify/updateOrder', [ShopifyWebhookController::class, 'updateOrder']);
// Lalamove
Route::post('/webhooks/lalamove/updateOrderStatus', [LalamoveWebhookController::class, 'updateOrderStatus']);
// Detrack
Route::post('/webhooks/detrack/updateJobStatus', [DetrackWebhookController::class, 'updateJobStatus']);
