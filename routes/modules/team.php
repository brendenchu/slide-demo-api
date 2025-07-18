<?php

use App\Http\Controllers\Account\TeamSelectController;
use Illuminate\Support\Facades\Route;

Route::prefix('team')
    ->group(function () {
        // Select
        Route::get('select', TeamSelectController::class)->name('team.select');
    });
