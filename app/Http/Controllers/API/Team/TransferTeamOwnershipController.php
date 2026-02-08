<?php

namespace App\Http\Controllers\API\Team;

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

        $team->update(['owner_id' => $newOwnerId]);

        // Ensure the new owner has admin privileges on the pivot
        $team->users()->updateExistingPivot($newOwnerId, ['is_admin' => true]);

        return $this->success(null, 'Ownership transferred successfully');
    }
}
