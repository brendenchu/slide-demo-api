<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        // get the user's current profile from the session
        //        $profile = auth()->user()->profile;

        // get the user's current team from the session
        $team = auth()->user()->teams->where('key', session('current_team'))->first();

        // get the team's projects
        $projects = $team->projects;

        return Inertia::render('Account/ClientDashboard', [
            'projects' => $projects->count()
                ? ProjectResource::collection($projects)
                : [],
        ]);
    }
}
