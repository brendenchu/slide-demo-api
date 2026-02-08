<?php

namespace App\Http\Resources\API\Account;

use App\Enums\Account\TeamRole;
use App\Models\Account\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    /**
     * Set by the controller before building the collection so each
     * resource can derive the role via Spatie permissions.
     */
    public static ?Team $team = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = $this->resolveRole();
        $teamRole = TeamRole::tryFrom($role);

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $role,
            'is_admin' => $teamRole?->isAdminLevel() ?? false,
            'joined_at' => $this->pivot->created_at?->toISOString(),
        ];
    }

    private function resolveRole(): string
    {
        if (self::$team) {
            $role = self::$team->getUserTeamRole($this->resource);

            return $role?->value ?? TeamRole::Member->value;
        }

        return TeamRole::Member->value;
    }
}
