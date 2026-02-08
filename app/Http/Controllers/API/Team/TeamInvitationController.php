<?php

namespace App\Http\Controllers\API\Team;

use App\Enums\Account\InvitationStatus;
use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Team\InviteTeamMemberRequest;
use App\Http\Resources\API\Account\TeamInvitationResource;
use App\Models\Account\Team;
use App\Models\Account\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TeamInvitationController extends ApiController
{
    public function index(string $teamId): JsonResponse
    {
        $team = Team::where('public_id', $teamId)->firstOrFail();

        if (! $team->isAdmin(auth()->user())) {
            return $this->forbidden('Only team admins can view invitations');
        }

        $invitations = $team->invitations()
            ->pending()
            ->with('invitedBy')
            ->latest()
            ->get();

        return $this->success(TeamInvitationResource::collection($invitations));
    }

    public function store(InviteTeamMemberRequest $request, string $teamId): JsonResponse
    {
        $team = Team::where('public_id', $teamId)->firstOrFail();
        $email = $request->input('email');

        if ($team->users()->where('email', $email)->exists()) {
            return $this->error('This user is already a member of the team', 422);
        }

        if ($team->invitations()->pending()->forEmail($email)->exists()) {
            return $this->error('An invitation has already been sent to this email', 422);
        }

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'invited_by' => auth()->id(),
            'email' => $email,
            'token' => Str::random(64),
            'role' => $request->input('role'),
            'status' => InvitationStatus::Pending,
            'expires_at' => now()->addDays(7),
        ]);

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $invitation->update(['user_id' => $existingUser->id]);
            $existingUser->notify(new TeamInvitationNotification($invitation));
        }

        $invitation->load('invitedBy');

        return $this->created(
            new TeamInvitationResource($invitation),
            'Invitation sent successfully'
        );
    }

    public function destroy(string $teamId, string $invitationId): JsonResponse
    {
        $team = Team::where('public_id', $teamId)->firstOrFail();

        if (! $team->isAdmin(auth()->user())) {
            return $this->forbidden('Only team admins can cancel invitations');
        }

        $invitation = $team->invitations()
            ->where('public_id', $invitationId)
            ->pending()
            ->firstOrFail();

        $invitation->update(['status' => InvitationStatus::Cancelled]);

        return $this->success(null, 'Invitation cancelled successfully');
    }
}
