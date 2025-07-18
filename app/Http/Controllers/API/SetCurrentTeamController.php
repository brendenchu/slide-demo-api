<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\SelectTeamRequest;
use Illuminate\Http\JsonResponse;

class SetCurrentTeamController extends Controller
{
    public function __invoke(SelectTeamRequest $request): JsonResponse
    {
        // write team slug to session
        session()->put('current_team', $request->input('team'));

        // write team slug to user settings
        $request->user()->setSetting('current_team', $request->input('team'));

        return response()->json([
            'message' => 'Team selected successfully.',
        ]);
    }
}
