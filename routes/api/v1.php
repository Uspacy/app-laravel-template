<?php

use App\Http\Controllers\V1\PortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('/portals')->group(function () {
    Route::post('/install', [PortalController::class, 'install']);
    Route::delete('/uninstall', [PortalController::class, 'uninstall']);
});
