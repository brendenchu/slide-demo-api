<?php

namespace App\Http\Resources\API\Account;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamInvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status->key(),
            'team' => new TeamResource($this->whenLoaded('team')),
            'invited_by' => $this->when($this->relationLoaded('invitedBy'), fn () => [
                'id' => (string) $this->invitedBy->id,
                'name' => $this->invitedBy->name,
            ]),
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
