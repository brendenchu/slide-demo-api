<?php

use App\Http\Controllers\Account\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('profile')
    ->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
