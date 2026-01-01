<?php

use App\Http\Controllers\Account\Terms\AcceptTermsController;
use App\Http\Controllers\Account\Terms\SetupTermsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['verified', 'profile', 'team'])
    ->group(function (): void {

        // Terms
        Route::get('accept-terms', SetupTermsController::class)->name('terms.setup');
        Route::post('accept-terms/{terms}', AcceptTermsController::class)->name('terms.accept');

        // Team Submodule
        require __DIR__ . '/team.php';

        // Profile Submodule
        require __DIR__ . '/profile.php';

    });
