<?php

use App\Http\Controllers\API\GetCurrentTeamController;
use App\Http\Controllers\API\SetCurrentTeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('team')->group(function () {
        Route::get('current', GetCurrentTeamController::class)->name('api.get-current-team');
        Route::post('current', SetCurrentTeamController::class)->name('api.set-current-team');
    });
});
