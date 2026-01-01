<?php

use App\Http\Controllers\Story\CompleteStoryController;
use App\Http\Controllers\Story\ContinueStoryController;
use App\Http\Controllers\Story\Form\LoadFormController;
use App\Http\Controllers\Story\Form\SaveFormController;
use App\Http\Controllers\Story\NewStoryController;
use App\Http\Controllers\Story\PublishStoryController;
use App\Http\Controllers\Story\StoryController;
use App\Http\Controllers\Story\StoryDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['verified', 'profile', 'team'])
    ->prefix('dashboard')
    ->group(function (): void {
        // Dashboard
        Route::get('{project}', StoryDashboardController::class)->name('story.dashboard');

    });

Route::middleware(['verified', 'terms', 'profile', 'team'])
    ->prefix('form')
    ->group(function (): void {
        // New
        Route::prefix('new')
            ->group(function (): void {
                Route::get('/', [NewStoryController::class, 'create'])->name('story.create');
                Route::post('/', [NewStoryController::class, 'store'])->name('story.store');
            });
        // Save Responses
        Route::post('save-responses', SaveFormController::class)->name('story.save-responses');
        Route::post('publish', PublishStoryController::class)->name('story.publish');

        Route::prefix('{project}')
            ->group(function (): void {
                // Continue
                Route::get('continue', ContinueStoryController::class)->name('story.continue');
                // Complete
                Route::get('complete', CompleteStoryController::class)->name('story.complete');
                // Form
                Route::get('{step}', LoadFormController::class)->name('story.form');
            });

        Route::get('/', StoryController::class)->name('story');

    });
