<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\User\AdminPasswordResetController;
use App\Http\Controllers\Admin\User\BrowseUsersController;
use App\Http\Controllers\Admin\User\ManageUserController;
use App\Http\Controllers\Admin\User\SearchUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:super-admin')->group(function () {
    // future routes for super admin, like debugging tools, etc.
});

Route::middleware('role:admin|super-admin')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)
            ->name('dashboard');

        // User Submodule
        Route::prefix('users')
            ->name('users.')
            ->group(function () {
                Route::get('/', function () {
                    return to_route('admin.users.browse');
                })->name('index');

                // Browse Users
                Route::get('browse', BrowseUsersController::class)
                    ->name('browse');
                Route::post('browse', BrowseUsersController::class)
                    ->name('browse.post');

                Route::post('search', SearchUserController::class)
                    ->name('search');
                Route::get('new', [ManageUserController::class, 'create'])
                    ->name('create');
                Route::post('new', [ManageUserController::class, 'store'])
                    ->name('store');
                Route::get('{profile}', [ManageUserController::class, 'show'])
                    ->name('show');
                Route::get('{profile}/edit', [ManageUserController::class, 'edit'])
                    ->name('edit');
                Route::get('{profile}/reset-password', AdminPasswordResetController::class)
                    ->name('reset-password');
            });
    });
