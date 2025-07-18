<?php

namespace App\Http\Middleware\Account;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserBelongsToTeam
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

        // if the user does not have a team
        if (! $user->teams->count()) {
            // redirect the user to the team creation page
            to_route('team.create');
        }

        // get the team from the session or the route
        $teamSlug = $request->session()->get('current_team') ?? $user->setting('current_team');

        // if the user does not belong to the team
        if (! $user->teams->contains('key', $teamSlug)) {
            // redirect the user to the team selection page
            return to_route('team.select');
        }

        // save team slug to session
        $request->session()->put('current_team', $teamSlug);

        // otherwise, continue with the request
        return $next($request);
    }
}
