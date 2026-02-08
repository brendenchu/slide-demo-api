<?php

namespace App\Http\Controllers\API\Team;

use App\Enums\Account\TeamRole;
use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Team\TransferTeamOwnershipRequest;
use App\Models\Account\Team;
use Illuminate\Http\JsonResponse;

class TransferTeamOwnershipController extends ApiController
{
    public function __invoke(TransferTeamOwnershipRequest $request, string $teamId): JsonResponse
    {
        $team = Team::where('public_id', $teamId)->firstOrFail();

        $newOwnerId = (int) $request->input('user_id');

        if (! $team->users()->where('users.id', $newOwnerId)->exists()) {
            return $this->error('The specified user is not a member of this team', 422);
        }

        $currentOwner = $request->user();
        $newOwner = $team->users()->where('users.id', $newOwnerId)->first();

        $team->assignTeamRole($currentOwner, TeamRole::Admin);
        $team->assignTeamRole($newOwner, TeamRole::Owner);

        return $this->success(null, 'Ownership transferred successfully');
    }
}
