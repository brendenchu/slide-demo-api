<?php

namespace App\Http\Controllers\API\Team;

use App\Http\Controllers\API\ApiController;
use App\Models\Account\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSearchController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
            'team_id' => ['required', 'string'],
        ]);

        $team = Team::where('public_id', $request->input('team_id'))->firstOrFail();

        $query = $request->input('q');

        $existingMemberIds = $team->users()->pluck('users.id');
        $pendingInvitationEmails = $team->invitations()->pending()->pluck('email');

        $users = User::query()
            ->where(function ($q) use ($query): void {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->whereNotIn('id', $existingMemberIds)
            ->whereNotIn('email', $pendingInvitationEmails)
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return $this->success($users);
    }
}
