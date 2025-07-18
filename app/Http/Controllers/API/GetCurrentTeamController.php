<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Account\Team;
use Illuminate\Http\JsonResponse;

class GetCurrentTeamController extends Controller
{
    /**
     * Get the current team stored in session.
     */
    public function __invoke(): JsonResponse
    {
        if (session()->has('current_team')) {
            $team = Team::where('key', session('current_team'))->first();

            if ($team) {
                return response()->json([
                    'current_team' => TeamResource::make($team),
                ]);
            }
        }

        return response()->json([
            'message' => 'Team not found.',
        ], 404);
    }
}
