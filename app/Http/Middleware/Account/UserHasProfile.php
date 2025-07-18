<?php

namespace App\Http\Middleware\Account;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserHasProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // get the user from the request
        $user = $request->user();

        // if the user does not have a profile
        if (! $user->profile) {
            // redirect the user to the profile creation page
            return to_route('profile.create');
        }

        // if the user has a profile and current route is the profile creation route
        if ($request->routeIs('profile.create')) {
            // redirect the user to the profile edit page
            return to_route('profile.edit');
        }

        // otherwise, continue with the request
        return $next($request);
    }
}
