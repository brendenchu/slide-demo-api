<?php

namespace App\Http\Controllers\API\Team;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\API\Team\AcceptInvitationRequest;
use App\Models\Account\TeamInvitation;
use Illuminate\Http\JsonResponse;

class AcceptInvitationController extends ApiController
{
    public function __invoke(AcceptInvitationRequest $request, string $invitationId): JsonResponse
    {
        $invitation = TeamInvitation::where('public_id', $invitationId)
            ->pending()
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return $this->error('This invitation has expired', 422);
        }

        $user = auth()->user();

        if ($invitation->email !== $user->email) {
            return $this->forbidden('This invitation was not sent to your email address');
        }

        if ($invitation->team->users()->where('users.id', $user->id)->exists()) {
            return $this->error('You are already a member of this team', 422);
        }

        $invitation->accept($user);

        return $this->success(null, 'Invitation accepted successfully');
    }
}
