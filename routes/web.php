<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // if user is client, redirect to story
    if (auth()->check() && auth()->user()->hasRole(['client', 'guest'])) {
        return Redirect::route('story');
    }

    // if user is admin, redirect to admin dashboard
    if (auth()->check() && auth()->user()->hasRole(['admin', 'super-admin'])) {
        return Redirect::route('admin.dashboard');
    }

    // if user is consultant, redirect to consultant dashboard
    if (auth()->check() && auth()->user()->hasRole(['consultant'])) {
        return Redirect::route('dashboard.consultant');
    }

    return Redirect::route('login');
})->name('home');

Route::middleware('auth:sanctum')->group(function () {

    // Admin Module
    require __DIR__ . '/modules/admin.php';

    // Account Module
    require __DIR__ . '/modules/account.php';

    // Story Module
    require __DIR__ . '/modules/story.php';
});

require __DIR__ . '/auth.php';
