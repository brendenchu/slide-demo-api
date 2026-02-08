<?php

namespace App\Http\Controllers\API\Team;

use App\Enums\Account\InvitationStatus;
use App\Http\Controllers\API\ApiController;
use App\Http\Resources\API\Account\TeamInvitationResource;
use App\Models\Account\TeamInvitation;
use Illuminate\Http\JsonResponse;

class UserInvitationController extends ApiController
{
    public function index(): JsonResponse
    {
        $invitations = TeamInvitation::query()
            ->pending()
            ->forEmail(auth()->user()->email)
            ->with(['team', 'invitedBy'])
            ->latest()
            ->get();

        return $this->success(TeamInvitationResource::collection($invitations));
    }

    public function decline(string $invitationId): JsonResponse
    {
        $invitation = TeamInvitation::where('public_id', $invitationId)
            ->pending()
            ->firstOrFail();

        if ($invitation->email !== auth()->user()->email) {
            return $this->forbidden('This invitation was not sent to your email address');
        }

        $invitation->update(['status' => InvitationStatus::Declined]);

        return $this->success(null, 'Invitation declined successfully');
    }
}
