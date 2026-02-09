<?php

namespace App\Http\Controllers\API\Team;

use App\Enums\Account\TeamRole;
use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Team\UpdateTeamMemberRequest;
use App\Http\Resources\API\Account\TeamMemberResource;
use App\Models\Account\Team;
use Illuminate\Http\JsonResponse;

class TeamMemberController extends ApiController
{
    public function index(string $teamId): JsonResponse
    {
        $team = Team::where('public_id', $teamId)->firstOrFail();

        if (! auth()->user()->teams->contains($team)) {
            return $this->forbidden('You do not have access to this team');
        }

        $members = $team->users()->get();

        TeamMemberResource::$team = $team;

        return $this->success(TeamMemberResource::collection($members));
    }

    public function destroy(string $teamId, string $userId): JsonResponse
    {
        $team = Team::where('public_id', $teamId)->firstOrFail();

        if (! $team->isAdmin(auth()->user())) {
            return $this->forbidden('Only team admins can remove members');
        }

        $member = $team->users()->where('users.id', $userId)->first();

        if (! $member) {
            return $this->notFound('Member not found');
        }

        if ($team->isOwner($member)) {
            return $this->error('Transfer ownership before removing the owner', 422);
        }

        if ((int) $userId === auth()->id()) {
            return $this->error('You cannot remove yourself from the team', 422);
        }

        $team->users()->detach($userId);

        return $this->success(null, 'Member removed successfully');
    }

    public function updateRole(UpdateTeamMemberRequest $request, string $teamId, string $userId): JsonResponse
    {
        $team = Team::where('public_id', $teamId)->firstOrFail();

        $member = $team->users()->where('users.id', $userId)->first();

        if (! $member) {
            return $this->notFound('Member not found');
        }

        if ($team->isOwner($member)) {
            return $this->error('Owner role cannot be changed', 422);
        }

        if ((int) $userId === auth()->id()) {
            return $this->error('You cannot change your own role', 422);
        }

        $team->assignTeamRole($member, TeamRole::from($request->input('role')));

        return $this->success(null, 'Member role updated successfully');
    }
}
