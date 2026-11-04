<?php

use App\Http\Controllers\PortalController;
use App\Http\Middleware\ResolvePortalMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/{integration}/portals')->group(function () {
    Route::post('/install', [PortalController::class, 'install']);
    Route::delete('/uninstall', [PortalController::class, 'uninstall']);
    Route::get('/me', [PortalController::class, 'me'])->middleware(ResolvePortalMiddleware::class);
});
