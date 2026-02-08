<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectDemoAccount
{
    /**
     * Handle an incoming request.
     *
     * Prevents destructive actions on seeded demo accounts when demo mode is enabled.
     *
     * @param  \Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('demo.enabled')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // Protect authenticated user's own account (delete, password change)
        if (in_array($routeName, ['api.v1.auth.destroy', 'api.v1.auth.password'])) {
            if ($this->isDemoAccount($request->user()?->email)) {
                return $this->denyResponse();
            }
        }

        return $next($request);
    }

    private function isDemoAccount(?string $email): bool
    {
        if ($email === null) {
            return false;
        }

        return $email === config('demo.demo_user_email');
    }

    private function denyResponse(): Response
    {
        return response()->json([
            'success' => false,
            'message' => 'Demo accounts cannot be modified.',
        ], 403);
    }
}
