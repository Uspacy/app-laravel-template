<?php

use App\Http\Controllers\V1\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'portals'
], function () {
    Route::post('install', [PortalController::class, 'install'])->name('portals_install');
    Route::delete('uninstall', [PortalController::class, 'uninstall']);
});
