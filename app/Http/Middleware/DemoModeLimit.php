<?php

namespace App\Http\Middleware;

use App\Models\Account\Team;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoModeLimit
{
    /**
     * Handle an incoming request.
     *
     * Enforces resource creation limits when demo mode is enabled.
     *
     * @param  \Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('demo.enabled')) {
            return $next($request);
        }

        if (! $request->isMethod('POST')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        $check = match ($routeName) {
            'api.v1.auth.register', 'api.v1.admin.users.store' => $this->checkUserLimit(),
            'api.v1.teams.store' => $this->checkTeamLimit($request),
            'api.v1.projects.store' => $this->checkProjectLimit($request),
            'api.v1.teams.invitations.store' => $this->checkInvitationLimit($request),
            default => null,
        };

        if ($check !== null) {
            return response()->json([
                'success' => false,
                'message' => $check,
            ], 403);
        }

        return $next($request);
    }

    private function checkUserLimit(): ?string
    {
        $max = config('demo.limits.max_users');

        if (User::query()->count() >= $max) {
            return "Demo limit reached: maximum of {$max} user accounts.";
        }

        return null;
    }

    private function checkTeamLimit(Request $request): ?string
    {
        $max = config('demo.limits.max_teams_per_user');
        $user = $request->user();

        if ($user && $user->teams()->where('is_personal', false)->count() >= $max) {
            return "Demo limit reached: maximum of {$max} teams per user.";
        }

        return null;
    }

    private function checkProjectLimit(Request $request): ?string
    {
        $max = config('demo.limits.max_projects_per_team');
        $user = $request->user();

        if ($user) {
            $team = $user->currentTeam();

            if ($team && $team->projects()->count() >= $max) {
                return "Demo limit reached: maximum of {$max} projects per team.";
            }
        }

        return null;
    }

    private function checkInvitationLimit(Request $request): ?string
    {
        $max = config('demo.limits.max_invitations_per_team');
        $teamId = $request->route('teamId');

        if ($teamId) {
            $team = Team::where('public_id', $teamId)->first();

            if ($team && $team->invitations()->pending()->count() >= $max) {
                return "Demo limit reached: maximum of {$max} pending invitations per team.";
            }
        }

        return null;
    }
}
