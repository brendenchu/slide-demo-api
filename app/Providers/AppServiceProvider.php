<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // disable wrapping of json resources
        JsonResource::withoutWrapping();

        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    private function configureRateLimiting(): void
    {
        // Authenticated API: 60 requests per minute, keyed by user ID
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        // Login/register: 5 per minute, keyed by IP + email to prevent credential stuffing
        RateLimiter::for('auth', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perMinute(5)->by('email:' . $request->input('email', '')),
        ]);

        // Public unauthenticated endpoints: 30 per minute per IP
        RateLimiter::for('public', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
    }
}
