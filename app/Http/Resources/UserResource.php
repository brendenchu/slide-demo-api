<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new ProfileResource($this->profile))->toArray($request),
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'name' => $this->name,
            'team' => TeamResource::make($this->currentTeam()),
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getPermissionsViaRoles()->pluck('name'),
        ];
    }
}
