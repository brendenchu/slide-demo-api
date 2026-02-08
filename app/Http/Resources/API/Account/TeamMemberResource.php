<?php

namespace App\Http\Resources\API\Account;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Expects User model with pivot data from users_teams.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => (bool) $this->pivot->is_admin,
            'joined_at' => $this->pivot->created_at?->toISOString(),
        ];
    }
}
