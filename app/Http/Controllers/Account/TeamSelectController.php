<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use Inertia\Inertia;
use Inertia\Response;

class TeamSelectController extends Controller
{
    public function __invoke(): Response|\Symfony\Component\HttpFoundation\Response
    {
        // fetch user's teams
        $teams = auth()->user()->teams;

        // if user has only one team, write team slug to session and redirect to dashboard
        if ($teams->count() === 1) {
            session()->put('current_team', $teams->first()->slug);

            return to_route('story')->with('info', 'You only have one team, so it has been selected for you.');
        }

        // iterate through teams and set current if slug matches session
        $teams->each(function ($team) {
            $team->current = $team->slug === session('current_team');
        });

        return Inertia::render('Account/Team/SelectTeam', [
            'teams' => TeamResource::collection($teams),
        ]);
    }
}
