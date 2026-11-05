<?php

use App\Http\Controllers\ExampleIntegration\CrmDealsController;
use App\Http\Controllers\ExampleIntegration\UspacyWebhooksController;
use App\Http\Controllers\PortalController;
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

// Example Uspacy API call on behalf of the current portal
Route::get('/{integration}/crm/deals', [CrmDealsController::class, 'index'])->middleware(ResolvePortalMiddleware::class);

// Test sns_subscription_url: logs SNS messages, no signature check
Route::post('/{integration}/uspacy/webhooks', [UspacyWebhooksController::class, 'handle'])
    ->whereIn('integration', ['example-integration']);
