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

        // Protect admin actions on demo accounts (update, delete)
        if (in_array($routeName, ['api.v1.admin.users.update', 'api.v1.admin.users.destroy'])) {
            $targetId = $request->route('id');
            $targetUser = \App\Models\User::find($targetId);

            if ($targetUser && $this->isDemoAccount($targetUser->email)) {
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

        return in_array($email, [
            config('demo.super_admin_email'),
            config('demo.admin_email'),
            config('demo.consultant_email'),
            config('demo.client_email'),
            config('demo.guest_email'),
        ]);
    }

    private function denyResponse(): Response
    {
        return response()->json([
            'success' => false,
            'message' => 'Demo accounts cannot be modified.',
        ], 403);
    }
}
