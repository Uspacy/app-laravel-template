<?php

use App\Http\Controllers\PortalController;
use App\Http\Controllers\UspacyWebhooksController;
use App\Http\Middleware\ResolvePortalMiddleware;
use Illuminate\Support\Facades\Route;

// {integration}: key of config('uspacy.integrations')
Route::prefix('/{integration}/portals')->group(function () {
    // Uspacy: app installed on a portal
    Route::post('/install', [PortalController::class, 'install']);
    // Uspacy: app removed from a portal
    Route::delete('/uninstall', [PortalController::class, 'uninstall']);
    // Current portal from the JWT
    Route::get('/me', [PortalController::class, 'me'])->middleware(ResolvePortalMiddleware::class);
});

// Test sns_subscription_url: logs SNS messages, no signature check
Route::post('/example/uspacy/webhooks', [UspacyWebhooksController::class, 'handle']);
