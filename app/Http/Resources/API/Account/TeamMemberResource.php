<?php

namespace App\Http\Resources\API\Account;

use App\Enums\Account\TeamRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    /**
     * Set by the controller before building the collection so each
     * resource can derive the "owner" role without an extra query.
     */
    public static ?int $teamOwnerId = null;

    /**
     * Transform the resource into an array.
     *
     * Expects User model with pivot data from users_teams.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isOwner = self::$teamOwnerId !== null
            && (int) $this->id === self::$teamOwnerId;

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->resolveRole($isOwner),
            'is_admin' => $isOwner || (bool) $this->pivot->is_admin,
            'joined_at' => $this->pivot->created_at?->toISOString(),
        ];
    }

    private function resolveRole(bool $isOwner): string
    {
        if ($isOwner) {
            return TeamRole::Owner->value;
        }

        return $this->pivot->is_admin
            ? TeamRole::Admin->value
            : TeamRole::Member->value;
    }
}
