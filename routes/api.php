<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\PasswordController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\UserController;
use App\Http\Controllers\API\DemoStatusController;
use App\Http\Controllers\API\GetCurrentTeamController;
use App\Http\Controllers\API\SafeNamesController;
use App\Http\Controllers\API\SetCurrentTeamController;
use App\Http\Controllers\API\Story\CompleteProjectController;
use App\Http\Controllers\API\Story\ProjectController;
use App\Http\Controllers\API\Story\SaveResponseController;
use App\Http\Controllers\API\Team\AcceptInvitationController;
use App\Http\Controllers\API\Team\TeamController;
use App\Http\Controllers\API\Team\TeamInvitationController;
use App\Http\Controllers\API\Team\TeamMemberController;
use App\Http\Controllers\API\Team\TransferTeamOwnershipController;
use App\Http\Controllers\API\Team\UserInvitationController;
use App\Http\Controllers\API\Team\UserSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for Vue SPA integration.
| All routes are prefixed with /api and use Sanctum authentication.
|
*/

// API v1 Routes
Route::prefix('v1')->group(function (): void {

    // Public endpoints
    Route::get('demo/status', DemoStatusController::class)->name('api.v1.demo.status');
    Route::get('names', SafeNamesController::class)->name('api.v1.names');

    // Public authentication routes with strict rate limiting (5 attempts per minute)
    Route::prefix('auth')->middleware(['throttle:5,1', 'demo_limit'])->group(function (): void {
        Route::post('login', LoginController::class)->name('api.v1.auth.login');
        Route::post('register', RegisterController::class)->name('api.v1.auth.register');
    });

    // Protected routes (require authentication) with standard rate limiting (60 per minute)
    Route::middleware(['auth:sanctum', 'throttle:60,1', 'demo_limit', 'protect_demo_account'])->group(function (): void {

        // Auth user profile routes
        Route::prefix('auth')->group(function (): void {
            Route::post('logout', LogoutController::class)->name('api.v1.auth.logout');
            Route::get('user', [UserController::class, 'show'])->name('api.v1.auth.user');
            Route::put('user', [UserController::class, 'update'])->name('api.v1.auth.update');
            Route::delete('user', [UserController::class, 'destroy'])->name('api.v1.auth.destroy');
            Route::put('password', [PasswordController::class, 'update'])->name('api.v1.auth.password');
        });

        // Projects routes
        Route::prefix('projects')->group(function (): void {
            Route::get('/', [ProjectController::class, 'index'])->name('api.v1.projects.index');
            Route::post('/', [ProjectController::class, 'store'])->name('api.v1.projects.store');
            Route::get('/{id}', [ProjectController::class, 'show'])->name('api.v1.projects.show');
            Route::put('/{id}', [ProjectController::class, 'update'])->name('api.v1.projects.update');
            Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('api.v1.projects.destroy');
            Route::post('/{id}/responses', SaveResponseController::class)->name('api.v1.projects.save-responses');
            Route::post('/{id}/complete', CompleteProjectController::class)->name('api.v1.projects.complete');
        });

        // User search (for team invitations)
        Route::get('/users/search', UserSearchController::class)->name('api.v1.users.search');

        // Teams routes
        Route::prefix('teams')->group(function (): void {
            Route::get('/', [TeamController::class, 'index'])->name('api.v1.teams.index');
            Route::post('/', [TeamController::class, 'store'])->name('api.v1.teams.store');
            Route::post('/current', SetCurrentTeamController::class)->name('api.v1.teams.set-current');
            Route::get('/{teamId}', [TeamController::class, 'show'])->name('api.v1.teams.show');
            Route::put('/{teamId}', [TeamController::class, 'update'])->name('api.v1.teams.update');
            Route::delete('/{teamId}', [TeamController::class, 'destroy'])->name('api.v1.teams.destroy');

            // Team members
            Route::get('/{teamId}/members', [TeamMemberController::class, 'index'])->name('api.v1.teams.members.index');
            Route::delete('/{teamId}/members/{userId}', [TeamMemberController::class, 'destroy'])->name('api.v1.teams.members.destroy');
            Route::put('/{teamId}/members/{userId}/role', [TeamMemberController::class, 'updateRole'])->name('api.v1.teams.members.update-role');

            // Transfer ownership
            Route::post('/{teamId}/transfer-ownership', TransferTeamOwnershipController::class)->name('api.v1.teams.transfer-ownership');

            // Team invitations
            Route::get('/{teamId}/invitations', [TeamInvitationController::class, 'index'])->name('api.v1.teams.invitations.index');
            Route::post('/{teamId}/invitations', [TeamInvitationController::class, 'store'])->name('api.v1.teams.invitations.store');
            Route::delete('/{teamId}/invitations/{invitationId}', [TeamInvitationController::class, 'destroy'])->name('api.v1.teams.invitations.destroy');
        });

        // User invitations
        Route::get('/invitations', [UserInvitationController::class, 'index'])->name('api.v1.invitations.index');
        Route::post('/invitations/{invitationId}/accept', AcceptInvitationController::class)->name('api.v1.invitations.accept');
        Route::post('/invitations/{invitationId}/decline', [UserInvitationController::class, 'decline'])->name('api.v1.invitations.decline');
    });
});

// Legacy team routes (existing)
Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('team')->group(function (): void {
        Route::get('current', GetCurrentTeamController::class)->name('api.get-current-team');
        Route::post('current', SetCurrentTeamController::class)->name('api.set-current-team');
    });
});
